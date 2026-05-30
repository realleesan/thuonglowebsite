<?php
/**
 * FAQ Page
 * Standardized with View Initialization System and Dynamic Subpage content
 */

// 1. Khởi tạo View an toàn & ServiceManager
require_once __DIR__ . '/../../../core/view_init.php';

require_once __DIR__ . '/../../models/SubPageModel.php';
$subPageModel = new SubPageModel();
$pageData = $subPageModel->getByPageKey('faq');

// Parse dynamic content
$title = $pageData ? $pageData['title'] : 'Câu hỏi thường gặp';
$subtitle = $pageData && !empty($pageData['subtitle']) ? $pageData['subtitle'] : 'Tổng hợp các thắc mắc thường gặp về Logistics, Đơn hàng và Thanh toán tại ThuongLo';
$content = $pageData ? $pageData['content'] : '';
$banner = ($pageData && !empty($pageData['image'])) ? $pageData['image'] : '';
?>

<!-- Custom Premium CSS for FAQ Dynamic Page -->
<style>
    .dynamic-faq-hero {
        position: relative;
        padding: 80px 0;
        background: <?= !empty($banner) ? "url('$banner') no-repeat center center / cover" : "linear-gradient(135deg, #0f172a 0%, #1e293b 100%)" ?>;
        color: white;
        text-align: center;
        margin-bottom: 40px;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
    }
    .dynamic-faq-hero::before {
        content: '';
        position: absolute;
        inset: 0;
        background: rgba(15, 23, 42, 0.65);
        backdrop-filter: blur(2px);
        z-index: 1;
    }
    .dynamic-faq-hero .container {
        position: relative;
        z-index: 2;
    }
    .dynamic-faq-hero h1 {
        font-size: 36px;
        font-weight: 800;
        margin: 0 0 12px 0;
        letter-spacing: -0.025em;
    }
    .dynamic-faq-hero p {
        font-size: 16px;
        color: #94a3b8;
        max-width: 600px;
        margin: 0 auto;
    }
    .dynamic-faq-container {
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
    .dynamic-faq-container h3 {
        font-size: 20px;
        font-weight: 700;
        color: #0f172a;
        margin-top: 28px;
        margin-bottom: 14px;
        border-bottom: 2px solid #f1f5f9;
        padding-bottom: 8px;
    }
    .dynamic-faq-container p {
        margin-bottom: 16px;
    }
    .dynamic-faq-container strong {
        color: #1e293b;
        font-weight: 600;
        font-size: 16px;
    }
    .dynamic-faq-container img {
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
        <div class="dynamic-faq-hero">
            <div class="container">
                <h1><?= htmlspecialchars($title) ?></h1>
                <p><?= htmlspecialchars($subtitle) ?></p>
            </div>
        </div>

        <!-- Render Dynamic Content -->
        <div class="dynamic-faq-container">
            <?php if (!empty($content)): ?>
                <?= $content ?>
            <?php else: ?>
                <!-- Fallback to original static HTML if DB is not ready -->
                <h3>Đơn hàng &amp; Thanh toán</h3>
                <p><strong>Làm thế nào để đặt hàng trên ThuongLo?</strong><br>Bạn có thể đặt hàng trực tiếp trên website bằng cách chọn sản phẩm, thêm vào giỏ hàng và tiến hành thanh toán. Chúng tôi hỗ trợ nhiều hình thức thanh toán tiện lợi.</p>
                <p><strong>Các phương thức thanh toán nào được chấp nhận?</strong><br>Chúng tôi chấp nhận thanh toán khi nhận hàng (COD), chuyển khoản ngân hàng, ví điện tử và cổng thanh toán tự động PayOS.</p>
                
                <h3>Giao hàng &amp; Đổi trả</h3>
                <p><strong>Thời gian giao hàng bao lâu?</strong><br>Thời gian giao hàng nội thành 2-3 ngày, các tỉnh khác từ 3-5 ngày làm việc.</p>
                <p><strong>Chính sách đổi trả như thế nào?</strong><br>Bạn có thể đổi trả sản phẩm trong vòng 30 ngày nếu sản phẩm còn nguyên tem mác, chưa qua sử dụng và kèm hóa đơn mua hàng.</p>
            <?php endif; ?>
        </div>
    </div>
</div>
