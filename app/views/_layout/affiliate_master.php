<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title><?php echo isset($page_title) ? $page_title . ' - ' : ''; ?>ThuongLo Affiliate</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="<?php echo icon_url('logo/logo_mini.svg'); ?>">
    
    <!-- Google Fonts - Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Font Awesome 5 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    
    <!-- Affiliate Core Styles -->
    <link rel="stylesheet" href="<?php echo base_url(); ?>assets/css/affiliate_style.css">
    <link rel="stylesheet" href="<?php echo base_url(); ?>assets/css/affiliate_header.css">
    <link rel="stylesheet" href="<?php echo base_url(); ?>assets/css/affiliate_components.css">
    <link rel="stylesheet" href="<?php echo base_url(); ?>assets/css/affiliate_responsive.css">
    
    <!-- Module-specific Styles -->
    <link rel="stylesheet" href="<?php echo base_url(); ?>assets/css/affiliate_dashboard.css">
    <link rel="stylesheet" href="<?php echo base_url(); ?>assets/css/affiliate_commissions.css">
    <link rel="stylesheet" href="<?php echo base_url(); ?>assets/css/affiliate_customers.css">
    <link rel="stylesheet" href="<?php echo base_url(); ?>assets/css/affiliate_finance.css">
    <link rel="stylesheet" href="<?php echo base_url(); ?>assets/css/affiliate_marketing.css">
    <link rel="stylesheet" href="<?php echo base_url(); ?>assets/css/affiliate_reports.css">
    <link rel="stylesheet" href="<?php echo base_url(); ?>assets/css/affiliate_profile.css">
    
    <!-- Chart.js (for dashboard) -->
    <?php if (isset($load_chartjs) && $load_chartjs): ?>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <?php endif; ?>
    
    <!-- Additional CSS -->
    <?php if (isset($additional_css)): ?>
        <?php foreach ($additional_css as $css): ?>
            <link rel="stylesheet" href="<?php echo base_url() . $css; ?>">
        <?php endforeach; ?>
    <?php endif; ?>
</head>
<body class="affiliate-body">
    <div class="affiliate-wrapper">
        <!-- Sidebar -->
        <?php include __DIR__ . '/affiliate_sidebar.php'; ?>

        <!-- Main Content Area -->
        <div class="affiliate-main">
            <!-- Header -->
            <?php include __DIR__ . '/affiliate_header.php'; ?>

            <!-- Breadcrumb -->
            <?php include __DIR__ . '/affiliate_breadcrumb.php'; ?>

            <!-- Page Content -->
            <main class="affiliate-content">
                <?php
                // Echo the content that was buffered in the page file
                if (isset($content)) {
                    echo $content;
                } elseif (isset($content_file) && file_exists($content_file)) {
                    include $content_file;
                } else {
                    echo '<div class="alert alert-danger">Không tìm thấy nội dung trang.</div>';
                }
                ?>
            </main>

            <!-- Footer -->
            <?php include __DIR__ . '/affiliate_footer.php'; ?>
        </div>
    </div>

    <!-- Affiliate Core JavaScript -->
    <script src="<?php echo base_url(); ?>assets/js/affiliate_main.js"></script>
    
    <!-- Module-specific JavaScript -->
    <script src="<?php echo base_url(); ?>assets/js/affiliate_dashboard.js"></script>
    <script src="<?php echo base_url(); ?>assets/js/affiliate_commissions.js"></script>
    <script src="<?php echo base_url(); ?>assets/js/affiliate_customers.js"></script>
    <script src="<?php echo base_url(); ?>assets/js/affiliate_finance.js"></script>
    <script src="<?php echo base_url(); ?>assets/js/affiliate_marketing.js"></script>
    <script src="<?php echo base_url(); ?>assets/js/affiliate_reports.js"></script>
    <script src="<?php echo base_url(); ?>assets/js/affiliate_profile.js"></script>
    
    <!-- Chart Config (for dashboard) -->
    <?php if (isset($load_chartjs) && $load_chartjs): ?>
    <script src="<?php echo base_url(); ?>assets/js/affiliate_chart_config.js"></script>
    <?php endif; ?>
    
    <!-- AJAX Actions -->
    <script src="<?php echo base_url(); ?>assets/js/affiliate_ajax_actions.js"></script>
    
    <!-- Additional JavaScript -->
    <?php if (isset($additional_js)): ?>
        <?php foreach ($additional_js as $js): ?>
            <script src="<?php echo base_url() . $js; ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>
