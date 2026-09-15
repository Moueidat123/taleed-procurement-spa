export type Answer = 'yes' | 'no' | null;
export type Answers = Record<string, Answer>;
export type Band = 'foundational' | 'developing' | 'advanced' | 'best_in_class';
export type Role = 'champion' | 'analyst' | 'manager' | 'admin';
export interface Question { id: string; text: string; sourceCell: string }
export interface Action { id: string; text: string; sourceCell: string }
export interface Domain {
  key: string; name: string; order: number; questions: Question[];
  recommendations: Record<Band, Action[]>;
}
export interface Framework {
  version: string; title: string; attribution: string; sourceFile: string;
  sourceSha256: string; sourceStatus: string; domains: Domain[];
  interpretations: Record<Band, string>;
}
export interface FrameworkRecord {
  version: string; status: 'draft' | 'published' | 'retired'; content: Framework;
  approvalReference: string; publishedAt: string | null;
}
export interface User {
  id: string; name: string; email: string; jobTitle: string; role: Role;
  orgId: string | null; verified: boolean; active: boolean; canExport: boolean;
}
export interface Organization {
  id: string; name: string; country: string; sector: string; size: string;
  registrationId: string; authorityConfirmed: boolean; active: boolean;
}
export interface Cycle {
  id: string; label: string; opensAt: string; closesAt: string;
  frameworkVersion: string; status: 'open' | 'closed';
}
export interface DomainResult {
  key: string; name: string; order: number; yes: number; score: number;
  band: Band; actions: Action[];
}
export interface Result {
  yes: number; overall: number; band: Band; domains: DomainResult[];
  priorities: string[]; hasTie: boolean; interpretation: string;
}
export interface Snapshot {
  framework: Framework; organization: Organization;
  respondent: { name: string; email: string; jobTitle: string };
  answers: Answers; result: Result; submittedAt: string;
}
export interface Assessment {
  id: string; orgId: string; cycleId: string; frameworkVersion: string;
  revision: number; status: 'draft' | 'submitted'; answers: Answers;
  createdAt: string; updatedAt: string; submittedAt: string | null;
  submittedBy: string | null; supersedesId: string | null; correctionReason: string;
  snapshot: Snapshot | null;
}
export interface AuditEvent {
  id: string; at: string; actorId: string; action: string;
  targetId: string; orgId: string | null; detail: string;
}
/** Persisted whitelist. Never add session tokens, passwords, files or JSX here. */
export interface Database {
  schemaVersion: 1; instanceId: string; revision: number; savedAt: string;
  users: Record<string, User>; organizations: Record<string, Organization>;
  frameworks: Record<string, FrameworkRecord>; cycles: Record<string, Cycle>;
  assessments: Record<string, Assessment>; audit: AuditEvent[];
}
export interface CommandContext { id: string; now: string; actorId: string | null }
export type Command =
  | { type: 'register'; name: string; email: string; jobTitle: string; consent: boolean }
  | { type: 'verify'; code: string }
  | { type: 'saveProfile'; profile: Omit<Organization, 'id' | 'active'> }
  | { type: 'startAssessment'; cycleId: string }
  | { type: 'answer'; assessmentId: string; questionId: string; value: Answer }
  | { type: 'submit'; assessmentId: string; declaration: boolean }
  | { type: 'openCorrection'; assessmentId: string; reason: string }
  | { type: 'saveCycle'; cycle: Cycle }
  | { type: 'cloneFramework'; sourceVersion: string; version: string }
  | { type: 'updateDraftContent'; version: string; contentType: 'question' | 'recommendation'; targetId: string; text: string }
  | { type: 'publishFramework'; version: string; approvalReference: string }
  | { type: 'setUserAccess'; userId: string; active: boolean; canExport: boolean }
  | { type: 'createStaff'; name: string; email: string; role: Exclude<Role, 'champion'> }
  | { type: 'setOrganizationActive'; orgId: string; active: boolean }
  | { type: 'recordExport'; format: 'csv' | 'xlsx'; count: number };
