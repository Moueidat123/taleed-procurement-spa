'use strict';
// Run the existing `npm run test:core` first, then this script from any directory.
// This reads compiled local code; it never contacts a database or production.
const fs = require('node:fs');
const path = require('node:path');
const assert = require('node:assert/strict');
const root = path.resolve(__dirname, '../../..');
const compiled = path.join(root, '.core/domain/scoring.js');
try {
  if (!fs.existsSync(compiled)) throw new Error('Missing .core/domain/scoring.js. Run npm run test:core first.');
  const {scoreAssessment, answersFromCounts} = require(compiled);
  const framework = JSON.parse(fs.readFileSync(path.join(root, 'src/data/framework.json'), 'utf8'));
  const oracle = JSON.parse(fs.readFileSync(path.join(__dirname, 'scoring-count-oracle.json'), 'utf8'));
  assert.equal(oracle.caseCount, 14641);
  assert.equal(oracle.cases.length, oracle.caseCount);
  assert.deepEqual(oracle.domainOrder, framework.domains.map(d => d.key));
  let checked = 0;
  for (const c of oracle.cases) {
    const actual = scoreAssessment(answersFromCounts(framework, c.yesCounts), framework);
    assert.equal(actual.yes, c.overallYes);
    assert.equal(actual.overall * 100, c.overallBasisPoints);
    assert.equal(actual.band, c.overallBand);
    assert.deepEqual(actual.domains.map(d => d.band), c.domainBands);
    assert.deepEqual(actual.priorities, c.focusDomainIndexes.map(i => oracle.domainOrder[i]));
    checked++;
  }
  console.log(JSON.stringify({status:'passed', cases:checked, subject:'independent count oracle versus compiled uploaded TypeScript scoring', node:process.version}, null, 2));
} catch (error) {
  console.error(error instanceof Error ? error.stack : String(error));
  process.exitCode = 1;
}
