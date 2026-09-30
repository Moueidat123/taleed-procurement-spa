# Project status — read this first

**Last updated:** 30 September 2026 · **Updated by:** Claude Code
**Current phase:** Phase 1 (Foundation) is ✅ **complete, local only**. Phase 2 has **not started** and is waiting for owner approval.
**Branch:** `implementation/phase-1` (local commits, **not pushed**) · **Base:** `main` @ `c8c9a9d`

This file is the short entry point for any agent or colleague (Claude Code, Codex or others).
- Detailed log: `docs/production/HANDOFF.md`
- Rules: `AGENTS.md` → `prompts/production/MASTER.md`
- Decisions: `docs/production/decisions.md`
- Local how-to: `docs/production/LOCAL-DEVELOPMENT.md`

## Phase tracker
| Phase | Prompt | State |
|---|---|---|
| 0 Discovery | `prompts/production/00-DISCOVERY.md` | ✅ Done (commit `43640c4`). Output: `docs/production/{discovery,decisions,reuse-matrix}.md` |
| 1 Foundation | `prompts/production/01-FOUNDATION.md` | ✅ Done locally. Evidence: `docs/production/evidence/phase-1/` |
| 2 Identity & assessments | `02-IDENTITY-AND-ASSESSMENTS.md` | ⏳ **Next.** Needs owner approval. An independent review of Phase 1 is recommended first |
| 3 UI & reports | `03-UI-AND-REPORTS.md` | ⏳ Needs owner decision D-12 (privacy notice / CMS content) |
| 4 Release readiness | `04-RELEASE-READINESS.md` | ⏳ |
| 5 Production release | `05-PRODUCTION-RELEASE.md` | ⛔ Needs explicit release authorisation plus D-15 and D-19 inputs |

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
| ID | Needed by | Question |
|---|---|---|
| D-12 | Phase 3 | Is a client-approved privacy notice linked from registration consent? Which landing copy is CMS-editable? (`/pages/privacy` is a marked placeholder.) |
| D-15 | Phase 5 | Production VM placement (dedicated is proposed), DNS, certificate, data disk, mail, backups |
| D-19 | Phase 5 | Formal content-approval reference for framework v1.0.0 |
| Host changes | Any time | Owner approval to add the `/etc/hosts` entry and trust the local CA (`ops/local/dev trust-help`). Until then, use curl or the Playwright config |

## Known limits
- The SPA still uses its browser-local demo repository. Switching it to the API is Phase 2/3.
- CI workflows have been rewritten but not yet run on GitHub, because nothing has been pushed.
- Only macOS/arm64 has been verified.

## Standing constraints (summary)
- React SPA kept. Statamic **Core only**. MySQL 8.4. Local and production only; no staging.
- No production access, pushes or host changes (hosts file, trust store) without explicit owner approval.
- Never run destructive DB/volume commands outside `ops/local/dev reset`, which needs a typed confirmation and only works locally.
