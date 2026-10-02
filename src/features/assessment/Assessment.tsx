import { useCallback, useEffect, useMemo, useRef, useState } from 'react';
import { Link, Navigate, useNavigate, useParams, useSearchParams } from 'react-router-dom';
import { Alert, Badge, Button, Card, Dialog, EmptyState, Icon, LinkButton, PageHeading, Progress } from '../../components/ui';
import {
  useCyclesQuery, useFrameworkQuery, useOrganizationQuery, useRevisionQuery, useSaveAnswersMutation, useSubmitAssessmentMutation,
  type Answer, type Cycle, type Framework, type Organization, type Revision,
} from '../../infrastructure/companyApi';

type SaveState = 'idle' | 'saving' | 'saved' | 'error' | 'conflict';
const statusOf = (e: unknown) => (typeof e === 'object' && e && 'status' in e ? (e as { status: unknown }).status : undefined);
const cycleOpen = (c: Cycle | undefined) => !!c && Date.now() >= Date.parse(c.opensAt) && Date.now() < Date.parse(c.closesAt);

/** Loads the revision plus everything the screens need; scoped to the caller's company by the server. */
function useAssessmentData(id: string) {
  const rev = useRevisionQuery(id, { skip: !id, refetchOnMountOrArgChange: true });
  const fw = useFrameworkQuery(rev.data?.frameworkVersion ?? '', { skip: !rev.data });
  const cycles = useCyclesQuery();
  const org = useOrganizationQuery();
  const loading = rev.isLoading || fw.isLoading || cycles.isLoading || org.isLoading;
  const failed = rev.isError || fw.isError || cycles.isError || org.isError;
  const retry = () => { void rev.refetch(); if (rev.data) void fw.refetch(); void cycles.refetch(); void org.refetch(); };
  const cycle = cycles.data?.find((c) => c.id === rev.data?.cycleId);
  return { rev, revision: rev.data, framework: fw.data, cycle, org: org.data ?? null, loading, failed, retry, notFound: statusOf(rev.error) === 404 || statusOf(rev.error) === 403 };
}

/**
 * Autosave: edits are shown immediately, batched, and sent one request at a time with the
 * latest known version. A 409 means the draft changed elsewhere; local edits are kept until the user decides.
 */
function useAutosave(revision: Revision | undefined, refetch: () => unknown) {
  const [save] = useSaveAnswersMutation();
  const [local, setLocal] = useState<Record<string, Answer>>({});
  const [state, setState] = useState<SaveState>('idle');
  const pending = useRef<Record<string, Answer>>({});
  const version = useRef<number | undefined>(undefined);
  const chain = useRef<Promise<boolean>>(Promise.resolve(true));
  const timer = useRef<number | undefined>(undefined);
  const id = revision?.id;

  useEffect(() => { if (revision && (version.current === undefined || revision.version > version.current)) version.current = revision.version; }, [revision]);

  const runOnce = useCallback(async (): Promise<boolean> => {
    while (id && Object.keys(pending.current).length) {
      const batch = pending.current; pending.current = {};
      setState('saving');
      try {
        const saved = await save({ id, expectedVersion: version.current ?? 0, answers: batch }).unwrap();
        version.current = saved.version;
        setLocal((cur) => { const next = { ...cur }; for (const k of Object.keys(batch)) if (!(k in pending.current) && next[k] === batch[k]) delete next[k]; return next; });
      } catch (e) {
        pending.current = { ...batch, ...pending.current };
        if (statusOf(e) === 409) { setState('conflict'); refetch(); } else setState('error');
        return false;
      }
    }
    setState((s) => (s === 'saving' ? 'saved' : s));
    return true;
  }, [id, save, refetch]);

  const flush = useCallback(() => { window.clearTimeout(timer.current); chain.current = chain.current.then(runOnce, runOnce); return chain.current; }, [runOnce]);

  const set = useCallback((questionId: string, value: Answer) => {
    setLocal((cur) => ({ ...cur, [questionId]: value }));
    pending.current = { ...pending.current, [questionId]: value };
    window.clearTimeout(timer.current);
    timer.current = window.setTimeout(() => { void flush(); }, 600);
  }, [flush]);

  /** Conflict choices: keep my edits on top of the latest server version, or discard them. */
  const keepMine = useCallback(() => { version.current = revision?.version; setState('idle'); void flush(); }, [revision, flush]);
  const discard = useCallback(() => { pending.current = {}; setLocal({}); version.current = undefined; setState('idle'); refetch(); }, [refetch]);

  useEffect(() => {
    const warn = (e: BeforeUnloadEvent) => { if (Object.keys(pending.current).length) e.preventDefault(); };
    window.addEventListener('beforeunload', warn);
    return () => { window.removeEventListener('beforeunload', warn); void flush(); };
  }, [flush]);

  const answers = useMemo(() => ({ ...(revision?.answers ?? {}), ...local }), [revision, local]);
  return { answers, set, flush, state, keepMine, discard, dirty: Object.keys(local).length > 0 };
}

const countAnswered = (framework: Framework, answers: Record<string, Answer>) =>
  framework.domains.reduce((n, d) => n + d.questions.filter((q) => answers[q.id] != null).length, 0);
const totalQuestions = (framework: Framework) => framework.domains.reduce((n, d) => n + d.questions.length, 0);
const profileReady = (org: Organization | null) => !!org && org.active && org.authorityConfirmed && !!org.displayName && !!org.countryCode && !!org.sizeBand;

function Loading() { return <Card><p className="muted">Loading assessment…</p></Card>; }
function LoadFailed({ retry }: { retry: () => void }) { return <Alert kind="error" title="The assessment could not be loaded">Check your connection and try again. <Button variant="secondary" onClick={retry}>Try again</Button></Alert>; }
function Unavailable() { return <EmptyState title="Assessment unavailable" action={<LinkButton to="/app/dashboard">Return to workspace</LinkButton>}>This assessment or section is not available to your company.</EmptyState>; }

function SaveStatus({ state, keepMine, discard, retry }: { state: SaveState; keepMine: () => void; discard: () => void; retry: () => void }) {
  if (state === 'conflict') return <Alert kind="warning" title="This draft changed somewhere else">Another tab or session saved answers to this draft. Your unsaved answers are still on screen. <div className="dialog-actions"><Button variant="secondary" onClick={discard}>Load latest and discard mine</Button><Button onClick={keepMine}>Keep my answers</Button></div></Alert>;
  if (state === 'error') return <Alert kind="error" title="Answers not saved">Your latest answers could not be saved. <Button variant="secondary" onClick={retry}>Retry save</Button></Alert>;
  return null;
}

export function AssessmentPage() {
  const { id = '', section = '1' } = useParams();
  const navigate = useNavigate(); const [params] = useSearchParams();
  const [showMissing, setShowMissing] = useState(false);
  const data = useAssessmentData(id);
  const auto = useAutosave(data.revision, data.rev.refetch);
  const index = Number(section) - 1;
  const domain = Number.isInteger(index) ? data.framework?.domains[index] : undefined;

  useEffect(() => {
    setShowMissing(false);
    const target = params.get('question');
    if (!target || !domain) return;
    const frame = requestAnimationFrame(() => { const el = document.getElementById(`question-${target}`); el?.scrollIntoView({ block: 'center' }); el?.querySelector('input')?.focus(); });
    return () => cancelAnimationFrame(frame);
  }, [id, section, params, domain]);

  if (data.loading) return <Loading />;
  if (data.notFound) return <Unavailable />;
  if (data.failed) return <LoadFailed retry={data.retry} />;
  const { revision, framework, cycle, org } = data;
  if (!revision || !framework || !domain) return <Unavailable />;
  if (revision.status === 'submitted') return <Navigate to={`/app/results/${id}`} replace />;

  const answers = auto.answers; const domains = framework.domains; const last = domains.length - 1;
  const editable = !!org?.active && auto.state !== 'conflict';
  const answered = countAnswered(framework, answers); const total = totalQuestions(framework);
  const missing = domain.questions.filter((q) => answers[q.id] == null);
  const go = async (to: string) => { if (await auto.flush()) navigate(to); };
  const next = () => {
    if (missing.length) { setShowMissing(true); requestAnimationFrame(() => document.getElementById(`question-${missing[0]?.id}`)?.querySelector('input')?.focus()); return; }
    void go(index === last ? `/app/assessment/${id}/review` : `/app/assessment/${id}/${index + 2}`);
  };
  const saveLabel = auto.state === 'saving' || auto.dirty ? 'Saving…' : auto.state === 'error' || auto.state === 'conflict' ? 'Not saved' : 'All answers saved';

  return <><PageHeading eyebrow={`${cycle?.title ?? 'ASSESSMENT'} · REVISION ${revision.revisionNumber}`} title={domain.name} description={`Section ${index + 1} of ${domains.length} · Reflect on your current procurement practices.`}
    actions={<><span className="muted" role="status" aria-live="polite">{saveLabel}</span><Button variant="secondary" disabled={auto.state === 'saving'} onClick={() => void go('/app/dashboard')}><Icon name="check" />Save & exit</Button></>} />
    <SaveStatus state={auto.state} keepMine={auto.keepMine} discard={auto.discard} retry={() => void auto.flush()} />
    {!org?.active && <Alert kind="warning">Editing is paused because your organization is paused. Saved answers remain visible.</Alert>}
    {!revision.isCorrection && !cycleOpen(cycle) && <Alert kind="warning">This cycle is not open for submission. You can keep your answers, but you cannot submit until a cycle is open.</Alert>}
    {revision.correctionReason && <Alert kind="warning" title="Correction draft">{revision.correctionReason} Your previous submission remains effective until this revision is submitted.</Alert>}
    <Card className="assessment-progress"><div className="row-between"><strong>Overall completion</strong><span>{answered} / {total} questions</span></div><Progress value={answered} total={total} /></Card>
    <div className="assessment-layout"><aside className="assessment-aside"><nav aria-label="Assessment sections" className="stepper">
      {domains.map((d, i) => { const count = d.questions.filter((q) => answers[q.id] != null).length; const done = count === d.questions.length;
        return <Link key={d.key} to={`/app/assessment/${id}/${i + 1}`} onClick={() => void auto.flush()} className={`step ${index === i ? 'active' : ''}`} aria-current={index === i ? 'step' : undefined}><span className={`step-number ${done ? 'done' : ''}`}>{done ? <Icon name="check" size={16} /> : i + 1}</span><span><strong>{d.name}</strong><small>{count} of {d.questions.length} answered</small></span></Link>; })}
      <Link className="step" to={`/app/assessment/${id}/review`} onClick={() => void auto.flush()}><span className="step-number"><Icon name="file" size={16} /></span><span><strong>Review & submit</strong><small>Check all {total} answers</small></span></Link></nav>
      <Card className="guidance-card"><Icon name="info" /><h3>Answer honestly.</h3><p><strong>Yes</strong><br />Consistently in place across the function.</p><p><strong>No</strong><br />Missing or only partly implemented.</p><small>There is no N/A option or partial credit in the source framework.</small></Card></aside>
      <div className="question-list"><div className="row-between section-count"><h2>{domain.questions.length - missing.length} of {domain.questions.length} answered</h2><Badge tone={missing.length ? 'blue' : 'success'}>{missing.length ? 'In progress' : 'Section complete'}</Badge></div>
        {showMissing && missing.length > 0 && <Alert kind="error" title={`${missing.length} unanswered question${missing.length === 1 ? '' : 's'}`}>Answer every question in this section before continuing, or use the section navigation to return later.</Alert>}
        {domain.questions.map((q) => { const value = answers[q.id] ?? null; const invalid = showMissing && value === null;
          return <fieldset id={`question-${q.id}`} key={q.id} className={`question-card ${invalid ? 'question-error' : ''}`} aria-describedby={invalid ? `missing-${q.id}` : undefined} disabled={!editable}><legend><span className="question-id">{q.id}</span>{q.text}</legend>
            <div className="answer-options">{(['yes', 'no'] as const).map((a) => <label key={a} className={`answer-option ${value === a ? 'selected' : ''}`}><input type="radio" name={`answer-${q.id}`} value={a} checked={value === a} onChange={() => auto.set(q.id, a)} /><span><strong>{a === 'yes' ? 'Yes' : 'No'}</strong><small>{a === 'yes' ? 'Consistently in place' : 'Missing or partly in place'}</small></span>{value === a && <Icon name="check" size={18} />}</label>)}</div>
            <div className="question-footer"><small>{value === null ? 'Choose one answer' : ''}</small>{value !== null && <Button variant="ghost" onClick={() => auto.set(q.id, null)} aria-label={`Clear answer ${q.id}`}>Clear answer</Button>}</div>
            {invalid && <span id={`missing-${q.id}`} className="field-error">Choose Yes or No for this question.</span>}</fieldset>; })}
        <Card className="assessment-actions"><Button variant="secondary" onClick={() => void go(index === 0 ? '/app/dashboard' : `/app/assessment/${id}/${index}`)}><Icon name="back" />{index === 0 ? 'Workspace' : 'Previous section'}</Button><span>{domain.questions.length - missing.length}/{domain.questions.length} answered</span><Button onClick={next}>{index === last ? 'Review all answers' : 'Continue'}<Icon name="arrow" /></Button></Card>
      </div></div></>;
}

export function ReviewPage() {
  const { id = '' } = useParams(); const navigate = useNavigate();
  const data = useAssessmentData(id);
  const [submit, submitting] = useSubmitAssessmentMutation();
  const [declaration, setDeclaration] = useState(false); const [confirm, setConfirm] = useState(false); const [attempted, setAttempted] = useState(false);
  const [error, setError] = useState<string | null>(null);
  // One key per confirmed attempt at a given version, so a network retry cannot submit twice.
  const attempt = useRef<{ key: string; version: number } | null>(null);

  if (data.loading) return <Loading />;
  if (data.notFound) return <Unavailable />;
  if (data.failed) return <LoadFailed retry={data.retry} />;
  const { revision, framework, cycle, org } = data;
  if (!revision || !framework) return <Unavailable />;
  if (revision.status === 'submitted') return <Navigate to={`/app/results/${id}`} replace />;

  const answers = revision.answers; const total = totalQuestions(framework); const answered = countAnswered(framework, answers);
  const unanswered = framework.domains.flatMap((d, i) => d.questions.filter((q) => answers[q.id] == null).map((q) => ({ ...q, section: i + 1 })));
  const windowOk = revision.isCorrection || cycleOpen(cycle);
  const eligible = windowOk && profileReady(org) && unanswered.length === 0;

  const doSubmit = async () => {
    setError(null);
    if (!attempt.current || attempt.current.version !== revision.version) attempt.current = { key: crypto.randomUUID(), version: revision.version };
    try {
      await submit({ id, expectedVersion: attempt.current.version, idempotencyKey: attempt.current.key }).unwrap();
      setConfirm(false); navigate(`/app/results/${id}`);
    } catch (e) {
      const s = statusOf(e);
      if (s === 409) { attempt.current = null; setError('This draft changed or was already submitted. The latest answers have been loaded; review them and submit again.'); void data.rev.refetch(); }
      else if (s === 422) setError('Submission was refused. Check that every answer, your company profile and the cycle are in order.');
      else setError('The submission could not be confirmed. Try again; retrying will not create a duplicate submission.');
    }
  };

  return <><PageHeading eyebrow={`FINAL CHECK · REVISION ${revision.revisionNumber}`} title="Review your assessment" description="Check every answer. Once submitted, this revision becomes a read-only record." actions={<LinkButton variant="secondary" to="/app/dashboard">Save & exit</LinkButton>} />
    <div className="review-layout"><div className="stack"><Card><div className="row-between"><h2>Readiness check</h2><Badge tone={eligible ? 'success' : 'amber'}>{eligible ? 'Ready to submit' : 'Action required'}</Badge></div><Progress value={answered} total={total} /><p><strong>{answered} of {total} answers</strong> completed across {framework.domains.length} domains.</p>
      {unanswered.length > 0 && <Alert kind="warning" title={`${unanswered.length} answer${unanswered.length === 1 ? ' is' : 's are'} still missing`}>No maturity result is calculated for an incomplete assessment.<div className="missing-links">{unanswered.map((q) => <Link key={q.id} to={`/app/assessment/${id}/${q.section}?question=${q.id}`} aria-label={`Answer missing question ${q.id}`}>{q.id}</Link>)}</div></Alert>}
      {!profileReady(org) && <Alert kind="error">Complete the <Link to="/app/profile">company profile and authority declaration</Link>{org && !org.active ? ' (your organization is currently paused)' : ''}.</Alert>}
      {!windowOk && <Alert kind="warning">The cycle is not open. Submission is not available.</Alert>}</Card>
      {framework.domains.map((d, i) => <details className="review-domain" key={d.key} open={d.questions.some((q) => answers[q.id] == null)}><summary><span><span className="domain-number">0{i + 1}</span>{d.name}</span><Badge tone={d.questions.every((q) => answers[q.id] != null) ? 'success' : 'amber'}>{d.questions.filter((q) => answers[q.id] != null).length}/{d.questions.length} answered</Badge></summary>
        <div className="review-answers">{d.questions.map((q) => <div key={q.id}><span className="question-id">{q.id}</span><p>{q.text}</p><Badge tone={answers[q.id] == null ? 'amber' : 'neutral'}>{answers[q.id] == null ? 'Unanswered' : answers[q.id] === 'yes' ? 'Yes' : 'No'}</Badge><Link to={`/app/assessment/${id}/${i + 1}?question=${q.id}`} aria-label={`Edit answer ${q.id}`}>Edit</Link></div>)}</div></details>)}</div>
      <aside className="stack review-summary"><Card><h2>Submission summary</h2><dl className="definition-list"><dt>Company</dt><dd>{org?.displayName}</dd><dt>Cycle</dt><dd>{cycle?.title ?? '—'}</dd><dt>Framework</dt><dd>v{revision.frameworkVersion}</dd><dt>Revision</dt><dd>{revision.revisionNumber}</dd></dl><div className="divider" />
        <label className="checkbox"><input type="checkbox" checked={declaration} onChange={(e) => setDeclaration(e.target.checked)} /><span>I confirm these answers accurately reflect the company's current procurement practices and I am authorized to submit.</span></label>
        {attempted && !declaration && <p className="field-error">Confirm the declaration before submitting.</p>}
        <Button className="full-width" disabled={submitting.isLoading || !eligible} onClick={() => { setAttempted(true); if (declaration && eligible) setConfirm(true); }}>Submit assessment <Icon name="arrow" /></Button>
        {!eligible && <small className="muted block">Complete the readiness items to enable submission.</small>}</Card>
        <Alert>Results appear immediately after a successful submission. There is no mandatory Taleed approval gate or certification.</Alert></aside></div>
    <Dialog open={confirm} title="Submit this assessment?" onClose={() => { if (!submitting.isLoading) setConfirm(false); }}><p>You are submitting <strong>revision {revision.revisionNumber}</strong> for <strong>{org?.displayName}</strong>.</p><p>Your answers and result will become read-only. A Super Admin can later open a correction revision without deleting this one.</p>
      {error && <Alert kind="error">{error}</Alert>}
      <div className="dialog-actions"><Button variant="secondary" onClick={() => setConfirm(false)} disabled={submitting.isLoading}>Keep reviewing</Button><Button disabled={submitting.isLoading} onClick={() => void doSubmit()}>{submitting.isLoading ? 'Submitting…' : 'Confirm submission'}</Button></div></Dialog>
  </>;
}
