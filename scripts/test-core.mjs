import { spawnSync } from 'node:child_process';
import { mkdirSync, writeFileSync } from 'node:fs';
const compile=spawnSync(process.platform==='win32'?'tsc.cmd':'tsc',['-p','tsconfig.core.json'],{stdio:'inherit',shell:process.platform==='win32'});
if(compile.status!==0)process.exit(compile.status??1);
mkdirSync('.core',{recursive:true});writeFileSync('.core/package.json','{"type":"commonjs"}\n');
const tests=spawnSync(process.execPath,['--test','tests/core.test.cjs'],{stdio:'inherit'});
process.exit(tests.status??1);
