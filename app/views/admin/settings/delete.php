<?php
// Load models
require_once __DIR__ . '/../../../models/SettingsModel.php';

$settingsModel = new SettingsModel();

// Get setting key from URL
$setting_key = $_GET['key'] ?? '';

// Find the setting from database
$setting = $settingsModel->getByKey($setting_key);

// If setting not found, redirect
if (!$setting) {
    header('Location: ?page=admin&module=settings&error=not_found');
    exit;
}

// Check if setting is critical (cannot be deleted)
$critical_settings = ['site_name', 'contact_email', 'contact_phone'];
$is_critical = in_array($setting['key'], $critical_settings);
$can_delete = !$is_critical;

// Handle deletion
$success = false;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_delete'])) {
    if (!$can_delete) {
        $error = 'Không thể xóa cài đặt này vì nó là cài đặt quan trọng của hệ thống.';
    } else {
        // Demo: simulate deletion
        $success = true;
        // In real app: delete from database
        // header('Location: ?page=admin&module=settings&success=deleted');
        // exit;
    }
}

// Format functions
function formatSettingType($type) {
    $types = [
        'text' => 'Văn bản',
        'textarea' => 'Văn bản dài',
        'email' => 'Email',
        'url' => 'URL',
        'number' => 'Số',
        'boolean' => 'Đúng/Sai',
        'select' => 'Lựa chọn',
        'file' => 'Tệp tin'
    ];
    return $types[$type] ?? ucfirst($type);
}

function getTypeIcon($type) {
    $icons = [
        'text' => 'fas fa-font',
        'textarea' => 'fas fa-align-left',
        'email' => 'fas fa-envelope',
        'url' => 'fas fa-link',
        'number' => 'fas fa-hashtag',
        'boolean' => 'fas fa-toggle-on',
        'select' => 'fas fa-list',
        'file' => 'fas fa-file'
    ];
    return $icons[$type] ?? 'fas fa-cog';
}
?>

<div class="settings-page settings-delete-page">
    <!-- Page Header -->
    <div class="page-header">
        <div class="page-header-left">
            <h1 class="page-title">
                <i class="fas fa-trash"></i>
                Xóa Cài Đặt
            </h1>
            <p class="page-description">Xác nhận xóa cài đặt khỏi hệ thống</p>
        </div>
        <div class="page-header-right">
            <a href="?page=admin&module=settings&action=view&key=<?= urlencode($setting['key']) ?>" class="btn btn-info">
                <i class="fas fa-eye"></i>
                Xem chi tiết
            </a>
            <a href="?page=admin&module=settings" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i>
                Quay lại danh sách
            </a>
        </div>
    </div>

    <!-- Success/Error Messages -->
    <?php if ($success): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i>
            <div>
                <h4>Xóa cài đặt thành công!</h4>
                <p>Cài đặt "<?= htmlspecialchars($setting['key']) ?>" đã được xóa khỏi hệ thống. (Demo - dữ liệu không được xóa thật)</p>
                <div class="alert-actions">
                    <a href="?page=admin&module=settings" class="btn btn-primary">
                        <i class="fas fa-list"></i>
                        Quay lại danh sách cài đặt
                    </a>
                </div>
            </div>
        </div>
    <?php else: ?>
        
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-triangle"></i>
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <!-- Setting Information -->
        <div class="delete-confirmation-container">
            <div class="setting-summary">
                <div class="setting-summary-icon">
                    <i class="<?= getTypeIcon($setting['type']) ?>"></i>
                </div>
                <div class="setting-summary-info">
                    <h3><?= htmlspecialchars($setting['key']) ?></h3>
                    <div class="setting-meta">
                        <p><strong>Mô tả:</strong> <?= htmlspecialchars($setting['description']) ?></p>
                        <p><strong>Loại:</strong> 
                            <span class="type-badge type-<?= $setting['type'] ?>">
                                <i class="<?= getTypeIcon($setting['type']) ?>"></i>
                                <?= formatSettingType($setting['type']) ?>
                            </span>
                        </p>
                        <p><strong>Giá trị hiện tại:</strong> 
                            <code><?= htmlspecialchars($setting['value']) ?></code>
                        </p>
                        <?php if ($is_critical): ?>
                            <p><strong>Trạng thái:</strong> 
                                <span class="badge badge-danger">Cài đặt quan trọng</span>
                            </p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Warning Section -->
            <div class="warning-section">
                <div class="warning-header">
                    <i class="fas fa-exclamation-triangle"></i>
                    <h4>Cảnh báo quan trọng</h4>
                </div>
                
                <?php if ($is_critical): ?>
                    <div class="warning-content danger">
                        <h5>Không thể xóa cài đặt này!</h5>
                        <p>Cài đặt "<strong><?= htmlspecialchars($setting['key']) ?></strong>" là một cài đặt quan trọng của hệ thống. 
                           Việc xóa nó có thể làm hỏng website hoặc gây ra lỗi nghiêm trọng.</p>
                        
                        <div class="critical-settings-info">
                            <h6>Các cài đặt quan trọng không thể xóa:</h6>
                            <ul>
                                <li><code>site_name</code> - Tên website</li>
                                <li><code>contact_email</code> - Email liên hệ chính</li>
                                <li><code>contact_phone</code> - Số điện thoại liên hệ</li>
                            </ul>
                        </div>

                        <div class="alternative-actions">
                            <h6>Thay vào đó, bạn có thể:</h6>
                            <ul>
                                <li>Chỉnh sửa giá trị của cài đặt</li>
                                <li>Cập nhật mô tả để rõ ràng hơn</li>
                                <li>Thay đổi loại dữ liệu nếu cần</li>
                            </ul>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="warning-content">
                        <h5>Bạn sắp xóa cài đặt này!</h5>
                        <p>Hành động này sẽ:</p>
                        <ul>
                            <li><strong>Xóa vĩnh viễn</strong> cài đặt khỏi hệ thống</li>
                            <li><strong>Xóa tất cả</strong> thông tin liên quan (key, value, description...)</li>
                            <li><strong>Không thể hoàn tác</strong> sau khi thực hiện</li>
                            <li><strong>Có thể ảnh hưởng</strong> đến các tính năng sử dụng cài đặt này</li>
                        </ul>
                        <p class="text-warning"><strong>Lưu ý:</strong> Hãy chắc chắn rằng không có code nào đang sử dụng cài đặt này.</p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Impact Analysis -->
            <div class="impact-section">
                <h4>Phân Tích Tác Động</h4>
                <div class="impact-grid">
                    <div class="impact-item">
                        <div class="impact-icon">
                            <i class="fas fa-code"></i>
                        </div>
                        <div class="impact-content">
                            <h5>Tác động đến Code</h5>
                            <p>Các đoạn code sử dụng <code>getSetting('<?= $setting['key'] ?>')</code> sẽ trả về giá trị null hoặc lỗi.</p>
                        </div>
                    </div>
                    
                    <div class="impact-item">
                        <div class="impact-icon">
                            <i class="fas fa-desktop"></i>
                        </div>
                        <div class="impact-content">
                            <h5>Tác động đến Giao diện</h5>
                            <p>Các phần giao diện hiển thị cài đặt này có thể bị lỗi hoặc hiển thị trống.</p>
                        </div>
                    </div>
                    
                    <div class="impact-item">
                        <div class="impact-icon">
                            <i class="fas fa-database"></i>
                        </div>
                        <div class="impact-content">
                            <h5>Tác động đến Dữ liệu</h5>
                            <p>Dữ liệu cài đặt sẽ bị mất vĩnh viễn và không thể khôi phục.</p>
                        </div>
                    </div>
                    
                    <div class="impact-item">
                        <div class="impact-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="impact-content">
                            <h5>Tác động đến Người dùng</h5>
                            <p>Trải nghiệm người dùng có thể bị ảnh hưởng nếu cài đặt này quan trọng.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="delete-actions">
                <?php if ($can_delete): ?>
                    <form method="POST" class="delete-form">
                        <div class="confirmation-checkbox">
                            <label>
                                <input type="checkbox" id="confirm-checkbox" required>
                                Tôi hiểu rằng hành động này không thể hoàn tác và đồng ý xóa cài đặt này
                            </label>
                        </div>
                        
                        <div class="confirmation-checkbox">
                            <label>
                                <input type="checkbox" id="impact-checkbox" required>
                                Tôi đã kiểm tra và chắc chắn không có code nào đang sử dụng cài đặt này
                            </label>
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" name="confirm_delete" class="btn btn-danger" id="delete-btn" disabled>
                                <i class="fas fa-trash"></i>
                                Xác nhận xóa cài đặt
                            </button>
                            <a href="?page=admin&module=settings&action=edit&key=<?= urlencode($setting['key']) ?>" class="btn btn-warning">
                                <i class="fas fa-edit"></i>
                                Chỉnh sửa thay vì xóa
                            </a>
                            <a href="?page=admin&module=settings" class="btn btn-secondary">
                                <i class="fas fa-times"></i>
                                Hủy bỏ
                            </a>
                        </div>
                    </form>
                <?php else: ?>
                    <div class="form-actions">
                        <a href="?page=admin&module=settings&action=edit&key=<?= urlencode($setting['key']) ?>" class="btn btn-warning">
                            <i class="fas fa-edit"></i>
                            Chỉnh sửa cài đặt
                        </a>
                        <a href="?page=admin&module=settings&action=add" class="btn btn-success">
                            <i class="fas fa-plus"></i>
                            Tạo cài đặt mới
                        </a>
                        <a href="?page=admin&module=settings" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i>
                            Quay lại danh sách
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>

    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const confirmCheckbox = document.getElementById('confirm-checkbox');
    const impactCheckbox = document.getElementById('impact-checkbox');
    const deleteBtn = document.getElementById('delete-btn');
    
    if (confirmCheckbox && impactCheckbox && deleteBtn) {
        function updateDeleteButton() {
            deleteBtn.disabled = !(confirmCheckbox.checked && impactCheckbox.checked);
        }
        
        confirmCheckbox.addEventListener('change', updateDeleteButton);
        impactCheckbox.addEventListener('change', updateDeleteButton);
        
        // Double confirmation on submit
        deleteBtn.addEventListener('click', function(e) {
            if (!confirm('Bạn có THỰC SỰ chắc chắn muốn xóa cài đặt này? Hành động này KHÔNG THỂ hoàn tác!')) {
                e.preventDefault();
                return false;
            }
        });
    }
});
</script>