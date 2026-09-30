import { defineConfig, devices } from '@playwright/test';
import { execFileSync } from 'node:child_process';
import process from 'node:process';

// Browser checks against the running local Docker HTTPS stack (ops/local/dev up).
// No hosts-file or trust-store change: Chromium maps the hostname to 127.0.0.1
// and trusts ONLY the local certificate's public key (SPKI pin).
const host = process.env.PROCUREMENT_HOSTNAME ?? 'procurement.taleed.test';
const port = process.env.PROCUREMENT_HTTPS_PORT ?? '443';
const spki = execFileSync('sh', ['-c',
  'openssl x509 -in infra/local/certs/local.pem -pubkey -noout | openssl pkey -pubin -outform der | openssl dgst -sha256 -binary | openssl enc -base64',
]).toString().trim();

export default defineConfig({
  testDir: './tests/e2e-local',
  reporter: [['list']],
  use: {
    baseURL: `https://${host}${port === '443' ? '' : `:${port}`}`,
    trace: 'retain-on-failure',
    launchOptions: {
      args: [`--host-resolver-rules=MAP ${host} 127.0.0.1`, `--ignore-certificate-errors-spki-list=${spki}`],
    },
  },
  projects: [{ name: 'local-https-chromium', use: { ...devices['Desktop Chrome'] } }],
});
