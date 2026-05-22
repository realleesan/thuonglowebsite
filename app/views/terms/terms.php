<?php
/**
 * Terms of Service Page
 * Standardized with View Initialization System and Dynamic Subpage content
 */

// 1. Khởi tạo View an toàn & ServiceManager
require_once __DIR__ . '/../../../core/view_init.php';

require_once __DIR__ . '/../../models/SubPageModel.php';
$subPageModel = new SubPageModel();
$pageData = $subPageModel->getByPageKey('terms');

// Parse dynamic content
$title = $pageData ? $pageData['title'] : 'Điều khoản dịch vụ';
$content = $pageData ? $pageData['content'] : '';
$banner = ($pageData && !empty($pageData['image'])) ? $pageData['image'] : '';
?>

<!-- Custom Premium CSS for Terms Dynamic Page -->
<style>
    .dynamic-terms-hero {
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
    .dynamic-terms-hero::before {
        content: '';
        position: absolute;
        inset: 0;
        background: rgba(15, 23, 42, 0.65);
        backdrop-filter: blur(2px);
        z-index: 1;
    }
    .dynamic-terms-hero .container {
        position: relative;
        z-index: 2;
    }
    .dynamic-terms-hero h1 {
        font-size: 36px;
        font-weight: 800;
        margin: 0 0 12px 0;
        letter-spacing: -0.025em;
    }
    .dynamic-terms-hero p {
        font-size: 16px;
        color: #cbd5e1;
        max-width: 600px;
        margin: 0 auto;
    }
    .dynamic-terms-container {
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
    .dynamic-terms-container h2 {
        font-size: 24px;
        font-weight: 700;
        color: #0f172a;
        margin-top: 28px;
        margin-bottom: 16px;
        border-bottom: 2px solid #f1f5f9;
        padding-bottom: 8px;
    }
    .dynamic-terms-container h3 {
        font-size: 20px;
        font-weight: 600;
        color: #1e293b;
        margin-top: 24px;
        margin-bottom: 12px;
    }
    .dynamic-terms-container ul, .dynamic-terms-container ol {
        padding-left: 20px;
        margin-bottom: 20px;
    }
    .dynamic-terms-container li {
        margin-bottom: 8px;
    }
    .dynamic-terms-container strong {
        color: #111827;
        font-weight: 600;
    }
    .dynamic-terms-container img {
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
        <div class="dynamic-terms-hero">
            <div class="container">
                <h1><?= htmlspecialchars($title) ?></h1>
                <p>Điều khoản dịch vụ và quy chế hoạt động chính thức của hệ thống ThuongLo</p>
            </div>
        </div>

        <!-- Render Dynamic Content -->
        <div class="dynamic-terms-container">
            <?php if (!empty($content)): ?>
                <?= $content ?>
            <?php else: ?>
                <!-- Fallback to original static HTML if DB is not ready -->
                <h2>Điều khoản dịch vụ và chính sách sử dụng</h2>
                <p>Chào mừng bạn đến với hệ thống ThuongLo.com. Khi bạn truy cập, đăng ký tài khoản hoặc sử dụng dịch vụ của chúng tôi, đồng nghĩa với việc bạn đồng ý tuân thủ các điều khoản dịch vụ dưới đây:</p>
                
                <h3>1. Tài khoản Người dùng</h3>
                <p>Bạn chịu trách nhiệm bảo mật tài khoản và mật khẩu của mình. Mọi hoạt động phát sinh dưới tài khoản của bạn sẽ thuộc trách nhiệm cá nhân của bạn.</p>
                
                <h3>2. Sở hữu trí tuệ</h3>
                <p>Tất cả nội dung, gói dữ liệu nhà cung cấp, hình ảnh, mã nguồn và hệ thống tự động hóa thuộc quyền sở hữu trí tuệ độc quyền của ThuongLo. Nghiêm cấm mọi hành vi sao chép, phân phối hoặc bán lại khi chưa được sự đồng ý bằng văn bản của ban quản trị.</p>
                
                <h3>3. Giới hạn trách nhiệm</h3>
                <p>Chúng tôi luôn nỗ lực đảm bảo độ chính xác cao nhất của thông tin, tuy nhiên không chịu trách nhiệm trước bất kỳ tổn thất gián tiếp nào phát sinh do quá trình sử dụng dữ liệu.</p>
                
                <hr style="border: 0; border-top: 1px solid #f1f5f9; margin: 30px 0;">
                <p>Nếu có câu hỏi về điều khoản dịch vụ, vui lòng liên hệ:</p>
                <ul>
                    <li>Email: support@thuonglo.com</li>
                    <li>Hotline: 1900-1234</li>
                    <li>Địa chỉ: 123 Nguyễn Huệ, Quận 1, TP.HCM</li>
                    <li>Thời gian: 8:00 - 22:00 hàng ngày</li>
                </ul>
            <?php endif; ?>
        </div>
    </div>
</div>
