# Home Value Content System

Automated Instagram content for **Home Value LLC** — B2B wholesale food & home products.

Pulls the live product catalog from FullVendor (company 77), generates post scripts in 8 formats, and publishes on a schedule once Instagram is connected.

## Quick start

```bash
cd homevalue_content_system
cp .env.example .env
# Add FULLVENDOR_TOKEN to .env (get from your FullVendor admin)

node scripts/sync-catalog.mjs      # ~45s — pulls 2,502 SKUs
node scripts/generate-content.mjs    # generates post queue
```

## Instagram auth (already connected)

**@hvhomevalue** is live on Composio (`instagram_lovely-tolan`). Never use Vantage (`instagram_story-algid`).

See **[IG_AUTH_GUIDE.md](./IG_AUTH_GUIDE.md)** for account details.

## Automated schedule — 5 Reels/day

| Time (ET) | UTC cron |
|---|---|
| 9:00 AM | 13:00 |
| 12:00 PM | 16:00 |
| 3:00 PM | 19:00 |
| 6:00 PM | 22:00 |
| 9:00 PM | 01:00 |

GitHub Actions workflow: `.github/workflows/instagram-reels.yml`

**Required secrets:** `FULLVENDOR_TOKEN`, `COMPOSIO_API_KEY`

Each post:
- Picks a different in-stock product (rotation state in `content/state/rotation.json`)
- Renders a professional Reel — product image top, text panel bottom, **no overlap**
- Includes valuable B2B context + wholesale registration CTA
- Publishes to @hvhomevalue only

## Reel format (v2 — clean 3-scene)

Each Reel is **12 seconds**, 3 scenes with smooth fades:

| Scene | What the viewer sees |
|---|---|
| **1** | Product hero + "WHOLESALE ONLY" + name & case size |
| **2** | Product + **"Own a store, restaurant, or shop?"** callout |
| **3** | Numbered steps: ① Visit site ② Register ③ Login & order + big red CTA button |

- White background, lots of space — nothing overlaps the product
- Simple words a kid could follow
- Every post calls out **business owners** and shows **exactly how to buy**

## What's reusable vs. per-brand

| Reusable (don't touch) | Per-brand (already configured) |
|---|---|
| 8 video formats | Brand name, colors, logo |
| Render pipeline | Product catalog (FullVendor sync) |
| Analytics loop | Compliance/disclaimer text |
| Scheduler | IG account + hosting |

## B2B content rules

- **Never** show case prices publicly
- **Always** CTA → register/login for wholesale pricing
- Speak to store owners, restaurant managers, buyers
- Include case/pack sizes when relevant

## File structure

```
homevalue_content_system/
├── config/           brand, compliance, FullVendor settings
├── formats/          8 post format definitions
├── scripts/          sync-catalog, generate-content
├── catalog/          synced products (generated)
├── content/posts/    post queue + previews (generated)
├── IG_AUTH_GUIDE.md  ← start here for Instagram
└── SETUP_PROMPT.md   ← one-paste Claude Code prompt
```

## FullVendor API

Already wired to company **77**:

```
POST https://app.fullvendor.com/restapi/api/productList
Headers: X-API-KEY: <token>
Body: { company_id: "77", language_id: "1", customer_id: "-1" }
```

Token goes in `.env` only — never commit it.
