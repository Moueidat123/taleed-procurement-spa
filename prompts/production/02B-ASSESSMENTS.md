# Phase 2B — assessment engine, scoring and data integrity

Prerequisite: Phase 2A reviewed and merged; director approval to start 2B. Read `AGENTS.md`, `STATUS.md`, `docs/production/PLAN-CONTRACT.md`, the master, `03-DATABASE-SCHEMA.md`, the API contract and the handoff. Work locally only. Escalate uncovered questions to the director.

Implement, per PLAN-CONTRACT §2 "Phase 2B":
- Versioned framework tables and importer:
  - validates the workbook/JSON hash, 4 domains × 10 questions, 64 actions, source IDs as strings, and source cells;
  - import creates a draft version;
  - `procurement:framework:publish` requires `--approval-ref`;
  - published versions are immutable.
- Cycles:
  - one open annual cycle at a time, pinned to a framework, business timezone `Asia/Riyadh`;
  - audited `procurement:cycle:create`.
- Assessment data:
  - assessment aggregate, revisions (generated-column unique key for one open draft), answers (null ≠ No);
  - immutable submission snapshots with checksum;
  - domain results, idempotency receipts, transactional outbox.
- Endpoints:
  - create or resume a draft;
  - save answers with `expectedVersion` (409 on conflict);
  - atomic, idempotent submit with the declaration (`Idempotency-Key`);
  - result and history.
- Corrections: Super Admin only, with a reason of at least 10 characters. They are **allowed after cycle close** and audited. The prior submission stays effective until the correction commits.
- Staff read models:
  - effective submitted revisions only;
  - draft `stage` and `answeredCount` only, never answer values;
  - designated test organisations excluded.
- Scoring: an exact PHP port of `src/domain/scoring.ts`, covering:
  - all 14,641 oracle cases (`docs/production/reference/scoring-count-oracle.json`);
  - band boundaries (40, 65, 80 and just above each);
  - four actions per domain band;
  - stable focus-area ties;
  - all-100 behaviour.

Tests on real MySQL:
- concurrent draft creation;
- save/submit races;
- duplicate submit and retry;
- correction races;
- tampered IDs.

Also run one full Champion journey and one correction over real HTTP sessions.

Export `docs/production/schema.sql` from the tested clean database, with an ER diagram. Update the API contract, evidence under `docs/production/evidence/phase-2b/`, `STATUS.md` and `HANDOFF.md`. Open a pull request from `implementation/phase-2b`. Stop for independent review.
