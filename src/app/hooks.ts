import { useCallback } from 'react';
import { execute, selectUser, useAppDispatch, useAppSelector } from './store';
import type { Command, Database, User } from '../domain/types';
export function useDatabase(): Database {
  const db = useAppSelector((state) => state.data);
  if (!db) throw new Error('The local dataset has not loaded.');
  return db;
}
export function useCurrentUser(): User {
  const user = useAppSelector(selectUser);
  if (!user) throw new Error('A demo session is required.');
  return user;
}
export function useCommand() {
  const dispatch = useAppDispatch();
  const busy = useAppSelector((state) => state.ui.pending > 0 || state.ui.externalChange);
  const run = useCallback(async (command: Command): Promise<string | null> => {
    try { return await dispatch(execute(command)); }
    catch { return null; } // The command boundary displays the exact failure in the application alert.
  }, [dispatch]);
  return { run, busy };
}
