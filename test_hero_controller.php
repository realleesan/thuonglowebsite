<?php
// Test HeroSectionController directly
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>HeroSectionController Direct Test</h1>";

// Mock session for admin
session_start();
$_SESSION['user_id'] = 1;
$_SESSION['user_role'] = 'admin';
$_SESSION['username'] = 'admin';

echo "<h2>Mock Admin Session Set</h2>";
echo "<p>User ID: " . $_SESSION['user_id'] . "</p>";
echo "<p>User Role: " . $_SESSION['user_role'] . "</p>";

try {
    // Load required files
    require_once 'config.php';
    require_once 'core/view_init.php';
    require_once 'app/services/AuthService.php';
    require_once 'app/models/BaseModel.php';
    require_once 'app/models/HeroSectionModel.php';
    require_once 'app/models/HeroButtonModel.php';
    require_once 'app/controllers/HeroSectionController.php';
    
    echo "<h2>✓ All files loaded successfully</h2>";
    
    // Test AuthService
    $authService = new AuthService();
    echo "<h2>✓ AuthService created</h2>";
    
    $isLoggedIn = $authService->isLoggedIn();
    $hasAdminRole = $authService->hasRole('admin');
    
    echo "<p>Is logged in: " . ($isLoggedIn ? 'YES' : 'NO') . "</p>";
    echo "<p>Has admin role: " . ($hasAdminRole ? 'YES' : 'NO') . "</p>";
    
    // Test HeroSectionModel
    $heroModel = new HeroSectionModel();
    echo "<h2>✓ HeroSectionModel created</h2>";
    
    // Test getAllForAdmin method
    echo "<h3>Testing getAllForAdmin():</h3>";
    $heroSections = $heroModel->getAllForAdmin();
    
    if ($heroSections !== null) {
        echo "<p>✓ getAllForAdmin() returned data</p>";
        echo "<p>Number of hero sections: " . count($heroSections) . "</p>";
        
        if (!empty($heroSections)) {
            echo "<h4>First hero section data:</h4>";
            echo "<pre>" . print_r($heroSections[0], true) . "</pre>";
        }
    } else {
        echo "<p>✗ getAllForAdmin() returned null</p>";
    }
    
    // Test HeroButtonModel
    $buttonModel = new HeroButtonModel();
    echo "<h2>✓ HeroButtonModel created</h2>";
    
    // Create controller
    $controller = new HeroSectionController();
    echo "<h2>✓ HeroSectionController created</h2>";
    
    echo "<h2>✓ All components working correctly</h2>";
    echo "<p>The issue might be in the view rendering or layout.</p>";
    
    // Test view file existence
    $viewFile = 'app/views/admin/homepage/hero_section/index.php';
    if (file_exists($viewFile)) {
        echo "<p>✓ View file exists: $viewFile</p>";
        
        // Try to include view directly (without layout)
        echo "<h3>Testing view file directly:</h3>";
        
        // Mock data for view
        $title = 'Quản lý Hero Section';
        $heroSections = $heroSections;
        $user = ['id' => 1, 'username' => 'admin', 'role' => 'admin'];
        
        ob_start();
        include $viewFile;
        $viewContent = ob_get_clean();
        
        if (!empty($viewContent)) {
            echo "<p>✓ View file rendered successfully</p>";
            echo "<p>View content length: " . strlen($viewContent) . " characters</p>";
            
            // Show first 500 characters of view content
            echo "<h4>View content preview:</h4>";
            echo "<div style='border: 1px solid #ccc; padding: 10px; background: #f9f9f9;'>";
            echo "<pre>" . htmlspecialchars(substr($viewContent, 0, 500)) . "</pre>";
            if (strlen($viewContent) > 500) {
                echo "<p>... (truncated)</p>";
            }
            echo "</div>";
        } else {
            echo "<p>✗ View file returned empty content</p>";
        }
    } else {
        echo "<p>✗ View file missing: $viewFile</p>";
    }
    
} catch (Exception $e) {
    echo "<h2>✗ Error occurred:</h2>";
    echo "<p style='color: red;'><strong>Error:</strong> " . $e->getMessage() . "</p>";
    echo "<p><strong>File:</strong> " . $e->getFile() . ":" . $e->getLine() . "</p>";
    echo "<h3>Stack Trace:</h3>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
?>
