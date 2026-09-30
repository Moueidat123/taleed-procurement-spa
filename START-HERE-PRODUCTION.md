# Taleed Procurement — from approved prototype to production

Prepared 30 September 2026. **This package contains an implementation specification and prompts, not an installed backend or a deployed application.**

## The selected direction
Keep the approved React application. Add a Laravel + Statamic 6 Core backend under `backend/` in this repository. Store procurement accounts and business records in MySQL 8.4 LTS. Serve the SPA and its custom Laravel JSON endpoints from one HTTPS origin. Keep Statamic's one CMS administrator separate from application users. Local development and production only; there is no staging environment.

The local hostname proposed here is `https://procurement.taleed.test`. The production hostname, Google Cloud project, VM, zone, static IP, disk and mail settings must be discovered and confirmed; none is invented in this package.

## Choose the right archive
The full-repository archive contains the uploaded application plus this package. Only its root `AGENTS.md` is intentionally replaced; the original is archived at `docs/production/reference/AGENTS.prototype.md`. Application source, workbook, existing documentation and demo assets are unchanged.

The overlay archive contains these additions and the revised root instructions only. In an existing working repository, review and merge the overlay rather than replacing a newer `AGENTS.md` or overwriting uncommitted work. Neither archive contains Git history. Keep your existing `.git/` and remote configuration.

## Start in Claude Code or Codex
Open the real repository, inspect `git status`, and review this package before committing it. Do not copy credentials into prompts. Paste:

```text
We are implementing the client-approved Taleed Procurement SPA in this repository.
Read AGENTS.md, CLAUDE.md, START-HERE-PRODUCTION.md, and
prompts/production/MASTER.md. Then execute prompts/production/00-DISCOVERY.md only.
Use the uploaded SPA as the UX reference. Use Statamic 6 Core, independent Laravel
application authentication, MySQL, and local + production environments only.
Read authorized local Survey/ICTTool and Sustainability reference repositories
where available, without modifying them or reading production secrets or records.
Report findings with actual paths. Record the proposed architecture, schema,
license boundary, reuse decisions, source conflicts and test baseline.
You may create discovery documents only. Do not scaffold, install dependencies,
contact production, push commits, run migrations or deploy in this first phase.
Stop with the exact next phase and approval needed.
```

After reviewing discovery, paste:

```text
Discovery is approved for local implementation under the documented constraints.
Execute prompts/production/01-FOUNDATION.md only. Build and test the actual local
Docker environment. Do not just produce a plan. Do not access or change production.
At the phase boundary, provide changed files, commands and results, unresolved
issues, and an updated docs/production/HANDOFF.md for independent review.
```

Continue with `02-IDENTITY-AND-ASSESSMENTS.md`, `03-UI-AND-REPORTS.md`, and `04-RELEASE-READINESS.md`, approving one phase at a time. The separate `05-PRODUCTION-RELEASE.md` is for an explicitly authorized real release, not general implementation permission.

## Read in this order
| File | Purpose |
|---|---|
| `docs/production/01-REPOSITORY-AUDIT.md` | What was actually inspected and where the older documents diverge |
| `docs/production/02-ARCHITECTURE.md` | React/Laravel/Statamic boundary and dependency decisions |
| `docs/production/03-DATABASE-SCHEMA.md` | Data dictionary, relationships, invariants and transactions |
| `docs/production/04-OPERATIONS.md` | HTTPS, Docker, durable data, backups and controlled releases |
| `docs/production/05-ACCEPTANCE.md` | Functional, security, scoring and recovery evidence required |
| `docs/production/06-MULTI-AGENT.md` | Claude/Codex ownership, separate worktrees and handoffs |
| `docs/production/07-API-CONTRACT.md` | Proposed endpoint, permissions, errors and concurrency contract |
| `prompts/production/MASTER.md` | Shared engineering contract for either agent |
| `docs/production/SOURCES.md` | Official references and limits of the repository review |

## Already verified versus still to do
The uploaded SPA's `npm run test:core` passed **28/28** in this review. The source workbook hash, 40 question texts and 64 recommendation texts were checked. No full frontend install/build/browser run, PHP tests, Docker execution, restore drill or production deployment was performed here. A screenshot of a loading state is not a verified interface. Production-ready status must come from the later acceptance evidence, not this prompt pack.
