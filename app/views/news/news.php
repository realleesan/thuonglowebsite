<?php
/**
 * News Page - Trang tin tức
 */

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Debug: Check if file is loaded
error_log("News page is loading...");

// 1. Khởi tạo View an toàn & ServiceManager
require_once __DIR__ . '/../../../core/view_init.php';

// Load required models with existence check
$modelsPath = __DIR__ . '/../../models/';
$newsModelPath = $modelsPath . 'NewsModel.php';
$categoriesModelPath = $modelsPath . 'CategoriesModel.php';

if (!file_exists($newsModelPath)) {
    die("Error: NewsModel.php not found at: " . $newsModelPath);
}
if (!file_exists($categoriesModelPath)) {
    die("Error: CategoriesModel.php not found at: " . $categoriesModelPath);
}

require_once $newsModelPath;
require_once $categoriesModelPath;

// Verify classes are loaded
if (!class_exists('NewsModel')) {
    die("Error: NewsModel class not loaded");
}
if (!class_exists('CategoriesModel')) {
    die("Error: CategoriesModel class not loaded");
}

// 2. Chọn service phù hợp
$service = isset($currentService) ? $currentService : ($publicService ?? null);

// Get pagination parameters
$page = (int) ($_GET['p'] ?? 1);
$limit = 5; // News per page
$categoryId = $_GET['category'] ?? null;
$search = $_GET['search'] ?? '';
$sort = $_GET['sort'] ?? 'newest';
// Handle multiple tags - can be array or single value
$tags = isset($_GET['tag']) ? (is_array($_GET['tag']) ? $_GET['tag'] : [$_GET['tag']]) : [];

// Initialize data variables
$newsList = [];
$categories = [];
$pagination = [];
$totalNews = 0;
$showErrorMessage = false;
$errorMessage = '';

try {
    // Initialize models
    $newsModel = new NewsModel();
    $categoriesModel = new CategoriesModel();
    
    // Build query
    $offset = ($page - 1) * $limit;
    
    // Get all news
    $allNewsRaw = $newsModel->all();
    
    if (!is_array($allNewsRaw)) {
        throw new Exception('Failed to fetch news data from database');
    }
    
    // Filter published news
    $allNews = [];
    $now = time();
    
    foreach ($allNewsRaw as $news) {
        if (!isset($news['status']) || $news['status'] !== 'published') {
            continue;
        }
        
        $include = true;
        
        // Filter by category
        if ($categoryId && $news['category_id'] != $categoryId) {
            $include = false;
        }
        
        // Debug: Log filter info (remove after testing)
        if ($categoryId) {
            error_log("Filtering - News ID: {$news['id']}, Category ID: {$news['category_id']}, Filter: {$categoryId}, Include: " . ($include ? 'yes' : 'no'));
        }
        
        // Filter by tags (OR logic - news must have at least one of the selected tags)
        if (!empty($tags) && !empty($news['tags'])) {
            $newsTags = array_map('trim', explode(',', $news['tags']));
            $hasMatchingTag = false;
            foreach ($tags as $selectedTag) {
                if (in_array($selectedTag, $newsTags)) {
                    $hasMatchingTag = true;
                    break;
                }
            }
            if (!$hasMatchingTag) {
                $include = false;
            }
        }
        
        // Filter by date
        $newsTime = strtotime($news['published_at'] ?? $news['created_at']);
        if ($sort === 'this-week') {
            $weekStart = strtotime('monday this week');
            if ($newsTime < $weekStart) {
                $include = false;
            }
        } elseif ($sort === 'this-month') {
            $monthStart = strtotime('first day of this month');
            if ($newsTime < $monthStart) {
                $include = false;
            }
        }
        
        // Filter by search
        if ($search && $include) {
            $searchLower = mb_strtolower($search, 'UTF-8');
            $titleLower = mb_strtolower($news['title'], 'UTF-8');
            $contentLower = mb_strtolower($news['content'] ?? '', 'UTF-8');
            
            if (strpos($titleLower, $searchLower) === false && 
                strpos($contentLower, $searchLower) === false) {
                $include = false;
            }
        }
        
        if ($include) {
            $allNews[] = $news;
        }
    }
    
    // Sort by published_at
    usort($allNews, function($a, $b) use ($sort) {
        $timeA = strtotime($a['published_at'] ?? $a['created_at']);
        $timeB = strtotime($b['published_at'] ?? $b['created_at']);
        
        if ($sort === 'oldest') {
            return $timeA - $timeB; // Oldest first
        }
        return $timeB - $timeA; // Newest first (default)
    });
    
    $totalNews = count($allNews);
    
    // Apply pagination
    $newsList = array_slice($allNews, $offset, $limit);
    
    // Get categories for sidebar
    $allCategories = $categoriesModel->all();
    if (is_array($allCategories)) {
        foreach ($allCategories as $cat) {
            if (isset($cat['type']) && $cat['type'] === 'news') {
                $categories[] = $cat;
            }
        }
    }
    
    // Get category names for news items
    if (!empty($newsList)) {
        foreach ($newsList as &$news) {
            if (!empty($news['category_id'])) {
                $category = $categoriesModel->find($news['category_id']);
                $news['category_name'] = $category['name'] ?? 'Thương mại XB';
            } else {
                $news['category_name'] = 'Thương mại XB';
            }
        }
    }
    
    // Calculate pagination
    $totalPages = $totalNews > 0 ? ceil($totalNews / $limit) : 1;
    $pagination = [
        'current_page' => $page,
        'total' => $totalNews,
        'last_page' => $totalPages,
        'per_page' => $limit
    ];
    
} catch (Exception $e) {
    // Log error
    error_log("News page error: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    
    $showErrorMessage = true;
    $errorMessage = 'Có lỗi xảy ra khi tải tin tức. Vui lòng thử lại sau. Error: ' . $e->getMessage();
    $newsList = [];
    $pagination = ['current_page' => 1, 'total' => 0, 'last_page' => 1, 'per_page' => $limit];
    $categories = [];
}

// Helper function to get news image
if (!function_exists('getNewsImage')) {
    function getNewsImage($news) {
        if (!empty($news['image'])) {
            // If image path starts with /, it's relative path
            if (strpos($news['image'], '/') === 0) {
                return $news['image'];
            }
            // If it's full URL, return as is
            if (strpos($news['image'], 'http') === 0) {
                return $news['image'];
            }
            // Otherwise, prepend /assets/images/
            return '/assets/images/' . $news['image'];
        }
        // Default image
        return '/assets/images/about/about_tt&tt_1.jpg';
    }
}

// Helper function to format date
if (!function_exists('formatNewsDate')) {
    function formatNewsDate($date) {
        $timestamp = strtotime($date);
        return date('d/m/Y', $timestamp);
    }
}

// Helper function to truncate text
if (!function_exists('truncateText')) {
    function truncateText($text, $length = 150) {
        if (strlen($text) <= $length) {
            return $text;
        }
        return substr($text, 0, $length) . '...';
    }
}

// Ensure we always output HTML structure
ob_start();
?>

<!-- Main Content -->
<div id="wrapper-container" class="wrapper-container">
    <div class="content-pusher">
        <div id="main-content">
            <div class="elementor elementor-news">
                <!-- Error Message -->
                <?php if ($showErrorMessage): ?>
                <div class="error-message" style="background: #f8d7da; color: #721c24; padding: 15px; margin: 20px 0; border-radius: 5px;">
                    <strong>Thông báo:</strong> <?php echo htmlspecialchars($errorMessage); ?>
                </div>
                <?php endif; ?>
                
                <!-- Main News Section -->
                <section class="news-section">
                    <div class="container">
                        <div class="news-layout">
                            <!-- Left Column - News List -->
                            <div class="news-main">
                                <!-- Header -->
                                <div class="news-header">
                                    <h1 class="page-title">Tin tức</h1>
                                </div>

                                <!-- News List -->
                                <div class="news-list">
                                    <?php if (empty($newsList)): ?>
                                        <div class="no-news">
                                            <p>Không tìm thấy tin tức nào.</p>
                                        </div>
                                    <?php else: ?>
                                        <?php foreach ($newsList as $news): ?>
                                        <!-- News Item -->
                                        <article class="news-item">
                                            <div class="news-image">
                                                <a href="?page=news-details&id=<?php echo $news['id']; ?>">
                                                    <img src="<?php echo getNewsImage($news); ?>" 
                                                         alt="<?php echo htmlspecialchars($news['title']); ?>" 
                                                         loading="lazy">
                                                </a>
                                            </div>
                                            <div class="news-content">
                                                <h2 class="news-title">
                                                    <a href="?page=news-details&id=<?php echo $news['id']; ?>">
                                                        <?php echo htmlspecialchars($news['title']); ?>
                                                    </a>
                                                </h2>
                                                <div class="news-excerpt">
                                                    <?php 
                                                    // Get first 2 lines of content (strip HTML tags first)
                                                    $plainContent = strip_tags($news['content']);
                                                    // Split by newlines and get first 2 lines
                                                    $lines = preg_split('/\r\n|\r|\n/', $plainContent);
                                                    $firstTwoLines = array_slice($lines, 0, 2);
                                                    $excerpt = implode(' ', $firstTwoLines);
                                                    // Truncate to 200 characters max
                                                    echo truncateText($excerpt, 200);
                                                    ?>
                                                </div>
                                                <div class="news-meta">
                                                    <?php if (!empty($news['category_name'])): ?>
                                                    <span class="news-category">
                                                        <svg width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                            <path d="M12.25 1.75H1.75C1.19772 1.75 0.75 2.19772 0.75 2.75V11.25C0.75 11.8023 1.19772 12.25 1.75 12.25H12.25C12.8023 12.25 13.25 11.8023 13.25 11.25V2.75C13.25 2.19772 12.8023 1.75 12.25 1.75Z" stroke="#6B7280" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                        </svg>
                                                        <?php echo htmlspecialchars($news['category_name']); ?>
                                                    </span>
                                                    <?php endif; ?>
                                                    <span class="news-date">
                                                        <svg width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                            <path d="M11.0833 2.33333H2.91667C2.27233 2.33333 1.75 2.85566 1.75 3.5V11.6667C1.75 12.311 2.27233 12.8333 2.91667 12.8333H11.0833C11.7277 12.8333 12.25 12.311 12.25 11.6667V3.5C12.25 2.85566 11.7277 2.33333 11.0833 2.33333Z" stroke="#6B7280" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                            <path d="M9.33333 1.16667V3.5" stroke="#6B7280" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                            <path d="M4.66667 1.16667V3.5" stroke="#6B7280" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                            <path d="M1.75 5.83333H12.25" stroke="#6B7280" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                        </svg>
                                                        <?php echo formatNewsDate($news['published_at'] ?? $news['created_at']); ?>
                                                    </span>
                                                </div>
                                                <a href="?page=news-details&id=<?php echo $news['id']; ?>" class="read-more">
                                                    Đọc chi tiết →
                                                </a>
                                            </div>
                                        </article>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>

                                <!-- Pagination -->
                                <?php if ($pagination['last_page'] > 1): ?>
                                <div class="pagination-wrapper">
                                    <nav class="pagination">
                                        <!-- Previous Page -->
                                        <?php if ($pagination['current_page'] > 1): ?>
                                            <a href="?<?php echo http_build_query(array_merge($_GET, ['p' => $pagination['current_page'] - 1])); ?>" class="page-link prev">
                                                <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <path d="M12.5 15L7.5 10L12.5 5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                                </svg>
                                            </a>
                                        <?php endif; ?>
                                        
                                        <!-- Page Numbers -->
                                        <?php
                                        $startPage = max(1, $pagination['current_page'] - 2);
                                        $endPage = min($pagination['last_page'], $pagination['current_page'] + 2);
                                        
                                        for ($i = $startPage; $i <= $endPage; $i++):
                                        ?>
                                            <a href="?<?php echo http_build_query(array_merge($_GET, ['p' => $i])); ?>" 
                                               class="page-link <?php echo $i === $pagination['current_page'] ? 'active' : ''; ?>">
                                                <?php echo $i; ?>
                                            </a>
                                        <?php endfor; ?>
                                        
                                        <!-- Next Page -->
                                        <?php if ($pagination['current_page'] < $pagination['last_page']): ?>
                                            <a href="?<?php echo http_build_query(array_merge($_GET, ['p' => $pagination['current_page'] + 1])); ?>" class="page-link next">
                                                <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <path d="M7.5 15L12.5 10L7.5 5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                                </svg>
                                            </a>
                                        <?php endif; ?>
                                    </nav>
                                </div>
                                <?php endif; ?>
                            </div>

                            <!-- Right Column - Sidebar -->
                            <div class="news-sidebar" id="newsSidebar">
                                <div class="sidebar-header">
                                    <h3>Bộ lọc</h3>
                                    <button class="sidebar-close" id="sidebarClose">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                                <div class="sidebar-content">
                                    <!-- Categories Filter -->
                                    <div class="filter-section">
                                        <h3 class="filter-title">Danh mục</h3>
                                        <div class="filter-content">
                                            <ul class="category-list">
                                                <li>
                                                    <a href="#" 
                                                       data-filter-type="category" 
                                                       data-filter-value=""
                                                       class="<?php echo empty($categoryId) ? 'filter-active' : ''; ?>">
                                                        Tất cả
                                                    </a>
                                                </li>
                                                <?php if (!empty($categories)): ?>
                                                    <?php foreach ($categories as $cat): ?>
                                                    <li>
                                                        <a href="#" 
                                                           data-filter-type="category" 
                                                           data-filter-value="<?php echo $cat['id']; ?>"
                                                           class="<?php echo $categoryId == $cat['id'] ? 'filter-active' : ''; ?>">
                                                            <?php echo htmlspecialchars($cat['name']); ?>
                                                        </a>
                                                    </li>
                                                    <?php endforeach; ?>
                                                <?php endif; ?>
                                            </ul>
                                        </div>
                                    </div>

                                    <!-- Date Filter -->
                                    <div class="filter-section">
                                        <h3 class="filter-title">Ngày đăng</h3>
                                        <div class="filter-content">
                                            <ul class="links-list">
                                                <li>
                                                    <a href="#" 
                                                       data-filter-type="sort" 
                                                       data-filter-value="newest"
                                                       class="<?php echo $sort === 'newest' ? 'filter-active' : ''; ?>">
                                                        Mới nhất
                                                    </a>
                                                </li>
                                                <li>
                                                    <a href="#" 
                                                       data-filter-type="sort" 
                                                       data-filter-value="oldest"
                                                       class="<?php echo $sort === 'oldest' ? 'filter-active' : ''; ?>">
                                                        Cũ nhất
                                                    </a>
                                                </li>
                                                <li>
                                                    <a href="#" 
                                                       data-filter-type="sort" 
                                                       data-filter-value="this-week"
                                                       class="<?php echo $sort === 'this-week' ? 'filter-active' : ''; ?>">
                                                        Tuần này
                                                    </a>
                                                </li>
                                                <li>
                                                    <a href="#" 
                                                       data-filter-type="sort" 
                                                       data-filter-value="this-month"
                                                       class="<?php echo $sort === 'this-month' ? 'filter-active' : ''; ?>">
                                                        Tháng này
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>

                                    <!-- Tags Section -->
                                    <div class="filter-section">
                                        <h3 class="filter-title">Tags</h3>
                                        <div class="filter-content">
                                            <div class="tags-cloud">
                                                <?php
                                                // Get all unique tags from news
                                                $allTags = [];
                                                foreach ($allNewsRaw as $newsItem) {
                                                    if (!empty($newsItem['tags'])) {
                                                        $itemTags = array_map('trim', explode(',', $newsItem['tags']));
                                                        $allTags = array_merge($allTags, $itemTags);
                                                    }
                                                }
                                                $allTags = array_unique($allTags);
                                                sort($allTags);
                                                
                                                foreach ($allTags as $tagItem):
                                                    if (empty($tagItem)) continue;
                                                    $tagLabel = ucfirst(str_replace('-', ' ', $tagItem));
                                                ?>
                                                <a href="#" 
                                                   data-filter-type="tag" 
                                                   data-filter-value="<?php echo htmlspecialchars($tagItem); ?>"
                                                   class="tag <?php echo in_array($tagItem, $tags) ? 'filter-active' : ''; ?>">
                                                    <?php echo htmlspecialchars($tagLabel); ?>
                                                </a>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Reset Button -->
                                    <div class="filter-section">
                                        <button class="reset-filters-btn">Đặt lại</button>
                                    </div>

                                    <!-- Apply Button -->
                                    <div class="filter-section">
                                        <button class="apply-filters-btn">Áp dụng</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>
</div>

<?php
// Flush output buffer to ensure content is sent
$content = ob_get_clean();
echo $content;

// Log completion
error_log("News page rendered successfully. Content length: " . strlen($content));
?>
