<?php
/**
 * Valida as paginas transacionais oficiais do WooCommerce.
 *
 * Executado por WP-CLI via `wp eval-file` para evitar transportar PHP
 * multilinha como argumento de um comando nativo.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$options = array(
    'woocommerce_shop_page_id',
    'woocommerce_cart_page_id',
    'woocommerce_checkout_page_id',
    'woocommerce_myaccount_page_id',
);
$home = wp_parse_url( home_url() );
$page_ids = array();

foreach ( $options as $option ) {
    $page_id   = (int) get_option( $option, 0 );

    if ( $page_id <= 0 ) {
        fwrite( STDERR, "Pagina transacional sem ID valido para {$option}.\n" );
        exit( 1 );
    }

    $page_ids[ $option ] = $page_id;
}

$page_id_options = array();

foreach ( $page_ids as $option => $page_id ) {
    if ( ! isset( $page_id_options[ $page_id ] ) ) {
        $page_id_options[ $page_id ] = array();
    }

    $page_id_options[ $page_id ][] = $option;
}

foreach ( $page_id_options as $page_id => $duplicate_options ) {
    if ( count( $duplicate_options ) > 1 ) {
        fwrite( STDERR, 'Paginas transacionais compartilhadas no ID ' . $page_id . ': ' . implode( ', ', $duplicate_options ) . ".\n" );
        exit( 1 );
    }
}

foreach ( $page_ids as $option => $page_id ) {
    $page      = get_post( $page_id );
    $url       = get_permalink( $page_id );
    $url_parts = is_string( $url ) ? wp_parse_url( $url ) : false;

    if (
        $page_id <= 0 ||
        ! ( $page instanceof WP_Post ) ||
        'page' !== $page->post_type ||
        'publish' !== $page->post_status ||
        ! is_array( $url_parts ) ||
        empty( $url_parts['scheme'] ) ||
        empty( $url_parts['host'] ) ||
        empty( $home['host'] ) ||
        $url_parts['host'] !== $home['host']
    ) {
        fwrite( STDERR, "Pagina transacional invalida para {$option}.\n" );
        exit( 1 );
    }
}

echo "store-pages-valid\n";
