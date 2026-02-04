<?php
/**
 * Breadcrumb Component
 * Hiá»ƒn thá»‹ breadcrumb navigation cho táº¥t cáº£ cÃ¡c trang
 * 
 * @param array $breadcrumbs - Máº£ng cÃ¡c breadcrumb items
 * Format: [
 *   ['title' => 'Trang chá»§', 'url' => './'],
 *   ['title' => 'Sáº£n pháº©m', 'url' => '?page=products'],
 *   ['title' => 'Chi tiáº¿t sáº£n pháº©m'] // Item cuá»‘i khÃ´ng cÃ³ url
 * ]
 */

// Breadcrumb máº·c Ä‘á»‹nh náº¿u khÃ´ng Ä‘Æ°á»£c truyá»n vÃ o
if (!isset($breadcrumbs) || empty($breadcrumbs)) {
    $breadcrumbs = [
        ['title' => 'Trang chá»§', 'url' => './']
    ];
}

// Äáº£m báº£o luÃ´n cÃ³ "Trang chá»§" á»Ÿ Ä‘áº§u
$homeBreadcrumb = ['title' => 'Trang chá»§', 'url' => './'];
if (empty($breadcrumbs) || $breadcrumbs[0]['title'] !== 'Trang chá»§') {
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
                       <?php if ($index === 0): ?>aria-label="Vá» trang chá»§"<?php endif; ?>>
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