# GitHub Actions setup (recommended)

Runs directly on this repo — no Cursor Automations UI needed.

## Current secret status (as of last CI run)

| Secret | Status |
|---|---|
| `FULLVENDOR_TOKEN` | ✅ Set and working (catalog sync + render succeed) |
| `COMPOSIO_API_KEY` | ⚠️ Set but **invalid** — CI fails with `Invalid API key: ak_**vN9k` (401) |
| `CURSOR_API_KEY` | ❓ Not verified — add this for the recommended path |

## Fix (pick one)

### Option A — Recommended: add `CURSOR_API_KEY`

1. Go to [cursor.com/dashboard](https://cursor.com/dashboard) → **API Keys** → Create key
2. Add to GitHub: **Settings → Secrets → Actions → New secret**
   - Name: `CURSOR_API_KEY`
   - Value: your Cursor API key

The workflow will launch a Cursor Cloud Agent that publishes via Composio MCP (no `ak_` key needed).

### Option B — Fix existing `COMPOSIO_API_KEY`

1. Go to [app.composio.dev](https://app.composio.dev) → **Settings → Project Settings → API Keys**
2. Create a new **Project API key** (`ak_…`) — NOT the MCP consumer key (`ck_`)
3. Update the `COMPOSIO_API_KEY` secret in GitHub

## One-command setup

```bash
CURSOR_API_KEY=your_cursor_key FULLVENDOR_TOKEN=your_token \
  bash homevalue_content_system/scripts/setup-github-secrets.sh
```

Or with Composio REST fallback:

```bash
CURSOR_API_KEY=your_cursor_key COMPOSIO_API_KEY=your_ak_key FULLVENDOR_TOKEN=your_token \
  bash homevalue_content_system/scripts/setup-github-secrets.sh
```

## Test

```bash
gh workflow run "Home Value Instagram Reels"
```

Watch the run at **GitHub → Actions**. If using `CURSOR_API_KEY`, the log will show a link to the cloud agent run.

## Schedule

5× daily at 9am, 12pm, 3pm, 6pm, 9pm Eastern.

- **2 food Reels guaranteed per day**
- **Bad product images skipped** automatically

Composio Instagram account: **`instagram_lovely-tolan`** (@hvhomevalue) — never Vantage.
