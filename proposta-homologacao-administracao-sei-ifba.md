# Proposta de avaliação e homologação — filtros de pesquisa no WSSEI

**À administração do SEI/IFBA**  
**Data: 10/11/2026**  
**Situação: versão candidata local, ainda sem homologação ou aprovação do PEN.**

## Objetivo da proposta

Solicita-se avaliar uma contribuição ao módulo WSSEI que acrescenta filtros
opcionais à pesquisa REST existente. A finalidade é permitir consultas mais
precisas por **tipo documental/série, tipo processual e pessoa assinante**.

A necessidade surgiu no desenvolvimento do SEIA: a busca por texto encontra
documentos candidatos e exige chamadas adicionais para conferir assinaturas.
A contribuição procura disponibilizar pela REST critérios que já aparecem
no formulário de pesquisa do SEI Web.

## O que foi alterado

A proposta mantém `GET /processo/pesquisar` — sob o prefixo `/api/v2` — e o
índice Solr já utilizado pelo WSSEI. A relação com os campos da pesquisa web é:

| Campo do formulário SEI Web | Parâmetro REST candidato | Finalidade |
| --- | --- | --- |
| **Tipo do Documento** | `idSerie` | Filtrar a série documental selecionada. |
| **Tipo do Processo** | `idTipoProcedimento` | Filtrar a classificação processual selecionada. |
| **Assinatura / Autenticação** | `idContatoAssinante` | Pesquisar pela pessoa selecionada, usando seu ID de contato. |

O filtro recebe uma pessoa por vez. Os resultados devem respeitar o acesso
da identidade autenticada. O ID de contato não deve ser confundido com o ID
de cargo/função retornado por `/assinante/listar`.

Essa relação apresenta o objetivo da contribuição. A equivalência dos resultados
com a tela web ainda precisa ser comprovada, principalmente quanto à distinção
entre assinatura e autenticação e à cobertura de documentos externos.

O candidato foi desenvolvido sobre o WSSEI **3.0.5**, base pública `11c6bd4`.
Isso não confirma compatibilidade com a versão e os patches instalados no IFBA.
Os nomes dos parâmetros ainda estão sujeitos à revisão.

As mudanças de execução concentram-se no recebimento e validação dos filtros,
no objeto que transporta os critérios e na montagem da consulta existente.
Há também documentação Swagger e testes. Não houve alteração de autenticação,
rotinas de permissão ou aplicações consumidoras. A proposta não prevê migração
de banco, novo índice ou substituição do Solr.

## Validação já realizada

Em laboratório local descartável, com dados fictícios, foram aprovados:

- Sintaxe e testes dos filtros em **PHP 8.2.35 e 8.5.12**.
- **Oito consultas produzidas pelo PHP em Solr 9.6.1 isolado**, usando o schema
  público de protocolos do SEI, isto é, sua definição de campos.
- Combinação dos filtros, consulta por assinante, distinção de IDs semelhantes,
  totais, ordenação e paginação.
- PHPCS e Rector nos três arquivos PHP alterados.

O Solr do laboratório usou configuração mínima e dados escolhidos para testar
a consulta. Não executamos o indexador nativo, o SEI/SIP completo ou as regras
reais de acesso. Essa etapa não acessou a instalação real do IFBA. Inspeções
anteriores autorizadas de leitura ajudaram a entender a interface e os cadastros;
não houve instalação do candidato ou teste dele em produção.

## Pontos que precisam de confirmação

O principal ponto é o filtro de assinante: confirmar como o indexador da versão
alvo representa os IDs de contato no campo `id_assin`. A consulta candidata
exige um ID numérico completo, evitando confundir, por exemplo, `7` com `17`.
Esse comportamento passou nos dados fictícios, mas depende de a representação
real ser compatível. Se não for, o código deverá ser ajustado antes do aceite.

Também precisam ser verificados: permissões de usuários e unidades diferentes;
processos/documentos restritos e sigilosos; cancelamento e revogação; documentos
externos; distinção entre assinatura e autenticação documental; resultados,
totais e ausência de regressão nas consultas antigas. Não se presume que todas
as modalidades de assinatura estejam cobertas.

## Como seria feita a instalação para homologação

1. **Confirmar o ambiente alvo:** versões de SEI, SIP, WSSEI, PHP e Solr,
   patches locais e responsável técnico. Conferir as condições de uso das fontes.
2. **Definir e autorizar um laboratório separado da produção**, com banco,
   índice e credenciais próprios e dados fictícios. As fontes SEI/SIP/Infra
   devem ser obtidas por canal autorizado; podem permanecer sob gestão do IFBA.
3. **Preparar e revisar a entrega:** código, diferenças em relação à base,
   documentação, testes e um pacote com identificação exata da revisão e checksum.
   O pacote institucional ainda precisa ser preparado; não se trata de release oficial.
4. **Instalar somente na homologação**, pela equipe responsável e conforme o
   procedimento aplicável à versão do módulo, após backup ou snapshot do laboratório.
   Manter a versão anterior disponível para reversão.
5. **Criar processos, documentos e contas fictícios pelos mecanismos nativos**,
   aguardar a indexação e comparar os resultados esperados com os obtidos pela
   REST. Comparar também com a pesquisa web, mantendo a mesma identidade,
   unidade e critérios equivalentes. Conferir os diferentes níveis de acesso
   e explicar eventuais diferenças de resultados e totais.
6. **Registrar o resultado:** aprovar o recorte testado ou apontar ajustes e
   repetir os cenários afetados. Se houver falha, restaurar o ambiente anterior.

Não é necessário disponibilizar credenciais de produção para essa atividade.
O uso de cópia de dados reais não faz parte desta proposta. Não executar os
alvos gerais `make tests-api` ou `restore` na instalação institucional: eles
podem restaurar banco e instalar o módulo, além de executar operações de escrita.

## Entrega, responsabilidades e próximos passos

Estão disponíveis código candidato, descrição das mudanças, Swagger, testes
reproduzíveis e roteiro de integração. A equipe da contribuição poderá preparar
o pacote identificado e incorporar os ajustes; a administração do SEI deverá
avaliar a possibilidade do ambiente e definir quem autoriza e realiza a instalação
e a homologação. Essas responsabilidades ainda não foram acordadas.

Após avaliação e ajustes, pretende-se preparar a contribuição ao
[projeto oficial do PEN](https://github.com/pengovbr/mod-wssei). O aceite do PEN
e uma eventual adoção em produção pelo IFBA são decisões separadas.

**Encaminhamento proposto:** avaliar a viabilidade da homologação, confirmar
as versões e indicar um responsável técnico. Este documento não solicita nem
autoriza instalação imediata em produção.
