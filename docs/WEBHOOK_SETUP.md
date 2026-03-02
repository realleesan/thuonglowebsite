  # SePay Webhook Setup Guide

## Overview
Webhook endpoint để nhận thông báo từ SePay khi có giao dịch thanh toán (payment in) hoặc rút tiền (payment out).

## Webhook URL

### Production
```
https://yourdomain.com/api.php?path=webhook/sepay
```

### Test Endpoint
```
https://yourdomain.com/api.php?path=webhook/test
```

## Cấu hình trên SePay Dashboard

1. Đăng nhập vào SePay Dashboard
2. Vào phần **Settings** > **Webhooks**
3. Thêm webhook URL: `https://yourdomain.com/api.php?path=webhook/sepay`
4. Chọn events cần nhận:
   - ✅ Payment Received (payment_in)
   - ✅ Payment Sent (payment_out)
5. Lưu cấu hình

## Webhook Flow

### 1. Payment IN (Khách hàng thanh toán đơn hàng)

**Reference Code Format:** `DH{OrderId}`
- Ví dụ: `DH123` (Order ID = 123)

**Flow:**
1. SePay gửi webhook khi nhận được tiền
2. System log webhook vào `sepay_webhooks_log`
3. Xác định order từ reference code
4. Kiểm tra số tiền khớp với order total
5. Cập nhật order payment_status = 'paid'
6. Cập nhật order status = 'processing'
7. Nếu có affiliate, ghi nhận commission vào wallet

**Webhook Data Example:**
```json
{
  "id": "TXN123456",
  "transaction_id": "TXN123456",
  "amount_in": 500000,
  "content": "DH123",
  "account_number": "0123456789",
  "status": "success",
  "description": "Payment for order DH123"
}
```

### 2. Payment OUT (Rút tiền cho affiliate)

**Reference Code Format:** `RUT{YYMMDD}{XXXX}`
- Ví dụ: `RUT2602170001` (Withdrawal code)

**Flow:**
1. SePay gửi webhook khi chuyển tiền thành công
2. System log webhook vào `sepay_webhooks_log`
3. Tìm withdrawal request từ withdraw_code
4. Kiểm tra số tiền khớp với net_amount
5. Mark webhook received
6. Complete withdrawal:
   - Move từ pending_withdrawal → total_withdrawn
   - Tạo wallet transaction
   - Update withdrawal status = 'completed'

**Webhook Data Example:**
```json
{
  "id": "TXN789012",
  "transaction_id": "TXN789012",
  "amount_out": 1000000,
  "content": "RUT2602170001",
  "account_number": "9876543210",
  "status": "success",
  "description": "Withdrawal RUT2602170001"
}
```

## Security

### Signature Verification
Webhook có thể được verify bằng signature (nếu SePay cung cấp):

```php
$signature = $_SERVER['HTTP_X_SEPAY_SIGNATURE'] ?? null;
$verified = $sepayService->verifyWebhookSignature($rawData, $signature);
```

### IP Whitelist (Optional)
Có thể thêm IP whitelist trong config:

```php
// config.php
define('SEPAY_WEBHOOK_IPS', [
    '123.45.67.89',
    '98.76.54.32'
]);
```

## Webhook Logging

Tất cả webhooks được log vào bảng `sepay_webhooks_log`:

- `webhook_type`: payment_in, payment_out, unknown
- `transaction_id`: SePay transaction ID
- `reference_code`: DH{OrderId} hoặc RUT{WithdrawCode}
- `amount`: Số tiền giao dịch
- `processed`: 0/1 (đã xử lý chưa)
- `success`: 0/1 (xử lý thành công không)
- `processing_error`: Lỗi nếu có
- `raw_data`: Full webhook data (JSON)

## Testing

### 1. Test Endpoint Accessibility
```bash
curl https://yourdomain.com/api.php?path=webhook/test
```

Expected response:
```json
{
  "success": true,
  "message": "Webhook endpoint is working",
  "timestamp": "2026-02-17 10:30:00"
}
```

### 2. Test Payment IN Webhook
```bash
curl -X POST https://yourdomain.com/api.php?path=webhook/sepay \
  -H "Content-Type: application/json" \
  -d '{
    "id": "TEST123",
    "transaction_id": "TEST123",
    "amount_in": 500000,
    "content": "DH1",
    "account_number": "0123456789",
    "status": "success"
  }'
```

### 3. Test Payment OUT Webhook
```bash
curl -X POST  https://yourdomain.com/api.php?path=webhook/sepay \
  -H "Content-Type: application/json" \
  -d '{
    "id": "TEST456",
    "transaction_id": "TEST456",
    "amount_out": 1000000,
    "content": "RUT2602170001",
    "account_number": "9876543210",
    "status": "success"
  }'
```

## Monitoring

### Check Webhook Logs
```sql
-- Recent webhooks
SELECT * FROM sepay_webhooks_log 
ORDER BY received_at DESC 
LIMIT 20;

-- Unprocessed webhooks
SELECT * FROM sepay_webhooks_log 
WHERE processed = 0 
ORDER BY received_at ASC;

-- Failed webhooks
SELECT * FROM sepay_webhooks_log 
WHERE processed = 1 AND success = 0 
ORDER BY received_at DESC;
```

### Webhook Statistics
```sql
SELECT 
    webhook_type,
    COUNT(*) as total,
    SUM(processed) as processed_count,
    SUM(success) as success_count,
    SUM(CASE WHEN processed = 1 AND success = 0 THEN 1 ELSE 0 END) as failed_count
FROM sepay_webhooks_log
GROUP BY webhook_type;
```

## Error Handling

### Common Errors

1. **Order not found**
   - Check reference code format
   - Verify order exists in database

2. **Amount mismatch**
   - Check order total vs received amount
   - Allow small difference (0.01) for rounding

3. **Duplicate webhook**
   - Check if order already paid
   - Check if withdrawal already completed

4. **Invalid JSON**
   - Check webhook data format
   - Verify Content-Type header

## Troubleshooting

### Webhook không được nhận
1. Check URL accessibility từ internet
2. Check firewall/security rules
3. Check SSL certificate (phải valid)
4. Check SePay dashboard webhook config

### Webhook nhận nhưng không xử lý
1. Check webhook logs: `SELECT * FROM sepay_webhooks_log WHERE processed = 0`
2. Check processing_error column
3. Check PHP error logs
4. Verify database connections

### Webhook xử lý sai
1. Check raw_data trong webhook log
2. Verify reference code parsing
3. Check order/withdrawal status
4. Review transaction logs

## Support

Nếu gặp vấn đề:
1. Check webhook logs trong database
2. Check PHP error logs
3. Contact SePay support với transaction_id
4. Provide webhook log ID cho debugging
