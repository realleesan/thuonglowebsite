![1771345604982](image/WEBHOOK_TROUBLESHOOTING_VI/1771345604982.png)# 🔍 Khắc phục sự cố: Webhook log trống rỗng

## ❓ Vấn đề của bạn

Bạn đã chuyển khoản 10,000 VND vào tài khoản MB Bank (0389654785), nhưng khi kiểm tra log trong phpMyAdmin, bảng `sepay_webhooks_log` trống rỗng.

## 🎯 Nguyên nhân chính

Có **3 nguyên nhân** có thể xảy ra:

### 1. ❌ Webhook URL chưa được cấu hình trong Sepay
**Đây là nguyên nhân phổ biến nhất!**

Sepay không tự động biết server của bạn ở đâu. Bạn phải cấu hình webhook URL trong tài khoản Sepay.

**Cách kiểm tra:**
1. Đăng nhập vào https://my.sepay.vn
2. Vào **Cài đặt** → **Webhook** (hoặc **API Settings**)
3. Kiểm tra xem có URL webhook không?

**Cách khắc phục:**
- Nếu bạn đang test trên **localhost**, Sepay KHÔNG THỂ gửi webhook đến localhost
- Bạn cần:
  - **Option 1:** Deploy lên server thật (VPS, shared hosting)
  - **Option 2:** Dùng **ngrok** để expose localhost ra internet

### 2. 🌐 Server đang chạy localhost (không public)

Sepay chỉ có thể gửi webhook đến URL public (có thể truy cập từ internet).

**Kiểm tra:**
```bash
# Bạn đang chạy trên localhost?
http://localhost/api.php
```

**Khắc phục với ngrok:**
```bash
# 1. Tải ngrok: https://ngrok.com/download
# 2. Chạy ngrok
ngrok http 80

# 3. Copy URL ngrok (ví dụ: https://abc123.ngrok.io)
# 4. Cấu hình trong Sepay:
https://abc123.ngrok.io/api.php?action=webhook/sepay
```

### 3. 📝 Nội dung chuyển khoản không đúng format

Sepay cần nội dung chuyển khoản để xác định đơn hàng.

**Format đúng:**
```
DH1
```
hoặc
```
DH[số order ID]
```

**Ví dụ:**
- `DH1` → Order ID = 1
- `DH123` → Order ID = 123

## ✅ Giải pháp từng bước

### Bước 1: Kiểm tra database đang chạy

```bash
# Mở XAMPP Control Panel
# Bấm "Start" cho MySQL/MariaDB
```

Hoặc kiểm tra bằng lệnh:
```bash
php scripts/check_webhook_status.php
```

### Bước 2: Test webhook endpoint local

```bash
# Test endpoint có hoạt động không
php scripts/simulate_sepay_webhook.php
```

Script này sẽ:
- Gửi webhook giả lập đến server local
- Ghi log vào database
- Hiển thị kết quả

### Bước 3: Kiểm tra logs

```bash
# Xem webhook logs qua trình duyệt
http://localhost/scripts/view_webhook_logs.php
```

Hoặc kiểm tra trong phpMyAdmin:
```sql
SELECT * FROM sepay_webhooks_log ORDER BY id DESC LIMIT 10;
```

### Bước 4: Deploy lên server thật (nếu cần)

Nếu bạn muốn test với chuyển khoản thật, bạn cần:

1. **Deploy lên hosting/VPS**
2. **Cấu hình webhook URL trong Sepay:**
   ```
   https://yourdomain.com/api.php?action=webhook/sepay
   ```
3. **Chuyển khoản test:**
   - Số tài khoản: 0389654785
   - Ngân hàng: MB Bank
   - Số tiền: 10,000 VND
   - Nội dung: `DH1`
4. **Đợi 1-5 phút** (Sepay cần thời gian xử lý)
5. **Kiểm tra logs**

## 🧪 Test nhanh (không cần chuyển khoản thật)

### Test 1: Kiểm tra hệ thống
```bash
php scripts/check_webhook_status.php
```

### Test 2: Mô phỏng webhook
```bash
php scripts/simulate_sepay_webhook.php
```

### Test 3: Xem logs
```bash
# Mở trình duyệt
http://localhost/scripts/view_webhook_logs.php
```

## 📊 Hiểu về flow webhook

```
[Khách hàng] 
    ↓ Chuyển khoản 10k + nội dung "DH1"
[MB Bank]
    ↓ Ghi nhận giao dịch
[Sepay]
    ↓ Nhận thông báo từ bank
    ↓ Gửi webhook đến server của bạn
[Server của bạn]
    ↓ Nhận webhook
    ↓ Ghi log vào database (sepay_webhooks_log)
    ↓ Xử lý đơn hàng (cập nhật trạng thái)
    ↓ Trả về response cho Sepay
```

**Vấn đề của bạn:** Bước "Sepay gửi webhook đến server" không xảy ra vì:
- Sepay không biết URL server của bạn (chưa cấu hình)
- Hoặc server của bạn là localhost (không public)

## 🔧 Các công cụ debug đã tạo

### 1. `scripts/check_webhook_status.php`
Kiểm tra tổng quan hệ thống:
- Database connection
- Webhook logs
- File logs
- Endpoint status
- Cấu hình Sepay

```bash
php scripts/check_webhook_status.php
```

### 2. `scripts/simulate_sepay_webhook.php`
Mô phỏng webhook từ Sepay (test local):
```bash
php scripts/simulate_sepay_webhook.php
```

### 3. `scripts/view_webhook_logs.php`
Xem logs qua trình duyệt:
```
http://localhost/scripts/view_webhook_logs.php
```

### 4. `scripts/test_webhook_receive.php`
Ghi log chi tiết mọi request đến:
```
http://localhost/scripts/test_webhook_receive.php
```

## 📝 Checklist debug

- [ ] **MySQL đang chạy** (XAMPP Control Panel)
- [ ] **Bảng `sepay_webhooks_log` đã tạo** (chạy migration)
- [ ] **Test local thành công** (`php scripts/simulate_sepay_webhook.php`)
- [ ] **Webhook endpoint hoạt động** (`http://localhost/api.php?action=webhook/test`)
- [ ] **Nếu test với chuyển khoản thật:**
  - [ ] Server đã deploy (không phải localhost)
  - [ ] Webhook URL đã cấu hình trong Sepay
  - [ ] URL là HTTPS
  - [ ] Nội dung chuyển khoản đúng format (`DH1`)
  - [ ] Đã đợi 1-5 phút

## 🎯 Kết luận

**Nếu bạn đang test trên localhost:**
→ Webhook từ Sepay KHÔNG THỂ đến được
→ Dùng `scripts/simulate_sepay_webhook.php` để test

**Nếu bạn muốn test với chuyển khoản thật:**
→ Phải deploy lên server public
→ Cấu hình webhook URL trong Sepay
→ Đợi 1-5 phút sau khi chuyển khoản

**Để test ngay bây giờ (không cần chuyển khoản):**
```bash
# 1. Khởi động MySQL trong XAMPP
# 2. Chạy lệnh này
php scripts/simulate_sepay_webhook.php

# 3. Xem kết quả
http://localhost/scripts/view_webhook_logs.php
```

## 📞 Cần hỗ trợ thêm?

Nếu sau khi làm theo hướng dẫn mà vẫn không được:

1. **Chạy:** `php scripts/check_webhook_status.php`
2. **Chụp màn hình** kết quả
3. **Kiểm tra** phpMyAdmin xem bảng `sepay_webhooks_log` có dữ liệu không
4. **Liên hệ support Sepay** để kiểm tra logs từ phía họ

---

**Tóm tắt:** Webhook log trống vì Sepay chưa gửi webhook đến server của bạn. Nguyên nhân chính là chưa cấu hình webhook URL trong Sepay, hoặc server đang chạy localhost (không public).
