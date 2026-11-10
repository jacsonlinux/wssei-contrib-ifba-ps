# Contribuição ao WSSEI — filtros de pesquisa

Repositório privado independente para revisão da contribuição ao
[WSSEI oficial do PEN](https://github.com/pengovbr/mod-wssei). O objetivo é
permitir pesquisas estruturadas na origem, preservando a identidade e as
permissões do usuário autenticado.

A motivação vem do SEIA: descobrir documentos por texto e confirmar assinaturas
individualmente aumenta as chamadas e não garante cobertura completa. Um índice
próprio também depende da coleta e atualização desses metadados. Esta
contribuição atua no WSSEI; não modifica o SEIA.

## Primeira entrega: escopo atualizado

| Recurso | Estado local |
| --- | --- |
| Série/tipo documental (`idSerie`) | Implementado como candidato, com testes isolados |
| Tipo processual (`idTipoProcedimento`) | Implementado como candidato, com testes isolados |
| Pessoa assinante (`idContatoAssinante`) | Candidato implementado; testes PHP/Lucene/Solr aprovados; representação alvo e integração pendentes |
| Integração com SEI e homologação | Pendentes; ambiente e responsável a definir |

A primeira entrega **ainda não está homologada**. Os três filtros possuem
implementação candidata. Para assinante, foi encontrada referência pública da
Anatel que liga `hdnIdAssinante` a `id_assin` e pesquisa o ID contido no campo.
O candidato exige token numérico completo para evitar confundir IDs semelhantes.
A representação dos tokens e a semântica Assinatura/Autenticação da versão SEI
alvo ainda precisam ser confirmadas. A referência não substitui o indexador.

## Código e documentação

- [Checkout WSSEI](mod-wssei/): branch `feat/filtros-pesquisa`, base
  `11c6bd4e65e3d3b56fe3e658891d754982d57e66` (`master`, WSSEI 3.0.5).
  A pasta contém uma cópia do código público com as alterações candidatas.
  O histórico original do módulo permanece no checkout de desenvolvimento;
  este repositório registra o snapshot para revisão privada.
- [Regras deste workspace](AGENTS.md).
- [Planejamento e contexto](contribuicao-wssei-filtros-de-pesquisa.md).
- [Relatório de implementação e impedimentos](relatorio-implementacao-filtros-wssei.md).
- [Contrato candidato, fixtures e integração](mod-wssei/docs/pesquisa-filtros.md).
- [Testes isolados](mod-wssei/tests/isolated/README.md).

## Documentos para avaliação institucional

- [Resumo para a coordenação](resumo-coordenacao-filtros-wssei.md).
- [Proposta para a administração do SEI/IFBA](proposta-homologacao-administracao-sei-ifba.md).

Preparados para revisão e eventual encaminhamento pelo responsável; não enviados.

## Testes disponíveis

```bash
cd mod-wssei
python3 tests/isolated/run-local.py
```

Requer Python 3, Git, PHP compatível com o lockfile e Linux com `unshare -Urn`.
Dependências são instaladas em diretório temporário descartável; os testes
rodam sem rede, com dados sintéticos e substitutos das dependências SEI.
Os três filtros passaram nos testes isolados. A opção `--lucene` também
executa as consultas produzidas pelo PHP em índice sintético Lucene, sem rede.
Também foram aprovados testes em PHP 8.2.35 e 8.5.12 e Solr 9.6.1 descartável
com o schema público do SEI. O [roteiro de testes](mod-wssei/tests/isolated/README.md)
traz os comandos. PHPCS e Rector passaram nos três arquivos PHP alterados.
Não são testes de permissões efetivas ou de indexação real.

A coleção Postman dedicada está preparada, mas não foi executada contra um
SEI completo. Não executar `make tests-api` como simples verificação: o alvo
restaura dados e instala o módulo. Seguir o roteiro de integração antes de
qualquer serviço ou teste completo.

## O que falta

Fontes SEI/SIP/Infra obtidas por canal autorizado, laboratório descartável com
banco e Solr sintéticos, Compose/Newman e validação nas versões alvo. A
infraestrutura Docker pública não fornece o núcleo restrito do SEI.

Definir como serão realizados os testes de integração e a homologação. Pedir
apoio ao IFBA é uma possibilidade ainda sem decisão. Seguir depois com ajustes
e preparação de PR ao PEN. Nenhuma instalação, publicação ou contato é
consequência automática deste trabalho.

O usuário autorizou posteriormente inspeção autenticada de leitura pelas skills
existentes do SEIA, com credenciais externas e evidências privadas. Uma inspeção
do formulário atual foi concluída sem submissão de pesquisa ou abertura de
processos/documentos. Isso não é teste do candidato em produção nem acesso às
fontes PHP/indexador. Ver o [relatório](relatorio-implementacao-filtros-wssei.md).

Não alterar o SEI real ou aplicações consumidoras. Testes do candidato continuam
somente em laboratório descartável sintético. Não publicar issues/PRs nem
contatar terceiros sem autorização explícita.

## Organização desta cópia privada

Esta cópia reúne os documentos e o snapshot do módulo em `mod-wssei/`.
Não é fork oficial, release do PEN ou versão homologada. O código de origem
é do projeto `pengovbr/mod-wssei`, na base identificada acima. Não contém
fontes SEI/SIP/Infra restritas, credenciais ou evidências institucionais privadas.
Dumps e alguns arquivos binários omitidos do checkout de desenvolvimento não
integram esta cópia. Usar os testes isolados documentados; não executar a suíte
completa sem preparar o laboratório SEI autorizado.
