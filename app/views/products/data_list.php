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
$page = max(1, (int)($_GET['p'] ?? $_GET['page'] ?? 1));
$perPage = 10;

// Initialize variables
$product = null;
$dataList = [];
$pagination = [];
$error = '';
$success = true;
$expiresAt = null;

// Progressive blur configuration (initialized with defaults)
$ENABLE_PROGRESSIVE_BLUR = true; // Set to false to disable blur feature
$TOTAL_DURATION = 15 * 60; // 15 minutes in seconds
$BLUR_INTERVAL = 90; // 1 minute 30 seconds between each column blur
$TOTAL_COLS = 8; // Total columns to blur
$initialBlurCols = 0;
$nextBlurInSeconds = null;
$createdAt = null;

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
    $createdAt = $access['created_at'] ?? date('Y-m-d H:i:s');

    // Calculate elapsed time and which columns should be blurred
    $elapsedTime = time() - strtotime($createdAt);
    // Column i gets blurred when: elapsedTime >= (15min - (8-i)*1.5min)
    // Or: elapsedTime >= (i+1) * 90s when counting from rightmost column
    // Actually: col 0 blurs when 14:30 left (90s passed), col 1 at 13:00 left (180s passed), etc.
    $blurStagesPassed = floor($elapsedTime / $BLUR_INTERVAL);
    $initialBlurCols = max(0, min($TOTAL_COLS, $blurStagesPassed));
    // Time until next column should be blurred
    $nextBlurInSeconds = ($blurStagesPassed < $TOTAL_COLS) ? (($blurStagesPassed + 1) * $BLUR_INTERVAL - $elapsedTime) : null;
    
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
    
    <a href="?page=details&id=<?php echo $productId; ?>" class="back-btn"><i class="fas fa-arrow-left"></i> Quay lại sản phẩm</a>
    
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
                    <th>Phân loại phong cách</th>
                    <th>Ảnh cửa hàng</th>
                    <th>QR WeChat</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $startIndex = ($pagination['current_page'] - 1) * $pagination['per_page'];
                foreach ($dataList as $index => $row): 
                ?>
                <tr>
                    <td data-col="0"><?php echo $startIndex + $index + 1; ?></td>
                    <td data-col="1"><?php echo htmlspecialchars($row['supplier_name'] ?? ''); ?></td>
                    <td data-col="2"><?php echo htmlspecialchars($row['address'] ?? ''); ?></td>
                    <td data-col="3"><?php echo htmlspecialchars($row['wechat_account'] ?? ''); ?></td>
                    <td data-col="4"><?php echo htmlspecialchars($row['phone'] ?? ''); ?></td>
                    <td data-col="5"><?php echo htmlspecialchars($row['style_classification'] ?? ''); ?></td>
                    <td data-col="6" class="store-image-cell">
                        <?php if (!empty($row['store_image'])): ?>
                        <span class="store-image-trigger" onclick="openQrModal('<?php echo htmlspecialchars($row['store_image']); ?>')" title="Xem ảnh cửa hàng">
                            <i class="fas fa-store store-image-icon"></i>
                        </span>
                        <?php else: ?>
                        -
                        <?php endif; ?>
                    </td>
                    <td data-col="7" class="qr-cell">
                        <?php if (!empty($row['wechat_qr'])): ?>
                        <span class="qr-trigger" onclick="openQrModal('<?php echo htmlspecialchars($row['wechat_qr']); ?>')" title="Xem QR">
                            <img src="<?php echo htmlspecialchars($row['wechat_qr']); ?>" alt="QR" class="qr-thumb"
                                 onerror="this.style.display='none'; this.parentNode.querySelector('.qr-icon').style.display='inline-flex';">
                            <i class="fas fa-qrcode qr-icon" style="display:none;" title="Xem QR"></i>
                        </span>
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
            <a href="?page=product-data&id=<?php echo $productId; ?>&token=<?php echo htmlspecialchars($token); ?>&p=<?php echo $pagination['current_page'] - 1; ?>" 
               class="pagination-link">← Trước</a>
            <?php endif; ?>
            
            <?php 
            // Show page numbers
            $startPage = max(1, $pagination['current_page'] - 2);
            $endPage = min($pagination['last_page'], $pagination['current_page'] + 2);
            
            for ($i = $startPage; $i <= $endPage; $i++): 
            ?>
            <a href="?page=product-data&id=<?php echo $productId; ?>&token=<?php echo htmlspecialchars($token); ?>&p=<?php echo $i; ?>" 
               class="pagination-link <?php echo ($i === $pagination['current_page']) ? 'pagination-current' : ''; ?>">
                <?php echo $i; ?>
            </a>
            <?php endfor; ?>
            
            <?php if ($pagination['current_page'] < $pagination['last_page']): ?>
            <a href="?page=product-data&id=<?php echo $productId; ?>&token=<?php echo htmlspecialchars($token); ?>&p=<?php echo $pagination['current_page'] + 1; ?>" 
               class="pagination-link">Sau →</a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
    
    <?php endif; ?>
    
    <?php endif; ?>
</div>

<!-- QR Modal Lightbox -->
<div id="qrModal" class="qr-modal" onclick="closeQrModal(event)">
    <div class="qr-modal-content" onclick="event.stopPropagation()">
        <span class="qr-modal-close" onclick="closeQrModal()">&times;</span>
        <img id="qrModalImage" src="" alt="QR Code">
    </div>
</div>

<script>
// Progressive Blur Configuration
const ENABLE_PROGRESSIVE_BLUR = <?php echo $ENABLE_PROGRESSIVE_BLUR ? 'true' : 'false'; ?>;
const BLUR_CONFIG = {
    totalCols: <?php echo $TOTAL_COLS; ?>,
    blurInterval: <?php echo $BLUR_INTERVAL * 1000; ?>, // Convert to milliseconds
    initialBlurCols: <?php echo $initialBlurCols; ?>,
    nextBlurInMs: <?php echo $nextBlurInSeconds !== null ? $nextBlurInSeconds * 1000 : 'null'; ?>
};

let qrModalTimer = null;

function openQrModal(imageSrc) {
    const modal = document.getElementById('qrModal');
    const modalImg = document.getElementById('qrModalImage');
    modalImg.src = imageSrc;
    modal.classList.add('active');
    document.body.style.overflow = 'hidden';

    // Auto-close after 5 seconds
    clearTimeout(qrModalTimer);
    qrModalTimer = setTimeout(() => {
        closeQrModal();
    }, 5000);
}

function closeQrModal(event) {
    // If event is provided, only close if clicking the overlay (not the image)
    if (event && event.target !== event.currentTarget) return;

    const modal = document.getElementById('qrModal');
    modal.classList.remove('active');
    document.body.style.overflow = '';

    // Clear src after animation
    setTimeout(() => {
        document.getElementById('qrModalImage').src = '';
    }, 300);
}

// Close on Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeQrModal();
    }
});

// Progressive blur effect - columns blur based on time elapsed from session start
(function startProgressiveBlur() {
    if (!ENABLE_PROGRESSIVE_BLUR) return;

    const { totalCols, blurInterval, initialBlurCols, nextBlurInMs } = BLUR_CONFIG;

    // Blur columns that should already be blurred based on elapsed time
    for (let i = 0; i < initialBlurCols; i++) {
        const cells = document.querySelectorAll(`td[data-col="${i}"]`);
        cells.forEach(cell => {
            cell.classList.add('blurred-cell');
        });
    }

    // If there are more columns to blur, schedule the next one
    let currentCol = initialBlurCols;

    function scheduleNextBlur() {
        if (currentCol >= totalCols) return;

        const delay = (currentCol === initialBlurCols && nextBlurInMs !== null)
            ? nextBlurInMs  // First upcoming blur uses calculated time
            : blurInterval; // Subsequent blurs use fixed interval

        setTimeout(() => {
            // Add blur class to all cells with this column index
            const cells = document.querySelectorAll(`td[data-col="${currentCol}"]`);
            cells.forEach(cell => {
                cell.classList.add('blurred-cell');
            });

            currentCol++;
            scheduleNextBlur(); // Schedule next column
        }, delay);
    }

    scheduleNextBlur();
})();
</script>