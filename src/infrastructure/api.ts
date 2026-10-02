import { createApi, fetchBaseQuery, type BaseQueryFn, type FetchArgs, type FetchBaseQueryError } from '@reduxjs/toolkit/query/react';
import type { Role } from '../domain/types';

/** Same-origin business API (Laravel + Sanctum SPA cookies). No tokens are stored by the client. */
export const API_BASE = '/api/procurement/v1';

export interface ApiError { status: number; code: string; message: string; fields?: Record<string, string[]> }
export interface CurrentUser {
  id: string; name: string; email: string; jobTitle: string; role: Role;
  orgId: string | null; verified: boolean; active: boolean; canExport: boolean; twoFactorEnabled: boolean;
}

function readCookie(name: string): string {
  const raw = document.cookie.split('; ').find((c) => c.startsWith(`${name}=`))?.split('=')[1];
  return raw ? decodeURIComponent(raw) : '';
}

let csrfReady: Promise<void> | null = null;
/** Fetch the XSRF-TOKEN cookie once; refreshed after login/logout or a 419. */
export function ensureCsrf(force = false): Promise<void> {
  if (force || !csrfReady || !readCookie('XSRF-TOKEN')) {
    csrfReady = fetch('/sanctum/csrf-cookie', { credentials: 'same-origin', headers: { Accept: 'application/json' } })
      .then((r) => { if (!r.ok) throw new Error('Could not start a secure session.'); })
      .catch((e: unknown) => { csrfReady = null; throw e; });
  }
  return csrfReady;
}

const raw = fetchBaseQuery({
  baseUrl: API_BASE,
  credentials: 'same-origin',
  prepareHeaders: (headers) => {
    headers.set('Accept', 'application/json');
    const token = readCookie('XSRF-TOKEN');
    if (token) headers.set('X-XSRF-TOKEN', token);
    return headers;
  },
});

/** Listeners notified when the server says the session is gone (401). */
const expiryListeners = new Set<() => void>();
export function onSessionExpired(listener: () => void): () => void { expiryListeners.add(listener); return () => expiryListeners.delete(listener); }

export function toApiError(error: FetchBaseQueryError | undefined): ApiError {
  if (!error) return { status: 0, code: 'unknown', message: 'The request could not be completed.' };
  if (typeof error.status !== 'number') {
    return { status: 0, code: 'network', message: 'The server could not be reached. Check your connection and try again.' };
  }
  const body = error.data as { error?: { code?: string; message?: string; fields?: Record<string, string[]> }; message?: string; errors?: Record<string, string[]> } | undefined;
  const fields = body?.error?.fields ?? body?.errors;
  return {
    status: error.status,
    code: body?.error?.code ?? (error.status === 422 ? 'validation_failed' : `http_${error.status}`),
    message: body?.error?.message ?? body?.message ?? 'The request could not be completed.',
    ...(fields ? { fields } : {}),
  };
}

const MUTATING = new Set(['POST', 'PUT', 'PATCH', 'DELETE']);
const baseQuery: BaseQueryFn<string | FetchArgs, unknown, FetchBaseQueryError> = async (args, api, extra) => {
  const method = typeof args === 'string' ? 'GET' : (args.method ?? 'GET').toUpperCase();
  if (MUTATING.has(method)) {
    try { await ensureCsrf(); } catch { return { error: { status: 'FETCH_ERROR', error: 'csrf' } }; }
  }
  let result = await raw(args, api, extra);
  // Expired CSRF token: refresh once and retry the same request.
  if (result.error?.status === 419 && MUTATING.has(method)) {
    try { await ensureCsrf(true); result = await raw(args, api, extra); } catch { /* fall through */ }
  }
  const url = typeof args === 'string' ? args : args.url;
  // `/auth/me` answering 401 just means "signed out"; treating it as expiry would reset the cache,
  // refetch `/auth/me` and loop, cancelling an in-flight sign-in.
  if (result.error?.status === 401 && !url.startsWith('/auth/login') && !url.startsWith('/auth/two-factor') && !url.startsWith('/auth/me')) {
    expiryListeners.forEach((l) => l());
  }
  return result;
};

export const api = createApi({
  reducerPath: 'api',
  baseQuery,
  tagTypes: ['Me', 'Organization', 'Assessment', 'History', 'Cycles', 'Staff', 'StaffUsers'],
  endpoints: () => ({}),
});
