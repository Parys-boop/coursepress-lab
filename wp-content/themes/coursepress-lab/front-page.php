<?php
/**
 * Landing page da CoursePress Academy.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$context = coursepress_lab_get_landing_context();

get_header();
?>

<main id="conteudo-principal" class="coursepress-landing">
    <section class="coursepress-hero" aria-labelledby="coursepress-hero-title">
        <div class="coursepress-shell coursepress-hero__grid">
            <div class="coursepress-hero__content">
                <p class="coursepress-eyebrow"><?php esc_html_e( 'CoursePress Academy · curso demonstrativo', 'coursepress-lab' ); ?></p>
                <h1 id="coursepress-hero-title">
                    <?php echo esc_html( $context ? $context['course_title'] : __( 'WordPress para Negócios', 'coursepress-lab' ) ); ?>
                </h1>
                <p class="coursepress-hero__lead"><?php esc_html_e( 'Uma trilha prática para planejar, estruturar e publicar uma loja online com WordPress e WooCommerce.', 'coursepress-lab' ); ?></p>

                <?php if ( $context ) : ?>
                    <div class="coursepress-hero__purchase">
                        <span><?php esc_html_e( 'Investimento demonstrativo', 'coursepress-lab' ); ?></span>
                        <strong><?php echo wp_kses_post( $context['price_html'] ); ?></strong>
                    </div>
                    <div class="coursepress-action-group">
                        <a class="coursepress-button coursepress-button--primary" href="<?php echo esc_url( $context['checkout_url'] ); ?>">
                            <?php esc_html_e( 'Ir para o checkout demonstrativo', 'coursepress-lab' ); ?>
                        </a>
                        <a class="coursepress-text-link" href="#programa"><?php esc_html_e( 'Ver programa do curso', 'coursepress-lab' ); ?></a>
                    </div>
                <?php else : ?>
                    <p class="coursepress-status-message" role="status"><?php esc_html_e( 'O curso demonstrativo está temporariamente indisponível. Tente novamente mais tarde.', 'coursepress-lab' ); ?></p>
                <?php endif; ?>
            </div>

            <div class="coursepress-course-art" aria-hidden="true">
                <div class="coursepress-course-art__window">
                    <div class="coursepress-course-art__bar"><span></span><span></span><span></span></div>
                    <div class="coursepress-course-art__body">
                        <p><?php esc_html_e( 'Trilha de aprendizado', 'coursepress-lab' ); ?></p>
                        <div class="coursepress-course-art__line coursepress-course-art__line--wide"></div>
                        <div class="coursepress-course-art__line"></div>
                        <div class="coursepress-course-art__module"><span>01</span><i></i></div>
                        <div class="coursepress-course-art__module"><span>02</span><i></i></div>
                        <div class="coursepress-course-art__module"><span>03</span><i></i></div>
                    </div>
                </div>
                <div class="coursepress-course-art__card">
                    <span><?php esc_html_e( 'Prévia disponível', 'coursepress-lab' ); ?></span>
                    <strong><?php esc_html_e( 'Comece pelo planejamento', 'coursepress-lab' ); ?></strong>
                </div>
            </div>
        </div>
    </section>

    <section class="coursepress-section" aria-labelledby="beneficios">
        <div class="coursepress-shell">
            <div class="coursepress-section-heading">
                <p class="coursepress-eyebrow"><?php esc_html_e( 'O que a trilha aborda', 'coursepress-lab' ); ?></p>
                <h2 id="beneficios"><?php esc_html_e( 'Da decisão de negócio à publicação responsável', 'coursepress-lab' ); ?></h2>
            </div>
            <div class="coursepress-benefits-grid">
                <article class="coursepress-feature-card">
                    <span class="coursepress-feature-card__number">01</span>
                    <h3><?php esc_html_e( 'Planejamento antes da ferramenta', 'coursepress-lab' ); ?></h3>
                    <p><?php esc_html_e( 'Organize objetivos, público, páginas e prioridades antes de começar a construir.', 'coursepress-lab' ); ?></p>
                </article>
                <article class="coursepress-feature-card">
                    <span class="coursepress-feature-card__number">02</span>
                    <h3><?php esc_html_e( 'Estrutura técnica com contexto', 'coursepress-lab' ); ?></h3>
                    <p><?php esc_html_e( 'Conecte ambiente, catálogo e checkout a decisões que fazem sentido para a operação.', 'coursepress-lab' ); ?></p>
                </article>
                <article class="coursepress-feature-card">
                    <span class="coursepress-feature-card__number">03</span>
                    <h3><?php esc_html_e( 'Publicação com atenção ao básico', 'coursepress-lab' ); ?></h3>
                    <p><?php esc_html_e( 'Revise confiança, conformidade, segurança e próximos passos antes de colocar a loja no ar.', 'coursepress-lab' ); ?></p>
                </article>
            </div>
        </div>
    </section>

    <section class="coursepress-section coursepress-section--tint" aria-labelledby="para-quem">
        <div class="coursepress-shell coursepress-audience">
            <div>
                <p class="coursepress-eyebrow"><?php esc_html_e( 'Para quem é', 'coursepress-lab' ); ?></p>
                <h2 id="para-quem"><?php esc_html_e( 'Uma base para quem precisa decidir e construir com clareza', 'coursepress-lab' ); ?></h2>
            </div>
            <ul class="coursepress-check-list">
                <li><?php esc_html_e( 'Freelancers que querem estruturar um projeto de WordPress para clientes ou portfólio.', 'coursepress-lab' ); ?></li>
                <li><?php esc_html_e( 'Pequenos empreendedores que desejam entender a base de uma loja online.', 'coursepress-lab' ); ?></li>
                <li><?php esc_html_e( 'Pessoas em aprendizado que procuram uma trilha curta e demonstrativa de e-commerce.', 'coursepress-lab' ); ?></li>
            </ul>
        </div>
    </section>

    <section id="programa" class="coursepress-section" aria-labelledby="programa-title">
        <div class="coursepress-shell">
            <div class="coursepress-section-heading coursepress-section-heading--split">
                <div>
                    <p class="coursepress-eyebrow"><?php esc_html_e( 'Programa do curso', 'coursepress-lab' ); ?></p>
                    <h2 id="programa-title"><?php esc_html_e( 'Três módulos, seis aulas e uma prévia aberta', 'coursepress-lab' ); ?></h2>
                </div>
                <?php if ( $context ) : ?>
                    <a class="coursepress-text-link" href="<?php echo esc_url( $context['course_url'] ); ?>"><?php esc_html_e( 'Abrir página do curso', 'coursepress-lab' ); ?></a>
                <?php endif; ?>
            </div>

            <?php if ( $context ) : ?>
                <div class="coursepress-curriculum-grid">
                    <?php foreach ( $context['curriculum'] as $module_index => $module ) : ?>
                        <article class="coursepress-module-card">
                            <p class="coursepress-module-card__index"><?php echo esc_html( sprintf( '%02d', $module_index + 1 ) ); ?></p>
                            <h3><?php echo esc_html( $module['title'] ); ?></h3>
                            <ol>
                                <?php foreach ( $module['lessons'] as $lesson ) : ?>
                                    <?php $is_preview = '1' === (string) get_post_meta( $lesson->ID, '_is_preview', true ); ?>
                                    <li>
                                        <a href="<?php echo esc_url( get_permalink( $lesson ) ); ?>"><?php echo esc_html( get_the_title( $lesson ) ); ?></a>
                                        <span class="coursepress-lesson-status"><?php echo esc_html( $is_preview ? __( 'Prévia', 'coursepress-lab' ) : __( 'Protegida', 'coursepress-lab' ) ); ?></span>
                                    </li>
                                <?php endforeach; ?>
                            </ol>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php else : ?>
                <p class="coursepress-status-message" role="status"><?php esc_html_e( 'O programa do curso não está disponível no momento.', 'coursepress-lab' ); ?></p>
            <?php endif; ?>
        </div>
    </section>

    <section class="coursepress-section coursepress-section--dark" aria-labelledby="acesso-title">
        <div class="coursepress-shell coursepress-access-grid">
            <div>
                <p class="coursepress-eyebrow coursepress-eyebrow--light"><?php esc_html_e( 'Acesso ao conteúdo', 'coursepress-lab' ); ?></p>
                <h2 id="acesso-title"><?php esc_html_e( 'Explore a prévia. O restante acompanha a matrícula.', 'coursepress-lab' ); ?></h2>
                <p><?php esc_html_e( 'A aula de boas-vindas está aberta para conhecer a trilha. As demais aulas permanecem protegidas até a compra demonstrativa ser aprovada.', 'coursepress-lab' ); ?></p>
            </div>
            <div class="coursepress-access-steps">
                <article><span>1</span><h3><?php esc_html_e( 'Crie ou acesse sua conta', 'coursepress-lab' ); ?></h3><p><?php esc_html_e( 'O checkout demonstrativo exige uma conta para associar o acesso ao aluno.', 'coursepress-lab' ); ?></p></article>
                <article><span>2</span><h3><?php esc_html_e( 'Faça o pedido de teste', 'coursepress-lab' ); ?></h3><p><?php esc_html_e( 'O método Cheque é exclusivamente demonstrativo e não processa pagamentos reais.', 'coursepress-lab' ); ?></p></article>
                <article><span>3</span><h3><?php esc_html_e( 'Aguarde a aprovação', 'coursepress-lab' ); ?></h3><p><?php esc_html_e( 'Ao concluir manualmente o pedido, a integração nativa libera a matrícula e as aulas protegidas.', 'coursepress-lab' ); ?></p></article>
            </div>
        </div>
    </section>

    <section class="coursepress-section" aria-labelledby="demonstrativo-title">
        <div class="coursepress-shell coursepress-demo-panel">
            <div class="coursepress-demo-panel__label"><?php esc_html_e( 'Projeto de portfólio', 'coursepress-lab' ); ?></div>
            <div>
                <h2 id="demonstrativo-title"><?php esc_html_e( 'Uma experiência de compra e entrega construída para estudo', 'coursepress-lab' ); ?></h2>
                <p><?php esc_html_e( 'A CoursePress Academy é um ambiente educacional local. Produto, checkout, aprovação e matrícula existem para demonstrar a integração entre WordPress, WooCommerce e Tutor LMS.', 'coursepress-lab' ); ?></p>
            </div>
        </div>
    </section>

    <section class="coursepress-section coursepress-section--tint" aria-labelledby="faq-title">
        <div class="coursepress-shell coursepress-faq-layout">
            <div>
                <p class="coursepress-eyebrow"><?php esc_html_e( 'Perguntas frequentes', 'coursepress-lab' ); ?></p>
                <h2 id="faq-title"><?php esc_html_e( 'Antes de iniciar o checkout demonstrativo', 'coursepress-lab' ); ?></h2>
            </div>
            <div class="coursepress-faq-list">
                <details><summary><?php esc_html_e( 'O pagamento é real?', 'coursepress-lab' ); ?></summary><p><?php esc_html_e( 'Não. O método Cheque é habilitado apenas para demonstrar um pedido aguardando aprovação manual no ambiente local.', 'coursepress-lab' ); ?></p></details>
                <details><summary><?php esc_html_e( 'Por que preciso de uma conta?', 'coursepress-lab' ); ?></summary><p><?php esc_html_e( 'A conta identifica o aluno para que a integração nativa associe a matrícula ao pedido aprovado.', 'coursepress-lab' ); ?></p></details>
                <details><summary><?php esc_html_e( 'O que posso acessar antes da compra?', 'coursepress-lab' ); ?></summary><p><?php esc_html_e( 'A primeira aula é uma prévia pública. O restante da trilha é liberado após a aprovação do pedido demonstrativo.', 'coursepress-lab' ); ?></p></details>
            </div>
        </div>
    </section>

    <section class="coursepress-final-cta" aria-labelledby="cta-final-title">
        <div class="coursepress-shell">
            <p class="coursepress-eyebrow coursepress-eyebrow--light"><?php esc_html_e( 'Próximo passo', 'coursepress-lab' ); ?></p>
            <h2 id="cta-final-title"><?php esc_html_e( 'Conheça o fluxo completo de uma venda de curso em ambiente de teste.', 'coursepress-lab' ); ?></h2>
            <?php if ( $context ) : ?>
                <a class="coursepress-button coursepress-button--light" href="<?php echo esc_url( $context['checkout_url'] ); ?>"><?php esc_html_e( 'Começar checkout demonstrativo', 'coursepress-lab' ); ?></a>
            <?php endif; ?>
        </div>
    </section>
</main>

<?php get_footer(); ?>
