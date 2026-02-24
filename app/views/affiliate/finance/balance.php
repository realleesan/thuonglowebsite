<?php
/**
 * Finance - Số dư
 * Hiển thị số dư ví affiliate
 */

// 1. Khởi tạo View & ServiceManager
require_once __DIR__ . '/../../../../core/view_init.php';

// 2. Chọn service affiliate (được inject từ index.php)
$service = isset($currentService) ? $currentService : ($affiliateService ?? null);

// Initialize data variables
$wallet = [
    'balance' => 0,
    'total_earned' => 0,
    'total_withdrawn' => 0,
    'pending' => 0
];

try {
    if ($service) {
        // Get current affiliate ID from session
        $affiliateId = $_SESSION['user_id'] ?? 0;
        
        // Validate affiliate is logged in
        if ($affiliateId <= 0) {
            throw new Exception('Vui lòng đăng nhập để xem số dư');
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
            'total_earned' => ($financeData['pending_commission'] ?? 0) + ($financeData['paid_commission'] ?? 0),
            'total_withdrawn' => $financeData['paid_commission'] ?? 0,
            'pending' => $financeData['pending_commission'] ?? 0
        ];
    }
} catch (Exception $e) {
    $errorHandler->handleViewError($e, 'affiliate_balance', []);
}

// Page title
$page_title = 'Số dư';

// Include master layout
ob_start();
?>
<!-- Balance Section -->
<div class="balance-card">
    <h3>Số dư hiện tại</h3>
    <div class="balance-amount">
        <?php echo number_format($wallet['balance']); ?> VNĐ
    </div>
</div>
<?php
$content = ob_get_clean();
include __DIR__ . '/../../_layout/affiliate_master.php';
