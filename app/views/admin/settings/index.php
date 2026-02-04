<?php
// Settings page for admin
?>

<div class="admin-settings">
    <div class="page-header">
        <h2>Cài đặt hệ thống</h2>
    </div>

    <div class="settings-grid">
        <!-- General Settings -->
        <div class="settings-card">
            <div class="card-header">
                <h3>Cài đặt chung</h3>
            </div>
            <div class="card-body">
                <form class="settings-form">
                    <div class="form-group">
                        <label>Tên website</label>
                        <input type="text" class="form-control" value="Thuong Lo" />
                    </div>
                    <div class="form-group">
                        <label>Mô tả website</label>
                        <textarea class="form-control" rows="3">Nền tảng học trực tuyến hàng đầu</textarea>
                    </div>
                    <div class="form-group">
                        <label>Email liên hệ</label>
                        <input type="email" class="form-control" value="contact@thuonglo.com" />
                    </div>
                    <button type="submit" class="btn btn-primary">Lưu thay đổi</button>
                </form>
            </div>
        </div>

        <!-- System Info -->
        <div class="settings-card">
            <div class="card-header">
                <h3>Thông tin hệ thống</h3>
            </div>
            <div class="card-body">
                <div class="system-info">
                    <div class="info-item">
                        <strong>Phiên bản PHP:</strong>
                        <span><?php echo PHP_VERSION; ?></span>
                    </div>
                    <div class="info-item">
                        <strong>Phiên bản hệ thống:</strong>
                        <span>1.0.0</span>
                    </div>
                    <div class="info-item">
                        <strong>Dung lượng sử dụng:</strong>
                        <span>125 MB</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Backup & Maintenance -->
        <div class="settings-card">
            <div class="card-header">
                <h3>Sao lưu & Bảo trì</h3>
            </div>
            <div class="card-body">
                <div class="maintenance-actions">
                    <button class="btn btn-outline-primary">
                        <i class="fas fa-download"></i>
                        Sao lưu dữ liệu
                    </button>
                    <button class="btn btn-outline-warning">
                        <i class="fas fa-tools"></i>
                        Chế độ bảo trì
                    </button>
                    <button class="btn btn-outline-danger">
                        <i class="fas fa-trash"></i>
                        Xóa cache
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>