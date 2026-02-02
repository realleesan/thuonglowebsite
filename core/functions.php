<?php
/**
 * Core Functions
 * Các hàm tiện ích chung cho toàn bộ hệ thống
 */

/**
 * Render breadcrumb component
 * 
 * @param array $breadcrumbs Mảng breadcrumb items
 * @param bool $return Trả về HTML thay vì echo
 * @return string|void
 */
function render_breadcrumb($breadcrumbs = [], $return = false) {
    // Bắt đầu output buffering
    ob_start();
    
    // Include breadcrumb component
    include __DIR__ . '/../app/views/_layout/breadcrumb.php';
    
    // Lấy nội dung
    $output = ob_get_clean();
    
    if ($return) {
        return $output;
    } else {
        echo $output;
    }
}

/**
 * Tạo breadcrumb tự động dựa trên page hiện tại
 * 
 * @param string $current_page Trang hiện tại
 * @param array $additional_items Các item bổ sung
 * @return array
 */
function generate_breadcrumb($current_page = '', $additional_items = []) {
    $breadcrumbs = [
        ['title' => 'Trang chủ', 'url' => './']
    ];
    
    // Mapping các page với breadcrumb
    $page_mapping = [
        'products' => [
            ['title' => 'Sản phẩm']
        ],
        'details' => [
            ['title' => 'Sản phẩm', 'url' => '?page=products'],
            ['title' => 'Chi tiết sản phẩm']
        ],
        'categories' => [
            ['title' => 'Danh mục']
        ],
        'about' => [
            ['title' => 'Giới thiệu']
        ],
        'contact' => [
            ['title' => 'Liên hệ']
        ],
        'news' => [
            ['title' => 'Tin tức']
        ],
        'news-details' => [
            ['title' => 'Tin tức', 'url' => '?page=news'],
            ['title' => 'Chi tiết tin tức']
        ],
        'auth' => [
            ['title' => 'Đăng nhập']
        ],
        'register' => [
            ['title' => 'Đăng ký']
        ],
        'cart' => [
            ['title' => 'Giỏ hàng']
        ],
        'checkout' => [
            ['title' => 'Giỏ hàng', 'url' => '?page=cart'],
            ['title' => 'Thanh toán']
        ],
        'account' => [
            ['title' => 'Tài khoản']
        ],
        'orders' => [
            ['title' => 'Tài khoản', 'url' => '?page=account'],
            ['title' => 'Đơn hàng']
        ],
        'wishlist' => [
            ['title' => 'Tài khoản', 'url' => '?page=account'],
            ['title' => 'Danh sách yêu thích']
        ]
    ];
    
    // Thêm breadcrumb cho page hiện tại
    if ($current_page && isset($page_mapping[$current_page])) {
        $breadcrumbs = array_merge($breadcrumbs, $page_mapping[$current_page]);
    }
    
    // Thêm các item bổ sung
    if (!empty($additional_items)) {
        $breadcrumbs = array_merge($breadcrumbs, $additional_items);
    }
    
    return $breadcrumbs;
}

/**
 * Lấy page hiện tại từ URL
 * 
 * @return string
 */
function get_current_page() {
    return isset($_GET['page']) ? $_GET['page'] : 'home';
}

/**
 * Tạo breadcrumb cho trang sản phẩm với category
 * 
 * @param string $category_name Tên danh mục
 * @param string $product_name Tên sản phẩm (optional)
 * @return array
 */
function generate_product_breadcrumb($category_name = '', $product_name = '') {
    $breadcrumbs = [
        ['title' => 'Trang chủ', 'url' => './'],
        ['title' => 'Sản phẩm', 'url' => '?page=products']
    ];
    
    if ($category_name) {
        $breadcrumbs[] = ['title' => $category_name, 'url' => '?page=products&category=' . urlencode($category_name)];
    }
    
    if ($product_name) {
        $breadcrumbs[] = ['title' => $product_name];
    }
    
    return $breadcrumbs;
}

/**
 * Tạo breadcrumb cho trang tin tức
 * 
 * @param string $category_name Tên danh mục tin tức
 * @param string $news_title Tiêu đề tin tức (optional)
 * @return array
 */
function generate_news_breadcrumb($category_name = '', $news_title = '') {
    $breadcrumbs = [
        ['title' => 'Trang chủ', 'url' => './'],
        ['title' => 'Tin tức', 'url' => '?page=news']
    ];
    
    if ($category_name) {
        $breadcrumbs[] = ['title' => $category_name, 'url' => '?page=news&category=' . urlencode($category_name)];
    }
    
    if ($news_title) {
        $breadcrumbs[] = ['title' => $news_title];
    }
    
    return $breadcrumbs;
}

/**
 * Lấy breadcrumb từ database cho sản phẩm
 * 
 * @param int $product_id ID sản phẩm
 * @return array
 */
function get_product_breadcrumb_from_db($product_id) {
    // Ví dụ lấy từ database (cần implement database connection)
    /*
    try {
        $pdo = new PDO(...); // Database connection
        $stmt = $pdo->prepare("
            SELECT p.name as product_name, c.name as category_name 
            FROM products p 
            LEFT JOIN categories c ON p.category_id = c.id 
            WHERE p.id = ?
        ");
        $stmt->execute([$product_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result) {
            // Chỉ sử dụng product name để tránh trùng lặp
            return [
                ['title' => 'Trang chủ', 'url' => './'],
                ['title' => 'Sản phẩm', 'url' => '?page=products'],
                ['title' => $result['product_name']]
            ];
        }
    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
    }
    */
    
    // Fallback nếu không lấy được từ database
    return [
        ['title' => 'Trang chủ', 'url' => './'],
        ['title' => 'Sản phẩm', 'url' => '?page=products'],
        ['title' => 'Chi tiết sản phẩm']
    ];
}

/**
 * Lấy breadcrumb từ database cho tin tức
 * 
 * @param int $news_id ID tin tức
 * @return array
 */
function get_news_breadcrumb_from_db($news_id) {
    // Ví dụ lấy từ database (cần implement database connection)
    /*
    try {
        $pdo = new PDO(...); // Database connection
        $stmt = $pdo->prepare("
            SELECT n.title as news_title, c.name as category_name 
            FROM news n 
            LEFT JOIN news_categories c ON n.category_id = c.id 
            WHERE n.id = ?
        ");
        $stmt->execute([$news_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result) {
            return generate_news_breadcrumb($result['category_name'], $result['news_title']);
        }
    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
    }
    */
    
    // Fallback nếu không lấy được từ database
    return generate_news_breadcrumb('Tin tức', 'Chi tiết tin tức');
}

// ============================================================================
// URL HELPER FUNCTIONS
// ============================================================================

// Global variables for URL building
global $config, $urlBuilder;

/**
 * Initialize URL Builder
 * Should be called after config is loaded
 */
function init_url_builder() {
    global $config, $urlBuilder;
    
    if (!isset($urlBuilder) && isset($config)) {
        require_once __DIR__ . '/UrlBuilder.php';
        $urlBuilder = new UrlBuilder($config);
    }
}

/**
 * Get asset URL
 * @param string $path Asset path relative to assets directory
 * @return string Full asset URL
 */
function asset_url($path) {
    global $urlBuilder;
    
    if (!isset($urlBuilder)) {
        init_url_builder();
    }
    
    return $urlBuilder ? $urlBuilder->asset($path) : 'assets/' . ltrim($path, '/');
}

/**
 * Get CSS file URL
 * @param string $file CSS filename
 * @return string Full CSS URL
 */
function css_url($file) {
    return asset_url('css/' . ltrim($file, '/'));
}

/**
 * Get JavaScript file URL
 * @param string $file JS filename
 * @return string Full JS URL
 */
function js_url($file) {
    return asset_url('js/' . ltrim($file, '/'));
}

/**
 * Get image URL
 * @param string $file Image filename
 * @return string Full image URL
 */
function img_url($file) {
    return asset_url('images/' . ltrim($file, '/'));
}

/**
 * Get font URL
 * @param string $file Font filename
 * @return string Full font URL
 */
function font_url($file) {
    return asset_url('fonts/' . ltrim($file, '/'));
}

/**
 * Get icon URL
 * @param string $file Icon filename
 * @return string Full icon URL
 */
function icon_url($file) {
    return asset_url('icons/' . ltrim($file, '/'));
}

/**
 * Get versioned asset URL (for cache busting)
 * @param string $path Asset path
 * @return string Versioned asset URL
 */
function versioned_asset($path) {
    global $config;
    
    $assetUrl = asset_url($path);
    
    // Add version parameter for cache busting
    if (isset($config['performance']['cache_assets']) && $config['performance']['cache_assets']) {
        $fullPath = $_SERVER['DOCUMENT_ROOT'] . '/assets/' . ltrim($path, '/');
        $version = file_exists($fullPath) ? filemtime($fullPath) : time();
        $assetUrl .= '?v=' . $version;
    }
    
    return $assetUrl;
}

/**
 * Get versioned CSS URL
 * @param string $file CSS filename
 * @return string Versioned CSS URL
 */
function versioned_css($file) {
    return versioned_asset('css/' . ltrim($file, '/'));
}

/**
 * Get versioned JS URL
 * @param string $file JS filename
 * @return string Versioned JS URL
 */
function versioned_js($file) {
    return versioned_asset('js/' . ltrim($file, '/'));
}

/**
 * Generate page URL
 * @param string $page Page name
 * @param array $params Additional parameters
 * @return string Page URL
 */
function page_url($page, $params = []) {
    global $urlBuilder;
    
    if (!isset($urlBuilder)) {
        init_url_builder();
    }
    
    return $urlBuilder ? $urlBuilder->page($page, $params) : '?page=' . $page;
}

/**
 * Generate navigation URL
 * @param string $page Page name
 * @return string Navigation URL
 */
function nav_url($page) {
    return page_url($page);
}

/**
 * Get base URL
 * @return string Base URL
 */
function base_url($path = '') {
    global $urlBuilder;
    
    if (!isset($urlBuilder)) {
        init_url_builder();
    }
    
    return $urlBuilder ? $urlBuilder->url($path) : '/' . ltrim($path, '/');
}

/**
 * Get current environment
 * @return string Environment name (local, hosting)
 */
function get_environment() {
    global $config;
    return isset($config['app']['environment']) ? $config['app']['environment'] : 'hosting';
}

/**
 * Check if running in local environment
 * @return bool
 */
function is_local() {
    return get_environment() === 'local';
}

/**
 * Check if running in hosting environment
 * @return bool
 */
function is_hosting() {
    return get_environment() === 'hosting';
}

/**
 * Check if debug mode is enabled
 * @return bool
 */
function is_debug() {
    global $config;
    return isset($config['app']['debug']) ? $config['app']['debug'] : false;
}

/**
 * Generate form action URL
 * @param string $page Page name for form submission
 * @param array $params Additional parameters
 * @return string Form action URL
 */
function form_url($page = '', $params = []) {
    global $urlBuilder;
    
    if (!isset($urlBuilder)) {
        init_url_builder();
    }
    
    if (empty($page)) {
        // Return current page URL for self-submitting forms
        return $_SERVER['REQUEST_URI'] ?? '';
    }
    
    return $urlBuilder ? $urlBuilder->page($page, $params) : 'index.php?page=' . $page;
}

/**
 * Get configuration value
 * @param string $key Configuration key (dot notation supported)
 * @param mixed $default Default value if key not found
 * @return mixed Configuration value
 */
function config($key, $default = null) {
    global $config;
    
    if (!isset($config)) {
        return $default;
    }
    
    // Support dot notation (e.g., 'app.name')
    $keys = explode('.', $key);
    $value = $config;
    
    foreach ($keys as $k) {
        if (!is_array($value) || !isset($value[$k])) {
            return $default;
        }
        $value = $value[$k];
    }
    
    return $value;
}