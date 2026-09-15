# Codex prompt — validate and finish this existing implementation

Paste the text below with the extracted project opened in your authorized local workspace.

---

You are the principal React/TypeScript engineer and QA owner for Fadi Zahhar's Taleed Procurement Self-Assessment prototype.

Read every applicable AGENTS.md. Inspect git status and preserve all unrelated/uncommitted work. Read README.md, docs/architecture.md, docs/user-journeys.md, docs/content-provenance.md, docs/verification.md and docs/acceptance-checklist.md. Inspect actual code before changing it. This is the existing implementation: DO NOT replace it with a scaffold or redesign the approved visual foundations.

Complete the local validation and repair work now. You are authorized to install declared project dependencies and execute local tests in this workspace. Do not connect to production, send real emails, publish client content, push commits or deploy.

1. Verify the installed Node version and package manager. The baseline is Node 22.12+ within 22.x or Node 24+. Check actual official/registry versions and peer compatibility, keeping React and React DOM aligned. Preserve the requested React 19.3.0 / Redux Toolkit 2.12.0 / React Redux 9.3.0 baseline unless the registry proves a correction is necessary; document any correction. Do not infer "latest" from memory.
2. Run npm install if no lockfile exists; otherwise npm ci. Generate a genuine package-lock.json and inspect dependency errors. Do not use --force or --legacy-peer-deps to suppress a real incompatibility.
3. Run npm run typecheck, npm run lint, npm test and npm run build. Fix actual failures at the cause. Keep strict typing, business contracts, defaults and permission checks. Avoid blanket any, lint-disable, test.skip or snapshot rewriting that conceals defects.
4. Install Chromium using Playwright and run npm run test:e2e. Inspect browser console errors and failed traces. Verify desktop and mobile screens against the source-reference design tokens. Fix labels, layout overflow, keyboard focus, dialogs, saved-state indicators and routing defects rather than weakening the assertions.
5. Exercise a complete registration/profile/40-answer/submit/result/recommendation/report journey. Verify mixed-answer reload, clear/unanswered, missing questions, wrong organization/role, permissions, correction history, publish/version pinning, same-cohort export, storage quota/corruption, stale tabs and typed reset/import.
6. Test the built dist/ output using npm run preview, including hash navigation under a subpath. Use only synthetic data. Do not claim real authentication, tenant security, email verification, certification or backend persistence.
7. Review all 40 questions and 64 recommendation actions against src/data/framework.json and the source catalogue. Treat draft publication as a demo, not business approval. Keep original source IDs and exact scoring bands unchanged.
8. Update docs/verification.md with exact commands, dates, actual Pass/Fail/Blocked results and evidence paths. Update the acceptance checklist only for tests actually executed. Record remaining limitations separately from resolved defects.

Return changed files, exact start/test/build commands, executed results, screenshots/traces where actually generated, and any remaining blockers. The deliverable is the repaired runnable local prototype plus a truthful verification record, not another plan. Continue with safe independent fixes when one check is blocked; never fabricate a successful install, test or deployment.
