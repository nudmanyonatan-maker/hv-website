#!/usr/bin/env bash
# One-time setup: GitHub Actions secrets for 5×/day Reel automation.
#
# Recommended:
#   CURSOR_API_KEY=xxx FULLVENDOR_TOKEN=yyy bash homevalue_content_system/scripts/setup-github-secrets.sh
#
# Or with Composio REST fallback:
#   COMPOSIO_API_KEY=ak_xxx FULLVENDOR_TOKEN=yyy bash homevalue_content_system/scripts/setup-github-secrets.sh

set -euo pipefail

if [[ -z "${FULLVENDOR_TOKEN:-}" ]]; then
  echo "Missing FULLVENDOR_TOKEN"
  exit 1
fi

if [[ -z "${CURSOR_API_KEY:-}" && -z "${COMPOSIO_API_KEY:-}" ]]; then
  echo "Missing CURSOR_API_KEY or COMPOSIO_API_KEY (need at least one)"
  exit 1
fi

gh secret set FULLVENDOR_TOKEN --body "$FULLVENDOR_TOKEN"

if [[ -n "${CURSOR_API_KEY:-}" ]]; then
  gh secret set CURSOR_API_KEY --body "$CURSOR_API_KEY"
  echo "✓ Set CURSOR_API_KEY (recommended path)"
fi

if [[ -n "${COMPOSIO_API_KEY:-}" ]]; then
  gh secret set COMPOSIO_API_KEY --body "$COMPOSIO_API_KEY"
  echo "✓ Set COMPOSIO_API_KEY (REST fallback)"
fi

echo "✓ Secrets set. Trigger a test run:"
echo "  gh workflow run \"Home Value Instagram Reels\""
