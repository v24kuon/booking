# 予約システム 仕様ドキュメント（実装前 最終確認）

本ドキュメントは、実装着手前の最終確認用に、確定した要件と設計判断を一貫した仕様として統合したものです。

---

## 1. 前提・非機能

### 1.1 技術前提

- **フレームワーク**: Laravel（リポジトリ直下に新規作成）
- **認証**: Laravel Fortify（Breezeは使わない）
- **UI**: Pico.css（同梱・ビルド不要）
  - Tailwind/Vite前提を採用しない（Node.js不要）
  - CSS/JSは `public/` 配下に配置し `asset()` 参照
- **決済**: Stripe + Laravel Cashier（Stripe）中心
  - **Webhook前提**（`/stripe/webhook`）
- **API**: 実装しない

### 1.2 運用前提（レンタルサーバー）

- Node.jsなしで運用
- cronは **5分間隔**で設定可能（`php artisan schedule:run`）
- Stripe Webhookは https で外部から到達可能

---

## 2. DB設計（ソース）

- DB設計のソースは `DB情報/*.csv`
- CSVの文字化け（Shift-JIS系）はあるが、英数字のカラムID/型/PK/FK等を基に実装する

---

## 3. ドメインモデル（用語と意味）

### 3.1 エンティティ/概念

- **Member（会員）**: `member_info`
  - `member_mail` をログインID
  - `member_password` はハッシュ
  - `status`（仮/本/退会等）で制御
- **Program（プログラム）**: `program_master`
  - `program_category`（カテゴリ）
  - `program_point`（ポイント消費量）
  - `program_ticket`（回数券消費量）
- **Course（コース）**: `cource_master`
  - 「月謝で通える `program_category` の集合」
  - 例: ヨガわいわいコース = ヨガカテゴリ + セラピーカテゴリ
- **CourseCategorySet（コースに含めるカテゴリ）**: `course_program`
  - `cource_id × program_category`
- **Session（枠）**: `session`
  - `capacity` / `reserved_count`（通常）
  - `exp_capacity` / `reserved_exp_count`（体験）
- **Reservation（予約）**: `reseve_info`
  - `reserve_type`（通常/体験）
  - `reserve_payment`（支払い方法）
  - `contract_id`（消費元の契約）
- **Plan（販売プラン）**: `plan_master`
  - `plan_type`（月額/回数券/ポイント/コース）
  - `plan_usage_count`（付与量）
  - `plan_usage_date`（有効日数）
  - `cource_id`（サブスクの場合に必須）
- **Contract（権利/契約台帳）**: `contract_info`
  - `plan_remain_count`（残数/残ポイント）
  - `plan_limit_date`（失効日）
- **ContractEvent（履歴）**: `contract_event`
  - `event_type` は `VARCHAR(50)` のため運用値を追加可能

### 3.2 DDD観点の集約

- **Reservation集約**: 容量/締切/重複/体験制限/仮予約期限/席解放
- **Contract集約**: 残数/失効/消費/戻し（ただし失効後戻しなし）
- **Webhook（Stripe）**: 外部イベント → 内部状態更新（冪等）

---

## 4. テーブル一覧（採用）

- `member_info`
- `reseve_info`
- `session`
- `program_master`
- `cource_master`
- `course_program`
- `location_master`, `location_img`
- `staff_master`, `staff_img`
- `plan_master`
- `contract_info`
- `contract_event`
- `deadline_master`
- `additional_item_master`
- `label_setting`
- `mail_info_master`

### 4.1 存在するがV1で使わない

- `program_reputation_rule`（繰り返し設定）
  - V1は **枠（session）手動作成のみ**

---

## 5. CSV不整合の補正ルール（実装時の型）

- `session` の終了時刻は `end_at` として実装（CSV誤記補正）
- `start_at/end_at/canceled_at` は **DATETIME**
- `program_reputation_rule.start_time/end_time` は **TIME**
- `reseve_info.contract_id` は **VARCHAR(10)** に揃える

---

## 6. 支払い手段のマトリクス（確定）

### 6.1 通常予約（`reserve_type=1`）

- **許可**: ポイント（2）/回数券（3）/サブスク（5）
- **不可**: 現金（1）/カード（4）

### 6.2 体験予約（`reserve_type=2`）

- **許可**: 現金（来店時）/カード（Stripe単発決済）
- **不可**: ポイント/回数券/サブスク

### 6.3 `reserve_payment` の運用

- 既存CSV: 1現金 / 2ポイント / 3回数券 / 4カード
- **拡張**: **5サブスク**（追加）

---

## 7. 制約・バリデーション（確定）

### 7.1 重複予約

- DB: `reseve_info` に `unique(member_id, session_id)`
- 目的: 同一会員の同一枠多重予約を防止

### 7.2 体験回数制限

- **会員×program_idで1回まで**
- 判定: `reseve_info(reserve_type=2)` と `session.program_id` の join

### 7.3 予約/キャンセル締切

- `deadline_master.reserve_deadline` / `deadline_master.cancel_deadline` を利用
- 締切後キャンセルは可能だが（席は空く）、戻し/返金はしない

### 7.4 権利候補の選択

- ポイント/回数券/サブスクで契約候補が複数ある場合
  - **自動選択しない**
  - UIでユーザーが `contract_id` を選択して確定

---

## 8. 消費量（確定）

- ポイント予約: `program_point` 消費
- 回数券予約: `program_ticket` 消費
- サブスク予約: **常に1回**消費

---

## 9. 付与量・失効（確定）

### 9.1 付与量（CSV根拠）

- `plan_master.plan_usage_count` を付与量として使用
  - サブスク: 1周期あたりの付与回数N
  - 回数券: 購入時の付与回数
  - ポイント: 購入時の付与ポイント

### 9.2 失効

- 回数券/ポイントは **期限切れで失効**
- 失効日: 購入日 + `plan_master.plan_usage_date` 日
  - 保存先: `contract_info.plan_limit_date`
- 失効判定: **予約作成時刻基準**
- 失効後キャンセル: **戻さない**

---

## 10. サブスク（コース追加＝複数契約）

### 10.1 複数契約

- 会員はサブスク契約を複数同時保有できる（コース追加）

### 10.2 コース範囲チェック

- サブスク契約の `plan_master.cource_id` と、予約対象の `program_master.program_category` を `course_program` で突合

### 10.3 付与タイミング

- 初回購入直後も含め、課金成功で **`plan_remain_count = plan_usage_count` にリセット**（繰越なし）

---

## 11. 予約フロー仕様

### 11.1 共通（整合性）

- `session` 行をロックして容量整合性を担保
- 通常枠: `capacity` vs `reserved_count`
- 体験枠: `exp_capacity` vs `reserved_exp_count`

### 11.2 通常予約（ポイント/回数券/サブスク）

1. 予約締切チェック
2. 重複予約チェック
3. `session` ロックして空きを確認 → `reserved_count += 1`
4. 支払い種別に応じた契約候補抽出
5. 候補が複数ならユーザー選択
6. 選択契約をロックして残数減算
7. `reseve_info` 作成（`reserve_status=1`）

#### 通常予約キャンセル

- **席は必ず空く**（締切後も `reserved_count -= 1`）
- 締切前: 残数を戻す
- 締切後: 残数を戻さない

### 11.3 体験予約（現金）

- 締切/体験回数制限/重複チェック
- `session` ロック → `reserved_exp_count += 1`
- `reseve_info` 作成

---

## 12. 体験カード（Stripe単発）— 仮予約あり（DB変更最小）

### 12.1 目的

- Checkout中に他ユーザーが埋めることによる「決済したのに満席」を防止

### 12.2 DB表現

- 新テーブル追加なし
- `reseve_info.payment_status=0` を pending として運用追加
- `additional_info` に
  - `stripe.checkout_session_id`
  - `pending_expires_at`（15分）

### 12.3 体験カードの作成

1. 締切/重複/体験回数をチェック
2. `session` ロックで体験枠空きを確認
3. `reserved_exp_count += 1`（席確保）
4. `reseve_info` 作成（`reserve_type=2`, `reserve_payment=4`, `reserve_status=1`, `payment_status=0`）
5. `pending_expires_at=now+15min`
6. Checkoutへリダイレクト

### 12.4 Webhook確定

- `checkout.session.completed`（reserve_id metadata）で `payment_status=1` に更新

### 12.5 期限切れ自動解放（運用）

- cron（5分）＋疑似cronで掃除
- `payment_status=0` かつ `pending_expires_at < now` は
  - `reserve_status=9`, `payment_status=9`
  - `reserved_exp_count -= 1`

### 12.6 体験カードのキャンセルと返金

- 締切前: 自動返金
- 締切後: 返金なし（キャンセルは可能、席は空く）

---

## 13. Stripe/Cashier設計

### 13.1 plan ↔ Stripe price の対応

- DB変更最小のため、`plan_master.additional_info(JSON)` に `stripe_price_id` を保持
- Webhook/Checkout時に price_id→plan_id を逆引き

### 13.2 Webhook

- `/stripe/webhook`
- 署名検証
- 冪等: `event.id` を保存して二重適用防止

### 13.3 イベント別の更新

- `customer.subscription.created`: `contract_info` 作成（subscription id保存、plan_id決定）
- `invoice.payment_succeeded`: サブスク `plan_remain_count=plan_usage_count` にリセット
- `customer.subscription.deleted`: 解約
- `checkout.session.completed`（mode=payment）
  - 回数券/ポイント購入: `plan_remain_count += plan_usage_count`、`plan_limit_date` 設定
  - 体験カード: `payment_status=1`（確定）

---

## 14. 採番（接頭辞+連番）

- 目的: CSVのID形式（MB/SS/R/CT…）を維持しつつ、同時実行でも衝突しない
- 方針: **採番テーブルを追加**し `SELECT ... FOR UPDATE` で安全に払い出す
- 欠番（飛び番）は許容
- 実装
  - `id_sequences(key, next_number)` を追加
  - 発行はトランザクション内で `SELECT ... FOR UPDATE` → 更新 → ID文字列化

---

## 15. 管理画面（要件）

- 枠（session）は **全て手動作成**
- 管理機能
  - コース: `cource_master` CRUD
  - コース中身: `course_program` CRUD
  - プラン: `plan_master` CRUD（price_idの設定含む）
  - プログラム/スタッフ/ロケーション各マスタ + 画像
  - 予約/契約の参照運用
  - テンプレ: `mail_info_master`

---

## 16. 通知（要件）

- `mail_info_master` をテンプレソース
- 送信タイミング例
  - 本登録
  - 予約完了/キャンセル
  - サブスク購入/更新/解約
  - 回数券/ポイント購入

---

## 17. 運用（レンタルサーバー）

- Node不要
- cron: `php artisan schedule:run` を5分間隔
- 仮予約期限: 15分
- 疑似cron（アクセス時掃除）併用
