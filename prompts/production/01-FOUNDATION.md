# Phase 1 — working local foundation

Prerequisite: owner approval of Phase 0 for local implementation. Read master, discovery decisions and handoff. Do not contact or change production.

Implement the actual Laravel/Statamic Core application under `backend/` without replacing the React root. Resolve/install compatible versions, commit-ready lockfiles and reproducible scripts; do not invent lockfiles. Record resolved versions and why selected. Review package scripts before execution. Run the current frontend baseline before integration and record real results.

Build local Docker for app, MySQL 8.4, worker, one scheduler, HTTPS proxy, Vite/HMR and Mailpit. Establish unique local state and safe `.env.example` files; never load production credentials. Make normal startup non-destructive. Provide explicit local initialization/migration commands; demo seed is separately invoked. Request approval before modifying host DNS mappings/trust store and provide precise manual steps if not authorized.

Configure independent application Eloquent and Statamic guards/providers/password brokers with Core explicitly active. Add a minimal safe local authentication separation test and a real CMS-rendered help/privacy or landing-content example using existing tokens. No shared-account workaround or Pro API. Serve the existing SPA from the planned origin and return JSON health/API errors separately from HTML routes. Define initial versioned API contracts for Phase 2.

Test image builds, container health, trusted HTTPS, browser-to-API same-origin/CSRF behavior, DB connectivity, CMS login, application/CP principal isolation, frontend build and dev restart persistence. Add dependency/security checks appropriate to the locked packages. Provide actual quickstart commands and troubleshooting.

Do not implement every feature with fake API responses and call this done. Keep any local test placeholders explicit and non-production. Stop with a working local foundation, evidence, changed files, open issues and Phase 2 handoff. Do not execute production migrations or configure cloud credentials.
