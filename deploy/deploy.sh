#!/usr/bin/env bash
#
# git 差分ベースの SFTP 差分アップロード。
# 前回デプロイ時点（deploy/.last-deploy に記録した commit）から HEAD までに
# 変更されたファイルだけを SFTP で送信します。
#
# 使い方:
#   deploy/deploy.sh              前回デプロイ以降の差分をアップロード
#   deploy/deploy.sh -n           ドライラン（送信せず対象一覧だけ表示）
#   deploy/deploy.sh --all        追跡中の全ファイルを強制アップロード
#   deploy/deploy.sh <ref>        指定 commit/tag を起点に差分を計算
#
set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
REPO_ROOT="$(cd "$SCRIPT_DIR/.." && pwd)"
cd "$REPO_ROOT"

CONFIG="$SCRIPT_DIR/config.env"
STATE_FILE="$SCRIPT_DIR/.last-deploy"

DRY_RUN=0
FORCE_ALL=0
BASE_REF=""

while [ $# -gt 0 ]; do
  case "$1" in
    -n|--dry-run) DRY_RUN=1 ;;
    --all)        FORCE_ALL=1 ;;
    -h|--help)    sed -n '3,16p' "$0"; exit 0 ;;
    -*)           echo "不明なオプション: $1"; exit 1 ;;
    *)            BASE_REF="$1" ;;
  esac
  shift
done

# ---- 設定読み込み ----
if [ ! -f "$CONFIG" ]; then
  echo "❌ 設定ファイルがありません: $CONFIG"
  echo "   cp deploy/config.env.example deploy/config.env  を実行して編集してください。"
  exit 1
fi
set -a; . "$CONFIG"; set +a
: "${SFTP_HOST:?SFTP_HOST が未設定です}"
: "${SFTP_USER:?SFTP_USER が未設定です}"
: "${SFTP_PASS:?SFTP_PASS が未設定です}"
: "${REMOTE_PATH:?REMOTE_PATH が未設定です}"
SFTP_PORT="${SFTP_PORT:-22}"
REMOTE_PATH="${REMOTE_PATH%/}"   # 末尾スラッシュを除去

# ---- 未コミット変更の警告 ----
if ! git diff --quiet || ! git diff --cached --quiet; then
  echo "⚠️  コミットされていない変更があります。git 差分ベースのため、未コミット分はアップロードされません。"
fi

HEAD_SHA="$(git rev-parse HEAD)"

# ---- 差分対象の決定 ----
if [ "$FORCE_ALL" -eq 1 ]; then
  echo "ℹ️  強制全件モード: 追跡中の全ファイルをアップロードします。"
  CHANGES="$(git ls-files | sed 's/^/A	/')"
else
  if [ -n "$BASE_REF" ]; then
    BASE="$BASE_REF"
  elif [ -f "$STATE_FILE" ]; then
    BASE="$(cat "$STATE_FILE")"
  else
    BASE=""
  fi

  if [ -z "$BASE" ]; then
    echo "ℹ️  前回デプロイ記録がありません。初回として追跡中の全ファイルをアップロードします。"
    CHANGES="$(git ls-files | sed 's/^/A	/')"
  else
    if ! git rev-parse --verify "$BASE^{commit}" >/dev/null 2>&1; then
      echo "❌ 起点コミットが見つかりません: $BASE"; exit 1
    fi
    echo "ℹ️  差分範囲: ${BASE:0:12}..${HEAD_SHA:0:12}"
    CHANGES="$(git diff --name-status --no-renames "$BASE" "$HEAD_SHA")"
  fi
fi

# ---- 除外・パスマッピング ----
is_excluded() {
  case "$1" in
    deploy/*|.idea/*|docker/*|.github/*) return 0 ;;
    docker-compose.yaml|CLAUDE.md|AGENTS.md|.gitignore|.gitattributes|README.md) return 0 ;;
    *.DS_Store) return 0 ;;
  esac
  return 1
}
# ローカルパス -> リモート相対パス（_.htaccess は本番では .htaccess として配置）
map_remote() {
  if [ "$1" = "_.htaccess" ]; then echo ".htaccess"; else echo "$1"; fi
}

UP_LOCAL=(); UP_REMOTE=(); DEL_REMOTE=()
while IFS=$'\t' read -r status path _rest; do
  [ -z "${status:-}" ] && continue
  [ -z "${path:-}" ] && continue
  is_excluded "$path" && continue
  remote="$(map_remote "$path")"
  case "$status" in
    D)  DEL_REMOTE+=("$remote") ;;
    *)  UP_LOCAL+=("$path"); UP_REMOTE+=("$remote") ;;
  esac
done <<< "$CHANGES"

if [ "${#UP_LOCAL[@]}" -eq 0 ] && [ "${#DEL_REMOTE[@]}" -eq 0 ]; then
  echo "✅ 変更なし。アップロードするものはありません。"
  exit 0
fi

# ---- 計画表示 ----
echo
echo "── アップロード (${#UP_LOCAL[@]}) ──"
if [ "${#UP_REMOTE[@]}" -gt 0 ]; then
  for r in "${UP_REMOTE[@]}"; do echo "  ↑ $REMOTE_PATH/$r"; done
fi
echo "── 削除 (${#DEL_REMOTE[@]}) ──"
if [ "${#DEL_REMOTE[@]}" -gt 0 ]; then
  for r in "${DEL_REMOTE[@]}"; do echo "  ✕ $REMOTE_PATH/$r"; done
fi
echo

if [ "$DRY_RUN" -eq 1 ]; then
  echo "🟡 ドライラン: 実際の送信はしていません。"
  exit 0
fi

command -v lftp >/dev/null 2>&1 || { echo "❌ lftp が必要です。インストール: brew install lftp"; exit 1; }

# ---- lftp スクリプト生成（パスワードを含むので 600 で作成）----
umask 077
LFTP_SCRIPT="$(mktemp)"
trap 'rm -f "$LFTP_SCRIPT"' EXIT

{
  echo "set cmd:fail-exit yes"
  echo "set sftp:auto-confirm yes"
  echo "set net:max-retries 2"
  echo "set net:timeout 20"
  printf 'open -p %s sftp://%s\n' "$SFTP_PORT" "$SFTP_HOST"
  printf 'user "%s" "%s"\n' "$SFTP_USER" "$SFTP_PASS"
  for i in "${!UP_LOCAL[@]}"; do
    local_abs="$REPO_ROOT/${UP_LOCAL[$i]}"
    remote_abs="$REMOTE_PATH/${UP_REMOTE[$i]}"
    remote_dir="$(dirname "$remote_abs")"
    printf 'mkdir -p "%s"\n' "$remote_dir"
    printf 'put "%s" -o "%s"\n' "$local_abs" "$remote_abs"
  done
  if [ "${#DEL_REMOTE[@]}" -gt 0 ]; then
    for r in "${DEL_REMOTE[@]}"; do
      printf 'rm -f "%s"\n' "$REMOTE_PATH/$r"
    done
  fi
  echo "bye"
} > "$LFTP_SCRIPT"

echo "🚀 SFTP アップロード中: $SFTP_USER@$SFTP_HOST:$REMOTE_PATH"
lftp -f "$LFTP_SCRIPT"

# ---- 成功したらデプロイ地点を記録 ----
echo "$HEAD_SHA" > "$STATE_FILE"
echo "✅ デプロイ完了。最終デプロイ commit: ${HEAD_SHA:0:12}"