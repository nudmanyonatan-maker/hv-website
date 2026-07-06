# GitHub Actions setup (recommended — works without Cursor Automations UI)

The Cursor Automations repo picker only shows repos granted to the **Cursor GitHub App**. If `hv-website` doesn't appear, use **GitHub Actions** instead — it runs directly on this repo and doesn't need the dropdown.

## One-time setup (2 secrets)

In **GitHub → YonatanNudman/hv-website → Settings → Secrets and variables → Actions**:

| Secret | Where to get it |
|---|---|
| `CURSOR_API_KEY` | [cursor.com/dashboard](https://cursor.com/dashboard) → **API Keys** → Create key |
| `FULLVENDOR_TOKEN` | FullVendor admin → API key |

Or from your machine:

```bash
CURSOR_API_KEY=your_cursor_key FULLVENDOR_TOKEN=your_token \
  bash homevalue_content_system/scripts/setup-github-secrets.sh
```

## Test immediately

```bash
gh workflow run "Home Value Instagram Reels"
```

Then watch the agent at the URL printed in the workflow log, or check [cursor.com/agents](https://cursor.com/agents).

## Schedule

Runs automatically **5× daily** at 9am, 12pm, 3pm, 6pm, 9pm Eastern.

- **2 food Reels guaranteed per day** (until quota met, picks food category)
- **Bad product images skipped** automatically

## How it works

1. GitHub Actions cron fires on this repo
2. `trigger-cursor-agent.mjs` calls Cursor Cloud Agents API with repo URL explicitly set
3. Cloud agent runs `prepare-reel.mjs` → publishes via Composio MCP → marks posted

No Composio `ak_` API key needed — the cloud agent uses Composio MCP (same as manual Cursor chat).

## Optional: fix Cursor Automations UI (if you want the dashboard)

Your automation only shows `nudmanyonatan-maker/launchq` because the Cursor GitHub App isn't granted access to `YonatanNudman/hv-website`.

Fix:

1. Open [cursor.com/dashboard/integrations](https://cursor.com/dashboard/integrations)
2. **GitHub → Manage Connections**
3. If `hv-website` is on a **different GitHub account** than `launchq`, connect that account too
4. Or on GitHub: **Settings → Applications → Cursor → Configure** → add `YonatanNudman/hv-website`
5. Click **Refresh** in the automation repo picker

Composio Instagram account: **`instagram_lovely-tolan`** (@hvhomevalue) — never Vantage.
