# Stack Research — v1: GA4 + Google Tag Manager

Research mode: targeted_web
Motivo: o marco integra as interfaces externas, atuais e sensíveis a versão do
Google Tag Manager e do Google Analytics 4; o código local sozinho não define
o formato de instalação, configuração ou validação.

## Stack detectada

- WordPress 6.9 em PHP 8.3/Apache, com Docker Compose e WP-CLI, definidos em
  `compose.yaml` e `README.md`.
- Tema PHP próprio em `wp-content/themes/coursepress-lab`; `header.php` já
  expõe `wp_head()` e `wp_body_open()` nos locais exigidos pelo snippet GTM.
- S-001 confirmou, por leitura desses hooks e da documentação GTM, que não é
  necessário editar `header.php` diretamente.
- Plugin `coursepress-core` guarda leads privados com nome e e-mail. O escopo
  não instrumenta esse fluxo nem cria eventos ou parâmetros analíticos.
- Não há SDK, plugin de Analytics, script Google, `dataLayer`, CI ou suíte de
  testes versionada no repositório.

## Fontes primárias

- Fonte primária: Google Tag Manager Help — *Install a web container*, seção
  de instalação do contêiner web.
  URL: https://support.google.com/tagmanager/answer/14847097?hl=en-GB
  Acessado em: 2026-08-21
  Aplicação: a primeira parte do snippet GTM deve ficar o mais alto possível
  em `head` e a segunda imediatamente após `body`, em todas as páginas.

- Fonte primária: Google Tag Manager Help — *Set up Google Analytics in Tag
  Manager*, seções de configuração, Preview e publicação.
  URL: https://support.google.com/tagmanager/answer/9442095?hl=en
  Acessado em: 2026-08-21
  Aplicação: o Measurement ID `G-*` configura uma única tag Google no GTM,
  disparada em page view; Preview/Tag Assistant precede a publicação.

- Fonte primária: Google Analytics Help — *Troubleshoot tag setup on your
  website*, seção Tag Manager.
  URL: https://support.google.com/analytics/answer/9311124?hl=en
  Acessado em: 2026-08-21
  Aplicação: não instalar simultaneamente snippets GTM e Google tag/gtag.js;
  isso pode supercontar dados. A validação externa inclui Realtime/DebugView.

- Fonte primária: Google Analytics Help — *Best practices to avoid sending
  Personally Identifiable Information (PII)*.
  URL: https://support.google.com/analytics/answer/6366371?hl=en
  Acessado em: 2026-08-21
  Aplicação: nome, e-mail, IDs de usuário e parâmetros de URL potencialmente
  identificáveis não entram em tags, variáveis ou eventos deste marco.

- Fonte primária: Google Tag Manager Help — *Introduction to user consent
  management*.
  URL: https://support.google.com/tagmanager/answer/12329599?hl=en
  Acessado em: 2026-08-21
  Aplicação: a decisão de ativar coleta em ambiente público requer ação do
  responsável; CMP/Consent Mode não será construído neste escopo.

## Decisões aplicadas

- Usar somente GTM no HTML do WordPress; GA4 será uma tag Google configurada
  dentro do contêiner. Isso evita duas instalações concorrentes.
- Receber apenas o ID público do contêiner `GTM-*` por `.env` local, propagado
  para uma constante WordPress via `compose.yaml`; o Measurement ID `G-*`
  ficará somente na interface do GTM.
- Validar estritamente o formato do ID antes de imprimir HTML e não emitir
  snippet quando a configuração estiver vazia ou inválida.
- Limitar a primeira coleta ao `page_view` automático; não criar `dataLayer`
  nem eventos de lead, checkout ou compra.

## Alternativas rejeitadas

- Inserir `gtag.js`/Google tag diretamente no tema — duplicaria o caminho de
  coleta quando o contêiner GTM também estiver ativo.
- Adicionar Site Kit ou outro plugin de terceiros — amplia dependências e
  permissões administrativas sem ser necessário para o contêiner básico.
- Capturar campos do formulário ou parâmetros da URL no GTM — viola o limite
  do marco e aumenta o risco de envio de PII.

## Riscos e lacunas

- A conta GA4, o contêiner GTM, seus acessos e os IDs reais não existem no
  repositório; são ações externas bloqueadas por padrão seguro.
- A validade jurídica de coleta em uma futura publicação não é decidida por
  esta pesquisa. Sem confirmação do responsável, a configuração pública deve
  permanecer vazia e não emitir o snippet.
