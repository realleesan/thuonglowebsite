<?php
/**
 * Finance - Yêu cầu rút tiền
 * Form rút tiền với validation
 */

// 1. Khởi tạo View & ServiceManager
require_once __DIR__ . '/../../../../core/view_init.php';

// 2. Chọn service affiliate (được inject từ index.php)
$service = isset($currentService) ? $currentService : ($affiliateService ?? null);

// Initialize data variables
$wallet = [
    'balance' => 0,
    'total_withdrawn' => 0
];
$bankAccounts = [];
$appConfig = require __DIR__ . '/../../../../config.php';
$withdrawalConfig = $appConfig['withdrawal'] ?? [];
$withdrawalSettings = [
    'min_amount' => (float)($withdrawalConfig['min_amount'] ?? 5000),
    'max_amount' => (float)($withdrawalConfig['max_amount'] ?? 50000000),
    'fee_percentage' => 0,
    'processing_time' => '1-3 ngày làm việc',
    'rules' => []
];

// Danh sách các ngân hàng được hỗ trợ bởi cổng PayOS
$supportedBanksList = [
    'MB Bank' => 'MB Bank (MB)',
    'Vietcombank' => 'Vietcombank (VCB)',
    'BIDV' => 'BIDV',
    'Agribank' => 'Agribank (AGB)',
    'OCB' => 'OCB',
    'VietinBank' => 'VietinBank (CTG)',
    'Sacombank' => 'Sacombank (STB)',
    'Techcombank' => 'Techcombank (TCB)',
    'ACB' => 'ACB',
    'DongA Bank' => 'DongA Bank (DAB)',
    'TPBank' => 'TPBank (TPB)',
    'HDBank' => 'HDBank (HDB)',
    'VPBank' => 'VPBank (VPB)',
    'SHB' => 'SHB',
    'VietCapital Bank' => 'VietCapital Bank (Bản Việt)'
];

try {
    if ($service) {
        // Get current affiliate ID from session
        $affiliateId = $_SESSION['user_id'] ?? 0;
        
        // Validate affiliate is logged in
        if ($affiliateId <= 0) {
            throw new Exception('Vui lòng đăng nhập để rút tiền');
        }
        
        // Get dashboard data FIRST for affiliate info (needed by header)
        $dashboardData = $service->getDashboardData($affiliateId);
        $affiliateInfo = $dashboardData['affiliate'] ?? [
            'name' => '',
            'email' => ''
        ];
        
        // Get finance data từ AffiliateService
        $financeData = $service->getFinanceData($affiliateId);
        
        $wallet = [
            'balance' => $financeData['balance'] ?? 0,
            'total_withdrawn' => $financeData['paid_commission'] ?? 0
        ];
        
        // Get withdrawal settings from service
        $withdrawalSettings = $service->getWithdrawalSettings($affiliateId) ?? $withdrawalSettings;
        
        // Get bank accounts from service
        $bankAccounts = $service->getBankList($affiliateId) ?? [];
    }
} catch (Exception $e) {
    $errorHandler->handleViewError($e, 'affiliate_withdraw', []);
}

// Generate withdrawal reference code
$withdrawalCode = 'WD-' . date('Ymd') . '-' . strtoupper(substr(md5(uniqid()), 0, 6));

// Include master layout
ob_start();
?>

<!-- Withdrawal Form -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-file-invoice-dollar"></i>
            Thông tin rút tiền
        </h3>
    </div>

    <div class="card-body">
        <form id="withdrawalForm" class="withdrawal-form">
            <!-- Available Balance -->
            <div class="form-group balance-info">
                <label class="form-label">
                    <i class="fas fa-wallet"></i>
                    Số dư khả dụng
                </label>
                <div class="balance-display">
                    <div class="balance-amount" id="availableBalance" data-balance="<?php echo $wallet['balance']; ?>">
                        <?php echo number_format($wallet['balance']); ?> đ
                    </div>
                </div>
                <small class="form-help">
                    <i class="fas fa-info-circle"></i>
                    Số dư thực tế trong ví của bạn
                </small>
            </div>

            <!-- Withdrawal Code -->
            <div class="form-group">
                <label class="form-label">
                    <i class="fas fa-barcode"></i>
                    Mã rút tiền
                    <span class="label-badge">Tự động</span>
                </label>
                <div class="withdrawal-code-display">
                    <code id="withdrawalCode"><?php echo $withdrawalCode; ?></code>
                    <button type="button" class="btn-copy" onclick="copyToClipboard('<?php echo $withdrawalCode; ?>', this)">
                        <i class="fas fa-copy"></i>
                    </button>
                </div>
                <small class="form-help">
                    <i class="fas fa-info-circle"></i>
                    Mã này dùng để đối soát giao dịch nếu cần
                </small>
            </div>

            <!-- Bank Account Input -->
            <div class="form-group">
                <label class="form-label required">
                    <i class="fas fa-university"></i>
                    Thông tin tài khoản ngân hàng
                </label>

                <!-- Bank Name -->
                <div class="form-subgroup">
                    <label class="form-sublabel">Tên ngân hàng</label>
                    <?php $savedBank = !empty($bankAccounts) ? $bankAccounts[0]['bank_name'] : ''; ?>
                    <select class="form-select" id="bankName" name="bank_name" required>
                        <option value="">-- Chọn ngân hàng --</option>
                        <?php foreach ($supportedBanksList as $key => $label): ?>
                            <?php 
                            $selected = false;
                            if (!empty($savedBank)) {
                                if (strcasecmp($savedBank, $key) === 0) {
                                    $selected = true;
                                } else {
                                    // Chuẩn hóa chuỗi để so khớp mềm dẻo (không quan tâm viết hoa/thường, khoảng trắng, chữ bank)
                                    $cleanSaved = str_replace([' bank', 'bank', ' '], '', mb_strtolower($savedBank, 'UTF-8'));
                                    $cleanKey = str_replace([' bank', 'bank', ' '], '', mb_strtolower($key, 'UTF-8'));
                                    if ($cleanSaved === $cleanKey) {
                                        $selected = true;
                                    }
                                }
                            }
                            ?>
                            <option value="<?php echo htmlspecialchars($key); ?>" <?php echo $selected ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($label); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Account Number -->
                <div class="form-subgroup">
                    <label class="form-sublabel">Số tài khoản</label>
                    <input type="text"
                           class="form-input"
                           id="accountNumber"
                           name="bank_account"
                           placeholder="Nhập số tài khoản ngân hàng"
                           value="<?php echo !empty($bankAccounts) ? htmlspecialchars($bankAccounts[0]['account_number']) : ''; ?>"
                           required>
                </div>

                <!-- Account Holder -->
                <div class="form-subgroup">
                    <label class="form-sublabel">Chủ tài khoản</label>
                    <input type="text"
                           class="form-input"
                           id="accountHolder"
                           name="account_holder"
                           placeholder="Nhập tên chủ tài khoản (in hoa, không dấu)"
                           value="<?php echo !empty($bankAccounts) ? htmlspecialchars($bankAccounts[0]['account_holder']) : ''; ?>"
                           required>
                </div>

                <small class="form-help">
                    <i class="fas fa-info-circle"></i>
                    Vui lòng nhập chính xác thông tin để tránh lỗi chuyển khoản.
                </small>
            </div>

            <!-- Amount -->
            <div class="form-group">
                <label class="form-label required">
                    <i class="fas fa-dollar-sign"></i>
                    Số tiền rút
                </label>
                <div class="amount-input-wrapper">
                    <input type="text" 
                           class="form-input" 
                           id="withdrawalAmount" 
                           name="amount" 
                           data-min-amount="<?php echo (int)($withdrawalSettings['min_amount'] ?? 5000); ?>"
                           data-max-amount="<?php echo (int)($withdrawalSettings['max_amount'] ?? 50000000); ?>"
                           placeholder="Nhập số tiền"
                           required>
                    <span class="amount-suffix">VNĐ</span>
                </div>
                <div class="amount-suggestions">
                    <button type="button" class="amount-btn" onclick="setAmount(500000)">500K</button>
                    <button type="button" class="amount-btn" onclick="setAmount(1000000)">1M</button>
                    <button type="button" class="amount-btn" onclick="setAmount(2000000)">2M</button>
                    <button type="button" class="amount-btn" onclick="setAmount(5000000)">5M</button>
                    <button type="button" class="amount-btn" onclick="setAmount(<?php echo $wallet['balance']; ?>)">Tất cả</button>
                </div>
                <small class="form-help">
                    <i class="fas fa-info-circle"></i>
                    Tối thiểu: <?php echo number_format($withdrawalSettings['min_amount'] ?? 5000); ?> đ - 
                    Tối đa: <?php echo number_format($withdrawalSettings['max_amount'] ?? 50000000); ?> đ
                </small>
            </div>

            <!-- Balance After Withdrawal -->
            <div class="balance-preview" id="balancePreview" style="display: none;">
                <div class="preview-item">
                    <span class="preview-label">Số dư hiện tại:</span>
                    <span class="preview-value"><?php echo number_format($wallet['balance']); ?> đ</span>
                </div>
                <div class="preview-item">
                    <span class="preview-label">Số tiền rút:</span>
                    <span class="preview-value preview-negative" id="withdrawAmount">0 đ</span>
                </div>
                <div class="preview-divider"></div>
                <div class="preview-item preview-total">
                    <span class="preview-label">Số dư còn lại:</span>
                    <span class="preview-value" id="balanceAfter">0 đ</span>
                </div>
            </div>

            <!-- Note -->
            <div class="form-group">
                <label class="form-label">
                    <i class="fas fa-sticky-note"></i>
                    Ghi chú
                    <span class="label-optional">(Tùy chọn)</span>
                </label>
                <textarea class="form-textarea" 
                          id="withdrawalNote" 
                          name="note" 
                          rows="3" 
                          placeholder="Nhập ghi chú nếu cần..."></textarea>
            </div>

            <!-- Error Message -->
            <div class="alert alert-danger" id="errorMessage" style="display: none;">
                <i class="fas fa-exclamation-circle"></i>
                <span id="errorText"></span>
            </div>

            <!-- Submit Button -->
            <div class="form-actions">
                <button type="submit" class="btn btn-primary btn-lg">
                    <i class="fas fa-paper-plane"></i>
                    <span>Gửi yêu cầu rút tiền</span>
                </button>
                <a href="?page=affiliate&module=finance" class="btn btn-outline btn-lg">
                    <i class="fas fa-times"></i>
                    <span>Hủy bỏ</span>
                </a>
            </div>
        </form>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../_layout/affiliate_master.php';
?>
