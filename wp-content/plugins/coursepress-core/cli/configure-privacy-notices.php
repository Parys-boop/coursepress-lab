<?php
/**
 * Configura os avisos oficiais de privacidade do WooCommerce em pt_BR.
 *
 * Este arquivo e executado por `wp eval-file` apos a instalacao do pacote
 * de idioma do WooCommerce. Os textos armazenados pelo WooCommerce mantem o
 * placeholder [privacy_policy]; o HTML publico substitui esse placeholder
 * pelo link da politica de privacidade.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

const COURSEPRESS_CORE_PRIVACY_NOTICE_HASHES_OPTION = 'coursepress_core_privacy_notice_hashes';

/**
 * Encerra a execucao com uma mensagem adequada ao WP-CLI.
 *
 * @param string $message Mensagem de erro.
 * @return never
 */
function coursepress_core_privacy_notice_fail( string $message ) {
    if ( defined( 'WP_CLI' ) && WP_CLI ) {
        WP_CLI::error( $message );
    }

    throw new RuntimeException( $message );
}

if ( ! function_exists( 'wc_get_privacy_policy_text' ) ) {
    coursepress_core_privacy_notice_fail( 'O WooCommerce deve estar ativo para configurar os avisos de privacidade.' );
}

if ( 'pt_BR' !== determine_locale() ) {
    coursepress_core_privacy_notice_fail( 'O locale ativo deve ser pt_BR antes de configurar os avisos de privacidade.' );
}

$notices = array(
    'woocommerce_registration_privacy_policy_text' => array(
        'type'            => 'registration',
        'source_template' => 'Your personal data will be used to support your experience throughout this website, to manage access to your account, and for other purposes described in our %s.',
    ),
    'woocommerce_checkout_privacy_policy_text'     => array(
        'type'            => 'checkout',
        'source_template' => 'Your personal data will be used to process your order, support your experience throughout this website, and for other purposes described in our %s.',
    ),
);

$stored_hashes = get_option( COURSEPRESS_CORE_PRIVACY_NOTICE_HASHES_OPTION, array() );
if ( ! is_array( $stored_hashes ) ) {
    coursepress_core_privacy_notice_fail( 'O registro de avisos de privacidade gerenciados e invalido.' );
}

$next_hashes = $stored_hashes;
$changes     = array();
$results     = array();

global $wpdb;

foreach ( $notices as $option_name => $notice ) {
    $source_text   = sprintf( $notice['source_template'], '[privacy_policy]' );
    $official_text = sprintf( __( $notice['source_template'], 'woocommerce' ), '[privacy_policy]' );

    if ( $official_text === $source_text || false === strpos( $official_text, '[privacy_policy]' ) ) {
        coursepress_core_privacy_notice_fail( sprintf( 'A traducao oficial pt_BR para %s nao esta disponivel ou e invalida.', $option_name ) );
    }

    $current_value = get_option( $option_name, null );
    $option_exists = null !== $wpdb->get_var(
        $wpdb->prepare(
            "SELECT option_id FROM {$wpdb->options} WHERE option_name = %s LIMIT 1",
            $option_name
        )
    );
    $managed_hash  = isset( $stored_hashes[ $option_name ]['value_hash'] ) ? $stored_hashes[ $option_name ]['value_hash'] : null;
    $current_hash  = is_string( $current_value ) ? hash( 'sha256', $current_value ) : null;
    $status        = '';

    if ( ! $option_exists ) {
        $changes[ $option_name ] = $official_text;
        $status                  = 'initialized_missing';
    } elseif ( $current_value === $source_text ) {
        $changes[ $option_name ] = $official_text;
        $status                  = 'migrated_legacy_default';
    } elseif ( $current_value === $official_text ) {
        $status = 'official_current';
    } elseif ( is_string( $managed_hash ) && is_string( $current_hash ) && hash_equals( $managed_hash, $current_hash ) ) {
        $changes[ $option_name ] = $official_text;
        $status                  = 'updated_managed_value';
    } else {
        coursepress_core_privacy_notice_fail( sprintf( 'O aviso %s contem texto nao gerenciado. Nenhuma opcao foi alterada.', $option_name ) );
    }

    $next_hashes[ $option_name ] = array(
        'source_hash' => hash( 'sha256', $source_text ),
        'value_hash'  => hash( 'sha256', $official_text ),
    );

    $results[ $option_name ] = array(
        'type'           => $notice['type'],
        'status'         => $status,
        'stored_before'  => $current_value,
        'official_text'  => $official_text,
        'changed'        => array_key_exists( $option_name, $changes ),
    );
}

foreach ( $changes as $option_name => $official_text ) {
    if ( ! update_option( $option_name, $official_text ) ) {
        coursepress_core_privacy_notice_fail( sprintf( 'Nao foi possivel atualizar %s.', $option_name ) );
    }
}

if ( $next_hashes !== $stored_hashes && ! update_option( COURSEPRESS_CORE_PRIVACY_NOTICE_HASHES_OPTION, $next_hashes ) ) {
    coursepress_core_privacy_notice_fail( 'Nao foi possivel registrar os avisos de privacidade gerenciados.' );
}

foreach ( $notices as $option_name => $notice ) {
    $stored_value = get_option( $option_name, null );
    $returned     = wc_get_privacy_policy_text( $notice['type'] );

    if ( $stored_value !== $results[ $option_name ]['official_text'] || $returned !== $stored_value ) {
        coursepress_core_privacy_notice_fail( sprintf( 'O WooCommerce nao retornou o valor armazenado para %s.', $option_name ) );
    }

    $results[ $option_name ]['stored_after'] = $stored_value;
    $results[ $option_name ]['wc_returned']  = $returned;
}

echo wp_json_encode(
    array(
        'changed' => ! empty( $changes ) || $next_hashes !== $stored_hashes,
        'notices' => $results,
    ),
    JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
) . PHP_EOL;
