# Cursor Automation — @hvhomevalue (2 posts/day)

> **Can't select the repo in Automations UI?** Use [GitHub Actions instead](GITHUB_SECRETS.md) — it works without the repo dropdown.

**Schedule:** 1 carousel (10am ET) + 1 single product image (3pm ET) per day.

Use **Cursor Automations** OR **GitHub Actions**. Both launch a cloud agent that publishes via Composio MCP (no `ak_` API key needed).

## Why the repo doesn't appear in Automations

Your GitHub integration only shows repos granted to the **Cursor GitHub App**. If you see `nudmanyonatan-maker/launchq` but not `YonatanNudman/hv-website`, the app doesn't have access to `hv-website` yet.

**Fix (2 minutes):**

1. Open [cursor.com/dashboard/integrations](https://cursor.com/dashboard/integrations)
2. **GitHub → Manage Connections**
3. If `hv-website` lives on a **different GitHub account** than `launchq`, connect that account
4. On GitHub: **Settings → Applications → Cursor → Configure** → grant `YonatanNudman/hv-website`
5. In the automation, click **Refresh** on the repo picker

**Why the agent can't configure this for you:** Cursor Automations run in your logged-in browser session. This cloud agent has no access to your Cursor login or browser — and the Browser automation tool isn't available in this environment.

---

## Recommended: GitHub Actions (no repo picker needed)

See **[GITHUB_SECRETS.md](GITHUB_SECRETS.md)** — add `CURSOR_API_KEY` + `FULLVENDOR_TOKEN`, then:

```bash
gh workflow run "Home Value Instagram Posts" -f format=carousel
# or
gh workflow run "Home Value Instagram Posts" -f format=single
```

This calls the Cursor Cloud Agents API with the repo URL directly. Works even when the Automations UI can't select the repo.

---

## Alternative: Cursor Automations UI

Use **Cursor Automations** if you've fixed GitHub repo access above.

## Why Cursor instead of GitHub?

| | GitHub Actions | Cursor Automation |
|---|---|---|
| Composio auth | Needs `ak_` Project API key in secrets | Uses Composio MCP (already connected) |
| FullVendor sync | Needs `FULLVENDOR_TOKEN` secret | One Runtime Secret |
| Debugging | Read CI logs | Agent can fix and retry |
| Setup | GitHub secrets + workflow | Cursor UI + one secret |

---

## One-time setup

### 1. Cloud Agent environment

Open: **https://cursor.com/dashboard/cloud-agents#environments**

- Repository: `YonatanNudman/hv-website`
- Branch: `main`
- Install command is in `.cursor/environment.json` (ffmpeg + npm + music)

### 2. Runtime secret (only one required)

In the same environment, add:

| Secret | Value |
|---|---|
| `FULLVENDOR_TOKEN` | Your FullVendor API key |

You do **not** need `COMPOSIO_API_KEY` — publishing uses Composio MCP.

### 3. Enable Composio MCP

**Dashboard → Integrations & MCP** → ensure Composio is connected with Instagram (`instagram_lovely-tolan` → @hvhomevalue).

### 4. Create or fix the automation

Open your automation: **https://cursor.com/automations/ae3e9eee-7951-11f1-ba66-0e7d0216e441**

If the automation is blank or inactive, fill in every field:

| Field | Value |
|---|---|
| **Status** | **Active** (toggle ON) |
| **Name** | `HV Instagram Reels — 5× daily` |
| **Repository** | `YonatanNudman/hv-website` / `main` |
| **Environment** | The one from step 1 (with `FULLVENDOR_TOKEN`) |
| **Tools** | Enable **MCP server → Composio** (not just Memories) |
| **Triggers** | Add **5 scheduled triggers** (see below) |
| **Prompt** | Paste from [Automation prompt](#automation-prompt) below |

#### Schedule (5× daily, Eastern)

Add five separate scheduled triggers:

```
CRON_TZ=America/New_York 0 9 * * *
CRON_TZ=America/New_York 0 12 * * *
CRON_TZ=America/New_York 0 15 * * *
CRON_TZ=America/New_York 0 18 * * *
CRON_TZ=America/New_York 0 21 * * *
```

---

## Why your automation wasn't working

From the dashboard screenshot, these were all missing:

1. **Inactive** — toggle was OFF
2. **No repository** — must select `YonatanNudman/hv-website`
3. **No triggers** — cron schedules were never added
4. **Empty prompt** — agent had no instructions
5. **No Composio MCP** — only Memories was enabled; publishing requires Composio

Fix all five, then run a manual test (see below).

---

## Daily food guarantee (2 food posts/day)

The pipeline tracks food posts in `content/state/daily-food.json`.

- **2 food posts required every day** (carousel or single image)
- Until quota is met, `prepare-post.mjs` **always picks food**
- `mark-pending-posted.mjs` increments the food counter after publish

---

## Product image layout

Product slides use a **hero card** layout — full product visible inside a white frame with name and pack size below. No more blown-up crops where you can't tell what the SKU is.

---

## Automation prompt

Use two scheduled triggers (10am ET carousel, 3pm ET single) or run via GitHub Actions.

```
You are the Home Value Instagram publisher for @hvhomevalue. Publish exactly ONE post, then stop.

## Step 1 — Prepare
  cd homevalue_content_system && POST_FORMAT=carousel node scripts/prepare-post.mjs
  (or POST_FORMAT=single for the afternoon image post)

## Step 2 — Publish via Composio MCP
Follow mcpPublishSteps in content/state/pending-publish.json

## Step 3 — Mark posted
  cd homevalue_content_system && node scripts/mark-pending-posted.mjs
```

---

## Manual test (from Cursor agent chat)

Say: **"Run prepare-post carousel and publish to @hvhomevalue"**

Or locally:
```bash
cd homevalue_content_system
POST_FORMAT=carousel node scripts/prepare-post.mjs
# or POST_FORMAT=single for one product image
```

Test food-only pick:
```bash
cd homevalue_content_system
node scripts/pick-next.mjs --food
```

---

## GitHub Actions (optional backup)

The workflow at `.github/workflows/instagram-reels.yml` still works if you add `FULLVENDOR_TOKEN` + `COMPOSIO_API_KEY` (`ak_…`) to GitHub secrets. Cursor Automation is the recommended path.
