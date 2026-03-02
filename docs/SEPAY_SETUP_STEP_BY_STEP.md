# Hướng dẫn kết nối SePay - Từng bước chi tiết

## 📋 Tổng quan

Hướng dẫn này sẽ giúp bạn kết nối SePay với hệ thống từ A-Z.

**Thời gian:** ~30 phút  
**Yêu cầu:** Đã có tài khoản SePay và credentials

---

## ✅ BƯỚC 1: Chuẩn bị file .env

### 1.1. Đổi tên file sepay.env thành .env

**Windows (CMD):**
```cmd
copy sepay.env .env
```

**Windows (PowerShell):**
```powershell
Copy-Item sepay.env .env
```

**Linux/Mac:**
```bash
cp sepay.env .env
```

### 1.2. Kiểm tra file đã tạo

```cmd
dir .env
```

Bạn sẽ thấy file `.env` trong thư mục gốc.

---

## ✅ BƯỚC 2: Cập nhật thông tin SePay

### 2.1. Mở file .env

Mở file `.env` bằng text editor (Notepad++, VS Code, etc.)

### 2.2. Kiểm tra thông tin hiện tại

File của bạn đã có:
```env
SEPAY_API_KEY=SP-TEST-NHB36596
SEPAY_API_SECRET=spsk_test_GhirZka7wTrNcoKQBvAGH4DUCCsJgkdD
SEPAY_ACCOUNT_NUMBER=0389654785
SEPAY_WEBHOOK_SECRET=your_webhook_secret_here
```

### 2.3. Cập nhật Webhook Secret

**Lấy Webhook Secret từ SePay:**

1. Đăng nhập: https://my.sepay.vn
2. Vào **Cài đặt** → **API** → **Webhook**
3. Copy **Webhook Secret**
4. Paste vào file `.env`:

```env
SEPAY_WEBHOOK_SECRET=whs_abc123xyz456def789
```

> ⚠️ **Lưu ý:** Nếu chưa có webhook secret, bạn có thể tạo mới hoặc để tạm như vậy, sẽ cập nhật sau.

### 2.4. Lưu file .env

Nhấn `Ctrl + S` để lưu file.

---

## ✅ BƯỚC 3: Kiểm tra kết nối Database

### 3.1. Chạy migrations

Mở terminal/cmd tại thư mục gốc project:

```cmd
php scripts/migrate.php
```

**Kết quả mong đợi:**
```
✓ Migration 001_create_users_table.sql - Success
✓ Migration 002_create_categories_table.sql - Success
...
✓ Migration 020_create_withdrawal_requests_table.sql - Success

All migrations completed successfully!
```

### 3.2. Verify migrations

```cmd
php scripts/verify_payment_migrations.php
```

**Kết quả mong đợi:**
```
✓ Table 'wallet_transactions' exists
✓ Table 'sepay_webhooks_log' exists
✓ Table 'withdrawal_requests' exists
✓ All required columns present

Payment system migrations verified successfully!
```

---

## ✅ BƯỚC 4: Test cấu hình SePay

### 4.1. Tạo script test (nếu chưa có)

Tạo file `scripts/test_sepay_connection.php`:

```php
<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../app/services/SepayService.php';

echo "=== Testing SePay Connection ===\n\n";

try {
    $sepayService = new SepayService();
    
    // Test 1: Check config
    echo "1. Checking configuration...\n";
    $config = require __DIR__ . '/../config.php';
    
    if (empty($config['sepay']['api_key']) || $config['sepay']['api_key'] === 'YOUR_SEPAY_API_KEY_HERE') {
        echo "   ❌ API Key not configured\n";
        exit(1);
    }
    echo "   ✓ API Key: " . substr($config['sepay']['api_key'], 0, 10) . "...\n";
    
    if (empty($config['sepay']['api_secret']) || $config['sepay']['api_secret'] === 'YOUR_SEPAY_API_SECRET_HERE') {
        echo "   ❌ API Secret not configured\n";
        exit(1);
    }
    echo "   ✓ API Secret: " . substr($config['sepay']['api_secret'], 0, 10) . "...\n";
    
    if (empty($config['sepay']['account_number']) || $config['sepay']['account_number'] === 'YOUR_ACCOUNT_NUMBER_HERE') {
        echo "   ❌ Account Number not configured\n";
        exit(1);
    }
    echo "   ✓ Account Number: " . $config['sepay']['account_number'] . "\n";
    
    echo "\n2. Testing SePay API connection...\n";
    
    // Test 2: Get account info
    $accountInfo = $sepayService->getAccountInfo();
    
    if ($accountInfo['success']) {
        echo "   ✓ Connection successful!\n";
        echo "   ✓ Account: " . ($accountInfo['data']['account_name'] ?? 'N/A') . "\n";
        echo "   ✓ Balance: " . number_format($accountInfo['data']['balance'] ?? 0, 0, ',', '.') . " VND\n";
    } else {
        echo "   ❌ Connection failed: " . ($accountInfo['message'] ?? 'Unknown error') . "\n";
        exit(1);
    }
    
    echo "\n✅ All tests passed!\n";
    echo "\nNext steps:\n";
    echo "1. Configure webhook URL on SePay dashboard\n";
    echo "2. Test webhook endpoint\n";
    echo "3. Create test order\n";
    
} catch (Exception $e) {
    echo "\n❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
```

### 4.2. Chạy test

```cmd
php scripts/test_sepay_connection.php
```

**Kết quả mong đợi:**
```
=== Testing SePay Connection ===

1. Checking configuration...
   ✓ API Key: SP-TEST-NH...
   ✓ API Secret: spsk_test_...
   ✓ Account Number: 0389654785

2. Testing SePay API connection...
   ✓ Connection successful!
   ✓ Account: HOANG NAM
   ✓ Balance: 1,234,567 VND

✅ All tests passed!
```

> ⚠️ **Nếu gặp lỗi:** Kiểm tra lại API Key và API Secret trong file `.env`

---

## ✅ BƯỚC 5: Cấu hình Webhook URL

### 5.1. Xác định Webhook URL của bạn

**Nếu đang test local (localhost):**

Bạn cần expose localhost ra internet. Có 2 cách:

#### Cách 1: Sử dụng ngrok (Khuyên dùng)

1. Download ngrok: https://ngrok.com/download
2. Giải nén và chạy:
   ```cmd
   ngrok http 80
   ```
3. Copy URL ngrok (VD: `https://abc123.ngrok.io`)
4. Webhook URL sẽ là:
   ```
   https://abc12![1771343698120](image/SEPAY_SETUP_STEP_BY_STEP/1771343698120.png)3.ngrok.io/api.php?path=webhook/sepay
   ```

#### Cách 2: Deploy lên hosting test

1. Upload code lên hosting
2. Webhook URL sẽ là:
   ```
   https://yourdomain.com/api.php?path=webhook/sepay
   ```

**Nếu đang ở production:**
```
https://thuonglo.com/api.php?path=webhook/sepay
```

### 5.2. Đăng ký Webhook trên SePay

1. Đăng nhập: https://my.sepay.vn
2. Vào **Cài đặt** → **Webhook**
3. Click **Thêm Webhook**
4. Điền thông tin:
   - **URL:** `https://your-url/api.php?path=webhook/sepay`
   - **Events:** Chọn tất cả:
     - ✅ Transaction Success
     - ✅ Transaction Failed
     - ✅ Payment In
     - ✅ Payment Out
5. Click **Lưu**

### 5.3. Test Webhook Endpoint

**Test từ browser:**
```
https://your-url/api.php?path=webhook/test
```

**Kết quả mong đợi:**
```json
{
  "success": true,
  "message": "Webhook endpoint is working",
  "timestamp": "2026-02-17 10:30:00"
}
```

**Test bằng curl:**
```bash
curl https://your-url/api.php?path=webhook/test
```

---

## ✅ BƯỚC 6: Test thanh toán thực tế

### 6.1. Tạo đơn hàng test

1. Vào trang web của bạn
2. Thêm sản phẩm vào giỏ hàng
3. Tiến hành thanh toán
4. Chọn phương thức: **SePay**

### 6.2. Hệ thống sẽ tạo QR code

- Mã QR chứa thông tin chuyển khoản
- Nội dung chuyển khoản: `DH{OrderId}` (VD: `DH123`)
- Số tiền: Tổng tiền đơn hàng

### 6.3. Thực hiện chuyển khoản

**Option 1: Chuyển khoản thật (Test mode)**
- Mở app ngân hàng
- Quét QR code
- Chuyển khoản

**Option 2: Simulate webhook (Development)**
```bash
curl -X POST https://your-url/api.php?path=webhook/sepay \
  -H "Content-Type: application/json" \
  -d '{
    "id": "TEST123",
    "transaction_id": "TEST123",
    "amount_in": 500000,
    "content": "DH1",
    "account_number": "0389654785",
    "status": "success"
  }'
```

### 6.4. Kiểm tra kết quả

**Trong database:**
```sql
-- Check webhook log
SELECT * FROM sepay_webhooks_log 
ORDER BY received_at DESC LIMIT 5;

-- Check order payment status
SELECT id, order_number, payment_status, status 
FROM orders 
WHERE id = 1;

-- Check wallet transaction (nếu có affiliate)
SELECT * FROM wallet_transactions 
ORDER BY created_at DESC LIMIT 5;
```

**Kết quả mong đợi:**
- ✅ Webhook được log vào `sepay_webhooks_log`
- ✅ Order `payment_status` = 'paid'
- ✅ Order `status` = 'processing'
- ✅ Nếu có affiliate: Commission được cộng vào wallet

---

## ✅ BƯỚC 7: Test rút tiền (Withdrawal)

### 7.1. Tạo yêu cầu rút tiền

1. Đăng nhập với tài khoản affiliate
2. Vào **Ví** → **Rút tiền**
3. Nhập số tiền và thông tin ngân hàng
4. Submit

### 7.2. Admin xử lý

1. Đăng nhập admin
2. Vào **Quản lý rút tiền**
3. Xem yêu cầu rút tiền mới
4. Click **Xử lý** → Hệ thống tạo QR SePay
5. Quét QR và chuyển tiền

### 7.3. Webhook xử lý tự động

Khi chuyển tiền thành công:
- ✅ SePay gửi webhook về hệ thống
- ✅ Hệ thống cập nhật withdrawal status = 'completed'
- ✅ Tiền được trừ khỏi `pending_withdrawal`
- ✅ Cộng vào `total_withdrawn`
- ✅ Gửi email thông báo cho affiliate

---

## 🔍 Troubleshooting

### Lỗi 1: "API Key not configured"

**Nguyên nhân:** File `.env` chưa được load

**Giải pháp:**
1. Kiểm tra file `.env` có tồn tại không
2. Kiểm tra file `core/env.php` có load `.env` không
3. Restart web server

### Lỗi 2: "Webhook signature verification failed"

**Nguyên nhân:** Webhook secret không khớp

**Giải pháp:**
1. Kiểm tra `SEPAY_WEBHOOK_SECRET` trong `.env`
2. Lấy lại secret từ SePay dashboard
3. Cập nhật và lưu file `.env`

### Lỗi 3: "Connection timeout"

**Nguyên nhân:** Không kết nối được SePay API

**Giải pháp:**
1. Kiểm tra internet connection
2. Kiểm tra firewall/antivirus
3. Test bằng curl:
   ```bash
   curl https://my.sepay.vn/userapi/transactions/list
   ```

### Lỗi 4: "Webhook not received"

**Nguyên nhân:** SePay không gọi được webhook URL

**Giải pháp:**
1. Kiểm tra webhook URL có accessible từ internet không
2. Test endpoint: `https://your-url/api.php?path=webhook/test`
3. Kiểm tra SSL certificate (phải valid)
4. Xem log trên SePay dashboard

### Lỗi 5: "Order not found in webhook"

**Nguyên nhân:** Reference code không đúng format

**Giải pháp:**
1. Kiểm tra nội dung chuyển khoản phải là `DH{OrderId}`
2. Xem log webhook: `SELECT * FROM sepay_webhooks_log`
3. Kiểm tra `reference_code` column

---

## 📊 Monitoring

### Check Webhook Logs

```sql
-- Recent webhooks
SELECT 
    id,
    webhook_type,
    transaction_id,
    reference_code,
    amount,
    processed,
    success,
    received_at
FROM sepay_webhooks_log 
ORDER BY received_at DESC 
LIMIT 20;
```

### Check Unprocessed Webhooks

```sql
SELECT * FROM sepay_webhooks_log 
WHERE processed = 0 
ORDER BY received_at ASC;
```

### Check Failed Webhooks

```sql
SELECT 
    id,
    webhook_type,
    reference_code,
    processing_error,
    received_at
FROM sepay_webhooks_log 
WHERE processed = 1 AND success = 0 
ORDER BY received_at DESC;
```

### Check Wallet Transactions

```sql
SELECT 
    wt.id,
    wt.type,
    wt.amount,
    wt.balance_after,
    a.referral_code,
    u.name as affiliate_name,
    wt.created_at
FROM wallet_transactions wt
LEFT JOIN affiliates a ON wt.affiliate_id = a.id
LEFT JOIN users u ON a.user_id = u.id
ORDER BY wt.created_at DESC
LIMIT 20;
```

---

## 🎯 Checklist hoàn thành

- [ ] File `.env` đã được tạo từ `sepay.env`
- [ ] Đã cập nhật `SEPAY_WEBHOOK_SECRET`
- [ ] Migrations đã chạy thành công
- [ ] Test SePay connection thành công
- [ ] Webhook URL đã đăng ký trên SePay
- [ ] Test webhook endpoint thành công
- [ ] Đã test thanh toán thực tế
- [ ] Webhook nhận và xử lý thành công
- [ ] Order payment status được cập nhật
- [ ] Commission được cộng vào wallet (nếu có)
- [ ] Test rút tiền thành công

---

## 📞 Support

Nếu gặp vấn đề:

1. **Check logs:**
   - `logs/error.log`
   - `logs/payment.log`
   - `logs/webhook.log`

2. **Check database:**
   - `sepay_webhooks_log` table
   - `orders` table
   - `wallet_transactions` table

3. **SePay Documentation:**
   - https://docs.sepay.vn

4. **Contact:**
   - SePay Support: support@sepay.vn
   - System Admin: admin@thuonglo.com

---

## 🚀 Next Steps

Sau khi setup xong:

1. ✅ Test với nhiều scenarios khác nhau
2. ✅ Monitor webhook logs trong vài ngày
3. ✅ Setup email notifications
4. ✅ Configure withdrawal limits
5. ✅ Train admin team về quy trình xử lý
6. ✅ Prepare for production deployment

**Chúc mừng! Bạn đã hoàn thành setup SePay! 🎉**
