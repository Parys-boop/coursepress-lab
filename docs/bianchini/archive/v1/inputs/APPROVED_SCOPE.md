# Escopo aprovado — v1: GA4 + Google Tag Manager

## Origem

Solicitação do responsável em 21-08-2026: planejar exclusivamente o próximo
marco **GA4 + Google Tag Manager** do CoursePress Lab, após o encerramento do
marco de e-mail transacional.

## Resultado esperado

O ambiente deve ter uma integração de instrumentação web que:

- injete o contêiner Google Tag Manager (GTM) de modo seguro e configurável;
- permita configurar uma tag de configuração do Google Analytics 4 (GA4) no
  contêiner GTM, recebendo o Measurement ID fora do código versionado;
- preserve a operação local demonstrativa e não envie identificadores pessoais
  ao Google;
- documente as ações no Google Analytics e no Google Tag Manager e uma
  verificação reproduzível de que a instrumentação está presente.

## Limites obrigatórios

- Não implementar neste ciclo de planejamento.
- Não incluir SEO técnico, sitemap, metatags ou otimizações de busca.
- Não reabrir nem alterar o marco de e-mail transacional.
- Não criar eventos de conversão de produto, compra ou lead; o registro de
  eventos e leads permanece no marco próprio da Fase 5.
- Não introduzir banners, CMP ou uma reformulação visual; qualquer decisão
  jurídica de ativação em ambiente público fica como ação externa explícita.
- Não versionar IDs de produção, credenciais, dados pessoais ou exports de
  contas Google.
- Não realizar commit, push, pull request, merge, publicação ou alteração de
  código de produção durante este planejamento.

## Critério de aceite do futuro marco

Com IDs de teste fornecidos pelo responsável, o GTM é inserido uma única vez
na página pública pelos pontos oficiais do tema WordPress; uma tag GA4 no
contêiner recebe somente o Measurement ID configurado na interface Google; o
modo Preview/Tag Assistant mostra o carregamento da tag e o DebugView do GA4
recebe o `page_view` automático, sem parâmetros de identificação pessoal.

## Fora de escopo

SEO, Consent Mode, CMP/banner de cookies, campanhas, remarketing, Google Ads,
User-ID, enhanced measurement customizado, e-commerce, `begin_checkout`,
`purchase`, `generate_lead`, CRM, automação de marketing, relatórios e
dashboards.
