#!/usr/bin/env python3
"""Run only synthetic tests in a disposable directory with network disabled."""
from pathlib import Path
import shutil
import subprocess
import tempfile
import os
import sys
import urllib.request

repo = Path(__file__).resolve().parents[2]
with_lucene = '--lucene' in sys.argv
with_solr = '--solr' in sys.argv
php_image = next((a.split('=', 1)[1] for a in sys.argv if a.startswith('--php-image=')), None)
if (with_solr or php_image) and not shutil.which('docker'):
    raise SystemExit('Required: Docker')
if with_lucene and not shutil.which('java'):
    raise SystemExit('Required for --lucene: Java 17+')
for program in ('php', 'unshare', 'git'):
    if not shutil.which(program):
        raise SystemExit(f'Required: {program}')
# Fail before dependency installation if network isolation is unavailable.
subprocess.run(['unshare', '-Urn', 'true'], check=True)
with tempfile.TemporaryDirectory(prefix='wssei-filtros-lab-') as directory:
    lab = Path(directory)
    for name in ('composer.json', 'composer.lock'):
        shutil.copy2(repo / name, lab / name)
    # Also works with a sparse checkout that omits the bundled Composer binary.
    (lab / 'composer.phar').write_bytes(subprocess.check_output(
        ['git', '-C', str(repo), 'show', 'HEAD:./composer.phar']))
    for name in ('src/versao/MdWsSeiVersaoServicos.php',
                 'src/versao/v2/MdWsSeiServicosV2.php',
                 'src/rn/MdWsSeiProcedimentoRN.php',
                 'src/dto/MdWsSeiPesquisaProtocoloSolrDTO.php'):
        target = lab / name
        target.parent.mkdir(parents=True, exist_ok=True)
        shutil.copy2(repo / name, target)
    shutil.copytree(repo / 'tests/isolated', lab / 'tests/isolated')
    # Only dependency download uses network; no application or tests run here.
    subprocess.run(['php', str(lab / 'composer.phar'), 'install', '--no-dev',
                    '--no-scripts', '--no-plugins', '--no-interaction', '--prefer-dist'],
                   cwd=lab, check=True)
    if with_lucene:
        jars = lab / 'lucene'
        jars.mkdir()
        for artifact in ('lucene-core', 'lucene-queryparser', 'lucene-analysis-common', 'lucene-queries', 'lucene-sandbox'):
            url = f'https://repo.maven.apache.org/maven2/org/apache/lucene/{artifact}/9.10.0/{artifact}-9.10.0.jar'
            with urllib.request.urlopen(url, timeout=60) as response:
                (jars / f'{artifact}.jar').write_bytes(response.read())
    (lab / 'output').mkdir()
    if with_solr:
        sys.dont_write_bytecode = True
        import solr_lab
        solr_conf = solr_lab.prepare(lab)
    # Upstream has nullable-parameter deprecations on PHP 8.4. Warnings and
    # notices remain errors in the tests; deprecations alone are excluded.
    php = ['unshare', '-Urn', 'php', '-d', 'short_open_tag=1',
           '-d', 'error_reporting=8191']
    if php_image:
        php = ['docker', 'run', '--rm', '--network', 'none', '--read-only',
               '--cap-drop', 'ALL', '--security-opt', 'no-new-privileges',
               '--user', f'{os.getuid()}:{os.getgid()}', '--tmpfs', '/tmp',
               '--mount', f'type=bind,src={lab},dst={lab},readonly',
               '--mount', f"type=bind,src={lab / 'output'},dst={lab / 'output'}",
               '--env', f"WSSEI_SIGNER_QUERY_OUTPUT={lab / 'output/signer-queries.tsv'}",
               php_image, 'php', '-d', 'short_open_tag=1', '-d', 'error_reporting=8191']
        subprocess.run(php + ['-v'], check=True)
    for source in sorted(lab.rglob('*.php')):
        if 'vendor' not in source.relative_to(lab).parts:
            subprocess.run(php + ['-l', str(source)], cwd=lab, check=True,
                           stdout=subprocess.DEVNULL)
    for name in ('handler.php', 'query.php'):
        subprocess.run(php + [str(lab / 'tests/isolated' / name)], cwd=lab, check=True,
                       env=dict(os.environ, WSSEI_SIGNER_QUERY_OUTPUT=str(lab / 'output/signer-queries.tsv')))
    if with_lucene:
        subprocess.run(['unshare', '-Urn', 'java', '--class-path', str(jars / '*'),
                        str(lab / 'tests/isolated/SignerQueryTest.java'), str(lab / 'output/signer-queries.tsv')],
                       cwd=lab, check=True)
    if with_solr:
        solr_lab.run(lab, solr_conf)
