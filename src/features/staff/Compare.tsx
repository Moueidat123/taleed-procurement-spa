import { useEffect } from 'react';
import { useSearchParams } from 'react-router-dom';
import { Alert, BandBadge, Card, EmptyState, PageHeading } from '../../components/ui';
import { useCompareMutation, useStaffOrganizationsQuery } from '../../infrastructure/staffApi';
import { toApiError } from '../../infrastructure/api';
import type { FetchBaseQueryError } from '@reduxjs/toolkit/query';

/** Server-side comparison of 2–4 companies' effective submissions in one cycle. */
export default function Compare() {
  const [params, setParams] = useSearchParams();
  const cycleId = params.get('cycle') ?? undefined;
  const ids = (params.get('ids') ?? '').split(',').filter(Boolean).slice(0, 4);
  const list = useStaffOrganizationsQuery({ cycleId, stage: 'submitted', perPage: 100 });
  const [compare, result] = useCompareMutation();
  const key = ids.join(',');
  useEffect(() => { if (ids.length >= 2) void compare({ organizationIds: ids, ...(cycleId ? { cycleId } : {}) }); }, [key, cycleId]); // eslint-disable-line react-hooks/exhaustive-deps
  const toggle = (id: string, checked: boolean) => {
    const next = checked ? [...ids, id].slice(0, 4) : ids.filter((x) => x !== id);
    const p = new URLSearchParams(params); if (next.length) p.set('ids', next.join(',')); else p.delete('ids'); setParams(p);
  };
  const companies = ids.length >= 2 ? result.data?.companies ?? [] : [];
  const error = ids.length >= 2 && result.isError ? toApiError(result.error as FetchBaseQueryError).message : '';
  return <><PageHeading eyebrow="LIKE-FOR-LIKE COMPARISON" title="Compare company results" description="Select two to four companies with effective submissions to compare side by side." />
    <Card>{list.isLoading ? <p className="muted">Loading companies…</p> : list.isError ? <Alert kind="error">The company list could not be loaded.</Alert> :
      <div className="company-picker">{(list.data?.data ?? []).map((o) => <label className={`company-choice ${ids.includes(o.id) ? 'selected' : ''}`} key={o.id}><input type="checkbox" checked={ids.includes(o.id)} disabled={!ids.includes(o.id) && ids.length >= 4} onChange={(e) => toggle(o.id, e.target.checked)} /><span><strong>{o.name}</strong></span></label>)}</div>}
      {(list.data?.meta.total ?? 0) > 100 && <small className="muted">Showing the first 100 companies alphabetically.</small>}</Card>
    <Alert>These comparisons show self-reported results from participating companies. They are staff-only and are not a public ranking or an external benchmark.</Alert>
    {error && <Alert kind="error">{error}</Alert>}
    {ids.length < 2 ? <EmptyState title="Choose at least two companies">Select up to four companies above to build a side-by-side comparison.</EmptyState>
      : result.isLoading ? <Card><p className="muted">Building comparison…</p></Card>
      : companies.length > 0 && <Card className="table-card"><div className="table-wrap"><table className="comparison-table"><caption className="sr-only">Like-for-like company capability comparison</caption>
        <thead><tr><th>Measure</th>{companies.map((c) => <th key={c.id}>{c.name}</th>)}</tr></thead>
        <tbody><tr className="comparison-total"><th>Overall maturity</th>{companies.map((c) => <td key={c.id}><strong>{c.overall.toFixed(1)}%</strong><BandBadge band={c.band} /></td>)}</tr>
          {(companies[0]?.domains ?? []).map((d, i) => <tr key={d.key}><th>{d.name}</th>{companies.map((c) => { const r = c.domains[i]; return <td key={c.id}>{r && <><strong>{r.score.toFixed(1)}%</strong><div><BandBadge band={r.band} /></div></>}</td>; })}</tr>)}</tbody></table></div></Card>}
  </>;
}
