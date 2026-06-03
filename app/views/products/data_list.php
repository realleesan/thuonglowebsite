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

// Content protection configuration
$ENABLE_CONTENT_PROTECTION = true; // Set to false to disable copy/print protection

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
        <div class="table-responsive">
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
                                <i class="fas fa-qrcode qr-icon" title="Xem QR"></i>
                            </span>
                            <?php else: ?>
                            -
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
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
// Content Protection Configuration
const ENABLE_CONTENT_PROTECTION = <?php echo $ENABLE_CONTENT_PROTECTION ? 'true' : 'false'; ?>;

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

// Content Protection - Prevent copy, print, screenshot, right-click, F12
(function initContentProtection() {
    if (!ENABLE_CONTENT_PROTECTION) return;

    // Create overlay element for blur protection
    const overlay = document.createElement('div');
    overlay.id = 'protection-overlay';
    overlay.innerHTML = '<div class="overlay-content"></div>';
    document.body.appendChild(overlay);

    let isDevToolsOpen = false;
    let isWindowBlurred = false;
    let screenshotWarningActive = false;

    // Show protection overlay with specific reasons
    function showProtectionOverlay(reason) {
        if (!overlay) return;
        
        const content = overlay.querySelector('.overlay-content');
        if (content) {
            // If screenshot warning is active, force the screenshot warning message!
            const activeReason = screenshotWarningActive ? 'screenshot' : reason;
            
            if (activeReason === 'devtools') {
                content.innerHTML = `
                    <div class="lock-icon"><i class="fas fa-terminal"></i></div>
                    <h2>PHÁT HIỆN DEVELOPER TOOLS</h2>
                    <p>Vui lòng đóng Developer Tools (F12) để tiếp tục xem nội dung dữ liệu sản phẩm.</p>
                `;
            } else if (activeReason === 'blur') {
                content.innerHTML = `
                    <div class="lock-icon"><i class="fas fa-eye-slash"></i></div>
                    <h2>NỘI DUNG ĐƯỢC BẢO VỆ</h2>
                    <p>Nhấp vào bất kỳ đâu trên màn hình này để tiếp tục xem.</p>
                `;
            } else if (activeReason === 'screenshot') {
                content.innerHTML = `
                    <div class="lock-icon"><i class="fas fa-camera"></i></div>
                    <h2>CHỤP MÀN HÌNH BỊ CHẶN</h2>
                    <p>Hệ thống không cho phép chụp ảnh hoặc quay màn hình nội dung này. Clipboard của bạn đã bị xóa.</p>
                    <p style="margin-top: 15px; font-size: 13px; color: #ef4444; font-weight: 600;">Nhấp vào bất kỳ đâu trên màn hình này để tiếp tục xem.</p>
                `;
            }
        }
        
        overlay.style.display = 'flex';
        document.body.classList.add('protection-active');
    }

    // Hide protection overlay if safe
    function hideProtectionOverlay() {
        if (isDevToolsOpen) {
            showProtectionOverlay('devtools');
            return;
        }
        if (screenshotWarningActive) {
            showProtectionOverlay('screenshot');
            return;
        }
        if (document.hidden || !document.hasFocus()) {
            showProtectionOverlay('blur');
            return;
        }
        
        if (overlay) {
            overlay.style.display = 'none';
        }
        document.body.classList.remove('protection-active');
    }

    // Safe dismissal check for the overlay
    function dismissOverlayIfSafe() {
        if (isDevToolsOpen || screenshotWarningActive) return;
        
        // Hide only if window actually has focus and page is visible
        if (document.hasFocus() && document.visibilityState === 'visible') {
            isWindowBlurred = false;
            if (overlay) {
                overlay.style.display = 'none';
            }
            document.body.classList.remove('protection-active');
        }
    }

    // Click anywhere on overlay to dismiss it
    overlay.addEventListener('click', dismissOverlayIfSafe);

    // Also support scroll/keydown to dismiss the overlay (only if focused/visible/safe)
    window.addEventListener('scroll', dismissOverlayIfSafe, { passive: true });
    document.addEventListener('mousedown', dismissOverlayIfSafe);

    // Trigger screenshot warning (Instantly kick to homepage)
    function triggerScreenshotWarning() {
        // Overwrite clipboard
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText('[Nội dung đã được bảo vệ - THUONGLO.COM]').catch(() => {});
        }
        window.location.replace('index.php');
    }

    // DevTools Detection Module
    const DevToolsDetector = {
        isOpen: false,
        onChange: null,
        
        init(callback) {
            this.onChange = callback;
            
            // Listen to resize
            window.addEventListener('resize', () => this.detect());
            
            // Continuous size & property check
            setInterval(() => this.detect(), 1000);
            
            // Debugger detection loop
            this.runDebuggerCheck();
            
            // Console format getter check
            this.runConsoleCheck();
            
            this.detect();
        },
        
        detect() {
            let open = false;
            const threshold = 160;
            
            const widthDiff = window.outerWidth - window.innerWidth;
            const heightDiff = window.outerHeight - window.innerHeight;
            
            // Desktop dimensions difference check
            if (window.innerWidth > 768 && (widthDiff > threshold || heightDiff > threshold)) {
                open = true;
            }
            
            if (open !== this.isOpen) {
                this.isOpen = open;
                if (this.onChange) this.onChange(open);
            }
        },
        
        runDebuggerCheck() {
            const self = this;
            (function check() {
                const start = performance.now();
                debugger;
                const end = performance.now();
                if (end - start > 100) {
                    if (!self.isOpen) {
                        self.isOpen = true;
                        if (self.onChange) self.onChange(true);
                    }
                }
                setTimeout(check, 500);
            })();
        },
        
        runConsoleCheck() {
            const self = this;
            const element = new Image();
            Object.defineProperty(element, 'id', {
                get: function() {
                    if (!self.isOpen) {
                        self.isOpen = true;
                        if (self.onChange) self.onChange(true);
                    }
                }
            });
            
            setInterval(() => {
                console.log(element);
                console.clear();
            }, 1000);
        }
    };

    // Initialize DevToolsDetector
    DevToolsDetector.init((isOpen) => {
        isDevToolsOpen = isOpen;
        if (isOpen) {
            showProtectionOverlay('devtools');
        } else {
            hideProtectionOverlay();
        }
    });

    // Disable keyboard shortcuts
    document.addEventListener('keydown', function(e) {
        // F12 key
        if (e.key === 'F12' || e.keyCode === 123) {
            e.preventDefault();
            return false;
        }
        
        // Inspect Shortcuts (Ctrl+Shift+I / J / C)
        if ((e.ctrlKey || e.metaKey) && e.shiftKey && 
            (e.key === 'I' || e.key === 'i' || 
             e.key === 'J' || e.key === 'j' || 
             e.key === 'C' || e.key === 'c' || 
             e.key === 'K' || e.key === 'k')) {
            e.preventDefault();
            return false;
        }
        
        // macOS Inspector Shortcuts (Cmd+Alt+I / J / C)
        if (e.metaKey && e.altKey && 
            (e.key === 'I' || e.key === 'i' || 
             e.key === 'J' || e.key === 'j' || 
             e.key === 'C' || e.key === 'c')) {
            e.preventDefault();
            return false;
        }

        // Copy & Paste
        if ((e.ctrlKey || e.metaKey) && (e.key === 'c' || e.key === 'C' || e.key === 'v' || e.key === 'V')) {
            e.preventDefault();
            return false;
        }
        
        // View Source
        if (((e.ctrlKey || e.metaKey) && (e.key === 'u' || e.key === 'U')) || 
            (e.metaKey && e.altKey && (e.key === 'u' || e.key === 'U'))) {
            e.preventDefault();
            return false;
        }
        
        // Print Screen (PrtScn)
        if (e.key === 'PrintScreen' || e.keyCode === 44 || e.code === 'PrintScreen') {
            e.preventDefault();
            triggerScreenshotWarning();
            return false;
        }
        
        // macOS screenshots
        if (e.metaKey && e.shiftKey && (e.key === '3' || e.key === '4' || e.key === '5' || e.keyCode === 51 || e.keyCode === 52 || e.keyCode === 53)) {
            e.preventDefault();
            triggerScreenshotWarning();
            return false;
        }
        
        // Snipping Tool (Win+Shift+S)
        if (e.metaKey && e.shiftKey && (e.key === 'S' || e.key === 's' || e.keyCode === 83)) {
            e.preventDefault();
            triggerScreenshotWarning();
            return false;
        }
        
        // Print / Save
        if ((e.ctrlKey || e.metaKey) && (e.key === 'p' || e.key === 'P' || e.key === 's' || e.key === 'S')) {
            e.preventDefault();
            window.location.replace('index.php');
            return false;
        }
        
        // Select All inside table
        if ((e.ctrlKey || e.metaKey) && (e.key === 'a' || e.key === 'A')) {
            if (e.target.closest('.data-table-container')) {
                e.preventDefault();
                return false;
            }
        }
    });

    // Kick to homepage when window/tab loses focus (e.g., screenshot tool opens or user switches tabs)
    window.addEventListener('blur', function() {
        window.location.replace('index.php');
    });

    // Tab visibility changes
    document.addEventListener('visibilitychange', function() {
        if (document.visibilityState === 'hidden') {
            window.location.replace('index.php');
        }
    });

    // Disable right-click context menu
    document.addEventListener('contextmenu', function(e) {
        e.preventDefault();
        return false;
    });

    // Disable text selection on data table
    const dataTable = document.querySelector('.data-table');
    if (dataTable) {
        dataTable.addEventListener('selectstart', function(e) {
            e.preventDefault();
            return false;
        });
        dataTable.style.userSelect = 'none';
        dataTable.style.webkitUserSelect = 'none';
        dataTable.style.msUserSelect = 'none';
    }

    // Disable drag on images
    document.querySelectorAll('img').forEach(img => {
        img.addEventListener('dragstart', function(e) {
            e.preventDefault();
            return false;
        });
    });

    // Security Lockdown Trigger
    function lockdown() {
        if (observer) observer.disconnect();
        document.body.innerHTML = `
            <div style="display:flex;flex-direction:column;justify-content:center;align-items:center;height:100vh;background:#111827;color:#ffffff;font-family:'Inter',sans-serif;text-align:center;padding:20px;box-sizing:border-box;">
                <div style="font-size:64px;color:#ef4444;margin-bottom:24px;"><i class="fas fa-exclamation-triangle"></i></div>
                <h1 style="font-size:28px;font-weight:700;margin-bottom:16px;text-transform:uppercase;letter-spacing:1px;">CẢNH BÁO BẢO MẬT</h1>
                <p style="font-size:16px;color:#9ca3af;max-width:500px;line-height:1.6;margin:0 0 24px 0;">Phát hiện hành vi can thiệp trái phép vào cấu trúc trang web hoặc chụp ảnh màn hình. Phiên làm việc của bạn đã bị khóa để bảo vệ dữ liệu sản phẩm.</p>
                <button onclick="window.location.reload()" style="padding:12px 28px;background:#356df1;color:#ffffff;border:none;border-radius:8px;font-size:15px;font-weight:600;cursor:pointer;transition:background 0.2s ease;box-shadow:0 4px 6px -1px rgba(53, 109, 241, 0.4);outline:none;">
                    Tải lại trang
                </button>
            </div>
        `;
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText('[Security Block]').catch(() => {});
        }
    }

    // DOM Mutation Observer to detect element tampering
    const observer = new MutationObserver((mutations) => {
        let tampered = false;
        
        // Check if overlay was deleted
        const overlayCheck = document.getElementById('protection-overlay');
        if (!overlayCheck) {
            tampered = true;
        } else {
            // Check if styles were modified to hide overlay when it should be active
            const shouldBeActive = isDevToolsOpen || isWindowBlurred || screenshotWarningActive;
            const style = window.getComputedStyle(overlayCheck);
            if (shouldBeActive && (style.display === 'none' || style.visibility === 'hidden' || parseFloat(style.opacity) === 0)) {
                tampered = true;
            }
        }

        // Check if body class was removed during active protection
        const shouldHaveClass = isDevToolsOpen || isWindowBlurred || screenshotWarningActive;
        const hasClass = document.body.classList.contains('protection-active');
        if (shouldHaveClass && !hasClass) {
            tampered = true;
        }
        
        if (tampered) {
            lockdown();
        }
    });

    observer.observe(document.body, {
        attributes: true,
        childList: true,
        subtree: true,
        attributeFilter: ['class', 'style', 'id']
    });
})();
</script>

<style>
/* QR icon styles for user page */
.store-image-cell, .qr-cell {
    text-align: center;
    vertical-align: middle;
}

.store-image-trigger, .qr-trigger {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.3s ease;
}

.store-image-trigger {
    background-color: #f3f4f6;
    color: #6b7280;
}

.store-image-trigger:hover {
    background-color: #e5e7eb;
    color: #374151;
}

.store-image-icon {
    font-size: 14px;
}

.qr-trigger {
    background-color: #f0fdf4;
    border: 1px solid #dcfce7;
}

.qr-trigger:hover {
    background-color: #dcfce7;
    border-color: #bbf7d0;
}

.qr-icon {
    font-size: 16px;
    color: #16a34a;
}
</style>