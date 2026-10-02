import { useMeQuery } from '../infrastructure/authApi';
import type { CurrentUser } from '../infrastructure/api';

/** The signed-in user from the server session (`/auth/me`). Only call under the route guard. */
export function useCurrentUser(): CurrentUser {
  const { data } = useMeQuery();
  if (!data) throw new Error('A signed-in session is required.');
  return data;
}

export function workspacePath(user: Pick<CurrentUser, 'role'>): string {
  return user.role === 'champion' ? '/app/dashboard' : '/app/portfolio';
}
