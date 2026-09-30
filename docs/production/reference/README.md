# Reference evidence

`AGENTS.prototype.md` is the unchanged original instruction file archived for historical context. It is not active implementation policy.

`source-manifest.json` records the original uploaded archive hash and its files. Compare a newer checkout before changing it; do not overwrite newer source to match this snapshot.

`scoring-count-oracle.json` contains 14,641 independent domain-count cases generated with exact integer basis points. Compare it with the existing TypeScript engine and the new PHP implementation; passing it alone does not test question identity, source content, recommendations, partial-answer validation, authorization or the database. Tie ordering uses zero-based source domain indexes. All-100 narrative behavior needs its own test.

The band identifier spelling must match the repository. Do not normalize by guessing; inspect `src/domain/types.ts` and map deliberately in PHP/resources.

Run `npm run test:core`, then `node docs/production/reference/verify-count-oracle.cjs` to compare the generated oracle against the current compiled TypeScript engine. Both checks are local and do not prove PHP or production behavior.
