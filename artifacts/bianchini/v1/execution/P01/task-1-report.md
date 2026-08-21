# Implementer Report

- Brief: `artifacts/bianchini/v1/execution/P01/task-1-brief.md`
- Status: COMPLETED

## Changes

- Propagada a configuração opcional `COURSEPRESS_GTM_CONTAINER_ID` do Compose
  para a constante WordPress, vazia por padrão.
- Adicionada validação estrita do identificador e emissão idempotente dos
  snippets oficiais nos hooks `wp_head` e `wp_body_open`.
- Documentado o runbook local, os limites de privacidade e o fallback sem
  coleta; o roadmap continua pendente até U-001.

## Verification

- `git diff --check`: passou.
- Parse de `compose.yaml` com PyYAML: passou.
- Casos focados da regex `GTM-[A-Z0-9]+`: passaram.
- Inspeção estática de cardinalidade/escopo: passou.
- Compose, lint PHP, tema ativo e HTML público nos estados vazio e configurado:
  aprovados no commit da unidade.
- Preparação externa: contêiner de teste configurado somente no `.env`
  ignorado; WordPress recriado e HTML local confirmado com exatamente um
  `gtm.js` e um fallback `ns.html` para esse contêiner.
- Tag Assistant: aprovado em `proof-838c7b512fb19d2100da86238861c3d4`;
  conexão local, contêiner em Preview, única Google tag acionada uma vez e
  nenhuma etiqueta não acionada.
- GA4 DebugView: aprovado em `proof-5a16156fa14106a321fa0f10e9492ed4`;
  `page_view` com `debug_mode = 1`, apenas `first_visit` e `session_start`
  automáticos, sem evento personalizado ou dado pessoal.
- Capturas e hashes: `evidence/external-gates.json`.

## Decisions

- Manter o ID de teste apenas no `.env` ignorado e nunca promovê-lo para arquivo
  versionado; as capturas canônicas tiveram a barra do navegador removida antes
  do versionamento, preservando somente a evidência funcional.
- Preservar a spec delta congelada; sua promoção cabe ao encerramento do ciclo.

## Concerns

- Nenhum concern aberto; publicação do GTM permanece fora do escopo.
