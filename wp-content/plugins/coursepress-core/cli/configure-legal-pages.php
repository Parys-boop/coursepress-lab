<?php
/**
 * Configura as paginas legais gerenciadas da CoursePress Academy.
 *
 * Executado por WP-CLI. Este arquivo protege qualquer conteudo que nao tenha
 * sido criado pela automacao e so sincroniza uma pagina gerenciada quando o
 * hash registrado ainda corresponde ao seu conteudo atual.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

const COURSEPRESS_CORE_LEGAL_MANAGED_META  = '_coursepress_legal_managed';
const COURSEPRESS_CORE_LEGAL_KEY_META      = '_coursepress_legal_key';
const COURSEPRESS_CORE_LEGAL_CONTENT_META  = '_coursepress_legal_content_sha256';
const COURSEPRESS_CORE_LEGAL_MANAGED_VALUE = '1';

/**
 * Interrompe a automacao antes de qualquer sobrescrita insegura.
 *
 * @param string $message Mensagem de erro.
 * @return never
 */
function coursepress_core_legal_fail( string $message ) {
    if ( defined( 'WP_CLI' ) && WP_CLI ) {
        WP_CLI::error( $message );
    }

    throw new RuntimeException( $message );
}

/**
 * Retorna o conteudo legal deterministico de uma pagina.
 *
 * @param string $key Chave da pagina legal.
 * @return array{title: string, slug: string, content: string, option: string}
 */
function coursepress_core_legal_definition( string $key ): array {
    $definitions = array(
        'privacy' => array(
            'title'   => 'Política de Privacidade',
            'slug'    => 'politica-de-privacidade',
            'content' => implode(
                "\n",
                array(
                    '<p>Este é um ambiente educacional e demonstrativo da CoursePress Academy. Não processa pagamentos reais e não constitui uma oferta comercial.</p>',
                    '<h2>Finalidade desta página</h2>',
                    '<p>Esta página apresenta, de forma demonstrativa, a estrutura de uma política de privacidade para as funcionalidades de conta e de checkout usadas no projeto.</p>',
                    '<h2>Uso no ambiente demonstrativo</h2>',
                    '<p>As funcionalidades são usadas exclusivamente para estudo, portfólio e validação técnica. Não há operação comercial nem processamento de pagamentos reais.</p>',
                    '<h2>Atualizações</h2>',
                    '<p>O conteúdo desta página é mantido pela automação do projeto para que o ambiente local possa ser reproduzido de forma consistente.</p>',
                )
            ),
            'option'  => 'wp_page_for_privacy_policy',
        ),
        'terms'   => array(
            'title'   => 'Termos e Condições',
            'slug'    => 'termos-e-condicoes',
            'content' => implode(
                "\n",
                array(
                    '<p>Este é um ambiente educacional e demonstrativo da CoursePress Academy. Não processa pagamentos reais e não constitui uma oferta comercial.</p>',
                    '<h2>Finalidade do ambiente</h2>',
                    '<p>O projeto demonstra a configuração técnica de uma loja de cursos com WordPress e WooCommerce para fins de estudo e portfólio.</p>',
                    '<h2>Uso demonstrativo</h2>',
                    '<p>Produtos, conta, carrinho e finalização de compra existem apenas para demonstrar fluxos locais. Não há venda, contratação ou pagamento real.</p>',
                    '<h2>Atualizações</h2>',
                    '<p>O conteúdo desta página é mantido pela automação do projeto para que o ambiente local possa ser reproduzido de forma consistente.</p>',
                )
            ),
            'option'  => 'woocommerce_terms_page_id',
        ),
    );

    if ( ! isset( $definitions[ $key ] ) ) {
        coursepress_core_legal_fail( 'Chave de pagina legal desconhecida.' );
    }

    return $definitions[ $key ];
}

/**
 * Localiza uma unica pagina, inclusive em status nao publicados, pela chave.
 *
 * @param string $key Chave da pagina legal.
 * @return int
 */
function coursepress_core_legal_find_page_by_key( string $key ): int {
    global $wpdb;

    $ids = array_map(
        'intval',
        $wpdb->get_col(
            $wpdb->prepare(
                "SELECT DISTINCT posts.ID
                FROM {$wpdb->posts} AS posts
                INNER JOIN {$wpdb->postmeta} AS meta ON meta.post_id = posts.ID
                WHERE posts.post_type = 'page' AND meta.meta_key = %s AND meta.meta_value = %s",
                COURSEPRESS_CORE_LEGAL_KEY_META,
                $key
            )
        )
    );

    if ( 1 < count( $ids ) ) {
        coursepress_core_legal_fail( sprintf( 'Mais de uma pagina legal usa a chave %s.', $key ) );
    }

    return empty( $ids ) ? 0 : $ids[0];
}

/**
 * Exige que uma pagina localizada pela chave pertença a automacao.
 *
 * @param int    $page_id ID da pagina.
 * @param string $key Chave esperada.
 * @return WP_Post
 */
function coursepress_core_legal_require_managed_page( int $page_id, string $key ): WP_Post {
    $page = get_post( $page_id );

    if ( ! ( $page instanceof WP_Post ) || 'page' !== $page->post_type ) {
        coursepress_core_legal_fail( sprintf( 'A pagina legal %s nao esta disponivel.', $key ) );
    }

    if ( COURSEPRESS_CORE_LEGAL_MANAGED_VALUE !== (string) get_post_meta( $page_id, COURSEPRESS_CORE_LEGAL_MANAGED_META, true ) ) {
        coursepress_core_legal_fail( sprintf( 'A pagina legal %s nao pertence a automacao.', $key ) );
    }

    if ( $key !== (string) get_post_meta( $page_id, COURSEPRESS_CORE_LEGAL_KEY_META, true ) ) {
        coursepress_core_legal_fail( sprintf( 'A pagina legal %s tem uma chave invalida.', $key ) );
    }

    $stored_hash = (string) get_post_meta( $page_id, COURSEPRESS_CORE_LEGAL_CONTENT_META, true );
    $current_hash = hash( 'sha256', $page->post_content );

    if ( ! preg_match( '/^[a-f0-9]{64}$/', $stored_hash ) || ! hash_equals( $stored_hash, $current_hash ) ) {
        coursepress_core_legal_fail( sprintf( 'O conteudo da pagina legal %s foi alterado fora da automacao.', $key ) );
    }

    return $page;
}

/**
 * Verifica se o slug esperado esta livre, exceto para a pagina alvo.
 *
 * @param string $slug Slug esperado.
 * @param int    $expected_id ID que pode usar o slug.
 */
function coursepress_core_legal_require_slug_available( string $slug, int $expected_id ): void {
    global $wpdb;

    $ids = array_map(
        'intval',
        $wpdb->get_col(
            $wpdb->prepare(
                "SELECT ID FROM {$wpdb->posts} WHERE post_name = %s AND post_type != %s",
                $slug,
                'revision'
            )
        )
    );

    $collisions = array_filter(
        $ids,
        static function ( int $id ) use ( $expected_id ): bool {
            return $id !== $expected_id;
        }
    );

    if ( ! empty( $collisions ) ) {
        coursepress_core_legal_fail( sprintf( 'O slug legal %s esta em uso por outro recurso.', $slug ) );
    }
}

/**
 * Gera o conteudo padrao oficial no locale informado, com restauracao segura.
 *
 * @param string $locale Locale candidato.
 * @param string $current_locale Locale ativo antes da chamada.
 * @return string
 */
function coursepress_core_legal_default_privacy_content( string $locale, string $current_locale ): string {
    if ( $locale === $current_locale ) {
        return WP_Privacy_Policy_Content::get_default_content( false, true );
    }

    if ( ! switch_to_locale( $locale ) ) {
        coursepress_core_legal_fail( sprintf( 'Nao foi possivel trocar o locale para %s.', $locale ) );
    }

    try {
        return WP_Privacy_Policy_Content::get_default_content( false, true );
    } finally {
        if ( ! restore_previous_locale() ) {
            coursepress_core_legal_fail( sprintf( 'Nao foi possivel restaurar o locale apos usar %s.', $locale ) );
        }
    }
}

/**
 * Cria ou sincroniza uma pagina legal cujo preflight ja foi concluido.
 *
 * @param string  $key Chave legal.
 * @param array   $definition Definicao deterministica.
 * @param WP_Post|null $existing Pagina gerenciada existente.
 * @return array{id: int, action: string}
 */
function coursepress_core_legal_save_page( string $key, array $definition, ?WP_Post $existing ): array {
    $content_hash = hash( 'sha256', $definition['content'] );

    if ( null === $existing ) {
        $page_id = wp_insert_post(
            array(
                'post_content' => $definition['content'],
                'post_name'    => $definition['slug'],
                'post_status'  => 'publish',
                'post_title'   => $definition['title'],
                'post_type'    => 'page',
            ),
            true
        );

        if ( is_wp_error( $page_id ) || ! is_int( $page_id ) || $page_id <= 0 ) {
            coursepress_core_legal_fail( sprintf( 'Nao foi possivel criar a pagina legal %s.', $key ) );
        }

        foreach (
            array(
                COURSEPRESS_CORE_LEGAL_MANAGED_META => COURSEPRESS_CORE_LEGAL_MANAGED_VALUE,
                COURSEPRESS_CORE_LEGAL_KEY_META     => $key,
                COURSEPRESS_CORE_LEGAL_CONTENT_META => $content_hash,
            ) as $meta_key => $meta_value
        ) {
            if ( false === update_post_meta( $page_id, $meta_key, $meta_value ) ) {
                coursepress_core_legal_fail( sprintf( 'Nao foi possivel marcar a pagina legal %s.', $key ) );
            }
        }

        return array(
            'id'     => $page_id,
            'action' => 'created',
        );
    }

    $updates = array(
        'ID' => $existing->ID,
    );

    if ( $definition['content'] !== $existing->post_content ) {
        $updates['post_content'] = $definition['content'];
    }

    if ( $definition['slug'] !== $existing->post_name ) {
        $updates['post_name'] = $definition['slug'];
    }

    if ( 'publish' !== $existing->post_status ) {
        $updates['post_status'] = 'publish';
    }

    if ( $definition['title'] !== $existing->post_title ) {
        $updates['post_title'] = $definition['title'];
    }

    if ( 1 < count( $updates ) ) {
        $updated_id = wp_update_post( $updates, true );

        if ( is_wp_error( $updated_id ) || (int) $updated_id !== (int) $existing->ID ) {
            coursepress_core_legal_fail( sprintf( 'Nao foi possivel sincronizar a pagina legal %s.', $key ) );
        }
    }

    if ( $content_hash !== (string) get_post_meta( $existing->ID, COURSEPRESS_CORE_LEGAL_CONTENT_META, true ) ) {
        if ( false === update_post_meta( $existing->ID, COURSEPRESS_CORE_LEGAL_CONTENT_META, $content_hash ) ) {
            coursepress_core_legal_fail( sprintf( 'Nao foi possivel registrar o hash da pagina legal %s.', $key ) );
        }
    }

    return array(
        'id'     => (int) $existing->ID,
        'action' => 1 < count( $updates ) ? 'updated' : 'unchanged',
    );
}

/**
 * Valida todos os atributos e a URL publica de uma pagina legal criada.
 *
 * @param int    $page_id ID da pagina.
 * @param string $key Chave legal.
 * @param array  $definition Definicao esperada.
 */
function coursepress_core_legal_validate_page( int $page_id, string $key, array $definition ): void {
    $page = get_post( $page_id );

    if (
        ! ( $page instanceof WP_Post ) ||
        'page' !== $page->post_type ||
        'publish' !== $page->post_status ||
        $definition['title'] !== $page->post_title ||
        $definition['slug'] !== $page->post_name ||
        $definition['content'] !== $page->post_content ||
        COURSEPRESS_CORE_LEGAL_MANAGED_VALUE !== (string) get_post_meta( $page_id, COURSEPRESS_CORE_LEGAL_MANAGED_META, true ) ||
        $key !== (string) get_post_meta( $page_id, COURSEPRESS_CORE_LEGAL_KEY_META, true ) ||
        ! hash_equals( hash( 'sha256', $definition['content'] ), (string) get_post_meta( $page_id, COURSEPRESS_CORE_LEGAL_CONTENT_META, true ) )
    ) {
        coursepress_core_legal_fail( sprintf( 'A pagina legal %s nao passou na validacao.', $key ) );
    }

    $url = get_permalink( $page_id );
    $url_parts = is_string( $url ) ? wp_parse_url( $url ) : false;

    if ( ! is_string( $url ) || empty( $url_parts['scheme'] ) || empty( $url_parts['host'] ) ) {
        coursepress_core_legal_fail( sprintf( 'A pagina legal %s nao possui URL publica.', $key ) );
    }
}

$privacy_class = ABSPATH . 'wp-admin/includes/class-wp-privacy-policy-content.php';

if ( ! is_readable( $privacy_class ) ) {
    coursepress_core_legal_fail( 'A classe de conteudo padrao da politica de privacidade nao esta disponivel.' );
}

require_once $privacy_class;

if ( ! class_exists( 'WP_Privacy_Policy_Content' ) || ! is_callable( array( 'WP_Privacy_Policy_Content', 'get_default_content' ) ) ) {
    coursepress_core_legal_fail( 'O metodo de conteudo padrao da politica de privacidade nao esta disponivel.' );
}

$current_locale = determine_locale();
$privacy_candidates = array(
    'pt_BR' => coursepress_core_legal_default_privacy_content( 'pt_BR', $current_locale ),
    'en_US' => coursepress_core_legal_default_privacy_content( 'en_US', $current_locale ),
);

$definitions = array(
    'privacy' => coursepress_core_legal_definition( 'privacy' ),
    'terms'   => coursepress_core_legal_definition( 'terms' ),
);
$existing_pages = array();

foreach ( $definitions as $key => $definition ) {
    $page_id = coursepress_core_legal_find_page_by_key( $key );
    $existing_pages[ $key ] = $page_id > 0 ? coursepress_core_legal_require_managed_page( $page_id, $key ) : null;
    coursepress_core_legal_require_slug_available( $definition['slug'], $page_id );
}

$previous_privacy_page_id = (int) get_option( 'wp_page_for_privacy_policy', 0 );
$privacy_reassignment = array(
    'previous_id' => $previous_privacy_page_id,
    'match'       => null,
    'hashes'      => array(),
);

if ( 0 !== $previous_privacy_page_id && ( null === $existing_pages['privacy'] || $previous_privacy_page_id !== (int) $existing_pages['privacy']->ID ) ) {
    $previous_page = get_post( $previous_privacy_page_id );

    if ( ! ( $previous_page instanceof WP_Post ) || 'page' !== $previous_page->post_type || 'draft' !== $previous_page->post_status ) {
        coursepress_core_legal_fail( 'A politica de privacidade atualmente selecionada nao e um rascunho padrao elegivel para reatribuicao.' );
    }

    $privacy_reassignment['hashes']['current'] = hash( 'sha256', $previous_page->post_content );

    foreach ( $privacy_candidates as $locale => $candidate_content ) {
        $privacy_reassignment['hashes'][ $locale ] = hash( 'sha256', $candidate_content );

        if ( null === $privacy_reassignment['match'] && $previous_page->post_content === $candidate_content ) {
            $privacy_reassignment['match'] = $locale;
        }
    }

    if ( null === $privacy_reassignment['match'] ) {
        coursepress_core_legal_fail( 'A politica de privacidade atualmente selecionada nao corresponde integralmente a um conteudo padrao oficial.' );
    }
} elseif ( $previous_privacy_page_id > 0 && null !== $existing_pages['privacy'] && $previous_privacy_page_id === (int) $existing_pages['privacy']->ID ) {
    $privacy_reassignment['match'] = 'managed';
}

/* Fim do preflight: nenhuma escrita legal ocorreu ate este ponto. */
$results = array();

foreach ( $definitions as $key => $definition ) {
    $results[ $key ] = coursepress_core_legal_save_page( $key, $definition, $existing_pages[ $key ] );
    coursepress_core_legal_validate_page( $results[ $key ]['id'], $key, $definition );
}

if ( (int) get_option( 'wp_page_for_privacy_policy', 0 ) !== $results['privacy']['id'] && ! update_option( 'wp_page_for_privacy_policy', $results['privacy']['id'] ) ) {
    coursepress_core_legal_fail( 'Nao foi possivel atribuir a pagina de politica de privacidade.' );
}

if ( (int) get_option( 'woocommerce_terms_page_id', 0 ) !== $results['terms']['id'] && ! update_option( 'woocommerce_terms_page_id', $results['terms']['id'] ) ) {
    coursepress_core_legal_fail( 'Nao foi possivel atribuir a pagina de termos do WooCommerce.' );
}

if (
    (int) get_option( 'wp_page_for_privacy_policy', 0 ) !== $results['privacy']['id'] ||
    (int) get_option( 'woocommerce_terms_page_id', 0 ) !== $results['terms']['id']
) {
    coursepress_core_legal_fail( 'As opcoes das paginas legais nao foram preservadas.' );
}

echo wp_json_encode(
    array(
        'pages'                => $results,
        'privacy_reassignment' => $privacy_reassignment,
    ),
    JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
) . PHP_EOL;
