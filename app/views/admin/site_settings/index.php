<?php
/**
 * Site Settings - Logo & Favicon Management
 * Admin interface for managing site logos and favicon
 */
?>

<div class="admin-page-header">
    <div class="page-header-content">
        <h1 class="page-title">
            <i class="fas fa-image"></i>
            Quản lý Logo & Favicon
        </h1>
        <p class="page-description">Quản lý logo hiển thị trên website và favicon</p>
    </div>
</div>

<div class="admin-content-wrapper">
    <div class="logo-settings-container">
        
        <!-- User-facing Logos -->
        <div class="settings-section">
            <div class="section-header">
                <h2 class="section-title">
                    <i class="fas fa-users"></i>
                    Logo Người Dùng
                </h2>
                <p class="section-description">Logo hiển thị trên trang người dùng</p>
            </div>
            
            <div class="settings-grid">
                <!-- Header Logo -->
                <div class="setting-card">
                    <div class="setting-card-header">
                        <h3 class="setting-title">Logo Header</h3>
                        <span class="setting-badge">Header</span>
                    </div>
                    <div class="setting-card-body">
                        <div class="logo-preview">
                            <?php 
                            $headerLogo = '';
                            foreach ($GLOBALS['logoSettings'] as $setting) {
                                if ($setting['setting_key'] === 'logo_header') {
                                    $headerLogo = $setting['setting_value'];
                                    break;
                                }
                            }
                            ?>
                            <img src="<?php echo icon_url($headerLogo); ?>" alt="Header Logo" class="preview-image">
                        </div>
                        <form action="?page=admin&module=site-settings&action=update" method="POST" enctype="multipart/form-data" class="logo-upload-form">
                            <input type="hidden" name="setting_key" value="logo_header">
                            <div class="form-group">
                                <label class="file-upload-label">
                                    <i class="fas fa-cloud-upload-alt"></i>
                                    <span>Chọn file mới</span>
                                    <input type="file" name="logo_file" accept="image/*" class="file-input" onchange="previewImage(this, 'logo_header')">
                                </label>
                                <p class="file-hint">SVG, PNG, JPG, GIF, WEBP (Max 2MB)</p>
                            </div>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i>
                                Cập nhật
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Footer Logo -->
                <div class="setting-card">
                    <div class="setting-card-header">
                        <h3 class="setting-title">Logo Footer</h3>
                        <span class="setting-badge">Footer</span>
                    </div>
                    <div class="setting-card-body">
                        <div class="logo-preview">
                            <?php 
                            $footerLogo = '';
                            foreach ($GLOBALS['logoSettings'] as $setting) {
                                if ($setting['setting_key'] === 'logo_footer') {
                                    $footerLogo = $setting['setting_value'];
                                    break;
                                }
                            }
                            ?>
                            <img src="<?php echo icon_url($footerLogo); ?>" alt="Footer Logo" class="preview-image">
                        </div>
                        <form action="?page=admin&module=site-settings&action=update" method="POST" enctype="multipart/form-data" class="logo-upload-form">
                            <input type="hidden" name="setting_key" value="logo_footer">
                            <div class="form-group">
                                <label class="file-upload-label">
                                    <i class="fas fa-cloud-upload-alt"></i>
                                    <span>Chọn file mới</span>
                                    <input type="file" name="logo_file" accept="image/*" class="file-input" onchange="previewImage(this, 'logo_footer')">
                                </label>
                                <p class="file-hint">SVG, PNG, JPG, GIF, WEBP (Max 2MB)</p>
                            </div>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i>
                                Cập nhật
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Admin Logos -->
        <div class="settings-section">
            <div class="section-header">
                <h2 class="section-title">
                    <i class="fas fa-user-shield"></i>
                    Logo Admin
                </h2>
                <p class="section-description">Logo hiển thị trên trang quản trị</p>
            </div>
            
            <div class="settings-grid">
                <!-- Admin Full Logo -->
                <div class="setting-card">
                    <div class="setting-card-header">
                        <h3 class="setting-title">Logo Đầy Đủ</h3>
                        <span class="setting-badge">Sidebar Mở</span>
                    </div>
                    <div class="setting-card-body">
                        <div class="logo-preview">
                            <?php 
                            $adminFullLogo = '';
                            foreach ($GLOBALS['logoSettings'] as $setting) {
                                if ($setting['setting_key'] === 'logo_admin_full') {
                                    $adminFullLogo = $setting['setting_value'];
                                    break;
                                }
                            }
                            ?>
                            <img src="<?php echo icon_url($adminFullLogo); ?>" alt="Admin Full Logo" class="preview-image">
                        </div>
                        <form action="?page=admin&module=site-settings&action=update" method="POST" enctype="multipart/form-data" class="logo-upload-form">
                            <input type="hidden" name="setting_key" value="logo_admin_full">
                            <div class="form-group">
                                <label class="file-upload-label">
                                    <i class="fas fa-cloud-upload-alt"></i>
                                    <span>Chọn file mới</span>
                                    <input type="file" name="logo_file" accept="image/*" class="file-input" onchange="previewImage(this, 'logo_admin_full')">
                                </label>
                                <p class="file-hint">SVG, PNG, JPG, GIF, WEBP (Max 2MB)</p>
                            </div>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i>
                                Cập nhật
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Admin Mini Logo -->
                <div class="setting-card">
                    <div class="setting-card-header">
                        <h3 class="setting-title">Logo Mini</h3>
                        <span class="setting-badge">Sidebar Thu Gọn</span>
                    </div>
                    <div class="setting-card-body">
                        <div class="logo-preview logo-preview-mini">
                            <?php 
                            $adminMiniLogo = '';
                            foreach ($GLOBALS['logoSettings'] as $setting) {
                                if ($setting['setting_key'] === 'logo_admin_mini') {
                                    $adminMiniLogo = $setting['setting_value'];
                                    break;
                                }
                            }
                            ?>
                            <img src="<?php echo icon_url($adminMiniLogo); ?>" alt="Admin Mini Logo" class="preview-image">
                        </div>
                        <form action="?page=admin&module=site-settings&action=update" method="POST" enctype="multipart/form-data" class="logo-upload-form">
                            <input type="hidden" name="setting_key" value="logo_admin_mini">
                            <div class="form-group">
                                <label class="file-upload-label">
                                    <i class="fas fa-cloud-upload-alt"></i>
                                    <span>Chọn file mới</span>
                                    <input type="file" name="logo_file" accept="image/*" class="file-input" onchange="previewImage(this, 'logo_admin_mini')">
                                </label>
                                <p class="file-hint">SVG, PNG, JPG, GIF, WEBP (Max 2MB)</p>
                            </div>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i>
                                Cập nhật
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Affiliate Logos -->
        <div class="settings-section">
            <div class="section-header">
                <h2 class="section-title">
                    <i class="fas fa-store"></i>
                    Logo Affiliate
                </h2>
                <p class="section-description">Logo hiển thị trên trang đại lý</p>
            </div>
            
            <div class="settings-grid">
                <!-- Affiliate Full Logo -->
                <div class="setting-card">
                    <div class="setting-card-header">
                        <h3 class="setting-title">Logo Đầy Đủ</h3>
                        <span class="setting-badge">Sidebar Mở</span>
                    </div>
                    <div class="setting-card-body">
                        <div class="logo-preview">
                            <?php 
                            $affiliateFullLogo = '';
                            foreach ($GLOBALS['logoSettings'] as $setting) {
                                if ($setting['setting_key'] === 'logo_affiliate_full') {
                                    $affiliateFullLogo = $setting['setting_value'];
                                    break;
                                }
                            }
                            ?>
                            <img src="<?php echo icon_url($affiliateFullLogo); ?>" alt="Affiliate Full Logo" class="preview-image">
                        </div>
                        <form action="?page=admin&module=site-settings&action=update" method="POST" enctype="multipart/form-data" class="logo-upload-form">
                            <input type="hidden" name="setting_key" value="logo_affiliate_full">
                            <div class="form-group">
                                <label class="file-upload-label">
                                    <i class="fas fa-cloud-upload-alt"></i>
                                    <span>Chọn file mới</span>
                                    <input type="file" name="logo_file" accept="image/*" class="file-input" onchange="previewImage(this, 'logo_affiliate_full')">
                                </label>
                                <p class="file-hint">SVG, PNG, JPG, GIF, WEBP (Max 2MB)</p>
                            </div>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i>
                                Cập nhật
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Affiliate Mini Logo -->
                <div class="setting-card">
                    <div class="setting-card-header">
                        <h3 class="setting-title">Logo Mini</h3>
                        <span class="setting-badge">Sidebar Thu Gọn</span>
                    </div>
                    <div class="setting-card-body">
                        <div class="logo-preview logo-preview-mini">
                            <?php 
                            $affiliateMiniLogo = '';
                            foreach ($GLOBALS['logoSettings'] as $setting) {
                                if ($setting['setting_key'] === 'logo_affiliate_mini') {
                                    $affiliateMiniLogo = $setting['setting_value'];
                                    break;
                                }
                            }
                            ?>
                            <img src="<?php echo icon_url($affiliateMiniLogo); ?>" alt="Affiliate Mini Logo" class="preview-image">
                        </div>
                        <form action="?page=admin&module=site-settings&action=update" method="POST" enctype="multipart/form-data" class="logo-upload-form">
                            <input type="hidden" name="setting_key" value="logo_affiliate_mini">
                            <div class="form-group">
                                <label class="file-upload-label">
                                    <i class="fas fa-cloud-upload-alt"></i>
                                    <span>Chọn file mới</span>
                                    <input type="file" name="logo_file" accept="image/*" class="file-input" onchange="previewImage(this, 'logo_affiliate_mini')">
                                </label>
                                <p class="file-hint">SVG, PNG, JPG, GIF, WEBP (Max 2MB)</p>
                            </div>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i>
                                Cập nhật
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Favicon -->
        <div class="settings-section">
            <div class="section-header">
                <h2 class="section-title">
                    <i class="fas fa-star"></i>
                    Favicon
                </h2>
                <p class="section-description">Icon hiển thị trên tab trình duyệt</p>
            </div>
            
            <div class="settings-grid">
                <div class="setting-card">
                    <div class="setting-card-header">
                        <h3 class="setting-title">Favicon</h3>
                        <span class="setting-badge">Browser Tab</span>
                    </div>
                    <div class="setting-card-body">
                        <div class="logo-preview logo-preview-mini">
                            <?php 
                            $favicon = '';
                            foreach ($GLOBALS['logoSettings'] as $setting) {
                                if ($setting['setting_key'] === 'favicon') {
                                    $favicon = $setting['setting_value'];
                                    break;
                                }
                            }
                            ?>
                            <img src="<?php echo icon_url($favicon); ?>" alt="Favicon" class="preview-image">
                        </div>
                        <form action="?page=admin&module=site-settings&action=update" method="POST" enctype="multipart/form-data" class="logo-upload-form">
                            <input type="hidden" name="setting_key" value="favicon">
                            <div class="form-group">
                                <label class="file-upload-label">
                                    <i class="fas fa-cloud-upload-alt"></i>
                                    <span>Chọn file mới</span>
                                    <input type="file" name="logo_file" accept="image/*" class="file-input" onchange="previewImage(this, 'favicon')">
                                </label>
                                <p class="file-hint">SVG, PNG, ICO (Max 2MB, khuyến nghị 32x32px)</p>
                            </div>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i>
                                Cập nhật
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
