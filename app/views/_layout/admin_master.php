<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ' : ''; ?>Admin ThuongLo</title>
    
    <!-- CSS Files -->
    <link rel="stylesheet" href="assets/css/admin_sidebar.css">
    <link rel="stylesheet" href="assets/css/admin_header.css">
    <link rel="stylesheet" href="assets/css/admin_footer.css">
    <link rel="stylesheet" href="assets/css/admin_dashboard.css">
    <link rel="stylesheet" href="assets/css/admin_pages.css">
    <link rel="stylesheet" href="assets/css/admin_products.css">
    <link rel="stylesheet" href="assets/css/admin_categories.css">
    <link rel="stylesheet" href="assets/css/admin_news.css">
    <link rel="stylesheet" href="assets/css/admin_events.css">
    <link rel="stylesheet" href="assets/css/admin_orders.css">
    <link rel="stylesheet" href="assets/css/admin_users.css">
    <link rel="stylesheet" href="assets/css/admin_affiliates.css">
    <link rel="stylesheet" href="assets/css/admin_contact.css">
    
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
            
            <!-- Breadcrumb -->
            <?php include 'app/views/_layout/admin_breadcrumb.php'; ?>
            
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
    <script src="assets/js/admin_sidebar.js"></script>
    <script src="assets/js/admin_header.js"></script>
    <script src="assets/js/admin_footer.js"></script>
    <script src="assets/js/admin_pages.js"></script>
    <script src="assets/js/admin_dashboard.js"></script>
    <script src="assets/js/admin_products.js"></script>
    <script src="assets/js/admin_categories.js"></script>
    <script src="assets/js/admin_news.js"></script>
    <script src="assets/js/admin_events.js"></script>
    <script src="assets/js/admin_orders.js"></script>
    <script src="assets/js/admin_users.js"></script>
    <script src="assets/js/admin_affiliates.js"></script>
    <script src="assets/js/admin_contact.js"></script>
</body>
</html>