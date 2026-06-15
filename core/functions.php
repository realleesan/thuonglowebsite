<?php
/**
 * Core Functions
 * Các hàm tiện ích chung cho toàn bộ hệ thống
 */

if (!function_exists('render_breadcrumb')) {
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
}

if (!function_exists('generate_breadcrumb')) {
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
            ],
            'faq' => [
                ['title' => 'Câu hỏi thường gặp']
            ],
            'shopping-guide' => [
                ['title' => 'Hướng dẫn mua hàng']
            ],
            'terms' => [
                ['title' => 'Điều khoản dịch vụ']
            ],
            'privacy' => [
                ['title' => 'Chính sách bảo mật']
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
}

if (!function_exists('get_current_page')) {
    /**
     * Lấy page hiện tại từ URL
     * 
     * @return string
     */
    function get_current_page() {
        return isset($_GET['page']) ? $_GET['page'] : 'home';
    }
}

if (!function_exists('generate_product_breadcrumb')) {
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
}

if (!function_exists('generate_news_breadcrumb')) {
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
}

if (!function_exists('get_product_breadcrumb_from_db')) {
    /**
     * Lấy breadcrumb từ database cho sản phẩm
     * 
     * @param int $product_id ID sản phẩm
     * @return array
     */
    function get_product_breadcrumb_from_db($product_id) {
        // Fallback nếu không lấy được từ database
        return [
            ['title' => 'Trang chủ', 'url' => './'],
            ['title' => 'Sản phẩm', 'url' => '?page=products'],
            ['title' => 'Chi tiết sản phẩm']
        ];
    }
}

if (!function_exists('get_news_breadcrumb_from_db')) {
    /**
     * Lấy breadcrumb từ database cho tin tức
     * 
     * @param int $news_id ID tin tức
     * @return array
     */
    function get_news_breadcrumb_from_db($news_id) {
        try {
            require_once __DIR__ . '/../app/models/NewsModel.php';
            require_once __DIR__ . '/../app/models/CategoriesModel.php';
            
            $newsModel = new NewsModel();
            $news = $newsModel->find($news_id);
            
            if (!$news) {
                return [
                    ['title' => 'Trang chủ', 'url' => './'],
                    ['title' => 'Tin tức', 'url' => '?page=news'],
                    ['title' => 'Chi tiết tin tức']
                ];
            }
            
            $breadcrumbs = [
                ['title' => 'Trang chủ', 'url' => './'],
                ['title' => 'Tin tức', 'url' => '?page=news']
            ];
            
            // Get category name if exists and different from "Tin tức"
            if (!empty($news['category_id'])) {
                $categoriesModel = new CategoriesModel();
                $category = $categoriesModel->find($news['category_id']);
                if ($category && !empty($category['name']) && $category['name'] !== 'Tin tức') {
                    $breadcrumbs[] = [
                        'title' => $category['name'], 
                        'url' => '?page=news&category=' . $news['category_id']
                    ];
                }
            }
            
            // Add news title
            $breadcrumbs[] = ['title' => $news['title']];
            
            return $breadcrumbs;
        } catch (Exception $e) {
            error_log('Breadcrumb error: ' . $e->getMessage());
            return [
                ['title' => 'Trang chủ', 'url' => './'],
                ['title' => 'Tin tức', 'url' => '?page=news'],
                ['title' => 'Chi tiết tin tức']
            ];
        }
    }
}

if (!function_exists('init_url_builder')) {
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
}

if (!function_exists('asset_url')) {
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
}

if (!function_exists('css_url')) {
    /**
     * Get CSS file URL
     * @param string $file CSS filename
     * @return string Full CSS URL
     */
    function css_url($file) {
        return asset_url('css/' . ltrim($file, '/'));
    }
}

if (!function_exists('js_url')) {
    /**
     * Get JavaScript file URL
     * @param string $file JS filename
     * @return string Full JS URL
     */
    function js_url($file) {
        return asset_url('js/' . ltrim($file, '/'));
    }
}

if (!function_exists('img_url')) {
    /**
     * Get image URL - handles both full URLs and relative paths
     * @param string $file Image filename or full URL
     * @return string Full image URL
     */
    function img_url($file) {
        // Nếu đã là URL đầy đủ (http/https), trả về ngay
        if (!empty($file) && (strpos($file, 'http://') === 0 || strpos($file, 'https://') === 0)) {
            return $file;
        }
        return asset_url('images/' . ltrim($file, '/'));
    }
}

if (!function_exists('font_url')) {
    /**
     * Get font URL
     * @param string $file Font filename
     * @return string Full font URL
     */
    function font_url($file) {
        return asset_url('fonts/' . ltrim($file, '/'));
    }
}

if (!function_exists('icon_url')) {
    /**
     * Get icon URL
     * @param string $file Icon filename
     * @return string Full icon URL
     */
    function icon_url($file) {
        return asset_url('icons/' . ltrim($file, '/'));
    }
}

if (!function_exists('resolve_image_path')) {
    /**
     * Helper to resolve image path - handles both absolute external URLs and relative local paths.
     * Checks if local file exists before returning.
     * 
     * @param string $path Image path from database
     * @param string $fallback Fallback image relative to images/
     * @return string Full image URL
     */
    function resolve_image_path($path, $fallback = 'home/no-image.png') {
        if (empty($path)) {
            return img_url($fallback);
        }
        
        // Check if it's an absolute external URL
        if (strpos($path, 'http://') === 0 || strpos($path, 'https://') === 0) {
            // Block thimpress and unsplash external placeholders
            if (strpos($path, 'thimpress.com') !== false || strpos($path, 'unsplash.com') !== false) {
                return img_url($fallback);
            }
            return $path;
        }
        
        // It's a relative local path. Normalize by removing leading slash.
        $cleanPath = ltrim($path, '/');
        
        $projectRoot = dirname(__DIR__);
        
        // Check Location 1: root-relative
        $file1 = $projectRoot . '/' . $cleanPath;
        if (file_exists($file1) && is_file($file1)) {
            return base_url($cleanPath);
        }
        
        // Check Location 2: assets-relative
        $file2 = $projectRoot . '/assets/' . $cleanPath;
        if (file_exists($file2) && is_file($file2)) {
            return base_url('assets/' . $cleanPath);
        }
        
        // Check Location 3: images-relative
        $file3 = $projectRoot . '/assets/images/' . $cleanPath;
        if (file_exists($file3) && is_file($file3)) {
            return base_url('assets/images/' . $cleanPath);
        }
        
        // Fallback to designated local asset
        return img_url($fallback);
    }
}

if (!function_exists('getProductImage')) {
    /**
     * Safely get product image URL with fallback
     * @param array|string $product Product data or image path
     * @return string Image URL
     */
    function getProductImage($product) {
        $image = is_array($product) ? ($product['image'] ?? '') : $product;
        return resolve_image_path($image, 'home/no-image.png');
    }
}

if (!function_exists('getCategoryImage')) {
    /**
     * Safely get category image URL with fallback
     * @param array|string $category Category data or image path
     * @return string Image URL
     */
    function getCategoryImage($category) {
        $image = is_array($category) ? ($category['image'] ?? '') : $category;
        return resolve_image_path($image, 'home/no-image.png');
    }
}

if (!function_exists('getBrandImage')) {
    /**
     * Safely get brand image URL with fallback
     * @param array|string $brand Brand data or image path
     * @return string Image URL
     */
    function getBrandImage($brand) {
        $image = is_array($brand) ? ($brand['image'] ?? '') : $brand;
        return resolve_image_path($image, 'home/no-image.png');
    }
}

if (!function_exists('getNewsImage')) {
    /**
     * Safely get news image URL with fallback
     * @param array|string $news News data or image path
     * @return string Image URL
     */
    function getNewsImage($news) {
        $image = is_array($news) ? ($news['image'] ?? '') : $news;
        return resolve_image_path($image, 'about/about_tt&tt_1.jpg');
    }
}

if (!function_exists('versioned_asset')) {
    /**
     * Get versioned asset URL (for cache busting)
     * @param string $path Asset path
     * @return string Versioned asset URL
     */
    function versioned_asset($path) {
        global $config;
        
        $assetUrl = asset_url($path);
        
        // Add version parameter for cache busting
        $fullPath = $_SERVER['DOCUMENT_ROOT'] . '/assets/' . ltrim($path, '/');
        if (!file_exists($fullPath)) {
            $fullPath = __DIR__ . '/../assets/' . ltrim($path, '/');
        }
        $version = file_exists($fullPath) ? filemtime($fullPath) : time();
        $assetUrl .= '?v=' . $version;
        
        return $assetUrl;
    }
}

if (!function_exists('versioned_css')) {
    /**
     * Get versioned CSS URL
     * @param string $file CSS filename
     * @return string Versioned CSS URL
     */
    function versioned_css($file) {
        return versioned_asset('css/' . ltrim($file, '/'));
    }
}

if (!function_exists('versioned_js')) {
    /**
     * Get versioned JS URL
     * @param string $file JS filename
     * @return string Versioned JS URL
     */
    function versioned_js($file) {
        return versioned_asset('js/' . ltrim($file, '/'));
    }
}

if (!function_exists('page_url')) {
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
}

if (!function_exists('nav_url')) {
    /**
     * Generate navigation URL
     * @param string $page Page name
     * @return string Navigation URL
     */
    function nav_url($page) {
        return page_url($page);
    }
}

if (!function_exists('base_url')) {
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
}

if (!function_exists('get_environment')) {
    /**
     * Get current environment
     * @return string Environment name (local, hosting)
     */
    function get_environment() {
        global $config;
        return isset($config['app']['environment']) ? $config['app']['environment'] : 'hosting';
    }
}

if (!function_exists('is_local')) {
    /**
     * Check if running in local environment
     * @return bool
     */
    function is_local() {
        return get_environment() === 'local';
    }
}

if (!function_exists('is_hosting')) {
    /**
     * Check if running in hosting environment
     * @return bool
     */
    function is_hosting() {
        return get_environment() === 'hosting';
    }
}

if (!function_exists('is_debug')) {
    /**
     * Check if debug mode is enabled
     * @return bool
     */
    function is_debug() {
        global $config;
        return isset($config['app']['debug']) ? $config['app']['debug'] : false;
    }
}

if (!function_exists('form_url')) {
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
}

if (!function_exists('config')) {
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
}

if (!function_exists('get_logo')) {
    /**
     * Get dynamic logo from site settings
     * @param string $key Logo key (logo_header, logo_footer, logo_admin_full, logo_admin_mini, logo_affiliate_full, logo_affiliate_mini, favicon)
     * @param string $default Default logo path if not found
     * @return string Logo path
     */
    function get_logo($key, $default = 'logo/logo.svg') {
        static $cache = [];
        
        // Return from cache if available
        if (isset($cache[$key])) {
            return $cache[$key];
        }
        
        try {
            // Check if model file exists
            $modelPath = __DIR__ . '/../app/models/SiteSettingsModel.php';
            if (!file_exists($modelPath)) {
                $cache[$key] = $default;
                return $default;
            }
            
            require_once $modelPath;
            
            // Check if class exists
            if (!class_exists('SiteSettingsModel')) {
                $cache[$key] = $default;
                return $default;
            }
            
            $model = new SiteSettingsModel();
            $value = $model->getValue($key, $default);
            
            // Cache the value
            $cache[$key] = $value;
            
            return $value;
        } catch (Exception $e) {
            error_log("Error getting logo: " . $e->getMessage());
            // Cache the default to avoid repeated errors
            $cache[$key] = $default;
            return $default;
        } catch (Error $e) {
            // Catch PHP errors (like table not found)
            error_log("PHP Error getting logo: " . $e->getMessage());
            $cache[$key] = $default;
            return $default;
        }
    }
}

if (!function_exists('get_favicon')) {
    /**
     * Get dynamic favicon from site settings
     * @return string Favicon path
     */
    function get_favicon() {
        return get_logo('favicon', 'logo/logo_mini.svg');
    }
}
?>