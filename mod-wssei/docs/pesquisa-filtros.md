# Filtros opcionais na pesquisa REST

Contrato candidato para revisão, implementado sobre o WSSEI 3.0.5 do commit
`11c6bd4e65e3d3b56fe3e658891d754982d57e66`. A matriz de compatibilidade
continua a do módulo; compatibilidade de integração ainda precisa de validação
nas versões alvo. Não há migração de dados nem alteração de aplicações clientes.

`GET /api/v2/processo/pesquisar` aceita três parâmetros opcionais de query:

| Parâmetro | Critério indexado |
| --- | --- |
| `idSerie` | `IdSerie` / `id_serie`: série da ocorrência documental |
| `idTipoProcedimento` | `IdTipoProcedimento` / `id_tipo_proc`: tipo processual da ocorrência |
| `idContatoAssinante` | `IdContatoAssinante` / `id_assin`: um contato no critério Assinatura/Autenticação |

Exemplo com IDs fictícios, conservando token e unidade pelos headers existentes:

```text
GET /api/v2/processo/pesquisar?palavrasChave=capacitacao&idSerie=321&idTipoProcedimento=654&idContatoAssinante=7&limit=10&start=0
```

Os IDs devem ser inteiros decimais de `1` a `2147483647`, sem sinal, espaços,
zeros à esquerda ou notação decimal/exponencial. O limite candidato `int32`
acompanha a leitura desses campos como `int` no caminho Solr existente; ainda é
necessário conferir o schema e os catálogos das versões alvo com fontes
autorizadas. Não se promete suporte a IDs maiores sem essa verificação.

Ausência conserva os critérios anteriores. Vazio explícito, arrays e formatos
inválidos retornam HTTP 400 com o envelope de erro do módulo, sem executar a
pesquisa. Não há conversão silenciosa de filtro inválido em pesquisa ampla.
Os parâmetros são de query; não dependem de corpo em GET. Nomes repetidos
seguem o parsing do framework; listas/múltiplos IDs não são suportados.

Os filtros usam as condições existentes da RN, combinadas por AND entre si e
com texto, descrição, assunto, unidade geradora e período. Podem ser usados
sem texto. A inicialização explícita de `q` evita aviso de propriedade indefinida
nesse caminho. Valor válido inexistente ou interseção vazia deve produzir o
envelope vazio existente, após execução bem-sucedida no índice. Erros técnicos
continuam erros; nenhum tratamento novo os converte em vazio.

A série filtra a ocorrência indexada, não todos os documentos de um processo
que contenha aquela série. O tipo processual filtra o campo indexado da
ocorrência; não é texto temático. Confirmar o preenchimento desses campos na
indexação da versão alvo é requisito de aceite. `total` continua contando
ocorrências; não há deduplicação por processo ou assinatura. `start` mantém o
offset Solr e `limit` o tamanho solicitado. A ordenação permanece
`dta_ger desc, id_prot desc`.

Não há alterações nos middlewares de autenticação, sessão, permissões, guarda
de unidade de protocolo, formatação de retorno ou autorização da leitura
posterior. Isso não substitui testes dessas regras num SEI completo. Busca por
assinante está implementada como candidato pelo ID de contato, com as
limitações de representação e cobertura descritas abaixo. Seleção explícita do universo processo/documento
permanece fora desta implementação. Não combinar `buscaRapida` com os novos critérios: permanece a
orientação existente de uso exclusivo da busca rápida.

## Pessoa assinante: referência e contrato candidato

O cliente público do módulo de Pesquisa da Anatel mapeia `hdnIdAssinante` para
`id_assin` e o trata como campo com múltiplos valores no índice:
[sistema.js, linhas 186–190 e 558–577](https://github.com/anatelgovbr/mod-sei-pesquisa/blob/34eceaf7d64689eb7a8099ada35dfd1de4098a69/sei/web/modulos/pesquisa/solr/js/sistema.js#L558).
É uma referência pública de consulta, não a fonte do indexador nem garantia
sobre todas as versões. Nela, o valor informado é pesquisado com `*ID*`.

Este candidato recebe **um único ID de contato por requisição**. Ele não aceita
nome, ID de usuário ou ID de cargo/função de `/assinante/listar`. A consulta
proposta exige um token numérico completo para evitar confundir `7`, `17` e
`70`, com a expressão Lucene `id_assin:/(.*[^0-9])?7([^0-9].*)?/` no exemplo.
O cliente REST não pode fornecer uma expressão: o servidor a constrói somente
após validar e converter o inteiro.

A expressão não fixa um delimitador como ponto e vírgula. Seu **pré-requisito
é que o índice represente cada contato como token decimal canônico, separado
de outros números por caracteres não numéricos ou pelos limites do campo**.
Esses tokens devem representar contatos, sem outros números que possam ser
confundidos com eles, como IDs de assinatura ou datas.
A referência pública comprova a intenção da consulta por ID contido, mas não
prova esse pré-requisito no produtor de todas as versões. Tokens concatenados,
IDs com zeros à esquerda ou representação diferente exigem revisão do candidato;
não declarar ausência de assinaturas ou completude antes dessa verificação.

O filtro não altera o índice nem carrega todas as assinaturas para filtrar após
a paginação. Interseção, totais e paginação continuam sendo calculados pela
pesquisa existente. A semântica corresponde ao campo Assinatura/Autenticação:
a distinção de assinatura de autenticação e a cobertura de documentos externos,
assinaturas avançadas/qualificadas, revogação e cancelamento precisam ser
comprovadas na homologação. Não prometer equivalência integral com a tela.

Os testes Lucene e Solr usam apenas representações sintéticas escolhidas para validar
o algoritmo. Eles não provam que o SEI produza essas representações. Não houve
execução do candidato contra o SEI real. A entrega permanece pendente de
integração, confirmação da representação e revisão do contrato.

## Validação isolada

Ver [tests/isolated/README.md](../tests/isolated/README.md). Os testes locais
passaram em PHP 8.2.35 e 8.5.12, além de Lucene e Solr 9.6.1 descartável
com o schema público e dados fictícios. PHPCS e Rector passaram nos três
arquivos PHP alterados. A suíte não requer fontes SEI e não comprova resultados
institucionais, autorização efetiva ou a representação produzida pelo indexador.

## Laboratório de integração: pré-requisitos

Antes de executar integração, disponibilizar:

- Fontes **SEI, SIP e Infra obtidas por canal autorizado**, com versões e patches
  registrados. Infraestrutura Docker pública não fornece essas fontes.
  O workflow upstream consulta `pengovbr/sei` usando credencial própria dos
  mantenedores; essa configuração não concede acesso a outros contribuidores.
- Docker e Compose compatíveis; runtime PHP/extensões exigidos pelo SEI alvo;
  Composer e Newman. A CI PHP atual cobre 8.2 e 8.5; o Composer declara 8.2.
- Banco e índice Solr exclusivos do laboratório, sem conexões institucionais,
  com dados fictícios e permissões sintéticas. Conferir schema/indexação.
- Arquivos `.env` e `.modulo.env` locais ignorados, com caminhos e segredos
  exclusivos. Conferir todos os mounts antes de iniciar serviços.
- Responsável e ambiente autorizados para a execução. A homologação pode ser
  feita por terceiro autorizado, mas ambiente e responsável não estão definidos
  por esta contribuição.

`make tests-api` depende de `tests-up`, que executa `up restore install`:
restaura banco e instala o módulo, além das escritas da coleção geral. O alvo
`restore` seleciona containers por nome contendo `database`. Não executá-lo
num host compartilhado ou apontado a instalação institucional. Os dumps
upstream não foram auditados como sintéticos; não usá-los nesta contribuição
sem essa conferência. Preferir base nova com fixtures próprias.

## Fixtures e coleção Postman preparada

A coleção [Filtros-Pesquisa.postman_collection.json](../tests/Postman/Filtros-Pesquisa.postman_collection.json)
contém somente GETs para filtros, paginação, vazios, entradas inválidas e token
inválido/ausente. É separada da coleção geral para não disparar restaurações ou
escritas. Não está aprovada por execução num SEI completo.

Preparar dois tipos (`654`, `655`) e duas séries (`321`, `322`) numa base
inteiramente sintética. Criar os documentos públicos abaixo, todos sem anexos,
com datas de geração distintas, nessa ordem crescente, e conteúdo fictício:

| Processo | Tipo | Documento | Série | Data sintética |
| --- | --- | --- | --- | --- |
| 1001 | 654 | 2001 | 321 | 01/01/2026 |
| 1001 | 654 | 2002 | 322 | 02/01/2026 |
| 1002 | 655 | 2003 | 321 | 03/01/2026 |
| 1003 | 654 | 2004 | 321 | 04/01/2026 |

Para os cenários candidatos de assinante, associar contatos sintéticos `7` e
`17` ao documento `2001`, apenas `17` ao `2002` e apenas `7` aos documentos
`2003` e `2004`, usando os mecanismos nativos de assinatura do laboratório.
Confirmar os IDs efetivos e a representação indexada antes de executar a coleção.

Os IDs são ilustrativos, não instruções para inserção direta em banco. Usar os
mecanismos nativos de cadastro. Ajustar IDs e expectativas da coleção aos IDs
alocados pelo laboratório e registrar o mapa. Reservar `999999` como inexistente
ou substituí-lo por outro positivo confirmado ausente. Aguardar indexação e
confirmar que existem exatamente essas ocorrências documentais, sem anexos
textuais extras, antes dos testes. Não ajustar expectativas para esconder
resultados indevidos. A comparação por tipo deve incluir também ocorrências
processuais, conforme o índice da versão alvo.

Depois da montagem autorizada e revisão das fixtures:

```bash
# Definir token sintético apenas no ambiente local; não versioná-lo.
export WSSEI_LAB_DISPOSABLE=1
export WSSEI_LAB_BASEURL=http://127.0.0.1:8000/sei/modulos/wssei/controlador_ws.php/api/v2
node tests/Postman/run-filtros-local.mjs
```

O runner exige `WSSEI_LAB_TOKEN` no ambiente e Newman no PATH; só permite HTTP
em `127.0.0.1` e não segue redirects. Não cria nem restaura dados. A coleção
contém uma guarda adicional para execução manual. Loopback não prova isolamento:
verificar que não existe túnel ou proxy para instalação institucional.

## Aceite de integração ainda necessário

Comparar baseline e candidato na mesma base estável e registrar comandos,
versões, IDs sintéticos, resultados esperados/observados e tempos. Executar:

- Regressão sem novos filtros: mesmo envelope, campos, ordenação e paginação.
- Série e tipo isolados/conjuntos; texto, assunto, unidade e período; só filtros;
  IDs inexistentes; duas ou mais páginas com totais e ocorrências exatas.
- Dois usuários/unidades com acessos diferentes e unidade de protocolo;
  público/restrito/sigiloso, credencial e revogação, quando aplicáveis.
  Conferir também totais, snippets, metadados e leitura posterior.
- Falha de Solr/timeout/erro interno, sem sucesso vazio ou ampliação da busca.

A coleção cobre apenas parte dessa matriz. Os cenários de autorização,
revogação e falhas exigem montagem e evidência próprias. Não declarar o
candidato homologado apenas com os testes isolados ou com a coleção básica.

A publicação de issue/PR e qualquer contato externo dependem de autorização.
Revisão do código, homologação e eventual instalação são marcos distintos.
