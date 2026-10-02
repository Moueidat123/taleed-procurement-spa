import { useEffect, useState, type ReactNode } from 'react';
import { useForm, type FieldValues, type Path, type UseFormSetError } from 'react-hook-form';
import { zodResolver } from '@hookform/resolvers/zod';
import { z } from 'zod';
import { Link, Navigate, useNavigate, useSearchParams } from 'react-router-dom';
import type { FetchBaseQueryError } from '@reduxjs/toolkit/query';
import { PublicHeader, GlobalNotices } from '../../components/Layout';
import { Alert, Badge, Button, Card, Field, Icon, LinkButton } from '../../components/ui';
import { signedIn, useAppDispatch } from '../../app/store';
import { workspacePath } from '../../app/hooks';
import { toApiError } from '../../infrastructure/api';
import {
  useAcceptInvitationMutation, useConfirmVerificationMutation, useForgotPasswordMutation, useLoginMutation,
  useMeQuery, useRegisterMutation, useResetPasswordMutation, useSendVerificationMutation, useTwoFactorMutation,
} from '../../infrastructure/authApi';

const email = z.string().trim().email('Enter a valid email address.').max(254);
const password = z.string().min(12, 'Use at least 12 characters.').max(128).regex(/[A-Z]/, 'Include an uppercase letter.').regex(/[a-z]/, 'Include a lowercase letter.').regex(/[0-9]/, 'Include a number.');

/** Show the server's authoritative message; map field errors onto the form where the names match. */
function applyServerError<T extends FieldValues>(error: unknown, setError: UseFormSetError<T>, map: Record<string, Path<T>> = {}): void {
  const e = toApiError(error as FetchBaseQueryError);
  let placed = false;
  for (const [field, messages] of Object.entries(e.fields ?? {})) {
    const target = map[field] ?? (field as Path<T>);
    if (messages[0]) { setError(target, { message: messages[0] }); placed = true; }
  }
  if (!placed || e.status !== 422) setError('root' as Path<T>, { message: e.message });
}

function AuthShell({ title, subtitle, children }: { title: string; subtitle: string; children: ReactNode }) {
  return <div className="public-page"><PublicHeader/><GlobalNotices/><main className="auth-layout"><div className="auth-story"><Badge tone="amber">BUILD WITH CONFIDENCE</Badge><h1>Know where you stand.<br/>Choose where to grow.</h1><p>One guided assessment. Four capability areas. A practical starting point for your procurement journey.</p><div className="auth-points"><span><Icon name="check"/>Save and resume at any time</span><span><Icon name="check"/>Review every answer before submitting</span><span><Icon name="check"/>Receive domain-specific recommendations</span></div></div><Card className="auth-card"><h2>{title}</h2><p className="muted">{subtitle}</p>{children}</Card></main></div>;
}
function RootError({ message }: { message?: string }) { return message ? <Alert kind="error">{message}</Alert> : null; }

export function Login() {
  const { data: me } = useMeQuery();
  const [login, { isLoading }] = useLoginMutation();
  const [twoFactor, { isLoading: verifying }] = useTwoFactorMutation();
  const [challenge, setChallenge] = useState(false);
  const [useRecovery, setUseRecovery] = useState(false);
  const dispatch = useAppDispatch(); const navigate = useNavigate();
  const schema = z.object({ email, password: z.string().min(1, 'Enter your password.') });
  const form = useForm<z.infer<typeof schema>>({ resolver: zodResolver(schema) });
  const codeSchema = z.object({ code: z.string().trim().min(6, 'Enter the code.').max(64) });
  const codeForm = useForm<z.infer<typeof codeSchema>>({ resolver: zodResolver(codeSchema) });
  if (me) return <Navigate to={me.verified ? workspacePath(me) : '/verify'} replace/>;
  if (challenge) {
    return <AuthShell title="Two-step verification" subtitle={useRecovery ? 'Enter one of your recovery codes.' : 'Enter the six-digit code from your authenticator app.'}>
      <form key="two-factor" noValidate onSubmit={codeForm.handleSubmit(async ({ code }) => {
        try { await twoFactor(useRecovery ? { recovery_code: code } : { code }).unwrap(); dispatch(signedIn()); }
        catch (e) { applyServerError(e, codeForm.setError, { recovery_code: 'code' }); }
      })}><Field label={useRecovery ? 'Recovery code' : 'Authentication code'} htmlFor="tfa-code" error={codeForm.formState.errors.code?.message}><input id="tfa-code" className={useRecovery ? undefined : 'code-input'} inputMode={useRecovery ? 'text' : 'numeric'} autoComplete="one-time-code" autoFocus {...codeForm.register('code')}/></Field><RootError message={codeForm.formState.errors.root?.message}/><Button type="submit" disabled={verifying} className="full-width">Verify and sign in</Button></form>
      <Button variant="ghost" onClick={() => { setUseRecovery(!useRecovery); codeForm.reset(); }}>{useRecovery ? 'Use an authenticator code' : 'Use a recovery code'}</Button>
    </AuthShell>;
  }
  return <AuthShell title="Welcome back" subtitle="Sign in to continue your assessment or open your workspace.">
    <form noValidate onSubmit={form.handleSubmit(async (values) => {
      try {
        const r = await login(values).unwrap();
        if (r.twoFactor) setChallenge(true); else { dispatch(signedIn()); navigate('/app', { replace: true }); }
      } catch (e) { applyServerError(e, form.setError); }
    })}><Field label="Email address" htmlFor="login-email" error={form.formState.errors.email?.message}><input id="login-email" type="email" autoComplete="username" {...form.register('email')} aria-invalid={!!form.formState.errors.email}/></Field><Field label="Password" htmlFor="login-password" error={form.formState.errors.password?.message}><input id="login-password" type="password" autoComplete="current-password" {...form.register('password')} aria-invalid={!!form.formState.errors.password}/></Field><RootError message={form.formState.errors.root?.message}/><Button type="submit" disabled={isLoading} className="full-width">Sign in <Icon name="arrow"/></Button></form>
    <div className="auth-links"><Link to="/forgot-password">Forgot password?</Link><Link to="/register">Create an account</Link></div>
  </AuthShell>;
}

export function Register() {
  const schema = z.object({ name: z.string().trim().min(2, 'Enter your full name.').max(160), email, jobTitle: z.string().trim().min(2, 'Enter your role.').max(160), password, confirm: z.string(), consent: z.boolean().refine(Boolean, 'Accept the privacy notice to continue.') }).refine((d) => d.password === d.confirm, { message: 'Passwords do not match.', path: ['confirm'] });
  const { register, handleSubmit, setError, formState: { errors } } = useForm<z.infer<typeof schema>>({ resolver: zodResolver(schema), defaultValues: { consent: false } });
  const [registerUser, { isLoading }] = useRegisterMutation(); const navigate = useNavigate(); const dispatch = useAppDispatch();
  return <AuthShell title="Create your account" subtitle="An identified assessment for your company — not an anonymous survey."><form noValidate onSubmit={handleSubmit(async (v) => {
    try {
      await registerUser({ name: v.name, email: v.email, job_title: v.jobTitle, password: v.password, password_confirmation: v.confirm, consent: v.consent }).unwrap();
      dispatch(signedIn()); navigate('/verify');
    } catch (e) { applyServerError(e, setError, { job_title: 'jobTitle', password_confirmation: 'confirm' }); }
  })}><Field label="Full name" htmlFor="register-name" error={errors.name?.message}><input id="register-name" autoComplete="name" {...register('name')} aria-invalid={!!errors.name}/></Field><Field label="Work email" htmlFor="register-email" error={errors.email?.message}><input id="register-email" type="email" autoComplete="email" {...register('email')} aria-invalid={!!errors.email}/></Field><Field label="Job title" htmlFor="register-title" error={errors.jobTitle?.message}><input id="register-title" placeholder="e.g. Head of Procurement" autoComplete="organization-title" {...register('jobTitle')} aria-invalid={!!errors.jobTitle}/></Field><div className="form-grid"><Field label="Password" htmlFor="register-password" error={errors.password?.message} hint="12+ characters; uppercase, lowercase and a number."><input id="register-password" type="password" autoComplete="new-password" {...register('password')} aria-invalid={!!errors.password}/></Field><Field label="Confirm password" htmlFor="register-confirm" error={errors.confirm?.message}><input id="register-confirm" type="password" autoComplete="new-password" {...register('confirm')} aria-invalid={!!errors.confirm}/></Field></div><label className="checkbox"><input type="checkbox" {...register('consent')}/><span>I have read the privacy notice and agree to my details being used for this assessment programme.</span></label>{errors.consent && <span className="field-error">{errors.consent.message}</span>}<RootError message={errors.root?.message}/><Button type="submit" disabled={isLoading} className="full-width">Create account <Icon name="arrow"/></Button></form><p className="auth-bottom">Already registered? <Link to="/login">Sign in</Link></p></AuthShell>;
}

export function Verify() {
  const { data: user, isLoading: loading } = useMeQuery();
  const [confirm, { isLoading }] = useConfirmVerificationMutation();
  const [send, { isLoading: sending }] = useSendVerificationMutation();
  const navigate = useNavigate();
  const [cooldown, setCooldown] = useState(0); const [notice, setNotice] = useState('');
  useEffect(() => { if (!cooldown) return; const t = window.setTimeout(() => setCooldown(cooldown - 1), 1000); return () => window.clearTimeout(t); }, [cooldown]);
  const schema = z.object({ code: z.string().regex(/^\d{6}$/, 'Enter the six-digit code.') });
  const { register, handleSubmit, setError, formState: { errors } } = useForm<z.infer<typeof schema>>({ resolver: zodResolver(schema) });
  if (loading) return null;
  if (!user) return <Navigate to="/login" replace/>;
  if (user.verified) return <Navigate to={workspacePath(user)} replace/>;
  return <AuthShell title="Verify your email" subtitle={`We sent a six-digit code to ${user.email}. It expires after a short time.`}><span className="auth-feature-icon"><Icon name="lock" size={32}/></span><form noValidate onSubmit={handleSubmit(async ({ code }) => {
    try { const u = await confirm({ code }).unwrap(); navigate(u.role === 'champion' ? '/app/profile' : workspacePath(u)); }
    catch (e) { applyServerError(e, setError); }
  })}><Field label="Verification code" htmlFor="verify-code" error={errors.code?.message}><input className="code-input" id="verify-code" inputMode="numeric" maxLength={6} autoComplete="one-time-code" {...register('code')}/></Field><RootError message={errors.root?.message}/><Button disabled={isLoading} type="submit" className="full-width">Verify and continue</Button></form><Button variant="ghost" disabled={cooldown > 0 || sending} onClick={async () => {
    setCooldown(60);
    try { await send().unwrap(); setNotice('If your account is waiting for verification, a new code is on its way.'); }
    catch (e) { setNotice(toApiError(e as FetchBaseQueryError).message); }
  }}>Send a new code {cooldown > 0 && `(${cooldown}s)`}</Button>{notice && <p role="status" className="muted">{notice}</p>}</AuthShell>;
}

export function ForgotPassword() {
  const [sent, setSent] = useState(false);
  const [forgot, { isLoading }] = useForgotPasswordMutation();
  const schema = z.object({ email });
  const { register, handleSubmit, setError, formState: { errors } } = useForm<z.infer<typeof schema>>({ resolver: zodResolver(schema) });
  return <AuthShell title="Reset access" subtitle="We will email a reset link if the address belongs to an active account.">{sent
    ? <Alert kind="success" title="Check your email">If an account exists for that address, a reset link is on its way. The link expires after 60 minutes.</Alert>
    : <form noValidate onSubmit={handleSubmit(async (v) => { try { await forgot(v).unwrap(); setSent(true); } catch (e) { applyServerError(e, setError); } })}><Field label="Email address" htmlFor="forgot-email" error={errors.email?.message}><input id="forgot-email" type="email" autoComplete="email" {...register('email')}/></Field><RootError message={errors.root?.message}/><Button type="submit" disabled={isLoading} className="full-width">Send reset link</Button></form>}
    <p className="auth-bottom"><Link to="/login">Back to sign in</Link></p></AuthShell>;
}

export function ResetPassword() {
  const [params] = useSearchParams();
  const token = params.get('token') ?? ''; const address = params.get('email') ?? '';
  const [done, setDone] = useState(false);
  const [reset, { isLoading }] = useResetPasswordMutation();
  const schema = z.object({ password, confirm: z.string() }).refine((v) => v.password === v.confirm, { message: 'Passwords do not match.', path: ['confirm'] });
  const { register, handleSubmit, setError, formState: { errors } } = useForm<z.infer<typeof schema>>({ resolver: zodResolver(schema) });
  if (!token || !address) return <AuthShell title="Reset link incomplete" subtitle="Open the link from your email again, or request a new one."><LinkButton to="/forgot-password">Request a new link</LinkButton></AuthShell>;
  return <AuthShell title="Choose a new password" subtitle={`For ${address}`}>{done
    ? <><Alert kind="success">Your password has been changed. Other sessions have been signed out.</Alert><LinkButton to="/login">Sign in</LinkButton></>
    : <form noValidate onSubmit={handleSubmit(async (v) => {
        try { await reset({ token, email: address, password: v.password, password_confirmation: v.confirm }).unwrap(); setDone(true); }
        catch (e) { applyServerError(e, setError, { password_confirmation: 'confirm', email: 'root' as never, token: 'root' as never }); }
      })}><Field label="New password" htmlFor="reset-password" error={errors.password?.message} hint="12+ characters; uppercase, lowercase and a number."><input id="reset-password" type="password" autoComplete="new-password" {...register('password')}/></Field><Field label="Confirm new password" htmlFor="reset-confirm" error={errors.confirm?.message}><input id="reset-confirm" type="password" autoComplete="new-password" {...register('confirm')}/></Field><RootError message={errors.root?.message}/><Button type="submit" disabled={isLoading}>Change password</Button></form>}</AuthShell>;
}

export function AcceptInvitation() {
  const [params] = useSearchParams(); const token = params.get('token') ?? '';
  const [accept, { isLoading }] = useAcceptInvitationMutation(); const navigate = useNavigate(); const dispatch = useAppDispatch();
  const schema = z.object({ name: z.string().trim().min(2, 'Enter your full name.').max(160), password, confirm: z.string() }).refine((v) => v.password === v.confirm, { message: 'Passwords do not match.', path: ['confirm'] });
  const { register, handleSubmit, setError, formState: { errors } } = useForm<z.infer<typeof schema>>({ resolver: zodResolver(schema) });
  if (token.length !== 64) return <AuthShell title="Invitation link incomplete" subtitle="Open the link from your invitation email again, or ask a Super Admin to resend it."><LinkButton to="/login">Go to sign in</LinkButton></AuthShell>;
  return <AuthShell title="Accept your invitation" subtitle="Set your name and password. You will set up two-step verification next."><form noValidate onSubmit={handleSubmit(async (v) => {
    try { await accept({ token, name: v.name, password: v.password, password_confirmation: v.confirm }).unwrap(); dispatch(signedIn()); navigate('/app/security'); }
    catch (e) { applyServerError(e, setError, { password_confirmation: 'confirm', token: 'root' as never }); }
  })}><Field label="Full name" htmlFor="invite-name" error={errors.name?.message}><input id="invite-name" autoComplete="name" {...register('name')}/></Field><Field label="Password" htmlFor="invite-password" error={errors.password?.message} hint="12+ characters; uppercase, lowercase and a number."><input id="invite-password" type="password" autoComplete="new-password" {...register('password')}/></Field><Field label="Confirm password" htmlFor="invite-confirm" error={errors.confirm?.message}><input id="invite-confirm" type="password" autoComplete="new-password" {...register('confirm')}/></Field><RootError message={errors.root?.message}/><Button type="submit" disabled={isLoading} className="full-width">Accept invitation</Button></form></AuthShell>;
}
