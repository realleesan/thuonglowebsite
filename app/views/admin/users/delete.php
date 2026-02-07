<?php
// Load fake data
$fake_data = json_decode(file_get_contents(__DIR__ . '/../data/fake_data.json'), true);
$users = $fake_data['users'];
$orders = $fake_data['orders'];
$affiliates = $fake_data['affiliates'];

// Get user ID from URL
$user_id = (int)($_GET['id'] ?? 0);

// Find user
$user = null;
foreach ($users as $u) {
    if ($u['id'] == $user_id) {
        $user = $u;
        break;
    }
}

// Redirect if user not found
if (!$user) {
    header('Location: ?page=admin&module=users&error=not_found');
    exit;
}

// Get related data
$user_orders = array_filter($orders, function($order) use ($user_id) {
    return $order['user_id'] == $user_id;
});

$user_affiliate = null;
foreach ($affiliates as $affiliate) {
    if ($affiliate['user_id'] == $user_id) {
        $user_affiliate = $affiliate;
        break;
    }
}

// Handle form submission (demo)
$success = false;
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $confirm_checkbox = isset($_POST['confirm_delete']);
    $delete_action = $_POST['delete_action'] ?? '';
    
    if (!$confirm_checkbox) {
        $errors[] = 'Bạn phải xác nhận việc xóa người dùng';
    }
    
    if (empty($delete_action)) {
        $errors[] = 'Bạn phải chọn hành động xử lý dữ liệu liên quan';
    }
    
    if (empty($errors)) {
        $success = true;
        // In real app: delete user and handle related data
        // Redirect after successful delete
        // header('Location: ?page=admin&module=users&success=deleted');
        // exit;
    }
}

// Format functions
function formatPrice($price) {
    return number_format($price, 0, ',', '.') . ' VNĐ';
}

function formatDate($date) {
    return date('d/m/Y H:i', strtotime($date));
}

function getRoleDisplayName($role) {
    $roles = [
        'admin' => 'Quản trị viên',
        'user' => 'Người dùng',
        'agent' => 'Đại lý'
    ];
    return $roles[$role] ?? $role;
}

// Calculate statistics
$total_orders = count($user_orders);
$total_spent = array_sum(array_column($user_orders, 'total'));
$pending_orders = count(array_filter($user_orders, function($order) {
    return in_array($order['status'], ['pending', 'processing']);
}));
?>

<div class="users-delete-page">
    <!-- Page Header -->
    <div class="page-header">
        <div class="page-header-left">
            <h1 class="page-title">
                <i class="fas fa-user-times"></i>
                Xóa Người Dùng
            </h1>
            <p class="page-description">Xác nhận xóa người dùng: <?= htmlspecialchars($user['name']) ?></p>
        </div>
        <div class="page-header-right">
            <a href="?page=admin&module=users&action=view&id=<?= $user['id'] ?>" class="btn btn-info">
                <i class="fas fa-eye"></i>
                Xem chi tiết
            </a>
            <a href="?page=admin&module=users" class="btn btn-outline">
                <i class="fas fa-arrow-left"></i>
                Quay lại danh sách
            </a>
        </div>
    </div>

    <!-- Alert Messages -->
    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-triangle"></i>
            <div>
                <strong>Có lỗi xảy ra:</strong>
                <ul class="error-list">
                    <?php foreach ($errors as $error): ?>
                        <li><?= htmlspecialchars($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i>
            <span>Xóa người dùng thành công! (Demo)</span>
        </div>
    <?php endif; ?>

    <!-- Delete Confirmation Container -->
    <div class="delete-confirmation-container">
        <!-- User Summary -->
        <div class="user-summary">
            <div class="user-summary-avatar">
                <div class="avatar-circle large">
                    <?= strtoupper(substr($user['name'], 0, 2)) ?>
                </div>
            </div>
            <div class="user-summary-info">
                <h3><?= htmlspecialchars($user['name']) ?></h3>
                <div class="user-meta">
                    <p><i class="fas fa-envelope"></i> <?= htmlspecialchars($user['email']) ?></p>
                    <p><i class="fas fa-phone"></i> <?= htmlspecialchars($user['phone']) ?></p>
                    <p><i class="fas fa-user-tag"></i> <?= getRoleDisplayName($user['role']) ?></p>
                    <p><i class="fas fa-calendar"></i> Tạo ngày <?= formatDate($user['created_at']) ?></p>
                </div>
            </div>
        </div>

        <!-- Warning Section -->
        <div class="warning-section">
            <div class="warning-header">
                <i class="fas fa-exclamation-triangle"></i>
                <h4>Cảnh Báo Quan Trọng</h4>
            </div>
            
            <div class="warning-content danger">
                <h5>Hành động này sẽ xóa vĩnh viễn người dùng và KHÔNG THỂ hoàn tác!</h5>
                <p>Việc xóa người dùng này sẽ ảnh hưởng đến các dữ liệu sau:</p>
                
                <ul>
                    <li><strong><?= $total_orders ?> đơn hàng</strong> với tổng giá trị <?= formatPrice($total_spent) ?></li>
                    <?php if ($pending_orders > 0): ?>
                        <li><strong><?= $pending_orders ?> đơn hàng đang chờ xử lý</strong> - cần được xử lý trước khi xóa</li>
                    <?php endif; ?>
                    <?php if ($user_affiliate): ?>
                        <li><strong>Thông tin đại lý</strong> với doanh số <?= formatPrice($user_affiliate['total_sales']) ?></li>
                    <?php endif; ?>
                    <li><strong>Lịch sử hoạt động</strong> và các bản ghi liên quan</li>
                    <li><strong>Dữ liệu cá nhân</strong> và thông tin liên hệ</li>
                </ul>

                <?php if ($pending_orders > 0): ?>
                    <div class="related-orders">
                        <h6>Đơn hàng cần xử lý:</h6>
                        <ul>
                            <?php foreach ($user_orders as $order): ?>
                                <?php if (in_array($order['status'], ['pending', 'processing'])): ?>
                                    <li>Đơn hàng #<?= $order['id'] ?> - <?= formatPrice($order['total']) ?> (<?= $order['status'] ?>)</li>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <div class="alternative-actions">
                    <h6>Các hành động thay thế:</h6>
                    <ul>
                        <li>Vô hiệu hóa tài khoản thay vì xóa</li>
                        <li>Chuyển đổi vai trò người dùng</li>
                        <li>Xuất dữ liệu trước khi xóa</li>
                        <li>Liên hệ người dùng để xác nhận</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Delete Form -->
        <form method="POST" class="delete-form">
            <!-- Data Handling Options -->
            <div class="form-section">
                <h4>Xử Lý Dữ Liệu Liên Quan</h4>
                <p>Chọn cách xử lý dữ liệu liên quan đến người dùng này:</p>
                
                <div class="radio-group">
                    <label class="radio-option">
                        <input type="radio" name="delete_action" value="anonymize" required>
                        <div class="radio-content">
                            <strong>Ẩn danh hóa dữ liệu</strong>
                            <p>Giữ lại đơn hàng và dữ liệu thống kê nhưng xóa thông tin cá nhân</p>
                        </div>
                    </label>
                    
                    <label class="radio-option">
                        <input type="radio" name="delete_action" value="transfer" required>
                        <div class="radio-content">
                            <strong>Chuyển giao dữ liệu</strong>
                            <p>Chuyển đơn hàng và dữ liệu cho admin khác quản lý</p>
                        </div>
                    </label>
                    
                    <label class="radio-option">
                        <input type="radio" name="delete_action" value="delete_all" required>
                        <div class="radio-content">
                            <strong>Xóa tất cả dữ liệu</strong>
                            <p>Xóa hoàn toàn người dùng và tất cả dữ liệu liên quan</p>
                        </div>
                    </label>
                </div>
            </div>

            <!-- Additional Options -->
            <div class="form-section">
                <h4>Tùy Chọn Bổ Sung</h4>
                
                <div class="checkbox-group">
                    <label class="checkbox-option">
                        <input type="checkbox" name="backup_data" value="1" checked>
                        <span>Tạo bản sao lưu dữ liệu trước khi xóa</span>
                    </label>
                    
                    <label class="checkbox-option">
                        <input type="checkbox" name="notify_user" value="1">
                        <span>Gửi email thông báo cho người dùng</span>
                    </label>
                    
                    <label class="checkbox-option">
                        <input type="checkbox" name="log_action" value="1" checked>
                        <span>Ghi log hành động xóa</span>
                    </label>
                </div>
            </div>

            <!-- Confirmation -->
            <div class="confirmation-section">
                <div class="confirmation-checkbox">
                    <label>
                        <input type="checkbox" name="confirm_delete" value="1" required>
                        <strong>Tôi hiểu rằng hành động này không thể hoàn tác và xác nhận muốn xóa người dùng này</strong>
                    </label>
                </div>

                <div class="warning-box">
                    <i class="fas fa-exclamation-triangle"></i>
                    <div>
                        <p><strong>Lưu ý cuối cùng:</strong></p>
                        <p>Sau khi xóa, bạn sẽ không thể khôi phục dữ liệu này. Hãy chắc chắn rằng bạn đã cân nhắc kỹ lưỡng.</p>
                    </div>
                </div>
            </div>

            <!-- Delete Actions -->
            <div class="delete-actions">
                <button type="submit" class="btn btn-danger" id="delete-btn" disabled>
                    <i class="fas fa-trash"></i>
                    Xác Nhận Xóa Người Dùng
                </button>
                <button type="button" class="btn btn-warning" onclick="deactivateUser()">
                    <i class="fas fa-ban"></i>
                    Vô Hiệu Hóa Thay Thế
                </button>
                <a href="?page=admin&module=users&action=view&id=<?= $user['id'] ?>" class="btn btn-secondary">
                    <i class="fas fa-eye"></i>
                    Xem Chi Tiết
                </a>
                <a href="?page=admin&module=users" class="btn btn-outline">
                    <i class="fas fa-times"></i>
                    Hủy
                </a>
            </div>
        </form>
    </div>

    <!-- Deactivate Modal -->
    <div id="deactivateModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Vô hiệu hóa tài khoản</h3>
                <button type="button" class="modal-close">&times;</button>
            </div>
            <div class="modal-body">
                <p>Bạn có muốn vô hiệu hóa tài khoản này thay vì xóa?</p>
                <p>Vô hiệu hóa sẽ giữ lại tất cả dữ liệu nhưng người dùng không thể đăng nhập.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" id="cancelDeactivate">Hủy</button>
                <button type="button" class="btn btn-warning" id="confirmDeactivate">Vô Hiệu Hóa</button>
            </div>
        </div>
    </div>
</div>

<script>
// Enable/disable delete button based on checkbox
document.addEventListener('DOMContentLoaded', function() {
    const confirmCheckbox = document.querySelector('input[name="confirm_delete"]');
    const deleteBtn = document.getElementById('delete-btn');
    
    if (confirmCheckbox && deleteBtn) {
        confirmCheckbox.addEventListener('change', function() {
            deleteBtn.disabled = !this.checked;
        });
    }
});

function deactivateUser() {
    const deactivateModal = document.getElementById('deactivateModal');
    if (deactivateModal) {
        deactivateModal.style.display = 'flex';
    }
}

// Deactivate modal functionality
document.addEventListener('DOMContentLoaded', function() {
    const deactivateModal = document.getElementById('deactivateModal');
    const cancelDeactivateBtn = document.getElementById('cancelDeactivate');
    const confirmDeactivateBtn = document.getElementById('confirmDeactivate');
    const modalClose = document.querySelector('#deactivateModal .modal-close');

    function closeDeactivateModal() {
        if (deactivateModal) {
            deactivateModal.style.display = 'none';
        }
    }

    if (cancelDeactivateBtn) {
        cancelDeactivateBtn.addEventListener('click', closeDeactivateModal);
    }
    
    if (modalClose) {
        modalClose.addEventListener('click', closeDeactivateModal);
    }

    if (confirmDeactivateBtn) {
        confirmDeactivateBtn.addEventListener('click', function() {
            alert('Đã vô hiệu hóa tài khoản (Demo)');
            closeDeactivateModal();
            window.location.href = '?page=admin&module=users&action=edit&id=<?= $user['id'] ?>';
        });
    }

    // Close modal when clicking outside
    if (deactivateModal) {
        deactivateModal.addEventListener('click', function(e) {
            if (e.target === this) {
                closeDeactivateModal();
            }
        });
    }
});

// Form submission confirmation
document.querySelector('.delete-form').addEventListener('submit', function(e) {
    if (!confirm('Bạn có THỰC SỰ chắc chắn muốn xóa người dùng này? Hành động này KHÔNG THỂ hoàn tác!')) {
        e.preventDefault();
    }
});
</script>