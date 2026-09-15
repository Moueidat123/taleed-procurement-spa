#!/usr/bin/env node
/** Syntax and relative-import validation only; not a substitute for tsc/build. */
import ts from 'typescript';
import fs from 'node:fs';
import path from 'node:path';
import process from 'node:process';

const root = process.cwd();
function walk(directory) {
  return fs.readdirSync(directory, { withFileTypes: true }).flatMap((entry) => {
    const name = path.join(directory, entry.name);
    return entry.isDirectory() ? walk(name) : [name];
  });
}
const files = walk(path.join(root, 'src')).filter((file) => /\.tsx?$/.test(file));
const additional = ['vite.config.ts', 'playwright.config.ts', 'tests/e2e/prototype.spec.ts'];
let failures = 0;
for (const file of [...files, ...additional.map((name) => path.join(root, name))]) {
  const input = fs.readFileSync(file, 'utf8');
  const result = ts.transpileModule(input, {
    fileName: file,
    reportDiagnostics: true,
    compilerOptions: { target: ts.ScriptTarget.ES2022, module: ts.ModuleKind.ESNext, jsx: ts.JsxEmit.ReactJSX, isolatedModules: true },
  });
  for (const diagnostic of result.diagnostics ?? []) {
    if (diagnostic.category !== ts.DiagnosticCategory.Error) continue;
    failures += 1;
    console.error(ts.formatDiagnostics([diagnostic], { getCanonicalFileName: (name) => name, getCurrentDirectory: () => root, getNewLine: () => '\n' }));
  }
  for (const match of input.matchAll(/(?:from\s*|import\s*\(|import\s*)['"](\.[^'"]+)['"]/g)) {
    const target = path.resolve(path.dirname(file), match[1]);
    if (!['', '.ts', '.tsx', '.json', '.css'].some((extension) => fs.existsSync(target + extension))) {
      failures += 1;
      console.error(`Missing relative import: ${path.relative(root, file)} -> ${match[1]}`);
    }
  }
}
console.log(`${files.length} source files + ${additional.length} config/test files; ${failures} syntax/relative-import errors.`);
process.exitCode = failures ? 1 : 0;
