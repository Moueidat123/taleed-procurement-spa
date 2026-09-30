# Resume an interrupted implementation

Read AGENTS, CLAUDE, the master, decisions, the current phase and HANDOFF. Inspect `git status` and the actual changed files before assuming any previous work completed. Identify the latest reviewed commit and actual test evidence; do not rely on a chat summary alone.

Continue only the already authorized local phase and assigned paths. Preserve other writers' work. Do not rerun destructive initialization, regenerate secrets, recreate a database or automatically repeat a production command whose outcome is unknown. A lost connection requires status inspection under the appropriate authorization, not a second blind deployment.

Summarize confirmed state and blockers, complete the remaining bounded work, rerun relevant tests and update the handoff. Return the exact next phase and approval required. Do not silently broaden permissions or declare skipped checks passed.
