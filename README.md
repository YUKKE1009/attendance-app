# 勤怠管理アプリ

Laravelを使って作成した、指示書の仕様に基づく勤怠管理アプリです。
画面設計・テーブル仕様書に沿って開発しており、ダミーデータも作成済みです。

# 使用技術（実行環境）
- **PHP**: 8.1.34
- **Laravel**: 8.83.8
- **MySQL**: 8.0
- **nginx**: 1.21.1

# 環境構築
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
  - ログインアドレス：　admin@coachtech.com
  - ログインパスワード：　password123

- **phpMyAdmin (DB管理)**: [http://localhost:8080/](http://localhost:8080/)
  - ユーザー名: `laravel_user`
  - パスワード: `laravel_pass`

# 機能一覧
- 本アプリは、一般ユーザー（従業員）の勤怠管理と、管理者による確認・修正申請の承認フローを備えています。

1. 一般ユーザー機能（勤怠管理）
- 会員登録・ログイン機能：メール認証（必須要件）を含むセキュアな認証フロー。
- 勤怠登録機能：ワンクリックでの出勤、休憩開始、休憩戻り、退勤登録。
- リアルタイム表示：現在の勤務ステータス（勤務外、出勤中、休憩中など）および現在時刻の表示。
- 勤怠履歴閲覧：自身の過去の勤怠実績を一覧で確認。
- 勤怠詳細・修正申請：各勤怠データの詳細確認および、管理者への修正依頼機能。
- 申請一覧・通知：自身が提出した修正申請のステータス（承認待ち、承認済み）の確認。

2. 管理者機能（管理・承認）
- 管理者専用ログイン：専用画面からの管理権限アクセス。
- 全スタッフ勤怠管理：全従業員の勤怠実績を日次・月次単位で閲覧。
- スタッフ一覧：登録されているスタッフ情報の管理。
- 承認フロー管理：スタッフから提出された勤怠修正依頼の承認・却下プロセス。

3. 共通・応用機能
- メール認証機能：新規登録時の正当なメールアドレス確認プロセス（応用要件実装済み）。
- レスポンシブデザイン：PCだけでなく、スマートフォンからの操作にも対応したUI/UX。

## テスト

PHPUnitを用いて、指示書のテストケース一覧（ID 1〜16）をカバーする自動テストを実装済みです。
- 全テスト項目: 27項目
  - 基本機能テスト: 24項目
  - 応用機能（メール認証）: 3項目
  - ステータス: すべて合格（OK）

# テストの実行方法

1. テスト用データベースの作成（初回のみ）
Mac（ホスト側）のターミナルで実行し、テスト用のデータベースを作成します。
```bash
docker-compose exec mysql mysql -u root -proot -e "CREATE DATABASE IF NOT EXISTS attendance_test;"
```
2. コンテナ内へのログイン
PHPコンテナに入ります。
```bash
docker-compose exec php bash
```

3. テスト用環境変数の設定
テスト実行時に開発用DBを破壊しない設定と、Stripeのダミーキーを設定します。コンテナ内で実行してください。
```bash
# .env をコピーしてテスト用ファイルを作成
cp .env .env.testing

# DB接続先をテスト用DB(demo_test)に書き換え
sed -i 's/DB_DATABASE=fleamarket_db/DB_DATABASE=demo_test/g' .env.testing
```

4. テストの実行
コンテナ内で以下のコマンドを実行し、全てのテストが PASS することを確認してください。
```bash
php artisan test
```

# 技術的な工夫
- **ER図に基づくデータベース設計** 勤怠・休憩・ユーザー・修正申請の各リレーションを適切に設計し、データ整合性を担保。

- **品質保証の徹底:**: 要件定義に基づくテストケースを全て自動化。コード修正時のデグレードを即座に検知可能な環境を構築。

- **環境構築の簡略化**: Dockerを使用し、`docker-compose up` だけで開発環境が即座に整うよう設計。

# 開発状況

- **基本・応用機能の実装完了**:
要件シートの機能要件一覧（FN001〜FN029）に基づいた基本機能および応用要件（メール認証、修正申請フロー等）をすべて実装済みです。

- **自動テストによる品質保証**:
テストケース一覧（ID 1〜16）に基づいた全27項目のテストを実行し、全項目で合格（OK）を確認済みです。

# ER図

![ER図](<docs/模擬案件②勤怠管理アプリER図.drawio .png>)
- ・drawio　URL
https://app.diagrams.net/#G1899JpzpkCNUjCA8Ne5nnOVq-hsoyzpSm#%7B%22pageId%22%3A%222NCGB7udatKj07cz19ea%22%7D
