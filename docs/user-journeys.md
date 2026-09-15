# Complete flow, screen inventory and validation

## Company journey

**Introduction -> register -> simulated verification -> company profile -> workspace -> four sections -> review -> confirmation -> immediate result -> recommendations/report -> history.** Returning users sign in and resume the saved draft. A submitted company may receive a manager-opened correction, which follows the same answer/review/submission flow while preserving its previous result.

| Interface / hash route | Main content and actions | Validation / states |
|---|---|---|
| `/` | Value proposition, four areas, how it works, start/sign in | Prototype/source disclaimer |
| `/methodology` | Yes/No meaning, equal weights, maturity bands, use/limits | No N/A or certification claim |
| `/privacy` | Browser-local storage, synthetic records, recovery boundaries | Not a production privacy policy |
| `/register` | Name, work email, job title, dummy password/confirmation, notice | Required fields; email format/uniqueness; 12+ characters, upper/lower/digit; matching passwords; consent |
| `/verify` | Six-digit code and resend countdown | Requires local user; six digits; demo code 123456; incorrect-code error; 30-second resend simulation |
| `/login` | Email/shared password or demo role cards | Active local account; shared demo password; route to verification/profile/workspace |
| `/forgot-password` | Email input, generic simulated response | Valid email; no account-existence disclosure; no real email |
| `/reset-password` | Dummy password and confirmation | Same password rules; discards credential; no real token |
| `/app/profile` | Company, country, sector, employee range, optional reference, authority | Required proposed fields; duplicate name/reference prevention; authority required; unsaved warning on page unload |
| `/app/dashboard` | Current cycle, completion, draft/result link, four section cards | Profile required; active/open eligibility; no draft maturity score; no duplicate draft |
| `/app/assessment/:id/1` | Category Management, all 10 exact questions | Yes/No/null; explicit clear; missing-section message and focus |
| `/app/assessment/:id/2` | Spend Analysis, all 10 exact questions | Same; question 2.3 is first missing in seeded draft |
| `/app/assessment/:id/3` | Strategic Sourcing, all 10 exact questions | Same; cycle/organization pause disables editing |
| `/app/assessment/:id/4` | Supplier Relationship Management, all 10 exact questions | Same; then review |
| `/app/assessment/:id/review` | Readiness summary, all answers, missing-question links, declaration | Incomplete submission blocked; no result computed; live eligibility rechecked |
| Submit confirmation dialog | Company, revision, read-only warning, confirm/cancel | Exact same command revalidates all requirements; visible storage failure |
| `/app/results/:id` | Overall/domain scores, bands, narrative, three relative focus areas | Final authorized revision only; 100% sustain-excellence wording; ties disclosed |
| `/app/recommendations/:id` | Four actions per domain; total 16 | Select using each domain band; not tasks or AI-generated advice |
| `/app/responses/:id` | Exact submitted 40 answers | Read-only; no editing submitted revision |
| `/app/report/:id` | Company report, scoring, interpretation, recommendations, method | Browser print dialog/A4 CSS; snapshot JSON also available |
| `/app/history` | Cycles, revisions, completion/final status, result/report links | Own organization only; earlier revisions remain available |

## Taleed staff journey

**Staff demo sign-in -> portfolio -> filters -> organization/result -> same-cycle comparison -> authorized export.** Managers also create correction drafts, configure cycles, manage framework versions and staff access. Administrators inspect local audit/recovery controls.

| Interface | Main content and actions | Validation / states |
|---|---|---|
| `/app/portfolio` | Participation metadata, effective submitted scores, distribution, domain averages, table | Cycle/sector/band/search; visible n; drafts excluded from maturity; empty result; analyst export permission |
| `/app/organizations` | Company directory, search, active/paused filter | Staff only; no public company rankings |
| `/app/organizations/:id` | Identity, participation metadata, submitted history, result links | Staff cannot read draft answers; manager may pause company or open correction |
| Correction dialog | Reason; preserve parent submission | Manager/Admin; current effective final; meaningful reason; no existing draft; open cycle |
| `/app/compare` | Pick 2–4 submitted companies, compare domain/overall results | Same cycle/framework only; no external benchmark claim |
| `/app/frameworks` | Version, source, bands, full 40-question/64-action library | Staff read; manager clone to unused version; published content locked |
| Framework draft editor | Question or recommendation wording | Manager/Admin; draft only; IDs, counts, order and scoring locked; 10–2000 characters |
| Publication dialog | Approval-reference field, version context | Manager/Admin; draft only; simulation label; reference required |
| `/app/cycles` | Cycle ID/name, opening/closing dates, published framework, status | Required valid values; dates ordered; framework cannot change after first assessment |
| `/app/access` | Staff creation, access and analyst-export toggles | Email uniqueness; manager cannot create/alter Admin; no self-deactivation; no public role escalation |
| `/app/audit` | Search latest 500 local events | Admin; diagnostic only; not tamper-proof or compliance evidence |
| `/app/data` | JSON backup, raw export, file validation, reset/import | Admin; supported schema; 3.5 MB guard; references/results checked; typed REPLACE |
| Startup recovery | Preserve corrupt/unsupported bytes, export, import/reset | Only appears after actual load failure; no silent reseeding |

## Shared states

Unauthenticated access redirects to sign-in. Unverified accounts go to simulated verification. Forbidden roles see an explanatory screen. Missing records/routes have a return path. Lazy pages show a loading indicator; rendering errors show recovery guidance. Save errors stay visible until dismissed/retried. Cross-tab changes pause editing until reload. Native dialogs provide focus behavior; the skip link focuses main content without changing the hash route.

## PM walkthrough

1. Sign in as **Company Champion**. Show 12/40 progress, open Spend Analysis, select No for 2.3, refresh and confirm the answer remains. Clear it and demonstrate the missing-answer link from review.
2. Use **Second Company**. Show its 60% overall result, four different domain bands, tie ordering and 16 relevant recommendations. Open report preview and invoke browser print.
3. Switch to **Taleed Analyst**. Filter the portfolio to Logistics; explain the n=1 denominator and why the Sahara draft is not a final score. Show export disabled.
4. Switch to **Program Manager**. Open Namaa and create a correction with a reason. Its 60% submission stays effective while the new draft exists. Switch to Second Company, edit and submit the correction, then inspect retained history.
5. As Manager, clone framework 1.0.0 to 1.1.0, edit draft text and simulate publication. Show that 2026 submissions keep 1.0.0. Create a new cycle pinned to 1.1.0. Explain that the publication reference is a local simulation, not actual content sign-off.
6. As **Super Admin**, export a JSON backup, inspect audit activity, and show the typed reset/import confirmation. Do not replace a useful demonstration dataset without first exporting it.

The default `2026-09-15` demonstration clock determines all cycle-date behavior. The presentation controls do not send email, create an actual staff invitation, or synchronize data across devices.
