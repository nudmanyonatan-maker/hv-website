#!/usr/bin/env bash
# One-time setup: add GitHub Actions secrets for 5×/day Reel automation.
# Run from repo root with both env vars set:
#
#   FULLVENDOR_TOKEN=xxx COMPOSIO_API_KEY=yyy bash homevalue_content_system/scripts/setup-github-secrets.sh
#
# Get COMPOSIO_API_KEY: https://app.composio.dev → Settings → API Keys
# Get FULLVENDOR_TOKEN: FullVendor admin → API key

set -euo pipefail

if [[ -z "${FULLVENDOR_TOKEN:-}" ]]; then
  echo "Missing FULLVENDOR_TOKEN"
  exit 1
fi
if [[ -z "${COMPOSIO_API_KEY:-}" ]]; then
  echo "Missing COMPOSIO_API_KEY"
  exit 1
fi

gh secret set FULLVENDOR_TOKEN --body "$FULLVENDOR_TOKEN"
gh secret set COMPOSIO_API_KEY --body "$COMPOSIO_API_KEY"

echo "✓ Secrets set. Trigger a test run:"
echo "  gh workflow run \"Home Value Instagram Reels\""
