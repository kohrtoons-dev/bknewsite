#!/usr/bin/env bash
set -Eeuo pipefail

# BK Traders staging-to-production deployment.
#
# Preview only:
#   bash /home/bktraders/newsite.bktraders.com/scripts/deploy-live.sh
#
# Deploy after reviewing the preview:
#   bash /home/bktraders/newsite.bktraders.com/scripts/deploy-live.sh --deploy

EXPECTED_SOURCE="/home/bktraders/newsite.bktraders.com"
SOURCE="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
LIVE="/home/bktraders/public_html"
LOCK_DIR="/tmp/bktraders-live-deploy.lock"
MODE="${1:---dry-run}"

if [[ "$SOURCE" != "$EXPECTED_SOURCE" ]]; then
    printf 'Refusing to run: expected staging root %s, found %s\n' "$EXPECTED_SOURCE" "$SOURCE" >&2
    exit 1
fi

if [[ "$LIVE" != "/home/bktraders/public_html" || "$LIVE" == "/" || "$SOURCE" == "$LIVE" ]]; then
    printf 'Refusing to run because the deployment paths failed validation.\n' >&2
    exit 1
fi

if [[ ! -f "$SOURCE/index.php" || ! -d "$SOURCE/includes" || ! -d "$SOURCE/assets" ]]; then
    printf 'Refusing to run: the staging site does not contain the expected BK Traders files.\n' >&2
    exit 1
fi

if [[ ! -d "$LIVE" ]]; then
    printf 'Refusing to run: live document root does not exist: %s\n' "$LIVE" >&2
    exit 1
fi

if ! command -v rsync >/dev/null 2>&1; then
    printf 'rsync is required but is not available on this server.\n' >&2
    exit 1
fi

if ! mkdir "$LOCK_DIR" 2>/dev/null; then
    printf 'Another BK Traders deployment appears to be running.\n' >&2
    exit 1
fi
trap 'rmdir "$LOCK_DIR" 2>/dev/null || true' EXIT

RSYNC_OPTIONS=(
    --archive
    --itemize-changes
    --delete-before
    --force
    --human-readable
    --chmod=Du=rwx,Dgo=rx,Fu=rw,Fgo=r
    --exclude=/.htaccess
    --exclude=/.well-known/
    --exclude=/cgi-bin/
    --exclude=/_old/
    --exclude=/.git/
    --exclude=/.agents/
    --exclude=/.codex/
    --exclude=/outputs/
    --exclude=/README.md
    --exclude=/scripts/deploy-live.sh
    --exclude=/scripts/DEPLOYING.md
    --exclude='/storage/cache/*.lock'
)

case "$MODE" in
    --dry-run)
        printf '\nBK Traders deployment preview — no files will be changed.\n'
        printf 'Staging: %s/\nLive:    %s/\n\n' "$SOURCE" "$LIVE"
        rsync "${RSYNC_OPTIONS[@]}" --dry-run "$SOURCE/" "$LIVE/"
        printf '\nPreview complete. Review deletions carefully.\n'
        printf 'To publish, run:\n  bash %s/scripts/deploy-live.sh --deploy\n\n' "$SOURCE"
        ;;
    --deploy)
        printf '\nPublishing BK Traders staging to the live site...\n'
        rsync "${RSYNC_OPTIONS[@]}" "$SOURCE/" "$LIVE/"

        LEGACY_WORDPRESS_PATHS=()
        for legacy_path in wp-admin wp-content wp-includes wp-config.php wp-login.php; do
            if [[ -e "$LIVE/$legacy_path" ]]; then
                LEGACY_WORDPRESS_PATHS+=("$legacy_path")
            fi
        done

        if (( ${#LEGACY_WORDPRESS_PATHS[@]} > 0 )); then
            printf '\nWARNING: legacy WordPress paths remain in the live document root:\n' >&2
            printf '  %s\n' "${LEGACY_WORDPRESS_PATHS[@]}" >&2
            printf 'Review their ownership and permissions in cPanel before removing them.\n' >&2
            exit 3
        fi

        printf '\nDeployment complete: %s\n' "$(date '+%Y-%m-%d %H:%M:%S %Z')"
        printf 'The live .htaccess and cPanel system directories were preserved.\n\n'
        ;;
    *)
        printf 'Unknown option: %s\nUse --dry-run or --deploy.\n' "$MODE" >&2
        exit 2
        ;;
esac
