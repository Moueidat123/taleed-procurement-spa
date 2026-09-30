# Sources and verification boundaries

Official sources consulted on 30 September 2026. Re-check version-sensitive guidance at implementation time and follow the documentation matching the resolved packages. These references support engineering decisions; they do not verify the owner's running infrastructure.

| ID | Primary source | Used for |
|---|---|---|
| S1 | https://statamic.com/pricing | Core's scope, one admin, Pro capabilities |
| S2 | https://statamic.dev/getting-started/licensing | Core/Pro licensing, development versus production restrictions |
| S3 | https://statamic.dev/getting-started/requirements | Statamic 6 requirements, PHP minimum and extensions |
| S4 | https://statamic.dev/knowledge-base/tips/using-an-independent-authentication-guard | Separate Eloquent and Statamic guards/providers and password brokers |
| S5 | https://laravel.com/framework/docs/13.x/authentication | Laravel session authentication and guard/provider model; use the installed major's equivalent docs |
| S6 | https://laravel.com/framework/docs/13.x/sanctum | First-party SPA cookie/session authentication and CSRF; use the installed major's equivalent docs |
| S7 | https://dev.mysql.com/doc/refman/8.4/en/mysql-releases.html | MySQL Innovation versus LTS release model |
| S8 | https://caddyserver.com/docs/automatic-https | Local HTTPS and trust requirements, public HTTPS automation |
| S9 | https://docs.docker.com/engine/storage/volumes/ | Volume lifecycle, host backing and external state |
| S10 | https://docs.cloud.google.com/compute/docs/disks/snapshot-best-practices | Data disk/snapshot planning and application consistency |
| S11 | https://dev.mysql.com/doc/refman/8.4/en/mysqldump.html | Logical backup behavior and transaction/DDL consistency limits |
| S12 | https://developers.openai.com/codex/guides/agents-md/ | Codex repository instructions; redirected to current OpenAI documentation during review |
| S13 | https://code.claude.com/docs/en/memory | Claude Code project instructions and CLAUDE.md |
| S14 | https://git-scm.com/docs/git-worktree | Worktree mechanics and separate checkout behavior |
| S15 | https://statamic.dev/frontend/javascript-frameworks | Frontend framework independence and selective server-rendered data |
| S16 | https://statamic.dev/control-panel/users | Statamic's own users/permissions are marked Pro; do not confuse them with independent Laravel users |

## Supplied primary material
Repository code paths cited in `01-REPOSITORY-AUDIT.md`; `src/data/framework.json`; `Procurement_Self Assessment Tool_v01.xlsx`; `demo/Taleed_Procurement_Walkthrough.pptx`; `demo/Taleed_Procurement_Speech_Script.docx`; selected `demo/screenshots/` images. The user reports design/concept approval. Source-content formal approval remains separately labeled in the repository JSON.

## Connected reference repository (selected reads only)
- https://github.com/FadiZahhar/sustainability-diagnostic-tool/blob/main/README.md — older starter/Pro/Vue direction; not current VM evidence.
- https://github.com/FadiZahhar/sustainability-diagnostic-tool/blob/main/scripts/prod/deploy-local.sh — operator-driven commit archive/CI/remote runner, and dry-run side-effect caution.
- Search excerpts at commit `2b8ec66c55f8371e69715c55dfc82e18480603dd` from `scripts/prod/deploy-remote.sh`, `scripts/check-no-destructive-production-commands.sh`, `docs/discovery/icttool-audit.md`, `docs/discovery/reuse-decision-matrix.md`, and `docs/operations/environments.md`.

These are private, authorized source reads, not instructions to publish those repositories. No reference-repository source or credentials are copied into this package. Main may have advanced; inspect actual commits before reuse. Survey details are indirect audit-document excerpts; the Survey repository and live production services were not directly verified.

## Verification artifacts
`evidence/core-test-output-2026-09-30.txt`: actual SPA core run.
`evidence/workbook-audit.json`: actual source text/hash comparison.
`evidence/count-oracle-check-2026-09-30.json`: actual 14,641-case parity check against the uploaded TypeScript engine.
`reference/source-manifest.json`: hashes of original uploaded files, plus upload SHA-256.

No PHP/MySQL schema was executed here. `03-DATABASE-SCHEMA.md` is the proposed relational design. The implementation must produce migrations and export a schema from tested MySQL, not label a speculative SQL file as tested.
