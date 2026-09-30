# Phase 0 discovery — findings

**Date:** 30 September 2026. **Phase:** `00-DISCOVERY` (read-only). **Repository:** `/Users/user/Documents/GitHub/taleed-procurement-spa`, branch `main`, HEAD `c8c9a9d` ("adding the prompts and directions"), working tree clean before this phase.

Labels used throughout:
- **[E]** evidence observed in this phase (file read, command output, official page fetched on 2026-09-30).
- **[P]** proposed default, open to review.
- **[U]** externally unverified or unavailable; must be confirmed before the step that depends on it.

Nothing was installed, scaffolded, started, migrated, pushed or deployed. No `.env*` file (except `.env.example`), key, dump, backup, `storage/` or user record was opened in any repository. No cloud resource was contacted. Local toolchain versions were read only: Node 22.17.1, PHP 8.4.14 CLI, Composer 2.8.12, Docker 28.3.2, no host `mysql` client **[E]**.

Companion documents: `decisions.md` (selected decisions and open items), `reuse-matrix.md` (reference-repository reuse/adapt/reject).

---

## 1. Checkout state and the prompt package

| Finding | Evidence |
|---|---|
| The overlay was committed as `c8c9a9d` (21 files: `AGENTS.md` rewrite, `CLAUDE.md`, `Taleed_Procurement_Start_Here.md`, `docs/production/**`) | `git show --stat c8c9a9d` **[E]** |
| **`START-HERE-PRODUCTION.md` and `prompts/production/` are not in the repository.** `AGENTS.md`, `CLAUDE.md` and `MASTER.md` all reference them | `ls`; `find` **[E]** |
| `Taleed_Procurement_Start_Here.md` is byte-identical to the package's `START-HERE-PRODUCTION.md` (renamed) | `diff -q` **[E]** |
| The missing prompts were read from two identical extracts, `/Users/user/Downloads/taleed-procurement-spa-main/` and `.../taleed-procurement-spa-main 2/` (`diff -rq` differs only in `.DS_Store`). They contain `prompts/production/{MASTER,00-DISCOVERY,01-FOUNDATION,02-IDENTITY-AND-ASSESSMENTS,03-UI-AND-REPORTS,04-RELEASE-READINESS,05-PRODUCTION-RELEASE,REVIEW,CONTINUE}.md` | **[E]** |
| The extract's `src/`, `demo/` and `docs/production/` match the repository byte-for-byte, so the package was built from current source | `diff -rq` **[E]** |
| `docs/production/reference/AGENTS.prototype.md` equals the initial commit's `AGENTS.md` (`fb5ad33`, SHA-256 `f6bda319…`), so the archived "original" is faithful | `git show fb5ad33:AGENTS.md \| shasum` **[E]** |
| No nested `AGENTS.md`/`CLAUDE.md` exists below the root, apart from the archived copy | **[E]** |
| Remote `origin` = `github.com/Moueidat123/taleed-procurement-spa`; only `main` exists | `git remote -v`, `git branch -a` **[E]** |

**Consequence:** a later agent following `AGENTS.md` literally cannot find its prompts. Adding the missing files to the repo, copied unchanged from the verified extract, is part of the Phase 1 approval request (see `decisions.md` D-00).

---

## 2. Approved SPA inventory

### 2.1 Stack and tooling [E]
- React 19.3.0, React DOM 19.3.0, Redux Toolkit 2.12.0, React Redux 9.3.0, React Router DOM ^7.9.1, React Hook Form ^7.62.0, Zod ^4.1.5, ExcelJS 4.4.0 (lazy), Vite ^8.1.0, TypeScript ~5.9.3, Playwright ^1.55.
- `engines: >=22.12.0 <23 || >=24.0.0`; `.nvmrc` = `22`; `.npmrc` = `save-exact=true`, `engine-strict=true`.
- **No lockfile of any kind.** `package-lock.json` is listed in `.gitignore`. Commit `b8a2f5d` stopped tracking it, and `91236a6` removed the npm cache.
- Scripts: `dev`, `build` (typecheck + vite build), `typecheck`, `lint`, `test`/`test:core`, `test:e2e`, `check` (typecheck + lint + test + build), `verify:dependencies`, `check:syntax`.

### 2.2 Routes (`src/app/App.tsx`) [E]
Router: **HashRouter** (`App.tsx:2,56`). `vite.config.ts` uses `base: './'`.

| Area | Hash route | Component | Access |
|---|---|---|---|
| Public | `/`, `/login`, `/register`, `/verify`, `/forgot-password`, `/reset-password` | `Landing`, `Auth.tsx` exports | Anyone |
| Workspace | `/app` (index redirects: Champion → `dashboard`, others → `portfolio`) | `Layout` | Signed-in, active, verified (`Guard`, `App.tsx:25-31`) |
| Champion | `dashboard`, `profile`, `history`, `assessment/:id/review`, `assessment/:id/:section` | `Company.tsx`, `Assessment.tsx` | `champion` |
| Shared result | `results/:id`, `recommendations/:id`, `responses/:id`, `report/:id` | `Results.tsx` (tab modes) | Any role, gated by `canReadAssessment` |
| Staff | `portfolio`, `organizations`, `organizations/:id`, `compare` | `Portfolio.tsx`, `Organizations.tsx`, `Compare.tsx` | `analyst`, `admin` |
| Super Admin | `access` | `Administration.tsx` (People & access) | `admin` |
| Fallback | `/app/*` in-app 404; `*` public 404 | | |

A wrong-role visit renders an EmptyState ("This area is not available to your role") rather than redirecting.

**Code present but unrouted [E]:** `Cycles()` and `Audit()` (`src/features/staff/Administration.tsx:13-20, 31-35`); the whole of `src/features/staff/Frameworks.tsx`; `DataControls.tsx`, reachable only as the boot-corruption recovery screen (`App.tsx:7,56`). The domain commands `saveCycle`, `cloneFramework`, `updateDraftContent` and `publishFramework` (`src/domain/commands.ts:111-157`) have no UI caller.

### 2.3 Roles [E]
`Role = 'champion' | 'analyst' | 'admin'` (`src/domain/types.ts:4`). The UI labels are Company Champion, Taleed Analyst and Super Admin. `src/` and `tests/` contain no Program Manager references. The leftovers are documentation only (§5).

### 2.4 Approved journeys as implemented [E]
- **Register** (`Auth.tsx:28-36`). Zod checks: name ≥2; email; job title ≥2; password ≥12 with upper, lower and digit; confirmation; consent. The `register` command forces `role=champion` and `verified=false`, rejects duplicate emails and discards the password.
- **Verify** (`Auth.tsx:37-46`). Six-digit input. The code is **fixed at `123456`** (`commands.ts:44`) and shown on screen. Resend only starts a 30-second cooldown.
- **Login** (`Auth.tsx:17-27`). Any active seeded email plus the **shared demo password** (`seed.ts:9`); demo-role buttons.
- **Forgot/reset.** Validation-only simulation.
- **Company profile** (`Company.tsx:14-30`). Fields: name, country (8 options), size (5 ranges), optional registration ID, authority confirmation. Duplicate names or registration IDs are blocked (`commands.ts:55-58`). A `beforeunload` warning fires when the form is dirty. **No sector field** (removed in `ed739a9`).
- **Dashboard** (`Company.tsx:31-50`). Uses the single current cycle, with no cycle picker. Shows start/resume/view, a completion percentage and four domain cards.
- **Assessment** (`Assessment.tsx:10-34`):
  - Ten Yes/No radio groups per section. Every change autosaves through the `answer` command, and answers can be cleared.
  - Continue is blocked until all answers are given; focus moves to the first missing question, and `?question=` deep links work.
  - Editing is read-only when the cycle is closed or the organisation is paused. A banner marks a correction draft.
- **Review/submit** (`Assessment.tsx:35-51`). Readiness requires 40/40 answers, a valid profile, an open cycle and an active organisation. Then the declaration checkbox and a confirm dialog. Submit is idempotent for an already-submitted record and stores an immutable snapshot.
- **Results** (`Results.tsx:18-30`). Tabs: results, recommendations (16), submitted answers, report. The report uses `window.print()` ("Print / save PDF"). There is also an "Export result JSON" action (`downloads.ts:14-17`).
- **History** (`Company.tsx:51-55`). Drafts, the effective submission and earlier revisions.
- **Analyst / Super Admin:**
  - Portfolio: band and name filters in the URL; metrics, distribution, domain averages, table; CSV/XLSX export needs `canExport`, which admin always has (`downloads.ts:18-42`, formula guard `csv.ts`).
  - Organisations directory: champion-driven, with stages no-profile / not-started / in-progress / submitted and live **N/40 draft progress bars** (`Organizations.tsx:10-27`, commit `4a36be5`).
  - Organisation detail, and Compare for 2–4 effective submissions (`?ids=`).
- **Super Admin only:**
  - People & access: search; toggle analyst export; pause/enable; **create analyst/admin staff directly**. Self-change and last-admin removal are blocked.
  - Pause/enable an organisation.
  - **Open correction**, with a reason of at least 10 characters, on the effective revision only and one draft at a time (`Organizations.tsx:40-53`).
- **States.** Suspense "Loading your workspace…", an error boundary, a global error alert, a cross-tab "newer dataset" pause, a save-status strip, and a boot-corruption recovery screen.

### 2.5 Domain rules [E]
- `types.ts`: `Answer = 'yes'|'no'|null`. `Band = foundational|developing|advanced|best_in_class` (use these exact identifiers in PHP resources). Entities: User, Organization (no sector), Cycle (open/closed), Framework (version, sourceSha256, sourceStatus, interpretations), Assessment (revision, supersedesId, correctionReason, snapshot), AuditEvent. 15 command types.
- `scoring.ts`:
  - Domain = yes × 10; overall = yes × 2.5, both unrounded.
  - `classify`: ≤40 foundational, ≤65 developing, ≤80 advanced, otherwise best_in_class.
  - Recommendations: the four actions for each domain's own band.
  - Priorities: the three lowest domains, ties broken by domain order. `hasTie` is set on any equal scores.
  - Sustain wording at 100% (`Results.tsx:25`). Scoring throws unless all 40 are answered.
- `policies.ts`:
  - Staff = not champion; manage = admin; export = admin, or analyst with the grant.
  - Champions read their own organisation, drafts included. Staff read **submitted only**.
  - `requireEditable`: own organisation, draft, active organisation, open cycle. `effectiveSubmissions`: highest submitted revision per organisation and cycle.
- `validation.ts`: strict whitelists; blocks prototype pollution; at least one active admin; staff have `orgId=null`; one draft per organisation and cycle; correction chain valid; snapshots recomputed on load.
- `seed.ts`: `DEMO_DATE=2026-09-15` with a fixed clock; five synthetic organisations; one cycle `2026` (2026-01-01 to 2026-12-31).
- `src/data/framework.json`: version `1.0.0`, "Procurement Performance Self-Assessment Framework", attribution "Aramco Taleed & Roland Berger"; 4 domains (`category_management`, `spend_analysis`, `strategic_sourcing`, `supplier_relationship_management`); 40 questions `1.1`–`4.10`; 64 actions with IDs `${domain}.${band}.${n}`; `sourceCell` provenance such as `'2. Self-Assessment'!C8`.

### 2.6 Persistence to replace [E]
- `localStorage['taleed.procurement.prototype.v1']` holds the entire dataset (`localRepository.ts:4`). `sessionStorage['taleed.procurement.demo-session']` holds the user ID.
- Revision/instance compare-and-set, Web Locks serialisation and a cross-tab `storage` pause (`store.ts:56-85`). These are good client patterns to keep in spirit (serialised saves, no false "saved").
- Demo-only surfaces that must leave production: the "Switch demo role" dialog (`Layout.tsx:45`), login demo buttons, the shared password, the fixed code `123456`, the fixed clock, and seed bootstrap.
- Also the REPLACE-typed reset/import (`store.ts:99-110`, `DataControls.tsx`).

### 2.7 Tests and CI [E]
- `tests/core.test.cjs`: **28** `node --test` cases, run after `tsc -p tsconfig.core.json`. `tests/e2e/prototype.spec.ts`: **13** Playwright tests × 2 projects (desktop Chromium, Pixel 7).
- `.github/workflows/ci.yml`: `npm install`, `npm run check`, Playwright Chromium, e2e, report artifact. No deploy.
- `.github/workflows/deploy.yml`: **on every push to `main`** runs `npm install && npm run build` and publishes `dist` to **GitHub Pages**, **with no tests**.
- **Test baseline this phase:** no test was executed (the phase forbids `npm install`, and no lockfile exists). The 28/28 core pass in `evidence/core-test-output-2026-09-30.txt` and the 14,641-case oracle parity are the **package author's earlier results**, run on Node 22.16.0 with a global TypeScript 5.8.3, not the declared ~5.9.3. They were not reproduced here.

### 2.8 Source material [E]
- Workbook `Procurement_Self Assessment Tool_v01.xlsx`, SHA-256 `e86755a94db9bb4c2d14c3bf1f60eb237189d4a5bc1fd04efbb8d4faf197eecd`. Matches `framework.json` `sourceSha256`, `evidence/workbook-audit.json:4` and `reference/source-manifest.json:42`.
- The earlier source-text comparison (40 questions, 64 actions, no mismatches) is recorded in `evidence/workbook-audit.json`. It was not re-run here.
- `demo/`: `Taleed_Procurement_Walkthrough.pptx`, `Taleed_Procurement_Speech_Script.docx`, `build_deck.py`, `build_speech.py`, `walkthrough.mjs`, `screenshots/01–17`.
- **Six screenshots (~10 KB each) show only the loading spinner:** `07-review-answers`, `11-champion-history`, `13-analyst-organizations`, `14-analyst-compare`, `16-admin-organization-detail`, `17-admin-access`.
  - `13` was opened and confirmed as spinner-only. The other five are inferred from their near-identical size.
  - The deck may therefore embed blank captures (not opened).
- **No PDF exists in the repository** [E].

---

## 3. Reference repositories (read-only)

Search was bounded to sibling folders under `/Users/user/Documents/GitHub/` and `/Users/user/Documents/` whose names match survey/ICT/sustainability. The home directory was not scanned. Full verdicts are in `reuse-matrix.md`.

| Repository | Path | Commit | Nature |
|---|---|---|---|
| Survey (the "ICTTool/Survey" reference) | `/Users/user/Documents/GitHub/Survey-statamic6` | `main` @ `3f24fe44` (2026-09-18), clean | Laravel 13.6 + Statamic 6.15 Core + MySQL 8.0 + Blade/Antlers survey addon; operator-driven deploy to one GCE VM |
| Survey predecessor | `/Users/user/Documents/GitHub/statamictaleed` | `main` @ `e968cec` (2026-04-22), 1 uncommitted change | Older marketing site plus first survey addon; Laravel 10, **Statamic 4 Pro**, Sanctum 3 |
| Sustainability | `/Users/user/Documents/GitHub/sustainability-diagnostic-tool` | `main` @ `2b8ec66` (2026-09-11), clean | Laravel 13.26 + Statamic 6.28 Core + Fortify + Vue 3/Inertia 3 + MySQL 8.4; dedicated GCE VM |
| Sustainability starter (non-git) | `/Users/user/Documents/taleed-sustainability-diagnostic-repository-starter` | n/a | Prompt/doc starter; `prompts/` identical to the repo's; the model this procurement package follows |

**No separately named "ICTTool" checkout exists** in the bounded paths. Survey-statamic6 is treated as the Survey/ICTTool reference **[E for absence; identity of "ICTTool" = Survey is an inference]**.

### 3.1 Survey-statamic6 — key facts [E]
- **Auth:** one `web` guard with provider `driver: statamic` (file users). `config/statamic/users.php` guards `cp => web, web => web`. Respondents use custom coordinator credentials held in `surveys_instances.meta` JSON and SHA-256 hashed bearer tokens. **Section-assignment tokens are stored in plaintext.** Tokens are accepted from `?access_token=` query strings.
- **Fail-open defects:**
  - `routes/api.php` uses `auth:sanctum` although Sanctum is not installed.
  - `POST api/surveys/instances` is an unauthenticated test route, and `/survey-test-data` is public.
  - `EnsureSurveyInstanceAccess.php:34-39` skips token checks when `APP_ENV=local`.
  - The delegation login is unthrottled.
- **Core behaviour confirmed in vendor code:**
  - `vendor/statamic/cms/src/Http/Middleware/CP/CountUsers.php` throws `StatamicProRequiredException` when there is more than one user, and `MakeUser.php:73` refuses to create one.
  - Addon `Permission::register` calls do nothing in Core.
- **Domain:**
  - Template import is parse → preview/diff → commit, with a checksum and a parser registry. This is good.
  - Weaknesses: re-import upserts a version in place; scores are **computed live** from mutable weights, so history changes on re-import; cascade and hard deletes; sign-off stored in meta JSON and overwritten on reopen; no audit or outbox.
- **Ops:**
  - `php:8.4-fpm` with an unpinned `composer:latest`. Dev MySQL 8.0 publishes 3306 on all interfaces.
  - Production: nginx binds host 80/443 directly with a Let's Encrypt **IP** certificate; no scheduler service; named volumes on the boot disk with **no mount check**; `bootstrap/cache` persisted as a volume, causing stale-cache 404s.
  - Deploy builds images on the VM, uses `rsync --delete` with a preserved-path list, and runs `migrate --force`.
  - Backup: `mysqldump` **without `--single-transaction`**, stored on the **same VM disk**, and it copies `.env.production`. Restore does not verify SHA256SUMS.
  - `--dry-run` in `deploy-local.sh` skips the upload and the remote run (good). Rollback swaps images only, never the database (good).
- **Tests:** about 68 PHPUnit methods on in-memory SQLite. **No CI.**
- **Security hygiene — owner action recommended, outside this project's scope:**
  - `README.md` contains plaintext credential-like values near the top and later in the file. Operations docs contain production identifiers.
  - `statamictaleed` tracks `.env`, `.env.docker`, `src/env.txt`, user YAML files and SQLite state.
  - These values should be considered exposed and rotated. They were **not** copied into this repository or its docs.

### 3.2 Sustainability — key facts [E]
- **Auth: an independent Eloquent guard, but inverted relative to Statamic's documented layout.**
  - The default `web` guard = Statamic file provider (the CP). The `app` guard = Eloquent `App\Models\User`. Fortify uses `guard => app`.
  - This inversion made Statamic swap in its own password broker, which broke app-user resets (a 500 and an enumeration leak). It needed `app/Auth/ApplicationPasswordBrokerManager.php` to fix.
  - `tests/Feature/StatamicSingleUserConstraintTest.php` fails CI if a second Statamic user appears. ADR 0004 records the Core rationale.
- **App-user security:**
  - Fortify email verification is on. Login is throttled 5/min per user+IP; registration 3/min and 20/day.
  - Invitations are stored as a SHA-256 hash with expiry; the accept route is unthrottled.
  - **No MFA for app users** (open decision #9).
- **Domain:**
  - `HasOptimisticLock` (conditional UPDATE → `StaleRecordException`).
  - `BelongsToRevision` model immutability. Snapshot + checksum inside the submit transaction; notifications after commit.
  - Append-only `audit_events`, enforced by the model only (no DB grants).
  - **No idempotency keys and no outbox.**
  - 14-state reviewer/accreditation workflow and emission factors: **Sustainability-only, not to be imported**.
- **Tests:** about 487 PHPUnit methods, SQLite plus a **MySQL 8.4 suite** (`phpunit.mysql.xml`, dedicated test DB); Larastan level 5.
- **Infra:**
  - Digest-pinned multi-stage Dockerfile: composer → node → `php:8.4-fpm-alpine` running nginx+fpm under supervisord as `www-data`, with entrypoint roles web / queue / scheduler / migrate.
  - Dev compose publishes MySQL as 3307, plus Redis and Mailpit. Local HTTPS uses nginx + mkcert with a `*.test` hostname.
  - Production: only the edge publishes 80/443; DB sessions/cache/queue; `SESSION_SECURE_COOKIE` and `SESSION_ENCRYPT` on; **no scheduler service**; named volumes on the **boot disk** (no data disk, no mount check); **migration uses the runtime DB user**.
- **Deploy:**
  - `deploy-local.sh`: CI-green gate, `git archive` + sha256.
  - `deploy-remote.sh`: `flock` lock, preflight (≥5 GB free, DB volume must exist unless `--first-deploy`), backup, migrate once, switch, verify `/health` reports the release, auto-rollback only if no migration ran. A destructive-command guard is in `lib.sh:267-269`.
  - **Caveat:** its `--dry-run` still uploads the bundle and writes settings on the server (`scripts/prod/deploy-local.sh:224-227`). That makes it **not an offline plan**.
- **Backup:** `mysqldump --single-transaction`, volume tars, SHA256SUMS, off-site GCS copy without `.env`. `restore-backup.sh --verify-only` loads into a scratch schema. A real restore goes into a **new** schema that is then repointed.
- **Topology [E from docs]:** a **dedicated** VM with its own edge and IP certificate, in the same GCP project as Survey. **Not on the Survey VM.** `04-OPERATIONS.md`'s assumption that Survey and Sustainability may share one VM is therefore not supported by current evidence.

---

## 4. Official-source verification (fetched 2026-09-30)

| Fact | Source | Status |
|---|---|---|
| `statamic/cms` latest v6.34.1 (2026-09-23); `laravel/framework: ^12.40.0 \|\| ^13.0`; PHP ≥ 8.3; upgrade guide recommends Laravel 13 + PHP 8.5 | packagist.org/packages/statamic/cms; raw `statamic/cms` 6.x `composer.json`; statamic.dev/upgrade-guide/5-to-6; statamic.dev/requirements | [E] |
| v6.0.0 released "28 Jan" (year not shown; presumed 2026) | github.com/statamic/cms/releases/tag/v6.0.0 | [U] year |
| Core: **one admin user**, one form; Pro: roles & permissions, REST/GraphQL, multi-site, revisions, Git integration | statamic.dev/licensing; statamic.dev/users | [E] |
| Independent guard: add `statamic` session guard + `statamic` provider; `config/statamic/users.php` `guards => ['cp' => 'statamic', 'web' => 'web']` | statamic.dev/tips/using-an-independent-authentication-guard | [E] |
| **Caveats on that page:** both guards **share one session**, so `session()->invalidate()` also logs out the CP. With a file repository and an Eloquent web guard, Statamic tries to write `last_login` to the app model's table. Statamic user tags and forms do not work for app users | same | [E] |
| Laravel 12: security fixes until 2027-02-24. Laravel 13: released 2026-03-17, PHP 8.3–8.5, bug fixes until Q3 2027, security until 2028-03-17 | laravel.com/docs/13.x/releases | [E] |
| Sanctum SPA: stateful domains, `statefulApi()`, `GET /sanctum/csrf-cookie`; `config/sanctum.php` `'guard' => ['web']` lists the guards checked | laravel.com/docs/13.x/sanctum; laravel/sanctum 4.x config | [E] |
| Fortify v1.40.0 (MIT), illuminate ^11–^13: registration, reset, email verification, TOTP 2FA + recovery codes, passkeys; guard is configurable | packagist laravel/fortify; laravel.com/docs/13.x/fortify | [E] |
| PHP 8.4: active support to 2026-12-31, security to 2028-12-31. PHP 8.5: active to 2027-12-31, security to 2029-12-31 | php.net/supported-versions.php | [E] |
| MySQL 8.4.0 GA 2024-04-30 (LTS: 5 years premier + 3 years extended). 8.0 entered Sustaining Support 2026-04-21 | dev.mysql.com release notes; mysql-releases; EOL notice | [E]; exact LTS end dates [U] |
| No partial or filtered index syntax; UNIQUE allows multiple NULLs; unique secondary indexes on generated columns are supported | dev.mysql.com/doc/refman/8.4/en/create-index.html; create-table-secondary-indexes | [E] |
| PhpSpreadsheet 5.10.0 MIT (PHP ≥8.2); maatwebsite/excel 4.0.3 MIT (PHP ^8.3, Laravel ^12\|^13); openspout 5.12 MIT (PHP ≥8.4); dompdf 3.1.6 **LGPL-2.1**; dompdf defaults `isRemoteEnabled=false`, `isPhpEnabled=false` | packagist; dompdf `Options.php` | [E] |

`SOURCES.md` S2, S3 and S4 use older URL paths (`/getting-started/licensing`, `/getting-started/requirements`, `/knowledge-base/tips/...`). The current paths are listed above.

---

## 5. Source conflicts to record, not silently resolve

| # | Conflict | Sources | Proposed handling |
|---|---|---|---|
| C1 | `AGENTS.md`/`CLAUDE.md`/`MASTER.md` require `START-HERE-PRODUCTION.md` and `prompts/production/*`, which are absent | §1 | D-00: add the verified files unchanged in Phase 1 |
| C2 | `README.md` (dated 15 Sep) describes Program Manager, a 2025 history, methodology/privacy pages, cycle selection, framework/cycle/audit/data screens and no lockfile | `README.md:3,19-25,33-51,84,106` vs commits `8694096`, `930aa94`, `ed739a9`, `4a36be5` | Code and 3-role model win. Update README in the phase that changes behaviour; do not restore removed scope |
| C3 | `docs/user-journeys.md` lists `/methodology`, `/privacy`, `/app/frameworks`, `/app/cycles`, `/app/audit`, `/app/data`, sector and Program Manager; `docs/architecture.md:63-82` has a 4-role matrix; `docs/acceptance-checklist.md:31` mentions sector; `seed.ts:16` mentions "audit and demo data controls" | files cited | Same as C2 |
| C4 | `MASTER.md §3` / `02-ARCHITECTURE.md` say Statamic should serve "help/privacy/methodology". Commit `ed739a9` **removed** the methodology and privacy pages from the approved UI | `ed739a9` | **Owner decision** D-12: which CMS-managed surfaces exist. Proposed default below |
| C5 | Registration collects **consent**, but no privacy notice page exists after `ed739a9` | `Auth.tsx:28-36` | Tied to D-12 |
| C6 | SPA "People & access" creates staff directly; `07-API-CONTRACT.md` and `03-DATABASE-SCHEMA.md` specify expiring invitations | `Administration.tsx:22-30` | D-10: keep the same form; the server issues an invitation |
| C7 | Staff org detail shows **draft N/40 progress** and stage; `MASTER §5` says staff never see draft answers | `Organizations.tsx:10-27` | Compatible: return counts and stage metadata only, never answer values (D-11) |
| C8 | `01-REPOSITORY-AUDIT.md` says Survey could not be inspected and that Portfolio shows "cycle context" | audit vs §3, `ed739a9` | Superseded by this discovery; cycles remain in the data model, with no cycle picker in the UI |
| C9 | `04-OPERATIONS.md` assumes Survey and Sustainability may share one VM and edge; evidence shows separate VMs, each app-nginx owning 80/443 | §3 | Production placement is an **open production blocker** (D-15), not a Phase 1 blocker |
| C10 | `04-OPERATIONS.md` proposes Caddy for local HTTPS; both references use nginx + mkcert | §3 | D-07 selects nginx + mkcert |
| C11 | `deploy.yml` publishes every `main` push to GitHub Pages without tests; after backend integration the Pages build would be a broken or demo-mode SPA | `.github/workflows/deploy.yml` | D-16: restrict to manual dispatch in Phase 1 (needs approval) |
| C12 | `.gitignore` ignores `package-lock.json`; `MASTER §3` requires committed lockfiles | `.gitignore`, `b8a2f5d` | Phase 1 un-ignores it and commits a real lockfile |
| C13 | Six demo screenshots are spinner-only, yet serve as visual baselines | §2.8 | Re-capture loaded screens in Phase 3 |
| C14 | Sustainability `--dry-run` uploads to the server; `MASTER §10` requires offline plans to be non-mutating | §3.2 | Adopt Survey's dry-run semantics; see reuse matrix |
| C15 | Statamic's docs example uses `repository => 'file'` for the single admin; production must then persist `users/` as mutable state | statamic.dev tip; Survey/Sustainability volumes | D-04: file repository, persisted and backed up |

**Content approval vs concept approval.** The owner reports client approval of the **UX and workflow concept**. `framework.json` carries its own `sourceStatus` field for the **content** (40 questions, 64 actions, interpretations, attribution "Aramco Taleed & Roland Berger"). Formal publication approval of that content, recorded as an `approval_reference` on `framework_versions`, is **separate and still required before production** [U]. Nothing in this phase treats UX approval as content approval.

---

## 6. Proposed schema and API contract (summary)

The detailed baselines remain `03-DATABASE-SCHEMA.md` and `07-API-CONTRACT.md`. Discovery **confirms** them against the SPA, with these adjustments (details in `decisions.md`):

1. **Identity.**
   - `app_users` uses ULID string keys. Laravel's `sessions.user_id` and `password_reset_tokens` must be adjusted to a string identifier (D-05).
   - Add nullable `last_login` to `app_users` (or neutralise Statamic's write). Phase 1 verifies this against the Statamic caveat (D-03).
2. **Organisations.** No `sector` column (removed from the approved UI). `country_code` and `size_band` use the SPA's 8 countries and 5 ranges as validated enums; the enum values are frozen from `Company.tsx` in Phase 2.
3. **Cycles.** Keep `assessment_cycles` and pin to a framework. The UI shows the single current open cycle, and `GET /cycles/available` returns an ordered list. The SPA uses the first. Cycles are created by an audited artisan command, not a UI (D-13).
4. **Staff progress.** Add a read model or query for `answered_count` per open draft (C7). Expose counts and stage only.
5. **Staff creation.** `POST /staff/invitations` backs the existing "create staff" form (C6).
6. **Exports.**
   - Server-side CSV/XLSX replaces client ExcelJS for staff exports (D-14).
   - The "Export result JSON" action for a Champion's own result becomes `GET /assessments/{revisionId}/result` rendered as a download. No new server artefact is needed.
7. **No server PDF in the first release.** Keep the approved `window.print()` A4 report (D-14).
8. **Band identifiers** in JSON: `foundational|developing|advanced|best_in_class` exactly as `types.ts`. Answers use `yes|no|null`.

Transitions (create/resume draft, save with `expectedVersion`, idempotent submit with snapshot and pointer, correction) are as specified in `03-DATABASE-SCHEMA.md §Atomic operations`. They match the SPA's command semantics, including one draft per organisation and cycle and correction only from the effective revision.

---

## 7. Authentication separation (summary)

| Aspect | Application users | CMS administrator |
|---|---|---|
| Guard / provider | `web` session guard → `app_users` Eloquent provider (**Laravel default guard**) | `statamic` session guard → `statamic` provider |
| Statamic config | `config/statamic/users.php` `guards.web = web` | `guards.cp = statamic`, `repository = file`, exactly one user |
| Password broker | `app_users` (Laravel DB token table) | Statamic `users` / `activations` brokers |
| Auth features | Fortify on `web` (login, reset, TOTP 2FA); custom six-digit verification challenge; custom invitation accept; Sanctum SPA stateful with `'guard' => ['web']` only | Statamic CP login and Statamic 2FA |
| Roles | `champion`, `analyst`, `admin` + `can_export` in `app_users` | none (Core); a single super user |
| Access | `/api/procurement/v1/*` | `/cp/*` only |

**Shared-session caveat [E, Statamic docs].** Both guards live in the same Laravel session cookie. Consequences:
- An app logout that invalidates the session also ends a CMS session in the same browser. This is acceptable and fails safe.
- The business API must never accept the `statamic` guard. This is enforced by Sanctum `guard => ['web']` and route middleware `auth:web`, and is tested.
- The CMS admin should use a browser profile separate from app testing. Recommended: restrict `/cp` at the edge (IP/IAP) in production [P].

Why not Sustainability's layout: it made Statamic own the default guard, which caused the password-broker 500 and enumeration leak. The documented layout avoids that failure class. The reset-uniformity regression test is still adopted.

---

## 8. Durable-data inventory (production)

| State | Classification | Handling [P] |
|---|---|---|
| MySQL data directory (procurement DB) | Durable, authoritative | Dedicated non-boot persistent disk; external volume; fail-closed marker check |
| Statamic content (`backend/content/**`, including collections, entries, globals, navigation, trees) | Durable if CP-editable | Inventory in Phase 1: developer-owned structure is baked into the image; CP-editable content persisted on the data disk |
| Statamic single admin (`backend/users/*.yaml`) | Durable, secret-adjacent | Persisted on the data disk and backed up; never committed with real credentials |
| Statamic asset files + `.meta` + asset container state | Durable if assets are used | Persisted path on the data disk (or not enabled) |
| `storage/app/private` (exports, reports) | Durable, private, retention-bound | Persisted private path; expiry job |
| `storage/framework`, `bootstrap/cache`, Stache cache | Rebuildable | **Not** persisted; built or warmed per release (avoids Survey's stale-cache defect) |
| `APP_KEY`, DB runtime / migration / backup credentials, mail credentials | Stable secret | Host-side restricted env file or secret store, recoverable separately; never generated at boot; never inside backups on the same disk |
| Compiled SPA, `vendor/`, `public/vendor`, PHP code, blueprints | Release artefact | Inside the immutable image |
| Backups | Durable, off-VM | Encrypted, restricted bucket; manifest + checksums |

---

## 9. Safe deployment approach (summary, production not in scope yet)

- Two persistent environments only: local and production. CI databases and isolated restore drills are temporary.
- The release path combines Sustainability's serialised pipeline with Survey's non-mutating dry run:
  1. An **offline `plan`** that touches nothing remote.
  2. A separately authorised connected `preflight` (disk identity, mount, marker, free space, DB identity).
  3. Backup with `--single-transaction` plus a coordinated file manifest, copied off-VM.
  4. Migrate once with **separate migration credentials**.
  5. Switch app, worker and **scheduler** together.
  6. Verify that `/health` reports the new release.
  7. Image-only rollback when the schema is compatible.
- Build images from locked commits (CI or local), not ad hoc on the VM, where practical.
- Placement (dedicated VM vs co-location with Survey or Sustainability), DNS name, certificate, data disk and mail are **production blockers to confirm** before Phase 5. They do not block Phase 1.

---

## 10. What was not done in this phase
- No `npm install`, no `npm run check`, no Playwright run, no PHP or Composer execution, no Docker build, no MySQL. The 28/28 and 14,641-case results are **inherited evidence**, not reproduced.
- Demo PPTX/DOCX were not opened. Workbook cells were not re-read (the hash matched).
- No production host, bucket, DNS, mail relay or GCP project was contacted or read.
- No commit or push was made.
