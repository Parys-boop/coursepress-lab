# Readiness — SEO técnico básico

```json
{
  "schema_version": 1,
  "status": "ready",
  "scope_digest": "52e0cf898b1634c744836b09eb802205e5bb2dff3732abca1205a783d65496e0",
  "repository_revision": "c02d15ee78c231d997471f2ca3cadd6af263a78c",
  "design_required": false,
  "impact_map": {
    "applications": ["WordPress público local"],
    "modules": ["tema coursepress-lab", "automação WP-CLI coursepress-core", "WooCommerce", "Tutor LMS"],
    "contracts": ["metadados HTML", "robots.txt virtual", "wp-sitemap.xml", "configuração WP-CLI"],
    "data": ["opção blog_public", "IDs de páginas WooCommerce", "metadado _is_preview de aulas Tutor"],
    "platforms": ["WordPress/PHP 8.3 em Docker", "navegador/HTTP localhost"]
  },
  "decisions": [
    {
      "id": "D-001",
      "statement": "Títulos usam title-tag e document_title_parts; descriptions são emitidas uma vez pelo tema a partir de texto já publicado, sem criar conteúdo editorial.",
      "evidence": "functions.php já declara title-tag; wp_get_document_title() expõe document_title_parts.",
      "destinations": ["docs/bianchini/changes/v2/specs/seo-web-change.md", "docs/bianchini/changes/v2/spec-deltas/seo-web.md", "docs/bianchini/changes/v2/plans/P01-seo-tecnico-basico.md"]
    },
    {
      "id": "D-002",
      "statement": "Carrinho, Checkout, Minha conta, busca, 404, páginas paginadas e aulas protegidas recebem noindex,follow pelo filtro wp_robots; o tema não escreve uma segunda meta robots.",
      "evidence": "wp_robots é o hook nativo que agrega diretivas em uma única tag.",
      "destinations": ["docs/bianchini/changes/v2/specs/seo-web-change.md", "docs/bianchini/changes/v2/spec-deltas/seo-web.md", "docs/bianchini/changes/v2/plans/P01-seo-tecnico-basico.md"]
    },
    {
      "id": "D-003",
      "statement": "robots.txt e wp-sitemap.xml permanecem recursos do WordPress; a mudança não cria arquivo robots.txt nem sitemap paralelo e remove apenas URLs não indexáveis do sitemap nativo.",
      "evidence": "WP_Sitemaps registra a rota wp-sitemap.xml e robots_txt filtra a resposta virtual.",
      "destinations": ["docs/bianchini/changes/v2/specs/seo-web-change.md", "docs/bianchini/changes/v2/spec-deltas/seo-web.md", "docs/bianchini/changes/v2/plans/P01-seo-tecnico-basico.md"]
    },
    {
      "id": "D-004",
      "statement": "Metadados sociais não serão emitidos neste marco: não há imagem social aprovada e criar uma seria decisão visual/editorial fora do escopo.",
      "evidence": "O repositório não possui asset ou manifesto de design para imagem Open Graph; o escopo permite-os somente quando adequados.",
      "destinations": ["docs/bianchini/changes/v2/specs/seo-web-change.md", "docs/bianchini/changes/v2/spec-deltas/seo-web.md", "docs/bianchini/changes/v2/plans/P01-seo-tecnico-basico.md"]
    },
    {
      "id": "D-005",
      "statement": "O JSON-LD Product é exclusivamente do WooCommerce; a entrega só o inspeciona e não registra gerador próprio.",
      "evidence": "WC_Structured_Data gera e imprime structured data no WooCommerce.",
      "destinations": ["docs/bianchini/changes/v2/specs/seo-web-change.md", "docs/bianchini/changes/v2/spec-deltas/seo-web.md", "docs/bianchini/changes/v2/plans/P01-seo-tecnico-basico.md"]
    }
  ],
  "assumptions": [
    {
      "id": "A-001",
      "impact": "medium",
      "status": "bounded",
      "evidence": "compose.yaml fixa WordPress 6.9 e a referência oficial confirma os hooks e rotas necessários.",
      "fallback": "Se a instalação ativa tiver saída nativa divergente, não concluir o gate; ajustar apenas a integração após evidência WP-CLI/HTML e revalidar o pacote conforme change-policy.",
      "destinations": ["docs/bianchini/changes/v2/specs/seo-web-change.md", "docs/bianchini/changes/v2/plans/P01-seo-tecnico-basico.md"]
    }
  ],
  "pitfalls": [
    {
      "id": "P-001",
      "impact": "high",
      "prevention": "Usar title-tag, rel_canonical e wp_robots nativos; nunca imprimir title, canonical ou robots manual duplicado.",
      "recovery": "Remover somente o hook adicional que duplicar a saída e restaurar a saída única do core antes de novo gate.",
      "verification": "Validador WP-CLI e inspeção HTML contam exatamente uma tag title, canonical e robots por URL aplicável.",
      "destinations": ["docs/bianchini/changes/v2/specs/seo-web-change.md", "docs/bianchini/changes/v2/spec-deltas/seo-web.md", "docs/bianchini/changes/v2/plans/P01-seo-tecnico-basico.md"]
    },
    {
      "id": "P-002",
      "impact": "high",
      "prevention": "Derivar o post type de aula pelo Tutor LMS e limitar sitemap/indexação a prévias; excluir IDs WooCommerce transacionais da consulta de páginas do sitemap.",
      "recovery": "Aplicar noindex temporário ao tipo afetado, corrigir o filtro de sitemap e repetir a inspeção das URLs publicada e protegida.",
      "verification": "validate-seo.php compara IDs transacionais, metadado _is_preview, robots e entradas do sitemap real.",
      "destinations": ["docs/bianchini/changes/v2/specs/seo-web-change.md", "docs/bianchini/changes/v2/spec-deltas/seo-web.md", "docs/bianchini/changes/v2/plans/P01-seo-tecnico-basico.md"]
    }
  ],
  "user_actions": [
    {
      "id": "U-001",
      "needed_by": "P01",
      "can_continue_without": true,
      "fallback": "A implementação pode ser revisada estaticamente; o plano não é marcado concluído até o gate WP-CLI/HTML rodar em uma cópia local com .env válida.",
      "evidence_required": "Saída com código 0 de scripts/validate-seo.ps1 e HTML salvo das URLs aprovadas.",
      "destinations": ["docs/bianchini/changes/v2/USER_ACTIONS.md", "docs/bianchini/changes/v2/plans/P01-seo-tecnico-basico.md"]
    }
  ],
  "spikes": [
    {
      "id": "S-001",
      "status": "passed",
      "evidence": "Cartografia local e pesquisa oficial confirmaram os seams wp_head, wp_robots, WP_Sitemaps e WC_Structured_Data.",
      "decision": "Um único plano slice no tema e na automação existente cobre o escopo sem plugin novo.",
      "destinations": ["docs/bianchini/changes/v2/specs/seo-web-change.md", "docs/bianchini/changes/v2/plans/P01-seo-tecnico-basico.md"]
    }
  ],
  "design_surfaces": [],
  "spec_deltas": [
    {
      "id": "SD-001",
      "source": "docs/bianchini/changes/v2/spec-deltas/seo-web.md",
      "target": "docs/bianchini/current/specs/seo-web.md",
      "destinations": ["docs/bianchini/changes/v2/specs/seo-web-change.md", "docs/bianchini/changes/v2/spec-deltas/seo-web.md", "docs/bianchini/changes/v2/plans/P01-seo-tecnico-basico.md"]
    }
  ]
}
```
