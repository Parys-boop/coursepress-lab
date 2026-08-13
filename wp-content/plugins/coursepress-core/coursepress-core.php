<?php
/**
 * Plugin Name: CoursePress Core
 * Description: Funcionalidades autorais do projeto CoursePress Lab.
 * Version: 0.1.0
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
