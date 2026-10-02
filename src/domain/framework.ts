import source from '../data/framework.json';
import type { Framework } from './types';
import { BANDS } from './scoring';

function fail(message: string): never { throw new Error(message); }
function object(value: unknown): asserts value is Record<string, unknown> {
  if (!value || typeof value !== 'object' || Array.isArray(value)) fail('Expected a data object.');
}
function string(value: unknown, max = 1000): asserts value is string {
  if (typeof value !== 'string' || value.length > max) fail('A text field has an invalid type or length.');
}
function keys(value: Record<string, unknown>, allowed: string[]): void {
  if (Object.keys(value).some((key) => !allowed.includes(key)) || allowed.some((key) => !(key in value))) fail('Unexpected or missing data fields.');
}
export function assertFramework(value: unknown): asserts value is Framework {
  object(value);
  keys(value, ['version','title','attribution','sourceFile','sourceSha256','sourceStatus','domains','interpretations']);
  for (const field of ['version','title','attribution','sourceFile','sourceSha256','sourceStatus']) string(value[field]);
  if (!/^\d+\.\d+\.\d+$/.test(value.version as string)) fail('Invalid framework version.');
  const names = ['category_management','spend_analysis','strategic_sourcing','supplier_relationship_management'];
  if (!Array.isArray(value.domains) || value.domains.length !== 4) fail('A framework must have four domains.');
  const seen = new Set<string>();
  (value.domains as unknown[]).forEach((domain, index) => {
    object(domain); keys(domain, ['key','name','order','questions','recommendations']);
    string(domain.key); string(domain.name);
    if (domain.key !== names[index] || domain.order !== index + 1) fail('Preserve domain keys and source order.');
    if (!Array.isArray(domain.questions) || domain.questions.length !== 10) fail('Each domain needs ten questions.');
    (domain.questions as unknown[]).forEach((question, i) => {
      object(question); keys(question, ['id','text','sourceCell']);
      string(question.id); string(question.text, 2000); string(question.sourceCell);
      if (question.id !== `${index + 1}.${i + 1}` || seen.has(question.id)) fail('Invalid or duplicate string question ID.');
      seen.add(question.id);
    });
    object(domain.recommendations); keys(domain.recommendations, BANDS);
    for (const band of BANDS) {
      const actions = domain.recommendations[band];
      if (!Array.isArray(actions) || actions.length !== 4) fail('Each domain/band must contain four actions.');
      (actions as unknown[]).forEach((action, i) => {
        object(action); keys(action, ['id','text','sourceCell']);
        string(action.id); string(action.text, 2000); string(action.sourceCell);
        if (action.id !== `${domain.key}.${band}.${i+1}`) fail('Invalid recommendation ID.');
      });
    }
  });
  object(value.interpretations); keys(value.interpretations, BANDS);
  for (const band of BANDS) string(value.interpretations[band], 3000);
}

assertFramework(source);
/** The approved source framework (v1.0.0), used for public, illustrative content only. */
export const SOURCE_FRAMEWORK: Framework = source;
