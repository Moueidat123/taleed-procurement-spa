import type { Database, Framework, Organization } from './types';
import { assertAnswers, BANDS, scoreAssessment } from './scoring';

function fail(message: string): never { throw new Error(message); }
function object(value: unknown): asserts value is Record<string, unknown> {
  if (!value || typeof value !== 'object' || Array.isArray(value)) fail('Expected a data object.');
}
function string(value: unknown, max = 1000): asserts value is string {
  if (typeof value !== 'string' || value.length > max) fail('A text field has an invalid type or length.');
}
function bool(value: unknown): void { if (typeof value !== 'boolean') fail('Invalid boolean value.'); }
function integer(value: unknown, minimum = 0): void {
  if (!Number.isSafeInteger(value) || (value as number) < minimum) fail('Invalid integer value.');
}
function keys(value: Record<string, unknown>, allowed: string[]): void {
  if (Object.keys(value).some((key) => !allowed.includes(key)) || allowed.some((key) => !(key in value))) fail('Unexpected or missing data fields.');
}
export function isDate(value: string): boolean {
  return /^\d{4}-\d{2}-\d{2}$/.test(value) && !Number.isNaN(Date.parse(value)) && new Date(value).toISOString().slice(0, 10) === value;
}
export function isEmail(value: string): boolean {
  return value.length <= 254 && /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value);
}
function iso(value: unknown): void {
  string(value, 40);
  if (!/^\d{4}-\d{2}-\d{2}T/.test(value) || Number.isNaN(Date.parse(value))) fail('Invalid timestamp.');
}
export function assertProfile(value: unknown): asserts value is Organization {
  object(value);
  keys(value, ['id','name','country','sector','size','registrationId','authorityConfirmed','active']);
  for (const field of ['id','name','country','sector','size','registrationId']) string(value[field], 160);
  bool(value.authorityConfirmed); bool(value.active);
  if ((value.name as string).trim().length < 2 || !value.country || !value.sector || !value.size || !value.authorityConfirmed) fail('Complete the company profile and authority declaration.');
}
export function assertFramework(value: unknown): asserts value is Framework {
  object(value);
  keys(value, ['version','title','attribution','sourceFile','sourceSha256','sourceStatus','domains','interpretations']);
  for (const field of ['version','title','attribution','sourceFile','sourceSha256','sourceStatus']) string(value[field]);
  if (!/^\d+\.\d+\.\d+$/.test(value.version as string)) fail('Invalid framework version.');
  const names = ['category_management','spend_analysis','strategic_sourcing','supplier_relationship_management'];
  if (!Array.isArray(value.domains) || value.domains.length !== 4) fail('A framework must have four domains.');
  const seen = new Set<string>();
  (value.domains as unknown[]).forEach((domain, index) => {
    object(domain); keys(domain, ['key','name','order','questions','recommendations']);
    string(domain.key); string(domain.name);
    if (domain.key !== names[index] || domain.order !== index + 1) fail('Preserve domain keys and source order.');
    if (!Array.isArray(domain.questions) || domain.questions.length !== 10) fail('Each domain needs ten questions.');
    (domain.questions as unknown[]).forEach((question, i) => {
      object(question); keys(question, ['id','text','sourceCell']);
      string(question.id); string(question.text, 2000); string(question.sourceCell);
      if (question.id !== `${index + 1}.${i + 1}` || seen.has(question.id)) fail('Invalid or duplicate string question ID.');
      seen.add(question.id);
    });
    object(domain.recommendations); keys(domain.recommendations, BANDS);
    for (const band of BANDS) {
      const actions = domain.recommendations[band];
      if (!Array.isArray(actions) || actions.length !== 4) fail('Each domain/band must contain four actions.');
      (actions as unknown[]).forEach((action, i) => {
        object(action); keys(action, ['id','text','sourceCell']);
        string(action.id); string(action.text, 2000); string(action.sourceCell);
        if (action.id !== `${domain.key}.${band}.${i+1}`) fail('Invalid recommendation ID.');
      });
    }
  });
  object(value.interpretations); keys(value.interpretations, BANDS);
  for (const band of BANDS) string(value.interpretations[band], 3000);
}
/** Reject prototype pollution keys even when introduced into a JSON backup manually. */
function safeTree(value: unknown, depth = 0): void {
  if (depth > 30) fail('The backup is nested too deeply.');
  if (value && typeof value === 'object') {
    for (const [key, child] of Object.entries(value)) {
      if (['__proto__','constructor','prototype'].includes(key)) fail('Unsafe object key in backup.');
      safeTree(child, depth + 1);
    }
  }
}
export function validateDatabase(input: unknown): Database {
  safeTree(input); object(input);
  keys(input, ['schemaVersion','instanceId','revision','savedAt','users','organizations','frameworks','cycles','assessments','audit']);
  if (input.schemaVersion !== 1) fail('Unsupported storage version. Export the existing data before upgrading or resetting.');
  string(input.instanceId, 100); integer(input.revision); iso(input.savedAt);
  for (const table of ['users','organizations','frameworks','cycles','assessments']) object(input[table]);
  const db = input as unknown as Database;
  if (Object.keys(db.users).length > 200 || Object.keys(db.organizations).length > 100 || Object.keys(db.assessments).length > 150) fail('This local demonstration has reached its record limit.');
  for (const [id, org] of Object.entries(db.organizations)) { assertProfile(org); if (id !== org.id) fail('Organization key mismatch.'); }
  const emails = new Set<string>();
  for (const [id, user] of Object.entries(db.users)) {
    object(user); keys(user, ['id','name','email','jobTitle','role','orgId','verified','active','canExport']);
    for (const field of ['id','name','email','jobTitle','role']) string(user[field], 254);
    if (id !== user.id || !isEmail(user.email) || emails.has(user.email.toLowerCase())) fail('Invalid or duplicate user.');
    emails.add(user.email.toLowerCase());
    if (!['champion','analyst','admin'].includes(user.role)) fail('Unknown role.');
    if (user.orgId !== null && !db.organizations[user.orgId]) fail('Unknown user organization.');
    if (user.role !== 'champion' && user.orgId !== null) fail('Staff users cannot have a company membership.');
    bool(user.verified); bool(user.active); bool(user.canExport);
  }
  if (!Object.values(db.users).some((u) => u.role === 'admin' && u.active)) fail('Keep at least one active Super Admin.');
  for (const [version, record] of Object.entries(db.frameworks)) {
    object(record); keys(record, ['version','status','content','approvalReference','publishedAt']);
    assertFramework(record.content);
    if (record.version !== version || record.content.version !== version) fail('Framework key mismatch.');
    if (!['draft','published','retired'].includes(record.status)) fail('Invalid publication state.');
    string(record.approvalReference);
    if (record.publishedAt !== null) iso(record.publishedAt);
    if (record.status === 'published' && (!record.publishedAt || record.approvalReference.length < 8)) fail('Published framework metadata is incomplete.');
  }
  for (const [id, cycle] of Object.entries(db.cycles)) {
    object(cycle); keys(cycle, ['id','label','opensAt','closesAt','frameworkVersion','status']);
    for (const field of ['id','label','opensAt','closesAt','frameworkVersion','status']) string(cycle[field], 160);
    if (id !== cycle.id || !isDate(cycle.opensAt) || !isDate(cycle.closesAt) || cycle.opensAt > cycle.closesAt) fail('Invalid assessment cycle.');
    if (!db.frameworks[cycle.frameworkVersion] || !['open','closed'].includes(cycle.status)) fail('Invalid cycle reference.');
    if (cycle.status === 'open' && db.frameworks[cycle.frameworkVersion]?.status !== 'published') fail('An open cycle needs a published framework.');
  }
  const revisions = new Set<string>(); const drafts = new Set<string>();
  for (const [id, assessment] of Object.entries(db.assessments)) {
    object(assessment); keys(assessment, ['id','orgId','cycleId','frameworkVersion','revision','status','answers','createdAt','updatedAt','submittedAt','submittedBy','supersedesId','correctionReason','snapshot']);
    for (const field of ['id','orgId','cycleId','frameworkVersion','status','correctionReason']) string(assessment[field], 2000);
    integer(assessment.revision, 1); iso(assessment.createdAt); iso(assessment.updatedAt);
    const framework = db.frameworks[assessment.frameworkVersion]?.content;
    if (id !== assessment.id || !db.organizations[assessment.orgId] || !db.cycles[assessment.cycleId] || !framework) fail('Invalid assessment reference.');
    if (db.cycles[assessment.cycleId]?.frameworkVersion !== assessment.frameworkVersion) fail('Assessment/cycle framework mismatch.');
    if (!['draft','submitted'].includes(assessment.status)) fail('Unknown assessment status.');
    assertAnswers(assessment.answers, framework);
    const key = `${assessment.orgId}/${assessment.cycleId}`;
    if (revisions.has(`${key}/${assessment.revision}`)) fail('Duplicate assessment revision.');
    revisions.add(`${key}/${assessment.revision}`);
    if (assessment.supersedesId !== null) {
      const prior = db.assessments[assessment.supersedesId];
      if (!prior || prior.status !== 'submitted' || prior.orgId !== assessment.orgId || prior.cycleId !== assessment.cycleId || prior.revision + 1 !== assessment.revision) fail('Invalid correction chain.');
    } else if (assessment.revision !== 1) fail('A correction requires its previous revision.');
    if (assessment.status === 'draft') {
      if (drafts.has(key)) fail('Only one active draft is permitted per company/cycle.'); drafts.add(key);
      if (assessment.snapshot !== null || assessment.submittedAt !== null || assessment.submittedBy !== null) fail('Drafts cannot contain final results.');
    } else {
      assertAnswers(assessment.answers, framework, true); iso(assessment.submittedAt);
      if (!assessment.submittedBy || !db.users[assessment.submittedBy]) fail('Unknown submitting user.');
      const snapshot = assessment.snapshot; object(snapshot);
      keys(snapshot, ['framework','organization','respondent','answers','result','submittedAt']);
      assertFramework(snapshot.framework); assertProfile(snapshot.organization); object(snapshot.respondent);
      keys(snapshot.respondent, ['name','email','jobTitle']);
      for (const value of Object.values(snapshot.respondent)) string(value, 254);
      if (snapshot.framework.version !== assessment.frameworkVersion || snapshot.organization.id !== assessment.orgId || snapshot.submittedAt !== assessment.submittedAt) fail('Snapshot metadata mismatch.');
      if (JSON.stringify(snapshot.answers) !== JSON.stringify(assessment.answers)) fail('Snapshot answer mismatch.');
      const result = scoreAssessment(assessment.answers, snapshot.framework);
      if (JSON.stringify(result) !== JSON.stringify(snapshot.result)) fail('Stored result does not match its immutable inputs.');
    }
  }
  if (!Array.isArray(db.audit) || db.audit.length > 500) fail('Invalid audit log.');
  for (const event of db.audit) {
    object(event); keys(event, ['id','at','actorId','action','targetId','orgId','detail']);
    for (const field of ['id','actorId','action','targetId','detail']) string(event[field], 2000);
    iso(event.at); if (event.orgId !== null) string(event.orgId, 160);
  }
  return structuredClone(db);
}
export const MAX_BACKUP_BYTES = 3_500_000;
export function parseDatabase(raw: string): Database {
  if (raw.length > MAX_BACKUP_BYTES) fail('Backup exceeds the 3.5 MB prototype limit.');
  try { return validateDatabase(JSON.parse(raw) as unknown); }
  catch (error) { throw new Error(`Cannot load this local dataset: ${error instanceof Error ? error.message : 'Invalid JSON.'}`); }
}
