# Phase 4 — production artifacts and recovery readiness, no live release

Prerequisite: reviewed complete local app. Read master, operations and acceptance docs. This phase builds/tests release tooling with local/CI synthetic state; it does not authorize a production connection or deployment.

Implement separate production Compose, multi-stage locked images and tested local-to-production configuration separation. No dev services/source mounts/debug/public database ports. Add immutable image/release metadata, app/worker/scheduler health, resource/log limits and one scheduler. Keep CMS mutable content/users/assets/metadata/private reports and keys outside image replacement.

Implement retained-disk/external-volume/database-identity preflight that fails closed, including reboot ordering. Build a genuinely offline plan command and separate connected verification command. Do not copy the reference script's dry-run side effects. First provisioning stays separate from normal release. Keep other Taleed apps out of every script's scope.

Implement consistent backup with manifests/off-VM destination integration, guarded isolated restore, synthetic persistence checks, application-scoped release lock, coordinated migrations/workers and schema-compatible rollback/roll-forward. Prohibit destructive normal deploy paths and automatic DB restoration. Test failure paths: missing disk, wrong marker, backup failure, failed migration, queue problem, low space, deployment concurrency and interrupted operator session.

Replace or explicitly isolate the existing GitHub Pages deployment workflow. Build a CI/release workflow with locked dependencies, MySQL tests, frontend checks, production-config validation, immutable image build and a gated production step. Prefer approved workload identity over long-lived keys. Leave all actual deployment values as confirmed configuration or explicit placeholders; do not invent resource names or secret values.

Provide exact operator docs for local HTTPS, production setup, backup/restore, first real account/framework/cycle setup, routine release and incident response. Provide an acceptance matrix with actual evidence and unexecuted production gates. Do not label a local synthetic restore as a restore of the live production backup.

Stop with a deployable release candidate and the explicit production-read/deployment authorization and infrastructure facts still required for Phase 5. No actual GCP/DNS/mail changes.
