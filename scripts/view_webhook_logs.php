<?php
/**
 * View Webhook Logs
 * Xem logs webhook từ database
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../app/models/SepayWebhookLogModel.php';

$webhookModel = new SepayWebhookLogModel();

// Lấy 20 webhook logs gần nhất
$logs = $webhookModel->getRecentWithPagination(1, 20);

echo "<!DOCTYPE html>
<html lang='vi'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Webhook Logs</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background: #f5f5f5;
        }
        h1 {
            color: #333;
        }
        .stats {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
        }
        .stat-item {
            padding: 15px;
            background: #f8f9fa;
            border-radius: 4px;
            border-left: 4px solid #007bff;
        }
        .stat-label {
            font-size: 12px;
            color: #666;
            text-transform: uppercase;
        }
        .stat-value {
            font-size: 24px;
            font-weight: bold;
            color: #333;
            margin-top: 5px;
        }
        table {
            width: 100%;
            background: white;
            border-collapse: collapse;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            border-radius: 8px;
            overflow: hidden;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background: #007bff;
            color: white;
            font-weight: bold;
        }
        tr:hover {
            background: #f8f9fa;
        }
        .badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
        }
        .badge-success {
            background: #28a745;
            color: white;
        }
        .badge-danger {
            background: #dc3545;
            color: white;
        }
        .badge-warning {
            background: #ffc107;
            color: #333;
        }
        .badge-info {
            background: #17a2b8;
            color: white;
        }
        .details {
            font-size: 12px;
            color: #666;
            max-width: 300px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .empty {
            text-align: center;
            padding: 40px;
            color: #999;
        }
        .refresh-btn {
            background: #007bff;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin-bottom: 20px;
        }
        .refresh-btn:hover {
            background: #0056b3;
        }
    </style>
</head>
<body>
    <h1>📊 Webhook Logs - Sepay</h1>
    
    <button class='refresh-btn' onclick='location.reload()'>🔄 Refresh</button>
";

// Hiển thị thống kê
$stats = $webhookModel->getStats();
if ($stats) {
    echo "<div class='stats'>
        <h2>Thống kê</h2>
        <div class='stats-grid'>
            <div class='stat-item'>
                <div class='stat-label'>Tổng Webhooks</div>
                <div class='stat-value'>{$stats['total_webhooks']}</div>
            </div>
            <div class='stat-item'>
                <div class='stat-label'>Payment IN</div>
                <div class='stat-value'>{$stats['payment_in_count']}</div>
            </div>
            <div class='stat-item'>
                <div class='stat-label'>Payment OUT</div>
                <div class='stat-value'>{$stats['payment_out_count']}</div>
            </div>
            <div class='stat-item'>
                <div class='stat-label'>Đã xử lý</div>
                <div class='stat-value'>{$stats['processed_count']}</div>
            </div>
            <div class='stat-item'>
                <div class='stat-label'>Thành công</div>
                <div class='stat-value' style='color: #28a745'>{$stats['success_count']}</div>
            </div>
            <div class='stat-item'>
                <div class='stat-label'>Thất bại</div>
                <div class='stat-value' style='color: #dc3545'>{$stats['failed_count']}</div>
            </div>
        </div>
    </div>";
}

// Hiển thị logs
if (empty($logs['data'])) {
    echo "<div class='empty'>
        <h2>Chưa có webhook nào</h2>
        <p>Thử chuyển khoản hoặc chạy script simulate_sepay_webhook.php để test</p>
    </div>";
} else {
    echo "<table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Loại</th>
                <th>Transaction ID</th>
                <th>Mã tham chiếu</th>
                <th>Số tiền</th>
                <th>Trạng thái</th>
                <th>Xử lý</th>
                <th>Thời gian</th>
                <th>Chi tiết</th>
            </tr>
        </thead>
        <tbody>";
    
    foreach ($logs['data'] as $log) {
        $typeClass = match($log['webhook_type']) {
            'payment_in' => 'badge-success',
            'payment_out' => 'badge-info',
            default => 'badge-warning'
        };
        
        $processedClass = $log['processed'] ? 
            ($log['success'] ? 'badge-success' : 'badge-danger') : 
            'badge-warning';
        
        $processedText = $log['processed'] ? 
            ($log['success'] ? 'Thành công' : 'Thất bại') : 
            'Chưa xử lý';
        
        $amount = number_format($log['amount'], 0, ',', '.') . ' đ';
        
        echo "<tr>
            <td>{$log['id']}</td>
            <td><span class='badge {$typeClass}'>{$log['webhook_type']}</span></td>
            <td>{$log['transaction_id']}</td>
            <td>{$log['reference_code']}</td>
            <td>{$amount}</td>
            <td>{$log['status']}</td>
            <td><span class='badge {$processedClass}'>{$processedText}</span></td>
            <td>{$log['received_at']}</td>
            <td class='details' title='{$log['content']}'>{$log['content']}</td>
        </tr>";
    }
    
    echo "</tbody>
    </table>";
}

echo "
    <div style='margin-top: 20px; padding: 20px; background: white; border-radius: 8px;'>
        <h3>Hướng dẫn test webhook:</h3>
        <ol>
            <li>Chạy: <code>php scripts/simulate_sepay_webhook.php</code></li>
            <li>Hoặc chuyển khoản thật vào tài khoản MB: 0389654785</li>
            <li>Nội dung chuyển khoản: <strong>DH1</strong> (hoặc DH + số order ID)</li>
            <li>Refresh trang này để xem kết quả</li>
        </ol>
        
        <h3>Kiểm tra webhook URL:</h3>
        <p>URL webhook của bạn: <code>https://yourdomain.com/api.php?action=webhook/sepay</code></p>
        <p>Cấu hình URL này trong tài khoản Sepay tại: <a href='https://my.sepay.vn' target='_blank'>https://my.sepay.vn</a></p>
    </div>
</body>
</html>";
