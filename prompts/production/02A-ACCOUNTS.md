# Phase 2A — accounts, login and security

Prerequisite: Phase 1 reviewed and merged by the director; director approval to start 2A. Read `AGENTS.md`, `STATUS.md`, `docs/production/PLAN-CONTRACT.md` (binding scope and decisions), the master, `03-DATABASE-SCHEMA.md`, `07-API-CONTRACT.md`, `api/openapi-v1.yaml` and the handoff. Work locally only. Do not decide product questions: anything not covered by the contract is escalated to the director with options and a recommendation.

Implement, per PLAN-CONTRACT §2 "Phase 2A":
- Migrations, models, policies, form requests and API resources for:
  - organisations (approved profile fields only, no sector);
  - the app-user → organisation relationship;
  - staff invitations (72-hour, single-use, hashed token);
  - email verification challenges (six-digit code, 15 minutes, 5 attempts, throttled resend, hashed);
  - append-only audit events.
- Endpoints:
  - Champion-only registration, with allow-listed fields and throttling;
  - verification send and confirm;
  - organisation GET/PATCH;
  - staff invitation issue and accept (accept is throttled);
  - staff user list and access changes, where self-change is blocked and the last active Super Admin is protected under concurrency;
  - organisation pause and enable.
- Mandatory TOTP enrolment and challenge for Super Admin and Analyst, with recovery codes. Champions may opt in.
- Audited operator command `procurement:admin:bootstrap` (first Super Admin by invitation). Local fixtures stay synthetic and explicitly local-only.

Tests on MySQL 8.4 (never SQLite for constraints or concurrency):
- enumeration resistance, CSRF, rate limits and mass-assignment refusal;
- forged organisation, role and user IDs;
- identical emails across the app and CMS providers;
- expired, used and invalid invitations and codes;
- MFA enforcement for staff;
- last-admin and self-deactivation races.

Verify mail in Mailpit.

Update `api/openapi-v1.yaml`, evidence under `docs/production/evidence/phase-2a/`, `STATUS.md` and `HANDOFF.md`. Open a pull request from `implementation/phase-2a`. Stop for independent review; do not start 2B.
