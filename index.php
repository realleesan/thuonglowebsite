<?php
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
}

// Include master layout
include_once 'app/views/_layout/master.php';
?>