import { useState } from 'react';
import { Link, useSearchParams } from 'react-router-dom';
import { useCurrentUser } from '../../app/hooks';
import { BANDS, BAND_LABELS } from '../../domain/scoring';
import type { Band } from '../../domain/types';
import { buildPortfolioExport, downloadBlob } from '../../infrastructure/downloads';
import { toApiError } from '../../infrastructure/api';
import { useExportRowsMutation, usePortfolioQuery, useStaffOrganizationsQuery } from '../../infrastructure/staffApi';
import { Alert, Badge, BandBadge, Button, Card, EmptyState, Field, Icon, Metric, PageHeading } from '../../components/ui';
import { Pager } from './Pager';

/** Summary comes from /staff/portfolio; the company list is filtered and paged on the server. */
export default function Portfolio() {
  const user = useCurrentUser(); const allowed = user.role === 'admin' || user.canExport;
  const [params, setParams] = useSearchParams();
  const cycleId = params.get('cycle') ?? undefined; const band = (params.get('band') ?? '') as Band | '';
  const search = params.get('q') ?? ''; const page = Math.max(1, Number(params.get('page') ?? 1) || 1);
  const summary = usePortfolioQuery(cycleId);
  const list = useStaffOrganizationsQuery({ cycleId, stage: 'submitted', band, search, page, perPage: 25 });
  const [exportRows, exporting] = useExportRowsMutation(); const [exportError, setExportError] = useState('');
  const setFilter = (key: string, value: string) => setParams((cur) => { const next = new URLSearchParams(cur); if (value) next.set(key, value); else next.delete(key); next.delete('page'); return next; });
  const setPage = (p: number) => setParams((cur) => { const next = new URLSearchParams(cur); next.set('page', String(p)); return next; });
  const doExport = async (format: 'csv' | 'xlsx') => {
    setExportError('');
    try {
      const res = await exportRows({ format, cycleId, band, search }).unwrap();
      downloadBlob(`taleed-portfolio-${res.cycleId}.${format}`, await buildPortfolioExport(res.rows, res.frameworkVersion, format));
    } catch (e) { setExportError(e instanceof Error ? e.message : toApiError(e as never).message); }
  };
  if (summary.isLoading) return <Card><p className="muted">Loading portfolio…</p></Card>;
  if (summary.isError || !summary.data) return <Alert kind="error" title="The portfolio could not be loaded">No assessment cycle may exist yet, or the server could not be reached. <Button variant="secondary" onClick={() => void summary.refetch()}>Try again</Button></Alert>;
  const s = summary.data; const rows = list.data?.data ?? []; const total = list.data?.meta.total ?? 0;
  return <><PageHeading eyebrow="TALEED PROGRAM WORKSPACE" title="Procurement portfolio" description={`${s.cycle.title} · Framework ${s.cycle.frameworkVersion ?? '—'}`}
    actions={<><Button variant="secondary" disabled={exporting.isLoading || !total || !allowed} title={!allowed ? 'Portfolio export permission is required.' : undefined} onClick={() => void doExport('csv')}><Icon name="download" />CSV</Button><Button disabled={exporting.isLoading || !total || !allowed} onClick={() => void doExport('xlsx')}><Icon name="download" />{exporting.isLoading ? 'Preparing…' : 'Export Excel'}</Button></>} />
    <Card className="filters"><Field label="Maturity band" htmlFor="portfolio-band"><select id="portfolio-band" value={band} onChange={(e) => setFilter('band', e.target.value)}><option value="">All maturity bands</option>{BANDS.map((b) => <option key={b} value={b}>{BAND_LABELS[b]}</option>)}</select></Field><Field label="Company name" htmlFor="portfolio-search"><input id="portfolio-search" type="search" placeholder="Search companies…" value={search} onChange={(e) => setFilter('q', e.target.value)} /></Field></Card>
    {exportError && <Alert kind="error">{exportError}</Alert>}{!allowed && <Alert>You have read-only portfolio access. A Super Admin can grant export permission in People & access.</Alert>}
    <div className="metrics-grid"><Metric label="Effective submissions" value={s.submittedCount} foot="One latest submitted revision per company" icon="file" /><Metric label="Average maturity" value={s.averageOverall !== null ? `${s.averageOverall.toFixed(1)}%` : '—'} foot={`Calculated from ${s.submittedCount} submitted results`} icon="chart" /><Metric label="Foundational companies" value={s.bands.foundational} foot="Whole cycle cohort" icon="flag" /><Metric label="Open drafts" value={s.inProgressCount} foot="Started, not yet submitted" icon="clock" /></div>
    <div className="cohort-note"><Icon name="info" /><span>n = {s.submittedCount}. Drafts, earlier revisions and test companies are excluded from averages. These are self-reported programme results, not a market benchmark.</span></div>
    <div className="two-column"><Card><div className="card-heading"><h2>Maturity distribution</h2><Badge>{s.submittedCount} companies</Badge></div><div className="distribution">{BANDS.map((b) => <div key={b}><div className="row-between"><BandBadge band={b} /><strong>{s.bands[b]}</strong></div><div className={`distribution-bar ${b}`}><span style={{ width: `${s.submittedCount ? s.bands[b] / s.submittedCount * 100 : 0}%` }} /></div></div>)}</div></Card>
      <Card><div className="card-heading"><h2>Average capability scores</h2><Badge>Same cohort</Badge></div><div className="aggregate-domains">{s.domains.map((d) => <div key={d.key}><div className="row-between"><span>{d.name}</span><strong>{d.average.toFixed(1)}%</strong></div><div className="score-track"><span style={{ width: `${d.average}%` }} /></div></div>)}</div></Card></div>
    <div className="section-title"><h2>Submitted companies</h2><span>{total} result{total === 1 ? '' : 's'}</span></div>
    {list.isError ? <Alert kind="error">The company list could not be loaded. <Button variant="secondary" onClick={() => void list.refetch()}>Try again</Button></Alert>
      : rows.length ? <Card className="table-card"><div className="table-wrap"><table><caption className="sr-only">Filtered effective submitted assessments</caption><thead><tr><th>Company</th><th>Overall</th><th>Maturity</th><th>Details</th></tr></thead><tbody>{rows.map((r) => <tr key={r.id}><td><Link to={`/app/organizations/${r.id}`}><strong>{r.name}</strong></Link><small className="block">{r.country} · {r.size}</small></td><td className="numeric"><strong>{r.overall?.toFixed(1)}%</strong></td><td>{r.band && <BandBadge band={r.band} />}</td><td><Link to={`/app/organizations/${r.id}`}>View company</Link></td></tr>)}</tbody></table></div>{list.data && <Pager meta={list.data.meta} onPage={setPage} />}</Card>
      : list.isFetching ? <Card><p className="muted">Loading companies…</p></Card>
      : <EmptyState title="No submitted results match these filters" action={<Button variant="secondary" onClick={() => setParams(cycleId ? { cycle: cycleId } : {})}>Clear filters</Button>}>Adjust the filters or ask a Company Champion to complete and submit an assessment.</EmptyState>}</>;
}
