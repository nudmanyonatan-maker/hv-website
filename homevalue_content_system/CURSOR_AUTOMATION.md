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
| `FULLVENDOR_TOKEN` | Your FullVendor API key (e.g. `f008aa014308059be6c1c20ed70a006e`) |

You do **not** need `COMPOSIO_API_KEY` — publishing uses Composio MCP.

### 3. Enable Composio MCP

**Dashboard → Integrations & MCP** → ensure Composio is connected with Instagram (`instagram_lovely-tolan` → @hvhomevalue).

### 4. Create the automation

Open: **https://cursor.com/automations/new**

| Field | Value |
|---|---|
| **Name** | `HV Instagram Reels — 5× daily` |
| **Repository** | `YonatanNudman/hv-website` / `main` |
| **Environment** | The one from step 1 |
| **Tools** | Enable **MCP server** (Composio) |
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
- Do NOT open PRs or commit files
- On failure: report which step failed and the error

## Success reply
- Category name
- Instagram permalink (from INSTAGRAM_GET_IG_MEDIA)
- Music track used
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

---

## GitHub Actions (optional backup)

The workflow at `.github/workflows/instagram-reels.yml` still works if you add `FULLVENDOR_TOKEN` + `COMPOSIO_API_KEY` (`ak_…`) to GitHub secrets. Cursor Automation is the recommended path.
