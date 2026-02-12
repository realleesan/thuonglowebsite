<?php
// Khởi tạo session
session_start();

// Load basic configuration
$base_dir = __DIR__;
$config = require_once $base_dir . '/config.php';

// Set error reporting based on config
if ($config['app']['debug']) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Include core files
require_once $base_dir . '/core/security.php';
require_once $base_dir . '/core/functions.php';

// Initialize URL Builder
init_url_builder();

// Enable output buffering
if (ob_get_level() === 0) {
    ob_start();
}

// Lấy trang hiện tại từ URL
$page = $_GET['page'] ?? 'home';

// Thiết lập thông tin cho từng trang
switch($page) {
    case 'home':
        $title = 'Trang chủ - Thuong Lo';
        $content = 'app/views/home/home.php';
        $showPageHeader = false;
        $showCTA = true;
        $showBreadcrumb = false; // Trang chủ không cần breadcrumb
        break;
        
    case 'about':
        $title = 'Giới thiệu - Thuong Lo';
        $content = 'app/views/about/about.php';
        $showPageHeader = true;
        $showCTA = false;
        $showBreadcrumb = true;
        $breadcrumbs = generate_breadcrumb('about');
        $additionalCSS = ['assets/css/about.css?v=' . time()];
        $additionalJS = ['assets/js/about.js?v=' . time()];
        break;
        
    case 'products':
        $title = 'Sản phẩm - Thuong Lo';
        $content = 'app/views/products/products.php';
        $showPageHeader = true;
        $showCTA = false;
        $showBreadcrumb = true;
        $breadcrumbs = generate_breadcrumb('products');
        break;
        
    case 'categories':
        $title = 'Danh mục - Thuong Lo';
        $content = 'app/views/categories/categories.php';
        $showPageHeader = true;
        $showCTA = false;
        $showBreadcrumb = true;
        $breadcrumbs = generate_breadcrumb('categories');
        break;
        
    case 'details':
    case 'course-details':
        $title = 'Gói Data Nguồn Hàng Premium - Thuong Lo';
        $content = 'app/views/products/details.php';
        $showPageHeader = false;
        $showCTA = false;
        $showBreadcrumb = true;
        
        // Ưu tiên lấy từ database nếu có ID
        if (isset($_GET['id']) && is_numeric($_GET['id'])) {
            $breadcrumbs = get_product_breadcrumb_from_db($_GET['id']);
        } else {
            // Chỉ sử dụng product name, không dùng category để tránh trùng lặp
            $product_name = $_GET['product'] ?? 'Data nguồn hàng chất lượng cao';
            $breadcrumbs = [
                ['title' => 'Trang chủ', 'url' => './'],
                ['title' => 'Sản phẩm', 'url' => '?page=products'],
                ['title' => $product_name]
            ];
        }
        break;
        
    case 'news':
        $title = 'Tin tức - Thuong Lo';
        $content = 'app/views/news/news.php';
        $showPageHeader = true;
        $showCTA = false;
        $showBreadcrumb = true;
        $breadcrumbs = generate_breadcrumb('news');
        break;
        
    case 'news-details':
        $title = 'Chi tiết tin tức - Thuong Lo';
        $content = 'app/views/news/details.php';
        $showPageHeader = false;
        $showCTA = false;
        $showBreadcrumb = true;
        
        // Ưu tiên lấy từ database nếu có ID
        if (isset($_GET['id']) && is_numeric($_GET['id'])) {
            $breadcrumbs = get_news_breadcrumb_from_db($_GET['id']);
        } else {
            // Fallback sử dụng URL params
            $news_category = $_GET['category'] ?? '';
            $news_title = $_GET['title'] ?? 'Chi tiết tin tức';
            $breadcrumbs = generate_news_breadcrumb($news_category, $news_title);
        }
        break;
        
    case 'contact':
        $title = 'Liên hệ - Thuong Lo';
        $content = 'app/views/contact/contact.php';
        $showPageHeader = true;
        $showCTA = false;
        $showBreadcrumb = true;
        $breadcrumbs = generate_breadcrumb('contact');
        break;
        
    case 'login':
        $title = 'Đăng nhập - Thuong Lo';
        $content = 'app/views/auth/login.php';
        $showPageHeader = false;
        $showCTA = false;
        $showBreadcrumb = true;
        $breadcrumbs = generate_breadcrumb('auth');
        break;
        
    case 'register':
        $title = 'Đăng ký - Thuong Lo';
        $content = 'app/views/auth/register.php';
        $showPageHeader = false;
        $showCTA = false;
        $showBreadcrumb = true;
        $breadcrumbs = generate_breadcrumb('register');
        break;

    case 'forgot':
        $title = 'Quên mật khẩu - Thuong Lo';
        $content = 'app/views/auth/forgot.php';
        $showPageHeader = false;
        $showCTA = false;
        $showBreadcrumb = true;
        $breadcrumbs = [
            ['title' => 'Trang chủ', 'url' => './'],
            ['title' => 'Đăng nhập', 'url' => '?page=login'],
            ['title' => 'Quên mật khẩu']
        ];
        break;

    case 'checkout':
        $title = 'Thanh toán - Thuong Lo';
        $content = 'app/views/payment/checkout.php';
        $showPageHeader = false;
        $showCTA = false;
        $showBreadcrumb = true;
        $breadcrumbs = generate_breadcrumb('checkout');
        break;
        
    case 'payment':
        $title = 'Thanh toán - Thuong Lo';
        $content = 'app/views/payment/payment.php';
        $showPageHeader = false;
        $showCTA = false;
        $showBreadcrumb = true;
        $breadcrumbs = [
            ['title' => 'Trang chủ', 'url' => './'],
            ['title' => 'Giỏ hàng', 'url' => '?page=cart'],
            ['title' => 'Thanh toán', 'url' => '?page=checkout'],
            ['title' => 'Xử lý thanh toán']
        ];
        break;
        
    case 'payment_success':
        $title = 'Thành công - Thuong Lo';
        $content = 'app/views/payment/success.php';
        $showPageHeader = false;
        $showCTA = false;
        $showBreadcrumb = true;
        $breadcrumbs = [
            ['title' => 'Trang chủ', 'url' => './'],
            ['title' => 'Thanh toán thành công']
        ];
        break;
        
    case 'admin':
        // Admin panel routing
        $module = $_GET['module'] ?? 'dashboard';
        $action = $_GET['action'] ?? 'index';
        
        // Check admin authentication (tạm thời bỏ qua để test)
        // if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
        //     header('Location: ?page=login');
        //     exit;
        // }
        
        // Set admin page variables
        $title = 'Admin Panel - Thuong Lo';
        $useAdminLayout = true; // Flag to use admin layout
        
        // Route to specific admin modules
        switch($module) {
            case 'dashboard':
                $page_title = 'Dashboard';
                $content = 'app/views/admin/dashboard.php';
                break;
                
            case 'products':
                $page_title = 'Quản lý Sản phẩm';
                switch($action) {
                    case 'add':
                        $content = 'app/views/admin/products/add.php';
                        break;
                    case 'edit':
                        $content = 'app/views/admin/products/edit.php';
                        break;
                    case 'view':
                        $content = 'app/views/admin/products/view.php';
                        break;
                    case 'delete':
                        $content = 'app/views/admin/products/delete.php';
                        break;
                    default:
                        $content = 'app/views/admin/products/index.php';
                        break;
                }
                break;
                
            case 'categories':
                $page_title = 'Quản lý Danh mục';
                switch($action) {
                    case 'add':
                        $content = 'app/views/admin/categories/add.php';
                        break;
                    case 'edit':
                        $content = 'app/views/admin/categories/edit.php';
                        break;
                    case 'view':
                        $content = 'app/views/admin/categories/view.php';
                        break;
                    case 'delete':
                        $content = 'app/views/admin/categories/delete.php';
                        break;
                    default:
                        $content = 'app/views/admin/categories/index.php';
                        break;
                }
                break;
                
            case 'news':
                $page_title = 'Quản lý Tin tức';
                switch($action) {
                    case 'add':
                        $content = 'app/views/admin/news/add.php';
                        break;
                    case 'edit':
                        $content = 'app/views/admin/news/edit.php';
                        break;
                    case 'view':
                        $content = 'app/views/admin/news/view.php';
                        break;
                    case 'delete':
                        $content = 'app/views/admin/news/delete.php';
                        break;
                    default:
                        $content = 'app/views/admin/news/index.php';
                        break;
                }
                break;
                
            case 'events':
                $page_title = 'Quản lý Sự kiện';
                switch($action) {
                    case 'add':
                        $content = 'app/views/admin/events/add.php';
                        break;
                    case 'edit':
                        $content = 'app/views/admin/events/edit.php';
                        break;
                    case 'view':
                        $content = 'app/views/admin/events/view.php';
                        break;
                    case 'delete':
                        $content = 'app/views/admin/events/delete.php';
                        break;
                    default:
                        $content = 'app/views/admin/events/index.php';
                        break;
                }
                break;
                
            case 'orders':
                $page_title = 'Quản lý Đơn hàng';
                switch($action) {
                    case 'edit':
                        $content = 'app/views/admin/orders/edit.php';
                        break;
                    case 'view':
                        $content = 'app/views/admin/orders/view.php';
                        break;
                    case 'delete':
                        $content = 'app/views/admin/orders/delete.php';
                        break;
                    default:
                        $content = 'app/views/admin/orders/index.php';
                        break;
                }
                break;
                
            case 'users':
                $page_title = 'Quản lý Người dùng';
                switch($action) {
                    case 'add':
                        $content = 'app/views/admin/users/add.php';
                        break;
                    case 'edit':
                        $content = 'app/views/admin/users/edit.php';
                        break;
                    case 'view':
                        $content = 'app/views/admin/users/view.php';
                        break;
                    case 'delete':
                        $content = 'app/views/admin/users/delete.php';
                        break;
                    default:
                        $content = 'app/views/admin/users/index.php';
                        break;
                }
                break;
                
            case 'affiliates':
                $page_title = 'Quản lý Đại lý';
                switch($action) {
                    case 'add':
                        $content = 'app/views/admin/affiliates/add.php';
                        break;
                    case 'edit':
                        $content = 'app/views/admin/affiliates/edit.php';
                        break;
                    case 'view':
                        $content = 'app/views/admin/affiliates/view.php';
                        break;
                    case 'delete':
                        $content = 'app/views/admin/affiliates/delete.php';
                        break;
                    default:
                        $content = 'app/views/admin/affiliates/index.php';
                        break;
                }
                break;
                
            case 'contact':
                $page_title = 'Quản lý Liên hệ';
                switch($action) {
                    case 'edit':
                        $content = 'app/views/admin/contact/edit.php';
                        break;
                    case 'view':
                        $content = 'app/views/admin/contact/view.php';
                        break;
                    case 'delete':
                        $content = 'app/views/admin/contact/delete.php';
                        break;
                    default:
                        $content = 'app/views/admin/contact/index.php';
                        break;
                }
                break;
                
            case 'revenue':
                $page_title = 'Báo cáo Doanh thu';
                switch($action) {
                    case 'view':
                        $content = 'app/views/admin/revenue/view.php';
                        break;
                    default:
                        $content = 'app/views/admin/revenue/index.php';
                        break;
                }
                break;
                
            case 'settings':
                $page_title = 'Cài đặt Hệ thống';
                switch($action) {
                    case 'add':
                        $content = 'app/views/admin/settings/add.php';
                        break;
                    case 'edit':
                        $content = 'app/views/admin/settings/edit.php';
                        break;
                    case 'view':
                        $content = 'app/views/admin/settings/view.php';
                        break;
                    case 'delete':
                        $content = 'app/views/admin/settings/delete.php';
                        break;
                    default:
                        $content = 'app/views/admin/settings/index.php';
                        break;
                }
                break;
                
            default:
                $page_title = 'Dashboard';
                $content = 'app/views/admin/dashboard.php';
                break;
        }
        break;
        
    case 'users':
        // User dashboard routing
        $module = $_GET['module'] ?? 'dashboard';
        $action = $_GET['action'] ?? 'index';
        
        // Check user authentication (tạm thời bỏ qua để test)
        // if (!isset($_SESSION['role'])) {
        //     header('Location: ?page=login');
        //     exit;
        // }
        
        // Set user page variables
        $title = 'Tài khoản - Thuong Lo';
        $showPageHeader = false;
        $showCTA = false;
        $showBreadcrumb = true;
        
        // Route to specific user modules
        switch($module) {
            case 'dashboard':
                $page_title = 'Dashboard';
                $content = 'app/views/users/dashboard.php';
                $breadcrumbs = [
                    ['title' => 'Trang chủ', 'url' => './'],
                    ['title' => 'Tài khoản', 'url' => '?page=users'],
                    ['title' => 'Dashboard']
                ];
                break;
                
            case 'account':
                $page_title = 'Quản lý Tài khoản';
                switch($action) {
                    case 'edit':
                        $content = 'app/views/users/account/edit.php';
                        break;
                    case 'view':
                        $content = 'app/views/users/account/view.php';
                        break;
                    case 'delete':
                        $content = 'app/views/users/account/delete.php';
                        break;
                    default:
                        $content = 'app/views/users/account/index.php';
                        break;
                }
                $breadcrumbs = [
                    ['title' => 'Trang chủ', 'url' => './'],
                    ['title' => 'Tài khoản', 'url' => '?page=users'],
                    ['title' => 'Thông tin tài khoản']
                ];
                break;
                
            case 'orders':
                $page_title = 'Quản lý Đơn hàng';
                switch($action) {
                    case 'view':
                        $content = 'app/views/users/orders/view.php';
                        break;
                    case 'edit':
                        $content = 'app/views/users/orders/edit.php';
                        break;
                    case 'delete':
                        $content = 'app/views/users/orders/delete.php';
                        break;
                    default:
                        $content = 'app/views/users/orders/index.php';
                        break;
                }
                $breadcrumbs = [
                    ['title' => 'Trang chủ', 'url' => './'],
                    ['title' => 'Tài khoản', 'url' => '?page=users'],
                    ['title' => 'Đơn hàng']
                ];
                break;
                
            case 'cart':
                $page_title = 'Giỏ hàng';
                switch($action) {
                    case 'add':
                        $content = 'app/views/users/cart/add.php';
                        break;
                    case 'edit':
                        $content = 'app/views/users/cart/edit.php';
                        break;
                    case 'view':
                        $content = 'app/views/users/cart/view.php';
                        break;
                    case 'delete':
                        $content = 'app/views/users/cart/delete.php';
                        break;
                    default:
                        $content = 'app/views/users/cart/index.php';
                        break;
                }
                $breadcrumbs = [
                    ['title' => 'Trang chủ', 'url' => './'],
                    ['title' => 'Tài khoản', 'url' => '?page=users'],
                    ['title' => 'Giỏ hàng']
                ];
                break;
                
            case 'wishlist':
                $page_title = 'Danh sách yêu thích';
                switch($action) {
                    case 'add':
                        $content = 'app/views/users/wishlist/add.php';
                        break;
                    case 'edit':
                        $content = 'app/views/users/wishlist/edit.php';
                        break;
                    case 'view':
                        $content = 'app/views/users/wishlist/view.php';
                        break;
                    case 'delete':
                        $content = 'app/views/users/wishlist/delete.php';
                        break;
                    default:
                        $content = 'app/views/users/wishlist/index.php';
                        break;
                }
                $breadcrumbs = [
                    ['title' => 'Trang chủ', 'url' => './'],
                    ['title' => 'Tài khoản', 'url' => '?page=users'],
                    ['title' => 'Yêu thích']
                ];
                break;
                
            default:
                $page_title = 'Dashboard';
                $content = 'app/views/users/dashboard.php';
                $breadcrumbs = [
                    ['title' => 'Trang chủ', 'url' => './'],
                    ['title' => 'Tài khoản', 'url' => '?page=users'],
                    ['title' => 'Dashboard']
                ];
                break;
        }
        break;
        
    case 'affiliate':
        // Affiliate dashboard routing - Uses its own layout
        $module = $_GET['module'] ?? 'dashboard';
        $action = $_GET['action'] ?? 'index';
        
        // Check affiliate authentication (TEMPORARY DISABLED FOR TESTING)
        // TODO: Enable this after testing is complete
        // if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'agent') {
        //     header('Location: ?page=login');
        //     exit;
        // }
        
        // Set flag to use affiliate layout
        $useAffiliateLayout = true;
        
        // Route to specific affiliate modules
        switch($module) {
            case 'dashboard':
            default:
                $content = 'app/views/affiliate/dashboard.php';
                break;
                
            case 'commissions':
                switch($action) {
                    case 'history':
                        $content = 'app/views/affiliate/commissions/history.php';
                        break;
                    case 'policy':
                        $content = 'app/views/affiliate/commissions/policy.php';
                        break;
                    default:
                        $content = 'app/views/affiliate/commissions/index.php';
                        break;
                }
                break;
                
            case 'customers':
                switch($action) {
                    case 'detail':
                        $content = 'app/views/affiliate/customers/detail.php';
                        break;
                    case 'list':
                    default:
                        $content = 'app/views/affiliate/customers/list.php';
                        break;
                }
                break;
                
            case 'finance':
                switch($action) {
                    case 'withdraw':
                        $content = 'app/views/affiliate/finance/withdraw.php';
                        break;
                    case 'webhook_demo':
                        $content = 'app/views/affiliate/finance/webhook_demo.php';
                        break;
                    default:
                        $content = 'app/views/affiliate/finance/index.php';
                        break;
                }
                break;
                
            case 'marketing':
                $content = 'app/views/affiliate/marketing/index.php';
                break;
                
            case 'reports':
                switch($action) {
                    case 'orders':
                        $content = 'app/views/affiliate/reports/orders.php';
                        break;
                    case 'clicks':
                    default:
                        $content = 'app/views/affiliate/reports/clicks.php';
                        break;
                }
                break;
                
            case 'profile':
                switch($action) {
                    case 'settings':
                    default:
                        $content = 'app/views/affiliate/profile/settings.php';
                        break;
                }
                break;
        }
        break;
        
    default:
        $title = 'Không tìm thấy trang - Thuong Lo';
        $content = 'errors/404.php';
        $showPageHeader = false;
        $showCTA = false;
        $showBreadcrumb = false;
        break;
}

// Include master layout
if (isset($useAdminLayout) && $useAdminLayout) {
    include_once 'app/views/_layout/admin_master.php';
} elseif (isset($useAffiliateLayout) && $useAffiliateLayout) {
    // Affiliate pages include their own layout
    include_once $content;
} else {
    include_once 'app/views/_layout/master.php';
}

if (ob_get_level() > 0) {
    ob_end_flush();
}
?>