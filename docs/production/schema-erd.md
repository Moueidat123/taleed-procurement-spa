# Phase 2B database diagram

Generated from `docs/production/schema.sql` (structure only). Identity and security tables are Phase 2A.

```mermaid
erDiagram
  ORGANIZATIONS ||--o{ APP_USERS : "champions"
  FRAMEWORK_VERSIONS ||--o{ FRAMEWORK_DOMAINS : has
  FRAMEWORK_DOMAINS ||--o{ FRAMEWORK_QUESTIONS : has
  FRAMEWORK_DOMAINS ||--o{ RECOMMENDATION_ACTIONS : "4 per band"
  FRAMEWORK_VERSIONS ||--o{ ASSESSMENT_CYCLES : "pinned by"
  ORGANIZATIONS ||--o{ ASSESSMENTS : "one per cycle"
  ASSESSMENT_CYCLES ||--o{ ASSESSMENTS : contains
  ASSESSMENTS ||--o{ ASSESSMENT_REVISIONS : "numbered; one open draft"
  ASSESSMENTS |o--o| ASSESSMENT_REVISIONS : "current_submission_id"
  ASSESSMENT_REVISIONS |o--o{ ASSESSMENT_REVISIONS : "parent (correction)"
  ASSESSMENT_REVISIONS ||--o{ ASSESSMENT_ANSWERS : "40 rows; NULL = unanswered"
  FRAMEWORK_QUESTIONS ||--o{ ASSESSMENT_ANSWERS : "same framework version"
  ASSESSMENT_REVISIONS ||--o| SUBMISSION_SNAPSHOTS : "immutable + sha256"
  SUBMISSION_SNAPSHOTS ||--|{ SUBMISSION_DOMAIN_RESULTS : "4 per submission"
  APP_USERS ||--o{ IDEMPOTENCY_REQUESTS : "submit receipts"
  ASSESSMENTS ||--o{ OUTBOX_EVENTS : "assessment.submitted"
```

Database-enforced rules: one assessment per company per cycle (`uq_assessment_org_cycle`); one open draft per assessment (`uq_one_open_draft`, generated column); one open cycle (`uq_one_open_cycle`); unique revision numbers; composite foreign keys keep revisions, answers and results on the assessment's framework version; the current-submission pointer must reference a revision of the same assessment.
