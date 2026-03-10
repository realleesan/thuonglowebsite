<?php
/**
 * User Product Data List Page
 * Display data table with pagination after quota deduction
 */

// Initialize View
require_once __DIR__ . '/../../../core/view_init.php';

// Get services
$service = isset($currentService) ? $currentService : ($publicService ?? null);

// Get parameters
$productId = (int)($_GET['id'] ?? $_GET['product_id'] ?? 0);
$token = $_GET['token'] ?? '';
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 10;

// Initialize variables
$product = null;
$dataList = [];
$pagination = [];
$error = '';
$success = true;
$expiresAt = null;

try {
    if (!$productId) {
        throw new Exception('ID sản phẩm không hợp lệ');
    }
    
    if (!$token) {
        throw new Exception('Token truy cập không hợp lệ');
    }
    
    // Check login
    $userId = $_SESSION['user_id'] ?? 0;
    if (!$userId) {
        header('Location: ?page=login&redirect=' . urlencode($_SERVER['REQUEST_URI']));
        exit;
    }
    
    // Load model
    require_once __DIR__ . '/../../models/ProductDataModel.php';
    $productDataModel = new ProductDataModel();
    
    // Validate token
    $validation = $productDataModel->validateAccess($token);
    
    if (!$validation['valid']) {
        throw new Exception($validation['error']);
    }
    
    $access = $validation['access'];
    $expiresAt = $access['expires_at'];
    
    // Verify this token belongs to this user and product
    if ($access['user_id'] != $userId || $access['product_id'] != $productId) {
        throw new Exception('Bạn không có quyền truy cập dữ liệu này');
    }
    
    // Load ProductsModel
    require_once __DIR__ . '/../../models/ProductsModel.php';
    
    // Get product info
    $productsModel = new ProductsModel();
    $product = $productsModel->find($productId);
    
    if (!$product) {
        throw new Exception('Sản phẩm không tồn tại');
    }
    
    // Get data with pagination
    $dataPaginated = $productDataModel->getByProductPaginated($productId, $page, $perPage);
    $dataList = $dataPaginated['data'];
    $pagination = $dataPaginated;
    
} catch (Exception $e) {
    $error = $e->getMessage();
    $success = false;
}
?>

<?php include __DIR__ . '/../_layout/header.php'; ?>

<style>
.data-list-page {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
}
.page-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 30px;
    border-radius: 10px;
    margin-bottom: 20px;
}
.page-title {
    font-size: 28px;
    margin-bottom: 10px;
}
.product-name {
    font-size: 18px;
    opacity: 0.9;
}
.token-info {
    background: rgba(255,255,255,0.2);
    padding: 15px;
    border-radius: 8px;
    margin-top: 15px;
    font-size: 14px;
}
.token-timer {
    font-weight: 600;
    font-size: 16px;
}
.alert {
    padding: 15px 20px;
    border-radius: 8px;
    margin-bottom: 20px;
}
.alert-error {
    background: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}
.alert-warning {
    background: #fff3cd;
    color: #856404;
    border: 1px solid #ffeaa7;
}
.data-table-container {
    background: white;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    overflow: hidden;
}
.data-table {
    width: 100%;
    border-collapse: collapse;
}
.data-table th {
    background: #f8f9fa;
    padding: 15px;
    text-align: left;
    font-weight: 600;
    color: #333;
    border-bottom: 2px solid #dee2e6;
}
.data-table td {
    padding: 12px 15px;
    border-bottom: 1px solid #dee2e6;
}
.data-table tr:hover {
    background: #f8f9fa;
}
.data-table tr:last-child td {
    border-bottom: none;
}
.qr-cell {
    max-width: 80px;
}
.qr-thumb {
    width: 60px;
    height: 60px;
    object-fit: cover;
    border-radius: 4px;
    cursor: pointer;
    border: 1px solid #ddd;
}
.pagination-container {
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 20px;
    gap: 5px;
}
.pagination-link {
    padding: 8px 14px;
    border: 1px solid #ddd;
    border-radius: 4px;
    text-decoration: none;
    color: #333;
    transition: all 0.2s;
}
.pagination-link:hover {
    background: #667eea;
    color: white;
    border-color: #667eea;
}
.pagination-current {
    background: #667eea;
    color: white;
    border-color: #667eea;
}
.pagination-info {
    text-align: center;
    padding: 10px;
    color: #666;
}
.back-btn {
    display: inline-block;
    padding: 10px 20px;
    background: white;
    color: #667eea;
    text-decoration: none;
    border-radius: 6px;
    margin-bottom: 20px;
    font-weight: 500;
}
.back-btn:hover {
    background: #f8f9fa;
}
.empty-state {
    text-align: center;
    padding: 50px;
    color: #666;
}
</style>

<div class="data-list-page">
    <?php if (!$success): ?>
    <div class="alert alert-error">
        <h3>❌ Lỗi</h3>
        <p><?php echo htmlspecialchars($error); ?></p>
        <a href="?page=products" class="back-btn">← Quay lại trang sản phẩm</a>
    </div>
    <?php else: ?>
    
    <a href="?page=product&id=<?php echo $productId; ?>" class="back-btn">← Quay lại sản phẩm</a>
    
    <div class="page-header">
        <h1 class="page-title">📋 Danh sách dữ liệu</h1>
        <p class="product-name"><?php echo htmlspecialchars($product['name'] ?? ''); ?></p>
        
        <div class="token-info">
            <div>⏰ Phiên xem data sẽ hết hạn sau:</div>
            <div class="token-timer" id="countdown">
                <?php 
                $remaining = strtotime($expiresAt) - time();
                $minutes = floor($remaining / 60);
                $seconds = $remaining % 60;
                echo "{$minutes} phút {$seconds} giây";
                ?>
            </div>
            <div style="margin-top: 5px; font-size: 12px; opacity: 0.8;">
                (Hết hạn lúc: <?php echo date('H:i:s d/m/Y', strtotime($expiresAt)); ?>)
            </div>
        </div>
    </div>
    
    <?php if (empty($dataList)): ?>
    <div class="data-table-container">
        <div class="empty-state">
            <h3>Chưa có dữ liệu</h3>
            <p>Admin chưa upload dữ liệu cho sản phẩm này.</p>
        </div>
    </div>
    <?php else: ?>
    
    <div class="data-table-container">
        <table class="data-table">
            <thead>
                <tr>
                    <th>STT</th>
                    <th>Tên nhà cung cấp</th>
                    <th>Địa chỉ</th>
                    <th>WeChat</th>
                    <th>Điện thoại</th>
                    <th>QR WeChat</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $startIndex = ($pagination['current_page'] - 1) * $pagination['per_page'];
                foreach ($dataList as $index => $row): 
                ?>
                <tr>
                    <td><?php echo $startIndex + $index + 1; ?></td>
                    <td><?php echo htmlspecialchars($row['supplier_name'] ?? ''); ?></td>
                    <td><?php echo htmlspecialchars($row['address'] ?? ''); ?></td>
                    <td><?php echo htmlspecialchars($row['wechat_account'] ?? ''); ?></td>
                    <td><?php echo htmlspecialchars($row['phone'] ?? ''); ?></td>
                    <td class="qr-cell">
                        <?php if (!empty($row['wechat_qr'])): ?>
                        <a href="<?php echo htmlspecialchars($row['wechat_qr']); ?>" target="_blank">
                            <img src="<?php echo htmlspecialchars($row['wechat_qr']); ?>" alt="QR" class="qr-thumb" 
                                 onerror="this.style.display='none'; this.nextElementSibling.style.display='inline';">
                            <span style="display:none;">Xem QR</span>
                        </a>
                        <?php else: ?>
                        -
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <!-- Pagination -->
        <?php if ($pagination['last_page'] > 1): ?>
        <div class="pagination-info">
            Trang <?php echo $pagination['current_page']; ?> / <?php echo $pagination['last_page']; ?> 
            (Tổng: <?php echo $pagination['total']; ?> dòng)
        </div>
        <div class="pagination-container">
            <?php if ($pagination['current_page'] > 1): ?>
            <a href="?page=product-data&id=<?php echo $productId; ?>&token=<?php echo htmlspecialchars($token); ?>&page=<?php echo $pagination['current_page'] - 1; ?>" 
               class="pagination-link">← Trước</a>
            <?php endif; ?>
            
            <?php 
            // Show page numbers
            $startPage = max(1, $pagination['current_page'] - 2);
            $endPage = min($pagination['last_page'], $pagination['current_page'] + 2);
            
            for ($i = $startPage; $i <= $endPage; $i++): 
            ?>
            <a href="?page=product-data&id=<?php echo $productId; ?>&token=<?php echo htmlspecialchars($token); ?>&page=<?php echo $i; ?>" 
               class="pagination-link <?php echo ($i === $pagination['current_page']) ? 'pagination-current' : ''; ?>">
                <?php echo $i; ?>
            </a>
            <?php endfor; ?>
            
            <?php if ($pagination['current_page'] < $pagination['last_page']): ?>
            <a href="?page=product-data&id=<?php echo $productId; ?>&token=<?php echo htmlspecialchars($token); ?>&page=<?php echo $pagination['current_page'] + 1; ?>" 
               class="pagination-link">Sau →</a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
    
    <?php endif; ?>
    
    <?php endif; ?>
</div>

<script>
// Countdown timer
function updateCountdown() {
    const countdownEl = document.getElementById('countdown');
    if (!countdownEl) return;
    
    let timeRemaining = <?php echo strtotime($expiresAt) - time(); ?>;
    
    if (timeRemaining <= 0) {
        countdownEl.innerHTML = '⏰ Phiên đã hết hạn!';
        countdownEl.style.color = '#dc3545';
        return;
    }
    
    const minutes = Math.floor(timeRemaining / 60);
    const seconds = timeRemaining % 60;
    countdownEl.innerHTML = `${minutes} phút ${seconds} giây`;
    
    if (timeRemaining <= 60) {
        countdownEl.style.color = '#dc3545';
    } else if (timeRemaining <= 300) {
        countdownEl.style.color = '#ffc107';
    }
}

setInterval(updateCountdown, 1000);
updateCountdown();
</script>

<?php include __DIR__ . '/../_layout/footer.php'; ?>
