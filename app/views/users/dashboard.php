<?php
require_once '../auth/auth.php';

// Yêu cầu đăng nhập
requireAuth();

$debugInfo = getDebugInfo();
$userInfo = [
    'user_id' => $_SESSION['user_id'] ?? 'N/A',
    'full_name' => $_SESSION['full_name'] ?? 'N/A',
    'phone' => $_SESSION['phone'] ?? 'N/A',
    'role' => $_SESSION['role'] ?? 'N/A',
    'login_time' => $_SESSION['login_time'] ?? 'N/A',
    'device_id' => $_SESSION['device_id'] ?? 'N/A'
];
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - ThuongLo.com</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f8f9fa;
            min-height: 100vh;
            padding-bottom: 80px;
        }

        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            font-size: 24px;
            font-weight: bold;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .logout-btn {
            background: rgba(255,255,255,0.2);
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 20px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .logout-btn:hover {
            background: rgba(255,255,255,0.3);
        }

        .main-content {
            padding: 40px 0;
        }

        .welcome-card {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }

        .welcome-title {
            font-size: 28px;
            color: #333;
            margin-bottom: 10px;
        }

        .welcome-subtitle {
            color: #666;
            font-size: 16px;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 30px;
        }

        .info-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .info-card h3 {
            color: #333;
            margin-bottom: 15px;
            font-size: 18px;
        }

        .info-item {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #eee;
        }

        .info-item:last-child {
            border-bottom: none;
        }

        .info-label {
            font-weight: 500;
            color: #666;
        }

        .info-value {
            color: #333;
            font-weight: 600;
        }

        .role-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .role-user { background: #e3f2fd; color: #1976d2; }
        .role-agent { background: #f3e5f5; color: #7b1fa2; }
        .role-admin { background: #ffebee; color: #c62828; }

        /* Debug Console */
        .debug-console {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: #1a1a1a;
            color: #00ff00;
            padding: 10px;
            font-family: 'Courier New', monospace;
            font-size: 12px;
            z-index: 1000;
            border-top: 2px solid #333;
        }

        .debug-console .debug-item {
            display: inline-block;
            margin-right: 20px;
            padding: 2px 8px;
            background: #333;
            border-radius: 3px;
        }

        .debug-console .debug-label {
            color: #ffff00;
            font-weight: bold;
        }

        @media (max-width: 768px) {
            .header-content {
                flex-direction: column;
                gap: 15px;
            }

            .info-grid {
                grid-template-columns: 1fr;
            }

            .container {
                padding: 0 15px;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="container">
            <div class="header-content">
                <div class="logo">ThuongLo.com</div>
                <div class="user-info">
                    <span>Xin chào, <?php echo htmlspecialchars($userInfo['full_name']); ?>!</span>
                    <button class="logout-btn" onclick="logout()">Đăng xuất</button>
                </div>
            </div>
        </div>
    </div>

    <div class="main-content">
        <div class="container">
            <div class="welcome-card">
                <h1 class="welcome-title">Chào mừng đến với Dashboard!</h1>
                <p class="welcome-subtitle">
                    Bạn đã đăng nhập thành công vào hệ thống ThuongLo.com. 
                    Đây là trang dashboard mô phỏng để test hệ thống xác thực.
                </p>
            </div>

            <div class="info-grid">
                <div class="info-card">
                    <h3>Thông tin tài khoản</h3>
                    <div class="info-item">
                        <span class="info-label">User ID:</span>
                        <span class="info-value"><?php echo htmlspecialchars($userInfo['user_id']); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Họ tên:</span>
                        <span class="info-value"><?php echo htmlspecialchars($userInfo['full_name']); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Số điện thoại:</span>
                        <span class="info-value"><?php echo htmlspecialchars($userInfo['phone']); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Vai trò:</span>
                        <span class="info-value">
                            <span class="role-badge role-<?php echo $userInfo['role']; ?>">
                                <?php 
                                $roleNames = [
                                    'user' => 'Người dùng',
                                    'agent' => 'Đại lý', 
                                    'admin' => 'Quản trị'
                                ];
                                echo $roleNames[$userInfo['role']] ?? $userInfo['role']; 
                                ?>
                            </span>
                        </span>
                    </div>
                </div>

                <div class="info-card">
                    <h3>Thông tin phiên làm việc</h3>
                    <div class="info-item">
                        <span class="info-label">Thời gian đăng nhập:</span>
                        <span class="info-value"><?php echo htmlspecialchars($userInfo['login_time']); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Device ID:</span>
                        <span class="info-value"><?php echo htmlspecialchars($userInfo['device_id']); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">IP Address:</span>
                        <span class="info-value"><?php echo htmlspecialchars($_SESSION['ip_address'] ?? 'N/A'); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Mã giới thiệu:</span>
                        <span class="info-value"><?php echo htmlspecialchars($_SESSION['referred_by'] ?? 'Không có'); ?></span>
                    </div>
                </div>
            </div>

            <?php if (isset($_SESSION['security_logs']) && !empty($_SESSION['security_logs'])): ?>
            <div class="info-card" style="margin-top: 20px;">
                <h3>Log bảo mật (5 hoạt động gần nhất)</h3>
                <?php 
                $logs = array_slice(array_reverse($_SESSION['security_logs']), 0, 5);
                foreach ($logs as $log): 
                ?>
                <div class="info-item">
                    <span class="info-label"><?php echo htmlspecialchars($log['timestamp']); ?>:</span>
                    <span class="info-value"><?php echo htmlspecialchars($log['action']); ?></span>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Debug Console -->
    <div class="debug-console">
        <span class="debug-item">
            <span class="debug-label">Status:</span> <?php echo $debugInfo['status']; ?>
        </span>
        <span class="debug-item">
            <span class="debug-label">Role:</span> <?php echo $debugInfo['role']; ?>
        </span>
        <span class="debug-item">
            <span class="debug-label">Ref Code:</span> <?php echo $debugInfo['ref_code']; ?>
        </span>
        <span class="debug-item">
            <span class="debug-label">Security:</span> <?php echo $debugInfo['security_alert']; ?>
        </span>
    </div>

    <script>
        function logout() {
            if (confirm('Bạn có chắc chắn muốn đăng xuất?')) {
                window.location.href = '../auth/logout.php';
            }
        }

        // Auto refresh debug info every 5 seconds
        setInterval(function() {
            // In a real app, this would be an AJAX call
            // For demo purposes, we'll just reload the debug console
            console.log('Debug info refreshed at:', new Date().toLocaleTimeString());
        }, 5000);
    </script>
</body>
</html>