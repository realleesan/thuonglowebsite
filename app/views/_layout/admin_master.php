<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ' : ''; ?>Admin ThuongLo</title>
    
    <!-- CSS Files -->
    <link rel="stylesheet" href="<?php echo css_url('admin_sidebar.css'); ?>">
    <link rel="stylesheet" href="<?php echo css_url('admin_header.css'); ?>">
    <link rel="stylesheet" href="<?php echo css_url('admin_footer.css'); ?>">
    <link rel="stylesheet" href="<?php echo css_url('admin_dashboard.css'); ?>">
    <link rel="stylesheet" href="<?php echo css_url('admin_pages.css'); ?>">
    <link rel="stylesheet" href="<?php echo css_url('admin_products.css'); ?>">
    <link rel="stylesheet" href="<?php echo css_url('admin_categories.css'); ?>">
    <link rel="stylesheet" href="<?php echo css_url('admin_news.css'); ?>">
    <link rel="stylesheet" href="<?php echo css_url('admin_events.css'); ?>">
    <link rel="stylesheet" href="<?php echo css_url('admin_orders.css'); ?>">
    <link rel="stylesheet" href="<?php echo css_url('admin_users.css'); ?>">
    <link rel="stylesheet" href="<?php echo css_url('admin_affiliates.css'); ?>">
    <link rel="stylesheet" href="<?php echo css_url('admin_contact.css'); ?>">
    <link rel="stylesheet" href="<?php echo css_url('admin_revenue.css'); ?>">
    <link rel="stylesheet" href="<?php echo css_url('admin_settings.css'); ?>">
    
    <!-- FontAwesome CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    
    <!-- Chart.js CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="admin-body">
    <div class="admin-wrapper">
        <!-- Sidebar -->
        <?php include 'app/views/_layout/admin_sidebar.php'; ?>
        
        <!-- Main Content Area -->
        <div class="admin-main">
            <!-- Header -->
            <?php include 'app/views/_layout/admin_header.php'; ?>
            
            <!-- Content -->
            <div class="admin-content">
                <?php 
                if (isset($content) && $content) {
                    include $content;
                }
                ?>
            </div>
            
            <!-- Footer -->
            <?php include 'app/views/_layout/admin_footer.php'; ?>
        </div>
    </div>
    
    <!-- JavaScript Files -->
    <script src="<?php echo js_url('admin_sidebar.js'); ?>"></script>
    <script src="<?php echo js_url('admin_header.js'); ?>"></script>
    <script src="<?php echo js_url('admin_footer.js'); ?>"></script>
    <script src="<?php echo js_url('admin_pages.js'); ?>"></script>
    <script src="<?php echo js_url('admin_dashboard.js'); ?>"></script>
    <script src="<?php echo js_url('admin_products.js'); ?>"></script>
    <script src="<?php echo js_url('admin_categories.js'); ?>"></script>
    <script src="<?php echo js_url('admin_news.js'); ?>"></script>
    <script src="<?php echo js_url('admin_events.js'); ?>"></script>
    <script src="<?php echo js_url('admin_orders.js'); ?>"></script>
    <script src="<?php echo js_url('admin_users.js'); ?>"></script>
    <script src="<?php echo js_url('admin_affiliates.js'); ?>"></script>
    <script src="<?php echo js_url('admin_contact.js'); ?>"></script>
    <script src="<?php echo js_url('admin_revenue.js'); ?>"></script>
    <script src="<?php echo js_url('admin_settings.js'); ?>"></script>
    
</body>
</html>