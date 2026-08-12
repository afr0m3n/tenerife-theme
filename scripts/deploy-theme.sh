#!/usr/bin/env bash
set -Eeuo pipefail

SCRIPT_DIR="$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")" && pwd)"
REPO_ROOT="$(cd -- "$SCRIPT_DIR/.." && pwd)"
ENV_FILE="${ENV_FILE:-$REPO_ROOT/.env.deploy}"

MODE="dry-run"
DELETE_REMOTE=0
BACKUP_BEFORE_APPLY=1

die() {
  echo "ERROR: $*" >&2
  exit 1
}

while [[ $# -gt 0 ]]; do
  case "$1" in
    --dry-run)
      MODE="dry-run"
      shift
      ;;
    --apply)
      MODE="apply"
      shift
      ;;
    --delete)
      DELETE_REMOTE=1
      shift
      ;;
    --no-backup)
      BACKUP_BEFORE_APPLY=0
      shift
      ;;
    -h|--help)
      cat <<'USAGE'
Usage:
  scripts/deploy-theme.sh --dry-run
  scripts/deploy-theme.sh --apply
  scripts/deploy-theme.sh --apply --delete
  scripts/deploy-theme.sh --apply --no-backup
USAGE
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
LOCAL_THEME_DIR="${LOCAL_THEME_DIR:-$REPO_ROOT}"
LFTP_PARALLEL="${LFTP_PARALLEL:-2}"

LOCAL_THEME_DIR="${LOCAL_THEME_DIR%/}"
REMOTE_THEME_DIR="${REMOTE_THEME_DIR%/}"
REMOTE_BASENAME="$(basename -- "$REMOTE_THEME_DIR")"

[[ "$DEPLOY_CONNECTION" == "sftp" ]] || die "This script is configured for sftp only"
[[ -d "$LOCAL_THEME_DIR" ]] || die "Missing local theme dir: $LOCAL_THEME_DIR"
[[ -f "$LOCAL_THEME_DIR/style.css" ]] || die "Missing local style.css in: $LOCAL_THEME_DIR"
[[ "$REMOTE_THEME_DIR" == *"wp-content/themes/"* ]] || die "REMOTE_THEME_DIR does not look like a WP theme path: $REMOTE_THEME_DIR"
[[ "$REMOTE_BASENAME" == "$REMOTE_THEME_SLUG" ]] || die "REMOTE_THEME_SLUG '$REMOTE_THEME_SLUG' does not match remote folder '$REMOTE_BASENAME'"
[[ "$REMOTE_THEME_DIR" != "/" ]] || die "Refusing remote root"
[[ "$REMOTE_THEME_SLUG" != "themes" ]] || die "Invalid theme slug"

command -v lftp >/dev/null 2>&1 || die "lftp is not installed"

if git -C "$LOCAL_THEME_DIR" rev-parse --is-inside-work-tree >/dev/null 2>&1; then
  CURRENT_BRANCH="$(git -C "$LOCAL_THEME_DIR" branch --show-current || true)"
  CURRENT_COMMIT="$(git -C "$LOCAL_THEME_DIR" rev-parse --short HEAD)"
  GIT_DIRTY="$(git -C "$LOCAL_THEME_DIR" status --porcelain)"

  echo "Git branch: ${CURRENT_BRANCH:-detached}"
  echo "Git commit: $CURRENT_COMMIT"

  if [[ -n "$GIT_DIRTY" ]]; then
    echo
    echo "Git working tree is not clean:"
    git -C "$LOCAL_THEME_DIR" status --short

    if [[ "$MODE" == "apply" && "${ALLOW_DIRTY_DEPLOY:-0}" != "1" ]]; then
      die "Refusing production deploy with dirty git state. Commit/stash first, or set ALLOW_DIRTY_DEPLOY=1 intentionally."
    fi
  fi
fi

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

DEPLOY_EXCLUDES=(
  ".git"
  ".git/**"
  ".github"
  ".github/**"
  ".gitignore"

  "node_modules"
  "node_modules/**"
  "vendor"
  "vendor/**"

  "AGENTS.md"
  "README.md"
  "README-en.md"

  ".env.deploy"
  ".env.deploy.local"
  ".env.deploy.*.local"
  ".env.deploy.example"

  "*.log"
  "logs"
  "logs/**"

  ".cache"
  ".cache/**"
  "cache"
  "cache/**"
  "tmp"
  "tmp/**"
  "backups"
  "backups/**"

  "scripts"
  "scripts/**"

  "package.json"
  "package-lock.json"
  "pnpm-lock.yaml"
  "yarn.lock"
  "composer.json"
  "composer.lock"
  "phpunit.xml"
  "phpunit.xml.dist"
  "tests"
  "tests/**"

  ".DS_Store"
)

build_excludes() {
  local out=""
  local pattern
  for pattern in "${DEPLOY_EXCLUDES[@]}"; do
    out+=" --exclude-glob $(lftp_quote "$pattern")"
  done
  printf '%s' "$out"
}

DRY_FLAG=""
DELETE_FLAG=""

if [[ "$MODE" == "dry-run" ]]; then
  DRY_FLAG="--dry-run"
fi

if [[ "$DELETE_REMOTE" -eq 1 ]]; then
  DELETE_FLAG="--delete"
fi

echo
echo "Deploy mode:  $MODE"
echo "Connection:   sftp://$DEPLOY_HOST:$DEPLOY_PORT"
echo "Local theme:  $LOCAL_THEME_DIR"
echo "Remote theme: $REMOTE_THEME_DIR"
echo "Theme slug:   $REMOTE_THEME_SLUG"
echo "Delete mode:  $DELETE_REMOTE"

if [[ "$MODE" == "apply" ]]; then
  echo
  echo "This will upload local theme files into the existing production theme folder."
  echo "It will NOT touch WordPress core, database, uploads, plugins, or other themes."

  if [[ "$DELETE_REMOTE" -eq 1 ]]; then
    echo
    echo "WARNING: --delete is enabled. Remote files missing locally may be deleted."
    read -r -p "To confirm destructive deploy, type DELETE $REMOTE_THEME_SLUG: " CONFIRM
    [[ "$CONFIRM" == "DELETE $REMOTE_THEME_SLUG" ]] || die "Confirmation failed"
  else
    read -r -p "To confirm production deploy, type DEPLOY: " CONFIRM
    [[ "$CONFIRM" == "DEPLOY" ]] || die "Confirmation failed"
  fi

  if [[ "$BACKUP_BEFORE_APPLY" -eq 1 ]]; then
    echo
    echo "Creating backup before deploy..."
    bash "$SCRIPT_DIR/backup-remote-theme.sh"
  fi
fi

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

  EXCLUDES="$(build_excludes)"
  echo "mirror -R --verbose --continue --parallel=$LFTP_PARALLEL $DRY_FLAG $DELETE_FLAG $EXCLUDES $(lftp_quote "$LOCAL_THEME_DIR") $(lftp_quote "$REMOTE_THEME_DIR")"

  echo "bye"
} > "$LFTP_SCRIPT"

run_lftp_redacted

echo
if [[ "$MODE" == "dry-run" ]]; then
  echo "Dry-run complete. No production files changed."
else
  echo "Deploy complete."
fi
