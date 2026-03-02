# 🛠️ Scripts Webhook Testing & Debugging

Các scripts hỗ trợ test và debug webhook Sepay.

## 📋 Danh sách Scripts

### 1. ✅ `check_webhook_status.php`
**Mục đích:** Kiểm tra tổng quan trạng thái webhook

**Chạy:**
```bash
php scripts/check_webhook_status.php
```

**Kiểm tra:**
- ✅ Database connection
- ✅ Bảng sepay_webhooks_log
- ✅ Thống kê webhooks
- ✅ 5 webhooks gần nhất
- ✅ File logs
- ✅ Webhook endpoint
- ✅ Cấu hình Sepay

**Khi nào dùng:** Khi bạn muốn kiểm tra nhanh hệ thống có hoạt động không.

---

### 2. 🎭 `simulate_sepay_webhook.php`
**Mục đích:** Mô phỏng webhook từ Sepay (test local)

**Chạy:**
```bash
php scripts/simulate_sepay_webhook.php
```

**Làm gì:**
- Gửi webhook giả lập đến `http://localhost/api.php?action=webhook/sepay`
- Dữ liệu giống format Sepay thật
- Ghi log vào database
- Hiển thị response

**Khi nào dùng:** 
- Test webhook handler trên localhost
- Không cần chuyển khoản thật
- Debug logic xử lý webhook

---

### 3. 📊 `view_webhook_logs.php`
**Mục đích:** Xem webhook logs qua giao diện web

**Chạy:**
```bash
# Mở trình duyệt
http://localhost/scripts/view_webhook_logs.php
```

**Hiển thị:**
- 📊 Thống kê tổng quan
- 📝 Danh sách 20 webhooks gần nhất
- 🎨 Giao diện đẹp, dễ đọc
- 🔄 Nút refresh

**Khi nào dùng:** Khi bạn muốn xem logs một cách trực quan.

---

### 4. 🔍 `test_webhook_receive.php`
**Mục đích:** Ghi log chi tiết mọi request đến

**Chạy:**
```bash
# Truy cập URL này
http://localhost/scripts/test_webhook_receive.php
```

**