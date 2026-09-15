import type { Assessment, Command, CommandContext, Database, Organization } from './types';
import { assertProfile, isDate, isEmail } from './validation';
import { emptyAnswers, questionIds, scoreAssessment } from './scoring';
import { canExport, effectiveSubmissions, isCycleOpen, requireEditable, requireManager, requireUser, validProfile } from './policies';

const requireText = (value: string, minimum: number, maximum = 160): string => {
  const text = value.trim();
  if (text.length < minimum || text.length > maximum) throw new Error(`Enter between ${minimum} and ${maximum} characters.`);
  return text;
};
function newDraft(db: Database, orgId: string, cycleId: string, context: CommandContext, prior?: Assessment, reason = ''): Assessment {
  const cycle = db.cycles[cycleId];
  if (!cycle || !isCycleOpen(cycle, context.now)) throw new Error('This assessment cycle is not open.');
  const framework = db.frameworks[cycle.frameworkVersion];
  if (framework?.status !== 'published') throw new Error('A published framework is required.');
  return {
    id: context.id, orgId, cycleId, frameworkVersion: cycle.frameworkVersion,
    revision: (prior?.revision ?? 0) + 1, status: 'draft',
    answers: prior ? structuredClone(prior.answers) : emptyAnswers(framework.content),
    createdAt: context.now, updatedAt: context.now, submittedAt: null, submittedBy: null,
    supersedesId: prior?.id ?? null, correctionReason: reason, snapshot: null,
  };
}
/** All mutations are pure, policy-checked commands. IDs and time are injected by the caller. */
export function applyCommand(previous: Database, command: Command, context: CommandContext): Database {
  const db = structuredClone(previous);
  let targetId = context.id;
  let orgId: string | null = null;
  let detail = '';
  if (command.type === 'register') {
    const email = command.email.trim().toLowerCase();
    if (!command.consent) throw new Error('Accept the prototype privacy notice.');
    if (!isEmail(email)) throw new Error('Enter a valid work email.');
    if (Object.values(db.users).some((user) => user.email.toLowerCase() === email)) throw new Error('This email is already registered in this browser. Sign in instead.');
    db.users[context.id] = { id: context.id, name: requireText(command.name, 2), email,
      jobTitle: requireText(command.jobTitle, 2), role: 'champion', orgId: null,
      verified: false, active: true, canExport: false };
    detail = 'Local demo account created. No password stored; no email sent.';
  } else {
    const user = requireUser(db, context.actorId, command.type !== 'verify');
    orgId = user.orgId;
    switch (command.type) {
      case 'verify':
        if (command.code !== '123456') throw new Error('For this demonstration, enter 123456.');
        db.users[user.id] = { ...user, verified: true }; targetId = user.id;
        detail = 'Email verification simulated; no external identity verification occurred.';
        break;
      case 'saveProfile': {
        if (user.role !== 'champion') throw new Error('Company Champion access is required.');
        const id = user.orgId ?? context.id;
        const profile: Organization = { ...command.profile, id, active: db.organizations[id]?.active ?? true };
        profile.name = requireText(profile.name, 2);
        profile.registrationId = profile.registrationId.trim();
        assertProfile(profile);
        const duplicate = Object.values(db.organizations).some((org) => org.id !== id &&
          (org.name.trim().toLowerCase() === profile.name.toLowerCase() ||
          (!!profile.registrationId && org.registrationId.toLowerCase() === profile.registrationId.toLowerCase())));
        if (duplicate) throw new Error('This company is already registered in this browser. Contact the program team rather than creating a duplicate.');
        db.organizations[id] = profile; db.users[user.id] = { ...user, orgId: id };
        orgId = id; targetId = id; detail = 'Company profile saved; historical snapshots are unchanged.';
        break;
      }
      case 'startAssessment': {
        if (user.role !== 'champion' || !user.orgId || !validProfile(db.organizations[user.orgId])) throw new Error('Complete your company profile before starting.');
        if (!db.organizations[user.orgId]?.active) throw new Error('Your company is paused.');
        if (Object.values(db.assessments).some((a) => a.orgId === user.orgId && a.cycleId === command.cycleId)) throw new Error('An assessment already exists for this cycle. Resume it, or request a controlled correction.');
        db.assessments[context.id] = newDraft(db, user.orgId, command.cycleId, context);
        detail = 'New assessment draft created.';
        break;
      }
      case 'answer': {
        const assessment = requireEditable(db, user, command.assessmentId, context.now);
        const framework = db.frameworks[assessment.frameworkVersion]?.content;
        if (!framework || !questionIds(framework).includes(command.questionId)) throw new Error('Unknown question for this framework.');
        if (command.value !== null && command.value !== 'yes' && command.value !== 'no') throw new Error('Select Yes or No.');
        assessment.answers[command.questionId] = command.value; assessment.updatedAt = context.now;
        targetId = assessment.id; detail = `Response ${command.questionId} saved (answer content omitted from log).`;
        break;
      }
      case 'submit': {
        // Idempotent retries return the already committed revision, but never reveal another org.
        const existing = db.assessments[command.assessmentId];
        if (existing?.status === 'submitted' && user.role === 'champion' && existing.orgId === user.orgId) return previous;
        const assessment = requireEditable(db, user, command.assessmentId, context.now);
        const organization = db.organizations[assessment.orgId];
        if (!validProfile(organization) || !organization) throw new Error('Complete your company profile.');
        if (!command.declaration) throw new Error('Confirm the accuracy declaration before submitting.');
        const framework = db.frameworks[assessment.frameworkVersion]?.content;
        if (!framework) throw new Error('The pinned framework is unavailable.');
        const result = scoreAssessment(assessment.answers, framework);
        assessment.status = 'submitted'; assessment.submittedAt = context.now;
        assessment.updatedAt = context.now; assessment.submittedBy = user.id;
        assessment.snapshot = { framework: structuredClone(framework), organization: structuredClone(organization),
          respondent: { name: user.name, email: user.email, jobTitle: user.jobTitle },
          answers: structuredClone(assessment.answers), result, submittedAt: context.now };
        targetId = assessment.id; detail = `Revision ${assessment.revision} submitted. Immediate self-assessment results available.`;
        break;
      }
      case 'openCorrection': {
        requireManager(user);
        const previousAssessment = db.assessments[command.assessmentId];
        if (!previousAssessment || previousAssessment.status !== 'submitted') throw new Error('Select a submitted assessment.');
        const effective = effectiveSubmissions(db, previousAssessment.cycleId).find((a) => a.orgId === previousAssessment.orgId);
        if (effective?.id !== previousAssessment.id) throw new Error('Open a correction from the effective latest revision only.');
        if (Object.values(db.assessments).some((a) => a.orgId === previousAssessment.orgId && a.cycleId === previousAssessment.cycleId && a.status === 'draft')) throw new Error('A correction draft is already open.');
        detail = requireText(command.reason, 10, 1000);
        db.assessments[context.id] = newDraft(db, previousAssessment.orgId, previousAssessment.cycleId, context, previousAssessment, detail);
        orgId = previousAssessment.orgId;
        break;
      }
      case 'saveCycle': {
        requireManager(user);
        const cycle = { ...command.cycle, label: requireText(command.cycle.label, 2) };
        if (!/^[a-zA-Z0-9_-]{1,80}$/.test(cycle.id)) throw new Error('Cycle ID must contain only letters, digits, dashes or underscores.');
        if (!isDate(cycle.opensAt) || !isDate(cycle.closesAt) || cycle.opensAt > cycle.closesAt) throw new Error('Enter valid opening and closing dates in order.');
        if (db.frameworks[cycle.frameworkVersion]?.status !== 'published') throw new Error('Select a published framework.');
        const current = db.cycles[cycle.id];
        if (current && current.frameworkVersion !== cycle.frameworkVersion && Object.values(db.assessments).some((a) => a.cycleId === cycle.id)) throw new Error('A cycle with assessments cannot change its framework. Create a new cycle.');
        if (!['open','closed'].includes(cycle.status)) throw new Error('Invalid cycle status.');
        db.cycles[cycle.id] = cycle; targetId = cycle.id; detail = `Cycle ${cycle.label}: ${cycle.status}.`;
        break;
      }
      case 'cloneFramework': {
        requireManager(user);
        if (!/^\d+\.\d+\.\d+$/.test(command.version) || db.frameworks[command.version]) throw new Error('Enter a new, unused semantic version such as 1.1.0.');
        const source = db.frameworks[command.sourceVersion]; if (!source) throw new Error('Source framework not found.');
        db.frameworks[command.version] = { version: command.version, status: 'draft',
          content: { ...structuredClone(source.content), version: command.version }, approvalReference: '', publishedAt: null };
        targetId = command.version; detail = 'Draft copy prepared. Text can be edited before simulated publication; weights and IDs stay fixed.';
        break;
      }
      case 'updateDraftContent': {
        requireManager(user);
        const record = db.frameworks[command.version];
        if (!record || record.status !== 'draft') throw new Error('Only draft framework content can be edited. Published versions are locked.');
        const text = requireText(command.text, 10, 2000);
        const items = command.contentType === 'question'
          ? record.content.domains.flatMap((domain) => domain.questions)
          : command.contentType === 'recommendation'
            ? record.content.domains.flatMap((domain) => Object.values(domain.recommendations).flat())
            : [];
        const item = items.find((entry) => entry.id === command.targetId);
        if (!item) throw new Error('Content item not found.');
        item.text = text;
        record.content.sourceStatus = 'Edited demonstration draft derived from the source workbook; changes require formal content approval before real use.';
        targetId = command.targetId; detail = `Draft ${command.version} ${command.contentType} text updated. Source IDs, mappings and scoring remain fixed.`;
        break;
      }
      case 'publishFramework': {
        requireManager(user);
        const record = db.frameworks[command.version];
        if (!record || record.status !== 'draft') throw new Error('Only a draft framework can be published.');
        record.approvalReference = requireText(command.approvalReference, 8, 200);
        record.status = 'published'; record.publishedAt = context.now;
        targetId = record.version; detail = 'Local publication simulated; not an external client approval.';
        break;
      }
      case 'setUserAccess': {
        requireManager(user); const target = db.users[command.userId];
        if (!target) throw new Error('User not found.');
        if (target.id === user.id) throw new Error('You cannot alter your own access.');
        if (target.role === 'admin' && user.role !== 'admin') throw new Error('Only a Super Admin can change administrator access.');
        if (!command.active && target.role === 'admin' && Object.values(db.users).filter((u) => u.active && u.role === 'admin').length < 2) throw new Error('Keep at least one active Super Admin.');
        target.active = command.active; target.canExport = target.role === 'analyst' ? command.canExport : false;
        targetId = target.id; detail = 'Local demo account permissions updated.';
        break;
      }
      case 'createStaff': {
        requireManager(user);
        if (command.role === 'admin' && user.role !== 'admin') throw new Error('Only a Super Admin can create another administrator.');
        if (!['analyst','manager','admin'].includes(command.role)) throw new Error('Select a staff role.');
        const email = command.email.trim().toLowerCase();
        if (!isEmail(email) || Object.values(db.users).some((u) => u.email.toLowerCase() === email)) throw new Error('Use a valid, unique email.');
        db.users[context.id] = { id: context.id, name: requireText(command.name, 2), email, jobTitle: 'Taleed program team',
          role: command.role, orgId: null, verified: true, active: true, canExport: false };
        detail = 'Demo staff account created; no invitation email sent.';
        break;
      }
      case 'setOrganizationActive': {
        requireManager(user); const org = db.organizations[command.orgId];
        if (!org) throw new Error('Organization not found.');
        org.active = command.active; targetId = org.id; orgId = org.id;
        detail = command.active ? 'Organization resumed.' : 'Organization paused; historical results preserved.';
        break;
      }
      case 'recordExport':
        if (!canExport(user)) throw new Error('Your role does not have portfolio export permission.');
        if (!Number.isSafeInteger(command.count) || command.count < 0 || !['csv','xlsx'].includes(command.format)) throw new Error('Invalid export request.');
        targetId = 'portfolio'; detail = `${command.count} effective submissions exported as ${command.format.toUpperCase()}.`;
        break;
    }
  }
  db.revision += 1; db.savedAt = context.now;
  db.audit.unshift({ id: `${context.id}:event`, at: context.now, actorId: context.actorId ?? context.id,
    action: command.type, targetId, orgId, detail });
  db.audit = db.audit.slice(0, 500);
  return db;
}
