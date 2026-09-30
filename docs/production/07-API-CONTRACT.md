# Proposed business API contract

This is a design baseline for Phase 0/1, not an API already implemented. Produce a versioned OpenAPI document and generated/checked TypeScript types before splitting backend and frontend implementation. All paths below are on the SPA's HTTPS origin and use Laravel application-session/CSRF handling, not the Statamic CP identity. The business prefix is `/api/procurement/v1`.

## Endpoints and access
| Method / relative route | Purpose | Authorization |
|---|---|---|
| `POST /auth/register` | Create unverified Champion from allowed fields | Public, throttled, no role/org/verification fields accepted |
| `POST /auth/login` | Real credentials, then MFA when required | Public, throttled, session regenerated |
| `POST /auth/logout` | Invalidate application session | Application session + CSRF |
| `GET /auth/me` | Minimal identity/permissions, not all users | Application session |
| `POST /auth/verification/send` | Send real verification challenge | Unverified application identity, throttled |
| `POST /auth/verification/confirm` | Validate expiring challenge | Same user, attempt limit, single-use |
| `POST /auth/forgot-password` | Generic acknowledgement, real eligible-user email | Public, throttled |
| `POST /auth/reset-password` | Framework broker token validation/reset | Public token, throttled |
| `POST /auth/invitations/accept` | Validate single-use staff invitation | Invitation token + allowed account data |
| `GET/PATCH /organization` | Current Champion's company | Active verified Champion, own tenant |
| `GET /cycles/available` | Eligible pinned cycles | Active verified application user, scoped fields |
| `GET /frameworks/{version}` | Published assessment definition | Authorized cycle participant or staff; no draft publication metadata |
| `POST /assessments` | Create/resume assessment for allowed cycle | Champion, own company, profile/declaration/cycle rules |
| `GET /assessments/{revisionId}` | Authorized draft or submitted revision | Own Champion; staff submitted-only |
| `PATCH /assessments/{revisionId}/answers` | Save/clear answers with expected version | Own Champion, draft, current cycle/state |
| `POST /assessments/{revisionId}/submit` | Atomic final submission | Own Champion, complete draft, declaration, idempotency |
| `GET /assessments/{revisionId}/result` | Immutable submitted result | Own Champion or authorized staff |
| `GET /assessments/history` | Own organization's revisions | Champion; register before variable revision route |
| `POST /staff/assessments/{revisionId}/corrections` | Authorize next correction draft | Application Super Admin only |
| `GET /staff/portfolio` | Filtered aggregate and denominator | Analyst or application Super Admin |
| `GET /staff/organizations` | Paginated company participation metadata | Analyst or application Super Admin |
| `GET /staff/organizations/{id}` | Company details and permitted submissions | Analyst or application Super Admin; no draft answers |
| `POST /staff/comparisons` | Compare 2–4 compatible effective submissions | Analyst or application Super Admin, server eligibility query |
| `POST /staff/exports` | Authorized CSV/XLSX request | Admin or Analyst with export permission |
| `GET /staff/exports/{id}` | Own authorized export status | Requester/current permission or explicit admin policy |
| `GET /artifacts/{id}/download` | Stream private report/export | Current user, tenant/export permission and artifact validity |
| `GET /staff/users` | Minimal paginated access-management list | Application Super Admin |
| `POST /staff/invitations` | Issue staff invitation | Application Super Admin |
| `PATCH /staff/users/{id}/access` | Active/export changes | Application Super Admin; self/last-admin safeguards |
| `PATCH /staff/organizations/{id}/access` | Enable/pause company | Application Super Admin, audited |

Implement MFA endpoints through the selected maintained authentication facility and record their real contract; do not invent custom cryptography. The CSRF initialization route may be Sanctum's standard `/sanctum/csrf-cookie`; reserve it before the SPA fallback. Core `/cp` routes use their own guard and never map to `/staff/*`.

Framework publication, cycle administration and initial-account operations are audited commands in the baseline. A later approved management interface can wrap domain services without altering authority or allowing editing published content.

## Request / response rules
Use a consistent `{data, meta}` JSON resource envelope. Paginated resources include links and counts appropriate to the request, not unfiltered private totals. Error objects contain stable codes, a safe message, field errors when relevant and a non-secret request correlation ID. Do not return exception traces, tokens, private config or full model attributes.

Use 401 for missing/expired application identity; 403 for an authenticated forbidden action; a consistent non-enumerating 404 for inaccessible tenant objects where appropriate; 409 for stale revision/state/idempotency conflicts; 422 for input validation; 429 for rate limiting with retry information. Handle framework CSRF/session-expiry responses explicitly in the SPA. Invalid bearer tokens in this cookie-only application must not cause an unhandled database error or authenticate an unintended provider.

Example draft mutation:
```json
{
  "expectedVersion": 7,
  "answers": { "1.1": "yes", "1.10": null }
}
```
The server validates each provided key against the pinned framework, preserves other answers, commits and returns the new version plus canonical changed answers. Null clears an answer. Client-supplied percentages, roles, organization IDs or submission timestamps are rejected/ignored by explicit allowlists, never mass-assigned.

Example submit:
```text
POST /api/procurement/v1/assessments/<REVISION_ID>/submit
Idempotency-Key: <CLIENT_GENERATED_UNIQUE_REQUEST_KEY>
Content-Type: application/json

{"expectedVersion":8,"declaration":true}
```
The server reads its saved answers, recomputes and freezes results. A retry with the same key/body returns the same receipt/result. A reused key with different input is rejected. Do not submit while an earlier autosave is unacknowledged.

For exports, the server resolves the filter set to authorized effective revision IDs and stores that request membership. An untrusted browser list of selected results cannot grant access. Deliver status/progress/errors without leaking other users' exports. File downloads should use no-store/private caching as appropriate; never public CDN caching of authenticated responses.

## Compatibility and tests
Keep JSON field naming consistent with the preserved TypeScript interfaces or implement explicit adapters; do not let camelCase/snake_case drift silently break forms. Publish enum values and nullability, time/date conventions, filter semantics and pagination limits. IDs remain strings. Use resource allowlists and response-schema tests.

Document a concurrency/version-refresh policy before parallel work. Tests must prove unknown API routes return JSON rather than the SPA HTML shell, history routes are not captured as IDs, CSRF is active and app/CMS sessions are not interchangeable.
