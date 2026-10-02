# Phase 3 acceptance evidence — 2 Oct 2026 (local only)

Raw outputs from this machine (macOS/arm64, Docker stack `taleed-procurement-dev`, MySQL 8.4). Each file ends with its exit code.

| Check | Result | File |
|---|---|---|
| TypeScript, ESLint | pass | typecheck-core-lint.txt |
| Core tests (scoring, framework, CSV) | 9/9 | typecheck-core-lint.txt |
| Frontend build | pass | frontend-build.txt |
| Backend PHPUnit (MySQL `_test` DB) | 125/125 | phpunit-mysql.txt |
| Larastan level 6 / Pint | 0 errors / clean | larastan-pint.txt |
| Playwright, local HTTPS against Docker + MySQL | 7 passed, 2 skipped | playwright-local-https.txt |

## Browser journeys (`tests/e2e-local/journeys.spec.ts`)
Each run seeds its own synthetic data with `procurement:dev:seed-e2e`, which only runs locally.
1. A champion starts an assessment. Answers autosave and survive a reload. Another session saves first, causing a 409 conflict; the champion chooses "Keep my answers" and both edits end up on the server. The champion then submits from review, sees the results and the effective-submission badge, and the report shows Print / save PDF. Editing after submission is refused.
2. An analyst signs in with TOTP. Server-side search and stage filters work. Compare shows two companies (Bravo at 60.0%). Export is disabled in the screen, and the API returns 403.
3. An analyst with the export grant downloads a CSV (BOM present; all seeded companies included) and an XLSX file (valid zip).
4. A Super Admin opens a correction; the original submission stays effective.

## App fixes found by these journeys
- Sign-in looped: a signed-out `/auth/me` 401 was treated as session expiry, which reset the cache and cancelled sign-in. `/auth/me` is now excluded.
- The two-factor code box reused the email field's value. The form now gets its own key.
- Assessment screens now reload the draft from the server when opened.
- The Vite dev server returned 504 for the lazily loaded Excel library. `exceljs` is now pre-bundled.

## Gaps (not claimed)
- The 2 skipped tests in `foundation.spec.ts` need `E2E_ANALYST_PASSWORD` and `E2E_CMS_PASSWORD`.
- Browser tests run locally only. CI no longer runs a browser job, because the old prototype suite was retired with the demo store.
- The seed command has no PHPUnit test of its own; the journeys exercise it.
