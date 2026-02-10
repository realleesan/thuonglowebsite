<?php
// Load Models
require_once __DIR__ . '/../../models/AffiliateModel.php';
require_once __DIR__ . '/../../models/UsersModel.php';

$affiliateModel = new AffiliateModel();
$usersModel = new UsersModel();

// Get affiliate ID from URL
$affiliate_id = (int)($_GET['id'] ?? 0);

// Get affiliate from database
$affiliate = $affiliateModel->getById($affiliate_id);

// If affiliate not found, redirect
if (!$affiliate) {
    header('Location: ?page=admin&module=affiliates&error=not_found');
    exit;
}

// Get user info
$user = $usersModel->getById($affiliate['user_id']);

// Handle form submission (demo)
$success = false;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $confirm = $_POST['confirm'] ?? '';
    
    if ($confirm === 'DELETE') {
        // Delete from database
        if ($affiliateModel->delete($affiliate_id)) {
            $success = true;
            header('Location: ?page=admin&module=affiliates&success=deleted');
            exit;
        } else {
            $error = 'Có lỗi xảy ra khi xóa đại lý';
        }
    } else {
        $error = 'Vui lòng nhập "DELETE" để xác nhận xóa';
    }
}

// Format price function
function formatPrice($price) {
    return number_format($price, 0, ',', '.') . ' VNĐ';
}

// Format date function
function formatDate($date) {
    return date('d/m/Y H:i', strtotime($date));
}
?>

<div class="affiliates-page affiliates-delete-page">
    <!-- Page Header -->
    <div class="page-header">
        <div class="page-header-left">
            <h1 class="page-title">
                <i class="fas fa-trash"></i>
                Xóa Đại Lý
            </h1>
            <p class="page-description">Xác nhận xóa đại lý khỏi hệ thống</p>
        </div>
        <div class="page-header-right">
            <a href="?page=admin&module=affiliates&action=view&id=<?= $affiliate_id ?>" class="btn btn-info">
                <i class="fas fa-eye"></i>
                Xem chi tiết
            </a>
            <a href="?page=admin&module=affiliates" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i>
                Quay lại danh sách
            </a>
        </div>
    </div>

    <!-- Success/Error Messages -->
    <?php if ($success): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i>
            Xóa đại lý thành công!
            <div class="alert-actions">
                <a href="?page=admin&module=affiliates" class="btn btn-sm btn-primary">
                    Quay lại danh sách
                </a>
            </div>
        </div>
    <?php endif; ?>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-triangle"></i>
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <?php if (!$success): ?>
        <!-- Delete Confirmation -->
        <div class="delete-confirmation-container">
            <div class="warning-card">
                <div class="warning-header">
                    <i class="fas fa-exclamation-triangle"></i>
                    <h3>Cảnh Báo: Hành Động Không Thể Hoàn Tác</h3>
                </div>
                <div class="warning-body">
                    <p>Bạn đang chuẩn bị xóa đại lý sau khỏi hệ thống:</p>
                </div>
            </div>

            <!-- Affiliate Info -->
            <div class="affiliate-info-card">
                <div class="card-header">
                    <h3>Thông Tin Đại Lý Sẽ Bị Xóa</h3>
                </div>
                <div class="card-body">
                    <div class="affiliate-summary">
                        <div class="affiliate-avatar">
                            <i class="fas fa-user-circle"></i>
                        </div>
                        <div class="affiliate-details">
                            <h4><?= htmlspecialchars($user['name'] ?? 'N/A') ?></h4>
                            <p><i class="fas fa-envelope"></i> <?= htmlspecialchars($user['email'] ?? 'N/A') ?></p>
                            <p><i class="fas fa-phone"></i> <?= htmlspecialchars($user['phone'] ?? 'N/A') ?></p>
                            <p><i class="fas fa-id-badge"></i> Mã giới thiệu: <strong><?= htmlspecialchars($affiliate['referral_code']) ?></strong></p>
                        </div>
                        <div class="affiliate-stats">
                            <div class="stat-item">
                                <label>Tổng doanh số:</label>
                                <span><?= formatPrice($affiliate['total_sales']) ?></span>
                            </div>
                            <div class="stat-item">
                                <label>Tổng hoa hồng:</label>
                                <span><?= formatPrice($affiliate['total_commission']) ?></span>
                            </div>
                            <div class="stat-item">
                                <label>Tỷ lệ hoa hồng:</label>
                                <span><?= $affiliate['commission_rate'] ?>%</span>
                            </div>
                            <div class="stat-item">
                                <label>Trạng thái:</label>
                                <span class="status-badge status-<?= $affiliate['status'] ?>">
                                    <?php
                                    switch($affiliate['status']) {
                                        case 'active': echo 'Hoạt động'; break;
                                        case 'inactive': echo 'Không hoạt động'; break;
                                        case 'pending': echo 'Chờ duyệt'; break;
                                        default: echo 'N/A';
                                    }
                                    ?>
                                </span>
                            </div>
                            <div class="stat-item">
                                <label>Ngày tham gia:</label>
                                <span><?= formatDate($affiliate['created_at']) ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Impact Warning -->
            <div class="impact-warning-card">
                <div class="card-header">
                    <h3>Tác Động Khi Xóa</h3>
                </div>
                <div class="card-body">
                    <div class="impact-list">
                        <div class="impact-item danger">
                            <i class="fas fa-times-circle"></i>
                            <div class="impact-content">
                                <h5>Dữ liệu đại lý sẽ bị xóa vĩnh viễn</h5>
                                <p>Tất cả thông tin về đại lý này sẽ bị xóa khỏi hệ thống</p>
                            </div>
                        </div>
                        
                        <div class="impact-item danger">
                            <i class="fas fa-link"></i>
                            <div class="impact-content">
                                <h5>Mã giới thiệu sẽ không còn hoạt động</h5>
                                <p>Link giới thiệu với mã "<?= htmlspecialchars($affiliate['referral_code']) ?>" sẽ không còn hiệu lực</p>
                            </div>
                        </div>
                        
                        <div class="impact-item warning">
                            <i class="fas fa-chart-line"></i>
                            <div class="impact-content">
                                <h5>Lịch sử hoa hồng sẽ được lưu trữ</h5>
                                <p>Dữ liệu hoa hồng đã thanh toán sẽ được giữ lại để báo cáo</p>
                            </div>
                        </div>
                        
                        <div class="impact-item info">
                            <i class="fas fa-user"></i>
                            <div class="impact-content">
                                <h5>Tài khoản người dùng không bị ảnh hưởng</h5>
                                <p>Tài khoản của <?= htmlspecialchars($user['name'] ?? 'N/A') ?> vẫn hoạt động bình thường</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Delete Form -->
            <div class="delete-form-card">
                <div class="card-header">
                    <h3>Xác Nhận Xóa</h3>
                </div>
                <div class="card-body">
                    <form method="POST" class="delete-form">
                        <div class="form-group">
                            <label for="confirm" class="required">
                                Để xác nhận xóa, vui lòng nhập từ <strong>"DELETE"</strong> vào ô bên dưới:
                            </label>
                            <input type="text" id="confirm" name="confirm" 
                                   placeholder="Nhập DELETE để xác nhận" 
                                   autocomplete="off" required>
                            <small class="text-danger">Chú ý: Phải nhập chính xác "DELETE" (viết hoa)</small>
                        </div>

                        <div class="form-group">
                            <label>
                                <input type="checkbox" id="understand" required>
                                Tôi hiểu rằng hành động này không thể hoàn tác và sẽ xóa vĩnh viễn dữ liệu đại lý
                            </label>
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="btn btn-danger" id="deleteButton" disabled>
                                <i class="fas fa-trash"></i>
                                Xóa Đại Lý Vĩnh Viễn
                            </button>
                            <a href="?page=admin&module=affiliates&action=view&id=<?= $affiliate_id ?>" class="btn btn-secondary">
                                <i class="fas fa-times"></i>
                                Hủy bỏ
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const confirmInput = document.getElementById('confirm');
    const understandCheckbox = document.getElementById('understand');
    const deleteButton = document.getElementById('deleteButton');
    
    function checkFormValidity() {
        const isConfirmValid = confirmInput.value === 'DELETE';
        const isUnderstood = understandCheckbox.checked;
        
        deleteButton.disabled = !(isConfirmValid && isUnderstood);
        
        // Visual feedback
        if (confirmInput.value.length > 0) {
            if (isConfirmValid) {
                confirmInput.style.borderColor = '#10B981';
                confirmInput.style.backgroundColor = '#F0FDF4';
            } else {
                confirmInput.style.borderColor = '#EF4444';
                confirmInput.style.backgroundColor = '#FEF2F2';
            }
        } else {
            confirmInput.style.borderColor = '';
            confirmInput.style.backgroundColor = '';
        }
    }
    
    confirmInput.addEventListener('input', checkFormValidity);
    understandCheckbox.addEventListener('change', checkFormValidity);
    
    // Prevent accidental form submission
    document.querySelector('.delete-form').addEventListener('submit', function(e) {
        if (!confirm('Bạn có THỰC SỰ chắc chắn muốn xóa đại lý này? Hành động này KHÔNG THỂ hoàn tác!')) {
            e.preventDefault();
        }
    });
});
</script>