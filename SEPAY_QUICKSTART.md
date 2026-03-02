# 🚀 SePay Quick Start - 5 phút setup

## Bước 1: Tạo file .env (30 giây)

```cmd
copy sepay.env .env
```

## Bước 2: Cập nhật Webhook Secret (1 phút)

1. Vào https://my.sepay.vn → **Cài đặt** → **Webhook**
2. Copy **Webhook Secret**
3. Mở file `.env` và paste vào:
   ```env
   SEPAY_WEBHOOK_SECRET=whs_your_secret_here
   ```
4. Lưu file

## Bước 3: Chạy migrations (1 phút)

```cmd
php scripts/migrate.php
```

## Bước 4: Test kết nối (30 giây)

```cmd
php scripts/test_sepay_connection.php
```

**Kết quả mong đợi:**
```
✅ All critical tests passed!
```

## Bước 5: Đăng ký Webhook URL (2 phút)

### Nếu đang test local:

1. Download ngrok: https://ngrok.com/download
2. Chạy: `ngrok http 80`
3. Copy URL ngrok (VD: `https://abc123.ngrok.io`)

### Đăng ký trên SePay:

1. Vào https://my.sepay.vn → **Webhook**
2. Thêm URL:
   ```
   https://your-url/api.php?path=webhook/sepay
   ```
3. Chọn tất cả events
4. Lưu

## Bước 6: Test webhook (30 giây)

Mở browser:
```
https://your-url/api.php?path=webhook/test
```

Kết quả:
```json
{"success":true,"message":"Webhook endpoint is working"}
```

---

## ✅ Xong! Bây giờ bạn có thể:

- ✅ Tạo đơn hàng và thanh toán qua SePay
- ✅ Nhận webhook tự động khi có giao dịch
- ✅ Xử lý commission cho affiliate
- ✅ Rút tiền về tài khoản ngân hàng

---

## 📚 Tài liệu chi tiết

- **Setup đầy đủ:** `docs/SEPAY_SETUP_STEP_BY_STEP.md`
- **Webhook guide:** `docs/WEBHOOK_SETUP.md`
- **Config guide:** `docs/CONFIG_SETUP_GUIDE.md`

## 🆘 Gặp vấn đề?

1. Check logs: `logs/error.log`, `logs/webhook.log`
2. Xem troubleshooting: `docs/SEPAY_SETUP_STEP_BY_STEP.md`
3. Test lại: `php scripts/test_sepay_connection.php`

---

**Credentials hiện tại của bạn:**
- ✅ API Key: SP-TEST-NHB36596
- ✅ Account: 0389654785
- ⚠️ Webhook Secret: Cần cập nhật

**Chúc mừng! 🎉**
