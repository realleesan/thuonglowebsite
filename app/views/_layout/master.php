<?php
// Set charset headers for proper UTF-8 encoding
header('Content-Type: text/html; charset=UTF-8');
mb_internal_encoding('UTF-8');
mb_http_output('UTF-8');
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($title) ? $title : 'Thuong Lo - Nền tảng học trực tuyến'; ?></title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- CSS Files -->
    <?php
    // Determine current page for conditional CSS loading
    $currentPage = '';
    if (isset($_GET['page'])) {
        $currentPage = $_GET['page'];
    } else {
        $currentPage = 'home';
    }
    ?>
    
    <!-- Core CSS Files -->
    <link rel="stylesheet" href="<?php echo versioned_css('header.css'); ?>">
    <link rel="stylesheet" href="<?php echo versioned_css('footer.css'); ?>">
    <link rel="stylesheet" href="<?php echo versioned_css('cta.css'); ?>">
    <link rel="stylesheet" href="<?php echo versioned_css('pusher.css'); ?>">
    
    <?php
    // Load page-specific CSS
    switch($currentPage) {
        case 'home':
            echo '<link rel="stylesheet" href="' . versioned_css('home.css') . '">';
            break;
        case 'categories':
            echo '<link rel="stylesheet" href="' . versioned_css('categories.css') . '">';
            break;
        case 'contact':
            echo '<link rel="stylesheet" href="' . versioned_css('contact.css') . '">';
            break;
        case 'about':
            echo '<link rel="stylesheet" href="' . versioned_css('about.css') . '">';
            break;
        case 'products':
        case 'courses':
            echo '<link rel="stylesheet" href="' . versioned_css('products.css') . '">';
            break;
        case 'details':
        case 'course-details':
            echo '<link rel="stylesheet" href="' . versioned_css('details.css') . '">';
            echo '<link rel="stylesheet" href="' . versioned_css('related.css') . '">';
            break;
        case 'auth':
        case 'login':
        case 'register':
            echo '<link rel="stylesheet" href="' . versioned_css('auth.css') . '">';
            break;
        case 'forgot':
            echo '<link rel="stylesheet" href="' . versioned_css('forgot.css') . '">';
            break;
        case 'users':
            echo '<link rel="stylesheet" href="' . versioned_css('user_sidebar.css') . '">';
            echo '<link rel="stylesheet" href="' . versioned_css('user_dashboard.css') . '">';
            echo '<link rel="stylesheet" href="' . versioned_css('user_account.css') . '">';
            echo '<link rel="stylesheet" href="' . versioned_css('user_orders.css') . '">';
            break;
        case 'checkout':
        case 'payment':
        case 'payment_success':
            echo '<link rel="stylesheet" href="' . versioned_css('payment.css') . '">';
            break;
        default:
            echo '<link rel="stylesheet" href="' . versioned_css('home.css') . '">';
            break;
    }
    ?>
    
    <!-- Breadcrumb CSS - Load after page-specific CSS to ensure priority -->
    <link rel="stylesheet" href="<?php echo versioned_css('breadcrumb.css'); ?>"
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    
    <!-- Additional CSS if needed -->
    <?php if (isset($additionalCSS)): ?>
        <?php foreach ($additionalCSS as $css): ?>
            <link rel="stylesheet" href="<?php echo $css; ?>">
        <?php endforeach; ?>
    <?php endif; ?>
</head>
<body class="<?php echo $currentPage; ?>-page">
    <!-- Header -->
    <?php include_once 'header.php'; ?>
    
    <!-- Breadcrumb (if needed) -->
    <?php if (isset($showBreadcrumb) && $showBreadcrumb && isset($breadcrumbs)): ?>
        <?php render_breadcrumb($breadcrumbs); ?>
    <?php endif; ?>
    
    <!-- Page Header (if needed) -->
    <?php if (isset($showPageHeader) && $showPageHeader): ?>
        <?php include_once 'pageheader.php'; ?>
    <?php endif; ?>
    
    <!-- Main Content -->
    <main class="main-content">
        <?php 
        // Include the specific page content
        if (isset($content) && $content) {
            include_once $content;
        }
        ?>
    </main>
    
    <!-- CTA Section (if needed) -->
    <?php if (isset($showCTA) && $showCTA): ?>
        <?php include_once 'cta.php'; ?>
    <?php endif; ?>
    
    <!-- Footer -->
    <?php include_once 'footer.php'; ?>
    
    <!-- Scroll to Top Button -->
    <?php include_once 'pusher.php'; ?>
    
    <!-- JavaScript Files -->
    <script src="<?php echo versioned_js('header.js'); ?>"></script>
    <script src="<?php echo versioned_js('footer.js'); ?>"></script>
    <script src="<?php echo versioned_js('cta.js'); ?>"></script>
    <script src="<?php echo versioned_js('pusher.js'); ?>"></script>
    <script src="<?php echo versioned_js('breadcrumb.js'); ?>"></script>
    
    <?php
    // Load page-specific JavaScript
    switch($currentPage) {
        case 'home':
            echo '<script src="' . versioned_js('home.js') . '"></script>';
            break;
         case 'categories':
            echo '<script src="' . versioned_js('categories.js') . '"></script>';
            break;
        case 'contact':
            echo '<script src="' . versioned_js('contact.js') . '"></script>';
            break;
        case 'about':
            echo '<script src="' . versioned_js('about.js') . '"></script>';
            break;
        case 'products':
        case 'courses':
            echo '<script src="' . versioned_js('products.js') . '"></script>';
            break;
        case 'details':
        case 'course-details':
            echo '<script src="' . versioned_js('details.js') . '"></script>';
            echo '<script src="' . versioned_js('related.js') . '"></script>';
            break;
        case 'auth':
        case 'login':
        case 'register':
            echo '<script src="' . versioned_js('auth.js') . '"></script>';
            break;
        case 'forgot':
            echo '<script src="' . versioned_js('forgot.js') . '"></script>';
            break;
        case 'users':
            echo '<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>';
            echo '<script src="' . versioned_js('user_sidebar.js') . '"></script>';
            echo '<script src="' . versioned_js('user_dashboard.js') . '"></script>';
            echo '<script src="' . versioned_js('user_account.js') . '"></script>';
            echo '<script src="' . versioned_js('user_orders.js') . '"></script>';
            break;
        default:
            echo '<script src="' . versioned_js('home.js') . '"></script>';
            break;
    }
    ?>
    
    <!-- Additional JS if needed -->
    <?php if (isset($additionalJS)): ?>
        <?php foreach ($additionalJS as $js): ?>
            <script src="<?php echo $js; ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>