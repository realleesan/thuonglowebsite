<?php
/**
 * Agent Dynamic Page View
 * Displays dynamic contents (program, guide, policies, resources) for agents
 */

// If $pageData is not set, we cannot show anything
if (!isset($pageData) || !$pageData) {
    echo '<div class="container" style="padding: 80px 20px; text-align: center;">';
    echo '<h2>Không tìm thấy trang nội dung yêu cầu</h2>';
    echo '<p><a href="' . base_url() . '" class="btn btn-primary">Quay lại trang chủ</a></p>';
    echo '</div>';
    return;
}

$pageTitle = $pageData['title'];
$pageSubtitle = !empty($pageData['subtitle']) ? $pageData['subtitle'] : 'Cùng Thuong Lo phát triển sự nghiệp kinh doanh bền vững';
$pageContent = $pageData['content'];
$pageBanner = $pageData['image'];

// Set default breadcrumbs
$breadcrumbs = [
    ['text' => 'Trang chủ', 'url' => base_url()],
    ['text' => 'Đại lý', 'url' => '?page=agent'],
    ['text' => $pageTitle, 'url' => '#', 'active' => true]
];
?>

<div class="agent-dynamic-page" style="background: #f8fafc; padding-bottom: 60px; font-family: 'Inter', sans-serif;">
    <!-- Page Breadcrumb Section -->
    <div class="breadcrumb-container" style="background: white; border-bottom: 1px solid #e2e8f0; padding: 14px 0;">
        <div class="container">
            <nav class="agent-breadcrumb" style="display: flex; gap: 8px; font-size: 14px; color: #64748b; align-items: center;">
                <a href="<?= base_url() ?>" style="color: #64748b; text-decoration: none; transition: color 0.2s;"><i class="fas fa-home" style="margin-right: 4px;"></i> Trang chủ</a>
                <i class="fas fa-chevron-right" style="font-size: 10px; color: #94a3b8;"></i>
                <span style="color: #64748b;">Đại lý</span>
                <i class="fas fa-chevron-right" style="font-size: 10px; color: #94a3b8;"></i>
                <span style="color: #1e293b; font-weight: 550;"><?= htmlspecialchars($pageTitle) ?></span>
            </nav>
        </div>
    </div>

    <!-- Hero Banner Section -->
    <?php if (!empty($pageBanner)): ?>
        <!-- Custom image banner -->
        <div class="agent-hero-banner" style="position: relative; height: 350px; background: url('<?= htmlspecialchars($pageBanner) ?>') no-repeat center center; background-size: cover; display: flex; align-items: center; justify-content: center;">
            <div class="banner-overlay" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: linear-gradient(180deg, rgba(15,23,42,0.3) 0%, rgba(15,23,42,0.7) 100%);"></div>
            <div class="container" style="position: relative; z-index: 2; text-align: center;">
                <h1 style="color: white; font-size: 2.8rem; font-weight: 700; text-shadow: 0 4px 6px rgba(0,0,0,0.3); margin: 0; letter-spacing: -0.5px; line-height: 1.2;">
                    <?= htmlspecialchars($pageTitle) ?>
                </h1>
                <p style="color: rgba(255,255,255,0.9); font-size: 1.1rem; margin-top: 12px; font-weight: 400; text-shadow: 0 2px 4px rgba(0,0,0,0.2);">
                    <?= htmlspecialchars($pageSubtitle) ?>
                </p>
            </div>
        </div>
    <?php else: ?>
        <!-- Premium CSS gradient banner if no image is uploaded -->
        <div class="agent-hero-banner" style="background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%); padding: 80px 0; text-align: center; color: white; position: relative; overflow: hidden;">
            <div class="decorative-circle-1" style="position: absolute; top: -100px; right: -100px; width: 300px; height: 300px; border-radius: 50%; background: rgba(255, 255, 255, 0.05);"></div>
            <div class="decorative-circle-2" style="position: absolute; bottom: -80px; left: -80px; width: 200px; height: 200px; border-radius: 50%; background: rgba(255, 255, 255, 0.05);"></div>
            <div class="container" style="position: relative; z-index: 2;">
                <h1 style="font-size: 2.6rem; font-weight: 800; margin: 0; letter-spacing: -0.5px; line-height: 1.2;">
                    <?= htmlspecialchars($pageTitle) ?>
                </h1>
                <p style="color: rgba(255, 255, 255, 0.85); font-size: 1.1rem; margin-top: 12px; font-weight: 400; max-width: 600px; margin-left: auto; margin-right: auto;">
                    <?= htmlspecialchars($pageSubtitle) ?>
                </p>
            </div>
        </div>
    <?php endif; ?>

    <!-- Main Content Body -->
    <div class="container" style="margin-top: 40px;">
        <div class="content-wrapper" style="background: white; border-radius: 16px; padding: 40px; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05), 0 4px 6px -4px rgba(0, 0, 0, 0.05); border: 1px solid #f1f5f9;">
            
            <!-- Safe dynamic content rendering from rich text editor -->
            <div class="dynamic-page-content" style="color: #334155; font-size: 1.05rem; line-height: 1.8;">
                <?= $pageContent ?>
            </div>
            
        </div>

        <!-- Call To Action (Affiliate Program Registration) -->
        <div class="agent-cta-box" style="margin-top: 40px; background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); border-radius: 16px; padding: 40px; text-align: center; color: white; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1);">
            <h3 style="font-size: 1.6rem; font-weight: 700; margin-top: 0; margin-bottom: 12px;">Bạn đã sẵn sàng đồng hành cùng Thuong Lo chưa?</h3>
            <p style="color: #94a3b8; max-width: 600px; margin-left: auto; margin-right: auto; margin-bottom: 24px; font-size: 1rem; line-height: 1.5;">
                Đăng ký trở thành Đại lý ngay để nhận mức hoa hồng cực khủng, sự hỗ trợ truyền thông chuyên nghiệp và tài nguyên tiếp thị dồi dào từ chúng tôi.
            </p>
            <div style="display: flex; gap: 16px; justify-content: center; flex-wrap: wrap;">
                <a href="?page=agent" class="btn btn-primary" style="background: #356df1; border-color: #356df1; color: white; padding: 12px 30px; font-weight: 600; border-radius: 8px; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; transition: transform 0.2s, background 0.2s;">
                    <i class="fas fa-user-plus"></i> Đăng Ký Ngay
                </a>
                <a href="?page=agent-page&key=chuong_trinh" class="btn btn-outline" style="border: 1px solid rgba(255,255,255,0.2); background: transparent; color: white; padding: 12px 30px; font-weight: 600; border-radius: 8px; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; transition: background 0.2s;">
                    <i class="fas fa-info-circle"></i> Tìm hiểu thêm
                </a>
            </div>
        </div>
    </div>
</div>

<style>
/* CSS styles for premium content rendering inside dynamic editor content */
.dynamic-page-content h2 {
    font-size: 1.8rem;
    font-weight: 700;
    color: #0f172a;
    margin-top: 32px;
    margin-bottom: 16px;
    border-left: 4px solid #356df1;
    padding-left: 12px;
}
.dynamic-page-content h3 {
    font-size: 1.4rem;
    font-weight: 600;
    color: #1e293b;
    margin-top: 24px;
    margin-bottom: 12px;
}
.dynamic-page-content p {
    margin-bottom: 20px;
}
.dynamic-page-content img {
    max-width: 100%;
    height: auto;
    border-radius: 12px;
    margin: 24px auto;
    display: block;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -2px rgba(0, 0, 0, 0.05);
}
.dynamic-page-content ul, .dynamic-page-content ol {
    margin-bottom: 20px;
    padding-left: 24px;
}
.dynamic-page-content li {
    margin-bottom: 8px;
}
.agent-cta-box .btn:hover {
    transform: translateY(-2px);
}
.agent-cta-box .btn-primary:hover {
    background: #2557c7 !important;
}
.agent-cta-box .btn-outline:hover {
    background: rgba(255, 255, 255, 0.1) !important;
}
</style>
