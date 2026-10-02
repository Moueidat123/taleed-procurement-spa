import { configureStore, createSlice, type PayloadAction } from '@reduxjs/toolkit';
import { useDispatch, useSelector, useStore } from 'react-redux';
import { api, onSessionExpired } from '../infrastructure/api';

/**
 * Phase 3 — the server is the source of truth. Redux holds only the RTK Query
 * cache and transient UI state (save status, session-expired notice, errors).
 * Nothing business-related is written to localStorage or sessionStorage.
 */
export type SaveState = 'idle' | 'unsaved' | 'saving' | 'saved' | 'error' | 'conflict';
interface UiState { error: string; sessionExpired: boolean; save: SaveState; lastSaved: string }
const initialUi: UiState = { error: '', sessionExpired: false, save: 'idle', lastSaved: '' };

const uiSlice = createSlice({
  name: 'ui',
  initialState: initialUi,
  reducers: {
    failed: (state, action: PayloadAction<string>) => { state.error = action.payload; },
    clear: (state) => { state.error = ''; },
    expired: (state) => { state.sessionExpired = true; },
    signedIn: (state) => { state.sessionExpired = false; state.error = ''; },
    saveChanged: (state, action: PayloadAction<SaveState>) => {
      state.save = action.payload;
      if (action.payload === 'saved') state.lastSaved = new Date().toISOString();
    },
  },
});
export const { failed: showError, clear: clearError, expired: sessionExpired, signedIn, saveChanged } = uiSlice.actions;

export const store = configureStore({
  reducer: { ui: uiSlice.reducer, [api.reducerPath]: api.reducer },
  middleware: (getDefaultMiddleware) => getDefaultMiddleware().concat(api.middleware),
  devTools: import.meta.env.DEV,
});

// A 401 from any business request means the server session ended: clear cached
// data (so nothing from the old session can be shown) and flag the notice.
onSessionExpired(() => {
  store.dispatch(sessionExpired());
  store.dispatch(api.util.resetApiState());
});

export type RootState = ReturnType<typeof store.getState>;
export type AppDispatch = typeof store.dispatch;
export const useAppDispatch = useDispatch.withTypes<AppDispatch>();
export const useAppSelector = useSelector.withTypes<RootState>();
export const useAppStore = useStore.withTypes<typeof store>();
