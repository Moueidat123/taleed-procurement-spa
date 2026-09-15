# Taleed Procurement Self-Assessment

**React + Redux Toolkit single-page prototype · prepared for Fadi Zahhar · 15 September 2026**

A working source implementation of the company and Taleed staff journeys, using a local browser dataset. It follows the approved prototype's navy/blue/amber visual foundations and the supplied 40-question procurement framework. This is a **local demonstration, not a production Statamic application**.

## Start here

Use Node **22.12 or newer in the 22.x line**, or Node 24+. Node 22 is selected in `.nvmrc`.

```sh
# Inside the extracted taleed-procurement-spa folder:
npm install
npm run dev
```

Open the address printed by Vite, normally `http://127.0.0.1:5173/`. Follow **Sign in**, then choose **Company Champion** to resume the 12-answer draft, **Second Company** to see a completed result, or a staff perspective. No `.env`, API key, database, email provider or backend is required.

**First-install note:** No package lock is fabricated or included. Package-registry access was unavailable during preparation. The first successful `npm install` generates the real `package-lock.json`; review and commit it. Subsequent reproducible installs should use `npm ci`. Do not bypass peer-dependency errors with `--force` or `--legacy-peer-deps`; investigate and record a compatible resolution.

## What has actually been verified

The dependency-free domain and persistence modules were compiled with strict TypeScript, then **28 automated tests passed**. One test enumerates **all 14,641 four-domain score-count combinations**. All 28 source TypeScript/TSX files passed syntax and relative-import checks.

The actual React/Vite dependencies could not be downloaded in the preparation environment. Consequently **a full installed-dependency type check, Vite build, lint run, and browser acceptance run have not passed here**. Fourteen Playwright scenarios are supplied for execution on desktop/mobile Chromium. Read `docs/verification.md` and run the commands below before presenting the prototype as browser-accepted. This is a source handover, not a claim of deployment or final client acceptance.

## Demonstration identities

All company names and personal records are synthetic. These addresses belong to the local dataset, not an email system.

| Perspective | Email | Starting state |
|---|---|---|
| Company Champion | `sahara@example.com` | Sahara Industrial Solutions; 12/40-answer 2026 draft and a 2025 historical result |
| Second company | `namaa@example.com` | Namaa Logistics; 60% Developing result |
| Taleed Analyst | `analyst@example.com` | Submitted portfolio; exports disabled initially |
| Program Manager | `manager@example.com` | Portfolio, corrections, framework drafts/publication, cycles and access |
| Super Admin | `admin@example.com` | Staff features, audit and local data controls |

Shared demo password: **`TaleedDemo!2026`**. Simulated verification code: **`123456`**.

Three more synthetic company accounts, `desert@example.com`, `atlas@example.com`, and `horizon@example.com`, demonstrate 30%, 82.5%, and 70% results. Newly registered demo users also sign in later using the shared demo password: registration/reset password fields validate the intended UI but **discard the password**.

The app intentionally uses a fixed demonstration date of **15 September 2026** so cycle availability remains repeatable. `src/domain/seed.ts` contains that clock. This is not a production time source.

## Included interfaces and behavior

**Public and onboarding:** introduction, methodology, privacy notice, sign-in, registration with inline validation, simulated email verification/resend, simulated forgot/reset password, organization setup and authority declaration.

**Company:** dashboard, cycle selection, saved progress, four ten-question sections, explicit Yes/No/clear actions, missing-answer links, review with declaration/confirmation, immediate result, domain scores, three relative focus areas, 16 recommendations, submitted answers, printable report, result JSON and revision history.

**Taleed:** filtered portfolio and denominators, organization directory/detail, submitted-answer visibility, same-cycle company comparison, controlled correction creation, cycle create/edit/open/close, framework clone/draft wording edit/simulated publication, staff account/access management, permission-controlled CSV/XLSX, audit activity and administrator backup/import/reset.

**Recovery and presentation:** role switcher, empty/forbidden/missing routes, loading fallback, rendering error boundary, native confirmation dialogs, visible save failures, stale-tab conflict handling, corrupt-data recovery, typed destructive confirmation, responsive CSS, keyboard labels/focus, reduced-motion and A4 print styling.

A detailed route and validation matrix is in `docs/user-journeys.md`. Test steps are in `docs/acceptance-checklist.md`.

## Preserved assessment rules

Exactly four domains, ten questions per domain, string IDs `1.1` through `4.10`. `yes` = 1, `no` = 0, `null` = unanswered. No N/A, no partial credit, no default answer. Progress counts both Yes and No. No final result is produced for an incomplete draft.

Overall = Yes count / 40 × 100. Domain = domain Yes count / 10 × 100. Use **unrounded** values for the bands: Foundational 0–40%; Developing >40–65%; Advanced >65–80%; Best-in-Class >80–100%. Display one decimal place.

Recommendations use each domain's band, not the overall band: four actions per domain, 16 displayed. Three lowest domain scores are the relative focus areas; ties use the original domain order. "Best-in-Class" is a framework label, not certification. There is no mandatory reviewer approval before a company sees its submitted result.

## Technology and organization

The manifest pins **React/React DOM 19.3.0**, **Redux Toolkit 2.12.0**, and **React Redux 9.3.0**, and uses Vite 8.1-compatible tooling. The React/Redux core versions were checked against official releases on 15 September 2026. Other dependencies are specified as compatible ranges, not a claim that every package is the latest release. See `docs/dependencies.md`.

```text
src/
  app/                 Redux store, typed hooks/selectors, routing and guards
  components/          Shared UI primitives and application shell
  data/framework.json  Forty source questions and sixty-four recommended actions
  domain/              Types, rules, scoring, policies, commands, validation and seed
  features/
    public/            Landing, guidance and simulated identity journey
    company/           Profile, workspace and history
    assessment/        Questionnaire, review, results, advice and report
    staff/             Portfolio, organizations, comparison and administration
  infrastructure/      Repository adapter, JSON/CSV/Excel download adapters
  styles/              Approved-reference tokens, responsive UI and print styles
scripts/               Core-test runner and registry verification
tests/                Core tests and Playwright scenarios
 docs/                 Architecture, journeys, verification and Codex prompts
```

Business rules do not import React, Redux, a storage API or Statamic. Domain commands are serializable. The repository writes before Redux displays success. Forms and transient UI state remain local; shared business records are normalized in Redux. These choices make the future server transition explicit rather than pretending localStorage is a backend.

## Development commands

```sh
npm run dev                   # Local development
npm test                      # Strict core compilation + 28 Node tests
npm run typecheck             # Full project type check after installing dependencies
npm run lint                  # React/TypeScript lint
npm run build                 # Type check + static Vite build to dist/
npm run preview               # Serve the built dist/ output locally
npx playwright install chromium
npm run test:e2e               # Desktop + mobile Chromium scenarios
npm run check                 # Type check, lint, core tests, build
npm run verify:dependencies    # Read-only registry check of the core stack
npm run verify:dependencies -- --all
npm run format                # Format source, docs and tests
```

`.github/workflows/ci.yml` runs the checks and retains browser evidence; it deliberately does not deploy. Browser-test results are not a substitute for human review of the original approved design.

## Static hosting

After a successful build, host the **contents of `dist/`** on an authorized static host. Vite uses `base: './'` and React Router uses hash routes so repository subpaths do not require a server rewrite for application navigation. Serve via HTTP(S), not `file://`. The application is a SPA with many client-side routes, not a single source-file HTML mockup.

Local data belongs to one browser **origin**. Switching between `localhost`, `127.0.0.1`, a different port, or the eventual hosted domain creates a different dataset. Clearing site data or using private browsing can remove it. After dependencies are installed and a normal static build is served, this app makes no API/CDN calls for its business functions; no service worker/offline-installable PWA is claimed.

## Storage, privacy and limits

The key `taleed.procurement.prototype.v1` stores the validated business dataset. A selected synthetic user ID is kept in `sessionStorage` under `taleed.procurement.demo-session`; no password/token is persisted. All demo roles still share one inspectable local dataset. Browser DevTools can alter data or role state: **these are workflow guards, not security or tenant isolation**.

The bounded prototype allows at most 200 users, 100 organizations, 150 assessments and 500 retained local audit events, with a 3.5 MB import/storage guard. It serializes and validates whole-dataset writes. It is not intended to scale to a real client portfolio or concurrent users. Web Locks serialize cooperating same-origin tabs when available; without Web Locks, use one tab only. Unsupported/corrupt schema data is retained for deliberate recovery, not silently reset.

Reports use browser Print / Save as PDF. CSV, XLSX and JSON are actual file outputs when run in a browser. The PDF button is not a server job. Audit events are local diagnostic history, not tamper-proof evidence. Use dummy data only, and obtain permission before publicly distributing the proprietary assessment content or the Taleed presentation.

## Continue or rebuild with Codex

For this source project, read `AGENTS.md`, then paste `docs/prompts/CONTINUE_AND_VERIFY.md` into Codex. It instructs the agent to install, validate and fix this implementation rather than replacing it.

For a new project, use the separate **Codex starter ZIP** with its `reference/framework.json`, design tokens, requirements and `BUILD_FROM_SCRATCH.md`. The same full master prompt is included at `docs/prompts/BUILD_FROM_SCRATCH.md` here. Do not rely on a prompt alone to reproduce proprietary wording: keep the complete reference JSON beside it.

## Future Statamic handover

`docs/architecture.md` describes replacing the local repository/identity simulation with Statamic/Laravel services and a real approved database. Server authorization, transactions, email delivery, private reports, idempotency, retention, backups and recovery remain future implementation work. The earlier SQL Server direction is documented context, not a verified production connection. No backend, cloud infrastructure, deployment or cross-tool SSO was changed by this package.
