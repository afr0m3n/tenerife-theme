#!/usr/bin/env bash
set -Eeuo pipefail

SCRIPT_DIR="$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")" && pwd)"
REPO_ROOT="$(cd -- "$SCRIPT_DIR/.." && pwd)"
ENV_FILE="${ENV_FILE:-$REPO_ROOT/.env.deploy}"

DRY_RUN=0

die() {
  echo "ERROR: $*" >&2
  exit 1
}

while [[ $# -gt 0 ]]; do
  case "$1" in
    --dry-run)
      DRY_RUN=1
      shift
      ;;
    -h|--help)
      echo "Usage: scripts/backup-remote-theme.sh [--dry-run]"
      exit 0
      ;;
    *)
      die "Unknown argument: $1"
      ;;
  esac
done

[[ -f "$ENV_FILE" ]] || die "Missing env file: $ENV_FILE"

set -a
source "$ENV_FILE"
set +a

: "${DEPLOY_CONNECTION:?Missing DEPLOY_CONNECTION}"
: "${DEPLOY_HOST:?Missing DEPLOY_HOST}"
: "${DEPLOY_USER:?Missing DEPLOY_USER}"
: "${REMOTE_THEME_DIR:?Missing REMOTE_THEME_DIR}"
: "${REMOTE_THEME_SLUG:?Missing REMOTE_THEME_SLUG}"

DEPLOY_PORT="${DEPLOY_PORT:-22}"
BACKUP_BASE_DIR="${BACKUP_BASE_DIR:-/srv/backups/tenerife-theme}"
LFTP_PARALLEL="${LFTP_PARALLEL:-2}"

REMOTE_THEME_DIR="${REMOTE_THEME_DIR%/}"
REMOTE_BASENAME="$(basename -- "$REMOTE_THEME_DIR")"

[[ "$DEPLOY_CONNECTION" == "sftp" ]] || die "This script is configured for sftp only"
[[ "$REMOTE_THEME_DIR" == *"wp-content/themes/"* ]] || die "REMOTE_THEME_DIR does not look like a WP theme path: $REMOTE_THEME_DIR"
[[ "$REMOTE_BASENAME" == "$REMOTE_THEME_SLUG" ]] || die "REMOTE_THEME_SLUG '$REMOTE_THEME_SLUG' does not match remote folder '$REMOTE_BASENAME'"
[[ "$REMOTE_THEME_DIR" != "/" ]] || die "Refusing remote root"
[[ "$REMOTE_THEME_SLUG" != "themes" ]] || die "Invalid theme slug"

command -v lftp >/dev/null 2>&1 || die "lftp is not installed"

lftp_quote() {
  local s="${1//\\/\\\\}"
  s="${s//\"/\\\"}"
  printf '"%s"' "$s"
}

run_lftp_redacted() {
  if [[ -n "${DEPLOY_PASSWORD:-}" ]]; then
    lftp -f "$LFTP_SCRIPT" 2>&1 | perl -pe 'BEGIN { $p=$ENV{"DEPLOY_PASSWORD"} // ""; } if (length($p)) { s/\Q$p\E/[REDACTED]/g; }'
  else
    lftp -f "$LFTP_SCRIPT"
  fi
}

STAMP="$(date +'%Y-%m-%d_%H-%M-%S')"
BACKUP_DIR="$BACKUP_BASE_DIR/$STAMP"

echo "Remote:     sftp://$DEPLOY_HOST:$DEPLOY_PORT$REMOTE_THEME_DIR"
echo "Theme slug: $REMOTE_THEME_SLUG"
echo "Backup to:  $BACKUP_DIR"

if [[ "$DRY_RUN" -eq 1 ]]; then
  echo
  echo "Dry-run only. No files downloaded."
  exit 0
fi

mkdir -p "$BACKUP_DIR"

cat > "$BACKUP_DIR/backup-info.txt" <<INFO
Created: $(date --iso-8601=seconds)
Host: $DEPLOY_HOST
Connection: $DEPLOY_CONNECTION
Port: $DEPLOY_PORT
Remote theme dir: $REMOTE_THEME_DIR
Remote theme slug: $REMOTE_THEME_SLUG
INFO

LFTP_SCRIPT="$(mktemp)"
trap 'rm -f "$LFTP_SCRIPT"' EXIT
chmod 600 "$LFTP_SCRIPT"

{
  echo "set cmd:fail-exit yes"
  echo "set net:max-retries 2"
  echo "set net:timeout 30"
  echo "set sftp:auto-confirm yes"

  USER_Q="$(lftp_quote "$DEPLOY_USER")"
  PASS_Q="$(lftp_quote "${DEPLOY_PASSWORD:-}")"
  URL_Q="$(lftp_quote "sftp://$DEPLOY_HOST")"

  if [[ -n "${DEPLOY_PASSWORD:-}" ]]; then
    echo "open -u $USER_Q,$PASS_Q -p $DEPLOY_PORT $URL_Q"
  else
    echo "open -u $USER_Q -p $DEPLOY_PORT $URL_Q"
  fi

  echo "cls $(lftp_quote "$REMOTE_THEME_DIR/style.css")"
  echo "mirror --verbose --continue --parallel=$LFTP_PARALLEL $(lftp_quote "$REMOTE_THEME_DIR") $(lftp_quote "$BACKUP_DIR")"
  echo "bye"
} > "$LFTP_SCRIPT"

run_lftp_redacted

echo
echo "Backup complete:"
echo "$BACKUP_DIR"
