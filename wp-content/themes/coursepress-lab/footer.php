<?php
/**
 * Rodapé compartilhado do tema CoursePress Lab.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$privacy_url = get_privacy_policy_url();
$terms_url   = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'terms' ) : '';
?>
<footer class="coursepress-footer">
    <div class="coursepress-shell coursepress-footer__inner">
        <div>
            <a class="coursepress-brand coursepress-brand--footer" href="<?php echo esc_url( home_url( '/' ) ); ?>">
                <span class="coursepress-brand__mark" aria-hidden="true">CP</span>
                <span><?php echo esc_html( get_bloginfo( 'name' ) ); ?></span>
            </a>
            <p><?php esc_html_e( 'Ambiente educacional de portfólio para demonstrar uma operação de cursos com WordPress.', 'coursepress-lab' ); ?></p>
        </div>
        <div class="coursepress-footer__meta">
            <nav aria-label="<?php esc_attr_e( 'Links legais', 'coursepress-lab' ); ?>">
                <ul class="coursepress-footer__links">
                    <?php if ( $privacy_url ) : ?>
                        <li><a href="<?php echo esc_url( $privacy_url ); ?>"><?php esc_html_e( 'Política de Privacidade', 'coursepress-lab' ); ?></a></li>
                    <?php endif; ?>
                    <?php if ( $terms_url ) : ?>
                        <li><a href="<?php echo esc_url( $terms_url ); ?>"><?php esc_html_e( 'Termos e Condições', 'coursepress-lab' ); ?></a></li>
                    <?php endif; ?>
                </ul>
            </nav>
            <small>&copy; <?php echo esc_html( wp_date( 'Y' ) ); ?> <?php echo esc_html( get_bloginfo( 'name' ) ); ?></small>
        </div>
    </div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
