import { expect, test, type Page } from '@playwright/test';
import { createHmac } from 'node:crypto';
import { execFileSync } from 'node:child_process';
import { readFileSync } from 'node:fs';

// Phase 3 browser journeys against the local Docker stack + MySQL.
// A fresh synthetic dataset is seeded per run (procurement:dev:seed-e2e, local only).
const PASSWORD = `E2e-${Date.now().toString(36)}-Passw0rd!`;
interface Seed {
  run: string;
  champions: Record<'alpha' | 'bravo' | 'charlie' | 'fresh', { email: string; company: string; orgId: string; revisionId?: string }>;
  staff: Record<'analyst' | 'exporter' | 'admin', { email: string; totpSecret: string }>;
}
let seed: Seed;

test.describe.configure({ mode: 'serial' });
test.beforeAll(() => {
  const run = `r${Date.now().toString(36).slice(-8)}`;
  const out = execFileSync('ops/local/dev', ['artisan', 'procurement:dev:seed-e2e', `--run=${run}`, `--password=${PASSWORD}`]).toString();
  const line = out.split('\n').reverse().find((l) => l.trim().startsWith('{'));
  if (!line) throw new Error(`Seed failed:\n${out}`);
  seed = JSON.parse(line) as Seed;
});

function totp(secret: string, at = Date.now()): string {
  const alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567'; let bits = '';
  for (const c of secret.replace(/=+$/, '')) bits += alphabet.indexOf(c).toString(2).padStart(5, '0');
  const key = Buffer.from(bits.match(/.{8}/g)!.map((b) => parseInt(b, 2)));
  const counter = Buffer.alloc(8); counter.writeBigUInt64BE(BigInt(Math.floor(at / 30000)));
  const h = createHmac('sha1', key).update(counter).digest(); const o = h[h.length - 1]! & 15;
  return String((h.readUInt32BE(o) & 0x7fffffff) % 1_000_000).padStart(6, '0');
}

async function signIn(page: Page, email: string, secret?: string) {
  await page.goto('/#/login');
  await page.getByLabel('Email address').fill(email);
  await page.getByLabel('Password', { exact: true }).fill(PASSWORD);
  await page.getByRole('button', { name: 'Sign in' }).click();
  if (secret) {
    await page.getByLabel('Authentication code').fill(totp(secret));
    await page.getByRole('button', { name: 'Verify and sign in' }).click();
  }
  await expect(page).toHaveURL(/#\/app\//);
}

async function api(page: Page, method: string, path: string, body?: unknown) {
  return page.evaluate(async ({ method, path, body }) => {
    const xsrf = decodeURIComponent(document.cookie.split('; ').find((c) => c.startsWith('XSRF-TOKEN='))?.split('=')[1] ?? '');
    const r = await fetch(`/api/procurement/v1${path}`, { method, credentials: 'same-origin',
      headers: { Accept: 'application/json', 'Content-Type': 'application/json', 'X-XSRF-TOKEN': xsrf },
      body: body === undefined ? undefined : JSON.stringify(body) });
    return { status: r.status, body: await r.json().catch(() => null) as { data?: Record<string, unknown> } | null };
  }, { method, path, body });
}

test('champion answers with autosave, resolves a version conflict, submits and sees frozen results', async ({ page }) => {
  const errors: string[] = []; page.on('pageerror', (e) => errors.push(e.message));
  await signIn(page, seed.champions.fresh.email);
  await page.getByRole('button', { name: 'Start assessment' }).click();
  await expect(page).toHaveURL(/#\/app\/assessment\/[^/]+\/1/);
  const id = /assessment\/([^/]+)\//.exec(page.url())![1]!;

  // Autosave: answers persist across a reload.
  for (const q of ['1.1', '1.2', '1.3']) await page.locator(`[id="question-${q}"]`).getByText('Yes', { exact: true }).click();
  await expect(page.getByRole('status')).toHaveText('All answers saved');
  await page.reload();
  await expect(page.locator('[id="question-1.3"] input[value="yes"]')).toBeChecked();

  // Another session saves first; the next local edit hits a 409 and the user keeps their answer.
  const current = await api(page, 'GET', `/assessments/${id}`);
  const version = current.body!.data!.version as number;
  expect((await api(page, 'PATCH', `/assessments/${id}/answers`, { expectedVersion: version, answers: { '1.5': 'no' } })).status).toBe(200);
  await page.locator('[id="question-1.4"]').getByText('No', { exact: true }).click();
  await expect(page.getByText('This draft changed somewhere else')).toBeVisible();
  await page.getByRole('button', { name: 'Keep my answers' }).click();
  await expect(page.getByRole('status')).toHaveText('All answers saved');
  const after = await api(page, 'GET', `/assessments/${id}`);
  expect(after.body!.data!.answers).toMatchObject({ '1.4': 'no', '1.5': 'no' });

  // Fill the rest server-side, then review and submit in the UI.
  const answers: Record<string, string> = {};
  for (let d = 1; d <= 4; d++) for (let q = 1; q <= 10; q++) answers[`${d}.${q}`] = (d + q) % 3 ? 'yes' : 'no';
  const v2 = after.body!.data!.version as number;
  expect((await api(page, 'PATCH', `/assessments/${id}/answers`, { expectedVersion: v2, answers })).status).toBe(200);
  await page.goto(`/#/app/assessment/${id}/review`);
  await page.reload(); // answers were written outside the app above
  await expect(page.getByText('Ready to submit')).toBeVisible();
  await page.getByRole('checkbox').check();
  await page.getByRole('button', { name: /Submit assessment/ }).click();
  await page.getByRole('button', { name: 'Confirm submission' }).click();
  await expect(page).toHaveURL(new RegExp(`#/app/results/${id}`));
  await expect(page.getByRole('heading', { name: 'Your procurement maturity' })).toBeVisible();
  await expect(page.getByText('Effective submission')).toBeVisible();

  // Report preview offers browser print/PDF.
  await page.goto(`/#/app/report/${id}`);
  await expect(page.getByRole('heading', { level: 1, name: seed.champions.fresh.company })).toBeVisible();
  await expect(page.getByRole('button', { name: 'Print / save PDF' })).toBeVisible();
  // Submitted answers are read-only on the server too.
  expect((await api(page, 'PATCH', `/assessments/${id}/answers`, { expectedVersion: 99, answers: { '1.1': 'no' } })).status).toBeGreaterThanOrEqual(409);
  expect(errors).toEqual([]);
});

test('analyst filters on the server, compares companies and cannot export', async ({ page }) => {
  await signIn(page, seed.staff.analyst.email, seed.staff.analyst.totpSecret);
  await page.goto('/#/app/organizations');
  await page.getByLabel('Company name').fill(seed.run);
  await expect(page.locator('tbody tr')).toHaveCount(4);
  await page.getByLabel('Assessment stage').selectOption('submitted');
  await expect(page.locator('tbody tr')).toHaveCount(4); // alpha, bravo, charlie + fresh submitted in the previous test
  await page.getByLabel('Company name').fill(`E2E Bravo ${seed.run}`);
  await expect(page.locator('tbody tr')).toHaveCount(1);

  await page.goto(`/#/app/compare?ids=${seed.champions.alpha.orgId},${seed.champions.bravo.orgId}`);
  const table = page.getByRole('table');
  await expect(table.getByRole('columnheader', { name: seed.champions.alpha.company })).toBeVisible();
  await expect(table.getByRole('columnheader', { name: seed.champions.bravo.company })).toBeVisible();
  await expect(table.getByRole('row', { name: /Overall maturity/ })).toContainText('60.0%'); // Bravo 6/4/8/6

  await page.goto('/#/app/portfolio');
  await expect(page.getByRole('button', { name: 'CSV' })).toBeDisabled();
  expect((await api(page, 'POST', '/staff/exports', { format: 'csv' })).status).toBe(403);
});

test('analyst with the export grant downloads CSV and XLSX of effective submissions', async ({ page }) => {
  await signIn(page, seed.staff.exporter.email, seed.staff.exporter.totpSecret);
  await page.goto(`/#/app/portfolio?q=${encodeURIComponent(seed.run)}`);
  const [csv] = await Promise.all([page.waitForEvent('download'), page.getByRole('button', { name: 'CSV' }).click()]);
  expect(csv.suggestedFilename()).toMatch(/\.csv$/);
  const text = readFileSync(await csv.path()).toString('utf8');
  expect(text.startsWith('\uFEFF"Company"')).toBe(true);
  for (const c of ['alpha', 'bravo', 'charlie'] as const) expect(text).toContain(seed.champions[c].company);
  const [xlsx] = await Promise.all([page.waitForEvent('download'), page.getByRole('button', { name: /Export Excel/ }).click()]);
  expect(xlsx.suggestedFilename()).toMatch(/\.xlsx$/);
  expect(readFileSync(await xlsx.path()).subarray(0, 2).toString()).toBe('PK');
});

test('super admin opens a correction; the original result stays effective', async ({ page }) => {
  await signIn(page, seed.staff.admin.email, seed.staff.admin.totpSecret);
  await page.goto(`/#/app/organizations/${seed.champions.charlie.orgId}`);
  await page.getByRole('button', { name: 'Open correction' }).click();
  await page.getByLabel(/Reason for correction/).fill('Local browser test: please re-check domain two answers.');
  await page.getByRole('button', { name: 'Create correction draft' }).click();
  await expect(page.getByRole('dialog')).toBeHidden();
  await expect(page.getByText('Effective', { exact: true })).toBeVisible();
});
