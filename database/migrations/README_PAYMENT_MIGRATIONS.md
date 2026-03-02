# Payment System Migrations

## Tổng quan

5 migration files này tạo nền tảng database cho hệ thống thanh toán tích hợp SePay, bao gồm:
- Thanh toán đơn hàng (User → System)
- Quản lý ví Affiliate
- Rút tiền (System → Affiliate)
- Logging webhook từ SePay

## Danh sách Migrations

### 015_add_payment_fields_to_orders.sql
**Mục đích:** Bổ sung các trường SePay vào bảng `orders`

**Các trường mới:**
- `sepay_transaction_id` - ID giao dịch từ SePay
- `sepay_qr_code` - Dữ liệu QR code
- `qr_generated_at` - Thời gian tạo QR
- `qr_expired_at` - Thời gian hết hạn QR (120s)
- `payment_timeout` - Timeout thanh toán (mặc định 120s)
- `payment_completed_at` - Thời gian hoàn thành thanh toán
- `payment_failed_at` - Thời gian thanh toán thất bại
- `payment_error_message` - Thông báo lỗi
- `webhook_received_at` - Thời gian nhận webhook
- `is_expired` - Đơn hàng đã hết hạn chưa

### 016_create_wallet_transactions_table.sql
**Mục đích:** Tạo bảng `wallet_transactions` để tracking lịch sử giao dịch ví

**Các loại giao dịch:**
- `commission` - Cộng hoa hồng
- `withdrawal` - Rút tiền
- `adjustment` - Điều chỉnh thủ công
- `refund` - Hoàn tiền (thu hồi hoa hồng)

**Tính năng:**
- Lưu balance trước/sau giao dịch
- Link với order_id, withdrawal_id
- Audit trail đầy đủ

### 017_create_withdrawal_requests_table.sql
**Mục đích:** Tạo bảng `withdrawal_requests` để quản lý yêu cầu rút tiền

**Workflow:**
1. Affiliate tạo yêu cầu → status: `pending`
2. Admin duyệt → status: `processing`, tạo QR SePay
3. Admin quét QR và chuyển tiền
4. Webhook về → status: `completed`

**Các trường quan trọng:**
- `withdraw_code` - Mã rút tiền duy nhất (VD: RUT12345)
- `amount` - Số tiền rút
- `fee` - Phí rút (hiện tại = 0)
- `bank_name`, `bank_account`, `account_holder` - Thông tin NH
- `sepay_qr_code` - QR cho admin quét

### 018_add_wallet_fields_to_affiliates.sql
**Mục đích:** Bổ sung các trường ví và ngân hàng vào bảng `affiliates`

**Các trường ví:**
- `balance` - Số dư khả dụng (có thể rút)
- `pending_withdrawal` - Tiền đang chờ rút (đóng băng)
- `total_withdrawn` - Tổng đã rút

**Các trường ngân hàng:**
- `bank_name`, `bank_account`, `account_holder` - Thông tin NH
- `bank_verified` - Đã xác thực chưa
- `bank_change_otp` - OTP để đổi thông tin NH
- `bank_change_otp_expires_at` - Hết hạn OTP

**Bảo mật:**
- Yêu cầu OTP khi thay đổi thông tin ngân hàng
- Tracking lần cuối thay đổi

### 019_create_sepay_webhooks_log_table.sql
**Mục đích:** Tạo bảng `sepay_webhooks_log` để log tất cả webhook từ SePay

**Webhook types:**
- `payment_in` - Thanh toán từ User (mua hàng)
- `payment_out` - Chi tiền cho Affiliate (rút tiền)

**Tính năng:**
- Lưu raw data đầy đủ (JSON)
- Verify signature
- Track processing status
- Link với order_id hoặc withdrawal_id
- Lưu IP address để audit

## Cách chạy Migrations

### Bước 1: Chạy migrations
```bash
# Từ command line
php scripts/migrate.php

# Hoặc truy cập qua browser
http://localhost/scripts/migrate.php
```

### Bước 2: Verify migrations
```bash
# Từ command line
php scripts/verify_payment_migrations.php

# Hoặc truy cập qua browser
http://localhost/scripts/verify_payment_migrations.php
```

## Kiểm tra thủ công

### Kiểm tra bảng đã tạo
```sql
SHOW TABLES LIKE '%wallet%';
SHOW TABLES LIKE '%withdrawal%';
SHOW TABLES LIKE '%sepay%';
```

### Kiểm tra cấu trúc bảng
```sql
DESCRIBE orders;
DESCRIBE affiliates;
DESCRIBE wallet_transactions;
DESCRIBE withdrawal_requests;
DESCRIBE sepay_webhooks_log;
```

### Kiểm tra indexes
```sql
SHOW INDEX FROM orders;
SHOW INDEX FROM wallet_transactions;
SHOW INDEX FROM withdrawal_requests;
SHOW INDEX FROM sepay_webhooks_log;
```

## Rollback (nếu cần)

Nếu cần rollback, chạy các lệnh sau theo thứ tự ngược:

```sql
-- 019: Drop sepay_webhooks_log
DROP TABLE IF EXISTS sepay_webhooks_log;

-- 017: Drop withdrawal_requests
DROP TABLE IF EXISTS withdrawal_requests;

-- 016: Drop wallet_transactions
DROP TABLE IF EXISTS wallet_transactions;

-- 018: Remove wallet fields from affiliates
ALTER TABLE affiliates
DROP COLUMN balance,
DROP COLUMN pending_withdrawal,
DROP COLUMN total_withdrawn,
DROP COLUMN bank_name,
DROP COLUMN bank_account,
DROP COLUMN account_holder,
DROP COLUMN bank_branch,
DROP COLUMN bank_verified,
DROP COLUMN bank_verified_at,
DROP COLUMN bank_change_otp,
DROP COLUMN bank_change_otp_expires_at,
DROP COLUMN bank_last_changed_at;

-- 015: Remove payment fields from orders
ALTER TABLE orders
DROP COLUMN sepay_transaction_id,
DROP COLUMN sepay_qr_code,
DROP COLUMN qr_generated_at,
DROP COLUMN qr_expired_at,
DROP COLUMN payment_timeout,
DROP COLUMN payment_completed_at,
DROP COLUMN payment_failed_at,
DROP COLUMN payment_error_message,
DROP COLUMN webhook_received_at,
DROP COLUMN is_expired;
```

## Quan hệ giữa các bảng

```
orders
  ├─> wallet_transactions (via order_id)
  └─> sepay_webhooks_log (via order_id)

affiliates
  ├─> wallet_transactions (via affiliate_id)
  └─> withdrawal_requests (via affiliate_id)

withdrawal_requests
  ├─> wallet_transactions (via withdrawal_id)
  └─> sepay_webhooks_log (via withdrawal_id)
```

## Bước tiếp theo

Sau khi migrations hoàn tất, bạn có thể:

1. ✅ Tạo Models (WalletTransactionModel, WithdrawalRequestModel, SepayWebhookLogModel)
2. ✅ Tạo Services (SepayService, WalletService, CommissionService)
3. ✅ Cập nhật config.php với SePay credentials
4. ✅ Tạo WebhookController để nhận webhook từ SePay

## Lưu ý quan trọng

- **Không xóa** bảng `migrations` khi rollback
- **Backup database** trước khi chạy migrations trên production
- **Test kỹ** trên local trước khi deploy
- **Kiểm tra** foreign key constraints trước khi xóa dữ liệu

## Hỗ trợ

Nếu gặp lỗi khi chạy migrations:
1. Kiểm tra database connection trong `config.php`
2. Kiểm tra quyền user database (cần quyền CREATE, ALTER, DROP)
3. Xem log lỗi trong `logs/error.log`
4. Chạy script verify để xem bảng nào còn thiếu
