<?php
/**
 * Dynamic CTA Section
 */

$ctaSection = null;
try {
    require_once __DIR__ . '/../../models/CtaSectionModel.php';
    $ctaModel = new CtaSectionModel();
    // Retrieve the first record (including inactive ones) to properly respect visibility status
    $ctaSection = $ctaModel->getFirst();
} catch (Exception $e) {
    error_log("CTA section frontend database load error: " . $e->getMessage());
}

// Fallback to default static values if database record is missing or table is not created yet
if (!$ctaSection) {
    $ctaSection = [
        'title' => 'Trở thành một trong <span class="highlight">500+</span>',
        'subtitle' => 'Đại Lý Affiliate ThuongLo',
        'content' => '<p>Tham gia cùng chúng tôi và kiếm thu nhập thụ động từ việc giới thiệu dịch vụ thương mại xuyên biên giới hàng đầu Việt Nam</p>',
        'button_text' => 'Đăng ký ngay',
        'button_url' => '?page=agent',
        'background_color' => '#ECEDEF',
        'image_url' => 'home/cta-final-1.png',
        'is_active' => 1
    ];
}

// Render only if CTA Section is active
if ($ctaSection && isset($ctaSection['is_active']) && $ctaSection['is_active']):
    $imgUrl = $ctaSection['image_url'] ?? '';
    $finalImg = '';
    if ($imgUrl) {
        $finalImg = (strpos($imgUrl, 'http') === 0) ? $imgUrl : img_url($imgUrl);
    } else {
        $finalImg = img_url('home/cta-final-1.png');
    }
?>
<!-- CTA Section -->
<section class="cta-section" style="<?php echo !empty($ctaSection['background_color']) ? 'background-color: ' . htmlspecialchars($ctaSection['background_color']) . ';' : ''; ?>">
    <div class="cta-container">
        <div class="cta-content">
            <div class="cta-image">
                <img loading="lazy" decoding="async" width="500" height="500"
                     src="<?php echo $finalImg; ?>"
                     alt="<?php echo htmlspecialchars(strip_tags($ctaSection['title'] ?? 'Trở thành đối tác ThuongLo')); ?>">
            </div>
            <div class="cta-text">
                <?php if (!empty($ctaSection['title'])): ?>
                    <h2 class="cta-title"><?php echo $ctaSection['title']; ?></h2>
                <?php endif; ?>
                <?php if (!empty($ctaSection['subtitle'])): ?>
                    <h2 class="cta-subtitle"><?php echo $ctaSection['subtitle']; ?></h2>
                <?php endif; ?>
                <?php if (!empty($ctaSection['content'])): ?>
                    <div class="cta-description">
                        <?php echo $ctaSection['content']; ?>
                    </div>
                <?php endif; ?>
                <?php if (!empty($ctaSection['button_text'])): ?>
                    <a class="cta-button" href="<?php echo htmlspecialchars($ctaSection['button_url'] ?? '#'); ?>">
                        <?php echo htmlspecialchars($ctaSection['button_text']); ?>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>