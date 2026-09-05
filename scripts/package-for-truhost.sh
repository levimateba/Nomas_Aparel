#!/bin/bash
# Build a deployment ZIP for Truhost cPanel upload.
# Run locally from project root: ./scripts/package-for-truhost.sh

set -e

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$ROOT"
OUTPUT="$ROOT/nomas-apparel-deploy.zip"

echo "==> Building Nomas Apparel deployment package"

echo "==> Installing PHP dependencies (production)"
composer install --no-dev --optimize-autoloader --no-interaction

echo "==> Installing Node dependencies"
npm ci --silent 2>/dev/null || npm install --silent

echo "==> Building frontend assets"
npm run build

echo "==> Creating ZIP (excluding dev files)"
rm -f "$OUTPUT"

zip -r "$OUTPUT" . \
    -x "*.git*" \
    -x "*node_modules/*" \
    -x "*.env" \
    -x "*storage/logs/*" \
    -x "*storage/framework/cache/data/*" \
    -x "*storage/framework/sessions/*" \
    -x "*storage/framework/views/*" \
    -x "*tests/*" \
    -x "*database/database.sqlite" \
    -x "*nomas-apparel-deploy.zip" \
    -x "*.DS_Store" \
    -x "*agent-tools/*" \
    -x "*agent-transcripts/*"

SIZE=$(du -h "$OUTPUT" | cut -f1)
echo ""
echo "Package ready: $OUTPUT ($SIZE)"
echo ""
echo "Next steps:"
echo "  1. Upload to Truhost cPanel File Manager → /home/YOUR_USERNAME/"
echo "  2. Extract as nomas-apparel/"
echo "  3. Set subdomain document root to ~/nomas-apparel/public"
echo "  4. Follow TRUHOST_DEPLOYMENT.md"
