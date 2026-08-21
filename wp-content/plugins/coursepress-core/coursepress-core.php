<?php
/**
 * Plugin Name: CoursePress Core
 * Description: Funcionalidades autorais do projeto CoursePress Lab.
 * Version: 0.3.0
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
const COURSEPRESS_CORE_LEAD_CONFIRMATION_SENT_AT_META = '_coursepress_lead_confirmation_sent_at';
const COURSEPRESS_CORE_LEAD_CAPTURE_ACTION = 'coursepress_capture_lead';
const COURSEPRESS_CORE_LOCAL_MAIL_ENABLED  = 'COURSEPRESS_LOCAL_MAIL_ENABLED';
const COURSEPRESS_CORE_LOCAL_MAIL_FROM      = 'no-reply@coursepress.test';
const COURSEPRESS_CORE_LOCAL_MAIL_FROM_NAME = 'CoursePress Academy';

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
        'coursepress_lead_confirmation' => __( 'Confirmação', 'coursepress-core' ),
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

    if ( 'coursepress_lead_confirmation' === $column ) {
        $sent_at = (string) get_post_meta( $post_id, COURSEPRESS_CORE_LEAD_CONFIRMATION_SENT_AT_META, true );

        echo esc_html( $sent_at ? $sent_at : __( 'Pendente', 'coursepress-core' ) );
    }
}
add_action( 'manage_' . COURSEPRESS_CORE_LEAD_POST_TYPE . '_posts_custom_column', 'coursepress_core_render_lead_column', 10, 2 );

/**
 * Informa se o transporte SMTP demonstrativo pode ser usado neste ambiente.
 */
function coursepress_core_local_mail_transport_enabled(): bool {
    return 'local' === wp_get_environment_type() && '1' === (string) getenv( COURSEPRESS_CORE_LOCAL_MAIL_ENABLED );
}

/**
 * Define um remetente válido apenas para o transporte demonstrativo local.
 *
 * @param string $from Remetente original do WordPress.
 * @return string
 */
function coursepress_core_local_mail_from( string $from ): string {
    return coursepress_core_local_mail_transport_enabled() ? COURSEPRESS_CORE_LOCAL_MAIL_FROM : $from;
}
add_filter( 'wp_mail_from', 'coursepress_core_local_mail_from' );

/**
 * Define o nome do remetente apenas para o transporte demonstrativo local.
 *
 * @param string $from_name Nome original do remetente.
 * @return string
 */
function coursepress_core_local_mail_from_name( string $from_name ): string {
    return coursepress_core_local_mail_transport_enabled() ? COURSEPRESS_CORE_LOCAL_MAIL_FROM_NAME : $from_name;
}
add_filter( 'wp_mail_from_name', 'coursepress_core_local_mail_from_name' );

/**
 * Direciona o PHPMailer ao Mailpit interno quando habilitado explicitamente.
 *
 * @param PHPMailer\PHPMailer\PHPMailer $phpmailer Instância configurada pelo WordPress.
 */
function coursepress_core_configure_local_mail_transport( $phpmailer ): void {
    if ( ! coursepress_core_local_mail_transport_enabled() ) {
        return;
    }

    $phpmailer->isSMTP();
    $phpmailer->Host        = 'mailpit';
    $phpmailer->Port        = 1025;
    $phpmailer->SMTPAuth    = false;
    $phpmailer->SMTPAutoTLS = false;
    $phpmailer->SMTPSecure  = '';
    $phpmailer->From        = COURSEPRESS_CORE_LOCAL_MAIL_FROM;
    $phpmailer->FromName    = COURSEPRESS_CORE_LOCAL_MAIL_FROM_NAME;
}
add_action( 'phpmailer_init', 'coursepress_core_configure_local_mail_transport' );

/**
 * Envia a confirmação transacional de um lead quando ela ainda estiver pendente.
 *
 * @param int    $lead_id ID do lead privado.
 * @param string $email   Endereço normalizado do lead.
 * @return bool
 */
function coursepress_core_maybe_send_lead_confirmation( int $lead_id, string $email ): bool {
    if (
        ! coursepress_core_local_mail_transport_enabled()
        || ! is_email( $email )
        || '' !== (string) get_post_meta( $lead_id, COURSEPRESS_CORE_LEAD_CONFIRMATION_SENT_AT_META, true )
    ) {
        return false;
    }

    $subject = 'Confirmação de interesse — CoursePress Academy';
    $message = implode(
        "\n",
        array(
            'Seu interesse foi registrado na CoursePress Academy.',
            '',
            'Este é um ambiente educacional e demonstrativo.',
            'Nenhuma oferta comercial ou pagamento real está associado a esta mensagem.',
        )
    );
    $headers = array( 'Content-Type: text/plain; charset=UTF-8' );

    if ( ! wp_mail( $email, $subject, $message, $headers ) ) {
        return false;
    }

    return false !== update_post_meta( $lead_id, COURSEPRESS_CORE_LEAD_CONFIRMATION_SENT_AT_META, current_time( 'mysql', true ) );
}

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
        coursepress_core_maybe_send_lead_confirmation( (int) $existing_lead_ids[0], $email );
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

    coursepress_core_maybe_send_lead_confirmation( $lead_id, $email );
    coursepress_core_finish_lead_capture( 'success' );
}
add_action( 'admin_post_' . COURSEPRESS_CORE_LEAD_CAPTURE_ACTION, 'coursepress_core_capture_lead' );
add_action( 'admin_post_nopriv_' . COURSEPRESS_CORE_LEAD_CAPTURE_ACTION, 'coursepress_core_capture_lead' );
