<?php
/**
 * Template principal do tema.
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

<header class="coursepress-header" data-coursepress-header>
    <div class="coursepress-shell">
        <a class="coursepress-brand" href="<?php echo esc_url( home_url( '/' ) ); ?>">
            <?php echo esc_html( get_bloginfo( 'name' ) ); ?>
        </a>
        <button
            class="coursepress-menu-toggle"
            type="button"
            aria-controls="coursepress-primary-navigation"
            aria-expanded="false"
            aria-label="<?php esc_attr_e( 'Abrir menu', 'coursepress-lab' ); ?>"
        >
            <?php esc_html_e( 'Menu', 'coursepress-lab' ); ?>
        </button>
        <nav
            id="coursepress-primary-navigation"
            class="coursepress-primary-navigation"
            aria-label="<?php esc_attr_e( 'Navegação principal', 'coursepress-lab' ); ?>"
        >
            <?php
            wp_nav_menu(
                array(
                    'theme_location' => 'primary',
                    'container'      => false,
                    'menu_class'     => 'coursepress-menu',
                    'fallback_cb'    => false,
                    'depth'          => 1,
                )
            );
            ?>
        </nav>
    </div>
</header>

<main class="coursepress-main">
    <div class="coursepress-shell">
        <?php if ( have_posts() ) : ?>
            <?php while ( have_posts() ) : ?>
                <?php the_post(); ?>
                <article <?php post_class(); ?>>
                    <h1><?php the_title(); ?></h1>
                    <?php the_content(); ?>
                </article>
            <?php endwhile; ?>
        <?php else : ?>
            <h1><?php esc_html_e( 'CoursePress Lab', 'coursepress-lab' ); ?></h1>
            <p><?php esc_html_e( 'O ambiente está pronto para a próxima etapa.', 'coursepress-lab' ); ?></p>
        <?php endif; ?>
    </div>
</main>

<footer class="coursepress-footer">
    <div class="coursepress-shell">
        <small>
            &copy; <?php echo esc_html( wp_date( 'Y' ) ); ?>
            <?php bloginfo( 'name' ); ?>
        </small>
    </div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
