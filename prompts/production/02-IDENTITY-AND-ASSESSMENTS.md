# Phase 2 — real identity, schema and assessment engine

Prerequisite: reviewed working local foundation. Read master and database design. Work locally only.

Implement ordered MySQL migrations, models/casts, constraints/indexes, application policies, request validation and API resources. Include the framework importer/publication provenance and cycle-pinning rules; use the source workbook/JSON hash. Create standard Laravel infrastructure tables with correct string identity widths and separate password brokers. Export a schema from the tested clean local database to `docs/production/schema.sql`; include a readable schema diagram and migration evidence.

Implement real registration, six-digit or explicitly approved link verification, session login/logout, recovery, staff invitation, active/export permission management and privileged MFA. Use Mailpit locally. Test separate app/CMS sessions and broker confusion, including identical emails across providers. Test rate limits, CSRF, current role checks, tenant ownership, last-admin concurrency and no mass assignment.

Implement profile/declaration, draft creation/resume/save with version conflicts, submit idempotency and atomic snapshot/result/outbox creation, immediate results/history, and controlled corrections. Enforce one draft and one effective submitted result per company/cycle. Port scoring exactly and compare against all 14,641 source domain-count combinations. Add real MySQL concurrency tests for draft creation, save/submit races, duplicate submit and corrections. Never treat SQLite success as concurrency proof.

Use actual HTTP requests/session auth to verify at least one Champion vertical slice through register/verify/profile/draft/submit/result and an admin-authorized correction. Test forbidden accesses with deliberately tampered identifiers and roles. Do not ship global database dumps to bootstrap the SPA.

Provide audited operator commands for initial admin, source import/publication and cycles. Real production identities/approval references must not be seeded by normal migrations; local fixtures are synthetic and explicitly local-only. Closed-cycle correction policy must be recorded and tested.

Stop with tested backend functionality and updated API/schema contracts, evidence and handoff for Phase 3. Do not connect to real mail or production.
