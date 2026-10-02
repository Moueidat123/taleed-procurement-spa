# Implementation handoff

Updated: 1 October 2026. Phase: **01-FOUNDATION complete and merged to `main`** (commit `ef9cc3d`, PR #1 merge `1dc49c9`). **Next: Phase 2A on the local track.** Deployment track (Phases 4–5) is frozen per the director instruction of 1 Oct 2026 (see `PLAN-CONTRACT.md` → "Working mode").

## Current state
A working local foundation exists:
- Laravel 13.34 + Statamic 6.34.1 **Core** in `backend/`, on MySQL 8.4.11 in Docker.
- Served same-origin over local HTTPS (`https://procurement.taleed.test`) with the approved React SPA.
- Independent app and CMS guards, verified by tests.

The SPA is wired to the server API only (Phase 3, 2 Oct 2026); the browser-local demo store is removed. No production resource has been touched.

**Working mode (1 Oct 2026):** local-track phases (2A accounts/security, 2B assessment engine/scoring, 3 React wiring + staff views + reports) are actively buildable on this machine. The deployment track (4 Release readiness, 5 Production launch) is frozen and unchanged until the director unfreezes it with the section 5 inputs.

## Active assignment
Writer: Claude Code (Phases 0–1). Reviewer: unassigned; recommended: an independent reviewer (e.g. Codex) on the Phase 1 commit, per `prompts/production/REVIEW.md`. Branch `implementation/phase-1` (local, not pushed). Next action: owner review and approval of Phase 2.

## Decisions
All director decisions are recorded on 30 Sep 2026 in `decisions.md` (D-26 to D-41) and summarised in the binding `PLAN-CONTRACT.md`. The earlier proposal below is kept for history.

### Original proposal
Preserve React; Laravel + Statamic 6 Core under backend; separate Eloquent/Statamic guards; MySQL 8.4 LTS; same-origin HTTPS; local + production only; retained production data independent of code; explicit release authorization.

## Known evidence gaps
- Reference repos were read in Phase 0; no VM was inspected.
- The GitHub Actions workflows were rewritten but **not executed remotely** (no push).
- Only macOS/arm64 was verified; Windows/Linux hosts are not.
- The browser used an SPKI pin, not an OS-trusted CA (host trust changes are not approved).
- Six spinner-only screenshots remain (Phase 3).

## Production blockers
Confirm actual GCP/VM/network/disk/database/edge/DNS identities, deployment authorization, production mail, approved source-content publication, real initial admin, backups/restore rehearsal, capacity, operational owners and recovery targets. Do not place credentials or real business records here.

## Phase 0 — 00-DISCOVERY
Scope and acceptance IDs: read-only discovery; no acceptance gates claimed.
Branch and commit: `main` @ `c8c9a9d` (clean before phase). No commit made.
Changed files (new, uncommitted): `docs/production/discovery.md`, `docs/production/decisions.md`, `docs/production/reuse-matrix.md`; edited: `docs/production/HANDOFF.md`.
Database/schema/contract changes: none applied. Proposed adjustments to `03-DATABASE-SCHEMA.md` / `07-API-CONTRACT.md` are listed in `discovery.md §6` and `decisions.md` (D-05, D-10, D-11, D-13, D-14); the baseline docs themselves were not edited.
Commands, exit statuses and evidence: read-only `git`, `ls`, `find`, `diff`, `grep`, `sed`, `shasum` (all exit 0 except intentional `diff` exit 1 showing differences); tool versions read (Node 22.17.1, PHP 8.4.14, Composer 2.8.12, Docker 28.3.2). Workbook SHA-256 re-computed and matches. **Not run:** npm install/check/test, Playwright, PHP, Docker, MySQL. 28/28 core and 14,641 oracle results remain inherited evidence.
Reviewer findings and resolutions: none yet.
Unresolved blockers: none for Phase 1. Phase 3: D-12 (CMS-managed content / privacy notice). Phase 5: D-15 (placement + production identities), D-19 (content approval reference).
Key conflicts: prompt files `START-HERE-PRODUCTION.md` and `prompts/production/` missing from repo (C1); README/docs stale after 15–22 Sep commits (C2–C3); Pages workflow auto-deploys `main` (C11); no npm lockfile (C12); six spinner-only screenshots (C13); `04-OPERATIONS.md` shared-VM assumption not supported (C9).
Exact next phase and approval required: see the Phase 1 approval text in the Phase 0 report / `decisions.md`; Phase 1 = `01-FOUNDATION.md`, local only.

## Phase 1 — 01-FOUNDATION
### Scope and acceptance
Local foundation only. The 05-ACCEPTANCE "Foundation and free-Core boundary" gates are covered locally as follows.

**Clean boot with documented commands**
- Evidence: `ops/local/dev init` then `up`.
- Local only; a clean-clone run on another machine has not been done.

**Versions locked**
- `composer.lock`: Laravel 13.34.0, Statamic 6.34.1, Fortify 1.40.0, Sanctum 4.3.3.
- `package-lock.json` installs with `npm ci` under npm 10 and npm 11.

**Core-only**
- Pro is off, and the REST/GraphQL APIs are off.
- The CMS admin command refuses a second user and never enables Pro.

**App users have no `/cp` access; a CMS session gets 401 from the API**
- Covered by PHPUnit and the browser suite.

**Brokers never cross providers**
- Forgot-password gives the same response for every email (no enumeration).

**Security basics**
- CSRF, session regeneration, logout invalidation, and a Secure/HttpOnly/Lax host-only cookie are verified in the browser.
- The request-forgery matrix is covered by the smoke test.

**Real CMS use**
- The `/pages/privacy` placeholder is rendered by Statamic with the SPA's design tokens.
- The CMS admin login was exercised in the browser.

### Branch and commit
`implementation/phase-1`:
- Phase 0 docs: `43640c4`.
- Phase 1 foundation: `ef9cc3d` (the review target).

### Changed files
**New:**
- `backend/`, the Laravel + Statamic app:
  - auth config: `config/auth.php`, `fortify.php`, `sanctum.php`, `statamic/users.php`, `statamic/stache.php`;
  - `app/Models/AppUser.php`;
  - migration `0001_01_01_000000_create_identity_tables.php`;
  - API: `routes/api.php`, `routes/web.php`, `app/Http/*`, `app/Support/ApiErrorRenderer.php`;
  - `app/Console/Commands/*`;
  - `tests/Feature/Foundation/*`;
  - `phpstan.neon`.
- `compose.yaml`, `infra/docker/**`, `infra/local/{.env.example,mysql-init/}`.
- `ops/local/dev`, `ops/local/checks/https-smoke.sh`.
- `playwright.local.config.ts`, `tests/e2e-local/foundation.spec.ts`.
- `src/styles/cms-page.css`, `package-lock.json`, `.dockerignore`.
- `docs/production/{LOCAL-DEVELOPMENT.md,api/openapi-v1.yaml}`, `docs/production/evidence/phase-1/*`.

**Edited:**
- `vite.config.ts`: backend build mode and HMR origin, env-gated; the default build is unchanged.
- `package.json`: `build:backend`, `packageManager`, `@hookform/resolvers` 5.2.2.
- `.gitignore`: lockfile now tracked; local state ignored.
- `.github/workflows/ci.yml`: frontend, backend on MySQL, infrastructure.
- `.github/workflows/deploy.yml`: manual only (D-16).
- `docs/production/decisions.md`: D-20 to D-25.

### Database, schema and contract changes
- Migrations for `app_users` (ULID), `app_password_reset_tokens`, `cms_password_reset_tokens`, `cms_password_activation_tokens`, and `sessions` (string `user_id`), plus the framework cache and jobs tables.
- Local accounts: runtime (DML only), migrator, test.
- Initial API contract: `docs/production/api/openapi-v1.yaml`. Phase 1 endpoints are implemented; Phase 2 endpoints are marked planned.

### Commands, exit statuses and evidence
All output is in `docs/production/evidence/phase-1/`.

**Frontend baseline, before integration** (`frontend-check-baseline.txt`, `frontend-e2e-baseline.txt`):
- `npm run check` exit 0, core 28/28.
- `npm run test:e2e` exit 0: 25 passed, 1 skipped.
- `verify-count-oracle` exit 0.

**Frontend after integration** (`frontend-check.txt`, `frontend-e2e.txt`, `oracle-check.txt`):
- Same results: 28/28; 25 passed, 1 skipped; oracle 0.

**Local environment:**
- `ops/local/dev init` exit 0 (`dev-init.txt`), including the image build, `composer install` in the container and migrations as the migrator.
- `ops/local/dev up` exit 0; all 7 services healthy.

**Backend:**
- `ops/local/dev test`: 16 passed, 84 assertions, on MySQL `procurement_test` (`backend-phpunit.txt`).
- Larastan level 6: no errors. Pint: clean.
- `composer validate --strict`: OK. `composer audit`: no advisories.

**npm audit:**
- 2 moderate advisories (`uuid` via ExcelJS), which D-14 removes from the staff path.

**HTTPS smoke** (`https-smoke-dev.txt`, `https-smoke-built.txt`):
- TLS verified against the project CA; HTTP→HTTPS 301.
- Health returns JSON; unknown `/api/*` routes return JSON 404; `/me` returns 401 JSON.
- SPA served in both dev and built modes; CMS page 200; `/cp` 200.
- Forgery matrix: 419/419/419, and 202 for same-origin.
- MySQL is not published on the host.

**Browser** (`browser-local-https*.txt`):
- `playwright.local.config.ts`: 5/5 in dev mode and 5/5 in built mode (twice).

**Persistence and privileges:**
- Restart persistence: data survives `down`/`up` and a mode switch; `down -v` is refused (`restart-persistence.txt`).
- Runtime DB account: DROP and CREATE denied; the test account is denied on the dev schema (`db-privileges.txt`).

**Release image:**
- The `release` target builds and runs as `www-data`.
- No dev packages, `.env`, CMS users or hot file; the compiled SPA and CP assets are present (`release-image-build.txt`).
- The backend `tests/` directory was excluded after the first build.

**CI:**
- `ci.yml` and `deploy.yml` parse.
- `docker compose config` resolves with placeholders.
- Not executed on GitHub.

### Reviewer findings and resolutions
None yet; review has not been assigned.

Self-found and fixed during the phase:
1. Fortify's default forgot-password response enumerated accounts. Now generic, with a test.
2. API guests were redirected to a missing `login` route, giving a 500. Now a JSON 401.
3. Statamic's catch-all rendered HTML for unknown `/api/*` paths. The namespace is now reserved.
4. The npm lockfile was inconsistent (see D-21).
5. Host `bootstrap/cache` leaked into the release image. Now excluded.
6. Statamic `make:user` offers to enable Pro. Replaced by a refusing command.

### Unresolved and open items
**Needed before Phase 3:**
- D-12: privacy notice text and which CMS content is managed.

**Needed before Phase 5:**
- D-15: production placement and identities.
- D-19: content approval reference.

**Owner approval needed for host changes:**
- The `/etc/hosts` entry and trusting the local CA (`ops/local/dev trust-help`).

**Known limits:**
- The SPA still uses the demo `localRepository`; replacing it is Phase 2/3.
- Privileged MFA enforcement is Phase 2.
- README and older docs remain stale (C2/C3); update them when behaviour changes.

### Exact next phase and approval required
1. Independent review of `ef9cc3d` (mandatory, D-27).
2. A PR and the director's merge (D-28).
3. Phase **2A**, `prompts/production/02A-ACCOUNTS.md`, local only, after the director's written go-ahead.

## Phase 2A — 02A-ACCOUNTS
### Scope and acceptance
Implemented per PLAN-CONTRACT §2 "Phase 2A", local only:
- Schema and models: organisations (no sector), app-user → organisation link, staff invitations (72 h, single use, hashed token), email verification challenges (six digits, 15 min, 5 attempts, hashed), append-only audit events.
- Endpoints: Champion registration (allow-listed, 3/min and 20/day per IP); verification send/confirm (throttled); organisation GET/PATCH (duplicates blocked); staff invitation issue/accept (accept 5/min per IP); staff list and access changes; organisation pause/enable.
- Self-change refused (422). Last active Super Admin protected under row locks (409).
- Staff MFA (D-17): Analysts and Super Admins without confirmed TOTP get 403 `two_factor_required` on every `/staff` route. Champions may opt in.
- `procurement:admin:bootstrap`: creates or promotes one audited Super Admin; idempotent; MFA still enrolled by the person.

### Branch and commits
`implementation/phase-2a` from `main` (`ba62b655`): f2c8b06c, f262f42d, 07aed7f1, 9d13b516, e3ed5c42, 157e30a9, d5ba1819, 27b3859a and the final evidence/docs commit.

### Database, schema and contract changes
- New migrations for organisations, invitations, verification challenges and audit events; `app_users` gains `organization_id`.
- `docs/production/api/openapi-v1.yaml` 1.0.0-phase2a: Phase 2A paths marked implemented with real paths and codes. Verification paths are `/auth/email/verify/send|confirm`. New `Forbidden` response and `two_factor_required` error code.

### Commands, exit statuses and evidence (`docs/production/evidence/phase-2a/`)
- `ops/local/dev test` on MySQL 8.4: 62 passed, 263 assertions, exit 0 (`test-run.txt`).
- Larastan: no errors, exit 0 (`larastan.txt`).
- Pint `--test`: pass after auto-fixing 3 test files, exit 0 (`pint.txt`).
- OpenAPI YAML parses: 30 paths (`openapi-parse.txt`).

Tests cover: enumeration resistance, mass-assignment refusal, forged IDs, expired/used/invalid invitations and codes, invitation-accept throttling, identical emails across app and CMS, MFA enforcement per staff role, organisation pause/enable with audit, the bootstrap command, and the last-admin race on MySQL with real row locks on a second connection.

### Reviewer findings and resolutions
None yet; independent review not assigned.

### Known gaps (skipped at the director's request to save time)
1. No dedicated CSRF test. Framework Sanctum CSRF is relied on.
2. Registration and verification rate limits are configured but not tested; only invitation accept is tested.
3. Self-deactivation is tested for the single-admin case only, not as a parallel race.
4. A Super Admin deactivated while their own request is in flight can still complete that request. The last-admin rule still holds. Recommended fix: re-check the actor under the row lock.
5. Mailpit visual check of the verification and invitation emails not done; emails are asserted with notification fakes.
6. composer audit not run for this phase.

### Exact next phase and approval required
1. Independent review of this PR (D-27).
2. Director merge (D-28).
3. Phase 2B, `prompts/production/02B-ASSESSMENTS.md`, local only, after written go-ahead.

## After each phase replace this section
Scope and acceptance IDs:
Branch and commit:
Changed files:
Database/schema/contract changes:
Commands, exit statuses and evidence:
Reviewer findings and resolutions:
Unresolved blockers:
Exact next phase and approval required:

## Phase 2B — 02B-ASSESSMENTS
### Scope and acceptance
Server-side assessment engine: versioned framework import/publish, annual cycles, Champion drafts with versioned saves, atomic idempotent submission with immutable snapshots, Super Admin corrections, and staff portfolio/directory/comparison read models. Local logic only; no deployment.

### Branch and commits
Branch `implementation/phase-2b` from `main` (`23069e5e`, PR #2 merge). Commits in order: scoring engine (`0cdf7ddd`), framework/cycle tables (`2a44f0d2`), importer/publish (`08f8a45c`), operator commands (`46b4a995`), assessment tables (`3a4a40db`) and models (`bec65a94`), Champion endpoints (`d326b265`), corrections (`616a81d9`), staff views (`18e0099e`), race tests and journeys (`2fa66815`), read endpoints/OpenAPI/schema/Larastan (`a9ab38e2`), docs (this commit).

### What was built
- `App\Domain\Scoring\ScoringEngine` — PHP port of `src/domain/scoring.ts`; matches all 14,641 cases in `tests/Fixtures/scoring-count-oracle.json`. Integer basis points; string question IDs (`1.10` ≠ `1.1`); null is not "no".
- `FrameworkImporter` + `procurement:framework:import` / `procurement:framework:publish --approval-ref=` / `procurement:cycle:create` (Asia/Riyadh business dates). Import verifies the workbook SHA-256 and exactly 4 domains × 10 questions × 4 actions per band (64). Published versions are immutable; all three are audited.
- Champion API: `POST /assessments`, `GET /assessments/{id}`, `PATCH /assessments/{id}/answers` (expectedVersion; 409 when stale or submitted), `POST /assessments/{id}/submit` (Idempotency-Key + declaration; snapshot + checksum + 4 domain rows + audit + outbox in one transaction), `GET /assessments/{id}/result`, `GET /assessments/history`, `GET /cycles/available`, `GET /frameworks/{version}`.
- `POST /staff/assessments/{id}/corrections` — Super Admin with TOTP, reason 10–1000 chars, allowed after cycle close; prior submission stays effective until the correction is submitted.
- Staff (Analyst/Super Admin with TOTP): `GET /staff/portfolio`, `GET /staff/organizations` (server filters + pagination; stage and answered count only, never draft answers), `GET /staff/organizations/{id}`, `GET /staff/assessments/{id}` (submitted only), `POST /staff/comparisons` (2–4 companies, same framework). Test companies are excluded everywhere.
- Gap found and fixed: a Champion had no way to confirm authority to submit. `PATCH /organization` now accepts `authorityConfirmed`, required before an assessment can start.

### Database, schema and contract changes
Migrations `2026_10_01_000001_create_framework_and_cycle_tables` and `2026_10_02_000001_create_assessment_tables`. Exported structure: `docs/production/schema.sql` (27 tables); diagram: `docs/production/schema-erd.md`. OpenAPI `docs/production/api/openapi-v1.yaml` is `1.0.0-phase2b`; also corrected email verification confirm to 201. Only the Phase 3 export endpoint remains `planned`.

### Commands, exit statuses and evidence (`docs/production/evidence/phase-2b/`)
| Check | Result |
|---|---|
| `ops/local/dev test` (MySQL) — `mysql-suite.txt` | 123 passed, exit 0 (run by the director, 2 Oct 2026) |
| Larastan — `larastan.txt` | No errors, exit 0 (72 type errors found and fixed) |
| Pint `--test` — `pint.txt` | exit 0 |
| OpenAPI parse — `openapi-parse.txt` | 31 paths, exit 0 |

Concurrency (real MySQL locks on a second connection): duplicate assessment and second open draft refused by the database; save blocked during submit; stale save after submit is 409; crossing submits create one snapshot and one outbox event; same-key retry waits then returns the original; overlapping corrections open one draft. Real-session journeys: Champion register → email code → profile → answer → submit → result; Super Admin password + TOTP → correction → Champion submits it.

### Unresolved and open items
- Outbox events are written but no dispatcher sends them yet (Phase 3 notifications).
- No app endpoint to mark a company as a test company (`is_test`); set by an operator only.
- Locally the framework is published with test reference `LOCAL-TEST`; the real content approval reference (D-19) is still required before production.
- Closing a cycle is not exposed as a command yet; tests close it directly.

### Exact next phase and approval required
Independent review of the Phase 2B PR and the director's merge. Phase 3 (connect the React SPA to these endpoints, exports and reports) starts only after that. Phases 4–5 remain frozen until the director provides production inputs and written approval.

## Phase 3 — 03-UI-AND-REPORTS (2 Oct 2026, local)
- **Assessment.tsx:** autosave sends one batch at a time with `expectedVersion`. On a 409 the user's unsaved answers are kept, and the user chooses to keep them or load the latest. Pending answers are saved before navigation and before submit, and submit uses an idempotency key. Cycle-open checks use the server cycle dates; the fixed demo date is gone.
- **Results.tsx:** reads the frozen submitted snapshot (`/assessments/{id}/result` for champions, `/staff/assessments/{id}` for staff). It never re-scores. Includes print/PDF through the browser and JSON export.
- **Staff screens:** Portfolio, Organizations (server filter and paging), organization detail (pause/resume, open correction), Compare (2–4 companies, server-side) and Frameworks (read-only, published version).
- **Exports:** new `POST /staff/exports` endpoint. It needs Super Admin or the `can_export` grant, is audited as `portfolio.exported`, returns effective submissions only, and builds CSV/XLSX in the browser with formula-injection protection.
- **Removed:** `domain/seed.ts`, `commands.ts`, `policies.ts`, `validation.ts` and `infrastructure/localRepository.ts`. Framework checks moved to `domain/framework.ts`. Core tests were cut to 9 scoring/framework/CSV tests; demo-store tests were deleted.
- **Evidence:** `docs/production/evidence/phase-3/README.md`, including the gaps.
- **Browser journeys:** `tests/e2e-local/journeys.spec.ts` (4 journeys, local Docker and MySQL) uses the local-only `procurement:dev:seed-e2e` command. Final run: 7 passed and 2 skipped (credentials not set).
- **Fixes found by the journeys:** the `/auth/me` 401 loop that cancelled sign-in; the two-factor input reusing the email value; drafts now reload when an assessment screen opens; `exceljs` is pre-bundled in Vite.
- **Retired:** `tests/e2e/prototype.spec.ts` and `playwright.config.ts`. `npm run test:e2e` now runs the local suite, and CI no longer runs browser tests.
- **API:** `/staff/exports` is documented as implemented in `openapi-v1.yaml`.
- **Open items:** the director confirms the read-only Framework library; independent review and merge.

## 2 Oct 2026 — after Phase 3
- **Merged:** PR #4 (Phase 3) was merged on director instruction without an independent review. PR #5 merged (pending champions list; superseded by PR #6).
- **PR #6, open:** the staff directory lists every champion from registration ("Awaiting profile setup"), then the company with its progress.
- **D-17 changed by the director:** staff two-step verification is optional in every environment. The `EnsureStaffTwoFactor` middleware and the `staff.2fa` group are removed. Tests now assert that staff without TOTP can use staff routes, while role checks still hold (analyst cannot manage access; champion is refused). `two_factor_required` is removed from `openapi-v1.yaml`. The Two-step verification page stays as an optional setting. Checks: PHPUnit 128/128, Larastan and Pint clean, tsc pass, Playwright 7 passed and 2 skipped.
- **Deployment track:** Phases 4–5 remain frozen by director instruction (2 Oct 2026); no work started.
