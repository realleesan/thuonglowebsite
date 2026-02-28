<?php
/**
 * View: Quản lý truy cập (Access Management)
 * Hiển thị danh sách thiết bị và yêu cầu phê duyệt
 */

// Lấy thông tin thiết bị từ Service
require_once __DIR__ . '/../../../services/DeviceAccessService.php';
$deviceService = new DeviceAccessService();
$userId = $_SESSION['user_id'];
$deviceData = $deviceService->getDeviceList($userId);

$activeDevices = $deviceData['active_devices'] ?? [];
$pendingDevices = $deviceData['pending_devices'] ?? [];
$activeCount = $deviceData['active_count'] ?? 0;
$maxDevices = $deviceData['max_devices'] ?? 3;
?>

<div class="user-content-with-sidebar">
    <!-- User Sidebar -->
    <?php include 'app/views/_layout/user_sidebar.php'; ?>
    
    <!-- Account Content -->
    <div class="user-account">
    <!-- Account Header -->
    <div class="account-header">
        <div class="account-header-left">
            <h1>Quản lý truy cập</h1>
            <p>Kiểm soát các thiết bị đăng nhập vào tài khoản</p>
        </div>
    </div>

    <!-- Account Content Grid -->
    <div class="account-content">
        
        <!-- Device Limit Card -->
        <div class="profile-card profile-card-full">
            <div class="profile-card-header">
                <h3><i class="fas fa-shield-alt"></i> Giới hạn thiết bị</h3>
            </div>
            <div class="profile-card-content">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="text-muted">Thiết bị đang sử dụng</span>
                    <span class="fw-bold"><?php echo $activeCount; ?> / <?php echo $maxDevices; ?></span>
                </div>
                <div class="progress" style="height: 8px;">
                    <?php 
                        $percent = ($activeCount / $maxDevices) * 100;
                        $progressClass = $percent >= 100 ? 'bg-danger' : ($percent >= 70 ? 'bg-warning' : 'bg-success');
                    ?>
                    <div class="progress-bar <?php echo $progressClass; ?>" role="progressbar" style="width: <?php echo $percent; ?>%"></div>
                </div>
                <small class="text-muted d-block mt-2">
                    Tối đa <?php echo $maxDevices; ?> thiết bị có thể đăng nhập cùng lúc
                </small>
            </div>
        </div>

        <!-- Pending Approval Requests -->
        <?php if (!empty($pendingDevices)): ?>
        <div class="profile-card profile-card-full">
            <div class="profile-card-header bg-warning bg-opacity-10 border-warning">
                <h3 class="text-warning"><i class="fas fa-exclamation-triangle"></i> Yêu cầu chờ duyệt</h3>
            </div>
            <div class="profile-card-content">
                <?php foreach ($pendingDevices as $device): ?>
                <div class="device-item" id="device-<?php echo $device['id']; ?>">
                    <div class="device-icon-large">
                        <i class="fas <?php echo $device['device_type'] === 'mobile' ? 'fa-mobile-alt' : 'fa-desktop'; ?>"></i>
                    </div>
                    <div class="device-info">
                        <div class="fw-bold"><?php echo htmlspecialchars($device['device_name']); ?></div>
                        <small class="text-muted">
                            <?php echo $device['ip_address']; ?> • <?php echo htmlspecialchars($device['location']); ?>
                        </small>
                        <small class="text-muted d-block">
                            <?php echo htmlspecialchars($device['browser']); ?> trên <?php echo htmlspecialchars($device['os']); ?>
                        </small>
                    </div>
                    <div class="device-actions">
                        <button class="btn btn-success btn-sm btn-approve" data-id="<?php echo $device['id']; ?>" data-name="<?php echo htmlspecialchars($device['device_name']); ?>" data-info="<?php echo htmlspecialchars($device['ip_address'] . ' • ' . $device['location']); ?>">
                            <i class="fas fa-check"></i> Đồng ý
                        </button>
                        <button class="btn btn-outline-danger btn-sm btn-reject" data-id="<?php echo $device['id']; ?>">
                            <i class="fas fa-times"></i> Từ chối
                        </button>
                    </div>
                </div>
                <hr>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Active Devices List -->
        <div class="profile-card profile-card-full">
            <div class="profile-card-header">
                <h3><i class="fas fa-laptop"></i> Thiết bị đang đăng nhập</h3>
            </div>
            <div class="profile-card-content">
                <?php if (empty($activeDevices)): ?>
                    <div class="text-center py-4">
                        <i class="fas fa-laptop-code fa-3x text-muted mb-3"></i>
                        <p class="text-muted">Chưa có thiết bị nào đăng nhập</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($activeDevices as $device): ?>
                    <div class="device-item <?php echo $device['is_this_device'] ? 'current' : ''; ?>" id="device-<?php echo $device['id']; ?>">
                        <div class="device-icon-large">
                            <i class="fas <?php echo $device['device_type'] === 'mobile' ? 'fa-mobile-alt' : 'fa-desktop'; ?>"></i>
                        </div>
                        <div class="device-info">
                            <div class="fw-bold">
                                <?php echo htmlspecialchars($device['device_name']); ?>
                                <?php if ($device['is_this_device']): ?>
                                    <span class="badge bg-primary ms-2">Đang dùng</span>
                                <?php endif; ?>
                            </div>
                            <small class="text-muted">
                                <?php echo $device['ip_address']; ?> • <?php echo htmlspecialchars($device['location']); ?>
                            </small>
                            <small class="text-muted d-block">
                                Hoạt động: <?php echo date('d/m/Y H:i', strtotime($device['last_activity'])); ?>
                            </small>
                        </div>
                        <div class="device-actions">
                            <button class="btn btn-outline-danger btn-sm btn-remove" data-id="<?php echo $device['id']; ?>" data-current="<?php echo $device['is_this_device'] ? '1' : '0'; ?>">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </div>
                    </div>
                    <hr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

    </div>
</div>

<!-- Approve Confirmation Modal (First step) -->
<div class="modal-overlay" id="approveConfirmModal">
    <div class="modal-container" style="max-width: 400px;">
        <div class="modal-header-custom">
            <h5 class="modal-title-custom">Xác nhận phê duyệt</h5>
            <button type="button" class="btn-modal-close" onclick="closeApproveConfirmModal()" aria-label="Close">&times;</button>
        </div>
        <div class="modal-body-custom">
            <div class="text-center">
                <i class="fas fa-question-circle text-warning" style="font-size: 48px; margin-bottom: 15px;"></i>
                <p class="mb-2">Bạn có muốn phê duyệt thiết bị này không?</p>
                <p class="fw-bold" id="confirmDeviceName" style="font-size: 16px;"></p>
                <p class="text-muted small" id="confirmDeviceInfo"></p>
            </div>
        </div>
        <div class="modal-footer-custom">
            <button type="button" class="btn-modal-secondary" onclick="closeApproveConfirmModal()">Hủy</button>
            <button type="button" class="btn-modal-primary" id="btnConfirmDevice">Đồng ý</button>
        </div>
    </div>
</div>

<!-- Password Confirmation Modal (Custom) -->
<div class="modal-overlay" id="passwordConfirmModal">
    <div class="modal-container" style="max-width: 450px;">
        <div class="modal-header-custom">
            <h5 class="modal-title-custom">Xác nhận mật khẩu</h5>
            <button type="button" class="btn-modal-close" onclick="closePasswordModal()" aria-label="Close">&times;</button>
        </div>
        <div class="modal-body-custom">
            <p>Nhập mật khẩu để xác nhận phê duyệt thiết bị:</p>
            <p class="fw-bold" id="targetDeviceName"></p>
            <div class="mb-3">
                <label for="confirmPassword" class="form-label-custom">Mật khẩu</label>
                <input type="password" id="confirmPassword" class="form-control-custom" placeholder="Nhập mật khẩu">
                <div class="invalid-feedback-custom" id="passwordError">Mật khẩu không đúng.</div>
            </div>
            <div class="mb-3">
                <label for="confirmPassword2" class="form-label-custom">Nhập lại mật khẩu</label>
                <input type="password" id="confirmPassword2" class="form-control-custom" placeholder="Nhập lại mật khẩu">
                <div class="invalid-feedback-custom" id="password2Error">Mật khẩu không khớp.</div>
            </div>
        </div>
        <div class="modal-footer-custom">
            <button type="button" class="btn-modal-secondary" onclick="closePasswordModal()">Hủy</button>
            <button type="button" class="btn-modal-primary" id="btnConfirmApprove">Xác nhận</button>
        </div>
    </div>
</div>

<style>
.device-item {
    display: flex;
    align-items: center;
    padding: 15px 0;
}
.device-item.current {
    background: #f8faff;
    margin: 0 -15px;
    padding: 15px;
    border-radius: 8px;
}
.device-icon-large {
    width: 48px;
    height: 48px;
    background: #f0f0f0;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    color: #666;
    margin-right: 15px;
    flex-shrink: 0;
}
.device-item.current .device-icon-large {
    background: #e6f0ff;
    color: #0d6efd;
}
.device-info {
    flex-grow: 1;
}
.device-actions {
    display: flex;
    gap: 8px;
    flex-shrink: 0;
}
.profile-card-header h3 {
    margin: 0;
    font-size: 18px;
}
.profile-card-header h3 i {
    margin-right: 8px;
}
</style>
</div>
