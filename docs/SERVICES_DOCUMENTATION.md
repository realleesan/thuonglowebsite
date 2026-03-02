# Payment Services Documentation

## Tổng quan

4 core services cho hệ thống thanh toán SePay và quản lý hoa hồng affiliate.

## Services

### 1. SepayService

**File:** `app/services/SepayService.php`

**Chức năng:** Tích hợp API SePay cho payment gateway

**Methods:**

#### `generatePaymentQR(int $orderId, float $amount, ?string $description = null): array`
Tạo mã QR thanh toán cho user mua hàng.

**Parameters:**
- `$orderId` - ID đơn hàng
- `$amount` - Số tiền (VND)
- `$description` - Mô tả (optional)

**Returns:**
```php
[
    'success' => true,
    'qr_code' => 'QR_CODE_STRING',
    'qr_url' => 'https://...',
    'qr_data_url' => 'data:image/png;base64,...',
    'content' => 'DH123',  // Order reference
    'amount' => 100000,
    'expires_at' => '2026-02-17 10:02:00',
    'timeout' => 120
]
```

#### `generatePayoutQR(string $withdrawCode, float $amount, array $bankInfo): array`
Tạo mã QR chi tiền cho admin trả affiliate.

**Parameters:**
- `$withdrawCode` - Mã rút tiền (VD: RUT12345)
- `$amount` - Số tiền
- `$bankInfo` - Thông tin ngân hàng ['bank_name', 'account_number', 'account_holder']

#### `verifyWebhookSignature(array $data, string $signature): bool`
Xác thực chữ ký webhook từ SePay.

#### `parseWebhookData(array $webhookData): array`
Parse dữ liệu webhook thành format chuẩn.

#### `extractOrderId(string $content): ?int`
Lấy order ID từ payment content (DH123 → 123).

#### `extractWithdrawCode(string $content): ?string`
Lấy withdrawal code từ payout content.

**Test Mode:**
- Set `test_mode => true` trong config để dùng mock data
- Không gọi API thật, trả về mock QR code

---

### 2. WalletService

**File:** `app/services/WalletService.php`

**Chức năng:** Quản lý ví affiliate

**Methods:**

#### `getBalance(int $affiliateId): array`
Lấy số dư ví của affiliate.

**Returns:**
```php
[
    'success' => true,
    'balance' => 500000,           // Số dư khả dụng
    'pending_withdrawal' => 100000, // Tiền đang chờ rút
    'total_withdrawn' => 1000000,   // Tổng đã rút
    'available' => 500000,          // Có thể rút
    'total_commission' => 1600000   // Tổng hoa hồng
]
```

#### `addCommission(int $affiliateId, int $orderId, float $amount, ?string $description = null): array`
Cộng hoa hồng vào ví.

**Workflow:**
1. Lấy balance hiện tại
2. Cộng commission vào balance
3. Tạo transaction record
4. Update affiliate totals
5. Log activity

#### `createWithdrawalRequest(int $affiliateId, float $amount, array $bankInfo): array`
Tạo yêu cầu rút tiền.

**Workflow:**
1. Validate amount (min/max)
2. Check balance
3. Calculate fee
4. Generate withdraw code
5. Freeze amount (balance → pending_withdrawal)
6. Create withdrawal request
7. Create transaction record

**Returns:**
```php
[
    'success' => true,
    'withdrawal_id' => 123,
    'withdraw_code' => 'RUT1708156789123',
    'amount' => 100000,
    'fee' => 0,
    'net_amount' => 100000
]
```

#### `processWithdrawal(int $withdrawalId, string $status, ?string $note = null): array`
Xử lý yêu cầu rút tiền (admin approve/reject).

**Status:**
- `completed` - Chuyển tiền thành công
- `rejected` - Từ chối, hoàn tiền về balance

#### `adjustBalance(int $affiliateId, float $amount, string $reason, ?int $orderId = null): array`
Điều chỉnh số dư (refund/adjustment).

**Use cases:**
- Refund commission khi đơn bị hủy (amount âm)
- Manual adjustment bởi admin
- Correction

---

### 3. CommissionService

**File:** `app/services/CommissionService.php`

**Chức năng:** Tính toán và xử lý hoa hồng

**Methods:**

#### `calculateCommission(int $orderId): array`
Tính hoa hồng cho đơn hàng.

**Logic:**
1. Lấy order data
2. Check affiliate exists và active
3. Check minimum order amount
4. Get commission rate (từ affiliate hoặc default)
5. Calculate: `commission = (order_total * rate) / 100`

**Returns:**
```php
[
    'success' => true,
    'commission' => 10000,
    'rate' => 10.00,
    'affiliate_id' => 5,
    'order_total' => 100000
]
```

#### `processOrderCommission(int $orderId): array`
Xử lý hoa hồng khi đơn hàng được thanh toán.

**Workflow:**
1. Check commission chưa được xử lý
2. Calculate commission
3. Add to wallet (via WalletService)
4. Update order.commission_amount
5. Update affiliate.total_sales
6. Send email notification

**Trigger:** Gọi từ webhook khi payment_status = 'paid'

#### `refundCommission(int $orderId, string $reason = 'Order cancelled'): array`
Thu hồi hoa hồng khi đơn bị hủy.

**Workflow:**
1. Get commission amount từ order
2. Adjust wallet balance (negative amount)
3. Update order.commission_amount = 0
4. Log refund

#### `getAffiliateStats(int $affiliateId, ?string $startDate = null, ?string $endDate = null): array`
Lấy thống kê hoa hồng của affiliate.

#### `getTopAffiliates(int $limit = 10, string $period = 'month'): array`
Lấy top affiliates theo doanh số.

**Periods:** 'today', 'week', 'month', 'year', 'all'

---

### 4. EmailNotificationService

**File:** `app/services/EmailNotificationService.php`

**Chức năng:** Gửi email thông báo (hợp nhất từ EmailService)

**Features:**
- Gửi email đăng ký đại lý
- Gửi email phê duyệt
- Gửi email xác nhận đơn hàng
- Gửi email thanh toán thành công/thất bại
- Gửi email hoa hồng
- Gửi email rút tiền
- Gửi OTP xác thực ngân hàng
- Template-based emails với PHPMailer

**Methods:**

#### `sendOrderConfirmation(string $email, string $name, array $orderData): array`
Gửi email xác nhận đơn hàng.

#### `sendPaymentSuccess(string $email, string $name, array $orderData): array`
Gửi email thanh toán thành công.

#### `sendPaymentFailed(string $email, string $name, array $orderData, string $reason): array`
Gửi email thanh toán thất bại.

#### `sendCommissionEarned(string $email, string $name, float $commission, string $orderNumber): array`
Gửi email thông báo nhận hoa hồng.

#### `sendWithdrawalRequestToAdmin(array $withdrawalData): array`
Gửi email cho admin khi có yêu cầu rút tiền mới.

#### `sendWithdrawalCompleted(string $email, string $name, array $withdrawalData): array`
Gửi email cho affiliate khi rút tiền thành công.

#### `sendWithdrawalRejected(string $email, string $name, array $withdrawalData, string $reason): array`
Gửi email khi yêu cầu rút tiền bị từ chối.

#### `sendBankInfoOTP(string $email, string $name, string $otp): array`
Gửi OTP để xác thực thay đổi thông tin ngân hàng.

**Email Backend:**
- Primary: PHPMailer (SMTP)
- Fallback: PHP mail() function
- Templates: HTML với inline CSS

---

## Usage Examples

### Example 1: Xử lý thanh toán thành công (Webhook)

```php
// In WebhookController
$sepayService = new SepayService();
$commissionService = new CommissionService();
$emailService = new EmailNotificationService();

// Parse webhook
$webhookData = $sepayService->parseWebhookData($_POST);
$orderId = $sepayService->extractOrderId($webhookData['reference_code']);

// Update order payment status
$orderModel->updatePaymentStatus($orderId, 'paid', $webhookData['transaction_id']);

// Process commission (auto-credit)
if ($commissionService->isAutoCreditEnabled()) {
    $result = $commissionService->processOrderCommission($orderId);
}

// Send email
$order = $orderModel->getOrderWithItems($orderId);
$emailService->sendPaymentSuccess(
    $order['user_email'],
    $order['user_name'],
    $order
);
```

### Example 2: Tạo yêu cầu rút tiền

```php
// In AffiliateWalletController
$walletService = new WalletService();
$emailService = new EmailNotificationService();

// Create withdrawal request
$result = $walletService->createWithdrawalRequest(
    $affiliateId,
    $amount,
    [
        'bank_name' => 'MB Bank',
        'bank_account' => '0123456789',
        'account_holder' => 'NGUYEN VAN A'
    ]
);

if ($result['success']) {
    // Send notification to admin
    $emailService->sendWithdrawalRequestToAdmin([
        'withdraw_code' => $result['withdraw_code'],
        'affiliate_name' => $affiliateName,
        'amount' => $amount,
        'bank_name' => 'MB Bank',
        'bank_account' => '0123456789',
        'account_holder' => 'NGUYEN VAN A',
        'requested_at' => date('Y-m-d H:i:s')
    ]);
}
```

### Example 3: Admin duyệt rút tiền

```php
// In AdminWithdrawalController
$sepayService = new SepayService();
$walletService = new WalletService();
$emailService = new EmailNotificationService();

// Get withdrawal request
$withdrawal = $withdrawalModel->find($withdrawalId);

// Generate QR for admin to scan
$qrResult = $sepayService->generatePayoutQR(
    $withdrawal['withdraw_code'],
    $withdrawal['amount'],
    [
        'bank_name' => $withdrawal['bank_name'],
        'account_number' => $withdrawal['bank_account'],
        'account_holder' => $withdrawal['account_holder']
    ]
);

// Display QR to admin
// Admin scans and transfers money
// Webhook comes back → process withdrawal

// In webhook handler:
$walletService->processWithdrawal($withdrawalId, 'completed');

// Send email to affiliate
$emailService->sendWithdrawalCompleted(
    $affiliate['email'],
    $affiliate['name'],
    $withdrawal
);
```

### Example 4: Refund commission khi đơn bị hủy

```php
// In OrdersController or Admin
$commissionService = new CommissionService();

// Cancel order
$orderModel->updateStatus($orderId, 'cancelled');

// Refund commission
$result = $commissionService->refundCommission(
    $orderId,
    'Order cancelled by customer'
);

if ($result['success']) {
    // Commission refunded: -10,000 VND from affiliate wallet
}
```

---

## Configuration

All services use config from `config.php`:

```php
'sepay' => [
    'api_key' => Env::get('SEPAY_API_KEY'),
    'payment_timeout' => 120,
    'test_mode' => true, // Enable for development
],

'commission' => [
    'default_rate' => 10.00,
    'auto_credit' => true,
],

'withdrawal' => [
    'min_amount' => 5000,
    'max_amount' => 50000000,
    'fee' => 0,
],

'email' => [
    'enabled' => true,
    'smtp_host' => Env::get('SMTP_HOST'),
    'smtp_username' => Env::get('SMTP_USERNAME'),
],
```

---

## Logging

All services log to separate files:

- `logs/payment.log` - SePay transactions
- `logs/webhook.log` - Webhook events
- `logs/commission.log` - Commission & wallet activities
- `logs/email.log` - Email sending

---

## Error Handling

All services extend `BaseService` và sử dụng `ErrorHandler`:

```php
try {
    // Service logic
} catch (Exception $e) {
    return $this->handleError($e, $context);
}
```

Returns:
```php
[
    'success' => false,
    'message' => 'Error message',
    'fallback' => true
]
```

---

## Testing

### Test SePay Service
```bash
php scripts/test_sepay_service.php
```

### Test Wallet Service
```bash
php scripts/test_wallet_service.php
```

### Test Email Service
```bash
php scripts/test_email_service.php
```

---

## Next Steps

1. ✅ Services created
2. ⏳ Create Models (WalletTransactionModel, WithdrawalRequestModel, SepayWebhookLogModel)
3. ⏳ Create Controllers (WebhookController, PaymentController, etc.)
4. ⏳ Create Views
5. ⏳ Integration testing

---

## Support

Nếu gặp vấn đề:
1. Check logs trong `logs/` folder
2. Verify config: `php scripts/test_config.php`
3. Test services individually
4. Check SePay API documentation
