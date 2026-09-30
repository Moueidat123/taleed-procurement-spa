# Phase 5 — explicitly authorized production release

This prompt is a runbook template, **not permission to deploy**. Begin only after the owner explicitly authorizes the named production target and operations, the candidate passes review, and credentials are supplied through an approved local/runtime mechanism rather than chat or Git.

Read the master, release candidate handoff and operations runbook. Verify the exact release commit/digest, current target project/VM/zone/static IP, edge/DNS owner, retained disk/mount/volumes/database identity, registry, deployment account, backup destination, mail configuration and operational owners. Confirm source-content publication approval, first real app admin/CMS admin identities and recovery targets. Never use remembered values or defaults to select the live target.

Separate an authorized read-only target inspection from state-changing deployment. Present a redacted plan showing affected app/services, unchanged existing Taleed apps, storage identity, backup, migrations, expected interruption, rollback limits and post-release checks. Stop if target facts, backup viability, capacity, DNS/edge ownership or release authorization are unresolved.

On an authorized first installation, use the reviewed one-time provisioning runbook; verify disk protections, private service networks and durable state before initialization. On subsequent releases, require the existing database/data identity and fail closed on an empty substitute. Do not initialize or seed on ordinary startup.

Acquire the release lock, take/verify required backup, coordinate writes and workers, run only the reviewed migrations once and deploy the approved immutable release. Run health/HTTPS/authentication/report checks without exposing real user data in logs. Initialize real accounts/source version/cycle only through separately approved, audited idempotent commands, not demo seeders.

Validate a second harmless release/container replacement retains controlled test records, saved drafts/submissions, CMS edits and reports. Do not delete or mutate real submissions as a persistence test. Perform the separately authorized secure isolated recovery drill before calling recovery readiness complete. Keep the test target non-public and mail/jobs disabled.

Return the actually verified production URL, release commit/image, schema state, sanitized storage/backup/restore evidence, verified role-based journeys, known limitations and owner runbook. No claim of zero downtime or zero data-loss guarantees. If any step fails, preserve data, stop unsafe progression and record exactly what changed; do not automatically restore the live database.
