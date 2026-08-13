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

<header class="coursepress-header">
    <div class="coursepress-shell">
        <a href="<?php echo esc_url( home_url( '/' ) ); ?>">
            <?php bloginfo( 'name' ); ?>
        </a>
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
