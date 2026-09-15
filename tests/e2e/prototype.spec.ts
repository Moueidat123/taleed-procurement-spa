import { expect, test, type Page } from '@playwright/test';
import { Buffer } from 'node:buffer';
import type { Database } from '../../src/domain/types';

const key = 'taleed.procurement.prototype.v1';
const draftId = 'assessment-sahara-2026';
async function dataset(page: Page): Promise<Database> {
  return page.evaluate((storageKey) => JSON.parse(localStorage.getItem(storageKey) ?? 'null') as Database, key);
}
async function login(page: Page, email = 'sahara@example.com'): Promise<void> {
  await page.goto('/#/login');
  await page.getByLabel('Email address', { exact: true }).fill(email);
  await page.getByLabel('Demo password', { exact: true }).fill('TaleedDemo!2026');
  await page.getByRole('button', { name: 'Sign in', exact: true }).click();
  await expect(page.locator('main.main-content')).toBeVisible();
}
async function answer(page: Page, questionId: string, value: 'yes' | 'no'): Promise<void> {
  const input = page.locator(`input[name="answer-${questionId}"][value="${value}"]`);
  await expect(input).toBeEnabled();
  await input.check();
  await expect.poll(async () => (await dataset(page)).assessments[draftId]?.answers[questionId]).toBe(value);
}

test('registration validates, verifies, and creates a company before assessment', async ({ page }) => {
  await page.goto('/#/register');
  await page.getByRole('button', { name: 'Create demo account' }).click();
  await expect(page.getByText('Enter your full name.', { exact: true })).toBeVisible();
  await page.getByLabel('Full name', { exact: true }).fill('Demo Procurement Leader');
  await page.getByLabel('Work email', { exact: true }).fill('demo-new@example.com');
  await page.getByLabel('Job title', { exact: true }).fill('Head of Procurement');
  await page.getByLabel('Dummy password', { exact: true }).fill('DemoExample!2026');
  await page.getByLabel('Confirm dummy password', { exact: true }).fill('DemoExample!2026');
  await page.locator('input[type="checkbox"]').check();
  await page.getByRole('button', { name: 'Create demo account' }).click();
  await expect(page).toHaveURL(/#\/verify$/);
  await page.getByLabel('Verification code').fill('123456');
  await page.getByRole('button', { name: 'Verify and continue' }).click();
  await page.getByLabel('Legal company name *', { exact: true }).fill('Demonstration Company Six');
  await page.getByLabel('Country *', { exact: true }).selectOption('Saudi Arabia');
  await page.getByLabel('Sector *', { exact: true }).selectOption('Technology');
  await page.getByLabel('Company size *', { exact: true }).selectOption('11–50');
  await page.locator('input[type="checkbox"]').check();
  await page.getByRole('button', { name: 'Save and continue' }).click();
  await expect(page).toHaveURL(/#\/app\/dashboard$/);
  const db = await dataset(page);
  expect(Object.values(db.users).find((user) => user.email === 'demo-new@example.com')?.role).toBe('champion');
  expect(JSON.stringify(db)).not.toContain('DemoExample!2026');
});

test('mixed answers survive reload and clearing restores unanswered state', async ({ page }) => {
  await login(page);
  await page.goto(`/#/app/assessment/${draftId}/2`);
  await answer(page, '2.3', 'no');
  await page.reload();
  await expect(page.locator('input[name="answer-2.3"][value="no"]')).toBeChecked();
  await page.getByRole('button', { name: 'Clear answer 2.3', exact: true }).click();
  await expect.poll(async () => (await dataset(page)).assessments[draftId]?.answers['2.3']).toBeNull();
});

test('incomplete review blocks submission and focuses the missing question', async ({ page }) => {
  await login(page);
  await page.goto(`/#/app/assessment/${draftId}/review`);
  await expect(page.getByRole('button', { name: 'Submit assessment', exact: true })).toBeDisabled();
  await page.getByRole('link', { name: 'Answer missing question 2.3', exact: true }).click();
  await expect(page.locator('input[name="answer-2.3"][value="yes"]')).toBeFocused();
  expect((await dataset(page)).assessments[draftId]?.snapshot).toBeNull();
});

test('complete four-section journey submits once and shows 16 recommendations', async ({ page }) => {
  await login(page);
  for (let section = 1; section <= 4; section += 1) {
    await page.goto(`/#/app/assessment/${draftId}/${section}`);
    for (let question = 1; question <= 10; question += 1) await answer(page, `${section}.${question}`, 'yes');
  }
  await page.getByRole('button', { name: 'Review all answers', exact: true }).click();
  await page.locator('input[type="checkbox"]').check();
  await page.getByRole('button', { name: 'Submit assessment', exact: true }).click();
  await page.getByRole('button', { name: 'Confirm submission', exact: true }).click();
  await expect(page).toHaveURL(new RegExp(`#/app/results/${draftId}$`));
  expect((await dataset(page)).assessments[draftId]?.snapshot?.result.overall).toBe(100);
  await page.goto(`/#/app/recommendations/${draftId}`);
  await expect(page.locator('.action-list li')).toHaveCount(16);
  await page.goto(`/#/app/assessment/${draftId}/1`);
  await expect(page).toHaveURL(new RegExp(`#/app/results/${draftId}$`));
});

test('company cannot open another company result or staff controls', async ({ page }) => {
  await login(page);
  await page.goto('/#/app/results/assessment-namaa-2026');
  await expect(page.getByRole('heading', { name: 'No submitted result is available' })).toBeVisible();
  await page.goto('/#/app/access');
  await expect(page.getByRole('heading', { name: 'This area is not available to your role' })).toBeVisible();
});

test('analyst sees portfolio but not draft answers or exports', async ({ page }) => {
  await login(page, 'analyst@example.com');
  await expect(page.getByRole('heading', { name: 'Procurement portfolio' })).toBeVisible();
  await expect(page.getByRole('button', { name: 'CSV', exact: true })).toBeDisabled();
  await page.goto(`/#/app/results/${draftId}`);
  await expect(page.getByRole('heading', { name: 'No submitted result is available' })).toBeVisible();
});

test('manager creates a correction draft without replacing the effective submission', async ({ page }) => {
  await login(page, 'manager@example.com');
  await page.goto('/#/app/organizations/org-namaa');
  await page.getByRole('button', { name: 'Open correction', exact: true }).click();
  await page.locator('#correction-reason').fill('Demonstration: update the supplier practice answers.');
  await page.getByRole('button', { name: 'Create correction draft' }).click();
  await expect.poll(async () => Object.values((await dataset(page)).assessments).filter((a) => a.orgId === 'org-namaa' && a.status === 'draft').length).toBe(1);
  const db = await dataset(page);
  expect(db.assessments['assessment-namaa-2026']?.snapshot?.result.overall).toBe(60);
});

test('manager CSV export contains only current filtered effective submissions', async ({ page }) => {
  await login(page, 'manager@example.com');
  await page.locator('#portfolio-sector').selectOption('Logistics');
  const download = page.waitForEvent('download');
  await page.getByRole('button', { name: 'CSV', exact: true }).click();
  const file = await download;
  expect(file.suggestedFilename()).toMatch(/\.csv$/);
  const stream = await file.createReadStream();
  if (!stream) throw new Error('Download stream unavailable.');
  const chunks: Buffer[] = [];
  for await (const chunk of stream) chunks.push(Buffer.from(chunk));
  const csv = Buffer.concat(chunks).toString('utf8');
  expect(csv).toContain('Namaa Logistics');
  expect(csv).not.toContain('Atlas Energy');
});

test('storage quota failure never reports a changed answer as saved', async ({ page }) => {
  await login(page);
  await page.goto(`/#/app/assessment/${draftId}/2`);
  await page.evaluate((storageKey) => {
    const original = Storage.prototype.setItem;
    Storage.prototype.setItem = function (name: string, value: string) {
      if (name === storageKey) throw new DOMException('Simulated quota', 'QuotaExceededError');
      return original.call(this, name, value);
    };
  }, key);
  await page.locator('input[name="answer-2.3"][value="yes"]').click();
  await expect(page.getByText('Operation not completed', { exact: true })).toBeVisible();
  expect((await dataset(page)).assessments[draftId]?.answers['2.3']).toBeNull();
  await expect(page.locator('input[name="answer-2.3"][value="yes"]')).not.toBeChecked();
});

test('cross-tab writes pause a stale editor until it reloads', async ({ page, context }) => {
  await login(page);
  await page.goto(`/#/app/assessment/${draftId}/2`);
  const other = await context.newPage();
  await login(other);
  await other.goto(`/#/app/assessment/${draftId}/2`);
  await answer(page, '2.3', 'yes');
  await expect(other.getByText('A newer dataset is available in another tab.', { exact: true })).toBeVisible();
  await expect(other.locator('input[name="answer-2.4"][value="yes"]')).toBeDisabled();
  await other.getByRole('button', { name: 'Reload saved data' }).click();
  await expect(other.locator('input[name="answer-2.3"][value="yes"]')).toBeChecked();
});

test('corrupt persistence preserves original bytes and enters explicit recovery', async ({ page }) => {
  await page.goto('/');
  await page.evaluate((storageKey) => localStorage.setItem(storageKey, '{broken-json'), key);
  await page.reload();
  await expect(page.getByRole('heading', { name: 'Your local data needs attention' })).toBeVisible();
  expect(await page.evaluate((storageKey) => localStorage.getItem(storageKey), key)).toBe('{broken-json');
  await page.getByRole('button', { name: 'Reset demo dataset' }).click();
  await expect(page.getByRole('button', { name: 'Replace local dataset' })).toBeDisabled();
  await page.getByLabel('Type REPLACE to confirm').fill('REPLACE');
  await page.getByRole('button', { name: 'Replace local dataset' }).click();
  await expect(page).toHaveURL(/#\/$/);
  await expect.poll(async () => (await dataset(page)).schemaVersion).toBe(1);
});

test('invalid JSON import cannot enable dataset replacement', async ({ page }) => {
  await login(page, 'admin@example.com');
  await page.goto('/#/app/data');
  await page.getByLabel('Backup file', { exact: true }).setInputFiles({ name: 'invalid.json', mimeType: 'application/json', buffer: Buffer.from('{"schemaVersion":99}') });
  await expect(page.getByRole('button', { name: 'Review and replace local data' })).toHaveCount(0);
  expect((await dataset(page)).schemaVersion).toBe(1);
});

test('desktop and mobile layout has no document-level horizontal overflow', async ({ page }) => {
  await login(page);
  const dimensions = await page.evaluate(() => ({ viewport: window.innerWidth, document: document.documentElement.scrollWidth }));
  expect(dimensions.document).toBeLessThanOrEqual(dimensions.viewport + 1);
});

test('skip-to-content focuses the main landmark without changing hash route', async ({ page }, testInfo) => {
  test.skip(testInfo.project.name.includes('mobile'), 'Keyboard navigation check uses the desktop project.');
  await login(page);
  const url = page.url();
  await page.locator('.skip-link').focus();
  await page.keyboard.press('Enter');
  await expect(page.locator('#main-content')).toBeFocused();
  expect(page.url()).toBe(url);
});
