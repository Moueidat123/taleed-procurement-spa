# Executed verification record

**Preparation date:** 15 September 2026  
**Scope:** Source handover of a local React/Redux prototype; not deployment or client acceptance.

## Executed checks

| Check | Actual result | Evidence / qualification |
|---|---|---|
| Strict TypeScript compilation of domain + local repository | PASS | `tsc -p tsconfig.core.json`, through `npm run test:core`; environment compiler 5.8.3 |
| Node automated core suite | PASS: 28 tests, 0 failures | `docs/core-test-output.txt` contains the actual TAP output |
| All domain score-count combinations | PASS: 14,641 combinations | One of the 28 tests; 11 possible Yes counts in each of 4 domains, not 14,641 browser journeys |
| Source content structural checks | PASS: 40 question IDs and 64 recommendation action IDs | Core suite validates counts, distinct string IDs and expected source shape |
| Source TypeScript/TSX parse and relative-import existence | PASS: 28 source files + 3 config/test files | `docs/syntax-check-output.txt`; parser/transpiler and internal-import path check, not an installed-dependency type check |

The core tests also cover exact score boundaries, domain recommendations/ties, invalid answers, clearing, finalization/declaration/idempotence, revisions/history, role/organization checks, duplicates, cycle/version/publication rules, draft content edits, quota/revision/corruption cases, malicious import keys, audit bounds and CSV escaping.

## Not completed in this environment

| Check | Actual status | Reason / next command |
|---|---|---|
| npm install and dependency tree | BLOCKED | Package registry network/DNS access unavailable; execute `npm install` in the local project |
| Genuine package-lock.json | NOT GENERATED | Generate from the successful install; no synthetic lock is supplied |
| Full application strict typecheck | BLOCKED | React/Vite/library type dependencies absent; execute `npm run typecheck` |
| ESLint/format command | NOT RUN | Dependencies unavailable; execute `npm run lint` / `npm run format` |
| Vite development runtime / production build | NOT RUN | Dependencies unavailable; execute `npm run dev` / `npm run build` |
| Playwright browser scenarios | NOT RUN | Actual SPA dependency/runtime unavailable; install Chromium and execute `npm run test:e2e` |
| Visual, keyboard, mobile and print acceptance | NOT RUN | Execute the browser checklist on the installed/built app |
| XLSX actual browser download/open | NOT RUN | ExcelJS/browser runtime unavailable; test CSV/XLSX in actual Excel-compatible software |
| npm audit / dependency license inventory | NOT RUN | Requires the real installed dependency tree |
| Statamic/backend/security/cloud tests | OUT OF SCOPE | No server or cloud application is included or modified |

Fourteen Playwright scenarios are provided. They are configured for desktop and mobile Chromium; the desktop-only skip-link keyboard scenario is intentionally skipped in the mobile project. They are **not** labelled passed. The test project count must not be described as successful browser execution.

The source parser and core compiler checks cannot catch every React type, package export, bundler, CSS, accessibility or browser interaction issue. The included `docs/prompts/CONTINUE_AND_VERIFY.md` instructs Codex to install, run the full checks and fix actual failures without redesigning the approved flow or concealing errors.

## Release gate before the PM demo

Use the declared Node baseline. Run `npm install`, `npm run check`, `npx playwright install chromium`, and `npm run test:e2e`. Then open the built app with `npm run preview`, review the PM walkthrough and print a company report. Record the actual browser/version, executed commands, commit/build reference, screenshots/traces, unresolved defects and PM decision. Do not describe the artifact as browser-accepted until that evidence exists.

The 40-row human checklist intentionally remains **Not run**. Core correctness evidence is not a substitute for human content approval, pixel-fidelity review or real-backend authorization/persistence testing.
