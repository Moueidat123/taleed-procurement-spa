import { Button } from '../../components/ui';

/** Server-side pagination controls. */
export function Pager({ meta, onPage }: { meta: { page: number; lastPage: number; total: number }; onPage: (page: number) => void }) {
  if (meta.lastPage <= 1) return null;
  return <nav className="row-between pager" aria-label="Pagination"><Button variant="secondary" disabled={meta.page <= 1} onClick={() => onPage(meta.page - 1)}>Previous</Button><span>Page {meta.page} of {meta.lastPage} · {meta.total} total</span><Button variant="secondary" disabled={meta.page >= meta.lastPage} onClick={() => onPage(meta.page + 1)}>Next</Button></nav>;
}
