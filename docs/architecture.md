# Architecture and state-management contract

## 1. Runtime boundary

This is a Vite-built React SPA served as static assets. React Router hash routes provide multiple interfaces without server rendering. The only runtime persistence is one versioned localStorage dataset plus a session-only synthetic identity. There is no network API, service worker, database server, real login or email service.

The intended upgrade path is a new repository adapter and genuine server domain services, not uploading browser JSON into production as trusted data.

## 2. State ownership

| State | Owner | Persistence | Reason |
|---|---|---|---|
| Organizations, users, cycles, framework versions, assessments, snapshots, audit | Redux `data` slice | Validated repository | Shared cross-screen business state |
| Selected demo user ID | Redux `session` slice | Session storage through listener middleware | Refresh continuity without credentials |
| Saving counter, failed operation, boot error, external-change flag | Redux `ui` slice | None | Shared operational feedback |
| Form inputs and field errors | React Hook Form/Zod | Explicit successful command only | Avoid global Redux updates for every text keystroke |
| Open dialog, mobile menu, acknowledgement checkboxes | Component state | None | Transient presentation state |
| Portfolio search/filter and selected cycle | URL search parameters where implemented | URL | Back/forward-friendly views |
| Completion, effective revisions, maturity totals, filtered aggregates | Pure functions / memoized selectors | Not duplicated for drafts | Avoid contradictory derived state |
| Submitted result, text and inputs | Immutable submission snapshot | Repository | Preserve the historical evidence used to produce a result |

Maps are normalized by string ID. A dataset contains references between records instead of embedding live users/organizations into every draft. A final result intentionally copies the relevant source framework, organization details, respondent and answers. This duplication is a historical snapshot, not a second editable truth.

## 3. File and dependency direction

`features/components -> app commands and selectors -> domain -> types`

`app command orchestration -> AssessmentRepository -> LocalRepository`

`domain` imports neither React nor browser APIs nor an external library. IDs and timestamps are injected into `applyCommand`. Scoring and permission checks can be tested independently. Zod validates form input for useful feedback; the repository boundary also runs an independent structural/domain validator, so form validation is not the only protection against malformed imported data.

## 4. Write lifecycle

1. The component validates a form and dispatches a typed command.
2. A serialized Promise queue ensures same-tab commands do not overlap.
3. When supported, a Web Lock named for this dataset prevents cooperating tabs from writing simultaneously.
4. The command checks the current synthetic actor, role, ownership, cycle, status and inputs.
5. It computes the next dataset without mutating the previous one. Submitted snapshots are never changed by an edit command.
6. The repository reads the latest stored instance/revision and compares them with the command's expected base. A reset creates a new instance ID so an old tab cannot overwrite it merely because revision numbers happen to match.
7. The repository validates and serializes the next dataset. Its version increments once. `setItem` must succeed before Redux replaces the displayed domain data.
8. Success updates the saved indicator. Quota, invalid data and revision errors are displayed; the UI does not claim an unsaved answer succeeded.

This is deliberately **save-before-success**, not optimistic UI. An answer click updates the persistent draft immediately; it is not deferred in a debounce that could be lost during sign-out/navigation. Inputs are temporarily disabled during a write. Profile/staff forms save explicitly.

No reducer reads localStorage, generates random IDs, fetches data or creates dates. Redux's default serializability and immutability middleware remain enabled. Listener middleware handles only session persistence; the actual business repository is an explicit orchestration dependency rather than a blind entire-store subscriber.

## 5. Concurrency and recovery limits

A `storage` event, including a cross-tab clear, pauses editing in stale tabs and offers reload. A compare/revision conflict also exposes the reload state. Data is not automatically merged. The fallback without Web Locks performs a revision check but **cannot guarantee atomic cross-tab compare-and-swap**; use one tab in that environment.

Startup requires a supported, validated schema. It seeds only a genuinely missing dataset. Corrupt or future-version data remains untouched; recovery offers raw export, validated backup selection and a typed `REPLACE` confirmation. Import checks keys, field types, bounded lengths, relationships, answer IDs/values, correction chains and recomputed snapshots. Dangerous prototype property names are rejected. There is no legacy schema that can honestly be migrated yet: v1 is supported, future versions fail closed. Add a tested explicit migration when a real older schema exists.

The design copies and validates the whole bounded dataset on a write. That is straightforward and testable for this presentation, but is not optimized for hundreds of organizations or many historical snapshots. A real API implementation should update individual records under server transactions and use per-entity query invalidation or Redux Toolkit Query. Do not introduce multiple competing remote-data caches.

## 6. Assessment state machine

`no assessment -> draft -> submitted`

`effective submitted revision -> manager opens correction draft -> company submits correction -> new effective revision`

A draft has nullable answers and no result snapshot. Submission requires an active verified Champion, owned active organization, complete authority-confirmed profile, open pinned cycle, all 40 canonical answers and a declaration. It immediately creates a read-only result. Repeating submission for the same owned final record is idempotent.

There is one active draft per organization/cycle. A manager can open a correction only from the current effective submission, with a meaningful reason. The old final remains effective until the replacement draft is submitted. Effective reporting selects the highest submitted revision for each organization/cycle; draft corrections and superseded final records do not inflate the denominator.

## 7. Framework versions

The 1.0.0 framework is marked published **only in the demo seed**, with an explicit non-approval reference. A manager can clone to an unused semantic version, edit question/recommendation wording in the draft, and simulate publication with an approval reference. Source IDs, number/order of domains/questions, thresholds and weights are not editable. Published wording is immutable through commands. A cycle cannot change its framework after an assessment exists.

Source content approval is not inferred from design approval. The source checksum is provenance of the original workbook, not the checksum of the reconstructed JSON or the edited draft. An edited draft records its changed status; it does not claim byte-for-byte source parity.

## 8. Roles

| Capability | Champion | Analyst | Program Manager | Super Admin |
|---|---|---|---|---|
| Own profile, draft, answers, submit | Yes | No | No | No |
| Own final results and report | Yes | — | — | — |
| Portfolio and submitted company details | No | Yes | Yes | Yes |
| View draft answer content as staff | No | No | No | No |
| Portfolio CSV/XLSX | No | Explicit grant | Yes | Yes |
| Open correction; manage cycles/framework drafts | No | No | Yes | Yes |
| Staff invitations/access simulation | No | No | Restricted; cannot create/alter Admin | Yes |
| Audit and dataset replacement | No | No | No | Yes |

A local role switcher intentionally bypasses real authentication for presentation. Every claim here is about UI/command behavior in a shared synthetic dataset, not protection from someone controlling their browser. There is no claim of secure multitenancy.

## 9. Reports and export

Company pages and print reports use snapshot answers, source text, organization details and computed result. The cycle label displayed in the surrounding UI/report is current administrative metadata; score/content snapshots are immutable. Current effective/historical status also changes legitimately when a correction is submitted.

CSV/XLSX require a staff role and export permission, use effective submissions from one cycle/framework, and share the same filtered rows as the portfolio. Formula-leading strings are escaped before spreadsheet consumption. XLSX loads ExcelJS lazily. JSON is a local snapshot or administrator backup. Browser Print / Save PDF is an actual print flow, not a server-generated report.

## 10. Statamic/Laravel transition

| Prototype | Future server implementation |
|---|---|
| Synthetic session and role switcher | Real identity, email verification, session cookies/CSRF and server role membership |
| `LocalRepository` | HTTP repository client and authorized Laravel endpoints |
| `applyCommand` in browser | Server domain services, validation and policies; browser validation remains UX only |
| Dataset revision + Web Lock | Database transactions, row versions, unique constraints and idempotency keys |
| Local normalized records | Relational operational tables using the verified approved database |
| Local result snapshot | Server-authoritative immutable result, content version/hash and audit linkage |
| Native print / local file exports | Authorized private report generation/download, as approved |
| Mutable local audit | Protected server audit with access/retention controls |
| Explicit local backup/reset | Production backup, restoration drill, deployment safety and retention policies |

Suggested contracts: create/read draft; PATCH answers with expected version; GET readiness; POST submit with idempotency key; GET authorized result; POST controlled correction; GET same-framework portfolio; POST authorized exports. Validate ownership independently on every server request and report download. Never accept a browser-submitted score as authoritative.

Preserve the actual approved Taleed deployment/database conventions after inspecting the authorized reference repositories. The earlier Statamic 6/Laravel/SQL Server direction does not verify cloud resources, licenses or production mounts. Do not copy production credentials or sensitive records into this prototype.
