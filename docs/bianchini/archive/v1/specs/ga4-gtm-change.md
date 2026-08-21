# Mudança v1 — GA4 + Google Tag Manager

## Objetivo

Instrumentar as páginas públicas do CoursePress Lab por um único contêiner
GTM configurável e delegar a coleta base de GA4 ao contêiner. A entrega prova
o `page_view` automático de teste sem expor dados de leads ou de alunos.

## Limites

- Inclui somente o contêiner GTM, sua configuração local segura, o runbook de
  GA4/GTM e as verificações local e externa.
- Exclui SEO, `gtag.js` direto, data layer customizado, eventos de conversão,
  Google Ads, Consent Mode, CMP/banner, e-mail transacional e alterações no
  plugin de leads.

## Arquitetura

1. `.env` local contém opcionalmente `COURSEPRESS_GTM_CONTAINER_ID=GTM-...`;
   `.env.example` documenta a chave vazia sem incluir ID real.
2. `compose.yaml` repassa a variável apenas aos serviços WordPress/WP-CLI e
   `WORDPRESS_CONFIG_EXTRA` expõe uma constante de mesmo propósito.
3. Uma função do tema lê a constante, valida `GTM-[A-Z0-9]+` e não gera HTML
   quando o valor é ausente ou inválido. D-001, A-001, P-001.
4. Hooks em `wp_head` e `wp_body_open` imprimem respectivamente as partes
   oficiais head e noscript do mesmo contêiner, uma vez por resposta pública.
   D-001, P-001, S-001.
5. A interface GTM contém a única tag Google associada ao Measurement ID GA4;
   o tema não conhece nem imprime `G-*` ou `gtag.js`. D-002, U-001, P-001.

## Contratos públicos e invariantes

- A configuração aceita exclusivamente um ID `GTM-*` válido e não contém
  credenciais ou PII. P-002.
- Com configuração vazia/inválida, a resposta pública não contém URLs ou
  snippets de `googletagmanager.com`.
- Com configuração válida, cada documento público contém exatamente uma
  referência ao script do contêiner e um fallback noscript com o mesmo ID.
- Não há tag Google/GA4 direta fora do GTM; a tag não é duplicada. D-002,
  P-001.
- Não há `dataLayer.push` de nome, e-mail, URL com PII, lead, checkout ou
  compra. P-002.
- O plugin `coursepress-core`, a persistência de leads, o Mailpit e os e-mails
  transacionais permanecem byte a byte fora da mudança.

## Dados, permissões e segurança

- O ID GTM é configuração local e pode ser visível no HTML por natureza; o
  Measurement ID e qualquer acesso Google não entram no Git.
- Nenhum novo dado WordPress, cookie, post type, opção, permissão ou endpoint
  é criado.
- A ativação fora do ambiente demonstrativo depende de U-002. Até então, o
  fallback é retirar a variável e desativar toda emissão.

## Jornadas e verificação

1. Sem ID: subir o ambiente e confirmar que a home não possui script GTM.
2. Com ID de teste: reiniciar o serviço WordPress e confirmar uma inserção
   única do contêiner no HTML.
3. No GTM Preview/Tag Assistant, conectar à home e verificar a tag Google.
4. No GA4 DebugView, confirmar o `page_view` automático. U-001.

## Seams e referências

- Configuração Docker: `.env.example`, `compose.yaml`.
- Renderização WordPress: `coursepress_lab_*` em
  `wp-content/themes/coursepress-lab/functions.php`, usando os hooks já
  emitidos em `header.php`.
- Operação externa: `docs/bianchini/changes/v1/USER_ACTIONS.md`.
- Contrato de domínio pós-mudança: SD-001 em
  `docs/bianchini/changes/v1/spec-deltas/analytics-web.md`.
