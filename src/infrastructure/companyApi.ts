import { api } from './api';

interface Wrapped<T> { data: T }
export type Answer = 'yes' | 'no' | null;
export interface Organization { id: string; displayName: string; countryCode: string; sizeBand: string; registrationId: string | null; active: boolean; authorityConfirmed: boolean }
export interface ProfileInput { displayName: string; countryCode: string; sizeBand: string; registrationId: string | null; authorityConfirmed: boolean }
export interface Cycle { id: string; title: string; frameworkVersion: string; opensAt: string; closesAt: string; timezone: string }
export interface HistoryEntry { id: string; cycleId: string; revisionNumber: number; status: 'draft' | 'submitted'; effective: boolean; isCorrection: boolean; submittedAt: string | null }
export interface Revision { id: string; assessmentId: string; cycleId: string; frameworkVersion: string; revisionNumber: number; status: 'draft' | 'submitted'; version: number; isCorrection: boolean; correctionReason: string | null; answers: Record<string, Answer>; answeredCount: number; submittedAt: string | null }
export interface FrameworkQuestion { id: string; text: string; sourceCell: string }
export interface FrameworkDomain { key: string; name: string; order: number; questions: FrameworkQuestion[]; recommendations: Record<string, { id: string; text: string; sourceCell: string }[]> }
export interface Framework { version: string; title: string; domains: FrameworkDomain[]; interpretations: unknown }

/** Champion endpoints (Phase 2B). The server scopes everything to the caller's own company. */
export const companyApi = api.injectEndpoints({
  endpoints: (build) => ({
    organization: build.query<Organization | null, void>({
      // 404 means "no profile yet", which is the normal first-run state.
      queryFn: async (_a, _api, _e, baseQuery) => {
        const r = await baseQuery('/organization');
        if (r.error) return r.error.status === 404 ? { data: null } : { error: r.error };
        return { data: (r.data as Wrapped<Organization>).data };
      },
      providesTags: ['Organization'],
    }),
    saveOrganization: build.mutation<Organization, ProfileInput>({
      query: (body) => ({ url: '/organization', method: 'PATCH', body }),
      transformResponse: (r: Wrapped<Organization>) => r.data,
      invalidatesTags: ['Organization', 'Me'],
    }),
    cycles: build.query<Cycle[], void>({ query: () => '/cycles/available', transformResponse: (r: Wrapped<Cycle[]>) => r.data, providesTags: ['Cycles'] }),
    framework: build.query<Framework, string>({ query: (v) => `/frameworks/${encodeURIComponent(v)}`, transformResponse: (r: Wrapped<Framework>) => r.data, keepUnusedDataFor: 3600 }),
    history: build.query<HistoryEntry[], void>({ query: () => '/assessments/history', transformResponse: (r: Wrapped<HistoryEntry[]>) => r.data, providesTags: ['History'] }),
    revision: build.query<Revision, string>({
      query: (id) => `/assessments/${encodeURIComponent(id)}`,
      transformResponse: (r: Wrapped<Revision>) => r.data,
      providesTags: (_r, _e, id) => [{ type: 'Assessment', id }],
    }),
    startAssessment: build.mutation<Revision, void>({
      query: () => ({ url: '/assessments', method: 'POST' }),
      transformResponse: (r: Wrapped<Revision>) => r.data,
      invalidatesTags: ['History'],
    }),
    saveAnswers: build.mutation<Revision, { id: string; expectedVersion: number; answers: Record<string, Answer> }>({
      query: ({ id, expectedVersion, answers }) => ({ url: `/assessments/${encodeURIComponent(id)}/answers`, method: 'PATCH', body: { expectedVersion, answers } }),
      transformResponse: (r: Wrapped<Revision>) => r.data,
      // Write the server copy into the cache; a refetch would race pending edits.
      async onQueryStarted({ id }, { dispatch, queryFulfilled }) {
        try { const { data } = await queryFulfilled; dispatch(companyApi.util.upsertQueryData('revision', id, data)); } catch { /* caller handles */ }
      },
    }),
    submitAssessment: build.mutation<Revision, { id: string; expectedVersion: number; idempotencyKey: string }>({
      query: ({ id, expectedVersion, idempotencyKey }) => ({
        url: `/assessments/${encodeURIComponent(id)}/submit`, method: 'POST',
        headers: { 'Idempotency-Key': idempotencyKey }, body: { expectedVersion, declaration: true },
      }),
      transformResponse: (r: Wrapped<Revision>) => r.data,
      invalidatesTags: (_r, _e, { id }) => ['History', { type: 'Assessment', id }],
    }),
  }),
});

export const {
  useOrganizationQuery, useSaveOrganizationMutation, useCyclesQuery, useFrameworkQuery,
  useHistoryQuery, useRevisionQuery, useStartAssessmentMutation, useSaveAnswersMutation, useSubmitAssessmentMutation,
} = companyApi;
