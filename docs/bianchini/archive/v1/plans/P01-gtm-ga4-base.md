# P01 — Instrumentação base GTM + GA4

**Risco:** médio — a alteração é invisível na interface, mas introduz uma
fronteira externa de coleta e precisa impedir duplicidade e PII.

**Política calculada:** `standard` / `medium` / `integration` → `slice`,
`per_slice`, mutação `not_required`, até 3 correções no seam
`analytics-container`.

### Tarefa 1 — Entregar contêiner GTM configurável e runbook de GA4

**Execution:** slice

**Review:** per_slice

**Change:** integration

**Readiness refs:** D-001, D-002, A-001, P-001, P-002, U-001, U-002, S-001, SD-001

**Test seams:** configuração Compose → constante WordPress; hooks `wp_head` e
`wp_body_open` → HTML público; contêiner GTM → tag Google/GA4 de teste.

**Spec refs:** `ga4-gtm-change.md` — Arquitetura, Contratos públicos e
invariantes, Dados/permissões/segurança e Jornadas/verificação;
`analytics-web.md` — Configuração, Comportamento, Privacidade e limites e
Verificação.

**Files:** `.env.example`; `compose.yaml`;
`wp-content/themes/coursepress-lab/functions.php`; `README.md`;
`docs/ROADMAP.md`; `docs/bianchini/changes/v1/spec-deltas/analytics-web.md`.

**Contract:** adicionar uma variável local opcional de ID de contêiner GTM,
propagá-la ao WordPress e renderizar as duas partes oficiais do contêiner uma
vez apenas quando o valor corresponder a `GTM-[A-Z0-9]+`. A tag Google com ID
GA4 existe somente no GTM. Não alterar plugin, formulário, Mailpit, fluxo de
checkout, CSS, dados, eventos nem SEO; nunca disponibilizar PII no HTML ou em
dataLayer.

**Verification:** `docker compose config --quiet`; `docker compose up -d`;
`docker compose exec -T wordpress php -l /var/www/html/wp-content/themes/coursepress-lab/functions.php`;
`docker compose run --rm cli theme is-active coursepress-lab`;
`curl -fsS http://localhost:8080/ | rg -o 'googletagmanager\.com/gtm\.js\?id=GTM-[A-Z0-9]+' | wc -l` deve retornar `0` com a variável vazia e `1` com ID de teste; Preview/Tag Assistant deve mostrar uma única tag Google e GA4 DebugView um `page_view` sem PII.

**Done when:** o tema não gera rastreamento sem configuração, gera uma única
instalação GTM válida com ID de teste, e o runbook permite ao responsável
configurar/validar GA4 via GTM sem armazenar segredos ou Measurement ID no
repositório. O roadmap marca apenas GA4 + GTM como concluído após a evidência
externa U-001; SEO e eventos permanecem pendentes.
