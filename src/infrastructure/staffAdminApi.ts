import { api } from './api';
import type { Role } from '../domain/types';

export interface StaffUser {
  id: string; name: string; email: string; jobTitle: string | null; role: Exclude<Role, 'champion'>;
  active: boolean; canExport: boolean; twoFactorEnabled: boolean; createdAt: string | null;
}
interface Wrapped<T> { data: T }

/** Super Admin people & access, plus the signed-in staff member's own two-step verification (Fortify). */
export const staffAdminApi = api.injectEndpoints({
  endpoints: (build) => ({
    staffUsers: build.query<StaffUser[], void>({
      query: () => '/staff/users',
      transformResponse: (r: Wrapped<StaffUser[]>) => r.data,
      providesTags: ['StaffUsers'],
    }),
    updateStaffAccess: build.mutation<StaffUser, { id: string; active?: boolean; canExport?: boolean }>({
      query: ({ id, ...body }) => ({ url: `/staff/users/${encodeURIComponent(id)}/access`, method: 'PATCH', body }),
      transformResponse: (r: Wrapped<StaffUser>) => r.data,
      invalidatesTags: ['StaffUsers'],
    }),
    inviteStaff: build.mutation<{ id: string; email: string; role: string }, { email: string; role: 'analyst' | 'admin' }>({
      query: (body) => ({ url: '/staff/invitations', method: 'POST', body }),
      transformResponse: (r: Wrapped<{ id: string; email: string; role: string }>) => r.data,
      invalidatesTags: ['StaffUsers'],
    }),
    confirmPassword: build.mutation<void, { password: string }>({
      query: (body) => ({ url: '/auth/user/confirm-password', method: 'POST', body }),
    }),
    enableTwoFactor: build.mutation<void, void>({
      query: () => ({ url: '/auth/user/two-factor-authentication', method: 'POST' }),
    }),
    twoFactorQr: build.query<{ svg: string }, void>({ query: () => '/auth/user/two-factor-qr-code' }),
    confirmTwoFactor: build.mutation<void, { code: string }>({
      query: (body) => ({ url: '/auth/user/confirmed-two-factor-authentication', method: 'POST', body }),
      invalidatesTags: ['Me'],
    }),
    recoveryCodes: build.query<string[], void>({ query: () => '/auth/user/two-factor-recovery-codes' }),
  }),
});

export const {
  useStaffUsersQuery, useUpdateStaffAccessMutation, useInviteStaffMutation, useConfirmPasswordMutation,
  useEnableTwoFactorMutation, useLazyTwoFactorQrQuery, useConfirmTwoFactorMutation, useLazyRecoveryCodesQuery,
} = staffAdminApi;
