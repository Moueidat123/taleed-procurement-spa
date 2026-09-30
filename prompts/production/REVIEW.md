# Independent phase review — either agent

You are the independent reviewer, not the implementation writer. Read AGENTS, the master, the assigned phase, acceptance gates, current handoff and the actual diff at the supplied commit. Use a separate worktree and test database. Do not edit the writer's worktree or contact production.

Review first for data-loss paths, broken app/CMS auth separation, Pro/trial dependencies, tenant leaks, secret handling, migration/concurrency failures, source/scoring drift and fake test evidence. Then review UI/API contract parity, operational reliability and maintainability.

Run relevant checks independently when tools are available. A code inspection is not a passing runtime test. Review actual resolved Docker config and operational control flow, not only comments or function names. A flag called dry-run is not proof of no side effects. Ensure runtime and migration identities are distinct and failed mount checks cannot fall through to empty DB initialization.

Report blocking/high/medium/low findings with file/line, reproduction, impact, expected behavior and a proposed fix. Distinguish confirmed defects from uncertainty and untested conditions. State whether the phase is ready for the next phase, not whether all future deployment is approved. Do not silently fix code unless expressly assigned a separate correction task.
