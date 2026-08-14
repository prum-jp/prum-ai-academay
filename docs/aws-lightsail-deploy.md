# AWS Lightsail デプロイ手順

PRUM AI Academy を AWS Lightsail 上で本番運用するためのガイドです。

## 推奨構成

| コンポーネント | サービス | 用途 |
|---|---|---|
| アプリ | Lightsail Container Service (Micro) | nginx + php-fpm（`api-server/Dockerfile`） |
| DB | Lightsail Managed Database (MySQL) | 永続データ |
| ファイル | S3 | アバター・クエスト提出物 |
| メール | SES（任意） | 通知メール |
| 監視 | CloudWatch / Budgets（任意） | ログ・アラート・コスト |

## 本番リソース名（統一）

| リソース | 名前 |
|---|---|
| S3 バケット | `ai-academy-prd` |
| IAM ユーザー（アプリ → S3） | `ai-academy-prd-s3-app` |
| Lightsail Container Service | `ai-academy-prd` |
| Lightsail MySQL | `ai-academy-prd-db` |
| MySQL データベース名（`DB_DATABASE`） | `prum_ai_academy` |
| Docker イメージ tag | `ai-academy-prd` |

## 1. 事前準備

### S3 バケット

1. `ap-northeast-1` にバケット `ai-academy-prd` を作成
2. **Block Public Access はすべてオン** のままで可（アプリは署名付き URL で読み取り）
3. IAM ユーザーまたはロールに以下を付与:
   - `s3:PutObject`
   - `s3:GetObject`
   - `s3:DeleteObject`

### Lightsail MySQL

1. Lightsail コンソールで MySQL を作成
2. データベース名・ユーザー・パスワードを控える
3. アプリコンテナと **同一リージョン** に配置

## 2. 環境変数

`.env.production.example` をコピーして本番値を設定します。

```bash
cp .env.production.example .env.production
```

必須項目:

| 変数 | 説明 |
|---|---|
| `APP_KEY` | `php artisan key:generate --show` で生成 |
| `APP_URL` | 本番 URL（HTTPS） |
| `DB_*` | Lightsail MySQL の接続情報 |
| `FILESYSTEM_PUBLIC_DISK` | `s3` |
| `AWS_*` | S3 バケットと IAM キー |
| `AWS_S3_TEMPORARY_URL_MINUTES` | 署名付き URL の有効期限（分・既定 60） |
| `SESSION_SECURE_COOKIE` | `true` |
| `DB_FRESH` / `RUN_SEEDER` | 必ず `false` |

## 3. Docker イメージ

```bash
cd api-server
docker build -t ai-academy-prd .
```

Lightsail へ push する例:

```bash
aws lightsail push-container-image \
  --service-name ai-academy-prd \
  --label ai-academy-prd \
  --image ai-academy-prd
```

イメージには以下が含まれます:

- Vite ビルド済みフロントエンド
- nginx + php-fpm
- MySQL 起動待機（`docker/aws/start.sh`）
- `migrate --force` 自動実行
- キューワーカー（`QUEUE_CONNECTION=database` 時）

## 4. Lightsail Container Service

1. コンテナサービス `ai-academy-prd` を作成（Micro プランから開始）
2. 上記 Docker イメージをプッシュ / デプロイ
3. **環境変数** に `.env.production` の内容を設定
4. 公開ポート: コンテナ `80` → ロードバランサー `443`（HTTPS）
5. ヘルスチェック: `/up`

`PORT` は Lightsail が注入する場合があります。未設定時は `80` が使われます。

## 5. 初回デプロイ後

1. `/up` が 200 を返すことを確認
2. ログイン・クエスト一覧が表示されること
3. アバターアップロードが S3 署名付き URL になること
4. `DB_FRESH=false` のまま再デプロイしてもデータが消えないこと

## 6. Render からの移行

| 項目 | Render（現状） | AWS Lightsail |
|---|---|---|
| DB | SQLite（揮発性） | MySQL（永続） |
| ファイル | ローカル disk | S3 |
| ポート | `10000`（自動注入） | `80` |
| 起動脚本 | `docker/aws/start.sh`（共通） |

Render 用 `render.yaml` は開発検証用として残しています。本番は Lightsail + MySQL + S3 を使用してください。

## 7. コスト目安（参考）

- Container Micro: 約 $10/月
- MySQL Standard: 約 $15/月
- S3 + 転送: 利用量次第（小規模なら数ドル未満）

為替 160 円/USD の場合、合計おおよそ **4,000〜5,500 円/月（税別）** 程度。

## トラブルシューティング

### MySQL 接続エラー

- `DB_HOST` が Lightsail DB のエンドポイントか確認
- コンテナと DB が同一リージョンか確認
- `DB_WAIT_ATTEMPTS` を増やす（起動順の問題）

### S3 アップロード失敗

- `league/flysystem-aws-s3-v3` がインストールされているか（`composer install`）
- IAM ポリシーと `AWS_BUCKET=ai-academy-prd` / `AWS_DEFAULT_REGION` を確認
- `FILESYSTEM_PUBLIC_DISK=s3` になっているか

### 502 Bad Gateway

- コンテナログで php-fpm / nginx のエラーを確認
- `PORT` が Lightsail の公開ポート設定と一致しているか
