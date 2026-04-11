<?php
/**
 * Profile - Cài Đặt Hồ Sơ
 * Quản lý thông tin cá nhân và tài khoản ngân hàng
 */

// 1. Khởi tạo View & ServiceManager
require_once __DIR__ . '/../../../../core/view_init.php';

// 2. Chọn service affiliate (được inject từ index.php)
$service = isset($currentService) ? $currentService : ($affiliateService ?? null);

// Initialize data variables
$profileData = [
    'name' => '',
    'email' => '',
    'affiliate_link' => '',
    'referral_code' => ''
];

try {
    if ($service) {
        // Get current affiliate ID from session
        $affiliateId = $_SESSION['user_id'] ?? 0;
        
        // Validate affiliate is logged in
        if ($affiliateId <= 0) {
            throw new Exception('Vui lòng đăng nhập để xem hồ sơ');
        }
        
        // Get dashboard data FIRST for affiliate info (needed by header)
        $dashboardData = $service->getDashboardData($affiliateId);
        $affiliateInfo = $dashboardData['affiliate'] ?? [
            'name' => '',
            'email' => ''
        ];
        
        $profileData = $dashboardData['affiliate'] ?? $profileData;
        
        // Get bank list from service
        $bankList = $service->getBankList($affiliateId) ?? [
            'Vietcombank', 'Techcombank', 'VietinBank', 'BIDV', 'ACB', 'MB Bank', 'VPBank'
        ];
    }
} catch (Exception $e) {
    $errorHandler->handleViewError($e, 'affiliate_profile', []);
    error_log('Profile Settings Error: ' . $e->getMessage());
    $bankList = ['Vietcombank', 'Techcombank', 'VietinBank', 'BIDV', 'ACB', 'MB Bank', 'VPBank'];
}

// Page title
$page_title = 'Cài Đặt Hồ Sơ';

// Include master layout
ob_start();
?>

<!-- Profile Tabs -->
<div class="profile-tabs">
    <button type="button" class="tab-btn active" data-tab="personal">
        <i class="fas fa-user"></i>
        <span>Thông Tin Cá Nhân</span>
    </button>
    <button type="button" class="tab-btn" data-tab="bank">
        <i class="fas fa-university"></i>
        <span>Tài Khoản Ngân Hàng</span>
    </button>
    <button type="button" class="tab-btn" data-tab="security">
        <i class="fas fa-shield-alt"></i>
        <span>Bảo Mật</span>
    </button>
</div>

<!-- Tab Content -->
<div class="profile-content">
    <!-- Personal Info Tab -->
    <div class="tab-pane active" id="personal-tab">
        <div class="profile-card">
            <div class="profile-card-header">
                <h3 class="profile-card-title">
                    <i class="fas fa-user"></i>
                    Thông Tin Cá Nhân
                </h3>
            </div>
            <div class="profile-card-body">
                <form id="personalInfoForm" class="profile-form">
                    <!-- Avatar Upload -->
                    <div class="form-group">
                        <label class="form-label">Ảnh Đại Diện</label>
                        <div class="avatar-upload">
                            <div class="avatar-preview">
                                <img src="<?php echo htmlspecialchars($profileData['avatar']); ?>" 
                                     alt="Avatar" 
                                     id="avatarPreview"
                                     onerror="this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%22120%22 height=%22120%22%3E%3Crect width=%22120%22 height=%22120%22 fill=%22%23e5e7eb%22/%3E%3Ctext x=%2250%25%22 y=%2250%25%22 text-anchor=%22middle%22 dy=%22.3em%22 fill=%22%236b7280%22 font-family=%22Arial%22 font-size=%2240%22%3E👤%3C/text%3E%3C/svg%3E'">
                            </div>
                            <div class="avatar-actions">
                                <button type="button" class="btn btn-sm btn-primary" onclick="document.getElementById('avatarInput').click()">
                                    <i class="fas fa-upload"></i>
                                    <span>Tải Ảnh Lên</span>
                                </button>
                                <input type="file" id="avatarInput" accept="image/*" style="display: none;" onchange="previewAvatar(this)">
                                <small class="form-help">
                                    <i class="fas fa-info-circle"></i>
                                    JPG, PNG. Tối đa 2MB
                                </small>
                            </div>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label required">Họ và Tên</label>
                            <input type="text" 
                                   class="form-input" 
                                   name="name" 
                                   value="<?php echo htmlspecialchars($profileData['name']); ?>"
                                   required>
                        </div>

                        <div class="form-group">
                            <label class="form-label required">Email</label>
                            <input type="email" 
                                   class="form-input" 
                                   name="email" 
                                   value="<?php echo htmlspecialchars($profileData['email']); ?>"
                                   required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label required">Số Điện Thoại</label>
                            <input type="tel" 
                                   class="form-input" 
                                   name="phone" 
                                   value="<?php echo htmlspecialchars($profileData['phone']); ?>"
                                   required>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Ngày Tham Gia</label>
                            <input type="text" 
                                   class="form-input" 
                                   value="<?php echo isset($profileData['joined_date']) ? date('d/m/Y', strtotime($profileData['joined_date'])) : 'N/A'; ?>"
                                   readonly>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Địa Chỉ</label>
                        <textarea class="form-textarea" 
                                  name="address" 
                                  rows="3"><?php echo htmlspecialchars($profileData['address'] ?? ''); ?></textarea>
                    </div>

                    <!-- Affiliate Info (Read-only) -->
                    <div class="info-box">
                        <div class="info-box-header">
                            <i class="fas fa-id-badge"></i>
                            <span>Thông Tin Affiliate</span>
                        </div>
                        <div class="info-box-body">
                            <div class="info-row">
                                <span class="info-label">Affiliate ID:</span>
                                <span class="info-value">
                                    <code><?php echo htmlspecialchars($profileData['affiliate_id'] ?? 'N/A'); ?></code>
                                </span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Cấp Độ:</span>
                                <span class="info-value">
                                    <span class="badge badge-primary">
                                        <?php echo htmlspecialchars($profileData['tier_name']); ?>
                                    </span>
                                </span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Trạng Thái:</span>
                                <span class="info-value">
                                    <span class="badge badge-success">
                                        <i class="fas fa-check-circle"></i>
                                        Đang Hoạt Động
                                    </span>
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-save"></i>
                            <span>Lưu Thay Đổi</span>
                        </button>
                        <button type="button" class="btn btn-outline btn-lg" onclick="resetPersonalForm()">
                            <i class="fas fa-undo"></i>
                            <span>Đặt Lại</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bank Account Tab -->
    <div class="tab-pane" id="bank-tab">
        <div class="profile-card">
            <div class="profile-card-header">
                <h3 class="profile-card-title">
                    <i class="fas fa-university"></i>
                    Tài Khoản Ngân Hàng
                </h3>
                <p class="profile-card-subtitle">Quản lý tài khoản để nhận tiền rút</p>
            </div>
            <div class="profile-card-body">
                <form id="bankAccountForm" class="profile-form">
                    <div class="form-group">
                        <label class="form-label required">Tên Ngân Hàng</label>
                        <select class="form-select" name="bank_name" required>
                            <option value="">-- Chọn ngân hàng --</option>
                            <?php foreach ($bankList as $bank): ?>
                            <option value="<?php echo htmlspecialchars($bank); ?>" <?php echo ($profileData['bank_info']['bank_name'] ?? '') === $bank ? 'selected' : ''; ?>><?php echo htmlspecialchars($bank); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label required">Số Tài Khoản</label>
                        <input type="text" 
                               class="form-input" 
                               name="account_number" 
                               value="<?php echo htmlspecialchars($profileData['bank_info']['account_number']); ?>"
                               placeholder="Nhập số tài khoản"
                               required>
                    </div>

                    <div class="form-group">
                        <label class="form-label required">Chủ Tài Khoản</label>
                        <input type="text" 
                               class="form-input" 
                               name="account_holder" 
                               value="<?php echo htmlspecialchars($profileData['bank_info']['account_holder']); ?>"
                               placeholder="VD: NGUYEN VAN A"
                               style="text-transform: uppercase;"
                               required>
                        <small class="form-help">
                            <i class="fas fa-info-circle"></i>
                            Nhập chữ IN HOA, không dấu
                        </small>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Chi Nhánh</label>
                        <input type="text" 
                               class="form-input" 
                               name="branch" 
                               value="<?php echo htmlspecialchars($profileData['bank_info']['branch']); ?>"
                               placeholder="VD: Chi nhánh TP.HCM">
                    </div>

                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        <div class="alert-content">
                            <strong>Lưu ý quan trọng:</strong>
                            <ul style="margin: 8px 0 0 20px; padding: 0;">
                                <li>Thông tin ngân hàng phải chính xác để nhận tiền rút</li>
                                <li>Tên chủ tài khoản phải trùng với tên đăng ký</li>
                                <li>Sau khi lưu, cần xác minh lại trước khi rút tiền</li>
                            </ul>
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-save"></i>
                            <span>Lưu Thông Tin</span>
                        </button>
                        <button type="button" class="btn btn-outline btn-lg" onclick="resetBankForm()">
                            <i class="fas fa-undo"></i>
                            <span>Đặt Lại</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Security Tab -->
    <div class="tab-pane" id="security-tab">
        <div class="profile-card">
            <div class="profile-card-header">
                <h3 class="profile-card-title">
                    <i class="fas fa-key"></i>
                    Đổi Mật Khẩu
                </h3>
                <p class="profile-card-subtitle">Cập nhật mật khẩu để bảo mật tài khoản</p>
            </div>
            <div class="profile-card-body">
                <form id="changePasswordForm" class="profile-form">
                    <div class="form-group">
                        <label class="form-label required">Mật Khẩu Hiện Tại</label>
                        <div class="password-input-wrapper">
                            <input type="password" 
                                   class="form-input" 
                                   name="current_password" 
                                   id="currentPassword"
                                   placeholder="Nhập mật khẩu hiện tại"
                                   required>
                            <button type="button" class="btn-toggle-password" onclick="togglePassword('currentPassword')">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label required">Mật Khẩu Mới</label>
                        <div class="password-input-wrapper">
                            <input type="password" 
                                   class="form-input" 
                                   name="new_password" 
                                   id="newPassword"
                                   placeholder="Nhập mật khẩu mới"
                                   required>
                            <button type="button" class="btn-toggle-password" onclick="togglePassword('newPassword')">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <div class="password-strength" id="passwordStrength" style="display: none;">
                            <div class="strength-bar">
                                <div class="strength-fill" id="strengthFill"></div>
                            </div>
                            <span class="strength-text" id="strengthText"></span>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label required">Xác Nhận Mật Khẩu</label>
                        <div class="password-input-wrapper">
                            <input type="password" 
                                   class="form-input" 
                                   name="confirm_password" 
                                   id="confirmPassword"
                                   placeholder="Nhập lại mật khẩu mới"
                                   required>
                            <button type="button" class="btn-toggle-password" onclick="togglePassword('confirmPassword')">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <div class="password-requirements">
                        <h4>Yêu cầu mật khẩu:</h4>
                        <ul>
                            <li id="req-length">
                                <i class="fas fa-circle"></i>
                                <span>Tối thiểu 8 ký tự</span>
                            </li>
                            <li id="req-uppercase">
                                <i class="fas fa-circle"></i>
                                <span>Ít nhất 1 chữ hoa</span>
                            </li>
                            <li id="req-lowercase">
                                <i class="fas fa-circle"></i>
                                <span>Ít nhất 1 chữ thường</span>
                            </li>
                            <li id="req-number">
                                <i class="fas fa-circle"></i>
                                <span>Ít nhất 1 số</span>
                            </li>
                        </ul>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-key"></i>
                            <span>Đổi Mật Khẩu</span>
                        </button>
                        <button type="button" class="btn btn-outline btn-lg" onclick="resetPasswordForm()">
                            <i class="fas fa-undo"></i>
                            <span>Đặt Lại</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../_layout/affiliate_master.php';
?>
