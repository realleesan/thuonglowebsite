<?php
// Debug script for affiliate dashboard
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Affiliate Debug</h1>";

try {
    // Load view_init
    require_once __DIR__ . '/core/view_init.php';
    
    echo "<p>1. view_init.php loaded successfully</p>";
    
    // Check if affiliateService is available
    global $affiliateService;
    
    if (isset($affiliateService)) {
        echo "<p>2. affiliateService is available</p>";
        
        if ($affiliateService instanceof AffiliateService) {
            echo "<p>3. affiliateService is correct type</p>";
            
            // Test getDashboardData
            $userId = $_SESSION['user_id'] ?? 1;
            echo "<p>4. Testing with user ID: $userId</p>";
            
            $dashboardData = $affiliateService->getDashboardData($userId);
            
            if ($dashboardData) {
                echo "<p>5. getDashboardData returned data</p>";
                echo "<pre>" . print_r($dashboardData, true) . "</pre>";
            } else {
                echo "<p>5. getDashboardData returned null/empty</p>";
            }
        } else {
            echo "<p>3. affiliateService is wrong type: " . get_class($affiliateService) . "</p>";
        }
    } else {
        echo "<p>2. affiliateService is NOT available</p>";
        
        // Check global variables
        echo "<p>Available globals:</p>";
        echo "<pre>" . print_r(array_keys($GLOBALS), true) . "</pre>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
    echo "<p>File: " . $e->getFile() . " Line: " . $e->getLine() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
} catch (Error $e) {
    echo "<p style='color: red;'>Fatal Error: " . $e->getMessage() . "</p>";
    echo "<p>File: " . $e->getFile() . " Line: " . $e->getLine() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

echo "<p><a href='?page=affiliate'>Try accessing affiliate page</a></p>";
?>
