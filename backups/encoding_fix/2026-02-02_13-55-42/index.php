<?php
// Khá»Ÿi táº¡o session
session_start();

// Include cÃ¡c file cáº§n thiáº¿t
$config = require_once 'config.php';
require_once 'core/functions.php';

// Initialize URL Builder
init_url_builder();

// Báº­t output buffering Ä‘á»ƒ cÃ¡c trang con cÃ³ thá»ƒ sá»­ dá»¥ng header() sau khi layout Ä‘Ã£ báº¯t Ä‘áº§u render
if (ob_get_level() === 0) {
    ob_start();
}

// Láº¥y trang hiá»‡n táº¡i tá»« URL
$page = $_GET['page'] ?? 'home';

// Thiáº¿t láº­p thÃ´ng tin cho tá»«ng trang
switch($page) {
    case 'home':
        $title = 'Trang chá»§ - Thuong Lo';
        $content = 'app/views/home/home.php';
        $showPageHeader = false;
        $showCTA = true;
        $showBreadcrumb = false; // Trang chá»§ khÃ´ng cáº§n breadcrumb
        break;
        
    case 'about':
        $title = 'Giá»›i thiá»‡u - Thuong Lo';
        $content = 'app/views/about/about.php';
        $showPageHeader = true;
        $showCTA = false;
        $showBreadcrumb = true;
        $breadcrumbs = generate_breadcrumb('about');
        $additionalCSS = ['assets/css/about.css?v=' . time()];
        $additionalJS = ['assets/js/about.js?v=' . time()];
        break;
        
    case 'products':
        $title = 'Sáº£n pháº©m - Thuong Lo';
        $content = 'app/views/products/products.php';
        $showPageHeader = true;
        $showCTA = false;
        $showBreadcrumb = true;
        $breadcrumbs = generate_breadcrumb('products');
        break;
        
    case 'categories':
        $title = 'Danh má»¥c - Thuong Lo';
        $content = 'app/views/categories/categories.php';
        $showPageHeader = true;
        $showCTA = false;
        $showBreadcrumb = true;
        $breadcrumbs = generate_breadcrumb('categories');
        break;
        
    case 'details':
    case 'course-details':
        $title = 'GÃ³i Data Nguá»“n HÃ ng Premium - Thuong Lo';
        $content = 'app/views/products/details.php';
        $showPageHeader = false;
        $showCTA = false;
        $showBreadcrumb = true;
        
        // Æ¯u tiÃªn láº¥y tá»« database náº¿u cÃ³ ID
        if (isset($_GET['id']) && is_numeric($_GET['id'])) {
            $breadcrumbs = get_product_breadcrumb_from_db($_GET['id']);
        } else {
            // Chá»‰ sá»­ dá»¥ng product name, khÃ´ng dÃ¹ng category Ä‘á»ƒ trÃ¡nh trÃ¹ng láº·p
            $product_name = $_GET['product'] ?? 'Data nguá»“n hÃ ng cháº¥t lÆ°á»£ng cao';
            $breadcrumbs = [
                ['title' => 'Trang chá»§', 'url' => './'],
                ['title' => 'Sáº£n pháº©m', 'url' => '?page=products'],
                ['title' => $product_name]
            ];
        }
        break;
        
    case 'news':
        $title = 'Tin tá»©c - Thuong Lo';
        $content = 'app/views/news/news.php';
        $showPageHeader = true;
        $showCTA = false;
        $showBreadcrumb = true;
        $breadcrumbs = generate_breadcrumb('news');
        break;
        
    case 'news-details':
        $title = 'Chi tiáº¿t tin tá»©c - Thuong Lo';
        $content = 'app/views/news/details.php';
        $showPageHeader = false;
        $showCTA = false;
        $showBreadcrumb = true;
        
        // Æ¯u tiÃªn láº¥y tá»« database náº¿u cÃ³ ID
        if (isset($_GET['id']) && is_numeric($_GET['id'])) {
            $breadcrumbs = get_news_breadcrumb_from_db($_GET['id']);
        } else {
            // Fallback sá»­ dá»¥ng URL params
            $news_category = $_GET['category'] ?? '';
            $news_title = $_GET['title'] ?? 'Chi tiáº¿t tin tá»©c';
            $breadcrumbs = generate_news_breadcrumb($news_category, $news_title);
        }
        break;
        
    case 'contact':
        $title = 'LiÃªn há»‡ - Thuong Lo';
        $content = 'app/views/contact/contact.php';
        $showPageHeader = true;
        $showCTA = false;
        $showBreadcrumb = true;
        $breadcrumbs = generate_breadcrumb('contact');
        break;
        
    case 'login':
        $title = 'ÄÄƒng nháº­p - Thuong Lo';
        $content = 'app/views/auth/login.php';
        $showPageHeader = false;
        $showCTA = false;
        $showBreadcrumb = true;
        $breadcrumbs = generate_breadcrumb('auth');
        break;
        
    case 'register':
        $title = 'ÄÄƒng kÃ½ - Thuong Lo';
        $content = 'app/views/auth/register.php';
        $showPageHeader = false;
        $showCTA = false;
        $showBreadcrumb = true;
        $breadcrumbs = generate_breadcrumb('register');
        break;

    case 'forgot':
        $title = 'QuÃªn máº­t kháº©u - Thuong Lo';
        $content = 'app/views/auth/forgot.php';
        $showPageHeader = false;
        $showCTA = false;
        $showBreadcrumb = true;
        $breadcrumbs = [
            ['title' => 'Trang chá»§', 'url' => './'],
            ['title' => 'ÄÄƒng nháº­p', 'url' => '?page=login'],
            ['title' => 'QuÃªn máº­t kháº©u']
        ];
        break;

    case 'checkout':
        $title = 'Thanh toÃ¡n - Thuong Lo';
        $content = 'app/views/payment/checkout.php';
        $showPageHeader = false;
        $showCTA = false;
        $showBreadcrumb = true;
        $breadcrumbs = generate_breadcrumb('checkout');
        break;
        
    case 'payment':
        $title = 'Thanh toÃ¡n - Thuong Lo';
        $content = 'app/views/payment/payment.php';
        $showPageHeader = false;
        $showCTA = false;
        $showBreadcrumb = true;
        $breadcrumbs = [
            ['title' => 'Trang chá»§', 'url' => './'],
            ['title' => 'Giá» hÃ ng', 'url' => '?page=cart'],
            ['title' => 'Thanh toÃ¡n', 'url' => '?page=checkout'],
            ['title' => 'Xá»­ lÃ½ thanh toÃ¡n']
        ];
        break;
        
    case 'payment_success':
        $title = 'ThÃ nh cÃ´ng - Thuong Lo';
        $content = 'app/views/payment/success.php';
        $showPageHeader = false;
        $showCTA = false;
        $showBreadcrumb = true;
        $breadcrumbs = [
            ['title' => 'Trang chá»§', 'url' => './'],
            ['title' => 'Thanh toÃ¡n thÃ nh cÃ´ng']
        ];
        break;
        
    default:
        $title = 'KhÃ´ng tÃ¬m tháº¥y trang - Thuong Lo';
        $content = 'errors/404.php';
        $showPageHeader = false;
        $showCTA = false;
        $showBreadcrumb = false;
        break;
}

// Include master layout
include_once 'app/views/_layout/master.php';

if (ob_get_level() > 0) {
    ob_end_flush();
}
?>