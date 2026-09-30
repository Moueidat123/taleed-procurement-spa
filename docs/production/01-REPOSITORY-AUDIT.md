# Repository audit and evidence

**Review date:** 30 September 2026. **Input:** `taleed-procurement-spa-main.zip`. The user reports approval of the demo's UX/UI and concept flow. This is not evidence that production controls, operational infrastructure, source-content publication or a SLA have already been approved.

## What was inspected
The review covered application routing, Redux state, domain commands/types/policies/scoring/validation, framework JSON, local persistence, current CI/Pages workflows, source documentation, the source workbook, the 25-slide walkthrough's text, its speech-script text, and selected supplied screenshots. The core test suite was executed in an extracted copy.

No PDF exists in the uploaded ZIP. The supplied source documents are a workbook, Markdown, a DOCX script, a PPTX deck and screenshots. Do not claim to have reviewed a missing PDF. Additional local PDFs should be inventoried by the implementing agent if present.

Selected files from the connected private `FadiZahhar/sustainability-diagnostic-tool` repository were also read. No sibling working directory on the owner's computer, Google Cloud VM, live database, DNS configuration or production disk was inspected. Searches for accessible `survey-statamic6`, `survey` and `ICTTool` repositories did not return a directly accessible repository; Survey observations below are indirect reference-document evidence.

## Current application inventory
| Evidence | Finding | Production consequence |
|---|---|---|
| `package.json` | React 19.3.0, Redux Toolkit 2.12.0, React Redux 9.3.0, Vite ^8.1.0, TypeScript ~5.9.3 declared | Verify resolution and compatibility; preserve stack rather than upgrading unrelated majors |
| ZIP inventory | No `composer.json`, PHP backend, Docker setup or `package-lock.json` | Add backend in a subdirectory; generate and verify a real lockfile |
| `src/app/App.tsx` | HashRouter and role-based React routes | Preserve paths/flows; explicitly test any move to server-backed browser routing |
| `src/app/store.ts`, `src/infrastructure/localRepository.ts` | Browser-local database and session simulation | Replace with authorized request-specific API resources; never send all tenants' records to the browser |
| `src/features/public/Auth.tsx` | Shared demo password, fixed six-digit verification, simulated recovery | Implement real credentials, email ownership, reset tokens, session lifecycle and rate limiting |
| `src/domain/seed.ts` | Synthetic users and fixed demonstration date | Local-only explicit fixtures; real server clock in production |
| `.github/workflows/deploy.yml` | GitHub Pages deployment, not GCP deployment | Replace or explicitly isolate demo publication; do not publish backend or private source materials |
| `.github/workflows/ci.yml` | `npm install`, checks and browser workflow | Lock dependencies; use reproducible installation and actual MySQL backend tests |
| `src/features/staff/Portfolio.tsx` | Name/band filters, cycle context, effective revisions, draft count metadata | Server-side eligibility query; separate draft-count query with no draft answers exposed |

## Approved-role and route drift
The implemented roles and the latest walkthrough are **Champion, Analyst, Super Admin**. Some older Markdown mentions Program Manager. Do not add this fourth role without an explicit scope decision.

Visible company routes include dashboard, profile, assessment sections/review, results, recommendations, responses, report and history. Staff routes include portfolio, organizations, organization details and comparison. Super Admin additionally has People & access. `Frameworks.tsx` and cycle/audit components exist, but their management routes are not registered in the current `App.tsx`. `DataControls` is also a local recovery/reset facility. Existing code does not make all dormant features approved visible scope.

Framework import/publication, cycle creation and initial account provisioning are necessary operating capabilities. Provide explicit audited administration commands first. A new visible management interface requires a small recorded scope/UX decision; do not quietly restore all old menus.

The current portfolio UI does not show a sector filter. Older documents are not permission to add sector data or silently change approved profile fields. Comparison supports two to four submitted companies and must remain within a compatible cycle/framework.

## Source and scoring evidence
Workbook: `Procurement_Self Assessment Tool_v01.xlsx`.
SHA-256: `e86755a94db9bb4c2d14c3bf1f60eb237189d4a5bc1fd04efbb8d4faf197eecd`.

Using artifact_tool to read the source cells, the review found 40 question texts and 64 recommendation texts matching `src/data/framework.json`. Recommendation comparison removed leading bullet markers and surrounding line whitespace only. This was a source-text comparison, not an Excel recalculation. See `evidence/workbook-audit.json`.

Four domains have ten questions each. Yes = 1; No = 0; unanswered remains incomplete. Domain score = Yes count × 10. Overall = total Yes × 2.5. Exact bands are `<=40`, `>40 and <=65`, `>65 and <=80`, `>80`. The presentation's simplified labels such as 41–65 must not change code classification of a 40.5 or 42.5 result. Preserve question IDs as strings: `1.10` is not `1.1`.

Select four actions for each domain's own band, giving sixteen recommendations. Rank three relative focus areas by score, then source domain order for ties. At all-100%, describe sustaining strengths rather than inventing a performance gap. Published source content and submitted snapshots must remain versioned.

## Verified checks and visual limits
`npm run test:core` exited 0: **28 tests passed, 0 failed**. Its scoring coverage includes **14,641 domain Yes-count combinations**, not every binary answer permutation and not end-to-end production security. Full output is in `evidence/core-test-output-2026-09-30.txt`. The review used Node 22.16.0 and the available global TypeScript 5.8.3 compiler, not an installation of the package-declared TypeScript ~5.9.3. It therefore does not prove the uninstalled locked dependency toolchain works.

The supplied question-screen screenshot was inspected. `demo/screenshots/07-review-answers.png` was inspected and contains only a loading state. Other unusually small screenshots must be checked before being used as visual baselines. Re-capture loaded screens from a working baseline; do not accept a loading spinner as the approved final screen.

No full npm dependency installation, production frontend build, Playwright run, PHP execution, Docker execution, migration, database restore test or real deployment was completed in this review.

The package also includes an independent integer-basis-point scoring oracle. All **14,641 generated cases matched the uploaded compiled TypeScript engine** for overall Yes count/score/band, domain bands and stable focus order. This is not a PHP test. See `evidence/count-oracle-check-2026-09-30.json` and `reference/verify-count-oracle.cjs`.

## Sustainability / Survey reuse findings
The Sustainability README still describes an earlier starter and a Vue/Inertia + Statamic Pro direction. Current code search also shows implemented backend and operational scripts. This mismatch itself means README statements cannot be treated as live infrastructure evidence.

Read evidence includes `scripts/prod/deploy-local.sh`, last modified 11 September 2026. It uses a committed Git archive, green-CI check, operator-side orchestration and a detached remote runner, helping a deployment survive an SSH disconnect. These are useful patterns to evaluate. Do not copy its architecture or script verbatim.

In particular, the local deployment script's `--dry-run` path still uploads a bundle and calls remote bundle/settings-writing functions before invoking the remote dry run. A procurement **offline plan** must be genuinely non-mutating; distinguish it from a separately authorized connected validation. Also do not carry over routine CI-bypass flags into an automatic production pipeline.

Indexed `scripts/prod/deploy-remote.sh` checks a project-named MySQL volume and rejects `--first-deploy` when that volume exists. This is useful but does not establish that the volume is on a retained non-boot disk or that backups are recoverable.

Sustainability's `docs/discovery/icttool-audit.md` reports an older Survey reference using Nginx, PHP-FPM, a worker, Certbot and MySQL 8.0 named volumes, with no scheduler service in the cited Compose. This is **indirect documentation**, not a read of current Survey source or its VM. Older managed-database-only guidance conflicts with the present single-VM/MySQL requirement; record the new decision rather than mixing them.

Local agents must inspect authorized current sibling source, copy patterns only after review, and verify actual VM resources separately with permission. Do not reuse remembered domains, resource IDs, IPs or database names as deployment configuration.
