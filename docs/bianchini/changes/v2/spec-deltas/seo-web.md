# Spec delta — SEO técnico web

**Destino após o ciclo:** `docs/bianchini/current/specs/seo-web.md`

## Propósito

O CoursePress Lab fornece indexabilidade técnica básica, reproduzível e sem
plugin SEO de terceiros para suas URLs públicas. SD-001.

## Metadados HTML

- O tema mantém `add_theme_support( 'title-tag' )` e não imprime `<title>`
  manualmente. D-001, P-001.
- Cada página pública indexável com texto-fonte tem uma única description
  sanitizada e limitada; o conteúdo é existente, não copy criada neste ciclo.
- URLs singulares usam canonical do core; a Loja tem exatamente um canonical
  para `wc_get_page_permalink( 'shop' )`. D-001, A-001.
- Social Open Graph/Twitter não é emitido até existir imagem social aprovada e
  escopo específico. D-004.

## Política de indexação

- Início, Loja, produto publicado, curso publicado, aula marcada como prévia e
  páginas legais permanecem indexáveis, salvo diretiva nativa externa.
- Carrinho, Checkout, Minha conta, busca, 404, paginação e aula não marcada
  como prévia recebem `noindex,follow` por `wp_robots`. D-002, P-002.
- O tema não gera uma segunda meta robots; a resposta contém no máximo a tag
  agregada do WordPress. P-001.

## Descoberta por rastreadores

- `robots.txt` permanece virtual e conserva o sitemap do core; não existe
  arquivo físico ou Sitemap alternativo. D-003.
- `wp-sitemap.xml` permanece o índice XML do WordPress. A consulta de páginas
  exclui IDs oficiais Carrinho, Checkout e Minha conta; a consulta de aulas
  Tutor LMS só inclui prévias publicadas. D-003, P-002.

## Dados estruturados

- `WC_Structured_Data` é o único produtor de `Product` JSON-LD; o código
  CoursePress não imprime JSON-LD de produto. D-005.

## Automação e verificação

- `scripts/configure-seo.ps1` é idempotente e recusa `.env`, Docker, tema ou
  plugins inválidos antes de alterar `blog_public`.
- `scripts/validate-seo.ps1` e `cli/validate-seo.php` verificam opções, URLs,
  tags HTML, sitemap, robots e a ausência de duplicidade. U-001.
