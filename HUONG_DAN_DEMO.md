# HƯỚNG DẪN SỬ DỤNG HỆ THỐNG DEMO THANH TOÁN

## 📋 TỔNG QUAN

Hệ thống demo này được tạo để test chức năng thanh toán với SePay trước khi tích hợp vào trang sản phẩm thật.

### Các tính năng đã hoàn thiện:
- ✅ Trang danh sách sản phẩm demo (5 sản phẩm, giá 10,000đ)
- ✅ Trang chi tiết sản phẩm demo
- ✅ Trang checkout (nhập thông tin khách hàng)
- ✅ Trang thanh toán với QR code SePay THẬT
- ✅ Webhook tự động xác nhận thanh toán
- ✅ Trang thông báo thanh toán thành công
- ✅ Database riêng biệt (không ảnh hưởng dữ liệu thật)

---

## 🚀 BƯỚC 1: CÀI ĐẶT DATABASE

### 1.1. Chạy file SQL để tạo bảng demo

```bash
# Truy cập phpMyAdmin hoặc MySQL command line
# Import file: database/demo_schema.sql
```

Hoặc chạy trực tiếp:

```sql
-- Tạo 3 bảng:
-- 1. products_demo (5 sản phẩm demo)
-- 2. orders_demo (đơn hàng demo)
-- 3. order_items_demo (chi tiết đơn hàng)
```

### 1.2. Kiểm tra dữ liệu

```sql
SELECT * FROM products_demo;
-- Kết quả: 5 sản phẩm demo, mỗi sản phẩm giá 10,000đ
```

---

## 🔧 BƯỚC 2: CẤU HÌNH SEPAY

### 2.1. Kiểm tra file .env

File `.env` đã có sẵn thông tin SePay:

```env
SEPAY_API_KEY=0RB4ISOOP8UHMJA4EXVSESK7GZMNFDFHQM2TFNGWYTBURHADVK8FY9YV6NGOBUDC
SEPAY_API_SECRET=spsk_live_s39E5CW6hqLYq4Pb6QH3WFcTSfjVGFWQ
SEPAY_ACCOUNT_NUMBER=0389654785
```

### 2.2. Cấu hình Webhook trên SePay

1. Đăng nhập vào https://my.sepay.vn
2. Vào mục **Webhook Settings**
3. Thêm URL webhook cho demo:

```
https://your-domain.com/api.php?path=webhook/sepay_demo
```

4. Chọn sự kiện: **Payment Received**
5. Lưu cấu hình

---

## 📱 BƯỚC 3: TEST CHỨC NĂNG

### 3.1. Truy cập trang demo

```
https://your-domain.com/?page=products_demo
```

### 3.2. Flow test hoàn chỉnh

1. **Xem danh sách sản phẩm**
   - Truy cập: `?page=products_demo`
   - Hiển thị 5 sản phẩm demo
   - Mỗi sản phẩm có nút "Xem chi tiết" và "Mua ngay"

2. **Xem chi tiết sản phẩm**
   - Click vào sản phẩm
   - URL: `?page=details_demo&id=1`
   - Hiển thị thông tin chi tiết, giá, nút "Thanh toán ngay"

3. **Checkout**
   - Click "Thanh toán ngay"
   - URL: `?page=checkout_demo&product_id=1`
   - Nhập thông tin: Họ tên, Email, SĐT
   - Click "Đặt hàng ngay"

4. **Thanh toán**
   - URL: `?page=payment_demo`
   - Hiển thị mã QR SePay THẬT
   - Thông tin chuyển khoản:
     - Ngân hàng: MB Bank
     - Số TK: 0389654785
     - Số tiền: 10,000đ
     - Nội dung: DEMO[OrderId]

5. **Quét QR và chuyển khoản**
   - Mở app ngân hàng
   - Quét mã QR
   - Chuyển khoản 10,000đ
   - Nhập đúng nội dung

6. **Webhook tự động**
   - SePay gọi webhook: `api.php?path=webhook/sepay_demo`
   - Hệ thống tự động cập nhật trạng thái đơn hàng
   - Trang thanh toán tự động chuyển sang Success

7. **Thành công**
   - URL: `?page=payment_success_demo&order_number=DEMO20250218001`
   - Hiển thị thông tin đơn hàng
   - Trạng thái: Đã thanh toán

---

## 🔍 BƯỚC 4: KIỂM TRA LOG

### 4.1. Kiểm tra đơn hàng demo

```sql
SELECT * FROM orders_demo ORDER BY created_at DESC LIMIT 10;
```

### 4.2. Kiểm tra webhook log

```sql
SELECT * FROM sepay_webhook_logs 
WHERE webhook_type = 'sepay_demo' 
ORDER BY created_at DESC LIMIT 10;
```

### 4.3. Kiểm tra file log

```bash
# Xem log thanh toán
cat logs/payment.log

# Xem log webhook
cat logs/webhook.log
```

---

## 📂 CẤU TRÚC FILE DEMO

```
app/
├── controllers/
│   ├── ProductsDemoController.php      # Controller sản phẩm demo
│   ├── PaymentDemoController.php       # Controller thanh toán demo
│   └── WebhookDemoController.php       # Controller webhook demo
├── models/
│   ├── ProductsDemoModel.php           # Model sản phẩm demo
│   └── OrdersDemoModel.php             # Model đơn hàng demo
└── views/
    ├── products/
    │   ├── products_demo.php           # Danh sách sản phẩm demo
    │   └── details_demo.php            # Chi tiết sản phẩm demo
    └── payment/
        ├── checkout_demo.php           # Trang checkout demo
        ├── payment_demo.php            # Trang thanh toán demo
        └── success_demo.php            # Trang thành công demo

database/
└── demo_schema.sql                     # Schema database demo
```

---

## 🎯 SAU KHI HOÀN THIỆN TRANG THẬT

### Bước 1: Xóa các file demo

```bash
# Xóa controllers demo
rm app/controllers/ProductsDemoController.php
rm app/controllers/PaymentDemoController.php
rm app/controllers/WebhookDemoController.php

# Xóa models demo
rm app/models/ProductsDemoModel.php
rm app/models/OrdersDemoModel.php

# Xóa views demo
rm app/views/products/products_demo.php
rm app/views/products/details_demo.php
rm app/views/payment/checkout_demo.php
rm app/views/payment/payment_demo.php
rm app/views/payment/success_demo.php
```

### Bước 2: Xóa routing demo trong index.php

Xóa các case:
- `products_demo`
- `details_demo`
- `checkout_demo`
- `payment_demo`
- `payment_success_demo`
- `check_payment_demo`

### Bước 3: Xóa webhook demo trong api.php

Xóa case: `webhook/sepay_demo`

### Bước 4: Xóa database demo (tùy chọn)

```sql
DROP TABLE IF EXISTS products_demo;
DROP TABLE IF EXISTS orders_demo;
DROP TABLE IF EXISTS order_items_demo;
```

### Bước 5: Tích hợp vào trang thật

Copy logic từ các file demo sang file thật:
- `ProductsDemoController` → `ProductsController`
- `PaymentDemoController` → `PaymentController`
- `WebhookDemoController` → `WebhookController`

---

## ⚠️ LƯU Ý QUAN TRỌNG

1. **Đây là giao dịch THẬT với SePay**
   - Mỗi lần test sẽ chuyển khoản thật 10,000đ
   - Kiểm tra số dư tài khoản trước khi test

2. **Webhook URL**
   - Phải là domain thật, không dùng localhost
   - Phải có SSL (https://)
   - SePay không gọi webhook đến localhost

3. **Test trên hosting**
   - Upload toàn bộ file lên hosting
   - Chạy SQL tạo bảng demo
   - Cấu hình webhook trên SePay
   - Test thanh toán thật

4. **Không xóa file demo khi đang test**
   - Chỉ xóa sau khi trang thật hoàn thiện
   - Backup trước khi xóa

---

## 🆘 TROUBLESHOOTING

### Lỗi: Không tìm thấy sản phẩm demo

**Nguyên nhân:** Chưa chạy file SQL

**Giải pháp:**
```sql
-- Import file database/demo_schema.sql
```

### Lỗi: Webhook không được gọi

**Nguyên nhân:** 
- URL webhook sai
- Domain không có SSL
- SePay chưa cấu hình

**Giải pháp:**
1. Kiểm tra URL webhook trên SePay
2. Đảm bảo domain có https://
3. Test webhook bằng tool: https://webhook.site

### Lỗi: QR code không hiển thị

**Nguyên nhân:** API key SePay sai

**Giải pháp:**
1. Kiểm tra file .env
2. Đảm bảo SEPAY_API_KEY đúng
3. Kiểm tra SEPAY_ACCOUNT_NUMBER

---

## 📞 HỖ TRỢ

Nếu gặp vấn đề, kiểm tra:
1. File log: `logs/payment.log`, `logs/webhook.log`
2. Database: Bảng `sepay_webhook_logs`
3. Browser Console: F12 → Console tab

---

**Chúc bạn test thành công! 🎉**
