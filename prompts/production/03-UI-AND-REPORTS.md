# Phase 3 — API-backed approved UX and reports

Prerequisite: reviewed identity/assessment API and schema. Read the master and current UI/screens.

Wire existing React features through RTK Query, maintaining the approved tokens/components/layout and the three roles. Preserve form validation while displaying authoritative backend errors. Do not add a new design system, Vue rewrite, generic admin template, Program Manager or dormant screens without approval.

Replace localRepository persistence and account/session simulation. Production has no shared demo password/code, role switcher, data import/reset, fixed date or demo fallback. Only harmless preferences may remain in localStorage. Autosave displays committed/unsaved/error states, serializes writes and handles stale-version conflicts. Flush pending saves before submit and recover safely from session expiry/network failures.

Complete all Champion, Analyst and Super Admin journeys. Implement server-side staff filtering, pagination, effective-revision aggregates and compatible 2–4-company comparison. Staff may receive participation metadata but never draft answers. Implement authorized CSV/XLSX and A4 print/PDF report flows from immutable snapshots. Freeze export cohorts, protect private downloads, reauthorize async jobs, neutralize spreadsheet formulas and type string IDs. Do not rely on client-only permission hiding.

Test route/deep-link behavior, same-origin cookies/CSRF, cache clearing and rendering. Re-capture actual loaded approved screens rather than accepting supplied loading-state images. Run frontend checks/build and Playwright against the real local Docker backend/MySQL, not only mocks. Include keyboard/mobile/error/conflict/auth-expiry checks and print/PDF review.

Update user and admin documentation and the acceptance evidence. Stop with a working local application and explicit remaining release blockers. No cloud changes or real email.
