# Cursor Automation — @hvhomevalue Reels (5×/day)

Use **Cursor Automations** instead of GitHub Actions. Composio MCP is already connected in Cursor — no `ak_` API key needed for publishing.

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

## Daily food guarantee (2 food Reels/day)

The pipeline tracks food posts in `content/state/daily-food.json`.

- **2 food carousel Reels required every day** (category: Oriental Authentic Food & Drink)
- Until the daily food quota is met, `prepare-reel.mjs` **always picks food**
- After 2 food posts, remaining runs pick other wholesale categories
- `mark-pending-posted.mjs` increments the food counter when a food Reel is published

---

## Image quality filter

Bad supplier photos (tiny, blank, mostly white/black, wrong aspect ratio) are **automatically skipped** during product selection. The Reel only uses products whose images score well — no more awkward crops from empty or low-res photos.

---

## Automation prompt

Copy this entire block into the automation prompt field:

```
You are the Home Value Instagram Reel publisher for @hvhomevalue. Publish exactly ONE Reel per run, then stop.

## Account (never change)
- Instagram: @hvhomevalue
- Composio account: instagram_lovely-tolan (NEVER instagram_story-algid / @vantagepeptide)
- IG User ID: 27741817182104982

## Step 1 — Prepare Reel (render + upload)
Run from repo root:
  cd homevalue_content_system && node scripts/prepare-reel.mjs

This outputs JSON with videoUrl, caption, linkComment, and composio publish args.
Read content/state/pending-publish.json if needed.

The script automatically:
- Picks FOOD products when daily food quota (2/day) is not yet met
- Skips low-quality product images (tiny, blank, bad aspect ratio)

Check isFood and foodQuotaRemaining in the output JSON.

## Step 2 — Publish via Composio MCP
Use COMPOSIO_MULTI_EXECUTE_TOOL on the composio server:

1. INSTAGRAM_POST_IG_USER_MEDIA
   account: instagram_lovely-tolan
   arguments: ig_user_id, video_url, caption, media_type REELS, share_to_feed true

2. INSTAGRAM_POST_IG_USER_MEDIA_PUBLISH
   account: instagram_lovely-tolan
   arguments: ig_user_id, creation_id from step 1, max_wait_seconds 180

3. INSTAGRAM_POST_IG_MEDIA_COMMENTS
   account: instagram_lovely-tolan
   arguments: ig_media_id from step 2, message = linkComment from manifest

## Step 3 — Mark posted
Run:
  cd homevalue_content_system && node scripts/mark-pending-posted.mjs

## Rules
- B2B wholesale only — no public pricing
- One category per Reel (6 products, carousel-style video)
- 2 food Reels guaranteed per day — do not skip food when foodQuotaRemaining > 0
- Do NOT open PRs or commit files
- On failure: report which step failed and the error

## Success reply
- Category name (note if food)
- Food quota status (e.g. "Food 2/2 today")
- Instagram permalink (from INSTAGRAM_GET_IG_MEDIA)
- Music track used
- How many bad images were skipped (imageRejectedCount)
- "✓ Done @hvhomevalue"
```

---

## Manual test (from Cursor agent chat)

Say: **"Run prepare-reel and publish to @hvhomevalue via Composio MCP"**

Or locally:
```bash
cd homevalue_content_system
FULLVENDOR_TOKEN=your_token node scripts/prepare-reel.mjs
# Then agent publishes via Composio MCP using the JSON output
```

Test food-only pick:
```bash
cd homevalue_content_system
node scripts/pick-next.mjs --food
```

---

## GitHub Actions (optional backup)

The workflow at `.github/workflows/instagram-reels.yml` still works if you add `FULLVENDOR_TOKEN` + `COMPOSIO_API_KEY` (`ak_…`) to GitHub secrets. Cursor Automation is the recommended path.
