<?php
/**
 * Marketing - Công cụ Marketing
 * Affiliate links, QR code, Banners, Social share
 */

// Load data
require_once __DIR__ . '/../../../../core/AffiliateDataLoader.php';
$dataLoader = new AffiliateDataLoader();
$marketingData = $dataLoader->getData('marketing');
$affiliateInfo = $dataLoader->getData('affiliate_info');

$affiliateLink = $marketingData['affiliate_link'];
$affiliateId = $marketingData['affiliate_id'];
$qrCodeUrl = $marketingData['qr_code_url'];
$campaigns = $marketingData['campaigns'];
$banners = $marketingData['banners'];
$socialShare = $marketingData['social_share'];

// Page title
$page_title = 'Công cụ Marketing';

// Include master layout
ob_start();
?>

<!-- Page Header -->
<div class="page-header">
    <div class="page-header-content">
        <h1 class="page-title">
            <i class="fas fa-bullhorn"></i>
            Công cụ Marketing
        </h1>
        <p class="page-description">Quản lý links, banners và công cụ chia sẻ</p>
    </div>
</div>

<!-- Affiliate Link Section -->
<div class="marketing-section">
    <div class="section-header">
        <h2 class="section-title">
            <i class="fas fa-link"></i>
            Link Giới Thiệu
        </h2>
        <p class="section-description">Chia sẻ link này để nhận hoa hồng từ khách hàng</p>
    </div>

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
    <div class="section-header">
        <h2 class="section-title">
            <i class="fas fa-qrcode"></i>
            QR Code
        </h2>
        <p class="section-description">Tải QR code để in ấn hoặc chia sẻ</p>
    </div>

    <div class="qr-code-card">
        <div class="qr-code-preview">
            <img src="<?php echo htmlspecialchars($qrCodeUrl); ?>" 
                 alt="QR Code" 
                 class="qr-code-image"
                 onerror="this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%22200%22 height=%22200%22%3E%3Crect width=%22200%22 height=%22200%22 fill=%22%23f3f4f6%22/%3E%3Ctext x=%2250%25%22 y=%2250%25%22 text-anchor=%22middle%22 dy=%22.3em%22 fill=%22%239ca3af%22 font-family=%22Arial%22 font-size=%2214%22%3EQR Code%3C/text%3E%3C/svg%3E'">
        </div>
        <div class="qr-code-info">
            <h3 class="qr-code-title">QR Code Giới Thiệu</h3>
            <p class="qr-code-description">
                Khách hàng quét QR code này sẽ tự động truy cập link giới thiệu của bạn. 
                Bạn có thể in QR code ra giấy, đặt trên website, hoặc chia sẻ trên mạng xã hội.
            </p>
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

<!-- Social Share Section -->
<div class="marketing-section">
    <div class="section-header">
        <h2 class="section-title">
            <i class="fas fa-share-alt"></i>
            Chia Sẻ Mạng Xã Hội
        </h2>
        <p class="section-description">Chia sẻ link giới thiệu lên các nền tảng</p>
    </div>

    <div class="social-share-grid">
        <a href="<?php echo htmlspecialchars($socialShare['facebook']); ?>" 
           target="_blank" 
           class="social-share-btn social-facebook">
            <i class="fab fa-facebook-f"></i>
            <span>Facebook</span>
        </a>
        <a href="<?php echo htmlspecialchars($socialShare['twitter']); ?>" 
           target="_blank" 
           class="social-share-btn social-twitter">
            <i class="fab fa-twitter"></i>
            <span>Twitter</span>
        </a>
        <a href="<?php echo htmlspecialchars($socialShare['linkedin']); ?>" 
           target="_blank" 
           class="social-share-btn social-linkedin">
            <i class="fab fa-linkedin-in"></i>
            <span>LinkedIn</span>
        </a>
        <a href="<?php echo htmlspecialchars($socialShare['email']); ?>" 
           class="social-share-btn social-email">
            <i class="fas fa-envelope"></i>
            <span>Email</span>
        </a>
    </div>
</div>

<!-- Campaigns Section -->
<div class="marketing-section">
    <div class="section-header">
        <h2 class="section-title">
            <i class="fas fa-rocket"></i>
            Chiến Dịch Marketing
        </h2>
        <p class="section-description">Theo dõi hiệu quả các chiến dịch của bạn</p>
    </div>

    <div class="campaigns-grid">
        <?php foreach ($campaigns as $campaign): ?>
        <div class="campaign-card">
            <div class="campaign-header">
                <h3 class="campaign-name"><?php echo htmlspecialchars($campaign['name']); ?></h3>
                <?php if ($campaign['status'] === 'active'): ?>
                    <span class="badge badge-success">
                        <i class="fas fa-circle"></i>
                        Đang chạy
                    </span>
                <?php else: ?>
                    <span class="badge badge-secondary">
                        <i class="fas fa-circle"></i>
                        Đã kết thúc
                    </span>
                <?php endif; ?>
            </div>
            <div class="campaign-dates">
                <i class="fas fa-calendar"></i>
                <?php echo date('d/m/Y', strtotime($campaign['start_date'])); ?> - 
                <?php echo date('d/m/Y', strtotime($campaign['end_date'])); ?>
            </div>
            <div class="campaign-stats">
                <div class="campaign-stat">
                    <div class="stat-label">Clicks</div>
                    <div class="stat-value"><?php echo number_format($campaign['clicks']); ?></div>
                </div>
                <div class="campaign-stat">
                    <div class="stat-label">Chuyển đổi</div>
                    <div class="stat-value"><?php echo number_format($campaign['conversions']); ?></div>
                </div>
                <div class="campaign-stat">
                    <div class="stat-label">Tỷ lệ</div>
                    <div class="stat-value"><?php echo number_format($campaign['conversion_rate'], 2); ?>%</div>
                </div>
                <div class="campaign-stat">
                    <div class="stat-label">Hoa hồng</div>
                    <div class="stat-value"><?php echo number_format($campaign['commission']); ?> đ</div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../_layout/affiliate_master.php';
?>
