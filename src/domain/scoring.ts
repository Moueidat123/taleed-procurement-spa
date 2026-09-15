import type { Answers, Band, Framework, Result } from './types';

export const BAND_LABELS: Record<Band, string> = {
  foundational: 'Foundational', developing: 'Developing',
  advanced: 'Advanced', best_in_class: 'Best-in-Class',
};
export const BANDS = Object.keys(BAND_LABELS) as Band[];
export const questionIds = (framework: Framework): string[] =>
  framework.domains.flatMap((domain) => domain.questions.map((question) => question.id));
export const emptyAnswers = (framework: Framework): Answers =>
  Object.fromEntries(questionIds(framework).map((id) => [id, null]));
export const completion = (answers: Answers): number =>
  Object.values(answers).filter((value) => value === 'yes' || value === 'no').length;

export function classify(score: number): Band {
  if (!Number.isFinite(score) || score < 0 || score > 100) throw new Error('Invalid score.');
  if (score <= 40) return 'foundational';
  if (score <= 65) return 'developing';
  if (score <= 80) return 'advanced';
  return 'best_in_class';
}
export function assertAnswers(answers: Answers, framework: Framework, complete = false): void {
  const ids = questionIds(framework);
  if (!answers || typeof answers !== 'object' || Array.isArray(answers)) throw new Error('Answers must be a keyed object.');
  if (Object.keys(answers).length !== ids.length || Object.keys(answers).some((id) => !ids.includes(id))) {
    throw new Error('Answers do not match the pinned framework.');
  }
  for (const id of ids) {
    const answer = answers[id];
    if (answer !== 'yes' && answer !== 'no' && answer !== null) throw new Error(`Invalid answer for ${id}.`);
    if (complete && answer === null) throw new Error(`Answer question ${id} before submitting.`);
  }
}
/** Deterministic, unrounded, complete-only scoring. No UI or storage dependencies. */
export function scoreAssessment(answers: Answers, framework: Framework): Result {
  assertAnswers(answers, framework, true);
  const domains = framework.domains.map((domain) => {
    const yes = domain.questions.filter((question) => answers[question.id] === 'yes').length;
    const score = yes * 10;
    const band = classify(score);
    return { key: domain.key, name: domain.name, order: domain.order, yes, score, band,
      actions: structuredClone(domain.recommendations[band]) };
  });
  const yes = domains.reduce((sum, domain) => sum + domain.yes, 0);
  const overall = yes * 2.5;
  const band = classify(overall);
  return {
    yes, overall, band, domains,
    priorities: [...domains].sort((a, b) => a.score - b.score || a.order - b.order)
      .slice(0, 3).map((domain) => domain.key),
    hasTie: new Set(domains.map((domain) => domain.score)).size !== domains.length,
    interpretation: framework.interpretations[band],
  };
}
export function answersFromCounts(framework: Framework, counts: number[]): Answers {
  if (counts.length !== 4 || counts.some((n) => !Number.isInteger(n) || n < 0 || n > 10)) throw new Error('Four counts from 0–10 are required.');
  return Object.fromEntries(framework.domains.flatMap((domain, d) =>
    domain.questions.map((question, i) => [question.id, i < (counts[d] ?? 0) ? 'yes' : 'no'])));
}
