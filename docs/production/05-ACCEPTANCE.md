# Acceptance gates and required evidence

Statuses initially: **not implemented / not tested**, except the uploaded SPA core test and source-text comparisons documented in the audit. Check boxes below are targets, not claims of completion.

## Foundation and free-Core boundary
- [ ] A clean clone boots locally with Docker and trusted HTTPS using documented commands, with no production access or records.
- [ ] Supported Statamic 6 / Laravel / PHP versions resolve and lock; npm lock is committed and `npm ci` succeeds.
- [ ] Core is configured in both environments; no Pro key, trial feature, paid addon or licensed headless API is needed.
- [ ] An application Champion, Analyst and Super Admin cannot log into `/cp`; a CMS-only session cannot call the business API.
- [ ] Same-email users in separate providers, if present, do not inherit each other's login, reset tokens, roles or permissions.
- [ ] CSRF, login session regeneration, logout invalidation, host-only Secure cookies, active/verified checks and trusted proxy handling pass.
- [ ] Correctly rendered CMS content and a functioning single-admin control panel demonstrate actual Statamic use.

## Identity and tenant isolation
- [ ] Registration accepts only allowed profile fields; tampered role/export/verification/org IDs do not escalate privileges.
- [ ] Real verification expires, limits attempts/resends and cannot be replayed. Recovery responses do not disclose account existence.
- [ ] Staff invitations expire and are single-use, with a concurrency test; privileged accounts complete MFA.
- [ ] Server rejects cross-organization reads/writes by identifier on every profile, draft, result, report and artifact endpoint.
- [ ] Staff responses contain no draft answers, even in hidden fields, exports, logs, cached responses or the SPA bootstrap payload.
- [ ] Analyst export permission is enforced on request, job execution and download; revocation takes effect.
- [ ] Disabled account/organization loses access; last-admin and self-deactivation protections survive concurrent requests.
- [ ] RTK Query caches and any transient sensitive UI data are cleared on logout/identity change.

## Source fidelity, scoring and workflow
- [ ] Preserve exact 40 source question IDs/texts, four ordered domains and 64 recommendation actions, including source hash/cells.
- [ ] Port the existing TypeScript score oracle into PHP tests and compare all 14,641 domain Yes-count combinations.
- [ ] Test boundary classification directly at 40, just above 40, 65, just above 65, 80 and just above 80; never classify rounded presentation text.
- [ ] Reject missing, extra, duplicate, wrong-version or unknown questions; null is incomplete, not No.
- [ ] Return four actions per domain based on that domain's band; verify stable focus ordering and all-100 behavior.
- [ ] A profile and declaration are required; dates use real server time and the recorded cycle timezone.
- [ ] Concurrent draft creation produces one draft; stale autosave returns 409; delayed autosave cannot change a submission.
- [ ] Submit is transactional/idempotent, creates a full immutable snapshot and updates the effective pointer only after valid commit.
- [ ] Concurrent submit/retry yields one submitted revision and one effective submission, with no duplicate notifications.
- [ ] Correction creates a separate draft with reason and parent; old submission remains effective until corrected submission succeeds.
- [ ] Published framework/cycle pins cannot mutate underneath historical or in-progress assessments.
- [ ] A respondent receives results immediately; no unapproved human scoring-approval stage is introduced.

## Approved UI, reports and accessibility
- [ ] Champion register → verify → company profile → dashboard → four sections → review → submit → results/recommendations → report/history works end to end.
- [ ] Analyst portfolio → organization → result → 2–4-company comparison works; unavailable exports are clearly explained.
- [ ] Super Admin exports, correction authorization and People & access work with backend enforcement.
- [ ] Preserve approved styling and responsive behavior; loaded-screen screenshots replace incomplete loading-state baselines.
- [ ] Keyboard navigation, visible focus, associated errors, accessible radio groups and save/error status announcements work.
- [ ] Network loss, expired session, validation failure, conflict, mail/report failure and retry never show false success.
- [ ] Dashboard metrics and CSV/XLSX use identical filters and effective-submission membership; test exclusion and denominator reconcile.
- [ ] CSV/XLSX formula-injection protection covers user-entered cells; question IDs remain strings; output contains no credentials or unauthorized personal data.
- [ ] Printable A4 report preserves the approved layout and immutable source/snapshot information. Any server PDF is private, accurately rendered and cannot fetch arbitrary URLs or execute untrusted content.
- [ ] Production bundle/network traffic has no role switcher, shared demo password/code, browser database reset/import or automatic demo fallback.

## Production safety and recovery
- [ ] Production resolved Compose has no development source mounts, Vite, Mailpit, debug mode or public DB/cache port.
- [ ] Shared-VM edge routes work without replacing other Taleed apps; app networks, cookies, DB accounts and volumes are separate.
- [ ] A missing/wrong retained disk or database marker prevents startup/release rather than creating an empty replacement.
- [ ] Replacing application containers and a second code release retain users, drafts, submissions, CMS edits and reports unchanged.
- [ ] Authorized host-reboot test verifies the disk-before-database startup dependency.
- [ ] Backup completes with integrity metadata and restricted off-VM copy; an isolated restore proves the data/key/report recovery path.
- [ ] Backup failure, low disk, queue failure, unavailable DB, TLS expiry and unhealthy app have actionable alerts.
- [ ] Deployment is serialized, migrations run once, workers are coordinated and an SSH interruption does not cause a half-applied release.
- [ ] Rollback compatibility and no-automatic-database-restore behavior are demonstrated safely with synthetic data.
- [ ] Source-content approval, environment values, operational owners and recovery targets are explicitly recorded before launch.

## Evidence format
For each gate, record test ID, environment, commit/image digest, command, result, timestamp and artifact reference. Retain sanitized browser traces/screenshots and machine-readable test results. Never store production credentials, real user answers or private backup data in evidence.

Frontend: core tests, typecheck, lint, production build and Playwright. Backend: unit/feature tests plus real MySQL integration/concurrency tests. Infrastructure: build, Compose validation, preflight guard tests, synthetic persistence test and an authorized isolated recovery exercise. Unavailable commands are **not run**, not passed.

Definition of local completion: a real API-backed vertical slice and all approved flows operate from the Docker HTTPS URL. Definition of production completion: the verified external HTTPS URL and release/recovery evidence exist after explicit deployment authorization. 'Generated Docker files' is neither definition.
