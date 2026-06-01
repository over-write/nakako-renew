# SFTP 差分デプロイ

git のコミット差分を見て、変更されたファイルだけを SFTP でアップロードします。

## 準備（初回のみ）

1. lftp をインストール（未インストールの場合）:
   ```bash
   brew install lftp
   ```
2. 接続情報ファイルを作成して編集:
   ```bash
   cp deploy/config.env.example deploy/config.env
   # deploy/config.env を開いて SFTP_HOST / SFTP_USER / SFTP_PASS / REMOTE_PATH を入力
   ```
   `deploy/config.env` は `.gitignore` 済みなのでコミットされません。

## 使い方

```bash
# 前回デプロイ以降の差分をアップロード
deploy/deploy.sh

# 送信せずに対象一覧だけ確認（ドライラン）
deploy/deploy.sh -n

# 追跡中の全ファイルを強制アップロード（初回フルデプロイなど）
deploy/deploy.sh --all

# 指定した commit / tag を起点に差分計算
deploy/deploy.sh <commit-or-tag>
```

## 仕組み・仕様

- **差分の起点**: `deploy/.last-deploy`（gitignore 済み）に前回デプロイした commit を記録し、
  次回はそこから `HEAD` までの `git diff` を対象にします。アップロード成功時のみ更新されます。
- **未コミット変更**: git 差分ベースのため、コミットしていない変更は送信されません（実行時に警告します）。
- **削除も反映**: git 上で削除されたファイルはサーバー側でも `rm` します。
- **`_.htaccess` → `.htaccess`**: ルートの `_.htaccess` は本番では `.htaccess` という名前でアップロードされます。
- **除外**: `deploy/`・`docker/`・`.idea/`・`.github/`・`docker-compose.yaml`・`CLAUDE.md`・
  `AGENTS.md`・`.gitignore`・`README.md`・`.DS_Store` は送信しません。
- 途中でアップロードに失敗した場合は `.last-deploy` を更新しないので、再実行で同じ差分をやり直せます。

## 注意

- パスワードに `"` を含むとエスケープが必要になります。可能なら別の文字へ変更してください。
- リモートのディレクトリ構成（`REMOTE_PATH` 配下）はローカルのリポジトリ構成と一致している前提です。