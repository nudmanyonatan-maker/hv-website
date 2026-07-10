<!-- BEGIN:nextjs-agent-rules -->
# This is NOT the Next.js you know

This version has breaking changes — APIs, conventions, and file structure may all differ from your training data. Read the relevant guide in `node_modules/next/dist/docs/` before writing any code. Heed deprecation notices.
<!-- END:nextjs-agent-rules -->

## Cursor Cloud specific instructions

This repo has two subsystems:

- **Root `hv-website`** — the Next.js 16 (App Router, Tailwind v4) B2B wholesale catalog. This is the primary product. Data comes from the external FullVendor API (company `77`); there is no local database to run. Scripts are in `package.json` (`dev`, `build`, `start`, `lint`).
- **`homevalue_content_system/`** — a standalone Node scripts package (its own `package.json`) that syncs the catalog and generates/publishes Instagram content. Secondary; see its `README.md`.

### Environment variables (website)
`src/lib/env.ts` throws at import if these are missing, so the site won't build/run without them. `FULLVENDOR_TOKEN` is provided as an injected secret; the rest are non-secret config. Create `.env.local` at the repo root (git-ignored) with:

```
FULLVENDOR_BASE_URL=https://app.fullvendor.com/restapi/api/
FULLVENDOR_COMPANY_ID=77
JWT_SECRET=<any non-empty dev string>
ADMIN_EMAIL=dev@example.com
NEXT_PUBLIC_SITE_URL=http://localhost:3000
```
`RESEND_API_KEY` is optional (email is skipped when unset).

### Running the website — important dev gotcha
- **`npm run dev` (default Turbopack) is currently broken**: it returns HTTP 500 on every page because `src/app/globals.css` has a Google-Fonts `@import` after `@import "tailwindcss"`, and Turbopack's dev CSS parser rejects `@import` that follows other rules once Tailwind is inlined. **Use `npm run dev -- --webpack`** for local development (verified working; serves live catalog data).
- `npm run build` then `npm start` (production, Turbopack) work fine — the production CSS pipeline tolerates the import order.
- `npm run lint` currently exits non-zero due to **pre-existing** lint errors in the codebase (not a setup problem).

### Content system
From `homevalue_content_system/`: `npm run sync` (pulls ~2,500 SKUs) and `npm run generate` need only `FULLVENDOR_TOKEN`. Publishing (`npm run publish*`, reel/carousel rendering) additionally needs `COMPOSIO_API_KEY`/`CURSOR_API_KEY` and `ffmpeg`, which are not configured here.
