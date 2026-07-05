# GitHub Actions Secrets (required for 5×/day automation)

Add these in **GitHub → Settings → Secrets and variables → Actions → New repository secret**:

| Secret | Where to get it |
|---|---|
| `FULLVENDOR_TOKEN` | FullVendor admin → API key |
| `COMPOSIO_API_KEY` | [app.composio.dev](https://app.composio.dev) → Settings → API Keys |

The workflow `.github/workflows/instagram-reels.yml` runs automatically at 9am, 12pm, 3pm, 6pm, 9pm Eastern once secrets are set and the branch is merged to `main`.

Composio Instagram account used: **`instagram_lovely-tolan`** (@hvhomevalue) — never Vantage.
