<?php
/**
 * Test delete category ID 66
 */

// Enable error reporting
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Include required files
require_once __DIR__ . '/app/services/AdminService.php';

echo "<h2>Test Delete Category ID 66</h2>";

try {
    $adminService = new AdminService(null, 'admin');
    
    echo "<h3>1. Testing deleteCategory(66):</h3>";
    $result = $adminService->deleteCategory(66);
    
    echo "<pre>";
    echo "Result: " . print_r($result, true);
    echo "</pre>";
    
    if (isset($result['requires_confirmation']) && $result['requires_confirmation']) {
        echo "<h3>2. Testing forceDeleteCategory(66):</h3>";
        $forceResult = $adminService->forceDeleteCategory(66);
        
        echo "<pre>";
        echo "Force Result: " . print_r($forceResult, true);
        echo "</pre>";
    }
    
} catch (Exception $e) {
    echo "<h3>Error:</h3>";
    echo "<pre>";
    echo "Exception: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    echo "Trace:\n" . $e->getTraceAsString();
    echo "</pre>";
}

// Also test the AJAX endpoint directly
echo "<h3>3. Testing AJAX endpoint simulation:</h3>";

// Simulate POST request
$_POST['force_delete'] = '1';
$_GET['id'] = '66';
$_GET['page'] = 'admin';
$_GET['module'] = 'categories';
$_GET['action'] = 'delete';
$_SERVER['REQUEST_METHOD'] = 'POST';

// Clean any output
if (ob_get_length()) ob_clean();

// Set JSON header
header('Content-Type: application/json');

try {
    require_once __DIR__ . '/app/services/AdminService.php';
    $adminService = new AdminService(null, 'admin');
    
    $forceDelete = $_POST['force_delete'] ?? false;
    
    if ($forceDelete) {
        $result = $adminService->forceDeleteCategory((int)$_GET['id']);
    } else {
        $result = $adminService->deleteCategory((int)$_GET['id']);
    }
    
    echo json_encode($result);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()]);
}
?>
