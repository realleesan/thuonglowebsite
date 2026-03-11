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

<div class="data-list-page user-data-list">
    <?php if (!$success): ?>
    <div class="alert alert-error">
        <h3><i class="fas fa-exclamation-circle"></i> Lỗi</h3>
        <p><?php echo htmlspecialchars($error); ?></p>
        <a href="?page=products" class="back-btn"><i class="fas fa-arrow-left"></i> Quay lại trang sản phẩm</a>
    </div>
    <?php else: ?>
    
    <a href="?page=product&id=<?php echo $productId; ?>" class="back-btn"><i class="fas fa-arrow-left"></i> Quay lại sản phẩm</a>
    
    <div class="page-header">
        <div class="page-header-inner">
            <div class="page-header-left">
                <h1 class="page-title"><i class="fas fa-list"></i> Danh sách dữ liệu</h1>
                <p class="product-name"><?php echo htmlspecialchars($product['name'] ?? ''); ?></p>
            </div>
            <div class="token-info">
                <div class="token-info-label"><i class="fas fa-clock"></i> Phiên xem data sẽ hết hạn sau:</div>
                <div class="token-timer" id="countdown" data-expires-at="<?php echo strtotime($expiresAt); ?>">
                    <?php 
                    $remaining = strtotime($expiresAt) - time();
                    $minutes = floor($remaining / 60);
                    $seconds = $remaining % 60;
                    echo "{$minutes} phút {$seconds} giây";
                    ?>
                </div>
                <div class="token-expire-time">
                    (Hết hạn lúc: <?php echo date('H:i:s d/m/Y', strtotime($expiresAt)); ?>)
                </div>
            </div>
        </div>
    </div>
    
    <?php if (empty($dataList)): ?>
    <div class="data-table-container">
        <div class="empty-state">
            <i class="fas fa-inbox"></i>
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