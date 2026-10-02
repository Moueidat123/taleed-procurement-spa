import { Component, Suspense, lazy, type ErrorInfo, type ReactNode } from 'react';
import { HashRouter, Navigate, Outlet, Route, Routes, useLocation } from 'react-router-dom';
import { Layout } from '../components/Layout';
import { Button, EmptyState, LinkButton } from '../components/ui';
import type { Role } from '../domain/types';
import { useMeQuery } from '../infrastructure/authApi';
import { workspacePath } from './hooks';
const Landing=lazy(()=>import('../features/public/Landing'));
const Login=lazy(()=>import('../features/public/Auth').then((m)=>({default:m.Login})));
const Register=lazy(()=>import('../features/public/Auth').then((m)=>({default:m.Register})));
const Verify=lazy(()=>import('../features/public/Auth').then((m)=>({default:m.Verify})));
const Forgot=lazy(()=>import('../features/public/Auth').then((m)=>({default:m.ForgotPassword})));
const Reset=lazy(()=>import('../features/public/Auth').then((m)=>({default:m.ResetPassword})));
const AcceptInvitation=lazy(()=>import('../features/public/Auth').then((m)=>({default:m.AcceptInvitation})));
const Dashboard=lazy(()=>import('../features/company/Company').then((m)=>({default:m.Dashboard})));
const Profile=lazy(()=>import('../features/company/Company').then((m)=>({default:m.Profile})));
const History=lazy(()=>import('../features/company/Company').then((m)=>({default:m.History})));
const Assessment=lazy(()=>import('../features/assessment/Assessment').then((m)=>({default:m.AssessmentPage})));
const Review=lazy(()=>import('../features/assessment/Assessment').then((m)=>({default:m.ReviewPage})));
const Results=lazy(()=>import('../features/assessment/Results'));
const Portfolio=lazy(()=>import('../features/staff/Portfolio'));
const Organizations=lazy(()=>import('../features/staff/Organizations').then((m)=>({default:m.Organizations})));
const OrganizationDetail=lazy(()=>import('../features/staff/Organizations').then((m)=>({default:m.OrganizationDetail})));
const Compare=lazy(()=>import('../features/staff/Compare'));
const Access=lazy(()=>import('../features/staff/Administration').then((m)=>({default:m.Access})));
const Security=lazy(()=>import('../features/staff/Administration').then((m)=>({default:m.Security})));
const Frameworks=lazy(()=>import('../features/staff/Frameworks'));
const Loading=()=> <div className="loading-state" role="status"><span className="loader"/>Loading your workspace…</div>;
/** Route access mirrors the server for navigation only; the API enforces every permission. */
function Guard({roles}:{roles?:Role[]}) {
  const {data:user,isLoading,isError}=useMeQuery();
  const location=useLocation();
  if(isLoading)return <Loading/>;
  if(isError)return <EmptyState title="The service could not be reached" action={<Button onClick={()=>window.location.reload()}>Try again</Button>}>Check your connection. Your saved answers are stored on the server.</EmptyState>;
  if(!user?.active)return <Navigate to="/login" replace state={{from:location.pathname}}/>;
  if(!user.verified)return <Navigate to="/verify" replace/>;
  if(roles&&!roles.includes(user.role))return <EmptyState title="This area is not available to your role" action={<LinkButton to={workspacePath(user)}>Return to your workspace</LinkButton>}>Company access does not grant staff capabilities.</EmptyState>;
  return <Outlet/>;
}
function WorkspaceRedirect(){const {data:user}=useMeQuery();return user?<Navigate to={workspacePath(user)} replace/>:<Loading/>;}
export class AppErrorBoundary extends Component<{children:ReactNode},{failed:boolean}> {
  state={failed:false};
  static getDerivedStateFromError(){return {failed:true};}
  componentDidCatch(error:Error,info:ErrorInfo){console.error('Rendering error',error.message,info.componentStack);}
  render(){return this.state.failed?<div className="fatal-error"><h1>This screen could not be displayed.</h1><p>Your saved answers are stored on the server and have not been lost. Reload to try again.</p><Button onClick={()=>window.location.reload()}>Reload</Button></div>:this.props.children;}
}
export default function App() {
  return <HashRouter><AppErrorBoundary><Suspense fallback={<Loading/>}><Routes>
    <Route path="/" element={<Landing/>}/><Route path="/login" element={<Login/>}/><Route path="/register" element={<Register/>}/><Route path="/verify" element={<Verify/>}/><Route path="/forgot-password" element={<Forgot/>}/><Route path="/reset-password" element={<Reset/>}/><Route path="/accept-invitation" element={<AcceptInvitation/>}/>
    <Route element={<Guard/>}><Route path="/app" element={<Layout/>}><Route index element={<WorkspaceRedirect/>}/>
      <Route element={<Guard roles={['champion']}/>}><Route path="dashboard" element={<Dashboard/>}/><Route path="profile" element={<Profile/>}/><Route path="history" element={<History/>}/><Route path="assessment/:id/review" element={<Review/>}/><Route path="assessment/:id/:section" element={<Assessment/>}/></Route>
      <Route path="results/:id" element={<Results/>}/><Route path="recommendations/:id" element={<Results mode="recommendations"/>}/><Route path="responses/:id" element={<Results mode="responses"/>}/><Route path="report/:id" element={<Results mode="report"/>}/>
      <Route element={<Guard roles={['analyst','admin']}/>}><Route path="security" element={<Security/>}/><Route path="portfolio" element={<Portfolio/>}/><Route path="organizations" element={<Organizations/>}/><Route path="organizations/:id" element={<OrganizationDetail/>}/><Route path="compare" element={<Compare/>}/><Route path="frameworks" element={<Frameworks/>}/></Route>
      <Route element={<Guard roles={['admin']}/>}><Route path="access" element={<Access/>}/></Route>
      <Route path="*" element={<EmptyState title="Page not found" action={<LinkButton to="/app">Go to workspace</LinkButton>}>This page does not exist.</EmptyState>}/>
    </Route></Route><Route path="*" element={<div className="guidance-layout"><EmptyState title="Page not found" action={<LinkButton to="/">Return to the introduction</LinkButton>}>Check the address and try again.</EmptyState></div>}/>
  </Routes></Suspense></AppErrorBoundary></HashRouter>;
}
