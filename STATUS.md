# Project status — read this first

**Last updated:** 2 October 2026 · **Updated by:** GitHub Copilot
**Current phase:** Local track complete. Phases 1, 2A, 2B and 3 are merged to `main` (PRs #1–#5). PR #6 (staff champion directory) is open. The deployment track (Phases 4–5) stays frozen by director instruction.
**Branch:** `main` · new phase work happens on `implementation/<phase>` branches.

> **Working mode (director instruction, 1 Oct 2026):** continue all **local logic and application** work (Phases 2A, 2B, 3). **Deployment phases (4 Release readiness, 5 Production launch) stay frozen** until the director provides the §5 inputs and a written go-ahead. Nothing in this repo touches production, pushes credentials, or provisions paid resources.

This file is the short entry point for any agent or colleague (Claude Code, Codex or others).

**Binding plan and decisions: `docs/production/PLAN-CONTRACT.md`.** Developers execute it and do not make product decisions. Anything it does not cover is escalated to the director with options and a recommendation.
- Detailed log: `docs/production/HANDOFF.md`
- Rules: `AGENTS.md` → `prompts/production/MASTER.md`
- Decisions: `docs/production/decisions.md`
- Local how-to: `docs/production/LOCAL-DEVELOPMENT.md`

## Phase tracker (7 phases in total; D-26)

### Local track — buildable now on this machine
| Phase | Prompt | State |
|---|---|---|
| 0 Discovery | `prompts/production/00-DISCOVERY.md` | ✅ Done (commit `43640c4`) |
| 1 Foundation | `prompts/production/01-FOUNDATION.md` | ✅ Done and **merged to `main`** (`ef9cc3d`, PR #1 `1dc49c9`) |
| 2A Accounts, login and security | `02A-ACCOUNTS.md` | ✅ Merged (PR #2) |
| 2B Assessment engine and scoring | `02B-ASSESSMENTS.md` | ✅ Merged (PR #3) |
| 3 Connect the React app, staff views and reports | `03-UI-AND-REPORTS.md` | ✅ Merged (PR #4, 2 Oct 2026; merged on director instruction without independent review). Follow-up PR #5 merged. Evidence `docs/production/evidence/phase-3/`. Privacy text remains a launch gate |

### Deployment track — FROZEN (kept as-is; director inputs required)
| Phase | Prompt | State |
|---|---|---|
| 4 Release readiness | `04-RELEASE-READINESS.md` | 🔒 Frozen — prod tooling/backups/CI/HTTPS/VM; needs §5 inputs |
| 5 Production launch | `05-PRODUCTION-RELEASE.md` | 🔒 Frozen — needs the director's written go-ahead and §5 inputs #4–#10 |

Every phase: branch `implementation/<phase>`, then evidence, then `STATUS.md` and `HANDOFF.md` updates, then a PR, then an independent review, then the director merges and approves the next phase (D-27, D-28). The deployment track does not start until it is explicitly unfrozen.

## What works now
- **Staff two-step verification is optional** (D-17 changed by the director, 2 Oct 2026). Analysts and Super Admins sign in with a password only unless they choose to enable TOTP. Risk accepted by the director: a staff password alone exposes all company results and exports.
- Latest checks (2 Oct 2026): PHPUnit 128/128 (MySQL), Larastan 0 errors, Pint clean, type check pass, local Playwright 7 passed and 2 skipped.

### Foundation baseline (verified 30 Sep 2026)
- **Stack:** Laravel 13.34 + Statamic 6.34.1 **Core** (no Pro, no licence key) in `backend/`; MySQL 8.4.11; PHP 8.4.26.
- **Docker:** app, queue, one scheduler, MySQL, Vite, Mailpit and an nginx HTTPS edge.
- **Origin:** `https://procurement.taleed.test` serves the approved React SPA, the business API `/api/procurement/v1/*`, CMS pages `/pages/*` and the Statamic CP `/cp`.
- **Separate identities:**
  - App users (Champion, Analyst, Super Admin) use the Eloquent `web` guard.
  - The single CMS admin uses the `statamic` guard.
  - Each has its own password brokers. Neither can use the other's area.
  - Covered by tests.
- **Implemented endpoints:** `health`, `auth/login`, `auth/logout`, `auth/me`, `auth/forgot-password` (non-enumerating), `auth/reset-password`, optional TOTP two-factor routes. Contract: `docs/production/api/openapi-v1.yaml`.
- **Checks:**

  | Check | Result |
  |---|---|
  | Backend PHPUnit | 16/16 (MySQL) |
  | Larastan level 6 | 0 errors |
  | Pint | clean |
  | composer audit | clean |
  | Frontend `npm run check` | 28/28 core |
  | Prototype e2e | 25 passed, 1 skipped |
  | Local-HTTPS browser tests | 5/5 |
  | Smoke script | passes |

## How to resume (any agent)
1. Read `AGENTS.md`, this file, `docs/production/HANDOFF.md` and `docs/production/decisions.md`.
2. Run `git status`. Phase 1 is on `main`. For new work, create a new `implementation/<topic>` branch from `main`. Never discard uncommitted work.
3. Local environment: `ops/local/dev init` (once), then `ops/local/dev up`, `ops/local/dev test` and `ops/local/checks/https-smoke.sh`. See `docs/production/LOCAL-DEVELOPMENT.md`.
4. Start the phase marked **Next** only when the owner has approved it. Use that phase's prompt.
5. At the end of a phase, update **this file** and `HANDOFF.md`, open a PR, and let the director merge. Do not start the frozen deployment track (Phases 4–5) without an explicit unfreeze.

## Open decisions and blockers
All product decisions are made (PLAN-CONTRACT §3). What remains are **director inputs** with deadlines (§5):
- privacy notice text (launch gate);
- confirm the read-only Framework library (versions published by server commands);
- retention policy (end of Phase 4);
- programme-owner content sign-off, GCP access, static IP and VM cost, SendGrid key and sender address on `myprototype.cloud`, `/cp` allow-list IPs, alert emails, first real admin identities, written release go-ahead (all before Phase 5).

Host changes for local development (the hosts entry and trusting the local CA) are optional. `ops/local/dev trust-help` explains them.

## Known limits
- The SPA now uses only the server API; the browser-local demo store is removed (2 Oct 2026). Authenticated browser journeys (assessment, conflict, submit, results, staff filters, compare, exports, correction) pass locally; browser tests are not run in CI.
- The Framework library screen is read-only; versioning and publishing happen through server commands (awaiting director confirmation).
- CI workflows run on GitHub (Phase 1 was pushed and merged via PR #1). Deployment workflows remain manual-only (D-16).
- Only macOS/arm64 has been verified.

## Standing constraints (summary)
- React SPA kept. Statamic **Core only**. MySQL 8.4. Local and production only; no staging.
- No production access, pushes or host changes (hosts file, trust store) without explicit owner approval.
- Never run destructive DB/volume commands outside `ops/local/dev reset`, which needs a typed confirmation and only works locally.
