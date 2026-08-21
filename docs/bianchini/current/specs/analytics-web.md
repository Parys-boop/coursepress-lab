# Spec delta — Instrumentação web de analytics

**Destino após o ciclo:** `docs/bianchini/current/specs/analytics-web.md`

## Propósito

As páginas públicas do CoursePress Lab podem carregar um único contêiner
Google Tag Manager configurado localmente. O contêiner é a única via para a
tag base do Google Analytics 4.

## Configuração

- `COURSEPRESS_GTM_CONTAINER_ID` é opcional, fica no `.env` não versionado e
  deve obedecer a `GTM-[A-Z0-9]+`.
- O Compose expõe o valor como constante WordPress apenas aos serviços locais.
- O Measurement ID GA4 `G-*` é mantido na tag Google do GTM, não em código,
  arquivo de ambiente versionado, banco WordPress ou documentação pública.

## Comportamento

- Valor ausente ou inválido: nenhuma URL, script, iframe ou noscript de GTM é
  emitido.
- Valor válido: `wp_head` emite a parte head e `wp_body_open` emite o fallback
  noscript, ambos usando o mesmo ID; cada resposta pública contém uma única
  instalação do contêiner.
- O GTM configura uma única tag Google para GA4, acionada em page view. Não há
  Google tag/`gtag.js` adicional no tema. D-002, P-001.

## Privacidade e limites

- Nenhum nome, e-mail, identificador WordPress, parâmetro de formulário ou
  URL potencialmente identificável é enviado, exposto no `dataLayer` ou usado
  como variável/tag neste contrato. P-002.
- Eventos `generate_lead`, `begin_checkout`, `purchase` e equivalentes não
  existem neste ciclo.
- Ativação em produção requer a ação externa U-002; sem ela, o ID público não
  é configurado.

## Verificação

- Inspecionar HTML com e sem o ID de teste e provar a cardinalidade do
  contêiner.
- Usar Preview/Tag Assistant e GA4 DebugView para confirmar somente o
  `page_view` automático no fluxo de teste. U-001.

## Referência de readiness

SD-001 define este contrato futuro para promoção ao diretório de specs atuais
no encerramento do ciclo.
