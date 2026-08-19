<?php
/**
 * Configura a identidade e a estrutura institucional da CoursePress Academy.
 *
 * Executado somente por scripts/configure-site-identity.ps1 via WP-CLI.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$managed_meta_key   = '_coursepress_identity_managed';
$managed_meta_value = '1';
$menu_item_key      = '_coursepress_identity_menu_item_key';

$page_title   = 'Início';
$page_slug    = 'inicio';
$page_content = implode(
    "\n",
    array(
        '<p>Bem-vindo à CoursePress Academy.</p>',
        '<p>Um ambiente educacional de portfólio para demonstrar a criação e a venda de cursos online com WordPress e WooCommerce.</p>',
        '<p>Explore a loja e acesse sua conta para acompanhar a estrutura do projeto.</p>',
    )
);

$menu_name = 'Navegação principal';
$menu_slug = 'navegacao-principal';

$store_pages = array(
    'shop'    => array(
        'option' => 'woocommerce_shop_page_id',
        'title'  => 'Loja',
    ),
    'account' => array(
        'option' => 'woocommerce_myaccount_page_id',
        'title'  => 'Minha conta',
    ),
);

/**
 * Interrompe a automação com uma mensagem clara antes de sobrescrever conteúdo.
 *
 * @param string $message Mensagem de erro.
 */
function coursepress_core_identity_fail( string $message ): void {
    throw new RuntimeException( $message );
}

/**
 * Confirma que um recurso existente pertence à automação.
 *
 * @param int    $resource_id ID do recurso.
 * @param string $resource    Tipo do recurso para a mensagem.
 * @param string $meta_type   Tipo de metadado: post ou term.
 * @param string $meta_key    Chave do marcador.
 * @param string $meta_value  Valor esperado do marcador.
 */
function coursepress_core_identity_require_managed( int $resource_id, string $resource, string $meta_type, string $meta_key, string $meta_value ): void {
    $stored_value = 'post' === $meta_type
        ? (string) get_post_meta( $resource_id, $meta_key, true )
        : (string) get_term_meta( $resource_id, $meta_key, true );

    if ( $meta_value !== $stored_value ) {
        coursepress_core_identity_fail( sprintf( '%s existente nao e gerenciado pela automacao.', $resource ) );
    }
}

global $wpdb;

/*
 * Preflight somente leitura: toda colisão e propriedade é validada antes das
 * operações de criação, sincronização, exclusão de itens gerenciados ou opções.
 */
$page_ids = array_map(
    'intval',
    $wpdb->get_col(
        $wpdb->prepare(
            "SELECT ID FROM {$wpdb->posts} WHERE post_name = %s AND post_type != %s AND post_status != %s",
            $page_slug,
            'revision',
            'trash'
        )
    )
);

$page_id     = 0;
$page_action = 'criada';

if ( ! empty( $page_ids ) ) {
    if ( 1 !== count( $page_ids ) ) {
        coursepress_core_identity_fail( 'O slug da pagina Início esta em uso por mais de um conteudo.' );
    }

    $page_id = $page_ids[0];
    $page    = get_post( $page_id );

    if ( ! ( $page instanceof WP_Post ) || 'page' !== $page->post_type ) {
        coursepress_core_identity_fail( 'O slug da pagina Início esta em uso por outro tipo de conteudo.' );
    }

    coursepress_core_identity_require_managed( $page_id, 'A pagina Início', 'post', $managed_meta_key, $managed_meta_value );
    $page_action = 'inalterada';
}

$store_page_ids = array();

foreach ( $store_pages as $key => $store_page ) {
    $store_page_id   = (int) get_option( $store_page['option'] );
    $store_page_post = get_post( $store_page_id );

    if ( ! ( $store_page_post instanceof WP_Post ) || 'page' !== $store_page_post->post_type || 'trash' === $store_page_post->post_status ) {
        coursepress_core_identity_fail( sprintf( 'A pagina %s nao esta disponivel.', $store_page['title'] ) );
    }

    $store_page_ids[ $key ] = $store_page_id;
}

$menu        = wp_get_nav_menu_object( $menu_slug );
$menu_id     = 0;
$menu_action = 'criado';

if ( $menu ) {
    $menu_id = (int) $menu->term_id;
    coursepress_core_identity_require_managed( $menu_id, 'O menu Navegação principal', 'term', $managed_meta_key, $managed_meta_value );
    $menu_action = 'inalterado';
}

$locations = get_nav_menu_locations();

if ( ! empty( $locations['primary'] ) && ( 0 === $menu_id || (int) $locations['primary'] !== $menu_id ) ) {
    coursepress_core_identity_fail( 'A localizacao principal esta atribuida a outro menu nao gerenciado.' );
}

$existing_menu_items = array();

if ( $menu_id > 0 ) {
    $existing_menu_items = wp_get_nav_menu_items( $menu_id, array( 'post_status' => 'any' ) );

    if ( false === $existing_menu_items ) {
        coursepress_core_identity_fail( 'Nao foi possivel ler os itens do menu Navegação principal.' );
    }

    foreach ( $existing_menu_items as $existing_menu_item ) {
        coursepress_core_identity_require_managed(
            (int) $existing_menu_item->ID,
            'Um item do menu Navegação principal',
            'post',
            $managed_meta_key,
            $managed_meta_value
        );
    }
}

/* Fim do preflight. */

if ( 0 === $page_id ) {
    $page_id = wp_insert_post(
        array(
            'post_content' => $page_content,
            'post_name'    => $page_slug,
            'post_status'  => 'publish',
            'post_title'   => $page_title,
            'post_type'    => 'page',
        ),
        true
    );

    if ( is_wp_error( $page_id ) ) {
        coursepress_core_identity_fail( 'Nao foi possivel criar a pagina Início.' );
    }

    $page_id = (int) $page_id;
    update_post_meta( $page_id, $managed_meta_key, $managed_meta_value );
} else {
    $page_updates = array();

    if ( $page_title !== get_the_title( $page_id ) ) {
        $page_updates['post_title'] = $page_title;
    }

    if ( $page_slug !== get_post_field( 'post_name', $page_id ) ) {
        $page_updates['post_name'] = $page_slug;
    }

    if ( 'publish' !== get_post_status( $page_id ) ) {
        $page_updates['post_status'] = 'publish';
    }

    if ( bin2hex( $page_content ) !== bin2hex( (string) get_post_field( 'post_content', $page_id ) ) ) {
        $page_updates['post_content'] = $page_content;
    }

    if ( ! empty( $page_updates ) ) {
        $page_updates['ID'] = $page_id;
        $updated_page_id    = wp_update_post( $page_updates, true );

        if ( is_wp_error( $updated_page_id ) ) {
            coursepress_core_identity_fail( 'Nao foi possivel sincronizar a pagina Início.' );
        }

        $page_action = 'atualizada';
    }
}

if ( 0 === $menu_id ) {
    $created_menu = wp_insert_term(
        $menu_name,
        'nav_menu',
        array(
            'slug' => $menu_slug,
        )
    );

    if ( is_wp_error( $created_menu ) ) {
        coursepress_core_identity_fail( 'Nao foi possivel criar o menu Navegação principal.' );
    }

    $menu_id = (int) $created_menu['term_id'];
    update_term_meta( $menu_id, $managed_meta_key, $managed_meta_value );
} elseif ( $menu_name !== $menu->name ) {
    $updated_menu = wp_update_term(
        $menu_id,
        'nav_menu',
        array(
            'name' => $menu_name,
        )
    );

    if ( is_wp_error( $updated_menu ) ) {
        coursepress_core_identity_fail( 'Nao foi possivel sincronizar o menu Navegação principal.' );
    }

    $menu_action = 'atualizado';
}

$expected_menu_items = array(
    'home'    => array(
        'object_id' => $page_id,
        'title'     => $page_title,
    ),
    'shop'    => array(
        'object_id' => $store_page_ids['shop'],
        'title'     => $store_pages['shop']['title'],
    ),
    'account' => array(
        'object_id' => $store_page_ids['account'],
        'title'     => $store_pages['account']['title'],
    ),
);

$items_by_key = array();

foreach ( $existing_menu_items as $existing_menu_item ) {
    $existing_key = (string) get_post_meta( $existing_menu_item->ID, $menu_item_key, true );

    if ( ! isset( $items_by_key[ $existing_key ] ) ) {
        $items_by_key[ $existing_key ] = array();
    }

    $items_by_key[ $existing_key ][] = $existing_menu_item;
}

foreach ( $items_by_key as $existing_key => $items ) {
    if ( ! isset( $expected_menu_items[ $existing_key ] ) ) {
        foreach ( $items as $item ) {
            wp_delete_post( $item->ID, true );
        }

        $menu_action = 'atualizado';
        continue;
    }

    while ( count( $items ) > 1 ) {
        $duplicate_item = array_pop( $items );
        wp_delete_post( $duplicate_item->ID, true );
        $menu_action = 'atualizado';
    }

    $items_by_key[ $existing_key ] = $items;
}

$menu_position = 1;

foreach ( $expected_menu_items as $item_key => $expected_menu_item ) {
    $existing_item = isset( $items_by_key[ $item_key ][0] ) ? $items_by_key[ $item_key ][0] : null;
    $item_id       = $existing_item ? (int) $existing_item->ID : 0;
    $updated_item_id = wp_update_nav_menu_item(
        $menu_id,
        $item_id,
        array(
            'menu-item-object-id' => $expected_menu_item['object_id'],
            'menu-item-object'    => 'page',
            'menu-item-parent-id' => 0,
            'menu-item-position'  => $menu_position,
            'menu-item-status'    => 'publish',
            'menu-item-title'     => $expected_menu_item['title'],
            'menu-item-type'      => 'post_type',
        )
    );

    if ( is_wp_error( $updated_item_id ) || $updated_item_id <= 0 ) {
        coursepress_core_identity_fail( 'Nao foi possivel sincronizar um item do menu Navegação principal.' );
    }

    $updated_item_id = (int) $updated_item_id;
    update_post_meta( $updated_item_id, $managed_meta_key, $managed_meta_value );
    update_post_meta( $updated_item_id, $menu_item_key, $item_key );
    $menu_position++;
}

update_option( 'show_on_front', 'page' );
update_option( 'page_on_front', $page_id );

$locations['primary'] = $menu_id;
set_theme_mod( 'nav_menu_locations', $locations );

$verified_page       = get_post( $page_id );
$verified_menu       = wp_get_nav_menu_object( $menu_id );
$verified_locations  = get_nav_menu_locations();
$verified_menu_items = wp_get_nav_menu_items( $menu_id, array( 'post_status' => 'publish' ) );
$validation_errors   = array();

if ( ! ( $verified_page instanceof WP_Post ) || $page_title !== $verified_page->post_title || $page_slug !== $verified_page->post_name || 'publish' !== $verified_page->post_status || bin2hex( $page_content ) !== bin2hex( $verified_page->post_content ) ) {
    $validation_errors[] = 'pagina';
}

if ( $managed_meta_value !== (string) get_post_meta( $page_id, $managed_meta_key, true ) ) {
    $validation_errors[] = 'marcador-pagina';
}

if ( ! $verified_menu || $menu_name !== $verified_menu->name || $menu_slug !== $verified_menu->slug || $managed_meta_value !== (string) get_term_meta( $menu_id, $managed_meta_key, true ) ) {
    $validation_errors[] = 'menu';
}

if ( 'page' !== get_option( 'show_on_front' ) || $page_id !== (int) get_option( 'page_on_front' ) || $menu_id !== (int) ( $verified_locations['primary'] ?? 0 ) ) {
    $validation_errors[] = 'atribuicoes';
}

if ( ! is_array( $verified_menu_items ) || count( $expected_menu_items ) !== count( $verified_menu_items ) ) {
    $validation_errors[] = 'itens-menu';
} else {
    $expected_keys = array_keys( $expected_menu_items );

    foreach ( array_values( $verified_menu_items ) as $index => $verified_menu_item ) {
        $expected_key  = $expected_keys[ $index ];
        $expected_item = $expected_menu_items[ $expected_key ];

        if (
            $expected_key !== (string) get_post_meta( $verified_menu_item->ID, $menu_item_key, true ) ||
            $managed_meta_value !== (string) get_post_meta( $verified_menu_item->ID, $managed_meta_key, true ) ||
            (int) $expected_item['object_id'] !== (int) $verified_menu_item->object_id ||
            'page' !== $verified_menu_item->object ||
            'post_type' !== $verified_menu_item->type ||
            $expected_item['title'] !== $verified_menu_item->title ||
            0 !== (int) $verified_menu_item->menu_item_parent
        ) {
            $validation_errors[] = 'itens-menu';
            break;
        }
    }
}

if ( ! empty( $validation_errors ) ) {
    coursepress_core_identity_fail( 'A identidade do site nao foi validada: ' . implode( ', ', array_unique( $validation_errors ) ) . '.' );
}

printf(
    "Identidade %s. Pagina Início: %d | Menu Navegação principal: %s.\n",
    $page_action,
    $page_id,
    $menu_action
);
