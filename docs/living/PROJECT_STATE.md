{
  "method_version": 2,
  "method_mode": "standalone-adaptive",
  "planning_version": "v2",
  "planning_status": "approved",
  "execution_policy": "adaptive",
  "assurance_profile": "standard",
  "architecture_audit": "optional",
  "architecture_audit_status": "not_run",
  "manual_pdf": "scope",
  "scope": {
    "status": "approved",
    "source": "docs/bianchini/changes/v2/inputs/APPROVED_SCOPE.md",
    "approved_at": null
  },
  "planning": {
    "quality_version": 2,
    "research_mode": "targeted_web",
    "research": "docs/bianchini/changes/v2/STACK_RESEARCH.md",
    "readiness": "docs/bianchini/changes/v2/READINESS.md",
    "user_actions": "docs/bianchini/changes/v2/USER_ACTIONS.md",
    "spec": "docs/bianchini/changes/v2/specs/seo-web-change.md",
    "review": "docs/bianchini/changes/v2/PLANNING_REVIEW.md",
    "checker": {
      "status": "passed",
      "rounds": 1,
      "history_path": "artifacts/bianchini/v2/planning/checker.jsonl",
      "package_digest": "d366151dc497778aa559af5b8fe5398f70b40b330ab417edd45813ae4a8e27a2",
      "report_digest": "d1217ebd09e56ec3b66725f73c53efd035c447f3b7245d9275e69e4bbff99f96"
    },
    "design_manifest": null,
    "change_root": "docs/bianchini/changes/v2",
    "current_specs": "docs/bianchini/current/specs"
  },
  "complexity_review": {
    "decision": "within_budget",
    "justification": "Um único plano médio, com três slices no mesmo seam de metadados e automação, cobre todo o escopo sem adiar requisito aprovado.",
    "deferred_scope": [],
    "scope_split_approved": false,
    "scope_split_approved_by": null,
    "scope_split_approved_at": null
  },
  "approval": {
    "status": "approved",
    "approved_at": "2026-08-21T17:02:27-03:00",
    "approved_by": "responsável do projeto",
    "approved_plans": [
      "P01"
    ],
    "package": {
      "algorithm": "sha256-manifest-v1",
      "manifest_path": "artifacts/bianchini/v2/approval/manifest.sha256",
      "manifest_digest": "e91f69e46849b0cd81f2c9ce68d9b33e5fc015cd13ce56313b2dc64ee9d9eb2a",
      "files": [
        "docs/bianchini/changes/v2/inputs/APPROVED_SCOPE.md",
        "docs/bianchini/changes/v2/STACK_RESEARCH.md",
        "docs/bianchini/changes/v2/READINESS.md",
        "docs/bianchini/changes/v2/USER_ACTIONS.md",
        "docs/bianchini/changes/v2/specs/seo-web-change.md",
        "docs/bianchini/changes/v2/spec-deltas/seo-web.md",
        "docs/bianchini/changes/v2/plans/P01-seo-tecnico-basico.md",
        "docs/bianchini/changes/v2/PLANNING_REVIEW.md"
      ]
    }
  },
  "plans": [
    {
      "id": "P01",
      "path": "docs/bianchini/changes/v2/plans/P01-seo-tecnico-basico.md",
      "status": "approved",
      "risk": "medium",
      "execution": "slice",
      "review": "per_slice",
      "test_seams": [
        "wp_head",
        "wp_robots",
        "wp_sitemaps_posts_query_args",
        "WP-CLI/HTTP"
      ],
      "depends_on": [],
      "ledger": "artifacts/bianchini/v2/ledgers/P01.md",
      "gates": [
        "WP-CLI",
        "HTML",
        "robots",
        "sitemap"
      ]
    }
  ],
  "verification": {
    "fast": {
      "commands": [
        "pwsh -NoProfile -File ./scripts/validate-seo.ps1 -Scope fast"
      ],
      "status": "pending"
    },
    "plan": {
      "commands": [
        "pwsh -NoProfile -File ./scripts/configure-seo.ps1",
        "pwsh -NoProfile -File ./scripts/validate-seo.ps1 -Scope plan"
      ],
      "status": "pending"
    },
    "release": {
      "commands": [
        "pwsh -NoProfile -File ./scripts/validate-seo.ps1 -Scope release"
      ],
      "status": "pending"
    }
  },
  "release": {
    "status": "pending",
    "platforms": [],
    "profiles": [],
    "candidate": null,
    "final_gate": "homologar-sistema",
    "homologation": "pending",
    "final_review": "pending",
    "delivery": "pending"
  },
  "active_execution": null,
  "telemetry": {
    "enabled": false,
    "path": "artifacts/bianchini/v2/telemetry.jsonl"
  },
  "blockers": [],
  "next_action": "Criar o workspace vinculado para P01 somente quando a implementação for autorizada explicitamente."
}
