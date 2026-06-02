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
                                    <button class="filter-toggle-btn" id="filterToggle">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                            <path d="M8.37013 7.79006C8.42013 8.22006 8.73013 8.55006 9.17013 8.63006C9.24013 8.64006 9.31013 8.65006 9.38013 8.65006C9.74013 8.65006 10.0701 8.46006 10.2401 8.15006C10.2401 8.15006 10.3701 7.93006 10.3701 7.86006V6.83006H21.3701C21.4801 6.83006 21.8501 6.61006 21.9301 6.52006C22.1301 6.31006 22.2301 5.99006 22.1801 5.68006C22.1401 5.36006 21.9601 5.10006 21.7001 4.95006C21.6801 4.94006 21.3401 4.81006 21.2801 4.81006H10.3701V3.77006C10.3701 3.64006 10.1101 3.30006 10.0601 3.25006C9.80013 3.01006 9.39013 2.94006 9.03013 3.07006C8.68013 3.19006 8.44013 3.47006 8.39013 3.81006C8.34013 4.16006 8.36013 4.61006 8.37013 5.05006C8.37013 5.25006 8.39013 5.44006 8.39013 5.61006C8.39013 5.78006 8.39013 5.96006 8.37013 6.16006C8.35013 6.71006 8.33013 7.34006 8.37013 7.80006V7.79006Z" fill="#098CE9"></path>
                                            <path d="M21.3701 17.5401H10.3701V16.5101C10.3701 16.4501 10.2201 16.1701 10.2201 16.1601C9.99013 15.8101 9.57013 15.6601 9.14013 15.7501C8.72013 15.8501 8.42013 16.1701 8.37013 16.5801C8.34013 16.9301 8.35013 17.3401 8.37013 17.7401C8.37013 17.9501 8.38013 18.1501 8.38013 18.3401C8.38013 18.5001 8.38013 18.6801 8.36013 18.8801C8.34013 19.4601 8.31013 20.1201 8.38013 20.5701C8.42013 20.8601 8.61013 21.1101 8.89013 21.2601C9.05013 21.3401 9.22013 21.3801 9.39013 21.3801C9.56013 21.3801 9.71013 21.3401 9.85013 21.2701C10.0201 21.1901 10.3701 20.8201 10.3701 20.6101V19.5801H21.2801C21.3401 19.5801 21.6801 19.4501 21.7001 19.4401C21.9601 19.2901 22.1301 19.0201 22.1701 18.7101C22.2101 18.4001 22.1201 18.0801 21.9101 17.8601C21.8601 17.8101 21.4801 17.5501 21.3501 17.5501L21.3701 17.5401Z" fill="#098CE9"></path>
                                            <path d="M14.3401 9.45006C14.0301 9.31006 13.7001 9.32006 13.4301 9.49006C13.1301 9.67006 12.9201 10.0201 12.8901 10.4201C12.8101 11.4001 12.8301 13.0001 12.8901 13.9301C12.9301 14.3901 13.1701 14.7801 13.5201 14.9501C13.6401 15.0101 13.7701 15.0301 13.9001 15.0301C14.1101 15.0301 14.3101 14.9601 14.5101 14.8201C14.6601 14.7201 14.9201 14.3701 14.9201 14.1701V13.1901H21.4001C21.4001 13.1901 21.4501 13.1901 21.6901 13.0701C21.9901 12.9001 22.1801 12.5901 22.1901 12.2301C22.2001 11.8701 22.0301 11.5401 21.7401 11.3601C21.7201 11.3501 21.4601 11.2101 21.3901 11.2101H14.9101V10.2201C14.9101 9.96006 14.5501 9.56006 14.3301 9.46006L14.3401 9.45006Z" fill="#098CE9"></path>
                                            <path d="M2.84013 13.1801H11.3801C11.8701 13.0601 12.2001 12.6701 12.2101 12.2001C12.2101 11.7301 11.9201 11.3401 11.4301 11.2001H2.77013C2.23013 11.3501 2.00013 11.8201 2.00013 12.2201C2.01013 12.7001 2.33013 13.0801 2.83013 13.1901L2.84013 13.1801Z" fill="#098CE9"></path>
                                            <path d="M2.84013 6.82006H6.82013C7.40013 6.69006 7.66013 6.22006 7.65013 5.80006C7.65013 5.39006 7.38013 4.92006 6.77013 4.81006C6.25013 4.84006 5.66013 4.81006 5.09013 4.78006C4.35013 4.74006 3.58013 4.70006 2.92013 4.79006C2.31013 4.90006 2.02013 5.36006 2.00013 5.79006C1.98013 6.21006 2.23013 6.69006 2.83013 6.83006L2.84013 6.82006Z" fill="#098CE9"></path>
                                            <path d="M6.86013 17.5501H2.82013C2.23013 17.6901 1.98013 18.1801 2.00013 18.5901C2.02013 19.0101 2.31013 19.4701 2.92013 19.5601C3.22013 19.6001 3.54013 19.6201 3.87013 19.6201C4.23013 19.6201 4.60013 19.6001 4.96013 19.5901C5.55013 19.5601 6.15013 19.5401 6.69013 19.5901H6.71013C7.31013 19.5101 7.60013 19.0601 7.63013 18.6401C7.66013 18.2201 7.43013 17.7201 6.84013 17.5701L6.86013 17.5501Z" fill="#098CE9"></path>
                                        </svg>
                                    </button>
                                </div>

                                <!-- Top Bar with Results and Sort -->
                                <div class="news-topbar">
                                    <div class="results-count">
                                        Hiển thị <?php echo min(($pagination['current_page'] - 1) * $limit + 1, $totalNews); ?>-<?php echo min($pagination['current_page'] * $limit, $totalNews); ?> trong tổng số <?php echo $totalNews; ?> kết quả
                                    </div>
                                    <form method="get" action="?page=news" class="sort-form">
                                        <input type="hidden" name="page" value="news">
                                        <input type="hidden" name="p" value="<?php echo $page; ?>">
                                        <?php if (!empty($categoryId)): ?>
                                        <input type="hidden" name="category" value="<?php echo htmlspecialchars($categoryId); ?>">
                                        <?php endif; ?>
                                        <?php if (!empty($search)): ?>
                                        <input type="hidden" name="search" value="<?php echo htmlspecialchars($search); ?>">
                                        <?php endif; ?>
                                        <?php if (!empty($tags)): ?>
                                            <?php foreach ($tags as $tag): ?>
                                                <input type="hidden" name="tag[]" value="<?php echo htmlspecialchars($tag); ?>">
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                        <select name="sort" class="sort-select" onchange="this.form.submit()">
                                            <option value="newest" <?php echo $sort === 'newest' ? 'selected' : ''; ?>>Mới nhất</option>
                                            <option value="oldest" <?php echo $sort === 'oldest' ? 'selected' : ''; ?>>Cũ nhất</option>
                                            <option value="this-week" <?php echo $sort === 'this-week' ? 'selected' : ''; ?>>Tuần này</option>
                                            <option value="this-month" <?php echo $sort === 'this-month' ? 'selected' : ''; ?>>Tháng này</option>
                                        </select>
                                    </form>
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
                                            <a href="?<?php echo http_build_query(array_merge(['page' => 'news'], $_GET, ['p' => $pagination['current_page'] - 1])); ?>" class="page-link prev">
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
                                            <a href="?<?php echo http_build_query(array_merge(['page' => 'news'], $_GET, ['p' => $i])); ?>" 
                                               class="page-link <?php echo $i === $pagination['current_page'] ? 'active' : ''; ?>">
                                                <?php echo $i; ?>
                                            </a>
                                        <?php endfor; ?>
                                        
                                        <!-- Next Page -->
                                        <?php if ($pagination['current_page'] < $pagination['last_page']): ?>
                                            <a href="?<?php echo http_build_query(array_merge(['page' => 'news'], $_GET, ['p' => $pagination['current_page'] + 1])); ?>" class="page-link next">
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
                                <form method="get" action="?page=news" class="filter-form">
                                        <div class="sidebar-content">
                                            <input type="hidden" name="page" value="news">
                                            <input type="hidden" name="p" value="<?php echo $page; ?>">
                                            <input type="hidden" name="search" value="<?php echo htmlspecialchars($search); ?>">
                                            <input type="hidden" name="sort" value="<?php echo htmlspecialchars($sort); ?>">
                                         <!-- Categories Filter (radio buttons) -->
                                         <div class="filter-section">
                                             <h3 class="filter-title">
                                                 <div class="title-icon-wrapper"><i class="fas fa-folder-open"></i> Danh mục</div>
                                                 <i class="fas fa-chevron-down chevron-icon"></i>
                                             </h3>
                                             <div class="filter-content">
                                                 <ul class="category-list">
                                                     <li class="category-item <?php echo empty($categoryId) ? 'active' : ''; ?>">
                                                         <div class="category-item-content">
                                                             <label class="filter-item-label">
                                                                 <input type="radio" name="category" value="" 
                                                                        <?php echo empty($categoryId) ? 'checked' : ''; ?>>
                                                                 <span class="custom-radio"></span>
                                                                 <span class="category-label">Tất cả</span>
                                                             </label>
                                                         </div>
                                                     </li>
                                                     <?php if (!empty($categories)): ?>
                                                         <?php foreach ($categories as $cat): ?>
                                                         <li class="category-item <?php echo $categoryId == $cat['id'] ? 'active' : ''; ?>">
                                                             <div class="category-item-content">
                                                                 <label class="filter-item-label">
                                                                     <input type="radio" name="category" value="<?php echo $cat['id']; ?>" 
                                                                            <?php echo $categoryId == $cat['id'] ? 'checked' : ''; ?>>
                                                                     <span class="custom-radio"></span>
                                                                     <span class="category-label"><?php echo htmlspecialchars($cat['name']); ?></span>
                                                                 </label>
                                                             </div>
                                                         </li>
                                                         <?php endforeach; ?>
                                                     <?php endif; ?>
                                                 </ul>
                                             </div>
                                         </div>

                                         <?php
                                         // Get all unique tags from news for filter
                                         $filterTags = [];
                                         if (!empty($allNewsRaw)) {
                                             foreach ($allNewsRaw as $newsItem) {
                                                 if (!empty($newsItem['tags'])) {
                                                     $itemTags = array_map('trim', explode(',', $newsItem['tags']));
                                                     $filterTags = array_merge($filterTags, $itemTags);
                                                 }
                                             }
                                             $filterTags = array_unique($filterTags);
                                             sort($filterTags);
                                         }
                                         ?>

                                         <!-- Tags Section -->
                                         <div class="filter-section">
                                             <h3 class="filter-title">
                                                 <div class="title-icon-wrapper"><i class="fas fa-tags"></i> Thẻ (Tags)</div>
                                                 <i class="fas fa-chevron-down chevron-icon"></i>
                                             </h3>
                                             <div class="filter-content">
                                                 <div class="tags-checkbox-list">
                                                     <?php if (!empty($filterTags)): ?>
                                                         <?php foreach ($filterTags as $tagItem): ?>
                                                         <?php $isTagActive = in_array($tagItem, $tags); ?>
                                                         <div class="category-item <?php echo $isTagActive ? 'active' : ''; ?>">
                                                             <div class="category-item-content">
                                                                <label class="filter-item-label">
                                                                    <input type="checkbox" name="tag[]" value="<?php echo htmlspecialchars($tagItem); ?>"
                                                                           <?php echo $isTagActive ? 'checked' : ''; ?>>
                                                                    <span class="custom-checkbox"></span>
                                                                    <span class="category-label"><?php echo htmlspecialchars(ucfirst(str_replace('-', ' ', $tagItem))); ?></span>
                                                                </label>
                                                             </div>
                                                         </div>
                                                         <?php endforeach; ?>
                                                     <?php endif; ?>
                                                 </div>
                                             </div>
                                         </div>

                                         <!-- Actions -->
                                         <div class="filter-actions">
                                             <button type="submit" class="apply-filters-btn">Áp dụng bộ lọc</button>
                                             <button type="button" class="reset-filters-btn" onclick="window.location.href='?page=news'">Đặt lại</button>
                                         </div>
                                     </div>
                                </form>
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
