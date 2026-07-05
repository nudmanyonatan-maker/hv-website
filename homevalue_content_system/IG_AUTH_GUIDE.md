# Instagram Auth for Home Value Content System

## Already connected (Composio)

Home Value Instagram is **live on Composio**:

| Field | Value |
|---|---|
| Account | **@hvhomevalue** (Home Value) |
| Composio ID | `instagram_lovely-tolan` |
| Alias | `HomeValue` |
| IG User ID | `27741817182104982` |
| Status | ACTIVE |

**Do NOT use the Vantage account** (`instagram_story-algid` / `@vantagepeptide`) — that's a separate brand.

All Composio Instagram calls must pass:
```
connected_account_id: "instagram_lovely-tolan"
```

Config is pinned in `config/composio.json`.

---

Home Value posts through **Composio** or directly via **Meta's Instagram Graph API**. Composio is already set up — no re-auth needed unless the token expires.

---

## Before you start (required)

Your Instagram account must be one of these:

| Account type | Works? |
|---|---|
| **Instagram Business** | ✅ Yes (recommended) |
| **Instagram Creator** | ✅ Yes |
| Personal account | ❌ No — convert to Business/Creator first |

Also required:

1. A **Facebook Page** linked to the IG account (Meta → Settings → Account → Linked accounts)
2. Admin access to that Facebook Page
3. The IG handle filled in at `config/brand.json` → `"handle"`

### Convert personal → Business (2 minutes)

1. Instagram app → **Profile** → **☰ Menu** → **Settings**
2. **Account type and tools** → **Switch to professional account**
3. Choose **Business** → link your Facebook Page (create one if needed)

---

## Option A: Composio (recommended — what the template uses)

This is the "authorize once, auto-post forever" path.

### Step 1 — Create Composio account

1. Go to [https://app.composio.dev](https://app.composio.dev)
2. Sign up (free tier works for testing)

### Step 2 — Enable Instagram

1. Composio dashboard → **Integrations** → search **Instagram**
2. Click **Enable**

### Step 3 — Connect in Claude Code (on your Mac)

Open a **fresh Claude Code session** in the `homevalue_content_system` folder and say:

```
Connect my Instagram account through Composio for Home Value posting.
```

Claude will run the Composio connect flow. You'll get a **browser popup**:

1. Click **Authorize**
2. Log into the **Facebook account** that owns the linked Page
3. Select the **Home Value Facebook Page**
4. Select the **Home Value Instagram account**
5. Grant permissions: `instagram_basic`, `instagram_content_publish`, `pages_read_engagement`

When it succeeds, Composio stores the token. You won't need to log in again unless the token expires (~60 days) or you revoke access.

### Step 4 — Verify

In Claude Code:

```
Post a test draft to Instagram for Home Value — don't publish yet, just confirm the connection works.
```

Or check Composio dashboard → **Connected Accounts** → Instagram should show as **Active**.

---

## Option B: Meta Graph API (manual / no Composio)

Use this if you want full control or Composio isn't available.

### Step 1 — Facebook Developer App

1. [https://developers.facebook.com](https://developers.facebook.com) → **My Apps** → **Create App**
2. Type: **Business**
3. Add product: **Instagram Graph API**

### Step 2 — Get tokens

1. **Graph API Explorer** → select your app
2. Permissions: `instagram_basic`, `instagram_content_publish`, `pages_show_list`, `pages_read_engagement`
3. Generate a **User Access Token** → exchange for a **Long-Lived Token** (60 days)
4. Get your **Instagram Business Account ID**:
   ```
   GET /{page-id}?fields=instagram_business_account
   ```

### Step 3 — Store in env

```bash
# .env (never commit)
INSTAGRAM_ACCESS_TOKEN=your_long_lived_token
INSTAGRAM_ACCOUNT_ID=your_ig_business_account_id
```

The scheduler scripts read these when Composio isn't configured.

---

## Troubleshooting

| Problem | Fix |
|---|---|
| "No Instagram account linked to Page" | IG Settings → Account → Linked accounts → connect Facebook Page |
| OAuth popup closes instantly | Disable popup blocker; try Chrome |
| "Insufficient permissions" | Re-authorize with all 3 IG permissions checked |
| Token expired after ~60 days | Re-run Composio connect (Option A) or refresh long-lived token (Option B) |
| Posts fail with media error | Video must be MP4, 3–60 sec for Reels, hosted at a public URL (catbox works) |

---

## What happens after auth

Once connected, the two scheduled tasks take over:

| Task | Schedule | Action |
|---|---|---|
| **Daily post** | 9:00 AM ET | Render next video from rotation → publish to IG |
| **Weekly analytics** | Sunday 8:00 PM ET | Pull insights → adjust format/product weights |

You only interact again if the token expires or you want to review content before the first live post.

---

## Security notes

- Never paste your FullVendor API token or IG access token into chat logs
- Keep tokens in `.env` only (see `.env.example`)
- Revoke Composio/Meta access anytime from their dashboards if you rotate accounts
