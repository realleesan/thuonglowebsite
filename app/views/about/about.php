<?php
/**
 * Dynamic About Page
 */
require_once __DIR__ . '/../../models/SubPageModel.php';
$subPageModel = new SubPageModel();
$pageData = $subPageModel->getByPageKey('about');

// Parse dynamic content or fallback to static HTML
$title = $pageData ? $pageData['title'] : 'Giới thiệu';
$content = $pageData ? $pageData['content'] : '';
$banner = ($pageData && !empty($pageData['image'])) ? $pageData['image'] : '';
?>

<!-- Custom Premium CSS for Dynamic Subpages -->
<style>
    .dynamic-about-hero {
        position: relative;
        padding: 90px 0;
        background: <?= !empty($banner) ? "url('$banner') no-repeat center center / cover" : "linear-gradient(135deg, #1e1b4b 0%, #312e81 100%)" ?>;
        color: white;
        text-align: center;
        margin-bottom: 50px;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.05);
    }
    .dynamic-about-hero::before {
        content: '';
        position: absolute;
        inset: 0;
        background: rgba(15, 23, 42, 0.65);
        backdrop-filter: blur(2px);
        z-index: 1;
    }
    .dynamic-about-hero .container {
        position: relative;
        z-index: 2;
    }
    .dynamic-about-hero h1 {
        font-size: 38px;
        font-weight: 800;
        margin: 0 0 16px 0;
        letter-spacing: -0.025em;
        line-height: 1.2;
    }
    .dynamic-about-hero p {
        font-size: 17px;
        color: #e2e8f0;
        max-width: 700px;
        margin: 0 auto;
        line-height: 1.6;
    }
    .dynamic-content-wrapper {
        background: white;
        border-radius: 16px;
        padding: 40px;
        box-shadow: 0 4px 20px -2px rgba(0, 0, 0, 0.05);
        border: 1px solid #f1f5f9;
        margin-bottom: 50px;
        color: #334155;
        font-size: 16px;
        line-height: 1.8;
    }
    .dynamic-content-wrapper h2 {
        font-size: 28px;
        font-weight: 700;
        color: #0f172a;
        margin-top: 30px;
        margin-bottom: 16px;
        border-bottom: 2px solid #e2e8f0;
        padding-bottom: 8px;
    }
    .dynamic-content-wrapper h3 {
        font-size: 22px;
        font-weight: 600;
        color: #1e293b;
        margin-top: 24px;
        margin-bottom: 12px;
    }
    .dynamic-content-wrapper p {
        margin-bottom: 16px;
    }
    .dynamic-content-wrapper ul, .dynamic-content-wrapper ol {
        margin-bottom: 20px;
        padding-left: 24px;
    }
    .dynamic-content-wrapper li {
        margin-bottom: 8px;
    }
    .dynamic-content-wrapper img {
        max-width: 100%;
        height: auto;
        border-radius: 12px;
        margin: 24px auto;
        display: block;
        box-shadow: 0 10px 15px -3px rgba(0,0,0,0.05);
    }
    .highlight {
        color: #3b82f6;
        font-weight: 700;
    }
</style>

<!-- Main Content -->
<div id="wrapper-container" class="wrapper-container" style="padding: 40px 0;">
    <div class="container">
        <!-- Hero Banner Section -->
        <div class="dynamic-about-hero">
            <div class="container">
                <h1><?= htmlspecialchars($title) ?></h1>
                <p>Khám phá năng lực cốt lõi của ThuongLo.com - Giải pháp tự động hóa logistics & nguồn hàng gốc</p>
            </div>
        </div>

        <!-- Render Dynamic Content -->
        <div class="dynamic-content-wrapper">
            <?php if (!empty($content)): ?>
                <?= $content ?>
            <?php else: ?>
                <!-- Fallback to original static HTML if DB is not ready -->
                <h2>ThuongLo<br><span class="highlight">Nguồn Hàng Gốc</span> &amp; <span class="highlight">Tự Động Hóa Logistics</span></h2>
                <p>Nền tảng tiên phong cung cấp các gói dữ liệu nhà cung cấp độc quyền và giải pháp vận chuyển thông minh. Chúng tôi bảo vệ tài sản thông tin của bạn bằng công nghệ mã hóa hiện đại, đồng thời tự động hóa quy trình từ thanh toán đến bàn giao dữ liệu chỉ trong vài giây.</p>
                
                <h3>Kho Nguồn Hàng Độc Quyền</h3>
                <p>Thượng Lộ cung cấp các gói dữ liệu nhà máy, xưởng sản xuất đã qua kiểm duyệt kỹ lưỡng. Là "vũ khí bí mật" giúp bạn tối ưu biên lợi nhuận ngay từ khâu nhập hàng.</p>
                
                <h3>Công Nghệ Chống Sao Chép</h3>
                <p>Hệ thống của chúng tôi tích hợp các lớp bảo mật cấp cao, ngăn chặn hành vi chia sẻ trái phép hoặc bán lại, đảm bảo lợi thế cạnh tranh độc tôn cho chủ sở hữu gói.</p>
                
                <h3>Thanh Toán &amp; Kích Hoạt Tự Động</h3>
                <p>Loại bỏ quy trình xác nhận thủ công chậm chạp. Với Thượng Lộ, ngay sau khi quét QR thanh toán, hệ thống Logistics được kích hoạt và kho dữ liệu được mở khóa tự động 100%.</p>

                <!-- Static Testimonial Design -->
                <div style="margin-top: 40px; padding: 24px; background: #f8fafc; border-left: 4px solid #3b82f6; border-radius: 8px;">
                    <div style="font-size: 32px; color: #cbd5e1; line-height: 1; font-family: Georgia, serif; margin-bottom: -10px;">"</div>
                    <p style="font-style: italic; color: #475569; font-size: 15px;">Trong thương mại điện tử, ai nắm giữ Nguồn Hàng Gốc và tối ưu được Logistics, người đó nắm giữ thị phần. Thượng Lộ ra đời để trao quyền năng đó cho bạn thông qua công nghệ tự động hóa và cơ chế bảo mật dữ liệu khắt khe nhất.</p>
                    <div style="font-weight: 600; color: #1e293b; font-size: 14px; margin-top: 8px;">— Founder ThuongLo.com</div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
