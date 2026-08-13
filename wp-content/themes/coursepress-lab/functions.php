<?php
/**
 * Configuração inicial do tema CoursePress Lab.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function coursepress_lab_setup(): void {
    add_theme_support( 'title-tag' );
    add_theme_support( 'post-thumbnails' );
    add_theme_support( 'html5', array( 'search-form', 'comment-form', 'gallery', 'caption', 'style', 'script' ) );
}
add_action( 'after_setup_theme', 'coursepress_lab_setup' );

function coursepress_lab_assets(): void {
    wp_enqueue_style(
        'coursepress-lab',
        get_stylesheet_uri(),
        array(),
        wp_get_theme()->get( 'Version' )
    );
}
add_action( 'wp_enqueue_scripts', 'coursepress_lab_assets' );
