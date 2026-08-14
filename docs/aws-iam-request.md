# AWS 権限依頼・構築チェックリスト（PRUM AI Academy）

会社 AWS 管理者への依頼文と、権限を付与された後に自分で行う作業手順です。

---

## 1. 管理者への依頼文（コピペ用）

```
件名: PRUM AI Academy 本番環境構築のための AWS 権限依頼

お疲れさまです。
PRUM AI Academy の本番環境を AWS Lightsail 上に構築します。
以下の権限と情報をご付与・ご共有いただけますでしょうか。

■ 目的
- Web アプリ（Laravel + Vue）の本番デプロイ
- 利用規模: 受講生 13〜15 名、同時接続 5〜8 名程度

■ 使用サービス
- Amazon Lightsail Container Service（Micro 想定・サービス名: `ai-academy-prd`）
- Amazon Lightsail Managed Database（MySQL・インスタンス名: `ai-academy-prd-db`）
- Amazon S3 バケット `ai-academy-prd`（アバター・クエスト提出物）
- （任意）Amazon SES / CloudWatch / Budgets

■ 必要な IAM 権限
- リージョン: ap-northeast-1（東京）
- Lightsail: Container / Database の作成・更新・削除・デプロイ
- S3: バケット `ai-academy-prd` の作成・管理
- IAM: アプリ用 IAM ユーザー `ai-academy-prd-s3-app` とアクセスキーの作成（S3 専用・最小権限）

■ ログイン方法
- AWS コンソールへのログイン URL（SSO または IAM ユーザー）
- MFA 要否

■ 確認したいこと
- 使用する AWS アカウント ID
- 本番ドメイン（APP_URL 用）
- 月額コスト上限の承認（目安: 約 $26〜29/月 ≒ 4,000〜5,500 円/月 税別）

■ 参考
- リポジトリ内 docs/aws-lightsail-deploy.md にデプロイ手順あり
- 本番環境変数テンプレート: api-server/.env.production.example

よろしくお願いいたします。
```

---

## 2. 開発者用 IAM ポリシー（管理者がアタッチするもの）

アカウント ID（`ACCOUNT_ID`）のみ環境に合わせて置き換えてください。リソース名は `ai-academy-prd` で統一しています。

### 2-1. Lightsail 操作

```json
{
  "Version": "2012-10-17",
  "Statement": [
    {
      "Sid": "LightsailManage",
      "Effect": "Allow",
      "Action": [
        "lightsail:*"
      ],
      "Resource": "*"
    }
  ]
}
```

Lightsail だけ絞る場合は AWS マネージドポリシー `AmazonLightsailFullAccess` でも可。

### 2-2. S3（特定バケットのみ）

```json
{
  "Version": "2012-10-17",
  "Statement": [
    {
      "Sid": "S3BucketManage",
      "Effect": "Allow",
      "Action": [
        "s3:CreateBucket",
        "s3:DeleteBucket",
        "s3:ListBucket",
        "s3:GetBucketLocation",
        "s3:GetBucketPolicy",
        "s3:PutBucketPolicy",
        "s3:DeleteBucketPolicy",
        "s3:GetBucketPublicAccessBlock",
        "s3:PutBucketPublicAccessBlock",
        "s3:GetEncryptionConfiguration",
        "s3:PutEncryptionConfiguration"
      ],
      "Resource": "arn:aws:s3:::ai-academy-prd"
    },
    {
      "Sid": "S3ObjectManage",
      "Effect": "Allow",
      "Action": [
        "s3:PutObject",
        "s3:GetObject",
        "s3:DeleteObject",
        "s3:PutObjectAcl"
      ],
      "Resource": "arn:aws:s3:::ai-academy-prd/*"
    },
    {
      "Sid": "S3ListForConsole",
      "Effect": "Allow",
      "Action": "s3:ListAllMyBuckets",
      "Resource": "*"
    }
  ]
}
```

### 2-3. アプリ用 IAM ユーザー作成

```json
{
  "Version": "2012-10-17",
  "Statement": [
    {
      "Sid": "CreateAppUser",
      "Effect": "Allow",
      "Action": [
        "iam:CreateUser",
        "iam:DeleteUser",
        "iam:GetUser",
        "iam:ListUsers",
        "iam:PutUserPolicy",
        "iam:DeleteUserPolicy",
        "iam:GetUserPolicy",
        "iam:ListUserPolicies",
        "iam:CreateAccessKey",
        "iam:DeleteAccessKey",
        "iam:ListAccessKeys",
        "iam:TagUser"
      ],
      "Resource": "arn:aws:iam::ACCOUNT_ID:user/ai-academy-prd-*"
    }
  ]
}
```

`ACCOUNT_ID` は 12 桁の AWS アカウント ID。

### 2-4. 任意（ログ・予算）

```json
{
  "Version": "2012-10-17",
  "Statement": [
    {
      "Sid": "ReadLogsAndBudgets",
      "Effect": "Allow",
      "Action": [
        "logs:Describe*",
        "logs:Get*",
        "logs:List*",
        "logs:FilterLogEvents",
        "budgets:ViewBudget",
        "budgets:Describe*"
      ],
      "Resource": "*"
    }
  ]
}
```

---

## 3. アプリ用 IAM ユーザー（Laravel → S3）

構築時に自分で作るユーザー名: `ai-academy-prd-s3-app`

### ポリシー（インラインポリシー）

```json
{
  "Version": "2012-10-17",
  "Statement": [
    {
      "Effect": "Allow",
      "Action": [
        "s3:PutObject",
        "s3:GetObject",
        "s3:DeleteObject"
      ],
      "Resource": "arn:aws:s3:::ai-academy-prd/*"
    },
    {
      "Effect": "Allow",
      "Action": "s3:ListBucket",
      "Resource": "arn:aws:s3:::ai-academy-prd"
    }
  ]
}
```

アクセスキーは **1 回だけ表示** されるため、控えて Lightsail Container の環境変数に設定する。

```env
FILESYSTEM_PUBLIC_DISK=s3
AWS_ACCESS_KEY_ID=...
AWS_SECRET_ACCESS_KEY=...
AWS_DEFAULT_REGION=ap-northeast-1
AWS_BUCKET=ai-academy-prd
```

---

## 4. 権限をもらった後の作業チェックリスト

### Phase 0: ログイン確認

- [ ] AWS コンソールにログインできる
- [ ] リージョンが `ap-northeast-1` になっている
- [ ] Lightsail コンソールが開ける

### Phase 1: S3

- [ ] バケット `ai-academy-prd` を作成（Block Public Access すべてオンで可）
- [ ] アプリ用 IAM ユーザー `ai-academy-prd-s3-app` を作成 + ポリシーアタッチ + アクセスキー発行
- [ ] キーを安全な場所に保管（Lightsail 環境変数用）

### Phase 2: Lightsail MySQL

- [ ] Standard プラン等で MySQL `ai-academy-prd-db` を作成
- [ ] DB 名・ユーザー・パスワードを控える
- [ ] エンドポイント（ホスト名）を控える
- [ ] 必要ならアプリコンテナからの接続を許可

### Phase 3: Docker イメージ

```bash
cd api-server
docker build -t ai-academy-prd .
```

- [ ] ローカルでビルド成功
- [ ] `aws lightsail push-container-image --service-name ai-academy-prd --label ai-academy-prd --image ai-academy-prd` で push

### Phase 4: Lightsail Container Service

- [ ] Micro プランで Container Service `ai-academy-prd` を作成
- [ ] 環境変数を設定（`.env.production.example` 参照）
- [ ] 必須: `APP_KEY`, `APP_URL`, `DB_*`, `AWS_*`, `FILESYSTEM_PUBLIC_DISK=s3`
- [ ] 必須: `DB_FRESH=false`, `RUN_SEEDER=false`
- [ ] 公開ポート: コンテナ `80`
- [ ] ヘルスチェック: `/up`
- [ ] HTTPS / カスタムドメイン（任意）

### Phase 5: 動作確認

- [ ] `/up` が 200
- [ ] ログインできる
- [ ] クエスト一覧が表示される
- [ ] アバターアップロード → S3 URL になる
- [ ] 再デプロイしても DB データが消えない

---

## 5. 環境変数一覧（Lightsail Container に設定）

| 変数 | 例 / 備考 |
|---|---|
| `APP_ENV` | `production` |
| `APP_DEBUG` | `false` |
| `APP_KEY` | `php artisan key:generate --show` |
| `APP_URL` | `https://本番ドメイン` |
| `DB_CONNECTION` | `mysql` |
| `DB_HOST` | Lightsail MySQL エンドポイント |
| `DB_PORT` | `3306` |
| `DB_DATABASE` | `prum_ai_academy` |
| `DB_USERNAME` | 作成時のユーザー |
| `DB_PASSWORD` | 作成時のパスワード |
| `FILESYSTEM_PUBLIC_DISK` | `s3` |
| `AWS_ACCESS_KEY_ID` | アプリ用 IAM |
| `AWS_SECRET_ACCESS_KEY` | アプリ用 IAM |
| `AWS_DEFAULT_REGION` | `ap-northeast-1` |
| `AWS_BUCKET` | `ai-academy-prd` |
| `SESSION_DRIVER` | `database` |
| `SESSION_SECURE_COOKIE` | `true` |
| `CACHE_STORE` | `database` |
| `QUEUE_CONNECTION` | `database` |
| `DB_FRESH` | `false` |
| `RUN_SEEDER` | `false` |
| `PORT` | `80` |

---

## 6. セキュリティ注意

- アプリ用 IAM キーは Git にコミットしない
- 人間用 IAM とアプリ用 IAM は分ける
- 本番 `DB_FRESH=true` / `RUN_SEEDER=true` は使わない
- MFA を有効にする（会社ポリシーに従う）

---

## 7. 関連ドキュメント

- [aws-lightsail-deploy.md](./aws-lightsail-deploy.md) — デプロイ詳細
- [../api-server/.env.production.example](../api-server/.env.production.example) — 環境変数テンプレート
