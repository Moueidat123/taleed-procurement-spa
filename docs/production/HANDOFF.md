# Implementation handoff

Updated: 30 September 2026. Phase: **00-DISCOVERY complete (documentation only)**; implementation has not started.

## Current state
Uploaded SPA reviewed. Core tests passed 28/28 in an extracted copy. Source workbook questions/recommendations match the JSON. Existing app source is unchanged. No backend, Docker environment, production schema or live link has been created by this package.

## Active assignment
Writer: Claude Code (Phase 0). Reviewer: unassigned. Branch `main`, base commit `c8c9a9d`; Phase 0 docs are uncommitted. Next action: owner review of `discovery.md` / `decisions.md` / `reuse-matrix.md`, then `prompts/production/01-FOUNDATION.md` on approval.

## Decisions proposed
Preserve React; Laravel + Statamic 6 Core under backend; separate Eloquent/Statamic guards; MySQL 8.4 LTS; same-origin HTTPS; local + production only; retained production data independent of code; explicit release authorization.

## Known evidence gaps
Local Survey (`Survey-statamic6` @ 3f24fe44) and Sustainability (`sustainability-diagnostic-tool` @ 2b8ec66) checkouts were read in Phase 0 (see `discovery.md §3`); no VM inspected. No PDF in upload. No package lock, PHP/Docker checks, full frontend build or browser checks performed here. At least one supplied screenshot captures a loading state.

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

## After each phase replace this section
Scope and acceptance IDs:
Branch and commit:
Changed files:
Database/schema/contract changes:
Commands, exit statuses and evidence:
Reviewer findings and resolutions:
Unresolved blockers:
Exact next phase and approval required:
