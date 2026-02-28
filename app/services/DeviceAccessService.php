<?php
/**
 * DeviceAccessService - Service quản lý truy cập đa thiết bị
 * Xử lý logic nghiệp vụ: kiểm tra device, gửi OTP, phê duyệt, từ chối
 */

require_once __DIR__ . '/ServiceInterface.php';
require_once __DIR__ . '/../models/DeviceAccessModel.php';
require_once __DIR__ . '/EmailNotificationService.php';

class DeviceAccessService implements ServiceInterface {
    private DeviceAccessModel $model;
    private EmailNotificationService $emailService;

    private const MAX_DEVICES = 3; // Tối đa 3 thiết bị có thể sử dụng
    private const OTP_EXPIRY_MINUTES = 5;
    private const RESEND_COOLDOWN_SECONDS = 120;
    private const MAX_OTP_ATTEMPTS = 5;

    public function __construct() {
        $this->model = new DeviceAccessModel();
        $this->emailService = new EmailNotificationService();
    }

    /**
     * ServiceInterface implementation
     */
    public function getData(string $method, array $params = []): array {
        try {
            switch ($method) {
                case 'getDeviceList':
                    return $this->getDeviceList($params['user_id'] ?? 0);
                case 'getPendingDevices':
                    return ['success' => true, 'devices' => $this->model->getPendingDevices($params['user_id'] ?? 0)];
                case 'checkDeviceOnLogin':
                    return $this->checkDeviceOnLogin($params['user_id'] ?? 0);
                default:
                    throw new \Exception("Unknown method: $method");
            }
        } catch (\Exception $e) {
            return $this->handleError($e, ['method' => $method]);
        }
    }

    public function getModel(string $modelName) {
        if ($modelName === 'DeviceAccessModel') {
            return $this->model;
        }
        return null;
    }

    public function handleError(\Exception $e, array $context = []): array {
        error_log("DeviceAccessService Error: " . $e->getMessage() . " Context: " . json_encode($context));
        return [
            'success' => false,
            'message' => $e->getMessage()
        ];
    }

    // ==========================================
    // LOGIN FLOW
    // ==========================================

    /**
     * Kiểm tra thiết bị khi đăng nhập
     * Trả về: cần xác thực hay không
     */
    public function checkDeviceOnLogin(int $userId): array {
        $activeCount = $this->model->getActiveDeviceCount($userId);
        $currentSessionId = session_id();

        // Kiểm tra xem thiết bị này đã có phiên active chưa
        $existingDevice = $this->model->findByUserAndSession($userId, $currentSessionId);
        if ($existingDevice && $existingDevice['status'] === 'active') {
            // Thiết bị đã được xác thực - cập nhật last_activity
            $this->model->updateLastActivity($existingDevice['id']);
            return [
                'success' => true,
                'requires_verification' => false,
                'device_id' => $existingDevice['id']
            ];
        }

        // Nếu là thiết bị đầu tiên - cho phép đăng nhập ngay
        if ($activeCount === 0) {
            $deviceId = $this->registerCurrentDevice($userId, 'active');
            return [
                'success' => true,
                'requires_verification' => false,
                'device_id' => $deviceId
            ];
        }

        // Kiểm tra xem có thiết bị nào của user đang pending với IP và user-agent tương tự không
        // (Trường hợp: thiết bị đã được duyệt nhưng session_id thay đổi)
        $ua = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
        $ip = $this->model->getClientIP();
        $deviceInfo = $this->model->parseUserAgent($ua);
        
        // Tìm thiết bị pending với cùng IP và device type
        $pendingDevices = $this->model->getPendingDevices($userId);
        foreach ($pendingDevices as $pending) {
            // Nếu thiết bị pending có cùng IP và device type, tự động activate
            if ($pending['ip_address'] === $ip && 
                $pending['device_type'] === $deviceInfo['device_type']) {
                // Cập nhật session_id mới và activate
                $this->model->updateSessionId($pending['id'], $currentSessionId);
                $this->model->updateDeviceStatus($pending['id'], 'active');
                $this->model->setCurrentDevice($userId, $pending['id']);
                return [
                    'success' => true,
                    'requires_verification' => false,
                    'device_id' => $pending['id'],
                    'auto_activated' => true
                ];
            }
        }

        // Thiết bị thứ 2 trở đi - cần xác thực
        $deviceId = $this->registerCurrentDevice($userId, 'pending');
        return [
            'success' => true,
            'requires_verification' => true,
            'device_session_id' => $deviceId,
            'active_count' => $activeCount,
            'max_devices' => self::MAX_DEVICES,
            'message' => 'Tài khoản đã có thiết bị đăng nhập. Vui lòng xác thực để tiếp tục.'
        ];
    }

    /**
     * Đăng ký thiết bị hiện tại vào database
     */
    public function registerCurrentDevice(int $userId, string $status = 'active'): int {
        $ua = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
        $deviceInfo = $this->model->parseUserAgent($ua);
        $ip = $this->model->getClientIP();
        $location = $this->model->getLocationFromIP($ip);

        $deviceId = $this->model->createDeviceSession([
            'user_id' => $userId,
            'session_id' => session_id(),
            'device_name' => $deviceInfo['device_name'],
            'device_type' => $deviceInfo['device_type'],
            'browser' => $deviceInfo['browser'],
            'os' => $deviceInfo['os'],
            'ip_address' => $ip,
            'location' => $location,
            'status' => $status,
            'is_current' => ($status === 'active') ? 1 : 0
        ]);

        if ($status === 'active') {
            $this->model->setCurrentDevice($userId, $deviceId);
        }

        return $deviceId;
    }

    // ==========================================
    // EMAIL OTP VERIFICATION
    // ==========================================

    /**
     * Bắt đầu xác thực email - gửi OTP
     */
    public function initiateEmailVerification(int $userId, string $inputEmail, int $deviceSessionId): array {
        // Lấy thông tin user để kiểm tra email
        require_once __DIR__ . '/../models/UsersModel.php';
        $usersModel = new UsersModel();
        $user = $usersModel->find($userId);

        if (!$user) {
            return ['success' => false, 'message' => 'Không tìm thấy tài khoản.'];
        }

        // Kiểm tra email có khớp không
        if (strtolower(trim($inputEmail)) !== strtolower(trim($user['email']))) {
            return ['success' => false, 'message' => 'Email không khớp với tài khoản đăng ký.'];
        }

        // Kiểm tra cooldown
        $cooldown = $this->model->canResendCode($deviceSessionId);
        if (!$cooldown['can_resend']) {
            return [
                'success' => false,
                'message' => 'Vui lòng chờ ' . $cooldown['wait_seconds'] . ' giây trước khi gửi lại mã.',
                'wait_seconds' => $cooldown['wait_seconds']
            ];
        }

        // Tạo và gửi OTP
        $code = $this->model->createVerificationCode($userId, $deviceSessionId);
        
        // Lấy thông tin thiết bị để đưa vào email
        $device = $this->model->find($deviceSessionId);
        $deviceInfo = $device ? [
            'device_name' => $device['device_name'],
            'ip_address' => $device['ip_address'],
            'location' => $device['location'],
            'browser' => $device['browser'],
            'os' => $device['os']
        ] : [];

        // Gửi email
        $emailSent = $this->sendVerificationEmail(
            $user['email'],
            $user['name'] ?? 'User',
            $code,
            $deviceInfo
        );

        if (!$emailSent) {
            return ['success' => false, 'message' => 'Không thể gửi email. Vui lòng thử lại sau.'];
        }

        // Mask email
        $maskedEmail = $this->maskEmail($user['email']);

        return [
            'success' => true,
            'message' => 'Mã xác thực đã được gửi đến ' . $maskedEmail,
            'masked_email' => $maskedEmail,
            'expires_in' => self::OTP_EXPIRY_MINUTES * 60,
            'cooldown' => self::RESEND_COOLDOWN_SECONDS
        ];
    }

    /**
     * Xác thực mã OTP
     */
    public function verifyOTP(int $userId, string $code, int $deviceSessionId): array {
        // Kiểm tra mã
        $verification = $this->model->validateVerificationCode($userId, $code);

        if (!$verification) {
            // Kiểm tra số lần thử
            $activeCode = $this->model->getActiveVerificationCode($deviceSessionId);
            $attemptsLeft = $activeCode ? (self::MAX_OTP_ATTEMPTS - $activeCode['attempts']) : 0;

            if ($attemptsLeft <= 0) {
                return [
                    'success' => false,
                    'message' => 'Đã vượt quá số lần thử. Vui lòng yêu cầu mã mới.',
                    'attempts_left' => 0
                ];
            }

            return [
                'success' => false,
                'message' => 'Mã xác thực không đúng hoặc đã hết hạn.',
                'attempts_left' => $attemptsLeft
            ];
        }

        // OTP đúng - activate device
        $this->model->updateDeviceStatus($deviceSessionId, 'active');
        $this->model->setCurrentDevice($userId, $deviceSessionId);

        // Lấy session_id của thiết bị mới
        $newDevice = $this->model->find($deviceSessionId);
        $newSessionId = $newDevice ? $newDevice['session_id'] : '';

        // Hủy tất cả các thiết bị khác (logout các thiết bị cũ)
        if ($newSessionId) {
            $this->model->deactivateOtherSessions($userId, $newSessionId);
        }

        return [
            'success' => true,
            'message' => 'Xác thực thành công! Đang đăng nhập...',
            'device_activated' => true,
            'logged_out_other_devices' => true
        ];
    }

    /**
     * Gửi lại mã OTP
     */
    public function resendOTP(int $userId, int $deviceSessionId): array {
        // Kiểm tra device session có thuộc user không
        $device = $this->model->findByIdAndUser($deviceSessionId, $userId);
        if (!$device) {
            return ['success' => false, 'message' => 'Thiết bị không hợp lệ.'];
        }

        // Kiểm tra cooldown
        $cooldown = $this->model->canResendCode($deviceSessionId);
        if (!$cooldown['can_resend']) {
            return [
                'success' => false,
                'message' => 'Vui lòng chờ ' . $cooldown['wait_seconds'] . ' giây.',
                'wait_seconds' => $cooldown['wait_seconds']
            ];
        }

        // Lấy email user
        require_once __DIR__ . '/../models/UsersModel.php';
        $usersModel = new UsersModel();
        $user = $usersModel->find($userId);

        if (!$user) {
            return ['success' => false, 'message' => 'Không tìm thấy tài khoản.'];
        }

        // Tạo mã mới và gửi
        $code = $this->model->createVerificationCode($userId, $deviceSessionId);
        $emailSent = $this->sendVerificationEmail(
            $user['email'],
            $user['name'] ?? 'User',
            $code,
            [
                'device_name' => $device['device_name'],
                'ip_address' => $device['ip_address'],
                'location' => $device['location']
            ]
        );

        if (!$emailSent) {
            return ['success' => false, 'message' => 'Không thể gửi email. Vui lòng thử lại sau.'];
        }

        $maskedEmail = $this->maskEmail($user['email']);
        return [
            'success' => true,
            'message' => 'Mã mới đã được gửi đến ' . $maskedEmail,
            'expires_in' => self::OTP_EXPIRY_MINUTES * 60,
            'cooldown' => self::RESEND_COOLDOWN_SECONDS
        ];
    }

    // ==========================================
    // DEVICE APPROVAL (FROM DEVICE A)
    // ==========================================

    /**
     * Phê duyệt thiết bị từ thiết bị hiện tại (Device A approves Device B)
     */
    public function approveDevice(int $userId, int $deviceSessionId, string $password): array {
        // Debug - Trả về thông tin để debug
        $debugInfo = [
            'user_id' => $userId,
            'session_user_id' => isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'not_set',
            'device_session_id' => $deviceSessionId,
            'password_length' => strlen($password)
        ];
        
        // Lấy user trực tiếp từ database để có password (không bị ẩn bởi hidden field)
        require_once __DIR__ . '/../models/UsersModel.php';
        $usersModel = new UsersModel();
        
        // Sử dụng query trực tiếp để lấy password
        $user = $usersModel->query("SELECT * FROM users WHERE id = ?", [$userId]);
        
        if (empty($user)) {
            return ['success' => false, 'message' => 'Không tìm thấy tài khoản.', 'debug' => $debugInfo];
        }
        
        $user = $user[0];
        
        $debugInfo['user_email'] = $user['email'];
        $debugInfo['stored_password_length'] = strlen($user['password'] ?? '');
        $debugInfo['stored_password_prefix'] = substr($user['password'] ?? '', 0, 10);
        
        // Kiểm tra nếu không có mật khẩu
        if (empty($user['password'])) {
            return ['success' => false, 'message' => 'Tài khoản này không có mật khẩu. Bạn vui lòng đặt mật khẩu trong phần cài đặt tài khoản trước khi phê duyệt thiết bị.', 'debug' => $debugInfo];
        }
        
        // Thử password_verify trước
        $passwordValid = password_verify($password, $user['password']);
        $debugInfo['password_verify_result'] = $passwordValid;
        
        // Nếu không đúng, thử với MD5 (cho các tài khoản cũ)
        if (!$passwordValid) {
            $md5Input = md5($password);
            $md5Match = ($md5Input === $user['password']);
            $debugInfo['md5_input'] = $md5Input;
            $debugInfo['md5_match'] = $md5Match;
            
            if ($md5Match) {
                $passwordValid = true;
            }
        }
        
        if (!$passwordValid) {
            return ['success' => false, 'message' => 'Mật khẩu không đúng.', 'debug' => $debugInfo];
        }

        // Kiểm tra thiết bị có pending không
        $device = $this->model->findByIdAndUser($deviceSessionId, $userId);
        if (!$device || $device['status'] !== 'pending') {
            return ['success' => false, 'message' => 'Thiết bị không hợp lệ hoặc đã được xử lý.', 'debug' => $debugInfo];
        }

        // Activate thiết bị mới
        $this->model->updateDeviceStatus($deviceSessionId, 'active');
        $this->model->setCurrentDevice($userId, $deviceSessionId);

        // Deactivate các thiết bị khác để Device A bị đăng xuất
        // Nhưng giữ nguyên session của Device B
        $this->model->deactivateOtherSessions($userId, $device['session_id']);

        return [
            'success' => true,
            'message' => 'Đã phê duyệt thiết bị thành công!',
            'logged_out_other_devices' => true,
            'approved_device_session_id' => $deviceSessionId
        ];
    }

    /**
     * Từ chối thiết bị
     */
    public function rejectDevice(int $userId, int $deviceSessionId): array {
        $device = $this->model->findByIdAndUser($deviceSessionId, $userId);
        if (!$device) {
            return ['success' => false, 'message' => 'Thiết bị không hợp lệ.'];
        }

        $this->model->updateDeviceStatus($deviceSessionId, 'rejected');

        return [
            'success' => true,
            'message' => 'Đã từ chối thiết bị.'
        ];
    }

    // ==========================================
    // DEVICE MANAGEMENT
    // ==========================================

    /**
     * Xóa thiết bị khỏi danh sách
     */
    public function removeDevice(int $userId, int $deviceId): array {
        $device = $this->model->findByIdAndUser($deviceId, $userId);
        if (!$device) {
            return ['success' => false, 'message' => 'Thiết bị không tồn tại.'];
        }

        // Chỉ logout nếu xóa chính thiết bị hiện tại (session_id khớp)
        // KHÔNG dựa vào is_current vì nó có thể bị sai
        $isCurrentDevice = ($device['session_id'] === session_id());
        
        // Debug log
        error_log("removeDevice: userId=$userId, device_id=$deviceId, device_session=" . $device['session_id'] . ", current_session=" . session_id() . ", isCurrentDevice=$isCurrentDevice");
        
        // Đánh dấu thiết bị là rejected
        $this->model->deleteDeviceSession($deviceId);

        return [
            'success' => true,
            'message' => $isCurrentDevice ? 'Đã xóa thiết bị hiện tại. Bạn sẽ bị đăng xuất.' : 'Đã xóa thiết bị.',
            'is_current_device' => $isCurrentDevice,
            'should_logout' => $isCurrentDevice
        ];
    }

    /**
     * Lấy danh sách tất cả thiết bị
     */
    public function getDeviceList(int $userId): array {
        $activeDevices = $this->model->getActiveDevices($userId);
        $pendingDevices = $this->model->getPendingDevices($userId);
        $currentSessionId = session_id();

        // Đánh dấu thiết bị hiện tại
        foreach ($activeDevices as &$device) {
            $device['is_this_device'] = ($device['session_id'] === $currentSessionId);
        }
        foreach ($pendingDevices as &$device) {
            $device['is_this_device'] = ($device['session_id'] === $currentSessionId);
        }

        return [
            'success' => true,
            'active_devices' => $activeDevices,
            'pending_devices' => $pendingDevices,
            'active_count' => count($activeDevices),
            'max_devices' => self::MAX_DEVICES
        ];
    }

    /**
     * Poll trạng thái thiết bị (Device B poll để biết đã được duyệt/từ chối)
     */
    public function pollDeviceStatus(int $deviceSessionId): array {
        $device = $this->model->find($deviceSessionId);
        if (!$device) {
            return ['success' => false, 'status' => 'not_found'];
        }

        return [
            'success' => true,
            'status' => $device['status'],
            'device_id' => $device['id']
        ];
    }

    // ==========================================
    // HELPER METHODS
    // ==========================================

    /**
     * Mask email address (ví dụ: t***t@gmail.com)
     */
    private function maskEmail(string $email): string {
        $parts = explode('@', $email);
        if (count($parts) !== 2) return '***@***';

        $name = $parts[0];
        $domain = $parts[1];

        if (strlen($name) <= 2) {
            $masked = $name[0] . '***';
        } else {
            $masked = $name[0] . str_repeat('*', strlen($name) - 2) . $name[strlen($name) - 1];
        }

        return $masked . '@' . $domain;
    }

    /**
     * Gửi email xác thực thiết bị
     */
    private function sendVerificationEmail(string $email, string $userName, string $code, array $deviceInfo = []): bool {
        try {
            // Sử dụng EmailNotificationService có sẵn
            return $this->emailService->sendDeviceVerificationCode($email, $userName, $code, $deviceInfo);
        } catch (\Exception $e) {
            error_log("Failed to send device verification email: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Kiểm tra phiên thiết bị hiện tại có còn active không
     * Được gọi từ AuthService::isAuthenticated() để xác nhận user vẫn còn quyền truy cập
     */
    public function checkCurrentDeviceSession(int $userId): bool {
        $currentSessionId = session_id();
        
        // Tìm thiết bị với session_id hiện tại
        $device = $this->model->findByUserAndSession($userId, $currentSessionId);
        
        // Nếu không tìm thấy thiết bị, có thể do session_id đã được regeneration
        // Thử tìm thiết bị active gần nhất của user để cập nhật session_id
        if (!$device) {
            error_log("checkCurrentDeviceSession: user=$userId, session=$currentSessionId, device=NOT_FOUND - checking for session regeneration");
            
            // Tìm thiết bị active của user
            $activeDevices = $this->model->getActiveDevices($userId);
            if (!empty($activeDevices)) {
                // Lấy thiết bị đầu tiên (thường là thiết bị hiện tại)
                $device = $activeDevices[0];
                
                // Cập nhật session_id cho thiết bị
                $this->model->updateSessionId($device['id'], $currentSessionId);
                error_log("checkCurrentDeviceSession: updated session_id for device " . $device['id']);
                
                // Kiểm tra lại status
                if ($device['status'] !== 'active') {
                    error_log("checkCurrentDeviceSession: user=$userId, device status=" . $device['status']);
                    return false;
                }
                
                return true;
            }
            
            // Không có thiết bị active nào
            return false;
        }
        
        if ($device['status'] !== 'active') {
            // Log để debug
            error_log("checkCurrentDeviceSession: user=$userId, session=$currentSessionId, device status=" . $device['status']);
            return false;
        }
        
        error_log("checkCurrentDeviceSession: user=$userId, session=$currentSessionId, device FOUND, status=" . $device['status']);
        return true;
    }
}
