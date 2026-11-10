# Execução local — filtros opcionais WSSEI

Data: 10/11/2026. Estado: **candidato implementado; testes isolados aprovados;
integração SEI e homologação pendentes**. Ambiente e responsável pela homologação
não estão definidos; uma eventual solicitação ao IFBA será decidida depois.
Nenhum contato foi realizado.

**Escopo atualizado pelo usuário:** assinante deve integrar a primeira entrega.
Os dois filtros já implementados permanecem candidatos; a entrega está
incompleta até concluir o requisito de assinante e a validação aplicável.
As referências anteriores a assinante fora do escopo registram a fase inicial.

Diagnóstico inicial de assinante: o
[schema público de referência](https://github.com/pengovbr/sei-docker/blob/732cb24f6144c68098a5a6c83a616ff9aae5e1db/containers/solr-9.4.0/assets/solr9.4.0sei/sei-cores-9.4.0/sei-protocolos/conf/schema.xml)
declara `id_assin` como `string` indexada e armazenada. Isso não demonstra o
formato dos valores nem a identidade usada pelo produtor. O WSSEI não expõe
esse critério no DTO/construtor atual. Seu catálogo `/assinante/listar` retorna
cargos/funções (`CargoFuncao`), não uma identidade de pessoa comprovada para
esse campo. A listagem de assinaturas por documento retorna nome, cargo,
unidade e data/hora; não resolve o contrato de pesquisa invertida por si só.

Falta fonte autorizada do indexador/consulta nativa para confirmar família do
ID, delimitação, múltiplas assinaturas, internos/externos e atualização após
cancelamento/revogação. Não foi acrescentada uma query experimental sem essa
evidência. Não tratar `id_assin:<ID>` nem `id_assin:*;<ID>;*` como contratos
confirmados. Próximo passo depende dessas fontes ou de evidência técnica
fornecida por responsável autorizado; não houve contato externo.

### Retomada da implementação de assinante

Revalidado o `main` público de `pengovbr/sei-docker`, SHA
`732cb24f6144c68098a5a6c83a616ff9aae5e1db`. O
[schema Solr 9.6.1](https://github.com/pengovbr/sei-docker/blob/732cb24f6144c68098a5a6c83a616ff9aae5e1db/containers/solr-9.6.1/assets/solr9.6.1sei/sei-cores-9.6.1/sei-protocolos/conf/schema.xml)
também declara `id_assin` como `string`, indexada e armazenada, sem informar
codificação, delimitação ou família do ID. É a versão de Solr referida pelo
Compose WSSEI; não comprova o schema ou conteúdo de nenhuma instalação IFBA.
O código público de pesquisa externa da Anatel consultado lê esse campo para
identificar presença de assinatura, sem demonstrar o produtor dos valores ou
um contrato de filtro por pessoa. Não foi usado como prova dessa equivalência.
Buscas públicas adicionais não localizaram o produtor necessário.

**Impedimento técnico para implementar o filtro:** a declaração de campo string
não permite escolher corretamente entre igualdade, lista delimitada, IDs de
usuário/contato ou outra representação. Acrescentar wildcard arbitrário pode
confundir IDs ou produzir falsos negativos; mocks dessa hipótese não validariam
o comportamento SEI. Nenhum parâmetro de assinante foi exposto como funcional.

Material mínimo a obter por canal autorizado, sem dados ou segredos reais:

1. Versão/patch do SEI alvo e rotina que transforma o critério de pessoa
   assinante da pesquisa nativa em consulta ao índice, incluindo resolução
   dos IDs selecionáveis e tratamento de usuários internos/externos.
2. Trecho do indexador que preenche `id_assin`, com definição da família do
   identificador, formato e comportamento com várias pessoas no mesmo documento.
3. Regra de atualização do campo após alterações de assinatura/autenticação,
   cancelamento e revogação, distinguindo assinatura de autenticação quando
   aplicável. Indicar se documentos externos têm cobertura e em quais condições.
4. Schema efetivamente suportado e exemplos inteiramente sintéticos dos valores
   produzidos, incluindo duas pessoas e IDs como `7`/`17` para testar colisões.

Não é necessário disponibilizar credenciais, dump ou acesso ao SEI real para
essa análise. Podem ser disponibilizados trechos autorizados com versão e
origem identificadas. Qualquer solicitação externa ainda depende de autorização;
nenhum pedido foi enviado ao IFBA ou PEN. Esses requisitos não presumem que o
IFBA tenha assumido a homologação.

Os testes de série/tipo foram reexecutados com
`python3 tests/isolated/run-local.py`: aprovados em laboratório temporário, sem
rede durante sintaxe e testes. Não houve alteração do código PHP nesta retomada.
Assinante continua requisito pendente da primeira entrega, sem data de conclusão
ou contrato público definitivo prometidos.


### Busca adicional em repositórios públicos do PEN

Consultado o catálogo público da organização `pengovbr` (29 repositórios
listados na consulta), sem interpretar essa listagem como leitura de todos os
seus conteúdos. Busca textual adicional em 604 arquivos de código/documentação
nos repositórios `mod-sei-resposta`, `mod-sei-estatisticas`, `mod-sei-pen` e
`sei-loadtests`, sem localizar `id_assin`, variantes de identificação do
assinante ou referência textual conjunta de Solr/assinatura nas buscas usadas.

Arquivos grandes dos projetos de infraestrutura/manuais excederam o limite de
download definido para pesquisa em memória; a busca prosseguiu pela árvore Git
e por arquivos selecionados, sem baixar esses pacotes integralmente:

- `pen-docs`, SHA `dac81cd7ff9961d6efeeb0c7d50833b3f19f899f`:
  43 arquivos textuais selecionados por caminhos de assinatura, pesquisa,
  indexação, documentação técnica/instalação/arquitetura e índices. Nenhuma
  explicação de `id_assin` localizada nesses arquivos.
- `sei-vagrant`, SHA `2051c518ab67aface1bcaf8bf92345236788054f`:
  13 arquivos selecionados. O schema de protocolos Solr 8.2 também declara
  `id_assin` como `string`, indexada e armazenada, sem especificar composição.

Buscas públicas em issues/PRs da organização não localizaram o literal
`id_assin`. Consultas por Solr/assinatura localizaram discussões gerais de erro,
não evidência do formato do campo. Nenhuma issue, PR ou mensagem foi publicada.

Isso reforça a informação parcial já encontrada no `sei-docker`: há declaração
do campo, mas não foi localizado contrato público para seus valores. A ausência
nos arquivos consultados não comprova inexistência em toda a organização,
histórico, documentação restrita ou documentos binários/PDF não pesquisados.

Fontes públicas:
[Schema Solr 8.2](https://github.com/pengovbr/sei-vagrant/blob/2051c518ab67aface1bcaf8bf92345236788054f/solr/assets/solr8sei/sei-cores-8.2.0/sei-protocolos/conf/schema.xml),
[manual PEN](https://github.com/pengovbr/pen-docs/tree/dac81cd7ff9961d6efeeb0c7d50833b3f19f899f),
[schema Solr 9.6.1](https://github.com/pengovbr/sei-docker/blob/732cb24f6144c68098a5a6c83a616ff9aae5e1db/containers/solr-9.6.1/assets/solr9.6.1sei/sei-cores-9.6.1/sei-protocolos/conf/schema.xml).

### Investigação dirigida de documento com duas assinaturas

O usuário forneceu uma referência processual e autorizou pesquisa aprofundada,
leitura do processo/documentos e ajustes necessários no inspetor. A primeira
seleção foi descartada por estar cancelada. Foi identificado outro documento
ativo, com visualização permitida pela REST, que possui duas assinaturas.
Os dois nomes foram relacionados a dois IDs de contato distintos por consultas
dirigidas ao catálogo REST, após seleção no autocompletar web.

A pesquisa nativa pelo número pode redirecionar para o processo. O bloqueio
inicial era do inspetor, não evidência de acesso negado pelo SEI. Criada cópia
privada que permite GET somente para o processo informado e para o documento
selecionado. Abertura de leitura do processo confirmada, sem alterar dados,
backend, configurações ou script original do SEIA.

Não tratar redirecionamento por número como prova isolada de que o filtro foi
aplicado: pode haver atalho de navegação. Snapshot sem formulário após mudança
de página não é resultado vazio. As consultas dirigidas ao documento cancelado
e os resultados indisponíveis foram separados das observações do documento ativo.

O caso comprova duas assinaturas e sua correspondência com contatos nesta
amostra. Não revela como `id_assin` armazena múltiplos valores. Nenhuma query
Solr foi construída com delimitador presumido. O filtro continua pendente.

Relatório detalhado privado:
`/home/kali/seia/var/log/seia/web-inspections/multisign-investigation-gGaHhJ/relatorio-investigacao.md`.
Evidências de seleção, controles, navegação e correlações preservadas com
manifesto SHA-256, arquivos `600`. Referências reais e nomes não foram copiados
para a documentação pública da contribuição. Não houve testes do candidato
em produção, gravações no SEI ou acesso direto ao índice.

### Navegação dirigida: identidade do assinante

Por solicitação de navegação e investigação técnica do usuário, utilizada a
skill existente `sei-web-inspection`, com Puppeteer. Não há Spider Browser
conectado nesta sessão. Foram realizadas operações de filtro e autocompletar,
sem submissão de pesquisa, abertura de processos/documentos ou alteração de
objetos. A conta foi autenticada pelo adaptador REST existente; a senha e o
token não foram exibidos ou persistidos pelos scripts auxiliares.

**Resultado confirmado na amostra da própria conta autenticada:** o
identificador preenchido em `hdnIdAssinante` é numérico e difere do `userId`
devolvido pela autenticação REST. Uma consulta dirigida ao catálogo
`GET /contato/pesquisar`, usando somente esse identificador, retornou um
registro com o mesmo ID e nome correspondente ao da identidade autenticada.
Isso comprova correspondência com o ID de contato nessa amostra. Não comprova
que toda identidade interna/externa siga a mesma regra nem o preenchimento do
campo Solr `id_assin`.

A sugestão web inclui formatação além do nome simples. A primeira tentativa
exigiu igualdade literal e não selecionou nenhuma pessoa. Na retomada, foi
usada a única sugestão contendo o nome completo normalizado; o script nativo
exigiu a seleção por seu rótulo exato. Nenhuma seleção ambígua foi resolvida
arbitrariamente. A confirmação posterior pelo catálogo é evidência adicional
da correspondência observada, não uma garantia universal contra homônimos.

Evidências privadas:

- `/home/kali/seia/var/log/seia/web-inspections/signer-investigation-Zh8ogW/`:
  primeira tentativa, sem seleção por igualdade literal.
- `/home/kali/seia/var/log/seia/web-inspections/inspection-3bOWvx/`:
  snapshots da seleção no formulário, incluindo critérios ocultos normalizados.
- `/home/kali/seia/var/log/seia/web-inspections/signer-investigation-gP3ZeI/`:
  resumo sem nomes/IDs, comparação de contato e manifesto SHA-256 dos arquivos
  de evidência e scripts utilizados. Artefatos privados com modo `600`.

As duas primeiras etapas autenticaram a aplicação para identificar a conta e
executaram o navegador controlado existente; a etapa de comparação realizou
uma autenticação e uma consulta de catálogo dirigida. Não houve consultas
livres ao Solr, consultas de resultado ou coleta por assinaturas/documentos.
Não foram alterados scripts, runtime ou configurações do SEIA: os auxiliares
foram temporários, fora do repositório, e preservados junto à evidência privada.

**Dúvida reduzida, implementação ainda pendente:** existe agora evidência de
contato como família do seletor web para uma pessoa interna. Permanecem sem
prova a transformação desse ID em `id_assin`, sua codificação/delimitação,
usuários externos, múltiplas assinaturas e atualização após mudanças. Não
substituir o ID de contato por `userId` ou pelo ID do catálogo de cargos.

### Autorização posterior e inspeção autenticada

O usuário autorizou usar as credenciais externas e skills do SEIA para a
investigação necessária. Essa decisão substitui a restrição inicial de qualquer
acesso real somente para inspeção técnica de leitura; não autoriza escrita,
implantação, testes do candidato em produção ou coleta ampla.

Consultadas as skills `sei-web-inspection`, `sei-api-study`,
`sei-secrets-and-deploy` e a referência de arquitetura, além dos contratos,
estudos e registros locais existentes. A configuração externa foi carregada
por `loadConfig`, sem exibir valores. Confirmados ambiente de desenvolvimento,
HTTPS, origem documentada e presença de credenciais/navegador. O `.env` externo
possui modo `600`; nenhum segredo foi copiado para este workspace.

Execução autorizada de engenharia, fora do atendimento:

```bash
SEIA_ENV_FILE=/home/kali/seia/etc/seia/.env node scripts/inspect-sei-web.js --live
```

Resultado: autenticação REST/web e confirmação de identidade/unidade pelo
script existente; um snapshot do formulário. Detectados os controles
`txtAssinante`, `selSeriePesquisa` e `selTipoProcedimentoPesquisa`. Zero
submissões de pesquisa e zero navegações para processos/documentos. Evidência
privada em `/home/kali/seia/var/log/seia/web-inspections/inspection-jweQZg/`,
diretório `700`, snapshot `600`. Não copiar seu conteúdo pessoal ao Git.
O backend, suas configurações e serviços não foram modificados.

A documentação anterior já confirma `hdnIdAssinante` na submissão web; seus
registros de funções do cliente não contêm `id_assin`. A inspeção atual confirma
os controles, não o processamento PHP. Credenciais de aplicação não foram
tratadas como acesso às fontes ou ao Solr. Não foram usados parâmetros livres
para consultar campos internos, credenciais em outra origem ou rotas de debug.
O contrato do produtor de `id_assin` permanece pendente; não foi acrescentado
filtro especulativo ao WSSEI.

As afirmações anteriores de ausência de acesso real neste relatório descrevem
a etapa anterior a esta autorização. A inspeção acima é leitura de engenharia,
não teste/homologação dos filtros contra produção.

Checkout: [mod-wssei](mod-wssei/), branch `feat/filtros-pesquisa`.
Base upstream: `11c6bd4e65e3d3b56fe3e658891d754982d57e66`, `master`, WSSEI 3.0.5.
Esse SHA substitui o snapshot `9ac849b` do planejamento como base desta mudança.
As alterações estão no working tree, sem commit candidato, fork remoto ou push.
Remote de leitura: `upstream`; push desabilitado localmente por URL inválida.
Checkout raso/parcial, com dumps, PDFs Postman e Composer binário omitidos do
sparse checkout; nenhum deles integra a contribuição. O runner obtém o Composer
incluído no Git diretamente do objeto local, dentro do laboratório temporário.

## Validação local ampliada dos três filtros

Executadas verificações adicionais autorizadas em laboratório local descartável,
com dados inteiramente fictícios, sem credenciais/configuração SEIA ou chamada
ao IFBA nesta etapa. Não houve instalação do candidato no SEI real.

| Verificação | Resultado observado |
| --- | --- |
| Sintaxe, handler Slim e construtor Solr em PHP 8.2.35 | Aprovados; imagem oficial `php:8.2-cli` |
| Sintaxe, handler Slim e construtor Solr em PHP 8.5.12 | Aprovados; imagem oficial `php:8.5-cli` |
| Solr 9.6.1 executando oito queries produzidas pelo PHP | Aprovado com schema público de protocolos, sem alterações |
| IDs completos, várias pessoas, interseção com série/tipo | Aprovados em 15 registros sintéticos |
| Totais, offsets de duas ocorrências, ordem e ID inexistente | Aprovados no Solr local |
| PHPCS 3.13.5, regras upstream, três arquivos PHP alterados | Zero erros e zero avisos no baseline e candidato |
| Rector 2.8.1, configuração upstream, dry-run dos três arquivos PHP alterados | Zero arquivos alterados e zero erros no baseline e candidato |
| Swagger JSON/YAML e `git diff --check` | Aprovados |

Comandos de validação funcional reproduzíveis:

```bash
cd mod-wssei
python3 tests/isolated/run-local.py --php-image=php:8.2-cli --solr
python3 tests/isolated/run-local.py --php-image=php:8.5-cli
```

O runner foi ampliado para a matriz PHP e para um Solr isolado com
`--network none`, sem portas publicadas nem volumes persistentes. O índice usa
o schema público do commit `732cb24f`; configuração de handlers mínima e
arquivos de stopwords/sinônimos vazios e sintéticos. Isso não equivale à
configuração completa do SEI, à análise linguística ou ao processo de indexação
nativo. Os containers e diretórios de execução são removidos ao encerrar.

Imagens usadas, registradas para rastreabilidade:

- `php:8.2-cli`: digest `sha256:604de2f0cba9e9dde16cf717c2f03b0948c99949bdf7c11cdf76326e0da7d2ea`.
- `php:8.5-cli`: digest `sha256:50956f094e90a716d43bea28087d447555ad80510e386ab8c9c10d8c70d781a9`.
- `solr:9.6.1-slim`: digest `sha256:b8bd901691815cbc89e660720443fb233838b4ca4f0d2863452819a835bd0786`.

A análise estática usou dependências de desenvolvimento do lockfile em diretório
temporário, sem scripts/plugins. A extração foi feita pelo PHP host com extensões
ignoradas somente na instalação; PHPCS/Rector foram executados em PHP 8.2 com
as extensões necessárias, em container sem rede. Não houve mudança no lockfile,
instalação global ou execução da pasta PHPUnit descontinuada. A análise cobre
os três PHP alterados, não uma aprovação integral da CI ou do repositório.

Pré-requisitos locais que falharam foram resolvidos no laboratório: recursos
linguísticos não disponíveis no caminho público foram substituídos por arquivos
vazios explicitamente sintéticos; ausência de unzip na imagem PHP foi contornada
na preparação externa das dependências. Nenhum ajuste ocorreu no SEI real.

**Continua pendente:** confirmar que o indexador da versão SEI alvo produz
contatos como tokens decimais canônicos, com limites não numéricos e sem outros
números misturados; validar assinatura/autenticação e externos, revogação,
cancelamento, permissões reais, snippets/conteúdo e desempenho. A aprovação no
schema/parser e nos dados sintéticos não demonstra essas propriedades. Portanto,
a implementação está validada localmente no recorte descrito, mas não homologada.

Os resultados acima substituem os estados antigos de matriz PHP e ferramentas
estáticas não executadas. CI remota e testes de SEI/SIP/Infra completo permanecem
não executados.

## Atualização vigente: candidato de assinante implementado

A busca em todos os arquivos textuais do módulo público de Pesquisa da Anatel
localizou a referência que faltava na pesquisa anterior limitada ao construtor
PHP: `solr/js/sistema.js`, commit
`34eceaf7d64689eb7a8099ada35dfd1de4098a69`. O mapeamento liga `hdnIdAssinante`
a `id_assin`; a função `partialFields` trata o campo como múltiplo e produz
`*ID*`. Fonte:
[sistema.js](https://github.com/anatelgovbr/mod-sei-pesquisa/blob/34eceaf7d64689eb7a8099ada35dfd1de4098a69/sei/web/modulos/pesquisa/solr/js/sistema.js#L558).
Isso comprova uma intenção pública de consulta por ID contido; não revela o
produtor nem garante representação idêntica em toda versão SEI.

Foi implementado o candidato `idContatoAssinante`: um único ID de contato
positivo por requisição, com a mesma validação estrita dos outros dois filtros.
Novo atributo DTO e condição AND na RN existente. A condição usa expressão
Lucene de token numérico completo, para não confundir IDs `7`, `17`, `70` e
`107`; não fixa separador. O pré-requisito é que o contato esteja indexado como
token decimal canônico separado de outros números. Esse pré-requisito precisa
ser conferido na versão alvo. Não há garantia de correspondência se a versão
usar representação distinta, concatenação de números ou zeros à esquerda.

Contrato e Swagger JSON/YAML atualizados; coleção Postman ampliada. Testes
executados com `python3 tests/isolated/run-local.py --lucene`: aprovados.
Além do handler e construtor reais com dependências SEI substitutas, oito
queries geradas pelo PHP foram executadas em índice Apache Lucene 9.10.0
sintético em memória: ID único, duas pessoas no documento, colisões numéricas,
interseção, campo ausente e condição de unidade. Execução sem rede e em
diretório temporário descartável. O teste de condição de unidade não comprova
a política de acesso efetiva do SEI.

As fixtures escolhidas demonstram o algoritmo, não o formato real do indexador.
Não houve teste do candidato em produção. Cobertura de externos,
autenticação versus assinatura, revogação, desempenho e compatibilidade
PHP/SEI continuam pendentes. A entrega tem três filtros candidatos e ainda não
é homologada. Estados de assinante não implementado nas seções anteriores
registram fases históricas, substituídas por esta atualização.

## Inspeção e contribuições relacionadas

Lidos README, Makefile, Compose, envs, instalação, documentação de testes,
workflows, handler V2, DTO e caminho RN/Solr. Nenhum AGENTS.md, CONTRIBUTING ou
licença raiz foi encontrado na árvore upstream consultada. Conferir condições
de distribuição antes da publicação; a licença declarada no Swagger não foi
tratada como licença de todo o código.

Consulta pública somente de leitura pela API GitHub, com paginação das issues
em todos os estados e lista de PRs em todos os estados. Não foi encontrada uma
entrega equivalente aos dois parâmetros nesta rota; isso não é aceite pelo
mantenedor nem garantia sobre branches externas não consultadas.

| Referência | Resultado da inspeção |
| --- | --- |
| [PR #84](https://github.com/pengovbr/mod-wssei/pull/84) | Fechado sem merge; filtro `serie` em `/documento/listar/{procedimento}`, escopo diferente |
| [PR #68](https://github.com/pengovbr/mod-wssei/pull/68) e [PR #74](https://github.com/pengovbr/mod-wssei/pull/74) | Abertos; pesquisa/unidade administrativa, atenção a eventual conflito futuro |
| [Issue #32](https://github.com/pengovbr/mod-wssei/issues/32) | Pesquisa alterada pela unidade administrativa; não equivale aos dois filtros |
| [Issue #127](https://github.com/pengovbr/mod-wssei/issues/127) | Aberta; discute universo processos/documentos, relevante para a semântica de ocorrências |
| [PR #134](https://github.com/pengovbr/mod-wssei/pull/134) | Incorporado; baseline atual usa Slim 4 e CI PHP 8.2/8.5 |

## Implementação

- Handler de `/api/v2/processo/pesquisar`: lê `idSerie` e
  `idTipoProcedimento` da query; distingue ausência de vazio/array/inválido;
  valida decimal positivo canônico até `2147483647`; retorna HTTP 400 pelo
  formatador de erro existente antes de chamar a pesquisa; encaminha ao DTO.
- RN: apenas inicializa `q` para consultas sem palavras-chave. Reutiliza
  integralmente as condições existentes de série, tipo, acesso e unidade.
- Swagger JSON/YAML: parâmetros, limites, interseção, ocorrências, offset e 400.
  JSON é consumido pela página Swagger atual.
- Testes isolados, runner descartável sem rede, coleção Postman dedicada de
  35 requisições GET e runner restrito a loopback sem redirects.
- [Contrato, fixtures e roteiro de integração](mod-wssei/docs/pesquisa-filtros.md).

Os nomes e o intervalo `int32` são decisões candidatas, ainda não alinhadas com
o mantenedor. O limite acompanha a leitura pública dos campos como `int` no
caminho Solr, mas o schema e os catálogos das versões alvo ainda precisam ser
conferidos com fontes autorizadas. Não houve alteração de middleware,
autenticação, regras de autorização, envelope de sucesso, ordenação ou
paginação. Essa constatação de diff não demonstra autorização efetiva em SEI.

## Testes executados e limites

Comando reproduzível, a partir do checkout:

```bash
python3 tests/isolated/run-local.py
```

Ambiente observado: PHP CLI 8.4.24, Slim 4.15.2 e PSR7 1.8.0 do lockfile,
Linux com `unshare -Urn`, diretório temporário exclusivo, dados fictícios.
Dependências baixadas antes dos testes, sem scripts/plugins. Durante sintaxe
e testes, a rede fica desabilitada por namespace. O runner remove seu
diretório ao terminar; nenhum serviço SEI é iniciado.

| Verificação | Resultado |
| --- | --- |
| Sintaxe dos PHP copiados ao laboratório | Aprovada |
| Handler no Slim real, validação, extremos, arrays, expressões, combinações e preservação dos critérios anteriores | Aprovado com RN/middlewares/formatador de erro substitutos |
| Construtor Solr real, AND, demais critérios, condição de unidade, regra de unidade de protocolo, ordenação e offsets | Aprovado com dependências SEI substitutas |
| Consistência da operação Swagger JSON/YAML | Aprovada |
| Parsing dos 26 scripts Postman e sintaxe do runner Node | Aprovado; não é execução contra SEI |
| `git diff --check` | Aprovado |
| Testes com SEI/SIP/Infra e banco completos | Não executados; Solr real descartável aprovado com dados fictícios |
| CI remota e testes de SEI completo | Não executados; matriz PHP e checks estáticos locais aprovados no recorte descrito acima |

Depreciações de parâmetros implicitamente nullable do baseline no PHP 8.4 foram
excluídas do runner (`error_reporting=8191`); warnings/notices continuam virando
falha. Sem mbstring local, o teste de query usa conversão iconv apenas em
fixtures ASCII. Não é teste da normalização real nem da InfraDTO real.

Não foram provados por estes testes: resultados do índice, IDs inexistentes
retornando vazio real, ausência de repetição entre páginas no acervo, autenticação,
permissões efetivas, credenciais/revogação, sigilo, totais/snippets autorizados,
leitura posterior, falhas de Solr e desempenho. A coleção e o roteiro preparam
parte dessa validação, mas não substituem a execução autorizada.

## Impedimentos e pré-requisitos restantes

1. **Fontes autorizadas SEI/SIP/Infra.** A infraestrutura Docker pública não
   inclui o sistema. Não foram fornecidas fontes neste workspace. O workflow
   upstream consulta `pengovbr/sei` com credencial dos mantenedores; isso não
   concede autorização nem acesso a nós. Não foi tentado acesso a esse repositório
   restrito, nem usada cópia alternativa não autorizada.
2. **Ambiente de integração.** Definir quem disponibilizará e executará o
   laboratório/homologação. O IFBA é uma possibilidade, ainda sem decisão.
   Precisa ser descartável, com dados fictícios e sem conexão com SEI real.
3. **Ferramentas/runtime.** Docker daemon 28.5.2 disponível; `docker compose`
   e `docker-compose` ausentes. Newman e Composer global ausentes; Composer
   incluído no projeto foi suficiente no laboratório isolado. PHP local 8.4
   não substitui a matriz 8.2/8.5; mbstring e SimpleXML não estão carregados no
   PHP local. Validar todas as extensões exigidas pelo SEI alvo.
4. **Dados e versões.** Montar fixtures próprias, indexação e usuários com
   permissões distintas. Confirmar versões/patches alvo, inclusive do IFBA
   caso ele participe, sem assumir a instalação histórica descrita no plano.
5. **Aceite do contrato e distribuição.** Nomes dos parâmetros, faixa de IDs,
   semântica do índice e condições de redistribuição ainda precisam de revisão.
   Nenhuma mensagem será publicada ou enviada sem autorização.

`make tests-api`, `make restore`, `make install`, serviços Compose e dumps
upstream **não foram executados**. O Makefile pode restaurar base e instalar
módulo; não usar esses alvos em host compartilhado sem isolamento e inspeção.

## Sequência preservada

| Marco | Situação |
| --- | --- |
| Implementação e testes locais isolados | Candidato disponível para revisão |
| Testes de integração/homologação | Pendentes; ambiente e responsável a definir |
| Preparação para avaliação institucional | Roteiro técnico disponível; nenhuma entrega externa realizada |
| Ajustes após avaliação | Aguardam avaliação futura |
| PR ao PEN | Não preparado como publicação, não publicado; após avaliação e ajustes |

Nenhum SEI real do IFBA foi acessado ou alterado. Nenhuma aplicação consumidora
foi modificada. Pesquisa por assinante foi incluída posteriormente na primeira entrega e permanece pendente.
