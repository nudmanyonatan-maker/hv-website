#!/usr/bin/env bash
# One-time setup: GitHub Actions secrets for 5×/day Reel automation.
#
#   CURSOR_API_KEY=xxx FULLVENDOR_TOKEN=yyy bash homevalue_content_system/scripts/setup-github-secrets.sh
#
# CURSOR_API_KEY: https://cursor.com/dashboard → API Keys
# FULLVENDOR_TOKEN: FullVendor admin → API key

set -euo pipefail

if [[ -z "${CURSOR_API_KEY:-}" ]]; then
  echo "Missing CURSOR_API_KEY (cursor.com/dashboard → API Keys)"
  exit 1
fi
if [[ -z "${FULLVENDOR_TOKEN:-}" ]]; then
  echo "Missing FULLVENDOR_TOKEN"
  exit 1
fi

gh secret set CURSOR_API_KEY --body "$CURSOR_API_KEY"
gh secret set FULLVENDOR_TOKEN --body "$FULLVENDOR_TOKEN"

echo "✓ Secrets set. Trigger a test run:"
echo "  gh workflow run \"Home Value Instagram Reels\""
