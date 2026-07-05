# GitHub Actions Secrets (required for 5×/day automation)

## Quick setup (one command)

From your machine (logged into `gh` with repo admin access):

```bash
FULLVENDOR_TOKEN=your_token COMPOSIO_API_KEY=your_key bash homevalue_content_system/scripts/setup-github-secrets.sh
```

Or add manually in **GitHub → Settings → Secrets and variables → Actions**:

| Secret | Where to get it |
|---|---|
| `FULLVENDOR_TOKEN` | FullVendor admin → API key |
| `COMPOSIO_API_KEY` | [app.composio.dev](https://app.composio.dev) → Settings → API Keys |

## After secrets are set

Test immediately:

```bash
gh workflow run "Home Value Instagram Reels"
```

The workflow runs automatically at 9am, 12pm, 3pm, 6pm, 9pm Eastern once secrets are configured.

Composio Instagram account: **`instagram_lovely-tolan`** (@hvhomevalue) — never Vantage.

**Note:** `catalog/products.json` is committed to the repo so publishing works even before the first catalog sync. Sync runs when `FULLVENDOR_TOKEN` is present.
