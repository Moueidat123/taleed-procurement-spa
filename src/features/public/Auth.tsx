import { useEffect, useState, type ReactNode } from 'react';
import { useForm } from 'react-hook-form';
import { zodResolver } from '@hookform/resolvers/zod';
import { z } from 'zod';
import { Link, Navigate, useNavigate } from 'react-router-dom';
import { PublicHeader, GlobalNotices } from '../../components/Layout';
import { Alert, Badge, Button, Card, Field, Icon, LinkButton } from '../../components/ui';
import { useCommand, useDatabase } from '../../app/hooks';
import { selectUser, sessionChanged, useAppDispatch, useAppSelector } from '../../app/store';
import { DEMO_ACCOUNTS, DEMO_PASSWORD } from '../../domain/seed';

const email = z.string().trim().email('Enter a valid email address.').max(254);
const password = z.string().min(12, 'Use at least 12 characters.').max(128).regex(/[A-Z]/, 'Include an uppercase letter.').regex(/[a-z]/, 'Include a lowercase letter.').regex(/[0-9]/, 'Include a number.');
function AuthShell({ title, subtitle, children }: { title: string; subtitle: string; children: ReactNode }) {
  return <div className="public-page"><PublicHeader/><GlobalNotices/><main className="auth-layout"><div className="auth-story"><Badge tone="amber">BUILD WITH CONFIDENCE</Badge><h1>Know where you stand.<br/>Choose where to grow.</h1><p>One guided assessment. Four capability areas. A practical starting point for your procurement journey.</p><div className="auth-points"><span><Icon name="check"/>Save and resume in this browser</span><span><Icon name="check"/>Review every answer before submitting</span><span><Icon name="check"/>Receive domain-specific recommendations</span></div><small>Local demonstration · synthetic data only</small></div><Card className="auth-card"><h2>{title}</h2><p className="muted">{subtitle}</p>{children}</Card></main></div>;
}
export function Login() {
  const schema = z.object({ email, password: z.string().min(1, 'Enter the shared demo password.') });
  const { register, handleSubmit, formState: { errors }, setError } = useForm<z.infer<typeof schema>>({ resolver: zodResolver(schema) });
  const db = useDatabase(); const dispatch = useAppDispatch(); const navigate = useNavigate();
  const enter = (id: string): void => { const user = db.users[id]; if (!user?.active) return; dispatch(sessionChanged(id)); navigate(!user.verified ? '/verify' : user.role === 'champion' ? '/app/dashboard' : '/app/portfolio'); };
  return <AuthShell title="Welcome back" subtitle="Continue your assessment or explore a staff perspective."><Alert title="Demo sign-in">Use a demo account below or enter a registered email with <strong>{DEMO_PASSWORD}</strong>. This is not real authentication.</Alert><form noValidate onSubmit={handleSubmit((values) => {
    const user = Object.values(db.users).find((u) => u.email.toLowerCase() === values.email.toLowerCase() && u.active);
    if (!user || values.password !== DEMO_PASSWORD) { setError('root', { message: 'Use an active local demo email and the shared demo password shown above.' }); return; }
    enter(user.id);
  })}><Field label="Email address" htmlFor="login-email" error={errors.email?.message}><input id="login-email" type="email" autoComplete="username" {...register('email')} aria-invalid={!!errors.email}/></Field><Field label="Demo password" htmlFor="login-password" error={errors.password?.message}><input id="login-password" type="password" autoComplete="off" {...register('password')} aria-invalid={!!errors.password}/></Field>{errors.root && <Alert kind="error">{errors.root.message}</Alert>}<Button type="submit" className="full-width">Sign in <Icon name="arrow"/></Button></form><div className="auth-links"><Link to="/forgot-password">Forgot password?</Link><Link to="/register">Create an account</Link></div><div className="separator">OR EXPLORE A DEMO ROLE</div><div className="demo-login-grid">{DEMO_ACCOUNTS.map((a) => <button key={a.id} className="demo-login" disabled={!db.users[a.id]?.active} onClick={() => enter(a.id)}><strong>{a.label}</strong><small>{db.users[a.id]?.email}</small></button>)}</div></AuthShell>;
}
export function Register() {
  const schema = z.object({ name: z.string().trim().min(2,'Enter your full name.').max(160), email, jobTitle: z.string().trim().min(2,'Enter your role.').max(160), password, confirm: z.string(), consent: z.boolean().refine(Boolean, 'Accept the prototype privacy notice.') }).refine((data) => data.password === data.confirm, { message: 'Passwords do not match.', path: ['confirm'] });
  const { register, handleSubmit, formState: { errors } } = useForm<z.infer<typeof schema>>({ resolver: zodResolver(schema), defaultValues: { consent: false } });
  const { run, busy } = useCommand(); const dispatch = useAppDispatch(); const navigate = useNavigate();
  return <AuthShell title="Create your demo account" subtitle="An identified assessment for your company — not an anonymous survey."><Alert kind="warning">Use dummy details. Password fields demonstrate validation only; passwords are discarded. Return sign-ins use <strong>{DEMO_PASSWORD}</strong>.</Alert><form noValidate onSubmit={handleSubmit(async (values) => {
    const id = await run({ type: 'register', name: values.name, email: values.email, jobTitle: values.jobTitle, consent: values.consent });
    if (id) { dispatch(sessionChanged(id)); navigate('/verify'); }
  })}><Field label="Full name" htmlFor="register-name" error={errors.name?.message}><input id="register-name" autoComplete="off" {...register('name')} aria-invalid={!!errors.name}/></Field><Field label="Work email" htmlFor="register-email" error={errors.email?.message}><input id="register-email" type="email" autoComplete="off" {...register('email')} aria-invalid={!!errors.email}/></Field><Field label="Job title" htmlFor="register-title" error={errors.jobTitle?.message}><input id="register-title" placeholder="e.g. Head of Procurement" {...register('jobTitle')} aria-invalid={!!errors.jobTitle}/></Field><div className="form-grid"><Field label="Dummy password" htmlFor="register-password" error={errors.password?.message} hint="12+ characters; uppercase, lowercase and a number."><input id="register-password" type="password" autoComplete="off" {...register('password')} aria-invalid={!!errors.password}/></Field><Field label="Confirm dummy password" htmlFor="register-confirm" error={errors.confirm?.message}><input id="register-confirm" type="password" autoComplete="off" {...register('confirm')} aria-invalid={!!errors.confirm}/></Field></div><label className="checkbox"><input type="checkbox" {...register('consent')}/><span>I understand this is a local prototype and I will not enter real confidential information.</span></label>{errors.consent && <span className="field-error">{errors.consent.message}</span>}<Button type="submit" disabled={busy} className="full-width">Create demo account <Icon name="arrow"/></Button></form><p className="auth-bottom">Already registered? <Link to="/login">Sign in</Link></p></AuthShell>;
}
export function Verify() {
  const user = useAppSelector(selectUser); const { run, busy } = useCommand(); const navigate = useNavigate();
  const [cooldown, setCooldown] = useState(0); const [resent, setResent] = useState(false);
  useEffect(() => { if (!cooldown) return; const timeout = window.setTimeout(() => setCooldown(cooldown - 1), 1000); return () => window.clearTimeout(timeout); }, [cooldown]);
  const schema = z.object({ code: z.string().regex(/^\d{6}$/, 'Enter the six-digit code.') });
  const { register, handleSubmit, formState: { errors } } = useForm<z.infer<typeof schema>>({ resolver: zodResolver(schema) });
  if (!user) return <Navigate to="/login" replace/>;
  if (user.verified) return <Navigate to="/app/profile" replace/>;
  return <AuthShell title="Verify your email" subtitle={`Simulated verification for ${user.email}`}><span className="auth-feature-icon"><Icon name="lock" size={32}/></span><Alert>For this local demonstration, the code is <strong>123456</strong>. No email is sent and this does not prove ownership of an address.</Alert><form noValidate onSubmit={handleSubmit(async (values) => { const id = await run({type:'verify', code:values.code}); if (id) navigate('/app/profile'); })}><Field label="Verification code" htmlFor="verify-code" error={errors.code?.message}><input className="code-input" id="verify-code" inputMode="numeric" maxLength={6} autoComplete="one-time-code" {...register('code')}/></Field><Button disabled={busy} type="submit" className="full-width">Verify and continue</Button></form><Button variant="ghost" disabled={cooldown > 0} onClick={() => {setCooldown(30); setResent(true);}}>Resend demo code {cooldown > 0 && `(${cooldown}s)`}</Button>{resent && <p role="status" className="muted">Resend simulated. The code remains 123456; no email was sent.</p>}</AuthShell>;
}
export function ForgotPassword() {
  const [sent, setSent] = useState(false);
  const schema = z.object({ email }); const { register, handleSubmit, formState:{errors} } = useForm<z.infer<typeof schema>>({resolver:zodResolver(schema)});
  return <AuthShell title="Reset access" subtitle="Explore the intended account-recovery journey.">{sent ? <><Alert kind="success" title="Recovery request simulated">In production, an eligible account would receive an expiring recovery link. This prototype sends no email and does not disclose account existence.</Alert><LinkButton to="/reset-password">Open the simulated reset screen</LinkButton></> : <form noValidate onSubmit={handleSubmit(() => setSent(true))}><Field label="Email address" htmlFor="forgot-email" error={errors.email?.message}><input id="forgot-email" type="email" {...register('email')}/></Field><Button type="submit" className="full-width">Simulate recovery email</Button></form>}<p className="auth-bottom"><Link to="/login">Back to sign in</Link></p></AuthShell>;
}
export function ResetPassword() {
  const [done, setDone] = useState(false);
  const schema=z.object({password,confirm:z.string()}).refine((v)=>v.password===v.confirm,{message:'Passwords do not match.',path:['confirm']});
  const {register,handleSubmit,formState:{errors}}=useForm<z.infer<typeof schema>>({resolver:zodResolver(schema)});
  return <AuthShell title="Choose a dummy password" subtitle="This screen validates inputs without storing or changing credentials.">{done ? <><Alert kind="success">Reset validation completed. No credential was stored or changed. All demo sign-ins still use <strong>{DEMO_PASSWORD}</strong>.</Alert><LinkButton to="/login">Return to sign in</LinkButton></> : <><Alert kind="warning">There is no real recovery token here. Use only a dummy password.</Alert><form noValidate onSubmit={handleSubmit(()=>setDone(true))}><Field label="New dummy password" htmlFor="reset-password" error={errors.password?.message}><input id="reset-password" type="password" autoComplete="off" {...register('password')}/></Field><Field label="Confirm dummy password" htmlFor="reset-confirm" error={errors.confirm?.message}><input id="reset-confirm" type="password" autoComplete="off" {...register('confirm')}/></Field><Button type="submit">Validate reset</Button></form></>}</AuthShell>;
}
