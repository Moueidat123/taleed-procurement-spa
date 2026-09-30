# Local development (Docker, HTTPS)

Local only. Nothing here touches production. Verified on macOS (Apple Silicon), Docker 28.3, on 30 September 2026. Other platforms have not been verified yet.

## Prerequisites
- **Docker**, with Compose v2.
- **mkcert**, used only to *issue* a certificate from a project-local CA. The system trust store is not changed.
- **Node 22** and **openssl**, for the host-side browser checks.
- **Host PHP/Composer are optional.** Every backend command runs inside the containers.

## Quickstart
```bash
ops/local/dev init        # once: random local secrets, certificate, images, composer install, migrations
ops/local/dev up          # start: SPA with Vite HMR behind https://procurement.taleed.test
ops/local/dev trust-help  # how to reach the URL from your normal browser (host changes need approval)
```

Useful commands:

| Command | What it does |
|---|---|
| `ops/local/dev ps` / `logs [service]` | Status / follow logs |
| `ops/local/dev test` | Backend PHPUnit on MySQL (`procurement_test` schema only) |
| `ops/local/dev migrate` | Migrations using the separate **migrator** account |
| `ops/local/dev artisan <cmd>` | Artisan inside the app container |
| `ops/local/dev cms-admin <email>` | Create THE single Statamic CMS administrator (prompts for password) |
| `ops/local/dev seed-demo` | Synthetic champion/analyst/admin users (`*@example.test`; passwords printed once) |
| `ops/local/dev up --built` | Serve the compiled SPA through Laravel (production-like) |
| `ops/local/dev build-spa` | Compile the SPA into `backend/public/spa` |
| `ops/local/dev down` | Stop. **Volumes are always kept** (`down -v` is refused) |
| `ops/local/dev reset` | Delete this project's local volumes; requires typing the project name |
| `ops/local/checks/https-smoke.sh` | HTTPS / routing / CSRF / port smoke test (no host changes) |

### Browser checks against the running stack
```bash
ops/local/dev artisan procurement:dev:create-app-user e2e-analyst@example.test --role=analyst --password='<pw>'
E2E_ANALYST_PASSWORD='<pw>' E2E_CMS_PASSWORD='<cms pw>' npx playwright test -c playwright.local.config.ts
```
Chromium maps the hostname to 127.0.0.1 and trusts only the local certificate's key (SPKI pin). No hosts or trust changes are needed.

The original prototype checks still run unchanged: `npm run check` and `npm run test:e2e`.

## What runs

| Service | Role | Exposed on host |
|---|---|---|
| `web` | nginx HTTPS edge: `/api`, `/sanctum`, `/cp`, `/pages`, `/up` → PHP-FPM; everything else → SPA | 127.0.0.1:443 / :80 |
| `app` | PHP 8.4 FPM, Laravel 13 + Statamic 6 Core (source bind-mounted from `backend/`) | no |
| `queue` | `queue:work database` | no |
| `scheduler` | `schedule:work` (exactly one) | no |
| `mysql` | MySQL 8.4 (data in volume `<project>_mysql-data`) | **never published** |
| `vite` | Vite dev server + HMR over WSS through the edge (profile `spa-dev`) | no |
| `mailpit` | Local mail catcher UI | 127.0.0.1:8025 |

**Database accounts** (created on the first MySQL initialisation from `infra/local/mysql-init/`):

| Account | Privileges |
|---|---|
| `procurement_app` (runtime) | SELECT/INSERT/UPDATE/DELETE only |
| `procurement_migrator` | Schema changes |
| `procurement_test` | `procurement_test` schema only |

**Local secrets** are kept in gitignored files:
- `infra/local/.env` (mode 600) and `backend/.env` hold the generated passwords and the local `APP_KEY`.
- `infra/local/ca/` and `infra/local/certs/` hold the local CA and certificate.

## Parallel worktrees / second agent
Give each worktree its own `infra/local/.env` with a unique `COMPOSE_PROJECT_NAME`, which must contain "procurement", plus unique `PROCUREMENT_HTTPS_PORT`, `PROCUREMENT_HTTP_PORT`, `PROCUREMENT_MAILPIT_PORT` and `PROCUREMENT_HOSTNAME`. Projects never share volumes, databases or certificates. Only one stack can own 127.0.0.1:443.

## npm toolchain
- `package-lock.json` is generated with **npm 10.9.9**, the npm bundled with the pinned Node 22 image and used by CI.
- It installs with `npm ci` under npm 10 and npm 11.
- If `npm ci` ever reports the lock "out of sync", regenerate it inside the pinned node image, not with a different npm. `@hookform/resolvers` is pinned to 5.2.2 because 5.5+ declares an optional `ajv@8` peer that makes npm write a lock its own `npm ci` rejects.

## Troubleshooting
| Symptom | Cause / fix |
|---|---|
| `infra/local/.env missing` | Run `ops/local/dev init` |
| `MissingAppKeyException` during composer | `backend/.env` has no key: delete `backend/.env`, re-run `init` (local only) |
| `Table 'procurement.cache' doesn't exist` | Migrations have not run yet: `ops/local/dev migrate` |
| Vite container restarting | `docker logs <project>-vite-1`; usually a lockfile mismatch (see npm toolchain) or a transient npm registry 404 — restart it |
| Port 443/80 in use | Set `PROCUREMENT_HTTPS_PORT`/`PROCUREMENT_HTTP_PORT` in `infra/local/.env`; the origin becomes `https://procurement.taleed.test:<port>` |
| Browser shows certificate warning | Expected until the CA is trusted: see `ops/local/dev trust-help` (owner approval) |
| `/cp` login returns 409 once | Statamic's Inertia asset-version handshake; the client reloads automatically |
| Same-origin POST without XSRF header succeeds | Intended Laravel 13 behaviour (`Sec-Fetch-Site: same-origin`); cross-site / same-site / header-less requests still require the token (smoke test) |
| Tests refuse to run | Guard: the DB name must end in `_test` and the Statamic users dir must be under `storage/framework/testing` |
