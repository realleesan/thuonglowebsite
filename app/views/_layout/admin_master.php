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
    <title><?php echo isset($title) ? $title : 'Admin Panel - Thuong Lo'; ?></title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    
    <!-- Admin CSS Files -->
    <link rel="stylesheet" href="<?php echo versioned_css('admin.css'); ?>">
    <link rel="stylesheet" href="<?php echo versioned_css('admin_sidebar.css'); ?>">
    
    <?php
    // Load module-specific CSS
    $currentModule = $_GET['module'] ?? 'dashboard';
    switch($currentModule) {
        case 'products':
        case 'categories':
        case 'news':
        case 'events':
            echo '<link rel="stylesheet" href="' . versioned_css('admin.css') . '">';
            break;
    }
    ?>
    
    <!-- Additional CSS if needed -->
    <?php if (isset($additionalCSS)): ?>
        <?php foreach ($additionalCSS as $css): ?>
            <link rel="stylesheet" href="<?php echo $css; ?>">
        <?php endforeach; ?>
    <?php endif; ?>
</head>
<body class="admin-layout">
    <div class="admin-container">
        <!-- Admin Sidebar -->
        <?php include_once 'admin_sidebar.php'; ?>
        
        <!-- Main Admin Content -->
        <main class="admin-main" id="adminMain">
            <!-- Admin Header -->
            <header class="admin-header">
                <div class="admin-header-left">
                    <button class="mobile-sidebar-toggle" id="mobileSidebarToggle">
                        <i class="fas fa-bars"></i>
                    </button>
                    <h1><?php echo isset($pageTitle) ? $pageTitle : 'Dashboard'; ?></h1>
                </div>
                <div class="admin-header-right">
                    <div class="admin-user-menu">
                        <span>Xin chào, <?php echo $_SESSION['full_name'] ?? 'Admin'; ?></span>
                        <a href="?page=auth&action=logout" class="logout-btn">
                            <i class="fas fa-sign-out-alt"></i>
                        </a>
                    </div>
                </div>
            </header>
            
            <!-- Admin Content Area -->
            <div class="admin-content">
                <?php 
                // Include the specific admin page content
                if (isset($content) && $content) {
                    include_once $content;
                }
                ?>
            </div>
        </main>
    </div>
    
    <!-- JavaScript Files -->
    <script src="<?php echo versioned_js('admin_sidebar.js'); ?>"></script>
    <script src="<?php echo versioned_js('admin.js'); ?>"></script>
    
    <?php
    // Load module-specific JavaScript
    switch($currentModule) {
        case 'products':
        case 'categories':
        case 'news':
        case 'events':
            echo '<script src="' . versioned_js('admin.js') . '"></script>';
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