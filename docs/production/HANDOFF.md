# Implementation handoff

Updated: 1 October 2026. Phase: **01-FOUNDATION complete and merged to `main`** (commit `ef9cc3d`, PR #1 merge `1dc49c9`). **Next: Phase 2A on the local track.** Deployment track (Phases 4–5) is frozen per the director instruction of 1 Oct 2026 (see `PLAN-CONTRACT.md` → "Working mode").

## Current state
A working local foundation exists:
- Laravel 13.34 + Statamic 6.34.1 **Core** in `backend/`, on MySQL 8.4.11 in Docker.
- Served same-origin over local HTTPS (`https://procurement.taleed.test`) with the approved React SPA.
- Independent app and CMS guards, verified by tests.

The SPA is still wired to its browser-local demo repository, since API integration is Phase 2/3. No production resource has been touched.

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

## After each phase replace this section
Scope and acceptance IDs:
Branch and commit:
Changed files:
Database/schema/contract changes:
Commands, exit statuses and evidence:
Reviewer findings and resolutions:
Unresolved blockers:
Exact next phase and approval required:
