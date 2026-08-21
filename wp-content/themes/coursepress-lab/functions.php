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
 * Retorna o ID de contêiner GTM local quando ele atende ao formato aceito.
 */
function coursepress_lab_get_gtm_container_id(): string {
    if ( ! defined( 'COURSEPRESS_GTM_CONTAINER_ID' ) ) {
        return '';
    }

    $container_id = (string) COURSEPRESS_GTM_CONTAINER_ID;

    return 1 === preg_match( '/\AGTM-[A-Z0-9]+\z/', $container_id ) ? $container_id : '';
}

/**
 * Imprime a parte head do único contêiner GTM configurado.
 */
function coursepress_lab_render_gtm_head(): void {
    static $rendered = false;

    if ( $rendered ) {
        return;
    }

    $container_id = coursepress_lab_get_gtm_container_id();

    if ( '' === $container_id ) {
        return;
    }

    $rendered   = true;
    $encoded_id = rawurlencode( $container_id );
    $json_id    = wp_json_encode( $container_id );
    ?>
    <!-- Google Tag Manager -->
    <script>
    (function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
    new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
    j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
    'https://www.googletagmanager.com/gtm.js?id=<?php echo esc_js( $encoded_id ); ?>'+dl;f.parentNode.insertBefore(j,f);
    })(window,document,'script','dataLayer',<?php echo $json_id; ?>);
    </script>
    <!-- End Google Tag Manager -->
    <?php
}
add_action( 'wp_head', 'coursepress_lab_render_gtm_head', 1 );

/**
 * Imprime o fallback noscript do mesmo contêiner GTM configurado.
 */
function coursepress_lab_render_gtm_body(): void {
    static $rendered = false;

    if ( $rendered ) {
        return;
    }

    $container_id = coursepress_lab_get_gtm_container_id();

    if ( '' === $container_id ) {
        return;
    }

    $rendered      = true;
    $noscript_url  = 'https://www.googletagmanager.com/ns.html?id=' . rawurlencode( $container_id );
    ?>
    <!-- Google Tag Manager (noscript) -->
    <noscript><iframe src="<?php echo esc_url( $noscript_url ); ?>" height="0" width="0" style="display:none;visibility:hidden" title="<?php echo esc_attr__( 'Google Tag Manager', 'coursepress-lab' ); ?>"></iframe></noscript>
    <!-- End Google Tag Manager (noscript) -->
    <?php
}
add_action( 'wp_body_open', 'coursepress_lab_render_gtm_body', 1 );

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
