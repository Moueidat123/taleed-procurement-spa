import { api, ensureCsrf, type CurrentUser } from './api';

interface Wrapped<T> { data: T }
export interface LoginResult { twoFactor: boolean }
export interface RegisterInput { name: string; email: string; job_title: string; password: string; password_confirmation: string; consent: boolean }

/** Identity endpoints (Phase 2A). The server is the only source of the session. */
export const authApi = api.injectEndpoints({
  endpoints: (build) => ({
    me: build.query<CurrentUser | null, void>({
      // 401 means "signed out", not an error, for the session bootstrap.
      queryFn: async (_arg, _api, _extra, baseQuery) => {
        const r = await baseQuery('/auth/me');
        if (r.error) return r.error.status === 401 ? { data: null } : { error: r.error };
        return { data: (r.data as Wrapped<CurrentUser>).data };
      },
      providesTags: ['Me'],
    }),
    login: build.mutation<LoginResult, { email: string; password: string }>({
      query: (body) => ({ url: '/auth/login', method: 'POST', body }),
      transformResponse: (r: { two_factor?: boolean }) => ({ twoFactor: r?.two_factor === true }),
      onQueryStarted: async (_a, { queryFulfilled }) => { try { await queryFulfilled; await ensureCsrf(true); } catch { /* shown by caller */ } },
      invalidatesTags: (r) => (r && !r.twoFactor ? ['Me'] : []),
    }),
    twoFactor: build.mutation<void, { code?: string; recovery_code?: string }>({
      query: (body) => ({ url: '/auth/two-factor-challenge', method: 'POST', body }),
      onQueryStarted: async (_a, { queryFulfilled }) => { try { await queryFulfilled; await ensureCsrf(true); } catch { /* shown by caller */ } },
      invalidatesTags: ['Me'],
    }),
    logout: build.mutation<void, void>({
      query: () => ({ url: '/auth/logout', method: 'POST' }),
      onQueryStarted: async (_a, { dispatch, queryFulfilled }) => {
        try { await queryFulfilled; } finally { dispatch(api.util.resetApiState()); await ensureCsrf(true).catch(() => undefined); }
      },
    }),
    register: build.mutation<CurrentUser, RegisterInput>({
      query: (body) => ({ url: '/auth/register', method: 'POST', body }),
      transformResponse: (r: Wrapped<CurrentUser>) => r.data,
      onQueryStarted: async (_a, { queryFulfilled }) => { try { await queryFulfilled; await ensureCsrf(true); } catch { /* shown by caller */ } },
      invalidatesTags: ['Me'],
    }),
    sendVerification: build.mutation<void, void>({ query: () => ({ url: '/auth/email/verify/send', method: 'POST' }) }),
    confirmVerification: build.mutation<CurrentUser, { code: string }>({
      query: (body) => ({ url: '/auth/email/verify/confirm', method: 'POST', body }),
      transformResponse: (r: Wrapped<CurrentUser>) => r.data,
      invalidatesTags: ['Me'],
    }),
    forgotPassword: build.mutation<void, { email: string }>({ query: (body) => ({ url: '/auth/forgot-password', method: 'POST', body }) }),
    resetPassword: build.mutation<void, { token: string; email: string; password: string; password_confirmation: string }>({
      query: (body) => ({ url: '/auth/reset-password', method: 'POST', body }),
    }),
    acceptInvitation: build.mutation<CurrentUser, { token: string; name: string; password: string; password_confirmation: string }>({
      query: (body) => ({ url: '/auth/invitations/accept', method: 'POST', body }),
      transformResponse: (r: Wrapped<CurrentUser>) => r.data,
      invalidatesTags: ['Me'],
    }),
  }),
});

export const {
  useMeQuery, useLoginMutation, useTwoFactorMutation, useLogoutMutation, useRegisterMutation,
  useSendVerificationMutation, useConfirmVerificationMutation, useForgotPasswordMutation,
  useResetPasswordMutation, useAcceptInvitationMutation,
} = authApi;
