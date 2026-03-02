# Configuration Setup Guide - Payment System

## Tổng quan

Hướng dẫn cấu hình hệ thống thanh toán SePay và các tính năng liên quan.

## Bước 1: Tạo file .env

```bash
# Copy file .env.example thành .env
copy .env.example .env
```

## Bước 2: Cấu hình SePay

### 2.1. Đăng ký tài khoản SePay

1. Truy cập: https://my.sepay.vn
2. Đăng ký tài khoản
3. Xác thực tài khoản ngân hàng
4. Lấy API credentials

### 2.2. Lấy API Credentials

Sau khi đăng nhập SePay:

1. Vào **Cài đặt** → **API**
2. Copy các thông tin sau:
   - **API Key**: Dùng để xác thực API requests
   - **API Secret**: Dùng để mã hóa requests
   - **Account Number**: Số tài khoản ngân hàng nhận tiền
   - **Webhook Secret**: Dùng để verify webhook từ SePay

### 2.3. Cập nhật .env

Mở file `.env` và cập nhật:

```env
SEPAY_API_KEY=your_actual_api_key_here
SEPAY_API_SECRET=your_actual_api_secret_here
SEPAY_ACCOUNT_NUMBER=0123456789
SEPAY_WEBHOOK_SECRET=your_webhook_secret_here
```

## Bước 3: Cấu hình Email (Optional)

### 3.1. Sử dụng Gmail

1. Bật **2-Step Verification** cho Gmail
2. Tạo **App Password**:
   - Truy cập: https://myaccount.google.com/apppasswords
   - Chọn app: Mail
   - Chọn device: Other (Custom name)
   - Copy password được tạo

### 3.2. Cập nhật .env

```env
SMTP_HOST=smtp.gmail.com
SMTP_PORT=587
SMTP_USERNAME=your-email@gmail.com
SMTP_PASSWORD=your-16-character-app-password
MAIL_FROM_EMAIL=noreply@thuonglo.com
MAIL_FROM_NAME=Thuong Lo
```

### 3.3. Sử dụng SMTP khác

Nếu dùng SMTP provider khác (SendGrid, Mailgun, etc):

```env
SMTP_HOST=smtp.sendgrid.net
SMTP_PORT=587
SMTP_USERNAME=apikey
SMTP_PASSWORD=your_sendgrid_api_key
```

## Bước 4: Kiểm tra cấu hình

```bash
# Chạy script test config
php scripts/test_config.php
```

Output mong đợi:
```
✓ SePay API Key: Set
✓ SePay API Secret: Set
✓ Account Number: Set
✓ Webhook Secret: Set
✓ Email SMTP: Set
```

## Bước 5: Cấu hình Webhook URL

### 5.1. Xác định Webhook URL

**Local (Development):**
```
http://localhost/webhook/sepay/payment
http://localhost/webhook/sepay/payout
```

**Production:**
```
https://yourdomain.com/webhook/sepay/payment
https://yourdomain.com/webhook/sepay/payout
```

### 5.2. Đăng ký Webhook trên SePay

1. Đăng nhập SePay
2. Vào **Cài đặt** → **Webhook**
3. Thêm 2 webhook URLs:
   - **Payment In** (Thu tiền): `/webhook/sepay/payment`
   - **Payment Out** (Chi tiền): `/webhook/sepay/payout`
4. Chọn events:
   - Transaction Success
   - Transaction Failed
5. Save

### 5.3. Test Webhook (Local)

Nếu develop local, cần expose localhost ra internet:

**Option 1: Sử dụng ngrok**
```bash
ngrok http 80
```

Copy URL ngrok (VD: `https://abc123.ngrok.io`) và dùng làm webhook URL.

**Option 2: Deploy lên hosting test**

Deploy code lên hosting test và dùng URL hosting.

## Cấu hình nâng cao

### Commission Settings

Trong `config.php`:

```php
'commission' => [
    'default_rate' => 10.00,        // 10% hoa hồng mặc định
    'min_order_for_commission' => 0, // Đơn tối thiểu để có hoa hồng
    'auto_credit' => true,           // Tự động cộng hoa hồng
],
```

### Withdrawal Settings

```php
'withdrawal' => [
    'min_amount' => 5000,            // Rút tối thiểu 5,000 VND
    'max_amount' => 50000000,        // Rút tối đa 50,000,000 VND
    'fee' => 0,                      // Phí rút = 0 (miễn phí)
    'require_bank_verification' => true, // Yêu cầu OTP khi đổi bank
    'otp_expiry' => 300,             // OTP hết hạn sau 5 phút
],
```

### Payment Timeout

```php
'sepay' => [
    'payment_timeout' => 120,  // 120 giây (2 phút)
    'qr_timeout' => 120,       // QR hết hạn sau 120 giây
],
```

## Troubleshooting

### Lỗi: "SePay API Key not configured"

**Nguyên nhân:** File `.env` chưa được tạo hoặc chưa có API key

**Giải pháp:**
1. Copy `.env.example` thành `.env`
2. Cập nhật `SEPAY_API_KEY` trong `.env`
3. Chạy lại `php scripts/test_config.php`

### Lỗi: "Email SMTP not configured"

**Nguyên nhân:** SMTP credentials chưa được cấu hình

**Giải pháp:**
1. Tạo App Password cho Gmail
2. Cập nhật `SMTP_USERNAME` và `SMTP_PASSWORD` trong `.env`
3. Test email: `php scripts/test_email.php`

### Lỗi: "Webhook signature verification failed"

**Nguyên nhân:** Webhook secret không khớp

**Giải pháp:**
1. Kiểm tra `SEPAY_WEBHOOK_SECRET` trong `.env`
2. Đảm bảo khớp với secret trên SePay dashboard
3. Xem log: `logs/webhook.log`

## Security Best Practices

### 1. Bảo vệ .env file

```bash
# Thêm vào .gitignore
echo ".env" >> .gitignore
```

### 2. Không commit credentials

- ❌ KHÔNG commit `.env` vào git
- ✅ Chỉ commit `.env.example` (không có credentials thật)

### 3. Sử dụng HTTPS

- Production PHẢI dùng HTTPS
- Webhook URLs phải là HTTPS

### 4. Rotate credentials định kỳ

- Đổi API keys mỗi 3-6 tháng
- Đổi webhook secret nếu bị lộ

## Testing

### Test Config
```bash
php scripts/test_config.php
```

### Test Database Connection
```bash
php scripts/check_database_connection.php
```

### Test SePay Connection (sau khi có SepayService)
```bash
php scripts/test_sepay_connection.php
```

### Test Email (sau khi có EmailService)
```bash
php scripts/test_email.php
```

## Next Steps

Sau khi config xong:

1. ✅ Chạy migrations: `php scripts/migrate.php`
2. ✅ Verify migrations: `php scripts/verify_payment_migrations.php`
3. ✅ Tạo Services: SepayService, WalletService, EmailService
4. ✅ Tạo Models: WalletTransactionModel, WithdrawalRequestModel
5. ✅ Tạo Controllers: WebhookController, PaymentController

## Support

Nếu gặp vấn đề:
1. Check logs: `logs/error.log`, `logs/payment.log`
2. Run test scripts
3. Xem SePay documentation: https://docs.sepay.vn
