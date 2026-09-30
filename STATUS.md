# Project status — read this first

**Last updated:** 30 September 2026 · **Updated by:** Claude Code
**Current phase:** Phase 1 (Foundation) is ✅ complete locally, **awaiting independent review and merge**. The plan is fixed by `docs/production/PLAN-CONTRACT.md`, approved by the director on 30 Sep 2026. **Next: Phase 2A**, after the Phase 1 review, the director's merge and the director's go-ahead.
**Branch:** `implementation/phase-1` (local commits, **not pushed**) · **Base:** `main` @ `c8c9a9d`

This file is the short entry point for any agent or colleague (Claude Code, Codex or others).

**Binding plan and decisions: `docs/production/PLAN-CONTRACT.md`.** Developers execute it and do not make product decisions. Anything it does not cover is escalated to the director with options and a recommendation.
- Detailed log: `docs/production/HANDOFF.md`
- Rules: `AGENTS.md` → `prompts/production/MASTER.md`
- Decisions: `docs/production/decisions.md`
- Local how-to: `docs/production/LOCAL-DEVELOPMENT.md`

## Phase tracker (7 phases in total, 5 remaining; D-26)
| Phase | Prompt | State |
|---|---|---|
| 0 Discovery | `prompts/production/00-DISCOVERY.md` | ✅ Done (commit `43640c4`) |
| 1 Foundation | `prompts/production/01-FOUNDATION.md` | ✅ Done locally (commit `ef9cc3d`). ⏳ Independent review, PR and director merge pending |
| 2A Accounts, login and security | `02A-ACCOUNTS.md` | ⏳ **Next**, after the Phase 1 merge and the director's go-ahead |
| 2B Assessment engine and scoring | `02B-ASSESSMENTS.md` | ⏳ |
| 3 Connect the React app, staff views and reports | `03-UI-AND-REPORTS.md` | ⏳ Needs the privacy text by the end of Phase 3 (contract §5 #2) |
| 4 Release readiness | `04-RELEASE-READINESS.md` | ⏳ Needs the retention policy by the end of Phase 4 (§5 #3) |
| 5 Production launch | `05-PRODUCTION-RELEASE.md` | ⛔ Needs the director's written go-ahead and §5 inputs #4–#10 |

Every phase: branch `implementation/<phase>`, then evidence, then `STATUS.md` and `HANDOFF.md` updates, then a PR, then an independent review, then the director merges and approves the next phase (D-27, D-28).

## What works now (verified 30 Sep 2026)
- **Stack:** Laravel 13.34 + Statamic 6.34.1 **Core** (no Pro, no licence key) in `backend/`; MySQL 8.4.11; PHP 8.4.26.
- **Docker:** app, queue, one scheduler, MySQL, Vite, Mailpit and an nginx HTTPS edge.
- **Origin:** `https://procurement.taleed.test` serves the approved React SPA, the business API `/api/procurement/v1/*`, CMS pages `/pages/*` and the Statamic CP `/cp`.
- **Separate identities:**
  - App users (Champion, Analyst, Super Admin) use the Eloquent `web` guard.
  - The single CMS admin uses the `statamic` guard.
  - Each has its own password brokers. Neither can use the other's area.
  - Covered by tests.
- **Implemented endpoints:** `health`, `auth/login`, `auth/logout`, `auth/me`, `auth/forgot-password` (non-enumerating), `auth/reset-password`, TOTP two-factor routes. Contract: `docs/production/api/openapi-v1.yaml`.
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
2. Run `git status` and `git switch implementation/phase-1` (or continue from its HEAD). Never discard uncommitted work.
3. Local environment: `ops/local/dev init` (once), then `ops/local/dev up`, `ops/local/dev test` and `ops/local/checks/https-smoke.sh`. See `docs/production/LOCAL-DEVELOPMENT.md`.
4. Start the phase marked **Next** only when the owner has approved it. Use that phase's prompt.
5. At the end of a phase, update **this file** and `HANDOFF.md`, then commit locally. Push only on explicit instruction.

## Open decisions and blockers
All product decisions are made (PLAN-CONTRACT §3). What remains are **director inputs** with deadlines (§5):
- privacy notice text (end of Phase 3);
- retention policy (end of Phase 4);
- programme-owner content sign-off, GCP access, static IP and VM cost, SendGrid key and sender address on `myprototype.cloud`, `/cp` allow-list IPs, alert emails, first real admin identities, written release go-ahead (all before Phase 5).

Host changes for local development (the hosts entry and trusting the local CA) are optional. `ops/local/dev trust-help` explains them.

## Known limits
- The SPA still uses its browser-local demo repository. Switching it to the API is Phase 2/3.
- CI workflows have been rewritten but not yet run on GitHub, because nothing has been pushed.
- Only macOS/arm64 has been verified.

## Standing constraints (summary)
- React SPA kept. Statamic **Core only**. MySQL 8.4. Local and production only; no staging.
- No production access, pushes or host changes (hosts file, trust store) without explicit owner approval.
- Never run destructive DB/volume commands outside `ops/local/dev reset`, which needs a typed confirmation and only works locally.
