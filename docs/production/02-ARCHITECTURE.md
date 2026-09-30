# Architecture decision — preserve the SPA, separate business data from CMS

## Decision
Use a modular Laravel application with Statamic 6 Core installed under `backend/`, retaining the existing React + TypeScript + Redux application at the repository root. Use MySQL 8.4 LTS/InnoDB for procurement records. The same HTTPS origin serves the compiled React application, Laravel business endpoints and CMS pages. This is not an independent headless Statamic API deployment.

Proposed layout, to be created by the implementation agent:

```text
src/                              existing approved React application
src/services/procurementApi.ts    RTK Query endpoints and typed responses
src/features/                     existing features, progressively API-backed
backend/
  app/Domain/Procurement/          scoring, transitions and application services
  app/Http/                       controllers, resources, requests and middleware
  app/Models/                     Eloquent business models
  app/Policies/                   organization and staff authorization
  config/auth.php                 application + Statamic guard/provider separation
  config/statamic/                Core configuration, no Pro dependency
  database/migrations/            reviewed MySQL schema
  resources/views/                SPA shell and CMS-rendered pages
  content/                        seed CMS material; mutable live content externalized
  tests/                          unit, feature and real MySQL integration tests
  composer.json / composer.lock
infra/                            Dockerfiles, HTTPS and runtime configuration
ops/                              local tools, preflight, backup, release and restore tools
compose.dev.yaml                  development only
compose.prod.yaml                 production only
package.json / package-lock.json  frontend reproducibility
```

Do not scaffold at the repository root over the SPA. Do not split into new repositories, rewrite React into Vue or introduce Inertia merely because Statamic's own control panel uses different frontend technologies. Do not rename all existing folders without a demonstrated need.

## Responsibility split
| Surface | Owner and storage |
|---|---|
| Approved respondent and Taleed staff interfaces | Existing React application; API state in RTK Query, temporary form state in React Hook Form |
| Procurement accounts, permissions, assessments, reporting | Laravel services/policies with MySQL; authoritative server calculations |
| Public editorial pages such as help, privacy, methodology/landing content | Statamic Core, rendered through Antlers/Blade; preserve approved appearance |
| CMS control panel `/cp` | One distinct Statamic administrator using its own provider/guard |
| Business API `/api/procurement/v1/*` | Custom Laravel controllers for custom Eloquent business data, not Statamic's paid REST/GraphQL content APIs |
| Email, report generation and background work | Laravel jobs/outbox and one effective scheduler |

The public landing can retain its React design while receiving a strictly allowlisted, escaped CMS page payload from its server-rendered shell. Do not inject all entries, global user data or sensitive CMS metadata. Do not create a generic replacement headless CMS API to work around Pro. A simple server-rendered help/privacy page makes the CMS a useful part of the system rather than an unused dependency.

## Core licensing and authentication boundary
Statamic's current licensing/pricing documentation describes a free Core edition with one administrator, while multi-user/roles and the built-in headless APIs are Pro capabilities. Its official independent-authentication-guard guide documents separating Eloquent application users from file-backed Statamic users. [S1–S4]

Implement exactly this separation:
- `web` session guard + `app_users` Eloquent provider for Champion, Analyst and application Super Admin.
- `statamic` session guard + Statamic provider for the sole CMS administrator.
- Separate password brokers/reset tables and the appropriate Statamic activation broker. Verify actual Statamic 6 configuration keys against the resolved package, not an older copied snippet.
- A business Super Admin is not a Statamic super user and never gains `/cp` access through that role. A CMS login alone is not a procurement login. Do not implement automatic account mirroring or sharing.
- Disable Pro in development as well as production and test both authentication domains with no license key. Never patch license checks, use a trial as a production dependency or add paid packages silently.

This is the engineering recommendation based on the documented separation, not a promise that any use of Statamic users becomes free when renamed. If an implementation changes this boundary or the installed terms are ambiguous, record the issue and obtain vendor clarification before release. If multiple people must use the actual Statamic control panel, the free-Core requirement must be revisited rather than bypassed.

## Session security and API handling
Use standard Laravel cookie/session authentication for the first-party SPA. Sanctum's SPA mode is the recommended API integration, explicitly configured to consult only the application session guard, not a list containing the CMS guard. Disable unused personal-access-token routes for this first-party-only product. No JWT or bearer-token persistence in localStorage is needed. [S5–S6]

Use Secure, HttpOnly session cookies, an appropriate SameSite policy and host-only cookie scope; do not share cookies across other Taleed subdomains. Retain CSRF middleware and the normal CSRF initialization flow. Trust only the inspected reverse-proxy network and validate host headers; never trust forwarded host/scheme from arbitrary internet clients. Login regenerates the session; logout and revocation invalidate it. Current active/verified/role status must be checked server-side, not trusted from an old browser claim. Clear RTK Query caches on logout, identity changes and unauthorized responses.

Preserve the approved six-digit email verification interface with a real expiring one-time challenge, hashed/peppered storage, attempt limits and resend throttling. Or record a minimal UX decision for Laravel's signed-link verification. Never retain the fixed demo code in runtime. Use standard password hashing and reset brokers; never design custom password encryption. Require MFA for privileged application staff with a maintained free Laravel-compatible implementation; preserve the main UI while adding the necessary setup/recovery step. Test Core CMS MFA availability before relying on it; protect CMS access with an approved VPN/IAP restriction when required.

Registration always creates a Champion. It cannot accept arbitrary role, organization assignment, verification, active state or export permission. Staff invitations are admin-authorized, expiring and single-use. Prevent mass assignment, email enumeration, self-deactivation and removal of the last active Super Admin.

## Frontend migration without a second source of truth
RTK Query owns fetched server state. Redux slices own transient UI state only; React Hook Form/Zod retain immediate validation, with Laravel validation authoritative. Do not keep a full replica of the production database in Redux. Fetch only a page's authorized resources, using pagination for staff lists.

Introduce the HTTP adapter feature by feature, but the production build has no demo adapter fallback, role switcher, reset/import database feature or shared demo account. Remove persistence of real assessment answers from localStorage. Allow only non-sensitive preferences there. Failed or offline saves show a clear unsaved state; no false 'saved' message. A production offline-first database and reconciliation system are outside scope.

Autosave is serialized per draft, uses a version number and returns the committed server version. Reject stale writes with 409; retain the user's unsaved edits in memory and show a reconciliation path. Flush or block pending writes before submission. Do not let a delayed autosave overwrite a just-submitted revision.

Keep HashRouter during the first backend connection to reduce scope, or adopt BrowserRouter in one explicitly tested change. For BrowserRouter, serve a shell only for known SPA routes and reserve `/api/*`, `/cp/*`, auth callbacks, health checks and assets. Unknown API routes must return JSON 404, not HTML. Public CMS routes cannot be swallowed by an unconditional wildcard.

## Dependencies and modularity
Target a maintained PHP version supported by Statamic 6 (official minimum currently PHP 8.3). PHP 8.4 is the proposed runtime baseline, subject to Composer and package checks. Resolve a Laravel version actually supported by that Statamic release; do not assume Laravel 13 compatibility merely because current Laravel documentation uses it. Commit Composer and npm lockfiles; use install-from-lock in CI/images. [S3]

Keep current working React majors unless a verified incompatibility or security fix requires change. Suggested backend packages are Laravel Sanctum, a standard Laravel-compatible MFA/auth facility, a maintained free PDF renderer if server-generated PDFs are approved, and an XLSX writer such as PhpSpreadsheet. Verify each actual version, license and maintenance status before installing. No paid Nova/Statamic addon is required. Avoid generic repository wrappers over every Eloquent model; use domain services for important multi-record operations.

Use MySQL-backed queues/cache/session storage initially to reduce single-VM services; optimize with a private cache service only if measured load warrants it. One code image can serve PHP-FPM, queue and scheduler commands; compiled static assets have the same release identity. Never build npm assets or run Composer updates inside the live serving container.

## Scope limits
Do not add carbon factors, reviewer approval of scores, accreditation, payment, SSO, multi-language CMS, attachments, AI scoring or unapproved roles. Framework-content publication approval and the respondent's immediate assessment results are different processes. A completed respondent submission does not wait for a staff approval stage.

References: `SOURCES.md`.
