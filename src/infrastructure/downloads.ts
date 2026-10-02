import { BAND_LABELS } from '../domain/scoring';
import { safeSpreadsheetText, toCsv } from '../domain/csv';
import type { ExportRow, SubmissionView } from './staffApi';

export function downloadBlob(filename: string, blob: Blob): void {
  const url = URL.createObjectURL(blob); const anchor = document.createElement('a');
  anchor.href = url; anchor.download = filename; document.body.appendChild(anchor); anchor.click(); anchor.remove();
  window.setTimeout(() => URL.revokeObjectURL(url), 1000);
}
export function exportSnapshot(view: SubmissionView): void {
  const body = { notice: 'Self-reported result; not a certification.', revisionId: view.revisionId, checksum: view.checksum, ...view.snapshot };
  downloadBlob(`taleed-result-${view.revisionId}.json`, new Blob([JSON.stringify(body, null, 2)], { type: 'application/json;charset=utf-8' }));
}
/** Builds the file from server-authorized, server-filtered rows (effective submissions only). */
export async function buildPortfolioExport(rows: ExportRow[], frameworkVersion: string | null, format: 'csv' | 'xlsx'): Promise<Blob> {
  if (!rows.length) throw new Error('There are no submitted results to export.');
  const domainNames = rows[0]?.domains.map((d) => `${d.name} (%)`) ?? [];
  const values: (string | number)[][] = [['Company', 'Country', 'Company size', 'Framework', 'Revision', 'Submitted at', 'Overall (%)', 'Maturity', ...domainNames]];
  rows.forEach((r) => values.push([r.company, r.country, r.size, frameworkVersion ?? '', r.revisionNumber, r.submittedAt ?? '', r.overall, BAND_LABELS[r.band], ...r.domains.map((d) => d.score)]));
  if (format === 'csv') return new Blob([toCsv(values)], { type: 'text/csv;charset=utf-8' });
  const { default: ExcelJS } = await import('exceljs');
  const workbook = new ExcelJS.Workbook(); workbook.creator = 'Taleed Procurement';
  const sheet = workbook.addWorksheet('Effective submissions');
  values.forEach((row) => sheet.addRow(row.map((v) => (typeof v === 'number' ? v : safeSpreadsheetText(v)))));
  sheet.views = [{ state: 'frozen', ySplit: 1 }];
  sheet.autoFilter = { from: { row: 1, column: 1 }, to: { row: values.length, column: values[0]?.length ?? 1 } };
  sheet.getRow(1).font = { bold: true, color: { argb: 'FFFFFFFF' } };
  sheet.getRow(1).fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FF0A1D5C' } };
  sheet.columns.forEach((c, i) => { c.width = i === 0 ? 34 : 22; });
  const notice = workbook.addWorksheet('Read me');
  ['Taleed Procurement Self-Assessment — portfolio export',
    'One effective submitted revision per company in the selected cycle. Drafts, earlier revisions and test companies are excluded.',
    `Denominator: ${rows.length} companies with effective submitted results.`,
    'Self-reported results; not accreditation, independent verification or a market benchmark.',
    'Framework attribution: Aramco Taleed & Roland Berger.'].forEach((t) => notice.addRow([t]));
  notice.getColumn(1).width = 110;
  const bytes = new Uint8Array(await workbook.xlsx.writeBuffer()); const copy = new Uint8Array(bytes.length); copy.set(bytes);
  return new Blob([copy.buffer], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' });
}
