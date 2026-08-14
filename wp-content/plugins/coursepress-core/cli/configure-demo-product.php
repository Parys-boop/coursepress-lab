<?php
/**
 * Configura o produto demonstrativo gerenciado pela CoursePress Academy.
 *
 * Executado somente por scripts/configure-demo-product.ps1 via WP-CLI.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$product_name              = 'WordPress para Negócios: do Zero à Loja Online';
$product_sku               = 'CPA-WP-NEGOCIOS-001';
$product_slug              = 'wordpress-para-negocios-do-zero-a-loja-online';
$product_status            = 'draft';
$product_regular_price     = '297.00';
$product_short_description = 'Curso demonstrativo para freelancers e pequenos empreendedores que querem criar uma loja online com WordPress.';
$product_description       = implode(
    "\n\n",
    array(
        'WordPress para Negócios: do Zero à Loja Online é um curso demonstrativo da CoursePress Academy para freelancers e pequenos empreendedores que desejam planejar, construir e colocar no ar uma presença digital com recursos de comércio eletrônico.',
        'Ao longo do curso, o aluno percorre fundamentos do WordPress, configuração do ambiente, personalização visual, estruturação de produtos, WooCommerce, checkout, segurança, desempenho e preparação para publicação.',
        'Este produto faz parte de um projeto de portfólio executado em ambiente local. As compras e os pagamentos são exclusivamente demonstrativos e não representam uma oferta comercial real.',
    )
);
$category_name      = 'Cursos de WordPress';
$category_slug      = 'cursos-de-wordpress';
$managed_meta_key   = '_coursepress_demo_managed';
$managed_meta_value = '1';

$product_id = (int) wc_get_product_id_by_sku( $product_sku );

if ( $product_id > 0 ) {
    $product = wc_get_product( $product_id );

    if ( ! ( $product instanceof WC_Product_Simple ) ) {
        throw new RuntimeException( 'O SKU existe, mas nao pertence a um produto simples.' );
    }

    if ( $managed_meta_value !== (string) $product->get_meta( $managed_meta_key, true ) ) {
        throw new RuntimeException( 'O SKU existe, mas o produto nao e gerenciado pela automacao.' );
    }

    if ( ! wc_product_has_unique_sku( $product->get_id(), $product_sku ) ) {
        throw new RuntimeException( 'O SKU do produto demonstrativo nao e unico.' );
    }

    $product_action = 'inalterado';
} else {
    if ( ! wc_product_has_unique_sku( 0, $product_sku ) ) {
        throw new RuntimeException( 'O SKU do produto demonstrativo ja esta em uso.' );
    }

    $product_action = 'criado';
}

global $wpdb;

$slug_post_ids = $wpdb->get_col(
    $wpdb->prepare(
        "SELECT ID FROM {$wpdb->posts} WHERE post_name = %s AND post_type != %s AND post_status != %s",
        $product_slug,
        'revision',
        'trash'
    )
);

foreach ( $slug_post_ids as $slug_post_id ) {
    if ( $product_id !== (int) $slug_post_id ) {
        throw new RuntimeException( 'O slug do produto demonstrativo ja esta em uso por outro conteudo.' );
    }
}

$category = get_term_by( 'slug', $category_slug, 'product_cat' );

if ( false === $category ) {
    $created_category = wp_insert_term(
        $category_name,
        'product_cat',
        array(
            'slug' => $category_slug,
        )
    );

    if ( is_wp_error( $created_category ) ) {
        throw new RuntimeException( 'Nao foi possivel criar a categoria do produto demonstrativo.' );
    }

    $category_id = (int) $created_category['term_id'];
    update_term_meta( $category_id, $managed_meta_key, $managed_meta_value );
    $category_action = 'criada';
} else {
    $category_id = (int) $category->term_id;

    if ( $managed_meta_value !== (string) get_term_meta( $category_id, $managed_meta_key, true ) ) {
        throw new RuntimeException( 'A categoria esperada existe, mas nao e gerenciada pela automacao.' );
    }

    if ( $category_name !== $category->name ) {
        $updated_category = wp_update_term(
            $category_id,
            'product_cat',
            array(
                'name' => $category_name,
            )
        );

        if ( is_wp_error( $updated_category ) ) {
            throw new RuntimeException( 'Nao foi possivel atualizar a categoria do produto demonstrativo.' );
        }

        $category_action = 'atualizada';
    } else {
        $category_action = 'inalterada';
    }
}

if ( 'criado' === $product_action ) {
    $product = new WC_Product_Simple();
    $product->set_sku( $product_sku );
    $product->update_meta_data( $managed_meta_key, $managed_meta_value );
}

$product_updates = array();

if ( $product_name !== $product->get_name() ) {
    $product->set_name( $product_name );
    $product_updates[] = 'nome';
}

if ( $product_slug !== $product->get_slug() ) {
    $product->set_slug( $product_slug );
    $product_updates[] = 'slug';
}

if ( $product_status !== $product->get_status() ) {
    $product->set_status( $product_status );
    $product_updates[] = 'status';
}

if ( $product_regular_price !== wc_format_decimal( $product->get_regular_price(), 2 ) ) {
    $product->set_regular_price( $product_regular_price );
    $product_updates[] = 'preco';
}

if ( ! $product->get_virtual() ) {
    $product->set_virtual( true );
    $product_updates[] = 'virtual';
}

if ( $product->get_downloadable() ) {
    $product->set_downloadable( false );
    $product_updates[] = 'download';
}

if ( $product->get_manage_stock() ) {
    $product->set_manage_stock( false );
    $product_updates[] = 'estoque';
}

if ( 'instock' !== $product->get_stock_status() ) {
    $product->set_stock_status( 'instock' );
    $product_updates[] = 'disponibilidade';
}

if ( 'visible' !== $product->get_catalog_visibility() ) {
    $product->set_catalog_visibility( 'visible' );
    $product_updates[] = 'visibilidade';
}

if ( bin2hex( $product_short_description ) !== bin2hex( $product->get_short_description() ) ) {
    $product->set_short_description( $product_short_description );
    $product_updates[] = 'descricao-curta';
}

if ( bin2hex( $product_description ) !== bin2hex( $product->get_description() ) ) {
    $product->set_description( $product_description );
    $product_updates[] = 'descricao-completa';
}

$current_category_ids  = array_map( 'intval', $product->get_category_ids() );
$expected_category_ids = array( $category_id );
sort( $current_category_ids );

if ( $expected_category_ids !== $current_category_ids ) {
    $product->set_category_ids( $expected_category_ids );
    $product_updates[] = 'categoria';
}

if ( 'criado' === $product_action || ! empty( $product_updates ) ) {
    $product_id = $product->save();

    if ( 'inalterado' === $product_action ) {
        $product_action = 'atualizado';
    }
}

$product = wc_get_product( $product_id );

if ( ! ( $product instanceof WC_Product_Simple ) ) {
    throw new RuntimeException( 'O produto demonstrativo nao foi salvo como produto simples.' );
}

$verified_category_ids = array_map( 'intval', $product->get_category_ids() );
sort( $verified_category_ids );

$validation_errors = array();

if ( $product_name !== $product->get_name() ) {
    $validation_errors[] = 'nome';
}

if ( $product_sku !== $product->get_sku() ) {
    $validation_errors[] = 'SKU';
}

if ( $product_slug !== $product->get_slug() ) {
    $validation_errors[] = 'slug';
}

if ( $product_status !== $product->get_status() ) {
    $validation_errors[] = 'status';
}

if ( $product_regular_price !== wc_format_decimal( $product->get_regular_price(), 2 ) ) {
    $validation_errors[] = 'preco';
}

if ( ! $product->get_virtual() || $product->get_downloadable() || $product->get_manage_stock() || 'instock' !== $product->get_stock_status() ) {
    $validation_errors[] = 'configuracao';
}

if ( $expected_category_ids !== $verified_category_ids ) {
    $validation_errors[] = 'categoria';
}

if ( bin2hex( $product_short_description ) !== bin2hex( $product->get_short_description() ) ) {
    $validation_errors[] = 'descricao-curta';
}

if ( bin2hex( $product_description ) !== bin2hex( $product->get_description() ) ) {
    $validation_errors[] = 'descricao-completa';
}

if ( $managed_meta_value !== (string) $product->get_meta( $managed_meta_key, true ) ) {
    $validation_errors[] = 'marcador-produto';
}

if ( $managed_meta_value !== (string) get_term_meta( $category_id, $managed_meta_key, true ) ) {
    $validation_errors[] = 'marcador-categoria';
}

if ( ! empty( $validation_errors ) ) {
    throw new RuntimeException( 'O produto demonstrativo nao foi validado: ' . implode( ', ', $validation_errors ) . '.' );
}

printf(
    "Produto %s. ID: %d | SKU: %s | Status: %s | Categoria: %s.\n",
    $product_action,
    $product->get_id(),
    $product->get_sku(),
    $product->get_status(),
    $category_action
);
