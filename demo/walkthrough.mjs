import { chromium } from '@playwright/test';
import { fileURLToPath } from 'node:url';
import { dirname, join } from 'node:path';

const __dirname = dirname(fileURLToPath(import.meta.url));
const SHOTS = join(__dirname, 'screenshots');
const BASE = 'http://127.0.0.1:5173';
const PASSWORD = 'TaleedDemo!2026';
const KEY = 'taleed.procurement.prototype.v1';

let step = 0;
const shot = async (page, name) => {
  step += 1;
  const file = join(SHOTS, `${String(step).padStart(2, '0')}-${name}.png`);
  await page.screenshot({ path: file, fullPage: true });
  console.log('  📸', `${String(step).padStart(2, '0')}-${name}.png`);
};

const login = async (page, email) => {
  await page.goto(`${BASE}/#/login`);
  await page.getByLabel('Email address', { exact: true }).fill(email);
  await page.getByLabel('Demo password', { exact: true }).fill(PASSWORD);
  await page.getByRole('button', { name: 'Sign in', exact: true }).click();
  await page.locator('main.main-content').waitFor();
};

const answerAll = async (page, draftId, value = 'yes') => {
  for (let section = 1; section <= 4; section += 1) {
    await page.goto(`${BASE}/#/app/assessment/${draftId}/${section}`);
    for (let q = 1; q <= 10; q += 1) {
      const input = page.locator(`input[name="answer-${section}.${q}"][value="${value}"]`);
      await input.click();
      await page.waitForTimeout(30);
    }
  }
};

(async () => {
  const browser = await chromium.launch();
  const context = await browser.newContext({ viewport: { width: 1440, height: 900 } });
  const page = await context.newPage();

  // Make the run deterministic: reset local data first.
  await page.goto(BASE);
  await page.evaluate((k) => localStorage.removeItem(k), KEY);
  await page.reload();
  await page.locator('.public-page').waitFor();

  console.log('\n=== 1. PUBLIC LANDING ===');
  await shot(page, 'landing');

  console.log('\n=== 2. CHAMPION — REGISTER NEW COMPANY ===');
  await page.goto(`${BASE}/#/register`);
  await page.getByLabel('Full name', { exact: true }).fill('Demo Procurement Leader');
  await page.getByLabel('Work email', { exact: true }).fill('demo-champion@example.com');
  await page.getByLabel('Job title', { exact: true }).fill('Head of Procurement');
  await page.getByLabel('Dummy password', { exact: true }).fill('DemoExample!2026');
  await page.getByLabel('Confirm dummy password', { exact: true }).fill('DemoExample!2026');
  await page.locator('input[type="checkbox"]').check();
  await shot(page, 'register-filled');
  await page.getByRole('button', { name: 'Create demo account' }).click();

  await page.getByLabel('Verification code').waitFor();
  await page.getByLabel('Verification code').fill('123456');
  await shot(page, 'verify');
  await page.getByRole('button', { name: 'Verify and continue' }).click();

  console.log('\n=== 3. CHAMPION — COMPANY PROFILE ===');
  await page.getByLabel('Legal company name *', { exact: true }).fill('Demonstration Company');
  await page.getByLabel('Country *', { exact: true }).selectOption('Saudi Arabia');
  await page.getByLabel('Company size *', { exact: true }).selectOption('11–50');
  await page.locator('input[type="checkbox"]').check();
  await shot(page, 'company-profile');
  await page.getByRole('button', { name: 'Save and continue' }).click();
  await page.waitForURL(/#\/app\/dashboard$/);

  console.log('\n=== 4. CHAMPION — DASHBOARD ===');
  await shot(page, 'champion-dashboard');

  // Start a fresh assessment for THIS champion, then read its id.
  await page.getByRole('button', { name: /Start assessment/ }).click();
  await page.waitForURL(/#\/app\/assessment\/.+\/1$/);
  const draftId = await page.evaluate((k) => {
    const db = JSON.parse(localStorage.getItem(k));
    const user = Object.values(db.users).find((u) => u.email === 'demo-champion@example.com');
    const draft = Object.values(db.assessments).find((a) => a.status === 'draft' && a.orgId === user.orgId);
    return draft?.id;
  }, KEY);
  console.log('  draft id:', draftId);

  console.log('\n=== 5. CHAMPION — ANSWER SECTION 1 ===');
  await page.goto(`${BASE}/#/app/assessment/${draftId}/1`);
  await page.locator('input[name="answer-1.1"][value="yes"]').click();
  await page.locator('input[name="answer-1.2"][value="no"]').click();
  await shot(page, 'answering-questions');

  console.log('\n=== 6. CHAMPION — COMPLETE ALL 40 ===');
  await answerAll(page, draftId, 'yes');
  await page.goto(`${BASE}/#/app/assessment/${draftId}/review`);
  await shot(page, 'review-answers');
  await page.locator('input[type="checkbox"]').check();
  await page.getByRole('button', { name: 'Submit assessment', exact: true }).click();
  await page.getByRole('button', { name: 'Confirm submission', exact: true }).click();
  await page.waitForURL(new RegExp(`#/app/results/${draftId}$`));

  console.log('\n=== 7. CHAMPION — RESULTS ===');
  await shot(page, 'champion-results');

  console.log('\n=== 8. CHAMPION — RECOMMENDATIONS ===');
  await page.goto(`${BASE}/#/app/recommendations/${draftId}`);
  await shot(page, 'champion-recommendations');

  console.log('\n=== 9. CHAMPION — REPORT ===');
  await page.goto(`${BASE}/#/app/report/${draftId}`);
  await shot(page, 'champion-report');

  console.log('\n=== 10. CHAMPION — HISTORY ===');
  await page.goto(`${BASE}/#/app/history`);
  await shot(page, 'champion-history');

  // Sign out.
  await page.getByRole('button', { name: 'Sign out' }).click();

  console.log('\n=== 11. ANALYST — PORTFOLIO ===');
  await login(page, 'analyst@example.com');
  await page.getByRole('heading', { name: 'Procurement portfolio' }).waitFor();
  await shot(page, 'analyst-portfolio');

  console.log('\n=== 12. ANALYST — READ-ONLY RESULT ===');
  await page.goto(`${BASE}/#/app/organizations`);
  await shot(page, 'analyst-organizations');
  await page.goto(`${BASE}/#/app/compare`);
  await shot(page, 'analyst-compare');
  await page.getByRole('button', { name: 'Sign out' }).click();

  console.log('\n=== 13. SUPER ADMIN — PORTFOLIO + EXPORTS ===');
  await login(page, 'admin@example.com');
  await page.getByRole('heading', { name: 'Procurement portfolio' }).waitFor();
  await shot(page, 'admin-portfolio');

  console.log('\n=== 14. SUPER ADMIN — ORGANIZATIONS + CORRECTION ===');
  await page.goto(`${BASE}/#/app/organizations/org-namaa`);
  await shot(page, 'admin-organization-detail');

  console.log('\n=== 15. SUPER ADMIN — PEOPLE & ACCESS ===');
  await page.goto(`${BASE}/#/app/access`);
  await shot(page, 'admin-access');

  await browser.close();
  console.log('\n✅ Walkthrough complete —', step, 'screenshots in demo/screenshots/\n');
})();
