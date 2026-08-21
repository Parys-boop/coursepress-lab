# Review Package

- Base: `cc47becd96d7551f637092d317fa9456c42b8636`
- Head: `51c225052348411c01f94dcf5189e5cb5c1e79cd`
- Brief: `artifacts/bianchini/v1/execution/P01/task-1-brief.md` (192a9c8cb89afdbb495c08f648e810ee19ee79e92a9510cfb1986f9fd4899c7f)
- Report: `artifacts/bianchini/v1/execution/P01/task-1-report.md` (736a9cfd693ce12828387b8f68bcbca1743cb81fba95f3ef755c02ff20620d38)
- Security notice: sanitização heurística; 0 ocorrência(s) removida(s). Revise antes de compartilhar.

## Commits

```text
51c2250 feat: adiciona instrumentacao base do GTM
```

## Stat

```text
.env.example                                    |  3 ++
 README.md                                       | 32 ++++++++++-
 compose.yaml                                    |  2 +
 docs/ROADMAP.md                                 |  1 +
 wp-content/themes/coursepress-lab/functions.php | 72 +++++++++++++++++++++++++
 5 files changed, 109 insertions(+), 1 deletion(-)
```

## Diff

```diff
diff --git a/.env.example b/.env.example
index ee25eb1..67ffa89 100644
--- a/.env.example
+++ b/.env.example
@@ -8,10 +8,13 @@ WORDPRESS_DB_PASSWORD=coursepress_local_change_me
 MARIADB_ROOT_PASSWORD=root_local_change_me

 # 1 ativa o modo de depuração local; 0 desativa
 WORDPRESS_DEBUG=1

 # Interface web local do Mailpit; o SMTP permanece interno ao Docker.
 MAILPIT_UI_PORT=8025

 # 1 direciona wp_mail para o Mailpit local; 0 mantém o transporte padrão.
 COURSEPRESS_LOCAL_MAIL_ENABLED=1
+
+# ID opcional do contêiner web GTM para testes locais; deixe vazio para desativar.
+COURSEPRESS_GTM_CONTAINER_ID=
diff --git a/README.md b/README.md
index 1113bd2..2fde3f2 100644
--- a/README.md
+++ b/README.md
@@ -151,21 +151,51 @@ Quando o curso ou produto demonstrativo não estiver disponível, a página apre
 A landing inclui uma seção de captação local com nome, e-mail e consentimento explícito. O envio usa nonce, validação no servidor, honeypot e redirecionamento PRG. Os leads são registros privados do WordPress, acessíveis somente a administradores; reenvios do mesmo e-mail normalizado retornam sucesso sem criar duplicação.

 O formulário não envia e-mails, não usa CRM, analytics nem serviços externos. A Política de Privacidade gerenciada descreve essa captação demonstrativa.

 ### 11. Consultar e-mails transacionais locais

 O ambiente inclui o Mailpit `v1.30.7` para capturar e-mails sem entregá-los fora do Docker. Com `COURSEPRESS_LOCAL_MAIL_ENABLED=1`, o `wp_mail` usa o SMTP interno e a interface fica disponível em `http://127.0.0.1:MAILPIT_UI_PORT` (porta padrão `8025`).

 O primeiro registro de interesse envia uma confirmação em texto simples. Reenvios do mesmo e-mail não geram nova confirmação depois de um envio bem-sucedido; se o envio anterior falhar, uma nova tentativa é feita sem duplicar o lead.

-### 12. Encerrar a sessão
+### 12. Validar GA4 pelo Google Tag Manager
+
+A instrumentação é opcional e permanece desativada quando
+`COURSEPRESS_GTM_CONTAINER_ID` está vazio. Para uma validação local, use um
+contêiner Web exclusivo de teste e informe apenas um ID no formato `GTM-*` no
+arquivo `.env`:
+
+```dotenv
+COURSEPRESS_GTM_CONTAINER_ID=GTM-EXEMPLO
+```
+
+No painel do GTM, configure uma única **tag Google** com o Measurement ID
+`G-*` do fluxo GA4 de teste. O Measurement ID, credenciais e exports do Google
+não devem ser gravados no repositório. Depois de alterar o `.env`, recrie o
+serviço WordPress e confira a instalação local:
+
+```bash
+docker compose up -d --force-recreate wordpress
+curl -fsS http://localhost:8080/ | rg -o 'googletagmanager\.com/gtm\.js\?id=GTM-[A-Z0-9]+'
+```
+
+O comando deve encontrar uma única referência. Use o Preview/Tag Assistant
+para confirmar uma única tag Google e o GA4 DebugView para confirmar apenas o
+`page_view` automático, sem nome, e-mail, dados de lead, checkout ou compra.
+Remova o valor da variável e recrie o serviço para desativar a emissão.
+
+Não use um contêiner de produção nem habilite coleta pública sem aprovação
+jurídica/operacional sobre privacidade e consentimento. Eventos customizados,
+SEO, Consent Mode e CMP não fazem parte desta etapa.
+
+### 13. Encerrar a sessão

 ```bash
 docker compose stop
 ```

 Para iniciar novamente:

 ```bash
 docker compose start
 ```
diff --git a/compose.yaml b/compose.yaml
index 7199f67..195b569 100644
--- a/compose.yaml
+++ b/compose.yaml
@@ -38,25 +38,27 @@ services:
         condition: service_started
     ports:
       - "${WORDPRESS_PORT:-8080}:80"
     environment: &wordpress-environment
       WORDPRESS_DB_HOST: db:3306
       WORDPRESS_DB_NAME: ${WORDPRESS_DB_NAME}
       WORDPRESS_DB_USER: ${WORDPRESS_DB_USER}
       WORDPRESS_DB_PASSWORD: ${WORDPRESS_DB_PASSWORD}
       WORDPRESS_DEBUG: ${WORDPRESS_DEBUG:-1}
       COURSEPRESS_LOCAL_MAIL_ENABLED: ${COURSEPRESS_LOCAL_MAIL_ENABLED:-0}
+      COURSEPRESS_GTM_CONTAINER_ID: ${COURSEPRESS_GTM_CONTAINER_ID:-}
       WORDPRESS_CONFIG_EXTRA: |
         define('WP_ENVIRONMENT_TYPE', 'local');
         define('WP_DEBUG_LOG', true);
         define('WP_DEBUG_DISPLAY', false);
         define('DISALLOW_FILE_EDIT', true);
+        define('COURSEPRESS_GTM_CONTAINER_ID', getenv('COURSEPRESS_GTM_CONTAINER_ID') ?: '');
     volumes: &wordpress-volumes
       - wordpress_data:/var/www/html
       - ./wp-content/plugins/coursepress-core:/var/www/html/wp-content/plugins/coursepress-core
       - ./wp-content/themes/coursepress-lab:/var/www/html/wp-content/themes/coursepress-lab
     networks:
       - coursepress

   cli:
     image: wordpress:cli
     profiles: ["tools"]
diff --git a/docs/ROADMAP.md b/docs/ROADMAP.md
index a219912..ed1c896 100644
--- a/docs/ROADMAP.md
+++ b/docs/ROADMAP.md
@@ -30,20 +30,21 @@
 - [x] controlar acesso por prévia e conteúdo protegido;
 - [x] conectar compra aprovada à matrícula;
 - [x] testar a jornada completa.

 ## Fase 4 — Conversão e integrações

 - [x] construir landing page responsiva;
 - [x] configurar formulário e captação;
 - [x] integrar e-mail transacional;
 - [ ] configurar GA4 e Google Tag Manager;
+  - implementação local prevista; conclusão depende de Preview/Tag Assistant e GA4 DebugView com o contêiner de teste;
 - [ ] aplicar SEO técnico básico.

 ## Fase 5 — Código próprio

 - [x] evoluir o tema `coursepress-lab`;
 - [ ] evoluir o plugin `coursepress-core`;
 - [ ] registrar eventos e leads necessários;
 - [ ] adicionar configurações administrativas;
 - [ ] aplicar padrões de segurança do WordPress.

diff --git a/wp-content/themes/coursepress-lab/functions.php b/wp-content/themes/coursepress-lab/functions.php
index 898d81b..cc7b1df 100644
--- a/wp-content/themes/coursepress-lab/functions.php
+++ b/wp-content/themes/coursepress-lab/functions.php
@@ -30,20 +30,92 @@ function coursepress_lab_assets(): void {
     wp_enqueue_script(
         'coursepress-lab-navigation',
         get_template_directory_uri() . '/assets/js/navigation.js',
         array(),
         wp_get_theme()->get( 'Version' ),
         true
     );
 }
 add_action( 'wp_enqueue_scripts', 'coursepress_lab_assets' );

+/**
+ * Retorna o ID de contêiner GTM local quando ele atende ao formato aceito.
+ */
+function coursepress_lab_get_gtm_container_id(): string {
+    if ( ! defined( 'COURSEPRESS_GTM_CONTAINER_ID' ) ) {
+        return '';
+    }
+
+    $container_id = (string) COURSEPRESS_GTM_CONTAINER_ID;
+
+    return 1 === preg_match( '/\AGTM-[A-Z0-9]+\z/', $container_id ) ? $container_id : '';
+}
+
+/**
+ * Imprime a parte head do único contêiner GTM configurado.
+ */
+function coursepress_lab_render_gtm_head(): void {
+    static $rendered = false;
+
+    if ( $rendered ) {
+        return;
+    }
+
+    $container_id = coursepress_lab_get_gtm_container_id();
+
+    if ( '' === $container_id ) {
+        return;
+    }
+
+    $rendered   = true;
+    $encoded_id = rawurlencode( $container_id );
+    $json_id    = wp_json_encode( $container_id );
+    ?>
+    <!-- Google Tag Manager -->
+    <script>
+    (function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
+    new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
+    j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
+    'https://www.googletagmanager.com/gtm.js?id=<?php echo esc_js( $encoded_id ); ?>'+dl;f.parentNode.insertBefore(j,f);
+    })(window,document,'script','dataLayer',<?php echo $json_id; ?>);
+    </script>
+    <!-- End Google Tag Manager -->
+    <?php
+}
+add_action( 'wp_head', 'coursepress_lab_render_gtm_head', 1 );
+
+/**
+ * Imprime o fallback noscript do mesmo contêiner GTM configurado.
+ */
+function coursepress_lab_render_gtm_body(): void {
+    static $rendered = false;
+
+    if ( $rendered ) {
+        return;
+    }
+
+    $container_id = coursepress_lab_get_gtm_container_id();
+
+    if ( '' === $container_id ) {
+        return;
+    }
+
+    $rendered      = true;
+    $noscript_url  = 'https://www.googletagmanager.com/ns.html?id=' . rawurlencode( $container_id );
+    ?>
+    <!-- Google Tag Manager (noscript) -->
+    <noscript><iframe src="<?php echo esc_url( $noscript_url ); ?>" height="0" width="0" style="display:none;visibility:hidden" title="<?php echo esc_attr__( 'Google Tag Manager', 'coursepress-lab' ); ?>"></iframe></noscript>
+    <!-- End Google Tag Manager (noscript) -->
+    <?php
+}
+add_action( 'wp_body_open', 'coursepress_lab_render_gtm_body', 1 );
+
 /**
  * Obtém os dados publicados que a landing page pode apresentar com segurança.
  *
  * @return array<string, mixed>|null
  */
 function coursepress_lab_get_landing_context(): ?array {
     static $context = null;
     static $resolved = false;

     if ( $resolved ) {
```
