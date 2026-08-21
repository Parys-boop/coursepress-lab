<?php
/**
 * Cabeçalho compartilhado do tema CoursePress Lab.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<a class="coursepress-skip-link" href="#conteudo-principal"><?php esc_html_e( 'Pular para o conteúdo', 'coursepress-lab' ); ?></a>

<header class="coursepress-header" data-coursepress-header>
    <div class="coursepress-shell coursepress-header__inner">
        <a class="coursepress-brand" href="<?php echo esc_url( home_url( '/' ) ); ?>">
            <span class="coursepress-brand__mark" aria-hidden="true">CP</span>
            <span><?php echo esc_html( get_bloginfo( 'name' ) ); ?></span>
        </a>
        <button
            class="coursepress-menu-toggle"
            type="button"
            aria-controls="coursepress-primary-navigation"
            aria-expanded="false"
            data-coursepress-menu-toggle
        >
            <span class="screen-reader-text"><?php esc_html_e( 'Alternar menu de navegação', 'coursepress-lab' ); ?></span>
            <span aria-hidden="true" class="coursepress-menu-toggle__icon"></span>
        </button>
        <nav
            id="coursepress-primary-navigation"
            class="coursepress-primary-navigation"
            aria-label="<?php esc_attr_e( 'Navegação principal', 'coursepress-lab' ); ?>"
            data-coursepress-primary-navigation
        >
            <?php
            wp_nav_menu(
                array(
                    'container'      => false,
                    'depth'          => 1,
                    'fallback_cb'    => false,
                    'menu_class'     => 'coursepress-menu',
                    'theme_location' => 'primary',
                )
            );
            ?>
        </nav>
    </div>
</header>
