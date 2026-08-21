# Stack Research — v2 SEO técnico básico

Research mode: targeted_web
Motivo: os hooks de indexação, sitemap e dados estruturados dependem de APIs
mantidas por WordPress 6.9 e WooCommerce; versões e comportamento nativo devem
ser confirmados antes de decidir não introduzir um plugin SEO.

## Stack detectada

- WordPress `6.9-php8.3-apache`, declarado em `compose.yaml`; o contêiner
  confirmou `6.9.4` antes de a ausência de `.env` impedir consultas WP-CLI.
- WooCommerce `11.0.1`, fixado em `README.md` e `scripts/configure-store.ps1`.
- Tema próprio `coursepress-lab` usa `add_theme_support( 'title-tag' )` e
  `wp_head()`; plugin próprio contém os utilitários WP-CLI.

## Fontes primárias

- Fonte primária: WordPress Developer Resources — `wp_get_document_title()` e
  filtros de título do documento.
  URL: https://developer.wordpress.org/reference/functions/wp_get_document_title/
  Acessado em: 2026-08-21
  Aplicação: manter `title-tag` e ajustar somente `document_title_parts`, sem
  escrever uma segunda tag `<title>`.

- Fonte primária: WordPress Developer Resources — `rel_canonical()`.
  URL: https://developer.wordpress.org/reference/functions/rel_canonical/
  Acessado em: 2026-08-21
  Aplicação: preservar o canonical nativo de consultas singulares e acrescentar
  somente o canonical da Loja, que é um arquivo WooCommerce.

- Fonte primária: WordPress Developer Resources — hook `wp_robots`.
  URL: https://developer.wordpress.org/reference/hooks/wp_robots/
  Acessado em: 2026-08-21
  Aplicação: declarar `noindex,follow` no array nativo para transacionais e
  utilitários, sem imprimir meta robots concorrente.

- Fonte primária: WordPress Developer Resources — `WP_Sitemaps` e
  `wp_sitemaps_posts_query_args`.
  URL: https://developer.wordpress.org/reference/classes/wp_sitemaps/
  Acessado em: 2026-08-21
  Aplicação: conservar `wp-sitemap.xml` do core e filtrar somente entradas que a
  política define como não indexáveis.

- Fonte primária: WooCommerce Code Reference — `WC_Structured_Data`.
  URL: https://woocommerce.github.io/code-reference/files/woocommerce-includes-class-wc-structured-data.html
  Acessado em: 2026-08-21
  Aplicação: não criar JSON-LD próprio de `Product`; verificar a presença única
  da saída nativa numa página de produto publicada.

## Decisões aplicadas

- Usar APIs do core, WooCommerce e tema, sem dependência nova.
- Usar texto já administrado no WordPress (título, descrição da loja, resumo ou
  conteúdo publicado) para descrições; este ciclo não cria estratégia editorial.
- Usar `wp_robots` e filtros de sitemap para a política; `robots.txt` virtual
  permanece nativo e só é verificado, não substituído.

## Alternativas rejeitadas

- Plugin SEO de terceiros — proibido pelo escopo e desnecessário para o conjunto
  de hooks coberto pelo core.
- JSON-LD próprio de produto — duplicaria o gerador `WC_Structured_Data`.
- Open Graph/Twitter sem imagem social aprovada — produziria apresentação
  incompleta e introduziria decisão visual/conteúdo fora do marco.

## Riscos e lacunas

- A instalação local não pôde ser consultada por WP-CLI nesta sessão porque a
  `.env` não está presente. A política para tipos Tutor LMS será testada contra
  os tipos e metadados reais no gate do plano.
