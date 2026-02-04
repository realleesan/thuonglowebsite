<?php
// Khởi tạo session
session_start();

// Include các file cần thiết
$config = require_once 'config.php';
require_once 'core/functions.php';

// Initialize URL Builder
init_url_builder();

// Bật output buffering để các trang con có thể sử dụng header() sau khi layout đã bắt đầu render
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
        
        // Check admin authentication
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
            header('Location: ?page=login');
            exit;
        }
        
        // Set admin page variables
        $title = 'Admin Panel - Thuong Lo';
        $useAdminLayout = true; // Flag to use admin layout
        
        // Route to specific admin modules
        switch($module) {
            case 'dashboard':
                $pageTitle = 'Dashboard';
                $content = 'app/views/admin/dashboard.php';
                break;
                
            case 'products':
                $pageTitle = 'Quản lý sản phẩm';
                if ($action === 'change') {
                    $content = 'app/views/admin/products/change.php';
                    $pageTitle = isset($_GET['id']) ? 'Sửa sản phẩm' : 'Thêm sản phẩm';
                } elseif ($action === 'delete') {
                    $content = 'app/views/admin/products/delete.php';
                    $pageTitle = 'Xóa sản phẩm';
                } else {
                    $content = 'app/views/admin/products/index.php';
                }
                break;
                
            case 'categories':
                $pageTitle = 'Quản lý danh mục';
                if ($action === 'change') {
                    $content = 'app/views/admin/categories/change.php';
                    $pageTitle = isset($_GET['id']) ? 'Sửa danh mục' : 'Thêm danh mục';
                } elseif ($action === 'delete') {
                    $content = 'app/views/admin/categories/delete.php';
                    $pageTitle = 'Xóa danh mục';
                } else {
                    $content = 'app/views/admin/categories/index.php';
                }
                break;
                
            case 'news':
                $pageTitle = 'Quản lý tin tức';
                if ($action === 'change') {
                    $content = 'app/views/admin/news/change.php';
                    $pageTitle = isset($_GET['id']) ? 'Sửa tin tức' : 'Thêm tin tức';
                } elseif ($action === 'delete') {
                    $content = 'app/views/admin/news/delete.php';
                    $pageTitle = 'Xóa tin tức';
                } else {
                    $content = 'app/views/admin/news/index.php';
                }
                break;
                
            case 'events':
                $pageTitle = 'Quản lý sự kiện';
                if ($action === 'change') {
                    $content = 'app/views/admin/events/change.php';
                    $pageTitle = isset($_GET['id']) ? 'Sửa sự kiện' : 'Thêm sự kiện';
                } elseif ($action === 'delete') {
                    $content = 'app/views/admin/events/delete.php';
                    $pageTitle = 'Xóa sự kiện';
                } else {
                    $content = 'app/views/admin/events/index.php';
                }
                break;
                
            case 'users':
                $pageTitle = 'Quản lý người dùng';
                $content = 'app/views/admin/users/index.php';
                break;
                
            case 'settings':
                $pageTitle = 'Cài đặt hệ thống';
                $content = 'app/views/admin/settings/index.php';
                break;
                
            default:
                $pageTitle = 'Dashboard';
                $content = 'app/views/admin/dashboard.php';
                break;
        }
        break;
        
    case 'users':
        // User dashboard routing
        $module = $_GET['module'] ?? 'dashboard';
        
        // Check user authentication
        if (!isset($_SESSION['role'])) {
            header('Location: ?page=login');
            exit;
        }
        
        $title = 'Tài khoản - Thuong Lo';
        $content = 'app/views/users/dashboard.php';
        $showPageHeader = false;
        $showCTA = false;
        $showBreadcrumb = true;
        $breadcrumbs = [
            ['title' => 'Trang chủ', 'url' => './'],
            ['title' => 'Tài khoản']
        ];
        break;
        
    case 'affiliate':
        // Affiliate dashboard routing
        $module = $_GET['module'] ?? 'dashboard';
        
        // Check affiliate authentication
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'agent') {
            header('Location: ?page=login');
            exit;
        }
        
        $title = 'Đại lý - Thuong Lo';
        $content = 'app/views/affiliate/dashboard.php';
        $showPageHeader = false;
        $showCTA = false;
        $showBreadcrumb = true;
        $breadcrumbs = [
            ['title' => 'Trang chủ', 'url' => './'],
            ['title' => 'Đại lý']
        ];
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
} else {
    include_once 'app/views/_layout/master.php';
}

if (ob_get_level() > 0) {
    ob_end_flush();
}
?>