"""Disposable, network-isolated Solr. Public schema, synthetic data only."""
from pathlib import Path
import json
import subprocess
import time
import urllib.request
import uuid

SCHEMA_BASE = ('https://raw.githubusercontent.com/pengovbr/sei-docker/'
               '732cb24f6144c68098a5a6c83a616ff9aae5e1db/containers/solr-9.6.1/'
               'assets/solr9.6.1sei/sei-cores-9.6.1/sei-protocolos/conf/')

def prepare(lab):
    conf = lab / 'solr-conf'
    conf.mkdir(mode=0o755)
    for name in ('schema.xml',):
        target = conf / name
        target.parent.mkdir(parents=True, exist_ok=True)
        with urllib.request.urlopen(SCHEMA_BASE + name, timeout=30) as response:
            target.write_bytes(response.read())
    # Synthetic empty language resources: these tests exercise numeric fields,
    # not Portuguese analysis. The public schema references these file names.
    (conf / 'lang').mkdir()
    (conf / 'lang/stopwords_pt.txt').write_text('')
    (conf / 'synonyms.txt').write_text('')
    # Minimal laboratory handlers; the schema is unmodified. No SEI plugins.
    (conf / 'solrconfig.xml').write_text('''<config>
      <luceneMatchVersion>9.10.0</luceneMatchVersion>
      <schemaFactory class="ClassicIndexSchemaFactory"/>
      <updateHandler class="solr.DirectUpdateHandler2"/>
      <requestHandler name="/select" class="solr.SearchHandler">
        <lst name="defaults"><str name="df">content</str><str name="defType">lucene</str></lst>
      </requestHandler>
      <requestHandler name="/update" class="solr.UpdateRequestHandler"/>
    </config>''')
    return conf

def run(lab, conf):
    name = 'wssei-filtros-lab-' + uuid.uuid4().hex[:12]
    def request(path, params=None, payload=None):
        command = ['docker', 'exec', '-i', name, 'curl', '--noproxy', '*', '-sS', '--max-time', '10',
                   'http://127.0.0.1:8983/solr/wssei/' + path]
        if payload is None:
            command += ['--get']
            for key, value in (params or {}).items():
                command += ['--data-urlencode', f'{key}={value}']
        else:
            command += ['-H', 'Content-Type: application/json', '--data-binary', '@-']
        result = subprocess.run(command, input=json.dumps(payload) if payload is not None else None,
                                text=True, capture_output=True)
        if result.returncode:
            raise RuntimeError('Laboratory Solr not responding')
        return json.loads(result.stdout)
    subprocess.run(['docker', 'run', '--detach', '--rm', '--name', name,
                    '--network', 'none', '--cap-drop', 'ALL', '--security-opt', 'no-new-privileges',
                    '--memory', '1g', '--env', 'SOLR_HEAP=256m',
                    '--tmpfs', '/var/solr:uid=8983,gid=8983,mode=0755',
                    '--mount', f'type=bind,src={conf},dst=/lab-conf,readonly',
                    'solr:9.6.1-slim', 'solr-precreate', 'wssei', '/lab-conf'],
                   check=True, stdout=subprocess.DEVNULL)
    try:
        ready = False
        for _ in range(40):
            try:
                ready = 'response' in request('select', {'q': '*:*', 'wt': 'json', 'rows': 0})
            except (RuntimeError, ValueError):
                pass
            if ready:
                break
            time.sleep(1)
        if not ready:
            raise RuntimeError('Laboratory Solr failed to initialize')
        values = ['7', ';7;17;', ';17;', ';70;', ';7;', ';7;', ';7;', ';7;', None,
                  ';2147483647;', ';107;', ';7;70;', ';17;7;', '7,17', '0007']
        docs = []
        for index, value in enumerate(values, 1):
            doc = {'id': str(index), 'id_prot': index,
                   'id_serie': 322 if index == 5 else 321,
                   'id_tipo_proc': 655 if index == 6 else 654,
                   'tipo_aces_g': 'R' if index in (7, 8) else 'P',
                   'id_uni_aces': ';10;' if index == 7 else ';9;',
                   'dta_ger': f'2026-01-{index:02}T00:00:00Z'}
            if value is not None:
                doc['id_assin'] = value
            docs.append(doc)
        updated = request('update?commit=true&wt=json', payload=docs)
        assert updated.get('responseHeader', {}).get('status') == 0, updated
        checked = 0
        for line in (lab / 'output/signer-queries.tsv').read_text().splitlines():
            signer, kind, query = line.split('\t', 2)
            expected = {'7': [1, 2, 5, 6, 8, 12, 13, 14], '17': [2, 3, 13, 14],
                        '70': [4, 12], '2147483647': [10]}[signer][:]
            if kind == 'combined':
                expected = [n for n in expected if n not in (5, 6)]
            expected.sort(reverse=True)
            received = []
            for offset in range(0, len(expected) + 2, 2):
                result = request('select', {'q': query, 'rows': 2, 'start': offset,
                                 'sort': 'dta_ger desc,id_prot desc', 'fl': 'id', 'wt': 'json'})
                assert 'error' not in result, result
                assert result['response']['numFound'] == len(expected), result
                received.extend(int(d['id']) for d in result['response']['docs'])
            assert received == expected, (query, expected, received)
            checked += 1
        empty = request('select', {'q': 'id_assin:/(.*[^0-9])?999999([^0-9].*)?/', 'wt': 'json'})
        assert empty['response']['numFound'] == 0
        print(f'OK: Solr 9.6.1, public SEI schema, {checked} PHP queries, full IDs, AND, totals, offsets and stable ordering')
    except Exception:
        subprocess.run(['docker', 'logs', '--tail', '35', name], check=False)
        raise
    finally:
        subprocess.run(['docker', 'stop', '--time', '5', name], check=False,
                       stdout=subprocess.DEVNULL, stderr=subprocess.DEVNULL)
