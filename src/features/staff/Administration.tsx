import { useState } from 'react';
import { useForm } from 'react-hook-form';
import { zodResolver } from '@hookform/resolvers/zod';
import { z } from 'zod';
import { useCurrentUser } from '../../app/hooks';
import { Alert, Badge, Button, Card, Dialog, EmptyState, Field, Icon, PageHeading } from '../../components/ui';
import { toApiError, type ApiError } from '../../infrastructure/api';
import {
  useConfirmPasswordMutation, useConfirmTwoFactorMutation, useEnableTwoFactorMutation, useInviteStaffMutation,
  useLazyRecoveryCodesQuery, useLazyTwoFactorQrQuery, useStaffUsersQuery, useUpdateStaffAccessMutation,
} from '../../infrastructure/staffAdminApi';
import type { FetchBaseQueryError } from '@reduxjs/toolkit/query';

const roleLabel = { analyst: 'Taleed Analyst', admin: 'Super Admin' } as const;
const errOf = (e: unknown): ApiError => toApiError(e as FetchBaseQueryError);

export function Access() {
  const current = useCurrentUser();
  const { data: people = [], isLoading, isError, refetch } = useStaffUsersQuery();
  const [update, { isLoading: updating }] = useUpdateStaffAccessMutation();
  const [invite, { isLoading: inviting }] = useInviteStaffMutation();
  const [search, setSearch] = useState(''); const [open, setOpen] = useState(false);
  const [error, setError] = useState(''); const [sent, setSent] = useState('');
  const schema = z.object({ email: z.string().trim().email('Enter a valid email.').max(254), role: z.enum(['analyst', 'admin']) });
  const { register, handleSubmit, reset, setError: setFieldError, formState: { errors } } = useForm<z.infer<typeof schema>>({ resolver: zodResolver(schema), defaultValues: { email: '', role: 'analyst' } });
  const change = async (id: string, body: { active?: boolean; canExport?: boolean }) => {
    setError('');
    try { await update({ id, ...body }).unwrap(); } catch (e) { setError(errOf(e).message); }
  };
  const shown = people.filter((u) => `${u.name} ${u.email}`.toLowerCase().includes(search.toLowerCase()));
  return <><PageHeading eyebrow="SUPER ADMIN" title="People & access" description="Invite Taleed staff, pause access and control analyst exports. Public registration only creates Company Champions." actions={<Button onClick={() => { reset({ email: '', role: 'analyst' }); setSent(''); setOpen(true); }}><Icon name="users"/>Invite staff</Button>}/>
    {error && <Alert kind="error">{error}</Alert>}
    <Card className="filters compact"><Field label="Find a person" htmlFor="people-search"><input id="people-search" type="search" value={search} onChange={(e) => setSearch(e.target.value)} placeholder="Search name or email…"/></Field><span className="filter-count">{shown.length} people</span></Card>
    {isLoading ? <div className="loading-state" role="status"><span className="loader"/>Loading people…</div>
      : isError ? <EmptyState title="People could not be loaded" action={<Button onClick={() => void refetch()}>Try again</Button>}>Check your connection and try again.</EmptyState>
      : <Card className="table-card"><div className="table-wrap"><table><caption className="sr-only">Staff users and access</caption><thead><tr><th>Person</th><th>Role</th><th>Two-step verification</th><th>Portfolio exports</th><th>Access</th></tr></thead><tbody>{shown.map((u) => {
        const self = u.id === current.id;
        return <tr key={u.id}><td><strong>{u.name}</strong><small className="block">{u.email}</small></td><td>{roleLabel[u.role]}</td>
          <td><Badge tone={u.twoFactorEnabled ? 'success' : 'amber'}>{u.twoFactorEnabled ? 'Enabled' : 'Not set up'}</Badge></td>
          <td>{u.role === 'analyst' ? <label className="toggle-label"><input type="checkbox" checked={u.canExport} disabled={updating} onChange={(e) => void change(u.id, { canExport: e.target.checked })} aria-label={`Allow portfolio exports for ${u.name}`}/>{u.canExport ? 'Allowed' : 'Not allowed'}</label> : 'Included by role'}</td>
          <td><Badge tone={u.active ? 'success' : 'neutral'}>{u.active ? 'Active' : 'Paused'}</Badge> <Button variant="ghost" disabled={self || updating} onClick={() => void change(u.id, { active: !u.active })}>{u.active ? 'Pause access' : 'Enable access'}</Button></td></tr>;
      })}</tbody></table></div></Card>}
    <p className="muted">You cannot change your own access, and the last active Super Admin cannot be paused. The server enforces these rules.</p>
    <Dialog open={open} title="Invite a staff member" onClose={() => setOpen(false)}>{sent
      ? <><Alert kind="success" title="Invitation sent">An invitation link was emailed to {sent}. It expires, and can be used once.</Alert><div className="dialog-actions"><Button onClick={() => setOpen(false)}>Done</Button></div></>
      : <form noValidate onSubmit={handleSubmit(async (values) => {
        try { await invite(values).unwrap(); setSent(values.email); }
        catch (e) { const err = errOf(e); if (err.fields?.email) setFieldError('email', { message: err.fields.email[0] }); else setFieldError('root', { message: err.message }); }
      })}><Field label="Email address *" htmlFor="staff-email" error={errors.email?.message}><input id="staff-email" type="email" autoComplete="off" {...register('email')}/></Field><Field label="Staff role *" htmlFor="staff-role" error={errors.role?.message}><select id="staff-role" {...register('role')}><option value="analyst">Taleed Analyst</option><option value="admin">Super Admin</option></select></Field>{errors.root && <Alert kind="error">{errors.root.message}</Alert>}<div className="dialog-actions"><Button variant="secondary" onClick={() => setOpen(false)}>Cancel</Button><Button type="submit" disabled={inviting}>Send invitation</Button></div></form>}</Dialog></>;
}

/** Staff must confirm two-step verification before any staff API is available (server-enforced). */
export function Security() {
  const user = useCurrentUser();
  const [confirmPassword, { isLoading: confirming }] = useConfirmPasswordMutation();
  const [enable] = useEnableTwoFactorMutation();
  const [loadQr] = useLazyTwoFactorQrQuery();
  const [confirm, { isLoading: verifying }] = useConfirmTwoFactorMutation();
  const [loadCodes] = useLazyRecoveryCodesQuery();
  const [step, setStep] = useState<'password' | 'scan' | 'codes'>('password');
  const [qr, setQr] = useState(''); const [codes, setCodes] = useState<string[]>([]);
  const [password, setPassword] = useState(''); const [code, setCode] = useState(''); const [error, setError] = useState('');
  if (user.twoFactorEnabled && step !== 'codes') return <><PageHeading eyebrow="ACCOUNT SECURITY" title="Two-step verification" description="Your account is protected with an authenticator app."/><Alert kind="success" title="Two-step verification is on">You will be asked for a code from your authenticator app each time you sign in. Keep your recovery codes somewhere safe.</Alert></>;
  return <><PageHeading eyebrow="ACCOUNT SECURITY" title="Set up two-step verification" description="Optional: add an authenticator app for extra sign-in protection."/>
    {error && <Alert kind="error">{error}</Alert>}
    <Card>{step === 'password' && <form noValidate onSubmit={async (e) => {
      e.preventDefault(); setError('');
      try {
        await confirmPassword({ password }).unwrap();
        await enable().unwrap();
        const r = await loadQr().unwrap(); setQr(r.svg); setStep('scan'); setPassword('');
      } catch (err) { setError(errOf(err).status === 422 ? 'That password is not correct.' : errOf(err).message); }
    }}><p>Confirm your password to begin.</p><Field label="Current password" htmlFor="sec-password"><input id="sec-password" type="password" autoComplete="current-password" value={password} onChange={(e) => setPassword(e.target.value)}/></Field><Button type="submit" disabled={confirming || !password}>Continue</Button></form>}
    {step === 'scan' && <form noValidate onSubmit={async (e) => {
      e.preventDefault(); setError('');
      try { await confirm({ code: code.trim() }).unwrap(); setCodes(await loadCodes().unwrap()); setStep('codes'); }
      catch (err) { setError(errOf(err).status === 422 ? 'That code is not valid. Check the time on your device and try again.' : errOf(err).message); }
    }}><p>Scan this QR code with your authenticator app, then enter the six-digit code it shows.</p>
      {/* SVG is generated by the server (Fortify/BaconQrCode) for this user only. */}
      <div className="qr-code" aria-label="Authenticator QR code" role="img" dangerouslySetInnerHTML={{ __html: qr }}/>
      <Field label="Authentication code" htmlFor="sec-code"><input id="sec-code" className="code-input" inputMode="numeric" autoComplete="one-time-code" maxLength={6} value={code} onChange={(e) => setCode(e.target.value)}/></Field><Button type="submit" disabled={verifying || !/^\d{6}$/.test(code.trim())}>Turn on two-step verification</Button></form>}
    {step === 'codes' && <><Alert kind="success" title="Two-step verification is on">Save these recovery codes now. Each can be used once if you lose your device. They will not be shown again here.</Alert><ul className="recovery-codes">{codes.map((c) => <li key={c}><code>{c}</code></li>)}</ul></>}
    </Card></>;
}
