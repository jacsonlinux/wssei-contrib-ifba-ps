# Testes isolados dos filtros de pesquisa

Executar a partir do checkout:

```bash
python3 tests/isolated/run-local.py
```

Requer Linux com namespaces de usuário/rede (`unshare -Urn`), Python 3,
Git e PHP compatível com as dependências do lockfile. O runner copia somente
os arquivos necessários para um diretório temporário descartável, instala as
dependências de produção do lockfile sem scripts/plugins e executa sintaxe e
testes sem rede. O diretório é removido ao terminar. Não usa `.env`, banco,
Solr, dumps, credenciais ou fontes SEI. A instalação de dependências requer rede.
Não executa os alvos `make tests-api`, `restore` ou `install`.

`handler.php` registra as rotas reais no Slim e verifica validação, mapeamento,
limites, combinações, ausência dos novos filtros e preservação dos critérios
anteriores. RN, formatação de erro e middlewares são substitutos de teste:
esta suíte **não valida autenticação ou permissões efetivas**.

`query.php` invoca o construtor Solr real e verifica interseção, demais critérios,
condição de acesso por unidade, regra existente de unidade de protocolo,
ordenação e offsets. DTO/serviços SEI são substitutos; não há indexação, execução
de consulta ou formatação de resultados. As fixtures de texto são ASCII; quando
mbstring está ausente, um substituto usa iconv somente nesses testes.
`sei/SEI.php` é vazio, não contém código SEI.

Avisos e notices viram falhas. Depreciações são excluídas na execução porque o
baseline possui parâmetros implicitamente nullable no PHP 8.4. Não alterar
fontes upstream fora do escopo para silenciar essas depreciações.

Para integração real, usar o roteiro em [pesquisa-filtros.md](../../docs/pesquisa-filtros.md)
e a coleção Postman específica. Testes isolados aprovados não são homologação.

## Consulta de assinante com Lucene

```bash
python3 tests/isolated/run-local.py --lucene
```

Acrescenta Java 17+ aos requisitos. Baixa cinco artefatos Apache Lucene 9.10.0
do Maven Central antes dos testes. Em namespace sem rede, cria índice sintético
em memória e executa as queries produzidas pelo PHP real. Verifica uma pessoa,
documentos com mais de um ID, colisões `7`/`17`/`70`/`107`, campos ausentes,
interseção e condição de unidade. Os dados simulam tokens decimais separados;
não comprovam a representação, permissões ou cobertura do indexador SEI.

## PHP 8.2/8.5 e Solr 9.6.1 descartável

Preparar imagens no Docker **local**, antes da execução:

```bash
docker pull php:8.2-cli
docker pull php:8.5-cli
docker pull solr:9.6.1-slim
python3 tests/isolated/run-local.py --php-image=php:8.2-cli --solr
python3 tests/isolated/run-local.py --php-image=php:8.5-cli
```

`--php-image` executa sintaxe e suítes PHP na imagem escolhida, sem rede,
com fonte/dependências montadas somente para leitura e saída temporária própria.
Não monta `.env`, credenciais, fontes SEI ou estado de outra aplicação.

`--solr` baixa o schema de protocolos do commit público `732cb24f` de
`pengovbr/sei-docker`. O schema é usado sem alterações; os handlers são mínimos
do laboratório, e os recursos de stopwords/sinônimos são vazios e sintéticos.
Isso valida os campos/queries usados, não a análise linguística ou todos os
plugins/configurações da instalação SEI.

O container Solr usa `--network none`, sem publicação de portas ou volumes
persistentes. Os comandos de consulta entram por `docker exec` e acessam apenas
o loopback interno do container. O índice fica em memória/armazenamento efêmero,
com 15 registros fictícios. O runner interrompe/remove seu container ao terminar.
Não executa Make/Compose ou seleciona containers institucionais.

Verificações: queries produzidas pelo PHP real, ID completo e múltiplas pessoas,
interseção com série/tipo em campos numéricos do schema, condição de unidade,
totais e offsets de duas ocorrências por página, ordenação estável e ID ausente.
A condição sintética de unidade não reproduz a autorização efetiva do SEI.
A validação ainda não demonstra a representação emitida pelo indexador real.
