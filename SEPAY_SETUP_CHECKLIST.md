# ✅ SePay Setup Checklist

Đánh dấu ✅ khi hoàn thành mỗi bước.

## 📋 Phase 1: Cấu hình cơ bản

- [X] **1.1** Đã tạo file `.env` từ `sepay.env`
- [ ] **1.2** Đã cập nhật `SEPAY_WEBHOOK_SECRET` trong `.env`
- [ ] **1.3** File `.env` đã được lưu và không commit vào git

## 📋 Phase 2: Database Setup

- [ ] **2.1** Chạy migrations: `php scripts/migrate.php`
- [ ] **2.2** Verify migrations: `php scripts/verify_payment_migrations.php`
- [ ] **2.3** Kiểm tra các bảng đã tạo:
  - [ ] `wallet_transactions`
  - [ ] `withdrawal_requests`
  - [ ] `sepay_webhooks_log`
  - [ ] `affiliates` (có thêm wallet fields)

## 📋 Phase 3: Test SePay Connection

- [ ] **3.1** Chạy test: `php scripts/test_sepay_connection.php`
- [ ] **3.2** Kết quả: "✅ All critical tests passed!"
- [ ] **3.3** Xác nhận thông tin account hiển thị đúng
- [ ] **3.4** Xác nhận balance hiển thị (nếu có)

## 📋 Phase 4: Webhook Setup

### Local Development (nếu test local)

- [ ] **4.1** Download và cài đặt ngrok
- [ ] **4.2** Chạy: `ngrok http 80`
- [ ] **4.3** Copy ngrok URL (VD: `https://abc123.ngrok.io`)

### Webhook Registration

- [ ] **4.4** Đăng nhập SePay: https://my.sepay.vn
- [ ] **4.5** Vào **Cài đặt** → **Webhook**
- [ ] **4.6** Thêm webhook URL: `https://your-url/api.php?path=webhook/sepay`
- [ ] **4.7** Chọn events:
  - [ ] Transaction Success
  - [ ] Transaction Failed
  - [ ] Payment In
  - [ ] Payment Out
- [ ] **4.8** Lưu cấu hình

### Webhook Testing

- [ ] **4.9** Test endpoint: `https://your-url/api.php?path=webhook/test`
- [ ] **4.10** Kết quả: `{"success":true,"message":"Webhook endpoint is working"}`

## 📋 Phase 5: Payment Flow Testing

### Tạo đơn hàng test

- [ ] **5.1** Tạo đơn hàng mới trên website
- [ ] **5.2** Chọn phương thức thanh toán: SePay
- [ ] **5.3** QR code được hiển thị
- [ ] **5.4** Thông tin chuyển khoản đúng:
  - [ ] Số tài khoản: 0389654785
  - [ ] Nội dung: DH{OrderId}
  - [ ] Số tiền: Đúng với tổng đơn hàng

### Thực hiện thanh toán

- [ ] **5.5** Quét QR và chuyển khoản (hoặc simulate webhook)
- [ ] **5.6** Webhook được nhận và log vào database
- [ ] **5.7** Order `payment_status` = 'paid'
- [ ] **5.8** Order `status` = 'processing'

### Kiểm tra commission (nếu có affiliate)

- [ ] **5.9** Commission được tính đúng
- [ ] **5.10** Commission được cộng vào wallet
- [ ] **5.11** Transaction được log vào `wallet_transactions`
- [ ] **5.12** Email thông báo được gửi (nếu có)

## 📋 Phase 6: Withdrawal Flow Testing

### Tạo yêu cầu rút tiền

- [ ] **6.1** Đăng nhập với tài khoản affiliate
- [ ] **6.2** Vào trang Rút tiền
- [ ] **6.3** Nhập thông tin ngân hàng
- [ ] **6.4** Nhập số tiền rút
- [ ] **6.5** Submit yêu cầu thành công
- [ ] **6.6** Withdrawal request được tạo trong database
- [ ] **6.7** Balance được freeze (chuyển sang `pending_withdrawal`)

### Admin xử lý

- [ ] **6.8** Đăng nhập admin
- [ ] **6.9** Xem danh sách yêu cầu rút tiền
- [ ] **6.10** Click xử lý yêu cầu
- [ ] **6.11** QR code SePay được tạo
- [ ] **6.12** Quét QR và chuyển tiền

### Webhook xử lý

- [ ] **6.13** Webhook payment_out được nhận
- [ ] **6.14** Withdrawal status = 'completed'
- [ ] **6.15** Tiền được trừ khỏi `pending_withdrawal`
- [ ] **6.16** Cộng vào `total_withdrawn`
- [ ] **6.17** Email thông báo được gửi

## 📋 Phase 7: Monitoring & Logs

### Database Checks

- [ ] **7.1** Check webhook logs:
  ```sql
  SELECT * FROM sepay_webhooks_log ORDER BY received_at DESC LIMIT 10;
  ```
- [ ] **7.2** Check wallet transactions:
  ```sql
  SELECT * FROM wallet_transactions ORDER BY created_at DESC LIMIT 10;
  ```
- [ ] **7.3** Check withdrawal requests:
  ```sql
  SELECT * FROM withdrawal_requests ORDER BY requested_at DESC LIMIT 10;
  ```

### Log Files

- [ ] **7.4** Check `logs/error.log` - không có lỗi nghiêm trọng
- [ ] **7.5** Check `logs/payment.log` - payments được log
- [ ] **7.6** Check `logs/webhook.log` - webhooks được log

## 📋 Phase 8: Email Notifications (Optional)

- [ ] **8.1** Cấu hình SMTP trong `.env`
- [ ] **8.2** Test email: `php scripts/test_email.php`
- [ ] **8.3** Email order confirmation hoạt động
- [ ] **8.4** Email payment success hoạt động
- [ ] **8.5** Email commission earned hoạt động
- [ ] **8.6** Email withdrawal completed hoạt động

## 📋 Phase 9: Security & Production Ready

- [ ] **9.1** File `.env` không được commit vào git
- [ ] **9.2** Webhook signature verification được bật
- [ ] **9.3** HTTPS được sử dụng (production)
- [ ] **9.4** Firewall rules được cấu hình
- [ ] **9.5** Rate limiting được setup (nếu cần)
- [ ] **9.6** Backup database được schedule
- [ ] **9.7** Monitoring alerts được setup

## 📋 Phase 10: Documentation & Training

- [ ] **10.1** Admin team được training về quy trình
- [ ] **10.2** Affiliate guide được tạo
- [ ] **10.3** FAQ được chuẩn bị
- [ ] **10.4** Support contact được setup
- [ ] **10.5** Escalation process được định nghĩa

---

## 🎯 Progress Tracker

**Tổng số bước:** 100+
**Đã hoàn thành:** _____ / 100+
**Tiến độ:** _____ %

---

## 📊 Status Summary

| Phase                  | Status | Notes |
| ---------------------- | ------ | ----- |
| 1. Cấu hình cơ bản | ⬜     |       |
| 2. Database Setup      | ⬜     |       |
| 3. Test Connection     | ⬜     |       |
| 4. Webhook Setup       | ⬜     |       |
| 5. Payment Testing     | ⬜     |       |
| 6. Withdrawal Testing  | ⬜     |       |
| 7. Monitoring          | ⬜     |       |
| 8. Email               | ⬜     |       |
| 9. Security            | ⬜     |       |
| 10. Documentation      | ⬜     |       |

**Legend:**

- ⬜ Not Started
- 🟡 In Progress
- ✅ Completed
- ❌ Blocked

---

## 🆘 Issues Tracker

| Issue | Description | Status | Resolution |
| ----- | ----------- | ------ | ---------- |
|       |             |        |            |

---

## 📝 Notes

Ghi chú các vấn đề gặp phải và cách giải quyết:

```
[Date] - [Issue] - [Solution]

```

---

**Last Updated:** ___________
**Updated By:** ___________
**Next Review:** ___________
