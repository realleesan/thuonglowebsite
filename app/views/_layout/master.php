<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($title) ? $title : 'Thuong Lo - Nền tảng học trực tuyến'; ?></title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- CSS Files -->
    <?php
    $segments = explode('/', trim($_SERVER['SCRIPT_NAME'], '/'));
    $base = '/' . ($segments[0] ?? '') . '/';
    ?>
    <base href="<?php echo $base; ?>">
    <link rel="stylesheet" href="assets/css/header.css">
    <link rel="stylesheet" href="assets/css/home.css">
    <link rel="stylesheet" href="assets/css/footer.css">
    <link rel="stylesheet" href="assets/css/cta.css">
    <link rel="stylesheet" href="assets/css/contact.css">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="assets/fonts/awesome-5x/all.css">
    
    <!-- Additional CSS if needed -->
    <?php if (isset($additionalCSS)): ?>
        <?php foreach ($additionalCSS as $css): ?>
            <link rel="stylesheet" href="<?php echo $css; ?>">
        <?php endforeach; ?>
    <?php endif; ?>
</head>
<body>
    <!-- Header -->
    <?php include_once 'header.php'; ?>
    
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
    
    <!-- JavaScript Files -->
    <script src="assets/js/header.js"></script>
    <script src="assets/js/home.js"></script>
    <script src="assets/js/footer.js"></script>
    <script src="assets/js/cta.js"></script>
    <script src="assets/js/contact.js"></script>
    
    <!-- Additional JS if needed -->
    <?php if (isset($additionalJS)): ?>
        <?php foreach ($additionalJS as $js): ?>
            <script src="<?php echo $js; ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>