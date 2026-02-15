<?php
// User Account View - Detailed Profile View
require_once __DIR__ . '/../../../services/UserService.php';

// Get current user from session
$userId = $_SESSION['user_id'] ?? null;
if (!$userId) {
    header('Location: ?page=login');
    exit;
}

// Get account data from UserService
try {
    $userService = new UserService();
    $accountData = $userService->getAccountData($userId);
    $dashboardData = $userService->getDashboardData($userId);
    
    $user = $accountData['user'] ?? [];
    $stats = $dashboardData['stats'] ?? [
        'total_orders' => 0,
        'total_spent' => 0,
        'data_purchased' => 0,
        'loyalty_points' => 0
    ];
} catch (Exception $e) {
    // Fallback to session data if service fails
    $user = [
        'id' => $_SESSION['user_id'],
        'name' => $_SESSION['user_name'] ?? 'User',
        'username' => $_SESSION['username'] ?? '',
        'email' => $_SESSION['user_email'] ?? '',
        'phone' => '',
        'address' => '',
        'role' => $_SESSION['user_role'] ?? 'user',
        'points' => 0,
        'level' => 'Bronze',
        'status' => 'active',
        'created_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s')
    ];
    $stats = [
        'total_orders' => 0,
        'total_spent' => 0,
        'data_purchased' => 0,
        'loyalty_points' => 0
    ];
}

// Login history (mock data)
$loginHistory = [
    [
        'date' => '2024-02-10 14:30:00',
        'ip' => '192.168.1.100',
        'device' => 'Chrome on Windows',
        'location' => 'Hà Nội, Việt Nam',
        'status' => 'success'
    ],
    [
        'date' => '2024-02-09 09:15:00',
        'ip' => '192.168.1.100',
        'device' => 'Chrome on Windows',
        'location' => 'Hà Nội, Việt Nam',
        'status' => 'success'
    ],
    [
        'date' => '2024-02-08 16:45:00',
        'ip' => '10.0.0.50',
        'device' => 'Safari on iPhone',
        'location' => 'TP.HCM, Việt Nam',
        'status' => 'success'
    ],
    [
        'date' => '2024-02-07 11:20:00',
        'ip' => '203.162.4.191',
        'device' => 'Chrome on Android',
        'location' => 'Đà Nẵng, Việt Nam',
        'status' => 'failed'
    ],
    [
        'date' => '2024-02-06 08:30:00',
        'ip' => '192.168.1.100',
        'device' => 'Chrome on Windows',
        'location' => 'Hà Nội, Việt Nam',
        'status' => 'success'
    ]
];

// Activity log (mock data)
$activityLog = [
    [
        'date' => '2024-02-10 14:35:00',
        'action' => 'Xem sản phẩm',
        'details' => 'Data Nguồn Hàng Premium',
        'ip' => '192.168.1.100'
    ],
    [
        'date' => '2024-02-10 14:30:00',
        'action' => 'Đăng nhập',
        'details' => 'Đăng nhập thành công',
        'ip' => '192.168.1.100'
    ],
    [
        'date' => '2024-02-09 15:20:00',
        'action' => 'Mua hàng',
        'details' => 'Đơn hàng #DH2024020901',
        'ip' => '192.168.1.100'
    ],
    [
        'date' => '2024-02-09 09:15:00',
        'action' => 'Đăng nhập',
        'details' => 'Đăng nhập thành công',
        'ip' => '192.168.1.100'
    ],
    [
        'date' => '2024-02-08 16:50:00',
        'action' => 'Cập nhật profile',
        'details' => 'Thay đổi số điện thoại',
        'ip' => '10.0.0.50'
    ]
];
?>

<div class="user-content-with-sidebar">
    <!-- User Sidebar -->
    <?php include 'app/views/_layout/user_sidebar.php'; ?>
    
    <!-- Account View Content -->
    <div class="user-account">
        <!-- Account Header -->
        <div class="account-header">
            <div class="account-header-left">
                <h1>Chi tiết tài khoản</h1>
                <p>Xem thông tin chi tiết và lịch sử hoạt động của tài khoản</p>
            </div>
            <div class="account-actions">
                <a href="?page=users&module=account" class="account-btn account-btn-secondary">
                    <i class="fas fa-arrow-left"></i>
                    Quay lại
                </a>
                <a href="?page=users&module=account&action=edit" class="account-btn account-btn-primary">
                    <i class="fas fa-edit"></i>
                    Chỉnh sửa
                </a>
            </div>
        </div>

        <!-- Account Content Grid -->
        <div class="account-content">
            <!-- Detailed Profile Information -->
            <div class="profile-card profile-card-full">
                <div class="profile-card-header">
                    <h3>Thông tin chi tiết</h3>
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
                                <i class="fas fa-check-circle"></i>
                                <?php 
                                $statusText = [
                                    'active' => 'Đang hoạt động',
                                    'inactive' => 'Không hoạt động',
                                    'suspended' => 'Tạm khóa'
                                ];
                                echo $statusText[$user['status']] ?? $user['status']; 
                                ?>
                            </div>
                        </div>
                    </div>

                    <!-- Detailed Profile Info Grid -->
                    <div class="profile-info-grid">
                        <div class="profile-info-item">
                            <div class="profile-info-label">ID Tài khoản</div>
                            <div class="profile-info-value">
                                #<?php echo str_pad($user['id'], 6, '0', STR_PAD_LEFT); ?>
                            </div>
                        </div>
                        
                        <div class="profile-info-item">
                            <div class="profile-info-label">Họ và tên</div>
                            <div class="profile-info-value">
                                <?php echo htmlspecialchars($user['name']); ?>
                            </div>
                        </div>
                        
                        <div class="profile-info-item">
                            <div class="profile-info-label">Email</div>
                            <div class="profile-info-value">
                                <?php echo htmlspecialchars($user['email']); ?>
                            </div>
                        </div>
                        
                        <div class="profile-info-item">
                            <div class="profile-info-label">Số điện thoại</div>
                            <div class="profile-info-value <?php echo empty($user['phone']) ? 'empty' : ''; ?>">
                                <?php echo !empty($user['phone']) ? htmlspecialchars($user['phone']) : 'Chưa cập nhật'; ?>
                            </div>
                        </div>
                        
                        <div class="profile-info-item">
                            <div class="profile-info-label">Địa chỉ</div>
                            <div class="profile-info-value <?php echo empty($user['address']) ? 'empty' : ''; ?>">
                                <?php echo !empty($user['address']) ? htmlspecialchars($user['address']) : 'Chưa cập nhật'; ?>
                            </div>
                        </div>
                        
                        <div class="profile-info-item">
                            <div class="profile-info-label">Cấp độ thành viên</div>
                            <div class="profile-info-value">
                                <?php echo htmlspecialchars($user['level']); ?> Member
                            </div>
                        </div>
                        
                        <div class="profile-info-item">
                            <div class="profile-info-label">Trạng thái</div>
                            <div class="profile-info-value">
                                <span class="user-badge user-badge-<?php echo $user['status'] === 'active' ? 'success' : 'danger'; ?>">
                                    <?php echo $statusText[$user['status']] ?? $user['status']; ?>
                                </span>
                            </div>
                        </div>
                        
                        <div class="profile-info-item">
                            <div class="profile-info-label">Ngày tham gia</div>
                            <div class="profile-info-value">
                                <?php echo date('d/m/Y H:i', strtotime($user['created_at'])); ?>
                            </div>
                        </div>
                        
                        <div class="profile-info-item">
                            <div class="profile-info-label">Đăng nhập gần đây</div>
                            <div class="profile-info-value">
                                <?php echo date('d/m/Y H:i', strtotime($user['last_login'])); ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Account Statistics -->
            <div class="profile-card">
                <div class="profile-card-header">
                    <h3>Thống kê chi tiết</h3>
                </div>
                <div class="profile-card-content">
                    <div class="account-stats">
                        <div class="account-stat-item">
                            <div class="account-stat-value"><?php echo $stats['total_orders']; ?></div>
                            <div class="account-stat-label">Tổng đơn hàng</div>
                        </div>
                        
                        <div class="account-stat-item">
                            <div class="account-stat-value"><?php echo number_format($stats['total_spent']); ?></div>
                            <div class="account-stat-label">Tổng chi tiêu (VNĐ)</div>
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

            <!-- Login History -->
            <div class="profile-card profile-card-full">
                <div class="profile-card-header">
                    <h3 id="login-history">Lịch sử đăng nhập</h3>
                </div>
                <div class="profile-card-content">
                    <div class="login-history-list">
                        <?php foreach ($loginHistory as $login): ?>
                        <div class="login-history-item">
                            <div class="login-info">
                                <div class="login-date">
                                    <i class="fas fa-clock"></i>
                                    <?php echo date('d/m/Y H:i', strtotime($login['date'])); ?>
                                </div>
                                <div class="login-device">
                                    <i class="fas fa-desktop"></i>
                                    <?php echo htmlspecialchars($login['device']); ?>
                                </div>
                                <div class="login-location">
                                    <i class="fas fa-map-marker-alt"></i>
                                    <?php echo htmlspecialchars($login['location']); ?>
                                </div>
                            </div>
                            <div class="login-details">
                                <div class="login-ip">IP: <?php echo htmlspecialchars($login['ip']); ?></div>
                                <div class="login-status">
                                    <span class="user-badge user-badge-<?php echo $login['status'] === 'success' ? 'success' : 'danger'; ?>">
                                        <?php echo $login['status'] === 'success' ? 'Thành công' : 'Thất bại'; ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Activity Log -->
            <div class="profile-card profile-card-full">
                <div class="profile-card-header">
                    <h3>Nhật ký hoạt động</h3>
                </div>
                <div class="profile-card-content">
                    <div class="activity-log-list">
                        <?php foreach ($activityLog as $activity): ?>
                        <div class="activity-log-item">
                            <div class="activity-icon">
                                <?php
                                $iconClass = 'fas fa-circle';
                                switch ($activity['action']) {
                                    case 'Đăng nhập':
                                        $iconClass = 'fas fa-sign-in-alt';
                                        break;
                                    case 'Mua hàng':
                                        $iconClass = 'fas fa-shopping-cart';
                                        break;
                                    case 'Xem sản phẩm':
                                        $iconClass = 'fas fa-eye';
                                        break;
                                    case 'Cập nhật profile':
                                        $iconClass = 'fas fa-user-edit';
                                        break;
                                }
                                ?>
                                <i class="<?php echo $iconClass; ?>"></i>
                            </div>
                            <div class="activity-info">
                                <div class="activity-action">
                                    <?php echo htmlspecialchars($activity['action']); ?>
                                </div>
                                <div class="activity-details">
                                    <?php echo htmlspecialchars($activity['details']); ?>
                                </div>
                                <div class="activity-meta">
                                    <span class="activity-date">
                                        <i class="fas fa-clock"></i>
                                        <?php echo date('d/m/Y H:i', strtotime($activity['date'])); ?>
                                    </span>
                                    <span class="activity-ip">
                                        <i class="fas fa-globe"></i>
                                        <?php echo htmlspecialchars($activity['ip']); ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Include Account JavaScript -->
<script src="assets/js/user_account.js"></script>

<!-- Additional Styles for View Page -->
<style>
/* Login History Styles */
.login-history-list {
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.login-history-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 16px;
    background: #f9fafb;
    border-radius: 8px;
    border: 1px solid #e5e7eb;
}

.login-info {
    flex: 1;
}

.login-date, .login-device, .login-location {
    display: flex;
    align-items: center;
    margin-bottom: 4px;
    font-size: 14px;
    color: #374151;
}

.login-date i, .login-device i, .login-location i {
    margin-right: 8px;
    width: 16px;
    color: #6b7280;
}

.login-details {
    text-align: right;
}

.login-ip {
    font-size: 12px;
    color: #6b7280;
    margin-bottom: 8px;
}

/* Activity Log Styles */
.activity-log-list {
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.activity-log-item {
    display: flex;
    align-items: flex-start;
    padding: 16px;
    background: #f9fafb;
    border-radius: 8px;
    border: 1px solid #e5e7eb;
}

.activity-icon {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: #356DF1;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 16px;
    flex-shrink: 0;
}

.activity-icon i {
    color: #ffffff;
    font-size: 16px;
}

.activity-info {
    flex: 1;
}

.activity-action {
    font-size: 16px;
    font-weight: 500;
    color: #111827;
    margin-bottom: 4px;
}

.activity-details {
    font-size: 14px;
    color: #6b7280;
    margin-bottom: 8px;
}

.activity-meta {
    display: flex;
    gap: 16px;
    font-size: 12px;
    color: #9ca3af;
}

.activity-date i, .activity-ip i {
    margin-right: 4px;
}

/* Responsive Design */
@media (max-width: 768px) {
    .login-history-item {
        flex-direction: column;
        align-items: flex-start;
        gap: 12px;
    }
    
    .login-details {
        text-align: left;
        width: 100%;
    }
    
    .activity-meta {
        flex-direction: column;
        gap: 4px;
    }
}
</style>