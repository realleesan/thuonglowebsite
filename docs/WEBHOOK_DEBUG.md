# 🔍 Hướng dẫn Debug Webhook Sepay

## Vấn đề: Webhook log trống rỗng sau khi chuyển khoản

Khi bạn chuyển khoản 10k vào tài khoản MB nhưng log trong database trống, có thể do các nguyên nhân sau:

## ✅ Checklist Debug

### 1. Kiểm tra Webhook URL đã được cấu hình trong Sepay chưa?

**Bước 1:** Đăng nhập vào https://my.sepay.vn

**Bước 2:** Vào phần **Cài đặt** → **Webhook**

**Bước 3:** Kiểm tra URL webhook:
```
https://yourdomain.com/api.php?action=webhook/sepay
```

**Lưu ý:**
- URL phải là HTTPS (không phải HTTP)
- URL phải public, không phải localhost
- Sepay không thể gửi webhook đến localhost

### 2. Test Webhook URL có hoạt động không?

Chạy lệnh sau để test endpoint:

```bash
curl -X GET "https://yourdomain.com/api.php?action=webhook/test"
```

Kết quả mong đợi:
```json
{
  "success": true,
  "message": "Webhook endpoint is working",
  "timestamp": "2024-01-15 10:30:00"
}
```

### 3. Kiểm tra nội dung chuyển khoản

Khi chuyển khoản, nội dung phải có format:
```
DH1
```
hoặc
```
DH + [số order ID]
```

**Ví dụ:**
- `DH1` - cho order ID = 1
- `DH123` - cho order ID = 123

### 4. Kiểm tra bảng database

Chạy query trong phpMyAdmin:

```sql
SELECT * FROM sepay_webhooks_log ORDER BY id DESC LIMIT 10;
```

Nếu bảng trống hoàn toàn → Sepay chưa gửi webhook đến server

### 5. Xem log file

Kiểm tra file log:
```bash
cat logs/webhook_debug.log
```

Nếu file không tồn tại hoặc trống → Webhook chưa nhận được request

### 6. Test với script mô phỏng

Chạy script để mô phỏng webhook từ Sepay:

```bash
php scripts/simulate_sepay_webhook.php
```

Script này sẽ:
- Gửi webhook giả lập đến server
- Ghi log vào database
- Hiển thị kết quả

Sau đó kiểm tra lại database:
```bash
php scripts/view_webhook_logs.php
```

Mở trình duyệt: `http://localhost/scripts/view_webhook_logs.php`

## 🔧 Các nguyên nhân phổ biến

### Nguyên nhân 1: Webhook URL chưa được cấu hình trong Sepay

**Giải pháp:**
1. Đăng nhập https://my.sepay.vn
2. Cấu hình webhook URL
3. Lưu lại và test

### Nguyên nhân 2: Server không public (localhost)

**Giải pháp:**
- Deploy lên server thật (VPS, shared hosting)
- Hoặc dùng ngrok để expose localhost:
  ```bash
  ngrok http 80
  ```
  Sau đó dùng URL ngrok làm webhook URL

### Nguyên nhân 3: Firewall chặn request từ Sepay

**Giải pháp:**
- Kiểm tra firewall server
- Whitelist IP của Sepay (hỏi support Sepay)

### Nguyên nhân 4: .htaccess hoặc routing bị lỗi

**Giải pháp:**
Kiểm tra file `.htaccess`:
```apache
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ api.php?action=$1 [QSA,L]
```

### Nguyên nhân 5: PHP error hoặc exception

**Giải pháp:**
Bật error logging trong `php.ini`:
```ini
error_reporting = E_ALL
display_errors = On
log_errors = On
error_log = /path/to/php-error.log
```

Kiểm tra log:
```bash
tail -f /path/to/php-error.log
```

## 🧪 Test Flow hoàn chỉnh

### Test 1: Test endpoint cơ bản
```bash
curl -X GET "http://localhost/api.php?action=webhook/test"
```

### Test 2: Test với dữ liệu giả
```bash
php scripts/simulate_sepay_webhook.php
```

### Test 3: Xem logs
```bash
php scripts/view_webhook_logs.php
```
Mở: `http://localhost/scripts/view_webhook_logs.php`

### Test 4: Test với chuyển khoản thật
1. Chuyển khoản 10,000 VND
2. Tài khoản: 0389654785 (MB Bank)
3. Nội dung: `DH1`
4. Đợi 1-2 phút
5. Kiểm tra logs

## 📊 Xem Webhook Logs

### Cách 1: Qua trình duyệt
```
http://localhost/scripts/view_webhook_logs.php
```

### Cách 2: Qua phpMyAdmin
```sql
SELECT 
    id,
    webhook_type,
    transaction_id,
    reference_code,
    amount,
    status,
    processed,
    success,
    received_at,
    content
FROM sepay_webhooks_log 
ORDER BY id DESC 
LIMIT 20;
```

### Cách 3: Qua CLI
```bash
php -r "
require 'config.php';
require 'app/models/SepayWebhookLogModel.php';
\$model = new SepayWebhookLogModel();
\$logs = \$model->getRecentWithPagination(1, 10);
print_r(\$logs);
"
```

## 🆘 Vẫn không nhận được webhook?

### Bước 1: Kiểm tra Sepay có gửi không?

Liên hệ support Sepay:
- Email: support@sepay.vn
- Hỏi: "Tôi đã cấu hình webhook nhưng không nhận được. Vui lòng kiểm tra logs từ phía Sepay xem có gửi webhook không?"

### Bước 2: Kiểm tra server logs

```bash
# Apache access log
tail -f /var/log/apache2/access.log | grep webhook

# Nginx access log
tail -f /var/log/nginx/access.log | grep webhook

# PHP error log
tail -f /var/log/php/error.log
```

### Bước 3: Dùng webhook debug tool

Tạm thời dùng webhook.site để test:
1. Vào https://webhook.site
2. Copy URL unique
3. Cấu hình URL đó trong Sepay
4. Chuyển khoản test
5. Xem request trong webhook.site
6. Copy format dữ liệu Sepay gửi
7. Update code để match format đó

## 📝 Ghi chú quan trọng

1. **Sepay chỉ gửi webhook khi:**
   - Có giao dịch thành công
   - Webhook URL đã được cấu hình
   - Server có thể truy cập được (public)

2. **Webhook không được gửi khi:**
   - Giao dịch thất bại
   - Webhook URL không hợp lệ
   - Server không truy cập được

3. **Thời gian delay:**
   - Webhook thường được gửi trong vòng 1-5 phút sau khi chuyển khoản
   - Có thể delay lâu hơn vào giờ cao điểm

4. **Retry mechanism:**
   - Sepay sẽ retry nếu webhook fail
   - Thường retry 3-5 lần
   - Khoảng cách giữa các lần retry: 1 phút, 5 phút, 15 phút

## ✅ Checklist cuối cùng

- [ ] Webhook URL đã cấu hình trong Sepay
- [ ] URL là HTTPS và public
- [ ] Test endpoint trả về success
- [ ] Database table `sepay_webhooks_log` đã tạo
- [ ] File logs có quyền write (chmod 755)
- [ ] PHP không có error
- [ ] Firewall không chặn
- [ ] Nội dung chuyển khoản đúng format (DH1)
- [ ] Đã test với script simulate
- [ ] Đã đợi đủ thời gian (1-5 phút)

## 🎯 Kết luận

Nếu sau khi làm tất cả các bước trên mà vẫn không nhận được webhook, có 2 khả năng:

1. **Sepay chưa gửi webhook** → Liên hệ support Sepay
2. **Server có vấn đề** → Kiểm tra logs, firewall, routing

Hãy bắt đầu với **Test 2** (simulate webhook) để đảm bảo code hoạt động đúng, sau đó mới test với chuyển khoản thật.
