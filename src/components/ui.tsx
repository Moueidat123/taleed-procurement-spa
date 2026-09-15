import { cloneElement, isValidElement, useEffect, useId, useRef, type ButtonHTMLAttributes, type ReactNode } from 'react';
import { Link } from 'react-router-dom';
import type { Band } from '../domain/types';
import { BAND_LABELS } from '../domain/scoring';

export function Icon({ name, size = 20 }: { name: string; size?: number }) {
  const paths: Record<string, ReactNode> = {
    grid: <><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></>,
    chart: <><path d="M4 3v17h17M8 16v-5m5 5V7m5 9V4"/></>,
    file: <><path d="M14 2H5v20h14V7l-5-5zM14 2v6h5M8 12h8M8 16h8"/></>,
    company: <><path d="M4 21V4h10v17M14 10h6v11M2 21h20M7 8h4M7 12h4M7 16h4M17 14h1M17 18h1"/></>,
    arrow: <path d="M4 12h16m-6-6 6 6-6 6"/>,
    back: <path d="M20 12H4m6-6-6 6 6 6"/>,
    check: <path d="m5 12 4 4L19 6"/>,
    info: <><circle cx="12" cy="12" r="9"/><path d="M12 11v6M12 7h.01"/></>,
    lock: <><rect x="5" y="10" width="14" height="11" rx="2"/><path d="M8 10V7a4 4 0 0 1 8 0v3M12 14v3"/></>,
    clock: <><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></>,
    users: <><circle cx="9" cy="7" r="3"/><path d="M3 21v-3a6 6 0 0 1 12 0v3M17 4a3 3 0 0 1 0 6m1 4a5 5 0 0 1 3 4v3"/></>,
    download: <path d="M12 3v12m-5-5 5 5 5-5M4 16v5h16v-5"/>,
    menu: <path d="M3 6h18M3 12h18M3 18h18"/>,
    close: <path d="m6 6 12 12M6 18 18 6"/>,
    settings: <><circle cx="12" cy="12" r="4"/><path d="M12 2v3m0 14v3M2 12h3m14 0h3M5 5l2 2m10 10 2 2M5 19l2-2M17 7l2-2"/></>,
    leaf: <><path d="M20 3C7 2 2 9 7 16c7 5 14 0 13-13zM4 21 16 8"/></>,
    logout: <path d="M10 4H4v16h6M9 12h12m-4-4 4 4-4 4"/>,
    search: <><circle cx="10" cy="10" r="6"/><path d="m15 15 6 6"/></>,
    calendar: <><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M7 2v6m10-6v6M3 11h18"/></>,
    refresh: <><path d="M20 8a8 8 0 1 0 0 9M20 3v5h-5"/></>,
    flag: <><path d="M5 22V3c5-5 9 5 14 0v10c-5 5-9-5-14 0"/></>,
  };
  return <svg width={size} height={size} viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.65" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true">{paths[name] ?? paths.file}</svg>;
}
export function Button({ variant = 'primary', className = '', children, ...props }: ButtonHTMLAttributes<HTMLButtonElement> & { variant?: 'primary' | 'secondary' | 'ghost' | 'danger'; children: ReactNode }) {
  return <button type="button" className={`button button-${variant} ${className}`} {...props}>{children}</button>;
}
export function LinkButton({ to, children, variant = 'primary', className = '' }: { to: string; children: ReactNode; variant?: 'primary' | 'secondary' | 'ghost'; className?: string }) {
  return <Link className={`button button-${variant} ${className}`} to={to}>{children}</Link>;
}
export function Card({ children, className = '' }: { children: ReactNode; className?: string }) { return <section className={`card ${className}`}>{children}</section>; }
export function Badge({ children, tone = 'neutral' }: { children: ReactNode; tone?: string }) { return <span className={`badge badge-${tone}`}>{children}</span>; }
export function BandBadge({ band }: { band: Band }) { return <Badge tone={band}>{BAND_LABELS[band]}</Badge>; }
export function Alert({ children, kind = 'info', title }: { children: ReactNode; kind?: 'info' | 'warning' | 'error' | 'success'; title?: string }) {
  return <div className={`alert alert-${kind}`} role={kind === 'error' ? 'alert' : 'note'}><Icon name={kind === 'success' ? 'check' : 'info'}/><div>{title && <strong>{title}</strong>}<div>{children}</div></div></div>;
}
export function PageHeading({ title, eyebrow, description, actions }: { title: string; eyebrow?: string; description?: string; actions?: ReactNode }) {
  useEffect(() => { document.title = `${title} | Taleed Procurement`; }, [title]);
  return <header className="page-heading"><div>{eyebrow && <p className="eyebrow">{eyebrow}</p>}<h1 tabIndex={-1}>{title}</h1>{description && <p className="lede">{description}</p>}</div>{actions && <div className="heading-actions">{actions}</div>}</header>;
}
export function Progress({ value, total = 40, label = 'Assessment completion' }: { value: number; total?: number; label?: string }) {
  return <div className="progress" role="progressbar" aria-label={label} aria-valuenow={value} aria-valuemin={0} aria-valuemax={total}><span style={{ width: `${Math.max(0, Math.min(100, value / total * 100))}%` }}/></div>;
}
export function Metric({ label, value, foot, icon }: { label: string; value: ReactNode; foot: string; icon: string }) {
  return <Card className="metric"><div className="metric-top"><span>{label}</span><span className="icon-box"><Icon name={icon}/></span></div><strong>{value}</strong><small>{foot}</small></Card>;
}
export function EmptyState({ title, children, action }: { title: string; children: ReactNode; action?: ReactNode }) {
  return <Card className="empty-state"><span className="empty-icon"><Icon name="file" size={32}/></span><h2>{title}</h2><p>{children}</p>{action}</Card>;
}
export function Field({ label, htmlFor, error, hint, children }: { label: string; htmlFor: string; error?: string; hint?: string; children: ReactNode }) {
  return <div className={`field ${error ? 'has-error' : ''}`}><label htmlFor={htmlFor}>{label}</label>{isValidElement<{ 'aria-describedby'?: string; 'aria-invalid'?: boolean }>(children) ? cloneElement(children, { 'aria-describedby': [hint ? `${htmlFor}-hint` : '', error ? `${htmlFor}-error` : ''].filter(Boolean).join(' ') || undefined, 'aria-invalid': !!error }) : children}{hint && <small id={`${htmlFor}-hint`}>{hint}</small>}{error && <span className="field-error" id={`${htmlFor}-error`}>{error}</span>}</div>;
}
export function Dialog({ open, title, children, onClose }: { open: boolean; title: string; children: ReactNode; onClose: () => void }) {
  const ref = useRef<HTMLDialogElement>(null); const id = useId();
  useEffect(() => { const dialog = ref.current; if (open && dialog && !dialog.open) dialog.showModal(); if (!open && dialog?.open) dialog.close(); }, [open]);
  return <dialog ref={ref} aria-labelledby={id} onCancel={onClose} onClose={onClose} onClick={(event) => { if (event.target === event.currentTarget) onClose(); }}><div className="dialog-inner"><header><h2 id={id}>{title}</h2><Button variant="ghost" aria-label="Close dialog" onClick={onClose}><Icon name="close"/></Button></header>{children}</div></dialog>;
}
export function ScoreRing({ score, label }: { score: number; label: string }) {
  return <div className="score-ring" role="img" aria-label={`${label}: ${score.toFixed(1)} percent`} style={{ background: `conic-gradient(var(--blue) ${score * 3.6}deg, var(--blue-tint) 0deg)` }}><div><strong>{score.toFixed(1)}<span>%</span></strong><small>{label}</small></div></div>;
}
export const formatDate = (value: string | null): string => value ? new Intl.DateTimeFormat('en-GB', { day: '2-digit', month: 'short', year: 'numeric', ...(value.length === 10 ? { timeZone: 'UTC' } : {}) }).format(new Date(value)) : 'Not submitted';
