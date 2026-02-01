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