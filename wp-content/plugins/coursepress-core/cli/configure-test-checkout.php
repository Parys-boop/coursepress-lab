<?php
/**
 * Configura o pagamento demonstrativo por Cheque do WooCommerce.
 *
 * Executado somente por scripts/configure-test-checkout.ps1 via WP-CLI.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

const COURSEPRESS_CORE_TEST_CHECKOUT_OPTION = 'coursepress_core_test_checkout_cheque_configuration';

function coursepress_core_test_checkout_fail( string $message ): void {
    if ( defined( 'WP_CLI' ) && WP_CLI ) {
        WP_CLI::error( $message );
    }

    throw new RuntimeException( $message );
}

function coursepress_core_test_checkout_settings(): array {
    return array(
        'enabled'      => 'yes',
        'title'        => 'Pagamento demonstrativo por cheque',
        'description'  => 'Método exclusivo do ambiente local de demonstração. O pedido ficará aguardando aprovação manual; nenhum pagamento real será processado.',
        'instructions' => 'Pagamento demonstrativo local. Não envie cheque nem dados bancários. Um administrador deve aprovar o pedido ao mudar seu status para Concluído.',
    );
}

function coursepress_core_test_checkout_normalize( array $settings ): array {
    ksort( $settings );
    return $settings;
}

if ( 'local' !== wp_get_environment_type() ) {
    coursepress_core_test_checkout_fail( 'O checkout demonstrativo só pode ser configurado quando WP_ENVIRONMENT_TYPE for local.' );
}

if ( ! defined( 'WC_VERSION' ) || ! class_exists( 'WC_Gateway_Cheque' ) ) {
    coursepress_core_test_checkout_fail( 'WooCommerce com o método nativo Cheque deve estar ativo antes da configuração.' );
}

if ( ! defined( 'TUTOR_VERSION' ) || '4.0.5' !== TUTOR_VERSION || ! function_exists( 'tutor_utils' ) ) {
    coursepress_core_test_checkout_fail( 'Tutor LMS 4.0.5 deve estar ativo para validar a jornada de matrícula nativa.' );
}

$checkout_id = wc_get_page_id( 'checkout' );
$checkout    = $checkout_id > 0 ? get_post( $checkout_id ) : false;
if ( ! ( $checkout instanceof WP_Post ) || false === strpos( $checkout->post_content, 'wp:woocommerce/checkout' ) ) {
    coursepress_core_test_checkout_fail( 'A página oficial de checkout deve manter o Checkout Block nativo.' );
}

$account_options = array(
    'woocommerce_enable_guest_checkout'                 => 'no',
    'woocommerce_enable_signup_and_login_from_checkout' => 'yes',
    'woocommerce_enable_myaccount_registration'         => 'yes',
);

foreach ( $account_options as $option_name => $expected_value ) {
    if ( $expected_value !== (string) get_option( $option_name, '' ) ) {
        coursepress_core_test_checkout_fail( sprintf( 'A opção %s deve ser %s para exigir uma conta no checkout demonstrativo.', $option_name, $expected_value ) );
    }
}

$expected_settings = coursepress_core_test_checkout_settings();
$current_settings  = get_option( 'woocommerce_cheque_settings', false );
$managed_signature = hash( 'sha256', wp_json_encode( coursepress_core_test_checkout_normalize( $expected_settings ), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) );
$stored_signature  = get_option( COURSEPRESS_CORE_TEST_CHECKOUT_OPTION, false );

if ( false !== $current_settings && ! is_array( $current_settings ) ) {
    coursepress_core_test_checkout_fail( 'A configuração existente do método Cheque é inválida e não foi alterada.' );
}

if ( is_array( $current_settings ) && coursepress_core_test_checkout_normalize( $expected_settings ) !== coursepress_core_test_checkout_normalize( $current_settings ) ) {
    coursepress_core_test_checkout_fail( 'O método Cheque possui configuração manual desconhecida e não foi alterado.' );
}

if ( false !== $stored_signature && $managed_signature !== $stored_signature ) {
    coursepress_core_test_checkout_fail( 'O marcador da configuração demonstrativa é desconhecido e não foi alterado.' );
}

if ( false === $current_settings ) {
    if ( ! update_option( 'woocommerce_cheque_settings', $expected_settings ) ) {
        coursepress_core_test_checkout_fail( 'Não foi possível habilitar o método Cheque demonstrativo.' );
    }

    $action = 'configured';
} else {
    $action = 'unchanged';
}

if ( false === $stored_signature && ! update_option( COURSEPRESS_CORE_TEST_CHECKOUT_OPTION, $managed_signature ) ) {
    coursepress_core_test_checkout_fail( 'Não foi possível registrar a configuração demonstrativa do método Cheque.' );
}

$verified_settings = get_option( 'woocommerce_cheque_settings', false );
if ( ! is_array( $verified_settings ) || coursepress_core_test_checkout_normalize( $expected_settings ) !== coursepress_core_test_checkout_normalize( $verified_settings ) || $managed_signature !== get_option( COURSEPRESS_CORE_TEST_CHECKOUT_OPTION, false ) ) {
    coursepress_core_test_checkout_fail( 'A configuração do método Cheque demonstrativo não passou na validação.' );
}

echo wp_json_encode(
    array(
        'action'   => $action,
        'gateway'  => 'cheque',
        'checkout' => 'block',
        'settings' => $verified_settings,
    ),
    JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
) . PHP_EOL;
