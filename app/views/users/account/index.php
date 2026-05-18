<?php
// User Account Index - View Account Information
require_once __DIR__ . '/../../../services/UserService.php';

// Simple img_url function for avatar
function local_img_url($file) {
    // Tạo URL đầy đủ như sidebar
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https" : "http";
    $host = $_SERVER['HTTP_HOST'];
    return $protocol . "://" . $host . "/assets/images/" . ltrim($file, '/');
}

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

// Security info (placeholder for now - can be enhanced later)
$securityInfo = [
    'password_last_changed' => $user['updated_at'] ?? date('Y-m-d'),
    'two_factor_enabled' => false,
    'login_notifications' => true,
    'last_login_ip' => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1'
];
?>

<div class="user-content-with-sidebar">
    <!-- User Sidebar -->
    <?php include 'app/views/_layout/user_sidebar.php'; ?>
    
    <!-- Account Content -->
    <div class="user-account">
        <!-- Account Header -->
        <div class="account-header">
            <div class="account-header-left">
                <h1>Thông tin tài khoản</h1>
                <p>Quản lý thông tin cá nhân và cài đặt tài khoản của bạn</p>
            </div>
            <div class="account-actions">
                <a href="?page=users&module=account&action=edit" class="account-btn account-btn-primary">
                    <i class="fas fa-edit"></i>
                    Chỉnh sửa
                </a>
               
            </div>
        </div>

        <!-- Account Content Grid -->
        <div class="account-content">
            <!-- Profile Information -->
            <div class="profile-card profile-card-full">
                <div class="profile-card-header">
                    <h3>Thông tin cá nhân</h3>
                </div>
                <div class="profile-card-content">
                    <!-- Profile Avatar Section -->
                    <div class="profile-avatar-section">
                        <div class="profile-avatar">
                            <img src="https://test1.web3b.com/assets/images/home/home-banner-final.png" alt="Avatar Placeholder">
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

                    <!-- Profile Info Grid -->
                    <div class="profile-info-grid">
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
                            <div class="profile-info-label">Ngày tham gia</div>
                            <div class="profile-info-value">
                                <?php echo date('d/m/Y', strtotime($user['created_at'])); ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>



            <!-- Security Information -->
            <div class="profile-card">
                <div class="profile-card-header">
                    <h3>Bảo mật tài khoản</h3>
                </div>
                <div class="profile-card-content">
                    <div class="security-section">
                        <div class="security-item">
                            <div class="security-item-info">
                                <h4>Mật khẩu</h4>
                                <p>Thay đổi lần cuối: <?php echo date('d/m/Y', strtotime($securityInfo['password_last_changed'])); ?></p>
                            </div>
                            <a href="?page=users&module=account&action=edit#password" class="security-item-action">
                                Thay đổi
                            </a>
                        </div>
                        
                        
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Include Account JavaScript -->
<script src="assets/js/user_account.js"></script>

<!-- Include User Sidebar CSS for avatar styling -->
<link rel="stylesheet" href="assets/css/user_sidebar.css">

<style>
    .profile-card-content {
        padding: 20px;
    }

    .profile-info-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
    }

    .profile-info-item {
        display: flex;
        flex-direction: column;
        gap: 5px;
    }

    .profile-info-label {
        font-weight: 600;
        color: #666;
    }

    .profile-info-value {
        color: #333;
    }

    .account-stats {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 20px;
    }

    .account-stat-item {
        text-align: center;
        padding: 20px;
        background: #f8f9fa;
        border-radius: 8px;
    }

    .account-stat-value {
        font-size: 24px;
        font-weight: bold;
        color: #333;
    }

    .account-stat-label {
        color: #666;
        margin-top: 5px;
    }

    .nav-list {
        list-style: none;
        padding: 0;
    }

    .nav-list li {
        margin-bottom: 10px;
    }

    .nav-list a {
        display: block;
        padding: 10px 15px;
        text-decoration: none;
        color: #666;
        border-radius: 5px;
    }

    .nav-list li.active a,
    .nav-list a:hover {
        background: #f0f0f0;
        color: #333;
    }

    .account-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 30px;
        padding-bottom: 20px;
        border-bottom: 1px solid #eee;
    }

    .account-actions {
        display: flex;
        gap: 12px;
    }

    .account-btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 16px;
        border-radius: 8px;
        text-decoration: none;
        font-weight: 500;
        font-size: 14px;
    }

    .account-btn-primary {
        background: #3b82f6;
        color: white;
    }

    .account-btn-secondary {
        background: #f3f4f6;
        color: #374151;
    }

    .profile-avatar-section {
        display: flex;
        align-items: center;
        gap: 20px;
        margin-bottom: 30px;
    }

    .profile-avatar-placeholder {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        background: #3b82f6;
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 32px;
        font-weight: bold;
    }

    .profile-avatar {
        width: 80px;
        height: 80px;
        border-radius: 8px;
        overflow: hidden;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #f3f4f6;
    }

    .profile-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .security-section {
        display: flex;
        flex-direction: column;
        gap: 20px;
    }

    .security-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 15px;
        background: #f8f9fa;
        border-radius: 8px;
    }

    .security-item-action {
        color: #3b82f6;
        text-decoration: none;
        font-weight: 500;
    }
</style>