<?php
/**
 * Valida o transporte de e-mail local e envia uma única mensagem de teste.
 *
 * Executado por WP-CLI via `wp eval-file`, evitando PHP multilinha em argumentos.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! function_exists( 'coursepress_core_local_mail_transport_enabled' ) || ! coursepress_core_local_mail_transport_enabled() ) {
    WP_CLI::error( 'O transporte Mailpit local não está habilitado.' );
}

$recipient = 'mailpit-probe@coursepress.test';
$subject   = 'Teste de transporte — CoursePress Academy';
$message   = implode(
    "\n",
    array(
        'Esta é uma mensagem de teste do transporte local da CoursePress Academy.',
        'Ela deve ser capturada exclusivamente pelo Mailpit.',
    )
);

if ( ! wp_mail( $recipient, $subject, $message, array( 'Content-Type: text/plain; charset=UTF-8' ) ) ) {
    WP_CLI::error( 'Não foi possível enviar a mensagem de teste ao Mailpit.' );
}

WP_CLI::line(
    wp_json_encode(
        array(
            'from'      => COURSEPRESS_CORE_LOCAL_MAIL_FROM,
            'recipient' => $recipient,
            'subject'   => $subject,
        )
    )
);
