# 勤怠管理アプリ

Laravelを使って作成した、指示書の仕様に基づく勤怠管理アプリです。
画面設計・テーブル仕様書に沿って開発しており、ダミーデータも作成済みです。

## 使用技術（実行環境）

- **PHP**: 8.1.34
- **Laravel**: 8.83.8
- **MySQL**: 8.0
- **nginx**: 1.21.1

## 環境構築

### 1. Dockerビルド

```bash
# リポジトリをクローン
git clone git@github.com:YUKKE1009/attendance-app.git
# ディレクトリ移動
cd fleamarket-app
# Dockerビルド＆起動
docker-compose up -d --build
# VSCodeで開く
code .
```

### 2. Laravel環境構築

```bash
# コンテナ内に入る
docker-compose exec php bash
# 依存パッケージをインストール
composer install
# 環境変数ファイルを作成
cp .env.example .env
```

[.envファイルの設定値]

```.env
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=laravel_db
DB_USERNAME=laravel_user
DB_PASSWORD=laravel_pass

MAIL_FROM_ADDRESS="no-reply@attendance.com"
MAIL_FROM_NAME="${APP_NAME}"

```

### 3. Laravel初期化

```bash
php artisan key:generate
php artisan storage:link
php artisan migrate
php artisan db:seed
php artisan config:clear
```

## 環境開発（主要アクセス先）

開発環境起動後、以下のURLで各機能にアクセス可能です。

- **会員登録画面（一般ユーザー）**: http://localhost/register

- **ログイン画面（一般ユーザー）**: http://localhost/login

- **ログイン画面（管理者）**: http://localhost/admin/login
　ログインアドレス：　admin@coachtech.com
　ログインパスワード：　password123

- **phpMyAdmin (DB管理)**: [http://localhost:8080/](http://localhost:8080/)
  - ユーザー名: `laravel_user`
  - パスワード: `laravel_pass`

## 機能一覧

- 

## テスト

PHPUnitを用いて、指示書のテストケース一覧（ID 1〜16）をカバーする自動テストを実装済みです。

- テスト数: 19個

- アサーション数: 88個

- ステータス: すべて合格（OK）

# テストの実行方法

1. テスト用データベースの作成（初回のみ）
Mac（ホスト側）のターミナルで実行し、テスト用のデータベースを作成します。
```bash
docker-compose exec mysql mysql -u root -proot -e "CREATE DATABASE IF NOT EXISTS demo_test;"
```
2. コンテナ内へのログイン
PHPコンテナに入ります。
```bash
docker-compose exec php bash
```
3. テストフォルダの準備
Gitで管理されていない空のテストディレクトリを作成します。これがないとテスト実行時にエラーになります。
```bash
mkdir -p tests/Unit && touch tests/Unit/.gitkeep
```

4. テスト用環境変数の設定
テスト実行時に開発用DBを破壊しない設定と、Stripeのダミーキーを設定します。コンテナ内で実行してください。
```bash
# .env をコピーしてテスト用ファイルを作成
cp .env .env.testing

# DB接続先をテスト用DB(demo_test)に書き換え
sed -i 's/DB_DATABASE=fleamarket_db/DB_DATABASE=demo_test/g' .env.testing

# Stripeのダミーキーを追記（テストコード内でモック化しているため値は任意でOK）
echo "STRIPE_PUBLIC_KEY=pk_test_sample" >> .env.testing
echo "STRIPE_SECRET_KEY=sk_test_sample" >> .env.testing
```

5. テストの実行
コンテナ内で以下のコマンドを実行し、全てのテストが PASS することを確認してください。
```bash
php artisan test
```

## 技術的な工夫

- **ER図に基づく正規化**: カテゴリー管理を中間テーブルで行い、拡張性を持たせています。

- **テスト駆動開発（に近いアプローチ）**: 仕様に基づいたテストケースを作成し、全19項目の挙動を担保。

- **環境構築の簡略化**: Dockerを使用し、`docker-compose up` だけで開発環境が即座に整うよう設計。

## 開発状況

- **基本・応用機能の実装完了**:
要件シートの機能要件一覧（要件ID FN001〜FN029）に基づいた基本機能および応用要件（メール認証等）をすべて実装済みです。

- **自動テストによる品質保証**:
要件シートのテストケース一覧（ID 1〜16）に基づいた19個のテストケース（89個のアサーション）を実行し、全項目で合格（OK）を確認済みです。

## ER図

![ER図](<docs/模擬案件(フリーマーケットアプリ)ER図.png>)
