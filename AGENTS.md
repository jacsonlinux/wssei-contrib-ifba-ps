# Instruções para agentes — contribuição WSSEI

## Objetivo e escopo vigente

Este workspace prepara uma contribuição ao projeto oficial
`https://github.com/pengovbr/mod-wssei`. O código está em `mod-wssei/`.
`README.md` apresenta o estado; `contribuicao-wssei-filtros-de-pesquisa.md`
registra o planejamento; `relatorio-implementacao-filtros-wssei.md` registra
implementação, evidências e impedimentos. Distinguir decisões atuais de
estados históricos desses documentos.

Por instrução do usuário, a primeira entrega deve incluir filtros opcionais de
**série/tipo documental, tipo processual e pessoa assinante**. Série e tipo
processual estão implementados como candidatos locais. Assinante foi incluído
posteriormente no escopo e agora tem implementação candidata por
`idContatoAssinante`, apoiada em referência pública de consulta. Não declarar
a entrega completa antes de confirmar a representação do índice, integração
e homologação. Outros filtros precisam de
definição própria; não ampliar automaticamente o escopo.

A contribuição responde a limitações de consulta do SEIA, mas **não autoriza
alterar aplicações consumidoras**. O backend SEIA pode ser consultado por leitura
local para contexto, respeitando suas instruções. Por autorização posterior do
usuário, suas skills e credenciais externas também podem ser usadas para
inspeção técnica autenticada de leitura necessária à contribuição. Não executar
o atendimento, coletores, sincronizações ou consultas em massa. Não alterar
aplicações consumidoras.

## Limites de autorização

- Não alterar o SEI real do IFBA. A autorização posterior do usuário permite
  inspeção técnica autenticada de leitura, pelas skills e mecanismos existentes
  do SEIA, necessária para esclarecer a pesquisa por assinante. Essa autorização
  substitui a proibição inicial de qualquer acesso, somente nesse recorte.
  O usuário autorizou posteriormente a abertura de leitura do processo informado
  e dos documentos necessários à investigação de múltiplas assinaturas.
  Ajustar o inspetor de forma restrita aos objetos confirmados e métodos de
  leitura quando necessário, sem tratar bloqueio do script como negativa do SEI.
  Não abrir outros processos/documentos sem necessidade e autorização de escopo;
  não executar carga, coleta ampla, instalação ou testes do módulo em produção.
  Não acessar diretamente banco, Solr ou caminhos de servidor usando credenciais
  da aplicação web/REST. Permissão de login não implica autorização a essas camadas.
- Credenciais devem ser carregadas pelo mecanismo externo do SEIA, nunca
  exibidas ao modelo, copiadas ao workspace, registradas em logs ou versionadas.
  Usar skills/documentação existentes antes de repetir observações remotas;
  guardar evidências institucionais somente no diretório privado apropriado.
- Executar testes somente em laboratório local descartável, com dados fictícios.
  Não usar credenciais, configurações, dumps ou conteúdo institucionais nesses testes.
  Inspeção autenticada de leitura é atividade separada, não validação do candidato
  contra produção.
- Não publicar issues, PRs, pushes remotos, releases ou mensagens, nem contatar
  terceiros, sem autorização explícita. Consultas públicas de leitura ao
  upstream são permitidas. Não criar fork remoto por iniciativa própria.
- Obter fontes SEI/SIP/Infra somente por canal autorizado. Infraestrutura pública
  Docker não inclui nem concede acesso às fontes restritas. Não incorporar ou
  redistribuir fontes privadas na contribuição.
- Ambiente e responsável pela homologação ainda não estão definidos. Uma
  eventual solicitação ao IFBA é possibilidade, não decisão ou autorização.

## Implementação e evidências

Ler instruções, documentação e código do checkout antes de editar. Conferir
branch/SHA base, compatibilidade e issues/PRs equivalentes por leitura pública.
Preservar alterações existentes. Manter o diff pequeno e revisar arquivos novos.

Preservar autenticação, identidade, unidade da sessão, permissões, envelope,
ordenação, totais e semântica de paginação. Reutilizar as regras existentes do
SEI/WSSEI. Não trocar offset por página nem contar ocorrências como processos
únicos. Filtro inválido não pode ser descartado silenciosamente e ampliar a busca.
Não converter falha técnica/acesso negado em sucesso vazio.

Para assinante, rastrear antes de implementar: produtor do índice, formato e
família do identificador, delimitação dos valores, múltiplas assinaturas,
usuários internos/externos, cancelamento/revogação e atualização. O campo
`id_assin` num schema não comprova essas propriedades. `IdAssinante` do catálogo
de cargos/funções não deve ser tratado como identidade da pessoa sem evidência.
Nome textual e usuário gerador não equivalem a pessoa assinante. Não inventar
contrato definitivo ou query sobre formato desconhecido; informar o impedimento
e avançar no trabalho independente possível. Não usar filtragem depois da
paginação como substituto de filtro no índice.

Atualizar Swagger JSON e YAML da mesma operação, documentação e testes quando
o contrato mudar. Separar capacidade pública documentada de comportamento
comprovado numa instalação. Usar exemplos sintéticos e não publicar dados do SEIA.

## Validação e entrega

Os testes isolados podem ser executados com:

```bash
cd mod-wssei
python3 tests/isolated/run-local.py
```

O runner instala dependências num diretório temporário e executa testes sem
rede com substitutos das dependências SEI. Aprovação não comprova integração,
indexação real, autenticação ou autorização efetiva. Registrar limitações e
checks não executados, inclusive matriz PHP e ferramentas upstream.

Antes de qualquer alvo Make/Compose, ler seus efeitos e conferir isolamento.
`make tests-api` depende de restauração e instalação; `restore` seleciona
containers por nome. Não executar esses alvos sem laboratório apropriado.

Sequência: implementar/testar; preparar para avaliação/homologação autorizada;
incorporar ajustes; preparar PR ao PEN. Publicação e instalação institucional
continuam exigindo autorização própria. Não prometer release, homologação ou
compatibilidade não testada. Ao finalizar, informar mudanças, validação e
impedimentos em português claro.

## Escopo da publicação privada

O usuário autorizou criar esta cópia privada no GitHub. Isso não autoriza
publicar o conteúdo, mudar a visibilidade para pública, abrir issue/PR ao PEN
ou realizar contatos. O checkout upstream permanece separado. Para preparar
uma contribuição ao PEN, selecionar somente o diff apropriado do módulo e
revisar a documentação destinada a publicação.
