import { configureStore, createListenerMiddleware, createSlice, type PayloadAction, type ThunkAction, type UnknownAction } from '@reduxjs/toolkit';
import { useDispatch, useSelector, useStore } from 'react-redux';
import type { Command, Database, User } from '../domain/types';
import { applyCommand } from '../domain/commands';
import { demoNow, seedDatabase } from '../domain/seed';
import { LocalRepository, SESSION_KEY, STORAGE_KEY, StorageConflictError } from '../infrastructure/localRepository';
import { validateDatabase } from '../domain/validation';

const dataSlice = createSlice({ name: 'data', initialState: null as Database | null,
  reducers: { replaced: (_state, action: PayloadAction<Database>) => action.payload } });
const sessionSlice = createSlice({ name: 'session', initialState: { userId: null as string | null },
  reducers: { changed: (state, action: PayloadAction<string | null>) => { state.userId = action.payload; } } });
interface UiState { pending: number; error: string; externalChange: boolean; bootError: string; lastSaved: string }
const uiSlice = createSlice({ name: 'ui', initialState: { pending: 0, error: '', externalChange: false, bootError: '', lastSaved: '' } as UiState,
  reducers: {
    started: (state) => { state.pending += 1; state.error = ''; },
    finished: (state) => { state.pending = Math.max(0, state.pending - 1); },
    failed: (state, action: PayloadAction<string>) => { state.error = action.payload; },
    external: (state) => { state.externalChange = true; },
    bootFailed: (state, action: PayloadAction<string>) => { state.bootError = action.payload; },
    saved: (state, action: PayloadAction<string>) => { state.lastSaved = action.payload; state.error = ''; },
    clear: (state) => { state.error = ''; },
  } });
export const { changed: sessionChanged } = sessionSlice.actions;
export const { clear: clearError } = uiSlice.actions;
export const repository = new LocalRepository(() => window.localStorage);
const listeners = createListenerMiddleware();
export const store = configureStore({
  reducer: { data: dataSlice.reducer, session: sessionSlice.reducer, ui: uiSlice.reducer },
  middleware: (getDefaultMiddleware) => getDefaultMiddleware({ thunk: { extraArgument: repository } }).prepend(listeners.middleware),
  devTools: import.meta.env.DEV,
});
export type RootState = ReturnType<typeof store.getState>;
export type AppDispatch = typeof store.dispatch;
export type AppThunk<T = void> = ThunkAction<T, RootState, LocalRepository, UnknownAction>;
export const useAppDispatch = useDispatch.withTypes<AppDispatch>();
export const useAppSelector = useSelector.withTypes<RootState>();
export const useAppStore = useStore.withTypes<typeof store>();
export const selectUser = (state: RootState): User | null => state.session.userId ? state.data?.users[state.session.userId] ?? null : null;

listeners.startListening({ actionCreator: sessionChanged, effect: (action) => {
  try { if (action.payload) window.sessionStorage.setItem(SESSION_KEY, action.payload); else window.sessionStorage.removeItem(SESSION_KEY); }
  catch { /* Session is intentionally allowed to remain memory-only. No credentials are stored. */ }
} });

export const bootstrap = (): AppThunk => (dispatch, _getState, repo) => {
  try {
    const data = repo.initialize(seedDatabase(crypto.randomUUID()));
    dispatch(dataSlice.actions.replaced(data)); dispatch(uiSlice.actions.saved(data.savedAt));
    let id: string | null = null;
    try { id = window.sessionStorage.getItem(SESSION_KEY); } catch { /* Memory-only session. */ }
    if (id && data.users[id]?.active) dispatch(sessionChanged(id));
  } catch (error) { dispatch(uiSlice.actions.bootFailed(message(error))); }
};
function message(error: unknown): string { return error instanceof Error ? error.message : 'The operation could not be completed.'; }
let queue: Promise<unknown> = Promise.resolve();
async function withWriterLock<T>(work: () => T): Promise<T> {
  if (typeof navigator !== 'undefined' && navigator.locks) return navigator.locks.request(`${STORAGE_KEY}.writer`, work);
  // Fallback uses revision checking. Browsers without Web Locks must be used in ONE tab.
  return work();
}
/** Serialized, save-before-success commands. No localStorage access in any reducer or component. */
export const execute = (command: Command): AppThunk<Promise<string>> => (dispatch, getState, repo) => {
  const task = async (): Promise<string> => {
    dispatch(uiSlice.actions.started());
    try {
      return await withWriterLock(() => {
        const state = getState();
        if (state.ui.externalChange) throw new Error('Reload the latest saved dataset before making changes.');
        if (!state.data) throw new Error('The local dataset is not available.');
        const id = crypto.randomUUID();
        const next = applyCommand(state.data, command, { id, now: demoNow(), actorId: state.session.userId });
        if (next !== state.data) {
          const saved = repo.commit(state.data, next);
          dispatch(dataSlice.actions.replaced(saved)); dispatch(uiSlice.actions.saved(saved.savedAt));
        }
        return 'assessmentId' in command && command.type !== 'openCorrection' ? command.assessmentId : id;
      });
    } catch (error) { if (error instanceof StorageConflictError) dispatch(uiSlice.actions.external()); dispatch(uiSlice.actions.failed(message(error))); throw error; }
    finally { dispatch(uiSlice.actions.finished()); }
  };
  const result = queue.then(task, task);
  queue = result.catch(() => undefined);
  return result;
};
export function startStorageMonitor(): () => void {
  const onStorage = (event: StorageEvent): void => {
    if (event.key !== STORAGE_KEY && event.key !== null) return;
    const data = store.getState().data;
    try {
      const next = repository.load();
      if (!next || !data || next.instanceId !== data.instanceId || next.revision !== data.revision) store.dispatch(uiSlice.actions.external());
    } catch { store.dispatch(uiSlice.actions.external()); }
  };
  window.addEventListener('storage', onStorage);
  return () => window.removeEventListener('storage', onStorage);
}
/** Destructive demo operations have a separate, explicit permission/confirmation boundary. */
export async function replaceDemoDataset(input: Database, confirmation: string, recovery = false): Promise<void> {
  if (confirmation !== 'REPLACE') throw new Error('Type REPLACE to confirm this operation.');
  const state = store.getState(); const user = selectUser(state);
  if (recovery ? !state.ui.bootError : user?.role !== 'admin') throw new Error('Super Admin access or a genuine startup recovery state is required.');
  await queue;
  await withWriterLock(() => {
    const next = validateDatabase(input);
    next.instanceId = crypto.randomUUID(); next.revision = 0; next.savedAt = demoNow();
    repository.replace(next);
  });
  store.dispatch(sessionChanged(null)); window.location.hash = '#/'; window.location.reload();
}
