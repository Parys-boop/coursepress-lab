# P01 — SEO técnico básico reproduzível

**Risco:** medium

**Política calculada:** `slice` / `per_slice`; mutação `not_required` para
mudanças de comportamento integradas por hooks sem lógica de domínio isolada.

### Tarefa 1 — Política de indexação e descoberta aplicada pelo tema

**Execution:** slice
**Review:** per_slice
**Change:** behavioral
**Readiness refs:** D-002, D-003, A-001, P-001, P-002, S-001, SD-001
**Test seams:** wp_robots, wp_sitemaps_posts_query_args, robots.txt, wp-sitemap.xml
**Spec refs:** specs/seo-web-change.md#robots-e-indexação, specs/seo-web-change.md#sitemap-e-robotstxt, spec-deltas/seo-web.md#política-de-indexação
**Files:** wp-content/themes/coursepress-lab/functions.php, wp-content/plugins/coursepress-core/cli/validate-seo.php
**Contract:** O tema usa somente `wp_robots` para noindex,follow e preserva o robots.txt/sitemap do core; Carrinho, Checkout, Minha conta e aulas protegidas não entram na política indexável nem no sitemap, enquanto a prévia publicada continua elegível. A saída HTML nunca duplica robots.
**Verification:** `pwsh -NoProfile -File ./scripts/validate-seo.ps1 -Scope fast` retorna código 0 e relata uma diretiva robots por URL, sitemap nativo disponível e nenhuma URL transacional incluída.
**Done when:** A política fica codificada por condicionais WordPress/WooCommerce/Tutor LMS e o validador de leitura consegue comparar as opções e URLs reais sem alterar dados.

### Tarefa 2 — Metadados técnicos públicos sem sobreposição nativa

**Execution:** slice
**Review:** per_slice
**Change:** behavioral
**Readiness refs:** D-001, D-004, D-005, A-001, P-001, S-001, SD-001
**Test seams:** document_title_parts, rel_canonical, wp_head, WC_Structured_Data
**Spec refs:** specs/seo-web-change.md#contrato-de-páginas-públicas, specs/seo-web-change.md#metadados-sociais-e-dados-estruturados, spec-deltas/seo-web.md#metadados-html
**Files:** wp-content/themes/coursepress-lab/functions.php, wp-content/plugins/coursepress-core/cli/validate-seo.php
**Contract:** Títulos permanecem em title-tag; description é única, sanitizada e proveniente do conteúdo existente; canonicals nativos de singulares permanecem e a Loja recebe somente seu canonical oficial. Não há Open Graph/Twitter nem JSON-LD CoursePress de produto.
**Verification:** `pwsh -NoProfile -File ./scripts/validate-seo.ps1 -Scope fast` retorna código 0 e conta title, description, canonical, robots e JSON-LD Product nas URLs que o WP-CLI descobrir.
**Done when:** Início, Loja, produto, curso/prévia e páginas legais têm a apresentação técnica prevista quando existirem, e a página de produto demonstra somente a estrutura WooCommerce.

### Tarefa 3 — Configuração e gate local reproduzíveis

**Execution:** slice
**Review:** per_slice
**Change:** config
**Readiness refs:** D-001, D-002, D-003, D-004, D-005, P-001, P-002, U-001, SD-001
**Test seams:** WP-CLI preflight, blog_public, HTTP localhost, documentação operacional
**Spec refs:** specs/seo-web-change.md#configuração-permissões-e-dados, specs/seo-web-change.md#jornadas-e-plataformas, spec-deltas/seo-web.md#automação-e-verificação
**Files:** scripts/configure-seo.ps1, scripts/validate-seo.ps1, wp-content/plugins/coursepress-core/cli/configure-seo.php, wp-content/plugins/coursepress-core/cli/validate-seo.php, README.md, docs/ROADMAP.md
**Contract:** O configurador exige `.env`, Docker e componentes ativos, define explicitamente a visibilidade pública local sem tocar em conteúdo, GA4/GTM ou dados de comércio; o validador une WP-CLI e inspeção HTTP de Início, Loja, produto, Carrinho, Checkout, Minha conta, robots.txt e sitemap. README documenta pré-condições e o Roadmap só marca o marco quando o gate for aprovado.
**Verification:** `pwsh -NoProfile -File ./scripts/configure-seo.ps1` seguido de `pwsh -NoProfile -File ./scripts/validate-seo.ps1 -Scope plan` retorna código 0; `pwsh -NoProfile -File ./scripts/validate-seo.ps1 -Scope release` retorna código 0 antes de homologação.
**Done when:** U-001 possui a evidência operacional exigida; a documentação ensina o comando real e não anuncia publicação, deploy ou métricas.
