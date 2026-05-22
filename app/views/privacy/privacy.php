<?php
/**
 * Privacy Policy Page
 * Standardized with View Initialization System and Dynamic Subpage content
 */

// 1. Khởi tạo View an toàn & ServiceManager
require_once __DIR__ . '/../../../core/view_init.php';

require_once __DIR__ . '/../../models/SubPageModel.php';
$subPageModel = new SubPageModel();
$pageData = $subPageModel->getByPageKey('privacy');

// Parse dynamic content
$title = $pageData ? $pageData['title'] : 'Chính sách bảo mật';
$content = $pageData ? $pageData['content'] : '';
$banner = ($pageData && !empty($pageData['image'])) ? $pageData['image'] : '';
?>

<!-- Custom Premium CSS for Privacy Dynamic Page -->
<style>
    .dynamic-privacy-hero {
        position: relative;
        padding: 80px 0;
        background: <?= !empty($banner) ? "url('$banner') no-repeat center center / cover" : "linear-gradient(135deg, #1e293b 0%, #0f172a 100%)" ?>;
        color: white;
        text-align: center;
        margin-bottom: 40px;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
    }
    .dynamic-privacy-hero::before {
        content: '';
        position: absolute;
        inset: 0;
        background: rgba(15, 23, 42, 0.65);
        backdrop-filter: blur(2px);
        z-index: 1;
    }
    .dynamic-privacy-hero .container {
        position: relative;
        z-index: 2;
    }
    .dynamic-privacy-hero h1 {
        font-size: 36px;
        font-weight: 800;
        margin: 0 0 12px 0;
        letter-spacing: -0.025em;
    }
    .dynamic-privacy-hero p {
        font-size: 16px;
        color: #cbd5e1;
        max-width: 600px;
        margin: 0 auto;
    }
    .dynamic-privacy-container {
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
    .dynamic-privacy-container h2 {
        font-size: 24px;
        font-weight: 700;
        color: #0f172a;
        margin-top: 28px;
        margin-bottom: 16px;
        border-bottom: 2px solid #f1f5f9;
        padding-bottom: 8px;
    }
    .dynamic-privacy-container h3 {
        font-size: 20px;
        font-weight: 600;
        color: #1e293b;
        margin-top: 24px;
        margin-bottom: 12px;
    }
    .dynamic-privacy-container ul, .dynamic-privacy-container ol {
        padding-left: 20px;
        margin-bottom: 20px;
    }
    .dynamic-privacy-container li {
        margin-bottom: 8px;
    }
    .dynamic-privacy-container strong {
        color: #111827;
        font-weight: 600;
    }
    .dynamic-privacy-container img {
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
        <div class="dynamic-privacy-hero">
            <div class="container">
                <h1><?= htmlspecialchars($title) ?></h1>
                <p>Cam kết bảo mật dữ liệu, thông tin cá nhân và tài sản thông tin tuyệt đối tại ThuongLo</p>
            </div>
        </div>

        <!-- Render Dynamic Content -->
        <div class="dynamic-privacy-container">
            <?php if (!empty($content)): ?>
                <?= $content ?>
            <?php else: ?>
                <!-- Fallback to original static HTML if DB is not ready -->
                <h2>Chính sách bảo mật thông tin cá nhân</h2>
                <p>Thuong Lo cam kết bảo vệ tuyệt đối thông tin cá nhân của người dùng. Chính sách bảo mật dưới đây làm rõ cách thức chúng tôi thu thập, sử dụng và bảo vệ thông tin của bạn:</p>
                
                <h3>1. Thu thập thông tin</h3>
                <p>Chúng tôi thu thập thông tin khi bạn đăng ký tài khoản, đặt mua gói dữ liệu hoặc đăng ký làm đại lý (gồm Tên, Email, Số điện thoại và thông tin thanh toán phục vụ rút tiền qua PayOS).</p>
                
                <h3>2. Sử dụng thông tin</h3>
                <p>Thông tin thu thập được sử dụng để xử lý đơn hàng, gửi thông báo kích hoạt, hỗ trợ xử lý giao nhận logistics, và gửi ưu đãi khuyến mãi định kỳ (nếu bạn đồng ý nhận).</p>
                
                <h3>3. Bảo mật dữ liệu</h3>
                <p>Chúng tôi sử dụng giao thức mã hóa dữ liệu SSL bảo mật cao và lưu trữ dữ liệu trên máy chủ an toàn. Cam kết không chia sẻ, mua bán thông tin cá nhân của bạn cho bên thứ ba dưới bất kỳ hình thức nào.</p>
                
                <hr style="border: 0; border-top: 1px solid #f1f5f9; margin: 30px 0;">
                <p>Nếu bạn cho rằng quyền riêng tư bị vi phạm:</p>
                <ul>
                    <li>Liên hệ ngay với bộ phận bảo mật của chúng tôi</li>
                    <li>Cung cấp bằng chứng và mô tả chi tiết</li>
                    <li>Chúng tôi sẽ điều tra trong vòng 7 ngày làm việc</li>
                    <li>Nếu không hài lòng, bạn có thể khiếu nại lên Cục An toàn thông tin</li>
                </ul>
            <?php endif; ?>
        </div>
    </div>
</div>
