# Master Codex prompt — build Taleed Procurement from an empty workspace

**Prepared for Fadi Zahhar · 15 September 2026 · local React/Redux SPA prototype**

## Use

Extract the **Codex starter package** into a new authorized project directory and open it in your coding workspace. Keep the `reference/` and `requirements/` directories with this prompt. Paste everything from **BEGIN MASTER PROMPT** through **END MASTER PROMPT** into Codex.

In the supplied complete application, the equivalent reference files are `src/data/framework.json`, `src/styles/tokens.css`, and the `docs/` requirements. Do not run a destructive scaffold over the completed app; use `CONTINUE_AND_VERIFY.md` for that case.

The starter's source text is essential. A prompt without the full JSON cannot reproduce the exact 40 questions, 64 recommendation actions and four interpretations. Never invent missing source material.

---

## BEGIN MASTER PROMPT

You are the principal frontend architect, senior React/TypeScript engineer, interaction designer and QA owner building **Taleed Procurement Self-Assessment** for **Fadi Zahhar**. The PM approved the concept and its Taleed visual direction. Deliver a complete, coherent, responsive, local-first **React single-page application using Redux Toolkit and localStorage**, not screenshots, pseudo-code, a landing-only mockup or an implementation plan.

### 1. Authority, inputs and workspace safety

Read applicable AGENTS.md files and inspect the workspace before writing. Preserve unrelated code and uncommitted work. Determine whether this is an empty starter or an existing implementation.

Read these starter files completely:

- `reference/framework.json`: complete canonical prototype wording, question/action IDs, source provenance and interpretation text.
- `reference/design-tokens.css`: approved-reference colors, typography fallback, radii and spacing foundations.
- `requirements/user-journeys.md`: required screens, actions and validation states.
- `requirements/architecture.md`: state ownership, commands, permission and persistence contract.
- `requirements/acceptance-checklist.md`: human acceptance scenarios.
- `requirements/content-provenance.md`: original workbook authority, approval and usage boundaries.
- `requirements/dependencies.md`: dated stack baseline and verification policy.

In an existing full source package, read `src/data/framework.json`, `src/styles/tokens.css`, `docs/user-journeys.md`, `docs/architecture.md`, `docs/acceptance-checklist.md`, `docs/content-provenance.md`, and `docs/dependencies.md` instead. Do not overwrite the full project with an empty starter.

The original method came from `Procurement_Self Assessment Tool_v01.xlsx`, attributed to Aramco Taleed & Roland Berger. The source workbook hash recorded in the previous audit is `e86755a94db9bb4c2d14c3bf1f60eb237189d4a5bc1fd04efbb8d4faf197eecd`. The companion JSON is a normalized prototype content contract, not a byte-identical workbook. Preserve text and source IDs. Design approval does not equal formal source publication or a transfer of content rights.

Use only synthetic users and organizations. Do not access production, copy credentials, send email, provision paid resources, push a repository, or deploy. Installing declared dependencies and running local development/tests in this workspace are in scope. Do not ask the user to confirm each ordinary local coding step.

### 2. Outcome and product boundaries

The complete company flow is:

**Discover -> register -> simulated verify -> company profile -> dashboard -> answer four sections -> review -> declare/confirm -> submit -> results -> recommendations -> report -> history.**

Returning users resume saved progress. Managers may open a controlled correction draft from a final submission; the original stays intact and effective until the company submits the replacement.

The complete staff flow is:

**Staff sign-in -> portfolio -> cohort filters -> organization/result -> compare -> permitted export.** Managers additionally manage cycles, versioned framework drafts, simulated publication, staff access and correction requests. Administrators inspect local audit and dataset backup/recovery controls.

This is a procurement capability diagnostic. Do NOT add Carbon-style approval before results, certification, tenders, purchase orders, ERP integration, evidence uploads, anonymous submissions, public league tables, automatic SSO, Arabic translation, delegated editing, an action/task-management product or AI-generated recommendations. Preserve the scope instead of decorating it with unrelated modules.

### 3. Verify and install the current stack

Use the latest **stable** React/Redux line actually available from official releases/registry at execution time. Do not guess versions from memory, use prereleases, or silently downgrade to a CDN React 18 mockup.

Dated handover baseline: **React and React DOM 19.3.0**, **Redux Toolkit 2.12.0**, **React Redux 9.3.0**, **Vite 8.1**, **React plugin 6**, strict TypeScript and Node 22.12+ within 22.x or Node 24+. Verify availability and peer compatibility; record the exact resolved versions and any justified correction. Keep React and React DOM aligned.

Use React Router hash routes, React Hook Form + Zod/resolvers, ExcelJS for a lazy-loaded real XLSX export, ESLint/TypeScript/Hooks rules, Prettier, Node's built-in core test runner and Playwright. Use dependencies only when they do real work. No redux-persist blind store persistence, Redux Saga, Axios without an API, arbitrary UI framework, authentication SDK or backend/database service is needed.

Generate `package.json`, a real package-manager lockfile, strict tsconfig, Vite config, lint/format config, `.gitignore`, `.nvmrc`, test configuration and CI. Install normally. Resolve incompatibility instead of suppressing it with --force/--legacy-peer-deps. Never fabricate a lockfile, install log or vulnerability/audit result. When network access blocks installation, continue safe source work and explicitly record which checks could not execute.

### 4. Visual and interaction fidelity

Build the Taleed product shell, not a generic SaaS kit. Use the supplied tokens as the authority:

- Navy `#0a1d5c`: sidebar and strong brand regions.
- Blue `#1741c9`: primary controls/active navigation; darker `#1335a3` hover; tint `#eef2fb`.
- Amber `#f0a93b`: restrained emphasis; pale `#fff6e5`; accessible amber text `#7e4b0e`.
- Page background `#eef1f5`, white surfaces, text `#1e293b`, secondary text `#64748b`, dividers `#e2e8f0`.
- 4/6/8px corner system, light borders/shadows, generous whitespace and hierarchy, Inter/system sans-serif fallback. Do not redistribute font files or fetch arbitrary marketing photography.

Create a desktop navy sidebar, white top bar, role/organization context, page headings, breadcrumb context, local-demo/saving strip, good data tables, clear field labels, realistic empty/error/loading states, and compact but usable mobile navigation. Show draft completion as completion, not a maturity score. Use a score ring, horizontal domain bars, maturity badges and readable recommendation cards. Every chart must have text values; do not communicate status through color alone.

Native or properly accessible dialogs need meaningful titles, focus containment/return and Escape behavior. Use real button/link semantics, fieldsets and radio inputs, explicit labels and linked errors, visible focus, a skip link that does not corrupt the hash route, reduced-motion support, mobile layouts, contained table scrolling and A4 report print CSS. Do not claim WCAG compliance solely because these patterns exist; run tests and perform a manual review.

Use a simple local textual Taleed presentation mark when official assets are absent; do not claim it is an approved production logo. Keep shared tokens in one file; do not scatter conflicting colors through screens.

### 5. Domain content and exact scoring contract

Import and validate the complete companion JSON. It contains exactly four ordered domains:

1. Category Management.
2. Spend Analysis.
3. Strategic Sourcing.
4. Supplier Relationship Management.

Each has ten questions. Question IDs are **strings**, `1.1` to `1.10`, `2.1` to `2.10`, `3.1` to `3.10`, `4.1` to `4.10`. Never parse them as decimals or allow `1.10` to collapse into `1.1`. Preserve every question and recommendation's exact supplied wording and source reference. Do not substitute generic questions.

Answers are only `yes`, `no`, or `null`. Yes means consistently in place and scores 1. No includes missing or partial implementation and scores 0. Null is unanswered, not No. No default selection, N/A or partial credit. Progress counts either Yes or No. Exactly 40 valid answered questions are required for a final result.

Overall = 100 × total Yes / 40. Domain = 100 × domain Yes / 10. All questions have equal weight; all domains have equal 25% weight. Classify using unrounded values:

- Foundational: 0 to 40 inclusive.
- Developing: greater than 40 to 65 inclusive.
- Advanced: greater than 65 to 80 inclusive.
- Best-in-Class: greater than 80 to 100 inclusive.

Display one decimal place. Reject unknown/missing question keys, invalid answer values, mismatched frameworks, duplicate source IDs and malformed content. Never show a final maturity result or final report for a draft.

There are 64 recommendation actions: four actions for each of four bands in each of four domains. Select four actions using **that domain's band**, not the overall band, yielding **16 displayed actions** per complete result. Use the matching supplied overall interpretation.

Choose three relative focus domains sorted by domain score ascending then source order ascending. Ties must produce three unique domains and be explained, not represented as higher urgency. For all-100% answers, use sustain-excellence language rather than invented gaps. Best-in-Class remains a framework label, not certification or an external benchmark.

### 6. State-management architecture

Use feature-oriented TypeScript modules:

```text
src/app/             store, typed hooks, selectors, router and guards
src/components/      reusable UI primitives and shell
src/data/            versioned source framework
src/domain/          types, scoring, policies, commands, validation and seed
src/features/public/
src/features/company/
src/features/assessment/
src/features/staff/
src/infrastructure/  persistence and export adapters
src/styles/          tokens, layout/components, responsive and print styles
```

Redux Toolkit is the authoritative shared business state. Normalize entity maps for users, organizations, framework versions, cycles and assessments. Use a separate session slice and a small operational UI slice. Use typed `useDispatch`/`useSelector`, default serializable/immutable checks, and memoized derived selectors. Form values/field errors belong in React Hook Form. Dialogs/menu/declarations belong in component state. URL filters should survive back/forward. Do not persist transient UI, loading/error states, passwords, tokens, files, Blob objects or JSX.

Keep domain rules pure and independent of React, Redux and localStorage. Inject IDs/ISO timestamps. Do not generate random values, dates or side effects inside reducers. Commands must revalidate roles, ownership, status, version, cycle and values even when the UI already validates them. Components must not directly read/write localStorage.

Create an explicit `AssessmentRepository`/storage port and `LocalRepository`. There must be a documented place to replace it later with an HTTP adapter for Statamic/Laravel. Do not implement a fake server, RTK Query cache and local persisted state all competing as authorities.

For this bounded prototype, explicit serial whole-dataset commits are acceptable; document the limit and do not claim this scales to a real portfolio. For future server data, use appropriate normalized entity updates/query invalidation instead of rewriting an entire database on every click.

### 7. Persistence, saves and recovery

Use one namespaced, schema-versioned localStorage envelope with schema version, dataset instance ID, monotonic revision, saved timestamp, business records and bounded audit. Use a distinct sessionStorage key for the selected synthetic user ID. Always show that data is stored **in this browser**, not in a cloud account.

Persist accepted answer changes immediately. Prefer save-before-success: apply command, validate candidate, write successfully, then update displayed domain state and saved indicator. Do not report success while a debounce/timer still risks losing the answer. Serialize commands, catch quota/security/JSON errors, and disable conflicting interactions while saving. Keep profile/staff forms on explicit save.

Compare expected dataset revision and instance ID before a write. Use Web Locks for cooperating same-origin tabs when available. Listen for other-tab writes/clear and pause stale editing with an explicit reload action; no silent last-write-wins merge. Document that without Web Locks the prototype is single-tab and localStorage is not a transactional multi-user database.

Validate imports/hydration: allowed schema, strict fields/limits, no dangerous prototype keys, identifiers, canonical answers, record relationships, framework pinning, correction chains, and recomputed submitted snapshots. Reject unsupported future schemas without silently deleting data. Only seed a genuinely absent key. On corrupt data, retain original bytes, offer raw export, and present deliberate recovery. Do not fake migrations for a legacy schema that does not exist.

Administrator backup/import/reset requires preview and the literal confirmation **REPLACE**. Selecting a file must not overwrite data. Replacement creates a new dataset instance ID, validates first, signs out/reset views and touches only this prototype's key. Keep a 3.5 MB guard and clearly bounded entity/audit limits suitable for a demonstration. Real confidential data must never be used.

### 8. Identity and role simulation

Implement registration, login, verification/resend and forgot/reset password screens as **explicit simulations**. Show inline password strength/matching validation but discard passwords. Use a shared demonstration password `TaleedDemo!2026` and verification code `123456`. Do not pretend an email was delivered or a password reset changed actual credentials. New accounts begin as company Champions only; no public role chooser granting staff access.

Required roles:

- Champion: own company profile, owned draft answers, submit, own results/reports/history.
- Analyst: portfolio, directory, submitted company details and comparison; no draft answer content; exports disabled unless explicitly granted.
- Program Manager: staff read/export, cycles, framework drafts/publication, controlled corrections and restricted staff-access management. Cannot create/alter Super Admin or impersonate staff to edit company answers.
- Super Admin: staff management, local audit and dataset controls. No direct editing of submitted answers.

Add a plainly labelled demo-role switcher for presentation. UI/command authorization still needs tests, but acknowledge that all roles share one inspectable local browser dataset. Do not describe this as secure multitenancy, encryption, real authorization or production authentication. Protect self/last-Admin access from accidental lockout through normal commands.

### 9. Required company interfaces

Implement every route in the companion screen inventory, including:

- Public introduction and source/privacy/method guidance.
- Sign-in/register/verify/forgot/reset with all validation/error/success states.
- Organization setup with company name, country, sector, employee range, optional dummy registration reference and authority declaration. Prevent normalized duplicate name/reference instead of silently attaching a person to an existing company.
- Dashboard with selected cycle, open/closed state, completion, available start/resume/result action, four section cards, history links and clear distinction between prior results and a current draft.
- Four full ten-question sections, source IDs, Yes/No radio options, explicit clear, progress, save-and-exit, back/continue, missing-section message/focus, direct missing-question links and pause/closed states.
- Review of all 40 answers, completion/readiness, missing links, profile/authority eligibility, declaration and a submit confirmation dialog.
- Immediate submitted result with overall/domain scores/bands, supplied interpretation, three focus domains and tie explanation.
- All 16 recommendations; exact read-only submitted answers; snapshot JSON; report preview with A4 browser Print / Save PDF; history with revision/effective status.

No dead buttons. A disabled action must have a reason. No fake success toasts that do not change actual local state. Every provided action must work or be unmistakably identified as a simulation with its actual behavior.

### 10. Required staff interfaces

Implement portfolio filters by cycle/sector/band/company, meaningful participation and maturity denominators, score distribution/domain averages, organization directory/detail, submitted-answer drilldown, and a 2–4 company same-cycle/framework comparison.

Averages/exports count exactly one effective submitted revision per organization/cycle. Exclude unsubmitted drafts and superseded final revisions from scores. A correction draft does not replace an effective result. Clearly separate draft participation counts from submitted maturity cohorts. Never mix versions or present an external market benchmark.

Implement real CSV and XLSX downloads of the same authorized filtered rows. Recheck staff/export permission at the export boundary. Guard spreadsheet formula injection, preserve quotes/commas/newlines/Unicode, and include scope/denominator/prototype limitations. Load the spreadsheet library only when needed. Do not name an HTML or CSV file `.xlsx`.

Implement cycle create/edit/status with valid ordered dates and a published framework. Once an assessment exists, its cycle's framework cannot be silently replaced. Implement a complete source-content library and version cloning. Draft-only question/recommendation wording edits must preserve IDs, count/order and arithmetic. Publishing requires an explicit reference and simulation label; published content and historical snapshots remain immutable.

Implement named staff account creation, active/access toggles, explicit analyst export grant, manager/Admin boundaries, admin audit search and data backup/recovery. Do not implement a production-looking action that secretly does nothing.

### 11. Submission and correction integrity

One active draft per organization/cycle. A final requires a verified active Champion, own active organization, valid profile/authority, open pinned cycle, 40 canonical answers and declaration. Commit final answers, source version/content, company/respondent details, computed result/recommendations and timestamp together. Retrying the same finalization must not create duplicates.

Submitted records are read-only. A manager opens a new draft copying the current effective submission with an explicit reason, parent reference and incremented revision. The old final remains effective until the company submits the new draft. Keep every earlier final available. Reject a second active correction and corrections based on an outdated superseded parent. Recalculate snapshots from canonical inputs; never trust a score passed in from a component or imported without validation.

### 12. Seeded walkthrough and validation evidence

Use the supplied synthetic model or equivalent clearly labelled fixtures:

- Sahara Industrial Solutions, `sahara@example.com`: 12/40-answer 2026 draft and prior 2025 result.
- Namaa Logistics, `namaa@example.com`: domain Yes counts 6,4,8,6; overall 60% Developing.
- Desert Packaging: 2,3,4,3; overall 30% Foundational.
- Atlas Energy Services: 8,8,8,9; overall 82.5% Best-in-Class.
- Horizon Technology: 6,7,8,7; overall 70% Advanced.
- `analyst@example.com`, `manager@example.com`, `admin@example.com` with the roles above.

Use the fixed demonstration date 2026-09-15 with an open 2026 and closed historical 2025 cycle. Document the clock instead of implying real-time operational behavior. Publish the base framework only as an explicitly labelled demo record, not business approval.

Implement and execute meaningful core tests for source counts/IDs, all-No/all-Yes, totals 16/17/26/27/32/33, 6/4/8/6 fixture, ties, per-domain advice, all 11^4 = 14,641 domain-count combinations, invalid answers/source IDs, save/clear, submit/idempotence, history/corrections, permissions, cycle/pause, duplicate registration/company/draft, version pinning/publication, storage errors/CAS, corrupt/future/tampered imports, audit bounds and CSV formula escaping.

Implement and execute Playwright tests for registration/profile, persistence/reload, missing answers/focus, complete four-section submission, result/advice, role/organization route guards, manager correction, export permissions/downloads, quota failure, stale tabs, explicit corruption recovery, invalid import, keyboard skip link and desktop/mobile overflow. Capture real screenshots/traces on failure. Review built output, not only the dev server, before claiming browser acceptance.

### 13. Deliverables and definition of done

Produce the full repository now: source, exact content contract, synthetic seed, package manifest and real generated lock, working dev/build scripts, meaningful automated tests, role/validation behavior, responsive/print styles, safe local data tools and no backend requirement.

Maintain:

- README with startup, exact versions, accounts/code/password, demonstration flow, storage/hosting limitations and actual verification status.
- Architecture/state/data-model and future Statamic adapter notes.
- Route/user-flow/validation inventory.
- Source/branding provenance and business-approval boundaries.
- Human acceptance checklist and actual automated verification record.
- CI checks without automatic deployment.

Run installation, strict typecheck, lint, core tests, production build and browser tests; fix failures rather than weakening standards. No unexplained TODOs, placeholder business questions, inert CTA buttons, mocked claims of saved state, disabled strict checks or unapproved features. No claim of certification, real email, secure tenant isolation, production readiness or live deployment.

Do the implementation in coherent local increments, checking each as you go. Do not stop after a discovery plan. When a dependency or tool is genuinely blocked, record the exact blocker, complete the safe independent parts, and clearly distinguish implemented code from executed evidence.

Finish with the repository structure, exact commands, implemented journeys, actual pass/fail/blocked results, changed files and remaining limitations. Report only what actually happened.

## END MASTER PROMPT
