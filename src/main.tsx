import { StrictMode } from 'react';
import { createRoot } from 'react-dom/client';
import { Provider } from 'react-redux';
import App from './app/App';
import { bootstrap, startStorageMonitor, store } from './app/store';
import './styles/app.css';

store.dispatch(bootstrap());
const stopMonitor=startStorageMonitor();
const root=document.getElementById('root');
if(!root)throw new Error('Application root element is missing.');
createRoot(root).render(<StrictMode><Provider store={store}><App/></Provider></StrictMode>);
if(import.meta.hot)import.meta.hot.dispose(stopMonitor);
