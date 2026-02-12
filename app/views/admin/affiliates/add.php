<?php
$service = isset($currentService) ? $currentService : ($adminService ?? null);

try {
    // Get available users for dropdown (users without affiliate accounts)
    $usersData = $service->getAvailableUsersForAffiliate();
    $available_users = $usersData['users'] ?? [];
    
} catch (Exception $e) {
    $errorHandler->logError('Admin Affiliates Add View Error', $e);
    $available_users = [];
}

// Handle form submission
$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validation
    $user_id = (int)($_POST['user_id'] ?? 0);
    $commission_rate = (float)($_POST['commission_rate'] ?? 0);
    $referral_code = trim($_POST['referral_code'] ?? '');
    $status = $_POST['status'] ?? 'pending';
    
    if ($user_id <= 0) {
        $errors[] = 'Vui lòng chọn người dùng';
    }
    
    if ($commission_rate <= 0 || $commission_rate > 50) {
        $errors[] = 'Tỷ lệ hoa hồng phải từ 0.1% đến 50%';
    }
    
    if (empty($referral_code)) {
        $errors[] = 'Mã giới thiệu không được để trống';
    } elseif (strlen($referral_code) < 3) {
        $errors[] = 'Mã giới thiệu phải có ít nhất 3 ký tự';
    } elseif (!preg_match('/^[A-Z0-9]+$/', $referral_code)) {
        $errors[] = 'Mã giới thiệu chỉ được chứa chữ cái in hoa và số';
    } elseif ($service->checkReferralCodeExists($referral_code)) {
        $errors[] = 'Mã giới thiệu đã tồn tại';
    }
    
    // If no errors, save to database
    if (empty($errors)) {
        $affiliateData = [
            'user_id' => $user_id,
            'commission_rate' => $commission_rate,
            'referral_code' => strtoupper($referral_code),
            'status' => $status
        ];
        $created = $service->createAffiliate($affiliateData);
        if ($created) {
            header('Location: ?page=admin&module=affiliates&success=added');
            exit;
        } else {
            $errors[] = 'Không thể tạo đại lý';
        }
    }
}
?>

<div class="affiliates-page affiliates-add-page">
    <!-- Page Header -->
    <div class="page-header">
        <div class="page-header-left">
            <h1 class="page-title">
                <i class="fas fa-plus"></i>
                Thêm Đại Lý Mới
            </h1>
            <p class="page-description">Thêm đại lý mới vào hệ thống</p>
        </div>
        <div class="page-header-right">
            <a href="?page=admin&module=affiliates" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i>
                Quay lại danh sách
            </a>
        </div>
    </div>

    <!-- Success/Error Messages -->
    <?php if ($success): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i>
            Thêm đại lý thành công!
        </div>
    <?php endif; ?>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-triangle"></i>
            <ul class="error-list">
                <?php foreach ($errors as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <!-- Add Affiliate Form -->
    <div class="form-container">
        <form method="POST" class="admin-form">
            <div class="form-grid">
                <!-- Left Column -->
                <div class="form-column">
                    <div class="form-section">
                        <h3 class="section-title">Thông Tin Đại Lý</h3>
                        
                        <div class="form-group">
                            <label for="user_id" class="required">Chọn người dùng</label>
                            <select id="user_id" name="user_id" required>
                                <option value="">Chọn người dùng</option>
                                <?php foreach ($available_users as $user): ?>
                                    <option value="<?= $user['id'] ?>" 
                                            <?= (($_POST['user_id'] ?? '') == $user['id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($user['name']) ?> (<?= htmlspecialchars($user['email']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <small>Chỉ hiển thị người dùng chưa là đại lý</small>
                        </div>

                        <div class="form-group">
                            <label for="referral_code" class="required">Mã giới thiệu</label>
                            <div class="input-group">
                                <input type="text" id="referral_code" name="referral_code" 
                                       value="<?= htmlspecialchars($_POST['referral_code'] ?? 'AGENT' . strtoupper(bin2hex(random_bytes(3)))) ?>"
                                       placeholder="AGENT001" required maxlength="20" style="text-transform: uppercase;">
                                <button type="button" class="btn btn-outline" onclick="generateNewCode()">
                                    <i class="fas fa-sync"></i>
                                    Tạo mới
                                </button>
                            </div>
                            <small>Mã duy nhất, chỉ chứa chữ cái in hoa và số (3-20 ký tự)</small>
                        </div>

                        <div class="form-group">
                            <label for="commission_rate" class="required">Tỷ lệ hoa hồng (%)</label>
                            <input type="number" id="commission_rate" name="commission_rate" 
                                   value="<?= htmlspecialchars($_POST['commission_rate'] ?? '10') ?>" 
                                   placeholder="10" min="0.1" max="50" step="0.1" required>
                            <small>Từ 0.1% đến 50%</small>
                        </div>

                        <div class="form-group">
                            <label for="status">Trạng thái</label>
                            <select id="status" name="status">
                                <option value="pending" <?= (($_POST['status'] ?? 'pending') == 'pending') ? 'selected' : '' ?>>
                                    Chờ duyệt
                                </option>
                                <option value="active" <?= (($_POST['status'] ?? '') == 'active') ? 'selected' : '' ?>>
                                    Hoạt động
                                </option>
                                <option value="inactive" <?= (($_POST['status'] ?? '') == 'inactive') ? 'selected' : '' ?>>
                                    Không hoạt động
                                </option>
                            </select>
                        </div>
                    </div>

                    <div class="form-section">
                        <h3 class="section-title">Thông Tin Bổ Sung</h3>
                        
                        <div class="form-group">
                            <label for="notes">Ghi chú</label>
                            <textarea id="notes" name="notes" rows="4" 
                                      placeholder="Ghi chú về đại lý (tùy chọn)..."><?= htmlspecialchars($_POST['notes'] ?? '') ?></textarea>
                        </div>

                        <div class="form-group">
                            <label for="bank_account">Thông tin tài khoản ngân hàng</label>
                            <textarea id="bank_account" name="bank_account" rows="3" 
                                      placeholder="Tên ngân hàng, số tài khoản, tên chủ tài khoản..."><?= htmlspecialchars($_POST['bank_account'] ?? '') ?></textarea>
                            <small>Thông tin để chuyển hoa hồng</small>
                        </div>
                    </div>
                </div>

                <!-- Right Column -->
                <div class="form-column">
                    <div class="form-section">
                        <h3 class="section-title">Thông Tin Hoa Hồng</h3>
                        
                        <div class="info-card">
                            <div class="info-item">
                                <label>Tổng doanh số hiện tại:</label>
                                <span class="value">0 VNĐ</span>
                            </div>
                            <div class="info-item">
                                <label>Tổng hoa hồng hiện tại:</label>
                                <span class="value">0 VNĐ</span>
                            </div>
                            <div class="info-item">
                                <label>Số đơn hàng đã giới thiệu:</label>
                                <span class="value">0</span>
                            </div>
                        </div>

                        <div class="commission-calculator">
                            <h4>Tính toán hoa hồng mẫu</h4>
                            <div class="calculator-row">
                                <label>Doanh số:</label>
                                <input type="number" id="sample_sales" placeholder="1000000" min="0" step="1000">
                                <span>VNĐ</span>
                            </div>
                            <div class="calculator-row">
                                <label>Hoa hồng nhận được:</label>
                                <span id="calculated_commission">0 VNĐ</span>
                            </div>
                        </div>
                    </div>

                    <div class="form-section">
                        <h3 class="section-title">Quy Định Đại Lý</h3>
                        
                        <div class="rules-list">
                            <div class="rule-item">
                                <i class="fas fa-check-circle text-success"></i>
                                <span>Hoa hồng được tính theo doanh số thực tế</span>
                            </div>
                            <div class="rule-item">
                                <i class="fas fa-check-circle text-success"></i>
                                <span>Thanh toán hoa hồng vào cuối mỗi tháng</span>
                            </div>
                            <div class="rule-item">
                                <i class="fas fa-check-circle text-success"></i>
                                <span>Mã giới thiệu có thể được sử dụng vô số lần</span>
                            </div>
                            <div class="rule-item">
                                <i class="fas fa-exclamation-triangle text-warning"></i>
                                <span>Không được tự sử dụng mã giới thiệu của mình</span>
                            </div>
                            <div class="rule-item">
                                <i class="fas fa-exclamation-triangle text-warning"></i>
                                <span>Vi phạm quy định sẽ bị khóa tài khoản đại lý</span>
                            </div>
                        </div>
                    </div>

                    <div class="form-section">
                        <h3 class="section-title">Liên Kết Chia Sẻ</h3>
                        
                        <div class="share-links">
                            <div class="link-item">
                                <label>Link giới thiệu:</label>
                                <div class="link-preview">
                                    <code id="referral_link">https://thuonglo.com/?ref=<span id="ref_code_display"><?= htmlspecialchars($_POST['referral_code'] ?? 'AGENT' . strtoupper(bin2hex(random_bytes(3)))) ?></span></code>
                                    <button type="button" class="btn btn-sm btn-outline" onclick="copyLink()">
                                        <i class="fas fa-copy"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i>
                    Lưu Đại Lý
                </button>
                <button type="button" class="btn btn-secondary" onclick="resetForm()">
                    <i class="fas fa-undo"></i>
                    Đặt lại
                </button>
                <a href="?page=admin&module=affiliates" class="btn btn-outline">
                    <i class="fas fa-times"></i>
                    Hủy
                </a>
            </div>
        </form>
    </div>
</div>

<script>
function generateNewCode() {
    const code = 'AGENT' + String(Math.floor(Math.random() * 999) + 1).padStart(3, '0');
    document.getElementById('referral_code').value = code;
    document.getElementById('ref_code_display').textContent = code;
}

function calculateCommission() {
    const sales = parseFloat(document.getElementById('sample_sales').value) || 0;
    const rate = parseFloat(document.getElementById('commission_rate').value) || 0;
    const commission = sales * rate / 100;
    document.getElementById('calculated_commission').textContent = 
        new Intl.NumberFormat('vi-VN').format(commission) + ' VNĐ';
}

function copyLink() {
    const link = document.getElementById('referral_link').textContent;
    navigator.clipboard.writeText(link).then(() => {
        alert('Đã sao chép link giới thiệu!');
    });
}

function resetForm() {
    if (confirm('Bạn có chắc chắn muốn đặt lại form?')) {
        document.querySelector('.admin-form').reset();
        generateNewCode();
        calculateCommission();
    }
}

// Update referral link when code changes
document.getElementById('referral_code').addEventListener('input', function() {
    document.getElementById('ref_code_display').textContent = this.value.toUpperCase();
    this.value = this.value.toUpperCase();
});

// Update commission calculation when rate or sample sales change
document.getElementById('commission_rate').addEventListener('input', calculateCommission);
document.getElementById('sample_sales').addEventListener('input', calculateCommission);

// Initial calculation
calculateCommission();
</script>