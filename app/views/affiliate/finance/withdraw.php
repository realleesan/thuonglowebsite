<?php
/**
 * Finance - Yêu cầu rút tiền
 * Form rút tiền với validation
 */

// Load data
require_once __DIR__ . '/../../../../core/AffiliateDataLoader.php';
$dataLoader = new AffiliateDataLoader();
$financeData = $dataLoader->getData('finance');

$wallet = $financeData['wallet'];
$bankAccounts = $financeData['bank_accounts'];
$withdrawalSettings = $financeData['withdrawal_settings'];

// Generate unique withdrawal code
$withdrawalCode = 'WD-' . date('Ymd') . '-' . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT);

// Page title
$page_title = 'Yêu cầu rút tiền';

// Include master layout
ob_start();
?>

<!-- Page Header -->
<div class="page-header">
    <div class="page-header-content">
        <h1 class="page-title">
            <i class="fas fa-money-bill-wave"></i>
            Yêu cầu rút tiền
        </h1>
        <p class="page-description">Rút tiền về tài khoản ngân hàng</p>
    </div>
    <div class="page-header-actions">
        <a href="?page=affiliate&module=finance" class="btn btn-outline">
            <i class="fas fa-arrow-left"></i>
            <span>Quay lại</span>
        </a>
    </div>
</div>

<!-- Wallet Balance Card -->
<div class="balance-card">
    <div class="balance-card-header">
        <i class="fas fa-wallet"></i>
        <span>Số dư khả dụng</span>
    </div>
    <div class="balance-card-body">
        <div class="balance-amount" id="availableBalance" data-balance="<?php echo $wallet['balance']; ?>">
            <?php echo number_format($wallet['balance']); ?> đ
        </div>
        <div class="balance-note">Có thể rút ngay</div>
    </div>
</div>

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

            <!-- Bank Account -->
            <div class="form-group">
                <label class="form-label required">
                    <i class="fas fa-university"></i>
                    Tài khoản ngân hàng
                </label>
                <select class="form-select" id="bankAccountSelect" name="bank_account" required>
                    <option value="">-- Chọn tài khoản --</option>
                    <?php foreach ($bankAccounts as $account): ?>
                    <option value="<?php echo $account['id']; ?>" 
                            data-bank="<?php echo htmlspecialchars($account['bank_name']); ?>"
                            data-account="<?php echo htmlspecialchars($account['account_number']); ?>"
                            data-holder="<?php echo htmlspecialchars($account['account_holder']); ?>"
                            <?php echo $account['is_default'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($account['bank_name']); ?> - 
                        <?php echo htmlspecialchars($account['account_number']); ?> - 
                        <?php echo htmlspecialchars($account['account_holder']); ?>
                        <?php echo $account['is_default'] ? '(Mặc định)' : ''; ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Bank Details Display -->
            <div class="bank-details" id="bankDetails" style="display: none;">
                <div class="bank-detail-item">
                    <span class="detail-label">Ngân hàng:</span>
                    <span class="detail-value" id="bankName">-</span>
                </div>
                <div class="bank-detail-item">
                    <span class="detail-label">Số tài khoản:</span>
                    <span class="detail-value" id="accountNumber">-</span>
                </div>
                <div class="bank-detail-item">
                    <span class="detail-label">Chủ tài khoản:</span>
                    <span class="detail-value" id="accountHolder">-</span>
                </div>
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
                    Tối thiểu: <?php echo number_format($withdrawalSettings['min_amount']); ?> đ - 
                    Tối đa: <?php echo number_format($withdrawalSettings['max_amount']); ?> đ
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

<!-- Withdrawal Rules -->
<div class="info-card">
    <div class="info-card-header">
        <i class="fas fa-shield-alt"></i>
        <h3>Quy định rút tiền</h3>
    </div>
    <div class="info-card-body">
        <ul class="rules-list">
            <?php foreach ($withdrawalSettings['rules'] as $rule): ?>
            <li class="rule-item">
                <i class="fas fa-check-circle"></i>
                <span><?php echo htmlspecialchars($rule); ?></span>
            </li>
            <?php endforeach; ?>
        </ul>
    </div>
</div>

<!-- Processing Time Info -->
<div class="alert alert-info">
    <i class="fas fa-clock"></i>
    <div class="alert-content">
        <strong>Thời gian xử lý:</strong>
        <p>Yêu cầu rút tiền sẽ được xử lý trong vòng <?php echo htmlspecialchars($withdrawalSettings['processing_time']); ?>. 
        Tiền sẽ được chuyển vào tài khoản ngân hàng của bạn sau khi được duyệt.</p>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../_layout/affiliate_master.php';
?>
