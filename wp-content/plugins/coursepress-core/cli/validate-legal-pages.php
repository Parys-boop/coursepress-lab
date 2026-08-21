<?php
/**
 * Valida as paginas legais gerenciadas e suas opcoes oficiais.
 *
 * Executado por WP-CLI via `wp eval-file` para evitar transportar PHP
 * multilinha como argumento de um comando nativo.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$expected = array(
    'privacy' => array(
        'slug'   => 'politica-de-privacidade',
        'option' => 'wp_page_for_privacy_policy',
    ),
    'terms'   => array(
        'slug'   => 'termos-e-condicoes',
        'option' => 'woocommerce_terms_page_id',
    ),
);
$results  = array();

foreach ( $expected as $key => $definition ) {
    $page_ids = get_posts(
        array(
            'post_type'      => 'page',
            'post_status'    => 'any',
            'posts_per_page' => 2,
            'fields'         => 'ids',
            'meta_key'       => '_coursepress_legal_key',
            'meta_value'     => $key,
        )
    );

    if ( 1 !== count( $page_ids ) ) {
        fwrite( STDERR, "Quantidade invalida de paginas legais para {$key}.\n" );
        exit( 1 );
    }

    $page_id   = (int) $page_ids[0];
    $page      = get_post( $page_id );
    $url       = get_permalink( $page_id );
    $url_parts = is_string( $url ) ? wp_parse_url( $url ) : false;

    if (
        ! ( $page instanceof WP_Post ) ||
        'page' !== $page->post_type ||
        'publish' !== $page->post_status ||
        $definition['slug'] !== $page->post_name ||
        '1' !== (string) get_post_meta( $page->ID, '_coursepress_legal_managed', true ) ||
        $key !== (string) get_post_meta( $page->ID, '_coursepress_legal_key', true ) ||
        ! hash_equals( hash( 'sha256', $page->post_content ), (string) get_post_meta( $page->ID, '_coursepress_legal_content_sha256', true ) ) ||
        (int) get_option( $definition['option'], 0 ) !== (int) $page->ID ||
        ! is_array( $url_parts ) ||
        empty( $url_parts['scheme'] ) ||
        empty( $url_parts['host'] )
    ) {
        fwrite( STDERR, "Pagina legal invalida para {$key}.\n" );
        exit( 1 );
    }

    $results[ $key ] = array(
        'id' => (int) $page->ID,
    );
}

echo wp_json_encode( array( 'pages' => $results ), JSON_UNESCAPED_SLASHES ) . PHP_EOL;
