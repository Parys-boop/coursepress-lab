<?php
/**
 * Template de fallback do tema.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

get_header();
?>

<main id="conteudo-principal" class="coursepress-main">
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

<?php get_footer(); ?>
