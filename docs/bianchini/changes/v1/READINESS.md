{
  "schema_version": 1,
  "status": "ready",
  "scope_digest": "44ef5347e4bdb880cecbae42ab34788b61b1c6a70de7e58c1cdf19beb4f5e5e0",
  "repository_revision": "b78dfe7b4d57f48fb30ff3a4d4d222ddf6d19c8f",
  "design_required": false,
  "impact_map": {
    "applications": ["WordPress público", "GTM web", "GA4 web"],
    "modules": ["tema coursepress-lab", "Docker Compose", "runbook"],
    "contracts": ["injeção única do contêiner GTM", "GA4 via tag Google no GTM"],
    "data": ["variável local GTM-*", "nenhum PII ou evento de conversão"],
    "platforms": ["Docker local", "navegador", "Tag Assistant", "GA4 DebugView"]
  },
  "decisions": [
    {
      "id": "D-001",
      "statement": "O tema imprime somente o snippet oficial do contêiner GTM, validado e configurado por variável local; a parte head usa wp_head e o fallback usa wp_body_open.",
      "evidence": "header.php já chama wp_head() dentro de head e wp_body_open() imediatamente após body; a documentação oficial GTM exige esses dois posicionamentos.",
      "destinations": [
        "docs/bianchini/changes/v1/specs/ga4-gtm-change.md",
        "docs/bianchini/changes/v1/spec-deltas/analytics-web.md",
        "docs/bianchini/changes/v1/plans/P01-gtm-ga4-base.md"
      ]
    },
    {
      "id": "D-002",
      "statement": "GA4 será configurado como uma única tag Google dentro do GTM; o tema não emitirá gtag.js, Measurement ID ou dataLayer de eventos.",
      "evidence": "A ajuda oficial do Google alerta que instalar GTM e Google tag simultaneamente causa supercontagem; o escopo exclui eventos customizados.",
      "destinations": [
        "docs/bianchini/changes/v1/specs/ga4-gtm-change.md",
        "docs/bianchini/changes/v1/spec-deltas/analytics-web.md",
        "docs/bianchini/changes/v1/plans/P01-gtm-ga4-base.md"
      ]
    }
  ],
  "assumptions": [
    {
      "id": "A-001",
      "impact": "medium",
      "status": "confirmed",
      "statement": "O ambiente continuará recebendo configuração local pelo .env e Docker Compose, padrão já usado para depuração e Mailpit.",
      "evidence": ".env.example e compose.yaml já propagam WORDPRESS_DEBUG e COURSEPRESS_LOCAL_MAIL_ENABLED sem versionar configuração real.",
      "destinations": [
        "docs/bianchini/changes/v1/specs/ga4-gtm-change.md",
        "docs/bianchini/changes/v1/plans/P01-gtm-ga4-base.md"
      ]
    }
  ],
  "pitfalls": [
    {
      "id": "P-001",
      "impact": "high",
      "statement": "Instalar GTM e Google tag/gtag.js em paralelo, ou repetir o contêiner, duplica a coleta.",
      "prevention": "Concentrar a configuração GA4 no GTM, validar o ID GTM e testar a cardinalidade do snippet no HTML.",
      "recovery": "Remover o ID do .env para cessar emissão, retirar a tag/snippet duplicado e repetir Preview antes de publicar o contêiner.",
      "verification": "curl da home encontra uma referência ao gtm.js com ID de teste; Tag Assistant mostra uma única tag Google acionada.",
      "destinations": [
        "docs/bianchini/changes/v1/specs/ga4-gtm-change.md",
        "docs/bianchini/changes/v1/spec-deltas/analytics-web.md",
        "docs/bianchini/changes/v1/plans/P01-gtm-ga4-base.md"
      ]
    },
    {
      "id": "P-002",
      "impact": "high",
      "statement": "Campos de lead, identificadores de aluno ou URLs podem encaminhar PII ao Analytics se forem expostos a tags ou variáveis.",
      "prevention": "Não criar dataLayer ou eventos no tema/plugin e manter nome, e-mail, checkout e compra fora do marco.",
      "recovery": "Remover a configuração GTM, suspender a tag e revisar o Preview/variáveis antes de reativar a coleta.",
      "verification": "Inspeção do diff e Preview confirmam ausência de parâmetros de formulário, e-mail, nome, User-ID ou eventos de conversão.",
      "destinations": [
        "docs/bianchini/changes/v1/specs/ga4-gtm-change.md",
        "docs/bianchini/changes/v1/spec-deltas/analytics-web.md",
        "docs/bianchini/changes/v1/plans/P01-gtm-ga4-base.md"
      ]
    }
  ],
  "user_actions": [
    {
      "id": "U-001",
      "needed_by": "P01",
      "can_continue_without": true,
      "fallback": "Manter COURSEPRESS_GTM_CONTAINER_ID vazio; o tema permanece sem instrumentação e o restante da entrega pode ser verificado localmente.",
      "evidence_required": "ID GTM de teste fora do Git, Preview/Tag Assistant com a tag Google e DebugView com page_view.",
      "destinations": [
        "docs/bianchini/changes/v1/USER_ACTIONS.md",
        "docs/bianchini/changes/v1/specs/ga4-gtm-change.md",
        "docs/bianchini/changes/v1/spec-deltas/analytics-web.md",
        "docs/bianchini/changes/v1/plans/P01-gtm-ga4-base.md"
      ]
    },
    {
      "id": "U-002",
      "needed_by": "P01",
      "can_continue_without": true,
      "fallback": "Não configurar contêiner de produção e usar somente teste local, ou manter a variável vazia para desativar a emissão.",
      "evidence_required": "Autorização operacional/jurídica do responsável antes de qualquer ativação pública, sem credenciais no repositório.",
      "destinations": [
        "docs/bianchini/changes/v1/USER_ACTIONS.md",
        "docs/bianchini/changes/v1/specs/ga4-gtm-change.md",
        "docs/bianchini/changes/v1/spec-deltas/analytics-web.md",
        "docs/bianchini/changes/v1/plans/P01-gtm-ga4-base.md"
      ]
    }
  ],
  "spikes": [
    {
      "id": "S-001",
      "status": "passed",
      "evidence": "Cartografia local encontrou os hooks WordPress wp_head e wp_body_open em header.php; pesquisa oficial confirmou os pontos equivalentes do snippet GTM.",
      "decision": "Usar os hooks existentes no tema e não editar header.php diretamente.",
      "destinations": [
        "docs/bianchini/changes/v1/STACK_RESEARCH.md",
        "docs/bianchini/changes/v1/specs/ga4-gtm-change.md",
        "docs/bianchini/changes/v1/plans/P01-gtm-ga4-base.md"
      ]
    }
  ],
  "design_surfaces": [],
  "spec_deltas": [
    {
      "id": "SD-001",
      "source": "docs/bianchini/changes/v1/spec-deltas/analytics-web.md",
      "target": "docs/bianchini/current/specs/analytics-web.md",
      "destinations": [
        "docs/bianchini/changes/v1/specs/ga4-gtm-change.md",
        "docs/bianchini/changes/v1/spec-deltas/analytics-web.md",
        "docs/bianchini/changes/v1/plans/P01-gtm-ga4-base.md"
      ]
    }
  ]
}
