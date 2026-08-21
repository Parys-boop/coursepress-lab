# Mudança — SEO técnico básico web

## Objetivo e limites

O sistema passará a emitir metadados técnicos coerentes para URLs públicas
indexáveis e a impedir a indexação das superfícies transacionais ou utilitárias.
Ele usa o tema, WordPress e WooCommerce já instalados. Não altera analytics,
conversão, conteúdo editorial, plugin de terceiros, deploy, design ou
desempenho. D-001, D-002, D-003, D-004, D-005.

## Arquitetura e seams

`wp-content/themes/coursepress-lab/functions.php` compõe os filtros do core e
WooCommerce. `wp_head()` em `header.php` continua sendo o único ponto de saída
do tema. `scripts/configure-seo.ps1` chama arquivos WP-CLI no plugin próprio
para configurar e validar, seguindo os scripts existentes. A configuração não
depende de banco versionado. S-001, SD-001.

## Contrato de páginas públicas

### Título e description

O tema conserva `title-tag`; títulos recebem o nome da página/objeto e o nome
do site pelo filtro documentado do WordPress. Cada URL indexável relevante tem
no máximo uma `meta[name=description]`, derivada de texto já administrado:

- Início e Loja: `blogdescription` configurada pela loja;
- produto: descrição curta WooCommerce, com fallback para descrição publicada;
- curso, prévia e páginas legais: resumo publicado, com fallback para conteúdo
  sem HTML e truncado de forma segura.

URL sem fonte textual não recebe description vazia ou inventada. D-001, P-001.

### Canonical

Consultas singulares continuam no `rel_canonical()` do core. A Loja recebe um
canonical adicional somente em `is_shop()`, para sua URL WooCommerce oficial.
Nenhum canonical é emitido em duplicidade, nem são reescritos parâmetros de
checkout/lead. D-001, A-001, P-001.

### Robots e indexação

`wp_robots` é a única via de diretivas. Carrinho, Checkout, Minha conta,
busca, 404, paginação e aula Tutor LMS sem `_is_preview = 1` emitem
`noindex,follow`. Início, Loja, produto, curso publicado, prévia publicada e
páginas legais não recebem noindex por esta mudança. D-002, P-002.

## Sitemap e robots.txt

O `robots.txt` virtual e `wp-sitemap.xml` são nativos do WordPress. Não há
arquivo físico, endpoint alternativo ou segunda linha Sitemap. O filtro do
sitemap exclui os IDs oficiais de Carrinho, Checkout e Minha conta da lista de
páginas e limita as aulas Tutor LMS às prévias. D-003, A-001, P-002.

## Metadados sociais e dados estruturados

Não haverá Open Graph/Twitter no marco atual; sem imagem social aprovada, eles
não são tecnicamente completos e sua criação seria visual/editorial. D-004.

WooCommerce continua a ser o único produtor de JSON-LD `Product`; o tema não
emite `application/ld+json`. O validador verifica a presença nativa em uma
página de produto publicada e a ausência de gerador CoursePress concorrente.
D-005, P-001.

## Configuração, permissões e dados

`configure-seo.php` só opera numa instalação local válida, confere tema,
`coursepress-core` e WooCommerce ativos, torna `blog_public=1` explícito e
valida IDs das páginas WooCommerce antes de finalizar. Não manipula usuários,
pedidos, leads, dados pessoais ou credenciais. `validate-seo.php` é somente
leitura. U-001.

## Jornadas e plataformas

O gate prova Início, Loja, produto, Carrinho, Checkout, Minha conta e uma aula
Tutor LMS quando existirem; também prova `/robots.txt` e `/wp-sitemap.xml`.
O alvo é WordPress/PHP em Docker e a inspeção HTTP local. Sem `.env`, a prova
operacional fica pendente, não substituída por suposição. U-001, P-002.
