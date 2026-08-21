<?php
/**
 * Configura a fundacao gerenciada do Tutor LMS da CoursePress Academy.
 *
 * Executado somente por scripts/configure-lms.ps1 via WP-CLI.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

const COURSEPRESS_CORE_LMS_MANAGED_META = '_coursepress_lms_managed';
const COURSEPRESS_CORE_LMS_KEY_META = '_coursepress_lms_key';
const COURSEPRESS_CORE_LMS_HASH_META = '_coursepress_lms_definition_sha256';
const COURSEPRESS_CORE_LMS_MANAGED_VALUE = '1';

function coursepress_core_lms_fail( string $message ): void {
    if ( defined( 'WP_CLI' ) && WP_CLI ) {
        WP_CLI::error( $message );
    }

    throw new RuntimeException( $message );
}

function coursepress_core_lms_hash( array $definition ): string {
    return hash( 'sha256', wp_json_encode( $definition, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) );
}

function coursepress_core_lms_find_by_key( string $key ): int {
    $ids = get_posts(
        array(
            'post_type'      => array( tutor()->course_post_type, 'topics', tutor()->lesson_post_type ),
            'post_status'    => array( 'publish', 'draft', 'pending', 'future', 'private', 'trash' ),
            'posts_per_page' => 2,
            'fields'         => 'ids',
            'meta_key'       => COURSEPRESS_CORE_LMS_KEY_META,
            'meta_value'     => $key,
        )
    );

    if ( count( $ids ) > 1 ) {
        coursepress_core_lms_fail( sprintf( 'A chave LMS %s esta duplicada.', $key ) );
    }

    return empty( $ids ) ? 0 : (int) $ids[0];
}

function coursepress_core_lms_require_slug_available( string $slug, string $type, int $expected_id ): void {
    $ids = get_posts(
        array(
            'name'           => $slug,
            'post_type'      => $type,
            'post_status'    => array( 'publish', 'draft', 'pending', 'future', 'private' ),
            'posts_per_page' => 2,
            'fields'         => 'ids',
        )
    );

    foreach ( $ids as $id ) {
        if ( $expected_id !== (int) $id ) {
            coursepress_core_lms_fail( sprintf( 'O slug LMS %s ja esta em uso por um recurso nao gerenciado.', $slug ) );
        }
    }
}

function coursepress_core_lms_curriculum(): array {
    return array(
        'course' => array(
            'key'     => 'wordpress-negocios',
            'title'   => 'WordPress para Negócios: do Zero à Loja Online',
            'slug'    => 'wordpress-para-negocios-do-zero-a-loja-online',
            'content' => '<p>Curso demonstrativo da CoursePress Academy para freelancers e pequenos empreendedores que desejam criar uma presença digital com WordPress e WooCommerce.</p><p>A trilha apresenta planejamento, construção de uma loja e cuidados de publicação. O acesso completo depende de matrícula, que não é automatizada neste marco.</p>',
        ),
        'modules' => array(
            array(
                'key'     => 'fundamentos-planejamento',
                'title'   => 'Fundamentos e planejamento',
                'slug'    => 'fundamentos-e-planejamento',
                'content' => 'Base para definir objetivos, público e estrutura da presença digital.',
                'lessons' => array(
                    array( 'key' => 'boas-vindas-visao-geral', 'title' => 'Boas-vindas e visão geral', 'slug' => 'boas-vindas-e-visao-geral', 'content' => '<p>Conheça a trilha do curso e os resultados esperados. Esta aula introdutória é uma prévia pública.</p>', 'preview' => true ),
                    array( 'key' => 'planejamento-presenca-digital', 'title' => 'Planejamento da presença digital', 'slug' => 'planejamento-da-presenca-digital', 'content' => '<p>Organize objetivos, público, páginas e prioridades antes de construir a loja.</p>', 'preview' => false ),
                ),
            ),
            array(
                'key'     => 'construcao-loja',
                'title'   => 'Construção da loja',
                'slug'    => 'construcao-da-loja',
                'content' => 'Configuração técnica e comercial para uma loja com WordPress.',
                'lessons' => array(
                    array( 'key' => 'ambiente-wordpress-configuracao', 'title' => 'Ambiente WordPress e configuração essencial', 'slug' => 'ambiente-wordpress-e-configuracao-essencial', 'content' => '<p>Prepare WordPress, tema, plugins e configurações essenciais para o projeto.</p>', 'preview' => false ),
                    array( 'key' => 'catalogo-produtos-woocommerce', 'title' => 'Catálogo e produtos no WooCommerce', 'slug' => 'catalogo-e-produtos-no-woocommerce', 'content' => '<p>Estruture produtos, preços e informações que tornam o catálogo claro para o cliente.</p>', 'preview' => false ),
                ),
            ),
            array(
                'key'     => 'publicacao-evolucao',
                'title'   => 'Publicação e evolução',
                'slug'    => 'publicacao-e-evolucao',
                'content' => 'Cuidados de confiança, qualidade e evolução contínua da operação.',
                'lessons' => array(
                    array( 'key' => 'checkout-confianca-conformidade', 'title' => 'Checkout, confiança e conformidade', 'slug' => 'checkout-confianca-e-conformidade', 'content' => '<p>Revise checkout, páginas legais e sinais de confiança antes da publicação.</p>', 'preview' => false ),
                    array( 'key' => 'seguranca-desempenho-proximos-passos', 'title' => 'Segurança, desempenho e próximos passos', 'slug' => 'seguranca-desempenho-e-proximos-passos', 'content' => '<p>Planeje melhorias de segurança, desempenho, manutenção e evolução do negócio.</p>', 'preview' => false ),
                ),
            ),
        ),
    );
}

function coursepress_core_lms_require_unchanged( int $id, array $definition ): void {
    $post = get_post( $id );

    if (
        ! ( $post instanceof WP_Post ) ||
        $definition['post_type'] !== $post->post_type ||
        $definition['title'] !== $post->post_title ||
        $definition['slug'] !== $post->post_name ||
        $definition['content'] !== $post->post_content ||
        'publish' !== $post->post_status ||
        (int) $definition['parent'] !== (int) $post->post_parent ||
        (int) $definition['menu_order'] !== (int) $post->menu_order ||
        COURSEPRESS_CORE_LMS_MANAGED_VALUE !== (string) get_post_meta( $id, COURSEPRESS_CORE_LMS_MANAGED_META, true ) ||
        $definition['key'] !== (string) get_post_meta( $id, COURSEPRESS_CORE_LMS_KEY_META, true ) ||
        ! hash_equals( coursepress_core_lms_hash( $definition ), (string) get_post_meta( $id, COURSEPRESS_CORE_LMS_HASH_META, true ) )
    ) {
        coursepress_core_lms_fail( sprintf( 'O recurso LMS gerenciado %s foi alterado manualmente. Nenhuma escrita foi realizada.', $definition['key'] ) );
    }

    if ( isset( $definition['preview'] ) && $definition['preview'] !== ( '1' === (string) get_post_meta( $id, '_is_preview', true ) ) ) {
        coursepress_core_lms_fail( sprintf( 'A configuracao de previa da aula %s foi alterada manualmente.', $definition['key'] ) );
    }
}

function coursepress_core_lms_create( array $definition, int $author_id ): int {
    $id = wp_insert_post(
        array(
            'post_type'    => $definition['post_type'],
            'post_title'   => $definition['title'],
            'post_name'    => $definition['slug'],
            'post_content' => $definition['content'],
            'post_status'  => 'publish',
            'post_parent'  => $definition['parent'],
            'menu_order'   => $definition['menu_order'],
            'post_author'  => $author_id,
        ),
        true
    );

    if ( is_wp_error( $id ) || $id <= 0 ) {
        coursepress_core_lms_fail( sprintf( 'Nao foi possivel criar o recurso LMS %s.', $definition['key'] ) );
    }

    update_post_meta( $id, COURSEPRESS_CORE_LMS_MANAGED_META, COURSEPRESS_CORE_LMS_MANAGED_VALUE );
    update_post_meta( $id, COURSEPRESS_CORE_LMS_KEY_META, $definition['key'] );
    update_post_meta( $id, COURSEPRESS_CORE_LMS_HASH_META, coursepress_core_lms_hash( $definition ) );
    if ( isset( $definition['preview'] ) && $definition['preview'] ) {
        update_post_meta( $id, '_is_preview', '1' );
    }

    coursepress_core_lms_require_unchanged( $id, $definition );
    return (int) $id;
}

if ( ! defined( 'TUTOR_VERSION' ) || '4.0.5' !== TUTOR_VERSION || ! function_exists( 'tutor' ) || ! function_exists( 'tutor_utils' ) ) {
    coursepress_core_lms_fail( 'Tutor LMS 4.0.5 deve estar ativo antes de configurar o curso.' );
}

if ( ! defined( 'WC_VERSION' ) || ! function_exists( 'wc_get_product_id_by_sku' ) ) {
    coursepress_core_lms_fail( 'WooCommerce deve estar ativo antes de configurar o curso.' );
}

if ( 'pt_BR' !== get_locale() || 'pt_BR' !== determine_locale() ) {
    coursepress_core_lms_fail( 'O locale ativo deve ser pt_BR antes de configurar o Tutor LMS.' );
}

$product_id = (int) wc_get_product_id_by_sku( 'CPA-WP-NEGOCIOS-001' );
$product = $product_id > 0 ? wc_get_product( $product_id ) : false;
if ( ! ( $product instanceof WC_Product_Simple ) || 'publish' !== $product->get_status() || '297.00' !== wc_format_decimal( $product->get_regular_price(), 2 ) || '1' !== (string) $product->get_meta( '_coursepress_demo_managed', true ) ) {
    coursepress_core_lms_fail( 'O produto demonstrativo CPA-WP-NEGOCIOS-001 nao esta disponivel para vinculo.' );
}

$author_id = (int) get_current_user_id();
if ( $author_id <= 0 || ! user_can( $author_id, 'manage_options' ) ) {
    coursepress_core_lms_fail( 'A configuracao do curso exige um administrador valido.' );
}

$curriculum = coursepress_core_lms_curriculum();
$course = $curriculum['course'];
$course_definition = array( 'key' => $course['key'], 'post_type' => tutor()->course_post_type, 'title' => $course['title'], 'slug' => $course['slug'], 'content' => $course['content'], 'parent' => 0, 'menu_order' => 0 );
$course_id = coursepress_core_lms_find_by_key( $course['key'] );
coursepress_core_lms_require_slug_available( $course['slug'], tutor()->course_post_type, $course_id );

$module_ids = array();
$lesson_ids = array();
foreach ( $curriculum['modules'] as $module ) {
    $module_ids[ $module['key'] ] = coursepress_core_lms_find_by_key( $module['key'] );
    if ( 0 === $course_id && $module_ids[ $module['key'] ] > 0 ) {
        coursepress_core_lms_fail( sprintf( 'O modulo LMS %s esta sem o curso gerenciado pai.', $module['key'] ) );
    }
    coursepress_core_lms_require_slug_available( $module['slug'], 'topics', $module_ids[ $module['key'] ] );
    foreach ( $module['lessons'] as $lesson ) {
        $lesson_ids[ $lesson['key'] ] = coursepress_core_lms_find_by_key( $lesson['key'] );
        if ( 0 === $module_ids[ $module['key'] ] && $lesson_ids[ $lesson['key'] ] > 0 ) {
            coursepress_core_lms_fail( sprintf( 'A aula LMS %s esta sem o modulo gerenciado pai.', $lesson['key'] ) );
        }
        coursepress_core_lms_require_slug_available( $lesson['slug'], tutor()->lesson_post_type, $lesson_ids[ $lesson['key'] ] );
    }
}

if ( $course_id > 0 ) {
    coursepress_core_lms_require_unchanged( $course_id, $course_definition );
    if ( 'paid' !== (string) get_post_meta( $course_id, '_tutor_course_price_type', true ) || $product_id !== (int) get_post_meta( $course_id, '_tutor_course_product_id', true ) || 'yes' !== (string) get_post_meta( $product_id, '_tutor_product', true ) ) {
        coursepress_core_lms_fail( 'O vinculo WooCommerce do curso gerenciado foi alterado manualmente.' );
    }
}

foreach ( $curriculum['modules'] as $module_order => $module ) {
    $module_id = $module_ids[ $module['key'] ];
    $module_definition = array( 'key' => $module['key'], 'post_type' => 'topics', 'title' => $module['title'], 'slug' => $module['slug'], 'content' => $module['content'], 'parent' => $course_id, 'menu_order' => $module_order );
    if ( $module_id > 0 ) {
        coursepress_core_lms_require_unchanged( $module_id, $module_definition );
    }
    foreach ( $module['lessons'] as $lesson_order => $lesson ) {
        $lesson_id = $lesson_ids[ $lesson['key'] ];
        if ( $lesson_id > 0 ) {
            $lesson_definition = array( 'key' => $lesson['key'], 'post_type' => tutor()->lesson_post_type, 'title' => $lesson['title'], 'slug' => $lesson['slug'], 'content' => $lesson['content'], 'parent' => $module_id, 'menu_order' => $lesson_order, 'preview' => $lesson['preview'] );
            coursepress_core_lms_require_unchanged( $lesson_id, $lesson_definition );
        }
    }
}

/* Fim do preflight: nenhum recurso LMS ou opcao Tutor foi alterado ate este ponto. */
if ( 0 === $course_id ) {
    $course_id = coursepress_core_lms_create( $course_definition, $author_id );
    $course_action = 'created';
} else {
    $course_action = 'unchanged';
}

$module_results = array();
$lesson_results = array();
foreach ( $curriculum['modules'] as $module_order => $module ) {
    $module_definition = array( 'key' => $module['key'], 'post_type' => 'topics', 'title' => $module['title'], 'slug' => $module['slug'], 'content' => $module['content'], 'parent' => $course_id, 'menu_order' => $module_order );
    $module_id = $module_ids[ $module['key'] ];
    if ( 0 === $module_id ) {
        $module_id = coursepress_core_lms_create( $module_definition, $author_id );
        $module_action = 'created';
    } else {
        $module_action = 'unchanged';
    }
    $module_results[] = array( 'id' => $module_id, 'key' => $module['key'], 'action' => $module_action );

    foreach ( $module['lessons'] as $lesson_order => $lesson ) {
        $lesson_definition = array( 'key' => $lesson['key'], 'post_type' => tutor()->lesson_post_type, 'title' => $lesson['title'], 'slug' => $lesson['slug'], 'content' => $lesson['content'], 'parent' => $module_id, 'menu_order' => $lesson_order, 'preview' => $lesson['preview'] );
        $lesson_id = $lesson_ids[ $lesson['key'] ];
        if ( 0 === $lesson_id ) {
            $lesson_id = coursepress_core_lms_create( $lesson_definition, $author_id );
            $lesson_action = 'created';
        } else {
            $lesson_action = 'unchanged';
        }
        $lesson_results[] = array( 'id' => $lesson_id, 'key' => $lesson['key'], 'action' => $lesson_action, 'preview' => $lesson['preview'], 'url' => get_permalink( $lesson_id ) );
    }
}

tutor_utils()->update_option( 'monetize_by', 'wc' );
update_post_meta( $product_id, '_tutor_product', 'yes' );
update_post_meta( $course_id, '_tutor_course_price_type', 'paid' );
update_post_meta( $course_id, '_tutor_course_product_id', $product_id );
update_post_meta( $course_id, 'tutor_course_price', $product->get_regular_price() );

if ( 'wc' !== (string) tutor_utils()->get_option( 'monetize_by' ) || 'yes' !== (string) get_post_meta( $product_id, '_tutor_product', true ) || 'paid' !== (string) get_post_meta( $course_id, '_tutor_course_price_type', true ) || $product_id !== (int) get_post_meta( $course_id, '_tutor_course_product_id', true ) || 3 !== count( $module_results ) || 6 !== count( $lesson_results ) ) {
    coursepress_core_lms_fail( 'A fundacao do Tutor LMS nao passou na validacao final.' );
}

echo wp_json_encode(
    array(
        'course' => array( 'id' => $course_id, 'action' => $course_action, 'url' => get_permalink( $course_id ) ),
        'modules' => $module_results,
        'lessons' => $lesson_results,
        'product' => array( 'id' => $product_id, 'sku' => $product->get_sku() ),
        'monetization' => tutor_utils()->get_option( 'monetize_by' ),
    ),
    JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
) . PHP_EOL;
