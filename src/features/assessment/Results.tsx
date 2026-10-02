import { NavLink, useParams } from 'react-router-dom';
import { useCurrentUser, workspacePath } from '../../app/hooks';
import { BAND_LABELS } from '../../domain/scoring';
import { exportSnapshot } from '../../infrastructure/downloads';
import { useHistoryQuery } from '../../infrastructure/companyApi';
import { useOwnResultQuery, useStaffSubmissionQuery, type SubmissionSnapshot, type SubmissionView } from '../../infrastructure/staffApi';
import { Alert, Badge, BandBadge, Button, Card, EmptyState, Icon, LinkButton, PageHeading, ScoreRing, formatDate } from '../../components/ui';

type Mode = 'results' | 'recommendations' | 'responses' | 'report';

/** Champions read their own result; staff read any submitted snapshot. Both come from the frozen server snapshot. */
function useSubmission(id: string): { view?: SubmissionView; effective: boolean; loading: boolean; failed: boolean; missing: boolean; retry: () => void } {
  const user = useCurrentUser(); const staff = user.role !== 'champion';
  const own = useOwnResultQuery(id, { skip: staff || !id });
  const history = useHistoryQuery(undefined, { skip: staff });
  const other = useStaffSubmissionQuery(id, { skip: !staff || !id });
  const q = staff ? other : own;
  const status = q.error && 'status' in q.error ? q.error.status : undefined;
  const effective = staff ? !!other.data?.effective : !!history.data?.find((h) => h.id === id)?.effective;
  return { view: q.data, effective, loading: q.isLoading || (!staff && history.isLoading), failed: q.isError && status !== 404 && status !== 403, missing: status === 404 || status === 403, retry: () => void q.refetch() };
}

function DomainScores({ s }: { s: SubmissionSnapshot }) {
  return <div className="domain-scores">{s.result.domains.map((d) => <div className="score-row" key={d.key}><div className="row-between"><strong>{d.name}</strong><span>{d.score.toFixed(1)}%</span></div><div className="score-track" role="img" aria-label={`${d.name}: ${d.score} percent, ${BAND_LABELS[d.band]}`}><span style={{ width: `${d.score}%` }} /></div><div className="row-between"><small>{d.yes} of 10 practices consistently in place</small><BandBadge band={d.band} /></div></div>)}</div>;
}
function Recommendations({ s }: { s: SubmissionSnapshot }) {
  return <div className="recommendation-grid">{s.result.domains.map((d) => <Card className="recommendation-card" key={d.key}><div className="row-between"><span className="domain-number">0{d.order}</span><BandBadge band={d.band} /></div><h2>{d.name}</h2><p className="muted">{d.score.toFixed(1)}% · Advice selected for this domain's maturity level.</p><ol className="action-list">{d.actions.map((a) => <li key={a.id}>{a.text}</li>)}</ol></Card>)}</div>;
}
function SubmittedAnswers({ s }: { s: SubmissionSnapshot }) {
  return <div className="stack">{s.framework.domains.map((d) => <details key={d.key} className="review-domain"><summary><span>{d.name}</span><Badge>{d.questions.length} answers · read-only</Badge></summary><div className="review-answers read-only">{d.questions.map((q) => <div key={q.id}><span className="question-id">{q.id}</span><p>{q.text}</p><Badge tone={s.answers[q.id] === 'yes' ? 'success' : 'neutral'}>{s.answers[q.id] === 'yes' ? 'Yes' : 'No'}</Badge></div>)}</div></details>)}</div>;
}

export default function Results({ mode = 'results' }: { mode?: Mode }) {
  const { id = '' } = useParams(); const user = useCurrentUser();
  const { view, effective, loading, failed, missing, retry } = useSubmission(id);
  if (loading) return <Card><p className="muted">Loading result…</p></Card>;
  if (failed) return <Alert kind="error" title="The result could not be loaded">Check your connection and try again. <Button variant="secondary" onClick={retry}>Try again</Button></Alert>;
  if (missing || !view) return <EmptyState title="No submitted result is available" action={<LinkButton to={workspacePath(user)}>Return to workspace</LinkButton>}>Results are available only after a complete assessment is submitted by an authorized company representative.</EmptyState>;
  const s = view.snapshot; const r = s.result; const total = s.framework.domains.reduce((n, d) => n + d.questions.length, 0);
  const tabs: [Mode, string][] = [['results', 'Results overview'], ['recommendations', 'Recommendations'], ['responses', 'Submitted answers'], ['report', 'Report preview']];
  const title = { results: 'Your procurement maturity', recommendations: 'Recommended next steps', responses: 'Submitted answers', report: 'Company report' }[mode];
  return <div className={mode === 'report' ? 'report-page' : ''}><div className="no-print">
    <PageHeading eyebrow={`PROCUREMENT ASSESSMENT · FRAMEWORK ${s.framework.version} · REVISION ${s.revision.number}`} title={title} description={`${s.company.name} · Submitted ${formatDate(s.submittedAt)}`}
      actions={<><Button variant="secondary" onClick={() => exportSnapshot(view)}><Icon name="download" />Export result JSON</Button>{mode === 'report' ? <Button onClick={() => window.print()}><Icon name="download" />Print / save PDF</Button> : <LinkButton to={`/app/report/${id}`}><Icon name="file" />View report</LinkButton>}</>} />
    <div className="row-between result-status"><Badge tone={effective ? 'success' : 'neutral'}>{effective ? 'Effective submission' : 'Earlier revision — retained for history'}</Badge><span className="muted"><Icon name="lock" size={15} /> Submitted answers are read-only</span></div>
    {!effective && <Alert kind="warning">A later submitted revision is now effective for this company and cycle. This report preserves the earlier answers and result.</Alert>}
    <nav className="tabs" aria-label="Result views">{tabs.map(([path, label]) => <NavLink key={path} to={`/app/${path}/${id}`} className={({ isActive }) => (isActive ? 'active' : '')}>{label}</NavLink>)}</nav></div>
    {mode === 'results' && <><div className="result-overview"><Card className="overall-card"><p className="eyebrow">OVERALL MATURITY</p><ScoreRing score={r.overall} label="Overall score" /><BandBadge band={r.band} /><p>{r.yes} of {total} practices consistently in place</p><small>Self-reported maturity, not a certification.</small></Card><Card><div className="card-heading"><h2>Capabilities at a glance</h2><Badge>Four equally weighted domains</Badge></div><DomainScores s={s} /></Card></div>
      <Card className="interpretation"><span className="icon-box"><Icon name="info" /></span><div><h2>What your result means</h2><p>{r.interpretation}</p></div></Card>
      <div className="section-title"><div><h2>{r.overall === 100 ? 'Sustain your strongest practices' : 'Your relative focus areas'}</h2><p className="muted">The three lowest-scoring domains, ordered consistently.</p></div></div>
      <div className="priority-grid">{r.priorities.map((key, i) => { const d = r.domains.find((x) => x.key === key); return d ? <Card key={key}><div className="row-between"><Badge tone="amber">FOCUS {i + 1}</Badge><strong>{d.score.toFixed(1)}%</strong></div><h3>{d.name}</h3><BandBadge band={d.band} /><p>{r.overall === 100 ? 'Maintain excellence and keep exploring leading practices.' : d.actions[0]?.text}</p></Card> : null; })}</div>
      {r.hasTie && <Alert>Some domain scores are tied. Ties are presented in the workbook's original domain order; the order does not imply a stronger business urgency.</Alert>}
      <div className="section-cta"><div><h2>Turn your result into a useful conversation.</h2><p>Review the recommendations selected from each domain's own maturity band.</p></div><LinkButton to={`/app/recommendations/${id}`}>Explore recommendations <Icon name="arrow" /></LinkButton></div></>}
    {mode === 'recommendations' && <><Alert>These are framework recommendations, not assigned tasks or AI-generated advice. Actions are selected for each domain from its own maturity band.</Alert><Recommendations s={s} /></>}
    {mode === 'responses' && <><Alert>These are the exact answers saved with this revision. A correction requires a new draft opened by a Super Admin.</Alert><SubmittedAnswers s={s} /></>}
    {mode === 'report' && <><Alert title="Browser-generated report">Use Print / save PDF to open your browser's print dialog. Choose A4 and disable browser headers/footers. This is not a certified document.</Alert>
      <article className="print-report"><header className="report-header"><span className="report-wordmark">TALEED</span><span>PROCUREMENT SELF-ASSESSMENT<br />COMPANY REPORT</span></header><p className="eyebrow">COMPANY CAPABILITY REPORT</p><h1>{s.company.name}</h1><p>{s.framework.title} · Submitted {formatDate(s.submittedAt)} · Revision {s.revision.number}</p>
        <div className="report-meta"><span><strong>Respondent</strong>{s.respondent.name}{s.respondent.jobTitle ? ` · ${s.respondent.jobTitle}` : ''}</span><span><strong>Company</strong>{s.company.country}</span><span><strong>Framework</strong>v{s.framework.version}</span><span><strong>Status</strong>{effective ? 'Effective submission' : 'Historical revision'}</span></div>
        <div className="report-result"><strong>{r.overall.toFixed(1)}%</strong><div><h2>{BAND_LABELS[r.band]}</h2><p>{r.yes} of {total} practices consistently in place.</p></div></div><p>{r.interpretation}</p>
        <h2>Domain results</h2><table className="report-table"><thead><tr><th>Capability area</th><th>Yes</th><th>Score</th><th>Maturity</th></tr></thead><tbody>{r.domains.map((d) => <tr key={d.key}><td>{d.name}</td><td>{d.yes}/10</td><td>{d.score.toFixed(1)}%</td><td>{BAND_LABELS[d.band]}</td></tr>)}</tbody></table>
        <h2>Relative focus areas</h2><p>{r.priorities.map((k) => r.domains.find((d) => d.key === k)?.name).join(' · ')}</p>{r.hasTie && <p className="report-note">Tied scores use the source domain order. This is a relative ordering, not an urgency assessment.</p>}
        <h2>Recommended next steps</h2><Recommendations s={s} />
        <section className="report-method"><h2>Method and limitations</h2><p>Equally weighted Yes/No questions. Overall score = Yes answers ÷ total × 100. Domain score = Yes answers ÷ 10 × 100. Foundational: 0–40%; Developing: &gt;40–65%; Advanced: &gt;65–80%; Best-in-Class: &gt;80–100%.</p><p>Results are self-reported and do not constitute certification, independent assurance or a market benchmark. Source: Aramco Taleed &amp; Roland Berger.</p><small>Assessment reference: {view.revisionId} · Snapshot checksum {view.checksum.slice(0, 12)}</small></section></article></>}
  </div>;
}
