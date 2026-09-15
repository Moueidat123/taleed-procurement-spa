# Prototype acceptance checklist

**Prepared 15 September 2026. Browser execution status: NOT RUN in the preparation environment.**

Use a fresh browser profile/origin or Super Admin reset. Test with dummy records only. Record actual results in the last two columns; a supplied implementation or test script is not evidence of a passed browser check. Automated core evidence is separately recorded in verification.md.

| ID | Procedure | Expected result | Actual status | Evidence / notes |
|---|---|---|---|---|
| UAT-01 | Install dependencies; typecheck, lint, build | Compatible installed packages; all commands exit 0 | Not run | |
| UAT-02 | Open landing at desktop and 390 px width | Brand foundations, readable content, working sign-in/start links; no page overflow | Not run | |
| UAT-03 | Submit empty/invalid registration, then valid dummy values | Inline labels/errors; password/confirmation/consent validation; no data until valid | Not run | |
| UAT-04 | Register same normalized email twice | Duplicate rejected; no extra identity | Not run | |
| UAT-05 | Enter invalid code, resend, then 123456 | Wrong code rejected; countdown; simulated verification succeeds; no email claim | Not run | |
| UAT-06 | Complete company fields without authority, then with authority | Submission blocked until required fields/authority valid | Not run | |
| UAT-07 | Reuse existing company name/reference | Duplicate prevented; no silent organization merge | Not run | |
| UAT-08 | Sign into Sahara | Existing 12/40 draft restored; completion distinct from score | Not run | |
| UAT-09 | Save Yes and No; reload; sign out/in | Both saved; progress counts both; selected user/session behavior clear | Not run | |
| UAT-10 | Clear a saved answer | Answer becomes null; progress decreases; final submission blocked | Not run | |
| UAT-11 | Continue incomplete section | Missing message and first unanswered input focus; section navigation still available | Not run | |
| UAT-12 | Review with one blank | Submit unavailable; missing link opens exact question; no final result/report | Not run | |
| UAT-13 | Complete all 40; omit declaration | Declaration requested; no submission committed | Not run | |
| UAT-14 | Declare, confirm, then refresh/retry | One final revision/snapshot; read-only answers and immediate results | Not run | |
| UAT-15 | Synthetic all-No/all-Yes cases | 0% Foundational / 100% Best-in-Class; no urgent-gap language at 100% | Not run | Core covered |
| UAT-16 | Boundary totals 16,17,26,27,32,33 Yes | 40 F; 42.5 D; 65 D; 67.5 A; 80 A; 82.5 BIC | Not run | Core covered |
| UAT-17 | Namaa fixture 6/4/8/6 | Overall 60 D; domains 60D/40F/80A/60D; focus Spend/Category/SRM | Not run | Core covered |
| UAT-18 | Open recommendations | Exactly 16 actions from each domain's own band, no substitutions | Not run | |
| UAT-19 | Company report and JSON | Original snapshot details; print dialog/A4, no clipped output; JSON reconciles | Not run | |
| UAT-20 | Open another company's result and staff URL as Champion | No other company result or staff interface disclosed through routes | Not run | Not a DevTools/security test |
| UAT-21 | Analyst opens portfolio and a draft result URL | Submitted portfolio available; draft answers unavailable; export disabled by default | Not run | |
| UAT-22 | Manager grants analyst export then analyst exports | Permission reflected; same filtered effective rows; CSV/XLSX open correctly | Not run | |
| UAT-23 | Filter portfolio by cycle/sector/band/search | Table, averages, distribution, denominator and export agree; empty state helpful | Not run | |
| UAT-24 | Compare 2–4 organizations | Only same-cycle/framework submitted results; no drafts or external benchmarks | Not run | |
| UAT-25 | Manager opens Namaa correction | Reason required; old final unchanged and still effective; new editable copy | Not run | |
| UAT-26 | Company submits correction; inspect history/portfolio | Both revisions retained; only replacement counted as effective | Not run | |
| UAT-27 | Duplicate correction / edit final / stale parent | Every prohibited transition rejected | Not run | Core covered |
| UAT-28 | Close cycle or pause company | Answer edits and submission disabled/rejected; history remains readable | Not run | |
| UAT-29 | Clone framework; edit draft; publish | Source version unchanged; IDs/rules locked; reference required; demo publication | Not run | |
| UAT-30 | Change cycle framework after first assessment | Rejected; historical version pinned | Not run | |
| UAT-31 | Manager attempts Admin management; self-disable | Prohibited actions rejected; no public self-promotion | Not run | |
| UAT-32 | Export CSV with formula-leading company text | Spreadsheet formula injection escaped; commas/quotes/Unicode preserved | Not run | Core formatting covered |
| UAT-33 | Simulate quota exception | No false saved state, no persisted answer change, visible useful error | Not run | Core repository covered |
| UAT-34 | Modify in two tabs, then reload stale tab | Stale tab pauses; reload shows latest data; no silent overwrite | Not run | Use Web Locks-capable browser |
| UAT-35 | Corrupt stored JSON / future schema | Original retained; recovery screen; no silent seed overwrite | Not run | Core parser covered |
| UAT-36 | Import malformed/oversize/tampered snapshot backup | Rejected before data replacement; valid existing dataset retained | Not run | Core parser covered |
| UAT-37 | Export backup, reset, import valid backup | Type REPLACE required; new dataset instance; restores exact accepted records | Not run | |
| UAT-38 | Keyboard through forms/dialogs/skip link | Visible focus, labels/errors associated, no route change from skip link | Not run | |
| UAT-39 | Mobile questionnaire/results/staff navigation | Usable controls; no document overflow; tables may scroll within container | Not run | |
| UAT-40 | Built static output and subpath hash refresh | No server rewrite required for hash paths; relative assets load | Not run | |

Approval record: tester, browser/version, date, build/commit, unresolved severity, PM decision. Do not replace these placeholders with presumed approval. A local prototype cannot pass production security, real multitenancy, backup-recovery or cloud-deployment acceptance tests; those belong to the later backend project.
