## Booking（予約システム）

Laravel 12 + SQLite をベースにした予約/会員管理システムです。現状は **管理画面（マスタ管理）を先行実装**しています。

### 開発環境

- **Laravel Herd**（ローカルドメイン）: `http://booking.test`
- **DB**: SQLite（`database/database.sqlite`）

### 主な機能（現状）

- **管理者ログイン（admin guard）**
  - URL: `/admin/login`
- **管理画面（マスタCRUD）**
  - プログラム
  - コース（カテゴリ管理含む）
  - プラン
  - スタッフ（画像管理含む）
  - ロケーション（画像管理含む）
  - セッション枠
- **会員ログイン（Fortify）**
  - URL: `/login`
  - ログインID: `member_mail`
  - パスワード: `member_password`
  - ログイン後の雛形: `/mypage`
- **カスタムID採番（prefix + 連番）**
  - `id_sequences` テーブル + `app/Services/IdGenerator.php`

### Stripe連携（現状）

現状は **決済は未実装**です。

- **実装済み**: プランに `stripe_price_id` を保存（`plan_master.additional_info(JSON)`）し、管理画面で “Connected” 表示
- **未実装**: Cashier導入、Checkout作成、`/stripe/webhook`、署名検証、冪等（`event.id`保存）、イベント別の内部状態更新

### フロントエンド方針

- 管理画面: `public/css/admin.css` を `asset()` で読み込み（Glassmorphism + Aurora UI のデザイン）
- 会員画面: `public/css/pico.min.css` を `asset()` で読み込み
- 現状の画面表示に **Vite/Tailwind は必須ではありません**（NodeはUIのために必須ではない運用）

## セットアップ（Herd + SQLite）

### 前提

- PHP（HerdのPHP推奨）
- Composer

### 手順

1. 依存インストール

```bash
composer install
```

2. `.env` を用意（`.env.example` がある場合はコピー）

```bash
cp .env.example .env
```

3. `.env` を設定

- `APP_URL=http://booking.test`
- `DB_CONNECTION=sqlite`

4. SQLiteファイル作成

```bash
touch database/database.sqlite
```

5. マイグレーション

```bash
php artisan migrate
```

6. 管理者アカウント作成（例）

```bash
php artisan admin:create --name=admin --email=admin@example.com --password=admin1234 --force
```

7. アクセス

- 管理画面ログイン: `http://booking.test/admin/login`
- 会員ログイン: `http://booking.test/login`

## 開発コマンド

### テスト

```bash
php artisan test
```

### フォーマッタ（Pint）

```bash
vendor/bin/pint --dirty
```

## ドキュメント

- 仕様メモ: `docs/spec.md`

## 注意

- 会員登録/パスワードリセット等は現状無効（Fortify機能を絞っているため）。`member_info` への投入方法は別途用意してください。
- `.env` や `database/database.sqlite` をリポジトリにコミットしないでください。
