<?php
/**
 * Configuração inicial do tema CoursePress Lab.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function coursepress_lab_setup(): void {
    add_theme_support( 'title-tag' );
    add_theme_support( 'post-thumbnails' );
    add_theme_support( 'html5', array( 'search-form', 'comment-form', 'gallery', 'caption', 'style', 'script' ) );
    register_nav_menus(
        array(
            'primary' => esc_html__( 'Navegação principal', 'coursepress-lab' ),
        )
    );
}
add_action( 'after_setup_theme', 'coursepress_lab_setup' );

function coursepress_lab_assets(): void {
    wp_enqueue_style(
        'coursepress-lab',
        get_stylesheet_uri(),
        array(),
        wp_get_theme()->get( 'Version' )
    );

    wp_enqueue_script(
        'coursepress-lab-navigation',
        get_template_directory_uri() . '/assets/js/navigation.js',
        array(),
        wp_get_theme()->get( 'Version' ),
        true
    );
}
add_action( 'wp_enqueue_scripts', 'coursepress_lab_assets' );

/**
 * Obtém os dados publicados que a landing page pode apresentar com segurança.
 *
 * @return array<string, mixed>|null
 */
function coursepress_lab_get_landing_context(): ?array {
    static $context = null;
    static $resolved = false;

    if ( $resolved ) {
        return $context;
    }

    $resolved = true;

    if ( ! function_exists( 'wc_get_product_id_by_sku' ) || ! function_exists( 'tutor' ) ) {
        return null;
    }

    $product_id = (int) wc_get_product_id_by_sku( 'CPA-WP-NEGOCIOS-001' );
    $product    = $product_id > 0 ? wc_get_product( $product_id ) : false;

    if ( ! $product instanceof WC_Product || 'publish' !== $product->get_status() || ! $product->is_purchasable() ) {
        return null;
    }

    $course_post_type = tutor()->course_post_type;
    $course_ids       = get_posts(
        array(
            'fields'         => 'ids',
            'meta_key'       => '_tutor_course_product_id',
            'meta_value'     => (string) $product_id,
            'post_status'    => 'publish',
            'post_type'      => $course_post_type,
            'posts_per_page' => 2,
        )
    );

    if ( 1 !== count( $course_ids ) ) {
        return null;
    }

    $course_id = (int) $course_ids[0];
    $modules   = get_posts(
        array(
            'orderby'        => 'menu_order',
            'order'          => 'ASC',
            'post_parent'    => $course_id,
            'post_status'    => 'publish',
            'post_type'      => 'topics',
            'posts_per_page' => -1,
        )
    );

    if ( empty( $modules ) ) {
        return null;
    }

    $curriculum = array();

    foreach ( $modules as $module ) {
        $lessons = get_posts(
            array(
                'orderby'        => 'menu_order',
                'order'          => 'ASC',
                'post_parent'    => $module->ID,
                'post_status'    => 'publish',
                'post_type'      => tutor()->lesson_post_type,
                'posts_per_page' => -1,
            )
        );

        $curriculum[] = array(
            'id'      => (int) $module->ID,
            'lessons' => $lessons,
            'title'   => get_the_title( $module ),
        );
    }

    $context = array(
        'checkout_url' => add_query_arg(
            array(
                '_wpnonce'              => wp_create_nonce( 'coursepress_lab_checkout_' . $product_id ),
                'coursepress_checkout' => '1',
                'product_id'            => $product_id,
            ),
            home_url( '/' )
        ),
        'course_id'    => $course_id,
        'course_title' => get_the_title( $course_id ),
        'course_url'   => get_permalink( $course_id ),
        'curriculum'   => $curriculum,
        'price_html'   => wc_price( $product->get_price() ),
        'product_id'   => $product_id,
        'product_url'  => $product->get_permalink(),
    );

    return $context;
}

/**
 * Adiciona o único produto da landing ao carrinho e segue para o checkout.
 */
function coursepress_lab_handle_landing_checkout(): void {
    if ( ! isset( $_GET['coursepress_checkout'], $_GET['product_id'], $_GET['_wpnonce'] ) ) {
        return;
    }

    $product_id = absint( wp_unslash( $_GET['product_id'] ) );
    $nonce      = sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) );

    if ( ! wp_verify_nonce( $nonce, 'coursepress_lab_checkout_' . $product_id ) || ! function_exists( 'wc_get_product_id_by_sku' ) || $product_id !== (int) wc_get_product_id_by_sku( 'CPA-WP-NEGOCIOS-001' ) ) {
        wp_safe_redirect( home_url( '/' ) );
        exit;
    }

    $product = wc_get_product( $product_id );

    if ( ! $product instanceof WC_Product || 'publish' !== $product->get_status() || ! $product->is_purchasable() || ! function_exists( 'WC' ) ) {
        wp_safe_redirect( home_url( '/' ) );
        exit;
    }

    if ( null === WC()->cart ) {
        wc_load_cart();
    }

    if ( ! WC()->cart || ! WC()->cart->add_to_cart( $product_id ) ) {
        wp_safe_redirect( home_url( '/' ) );
        exit;
    }

    wp_safe_redirect( wc_get_checkout_url() );
    exit;
}
add_action( 'template_redirect', 'coursepress_lab_handle_landing_checkout' );
