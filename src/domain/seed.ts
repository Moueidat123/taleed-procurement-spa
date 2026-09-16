import source from '../data/framework.json';
import type { Assessment, Database, Framework, Organization, User } from './types';
import { assertFramework, validateDatabase } from './validation';
import { answersFromCounts, emptyAnswers, scoreAssessment } from './scoring';

assertFramework(source);
export const SOURCE_FRAMEWORK: Framework = source;
export const DEMO_DATE = '2026-09-15';
export const DEMO_PASSWORD = 'TaleedDemo!2026';
/** Fixed date keeps the presentation repeatable; the time component still distinguishes saves. */
export const demoNow = (): string => `${DEMO_DATE}T${new Date().toISOString().slice(11)}`;
export const DEMO_ACCOUNTS = [
  { id: 'user-sahara', label: 'Company Champion', detail: 'Sahara Industrial Solutions · 12/40 answers' },
  { id: 'user-namaa', label: 'Second Company', detail: 'Namaa Logistics · submitted assessment' },
  { id: 'user-analyst', label: 'Taleed Analyst', detail: 'Portfolio and submitted results · no export access' },
  { id: 'user-admin', label: 'Super Admin', detail: 'All staff features, audit and demo data controls' },
];
export function seedDatabase(instanceId = 'taleed-demo-initial'): Database {
  const now = '2026-09-15T08:00:00.000Z';
  const orgEntries = [
    ['sahara','Sahara Industrial Solutions','51–200','Layla Hassan'],
    ['namaa','Namaa Logistics','11–50','Noor Khalid'],
    ['desert','Desert Packaging','11–50','Sami Ahmed'],
    ['atlas','Atlas Energy Services','51–200','Reem Saad'],
    ['horizon','Horizon Technology','1–10','Omar Saleh'],
  ];
  const organizations: Record<string, Organization> = {};
  const users: Record<string, User> = {};
  orgEntries.forEach(([slug = '', name = '', size = '', person = '']) => {
    organizations[`org-${slug}`] = { id: `org-${slug}`, name, country: 'Saudi Arabia', size,
      registrationId: `DEMO-${slug.toUpperCase()}`, authorityConfirmed: true, active: true };
    users[`user-${slug}`] = { id: `user-${slug}`, name: person, email: `${slug}@example.com`,
      jobTitle: 'Head of Procurement', role: 'champion', orgId: `org-${slug}`, verified: true, active: true, canExport: false };
  });
  (['analyst','admin'] as const).forEach((role) => {
    users[`user-${role}`] = { id: `user-${role}`, name: { analyst: 'Dana Analyst', admin: 'Amina Administrator' }[role],
      email: `${role}@example.com`, jobTitle: 'Taleed program team', role, orgId: null, verified: true, active: true, canExport: false };
  });
  const db: Database = {
    schemaVersion: 1, instanceId, revision: 0, savedAt: now, users, organizations,
    frameworks: { '1.0.0': { version: '1.0.0', status: 'published', content: structuredClone(SOURCE_FRAMEWORK),
      approvalReference: 'DEMO-ONLY: simulated publication, not formal approval', publishedAt: now } },
    cycles: {
      '2026': { id: '2026', label: 'Procurement Assessment', opensAt: '2026-01-01', closesAt: '2026-12-31', frameworkVersion: '1.0.0', status: 'open' },
    }, assessments: {}, audit: [],
  };
  const add = (slug: string, cycleId: string, counts: number[], draft = false): void => {
    const id = `assessment-${slug}-${cycleId}`;
    const org = organizations[`org-${slug}`]; const user = users[`user-${slug}`];
    if (!org || !user) throw new Error('Invalid seed definition.');
    const answers = draft ? emptyAnswers(SOURCE_FRAMEWORK) : answersFromCounts(SOURCE_FRAMEWORK, counts);
    if (draft) Object.keys(answers).slice(0, 12).forEach((question, i) => { answers[question] = i % 3 === 0 ? 'no' : 'yes'; });
    const submittedAt = `${cycleId}-09-10T09:00:00.000Z`;
    const assessment: Assessment = { id, orgId: org.id, cycleId, frameworkVersion: '1.0.0', revision: 1,
      status: draft ? 'draft' : 'submitted', answers, createdAt: `${cycleId}-09-01T09:00:00.000Z`,
      updatedAt: draft ? now : submittedAt, submittedAt: draft ? null : submittedAt,
      submittedBy: draft ? null : user.id, supersedesId: null, correctionReason: '',
      snapshot: draft ? null : { framework: structuredClone(SOURCE_FRAMEWORK), organization: structuredClone(org),
        respondent: { name: user.name, email: user.email, jobTitle: user.jobTitle }, answers: structuredClone(answers),
        result: scoreAssessment(answers, SOURCE_FRAMEWORK), submittedAt },
    };
    db.assessments[id] = assessment;
  };
  add('sahara','2026',[0,0,0,0],true);
  add('namaa','2026',[6,4,8,6]); add('desert','2026',[2,3,4,3]);
  add('atlas','2026',[8,8,8,9]); add('horizon','2026',[6,7,8,7]);
  db.audit.push({ id: 'demo-seed', at: now, actorId: 'user-admin', action: 'seed', targetId: 'dataset', orgId: null,
    detail: 'Synthetic demo organizations loaded. No real client submissions are included.' });
  return validateDatabase(db);
}
