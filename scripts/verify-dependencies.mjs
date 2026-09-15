#!/usr/bin/env node
/** Read-only registry check. Does NOT update package.json or fabricate a lockfile. */
import { readFile } from 'node:fs/promises';
import { execFileSync } from 'node:child_process';
import process from 'node:process';

const manifest = JSON.parse(await readFile(new URL('../package.json', import.meta.url), 'utf8'));
const configured = { ...manifest.dependencies, ...manifest.devDependencies };
const packages = process.argv.includes('--all')
  ? Object.keys(configured)
  : ['react', 'react-dom', '@reduxjs/toolkit', 'react-redux', 'vite', '@vitejs/plugin-react'];
let failures = 0;
const npmCli = process.env.npm_execpath;
function npmView(spec, field) {
  const args = ['view', spec, field, '--json', '--fetch-retries=0', '--fetch-timeout=15000'];
  const result = npmCli
    ? execFileSync(process.execPath, [npmCli, ...args], { encoding: 'utf8', timeout: 20_000, stdio: ['ignore', 'pipe', 'pipe'] })
    : execFileSync(process.platform === 'win32' ? 'npm.cmd' : 'npm', args, { encoding: 'utf8', timeout: 20_000, stdio: ['ignore', 'pipe', 'pipe'], shell: process.platform === 'win32' });
  return JSON.parse(result);
}
console.log(`Dependency verification: ${new Date().toISOString()}`);
for (const name of packages) {
  try {
    const latest = npmView(name, 'dist-tags.latest');
    const resolved = npmView(`${name}@${configured[name]}`, 'version');
    console.log(JSON.stringify({ name, configured: configured[name], matchingVersions: resolved, latestStable: latest }));
  } catch (error) {
    failures += 1;
    console.error(`${name}: NOT VERIFIED. ${error instanceof Error ? error.message.split('\n')[0] : 'Registry request failed.'}`);
  }
}
console.log('Keep React and React DOM aligned. Review peer dependencies before upgrades. Commit the real npm-generated package-lock.json.');
process.exitCode = failures ? 1 : 0;
