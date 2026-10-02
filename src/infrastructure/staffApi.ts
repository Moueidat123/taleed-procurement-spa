import { api } from './api';
import type { Answer, Framework } from './companyApi';
import type { Band } from '../domain/types';

interface Wrapped<T> { data: T }
export type Stage = 'not_started' | 'in_progress' | 'submitted';
export interface CycleSummary { id: string; title: string; status: string; frameworkVersion: string | null }
export interface PortfolioSummary {
  cycle: CycleSummary; submittedCount: number; inProgressCount: number; averageOverall: number | null;
  bands: Record<Band, number>; domains: { key: string; name: string; average: number; bands: Record<Band, number> }[];
}
export interface OrgRow { id: string; name: string; country: string; size: string; active: boolean; stage: Stage; answeredCount: number; overall: number | null; band: Band | null }
export interface Page<T> { data: T[]; meta: { total: number; page: number; perPage: number; lastPage: number } }
export interface OrgFilters { cycleId?: string; search?: string; stage?: Stage | ''; band?: Band | ''; page?: number; perPage?: number }
export interface OrgDetail {
  id: string; name: string; country: string; size: string; registrationId: string | null; active: boolean; isTest: boolean;
  submissions: { id: string; cycleId: string; revisionNumber: number; effective: boolean; isCorrection: boolean; overall: number | null; band: Band | null; submittedAt: string | null }[];
}
export interface Comparison { cycle: CycleSummary; companies: { id: string; name: string; revisionId: string; overall: number; band: Band; domains: { key: string; name: string; score: number; band: Band }[] }[] }
export interface ExportRow { company: string; country: string; size: string; revisionId: string; revisionNumber: number; submittedAt: string | null; overall: number; band: Band; domains: { key: string; name: string; score: number }[] }

/** Frozen submission snapshot, exactly as stored at submit time (never recomputed). */
export interface SnapshotDomain { key: string; name: string; order: number; yes: number; score: number; band: Band; actions: { id: string; text: string; sourceCell?: string }[] }
export interface SubmissionSnapshot {
  framework: Pick<Framework, 'version' | 'title' | 'domains'> & { interpretations: unknown };
  company: { id: string; name: string; country: string; size: string; registrationId: string | null };
  respondent: { id: string; name: string; email: string; jobTitle: string | null };
  revision: { id: string; number: number; parentId: string | null; correctionReason: string | null };
  answers: Record<string, Answer>;
  result: { yes: number; overall: number; band: Band; domains: SnapshotDomain[]; priorities: string[]; hasTie: boolean; interpretation: string };
  submittedAt: string;
}
export interface SubmissionView { revisionId: string; checksum: string; snapshot: SubmissionSnapshot; effective?: boolean; organizationId?: string }

const clean = (f: object) => Object.fromEntries(Object.entries(f).filter(([, v]) => v !== '' && v !== undefined));

export const staffApi = api.injectEndpoints({
  endpoints: (build) => ({
    portfolio: build.query<PortfolioSummary, string | void>({
      query: (cycleId) => ({ url: '/staff/portfolio', params: cycleId ? { cycleId } : {} }),
      transformResponse: (r: Wrapped<PortfolioSummary>) => r.data, providesTags: ['Staff'],
    }),
    staffOrganizations: build.query<Page<OrgRow>, OrgFilters>({
      query: (f) => ({ url: '/staff/organizations', params: clean(f) }), providesTags: ['Staff'],
    }),
    staffOrganization: build.query<OrgDetail, string>({
      query: (id) => `/staff/organizations/${encodeURIComponent(id)}`,
      transformResponse: (r: Wrapped<OrgDetail>) => r.data, providesTags: ['Staff'],
    }),
    staffSubmission: build.query<SubmissionView, string>({
      query: (id) => `/staff/assessments/${encodeURIComponent(id)}`, transformResponse: (r: Wrapped<SubmissionView>) => r.data,
    }),
    ownResult: build.query<SubmissionView, string>({
      query: (id) => `/assessments/${encodeURIComponent(id)}/result`, transformResponse: (r: Wrapped<SubmissionView>) => r.data,
    }),
    compare: build.mutation<Comparison, { organizationIds: string[]; cycleId?: string }>({
      query: (body) => ({ url: '/staff/comparisons', method: 'POST', body }), transformResponse: (r: Wrapped<Comparison>) => r.data,
    }),
    exportRows: build.mutation<{ cycleId: string; frameworkVersion: string | null; rows: ExportRow[] }, { format: 'csv' | 'xlsx'; cycleId?: string; band?: string; search?: string }>({
      query: (body) => ({ url: '/staff/exports', method: 'POST', body: clean(body) }),
      transformResponse: (r: Wrapped<{ cycleId: string; frameworkVersion: string | null; rows: ExportRow[] }>) => r.data,
    }),
    openCorrection: build.mutation<unknown, { revisionId: string; reason: string }>({
      query: ({ revisionId, reason }) => ({ url: `/staff/assessments/${encodeURIComponent(revisionId)}/corrections`, method: 'POST', body: { reason } }),
      invalidatesTags: ['Staff'],
    }),
    setOrganizationActive: build.mutation<unknown, { id: string; active: boolean }>({
      query: ({ id, active }) => ({ url: `/staff/organizations/${encodeURIComponent(id)}/access`, method: 'PATCH', body: { active } }),
      invalidatesTags: ['Staff'],
    }),
  }),
});

export const {
  usePortfolioQuery, useStaffOrganizationsQuery, useStaffOrganizationQuery, useStaffSubmissionQuery, useOwnResultQuery,
  useCompareMutation, useExportRowsMutation, useOpenCorrectionMutation, useSetOrganizationActiveMutation,
} = staffApi;
