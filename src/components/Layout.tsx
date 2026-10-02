import { useEffect, useState } from 'react';
import { Link, NavLink, Outlet, useLocation, useNavigate } from 'react-router-dom';
import { clearError, useAppDispatch, useAppSelector } from '../app/store';
import { useCurrentUser } from '../app/hooks';
import { useLogoutMutation } from '../infrastructure/authApi';
import { Alert, Button, Icon, LinkButton } from './ui';

export function Brand({ light = false }: { light?: boolean }) {
  return <Link className={`brand ${light ? 'brand-light' : ''}`} to="/"><img className="brand-logo" src={`${import.meta.env.BASE_URL}taleed-logo.svg`} alt="aramco Taleed" width={118} height={39}/><small className="brand-tagline">Procurement Self-Assessment</small></Link>;
}
export function PublicHeader() {
  return <header className="public-header"><Brand/><nav aria-label="Public navigation"><Link to="/login" className="button button-secondary">Sign in</Link></nav></header>;
}
export function GlobalNotices() {
  const ui = useAppSelector((state) => state.ui); const dispatch = useAppDispatch();
  return <div className="global-notices" aria-live="polite">
    {ui.sessionExpired && <Alert kind="warning" title="Your session has ended.">Sign in again to continue. Answers that were saved are kept on the server. <LinkButton to="/login">Sign in</LinkButton></Alert>}
    {ui.error && <Alert kind="error" title="Operation not completed"><div className="row-between"><span>{ui.error}</span><Button variant="ghost" onClick={() => dispatch(clearError())} aria-label="Dismiss error"><Icon name="close"/></Button></div></Alert>}
  </div>;
}
const SAVE_LABEL = { idle: '', unsaved: 'Unsaved changes', saving: 'Saving…', saved: 'All changes saved', error: 'Not saved — retrying needed', conflict: 'Changed elsewhere — reload' } as const;
export function Layout() {
  const user = useCurrentUser();
  const save = useAppSelector((state) => state.ui.save);
  const [logout] = useLogoutMutation();
  const navigate = useNavigate(); const location = useLocation();
  const [menu, setMenu] = useState(false);
  useEffect(() => { setMenu(false); window.scrollTo(0, 0); }, [location.pathname]);
  const staff = user.role !== 'champion';
  const items = staff ? [
    ['/app/portfolio','chart','Portfolio overview'], ['/app/organizations','company','Organizations'],
    ['/app/compare','grid','Compare results'], ['/app/frameworks','check','Framework & cycle'],
    ...(user.role === 'admin' ? [['/app/access','users','People & access']] : []),
  ] : [['/app/dashboard','grid','My workspace'], ['/app/history','clock','Assessment history'], ['/app/profile','company','Company profile']];
  const roleLabel = user.role === 'admin' ? 'Super Admin' : user.role === 'analyst' ? 'Taleed Analyst' : 'Company Champion';
  return <div className="app-shell"><a className="skip-link" href="#main-content" onClick={(event) => { event.preventDefault(); document.getElementById('main-content')?.focus(); }}>Skip to content</a>
    {menu && <button className="sidebar-overlay" aria-label="Close navigation" onClick={() => setMenu(false)}/>}
    <aside className={`sidebar ${menu ? 'sidebar-open' : ''}`}><Brand light/><div className="sidebar-label">{staff ? 'PROGRAM WORKSPACE' : 'COMPANY WORKSPACE'}</div>
      <nav aria-label="Main navigation">{items.map(([path = '', icon = '', label = '']) => <NavLink key={path} to={path} className={({ isActive }) => isActive ? 'nav-link active' : 'nav-link'}><Icon name={icon}/><span>{label}</span></NavLink>)}</nav>
      <div className="sidebar-bottom"><div className="sidebar-note"><Icon name="leaf"/><strong>Better procurement.<br/>Stronger businesses.</strong><p>Understand your capabilities.<br/>Focus your next steps.</p></div></div>
    </aside>
    <div className="main-column"><header className="topbar"><div className="topbar-left"><Button variant="ghost" className="mobile-menu" aria-label="Open navigation" onClick={() => setMenu(true)}><Icon name="menu"/></Button><span className="breadcrumb">{staff ? 'Taleed program' : 'Company workspace'}<span>/</span>Procurement</span></div><div className="topbar-actions">{SAVE_LABEL[save] && <span className="save-status" role="status"><span className={`status-dot ${save === 'saving' ? 'pending' : ''}`}/>{SAVE_LABEL[save]}</span>}<span className="avatar" aria-hidden="true">{user.name.split(' ').map((part) => part[0]).slice(0,2).join('')}</span><div className="user-summary"><strong>{user.name}</strong><small>{roleLabel}</small></div><Button variant="ghost" aria-label="Sign out" onClick={async () => { await logout().unwrap().catch(() => undefined); navigate('/', { replace: true }); }}><Icon name="logout"/></Button></div></header>
      <GlobalNotices/>
      <main id="main-content" tabIndex={-1} className="main-content"><Outlet/></main>
      <footer className="app-footer"><span>Taleed Procurement Self-Assessment · Self-reported, not a certification</span></footer>
    </div>
  </div>;
}
