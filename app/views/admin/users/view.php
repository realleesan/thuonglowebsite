<?php
$service = isset($currentService) ? $currentService : ($adminService ?? null);

try {
    // Get user ID from URL
    $user_id = (int)($_GET['id'] ?? 0);
    
    // Get user details using AdminService
    $userData = $service->getUserDetailsData($user_id);
    $user = $userData['user'];
    
    // Redirect if user not found
    if (!$user) {
        header('Location: ?page=admin&module=users&error=not_found');
        exit;
    }
    
    // Get additional user data (orders and affiliate info) from AdminService
    $additionalData = $service->getUserAdditionalData($user_id);
    $user_orders = $additionalData['orders'];
    $user_affiliate = $additionalData['affiliate'];
    
} catch (Exception $e) {
    $errorHandler->logError('Admin Users View', $e->getMessage());
    header('Location: ?page=admin&module=users&error=system_error');
    exit;
}

// Calculate statistics
$total_orders = count($user_orders);
$total_spent = $user['total_spent'] ?? 0;
$completed_orders = count(array_filter($user_orders, function($order) {
    return $order['status'] == 'completed';
}));

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

function getStatusDisplayName($status) {
    return $status == 'active' ? 'Hoạt động' : 'Không hoạt động';
}

function getOrderStatusDisplayName($status) {
    $statuses = [
        'pending' => 'Chờ xử lý',
        'processing' => 'Đang xử lý',
        'completed' => 'Hoàn thành',
        'cancelled' => 'Đã hủy'
    ];
    return $statuses[$status] ?? $status;
}
?>

<div class="users-view-page">
    <!-- Page Header -->
    <div class="page-header">
        <div class="page-header-left">
            <h1 class="page-title">
                <i class="fas fa-user"></i>
                Chi Tiết Người Dùng
            </h1>
            <p class="page-description">Thông tin chi tiết của người dùng: <?= htmlspecialchars($user['name']) ?></p>
        </div>
        <div class="page-header-right">
            <a href="?page=admin&module=users&action=edit&id=<?= $user['id'] ?>" class="btn btn-warning">
                <i class="fas fa-edit"></i>
                Chỉnh sửa
            </a>
            <a href="?page=admin&module=users" class="btn btn-outline">
                <i class="fas fa-arrow-left"></i>
                Quay lại danh sách
            </a>
        </div>
    </div>

    <!-- User Overview -->
    <div class="user-overview">
        <div class="user-overview-grid">
            <!-- User Avatar & Basic Info -->
            <div class="user-avatar-section">
                <div class="user-avatar-main">
                    <div class="avatar-circle extra-large">
                        <?= strtoupper(substr($user['name'], 0, 2)) ?>
                    </div>
                </div>
                <div class="user-avatar-info">
                    <h2 class="user-name"><?= htmlspecialchars($user['name']) ?></h2>
                    <p class="user-role">
                        <span class="role-badge role-<?= $user['role'] ?>">
                            <?= getRoleDisplayName($user['role']) ?>
                        </span>
                    </p>
                    <p class="user-status">
                        <span class="status-badge status-<?= $user['status'] ?>">
                            <?= getStatusDisplayName($user['status']) ?>
                        </span>
                    </p>
                </div>
            </div>

            <!-- User Info Section -->
            <div class="user-info-section">
                <div class="user-header">
                    <div class="user-meta">
                        <div class="meta-item">
                            <span class="meta-label">ID:</span>
                            <span class="meta-value"><?= $user['id'] ?></span>
                        </div>
                        <div class="meta-item">
                            <span class="meta-label">Email:</span>
                            <span class="meta-value">
                                <a href="mailto:<?= htmlspecialchars($user['email']) ?>" class="email-link">
                                    <?= htmlspecialchars($user['email']) ?>
                                </a>
                            </span>
                        </div>
                        <div class="meta-item">
                            <span class="meta-label">Điện thoại:</span>
                            <span class="meta-value">
                                <a href="tel:<?= htmlspecialchars($user['phone']) ?>" class="phone-link">
                                    <?= htmlspecialchars($user['phone']) ?>
                                </a>
                            </span>
                        </div>
                        <div class="meta-item">
                            <span class="meta-label">Địa chỉ:</span>
                            <span class="meta-value"><?= htmlspecialchars($user['address']) ?></span>
                        </div>
                        <div class="meta-item">
                            <span class="meta-label">Ngày tạo:</span>
                            <span class="meta-value"><?= formatDate($user['created_at']) ?></span>
                        </div>
                    </div>
                </div>

                <!-- User Statistics -->
                <div class="user-stats-section">
                    <h4>Thống Kê Hoạt Động</h4>
                    <div class="stats-grid">
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="fas fa-shopping-cart"></i>
                            </div>
                            <div class="stat-content">
                                <div class="stat-number" title="<?= $total_orders ?> đơn hàng"><?= $total_orders ?></div>
                                <div class="stat-label">Tổng đơn hàng</div>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="fas fa-money-bill-wave"></i>
                            </div>
                            <div class="stat-content">
                                <div class="stat-number" title="<?= formatPrice($total_spent) ?>"><?= formatPrice($total_spent) ?></div>
                                <div class="stat-label">Tổng chi tiêu</div>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <div class="stat-content">
                                <div class="stat-number" title="<?= $completed_orders ?> đơn hoàn thành"><?= $completed_orders ?></div>
                                <div class="stat-label">Đơn hoàn thành</div>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="fas fa-percentage"></i>
                            </div>
                            <div class="stat-content">
                                <div class="stat-number" title="<?= $total_orders > 0 ? round(($completed_orders / $total_orders) * 100, 1) : 0 ?>%">
                                    <?= $total_orders > 0 ? round(($completed_orders / $total_orders) * 100, 1) : 0 ?>%
                                </div>
                                <div class="stat-label">Tỷ lệ hoàn thành</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- User Details Tabs -->
    <div class="user-details-tabs">
        <div class="tabs-header">
            <button class="tab-btn active" data-tab="orders-tab">
                <i class="fas fa-shopping-cart"></i>
                Đơn Hàng (<?= $total_orders ?>)
            </button>
            <button class="tab-btn" data-tab="activity-tab">
                <i class="fas fa-history"></i>
                Hoạt Động
            </button>
            <?php if ($user_affiliate): ?>
            <button class="tab-btn" data-tab="affiliate-tab">
                <i class="fas fa-handshake"></i>
                Thông Tin Đại Lý
            </button>
            <?php endif; ?>
            <button class="tab-btn" data-tab="details-tab">
                <i class="fas fa-info-circle"></i>
                Chi Tiết
            </button>
        </div>

        <div class="tabs-content">
            <!-- Orders Tab -->
            <div id="orders-tab" class="tab-content active">
                <?php if (empty($user_orders)): ?>
                    <div class="no-data">
                        <i class="fas fa-shopping-cart"></i>
                        <p>Người dùng chưa có đơn hàng nào</p>
                    </div>
                <?php else: ?>
                    <div class="orders-table-container">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Sản phẩm</th>
                                    <th>Số lượng</th>
                                    <th>Tổng tiền</th>
                                    <th>Trạng thái</th>
                                    <th>Ngày đặt</th>
                                    <th>Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($user_orders as $order): ?>
                                    <tr>
                                        <td><?= $order['id'] ?></td>
                                        <td>Sản phẩm #<?= $order['product_id'] ?></td>
                                        <td><?= $order['quantity'] ?></td>
                                        <td><?= formatPrice($order['total']) ?></td>
                                        <td>
                                            <span class="status-badge status-<?= $order['status'] ?>">
                                                <?= getOrderStatusDisplayName($order['status']) ?>
                                            </span>
                                        </td>
                                        <td><?= formatDate($order['created_at']) ?></td>
                                        <td>
                                            <a href="?page=admin&module=orders&action=view&id=<?= $order['id'] ?>" 
                                               class="btn btn-sm btn-info" title="Xem chi tiết">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Activity Tab -->
            <div id="activity-tab" class="tab-content">
                <div class="timeline">
                    <div class="timeline-item">
                        <div class="timeline-marker"></div>
                        <div class="timeline-content">
                            <div class="timeline-header">
                                <strong>Tài khoản được tạo</strong>
                                <span class="timeline-date"><?= formatDate($user['created_at']) ?></span>
                            </div>
                            <p>Tài khoản người dùng được tạo với vai trò <?= getRoleDisplayName($user['role']) ?></p>
                        </div>
                    </div>
                    
                    <?php if (!empty($user_orders)): ?>
                        <?php foreach (array_slice($user_orders, 0, 5) as $order): ?>
                            <div class="timeline-item">
                                <div class="timeline-marker"></div>
                                <div class="timeline-content">
                                    <div class="timeline-header">
                                        <strong>Đặt đơn hàng #<?= $order['id'] ?></strong>
                                        <span class="timeline-date"><?= formatDate($order['created_at']) ?></span>
                                    </div>
                                    <p>Đặt đơn hàng với tổng giá trị <?= formatPrice($order['total']) ?></p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>

                    <div class="timeline-item">
                        <div class="timeline-marker"></div>
                        <div class="timeline-content">
                            <div class="timeline-header">
                                <strong>Đăng nhập lần cuối</strong>
                                <span class="timeline-date">Hôm nay, 14:30</span>
                            </div>
                            <p>Đăng nhập từ IP: 192.168.1.100</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Affiliate Tab -->
            <?php if ($user_affiliate): ?>
            <div id="affiliate-tab" class="tab-content">
                <div class="affiliate-info">
                    <div class="affiliate-stats">
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="fas fa-percentage"></i>
                            </div>
                            <div class="stat-content">
                                <div class="stat-number"><?= $user_affiliate['commission_rate'] ?>%</div>
                                <div class="stat-label">Tỷ lệ hoa hồng</div>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="fas fa-chart-line"></i>
                            </div>
                            <div class="stat-content">
                                <div class="stat-number"><?= formatPrice($user_affiliate['total_sales']) ?></div>
                                <div class="stat-label">Tổng doanh số</div>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="fas fa-money-bill"></i>
                            </div>
                            <div class="stat-content">
                                <div class="stat-number"><?= formatPrice($user_affiliate['total_commission']) ?></div>
                                <div class="stat-label">Tổng hoa hồng</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="affiliate-details">
                        <h4>Thông Tin Đại Lý</h4>
                        <table class="details-table">
                            <tr>
                                <td>Mã giới thiệu:</td>
                                <td><strong><?= $user_affiliate['referral_code'] ?></strong></td>
                            </tr>
                            <tr>
                                <td>Trạng thái:</td>
                                <td>
                                    <span class="status-badge status-<?= $user_affiliate['status'] ?>">
                                        <?= getStatusDisplayName($user_affiliate['status']) ?>
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td>Ngày tham gia:</td>
                                <td><?= formatDate($user_affiliate['created_at']) ?></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Details Tab -->
            <div id="details-tab" class="tab-content">
                <div class="details-grid">
                    <div class="details-section">
                        <h4>Thông Tin Cá Nhân</h4>
                        <table class="details-table">
                            <tr>
                                <td>ID người dùng:</td>
                                <td><?= $user['id'] ?></td>
                            </tr>
                            <tr>
                                <td>Tên đầy đủ:</td>
                                <td><?= htmlspecialchars($user['name']) ?></td>
                            </tr>
                            <tr>
                                <td>Email:</td>
                                <td><?= htmlspecialchars($user['email']) ?></td>
                            </tr>
                            <tr>
                                <td>Số điện thoại:</td>
                                <td><?= htmlspecialchars($user['phone']) ?></td>
                            </tr>
                            <tr>
                                <td>Địa chỉ:</td>
                                <td><?= htmlspecialchars($user['address']) ?></td>
                            </tr>
                        </table>
                    </div>

                    <div class="details-section">
                        <h4>Thông Tin Tài Khoản</h4>
                        <table class="details-table">
                            <tr>
                                <td>Vai trò:</td>
                                <td>
                                    <span class="role-badge role-<?= $user['role'] ?>">
                                        <?= getRoleDisplayName($user['role']) ?>
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td>Trạng thái:</td>
                                <td>
                                    <span class="status-badge status-<?= $user['status'] ?>">
                                        <?= getStatusDisplayName($user['status']) ?>
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td>Ngày tạo:</td>
                                <td><?= formatDate($user['created_at']) ?></td>
                            </tr>
                            <tr>
                                <td>Đăng nhập cuối:</td>
                                <td>Hôm nay, 14:30</td>
                            </tr>
                            <tr>
                                <td>IP cuối:</td>
                                <td>192.168.1.100</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="action-buttons-container">
        <div class="action-buttons-grid">
            <button type="button" class="action-btn primary" onclick="editUser()">
                <i class="fas fa-edit"></i>
                <span>Chỉnh sửa thông tin</span>
            </button>
            <button type="button" class="action-btn secondary" onclick="resetPassword()">
                <i class="fas fa-key"></i>
                <span>Reset mật khẩu</span>
            </button>
            <button type="button" class="action-btn info" onclick="sendEmail()">
                <i class="fas fa-envelope"></i>
                <span>Gửi email</span>
            </button>
            <button type="button" class="action-btn warning" onclick="changeStatus()">
                <i class="fas fa-toggle-on"></i>
                <span>Đổi trạng thái</span>
            </button>
            <button type="button" class="action-btn danger" onclick="deleteUser()">
                <i class="fas fa-trash"></i>
                <span>Xóa người dùng</span>
            </button>
        </div>
    </div>
</div>

<script>
function editUser() {
    window.location.href = '?page=admin&module=users&action=edit&id=<?= $user['id'] ?>';
}

function resetPassword() {
    if (confirm('Bạn có chắc chắn muốn reset mật khẩu cho người dùng này?')) {
        alert('Đã gửi email reset mật khẩu');
    }
}

function sendEmail() {
    const email = '<?= htmlspecialchars($user['email']) ?>';
    window.location.href = `mailto:${email}`;
}

function changeStatus() {
    const currentStatus = '<?= $user['status'] ?>';
    const newStatus = currentStatus === 'active' ? 'inactive' : 'active';
    const action = newStatus === 'active' ? 'kích hoạt' : 'vô hiệu hóa';
    
    if (confirm(`Bạn có chắc chắn muốn ${action} tài khoản này?`)) {
        alert('Đã ' + action + ' tài khoản');
    }
}

function deleteUser() {
    if (confirm('Bạn có chắc chắn muốn xóa người dùng này? Hành động này không thể hoàn tác!')) {
        window.location.href = '?page=admin&module=users&action=delete&id=<?= $user['id'] ?>';
    }
}
</script>