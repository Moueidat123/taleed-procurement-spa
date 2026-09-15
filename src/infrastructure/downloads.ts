import type { Assessment, Database, User } from '../domain/types';
import { canExport, canReadAssessment, effectiveSubmissions } from '../domain/policies';
import { BAND_LABELS } from '../domain/scoring';
import { safeSpreadsheetText, toCsv } from '../domain/csv';

export function downloadBlob(filename: string, blob: Blob): void {
  const url=URL.createObjectURL(blob); const anchor=document.createElement('a');
  anchor.href=url; anchor.download=filename; document.body.appendChild(anchor); anchor.click(); anchor.remove();
  window.setTimeout(()=>URL.revokeObjectURL(url),1000);
}
export function downloadJson(filename: string, value: unknown): void {
  downloadBlob(filename,new Blob([JSON.stringify(value,null,2)],{type:'application/json;charset=utf-8'}));
}
export function exportSnapshot(assessment: Assessment, user: User): void {
  if(!canReadAssessment(user,assessment)||!assessment.snapshot)throw new Error('This submitted result is not available.');
  downloadJson(`taleed-result-${assessment.id}.json`,{notice:'Synthetic local demonstration; not a certification.',assessmentId:assessment.id,cycleId:assessment.cycleId,revision:assessment.revision,...assessment.snapshot});
}
export async function preparePortfolioExport(db: Database, user: User, rows: Assessment[], format: 'csv'|'xlsx'): Promise<Blob> {
  if(!user.active||!user.verified||!canExport(user))throw new Error('Portfolio export permission is required.');
  if(!rows.length)throw new Error('There are no submitted results to export.');
  const effective=new Set(effectiveSubmissions(db).map((a)=>a.id));
  if(rows.some((a)=>!effective.has(a.id)||!canReadAssessment(user,a)||!a.snapshot))throw new Error('Export only authorized, effective submitted revisions.');
  if(new Set(rows.map((a)=>`${a.cycleId}/${a.frameworkVersion}`)).size!==1)throw new Error('Choose a single cycle and framework before exporting.');
  const values: (string|number)[][]=[['Company','Country','Sector','Company size','Cycle','Framework','Revision','Respondent','Respondent role','Submitted at','Overall (%)','Maturity','Category (%)','Spend (%)','Sourcing (%)','SRM (%)']];
  rows.forEach((a)=>{const s=a.snapshot;if(!s)return;values.push([s.organization.name,s.organization.country,s.organization.sector,s.organization.size,db.cycles[a.cycleId]?.label??a.cycleId,a.frameworkVersion,a.revision,s.respondent.name,s.respondent.jobTitle,s.submittedAt,s.result.overall,BAND_LABELS[s.result.band],...s.result.domains.map((d)=>d.score)]);});
  if(format==='csv')return new Blob([toCsv(values)],{type:'text/csv;charset=utf-8'});
  const {default:ExcelJS}=await import('exceljs');
  const workbook=new ExcelJS.Workbook(); workbook.creator='Taleed Procurement Prototype';
  const sheet=workbook.addWorksheet('Effective submissions');
  values.forEach((row)=>sheet.addRow(row.map((v)=>typeof v==='number'?v:safeSpreadsheetText(v))));
  sheet.views=[{state:'frozen',ySplit:1}];sheet.autoFilter={from:{row:1,column:1},to:{row:values.length,column:16}};
  sheet.getRow(1).font={bold:true,color:{argb:'FFFFFFFF'}};sheet.getRow(1).fill={type:'pattern',pattern:'solid',fgColor:{argb:'FF0A1D5C'}};
  sheet.columns.forEach((column,i)=>{column.width=i===0?34:i===9?28:22;});
  const notice=workbook.addWorksheet('Read me');
  notice.addRow(['Taleed Procurement Self-Assessment — synthetic local prototype']);
  notice.addRow(['One effective submitted revision per company in the selected cycle/framework. Drafts and superseded revisions are excluded.']);
  notice.addRow([`Denominator: ${rows.length} participating companies with effective submitted results.`]);
  notice.addRow(['Self-reported results; not accreditation, independent verification or a market benchmark.']);
  notice.addRow(['Framework attribution: Aramco Taleed & Roland Berger.']);notice.getColumn(1).width=110;
  const buffer=await workbook.xlsx.writeBuffer();const bytes=new Uint8Array(buffer);const copy=new Uint8Array(bytes.length);copy.set(bytes);
  return new Blob([copy.buffer],{type:'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'});
}
