<?php
/**
 * Admin Affiliates Edit
 * Sử dụng AdminService thông qua ServiceManager
 */

// Khởi tạo View & ServiceManager
require_once __DIR__ . '/../../../../core/view_init.php';

// Chọn service admin (được inject từ index.php)
$service = isset($currentService) ? $currentService : ($adminService ?? null);

try {
    // Get affiliate ID from URL
    $affiliate_id = (int)($_GET['id'] ?? 0);
    
    if (!$affiliate_id) {
        header('Location: ?page=admin&module=affiliates&error=invalid_id');
        exit;
    }
    
    // Get affiliate data using AdminService
    $affiliateData = $service->getAffiliateDetailsData($affiliate_id);
    $affiliate = $affiliateData['affiliate'];
    
    // Redirect if affiliate not found
    if (!$affiliate) {
        header('Location: ?page=admin&module=affiliates&error=not_found');
        exit;
    }
    
} catch (Exception $e) {
    $errorHandler->logError('Admin Affiliates Edit View Error', $e);
    header('Location: ?page=admin&module=affiliates&error=system_error');
    exit;
}

// Handle form submission
$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validation
    $commission_rate = (float)($_POST['commission_rate'] ?? 0);
    $referral_code = trim($_POST['referral_code'] ?? '');
    $status = $_POST['status'] ?? 'active';
    
    if ($commission_rate <= 0 || $commission_rate > 50) {
        $errors[] = 'Tỷ lệ hoa hồng phải từ 0.1% đến 50%';
    }
    
    if (empty($referral_code)) {
        $errors[] = 'Mã giới thiệu không được để trống';
    } elseif (strlen($referral_code) < 3) {
        $errors[] = 'Mã giới thiệu phải có ít nhất 3 ký tự';
    } elseif (!preg_match('/^[A-Z0-9]+$/', $referral_code)) {
        $errors[] = 'Mã giới thiệu chỉ được chứa chữ cái in hoa và số';
    } elseif ($service->checkReferralCodeExists($referral_code, $affiliate_id)) {
        $errors[] = 'Mã giới thiệu đã tồn tại';
    }
    
    // If no errors, update database
    if (empty($errors)) {
        $affiliateData = [
            'commission_rate' => $commission_rate,
            'referral_code' => strtoupper($referral_code),
            'status' => $status
        ];
        $updated = $service->updateAffiliate($affiliate_id, $affiliateData);
        if ($updated) {
            header('Location: ?page=admin&module=affiliates&action=view&id=' . $affiliate_id . '&success=updated');
            exit;
        } else {
            $errors[] = 'Không thể cập nhật đại lý';
        }
    }
}

// Format price function
function formatPrice($price) {
    return number_format($price, 0, ',', '.') . ' VNĐ';
}
?>
<div class="affiliates-page affiliates-edit-page">
    <!-- Page Header -->
    <div class="page-header">
        <div class="page-header-left">
            <h1 class="page-title">
                <i class="fas fa-edit"></i>
                Chỉnh Sửa Đại Lý
            </h1>
                            <p class="page-description">Chỉnh sửa thông tin đại lý: <?= htmlspecialchars($affiliate['user_name'] ?? 'N/A') ?></p>
        </div>
        <div class="page-header-right">
            <a href="?page=admin&module=affiliates&action=view&id=<?= $affiliate_id ?>" class="btn btn-info">
                <i class="fas fa-eye"></i>
                Xem chi tiết
            </a>
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
            Cập nhật thông tin đại lý thành công!
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

    <!-- Edit Affiliate Form -->
    <div class="form-container">
        <form method="POST" class="admin-form">
            <div class="form-grid">
                <!-- Left Column -->
                <div class="form-column">
                    <div class="form-section">
                        <h3 class="section-title">Thông Tin Đại Lý</h3>
                        
                        <div class="form-group">
                            <label>Người dùng</label>
                            <div class="user-info-display">
                                <div class="user-avatar">
                                    <i class="fas fa-user-circle"></i>
                                </div>
                                <div class="user-details">
                                    <h4><?= htmlspecialchars($affiliate['user_name'] ?? 'N/A') ?></h4>
                                    <p><?= htmlspecialchars($affiliate['user_email'] ?? 'N/A') ?></p>
                                    <p><?= htmlspecialchars($affiliate['user_phone'] ?? 'N/A') ?></p>
                                </div>
                            </div>
                            <small>Không thể thay đổi người dùng sau khi tạo đại lý</small>
                        </div>

                        <div class="form-group">
                            <label for="referral_code" class="required">Mã giới thiệu</label>
                            <div class="input-group">
                                <input type="text" id="referral_code" name="referral_code" 
                                       value="<?= htmlspecialchars($_POST['referral_code'] ?? $affiliate['referral_code']) ?>" 
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
                                   value="<?= htmlspecialchars($_POST['commission_rate'] ?? $affiliate['commission_rate']) ?>" 
                                   placeholder="10" min="0.1" max="50" step="0.1" required>
                            <small>Từ 0.1% đến 50%</small>
                        </div>

                        <div class="form-group">
                            <label for="status">Trạng thái</label>
                            <select id="status" name="status">
                                <option value="pending" <?= (($_POST['status'] ?? $affiliate['status']) == 'pending') ? 'selected' : '' ?>>
                                    Chờ duyệt
                                </option>
                                <option value="active" <?= (($_POST['status'] ?? $affiliate['status']) == 'active') ? 'selected' : '' ?>>
                                    Hoạt động
                                </option>
                                <option value="inactive" <?= (($_POST['status'] ?? $affiliate['status']) == 'inactive') ? 'selected' : '' ?>>
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
                        <h3 class="section-title">Thống Kê Hiệu Suất</h3>
                        
                        <div class="stats-grid">
                            <div class="stat-card">
                                <div class="stat-icon">
                                    <i class="fas fa-chart-line"></i>
                                </div>
                                <div class="stat-content">
                                    <h4><?= formatPrice($affiliate['total_sales']) ?></h4>
                                    <p>Tổng doanh số</p>
                                </div>
                            </div>
                            
                            <div class="stat-card">
                                <div class="stat-icon">
                                    <i class="fas fa-money-bill-wave"></i>
                                </div>
                                <div class="stat-content">
                                    <h4><?= formatPrice($affiliate['total_commission']) ?></h4>
                                    <p>Tổng hoa hồng</p>
                                </div>
                            </div>
                            
                            <div class="stat-card">
                                <div class="stat-icon">
                                    <i class="fas fa-shopping-cart"></i>
                                </div>
                                <div class="stat-content">
                                    <h4>0</h4>
                                    <p>Đơn hàng tháng này</p>
                                </div>
                            </div>
                            
                            <div class="stat-card">
                                <div class="stat-icon">
                                    <i class="fas fa-users"></i>
                                </div>
                                <div class="stat-content">
                                    <h4>0</h4>
                                    <p>Khách hàng giới thiệu</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-section">
                        <h3 class="section-title">Tính Toán Hoa Hồng</h3>
                        
                        <div class="commission-calculator">
                            <div class="calculator-row">
                                <label>Doanh số mẫu:</label>
                                <input type="number" id="sample_sales" placeholder="1000000" min="0" step="1000" value="1000000">
                                <span>VNĐ</span>
                            </div>
                            <div class="calculator-row">
                                <label>Hoa hồng nhận được:</label>
                                <span id="calculated_commission">0 VNĐ</span>
                            </div>
                        </div>
                    </div>

                    <div class="form-section">
                        <h3 class="section-title">Liên Kết Chia Sẻ</h3>
                        
                        <div class="share-links">
                            <div class="link-item">
                                <label>Link giới thiệu:</label>
                                <div class="link-preview">
                                    <code id="referral_link">https://thuonglo.com/?ref=<span id="ref_code_display"><?= htmlspecialchars($_POST['referral_code'] ?? $affiliate['referral_code']) ?></span></code>
                                    <button type="button" class="btn btn-sm btn-outline" onclick="copyLink()">
                                        <i class="fas fa-copy"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-section">
                        <h3 class="section-title">Lịch Sử Hoạt Động</h3>
                        
                        <div class="activity-timeline">
                            <div class="timeline-item">
                                <div class="timeline-icon">
                                    <i class="fas fa-user-plus"></i>
                                </div>
                                <div class="timeline-content">
                                    <h5>Tham gia làm đại lý</h5>
                                    <p><?= date('d/m/Y H:i', strtotime($affiliate['created_at'])) ?></p>
                                </div>
                            </div>
                            
                            <div class="timeline-item">
                                <div class="timeline-icon">
                                    <i class="fas fa-check-circle"></i>
                                </div>
                                <div class="timeline-content">
                                    <h5>Tài khoản được kích hoạt</h5>
                                    <p><?= date('d/m/Y H:i', strtotime($affiliate['created_at'])) ?></p>
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
                    Cập Nhật Đại Lý
                </button>
                <button type="button" class="btn btn-secondary" onclick="resetForm()">
                    <i class="fas fa-undo"></i>
                    Đặt lại
                </button>
                <a href="?page=admin&module=affiliates&action=view&id=<?= $affiliate_id ?>" class="btn btn-info">
                    <i class="fas fa-eye"></i>
                    Xem chi tiết
                </a>
                <a href="?page=admin&module=affiliates" class="btn btn-outline">
                    <i class="fas fa-arrow-left"></i>
                    Quay lại
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
        location.reload();
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