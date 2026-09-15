import { useEffect, useState } from 'react';
import { Link, NavLink, Outlet, useLocation, useNavigate } from 'react-router-dom';
import { clearError, selectUser, sessionChanged, useAppDispatch, useAppSelector } from '../app/store';
import { useDatabase } from '../app/hooks';
import { DEMO_ACCOUNTS, DEMO_DATE } from '../domain/seed';
import { canManage, isStaff } from '../domain/policies';
import { Alert, Badge, Button, Dialog, Icon } from './ui';

export function Brand({ light = false }: { light?: boolean }) {
  return <Link className={`brand ${light ? 'brand-light' : ''}`} to="/"><img className="brand-logo" src={`${import.meta.env.BASE_URL}taleed-logo.svg`} alt="aramco Taleed" width={118} height={39}/><small className="brand-tagline">Procurement Self-Assessment</small></Link>;
}
export function PublicHeader() {
  return <header className="public-header"><Brand/><nav aria-label="Public navigation"><Link to="/methodology">How it works</Link><Link to="/login" className="button button-secondary">Sign in</Link></nav></header>;
}
export function GlobalNotices() {
  const ui = useAppSelector((state) => state.ui); const dispatch = useAppDispatch();
  return <div className="global-notices" aria-live="polite">{ui.externalChange && <Alert kind="warning" title="A newer dataset is available in another tab.">Editing is paused to prevent overwriting changes. <Button variant="secondary" onClick={() => window.location.reload()}>Reload saved data</Button></Alert>}{ui.error && <Alert kind="error" title="Operation not completed"><div className="row-between"><span>{ui.error}</span><Button variant="ghost" onClick={() => dispatch(clearError())} aria-label="Dismiss error"><Icon name="close"/></Button></div></Alert>}</div>;
}
export function Layout() {
  const user = useAppSelector(selectUser); const db = useDatabase(); const dispatch = useAppDispatch();
  const ui = useAppSelector((state) => state.ui);
  const navigate = useNavigate(); const location = useLocation();
  const [menu, setMenu] = useState(false); const [switcher, setSwitcher] = useState(false);
  useEffect(() => { setMenu(false); window.scrollTo(0, 0); }, [location.pathname]);
  if (!user) return null;
  const staff = isStaff(user);
  const items = staff ? [
    ['/app/portfolio','chart','Portfolio overview'], ['/app/organizations','company','Organizations'],
    ['/app/compare','grid','Compare results'], ['/app/frameworks','file','Framework library'],
    ...(canManage(user) ? [['/app/cycles','calendar','Assessment cycles'], ['/app/access','users','People & access']] : []),
    ...(user.role === 'admin' ? [['/app/audit','clock','Audit activity'], ['/app/data','settings','Demo data controls']] : []),
  ] : [['/app/dashboard','grid','My workspace'], ['/app/history','clock','Assessment history'], ['/app/profile','company','Company profile']];
  const org = user.orgId ? db.organizations[user.orgId] : null;
  return <div className="app-shell"><a className="skip-link" href="#main-content" onClick={(event) => { event.preventDefault(); document.getElementById('main-content')?.focus(); }}>Skip to content</a>
    {menu && <button className="sidebar-overlay" aria-label="Close navigation" onClick={() => setMenu(false)}/>}
    <aside className={`sidebar ${menu ? 'sidebar-open' : ''}`}><Brand light/><div className="sidebar-label">{staff ? 'PROGRAM WORKSPACE' : 'COMPANY WORKSPACE'}</div>
      <nav aria-label="Main navigation">{items.map(([path = '', icon = '', label = '']) => <NavLink key={path} to={path} className={({ isActive }) => isActive ? 'nav-link active' : 'nav-link'}><Icon name={icon}/><span>{label}</span></NavLink>)}</nav>
      <div className="sidebar-bottom"><div className="sidebar-note"><Icon name="leaf"/><strong>Better procurement.<br/>Stronger businesses.</strong><p>Understand your capabilities.<br/>Focus your next steps.</p></div><Link className="nav-link" to="/methodology"><Icon name="info"/>Methodology & guidance</Link><Link className="nav-link" to="/privacy"><Icon name="lock"/>Prototype privacy</Link><div className="sidebar-version">Framework 1.0.0 · English</div></div>
    </aside>
    <div className="main-column"><header className="topbar"><div className="topbar-left"><Button variant="ghost" className="mobile-menu" aria-label="Open navigation" onClick={() => setMenu(true)}><Icon name="menu"/></Button><span className="breadcrumb">{staff ? 'Taleed program' : org?.name ?? 'Company setup'}<span>/</span>Procurement</span></div><div className="topbar-actions"><Button variant="secondary" onClick={() => setSwitcher(true)}><Icon name="users"/><span>Switch demo role</span></Button><span className="avatar" aria-hidden="true">{user.name.split(' ').map((part) => part[0]).slice(0,2).join('')}</span><div className="user-summary"><strong>{user.name}</strong><small>{user.role === 'admin' ? 'Super Admin' : user.role === 'analyst' ? 'Taleed Analyst' : 'Company Champion'}</small></div><Button variant="ghost" aria-label="Sign out" onClick={() => { dispatch(sessionChanged(null)); navigate('/'); }}><Icon name="logout"/></Button></div></header>
      <div className="demo-strip"><span><Badge tone="amber">INTERACTIVE PROTOTYPE</Badge> Synthetic data only · browser-local storage · no real authentication or email</span><span className="save-status" role="status"><span className={`status-dot ${ui.pending ? 'pending' : ''}`}/>{ui.pending ? 'Saving…' : ui.error ? 'Action failed — see message' : 'Saved to this browser'}</span></div>
      <GlobalNotices/>
      <main id="main-content" tabIndex={-1} className="main-content"><Outlet/></main>
      <footer className="app-footer"><span>Taleed Procurement Self-Assessment · Self-reported, not a certification</span><span>Demo date: {DEMO_DATE} · Local prototype</span></footer>
    </div>
    <Dialog open={switcher} title="Explore another perspective" onClose={() => setSwitcher(false)}><p className="muted">These are simulated identities sharing the same local dataset. This control is a presentation tool, not a production permission feature.</p><div className="demo-roles">{DEMO_ACCOUNTS.map((account) => <button className="role-option" key={account.id} disabled={!db.users[account.id]?.active} onClick={() => { dispatch(sessionChanged(account.id)); setSwitcher(false); navigate(db.users[account.id]?.role === 'champion' ? '/app/dashboard' : '/app/portfolio'); }}><span className="icon-box"><Icon name={account.id.includes('sahara') || account.id.includes('namaa') ? 'company' : 'users'}/></span><span><strong>{account.label}</strong><small>{account.detail}</small></span><Icon name="arrow"/></button>)}</div></Dialog>
  </div>;
}
