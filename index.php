<?php
ob_start();
// Khởi tạo session
session_start();

// Include các file cần thiết
require_once 'config.php';
require_once 'core/functions.php';

// Lấy trang hiện tại từ URL
$page = $_GET['page'] ?? 'home';

// Thiết lập thông tin cho từng trang
switch($page) {
    case 'home':
        $title = 'Trang chủ - Thuong Lo';
        $content = 'app/views/home/home.php';
        $showPageHeader = false;
        $showCTA = true;
        break;
        
    case 'about':
        $title = 'Giới thiệu - Thuong Lo';
        $content = 'app/views/about/about.php';
        $showPageHeader = true;
        $showCTA = false;
        $additionalCSS = ['assets/css/about.css?v=' . time()];
        $additionalJS = ['assets/js/about.js?v=' . time()];
        break;
        
    case 'products':
        $title = 'Sản phẩm - Thuong Lo';
        $content = 'app/views/products/products.php';
        $showPageHeader = true;
        $showCTA = true;
        break;
        
    case 'news':
        $title = 'Tin tức - Thuong Lo';
        $content = 'app/views/news/news.php';
        $showPageHeader = true;
        $showCTA = false;
        break;
        
    case 'contact':
        $title = 'Liên hệ - Thuong Lo';
        $content = 'app/views/contact/contact.php';
        $showPageHeader = true;
        $showCTA = false;
        break;
        
    case 'login':
        $title = 'Đăng nhập - Thuong Lo';
        $content = 'app/views/auth/login.php';
        $showPageHeader = false;
        $showCTA = false;
        break;

    case 'forgot':
        $title = 'Quên mật khẩu';
        $content = 'app/views/auth/forgot.php';
        $showPageHeader = true;
        $showCTA = true;
        
        // Thêm ?v=time() vào CSS để xóa cache cũ
        $additionalCSS = [
            'assets/css/auth.css', 
            'assets/css/forgot.css'
        ];
        
        $additionalJS = [
            'assets/js/auth.js',
            'assets/js/forgot.js'
        ];
        break;
        
    case 'register':
        $title = 'Đăng ký - Thuong Lo';
        $content = 'app/views/auth/register.php';
        $showPageHeader = false;
        $showCTA = false;
        break;
        
    default:
        $title = 'Không tìm thấy trang - Thuong Lo';
        $content = 'errors/404.php';
        $showPageHeader = false;
        $showCTA = false;
        break;

    case 'checkout':
        $title = 'Thanh toán - Thuong Lo';
        $content = 'app/views/payment/checkout.php';
        $showPageHeader = true;
        $additionalCSS = ['assets/css/payment.css?v=' . time()];
        break;

    case 'payment':
        $title = 'Quét mã QR - Thuong Lo';
        $content = 'app/views/payment/payment.php';
        $showPageHeader = true;
        $additionalCSS = ['assets/css/payment.css?v=' . time()];
        break;

    case 'payment_success':
        $title = 'Thanh toán thành công';
        $content = 'app/views/payment/success.php';
        $showPageHeader = true;
        $additionalCSS = ['assets/css/payment.css?v=' . time()];
        break;

// ...
}

// Include master layout
include_once 'app/views/_layout/master.php';
ob_end_flush();
?>