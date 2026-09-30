# Local and production operating contract

## Two environments, one production authority
There is local development and production, with no persistent staging/UAT service. Temporary CI test databases and an explicitly authorized isolated restore exercise are validation facilities, not a third public environment. Production business records are authoritative for real users. Local records are synthetic and never promoted into production.

The database is allowed to receive legitimate application writes and reviewed forward-compatible migrations. 'Do not change the production database' means **do not replace, reseed, reset or overwrite its operational history during a code release**, not that schema evolution is impossible.

## Local Docker environment
Proposed URL: `https://procurement.taleed.test`. Add an explicit loopback hosts entry and a locally trusted development certificate. Use Caddy as a local HTTPS reverse proxy with mkcert-issued certificates, or Caddy's internal CA with a documented host trust step. Never disable browser certificate verification. Explain and request approval before changing the machine's hosts file or trust store. Do not commit certificates, CA private keys or development secrets. [S8]

Provide `compose.dev.yaml` with web/edge, PHP app, MySQL 8.4, worker, scheduler, Vite/HMR and Mailpit. Proxy HMR securely on the same development origin. Use synthetic seed data only through an explicit local command. Support Apple Silicon and Linux/amd64 by checking the actual images; do not assume the production VM has the workstation's CPU architecture.

Use a unique Compose project per repository/worktree, e.g. `taleed-procurement-dev-<suffix>`, and unique database/cache/session prefixes. Keep MySQL unpublished by default. Any optional inspection port is loopback-only and development-only. Mailpit is not a production mail provider. A second agent must not share the first agent's database, host ports or mutable CMS directory.

Required local commands, names finalized by the implementation:
`dev up`, `dev down` (without deleting volumes), `dev logs`, `dev test`, `dev seed-demo` (explicit local-only), and `dev reset` guarded by local environment and project/database identity. Provide Windows/macOS/Linux HTTPS setup and troubleshooting. Never make `up` a destructive reset.

## Production: one GCE VM with a retained data disk
Use the owner's confirmed single Google Compute Engine VM and reserved static IP. Use an approved DNS hostname pointing to that IP with a publicly trusted HTTPS certificate. The hostname in a prompt is not an issued certificate or a working link.

If Survey and Sustainability share this VM, retain its existing validated Nginx/Certbot or other TLS edge. Do not launch a second service binding the same public ports 80/443. Add a separately reviewed hostname/upstream. Use distinct container names, application networks, MySQL database/users, runtime secrets and durable directories for Procurement. Prefer a dedicated Procurement MySQL service on the same VM for lifecycle isolation if capacity permits; otherwise a separate database/user on an explicitly approved existing MySQL service is a recorded trade-off. Do not upgrade another application's database or share its schema during this project.

A shared edge may connect to the Procurement web upstream network; it does not need access to the database network. Only the edge is public. Restrict administration through the approved IAP/OS Login/VPN approach. Verify machine memory, CPU, disk IOPS/free space and backup duration before adding services. No claim of high availability: one VM is a single point of failure.

The production Compose includes only runtime services. Build immutable, locked application images; use the same release for PHP app, queue and scheduler. Static assets must correspond to that release. No source-code bind mounts, Node development server, Mailpit, debugger, demo seeding or public DB/cache ports. Limit resources and log growth. Use non-root processes where supported, health checks, private artifact paths and one effective scheduler. Distinguish liveness from readiness; never mark the application ready against an empty replacement database.

## Durable data is not application code
| State | Required handling |
|---|---|
| MySQL files | Dedicated Procurement path on retained **non-boot Persistent Disk**, referenced by independently provisioned external volume(s) |
| Private reports and durable uploads | Separate retained private directory/object prefix with authorization; not a public web path |
| Statamic content and single CMS user's YAML | Inventory actual writable paths and persist them outside image layers |
| Asset files and metadata, mutable CMS navigation/globals/settings | Persist and back up every runtime-editable source path, including hidden metadata; `storage/` alone is insufficient |
| APP_KEY, mail/DB credentials and any encryption/MFA key dependencies | Stable per environment, restricted runtime secret/config store, recoverable separately; never generated at boot |
| Compiled frontend, public vendor assets, vendor packages, PHP code and read-only blueprints | Release artifacts, not durable content volumes; do not hide new code behind old mounts |
| Cache | Rebuildable, never used as the only source of saved assessments |

Docker volumes survive container replacement but live on a host filesystem unless explicitly backed elsewhere; that alone is not disaster recovery. Set disk auto-delete/lifecycle protection appropriately, keep code and data in different paths, and maintain off-VM backups. [S9–S10]

Explicitly decide which Statamic configuration/blueprint editing is allowed in production. Treat developer-managed structure as immutable release code; allow only inventoried content editing. Test that this policy is compatible with the actual Core control panel. Do not persist the whole `backend/`, `resources/` or `vendor/` tree simply to make writes work.

## Fail-closed storage preflight
Before starting MySQL or releasing code, validate the expected mountpoint, block-device/filesystem identity, ownership, free space and a previously provisioned environment marker. Validate externally managed volume names and their device bindings. On a non-first release, also validate the existing MySQL initialization and application/database identity marker.

A wrong/missing disk, missing external volume or unexpected empty data directory must stop execution. Never create a fallback folder on the boot disk, initialize a fresh live database, format a disk, run an automatic restore or recursively change ownership to force startup. Disk provisioning/first initialization is an explicit one-time operator action; routine releases cannot invoke it. Use a service dependency/preflight that runs after host reboot as well as on deployment.

The metadata registry must distinguish Procurement from other Taleed applications even when the same host operator has access to all of them. Do not rely only on a guessed Compose project name to identify the database.

## Backup and restore
Take an application-consistent MySQL-native backup before schema-changing releases and on an approved schedule. For an all-InnoDB database, `mysqldump --single-transaction` is a normal logical-backup approach, but no concurrent DDL may invalidate its consistency; verify privileges and routines/events/trigger coverage against the actual schema and MySQL version. Never tar a running MySQL data directory and call it a consistent database backup. [S11]

Include a manifest with database identity, schema migration state, app image digest/commit, source framework hashes, file/object manifest/checksums, timestamps and encryption-key recovery dependencies. Coordinate mutable CMS/files with the database backup; use a brief, scoped maintenance/write pause when necessary. Do not claim independent snapshots taken at different times form one consistent application restore point. Persistent Disk snapshots complement database-native backups and require application-consistency planning. [S10]

Encrypt and restrict off-VM backup copies, for example in a private approved Cloud Storage bucket. Off-VM storage is not another application environment. Use least-privilege workload identity rather than committed service-account JSON. Retention/lifecycle, deletion permissions and alerting must be configured and verified.

Provisional planning targets: daily backup and a pre-release backup, **RPO up to 24 hours**, **RTO target 4 hours**, **30 days backup retention**. These are proposals requiring client/operator approval and a measured restore rehearsal, not an achieved SLA. More frequent backups or binlog-based recovery require a documented operational design. Data retention for personal information is a separate policy.

A verified restore means loading into a **fresh isolated target**, using an authorized secure recovery location, checking integrity and actually exercising login, submission snapshots, source versions, reports and decryption. Block outbound mail/jobs, public ingress and production credentials in the restored environment. Never point a restore test at the live database or copy production records into routine developer environments. Production-backup access itself requires authorization. Keep only sanitized evidence in Git.

## Controlled update pipeline
Suggested default: image build/test on protected commits, with an explicit production deployment approval. After initial operational acceptance, the owner may enable automatic deployment of passing protected-main commits using exactly the same gates. Do not use Watchtower or floating-image auto-updates to change a live application or MySQL version without tests and migration review.

```text
validated commit and lockfiles
  -> tests, security checks and immutable image build
  -> approved production identity and release lock
  -> disk/database preflight and capacity checks
  -> verified backup + coordinated write/worker handling
  -> reviewed forward-compatible migrations once
  -> matching app/web/worker/scheduler release
  -> HTTPS, authentication, reporting and persistence smoke checks
  -> release manifest and monitoring
```

Use an application-scoped server lock plus CI deployment concurrency controls. Acquire migration privileges only in the migration task. No production shell `composer update` or `npm install`. Keep the old image until new readiness is verified. Plan a short maintenance window where one-VM capacity or incompatible changes prevent safe overlap; do not promise zero downtime.

Schema changes use expand/contract. Old and new code must tolerate the transitional schema where rolling updates are used. Image rollback is allowed only when schema/content are compatible; otherwise use a tested roll-forward. Do not automatically run down migrations or restore yesterday's database: that can discard newer submissions. A real disaster restore requires separate authorization.

Retain necessary old hashed assets briefly so an already-open SPA tab does not break after release, and define a graceful version-refresh path. Restart/drain workers correctly; jobs must be compatible across the planned deployment boundary. Long-running migrations and jobs must not fail merely because the operator's SSH connection drops.

## Operational deliverables to implement
Separate offline `plan` from authorized connected `preflight`. An offline plan makes no remote writes, uploads, backups or settings changes. Implement app-scoped `deploy`, `backup`, `restore-isolated`, `verify-persistence` and an explicit one-time provisioning runbook. Production deployment and test-run commands must not include runtime/demo seeders.

Add CI checks for resolved production Compose configuration, unsafe script operations, ignored secrets and source-material exclusion. Validate using safe placeholder configuration and redact logs. Scanning documentation for quoted forbidden commands is not a meaningful safety test; inspect executable operational paths and test the guard behavior.

Required final handover includes exact verified URL, DNS/TLS evidence, deployed commit and image digest, DB/schema/disk identity references (no credentials), backup ID, restore-test evidence, rollback limits, access ownership, alerts, secret recovery process and a second non-destructive release proving data persistence.

## External values requiring confirmation
Google Cloud project/zone/VM, static IP ownership, deployment user/identity, current edge proxy, DNS owner/hostname, retained-disk resource and mount, volume names, separate database identity, registry, backup bucket and retention, mail provider/domain authorization, source-content approval reference, initial real admin identity, capacity and recovery targets. Discovery can proceed without these; deployment cannot guess them.

References: `SOURCES.md`.
