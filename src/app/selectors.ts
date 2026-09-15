import { createSelector } from '@reduxjs/toolkit';
import type { RootState } from './store';
import { selectUser } from './store';
import { effectiveSubmissions } from '../domain/policies';
const selectData = (state: RootState) => state.data;
export const selectOwnAssessments = createSelector([selectData, selectUser], (db, user) =>
  db && user?.orgId ? Object.values(db.assessments).filter((a) => a.orgId === user.orgId)
    .sort((a, b) => b.updatedAt.localeCompare(a.updatedAt)) : []);
export const selectEffective = createSelector([selectData], (db) => db ? effectiveSubmissions(db) : []);
export const selectOwnOrganization = createSelector([selectData, selectUser], (db, user) => user?.orgId ? db?.organizations[user.orgId] ?? null : null);
