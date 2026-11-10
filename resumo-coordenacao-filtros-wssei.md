# Melhoria na pesquisa do SEI — resumo para a coordenação

**Data: 10/11/2026**  
**Situação: código candidato implementado e testes locais aprovados. Homologação pendente.**

## O que estamos fazendo

Estamos preparando uma contribuição ao **WSSEI**, módulo que já disponibiliza
a API REST do SEI. A proposta acrescenta filtros à pesquisa existente para
facilitar consultas como “documentos deste tipo assinados por esta pessoa”.

Hoje, o SEIA precisa localizar documentos candidatos e consultar suas assinaturas
separadamente. Isso aumenta o número de chamadas e pode deixar documentos fora
da descoberta por texto. A melhoria pretende reduzir essa necessidade de
conferência individual, sem prometer ganho de desempenho ainda não medido.

## O que já temos

A proposta leva à API REST três critérios que já aparecem no formulário de
pesquisa do **SEI Web**:

| No formulário do SEI Web | Na contribuição ao WSSEI |
| --- | --- |
| **Tipo do Documento** | Filtrar pela série documental selecionada. |
| **Tipo do Processo** | Filtrar pela classificação processual selecionada. |
| **Assinatura / Autenticação** | Pesquisar pela pessoa selecionada, por seu ID de contato. |

O filtro recebe uma pessoa por vez. Esses critérios podem ser combinados na
consulta. A correspondência dos resultados com a pesquisa web ainda será
verificada na homologação, especialmente no campo Assinatura / Autenticação.

A proposta mantém a rota de pesquisa existente e o índice Solr já usado pelo SEI.
Não cria outra API ou índice paralelo. Não foram alteradas as aplicações consumidoras.
O código mantém os mecanismos existentes de autenticação, permissões e paginação;
a preservação efetiva dessas regras ainda será verificada na homologação.

## Como a pesquisa funciona hoje e o que muda

A comparação abaixo considera o contrato REST analisado nesta contribuição.
A versão e eventuais alterações instaladas no IFBA devem ser conferidas antes
da homologação.

| Aspecto | Hoje | Com a contribuição, após homologação |
| --- | --- | --- |
| Rota usada | `GET /api/v2/processo/pesquisar` | A mesma rota, com novos parâmetros opcionais. |
| Critérios existentes | Palavras-chave, descrição, assunto, unidade geradora e período, entre outros. | Esses critérios continuam disponíveis e podem ser combinados aos novos filtros. |
| Tipo do Documento | Não recebe esse critério como filtro específico nessa rota; o SEIA filtra os documentos candidatos localmente. | Recebe `idSerie` e aplica o critério na pesquisa. |
| Tipo do Processo | Não recebe esse critério como filtro específico nessa rota. | Recebe `idTipoProcedimento` e aplica o critério na pesquisa. |
| Pessoa assinante | O SEIA procura candidatos pelo nome no texto e consulta assinaturas em outra rota para confirmar. Isso não garante encontrar todos. | Recebe `idContatoAssinante` para pesquisar pela pessoa identificada, conforme a cobertura do índice confirmada na homologação. |
| Fonte da pesquisa | Índice Solr existente do SEI. | O mesmo índice Solr. |

**Exemplo atual:** pesquisa textual pelo termo “capacitacao”.

```http
GET /api/v2/processo/pesquisar?palavrasChave=capacitacao&limit=10&start=0
```

**Exemplo proposto, com IDs fictícios:** o mesmo texto, combinado com série,
tipo processual e pessoa assinante.

```http
GET /api/v2/processo/pesquisar?palavrasChave=capacitacao&idSerie=321&idTipoProcedimento=654&idContatoAssinante=7&limit=10&start=0
```

O filtro recebe uma pessoa por vez. Os novos parâmetros são opcionais e podem
ser usados separadamente. A consulta solicita os primeiros dez resultados.
Sem os novos filtros, o candidato preserva o comportamento anterior, que
também será conferido na homologação.

O WSSEI continuaria consultando o **Solr existente do SEI** e aplicando os
controles de acesso da identidade autenticada. Os demais endpoints permaneceriam
disponíveis, como `/documento/listar/{idProcesso}` para listar documentos e
`/documento/listar/assinaturas/{idDocumento}` para consultar assinaturas de um
documento encontrado. Esses caminhos são relativos ao mesmo prefixo `/api/v2`.
A equivalência do filtro de assinante com a pesquisa web depende da homologação.

## O que foi testado

**Os testes locais passaram:**

- PHP **8.2.35** e **8.5.12**.
- Oito consultas geradas pelo PHP contra um **Solr 9.6.1 isolado**, com a
  definição pública dos campos do índice do SEI e dados fictícios.
- Filtros usados em conjunto, consulta por assinante, distinção de IDs
  semelhantes, totais e paginação.
- PHPCS e Rector, ferramentas de revisão automática, nos três arquivos PHP alterados.

Essa etapa ocorreu em laboratório descartável, sem acessar o SEI real.
Houve anteriormente inspeções autorizadas de leitura para entender a pesquisa,
mas elas não foram testes da versão modificada. Nada foi instalado na produção.

## O que falta e como avançar no IFBA

Ainda precisamos confirmar como a versão SEI alvo grava os contatos no índice
e validar as permissões reais. O filtro de assinante permanece candidato até
essa confirmação; os testes locais não substituem a homologação.

A proposta é solicitar à administração do SEI que avalie o código e a possibilidade
de um **ambiente separado de homologação**, com dados fictícios. A equipe responsável
confirmaria as versões, instalaria um pacote identificado do candidato nesse
ambiente e acompanharia os testes. Se não houver esse ambiente, será preciso
definir uma alternativa autorizada antes de instalar ou testar a contribuição.

A sequência prevista é: **avaliação e homologação autorizadas → ajustes →
preparação de contribuição ao PEN por pull request**. Uma futura instalação em
produção dependerá de decisão própria do IFBA. O candidato ainda não é uma
versão oficial aprovada pelo PEN; não houve publicação ou contato externo automático.

**Decisão solicitada à coordenação:** avaliar o encaminhamento à administração
do SEI e definir quem poderá conduzir a revisão e a homologação. Não há instalação
em produção solicitada neste momento.
