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

## Instagram auth (the one manual step)

See **[IG_AUTH_GUIDE.md](./IG_AUTH_GUIDE.md)** — summary:

1. Convert IG to **Business** account, link to Facebook Page
2. Open Claude Code on your Mac → paste **SETUP_PROMPT.md**
3. Click **Authorize** when Composio OAuth popup appears
4. Done — scheduler handles daily posts

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
