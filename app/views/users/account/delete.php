<?php
// User Account Delete - Delete Account Confirmation
// Load fake data
$dataFile = __DIR__ . '/../data/user_fake_data.json';
$data = [];

if (file_exists($dataFile)) {
    $jsonContent = file_get_contents($dataFile);
    $data = json_decode($jsonContent, true) ?: [];
}

// Get user data
$user = $data['user'] ?? [
    'id' => 1,
    'name' => 'Người dùng',
    'email' => 'user@example.com',
    'phone' => '',
    'address' => '',
    'avatar' => '',
    'level' => 'Basic',
    'status' => 'active',
    'created_at' => date('Y-m-d H:i:s'),
    'last_login' => date('Y-m-d H:i:s')
];

// Calculate account stats
$stats = $data['stats'] ?? [
    'total_orders' => 0,
    'total_spent' => 0,
    'data_purchased' => 0,
    'loyalty_points' => 0
];

// Handle form submission (mock)
$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['confirm_delete'])) {
        // Mock account deletion process
        $success_message = 'Tài khoản đã được xóa thành công. Bạn sẽ được chuyển hướng về trang chủ.';
        // In real application, this would:
        // 1. Delete user data
        // 2. Log out user
        // 3. Redirect to homepage
        echo '<script>
            setTimeout(function() {
                window.location.href = "?page=home";
            }, 3000);
        </script>';
    }
}
?>

<div class="user-content-with-sidebar">
    <!-- User Sidebar -->
    <?php include 'app/views/_layout/user_sidebar.php'; ?>
    
    <!-- Account Delete Content -->
    <div class="user-account">
        <!-- Account Header -->
        <div class="account-header">
            <div class="account-header-left">
                <h1>Xóa tài khoản</h1>
                <p>Xác nhận xóa tài khoản vĩnh viễn</p>
            </div>
            <div class="account-actions">
                <a href="?page=users&module=account" class="account-btn account-btn-secondary">
                    <i class="fas fa-arrow-left"></i>
                    Quay lại
                </a>
            </div>
        </div>

        <!-- Success/Error Messages -->
        <?php if ($success_message): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <?php echo htmlspecialchars($success_message); ?>
            </div>
        <?php endif; ?>

        <?php if ($error_message): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i>
                <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>

        <!-- Account Content Grid -->
        <div class="account-content">
            <!-- Account Summary Before Deletion -->
            <div class="profile-card profile-card-full">
                <div class="profile-card-header">
                    <h3>Tóm tắt tài khoản</h3>
                </div>
                <div class="profile-card-content">
                    <!-- Profile Avatar Section -->
                    <div class="profile-avatar-section">
                        <div class="profile-avatar">
                            <?php if (!empty($user['avatar'])): ?>
                                <img src="<?php echo htmlspecialchars($user['avatar']); ?>" alt="Avatar">
                            <?php else: ?>
                                <div class="profile-avatar-placeholder">
                                    <?php echo strtoupper(substr($user['name'], 0, 1)); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="profile-avatar-info">
                            <h2><?php echo htmlspecialchars($user['name']); ?></h2>
                            <p><?php echo htmlspecialchars($user['email']); ?></p>
                            <div class="profile-status-badge">
                                <i class="fas fa-calendar-alt"></i>
                                Thành viên từ <?php echo date('d/m/Y', strtotime($user['created_at'])); ?>
                            </div>
                        </div>
                    </div>

                    <!-- Account Statistics -->
                    <div class="account-stats">
                        <div class="account-stat-item">
                            <div class="account-stat-value"><?php echo $stats['total_orders']; ?></div>
                            <div class="account-stat-label">Đơn hàng</div>
                        </div>
                        
                        <div class="account-stat-item">
                            <div class="account-stat-value"><?php echo number_format($stats['total_spent'] / 1000000, 1); ?>M</div>
                            <div class="account-stat-label">Chi tiêu (VNĐ)</div>
                        </div>
                        
                        <div class="account-stat-item">
                            <div class="account-stat-value"><?php echo $stats['data_purchased']; ?></div>
                            <div class="account-stat-label">Data đã mua</div>
                        </div>
                        
                        <div class="account-stat-item">
                            <div class="account-stat-value"><?php echo number_format($stats['loyalty_points']); ?></div>
                            <div class="account-stat-label">Điểm tích lũy</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Data Loss Warning -->
            <div class="profile-card">
                <div class="profile-card-header">
                    <h3>Dữ liệu sẽ bị xóa</h3>
                </div>
                <div class="profile-card-content">
                    <div class="data-loss-warning">
                        <div class="warning-item">
                            <div class="warning-icon">
                                <i class="fas fa-user"></i>
                            </div>
                            <div class="warning-content">
                                <h4>Thông tin cá nhân</h4>
                                <p>Tên, email, số điện thoại, địa chỉ và ảnh đại diện</p>
                            </div>
                        </div>

                        <div class="warning-item">
                            <div class="warning-icon">
                                <i class="fas fa-shopping-bag"></i>
                            </div>
                            <div class="warning-content">
                                <h4>Lịch sử đơn hàng</h4>
                                <p>Tất cả đơn hàng và giao dịch đã thực hiện</p>
                            </div>
                        </div>

                        <div class="warning-item">
                            <div class="warning-icon">
                                <i class="fas fa-database"></i>
                            </div>
                            <div class="warning-content">
                                <h4>Data đã mua</h4>
                                <p>Quyền truy cập vào tất cả data và khóa học đã mua</p>
                            </div>
                        </div>

                        <div class="warning-item">
                            <div class="warning-icon">
                                <i class="fas fa-star"></i>
                            </div>
                            <div class="warning-content">
                                <h4>Điểm tích lũy</h4>
                                <p>Tất cả điểm thưởng và ưu đãi đã tích lũy</p>
                            </div>
                        </div>

                        <div class="warning-item">
                            <div class="warning-icon">
                                <i class="fas fa-heart"></i>
                            </div>
                            <div class="warning-content">
                                <h4>Danh sách yêu thích</h4>
                                <p>Tất cả sản phẩm đã lưu vào danh sách yêu thích</p>
                            </div>
                        </div>

                        <div class="warning-item">
                            <div class="warning-icon">
                                <i class="fas fa-history"></i>
                            </div>
                            <div class="warning-content">
                                <h4>Lịch sử hoạt động</h4>
                                <p>Nhật ký đăng nhập và các hoạt động trên hệ thống</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Deletion Confirmation Form -->
            <div class="profile-card profile-card-full">
                <div class="profile-card-header">
                    <h3>Xác nhận xóa tài khoản</h3>
                </div>
                <div class="profile-card-content">
                    <div class="delete-confirmation-section">
                        <div class="delete-warning">
                            <div class="delete-warning-icon">
                                <i class="fas fa-exclamation-triangle"></i>
                            </div>
                            <div class="delete-warning-content">
                                <h3>Cảnh báo quan trọng!</h3>
                                <p>Hành động này không thể hoàn tác. Khi bạn xóa tài khoản:</p>
                                <ul>
                                    <li>Tất cả dữ liệu cá nhân sẽ bị xóa vĩnh viễn</li>
                                    <li>Bạn sẽ mất quyền truy cập vào tất cả data đã mua</li>
                                    <li>Điểm tích lũy và ưu đãi sẽ bị hủy bỏ</li>
                                    <li>Không thể khôi phục tài khoản sau khi xóa</li>
                                </ul>
                            </div>
                        </div>

                        <form method="POST" class="account-form delete-confirmation-form">
                            <div class="form-group">
                                <label class="form-label">
                                    <input type="checkbox" id="understand_consequences" name="understand_consequences" required>
                                    Tôi hiểu rằng việc xóa tài khoản là không thể hoàn tác
                                </label>
                            </div>

                            <div class="form-group">
                                <label class="form-label">
                                    <input type="checkbox" id="confirm_data_loss" name="confirm_data_loss" required>
                                    Tôi xác nhận rằng tôi sẽ mất tất cả dữ liệu và quyền truy cập
                                </label>
                            </div>

                            <div class="form-group">
                                <label for="email_confirmation" class="form-label required">
                                    Nhập email của bạn để xác nhận
                                </label>
                                <input type="email" id="email_confirmation" name="email_confirmation" 
                                       class="form-control" placeholder="<?php echo htmlspecialchars($user['email']); ?>" required>
                                <div class="form-text">Nhập chính xác email: <?php echo htmlspecialchars($user['email']); ?></div>
                            </div>

                            <div class="form-group">
                                <label for="deletion_reason" class="form-label">
                                    Lý do xóa tài khoản (tùy chọn)
                                </label>
                                <textarea id="deletion_reason" name="deletion_reason" class="form-control" 
                                          rows="3" placeholder="Chia sẻ lý do để chúng tôi cải thiện dịch vụ..."></textarea>
                            </div>

                            <div class="form-actions delete-form-actions">
                                <a href="?page=users&module=account" class="account-btn account-btn-secondary">
                                    <i class="fas fa-times"></i>
                                    Hủy bỏ
                                </a>
                                <button type="submit" name="confirm_delete" class="account-btn account-btn-danger" id="delete-confirm-btn" disabled>
                                    <i class="fas fa-trash"></i>
                                    Xóa tài khoản vĩnh viễn
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Include Account JavaScript -->
<script src="assets/js/user_account.js"></script>

<!-- Additional JavaScript for Delete Confirmation -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const understandCheckbox = document.getElementById('understand_consequences');
    const confirmDataLossCheckbox = document.getElementById('confirm_data_loss');
    const emailConfirmation = document.getElementById('email_confirmation');
    const deleteButton = document.getElementById('delete-confirm-btn');
    const userEmail = '<?php echo htmlspecialchars($user['email']); ?>';

    function checkFormValidity() {
        const isUnderstandChecked = understandCheckbox.checked;
        const isDataLossConfirmed = confirmDataLossCheckbox.checked;
        const isEmailCorrect = emailConfirmation.value.trim() === userEmail;

        deleteButton.disabled = !(isUnderstandChecked && isDataLossConfirmed && isEmailCorrect);
    }

    understandCheckbox.addEventListener('change', checkFormValidity);
    confirmDataLossCheckbox.addEventListener('change', checkFormValidity);
    emailConfirmation.addEventListener('input', checkFormValidity);

    // Final confirmation before deletion
    document.querySelector('.delete-confirmation-form').addEventListener('submit', function(e) {
        if (!confirm('Bạn có THỰC SỰ chắc chắn muốn xóa tài khoản? Hành động này KHÔNG THỂ hoàn tác!')) {
            e.preventDefault();
        }
    });
});
</script>

<!-- Additional Styles for Delete Page -->
<style>
/* Data Loss Warning Styles */
.data-loss-warning {
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.warning-item {
    display: flex;
    align-items: flex-start;
    padding: 16px;
    background: #fef3c7;
    border: 1px solid #fde68a;
    border-radius: 8px;
}

.warning-icon {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: #f59e0b;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 16px;
    flex-shrink: 0;
}

.warning-icon i {
    color: #ffffff;
    font-size: 16px;
}

.warning-content h4 {
    font-size: 14px;
    font-weight: 600;
    color: #92400e;
    margin: 0 0 4px 0;
}

.warning-content p {
    font-size: 13px;
    color: #92400e;
    margin: 0;
}

/* Delete Confirmation Styles */
.delete-confirmation-section {
    max-width: 600px;
    margin: 0 auto;
}

.delete-warning {
    display: flex;
    align-items: flex-start;
    padding: 24px;
    background: #fef2f2;
    border: 2px solid #fecaca;
    border-radius: 12px;
    margin-bottom: 32px;
}

.delete-warning-icon {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background: #ef4444;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 20px;
    flex-shrink: 0;
}

.delete-warning-icon i {
    color: #ffffff;
    font-size: 24px;
}

.delete-warning-content h3 {
    font-size: 18px;
    font-weight: 600;
    color: #991b1b;
    margin: 0 0 8px 0;
}

.delete-warning-content p {
    font-size: 14px;
    color: #7f1d1d;
    margin: 0 0 12px 0;
}

.delete-warning-content ul {
    margin: 0;
    padding-left: 20px;
    color: #7f1d1d;
}

.delete-warning-content li {
    font-size: 14px;
    margin-bottom: 4px;
}

/* Form Styles */
.delete-confirmation-form .form-group {
    margin-bottom: 24px;
}

.delete-confirmation-form .form-label {
    display: flex;
    align-items: flex-start;
    gap: 8px;
    cursor: pointer;
}

.delete-confirmation-form input[type="checkbox"] {
    margin-top: 2px;
}

.delete-form-actions {
    justify-content: center;
    gap: 20px;
}

.delete-form-actions .account-btn {
    min-width: 160px;
}

#delete-confirm-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

/* Responsive Design */
@media (max-width: 768px) {
    .delete-warning {
        flex-direction: column;
        text-align: center;
    }
    
    .delete-warning-icon {
        margin-right: 0;
        margin-bottom: 16px;
        align-self: center;
    }
    
    .delete-form-actions {
        flex-direction: column;
    }
    
    .delete-form-actions .account-btn {
        width: 100%;
    }
}
</style>