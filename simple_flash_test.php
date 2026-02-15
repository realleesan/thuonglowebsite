<?php
/**
 * Simple Flash Message Test
 */

session_start();

// Handle form submission
if ($_POST) {
    if (isset($_POST['test_success'])) {
        $_SESSION['flash_success'] = 'Test success message!';
        header('Location: simple_flash_test.php');
        exit;
    }
    
    if (isset($_POST['test_error'])) {
        $_SESSION['flash_error'] = 'Test error message!';
        header('Location: simple_flash_test.php');
        exit;
    }
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>Simple Flash Test</title>
    <link rel="stylesheet" href="assets/css/flash_messages.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <h1>Simple Flash Message Test</h1>
    
    <!-- Flash Messages (copy from master.php) -->
    <?php if (isset($_SESSION['flash_success'])): ?>
        <div class="flash-message flash-success">
            <i class="fas fa-check-circle"></i>
            <?php echo htmlspecialchars($_SESSION['flash_success']); ?>
            <button class="flash-close" onclick="this.parentElement.style.display='none'">&times;</button>
        </div>
        <?php unset($_SESSION['flash_success']); ?>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['flash_error'])): ?>
        <div class="flash-message flash-error">
            <i class="fas fa-exclamation-circle"></i>
            <?php echo htmlspecialchars($_SESSION['flash_error']); ?>
            <button class="flash-close" onclick="this.parentElement.style.display='none'">&times;</button>
        </div>
        <?php unset($_SESSION['flash_error']); ?>
    <?php endif; ?>
    
    <form method="POST">
        <button type="submit" name="test_success">Test Success Message</button>
        <button type="submit" name="test_error">Test Error Message</button>
    </form>
    
    <h3>Current Session:</h3>
    <pre><?php print_r($_SESSION); ?></pre>
    
    <script src="assets/js/flash_messages.js"></script>
</body>
</html>