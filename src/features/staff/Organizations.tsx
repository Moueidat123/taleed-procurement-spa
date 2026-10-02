import { useState } from 'react';
import { Link, useParams, useSearchParams } from 'react-router-dom';
import { useCurrentUser } from '../../app/hooks';
import { BANDS, BAND_LABELS } from '../../domain/scoring';
import type { Band } from '../../domain/types';
import { toApiError } from '../../infrastructure/api';
import { useOpenCorrectionMutation, useSetOrganizationActiveMutation, useStaffOrganizationQuery, useStaffOrganizationsQuery, type Stage } from '../../infrastructure/staffApi';
import { Alert, Badge, BandBadge, Button, Card, Dialog, EmptyState, Field, Icon, LinkButton, PageHeading, Progress, formatDate } from '../../components/ui';
import { Pager } from './Pager';

const STAGES: Record<Stage, { label: string; tone: string }> = {
  no_profile: { label: 'Awaiting profile setup', tone: 'neutral' }, not_started: { label: 'Not started', tone: 'amber' },
  in_progress: { label: 'In progress', tone: 'blue' }, submitted: { label: 'Completed', tone: 'success' },
};

/**
 * Champion & company directory: every Champion appears from registration ("Awaiting profile setup"),
 * then as an active company with progress. Search, filters and paging run on the server; no draft answers.
 */
export function Organizations() {
  const [params, setParams] = useSearchParams();
  const search = params.get('q') ?? ''; const stage = (params.get('stage') ?? '') as Stage | ''; const band = (params.get('band') ?? '') as Band | '';
  const page = Math.max(1, Number(params.get('page') ?? 1) || 1);
  const list = useStaffOrganizationsQuery({ search, stage, band, page, perPage: 25 });
  const setFilter = (key: string, value: string) => setParams((cur) => { const next = new URLSearchParams(cur); if (value) next.set(key, value); else next.delete(key); next.delete('page'); return next; });
  const rows = list.data?.data ?? []; const total = list.data?.meta.total ?? 0;
  const error = list.isError ? toApiError(list.error as never) : null;
  return <><PageHeading eyebrow="PARTICIPATION" title="Organizations & champion progress" description="Every registered Company Champion appears here from sign-up: profile setup, answering progress and submitted results. Draft answers are never shown to staff." />
    <Card className="filters compact"><Field label="Company or champion" htmlFor="org-search"><input id="org-search" type="search" value={search} onChange={(e) => setFilter('q', e.target.value)} placeholder="Search companies, names or emails…" /></Field>
      <Field label="Assessment stage" htmlFor="org-stage"><select id="org-stage" value={stage} onChange={(e) => setFilter('stage', e.target.value)}><option value="">All stages</option>{(Object.keys(STAGES) as Stage[]).map((s) => <option key={s} value={s}>{STAGES[s].label}</option>)}</select></Field>
      <Field label="Maturity band" htmlFor="org-band"><select id="org-band" value={band} onChange={(e) => setFilter('band', e.target.value)}><option value="">All bands</option>{BANDS.map((b) => <option key={b} value={b}>{BAND_LABELS[b]}</option>)}</select></Field>
      <span className="filter-count" role="status">{total} result{total === 1 ? '' : 's'}</span></Card>
    {list.isLoading ? <Card><p className="muted">Loading organizations…</p></Card>
      : error?.code === 'two_factor_required' ? <Alert kind="warning" title="Set up two-step verification first">Staff lists open only after two-step verification is confirmed for your account. <Link to="/app/security">Set up two-step verification</Link></Alert>
      : error ? <Alert kind="error" title="The directory could not be loaded">{error.message} <Button variant="secondary" onClick={() => void list.refetch()}>Try again</Button></Alert>
      : rows.length ? <Card className="table-card"><div className="table-wrap"><table><caption className="sr-only">Champion and company directory</caption><thead><tr><th>Company / champion</th><th>Size</th><th>Account</th><th>Assessment progress</th><th>Result</th><th>Details</th></tr></thead><tbody>{rows.map((r) => <tr key={`${r.organizationId ? 'o' : 'u'}-${r.id}`}>
        <td><strong>{r.name ?? 'Profile not set yet'}</strong>{r.champion && <small className="block">{r.champion.name} · {r.champion.email}</small>}</td>
        <td>{r.size ?? '—'}</td>
        <td>{r.organizationId ? <Badge tone={r.active ? 'success' : 'amber'}>{r.active ? 'Active' : 'Paused'}</Badge>
          : <><Badge tone={r.active ? 'neutral' : 'amber'}>{r.active ? 'Registered' : 'Paused'}</Badge>{r.champion && !r.champion.verified && <small className="block">Email not verified</small>}</>}</td>
        <td className="progress-cell"><div className="row-between"><Badge tone={STAGES[r.stage].tone}>{STAGES[r.stage].label}</Badge>{(r.stage === 'in_progress' || r.stage === 'submitted') && <small>{r.answeredCount}/40{r.stage === 'in_progress' ? ` · ${40 - r.answeredCount} left` : ''}</small>}</div>{(r.stage === 'in_progress' || r.stage === 'submitted') && <Progress value={r.answeredCount} label={`${r.name ?? 'Company'} completion`} />}</td>
        <td>{r.overall !== null ? <><strong>{r.overall.toFixed(1)}%</strong>{r.band && <><br /><BandBadge band={r.band} /></>}</> : '—'}</td>
        <td>{r.organizationId ? <Link to={`/app/organizations/${r.organizationId}`}>View company</Link> : <span className="muted">No company yet</span>}</td></tr>)}</tbody></table></div>{list.data && <Pager meta={list.data.meta} onPage={(p) => setParams((cur) => { const n = new URLSearchParams(cur); n.set('page', String(p)); return n; })} />}</Card>
      : <EmptyState title="No champions match these filters">Try a different search, stage or band. New champions appear here as soon as they register.</EmptyState>}</>;
}

export function OrganizationDetail() {
  const { id = '' } = useParams(); const user = useCurrentUser(); const admin = user.role === 'admin';
  const org = useStaffOrganizationQuery(id, { skip: !id });
  const [openCorrection, correcting] = useOpenCorrectionMutation(); const [setActive, toggling] = useSetOrganizationActiveMutation();
  const [correctionId, setCorrectionId] = useState<string | null>(null); const [reason, setReason] = useState(''); const [pause, setPause] = useState(false); const [error, setError] = useState('');
  if (org.isLoading) return <Card><p className="muted">Loading organization…</p></Card>;
  if (!org.data) return <EmptyState title="Organization not found" action={<LinkButton to="/app/organizations">Organization directory</LinkButton>}>This organization does not exist or could not be loaded.</EmptyState>;
  const o = org.data; const subs = [...o.submissions].reverse();
  const run = async (fn: () => Promise<unknown>, done: () => void) => { setError(''); try { await fn(); done(); } catch (e) { setError(toApiError(e as never).message); } };
  return <><PageHeading eyebrow="ORGANIZATION DETAIL" title={o.name} description={`${o.country} · ${o.size} employees`} actions={admin && <Button variant={o.active ? 'secondary' : 'primary'} onClick={() => { setError(''); setPause(true); }}>{o.active ? 'Pause organization' : 'Resume organization'}</Button>} />
    <Card><div className="card-heading"><h2>Company information</h2><Badge tone={o.active ? 'success' : 'amber'}>{o.active ? 'Active' : 'Paused'}</Badge></div><dl className="definition-list"><dt>Company reference</dt><dd>{o.registrationId || 'Not provided'}</dd>{o.isTest && <><dt>Data</dt><dd>Test company — excluded from portfolio figures</dd></>}</dl></Card>
    <div className="section-title"><h2>Submitted assessment history</h2><span>{subs.length} revisions</span></div>
    {subs.length ? <Card className="table-card"><div className="table-wrap"><table><caption className="sr-only">Company submitted assessments</caption><thead><tr><th>Revision</th><th>Result</th><th>Record status</th><th>Submitted</th><th>Actions</th></tr></thead><tbody>{subs.map((s) => <tr key={s.id}>
      <td>v{s.revisionNumber}{s.isCorrection ? ' · correction' : ''}</td><td>{s.overall !== null && <><strong>{s.overall.toFixed(1)}%</strong><br /></>}{s.band && <BandBadge band={s.band} />}</td>
      <td><Badge tone={s.effective ? 'success' : 'neutral'}>{s.effective ? 'Effective' : 'Earlier revision'}</Badge></td><td>{formatDate(s.submittedAt)}</td>
      <td><div className="table-actions"><Link to={`/app/results/${s.id}`}>View result</Link>{admin && s.effective && <Button variant="ghost" onClick={() => { setError(''); setReason(''); setCorrectionId(s.id); }}><Icon name="refresh" size={16} />Open correction</Button>}</div></td></tr>)}</tbody></table></div></Card>
      : <EmptyState title="No submitted assessments yet">Results appear after the company's representative submits all answers.</EmptyState>}
    <Dialog open={!!correctionId} title="Open a controlled correction" onClose={() => setCorrectionId(null)}><Alert kind="warning">The submitted result stays effective until the Company Champion submits the new revision. This is not an approval stage.</Alert>
      <Field label="Reason for correction *" htmlFor="correction-reason" hint="10–1,000 characters. Visible to the company."><textarea id="correction-reason" rows={4} maxLength={1000} value={reason} onChange={(e) => setReason(e.target.value)} /></Field>{error && <Alert kind="error">{error}</Alert>}
      <div className="dialog-actions"><Button variant="secondary" onClick={() => setCorrectionId(null)}>Cancel</Button><Button disabled={correcting.isLoading || reason.trim().length < 10} onClick={() => void run(() => openCorrection({ revisionId: correctionId ?? '', reason: reason.trim() }).unwrap(), () => setCorrectionId(null))}>Create correction draft</Button></div></Dialog>
    <Dialog open={pause} title={o.active ? 'Pause this organization?' : 'Resume this organization?'} onClose={() => setPause(false)}><p>{o.active ? 'Representatives keep read access to submitted results but cannot save or submit.' : 'Representatives can continue editing and submitting in open cycles.'} No history is deleted.</p>{error && <Alert kind="error">{error}</Alert>}
      <div className="dialog-actions"><Button variant="secondary" onClick={() => setPause(false)}>Cancel</Button><Button disabled={toggling.isLoading} onClick={() => void run(() => setActive({ id, active: !o.active }).unwrap(), () => setPause(false))}>Confirm</Button></div></Dialog></>;
}
