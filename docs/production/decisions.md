# Implementation decisions (Phase 0 output)

**Date:** 30 September 2026. Evidence is in `discovery.md`. Reuse rationale is in `reuse-matrix.md`.

Status values:
- **Fixed**: set by `AGENTS.md`/`MASTER.md`, not open.
- **Proposed**: Phase 0 default, accepted when Phase 1 is approved unless the owner objects.
- **Owner decision**: needs an explicit answer, with the phase that first needs it.
- **Production blocker**: needed before Phase 5 only.

| ID | Decision | Status |
|---|---|---|
| D-00 | Add the missing `START-HERE-PRODUCTION.md` and `prompts/production/*.md` to the repo, copied unchanged from the verified extract `~/Downloads/taleed-procurement-spa-main/` (identical to its duplicate). Keep `Taleed_Procurement_Start_Here.md` as-is or delete it as a duplicate, per owner preference | Proposed (Phase 1, first commit) |
| D-01 | Stack | Proposed |
| D-02 | Repository layout and single origin | Proposed |
| D-03 | Guard layout (Statamic documented pattern) | Proposed |
| D-04 | Single CMS admin in file repository | Proposed |
| D-05 | Identifier strategy | Proposed |
| D-06 | Auth packages: Fortify + Sanctum SPA | Proposed |
| D-07 | Local HTTPS: nginx + mkcert, `procurement.taleed.test` | Proposed; hosts/trust-store change needs approval at run time |
| D-08 | Keep HashRouter for Phases 1–3 | Proposed |
| D-09 | Docker layout | Proposed |
| D-10 | Staff creation becomes invitation behind the existing form | Proposed |
| D-11 | Staff see draft progress counts only | Proposed |
| D-12 | Which content Statamic manages | **Owner decision** (needed by Phase 3; Phase 1 uses a placeholder) |
| D-13 | Framework/cycle/bootstrap administration via audited artisan commands, no new UI | Proposed |
| D-14 | Exports server-side CSV/XLSX; report stays browser print; no server PDF in first release | Proposed |
| D-15 | Production placement and identities | **Production blocker** |
| D-16 | GitHub Pages workflow restricted to manual dispatch | Proposed (needs Phase 1 approval: it edits CI) |
| D-17 | Privileged MFA: Fortify TOTP for Super Admin and Analyst | Proposed |
| D-18 | CI: MySQL 8.4 integration tests + frontend check | Proposed |
| D-19 | Content approval separate from UX approval | **Owner decision** (before production publication) |

---

## D-01 Stack
- **PHP 8.4** runtime. Security support runs to 2028-12-31, and all three references run 8.4 successfully.
- **Laravel 13**. Security fixes run to 2028-03-17, while Laravel 12's end 2027-02-24.
- **Statamic `^6.34`**, which accepts `^12.40 || ^13`.
- **MySQL 8.4 LTS**, image pinned by digest.
- Composer resolves the exact versions in Phase 1, and `composer.lock` is committed. Statamic Pro is disabled via `STATAMIC_PRO_ENABLED=false` in every environment, and there is no license key.
- PHP 8.5 is recommended by the Statamic upgrade guide. It is deferred until images and extensions are verified; this can be revisited without schema impact.
- **Frontend.** Keep the declared React/Redux/Vite majors. Generate and commit `package-lock.json` (un-ignore it) using Node 22 LTS per `.nvmrc`.

## D-02 Layout and single origin
```text
/ (repo root)          existing React SPA (unchanged location)
backend/               Laravel 13 + Statamic 6 Core
  app/Domain/Procurement/   scoring, transitions, eligibility, services
  app/Http/{Controllers,Requests,Resources,Middleware}/Procurement
  app/Models, app/Policies
  content/, users/     Statamic (users/ never holds committed real credentials)
  resources/views/spa.blade.php   SPA shell (Vite manifest)
infra/docker/          Dockerfile(s), nginx, entrypoint
ops/                   local + production scripts (Phase 4+)
compose.yaml, compose.https.yaml, compose.prod.yaml (prod file added in Phase 4)
```
- The SPA is built by the root Vite config into `backend/public/spa/` with a manifest. Laravel serves the shell at `/`.
- Routes `/api/procurement/v1/*`, `/sanctum/csrf-cookie`, `/cp/*`, `/up` and `/health` are reserved ahead of any SPA fallback.
- In dev, Vite HMR is proxied through the same HTTPS origin.
- Unknown `/api/*` returns a JSON 404.

## D-03 Guard layout
Follow Statamic's documented independent-guard pattern, **not** Sustainability's inverted one:
- `config/auth.php`: `defaults.guard = web`. `web` is a session guard on provider `app_users` (Eloquent `App\Models\AppUser`, table `app_users`). `statamic` is a session guard on provider `statamic` (driver `statamic`).
- Password brokers: `app_users` (table `password_reset_tokens`) and Statamic's `users` + `activations` brokers.
- `config/statamic/users.php`: `guards => ['cp' => 'statamic', 'web' => 'web']`, `repository => 'file'`.
- Sanctum `guard => ['web']`. Business routes use `auth:web` (via Sanctum's stateful middleware). `/cp` never maps to business routes.

Phase 1 must prove with tests:
- A CMS-only session gets 401 on the business API.
- An app Super Admin gets no `/cp` access.
- App-user forgot-password gives the same response for known and unknown emails and actually issues a token (Sustainability's broker regression).
- The Statamic `last_login` write against `app_users` is handled, either with a nullable column or a verified no-op.

**Shared-session caveat.** One session cookie holds both identities, so session invalidation logs out both. This is accepted, and documented for the single CMS admin.

## D-04 Single CMS administrator
- File repository, one user, created by an explicit local command in Phase 1 (synthetic credentials locally).
- Port Sustainability's `StatamicSingleUserConstraintTest` so CI fails on a second Statamic user.
- In production, `users/` is durable state (see `discovery.md §8`). The real admin identity is a production input.

## D-05 Identifiers
- Business tables use ULID `CHAR(26)` ASCII binary keys, as in `03-DATABASE-SCHEMA.md`.
- `app_users.id` is a ULID. Laravel's `sessions.user_id` and any `morphs` are changed to string columns in the first migration.
- Source question IDs stay strings (`1.10` ≠ `1.1`).

## D-06 Auth packages
- **Fortify** (MIT) on guard `web`, headless:
  - login, logout, password reset and TOTP 2FA;
  - `authenticateUsing` rejects inactive users without saying why;
  - login throttled 5/min per email+IP (Sustainability pattern);
  - Fortify registration **disabled**.
- **Custom registration** endpoint: Champion only, allowlisted fields, throttled.
- **Custom six-digit email verification challenge**: hashed, expiring, attempt-limited, resend-throttled, table `email_verification_challenges`. This preserves the approved UI; Fortify's signed-link verification is not used.
- **Custom invitation accept**: SHA-256 token hash, expiry, single use, **throttled** (the gap in Sustainability).
- **Sanctum** (MIT) SPA stateful mode only. Personal access tokens are unused, and their migration is not published.
- **No JWT and no browser token storage.**

## D-07 Local HTTPS
- nginx overlay (`compose.https.yaml`) with mkcert certificates for `procurement.taleed.test`. This follows both references and mirrors the production edge technology. The Caddy option in `04-OPERATIONS.md` is superseded.
- Certificates are gitignored.
- Adding the `/etc/hosts` entry and installing the mkcert root CA are host changes. **Ask before running them.**
- MySQL and Mailpit bind to `127.0.0.1` only, if published at all.

## D-08 Router
- Keep **HashRouter** through Phase 3. Laravel then serves one shell at `/`, and deep links cannot collide with `/api`, `/cp` or `/health`.
- A BrowserRouter move is a separately tested change, if ever wanted.

## D-09 Docker layout
- One digest-pinned multi-stage image (composer → node → `php:8.4-fpm-alpine`, running nginx+fpm as a non-root user) with entrypoint roles `web`, `queue`, `scheduler` and `migrate`. This is adapted from Sustainability.
- Dev services: web, queue, **scheduler**, mysql:8.4, mailpit, vite. There is no Redis: sessions, cache and queue use MySQL.
- `bootstrap/cache` is **not** a volume.
- Each worktree gets a unique Compose project name and a unique DB name.
- Tests run against a separate MySQL test database, never the dev DB.

## D-10 Staff creation (C6)
- The approved "create staff" form in People & access stays visually the same.
- The server creates a **pending invitation** (email, role, `can_export`) instead of an active account. The UI shows it as "Invited" until it is accepted.
- The only visible change is status wording. **Object if direct creation with an admin-set password is required**; that path is not recommended.

## D-11 Staff draft visibility (C7)
- Staff endpoints return `stage` (no-profile / not-started / in-progress / submitted) and `answered_count` for the open draft.
- Answer values are never returned.
- Response-schema tests assert that no answers are present.

## D-12 CMS-managed content (C4/C5) — owner decision
**Background.** The client-approved UI removed the methodology and privacy pages (`ed739a9`), yet registration asks for consent. `MASTER.md` requires Statamic to be genuinely used, not an unused dependency.

**Proposed default.** Statamic Core manages:
1. landing-page copy, delivered as an allowlisted, escaped payload into the existing React landing (layout unchanged);
2. a **privacy notice** page rendered server-side and linked from the registration consent label;
3. transactional email copy (verification, reset, invitation), if desired.

No methodology page is restored. Phase 1 needs only a placeholder entry to prove rendering.

**Owner to confirm:** is a privacy notice required and linked (legal text supplied by the client), and which landing copy blocks are editable?

## D-13 Administration without new screens
- Artisan commands, each audited:
  - `procurement:framework:import <xlsx|json>`: creates a **draft** version and validates 4/40/64, IDs, hash and cells;
  - `procurement:framework:publish <version> --approval-ref=`;
  - `procurement:cycle:create`;
  - `procurement:admin:bootstrap` (first Super Admin, via invitation).
- The unrouted `Frameworks.tsx`, `Cycles`, `Audit` and `DataControls` stay unrouted. `DataControls`, the role switcher and the demo buttons are excluded from the production build.

## D-14 Exports and reports
- Staff CSV/XLSX are generated server-side with **maatwebsite/excel 4 / PhpSpreadsheet 5** (MIT).
- Exports use the same eligibility query as the portfolio.
- Formula-injection neutralisation and typed string IDs are ported from `src/.../csv.ts` and Survey's `BindsFormulaTextAsString`.
- Client-side ExcelJS export is removed from the production staff path.
- The Champion report remains the approved `window.print()` A4 layout, fed by the immutable snapshot.
- **No server PDF** in the first release. This avoids dompdf (LGPL-2.1, with sub-libraries up to LGPL-3.0) and the weight of Chromium. It can be revisited as a separate decision.

## D-15 Production placement — production blocker
- **Evidence:** Survey and Sustainability run on **separate** VMs in one GCP project, each with its own nginx owning 80/443 and IP certificates.
- **Options:**
  - (a) a dedicated Procurement VM, mirroring Sustainability. This is the simplest isolation and is proposed.
  - (b) co-location on an existing VM. That needs a shared edge redesign touching another live app.
- **Also needed:** DNS hostname and certificate; a dedicated data disk; mail relay; backup bucket; deploy identity; the initial real admin; recovery targets.
- Not needed until Phase 5. Never guessed or copied from reference docs.

## D-16 GitHub Pages workflow (C11)
- Change `deploy.yml` to `workflow_dispatch` only, and gate it on `npm run check`.
- This keeps the demo publishable on demand while stopping automatic publication of an SPA that will soon depend on the API.
- Alternative: delete the workflow. **Owner may choose.**

## D-17 Privileged MFA
- Fortify TOTP with recovery codes, required for `admin`; recommended for `analyst` and proposed as required.
- Enrolment is one added step after first login, styled with existing components.
- The CMS admin uses Statamic's own 2FA.
- Enforcement lands in Phase 2; the package is installed in Phase 1.

## D-18 CI
- Extend `ci.yml` with: `composer validate --strict`, Pint, Larastan (start at level 6), PHPUnit on **MySQL 8.4 service**, frontend `npm ci && npm run check`, Playwright, `docker compose config`, and a destructive-command guard script.
- No deploy job.

## D-19 Content approval
- Framework version `1.0.0` content is imported as a **draft** framework version.
- Publishing it in production requires an `approval_reference` from the content owner (Aramco Taleed / Roland Berger attribution), distinct from UX approval.
- Locally it is published with an explicit `--approval-ref=LOCAL-SYNTHETIC` marker.

---

## Genuine blockers
- **For Phase 1:** none. Everything Phase 1 needs is resolvable locally.
- **For Phase 3:** D-12.
- **For Phase 5:** D-15 and D-19, plus the production inputs listed in `04-OPERATIONS.md §External values`.
