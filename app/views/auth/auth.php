<?php
/**
 * Auth View - Authentication Status Page
 * Displays current authentication status and user information
 */

// Ensure user is authenticated
if (!isset($_SESSION['user_id'])) {
    header('Location: ?page=login');
    exit;
}

$user = $_SESSION['user'] ?? null;
?>

<main class="page-content">
    <section class="auth-section auth-status-page">
        <div class="container">
            <h1 class="page-title-main">Trạng thái xác thực</h1>

            <div class="auth-panel">
                <h2 class="auth-heading">Thông tin đăng nhập</h2>

                <?php if ($user): ?>
                    <div class="user-info">
                        <div class="info-row">
                            <label>Tên:</label>
                            <span><?php echo htmlspecialchars($user['name']); ?></span>
                        </div>
                        <div class="info-row">
                            <label>Email:</label>
                            <span><?php echo htmlspecialchars($user['email']); ?></span>
                        </div>
                        <div class="info-row">
                            <label>Vai trò:</label>
                            <span><?php echo htmlspecialchars($user['role']); ?></span>
                        </div>
                        <div class="info-row">
                            <label>Trạng thái:</label>
                            <span class="status-<?php echo $user['status']; ?>">
                                <?php echo htmlspecialchars($user['status']); ?>
                            </span>
                        </div>
                    </div>

                    <div class="auth-actions">
                        <a href="?page=home" class="btn-primary">Về Dashboard</a>
                        <a href="?page=logout" class="btn-secondary">Đăng xuất</a>
                    </div>
                <?php else: ?>
                    <div class="alert alert-error">
                        Không thể tải thông tin người dùng.
                    </div>
                    <div class="auth-actions">
                        <a href="?page=login" class="btn-primary">Đăng nhập lại</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>
</main>