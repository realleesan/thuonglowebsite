<?php
// Define security constant for core files
define('THUONGLO_INIT', true);

// Start session early
session_start();

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

// Set custom error handler
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    error_log("PHP Error [$errno]: $errstr in $errfile on line $errline");
    return false;
}); 

// Set exception handler
set_exception_handler(function($e) {
    error_log('Uncaught exception: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
    echo '<h1>Lỗi hệ thống</h1>';
    echo '<p>' . htmlspecialchars($e->getMessage()) . '</p>';
    echo '<pre>' . htmlspecialchars($e->getTraceAsString()) . '</pre>';
    exit;
});

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

// Start output buffering EARLY (before any potential headers or output)
if (ob_get_level() === 0) {
    ob_start();
}

// Include core files
require_once $base_dir . '/core/security.php';
require_once $base_dir . '/core/functions.php';
require_once $base_dir . '/app/middleware/AuthMiddleware.php'; // Authentication middleware
require_once $base_dir . '/core/view_init.php'; // Khởi tạo ServiceManager & services

// Initialize URL Builder
init_url_builder();

// Lấy trang hiện tại từ URL
$page = $_GET['page'] ?? 'home';

// Hỗ trợ định dạng URL kiểu admin/hero-section/edit/1
if (strpos($page, '/') !== false) {
    $parts = explode('/', trim($page, '/'));
    if ($parts[0] === 'admin') {
        $_GET['page'] = 'admin';
        $page = 'admin';
        
        if (isset($parts[1])) {
            $_GET['module'] = $parts[1];
        }
        if (isset($parts[2])) {
            $_GET['action'] = $parts[2];
        }
        if (isset($parts[3])) {
            $_GET['id'] = $parts[3];
        }
    }
}

// Mặc định: dùng PublicService cho các trang public
$currentService = $publicService ?? null;

// Thiết lập thông tin cho từng trang
switch($page) {
    case 'home':
        $title = 'Trang chủ - Thuong Lo';
        $content = 'app/views/home/home.php';
        $showPageHeader = false;
        $showCTA = true;
        $showBreadcrumb = false; // Trang chủ không cần breadcrumb
        // Public pages dùng PublicService
        $currentService = $publicService ?? $currentService;
        break;
        
    case 'test_debug':
        // Test debug page
        require_once __DIR__ . '/test_debug.php';
        exit;
        break;
        
    case 'test_table':
        // Test table structure
        require_once __DIR__ . '/test_table_structure.php';
        exit;
        break;
        
    case 'about':
        $title = 'Giới thiệu - Thuong Lo';
        $content = 'app/views/about/about.php';
        $showPageHeader = true;
        $showCTA = false;
        $showBreadcrumb = true;
        $breadcrumbs = generate_breadcrumb('about');
        $currentService = $publicService ?? $currentService;
        break;
        
    case 'products':
        $title = 'Sản phẩm - Thuong Lo';
        $content = 'app/views/products/products.php';
        $showPageHeader = true;
        $showCTA = false;
        $showBreadcrumb = true;
        $breadcrumbs = generate_breadcrumb('products');
        $currentService = $publicService ?? $currentService;
        break;
        
    case 'categories':
    case 'view':
        $title = 'Danh mục - Thuong Lo';
        $content = 'app/views/categories/categories.php';
        $showPageHeader = true;
        $showCTA = false;
        $showBreadcrumb = true;
        $breadcrumbs = generate_breadcrumb('categories');
        $currentService = $publicService ?? $currentService;
        break;
        
    case 'brands':
        $title = 'Thương hiệu - Thuong Lo';
        $content = 'app/views/brands/brands.php';
        $showPageHeader = true;
        $showCTA = false;
        $showBreadcrumb = true;
        $breadcrumbs = [
            ['title' => 'Trang chủ', 'url' => './'],
            ['title' => 'Thương hiệu']
        ];
        $currentService = $publicService ?? $currentService;
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
        $currentService = $publicService ?? $currentService;
        break;
        
    case 'product-data':
        $title = 'Danh sách dữ liệu - Thuong Lo';
        $content = 'app/views/products/data_list.php';
        $showPageHeader = false;
        $showCTA = false;
        $showBreadcrumb = true;
        $breadcrumbs = [
            ['title' => 'Trang chủ', 'url' => './'],
            ['title' => 'Danh sách dữ liệu']
        ];
        break;
        
    case 'news':
        $title = 'Tin tức - Thuong Lo';
        $content = 'app/views/news/news.php';
        $showPageHeader = true;
        $showCTA = false;
        $showBreadcrumb = true;
        $breadcrumbs = generate_breadcrumb('news');
        $currentService = $publicService ?? $currentService;
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
        $currentService = $publicService ?? $currentService;
        break;
        
    case 'contact':
        // Handle POST request for contact form submission
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $response = ['success' => false, 'message' => ''];
            
            try {
                // Validate required fields
                $name = trim($_POST['your-name'] ?? '');
                $email = trim($_POST['your-email'] ?? '');
                $subject = trim($_POST['your-subject'] ?? '');
                $message = trim($_POST['your-message'] ?? '');
                
                if (empty($name)) {
                    $response['message'] = 'Vui lòng nhập họ tên';
                } elseif (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $response['message'] = 'Vui lòng nhập email hợp lệ';
                } elseif (empty($subject)) {
                    $response['message'] = 'Vui lòng nhập tiêu đề';
                } elseif (empty($message)) {
                    $response['message'] = 'Vui lòng nhập nội dung';
                } else {
                    // Save contact to database
                    require_once 'app/models/ContactsModel.php';
                    $contactsModel = new ContactsModel();
                    
                    $data = [
                        'name' => $name,
                        'email' => $email,
                        'subject' => $subject,
                        'message' => $message,
                        'status' => 'new',
                        'priority' => 'normal'
                    ];
                    
                    $result = $contactsModel->createSubmission($data);
                    
                    if ($result) {
                        $response['success'] = true;
                        $response['message'] = 'Cảm ơn bạn! Tin nhắn của bạn đã được gửi thành công. Chúng tôi sẽ liên hệ lại sớm nhất có thể.';
                    } else {
                        $response['message'] = 'Đã xảy ra lỗi. Vui lòng thử lại sau.';
                    }
                }
            } catch (Exception $e) {
                $response['message'] = 'Đã xảy ra lỗi: ' . $e->getMessage();
            }
            
            // Return JSON response
            header('Content-Type: application/json');
            echo json_encode($response);
            exit;
        }
        
        $title = 'Liên hệ - Thuong Lo';
        $content = 'app/views/contact/contact.php';
        $showPageHeader = true;
        $showCTA = false;
        $showBreadcrumb = true;
        $breadcrumbs = generate_breadcrumb('contact');
        $currentService = $publicService ?? $currentService;
        break;
        
    case 'faq':
        $title = 'Câu hỏi thường gặp - Thuong Lo';
        $content = 'app/views/faq/faq.php';
        $showPageHeader = true;
        $showCTA = false;
        $showBreadcrumb = true;
        $breadcrumbs = generate_breadcrumb('faq');
        $currentService = $publicService ?? $currentService;
        break;
        
    case 'shopping-guide':
        $title = 'Hướng dẫn mua hàng - Thuong Lo';
        $content = 'app/views/shopping-guide/shopping-guide.php';
        $showPageHeader = true;
        $showCTA = false;
        $showBreadcrumb = true;
        $breadcrumbs = generate_breadcrumb('shopping-guide');
        $currentService = $publicService ?? $currentService;
        break;
        
    case 'terms':
        $title = 'Điều khoản dịch vụ - Thuong Lo';
        $content = 'app/views/terms/terms.php';
        $showPageHeader = true;
        $showCTA = false;
        $showBreadcrumb = true;
        $breadcrumbs = generate_breadcrumb('terms');
        $currentService = $publicService ?? $currentService;
        break;
        
    case 'privacy':
        $title = 'Chính sách bảo mật - Thuong Lo';
        $content = 'app/views/privacy/privacy.php';
        $showPageHeader = true;
        $showCTA = false;
        $showBreadcrumb = true;
        $breadcrumbs = generate_breadcrumb('privacy');
        $currentService = $publicService ?? $currentService;
        break;
        
    case 'login':
        $action = $_GET['action'] ?? '';
        if ($action === 'process' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            // Process login
            require_once 'app/controllers/AuthController.php';
            $authController = new AuthController();
            $authController->processLogin();
            exit;
        } else {
            // Show login form
            require_once 'app/controllers/AuthController.php';
            $authController = new AuthController();
            $authController->login();
            exit;
        }
        break;
        
    case 'register':
        $action = $_GET['action'] ?? '';
        if ($action === 'process' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            // Process registration
            require_once 'app/controllers/AuthController.php';
            $authController = new AuthController();
            $authController->processRegister();
            exit;
        } else {
            // Show registration form
            require_once 'app/controllers/AuthController.php';
            $authController = new AuthController();
            $authController->register();
            exit;
        }
        break;

    case 'forgot':
        $action = $_GET['action'] ?? '';
        if ($action === 'process' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            // Process forgot password
            require_once 'app/controllers/AuthController.php';
            $authController = new AuthController();
            $authController->processForgot();
            exit;
        } else {
            // Show forgot password form
            require_once 'app/controllers/AuthController.php';
            $authController = new AuthController();
            $authController->forgot();
            exit;
        }
        break;

    case 'logout':
        // Process logout
        require_once 'app/controllers/AuthController.php';
        $authController = new AuthController();
        $authController->logout();
        exit;
        break;

    case 'agent':
        // Agent registration routes
        $action = $_GET['action'] ?? '';
        
        // Check if user is logged in as admin - allow direct access
        if (isset($_SESSION['user_id']) && !empty($_SESSION['user_id'])) {
            $userRole = $_SESSION['user_role'] ?? '';
            if ($userRole === 'admin') {
                // Admin can access affiliate dashboard directly
                // Redirect to affiliate page
                header('Location: ?page=affiliate');
                exit;
            }
        }
        
        require_once 'app/controllers/AffiliateController.php';
        $agentController = new AffiliateController();
        
        switch($action) {
            case 'popup':
                $agentController->showRegistrationPopup();
                exit;
                break;
            case 'register':
                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    $agentController->processRegistration();
                } else {
                    $agentController->showRegistrationPopup();
                }
                exit;
                break;
            case 'status':
                $agentController->checkStatus();
                exit;
                break;
            case 'processing':
                $agentController->showProcessingMessage();
                exit;
                break;
            default:
                // Default to showing popup
                $agentController->showRegistrationPopup();
                exit;
                break;
        }
        break;

    case 'agent-page':
        // Load dynamic agent content page
        $page_key = $_GET['key'] ?? '';
        if (empty($page_key)) {
            header('Location: ' . base_url());
            exit;
        }

        require_once 'app/models/AgentContentModel.php';
        $agentContentModel = new AgentContentModel();
        $pageData = $agentContentModel->getByPageKey($page_key);

        if (!$pageData) {
            // Seed defaults if page key does not exist yet (fallback to prevent error screen)
            $defaultPages = [
                'chuong_trinh' => [
                    'page_key' => 'chuong_trinh',
                    'title' => 'Chương trình đại lý',
                    'content' => '<h2>Chào mừng bạn đến với Chương trình Đại lý Thuong Lo</h2><p>Hệ thống đại lý của chúng tôi cung cấp giải pháp gia tăng thu nhập vượt trội cùng các công cụ hỗ trợ bán hàng tối tân nhất. Nội dung đang được cập nhật thêm bởi quản trị viên...</p>',
                    'image' => '',
                    'meta_title' => 'Chương trình Đại lý - Thuong Lo',
                    'meta_description' => 'Tham gia chương trình đại lý Thuong Lo để nhận chiết khấu và hoa hồng cực cao.'
                ],
                'huong_dan' => [
                    'page_key' => 'huong_dan',
                    'title' => 'Hướng dẫn đăng ký đại lý',
                    'content' => '<h2>Hướng dẫn các bước đăng ký làm đại lý Thuong Lo</h2><p>Để trở thành đại lý, vui lòng nhấp vào nút "Đăng Ký Ngay" ở chân trang, điền đầy đủ thông tin cá nhân và tài khoản thanh toán PayOS để được kích hoạt tự động. Nội dung chi tiết đang được cập nhật...</p>',
                    'image' => '',
                    'meta_title' => 'Hướng dẫn đăng ký đại lý - Thuong Lo',
                    'meta_description' => 'Xem chi tiết các bước đăng ký tài khoản đại lý tại Thuong Lo nhanh chóng.'
                ],
                'chinh_sach' => [
                    'page_key' => 'chinh_sach',
                    'title' => 'Chính sách đại lý',
                    'content' => '<h2>Chính sách đại lý & Quyền lợi hợp tác</h2><p>Chúng tôi cam kết mức chiết khấu hấp dẫn lên đến 30% giá trị gói dữ liệu cùng chế độ rút tiền tự động hoàn toàn miễn phí qua cổng PayOS. Chi tiết chính sách đang được cập nhật...</p>',
                    'image' => '',
                    'meta_title' => 'Chính sách đại lý - Thuong Lo',
                    'meta_description' => 'Đọc các chính sách, điều khoản và quyền lợi dành cho đại lý Thuong Lo.'
                ],
                'tai_nguyen' => [
                    'page_key' => 'tai_nguyen',
                    'title' => 'Tài nguyên - tài liệu đại lý',
                    'content' => '<h2>Kho tài nguyên & Tài liệu phục vụ đại lý tiếp thị</h2><p>Hệ thống cung cấp sẵn các bộ Banner truyền thông, File thiết kế SVG, Tài liệu giới thiệu sản phẩm và Đường dẫn tiếp thị tùy biến. Nội dung kho tài nguyên đang được cập nhật...</p>',
                    'image' => '',
                    'meta_title' => 'Tài nguyên - Tài liệu đại lý - Thuong Lo',
                    'meta_description' => 'Tải xuống banner, hình ảnh, tài liệu giới thiệu và công cụ hỗ trợ bán hàng.'
                ]
            ];

            if (isset($defaultPages[$page_key])) {
                $pageData = $defaultPages[$page_key];
            } else {
                header('Location: ' . base_url());
                exit;
            }
        }

        // Set layout variables
        $title = !empty($pageData['meta_title']) ? $pageData['meta_title'] : $pageData['title'] . ' - Thuong Lo';
        $meta_description = !empty($pageData['meta_description']) ? $pageData['meta_description'] : '';
        $content = 'app/views/affiliate/agent_page.php';
        $showPageHeader = false;
        $showCTA = false;
        $showBreadcrumb = false;
        break;

    case 'users':
        // User dashboard and account pages
        $module = $_GET['module'] ?? 'dashboard';
        $action = $_GET['action'] ?? 'index';
        $title = 'Tài khoản - Thuong Lo';
        $showPageHeader = false;
        $showCTA = false;
        $showBreadcrumb = true;
        
        // Check authentication using AuthService (includes device validation)
        require_once __DIR__ . '/app/services/AuthService.php';
        $authService = new AuthService();
        if (!$authService->isAuthenticated()) {
            header('Location: ?page=login');
            exit;
        }
        
        // Route to specific user modules
        switch($module) {
            case 'dashboard':
            default:
                $content = 'app/views/users/dashboard.php';
                $title = 'Tài khoản của tôi - Thuong Lo';
                $breadcrumbs = [
                    ['title' => 'Trang chủ', 'url' => './'],
                    ['title' => 'Tài khoản', 'url' => '?page=users'],
                    ['title' => 'Dashboard']
                ];
                break;
                
            case 'account':
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
                $title = 'Thông tin tài khoản - Thuong Lo';
                $breadcrumbs = [
                    ['title' => 'Trang chủ', 'url' => './'],
                    ['title' => 'Tài khoản', 'url' => '?page=users'],
                    ['title' => 'Thông tin tài khoản']
                ];
                break;
                
            case 'orders':
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
                $title = 'Đơn hàng - Thuong Lo';
                $breadcrumbs = [
                    ['title' => 'Trang chủ', 'url' => './'],
                    ['title' => 'Tài khoản', 'url' => '?page=users'],
                    ['title' => 'Đơn hàng']
                ];
                break;
                
            case 'cart':
                $content = 'app/views/users/cart/index.php';
                $title = 'Giỏ hàng - Thuong Lo';
                $breadcrumbs = [
                    ['title' => 'Trang chủ', 'url' => './'],
                    ['title' => 'Tài khoản', 'url' => '?page=users'],
                    ['title' => 'Giỏ hàng']
                ];
                break;
                
            case 'wishlist':
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
                $title = 'Yêu thích - Thuong Lo';
                $breadcrumbs = [
                    ['title' => 'Trang chủ', 'url' => './'],
                    ['title' => 'Tài khoản', 'url' => '?page=users'],
                    ['title' => 'Yêu thích']
                ];
                break;

            case 'access':
                switch($action) {
                    case 'view':
                        $content = 'app/views/users/access/view.php';
                        break;
                    case 'edit':
                        $content = 'app/views/users/access/edit.php';
                        break;
                    case 'delete':
                        $content = 'app/views/users/access/delete.php';
                        break;
                    default:
                        $content = 'app/views/users/access/index.php';
                        break;
                }
                $title = 'Quản lý truy cập - Thuong Lo';
                $breadcrumbs = [
                    ['title' => 'Trang chủ', 'url' => './'],
                    ['title' => 'Tài khoản', 'url' => '?page=users'],
                    ['title' => 'Quản lý truy cập']
                ];
                break;
        }
        
        $currentService = $userService ?? $currentService;
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
        
        // Check authentication and admin role using AuthMiddleware (includes device validation)
        try {
            $authMiddleware = new AuthMiddleware();
            if (!$authMiddleware->requireAdmin()) {
                // Redirect to appropriate dashboard based on role
                if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'affiliate') {
                    header('Location: ?page=affiliate');
                } else {
                    header('Location: ?page=users');
                }
                exit;
            }
        } catch (Exception $e) {
            error_log('Admin auth error: ' . $e->getMessage());
            die('Authentication error: ' . $e->getMessage());
        }
        
        // Set admin page variables
        $title = 'Admin Panel - Thuong Lo';
        $useAdminLayout = true; // Flag to use admin layout
        $currentService = $adminService; // Use AdminService for all admin pages
        $page_title = 'Admin Dashboard';
        
        // Route to specific admin modules
        switch($module) {
            case 'dashboard':
                // Include dashboard view directly
                try {
                    // Make services available globally
                    global $adminService;
                    $content = 'app/views/admin/dashboard.php';
                    include_once 'app/views/_layout/admin_master.php';
                    exit;
                } catch (Exception $e) {
                    error_log('Admin dashboard error: ' . $e->getMessage());
                    die('Admin dashboard error: ' . $e->getMessage());
                }
                break;
                
            case 'products':
                $page_title = 'Quản lý Sản phẩm';
                

                
                // Handle delete action (redirect to delete_direct for direct deletion)
                if ($action === 'delete' && isset($_GET['id'])) {
                    require_once __DIR__ . '/app/models/ProductsModel.php';
                    $productsModel = new ProductsModel();
                    $product_id = (int)$_GET['id'];
                    if ($product_id > 0) {
                        $productsModel->delete($product_id);
                    }
                    // Redirect to products list
                    header('Location: ?page=admin&module=products');
                    exit;
                }
                
                // Handle delete_direct action (no view, just process and redirect)
                if ($action === 'delete_direct' && isset($_GET['id'])) {
                    require_once __DIR__ . '/app/models/ProductsModel.php';
                    $productsModel = new ProductsModel();
                    $product_id = (int)$_GET['id'];
                    if ($product_id > 0) {
                        $productsModel->delete($product_id);
                    }
                    // Redirect to products list
                    header('Location: ?page=admin&module=products');
                    exit;
                }
                
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
                    case 'data':
                        $page_title = 'Quản lý Dữ liệu Sản phẩm';
                        $content = 'app/views/admin/products/data/index.php';
                        
                        // Handle AJAX import request
                        if (isset($_GET['subaction']) && $_GET['subaction'] === 'import' && $_SERVER['REQUEST_METHOD'] === 'POST') {
                            require_once __DIR__ . '/app/views/admin/products/data/import.php';
                            exit;
                        }
                        break;
                    case 'import':
                        $page_title = 'Quản lý Dữ liệu Sản phẩm - Import';
                        $content = 'app/views/admin/products/data/index.php';
                        // Process import via AJAX endpoint
                        if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_FILES['import_file'])) {
                            // The import is handled via AJAX in the view
                        }
                        break;
                    case 'filter_config':
                        $page_title = 'Quản lý Dữ liệu Sản phẩm - Filter Config';
                        $content = 'app/views/admin/products/filter_config.php';
                        break;
                    default:
                        $content = 'app/views/admin/products/index.php';
                        break;
                }
                break;
                
            case 'subpages':
                $page_title = 'Quản lý Trang phụ';
                require_once __DIR__ . '/app/models/SubPageModel.php';
                $subPageModel = new SubPageModel();
                
                // Xử lý AJAX upload ảnh cho editor của Trang phụ
                if ($action === 'upload_editor_image' && $_SERVER['REQUEST_METHOD'] === 'POST') {
                    while (ob_get_level() > 0) {
                        ob_end_clean();
                    }
                    header('Content-Type: application/json');
                    
                    if (isset($_FILES['upload_file']) && $_FILES['upload_file']['error'] === UPLOAD_ERR_OK) {
                        $upload_dir = 'uploads/subpages/editor/';
                        if (!is_dir($upload_dir)) {
                            @mkdir($upload_dir, 0755, true);
                        }
                        $ext = strtolower(pathinfo($_FILES['upload_file']['name'], PATHINFO_EXTENSION));
                        $allowed = ['jpg','jpeg','png','gif','webp'];
                        if (in_array($ext, $allowed)) {
                            $filename = 'editor_' . time() . '_' . rand(1000, 9999) . '.' . $ext;
                            $dest = $upload_dir . $filename;
                            if (move_uploaded_file($_FILES['upload_file']['tmp_name'], $dest)) {
                                echo json_encode(['success' => true, 'url' => $dest]);
                            } else {
                                echo json_encode(['success' => false, 'message' => 'Không thể di chuyển file đã upload']);
                            }
                        } else {
                            echo json_encode(['success' => false, 'message' => 'Định dạng ảnh không hợp lệ']);
                        }
                    } else {
                        echo json_encode(['success' => false, 'message' => 'Không có file hoặc lỗi upload']);
                    }
                    exit;
                }
                
                // Xử lý POST update trang phụ
                if ($action === 'edit' && $_SERVER['REQUEST_METHOD'] === 'POST') {
                    $key = $_POST['page_key'] ?? $_GET['key'] ?? '';
                    $errors = [];
                    
                    if (!empty($key)) {
                        if ($key === 'footer_socials') {
                            // Xử lý mạng xã hội
                            $socials = $_POST['socials'] ?? [];
                            // Chuẩn hóa và lưu JSON string
                            $updateData = [
                                'content' => json_encode($socials, JSON_UNESCAPED_UNICODE)
                            ];
                        } else {
                            // Xử lý trang tĩnh thường
                            $title_val = trim($_POST['title'] ?? '');
                            $content_val = $_POST['content'] ?? '';
                            $meta_title = trim($_POST['meta_title'] ?? '');
                            $meta_description = trim($_POST['meta_description'] ?? '');
                            
                            if (empty($title_val)) {
                                $errors[] = 'Tiêu đề không được để trống';
                            }
                            
                            // Xử lý ảnh banner nếu có
                            $image_path = $_POST['current_image'] ?? '';
                            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                                $upload_dir = 'uploads/subpages/';
                                if (!is_dir($upload_dir)) {
                                    @mkdir($upload_dir, 0755, true);
                                }
                                $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
                                $allowed = ['jpg','jpeg','png','gif','webp'];
                                if (in_array($ext, $allowed)) {
                                    $filename = 'banner_' . $key . '_' . time() . '.' . $ext;
                                    $dest = $upload_dir . $filename;
                                    if (move_uploaded_file($_FILES['image']['tmp_name'], $dest)) {
                                        $image_path = $dest;
                                    }
                                } else {
                                    $errors[] = 'Định dạng ảnh banner không hợp lệ. Chỉ hỗ trợ JPG, PNG, WEBP, GIF';
                                }
                            }
                            
                            $updateData = [
                                'title' => $title_val,
                                'subtitle' => trim($_POST['subtitle'] ?? ''),
                                'content' => $content_val,
                                'image' => !empty($image_path) ? $image_path : null,
                                'meta_title' => !empty($meta_title) ? $meta_title : null,
                                'meta_description' => !empty($meta_description) ? $meta_description : null
                            ];
                        }
                        
                        if (empty($errors)) {
                            try {
                                $updated = $subPageModel->updateByKey($key, $updateData);
                                if ($updated) {
                                    header('Location: ?page=admin&module=subpages&success=updated');
                                    exit;
                                } else {
                                    $errors[] = 'Không có thay đổi nào hoặc không thể cập nhật';
                                }
                            } catch (Exception $e) {
                                error_log('Subpage update error: ' . $e->getMessage());
                                $errors[] = 'Lỗi hệ thống: ' . $e->getMessage();
                            }
                        }
                    } else {
                        header('Location: ?page=admin&module=subpages&error=invalid_key');
                        exit;
                    }
                    
                    if (!empty($errors)) {
                        $error_msg = urlencode(implode(', ', $errors));
                        header('Location: ?page=admin&module=subpages&action=edit&key=' . $key . '&error=' . $error_msg);
                        exit;
                    }
                }
                
                // Định tuyến View Trang phụ
                switch($action) {
                    case 'edit':
                        $content = 'app/views/admin/subpages/edit.php';
                        break;
                    default:
                        $content = 'app/views/admin/subpages/index.php';
                        break;
                }
                break;
                
            case 'categories':
                $page_title = 'Quản lý Danh mục';
                
                // Handle AJAX category deletion
                if ($action === 'delete' && isset($_GET['id']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
                    // Clean any previous output
                    if (ob_get_length()) ob_clean();
                    
                    // Set JSON header
                    header('Content-Type: application/json');
                    
                    try {
                        require_once __DIR__ . '/app/services/AdminService.php';
                        $adminService = new AdminService(null, 'admin');
                        
                        // Check if force delete is requested
                        $forceDelete = $_POST['force_delete'] ?? false;
                        
                        if ($forceDelete) {
                            $result = $adminService->forceDeleteCategory((int)$_GET['id']);
                        } else {
                            $result = $adminService->deleteCategory((int)$_GET['id']);
                        }
                        
                        echo json_encode($result);
                    } catch (Exception $e) {
                        echo json_encode(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()]);
                    }
                    exit;
                }
                
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
                        // Redirect to index if delete.php doesn't exist
                        $content = 'app/views/admin/categories/index.php';
                        break;
                    default:
                        $content = 'app/views/admin/categories/index.php';
                        break;
                }
                break;
                
            case 'brands':
                $page_title = 'Quản lý Thương hiệu';

                // Handle delete action BEFORE including layout
                if ($action === 'delete' && isset($_GET['id'])) {
                    $delete_id = (int)$_GET['id'];
                    if ($delete_id > 0 && $adminService) {
                        try {
                            // Check if brand has products before deleting
                            $brandData = $adminService->getBrandDetailsData($delete_id);
                            if ($brandData['brand'] && $brandData['brand']['product_count'] > 0) {
                                header('Location: ?page=admin&module=brands&error=has_products');
                                exit;
                            }
                            $adminService->deleteBrand($delete_id);
                        } catch (Exception $e) {
                            error_log('Delete brand error: ' . $e->getMessage());
                        }
                    }
                    // Redirect after delete
                    header('Location: ?page=admin&module=brands&success=deleted');
                    exit;
                }

                switch($action) {
                    case 'add':
                        $content = 'app/views/admin/brands/add.php';
                        break;
                    case 'edit':
                        $content = 'app/views/admin/brands/edit.php';
                        break;
                    case 'view':
                        $content = 'app/views/admin/brands/view.php';
                        break;
                    default:
                        $content = 'app/views/admin/brands/index.php';
                        break;
                }
                break;

            case 'news':
                $page_title = 'Quản lý Tin tức';
                
                // Handle delete action BEFORE including layout
                if ($action === 'delete' && isset($_GET['id'])) {
                    $delete_id = (int)$_GET['id'];
                    if ($delete_id > 0 && $adminService) {
                        try {
                            $adminService->deleteNews($delete_id);
                        } catch (Exception $e) {
                            error_log('Delete news error: ' . $e->getMessage());
                        }
                    }
                    header('Location: ?page=admin&module=news');
                    exit;
                }
                
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
                    case 'add_category_ajax':
                        // Handle AJAX request to add new category
                        header('Content-Type: application/json');
                        try {
                            if ($_SERVER['REQUEST_METHOD'] === 'POST' && $adminService) {
                                $name = trim($_POST['name'] ?? '');
                                $slug = trim($_POST['slug'] ?? '');
                                $type = trim($_POST['type'] ?? 'news');
                                
                                if (empty($name)) {
                                    echo json_encode(['success' => false, 'message' => 'Tên danh mục không được để trống']);
                                    exit;
                                }
                                
                                // Add category via AdminService
                                $categoryData = [
                                    'name' => $name,
                                    'slug' => $slug ?: strtolower(preg_replace('/[^a-z0-9\s-]/', '', $name)),
                                    'type' => $type,
                                    'status' => 'active',
                                    'description' => 'Danh mục tin tức: ' . $name
                                ];
                                
                                $categoryId = $adminService->createCategory($categoryData);
                                
                                if ($categoryId) {
                                    echo json_encode(['success' => true, 'category_id' => $categoryId, 'message' => 'Thêm danh mục thành công']);
                                } else {
                                    echo json_encode(['success' => false, 'message' => 'Không thể thêm danh mục']);
                                }
                            } else {
                                echo json_encode(['success' => false, 'message' => 'Invalid request']);
                            }
                        } catch (Exception $e) {
                            error_log('Add category AJAX error: ' . $e->getMessage());
                            echo json_encode(['success' => false, 'message' => 'Lỗi hệ thống: ' . $e->getMessage()]);
                        }
                        exit;
                    case 'delete_category_ajax':
                        // Handle AJAX request to delete category
                        header('Content-Type: application/json');
                        try {
                            if ($_SERVER['REQUEST_METHOD'] === 'POST' && $adminService) {
                                $categoryId = (int)($_POST['category_id'] ?? 0);
                                
                                if ($categoryId <= 0) {
                                    echo json_encode(['success' => false, 'message' => 'ID danh mục không hợp lệ']);
                                    exit;
                                }
                                
                                // Check if category has news
                                $newsCount = $adminService->getNewsCountByCategory($categoryId);
                                if ($newsCount > 0) {
                                    echo json_encode(['success' => false, 'message' => "Không thể xóa danh mục này vì có {$newsCount} tin tức đang sử dụng"]);
                                    exit;
                                }
                                
                                // Delete category
                                $result = $adminService->deleteCategory($categoryId);
                                
                                if ($result['success']) {
                                    echo json_encode(['success' => true, 'message' => 'Xóa danh mục thành công']);
                                } else {
                                    echo json_encode(['success' => false, 'message' => $result['message'] ?? 'Không thể xóa danh mục']);
                                }
                            } else {
                                echo json_encode(['success' => false, 'message' => 'Invalid request']);
                            }
                        } catch (Exception $e) {
                            error_log('Delete category AJAX error: ' . $e->getMessage());
                            echo json_encode(['success' => false, 'message' => 'Lỗi hệ thống: ' . $e->getMessage()]);
                        }
                        exit;
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
                
                // Handle delete action BEFORE including layout
                if ($action === 'delete' && isset($_GET['id'])) {
                    $delete_id = (int)$_GET['id'];
                    if ($delete_id > 0 && $adminService) {
                        try {
                            $adminService->deleteOrder($delete_id);
                        } catch (Exception $e) {
                            error_log('Delete order error: ' . $e->getMessage());
                        }
                    }
                    // Redirect after delete
                    header('Location: ?page=admin&module=orders');
                    exit;
                }
                
                switch($action) {
                    case 'edit':
                        $content = 'app/views/admin/orders/edit.php';
                        break;
                    case 'view':
                        $content = 'app/views/admin/orders/view.php';
                        break;
                    case 'delete':
                        // This case is now handled above
                        $content = 'app/views/admin/orders/index.php';
                        break;
                    default:
                        $content = 'app/views/admin/orders/index.php';
                        break;
                }
                break;
                
            case 'users':
                $page_title = 'Quản lý Người dùng';
                
                // Handle delete action BEFORE including layout
                if ($action === 'delete' && isset($_GET['id'])) {
                    $delete_id = (int)$_GET['id'];
                    if ($delete_id > 0) {
                        try {
                            require_once 'app/models/UsersModel.php';
                            $usersModel = new UsersModel();
                            $usersModel->delete($delete_id);
                        } catch (Exception $e) {
                            error_log('Delete user error: ' . $e->getMessage());
                        }
                    }
                    // Redirect after delete
                    header('Location: ?page=admin&module=users&deleted=1');
                    exit;
                }
                
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
                        // This case is now handled above
                        $content = 'app/views/admin/users/index.php';
                        break;
                    default:
                        $content = 'app/views/admin/users/index.php';
                        break;
                }
                break;
                
            case 'affiliates':
                $page_title = 'Quản lý Đại lý';
                
                // Handle POST update request BEFORE including layout (PRG pattern)
                if ($action === 'edit' && $_SERVER['REQUEST_METHOD'] === 'POST') {
                    $affiliate_id = (int)($_POST['affiliate_id'] ?? $_GET['id'] ?? 0);
                    $errors = [];
                    
                    if ($affiliate_id > 0) {
                        // Validate and process
                        $commission_rate = (float)($_POST['commission_rate'] ?? 0);
                        $referral_code = trim($_POST['referral_code'] ?? '');
                        $status = $_POST['status'] ?? 'active';
                        
                        if ($commission_rate <= 0 || $commission_rate > 50) {
                            $errors[] = 'Tỷ lệ hoa hồng phải từ 0.1% đến 50%';
                        }
                        
                        if (empty($referral_code)) {
                            $errors[] = 'Mã giới thiệu không được để trống';
                        } elseif (strlen($referral_code) < 3) {
                            $errors[] = 'Mã giới thiệu phải có ít nhất 3 ký tự';
                        } elseif (!preg_match('/^[A-Z0-9]+$/', $referral_code)) {
                            $errors[] = 'Mã giới thiệu chỉ được chứa chữ cái in hoa và số';
                        }
                        
                        if (empty($errors)) {
                            try {
                                require_once __DIR__ . '/app/services/AdminService.php';
                                $adminService = new AdminService(null, 'admin');
                                
                                // Check referral code exists
                                if ($adminService->checkReferralCodeExists($referral_code, $affiliate_id)) {
                                    $errors[] = 'Mã giới thiệu đã tồn tại';
                                } else {
                                    $affiliateData = [
                                        'commission_rate' => $commission_rate,
                                        'referral_code' => strtoupper($referral_code),
                                        'status' => $status
                                    ];
                                    $updated = $adminService->updateAffiliate($affiliate_id, $affiliateData);
                                    if ($updated) {
                                        // Redirect with success - PRG pattern
                                        header('Location: ?page=admin&module=affiliates&action=edit&id=' . $affiliate_id . '&success=updated');
                                        exit;
                                    } else {
                                        $errors[] = 'Không thể cập nhật đại lý';
                                    }
                                }
                            } catch (Exception $e) {
                                error_log('Affiliate update error: ' . $e->getMessage());
                                $errors[] = 'Lỗi hệ thống: ' . $e->getMessage();
                            }
                        }
                        
                        // If errors, redirect back with error message
                        if (!empty($errors)) {
                            $error_msg = urlencode(implode(', ', $errors));
                            header('Location: ?page=admin&module=affiliates&action=edit&id=' . $affiliate_id . '&error=' . $error_msg);
                            exit;
                        }
                    } else {
                        header('Location: ?page=admin&module=affiliates&error=invalid_id');
                        exit;
                    }
                }

                // Handle POST update for affiliate content
                if ($action === 'content_edit' && $_SERVER['REQUEST_METHOD'] === 'POST') {
                    $key = $_POST['page_key'] ?? $_GET['key'] ?? '';
                    $errors = [];
                    
                    if (!empty($key)) {
                        $title = trim($_POST['title'] ?? '');
                        $content_val = $_POST['content'] ?? '';
                        $meta_title = trim($_POST['meta_title'] ?? '');
                        $meta_description = trim($_POST['meta_description'] ?? '');
                        
                        if (empty($title)) {
                            $errors[] = 'Tiêu đề không được để trống';
                        }
                        
                        // Handle page banner image upload
                        $image_path = $_POST['current_image'] ?? '';
                        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                            $upload_dir = 'uploads/agent/';
                            if (!is_dir($upload_dir)) {
                                @mkdir($upload_dir, 0755, true);
                            }
                            $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
                            $allowed = ['jpg','jpeg','png','gif','webp'];
                            if (in_array($ext, $allowed)) {
                                $filename = 'banner_' . $key . '_' . time() . '.' . $ext;
                                $dest = $upload_dir . $filename;
                                if (move_uploaded_file($_FILES['image']['tmp_name'], $dest)) {
                                    $image_path = $dest;
                                }
                            } else {
                                $errors[] = 'Định dạng ảnh banner không hợp lệ. Chỉ hỗ trợ JPG, PNG, WEBP, GIF';
                            }
                        }
                        
                        if (empty($errors)) {
                            try {
                                require_once __DIR__ . '/app/models/AgentContentModel.php';
                                $agentContentModel = new AgentContentModel();
                                
                                $updateData = [
                                    'title' => $title,
                                    'subtitle' => trim($_POST['subtitle'] ?? ''),
                                    'content' => $content_val,
                                    'image' => !empty($image_path) ? $image_path : null,
                                    'meta_title' => !empty($meta_title) ? $meta_title : null,
                                    'meta_description' => !empty($meta_description) ? $meta_description : null
                                ];
                                
                                $updated = $agentContentModel->updateByKey($key, $updateData);
                                if ($updated) {
                                    header('Location: ?page=admin&module=affiliates&action=content&success=updated');
                                    exit;
                                } else {
                                    $errors[] = 'Không có thay đổi nào hoặc không thể cập nhật nội dung';
                                }
                            } catch (Exception $e) {
                                error_log('Agent content update error: ' . $e->getMessage());
                                $errors[] = 'Lỗi hệ thống: ' . $e->getMessage();
                            }
                        }
                    } else {
                        header('Location: ?page=admin&module=affiliates&action=content&error=invalid_key');
                        exit;
                    }
                    
                    if (!empty($errors)) {
                        $error_msg = urlencode(implode(', ', $errors));
                        header('Location: ?page=admin&module=affiliates&action=content_edit&key=' . $key . '&error=' . $error_msg);
                        exit;
                    }
                }

                // Handle AJAX image upload inside custom text editor
                if ($action === 'upload_editor_image' && $_SERVER['REQUEST_METHOD'] === 'POST') {
                    if (ob_get_level() > 0) {
                        ob_clean();
                    }
                    header('Content-Type: application/json');
                    
                    if (isset($_FILES['upload_file']) && $_FILES['upload_file']['error'] === UPLOAD_ERR_OK) {
                        $upload_dir = 'uploads/agent/editor/';
                        if (!is_dir($upload_dir)) {
                            @mkdir($upload_dir, 0755, true);
                        }
                        $ext = strtolower(pathinfo($_FILES['upload_file']['name'], PATHINFO_EXTENSION));
                        $allowed = ['jpg','jpeg','png','gif','webp'];
                        if (in_array($ext, $allowed)) {
                            $filename = 'editor_' . time() . '_' . rand(1000, 9999) . '.' . $ext;
                            $dest = $upload_dir . $filename;
                            if (move_uploaded_file($_FILES['upload_file']['tmp_name'], $dest)) {
                                echo json_encode(['success' => true, 'url' => $dest]);
                                exit;
                            }
                        }
                    }
                    echo json_encode(['success' => false, 'message' => 'Lỗi tải ảnh lên máy chủ']);
                    exit;
                }
                
                // Handle delete action BEFORE including layout
                if ($action === 'delete' && isset($_GET['id'])) {
                    $delete_id = (int)$_GET['id'];
                    if ($delete_id > 0) {
                        try {
                            require_once __DIR__ . '/app/services/AdminService.php';
                            $adminService = new AdminService(null, 'admin');
                            $adminService->deleteAffiliate($delete_id);
                        } catch (Exception $e) {
                            error_log('Delete affiliate error: ' . $e->getMessage());
                        }
                    }
                    // Redirect after delete
                    header('Location: ?page=admin&module=affiliates&deleted=1');
                    exit;
                }
                
                switch($action) {
                    case 'content':
                        $content = 'app/views/admin/affiliates/content_list.php';
                        break;
                    case 'content_edit':
                        $content = 'app/views/admin/affiliates/content_edit.php';
                        break;
                    case 'add':
                        $content = 'app/views/admin/affiliates/add.php';
                        break;
                    case 'edit':
                        $content = 'app/views/admin/affiliates/edit.php';
                        break;
                    case 'view':
                        $content = 'app/views/admin/affiliates/view.php';
                        break;
                    case 'requests':
                        $content = 'app/views/admin/affiliates/requests.php';
                        break;
                    case 'request_detail':
                        $content = 'app/views/admin/affiliates/request_detail.php';
                        break;
                    case 'approve_request':
                        $approve_id = (int)($_GET['id'] ?? 0);
                        if ($approve_id > 0) {
                            try {
                                require_once __DIR__ . '/app/models/AffiliateModel.php';
                                require_once __DIR__ . '/app/models/UsersModel.php';
                                $affiliateModel = new AffiliateModel();
                                $usersModel = new UsersModel();
                                
                                // Get affiliate info using direct SQL
                                $result = $affiliateModel->query("SELECT user_id, status FROM affiliates WHERE id = " . (int)$approve_id);
                                if (empty($result)) {
                                    header('Location: ?page=admin&module=affiliates&action=requests&error=not_found');
                                    exit;
                                }
                                
                                $affiliate = $result[0];
                                if ($affiliate['status'] !== 'pending') {
                                    header('Location: ?page=admin&module=affiliates&action=requests&error=already_processed');
                                    exit;
                                }
                                
                                $userId = $affiliate['user_id'];
                                
                                // Get user email for notification
                                $userResult = $usersModel->query("SELECT email, name FROM users WHERE id = " . (int)$userId);
                                $userEmail = '';
                                $userName = '';
                                if (!empty($userResult)) {
                                    $userEmail = $userResult[0]['email'] ?? '';
                                    $userName = $userResult[0]['name'] ?? 'Quý khách';
                                }
                                
                                // Update affiliate status to 'active' (approved)
                                $affiliateModel->query("UPDATE affiliates SET status = 'active' WHERE id = " . (int)$approve_id);
                                
                                // Update user role to 'affiliate' and status to 'approved'
                                $usersModel->query("UPDATE users SET role = 'affiliate', agent_request_status = 'approved' WHERE id = " . (int)$userId);
                                
                                // Send approval email (same pattern as rejection)
                                if (!empty($userEmail)) {
                                    try {
                                        require_once __DIR__ . '/app/services/EmailNotificationService.php';
                                        $emailService = new EmailNotificationService();
                                        $emailSent = $emailService->sendApprovalNotification($userEmail, $userName);
                                        error_log('Approval email sent to ' . $userEmail . ': ' . ($emailSent ? 'SUCCESS' : 'FAILED'));
                                    } catch (Exception $emailError) {
                                        error_log('Approval email error: ' . $emailError->getMessage() . ' in ' . $emailError->getFile() . ':' . $emailError->getLine());
                                    }
                                }
                                
                                header('Location: ?page=admin&module=affiliates&action=requests&success=approved');
                            } catch (Exception $e) {
                                $error_msg = urlencode('Error: ' . $e->getMessage());
                                header('Location: ?page=admin&module=affiliates&action=requests&error=' . $error_msg);
                            }
                        } else {
                            header('Location: ?page=admin&module=affiliates&action=requests&error=invalid_id');
                        }
                        exit;
                    case 'reject_request':
                        $reject_id = (int)($_GET['id'] ?? 0);
                        if ($reject_id > 0) {
                            try {
                                require_once __DIR__ . '/app/models/AffiliateModel.php';
                                require_once __DIR__ . '/app/models/UsersModel.php';
                                $affiliateModel = new AffiliateModel();
                                $usersModel = new UsersModel();
                                
                                // Get affiliate info using direct SQL
                                $result = $affiliateModel->query("SELECT user_id, status FROM affiliates WHERE id = " . (int)$reject_id);
                                if (empty($result)) {
                                    header('Location: ?page=admin&module=affiliates&action=requests&error=not_found');
                                    exit;
                                }
                                
                                $affiliate = $result[0];
                                if ($affiliate['status'] !== 'pending') {
                                    header('Location: ?page=admin&module=affiliates&action=requests&error=already_processed');
                                    exit;
                                }
                                
                                $userId = $affiliate['user_id'];
                                
                                // Get user email for notification
                                $userResult = $usersModel->query("SELECT email, name FROM users WHERE id = " . (int)$userId);
                                $userEmail = '';
                                $userName = '';
                                if (!empty($userResult)) {
                                    $userEmail = $userResult[0]['email'] ?? '';
                                    $userName = $userResult[0]['name'] ?? 'Quý khách';
                                }
                                error_log('Reject request: userId=' . $userId . ', userEmail=' . $userEmail . ', userName=' . $userName);
                                
                                // Update affiliate status to 'inactive' (rejected) - keep for history
                                $affiliateModel->query("UPDATE affiliates SET status = 'inactive' WHERE id = " . (int)$reject_id);
                                
                                // Reset user's agent_request_status to 'rejected' (valid ENUM value)
                                // User can still re-apply because AgentRegistrationService checks for 'pending' status only
                                $usersModel->query("UPDATE users SET agent_request_status = 'rejected' WHERE id = " . (int)$userId);
                                
                                // Send rejection email (same pattern as AgentRegistrationService)
                                if (!empty($userEmail)) {
                                    try {
                                        require_once __DIR__ . '/app/services/EmailNotificationService.php';
                                        $emailService = new EmailNotificationService();
                                        $emailSent = $emailService->sendRejectionNotification($userEmail, $userName);
                                        error_log('Rejection email sent to ' . $userEmail . ': ' . ($emailSent ? 'SUCCESS' : 'FAILED'));
                                    } catch (Exception $emailError) {
                                        // Log but don't fail the request
                                        error_log('Rejection email error: ' . $emailError->getMessage() . ' in ' . $emailError->getFile() . ':' . $emailError->getLine());
                                    }
                                }
                                
                                header('Location: ?page=admin&module=affiliates&action=requests&success=rejected');
                            } catch (Exception $e) {
                                $error_msg = urlencode('Error: ' . $e->getMessage());
                                header('Location: ?page=admin&module=affiliates&action=requests&error=' . $error_msg);
                            }
                        } else {
                            header('Location: ?page=admin&module=affiliates&action=requests&error=invalid_id');
                        }
                        exit;
                    case 'delete_request':
                        $delete_request_id = (int)($_GET['id'] ?? 0);
                        if ($delete_request_id > 0) {
                            require_once __DIR__ . '/app/services/AdminService.php';
                            $adminService = new AdminService(null, 'admin');
                            $result = $adminService->deleteAffiliateRequest($delete_request_id);
                        }
                        header('Location: ?page=admin&module=affiliates&action=requests&success=deleted');
                        exit;
                    case 'withdrawals':
                        $content = 'app/views/admin/affiliates/withdrawals.php';
                        break;
                    case 'withdrawal_detail':
                        $content = 'app/views/admin/affiliates/withdrawal_detail.php';
                        break;
                    case 'approve_withdrawal':
                        $withdrawal_id = (int)($_GET['id'] ?? 0);
                        if ($withdrawal_id > 0) {
                            try {
                                require_once __DIR__ . '/app/services/WalletService.php';
                                require_once __DIR__ . '/app/services/EmailNotificationService.php';
                                require_once __DIR__ . '/app/models/WithdrawalRequestModel.php';
                                require_once __DIR__ . '/app/models/AffiliateModel.php';
                                require_once __DIR__ . '/app/models/UsersModel.php';
                                
                                $withdrawalModel = new WithdrawalRequestModel();
                                $walletService = new WalletService();
                                $emailService = new EmailNotificationService();
                                $affiliateModel = new AffiliateModel();
                                $usersModel = new UsersModel();
                                
                                // Get withdrawal details
                                $withdrawal = $withdrawalModel->getWithDetails($withdrawal_id);
                                if (!$withdrawal) {
                                    header('Location: ?page=admin&module=affiliates&action=withdrawals&error=not_found');
                                    exit;
                                }
                                
                                if ($withdrawal['status'] !== 'pending') {
                                    header('Location: ?page=admin&module=affiliates&action=withdrawals&error=already_processed');
                                    exit;
                                }
                                
                                // Update status to processing
                                $withdrawalModel->updateStatus($withdrawal_id, 'processing', $_SESSION['user_id'] ?? null);
                                
                                // Initiate PayOS payout
                                require_once __DIR__ . '/app/services/PayOSService.php';
                                $payosService = new PayOSService();
                                
                                // Get bank BIN code from bank name
                                $bankBin = $payosService->getBankBinByName($withdrawal['bank_name'] ?? '');
                                
                                $payoutResult = $payosService->createPayout(
                                    $withdrawal['withdraw_code'],
                                    $withdrawal['net_amount'],
                                    [
                                        'bank_name' => $withdrawal['bank_name'] ?? '',
                                        'bank_code' => $bankBin,
                                        'account_number' => $withdrawal['bank_account'] ?? '',
                                        'account_holder' => $withdrawal['account_holder'] ?? ''
                                    ]
                                );
                                
                                if (!$payoutResult['success']) {
                                    // Payout failed, rollback status to pending
                                    $withdrawalModel->updateStatus($withdrawal_id, 'pending', null, 'PayOS payout failed: ' . $payoutResult['message']);
                                    throw new Exception('PayOS payout failed: ' . $payoutResult['message']);
                                }
                                
                                // Save PayOS payout ID to withdrawal record
                                $withdrawalModel->update($withdrawal_id, [
                                    'payos_payout_id' => $payoutResult['payout_id'] ?? null,
                                    'payos_status' => $payoutResult['status'] ?? 'PROCESSING',
                                    'payos_response' => json_encode($payoutResult)
                                ]);
                                
                                // Check if we should auto-complete or wait for webhook
                                $payosConfig = require __DIR__ . '/config.php';
                                $autoComplete = $payosConfig['payos']['auto_complete_on_success'] ?? true;
                                
                                if ($autoComplete && in_array($payoutResult['status'], ['COMPLETED', 'SUCCEEDED'], true)) {
                                    // Complete withdrawal immediately if already completed
                                    $walletService->completeWithdrawal($withdrawal_id);
                                }
                                
                                // Send email notification
                                $affiliateEmail = $withdrawal['affiliate_email'] ?? '';
                                $affiliateName = $withdrawal['affiliate_name'] ?? 'Quy khach';
                                $amount = $withdrawal['net_amount'] ?? 0;
                                
                                if (!empty($affiliateEmail)) {
                                    try {
                                        $emailService->sendWithdrawalApprovedNotification($affiliateEmail, $affiliateName, $amount, $withdrawal['withdraw_code']);
                                    } catch (Exception $emailError) {
                                        error_log('Withdrawal approval email error: ' . $emailError->getMessage());
                                    }
                                }
                                
                                header('Location: ?page=admin&module=affiliates&action=withdrawals&success=approved');
                            } catch (Exception $e) {
                                error_log('Approve withdrawal error: ' . $e->getMessage());
                                header('Location: ?page=admin&module=affiliates&action=withdrawals&error=' . urlencode($e->getMessage()));
                            }
                        } else {
                            header('Location: ?page=admin&module=affiliates&action=withdrawals&error=invalid_id');
                        }
                        exit;
                    case 'reject_withdrawal':
                        $reject_withdrawal_id = (int)($_GET['id'] ?? 0);
                        $admin_note = $_GET['note'] ?? '';
                        if ($reject_withdrawal_id > 0) {
                            try {
                                require_once __DIR__ . '/app/services/WalletService.php';
                                require_once __DIR__ . '/app/services/EmailNotificationService.php';
                                require_once __DIR__ . '/app/models/WithdrawalRequestModel.php';
                                
                                $withdrawalModel = new WithdrawalRequestModel();
                                $walletService = new WalletService();
                                $emailService = new EmailNotificationService();
                                
                                // Get withdrawal details
                                $withdrawal = $withdrawalModel->getWithDetails($reject_withdrawal_id);
                                if (!$withdrawal) {
                                    header('Location: ?page=admin&module=affiliates&action=withdrawals&error=not_found');
                                    exit;
                                }
                                
                                if ($withdrawal['status'] !== 'pending') {
                                    header('Location: ?page=admin&module=affiliates&action=withdrawals&error=already_processed');
                                    exit;
                                }
                                
                                // Cancel withdrawal - return money to balance
                                $walletService->cancelWithdrawal($reject_withdrawal_id, $admin_note);
                                
                                // Send email notification
                                $affiliateEmail = $withdrawal['affiliate_email'] ?? '';
                                $affiliateName = $withdrawal['affiliate_name'] ?? 'Quý khách';
                                $amount = $withdrawal['net_amount'] ?? 0;
                                
                                if (!empty($affiliateEmail)) {
                                    try {
                                        $emailService->sendWithdrawalRejectedNotification($affiliateEmail, $affiliateName, $amount, $admin_note);
                                    } catch (Exception $emailError) {
                                        error_log('Withdrawal rejection email error: ' . $emailError->getMessage());
                                    }
                                }
                                
                                header('Location: ?page=admin&module=affiliates&action=withdrawals&success=rejected');
                            } catch (Exception $e) {
                                error_log('Reject withdrawal error: ' . $e->getMessage());
                                header('Location: ?page=admin&module=affiliates&action=withdrawals&error=' . urlencode($e->getMessage()));
                            } finally {
                                exit;
                            }
                        } else {
                            header('Location: ?page=admin&module=affiliates&action=withdrawals&error=invalid_id');
                            exit;
                        }
                        break;
                    case 'approve_to_processing':
                        // Approve withdrawal and set status to processing (for manual bank transfer)
                        $processing_id = (int)($_GET['id'] ?? 0);
                        if ($processing_id > 0) {
                            try {
                                require_once __DIR__ . '/app/models/WithdrawalRequestModel.php';
                                $withdrawalModel = new WithdrawalRequestModel();
                                
                                $withdrawal = $withdrawalModel->find($processing_id);
                                if (!$withdrawal) {
                                    header('Location: ?page=admin&module=affiliates&action=withdrawals&error=not_found');
                                    exit;
                                }
                                
                                if ($withdrawal['status'] !== 'pending') {
                                    header('Location: ?page=admin&module=affiliates&action=withdrawals&error=already_processed');
                                    exit;
                                }
                                
                                // Update to processing status
                                $withdrawalModel->updateStatus($processing_id, 'processing', $_SESSION['user_id'] ?? null);
                                
                                header('Location: ?page=admin&module=affiliates&action=withdrawals&status=processing&success=approved_to_processing');
                            } catch (Exception $e) {
                                error_log('Approve to processing error: ' . $e->getMessage());
                                header('Location: ?page=admin&module=affiliates&action=withdrawals&error=' . urlencode($e->getMessage()));
                            }
                        } else {
                            header('Location: ?page=admin&module=affiliates&action=withdrawals&error=invalid_id');
                        }
                        exit;
                    case 'export_withdrawals':
                        // Export selected withdrawals to CSV for bulk bank transfer
                        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                            try {
                                require_once __DIR__ . '/app/services/WithdrawalExportService.php';
                                require_once __DIR__ . '/app/models/WithdrawalRequestModel.php';
                                
                                $exportService = new WithdrawalExportService();
                                $withdrawalModel = new WithdrawalRequestModel();
                                
                                $selectedIds = $_POST['selected_ids'] ?? '';
                                $bankFormat = $_POST['bank_format'] ?? 'mbbank';
                                
                                if (empty($selectedIds)) {
                                    header('Location: ?page=admin&module=affiliates&action=withdrawals&error=no_selection');
                                    exit;
                                }
                                
                                $ids = array_map('intval', explode(',', $selectedIds));
                                
                                foreach ($ids as $id) {
                                    $withdrawal = $withdrawalModel->find($id);
                                    if ($withdrawal && $withdrawal['status'] === 'pending') {
                                        $withdrawalModel->updateStatus($id, 'processing', $_SESSION['user_id'] ?? null);
                                    }
                                }
                                
                                switch ($bankFormat) {
                                    case 'tpbank':
                                        $result = $exportService->exportToTPBankCSV($ids);
                                        break;
                                    case 'vietcombank':
                                        $result = $exportService->exportToVietcombankCSV($ids);
                                        break;
                                    case 'mbbank':
                                    default:
                                        $result = $exportService->exportToBankCSV($ids);
                                        break;
                                }
                                
                                if (!$result['success']) {
                                    header('Location: ?page=admin&module=affiliates&action=withdrawals&status=processing&error=' . urlencode($result['error']));
                                    exit;
                                }
                                
                                header('Content-Type: text/csv; charset=utf-8');
                                header('Content-Disposition: attachment; filename="' . $result['filename'] . '"');
                                header('Pragma: no-cache');
                                header('Expires: 0');
                                
                                echo "\xEF\xBB\xBF";
                                echo $result['content'];
                                exit;
                                
                            } catch (Exception $e) {
                                error_log('Export withdrawals error: ' . $e->getMessage());
                                header('Location: ?page=admin&module=affiliates&action=withdrawals&error=' . urlencode($e->getMessage()));
                                exit;
                            }
                        }
                        header('Location: ?page=admin&module=affiliates&action=withdrawals');
                        exit;
                    case 'view':
                        $content = 'app/views/admin/affiliates/view.php';
                        break;
                    default:
                        $content = 'app/views/admin/affiliates/index.php';
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
                
            case 'contact':
                $page_title = 'Quản lý Liên hệ';
                switch($action) {
                    case 'view':
                        $content = 'app/views/admin/contact/view.php';
                        break;
                    case 'edit':
                        // Handle POST request for updating contact status
                        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                            $contact_id = (int)($_POST['contact_id'] ?? $_GET['id'] ?? 0);
                            $status = $_POST['status'] ?? 'new';
                            $priority = $_POST['priority'] ?? 'normal';
                            
                            if ($contact_id > 0) {
                                try {
                                    require_once __DIR__ . '/app/models/ContactsModel.php';
                                    $contactsModel = new ContactsModel();
                                    
                                    $updateData = [
                                        'status' => $status,
                                        'priority' => $priority,
                                        'updated_at' => date('Y-m-d H:i:s')
                                    ];
                                    
                                    $updated = $contactsModel->update($contact_id, $updateData);
                                    if ($updated) {
                                        header('Location: ?page=admin&module=contact&success=updated');
                                        exit;
                                    }
                                } catch (Exception $e) {
                                    error_log('Update contact error: ' . $e->getMessage());
                                }
                            }
                            header('Location: ?page=admin&module=contact&action=edit&id=' . $contact_id . '&error=update_failed');
                            exit;
                        } else {
                            // Show edit form
                            $content = 'app/views/admin/contact/edit.php';
                        }
                        break;
                    case 'delete':
                        // Handle delete action
                        if (isset($_GET['id'])) {
                            $delete_id = (int)$_GET['id'];
                            if ($delete_id > 0) {
                                try {
                                    require_once __DIR__ . '/app/models/ContactsModel.php';
                                    $contactsModel = new ContactsModel();
                                    $contactsModel->delete($delete_id);
                                } catch (Exception $e) {
                                    error_log('Delete contact error: ' . $e->getMessage());
                                }
                            }
                            header('Location: ?page=admin&module=contact&deleted=1');
                            exit;
                        }
                        break;
                    default:
                        $content = 'app/views/admin/contact/index.php';
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
                
            case 'agents':
                $page_title = 'Quản lý Đại lý';
                // Handle agent management through AdminController
                require_once 'app/controllers/AdminController.php';
                $adminController = new AdminController();
                
                switch($action) {
                    case 'manage':
                    default:
                        $adminController->manageAgentRequests();
                        exit;
                        break;
                    case 'approve':
                        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                            $adminController->approveAgentRequest();
                        } else {
                            $adminController->manageAgentRequests();
                        }
                        exit;
                        break;
                    case 'update_status':
                        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                            $adminController->updateAgentStatus();
                        } else {
                            $adminController->manageAgentRequests();
                        }
                        exit;
                        break;
                }
                break;
                
            case 'site-settings':
                // Use SiteSettingsController
                require_once __DIR__ . '/app/controllers/SiteSettingsController.php';
                $siteSettingsController = new SiteSettingsController();
                
                $action = $_GET['action'] ?? 'index';
                
                switch($action) {
                    case 'update':
                        $siteSettingsController->update();
                        exit;
                    case 'getLogo':
                        $siteSettingsController->getLogo();
                        exit;
                    case 'index':
                    default:
                        $siteSettingsController->index();
                        exit;
                }
                break;
                
            case 'homepage':
                // Use HomepageController for managing homepage sections
                require_once __DIR__ . '/app/controllers/HomepageController.php';
                $homepageController = new HomepageController();
                
                $action = $_GET['action'] ?? 'index';
                $id = $_GET['id'] ?? null;
                
                switch($action) {
                    // Hero Section actions
                    case 'edit-hero':
                        if ($id) {
                            $homepageController->editHero($id);
                        } else {
                            header('Location: ?page=admin&module=homepage');
                        }
                        exit;
                    case 'update-hero':
                        if ($id) {
                            $homepageController->updateHero($id);
                        } else {
                            header('Location: ?page=admin&module=homepage');
                        }
                        exit;
                    case 'toggle-hero-status':
                        $homepageController->toggleHeroStatus();
                        exit;
                    
                    // Featured Products Section actions
                    case 'edit-featured-products':
                        $homepageController->editFeaturedProducts();
                        exit;
                    case 'update-featured-products':
                        $homepageController->updateFeaturedProducts();
                        exit;
                    case 'toggle-featured-products-status':
                        $homepageController->toggleFeaturedProductsStatus();
                        exit;
                    
                    // Latest Products Section actions
                    case 'edit-latest-products':
                        $homepageController->editLatestProducts();
                        exit;
                    case 'update-latest-products':
                        $homepageController->updateLatestProducts();
                        exit;
                    case 'toggle-latest-products-status':
                        $homepageController->toggleLatestProductsStatus();
                        exit;
                    
                    // Budget Products Section actions
                    case 'edit-budget-products':
                        $homepageController->editBudgetProducts();
                        exit;
                    case 'update-budget-products':
                        $homepageController->updateBudgetProducts();
                        exit;
                    case 'toggle-budget-products-status':
                        $homepageController->toggleBudgetProductsStatus();
                        exit;
                    
                    // Sale Products Section actions
                    case 'edit-sale-products':
                        $homepageController->editSaleProducts();
                        exit;
                    case 'update-sale-products':
                        $homepageController->updateSaleProducts();
                        exit;
                    case 'toggle-sale-products-status':
                        $homepageController->toggleSaleProductsStatus();
                        exit;
                    
                    // Featured Categories Section actions
                    case 'edit-featured-categories':
                        $homepageController->editFeaturedCategories();
                        exit;
                    case 'update-featured-categories':
                        $homepageController->updateFeaturedCategories();
                        exit;
                    case 'toggle-featured-categories-status':
                        $homepageController->toggleFeaturedCategoriesStatus();
                        exit;
                    
                    // Featured Brands actions
                    case 'edit_featured_brands':
                        $homepageController->editFeaturedBrands();
                        exit;
                    case 'update_featured_brands':
                        $homepageController->updateFeaturedBrands();
                        exit;
                    case 'toggle-featured-brands-status':
                        $homepageController->toggleFeaturedBrandsStatus();
                        exit;
                    
                    // Latest News actions
                    case 'edit_latest_news':
                        $homepageController->editLatestNews();
                        exit;
                    case 'update_latest_news':
                        $homepageController->updateLatestNews();
                        exit;
                    case 'toggle-latest-news-status':
                        $homepageController->toggleLatestNewsStatus();
                        exit;
                    
                    // Why Choose Us actions
                    case 'edit_why_choose':
                        $homepageController->editWhyChoose();
                        exit;
                    case 'update_why_choose':
                        $homepageController->updateWhyChoose();
                        exit;
                    case 'toggle-why-choose-status':
                        $homepageController->toggleWhyChooseStatus();
                        exit;
                    
                    // Custom Category Sections actions
                    case 'edit-custom-category':
                        $homepageController->editCustomCategory();
                        exit;
                    case 'save-custom-category':
                        $homepageController->saveCustomCategory();
                        exit;
                    case 'delete-custom-category':
                        $homepageController->deleteCustomCategory();
                        exit;
                    case 'toggle-custom-category-status':
                        $homepageController->toggleCustomCategoryStatus();
                        exit;
                    
                    // CTA Section actions
                    case 'edit-cta':
                        $homepageController->editCta();
                        exit;
                    case 'update-cta':
                        $homepageController->updateCta();
                        exit;
                    case 'toggle-cta-status':
                        $homepageController->toggleCtaStatus();
                        exit;
                    
                    // Top Banner actions
                    case 'edit-top-banner':
                        $homepageController->editTopBanner();
                        exit;
                    case 'update-top-banner':
                        $homepageController->updateTopBanner();
                        exit;
                    case 'toggle-top-banner-status':
                        $homepageController->toggleTopBannerStatus();
                        exit;
                    
                    // Hero Button actions (delegated)
                    case 'createButton':
                        $homepageController->createButton();
                        exit;
                    case 'updateButton':
                        $id = $_GET['id'] ?? $_POST['id'] ?? 0;
                        if ($id > 0) {
                            $homepageController->updateButton($id);
                        } else {
                            header('Location: ?page=admin&module=homepage');
                        }
                        exit;
                    case 'deleteButton':
                         $id = $_GET['id'] ?? $_POST['id'] ?? 0;
                         if ($id > 0) {
                             $homepageController->deleteButton($id);
                         } else {
                             header('Location: ?page=admin&module=homepage');
                         }
                         exit;
                     case 'update-buttons':
                         $homepageController->updateButtons();
                         exit;
                     case 'upload-image':
                         $homepageController->uploadImage();
                         exit;
                     case 'reorderButtons':
                         $homepageController->reorderButtons();
                         exit;
                     
                     case 'index':
                    default:
                        $homepageController->index();
                        exit;
                }
                break;
                
            // Restore hero-section routing
            case 'hero-section':
                // Use HeroSectionController
                require_once __DIR__ . '/app/controllers/HeroSectionController.php';
                $heroSectionController = new HeroSectionController();
                
                $action = $_GET['action'] ?? 'index';
                $id = $_GET['id'] ?? null;
                
                switch($action) {
                    case 'create':
                        // Disabled - only allow editing existing hero section
                        header('Location: ?page=admin&module=hero-section');
                        exit;
                    case 'edit':
                        if ($id) {
                            $heroSectionController->edit($id);
                        } else {
                            header('Location: ?page=admin&module=hero-section');
                        }
                        exit;
                    case 'update':
                        if ($id) {
                            $heroSectionController->update($id);
                        } else {
                            header('Location: ?page=admin&module=hero-section');
                        }
                        exit;
                    case 'toggle-status':
                        // Handle both JSON and form data
                        $id = 0;
                        if (isset($_POST['id'])) {
                            $id = (int)$_POST['id'];
                        } else {
                            // Try to get from JSON body
                            $input = file_get_contents('php://input');
                            $data = json_decode($input, true);
                            $id = (int)($data['id'] ?? 0);
                        }
                        
                        if ($id > 0) {
                            $heroSectionController->toggleStatus($id);
                        } else {
                            $heroSectionController->sendJsonResponse(['success' => false, 'message' => 'Invalid ID']);
                        }
                        exit;
                    case 'delete':
                        // Disabled - hero sections cannot be deleted
                        header('Location: ?page=admin&module=hero-section');
                        exit;
                    case 'createButton':
                        $heroSectionController->createButton();
                        exit;
                    case 'updateButton':
                        $id = $_GET['id'] ?? $_POST['id'] ?? 0;
                        if ($id > 0) {
                            $heroSectionController->updateButton($id);
                        } else {
                            $heroSectionController->sendJsonResponse(['success' => false, 'message' => 'Invalid button ID']);
                        }
                        exit;
                    case 'deleteButton':
                         $id = $_GET['id'] ?? $_POST['id'] ?? 0;
                         if ($id > 0) {
                             $heroSectionController->deleteButton($id);
                         } else {
                             $heroSectionController->sendJsonResponse(['success' => false, 'message' => 'Invalid button ID']);
                         }
                         exit;
                     case 'update-buttons':
                         $heroSectionController->updateButtons();
                         exit;
                     case 'upload-image':
                         $heroSectionController->uploadImage();
                         exit;
                     case 'reorderButtons':
                         $heroSectionController->reorderButtons();
                         exit;
                     case 'index':
                    default:
                        $heroSectionController->index();
                        exit;
                }
                break;
                
            default:
                $page_title = 'Dashboard';
                $content = 'app/views/admin/dashboard.php';
                break;
        }
        break;
        
    case 'affiliate':
        // Affiliate dashboard routing - Uses its own layout
        $module = $_GET['module'] ?? 'dashboard';
        $action = $_GET['action'] ?? 'index';
        
        // Check authentication using AuthService (includes device validation)
        require_once __DIR__ . '/app/services/AuthService.php';
        $authService = new AuthService();
        if (!$authService->isAuthenticated()) {
            header('Location: ?page=login');
            exit;
        }
        
        // Check user role - allow admin or affiliate
        $userRole = $_SESSION['user_role'] ?? '';
        if ($userRole !== 'admin' && $userRole !== 'affiliate') {
            // Not an affiliate - redirect to users page
            header('Location: ?page=users');
            exit;
        }
        
        // Set flag to use affiliate layout
        $useAffiliateLayout = true;
        $currentService = $affiliateService ?? $currentService;
        
        // Route to specific affiliate modules
        switch($module) {
            case 'dashboard':
            default:
                // Use controller for dashboard
                require_once __DIR__ . '/app/controllers/AffiliateController.php';
                $affiliateController = new AffiliateController();
                $affiliateController->dashboard();
                exit;
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
    if (isset($content)) {
        include_once $content;
    } else {
        // Fallback if content not defined
        include_once 'errors/404.php';
    }
} else {
    include_once 'app/views/_layout/master.php';
}

if (ob_get_level() > 0) {
    ob_end_flush();
}