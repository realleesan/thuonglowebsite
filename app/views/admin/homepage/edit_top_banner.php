<?php
/**
 * Edit Top Banner View
 */

// Ensure banner data is available
if (!isset($banner)) {
    $banner = [];
}

// Set default values
$banner = array_merge([
    'id' => 0,
    'content' => 'Chào mừng đến với ThuongLo! Nền tảng data nguồn hàng và dịch vụ thương mại xuyên biên giới hàng đầu.',
    'button_text' => 'Khám phá ngay!',
    'button_url' => '?page=products',
    'is_active' => 1
], $banner);

// Get flash messages
$error = $_SESSION['flash_error'] ?? '';
unset($_SESSION['flash_error']);
?>

<div class="hero-section-page hero-section-edit-page">
    <!-- Page Header -->
    <div class="page-header">
        <div class="page-header-left">
            <h1 class="page-title">
                <i class="fas fa-flag text-success me-2"></i>
                Chỉnh sửa Thanh thông báo đầu trang (Top Banner)
            </h1>
            <p class="page-description">Tùy chỉnh nội dung chữ chạy thông báo, tiêu đề nút liên kết, đường dẫn và trạng thái hiển thị trên toàn trang.</p>
        </div>
        <div class="page-header-right">
            <a href="?page=admin&module=homepage" class="btn-back">
                <i class="fas fa-arrow-left me-1"></i>
                Quản lý Trang chủ
            </a>
        </div>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <div class="admin-form-full">
        <div class="admin-card card border-0 shadow-sm p-4">
            <form id="topBannerForm" method="POST" action="?page=admin&module=homepage&action=update-top-banner">
                <input type="hidden" name="id" value="<?php echo $banner['id']; ?>">
                
                <div class="row">
                    <div class="col-md-8">
                        <!-- Content / Text announcement -->
                        <div class="form-group mb-4">
                            <label class="admin-label fw-bold mb-2" for="content">Nội dung thông báo <span class="text-danger">*</span></label>
                            <textarea id="content" name="content" class="form-control" rows="4" required placeholder="Nhập nội dung chữ hiển thị trên thanh thông báo đầu trang..."><?php echo htmlspecialchars($banner['content']); ?></textarea>
                            <p class="text-muted small mt-1">Nên nhập nội dung ngắn gọn dưới 150 ký tự để đảm bảo hiển thị đẹp mắt trên mọi thiết bị.</p>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <!-- Button Text -->
                                <div class="form-group mb-4">
                                    <label class="admin-label fw-bold mb-2" for="button_text">Tiêu đề nút chuyển hướng</label>
                                    <input type="text" id="button_text" name="button_text" class="form-control" value="<?php echo htmlspecialchars($banner['button_text'] ?? ''); ?>" placeholder="Ví dụ: Khám phá ngay!">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <!-- Button URL -->
                                <div class="form-group mb-4">
                                    <label class="admin-label fw-bold mb-2" for="button_url">Đường dẫn chuyển hướng (URL)</label>
                                    <input type="text" id="button_url" name="button_url" class="form-control" value="<?php echo htmlspecialchars($banner['button_url'] ?? ''); ?>" placeholder="Ví dụ: ?page=products hoặc đường dẫn tuyệt đối">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <!-- Status Toggle & Settings Card -->
                        <div class="border rounded p-4 bg-light mb-4">
                            <h6 class="fw-bold text-dark mb-3"><i class="fas fa-cog me-1"></i> Thiết lập hiển thị</h6>
                            
                            <!-- Status Toggle -->
                            <div class="form-check mb-3 form-switch">
                                <input type="checkbox" class="form-check-input" id="is_active" name="is_active" value="1" style="width: 40px; height: 20px; cursor: pointer;" <?php echo ($banner['is_active'] ? 'checked' : ''); ?>>
                                <label for="is_active" class="form-check-label fw-bold ms-2" style="cursor: pointer;">Kích hoạt hiển thị</label>
                            </div>
                            <p class="text-muted small mb-0">Khi kích hoạt, thanh thông báo màu xanh lá sẽ hiển thị ở trên cùng của header của toàn bộ trang web.</p>
                        </div>
                    </div>
                </div>

                <div class="form-actions mt-4 pt-3 border-top d-flex gap-3">
                    <button type="submit" class="btn btn-primary px-4 py-2" style="border-radius: 8px; font-weight: 600;">
                        <i class="fas fa-save me-1"></i> Lưu cấu hình
                    </button>
                    <a href="?page=admin&module=homepage" class="btn btn-secondary px-4 py-2" style="border-radius: 8px; font-weight: 600;">
                        Hủy
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.hero-section-edit-page {
    padding: 20px;
}

.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 25px;
}

.page-title {
    font-size: 1.75rem;
    font-weight: 700;
    color: #1f2937;
    margin: 0 0 5px 0;
}

.page-description {
    color: #6B7280;
    margin: 0;
}

.btn-back {
    display: inline-flex;
    align-items: center;
    background: #f3f4f6;
    color: #4b5563;
    padding: 8px 16px;
    border-radius: 8px;
    text-decoration: none;
    font-weight: 600;
    font-size: 0.875rem;
    transition: all 0.2s;
}

.btn-back:hover {
    background: #e5e7eb;
    color: #1f2937;
}
</style>
