<?php
/**
 * Breadcrumb Component
 * Hiển thị breadcrumb navigation cho tất cả các trang
 * 
 * @param array $breadcrumbs - Mảng các breadcrumb items
 * Format: [
 *   ['title' => 'Trang chủ', 'url' => './'],
 *   ['title' => 'Sản phẩm', 'url' => '?page=products'],
 *   ['title' => 'Chi tiết sản phẩm'] // Item cuối không có url
 * ]
 */

// Breadcrumb mặc định nếu không được truyền vào
if (!isset($breadcrumbs) || empty($breadcrumbs)) {
    $breadcrumbs = [
        ['title' => 'Trang chủ', 'url' => './']
    ];
}

// Đảm bảo luôn có "Trang chủ" ở đầu
$homeBreadcrumb = ['title' => 'Trang chủ', 'url' => './'];
if (empty($breadcrumbs) || $breadcrumbs[0]['title'] !== 'Trang chủ') {
    array_unshift($breadcrumbs, $homeBreadcrumb);
}
?>

<!-- Breadcrumb Section -->
<section class="breadcrumb-section">
    <div class="container">
        <nav class="breadcrumb" aria-label="Breadcrumb navigation">
            <?php foreach ($breadcrumbs as $index => $item): ?>
                <?php if ($index > 0): ?>
                    <span class="delimiter" aria-hidden="true">
                        <svg width="6" height="10" viewBox="0 0 6 10" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M1 1L5 5L1 9" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </span>
                <?php endif; ?>
                
                <?php if (isset($item['url']) && !empty($item['url'])): ?>
                    <a href="<?php echo htmlspecialchars($item['url']); ?>" 
                       class="breadcrumb-link"
                       <?php if ($index === 0): ?>aria-label="Về trang chủ"<?php endif; ?>>
                        <?php echo htmlspecialchars($item['title']); ?>
                    </a>
                <?php else: ?>
                    <span class="breadcrumb-current" aria-current="page">
                        <?php echo htmlspecialchars($item['title']); ?>
                    </span>
                <?php endif; ?>
            <?php endforeach; ?>
        </nav>
    </div>
</section>