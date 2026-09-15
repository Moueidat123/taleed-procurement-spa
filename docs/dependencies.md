# Dependencies, installation and version policy

## Checked core release line — 15 September 2026

| Dependency | Manifest | Purpose |
|---|---|---|
| React and React DOM | 19.3.0 / 19.3.0 | Current stable React pair checked against official version history |
| Redux Toolkit | 2.12.0 | Store, slices, listener middleware, selectors, default middleware |
| React Redux | 9.3.0 | Provider and typed hooks |
| React Router DOM | ^7.9.1 | Hash routes, guards, route/search state |
| React Hook Form | ^7.62.0 | Form field state, touched/error state and submit handling |
| Zod + Hook Form resolvers | ^4.1.5 / ^5.2.1 | Declarative field-level schema feedback |
| ExcelJS | 4.4.0 | Actual XLSX export, imported lazily |
| TypeScript | ~5.9.3 | Strict source/project checking |
| Vite + React plugin | ^8.1.0 / ^6.0.0 | Development/build tooling |
| ESLint, TS ESLint, Hooks plugin | See package.json | Source/static checks |
| Playwright | ^1.55.0 | Browser flow, layout and persistence scenarios |
| Prettier | ^3.6.2 | Team formatting |
| Node test runner | Built into Node | Core unit/integration checks without a separate test framework |

React/DOM/Toolkit/React Redux are pinned deliberately. Ranges in the other packages describe a compatible requested line, **not independent verification that every dependency is the newest package**. The included registry script compares requested packages with live metadata without updating files.

## Install and lock

Node 22.12+ within 22.x or Node 24+ is the project baseline. Vite 8's official requirement includes Node 20.19+ and 22.12+; this project selects a narrower modern baseline.

Run `npm install` once, resolve any real peer conflicts without disabling validation, then commit the actual generated lockfile. Run `npm ls`, `npm audit`, `npm run check`, and browser tests. Record their real outputs. No successful install/audit/full build occurred in the handover environment, so there is no invented lockfile or clean-security claim.

For a future upgrade, keep React and React DOM exactly aligned, read the relevant release/migration notes, check React Redux/Router/form peer ranges, regenerate the lock intentionally and rerun all checks. Do not silently upgrade proprietary source wording while upgrading a package.

## Deliberately absent dependencies

No `redux-persist` (the data has an explicit validated storage contract), Redux Saga (no need for a second workflow layer), Axios (no real API yet), date library (limited ISO-date needs), chart framework (four-domain native display), icon package (small local SVG set), CSS framework (approved-reference tokens), PDF library (honest browser-print prototype), authentication SDK, database SDK, backend framework or cloud deployment tool.

## Official technical references

- React versions: https://react.dev/versions
- Redux Toolkit release: https://github.com/reduxjs/redux-toolkit/releases/tag/v2.12.0
- React Redux releases: https://github.com/reduxjs/react-redux/releases
- Redux Toolkit usage: https://redux-toolkit.js.org/usage/usage-guide
- Vite 8.1 release: https://vite.dev/blog/announcing-vite8-1
- Vite 8 / Node / React plugin requirements: https://vite.dev/blog/announcing-vite8
- React Router hash router: https://reactrouter.com/api/declarative-routers/HashRouter
- React Hook Form: https://react-hook-form.com/get-started
- Zod: https://zod.dev/
- Playwright: https://playwright.dev/docs/intro
- Web Storage limitations: https://developer.mozilla.org/en-US/docs/Web/API/Web_Storage_API
- Web Locks: https://developer.mozilla.org/en-US/docs/Web/API/Web_Locks_API

Versions can change after this dated handover. Official version pages are release evidence, not proof that a dependency was installed in the local preparation environment.
