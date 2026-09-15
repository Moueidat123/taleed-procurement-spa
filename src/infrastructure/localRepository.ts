import type { Database } from '../domain/types';
import { MAX_BACKUP_BYTES, parseDatabase, validateDatabase } from '../domain/validation';

export const STORAGE_KEY = 'taleed.procurement.prototype.v1';
export const SESSION_KEY = 'taleed.procurement.demo-session';
export interface StoragePort { getItem(key: string): string | null; setItem(key: string, value: string): void; removeItem(key: string): void }
export interface RevisionToken { instanceId: string; revision: number }
export class StorageConflictError extends Error {
  constructor() { super('Another tab changed the dataset. Reload the latest saved data before continuing.'); this.name = 'StorageConflictError'; }
}
/** Replace this port with an authenticated HTTP adapter for the Statamic implementation. */
export interface AssessmentRepository {
  load(): Database | null;
  initialize(seed: Database): Database;
  commit(expected: RevisionToken, next: Database): Database;
  raw(): string | null;
}
export class LocalRepository implements AssessmentRepository {
  constructor(private readonly getStorage: () => StoragePort) {}
  raw(): string | null { return this.getStorage().getItem(STORAGE_KEY); }
  load(): Database | null { const raw = this.raw(); return raw === null ? null : parseDatabase(raw); }
  initialize(seed: Database): Database {
    const existing = this.load();
    if (existing) return existing;
    return this.write(seed);
  }
  commit(expected: RevisionToken, next: Database): Database {
    const current = this.load();
    if (!current || current.instanceId !== expected.instanceId || current.revision !== expected.revision) throw new StorageConflictError();
    if (next.instanceId !== current.instanceId || next.revision !== current.revision + 1) throw new Error('Invalid local revision transition.');
    return this.write(next);
  }
  /** Explicit recovery/reset only. The caller must obtain confirmation and retain an export opportunity. */
  replace(next: Database): Database { return this.write(next); }
  private write(input: Database): Database {
    const next = validateDatabase(input); const raw = JSON.stringify(next);
    if (raw.length > MAX_BACKUP_BYTES) throw new Error('The local dataset is too large. Export a backup before resetting the demo.');
    try { this.getStorage().setItem(STORAGE_KEY, raw); }
    catch { throw new Error('Nothing was saved. Browser storage is unavailable or full. Free space or enable storage, then retry.'); }
    return next;
  }
}
