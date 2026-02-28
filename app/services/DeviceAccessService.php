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
    private const APPROVED_DEVICES_COOKIE = 'approved_devices';
    private const APPROVED_FINGERPRINTS_COOKIE = 'approved_fingerprints';
    private const COOKIE_EXPIRY_DAYS = 30; // Lưu cookie trong 30 ngày

    public function __construct() {
        $this->model = new DeviceAccessModel();
        $this->emailService = new EmailNotificationService();
    }

    // ==========================================
    // COOKIE HELPER METHODS
    // ==========================================

    /**
     * Tạo fingerprint cho thiết bị hiện tại
     */
    private function getDeviceFingerprint(): string {
        $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $ip = $_SERVER['REMOTE_ADDR'] ?? '';
        $acceptLang = $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '';
        
        // Tạo fingerprint dựa vào User-Agent, IP, ngôn ngữ
        $data = $ua . '|' . $ip . '|' . $acceptLang;
        return hash('sha256', $data);
    }

    /**
     * Lấy danh sách fingerprint đã được duyệt từ cookie
     */
    private function getApprovedFingerprints(): array {
        if (!isset($_COOKIE[self::APPROVED_FINGERPRINTS_COOKIE])) {
            return [];
        }
        $value = $_COOKIE[self::APPROVED_FINGERPRINTS_COOKIE];
        $data = json_decode($value, true);
        return is_array($data) ? $data : [];
    }

    /**
     * Lưu fingerprint vào cookie khi thiết bị được duyệt
     */
    private function addFingerprintApproved(string $fingerprint, int $userId): void {
        $data = $this->getApprovedFingerprints();
        
        // Tạo key theo user_id để lưu riêng cho từng user
        $key = 'user_' . $userId;
        if (!isset($data[$key])) {
            $data[$key] = [];
        }
        
        if (!in_array($fingerprint, $data[$key])) {
            $data[$key][] = $fingerprint;
            $this->saveApprovedFingerprints($data);
        }
    }

    /**
     * Xóa fingerprint khỏi cookie khi thiết bị bị xóa
     */
    private function removeFingerprintApproved(string $fingerprint, int $userId): void {
        $data = $this->getApprovedFingerprints();
        $key = 'user_' . $userId;
        
        if (isset($data[$key])) {
            $data[$key] = array_diff($data[$key], [$fingerprint]);
            $this->saveApprovedFingerprints($data);
        }
    }

    /**
     * Kiểm tra fingerprint đã được duyệt chưa
     */
    private function isFingerprintApproved(string $fingerprint, int $userId): bool {
        $data = $this->getApprovedFingerprints();
        $key = 'user_' . $userId;
        
        if (!isset($data[$key])) {
            return false;
        }
        
        return in_array($fingerprint, $data[$key]);
    }

    /**
     * Lưu danh sách fingerprint đã duyệt vào cookie
     */
    private function saveApprovedFingerprints(array $data): void {
        $value = json_encode($data);
        $expiry = time() + (self::COOKIE_EXPIRY_DAYS * 24 * 60 * 60);
        setcookie(self::APPROVED_FINGERPRINTS_COOKIE, $value, $expiry, '/');
    }

    /**
     * Lấy danh sách thiết bị đã được duyệt từ cookie
     */
    private function getApprovedDevices(): array {
        if (!isset($_COOKIE[self::APPROVED_DEVICES_COOKIE])) {
            return [];
        }
        $value = $_COOKIE[self::APPROVED_DEVICES_COOKIE];
        $devices = json_decode($value, true);
        return is_array($devices) ? $devices : [];
    }

    /**
     * Lưu device_id vào cookie khi thiết bị được duyệt
     */
    private function addApprovedDevice(int $deviceId): void {
        $devices = $this->getApprovedDevices();
        if (!in_array($deviceId, $devices)) {
            $devices[] = $deviceId;
            $this->saveApprovedDevices($devices);
        }
    }

    /**
     * Xóa device_id khỏi cookie khi thiết bị bị xóa
     */
    private function removeApprovedDevice(int $deviceId): void {
        $devices = $this->getApprovedDevices();
        $devices = array_diff($devices, [$deviceId]);
        $this->saveApprovedDevices(array_values($devices));
    }

    /**
     * Kiểm tra device_id đã được duyệt chưa
     */
    private function isDeviceApproved(int $deviceId): bool {
        $devices = $this->getApprovedDevices();
        return in_array($deviceId, $devices);
    }

    /**
     * Lưu danh sách thiết bị đã duyệt vào cookie
     */
    private function saveApprovedDevices(array $devices): void {
        $value = json_encode($devices);
        $expiry = time() + (self::COOKIE_EXPIRY_DAYS * 24 * 60 * 60);
        setcookie(self::APPROVED_DEVICES_COOKIE, $value, $expiry, '/');
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

        // Tạo fingerprint cho thiết bị hiện tại
        $currentFingerprint = $this->getDeviceFingerprint();
        
        // Kiểm tra xem thiết bị này đã được duyệt trước đó chưa (dựa vào cookie)
        if ($this->isFingerprintApproved($currentFingerprint, $userId)) {
            // Thiết bị đã được duyệt trước đó - cho phép đăng nhập ngay
            // Xóa tất cả các thiết bị active khác để tránh vượt quá giới hạn
            $currentSessionId = session_id();
            $this->model->deactivateOtherSessions($userId, $currentSessionId);
            
            $deviceId = $this->registerCurrentDevice($userId, 'active');
            return [
                'success' => true,
                'requires_verification' => false,
                'device_id' => $deviceId
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

        // Lưu fingerprint vào cookie để đánh dấu thiết bị đã được duyệt
        $fingerprint = $this->getDeviceFingerprint();
        $this->addFingerprintApproved($fingerprint, $userId);

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
        // Debug
        $debugInfo = [
            'user_id' => $userId,
            'session_username' => isset($_SESSION['username']) ? $_SESSION['username'] : 'not_set',
            'device_session_id' => $deviceSessionId,
            'password_length' => strlen($password)
        ];
        
        // Thay vì dùng user_id từ session, dùng username để xác thực
        require_once __DIR__ . '/../models/UsersModel.php';
        $usersModel = new UsersModel();
        
        // Lấy username từ session
        $login = $_SESSION['username'] ?? $_SESSION['email'] ?? '';
        
        if (!$login) {
            return ['success' => false, 'message' => 'Không tìm thấy thông tin đăng nhập.'];
        }
        
        // Thử đăng nhập bằng username/email để xác thực
        $user = $usersModel->authenticate($login, $password);
        
        if (!$user) {
            return ['success' => false, 'message' => 'Mật khẩu không đúng.'];
        }
        
        // Xác thực thành công! Lấy user info
        $user = is_array($user) ? $user : [];
        $userId = $user['id'];
        $debugInfo['authenticated_user_id'] = $userId;
        $debugInfo['user_email'] = $user['email'] ?? '';
        
        // Không cần kiểm tra password lại vì authenticate() đã xác thực rồi
        // Tiếp tục xử lý approve device

        // Kiểm tra thiết bị có pending không
        $device = $this->model->findByIdAndUser($deviceSessionId, $userId);
        if (!$device || $device['status'] !== 'pending') {
            return ['success' => false, 'message' => 'Thiết bị không hợp lệ hoặc đã được xử lý.', 'debug' => $debugInfo];
        }

        // Activate thiết bị mới
        $this->model->updateDeviceStatus($deviceSessionId, 'active');
        $this->model->setCurrentDevice($userId, $deviceSessionId);

        // Lưu fingerprint vào cookie để đánh dấu thiết bị đã được duyệt
        $fingerprint = $this->getDeviceFingerprint();
        $this->addFingerprintApproved($fingerprint, $userId);

        // Hủy tất cả các thiết bị khác (logout các thiết bị cũ bao gồm cả thiết bị hiện tại)
        $this->model->deactivateOtherSessions($userId, $device['session_id']);

        return [
            'success' => true,
            'message' => 'Đã phê duyệt thiết bị thành công! Các thiết bị khác đã được đăng xuất.',
            'logged_out_other_devices' => true
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
        
        // Xóa fingerprint khỏi cookie
        $fingerprint = $this->getDeviceFingerprint();
        $this->removeFingerprintApproved($fingerprint, $userId);
        
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

    /**
     * Đăng nhập tự động sau khi thiết bị được phê duyệt
     * Sử dụng device_session_id để xác định user và tạo session
     */
    public function autoLogin(int $deviceSessionId): array {
        // Tìm thiết bị theo session ID
        $device = $this->model->find($deviceSessionId);
        
        if (!$device) {
            return ['success' => false, 'message' => 'Thiết bị không hợp lệ.'];
        }
        
        // Kiểm tra trạng thái thiết bị
        if ($device['status'] !== 'active') {
            return ['success' => false, 'message' => 'Thiết bị chưa được phê duyệt.', 'status' => $device['status']];
        }
        
        // Lấy thông tin user
        $userId = $device['user_id'];
        require_once __DIR__ . '/../models/UsersModel.php';
        $usersModel = new UsersModel();
        $user = $usersModel->find($userId);
        
        if (!$user) {
            return ['success' => false, 'message' => 'Không tìm thấy tài khoản.'];
        }
        
        // Cập nhật session_id cho thiết bị (vì session_id mới khác với session_id lưu trong DB)
        $this->model->updateSessionId($deviceSessionId, session_id());
        
        // Đánh dấu đây là thiết bị hiện tại
        $this->model->setCurrentDevice($userId, $deviceSessionId);
        
        // Lưu fingerprint vào cookie để đánh dấu thiết bị đã được duyệt
        $fingerprint = $this->getDeviceFingerprint();
        $this->addFingerprintApproved($fingerprint, $userId);
        
        return [
            'success' => true,
            'message' => 'Đăng nhập thành công!',
            'user' => [
                'id' => $user['id'],
                'name' => $user['name'],
                'email' => $user['email'],
                'username' => $user['username'] ?? '',
                'role' => $user['role']
            ]
        ];
    }
}
