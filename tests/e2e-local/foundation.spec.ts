import { expect, test, type Page } from '@playwright/test';
import process from 'node:process';

// Requires: ops/local/dev up, and a synthetic analyst created by
//   ops/local/dev artisan procurement:dev:create-app-user e2e-analyst@example.test --role=analyst --password=<E2E_ANALYST_PASSWORD>
const email = 'e2e-analyst@example.test';
const password = process.env.E2E_ANALYST_PASSWORD ?? '';

type ApiResult = { status: number; body: unknown };

async function api(page: Page, method: string, path: string, body?: unknown, withXsrf = true): Promise<ApiResult> {
  return page.evaluate(async ({ method, path, body, withXsrf }) => {
    const xsrf = decodeURIComponent(document.cookie.split('; ').find((c) => c.startsWith('XSRF-TOKEN='))?.split('=')[1] ?? '');
    const headers: Record<string, string> = { Accept: 'application/json', 'Content-Type': 'application/json' };
    if (withXsrf && xsrf) headers['X-XSRF-TOKEN'] = xsrf;
    const res = await fetch(path, { method, headers, credentials: 'same-origin', body: body === undefined ? undefined : JSON.stringify(body) });
    const text = await res.text();
    let parsed: unknown = text;
    try { parsed = JSON.parse(text); } catch { /* non-JSON */ }
    return { status: res.status, body: parsed };
  }, { method, path, body, withXsrf });
}

test('trusted HTTPS origin serves the approved SPA without console errors', async ({ page }) => {
  const errors: string[] = [];
  page.on('pageerror', (e) => errors.push(e.message));
  page.on('console', (m) => { if (m.type() === 'error') errors.push(m.text()); });

  const response = await page.goto('/');
  expect(response?.status()).toBe(200);
  expect(page.url()).toMatch(/^https:\/\//);
  await expect(page.getByRole('heading', { level: 1 })).toContainText('Stronger procurement');
  expect(errors).toEqual([]);
});

// Forged cross-site / same-site / header-less POSTs without a token: see
// ops/local/checks/https-smoke.sh (needs DNS control that Node's request context lacks).

test('same-origin API: session login, secure host-only cookie, logout', async ({ page, context }) => {
  test.skip(!password, 'Set E2E_ANALYST_PASSWORD for the synthetic analyst.');
  await page.goto('/');

  expect((await api(page, 'GET', '/sanctum/csrf-cookie')).status).toBe(204);
  const xsrf = (await context.cookies()).find((c) => c.name === 'XSRF-TOKEN');
  expect(xsrf).toMatchObject({ secure: true, httpOnly: false }); // readable by the SPA by design

  const wrong = await api(page, 'POST', '/api/procurement/v1/auth/login', { email, password: 'wrong-password-123' });
  expect(wrong.status).toBe(422);

  expect((await api(page, 'POST', '/api/procurement/v1/auth/login', { email, password })).status).toBe(200);

  const me = await api(page, 'GET', '/api/procurement/v1/auth/me');
  expect(me.status).toBe(200);
  expect(me.body).toMatchObject({ data: { email, role: 'analyst' } });

  const session = (await context.cookies()).find((c) => c.name === 'taleed_procurement_session');
  expect(session).toBeDefined();
  expect(session).toMatchObject({ httpOnly: true, secure: true, sameSite: 'Lax' });
  expect(session?.domain.startsWith('.')).toBe(false); // host-only

  expect((await api(page, 'POST', '/api/procurement/v1/auth/logout')).status).toBe(204);
  expect((await api(page, 'GET', '/api/procurement/v1/auth/me')).status).toBe(401);
});

test('Statamic page is server-rendered with the approved design tokens', async ({ page }) => {
  await page.goto('/pages/privacy');
  await expect(page.getByRole('heading', { level: 1 })).toHaveText('Privacy notice');
  await expect(page.getByRole('note')).toHaveText('Placeholder content — pending approval');
  const background = await page.evaluate(() => getComputedStyle(document.body).backgroundColor);
  expect(background).toBe('rgb(238, 241, 245)'); // --bg from src/styles/tokens.css
});

test('Statamic control panel login is available on /cp', async ({ page }) => {
  const response = await page.goto('/cp/auth/login');
  expect(response?.status()).toBe(200);
});

test('CMS administrator signs in to /cp, and that session is rejected by the business API', async ({ page }) => {
  const cmsPassword = process.env.E2E_CMS_PASSWORD ?? '';
  test.skip(!cmsPassword, 'Set E2E_CMS_PASSWORD for the local CMS administrator.');

  await page.goto('/cp/auth/login');
  await page.waitForLoadState('networkidle'); // Inertia/Vue form must be mounted before filling
  // Statamic CP inputs are not label-associated; target by input type.
  await page.locator('input[type="email"], input[name="email"]').first().fill('cms-admin@example.test');
  await page.locator('input[type="password"]').first().fill(cmsPassword);
  const loginPost = page.waitForResponse((r) => r.url().endsWith('/cp/auth/login') && r.request().method() === 'POST');
  await page.getByRole('button', { name: 'Continue' }).click();
  expect((await loginPost).status()).toBeLessThan(500);
  // The Statamic 6 CP is an Inertia app: navigation after login is client-side.
  await expect(page).toHaveURL(/\/cp\/dashboard$/, { timeout: 15_000 });

  const me = await api(page, 'GET', '/api/procurement/v1/auth/me');
  expect(me.status).toBe(401);
  expect(me.body).toMatchObject({ error: { code: 'unauthenticated' } });
});
