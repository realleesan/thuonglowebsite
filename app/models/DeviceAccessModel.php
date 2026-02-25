<?php
/**
 * DeviceAccessModel - Model quản lý phiên thiết bị và mã xác thực
 * Xử lý CRUD cho bảng device_sessions và device_verification_codes
 */

require_once __DIR__ . '/BaseModel.php';

class DeviceAccessModel extends BaseModel {
    protected $table = 'device_sessions';
    protected $fillable = [
        'user_id', 'session_id', 'device_name', 'device_type',
        'browser', 'os', 'ip_address', 'location', 'status', 'is_current',
        'last_activity', 'created_at', 'updated_at'
    ];

    // ==========================================
    // DEVICE SESSION METHODS
    // ==========================================

    /**
     * Lấy danh sách thiết bị active của user
     */
    public function getActiveDevices(int $userId): array {
        return $this->query(
            "SELECT * FROM device_sessions WHERE user_id = :user_id AND status = 'active' ORDER BY last_activity DESC",
            ['user_id' => $userId]
        );
    }

    /**
     * Lấy danh sách thiết bị pending của user
     */
    public function getPendingDevices(int $userId): array {
        return $this->query(
            "SELECT * FROM device_sessions WHERE user_id = :user_id AND status = 'pending' ORDER BY created_at DESC",
            ['user_id' => $userId]
        );
    }

    /**
     * Đếm số thiết bị active của user
     */
    public function getActiveDeviceCount(int $userId): int {
        $result = $this->query(
            "SELECT COUNT(*) as count FROM device_sessions WHERE user_id = :user_id AND status = 'active'",
            ['user_id' => $userId]
        );
        return (int)($result[0]['count'] ?? 0);
    }

    /**
     * Tạo phiên thiết bị mới
     */
    public function createDeviceSession(array $data): int {
        $now = date('Y-m-d H:i:s');
        $stmt = $this->query(
            "INSERT INTO device_sessions (user_id, session_id, device_name, device_type, browser, os, ip_address, location, status, is_current, last_activity, created_at, updated_at) 
             VALUES (:user_id, :session_id, :device_name, :device_type, :browser, :os, :ip_address, :location, :status, :is_current, :last_activity, :created_at, :updated_at)",
            [
                'user_id' => $data['user_id'],
                'session_id' => $data['session_id'],
                'device_name' => $data['device_name'] ?? 'Unknown Device',
                'device_type' => $data['device_type'] ?? 'desktop',
                'browser' => $data['browser'] ?? 'Unknown',
                'os' => $data['os'] ?? 'Unknown',
                'ip_address' => $data['ip_address'] ?? '',
                'location' => $data['location'] ?? '',
                'status' => $data['status'] ?? 'active',
                'is_current' => $data['is_current'] ?? 0,
                'last_activity' => $now,
                'created_at' => $now,
                'updated_at' => $now
            ]
        );

        // Get the last insert ID
        $result = $this->query("SELECT LAST_INSERT_ID() as id");
        return (int)($result[0]['id'] ?? 0);
    }

    /**
     * Cập nhật trạng thái thiết bị
     */
    public function updateDeviceStatus(int $id, string $status): bool {
        $this->query(
            "UPDATE device_sessions SET status = :status, updated_at = :updated_at WHERE id = :id",
            ['status' => $status, 'updated_at' => date('Y-m-d H:i:s'), 'id' => $id]
        );
        return true;
    }

    /**
     * Cập nhật thời gian hoạt động cuối
     */
    public function updateLastActivity(int $id): bool {
        $this->query(
            "UPDATE device_sessions SET last_activity = :now, updated_at = :updated_at WHERE id = :id",
            ['now' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s'), 'id' => $id]
        );
        return true;
    }

    /**
     * Xóa phiên thiết bị
     */
    public function deleteDeviceSession(int $id): bool {
        $this->query(
            "DELETE FROM device_sessions WHERE id = :id",
            ['id' => $id]
        );
        return true;
    }

    /**
     * Tìm phiên theo session_id
     */
    public function findBySessionId(string $sessionId): ?array {
        $result = $this->query(
            "SELECT * FROM device_sessions WHERE session_id = :session_id LIMIT 1",
            ['session_id' => $sessionId]
        );
        return $result[0] ?? null;
    }

    /**
     * Tìm phiên theo user_id và session_id
     */
    public function findByUserAndSession(int $userId, string $sessionId): ?array {
        $result = $this->query(
            "SELECT * FROM device_sessions WHERE user_id = :user_id AND session_id = :session_id LIMIT 1",
            ['user_id' => $userId, 'session_id' => $sessionId]
        );
        return $result[0] ?? null;
    }

    /**
     * Tìm phiên theo ID và user_id (bảo mật)
     */
    public function findByIdAndUser(int $id, int $userId): ?array {
        $result = $this->query(
            "SELECT * FROM device_sessions WHERE id = :id AND user_id = :user_id LIMIT 1",
            ['id' => $id, 'user_id' => $userId]
        );
        return $result[0] ?? null;
    }

    /**
     * Hủy kích hoạt tất cả các phiên khác (giữ lại phiên hiện tại)
     */
    public function deactivateOtherSessions(int $userId, string $keepSessionId): int {
        $this->query(
            "UPDATE device_sessions SET status = 'rejected', is_current = 0, updated_at = :updated_at 
             WHERE user_id = :user_id AND session_id != :keep_session_id AND status = 'active'",
            [
                'updated_at' => date('Y-m-d H:i:s'),
                'user_id' => $userId,
                'keep_session_id' => $keepSessionId
            ]
        );
        return 1;
    }

    /**
     * Xóa thiết bị cũ nhất (khi vượt quá limit)
     */
    public function deactivateOldestDevice(int $userId, string $excludeSessionId): ?array {
        // Tìm thiết bị active cũ nhất (không phải current)
        $oldest = $this->query(
            "SELECT * FROM device_sessions 
             WHERE user_id = :user_id AND status = 'active' AND session_id != :exclude_session_id
             ORDER BY last_activity ASC LIMIT 1",
            ['user_id' => $userId, 'exclude_session_id' => $excludeSessionId]
        );

        if (!empty($oldest)) {
            $this->updateDeviceStatus($oldest[0]['id'], 'rejected');
            return $oldest[0];
        }
        return null;
    }

    /**
     * Đặt cờ is_current cho thiết bị
     */
    public function setCurrentDevice(int $userId, int $deviceId): bool {
        // Reset tất cả is_current
        $this->query(
            "UPDATE device_sessions SET is_current = 0 WHERE user_id = :user_id",
            ['user_id' => $userId]
        );
        // Đặt is_current cho thiết bị được chọn
        $this->query(
            "UPDATE device_sessions SET is_current = 1 WHERE id = :id AND user_id = :user_id",
            ['id' => $deviceId, 'user_id' => $userId]
        );
        return true;
    }

    /**
     * Xóa các phiên cũ (cleanup - phiên không hoạt động quá 30 ngày)
     */
    public function cleanupOldSessions(int $days = 30): int {
        $this->query(
            "DELETE FROM device_sessions WHERE last_activity < DATE_SUB(NOW(), INTERVAL :days DAY) AND status != 'active'",
            ['days' => $days]
        );
        return 1;
    }

    // ==========================================
    // VERIFICATION CODE METHODS
    // ==========================================

    /**
     * Tạo mã xác thực 6 số
     */
    public function createVerificationCode(int $userId, int $deviceSessionId): string {
        // Hủy các mã cũ chưa dùng
        $this->query(
            "UPDATE device_verification_codes SET is_used = 1 
             WHERE user_id = :user_id AND device_session_id = :device_session_id AND is_used = 0",
            ['user_id' => $userId, 'device_session_id' => $deviceSessionId]
        );

        // Tạo mã 6 số ngẫu nhiên
        $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $now = date('Y-m-d H:i:s');
        $expiresAt = date('Y-m-d H:i:s', strtotime('+5 minutes'));

        $this->query(
            "INSERT INTO device_verification_codes (user_id, device_session_id, code, expires_at, last_sent_at, attempts, is_used, created_at)
             VALUES (:user_id, :device_session_id, :code, :expires_at, :last_sent_at, 0, 0, :created_at)",
            [
                'user_id' => $userId,
                'device_session_id' => $deviceSessionId,
                'code' => $code,
                'expires_at' => $expiresAt,
                'last_sent_at' => $now,
                'created_at' => $now
            ]
        );

        return $code;
    }

    /**
     * Xác thực mã OTP
     */
    public function validateVerificationCode(int $userId, string $code): ?array {
        $result = $this->query(
            "SELECT vc.*, ds.session_id as device_session_session_id 
             FROM device_verification_codes vc 
             JOIN device_sessions ds ON vc.device_session_id = ds.id
             WHERE vc.user_id = :user_id AND vc.code = :code AND vc.is_used = 0 AND vc.expires_at > NOW()
             ORDER BY vc.created_at DESC LIMIT 1",
            ['user_id' => $userId, 'code' => $code]
        );

        if (empty($result)) {
            // Tăng số lần thử sai cho mã gần nhất
            $this->query(
                "UPDATE device_verification_codes SET attempts = attempts + 1 
                 WHERE user_id = :user_id AND is_used = 0 
                 ORDER BY created_at DESC LIMIT 1",
                ['user_id' => $userId]
            );
            return null;
        }

        // Đánh dấu mã đã sử dụng
        $this->query(
            "UPDATE device_verification_codes SET is_used = 1 WHERE id = :id",
            ['id' => $result[0]['id']]
        );

        return $result[0];
    }

    /**
     * Kiểm tra cooldown gửi lại mã (2 phút)
     */
    public function canResendCode(int $deviceSessionId): array {
        $result = $this->query(
            "SELECT last_sent_at FROM device_verification_codes 
             WHERE device_session_id = :device_session_id AND is_used = 0
             ORDER BY created_at DESC LIMIT 1",
            ['device_session_id' => $deviceSessionId]
        );

        if (empty($result)) {
            return ['can_resend' => true, 'wait_seconds' => 0];
        }

        $lastSent = strtotime($result[0]['last_sent_at']);
        $cooldownEnd = $lastSent + 120; // 2 phút
        $now = time();

        if ($now >= $cooldownEnd) {
            return ['can_resend' => true, 'wait_seconds' => 0];
        }

        return ['can_resend' => false, 'wait_seconds' => $cooldownEnd - $now];
    }

    /**
     * Lấy mã xác thực đang active cho device session
     */
    public function getActiveVerificationCode(int $deviceSessionId): ?array {
        $result = $this->query(
            "SELECT * FROM device_verification_codes 
             WHERE device_session_id = :device_session_id AND is_used = 0 AND expires_at > NOW()
             ORDER BY created_at DESC LIMIT 1",
            ['device_session_id' => $deviceSessionId]
        );
        return $result[0] ?? null;
    }

    // ==========================================
    // USER AGENT PARSING
    // ==========================================

    /**
     * Parse thông tin từ User-Agent string
     */
    public function parseUserAgent(string $ua): array {
        $result = [
            'device_name' => 'Unknown Device',
            'device_type' => 'desktop',
            'browser' => 'Unknown',
            'os' => 'Unknown'
        ];

        // Detect OS
        if (preg_match('/Windows NT 10/i', $ua)) {
            $result['os'] = 'Windows 10/11';
        } elseif (preg_match('/Windows NT 6\.3/i', $ua)) {
            $result['os'] = 'Windows 8.1';
        } elseif (preg_match('/Windows NT 6\.1/i', $ua)) {
            $result['os'] = 'Windows 7';
        } elseif (preg_match('/Mac OS X ([\d_]+)/i', $ua, $matches)) {
            $version = str_replace('_', '.', $matches[1]);
            $result['os'] = 'macOS ' . $version;
        } elseif (preg_match('/Android ([\d.]+)/i', $ua, $matches)) {
            $result['os'] = 'Android ' . $matches[1];
        } elseif (preg_match('/iPhone OS ([\d_]+)/i', $ua, $matches)) {
            $version = str_replace('_', '.', $matches[1]);
            $result['os'] = 'iOS ' . $version;
        } elseif (preg_match('/iPad.*OS ([\d_]+)/i', $ua, $matches)) {
            $version = str_replace('_', '.', $matches[1]);
            $result['os'] = 'iPadOS ' . $version;
        } elseif (preg_match('/Linux/i', $ua)) {
            $result['os'] = 'Linux';
        }

        // Detect Browser
        if (preg_match('/Edg\/([\d.]+)/i', $ua, $matches)) {
            $result['browser'] = 'Edge ' . $matches[1];
        } elseif (preg_match('/OPR\/([\d.]+)/i', $ua, $matches)) {
            $result['browser'] = 'Opera ' . $matches[1];
        } elseif (preg_match('/Chrome\/([\d.]+)/i', $ua, $matches)) {
            $result['browser'] = 'Chrome ' . $matches[1];
        } elseif (preg_match('/Firefox\/([\d.]+)/i', $ua, $matches)) {
            $result['browser'] = 'Firefox ' . $matches[1];
        } elseif (preg_match('/Safari\/([\d.]+)/i', $ua) && preg_match('/Version\/([\d.]+)/i', $ua, $matches)) {
            $result['browser'] = 'Safari ' . $matches[1];
        }

        // Detect Device Type
        if (preg_match('/Mobile|Android.*Mobile|iPhone/i', $ua)) {
            $result['device_type'] = 'mobile';
        } elseif (preg_match('/iPad|Android(?!.*Mobile)|Tablet/i', $ua)) {
            $result['device_type'] = 'tablet';
        } else {
            $result['device_type'] = 'desktop';
        }

        // Construct device name
        $result['device_name'] = $result['os'] . ' - ' . $result['browser'];

        return $result;
    }

    /**
     * Lấy IP address thực của user
     */
    public function getClientIP(): string {
        $ip = '0.0.0.0';
        
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            $ip = trim($ips[0]);
        } elseif (!empty($_SERVER['REMOTE_ADDR'])) {
            $ip = $_SERVER['REMOTE_ADDR'];
        }

        return filter_var($ip, FILTER_VALIDATE_IP) ? $ip : '0.0.0.0';
    }

    /**
     * Lấy vị trí ước lượng từ IP (sử dụng free API)
     */
    public function getLocationFromIP(string $ip): string {
        // Skip for local/private IPs
        if (in_array($ip, ['127.0.0.1', '::1', '0.0.0.0']) || 
            preg_match('/^(10\.|172\.(1[6-9]|2[0-9]|3[01])\.|192\.168\.)/', $ip)) {
            return 'Local Network';
        }

        try {
            $url = "http://ip-api.com/json/{$ip}?fields=status,country,regionName,city&lang=vi";
            $context = stream_context_create([
                'http' => ['timeout' => 3]
            ]);
            $response = @file_get_contents($url, false, $context);
            
            if ($response) {
                $data = json_decode($response, true);
                if ($data && $data['status'] === 'success') {
                    $parts = array_filter([
                        $data['city'] ?? '',
                        $data['regionName'] ?? '',
                        $data['country'] ?? ''
                    ]);
                    return implode(', ', $parts) ?: 'Unknown';
                }
            }
        } catch (\Exception $e) {
            error_log("IP geolocation failed for {$ip}: " . $e->getMessage());
        }

        return 'Unknown';
    }
}
