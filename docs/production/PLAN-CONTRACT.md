# Taleed Procurement: delivery plan contract

**Status:** Approved by the director (repository owner) on 30 September 2026.
**Applies to:** every developer or agent (Claude Code, Codex, or a colleague) working on this repository until production launch.
**Precedence:**
1. `AGENTS.md` safety rules.
2. **This contract** (scope and decisions).
3. `prompts/production/MASTER.md` (engineering standard).
4. The phase prompt.

If any of these conflict, stop and ask the director.

## Working mode (director instruction, 1 October 2026)
Development proceeds on the **local track only**: Phase 2A (accounts/security logic), Phase 2B (assessment engine and scoring) and Phase 3 (wiring the approved React app, staff views and reports) — all built and tested on the local machine. The **deployment track (Phase 4 Release readiness and Phase 5 Production launch) is frozen** and kept exactly as specified; it does not begin until the director supplies the section 5 inputs and a written go-ahead to unfreeze. No work on either track touches production, pushes secrets, or provisions paid resources. This note does not change any decision in section 3; it only sequences execution.

## 1. The rule for developers: execute, do not decide
- Every product, scope, security, hosting and data decision is already made (section 3), or it is listed as a director input with a deadline (section 5).
- If a situation arises that this contract does not cover, the developer **stops and asks the director**. They do not choose a default themselves. Each question must include options and a recommendation, and must be recorded in `docs/production/decisions.md` once answered.
- Technical implementation choices inside a decided boundary are the developer's to make: naming, internal code structure, which test goes where. Anything a user, the client, the data or the cost would notice is not.
- Documented defaults (section 4) apply unless the director changes them.

## 2. Phases: 7 in total, 5 remaining

| # | Phase | Prompt | State |
|---|---|---|---|
| 0 | Discovery | `00-DISCOVERY.md` | ✅ Done (`43640c4`) |
| 1 | Local foundation | `01-FOUNDATION.md` | ✅ Done and **merged to `main`** (`ef9cc3d`, PR #1 `1dc49c9`) |
| 2A | Accounts, login and security | `02A-ACCOUNTS.md` | 🛠️ In progress / next — **local track** |
| 2B | Assessment engine, scoring and data integrity | `02B-ASSESSMENTS.md` | ⏳ **local track** |
| 3 | Connect the approved React app, staff views and reports | `03-UI-AND-REPORTS.md` | ⏳ **local track** |
| 4 | Release readiness (production tooling, backups, CI), no live release | `04-RELEASE-READINESS.md` | 🔒 **Frozen (deployment track)** |
| 5 | Production launch, explicitly authorised | `05-PRODUCTION-RELEASE.md` | 🔒 **Frozen** — needs written go-ahead plus section 5 inputs |

**Gate between phases:** these steps are mandatory for every phase.
1. The developer finishes the phase and writes evidence under `docs/production/evidence/<phase>/`.
2. The developer updates `STATUS.md` and `docs/production/HANDOFF.md`.
3. The developer opens a pull request from `implementation/<phase>`.
4. An **independent reviewer** (a second agent or developer, using `prompts/production/REVIEW.md` in a separate worktree) reports its findings.
5. All blocking and high findings are fixed.
6. The **director merges** the pull request and approves the next phase in writing.

No phase starts before the previous one is merged.

### Phase 2A: Accounts, login and security
**Goal:** real user accounts behind the existing API contract. No UI wiring yet, beyond what tests need.

**Scope**
- Migrations for: organisations; app-user organisation FK; staff invitations; email verification challenges; audit events.
- Standard framework tables with string identity widths.

**Endpoints**
- `POST /auth/register`: Champion only; allow-listed fields; throttled.
- Six-digit email verification: send and confirm.
- Login, logout, forgot password, reset password (partly done in Phase 1).
- Staff invitation, issue and accept: 72-hour single-use link; accept is throttled.
- `GET`/`PATCH /organization`: company profile; fields exactly as in the approved form; **no sector**.
- Staff user list and access changes: active and export permission. The last active admin cannot be removed; admins cannot change their own access, and this holds under concurrency.
- Organisation pause and enable.
- TOTP two-factor: **optional for every role** (D-17, changed 2 Oct 2026), with recovery codes. Staff can enable it under Two-step verification; nothing requires it.

**Commands**
- Audited operator command `procurement:admin:bootstrap`: creates the first Super Admin by invitation.

**Tests**
- Enumeration, CSRF, rate limits, mass assignment.
- Role and tenant tampering using forged IDs.
- Confusion between identical emails across the app and CMS providers.
- Concurrency of the last-admin rule.

**Deliverables:** migrations, models, policies, requests, resources, tests, updated `openapi-v1.yaml`, evidence.

**Pass criteria**
- The 05-ACCEPTANCE "Identity and tenant isolation" gates pass locally.
- All tests run on MySQL 8.4.
- Mail is verified in Mailpit.

### Phase 2B: Assessment engine, scoring and data integrity
**Goal:** the authoritative server-side assessment lifecycle.

**Scope**
- Versioned framework tables and importer: workbook/JSON hash, 4 domains, 40 questions, 64 actions, source cells.
- A publish command that needs `--approval-ref`.
- Cycle tables and a cycle command: **one open annual cycle**, business timezone `Asia/Riyadh`.
- Assessments, revisions, answers, immutable submission snapshots, domain results.
- Idempotency receipts and the transactional outbox.

**Endpoints**
- Create or resume a draft.
- Save answers with `expectedVersion` (409 on conflict).
- Atomic idempotent submit with the declaration.
- Result and history.
- Super Admin correction, which needs a reason. **Corrections are allowed after the cycle closes**; they are audited, and the old result stays effective until the correction is submitted.
- Staff read models: effective submissions only; draft **stage and answered count only**, never answers.

**Scoring**
- An exact PHP port, compared against all 14,641 oracle cases, including band-boundary tests and focus-area tie-breaking.

**Tests**
- Real MySQL concurrency tests: duplicate draft creation, save/submit races, duplicate submit, correction races.

**Deliverables**
- `docs/production/schema.sql`, exported from the tested migrations, plus an ER diagram.
- API contract updates and evidence.

**Pass criteria**
- The 05-ACCEPTANCE "Source fidelity, scoring and workflow" gates pass locally.
- One full Champion journey (register → verify → profile → draft → submit → result) and one admin-authorised correction work over real HTTP.

### Phase 3: Connect the approved React app, staff views and reports
**Goal:** the approved UI running entirely on the backend.

**Scope**
- RTK Query replaces `localRepository`.
- Removed from production: the demo role switcher, the shared password and code, the fixed date, and the reset/import screens. localStorage keeps harmless preferences only.
- Autosave states (saved, unsaved, error, conflict).
- Session-expiry handling. Pending saves are flushed before submit.

**Journeys**
- All three roles' approved journeys.
- Server-side portfolio filters and pagination.
- Comparison of 2–4 companies.
- People & access, where "create staff" sends an invitation.

**Reports and exports**
- Staff CSV/XLSX are generated **on the server** from frozen cohorts, with formula-injection protection, string IDs and private downloads.
- The Champion report stays as the approved **browser print / Save as PDF** A4 layout. There is no server PDF.

**Landing page**
- "Explore the live demo" becomes **"Sign in"**.
- The prototype disclaimer is removed.
- The layout is unchanged, and the wording is CMS-editable.

**CMS (Statamic)**
- Editable: privacy and terms pages (`/pages/*`), landing-page text, and email wording.
- The consent checkbox links to `/pages/privacy`.
- Questions and recommendations are **not** CMS-editable.

**Also:** re-capture the six spinner-only screenshots, and update the README and the user and admin guides.

**Pass criteria**
- The 05-ACCEPTANCE "Approved UI, reports and accessibility" gates pass.
- Playwright runs against the real Docker backend: keyboard, mobile, conflict, expiry and print checks.

### Phase 4: Release readiness, with no live release
**Goal:** a deployable release candidate and proven recovery tooling, using synthetic data only.

**Scope**
- `compose.prod.yaml`: only the edge publishes ports 80 and 443.
- An nginx edge serving **HTTPS on the static IP** with a Let's Encrypt IP certificate. **Renewal is automated and verified.** HTTP redirects to HTTPS.
- `/cp` is restricted to the **allow-listed IPs**, plus Statamic two-factor.
- The same release image serves app, worker and scheduler.

**Durable data and preflight**
- Durable data lives on a **separate retained data disk**.
- A fail-closed preflight checks the mount, marker and database identity, including after a reboot.

**Operations tooling**
- A genuinely offline `plan` command, and a separate connected `preflight`.
- Release lock; backup before migration; separate migration credentials; schema-compatible rollback.

**Backups**
- Daily and before every release, using a MySQL-native dump plus a file manifest.
- An encrypted copy goes to a private Cloud Storage bucket, kept **30 days**.
- An isolated restore procedure.

**Monitoring, CI and runbooks**
- Uptime and alert policies (site down, backup failed, certificate expiry, low disk) emailing **the director and the developer**.
- CI and release workflow with a gated production step.
- Runbooks: first provisioning, releases, backup and restore, incidents, certificate renewal.
- An anonymise-account admin command (retention support).

**Pass criteria**
- The 05-ACCEPTANCE "Production safety and recovery" gates pass with synthetic data.
- Failure-path tests: missing disk, wrong marker, failed backup or migration, low space, concurrent deploy, interrupted session.

### Phase 5: Production launch
**Goal:** a verified live release, only after the director's written go-ahead naming the target.

**Steps**
1. Read-only inspection of the target, then a redacted plan for the director's approval.
2. One-time provisioning:
   - a **new dedicated VM** in the existing Taleed GCP project;
   - the reserved static IP;
   - the data disk;
   - the backup bucket.
3. Release.
4. Initial real accounts: Super Admin and CMS admin.
5. Content v1.0.0 published with the **Taleed programme owner's sign-off reference**.
6. First cycle created.

**Verification**
- A second harmless release proves persistence.
- An isolated restore drill.
- Handover evidence.

**Pass criteria**
- A verified `https://<static-ip>` URL, the release commit and image digest, and backup and restore evidence.
- All three roles verified live, and the director's acceptance.

## 3. Decision register (binding)
Full rationale is in `docs/production/decisions.md`.

### Process
| ID | Decision |
|---|---|
| D-26 | 7 phases (0, 1, 2A, 2B, 3, 4, 5); 5 remain |
| D-27 | Independent review of **every** phase before the next starts |
| D-28 | A branch and pull request per phase; **only the director merges** to `main`; CI on every pull request |
| D-29 | The director approves **each production release in writing**; the developer executes; no automatic deploys (to be revisited only after launch acceptance) |

### Architecture
| ID | Decision |
|---|---|
| D-01–D-09 | As in `decisions.md`: React SPA kept; Laravel 13 + Statamic 6 **Core** in `backend/`; PHP 8.4; MySQL 8.4; same-origin HTTPS; separate app and CMS guards; HashRouter; Docker layout |

### Identity
| ID | Decision |
|---|---|
| D-30 | Email verification by **six-digit code**: valid 15 minutes, 5 attempts, resend throttled |
| D-17 | TOTP two-factor **optional for all roles**, including Super Admin and Analyst. *Changed 2 Oct 2026 by director instruction (Mohamad Oueidat), replacing "mandatory for Super Admin and Analyst". Risk accepted: a staff password alone gives access to all company results and exports.* |
| D-10 | New staff are added by **email invitation**: 72-hour single-use link |
| D-31 | **Public sign-up**; **one Champion per company**; a duplicate company name or registration ID is blocked with "contact Taleed" |

### Content
| ID | Decision |
|---|---|
| D-12 | A client-supplied **privacy notice** at `/pages/privacy`, managed in Statamic and linked from the registration consent checkbox. **Launch is blocked without the approved text** |
| D-12b | CMS-editable: legal pages, landing text, email wording. Framework content is not editable |
| D-19 | Framework v1.0.0 is published only with the **Taleed programme owner's written sign-off** reference |
| D-32 | **English only** at launch. Arabic would be a separate future project |
| D-39 | Production landing page: "Explore the live demo" becomes **"Sign in"** and the prototype disclaimer is removed. The GitHub Pages demo stays manual-only (D-16) |

### Assessment and reports
| ID | Decision |
|---|---|
| D-33 | **One annual cycle** open at a time, created by an audited command; past cycles remain in history |
| D-34 | Super Admin **corrections are allowed after a cycle closes**, with a reason and an audit record |
| D-14 | Report PDF by **browser print** only; staff CSV/XLSX generated on the server |
| D-35 | Data retention: the **client's legal team provides the policy by the end of Phase 4**. Until then, keep everything and anonymise on written request. The anonymise command is built in Phase 4. The policy is a launch gate |

### Production
| ID | Decision |
|---|---|
| D-15 | A **new dedicated GCE VM** in the existing Taleed GCP project, with a separate data disk |
| D-41 | Address: a **reserved GCP static IP served over HTTPS only** (HTTP redirects), using an IP certificate with automated renewal. There is no domain name. A later move to a domain would change the URL, not the data |
| D-36 | Mail: **SendGrid** with the **`myprototype.cloud`** sender domain already authenticated for the Taleed apps, and a **new dedicated API key** for Procurement |
| D-37 | Backups daily and before each release; encrypted off-VM copy; 30 days; RPO 24 hours; RTO 4 hours; restore rehearsed before launch |
| D-38 | `/cp` reachable only from **allow-listed IPs**, plus two-factor |
| D-40 | Alerts emailed to **the director and the developer** |

## 4. Documented defaults
The developer uses these as written. Changes need director approval.

### Accounts and sessions
| Topic | Default |
|---|---|
| Password rule | 12+ characters with upper case, lower case and a digit (as in the approved form) |
| Login throttle | 5 per minute per email and IP |
| Registration throttle | 3 per minute and 20 per day per IP |
| Link lifetimes | Reset link 60 minutes; invitation 72 hours |
| Session | 120-minute idle timeout; Secure, HttpOnly, SameSite=Lax host-only cookie |
| Analyst export permission | Off by default; granted by a Super Admin |

### Data, time and exports
| Topic | Default |
|---|---|
| Storage time | Stored in UTC |
| Cycle timezone | `Asia/Riyadh` for cycle dates and display |
| Test organisations | Flagged and excluded from portfolio figures and exports |
| Generated export files | Kept 7 days, then deleted |

### Support and operations
| Topic | Default |
|---|---|
| Browsers | Last two versions of Chrome, Edge, Safari and Firefox; current iOS and Android |
| Accessibility | WCAG 2.1 AA for the approved screens (best effort, no formal audit) |
| Maintenance window | Up to 30 minutes, announced, for releases that change the schema; no zero-downtime promise |
| Production VM size | Proposed at Phase 4 with a monthly cost estimate for the director's approval; not assumed |

## 5. Director inputs and deadlines
| # | Input | Needed by | Blocks |
|---|---|---|---|
| 1 | Approve the Phase 1 review and merge; name the reviewer (agent or person) | Before 2A | 2A |
| 2 | Privacy notice text (client legal) | End of Phase 3 | Launch |
| 3 | Data retention policy (client legal) | End of Phase 4 | Launch |
| 4 | Taleed programme owner's written sign-off of workbook v1 (40 questions, 64 recommendations, bands) | Before Phase 5 | Publishing content |
| 5 | Developer access to the GCP project (least-privilege role), VM size and cost approval, static IP reservation | Before Phase 5 | Provisioning |
| 6 | A new SendGrid API key and the exact sender address on `myprototype.cloud` (proposed: `no-reply@myprototype.cloud`, display name "Taleed Procurement") | Before Phase 5 | Emails |
| 7 | IP addresses allowed to reach `/cp` | Before Phase 5 | CMS access |
| 8 | Alert email addresses (director and developer) | Before Phase 5 | Monitoring |
| 9 | Identities of the first real Super Admin and the CMS administrator | Before Phase 5 | Launch accounts |
| 10 | Written release go-ahead naming the date and window | Phase 5 | Launch |

Secrets (API keys, passwords) are **never** sent in chat, email, Git or tickets. They go straight into the approved runtime configuration or secret store.

## 6. Out of scope unless the director re-scopes
- Arabic or RTL; multiple users per company.
- Program Manager or new roles; reviewer approval of scores.
- Server-generated PDFs; a public demo in production.
- A staging environment; SSO.
- Payments, certification, AI scoring, attachments.
- Changes to the Survey or Sustainability applications or their VMs.

## 7. Change control
Any change to sections 2–6 needs the director's written approval and a new row in `docs/production/decisions.md`. The same applies to anything that would alter the approved design, add cost, touch production, or change what data is stored.
