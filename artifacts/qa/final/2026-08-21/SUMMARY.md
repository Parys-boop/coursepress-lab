# Homologação final — RC v1-p01-51c2250

## Fingerprint e ambiente

- Veredito: **ACEITO**.
- Executado em: `2026-08-21T15:58:50-03:00`.
- RC: `v1-p01-51c2250`.
- Revision: `51c225052348411c01f94dcf5189e5cb5c1e79cd`.
- Build: `docker-local`.
- Checksum: `0c8236a6242765ab9f0568f7a22be1115d7e169f22043cf3d0e1245bbe5acccb`.
- Runner real: Google Chrome `151.0.7922.173`, sessões limpas contra `http://localhost:8080/`.
- Plataforma/perfil: Docker local; navegador web; visitante anônimo; contêiner GTM e propriedade GA4 de teste.
- Viewports observados: desktop `1440×1200`; mobile `390×844` CSS px.
- Política: `standard`, risco médio, mutação `not_required`, manual `none` porque não há manual contratado no escopo.

## Baseline automatizada

| Comando | Resultado | Resumo |
|---|---|---|
| `docker compose ps` | passed | WordPress e Mailpit ativos; MariaDB saudável; porta pública `8080`. |
| `docker compose run --rm cli plugin list` | passed | `coursepress-core` e WooCommerce ativos; WP-CLI concluiu sem erro. |
| `curl -fsS http://localhost:8080/` | passed | Resposta com 35.347 bytes; exatamente um `gtm.js` e um `ns.html`. |

O fingerprint foi recalculado por `git archive` e corresponde ao candidato. O
`proof-map.json` vincula 3/3 comandos ao mesmo RC, sem lacunas automatizadas ou
manuais. Não há mutation testing aplicável a esta mudança de integração sem
regra material.

## Matriz de aceite

| ID | Plataforma | Perfil | Jornada ou ação | Automação | Execução real | Visual | Resultado | Evidência |
|---|---|---|---|---|---|---|---|---|
| QA-01 | Docker local | ambiente de teste | Subir e responder como RC | 3/3 release gates | Home pública respondeu | n/a | passed | `automation-evidence.json`; `proof-map.json` |
| QA-02 | Chrome desktop | visitante anônimo | Abrir/recarregar a home com GTM configurado | HTML: 1 script + 1 fallback | Chrome real, sessão limpa | Hierarquia, tipografia, contraste, alinhamento e conteúdo sem regressão | passed | `evidence/coursepress-qa-desktop-2026-08-21.png` |
| QA-03 | Chrome mobile | visitante anônimo | Abrir a home em 390×844, verificar responsividade, menu e foco | CDP: `innerWidth=clientWidth=scrollWidth=390`; 1 recurso GTM | Navegação e toggle operados no Chrome | Sem corte/overflow; conteúdo legível; foco visível; menu expande com `aria-expanded=true` | passed | `evidence/coursepress-qa-mobile-cdp.json`; screenshots mobile |
| QA-04 | Tag Assistant | contêiner de teste | Preview conectado à home | Proof do P01 aprovado | Preview real executado pelo responsável | Google tag acionada 1 vez; nenhuma não acionada | passed | `artifacts/bianchini/v1/execution/P01/evidence/coursepress-tag-assistant-2026-08-21.png` |
| QA-05 | GA4 DebugView | propriedade de teste | Receber `page_view` em debug | Proof do P01 aprovado | DebugView real executado pelo responsável | `page_view` com `debug_mode=1`; apenas automáticos `first_visit`/`session_start` | passed | `artifacts/bianchini/v1/execution/P01/evidence/coursepress-ga4-debugview-2026-08-21.jpg` |
| QA-06 | Chrome + GA4 | visitante anônimo | Confirmar limite de privacidade | Runtime: somente `gtm.js`, `gtm.dom`, `gtm.load` no dataLayer | Rede/DebugView observados | Nenhum evento customizado, lead, checkout, compra, nome ou e-mail | passed | CDP JSON e evidência GA4 |

## Varredura visual e acessibilidade básica

- Desktop e mobile preservam hierarquia, espaçamento, contraste e legibilidade.
- A home mobile não possui overflow horizontal; o primeiro recorte de 390 px
  foi um falso positivo da largura mínima do Chrome CLI e foi substituído por
  emulação CDP real, que mediu `scrollWidth = 390`.
- O toggle móvel mantém foco visível, nome acessível e atualiza
  `aria-expanded` ao abrir a navegação.
- O estado de curso temporariamente indisponível é compreensível nos dois
  viewports e não oferece ação destrutiva ou cobrança.
- Nenhuma mudança visual faz parte do P01 e nenhum manifesto de design é
  aplicável.

## Achados, retestes e limites

- Nenhuma falha `critical` ou `important`.
- Nenhuma alteração de produto ou onda de reteste foi necessária.
- Uma requisição eventual de `favicon.ico` retornou 404 numa captura inicial;
  é preexistente, fora do seam GA4/GTM e não afeta a jornada. A sessão final
  não registrou erro de console.
- GTM permaneceu em Preview e não foi publicado. Não houve push, merge ou
  deploy.

## Veredito

**ACEITO.** Todos os comandos de release, plataformas, perfis, jornadas
críticas, integrações externas e verificações visuais obrigatórias passaram no
fingerprint do RC. Não há linha obrigatória `not_run`, lacuna de proof-map,
falha relevante ou manual contratado pendente.
