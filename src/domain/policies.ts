import type { Assessment, Cycle, Database, Organization, User } from './types';

export const isStaff = (user: User): boolean => user.role !== 'champion';
export const canManage = (user: User): boolean => user.role === 'admin';
export const canExport = (user: User): boolean => isStaff(user) && (canManage(user) || user.canExport);
export function canReadAssessment(user: User, assessment: Assessment): boolean {
  return user.active && user.verified && (user.role === 'champion'
    ? user.orgId === assessment.orgId
    : assessment.status === 'submitted');
}
export function requireUser(db: Database, actorId: string | null, verified = true): User {
  const user = actorId ? db.users[actorId] : undefined;
  if (!user?.active) throw new Error('Sign in with an active demo account to continue.');
  if (verified && !user.verified) throw new Error('Complete the simulated email verification first.');
  return user;
}
export function requireManager(user: User): void {
  if (!canManage(user)) throw new Error('Super Admin access is required.');
}
export function requireEditable(db: Database, user: User, assessmentId: string, now: string): Assessment {
  const assessment = db.assessments[assessmentId];
  if (!assessment || user.role !== 'champion' || user.orgId !== assessment.orgId) throw new Error('This assessment is not available for editing.');
  if (assessment.status !== 'draft') throw new Error('Submitted revisions cannot be edited.');
  if (!db.organizations[assessment.orgId]?.active) throw new Error('Your organization is paused. Contact the program team.');
  const cycle = db.cycles[assessment.cycleId];
  if (!cycle || !isCycleOpen(cycle, now)) throw new Error('This assessment cycle is closed.');
  return assessment;
}
export function isCycleOpen(cycle: Cycle, now: string): boolean {
  const date = now.slice(0, 10);
  return cycle.status === 'open' && date >= cycle.opensAt && date <= cycle.closesAt;
}
export function validProfile(org: Organization | undefined): boolean {
  return !!org && org.name.trim().length >= 2 && !!org.country && !!org.size && org.authorityConfirmed;
}
/** One effective result per org/cycle. A correction draft never replaces a final result. */
export function effectiveSubmissions(db: Database, cycleId?: string): Assessment[] {
  const selected = new Map<string, Assessment>();
  Object.values(db.assessments).filter((a) => a.status === 'submitted' && (!cycleId || a.cycleId === cycleId)).forEach((a) => {
    const key = `${a.orgId}/${a.cycleId}`;
    const current = selected.get(key);
    if (!current || current.revision < a.revision) selected.set(key, a);
  });
  return [...selected.values()];
}
