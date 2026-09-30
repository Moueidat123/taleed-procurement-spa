# Reference reuse matrix

**Date:** 30 September 2026. The following were read-only checkouts:

| Tag | Repository | Path and commit |
|---|---|---|
| SV | Survey-statamic6 | `/Users/user/Documents/GitHub/Survey-statamic6` @ `3f24fe44` |
| ST | statamictaleed (older Survey predecessor) | `/Users/user/Documents/GitHub/statamictaleed` @ `e968cec` |
| SD | sustainability-diagnostic-tool | `/Users/user/Documents/GitHub/sustainability-diagnostic-tool` @ `2b8ec66` |

No code was copied in Phase 0. **Reuse** means adopt the pattern (re-implemented or copied after review, with attribution in the commit). **Adapt** means take the idea with named changes. **Reject** means do not bring it into Procurement.

## Identity and authorisation
| Pattern | Source | Verdict | Reason / required change |
|---|---|---|---|
| Statamic file users as application users on a shared `web` guard | SV `config/auth.php`, `config/statamic/users.php` | **Reject** | Core allows one user (`CountUsers.php`); app users need an independent Eloquent provider |
| Independent Eloquent app guard plus one Statamic user (ADR 0004) | SD | **Adapt** | Keep the concept but use Statamic's documented layout (`web` = app, `statamic` = CP) instead of SD's inverted `app` guard (D-03) |
| `ApplicationPasswordBrokerManager` | SD `app/Auth/` | **Adapt only if needed** | Needed in SD because Statamic owned the default guard. Port its regression test (uniform reset response) regardless |
| `StatamicSingleUserConstraintTest` | SD `tests/Feature/` | **Reuse** | Enforces the Core single-admin boundary in CI |
| Fortify headless login/reset with `authenticateUsing` rejecting inactive users | SD | **Reuse** | JSON responses for the SPA; Fortify registration off |
| Login and registration throttles | SD `FortifyServiceProvider.php` | **Reuse** | 5/min per email+IP; burst plus daily registration limits |
| Hashed invitation tokens with expiry | SD `invitations` | **Reuse + fix** | Add a throttle on accept routes (missing in SD); single-use in a transaction |
| Custom registration (honeypot, fill time, duplicate detection, consent) | SD ADR 0005 | **Adapt** | Champion-only fields per the approved form |
| Six-role model, org-scoped roles, reviewer and viewer roles | SD `Enums/RoleKey.php` | **Reject** | Procurement has exactly Champion, Analyst and Super Admin |
| `accessibleOrganizationIds()` plus `OrganizationScope` as the single tenant filter | SD | **Adapt** | A single scoping point for Champion access; staff get the submitted-only eligibility service |
| Hashed coordinator bearer tokens | SV | **Reject for app auth** | Procurement uses session plus CSRF; no bearer tokens |
| Plaintext assignment tokens, `?access_token=` query tokens, local-env bypass, `auth:sanctum` without Sanctum, public test routes | SV | **Reject** | Fail-open defects |
| Statamic 4 Pro, Sanctum 3, committed env and user files | ST | **Reject** | Superseded; licence and secret-hygiene violations |
| Session settings: DB driver, secure and encrypted cookie, `lax`, custom cookie name, `SESSION_DOMAIN=null` (host-only) | SD `compose.prod.yaml`, `.env.production.example` | **Reuse** | Matches same-origin host-only requirement |
| Edge overwrites `X-Forwarded-For`, then `TRUSTED_PROXIES` | SD `infra/docker/edge` | **Adapt** | Trust only the inspected proxy network CIDR rather than `*` where feasible |

## Domain and data
| Pattern | Source | Verdict | Reason / required change |
|---|---|---|---|
| `HasOptimisticLock` (conditional UPDATE → stale exception → 409) | SD | **Reuse** | Implements `expectedVersion` on draft saves |
| `BelongsToRevision` model immutability guard | SD | **Adapt** | Apply to submitted revisions, answers and snapshots; add tests; consider DB grants |
| Submit transaction → snapshot + checksum → notify after commit | SD `SubmitAssessmentRevision.php` | **Reuse + extend** | Add idempotency receipts and a transactional outbox, which SD lacks |
| Append-only `audit_events` | SD | **Reuse + harden** | Revoke UPDATE/DELETE for the runtime DB user (SD never did) |
| Unique business key per organisation and period; unique revision number | SD | **Adapt** | `(organization_id, cycle_id)`; generated-column unique key for one open draft (SD has none) |
| Restrict-on-delete FKs, soft-deleted users and organisations | SD | **Reuse** | Submissions survive account deactivation |
| Cascade deletes, CP hard-delete of templates and instances | SV | **Reject** | Conflicts with immutable submissions |
| Live scoring from mutable weights | SV `SectionScoringService.php` | **Reject** | Scores are frozen in snapshots and pinned to a framework version |
| Workbook import: parse → preview/diff → commit, checksum, parser registry | SV `WorkbookCommitService`, `TemplateDiffService` | **Adapt** | Import creates a new **draft** framework version and never upserts a published one; validates 4/40/64 and source cells |
| Drafts separate from final answers | SV | **Adapt** | Procurement uses revision rows (draft → submitted) rather than a separate drafts table; same invariant |
| Credentials, sign-off and reset tokens in a `meta` JSON column | SV | **Reject** | Use real columns and constraints |
| 14-state reviewer workflow, comments, approval-recommended, accreditation, emission factors, grid factors | SD | **Reject (Sustainability-only)** | Explicitly out of scope (`MASTER.md §2`) |
| Queued report job with `generated_reports` checksum metadata | SD | **Adapt** | For XLSX/CSV exports (`export_requests`, `private_artifacts`); no server PDF initially (D-14) |
| Maatwebsite Excel with a formula-injection guard | SV `BindsFormulaTextAsString` | **Reuse** | Use maatwebsite/excel 4.x with PhpSpreadsheet 5.x, not SV's locked 3.1 / PhpSpreadsheet 1.x |
| Queued exports with signed URL, TTL and checksum | SV | **Adapt** | Download also re-checks current permission (a signed URL alone is insufficient); add a scheduler for cleanup |
| Browsershot/Chromium or dompdf PDF | SD | **Reject for first release** | Browser print is the approved report; avoids LGPL and Chromium weight |
| Mail doctor, preflight commands, `sent_at` stamped after send | SV | **Reuse** | Paired with an outbox table |

## Frontend
| Pattern | Source | Verdict | Reason |
|---|---|---|---|
| Vue 3 / Inertia 3 portal, Blade console | SD | **Reject** | Keep the approved React SPA |
| Blade/Antlers survey UI | SV | **Reject** | Same |
| Vite 8 / Tailwind 4 versions | SV, SD | n/a | The SPA keeps its own declared stack |

## Containers, local environment and CI
| Pattern | Source | Verdict | Reason / required change |
|---|---|---|---|
| Digest-pinned multi-stage Dockerfile, nginx+fpm under supervisord, non-root, entrypoint roles, `config:cache` at start | SD `Dockerfile`, `infra/docker/entrypoint.sh` | **Reuse** | Avoids SV's unpinned `composer:latest` and persisted cache |
| `bootstrap/cache` as a volume | SV | **Reject** | Caused stale-cache 404s |
| Dev compose with MySQL 8.4 and Mailpit; per-service bootstrap volumes | SD `compose.yaml` | **Adapt** | Drop Redis and fake-GCS; do not publish the DB port (SD publishes 3307, SV publishes 3306 on all interfaces); add a scheduler |
| mkcert nginx HTTPS overlay, `*.test` hostname | SD `compose.https.yaml`, SV `docker-compose.https.yml` | **Reuse** | `procurement.taleed.test`; host changes need approval |
| MySQL test suite on a dedicated DB (`phpunit.mysql.xml`) | SD | **Reuse** | SD's own comment records that an earlier suite wiped the dev DB |
| SQLite-only tests, no CI | SV | **Reject** | |
| CI guardrails (destructive-command script, shellcheck), MySQL service job, PHPStan, Pint, Trivy, `compose config` | SD `.github/workflows/ci.yml` | **Adapt** | Swap the Vue job for the React `npm ci && npm run check` plus Playwright; drop Terraform |
| Cloud Run Terraform, `deploy.yml`, staging docs | SD | **Reject** | Dormant, and staging is excluded |

## Production operations
| Pattern | Source | Verdict | Reason / required change |
|---|---|---|---|
| Only the edge publishes 80/443; DB-backed session, cache and queue | SD `compose.prod.yaml` | **Reuse** | |
| No scheduler service in production | SV, SD | **Reject** | Procurement runs exactly one scheduler |
| Named volumes on the boot disk, no mount or marker check | SV, SD | **Reject** | Dedicated data disk plus fail-closed preflight (`04-OPERATIONS.md`) |
| `render-prod-conf.sh` refusing HTTPS without certificates | SV | **Reuse** | Fail closed |
| Let's Encrypt **IP** certificate + certbot renewal | SV, SD | **Adopt (director decision D-41)** | Production is served on a reserved static IP over HTTPS; renewal automation and an expiry alert are mandatory gates |
| Same DB user for migrate and runtime | SD | **Reject** | Separate migration credentials injected only into the one-shot `migrate` role |
| `deploy-local` → `deploy-remote`: CI-green gate, `git archive` + sha256, `flock` release lock, preflight, backup before migrate, migrate once, switch, `/health` reports the release, auto-rollback only without migrations, no tag reuse | SD `scripts/prod/*` | **Reuse (pattern)** | Re-implemented for Procurement names and paths; no identifiers copied |
| SD `--dry-run` that uploads the bundle and writes settings remotely | SD `deploy-local.sh:224-227` | **Reject** | The offline plan must be non-mutating |
| SV `--dry-run` that skips upload and remote run | SV `deploy-local.sh` | **Reuse** | Correct offline semantics |
| Building images on the VM from an uploaded source bundle | SV, SD | **Adapt** | Acceptable without a registry, but prefer building once from a locked commit and shipping the image, so production runs what was tested |
| `rsync --delete` with a preserved-path list | SV | **Reject** | Code lives only in the image; no directory sync over state |
| Typed-phrase confirmations; forbidden-command guard in the compose wrapper | SV, SD `lib.sh` | **Reuse** | Plus a CI test of the guard behaviour |
| Image-only rollback that keeps the previous tag, with dry-run | SV `rollback-remote.sh` | **Reuse** | Schema-compatible rollbacks only |
| Backup: `--single-transaction` dump, volume manifest, SHA256SUMS, off-site copy without `.env` | SD `backup-remote.sh` | **Reuse** | SV lacks `--single-transaction` and off-host copies and stores `.env` in backups; reject those parts |
| Restore into a scratch or new schema, never over live | SD `restore-backup.sh` | **Reuse + extend** | Plus an isolated-host drill with outbound mail and jobs blocked |
| SV restore that wipes volumes without checksum verification | SV | **Reject** | |
| `doctor.sh` read-only preflight | SV | **Adapt** | Add hard thresholds: mount identity, marker, free space, `APP_KEY` present, pending migrations |
| Additive-only migrations; rehearse on a restored dump | SV runbooks | **Adapt** | The rehearsal happens on an isolated restore target, never a persistent staging environment |
| Prompt, handoff and review workflow | SD starter | **Already mirrored** | This package follows it; Sustainability-specific branding excluded |

## Items to flag to the owner (outside Procurement scope)
- SV `README.md` and SV operations docs contain plaintext credential-like values and production identifiers.
- ST tracks env files, user YAML files and SQLite state.
- SD tracked docs contain GCP project, VM and service-account names.

None of these values were copied here. Rotation or cleanup of those repositories is the owner's call.
