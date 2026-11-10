# Contribuição ao WSSEI: filtros estruturados de pesquisa

Data: 10/11/2026. Estado: **planejamento entregue; implementação pendente**.
Destinatários: desenvolvedores responsáveis pela contribuição ao WSSEI.

**Atualização de escopo pelo usuário:** a primeira entrega passa a incluir
**pessoa assinante**, além de série documental e tipo processual. Assinante
está em investigação e ainda não foi implementado. As exclusões de assinante
nas seções originais abaixo são históricas e foram substituídas por esta decisão.
Não há autorização para alterar o SEIA ou acessar o SEI real do IFBA.
Ver [estado vigente](README.md) e [regras de trabalho](AGENTS.md).

**Atualização de execução (10/11/2026):** candidato implementado em checkout
local, com testes isolados aprovados. Integração SEI e homologação pendentes;
ambiente e responsável ainda não definidos. O planejamento e os estados abaixo
registram a situação original. Ver [relatório de execução local](relatorio-implementacao-filtros-wssei.md).

Este documento foi solicitado para orientar o início do trabalho. A entrega
atual é documental: nenhum código foi alterado, fork criado, issue aberta ou
pull request enviado. Instalação no IFBA e publicação em produção dependem de
autorização institucional própria.

## 1. Objetivo e primeira tarefa

Desenvolver uma melhoria no módulo REST WSSEI e submetê-la ao repositório oficial
do PEN por **pull request (PR)**. A melhoria deve permitir filtrar a pesquisa por
**tipo documental** e **tipo processual**, mantendo a identidade e as permissões
do usuário autenticado. A entrega dos desenvolvedores abrange código, testes,
documentação, entrega inicial ao IFBA para avaliação e posterior submissão
da contribuição ao PEN, conforme autorização.
Instalação institucional é etapa separada, conduzida pela administração do SEI.

**Primeira tarefa:** conferir o código atual, reproduzir a lacuna em laboratório
e preparar uma proposta de contrato para alinhamento com o mantenedor. Não
começar por alterar uma instalação real ou desenvolver aplicações clientes.

Fundamento: [estudo técnico do ecossistema e alternativas](../research/ecossistema-pen-e-alternativas-arquiteturais-seia.md).
O estudo é fundamento opcional para consulta; a execução desta contribuição
não exige conhecer ou alterar a arquitetura das aplicações consumidoras.

## 2. Contexto para quem está chegando

O SEI armazena processos e documentos. O WSSEI é um módulo PHP instalado no SEI
que expõe operações por API REST. Aplicações clientes utilizam essa API com a
identidade do usuário autenticado. O projeto oficial está na organização
`pengovbr`, no ecossistema PEN/MGI.

O trabalho é ampliar os critérios estruturados de pesquisa da API existente.
Pesquisar o nome de um tipo nas palavras-chave não equivale a filtrar pelo ID
cadastrado. Não será criado um novo módulo de IA ou uma aplicação cliente.

| Termo | Significado |
| --- | --- |
| Série documental | Tipo de documento; distinto de tipo processual |
| DTO | Objeto que transporta critérios e dados nas rotinas PHP |
| RN | Rotina de negócio utilizada pelo módulo/SEI |
| Solr | Mecanismo de pesquisa utilizado no caminho upstream examinado |
| Fork | Cópia do repositório onde a contribuição será desenvolvida |
| Pull request (PR) | Solicitação de revisão e incorporação ao projeto oficial |
| Upstream | Repositório oficial, distinto do fork |
| Homologação | Validação controlada antes da adoção institucional |

`Aplicação cliente → REST WSSEI → sessão/regras SEI → pesquisa nativa`

### Motivação prática: limitações observadas em uma aplicação cliente

O SEIA é uma aplicação cliente que transforma perguntas em consultas de leitura
ao WSSEI. Seus casos de uso ajudam a explicar a contribuição, mas não exigem
acesso ao código do SEIA nem alterações nele.

| Pergunta de exemplo | Limitação observada no contrato examinado | Resultado esperado da melhoria |
| --- | --- | --- |
| “Liste as portarias sobre capacitação” | A pesquisa geral não recebe série documental como filtro estruturado; a palavra “portaria” pode aparecer em outros tipos de documento | Combinar texto e ID da série, retornando ocorrências documentais do tipo correto |
| “Liste processos do tipo capacitação” | Texto temático não garante correspondência com o tipo processual cadastrado; o handler não recebe esse filtro | Restringir pelo ID do tipo processual, independentemente de menções incidentais ao tema |
| “Quais portarias foram assinadas por esta pessoa?” | Descobrir candidatos por nome e depois consultar suas assinaturas não oferece busca global completa por assinante | Necessidade posterior: pesquisa estruturada por identidade do assinante e série, após investigação própria |

Os exemplos são sintéticos e os nomes dos tipos são ilustrativos. A primeira
entrega cobre somente as duas primeiras linhas. Não prometer que ela resolve
busca por assinante, “todos os documentos” ou “último documento assinado”.

Ter um catálogo de tipos não significa que a pesquisa aceite esses tipos como
filtros. Da mesma forma, consultar assinaturas de um documento conhecido não
equivale a descobrir todos os documentos assinados por uma pessoa. O objetivo é
expor critérios de negócio na pesquisa nativa, preservando autorização e
semântica, em vez de aproximá-los por palavras-chave.

Para aprofundamento opcional, consultar a
[comparação entre pesquisa web e REST](../reference/sei-web-pesquisa-campos-e-capacidades.md),
especialmente a matriz de diferenças e limites. A referência descreve também
campos observados na interface e lacunas ainda não comprovadas no servidor:
campo visível na tela não é garantia de filtro REST disponível. O documento está
no repositório privado do SEIA; se não houver acesso, esta seção já fornece o
contexto necessário para iniciar a contribuição. Não publicar fontes privadas
ou dados institucionais na issue/PR; usar exemplos sintéticos e evidências do
código público do WSSEI.

## 3. Repositórios, versões e leituras de entrada

- Código oficial: <https://github.com/pengovbr/mod-wssei>.
- Discussões existentes: <https://github.com/pengovbr/mod-wssei/issues>.
- Contribuições abertas: <https://github.com/pengovbr/mod-wssei/pulls>.
- Versões publicadas: <https://github.com/pengovbr/mod-wssei/releases>.

Baseline upstream examinado em 10/11/2026:
`9ac849b0533df590753f77aa95c0d6183e0c74ee`, branch `master`.
Confirmar novamente a branch e o commit antes de iniciar; não assumir que este
snapshot continuará sendo o mais recente.

A referência local registra SEI 5.0.4 / WSSEI 3.0.2 no IFBA em verificações
anteriores. **Não é confirmação da versão atualmente instalada.** Solicitar
versão, patches, ambiente de homologação e responsável pela instalação ao IFBA.
Não presumir que o código local é idêntico ao master público.

A leitura obrigatória de entrada é a documentação e as instruções do próprio
WSSEI na revisão escolhida. As fontes técnicas estão vinculadas na seção 7.
Não é necessário acesso ao repositório de uma aplicação consumidora.

## 4. O que está confirmado e o que ainda precisa ser decidido

No snapshot examinado:

- O handler de `GET /processo/pesquisar` recebe critérios como palavras-chave,
  assunto, unidade geradora, datas e paginação, mas não encaminha os dois filtros
  propostos.
- `MdWsSeiPesquisaProtocoloSolrDTO` possui `IdSerie` e `IdTipoProcedimento`.
- `MdWsSeiProcedimentoRN::montaConsultaSolr` constrói condições para `id_serie`
  e `id_tipo_proc` quando esses atributos estão presentes.
- A pesquisa envolve contexto da sessão e condições de acesso. Preservar essas
  condições é parte da mudança, não uma etapa opcional.

Isso indica um ponto de reaproveitamento, mas não comprova que basta acrescentar
duas linhas ao handler. Precisam ser conferidos validação, combinações,
semântica das ocorrências, testes e compatibilidade na versão alvo.

| Decisão pendente | Responsável por esclarecer | Consequência |
| --- | --- | --- |
| Aceitação do desenho e nomes públicos dos parâmetros | Mantenedor WSSEI/PEN | Evita implementar contrato que o upstream não aceite |
| Versões oficialmente suportadas pela contribuição | Mantenedor e responsáveis técnicos | Define matriz de testes |
| Versão/patches do IFBA e ambiente de homologação | Administração SEI do IFBA | Define possibilidade de instalação local |
| Licença e condições para redistribuir fontes/artefatos | Responsáveis pelo projeto e mantenedor | Conferir antes de incorporar ou distribuir código |
| Pessoas autorizadas a publicar issue/PR em nome da contribuição | Responsável pela contribuição | Este documento não publica mensagens ou cria contribuições automaticamente |

Enquanto esses alinhamentos ocorrem, podem ser feitos leitura de código,
desenho do contrato e testes locais em ambiente autorizado. Não tratar silêncio
do mantenedor como aprovação nem prometer data de release.

## 5. Escopo da primeira contribuição

Entregar dois filtros opcionais na pesquisa existente, com documentação e testes.
Devem funcionar isoladamente e em conjunto com os critérios já suportados,
conforme semântica validada no laboratório.

Fora deste primeiro PR:

- Pesquisa estruturada por assinante ou ordenação por data de assinatura.
- Interface de chat dentro do SEI e integração de LLM no módulo PHP.
- Acesso direto de aplicações clientes a SQL/Solr livres, banco ou índice.
- Criação de outro canal operacional de integração com o SEI.
- Alterações em aplicações clientes, coleta ampla, migração de banco ou nova infraestrutura.
- Rotas de escrita e concessão de permissões adicionais.

Busca por assinante exige outro recorte: rastrear o produtor do índice,
identificadores, múltiplas assinaturas, datas e regras do SEI. Não incluir esse
recurso como simples nome textual nem prometer paridade integral com a tela.

### Funcionalidades de pesquisa e sequência das contribuições

O objetivo mais amplo é ampliar a pesquisa da API por meio de filtros
estruturados no servidor, úteis a diferentes aplicações clientes. Os dois filtros deste primeiro PR são a entrega
inicial, não a totalidade das necessidades. Priorizar contribuições pequenas e
revisáveis; não acrescentar todos os critérios ao mesmo PR.

| Prioridade | Critério | Pergunta que deve apoiar | Evidência e trabalho necessário |
| --- | --- | --- | --- |
| Primeira entrega | Tipo documental / série | “Liste as portarias” | DTO e construtor WSSEI já possuem suporte interno; expor pelo contrato e validar a ocorrência documental |
| Primeira entrega | Tipo processual | “Liste processos do tipo capacitação” | DTO e construtor já possuem suporte interno; resolver o tipo cadastrado, sem confundir classificação com tema textual |
| Próxima prioridade | Pessoa assinante | “Quais portarias foram assinadas por esta pessoa?” | Falta filtro estruturado no handler/DTO examinados; rastrear indexação, identidade interna/externa e autorização antes de definir contrato |
| Etapa posterior | Contato com papel de interessado, remetente ou destinatário | “Quais processos têm esta pessoa como interessada?” | Catálogo de contatos e interessados de processo conhecido não oferecem busca global equivalente; distinguir os papéis e confirmar suporte interno |
| Etapa posterior | Usuário gerador | “Quais documentos foram gerados por este usuário?” | Definir identificador e semântica; gerador não é automaticamente assinante, interessado ou responsável atual |
| Etapa posterior | Período com natureza da data explícita | “Quais documentos foram incluídos no SEI neste período?” | Comparar inclusão e geração e sua aplicação a processos/documentos; período existente não comprova equivalência com a tela |

Assunto e unidade geradora já são recebidos pelo handler WSSEI examinado.
O trabalho é preservar sua combinação com os novos filtros, sem confundir
unidade geradora com unidade da sessão ou impor restrição automática por campus.
As lacunas acima referem-se à pesquisa global estruturada; localizar ou confirmar
um candidato por outras operações não demonstra cobertura global equivalente.

Requisitos transversais a discutir para as próximas contribuições:

- Seleção explícita do universo: documentos ou processos. Avaliar separadamente
  a semântica de processos que contenham documentos correspondentes; não tratar
  uma lista processual como lista documental.
- Combinação dos critérios sem retirar filtros silenciosamente. Nome de pessoa
  ou tipo nas palavras-chave não substitui filtro por identidade/tipo cadastrado.
- Paginação, totais e ordenação com significado documentado. Contar ocorrências
  não equivale a contar processos, documentos únicos ou assinaturas.
- Identidade autenticada e permissões atuais aplicadas também a metadados,
  snippets e totais; a melhoria de descoberta não autoriza leitura de conteúdo.

**Data de assinatura e “último documento assinado” exigem investigação própria.**
A tela de pesquisa observada oferece inclusão e data do processo/documento,
não data de assinatura. A ordenação WSSEI examinada usa geração e ID, não última
assinatura. Antes de propor esse recurso, rastrear fonte dos eventos, múltiplas
assinaturas, empates, precisão e cobertura; não reutilizar geração como assinatura.

Para a prioridade de assinante, preparar primeiro um diagnóstico técnico:
identificar como o SEI indexa e consulta a pessoa, verificar o significado e
preenchimento de `id_assin` na versão alvo, mapear IDs sem equiparar famílias
cadastrais e definir como distinguir homônimos. Confirmar assinatura de documento
conhecido continua sendo operação diferente da busca invertida no acervo.
Só depois definir parâmetros, testes e eventual PR próprio.

Fontes: [comparação web × REST](../reference/sei-web-pesquisa-campos-e-capacidades.md),
[base WSSEI](../reference/rest-wssei-base-de-conhecimento.md) e
[estudo arquitetural](../research/ecossistema-pen-e-alternativas-arquiteturais-seia.md).
Essas fontes possuem observações datadas; nenhuma garante disponibilidade na
instalação atual do IFBA. O primeiro PR permanece restrito aos dois tipos.
As etapas posteriores são necessidades priorizadas, ainda sem contrato fechado
ou implementação autorizada por este documento.

## 6. Contrato proposto para discussão

**Proposta, ainda não disponível na API:** ampliar `GET /processo/pesquisar` com:

| Parâmetro proposto | Atributo interno | Semântica proposta |
| --- | --- | --- |
| `idSerie` | `IdSerie` | Identificador do tipo documental/série |
| `idTipoProcedimento` | `IdTipoProcedimento` | Identificador do tipo processual |

Os nomes devem ser confirmados com o mantenedor. A requisição preserva token e
contexto de unidade conforme o mecanismo existente; o modelo de IA não fornece
credenciais nem escolhe uma identidade arbitrária.

Exemplo sintético de caminho relativo, **somente após implementar o contrato**:

```text
GET /processo/pesquisar?palavrasChave=capacitacao&idSerie=321&idTipoProcedimento=654&limit=10&start=0
```

`321` e `654` são IDs fictícios. Usar referências cadastrais reais apenas no
ambiente autorizado, sem publicá-las como exemplos da instalação IFBA.

Regras de implementação propostas:

- Ausência dos novos parâmetros conserva o comportamento anterior.
- Quando informados, aceitar apenas identificadores positivos no formato e
  intervalo compatíveis com DTO, PHP e catálogo da versão alvo. Não converter
  arrays, negativos, expressões ou números excessivos silenciosamente.
- Proposta: vazio explícito e formato inválido retornam erro de entrada, seguindo
  o padrão do módulo e o contrato acordado; não ignorar filtro inválido e executar
  busca mais ampla.
- Valor válido sem correspondência resulta em resposta vazia segundo o envelope
  existente. Falha técnica ou acesso negado não deve virar resultado vazio.
- Combinar os filtros por interseção lógica, preservando os demais critérios.
  Se série e tipo forem incompatíveis, não remover um deles silenciosamente.
- Preservar envelope, campos, paginação e ordenação atuais. Não transformar
  `total` em quantidade de processos únicos ou assinaturas.
- Determinar se apenas os novos filtros, sem texto, são suportados pela pesquisa
  do módulo; documentar e testar. Isso não altera automaticamente a exigência de
  critérios de aplicações consumidoras.

A pesquisa pode retornar ocorrências de documentos e processos. O filtro de série
precisa demonstrar que a ocorrência documental pertence à série selecionada; não
basta o processo conter algum documento dessa série. O filtro processual deve
ser conferido no processo correspondente. Definir essa semântica antes do aceite.

## 7. Mapa do código a examinar e alterar

Links fixados no snapshot estudado; conferir os equivalentes no checkout atual.

| Arquivo upstream | Trabalho esperado |
| --- | --- |
| [Handler V2](https://github.com/pengovbr/mod-wssei/blob/9ac849b0533df590753f77aa95c0d6183e0c74ee/src/versao/v2/MdWsSeiServicosV2.php) | Localizar `/processo` → `/pesquisar`; validar entradas e preencher atributos do DTO |
| [DTO de pesquisa](https://github.com/pengovbr/mod-wssei/blob/9ac849b0533df590753f77aa95c0d6183e0c74ee/src/dto/MdWsSeiPesquisaProtocoloSolrDTO.php) | Confirmar atributos existentes; evitar duplicação |
| [RN processual](https://github.com/pengovbr/mod-wssei/blob/9ac849b0533df590753f77aa95c0d6183e0c74ee/src/rn/MdWsSeiProcedimentoRN.php) | Rastrear construtor/execução/retorno Solr e preservar filtros de acesso |
| [Swagger](https://github.com/pengovbr/mod-wssei/blob/9ac849b0533df590753f77aa95c0d6183e0c74ee/swagger/wssei.yaml) | Documentar os parâmetros somente na operação correta, com tipos e exemplos sintéticos |
| [Testes](https://github.com/pengovbr/mod-wssei/blob/9ac849b0533df590753f77aa95c0d6183e0c74ee/tests/README.md) | Seguir a estrutura ativa do projeto; documentação declara PHPUnit descontinuado |
| [Makefile](https://github.com/pengovbr/mod-wssei/blob/9ac849b0533df590753f77aa95c0d6183e0c74ee/Makefile) | Inspecionar dependências e efeitos de cada alvo antes da execução |

Usar o middleware, normalização e tratamento de erro existentes. Se a validação
precisar de novo helper, justificar a necessidade e manter o escopo restrito.
Não reimplementar as regras SEI em consultas novas sem necessidade comprovada.

## 8. Preparação do ambiente de desenvolvimento

Pré-requisitos: Git, acesso ao fork, Docker/Compose conforme upstream, fontes do
SEI obtidas legitimamente e base sintética descartável. O Composer do snapshot
usa plataforma PHP 8.2; confirmar a matriz atual antes de escolher a imagem.
Não é um aplicativo PHP independente: precisa do ambiente SEI/SIP correspondente.

Sequência sugerida, com URLs de exemplo a substituir antes de executar:

```bash
# Criar o fork no GitHub com a conta/organização autorizada.
git clone https://github.com/SUA_ORGANIZACAO/mod-wssei.git
cd mod-wssei
git remote add upstream https://github.com/pengovbr/mod-wssei.git
git fetch upstream
# Confirmar o nome da branch oficial antes deste comando.
git switch -c feat/filtros-pesquisa upstream/master
git rev-parse HEAD
```

Registrar o SHA base na descrição do trabalho. Ler `README.md`, `Makefile`,
`docker-compose.yml`, `envs/`, instalação e configuração da revisão escolhida.
Configurar caminhos e segredos locais conforme upstream, sem copiar a configuração
de aplicações clientes ou credenciais IFBA para o fork. Conferir que arquivos sensíveis estão
ignorados antes de instalar dependências ou subir serviços.

**Atenção aos testes upstream:** no snapshot, `make tests-api` depende de
`tests-up`, que depende de `up restore install`. Portanto, pode restaurar base,
instalar módulo e preparar dados, além de executar operações de escrita da
coleção. Usar somente laboratório descartável isolado. Alterar apenas a URL do
Newman não torna a coleção segura para uma instalação institucional.

Não executar comandos de instalação, restauração, cadastro ou suíte completa
contra produção ou contra o SEI institucional. Antes de homologação,
preparar um conjunto específico de consultas de leitura autorizado pelo IFBA.
Fonte: Makefile e documentação de testes vinculados acima.

## 9. Etapas e entregáveis

| Etapa | Ação | Entrega e condição de avanço |
| --- | --- | --- |
| A — Conferência | Atualizar fontes e revisar issues/PRs para evitar duplicação | SHA, versão alvo, lacuna reproduzida e contrato proposto |
| B — Alinhamento | Encaminhar proposta ao mantenedor com responsável autorizado | Registro das orientações, sem pressupor aceitação |
| C — Implementação local | Validar parâmetros, encaminhar DTO e ajustar documentação | Commits pequenos no fork, sem mudança de acesso ou resposta anterior |
| D — Validação local | Executar matriz abaixo com dados sintéticos | Resultados reproduzíveis e revisão de segurança/compatibilidade |
| E — Entrega inicial ao IFBA | Apresentar código, artefato identificado, testes e instruções à administração do SEI | Avaliação e, mediante autorização institucional, homologação; versão candidata, ainda não oficial |
| F — Ajustes após avaliação | Corrigir achados, repetir testes afetados e consolidar resultados | Registrar parecer e limitações; mudanças locais continuam sujeitas à revisão upstream |
| G — Submissão ao PEN | Publicar PR para a branch indicada pelo mantenedor, após avaliação IFBA | Código, testes, documentação e evidências sintéticas/sanitizadas; não publicar dados institucionais |
| H — Revisão upstream | Responder revisão e corrigir o necessário | PR aceito ou pendências explicitadas; alterações posteriores relevantes exigem nova validação |
| I — Adoção institucional | Administração IFBA decide instalação e atualização; solicitar apoio do PEN quando necessário | Produção exige aprovação própria; envio ao IFBA ou merge no PEN não autoriza implantação |

**Sequência definida: laboratório → avaliação IFBA → ajustes → PR ao PEN.**
A avaliação do IFBA pode começar pela revisão de código e evidências; instalação
em homologação exige autorização da administração do SEI. Não é necessário
aguardar aceite do PEN para apresentar a versão candidata ao IFBA. Se não houver
ambiente ou autorização para homologar, registrar a limitação e alinhar com o
responsável pela contribuição antes de avançar à submissão pública. Alinhamento
prévio de contrato com o mantenedor pode ocorrer antes, sem equivaler ao envio
do PR. Não manter um fork produtivo por decisão implícita.

### Adoção da melhoria pelo IFBA

A proposta pode ser apresentada à administração do SEI do IFBA como melhoria do
módulo que a instituição já utiliza, com benefício para diferentes clientes
da API. O uso existente não autoriza alterar sua instalação; confirmar versão,
compatibilidade e responsáveis antes de propor o pacote de atualização.

| Caminho | Encaminhamento ao IFBA | Condição |
| --- | --- | --- |
| Versão oficial do PEN com a contribuição incorporada | Solicitar avaliação, homologação e atualização para essa versão | Confirmar que a release inclui os filtros e é compatível com o SEI instalado |
| Versão modificada ainda em revisão no PEN | Solicitar autorização para avaliar o artefato candidato em homologação | Identificar fork, commit, alterações e resultados dos testes; não apresentar como release oficial |

Atualizar para a versão oficial mais recente, por si só, não garante os filtros:
eles precisam estar incluídos no pacote instalado. Aceite do PR também não
significa que já exista uma release com a contribuição.

Uma eventual adoção da versão modificada em produção exige aprovação específica
do IFBA, responsável pela manutenção das diferenças, testes de compatibilidade,
procedimento de reversão e definição de como acompanhar as atualizações oficiais.
A preferência é incorporar a melhoria ao upstream e reduzir a manutenção local;
essa preferência não substitui a decisão institucional.

Texto sugerido para encaminhamento, sem envio automático:

> Pretende-se apresentar primeiro ao IFBA uma melhoria de filtros de pesquisa
> no WSSEI, com código, testes e instruções, para avaliação e homologação
> autorizada. Após essa avaliação e os ajustes necessários, a contribuição será
> submetida ao PEN por pull request. A adoção em produção será decidida pela
> administração do SEI no IFBA, separadamente da revisão do PEN.

## 10. Matriz mínima de validação

Preparar uma base sintética com dois tipos processuais, duas séries documentais,
processos com documentos de séries diferentes e usuários com acessos diferentes.
Registrar os IDs esperados em fixtures de teste, nunca dados institucionais.

| Cenário | Resultado exigido |
| --- | --- |
| Consulta anterior sem filtros novos | Mesma semântica, envelope, ordenação e paginação do baseline |
| Somente série | Ocorrências documentais pertencem à série selecionada |
| Somente tipo processual | Resultados pertencem ao tipo processual selecionado |
| Série + tipo | Apenas a interseção, sem ampliar silenciosamente a consulta |
| Com texto, assunto, unidade geradora e período | Cada combinação respeita todos os critérios informados |
| Apenas novos filtros, sem texto | Comportamento decidido no contrato e documentado |
| ID inexistente | Vazio conforme contrato; não pesquisa sem filtro |
| Vazio, zero, negativo, letras, decimal, array, excesso numérico e expressão | Rejeição determinística; nenhuma expressão chega à query como filtro livre |
| Duas ou mais páginas | Mesmos filtros em todas; sem omissão/repetição em base estável; sem trocar offset por página |
| Usuário sem sessão ou token inválido | Bloqueio pelo mecanismo existente; não iniciar pesquisa anônima |
| Usuários/unidades com permissões diferentes | Nenhuma ampliação indevida de resultados, totais ou snippets |
| Restrito, sigiloso, credencial e revogação quando aplicáveis | Comportamento coerente com a política SEI, inclusive após mudança de acesso |
| Solr indisponível, timeout ou erro interno | Falha segura; não sucesso vazio ou total inventado |
| Documento localizado e leitura posterior | Pesquisa não concede acesso ao conteúdo; autorização documental continua própria |

A RN existente considera unidade de protocolo; incluir esse cenário quando
aplicável. Comparar com a tela apenas após confirmar igualdade de critérios,
identidade, unidade e semântica dos resultados. Diferenças devem ser explicadas,
não resolvidas retirando guardas de autorização.

Em laboratório estável, comparar resultados do baseline com a versão alterada.
Registrar versão SEI/WSSEI, banco e Solr, configuração relevante sem segredos,
SHA, comando utilizado, resultado esperado/observado e limitações. Medir tempo e
volume de chamadas no mesmo cenário; não prometer ganho percentual sem medição.

## 11. Como preparar a proposta e o pull request

Texto-base para issue/discussão, a ser revisado e publicado pelo responsável:

> Proposta: adicionar filtros opcionais de série documental e tipo processual
> à pesquisa REST existente. O objetivo é permitir que clientes consultem esses
> critérios de forma estruturada, preservando autenticação, autorização,
> envelope e paginação. No snapshot 9ac849b, o DTO e a RN já possuem suporte
> interno, mas o handler não encaminha esses campos. Propõe-se validar as
> entradas, aproveitar a implementação existente e incluir testes e Swagger.
> Qual contrato, branch e matriz de versões são recomendados para a contribuição?

Título sugerido do PR: `feat: adiciona filtros de série e tipo processual à pesquisa`.
A descrição precisa conter:

- Problema e comportamento antes/depois, com requisições sintéticas.
- Nomes/semântica dos parâmetros acordados e comportamento sem os filtros.
- Arquivos alterados e justificativa para qualquer mudança fora do handler.
- Testes executados, versões, resultados e verificações de permissão.
- Lacunas de homologação, desempenho e compatibilidade ainda existentes.
- Relação com issue/discussão, quando houver.

Não anexar tokens, credenciais, payloads reais, nomes de usuários, conteúdo de
processos, dumps ou capturas institucionais. Casos de segurança devem seguir o
canal de divulgação responsável do projeto, sem publicar exploração sensível.
Revisar o diff e arquivos gerados antes do push. Não incluir `vendor`, `.env`,
relatórios com dados, mudanças de lockfile ou formatação ampla sem necessidade.

O merge depende do mantenedor. A instalação depende da administração do SEI no
IFBA, com apoio do PEN quando aplicável. O PEN pode revisar e publicar o pacote,
mas não se presume que execute a instalação no órgão. Confirmar formalmente quem
executará cada ação; publicação do PR não produz instalação automática.

## 12. Riscos e reversão

| Risco | Controle proposto |
| --- | --- |
| Filtro ignorado gera resposta aparentemente correta | Validação estrita, teste de combinação e confirmação de capacidade |
| Acesso indevido por consulta nova | Reutilizar sessão/regras e verificar resultados, totais e snippets |
| Mudança quebra outros clientes | Novos parâmetros opcionais e regressão sem filtros |
| Mantenedor prefere outro contrato | Alinhamento antes de estabilizar implementação e documentação |
| Instalação IFBA diverge do upstream | Versões/patches e homologação explícitos |
| Suíte upstream restaura dados | Laboratório descartável; não apontar testes gerais ao IFBA |
| Manutenção de fork permanente | Preferir upstream; adoção local excepcional exige decisão específica |

Reversão em laboratório: restaurar snapshot descartável e checkout do baseline.
Para instalação institucional futura, preparar backup e pacote anterior conforme
procedimento IFBA antes de implantar; não pressupor reversão apenas com Git.
A mudança proposta não prevê migração de dados: se ela se tornar necessária,
reavaliar escopo, riscos e rollback antes de avançar. A adaptação de aplicações
consumidoras e sua reversão pertencem a trabalhos separados.

## 13. Critérios de conclusão e situação atual

A contribuição só estará concluída quando houver:

- Contrato definido, implementação revisada, documentação e testes aprovados.
- PR revisado pelo upstream, com aceite ou decisão final registrada.
- Commits e push no fork, com evidências de validação e pendências explícitas.
- Entrega inicial ao IFBA com instruções, artefato identificado e resultado da
  avaliação/homologação ou limitação formalmente registrada.

O desenvolvimento termina no escopo da contribuição; revisão upstream pode
continuar pendente. Instalação e adaptação dos clientes têm aceite separado.
Não confundir entrega do PR com adoção institucional. Cada marco deve ter
estado e evidência próprios neste documento, sem declarar etapas futuras cumpridas.

| Marco | Estado nesta entrega |
| --- | --- |
| Guia e contexto para desenvolvedores | Entregues; referências e links locais conferidos |
| Alinhamento com mantenedor | Pendente; nenhum contato realizado por este documento |
| Fork, implementação e testes executáveis | Pendentes |
| Entrega inicial e avaliação IFBA | Pendentes; antecedem o PR ao PEN |
| PR oficial e revisão | Pendentes; após avaliação IFBA e ajustes |
| Homologação e instalação IFBA, se aplicáveis | Pendentes; responsabilidade institucional, separada do desenvolvimento |

Validação documental: conferir caminhos de código, fontes fixadas, índice de planos,
links locais e `git diff --check`. Não exige reinício ou deploy de runtime.
