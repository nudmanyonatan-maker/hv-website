# Home Value — One-Paste Setup Prompt

Fill in the **6 blanks** below, then paste this entire file into a fresh Claude Code session on your Mac.

---

## YOUR INPUTS (fill these in)

```
BRAND_NAME=Home Value
IG_HANDLE=@hvhomevalue
WEBSITE=https://homevalue.fullvendor.com/en
WHAT_THEY_SELL=Wholesale food, cookware, home goods, and oriental authentic products for grocery stores, restaurants, and supermarkets (B2B — login required for pricing)
BRAND_COLORS=primary #b91c1c, background #FAFAF8, text #1a1a1a
HOSTING=no server — use catbox (free)
```

FullVendor API (already configured in this repo):
```
BASE_URL=https://app.fullvendor.com/restapi/api/
COMPANY_ID=77
TOKEN=<set in .env as FULLVENDOR_TOKEN — do not paste in chat>
```

---

## PROMPT (paste everything below this line)

You are setting up the Home Value Instagram content system. The template lives at `homevalue_content_system/` in this repo.

### Brand
- Name: **Home Value** (Home Value LLC)
- IG: **@FILL_IN_HANDLE**
- Website: **https://homevalue.fullvendor.com/en**
- Register CTA: **https://homevalue.fullvendor.com/en/register**
- Colors: red `#b91c1c`, cream background `#FAFAF8`
- Model: **B2B wholesale** — never show public prices; always CTA to register/login

### Product catalog source
Pull products from FullVendor API:
- `POST productList` with `company_id: 77`, `language_id: 1`, `customer_id: -1`
- Token from env `FULLVENDOR_TOKEN`
- 2,502 SKUs across 27 categories — prioritize in-stock items with images and high order volume

Run: `node scripts/sync-catalog.mjs` then `node scripts/generate-content.mjs`

### Compliance (NOT peptides — wholesale food/home)
- Disclaimer: "Wholesale pricing for verified business accounts. Register at our website."
- No health claims, no public pricing, no consumer hype
- Speak to store owners and restaurant buyers

### Instagram auth
**Already connected via Composio** — use account `instagram_lovely-tolan` (@hvhomevalue).
**NEVER use** `instagram_story-algid` (Vantage / @vantagepeptide).

See `config/composio.json` for pinned account IDs.

### Scheduling
Set up two scheduled tasks:
1. **Daily post** — 9:00 AM ET — pick next product from rotation, render video, publish
2. **Weekly analytics** — Sunday 8:00 PM ET — pull IG insights, rebalance format weights

### Hosting
No server — upload rendered MP4s to catbox.moe for public URLs before IG publish.

### Do now
1. Update `config/brand.json` with the real IG handle
2. Sync catalog from FullVendor
3. Generate content for the top 50 in-stock products
4. Walk me through Instagram auth (Composio OAuth — I'll click Authorize)
5. Show me 3 sample rendered post scripts before enabling the scheduler

---

## After paste

Claude will ask you to **Authorize Instagram** — that's the one click that connects your real account. Everything else is automatic.
