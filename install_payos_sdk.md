# Cài đặt PayOS SDK chính thức

## Composer command
```bash
composer require payos/payos
```

## Sample usage
```php
<?php
require 'vendor/autoload.php';

use PayOS\PayOS;

$payos = new PayOS(
    $clientId,
    $apiKey,
    $checksumKey
);

// Tạo payout
$payout = $payos->payouts->create([
    'referenceId' => 'RUT123456789',
    'amount' => 50000,
    'description' => 'Rut tien hoa hong RUT123456789',
    'toBin' => '970422',
    'toAccountNumber' => '0914960029666',
    'category' => ['salary']
]);
```

## Lợi ích
- SDK xử lý signature tự động
- Luôn cập nhật theo quy tắc mới nhất
- Ít lỗi hơn manual implement
```

## Tóm tắt vấn đề

Sau khi test tất cả các phương pháp:
1. ✅ Bỏ toAccountName khỏi request
2. ✅ Deep sort object keys  
3. ✅ Thử URL encoding và không encode
4. ✅ Thử 3 cách sort keys
5. ✅ Thử 3 format category
6. ❌ Còn lại: **Checksum key hoặc quy tắc signature của PayOS**

**Khuyến nghị**: Liên hệ PayOS support để xác nhận checksum key và quy tắc signature chính xác cho payout API.
