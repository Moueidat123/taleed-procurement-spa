import { useState } from 'react';
import { BANDS, BAND_LABELS } from '../../domain/scoring';
import type { Band } from '../../domain/types';
import { Alert, Badge, Button, Card, Field, PageHeading } from '../../components/ui';
import { useFrameworkQuery } from '../../infrastructure/companyApi';
import { usePortfolioQuery } from '../../infrastructure/staffApi';

/**
 * Read-only library of the published framework used by the latest cycle.
 * Versioning and publication are controlled server-side (procurement:framework:* commands); the SPA never edits content.
 */
export default function Frameworks() {
  const portfolio = usePortfolioQuery();
  const version = portfolio.data?.cycle.frameworkVersion ?? '';
  const fw = useFrameworkQuery(version, { skip: !version });
  const [band, setBand] = useState<Band>('foundational');
  if (portfolio.isLoading || fw.isLoading) return <Card><p className="muted">Loading framework…</p></Card>;
  if (portfolio.isError || fw.isError || !fw.data) return <Alert kind="error" title="The framework could not be loaded">Check your connection and try again. <Button variant="secondary" onClick={() => { void portfolio.refetch(); if (version) void fw.refetch(); }}>Try again</Button></Alert>;
  const f = fw.data;
  const questions = f.domains.reduce((n, d) => n + d.questions.length, 0);
  return <><PageHeading eyebrow="CONTROLLED CONTENT" title="Framework library" description="The published question bank, scoring contract and domain-specific recommendation library." />
    <Card className="framework-overview"><div className="row-between"><h2>{f.title}</h2><Badge tone="success">v{f.version} · published</Badge></div>
      <div className="framework-facts"><span><strong>{questions}</strong>Questions</span><span><strong>{f.domains.length}</strong>Equal domains</span><span><strong>16</strong>Actions per result</span></div>
      <p className="muted">Used by cycle: {portfolio.data?.cycle.title}</p></Card>
    <Alert>Published content and submitted result snapshots are never edited in place. New versions are imported and published by an administrator on the server with an approval reference.</Alert>
    <div className="two-column"><Card><h2>Scoring rules</h2><p>Yes = 1 point; No = 0 points. Unanswered is not No. All questions are required before a result is finalized.</p><p>Overall = Yes ÷ total × 100. Each domain = Yes ÷ domain questions × 100. There is no N/A or partial-credit answer.</p><p>Priority domains are the three lowest scores, with ties in source order. Recommendations use each domain's own band.</p></Card>
      <Card><h2>Maturity bands</h2><dl className="band-definitions">{BANDS.map((b, i) => <div key={b}><dt><Badge tone={b}>{BAND_LABELS[b]}</Badge></dt><dd>{['0–40%', '>40–65%', '>65–80%', '>80–100%'][i]}</dd></div>)}</dl><small>Classification uses the exact, unrounded score.</small></Card></div>
    <div className="section-title"><h2>Questions and recommendations</h2><Field label="Recommendation band" htmlFor="framework-band"><select id="framework-band" value={band} onChange={(e) => setBand(e.target.value as Band)}>{BANDS.map((b) => <option key={b} value={b}>{BAND_LABELS[b]}</option>)}</select></Field></div>
    <div className="stack">{f.domains.map((d) => <details className="framework-domain" key={d.key}><summary><span><span className="domain-number">0{d.order}</span>{d.name}</span><Badge>{d.questions.length} questions</Badge></summary>
      <div className="framework-domain-body"><div><h3>Source questions</h3>{d.questions.map((q) => <div className="catalogue-question" key={q.id}><strong>{q.id}</strong><p>{q.text}<small>{q.sourceCell}</small></p></div>)}</div>
        <div><h3>{BAND_LABELS[band]} recommendations</h3><ol className="action-list">{(d.recommendations[band] ?? []).map((a) => <li key={a.id}>{a.text}<small className="block muted">{a.sourceCell}</small></li>)}</ol></div></div></details>)}</div>
  </>;
}
