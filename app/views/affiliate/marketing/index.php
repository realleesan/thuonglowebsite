<?php
/**
 * Marketing - Công cụ Marketing
 * Affiliate links, QR code, Banners, Social share
 */

// 1. Khởi tạo View & ServiceManager
require_once __DIR__ . '/../../../../core/view_init.php';

// 2. Chọn service affiliate (được inject từ index.php)
$service = isset($currentService) ? $currentService : ($affiliateService ?? null);

// Initialize data variables
$affiliateInfo = [
    'referral_code' => '',
    'name' => ''
];
$campaigns = [];

try {
    if ($service) {
        // Get current affiliate ID from session
        $affiliateId = $_SESSION['user_id'] ?? 0;
        
        if ($affiliateId > 0) {
            // Get dashboard data từ AffiliateService
            $dashboardData = $service->getDashboardData($affiliateId);
            $affiliateInfo = $dashboardData['affiliate'] ?? $affiliateInfo;
            
            // Get marketing data from service (banners, campaigns)
            $marketingData = $service->getMarketingData($affiliateId);
            $banners = $marketingData['banners'] ?? [];
            $campaigns = $marketingData['campaigns'] ?? [];
        }
    }
    
    // Generate marketing data
    $referralCode = $affiliateInfo['referral_code'] ?? '';
    $affiliateLink = !empty($referralCode) ? base_url() . '?ref=' . urlencode($referralCode) : '';
    $qrCodeUrl = !empty($affiliateLink) ? "https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=" . urlencode($affiliateLink) : '';
    
} catch (Exception $e) {
    $errorHandler->handleViewError($e, 'affiliate_marketing', []);
    $affiliateLink = '';
    $qrCodeUrl = '';
    $banners = [];
    $campaigns = [];
}

// Set page variables - fallback default banners if not available
if (empty($banners)) {
    $banners = [
        ['id' => 1, 'name' => 'Banner 728x90', 'size' => '728x90', 'url' => 'assets/images/banners/728x90.jpg'],
        ['id' => 2, 'name' => 'Banner 300x250', 'size' => '300x250', 'url' => 'assets/images/banners/300x250.jpg']
    ];
}

$socialShare = [
    'facebook' => !empty($affiliateLink) ? "https://www.facebook.com/sharer/sharer.php?u=" . urlencode($affiliateLink) : '',
    'twitter' => !empty($affiliateLink) ? "https://twitter.com/intent/tweet?url=" . urlencode($affiliateLink) : '',
    'linkedin' => !empty($affiliateLink) ? "https://www.linkedin.com/sharing/share-offsite/?url=" . urlencode($affiliateLink) : '',
    'email' => !empty($affiliateLink) ? "mailto:?subject=Đăng ký ngay&body=Đăng ký qua link của tôi: " . urlencode($affiliateLink) : ''
];

// Page title
$page_title = 'Công cụ Marketing';

ob_start();
?>

<!-- Page Header -->
<div class="marketing-section">

    <div class="link-cards">
        <!-- Affiliate Link Card -->
        <div class="link-card">
            <div class="link-card-header">
                <div class="link-icon">
                    <i class="fas fa-link"></i>
                </div>
                <div class="link-info">
                    <h3 class="link-title">Affiliate Link</h3>
                    <p class="link-subtitle">Link giới thiệu của bạn</p>
                </div>
            </div>
            <div class="link-card-body">
                <div class="link-display">
                    <input type="text" 
                           class="link-input" 
                           value="<?php echo htmlspecialchars($affiliateLink); ?>" 
                           readonly 
                           id="affiliateLink">
                    <button type="button" 
                            class="btn-copy-link" 
                            onclick="copyToClipboard('<?php echo htmlspecialchars($affiliateLink); ?>', this)">
                        <i class="fas fa-copy"></i>
                        <span>Copy</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Affiliate ID Card -->
        <div class="link-card">
            <div class="link-card-header">
                <div class="link-icon">
                    <i class="fas fa-id-card"></i>
                </div>
                <div class="link-info">
                    <h3 class="link-title">Affiliate ID</h3>
                    <p class="link-subtitle">Mã giới thiệu của bạn</p>
                </div>
            </div>
            <div class="link-card-body">
                <div class="link-display">
                    <input type="text" 
                           class="link-input" 
                           value="<?php echo htmlspecialchars($affiliateId); ?>" 
                           readonly 
                           id="affiliateId">
                    <button type="button" 
                            class="btn-copy-link" 
                            onclick="copyToClipboard('<?php echo htmlspecialchars($affiliateId); ?>', this)">
                        <i class="fas fa-copy"></i>
                        <span>Copy</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- QR Code Section -->
<div class="marketing-section">
    <div class="qr-code-card">
        <div class="qr-code-preview">
            <img src="<?php echo htmlspecialchars($qrCodeUrl); ?>" 
                 alt="QR Code" 
                 class="qr-code-image"
                 onerror="this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%22200%22 height=%22200%22%3E%3Crect width=%22200%22 height=%22200%22 fill=%22%23f3f4f6%22/%3E%3Ctext x=%2250%25%22 y=%2250%25%22 text-anchor=%22middle%22 dy=%22.3em%22 fill=%22%239ca3af%22 font-family=%22Arial%22 font-size=%2214%22%3EQR Code%3C/text%3E%3C/svg%3E'">
        </div>
        <div class="qr-code-info">
            <div class="qr-code-actions">
                <button type="button" class="btn btn-primary" onclick="downloadQRCode()">
                    <i class="fas fa-download"></i>
                    <span>Tải xuống</span>
                </button>
                <button type="button" class="btn btn-outline" onclick="printQRCode()">
                    <i class="fas fa-print"></i>
                    <span>In QR Code</span>
                </button>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../_layout/affiliate_master.php';
?>
