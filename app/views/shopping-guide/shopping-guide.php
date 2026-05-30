<?php
/**
 * Shopping Guide Page
 * Standardized with View Initialization System and Dynamic Subpage content
 */

// 1. Khởi tạo View an toàn & ServiceManager
require_once __DIR__ . '/../../../core/view_init.php';

require_once __DIR__ . '/../../models/SubPageModel.php';
$subPageModel = new SubPageModel();
$pageData = $subPageModel->getByPageKey('shopping_guide');

// Parse dynamic content
$title = $pageData ? $pageData['title'] : 'Hướng dẫn mua hàng';
$subtitle = $pageData && !empty($pageData['subtitle']) ? $pageData['subtitle'] : 'Quy trình đặt mua nguồn hàng & tự động Logistics tại ThuongLo nhanh chóng, bảo mật và an toàn';
$content = $pageData ? $pageData['content'] : '';
$banner = ($pageData && !empty($pageData['image'])) ? $pageData['image'] : '';
?>

<!-- Custom Premium CSS for Shopping Guide Dynamic Page -->
<style>
    .dynamic-guide-hero {
        position: relative;
        padding: 80px 0;
        background: <?= !empty($banner) ? "url('$banner') no-repeat center center / cover" : "linear-gradient(135deg, #0d9488 0%, #0f766e 100%)" ?>;
        color: white;
        text-align: center;
        margin-bottom: 40px;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
    }
    .dynamic-guide-hero::before {
        content: '';
        position: absolute;
        inset: 0;
        background: rgba(15, 23, 42, 0.65);
        backdrop-filter: blur(2px);
        z-index: 1;
    }
    .dynamic-guide-hero .container {
        position: relative;
        z-index: 2;
    }
    .dynamic-guide-hero h1 {
        font-size: 36px;
        font-weight: 800;
        margin: 0 0 12px 0;
        letter-spacing: -0.025em;
    }
    .dynamic-guide-hero p {
        font-size: 16px;
        color: #ccfbf1;
        max-width: 600px;
        margin: 0 auto;
    }
    .dynamic-guide-container {
        background: white;
        border-radius: 16px;
        padding: 40px;
        box-shadow: 0 4px 20px -2px rgba(0, 0, 0, 0.05);
        border: 1px solid #f1f5f9;
        margin-bottom: 50px;
        color: #334155;
        font-size: 15px;
        line-height: 1.8;
    }
    .dynamic-guide-container h2 {
        font-size: 24px;
        font-weight: 700;
        color: #0f172a;
        margin-top: 28px;
        margin-bottom: 16px;
        border-bottom: 2px solid #f1f5f9;
        padding-bottom: 8px;
    }
    .dynamic-guide-container h3 {
        font-size: 20px;
        font-weight: 600;
        color: #1e293b;
        margin-top: 24px;
        margin-bottom: 12px;
    }
    .dynamic-guide-container ol, .dynamic-guide-container ul {
        padding-left: 20px;
        margin-bottom: 20px;
    }
    .dynamic-guide-container li {
        margin-bottom: 8px;
    }
    .dynamic-guide-container strong {
        color: #111827;
        font-weight: 600;
    }
    .dynamic-guide-container img {
        max-width: 100%;
        height: auto;
        border-radius: 8px;
        margin: 16px auto;
        display: block;
        box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);
    }
</style>

<!-- Main Content -->
<div id="wrapper-container" class="wrapper-container" style="padding: 40px 0;">
    <div class="container">
        <!-- Hero Section -->
        <div class="dynamic-guide-hero">
            <div class="container">
                <h1><?= htmlspecialchars($title) ?></h1>
                <p><?= htmlspecialchars($subtitle) ?></p>
            </div>
        </div>

        <!-- Dynamic Guide Content -->
        <div class="dynamic-guide-container">
            <?php if (!empty($content)): ?>
                <?= $content ?>
            <?php else: ?>
                <!-- Fallback to original static HTML if DB is not ready -->
                <h2>Quy trình mua hàng nhanh tại Thuong Lo</h2>
                <p>Chào mừng quý khách đến với hệ thống Thuong Lo. Để đảm bảo mua hàng nhanh và chuẩn xác nhất, quý khách vui lòng xem qua các bước hướng dẫn chi tiết:</p>
                
                <h3>Bước 1: Tìm kiếm sản phẩm</h3>
                <p>Sử dụng thanh tìm kiếm hoặc duyệt danh mục sản phẩm, thương hiệu để chọn gói dữ liệu hoặc sản phẩm ưng ý.</p>
                
                <h3>Bước 2: Thêm vào giỏ hàng và đặt hàng</h3>
                <p>Chọn số lượng sản phẩm và nhấn nút "Thêm vào giỏ hàng" hoặc nhấp "Mua ngay" để tiến hành đặt hàng trực tiếp.</p>
                
                <h3>Bước 3: Chọn hình thức thanh toán</h3>
                <p>Điền thông tin nhận hàng chính xác và chọn hình thức thanh toán COD hoặc quét mã QR PayOS tự động.</p>
                
                <h3>Bước 4: Xác nhận và nhận bàn giao</h3>
                <p>Hệ thống tự động kích hoạt xử lý đơn hàng và bàn giao dữ liệu logistics tức thì cho quý khách.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

