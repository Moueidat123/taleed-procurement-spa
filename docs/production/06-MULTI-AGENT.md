# Claude Code and Codex collaboration

Both agents use the same `AGENTS.md`, master contract and handoff. `CLAUDE.md` is a thin entry point, not another competing specification. Official agent documentation supports repository instruction files; Git worktrees provide separate checkouts for parallel work. [S12–S14]

## Recommended mode: one writer, one reviewer
The writer completes one bounded phase and records the commit under review. The reviewer uses a separate worktree at that exact commit, reads the phase requirements and runs independent checks. It reports severity, file/line, reproduction, impact and proposed correction. It does not secretly edit the writer's worktree. The writer addresses findings and records a new review commit.

Either product may be the writer. Suggested pairing: one agent implements backend/domain and the other performs an independent authorization/scoring/operations review; swap roles when useful. The model brand is not a substitute for ownership or a passing test.

## Controlled simultaneous implementation
After architecture and API contracts are stable, parallel work is possible with this ownership split:

| Owner | May edit | Must not edit without coordination |
|---|---|---|
| Backend owner | `backend/app`, backend tests, Laravel routes/resources | Frontend features or another agent's branch |
| UI owner | `src/`, frontend/e2e tests | Backend migrations, auth guard configuration, operations scripts |
| Integration owner (one designated person/agent) | Migrations, dependency lockfiles, shared API/schema, Docker/CI/release configuration | Simultaneously delegate these files to both writers |

Freeze a versioned OpenAPI/JSON contract before the split. UI work may use contract fixtures only in tests/local development; production must not silently fall back to mocks. The integration owner coordinates incompatible changes and merges reviewed branches. Each branch remains small enough to understand.

Example local worktree commands, run only after saving/reviewing the current working tree and choosing an actual base commit:

```bash
git worktree add ../taleed-procurement-backend -b implementation/backend <BASE_COMMIT>
git worktree add ../taleed-procurement-ui -b implementation/ui <BASE_COMMIT>
git worktree add --detach ../taleed-procurement-review <REVIEW_COMMIT>
```

These create local worktrees/branches, not remote pushes. Do not check out the same branch in both writers. Do not share a worktree's `node_modules`, vendor, `.env`, MySQL data, session store or mutable CMS paths. Assign unique Compose project names and configured HTTPS ports/hostnames. Only one shared local reverse proxy can own 80/443; document the selected arrangement.

No agent executes production commands while another is changing the release candidate. Production has one release owner and one lock regardless of the number of coding agents.

## Handoff contract
Update `docs/production/HANDOFF.md` on each phase branch with scope, branch/commit, changed files, schema/API changes, checks run and exit status, open risks and the exact next action. Concurrent agents keep their own branch handoffs; the integration owner reconciles on merge rather than racing on a shared live file.

Use `prompts/production/REVIEW.md` for a reviewer and `prompts/production/CONTINUE.md` after a context reset. Do not ask the next agent to infer completion from chat history or from a checklist with unchecked tasks silently removed.

References: `SOURCES.md`.
