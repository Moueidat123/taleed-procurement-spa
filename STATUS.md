# Project status — read this first

**Last updated:** 30 September 2026 · **Updated by:** Claude Code
**Current phase:** Phase 1 — `prompts/production/01-FOUNDATION.md` — **IN PROGRESS**
**Branch:** `implementation/phase-1` (local only, not pushed) · **Base:** `main` @ `c8c9a9d`

This file is the short entry point for any agent or colleague (Claude Code, Codex or other). The detailed log is `docs/production/HANDOFF.md`. The rules are `AGENTS.md` → `prompts/production/MASTER.md`.

## Phase tracker
| Phase | Prompt | State |
|---|---|---|
| 0 Discovery | `prompts/production/00-DISCOVERY.md` | ✅ Done. Owner approved on 2026-09-30. Output: `docs/production/{discovery,decisions,reuse-matrix}.md` |
| 1 Foundation | `prompts/production/01-FOUNDATION.md` | 🔄 In progress |
| 2 Identity & assessments | `02-IDENTITY-AND-ASSESSMENTS.md` | ⏳ Not started; needs owner approval |
| 3 UI & reports | `03-UI-AND-REPORTS.md` | ⏳ Needs owner decision D-12 (CMS content / privacy notice) |
| 4 Release readiness | `04-RELEASE-READINESS.md` | ⏳ |
| 5 Production release | `05-PRODUCTION-RELEASE.md` | ⛔ Needs explicit release authorisation and D-15/D-19 inputs |

## How to resume
1. Read `AGENTS.md`, this file, `docs/production/HANDOFF.md` and `docs/production/decisions.md`.
2. Run `git status` and `git log --oneline -5`. Do not discard uncommitted work.
3. Continue the phase marked 🔄 using its prompt. Never start the next phase without owner approval.
4. At the end of a phase, update **this file** and `HANDOFF.md`.

## Standing constraints (summary)
- React SPA kept as-is. Laravel 13 + Statamic 6 **Core** in `backend/`. MySQL 8.4.
- Local and production environments only. No staging.
- No production access, no push, and no host changes (hosts file, trust store) without explicit owner approval.
