import { useEffect, useState } from 'react';
import { useForm } from 'react-hook-form';
import { zodResolver } from '@hookform/resolvers/zod';
import { z } from 'zod';
import { Link, Navigate, useNavigate } from 'react-router-dom';
import { useCurrentUser } from '../../app/hooks';
import { Alert, Badge, Button, Card, EmptyState, Field, Icon, LinkButton, Metric, PageHeading, Progress, formatDate } from '../../components/ui';
import { toApiError } from '../../infrastructure/api';
import type { FetchBaseQueryError } from '@reduxjs/toolkit/query';
import {
  useCyclesQuery, useFrameworkQuery, useHistoryQuery, useOrganizationQuery, useRevisionQuery,
  useSaveOrganizationMutation, useStartAssessmentMutation, type HistoryEntry,
} from '../../infrastructure/companyApi';

/** ISO 3166-1 alpha-2 codes; the backend stores the code, the UI shows the name. */
export const COUNTRIES: { code: string; name: string }[] = [
  { code: 'SA', name: 'Saudi Arabia' }, { code: 'AE', name: 'United Arab Emirates' }, { code: 'BH', name: 'Bahrain' },
  { code: 'KW', name: 'Kuwait' }, { code: 'OM', name: 'Oman' }, { code: 'QA', name: 'Qatar' }, { code: 'LB', name: 'Lebanon' },
];
export const SIZES = ['1–10', '11–50', '51–200', '201–500', '501+'];
export const countryName = (code: string): string => COUNTRIES.find((c) => c.code === code)?.name ?? code;

function Loading({ label }: { label: string }) { return <p className="muted" role="status">{label}</p>; }
function LoadError({ error, retry }: { error: unknown; retry: () => void }) {
  return <Alert kind="error" title="Could not load this page">{toApiError(error as FetchBaseQueryError).message} <Button variant="secondary" onClick={retry}>Try again</Button></Alert>;
}

const profileSchema = z.object({
  displayName: z.string().trim().min(2, 'Enter the legal company name.').max(200),
  countryCode: z.string().length(2, 'Choose a country.'),
  sizeBand: z.string().min(1, 'Choose a company size.'),
  registrationId: z.string().trim().max(120),
  authorityConfirmed: z.boolean().refine(Boolean, 'Confirm your authority to respond for this company.'),
});
type ProfileValues = z.infer<typeof profileSchema>;
const SERVER_FIELDS: (keyof ProfileValues)[] = ['displayName', 'countryCode', 'sizeBand', 'registrationId', 'authorityConfirmed'];

export function Profile() {
  const user = useCurrentUser();
  const { data: org, isLoading, error, refetch } = useOrganizationQuery();
  if (isLoading) return <Loading label="Loading company profile…" />;
  if (error) return <LoadError error={error} retry={refetch} />;
  return <ProfileForm key={org?.id ?? 'new'} org={org ?? null} userName={user.name} jobTitle={user.jobTitle} />;
}

function ProfileForm({ org, userName, jobTitle }: { org: import('../../infrastructure/companyApi').Organization | null; userName: string; jobTitle: string }) {
  const navigate = useNavigate(); const [saved, setSaved] = useState(false); const [formError, setFormError] = useState('');
  const [save, { isLoading: busy }] = useSaveOrganizationMutation();
  const { register, handleSubmit, reset, setError, formState: { errors, isDirty } } = useForm<ProfileValues>({
    resolver: zodResolver(profileSchema),
    defaultValues: { displayName: org?.displayName ?? '', countryCode: org?.countryCode ?? '', sizeBand: org?.sizeBand ?? '', registrationId: org?.registrationId ?? '', authorityConfirmed: org?.authorityConfirmed ?? false },
  });
  useEffect(() => { if (!isDirty) return; const warn = (e: BeforeUnloadEvent) => { e.preventDefault(); }; window.addEventListener('beforeunload', warn); return () => window.removeEventListener('beforeunload', warn); }, [isDirty]);

  const onSubmit = handleSubmit(async (values) => {
    setFormError(''); setSaved(false);
    try {
      await save({ ...values, registrationId: values.registrationId || null }).unwrap();
      reset(values); setSaved(true);
      if (!org) navigate('/app/dashboard');
    } catch (e) {
      // Authoritative backend errors: map field errors onto inputs, show the rest above the form.
      const err = toApiError(e as FetchBaseQueryError); let mapped = false;
      for (const f of SERVER_FIELDS) { const m = err.fields?.[f]?.[0]; if (m) { setError(f, { type: 'server', message: m }); mapped = true; } }
      if (!mapped || err.status !== 422) setFormError(err.message);
    }
  });

  return <><PageHeading eyebrow={org ? 'COMPANY DETAILS' : 'GETTING STARTED · STEP 2 OF 2'} title={org ? 'Company profile' : 'Tell us about your company'} description="Identify the organization behind this assessment." />
    <div className="content-with-aside"><Card><form noValidate onSubmit={onSubmit}><div className="card-heading"><h2>Organization details</h2><Badge tone="blue">Identified assessment</Badge></div>
      {formError && <Alert kind="error">{formError}</Alert>}
      <Field label="Legal company name *" htmlFor="company-name" error={errors.displayName?.message}><input id="company-name" autoComplete="organization" {...register('displayName')} /></Field>
      <div className="form-grid">
        <Field label="Country *" htmlFor="country" error={errors.countryCode?.message}><select id="country" {...register('countryCode')}><option value="">Select country</option>{COUNTRIES.map((c) => <option key={c.code} value={c.code}>{c.name}</option>)}</select></Field>
        <Field label="Company size *" htmlFor="size" error={errors.sizeBand?.message}><select id="size" {...register('sizeBand')}><option value="">Select employee range</option>{SIZES.map((v) => <option key={v}>{v}</option>)}</select></Field>
        <Field label="Registration reference (optional)" htmlFor="registration-id" error={errors.registrationId?.message}><input id="registration-id" {...register('registrationId')} /></Field>
      </div>
      <div className="divider" /><h3>Your authority to respond</h3><p className="muted">Respondent: <strong>{userName}</strong>{jobTitle && <> · {jobTitle}</>}</p>
      <label className="checkbox"><input type="checkbox" {...register('authorityConfirmed')} /><span>I am authorized to complete this self-assessment on behalf of the company and understand that results are self-reported.</span></label>
      {errors.authorityConfirmed && <span className="field-error">{errors.authorityConfirmed.message}</span>}
      <div className="form-footer"><Button type="submit" disabled={busy}>{busy ? 'Saving…' : org ? 'Save company profile' : 'Save and continue'}<Icon name="arrow" /></Button>{isDirty && <small className="muted">Unsaved profile changes</small>}</div>
      {saved && !isDirty && <Alert kind="success">Company profile saved. Earlier submitted reports retain their original company details.</Alert>}
    </form></Card>
    <aside className="stack"><Card><span className="icon-box"><Icon name="info" /></span><h3>Why we ask</h3><p>Company information connects your submission to the right organization and lets the Taleed team understand participation across the program.</p><p>Each company can be registered once. A duplicate name or registration reference in the same country is blocked.</p></Card></aside></div></>;
}

/** Latest draft in the open cycle, otherwise the latest revision in it. */
function currentEntry(history: HistoryEntry[], cycleId: string | undefined): HistoryEntry | undefined {
  const inCycle = history.filter((h) => h.cycleId === cycleId);
  return inCycle.find((h) => h.status === 'draft') ?? [...inCycle].sort((a, b) => b.revisionNumber - a.revisionNumber)[0];
}

export function Dashboard() {
  const user = useCurrentUser(); const navigate = useNavigate();
  const orgQ = useOrganizationQuery(); const cyclesQ = useCyclesQuery(); const historyQ = useHistoryQuery();
  const cycle = cyclesQ.data?.[0];
  const entry = historyQ.data ? currentEntry(historyQ.data, cycle?.id) : undefined;
  const revQ = useRevisionQuery(entry?.id ?? '', { skip: !entry });
  const fwQ = useFrameworkQuery(revQ.data?.frameworkVersion ?? cycle?.frameworkVersion ?? '', { skip: !revQ.data && !cycle });
  const [start, { isLoading: starting }] = useStartAssessmentMutation(); const [startError, setStartError] = useState('');

  if (orgQ.isLoading || cyclesQ.isLoading || historyQ.isLoading) return <Loading label="Loading your workspace…" />;
  if (!orgQ.error && !orgQ.data) return <Navigate to="/app/profile" replace />;
  const failed = orgQ.error ?? cyclesQ.error ?? historyQ.error;
  if (failed) return <LoadError error={failed} retry={() => { void orgQ.refetch(); void cyclesQ.refetch(); void historyQ.refetch(); }} />;
  const org = orgQ.data;
  if (!org) return <Navigate to="/app/profile" replace />;

  const current = revQ.data; const framework = fwQ.data;
  const total = framework ? framework.domains.reduce((n, d) => n + d.questions.length, 0) : 40;
  const answered = current?.answeredCount ?? 0;
  const submitted = current?.status === 'submitted';
  const resumeSection = framework && current ? framework.domains.findIndex((d) => d.questions.some((q) => current.answers[q.id] == null)) : 0;
  const resume = current ? `/app/assessment/${current.id}/${Math.max(0, resumeSection) + 1}` : '';
  const previous = [...(historyQ.data ?? [])].filter((h) => h.status === 'submitted' && h.cycleId !== cycle?.id).sort((a, b) => (b.submittedAt ?? '').localeCompare(a.submittedAt ?? ''))[0];
  const open = !!cycle && org.active;

  const onStart = async () => {
    setStartError('');
    try { const r = await start().unwrap(); navigate(`/app/assessment/${r.id}/1`); }
    catch (e) { setStartError(toApiError(e as FetchBaseQueryError).message); }
  };

  return <><PageHeading eyebrow="YOUR COMPANY WORKSPACE" title={`Welcome, ${user.name.split(' ')[0]}.`} description="A clear view of your procurement capabilities starts with an honest assessment." />
    {!org.active && <Alert kind="warning" title="Your organization is paused">Existing reports remain available. New answers and submissions are paused until the program team reactivates the organization.</Alert>}
    <div className="metrics-grid three">
      <Metric label="Assessment status" value={submitted ? 'Submitted' : current ? 'In progress' : 'Not started'} foot={cycle?.title ?? 'Procurement Assessment'} icon="file" />
      <Metric label="Questions answered" value={<>{answered}<span className="metric-suffix"> / {total}</span></>} foot="Yes and No both count toward completion" icon="check" />
      <Metric label="Previous submission" value={previous ? formatDate(previous.submittedAt) : '—'} foot={previous ? 'Earlier cycle · see history' : 'Your first result starts a historical record'} icon="chart" />
    </div>
    <Card className="workspace-hero"><div>
      <Badge tone={submitted ? 'success' : 'blue'}>{submitted ? 'ASSESSMENT COMPLETE' : current?.isCorrection ? 'CORRECTION DRAFT' : 'YOUR NEXT STEP'}</Badge>
      <h2>{submitted ? 'Your results are ready.' : current ? 'Pick up where you left off.' : 'Begin your procurement assessment.'}</h2>
      <p>{submitted ? 'Explore your maturity result, compare your four domains and review the selected recommendations.' : 'Answer based on practices consistently in place today. You can move between sections and return later.'}</p>
      {current?.correctionReason && <Alert kind="warning" title={`Correction revision ${current.revisionNumber}`}>{current.correctionReason} The earlier submission remains effective until you submit this revision.</Alert>}
      {startError && <Alert kind="error">{startError}</Alert>}
      <div className="workspace-actions">{submitted ? <LinkButton to={`/app/results/${current.id}`}>View results <Icon name="arrow" /></LinkButton>
        : current ? <><LinkButton to={resume}>Continue assessment <Icon name="arrow" /></LinkButton><LinkButton variant="secondary" to={`/app/assessment/${current.id}/review`}>Review answers</LinkButton></>
        : <Button disabled={starting || !open || !org.authorityConfirmed} onClick={() => { void onStart(); }}>{starting ? 'Starting…' : 'Start assessment'} <Icon name="arrow" /></Button>}</div>
      {!open && <p className="muted">No assessment cycle is open or your company is paused. Saved results remain available.</p>}
      {open && !current && !org.authorityConfirmed && <p className="muted">Confirm your authority in the <Link to="/app/profile">company profile</Link> before starting.</p>}
      {cycle && <p className="muted">Open until {formatDate(cycle.closesAt)} ({cycle.timezone}).</p>}
    </div>
    <div className="completion-panel"><span className="large-percentage">{Math.round(answered / total * 100)}<small>%</small></span><span>completed</span><Progress value={answered} total={total} /><p>{answered} of {total} questions answered</p><small>This is completion, not a maturity score.</small></div></Card>
    {framework && <><div className="section-title"><h2>Your four capability areas</h2><span>{framework.domains[0]?.questions.length ?? 10} questions per section</span></div>
      <div className="domain-card-grid">{framework.domains.map((domain, i) => {
        const count = domain.questions.filter((q) => current?.answers[q.id] === 'yes' || current?.answers[q.id] === 'no').length; const n = domain.questions.length;
        return <Card key={domain.key} className="domain-work-card"><div className="row-between"><span className="domain-number">0{i + 1}</span><Badge tone={count === n ? 'success' : count ? 'blue' : 'neutral'}>{count === n ? 'Complete' : count ? 'In progress' : 'Not started'}</Badge></div><h3>{domain.name}</h3><Progress total={n} value={count} label={`${domain.name} completion`} /><div className="row-between"><small>{count} / {n} answered</small>{current && <Link to={submitted ? `/app/results/${current.id}` : `/app/assessment/${current.id}/${i + 1}`} aria-label={`Open ${domain.name}`}><Icon name="arrow" /></Link>}</div></Card>;
      })}</div></>}
    <div className="two-column"><Card><h3>Answer for today, not tomorrow.</h3><p><strong>Yes</strong> means the practice is consistently in place across the procurement function. <strong>No</strong> includes gaps or partial implementation.</p></Card><Card><h3>Your history stays intact.</h3><p>Submitted results cannot be edited directly. The program team can open a correction revision while preserving the original submission.</p><Link to="/app/history" className="text-link">View assessment history <Icon name="arrow" /></Link></Card></div></>;
}

export function History() {
  const { data, isLoading, error, refetch } = useHistoryQuery();
  if (isLoading) return <Loading label="Loading assessment history…" />;
  if (error) return <LoadError error={error} retry={refetch} />;
  const rows = [...(data ?? [])].reverse();
  return <><PageHeading eyebrow="YOUR RECORD" title="Assessment history" description="Every submitted revision stays available. A new correction never overwrites the original." />
    {rows.length ? <Card className="table-card"><div className="table-wrap"><table><caption className="sr-only">Your company's assessments and correction history</caption>
      <thead><tr><th>Assessment</th><th>Revision</th><th>Status</th><th>Submitted</th><th>Open</th></tr></thead>
      <tbody>{rows.map((a) => <tr key={a.id}><td><strong>Procurement Assessment</strong>{a.isCorrection && <small className="block">Correction revision</small>}</td><td>v{a.revisionNumber}</td>
        <td><Badge tone={a.status === 'draft' ? 'blue' : a.effective ? 'success' : 'neutral'}>{a.status === 'draft' ? 'Draft' : a.effective ? 'Effective submission' : 'Earlier revision'}</Badge></td>
        <td>{a.submittedAt ? formatDate(a.submittedAt) : 'Not submitted'}</td>
        <td><Link to={a.status === 'draft' ? `/app/assessment/${a.id}/review` : `/app/results/${a.id}`}>{a.status === 'draft' ? 'Resume' : 'View result'}</Link></td></tr>)}</tbody></table></div></Card>
      : <EmptyState title="No assessments yet" action={<LinkButton to="/app/dashboard">Go to workspace</LinkButton>}>Your drafts and submitted reports will appear here.</EmptyState>}</>;
}
