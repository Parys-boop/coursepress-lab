{
  "method_version": 2,
  "method_mode": "standalone-adaptive",
  "planning_version": "v1",
  "planning_status": "approved",
  "execution_policy": "adaptive",
  "assurance_profile": "standard",
  "architecture_audit": "optional",
  "architecture_audit_status": "not_run",
  "manual_pdf": "scope",
  "scope": {
    "status": "approved",
    "source": "docs/bianchini/changes/v1/inputs/APPROVED_SCOPE.md",
    "approved_at": "2026-08-21"
  },
  "planning": {
    "quality_version": 2,
    "research_mode": "targeted_web",
    "research": "docs/bianchini/changes/v1/STACK_RESEARCH.md",
    "readiness": "docs/bianchini/changes/v1/READINESS.md",
    "user_actions": "docs/bianchini/changes/v1/USER_ACTIONS.md",
    "spec": "docs/bianchini/changes/v1/specs/ga4-gtm-change.md",
    "review": "docs/bianchini/changes/v1/PLANNING_REVIEW.md",
    "checker": {
      "status": "passed",
      "rounds": 1,
      "history_path": "artifacts/bianchini/v1/planning/checker.jsonl",
      "package_digest": "1a5b3673f4e4ad82787e5408064bdb44ede958bd3c149552c44bdf7000529f8b",
      "report_digest": "b5121b259f86f2c0ca825dccc32847b20736165e185774ed721f95bdd19cdcf0"
    },
    "design_manifest": null,
    "change_root": "docs/bianchini/changes/v1",
    "current_specs": "docs/bianchini/current/specs"
  },
  "complexity_review": {
    "decision": "within_budget",
    "justification": "Uma única entrega de risco médio cobre o seam coeso configuração local → WordPress → GTM/GA4; nenhum requisito aprovado foi adiado ou dividido.",
    "deferred_scope": [],
    "scope_split_approved": false,
    "scope_split_approved_by": null,
    "scope_split_approved_at": null
  },
  "approval": {
    "status": "approved",
    "approved_at": "2026-08-21T11:54:55-03:00",
    "approved_by": "responsável do projeto",
    "approved_plans": ["P01"],
    "package": {
      "algorithm": "sha256-manifest-v1",
      "manifest_path": "artifacts/bianchini/v1/approval/manifest.sha256",
      "manifest_digest": "0116f468ebdbff2c914ba2e99d90130801c3bea3fcaccd275ac88ae61d8c3b0c",
      "files": [
        "docs/bianchini/changes/v1/inputs/APPROVED_SCOPE.md",
        "docs/bianchini/changes/v1/STACK_RESEARCH.md",
        "docs/bianchini/changes/v1/READINESS.md",
        "docs/bianchini/changes/v1/USER_ACTIONS.md",
        "docs/bianchini/changes/v1/specs/ga4-gtm-change.md",
        "docs/bianchini/changes/v1/spec-deltas/analytics-web.md",
        "docs/bianchini/changes/v1/plans/P01-gtm-ga4-base.md",
        "docs/bianchini/changes/v1/PLANNING_REVIEW.md"
      ]
    }
  },
  "plans": [
    {
      "id": "P01",
      "path": "docs/bianchini/changes/v1/plans/P01-gtm-ga4-base.md",
      "status": "approved",
      "risk": "medium",
      "execution": "slice",
      "review": "per_slice",
      "test_seams": [
        "compose-to-wordpress-config",
        "wordpress-hooks-to-public-html",
        "gtm-to-ga4-page-view"
      ],
      "depends_on": [],
      "ledger": "artifacts/bianchini/v1/ledgers/P01.md",
      "gates": [
        "config",
        "php-lint",
        "wp-cli",
        "public-html",
        "tag-assistant",
        "ga4-debugview"
      ]
    }
  ],
  "verification": {
    "fast": {
      "commands": [
        "docker compose config --quiet",
        "docker compose exec -T wordpress php -l /var/www/html/wp-content/themes/coursepress-lab/functions.php"
      ],
      "status": "pending"
    },
    "plan": {
      "commands": [
        "docker compose up -d",
        "docker compose run --rm cli theme is-active coursepress-lab",
        "curl -fsS http://localhost:8080/ | rg -o 'googletagmanager\\.com/gtm\\.js\\?id=GTM-[A-Z0-9]+' | wc -l"
      ],
      "status": "pending"
    },
    "release": {
      "commands": [
        "docker compose ps",
        "docker compose run --rm cli plugin list",
        "curl -fsS http://localhost:8080/"
      ],
      "status": "pending"
    }
  },
  "release": {
    "status": "pending",
    "platforms": [
      "Docker local",
      "navegador web"
    ],
    "profiles": [
      "contêiner GTM de teste",
      "propriedade GA4 de teste"
    ],
    "candidate": null,
    "final_gate": "homologar-sistema",
    "homologation": "pending",
    "final_review": "pending",
    "delivery": "pending"
  },
  "active_execution": null,
  "telemetry": {
    "enabled": false,
    "path": "artifacts/bianchini/v1/telemetry.jsonl"
  },
  "blockers": [],
  "next_action": "Manter o pacote aprovado sem commit até nova autorização; depois criar o commit local atômico do pacote antes de abrir o workspace de execução."
}
