<?php
/**
 * Plugin Name: CoursePress Core
 * Description: Funcionalidades autorais do projeto CoursePress Lab.
 * Version: 0.2.0
 * Author: Arthur Pires
 * Text Domain: coursepress-core
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Exibe o ano atual sem depender do conteúdo do tema.
 *
 * Uso: [coursepress_year]
 */
function coursepress_core_current_year(): string {
    return esc_html( wp_date( 'Y' ) );
}
add_shortcode( 'coursepress_year', 'coursepress_core_current_year' );

const COURSEPRESS_CORE_LEAD_POST_TYPE      = 'coursepress_lead';
const COURSEPRESS_CORE_LEAD_NAME_META      = '_coursepress_lead_name';
const COURSEPRESS_CORE_LEAD_EMAIL_META     = '_coursepress_lead_email';
const COURSEPRESS_CORE_LEAD_CONSENT_META   = '_coursepress_lead_consent';
const COURSEPRESS_CORE_LEAD_CAPTURE_ACTION = 'coursepress_capture_lead';

/**
 * Registra leads como recurso interno, sem superfície pública.
 */
function coursepress_core_register_lead_post_type(): void {
    $administrator_capability = 'manage_options';

    register_post_type(
        COURSEPRESS_CORE_LEAD_POST_TYPE,
        array(
            'capabilities'          => array(
                'create_posts'           => $administrator_capability,
                'delete_others_posts'    => $administrator_capability,
                'delete_post'            => $administrator_capability,
                'delete_posts'           => $administrator_capability,
                'delete_private_posts'   => $administrator_capability,
                'delete_published_posts' => $administrator_capability,
                'edit_others_posts'      => $administrator_capability,
                'edit_post'              => $administrator_capability,
                'edit_posts'             => $administrator_capability,
                'edit_private_posts'     => $administrator_capability,
                'edit_published_posts'   => $administrator_capability,
                'publish_posts'          => $administrator_capability,
                'read_post'              => $administrator_capability,
                'read_private_posts'     => $administrator_capability,
            ),
            'can_export'            => false,
            'delete_with_user'      => false,
            'exclude_from_search'   => true,
            'has_archive'           => false,
            'labels'                => array(
                'menu_name'          => __( 'Leads', 'coursepress-core' ),
                'name'               => __( 'Leads', 'coursepress-core' ),
                'singular_name'      => __( 'Lead', 'coursepress-core' ),
            ),
            'map_meta_cap'          => false,
            'public'                => false,
            'publicly_queryable'    => false,
            'query_var'             => false,
            'rewrite'               => false,
            'show_in_admin_bar'     => false,
            'show_in_menu'          => true,
            'show_in_nav_menus'     => false,
            'show_in_rest'          => false,
            'show_ui'               => true,
            'supports'              => array( 'title' ),
        )
    );
}
add_action( 'init', 'coursepress_core_register_lead_post_type' );

/**
 * Define as colunas administrativas sem expor dados fora do painel protegido.
 *
 * @param array<string, string> $columns Colunas existentes.
 * @return array<string, string>
 */
function coursepress_core_lead_columns( array $columns ): array {
    return array(
        'cb'                       => $columns['cb'],
        'coursepress_lead_name'    => __( 'Nome', 'coursepress-core' ),
        'coursepress_lead_email'   => __( 'E-mail', 'coursepress-core' ),
        'coursepress_lead_consent' => __( 'Consentimento', 'coursepress-core' ),
        'date'                     => __( 'Data', 'coursepress-core' ),
    );
}
add_filter( 'manage_' . COURSEPRESS_CORE_LEAD_POST_TYPE . '_posts_columns', 'coursepress_core_lead_columns' );

/**
 * Exibe metadados de leads na listagem administrativa.
 *
 * @param string $column  Nome da coluna.
 * @param int    $post_id ID do lead.
 */
function coursepress_core_render_lead_column( string $column, int $post_id ): void {
    if ( 'coursepress_lead_name' === $column ) {
        echo esc_html( (string) get_post_meta( $post_id, COURSEPRESS_CORE_LEAD_NAME_META, true ) );
    }

    if ( 'coursepress_lead_email' === $column ) {
        echo esc_html( (string) get_post_meta( $post_id, COURSEPRESS_CORE_LEAD_EMAIL_META, true ) );
    }

    if ( 'coursepress_lead_consent' === $column ) {
        esc_html_e( 'Confirmado', 'coursepress-core' );
    }
}
add_action( 'manage_' . COURSEPRESS_CORE_LEAD_POST_TYPE . '_posts_custom_column', 'coursepress_core_render_lead_column', 10, 2 );

/**
 * Retorna a URL da landing com um status permitido de captura.
 *
 * @param string $status Status da captura.
 * @return string
 */
function coursepress_core_lead_redirect_url( string $status ): string {
    $allowed_statuses = array( 'error', 'success' );
    $status           = in_array( $status, $allowed_statuses, true ) ? $status : 'error';

    return add_query_arg( 'lead_capture', $status, home_url( '/' ) ) . '#captacao';
}

/**
 * Redireciona após uma tentativa de captura, preservando o padrão PRG.
 *
 * @param string $status Status da captura.
 * @return never
 */
function coursepress_core_finish_lead_capture( string $status ): void {
    wp_safe_redirect( coursepress_core_lead_redirect_url( $status ) );
    exit;
}

/**
 * Processa a captura de um lead da landing page.
 */
function coursepress_core_capture_lead(): void {
    if ( 'POST' !== strtoupper( (string) ( $_SERVER['REQUEST_METHOD'] ?? '' ) ) ) {
        coursepress_core_finish_lead_capture( 'error' );
    }

    $nonce = isset( $_POST['coursepress_lead_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['coursepress_lead_nonce'] ) ) : '';

    if ( ! wp_verify_nonce( $nonce, COURSEPRESS_CORE_LEAD_CAPTURE_ACTION ) ) {
        coursepress_core_finish_lead_capture( 'error' );
    }

    $honeypot = isset( $_POST['coursepress_lead_website'] ) ? sanitize_text_field( wp_unslash( $_POST['coursepress_lead_website'] ) ) : '';

    if ( '' !== trim( $honeypot ) ) {
        coursepress_core_finish_lead_capture( 'error' );
    }

    $name  = isset( $_POST['coursepress_lead_name'] ) ? trim( sanitize_text_field( wp_unslash( $_POST['coursepress_lead_name'] ) ) ) : '';
    $email = isset( $_POST['coursepress_lead_email'] ) ? strtolower( trim( sanitize_email( wp_unslash( $_POST['coursepress_lead_email'] ) ) ) ) : '';
    $consent = isset( $_POST['coursepress_lead_consent'] ) ? sanitize_text_field( wp_unslash( $_POST['coursepress_lead_consent'] ) ) : '';

    if ( '' === $name || ! is_email( $email ) || '1' !== $consent ) {
        coursepress_core_finish_lead_capture( 'error' );
    }

    $existing_lead_ids = get_posts(
        array(
            'fields'         => 'ids',
            'meta_key'       => COURSEPRESS_CORE_LEAD_EMAIL_META,
            'meta_value'     => $email,
            'no_found_rows'  => true,
            'post_status'    => 'private',
            'post_type'      => COURSEPRESS_CORE_LEAD_POST_TYPE,
            'posts_per_page' => 1,
        )
    );

    if ( ! empty( $existing_lead_ids ) ) {
        coursepress_core_finish_lead_capture( 'success' );
    }

    $lead_id = wp_insert_post(
        array(
            'post_author'  => 0,
            'post_content' => '',
            'post_excerpt' => '',
            'post_status'  => 'private',
            'post_title'   => __( 'Lead de captação', 'coursepress-core' ),
            'post_type'    => COURSEPRESS_CORE_LEAD_POST_TYPE,
        ),
        true
    );

    if ( is_wp_error( $lead_id ) || ! is_int( $lead_id ) || $lead_id <= 0 ) {
        coursepress_core_finish_lead_capture( 'error' );
    }

    $saved = add_post_meta( $lead_id, COURSEPRESS_CORE_LEAD_NAME_META, $name, true )
        && add_post_meta( $lead_id, COURSEPRESS_CORE_LEAD_EMAIL_META, $email, true )
        && add_post_meta( $lead_id, COURSEPRESS_CORE_LEAD_CONSENT_META, '1', true );

    if ( ! $saved ) {
        wp_delete_post( $lead_id, true );
        coursepress_core_finish_lead_capture( 'error' );
    }

    coursepress_core_finish_lead_capture( 'success' );
}
add_action( 'admin_post_' . COURSEPRESS_CORE_LEAD_CAPTURE_ACTION, 'coursepress_core_capture_lead' );
add_action( 'admin_post_nopriv_' . COURSEPRESS_CORE_LEAD_CAPTURE_ACTION, 'coursepress_core_capture_lead' );
