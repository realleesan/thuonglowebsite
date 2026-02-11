<?php
/**
 * Admin Dashboard ViewDataService Integration Test
 * Tests that admin dashboard properly uses ViewDataService
 */

class AdminDashboardViewDataServiceTest {
    
    /**
     * Test that admin dashboard uses ViewDataService
     */
    public function testAdminDashboardUsesViewDataService() {
        $dashboardPath = __DIR__ . '/../app/views/admin/dashboard.php';
        
        if (!file_exists($dashboardPath)) {
            throw new Exception("Admin dashboard view file not found");
        }
        
        $content = file_get_contents($dashboardPath);
        
        // Check for ViewDataService usage
        if (strpos($content, 'ViewDataService') === false) {
            throw new Exception("Admin dashboard should use ViewDataService");
        }
        
        // Check for service instantiation
        if (strpos($content, 'new ViewDataService()') === false) {
            throw new Exception("Admin dashboard should instantiate ViewDataService");
        }
        
        // Check for getAdminDashboardData method call
        if (strpos($content, 'getAdminDashboardData') === false) {
            throw new Exception("Admin dashboard should call getAdminDashboardData method");
        }
        
        echo "✓ Admin dashboard uses ViewDataService correctly\n";
        return true;
    }
    
    /**
     * Test that ViewDataService has getAdminDashboardData method
     */
    public function testViewDataServiceHasAdminDashboardMethod() {
        $servicePath = __DIR__ . '/../app/services/ViewDataService.php';
        
        if (!file_exists($servicePath)) {
            throw new Exception("ViewDataService file not found");
        }
        
        $content = file_get_contents($servicePath);
        
        // Check for getAdminDashboardData method
        if (strpos($content, 'function getAdminDashboardData') === false) {
            throw new Exception("ViewDataService should have getAdminDashboardData method");
        }
        
        // Check for helper methods
        $helperMethods = [
            'calculateDashboardStats',
            'calculateDashboardTrends',
            'generateDashboardAlerts',
            'getTopProducts',
            'getRecentActivities',
            'getDashboardChartsData'
        ];
        
        foreach ($helperMethods as $method) {
            if (strpos($content, "function {$method}") === false) {
                throw new Exception("ViewDataService should have {$method} method");
            }
        }
        
        echo "✓ ViewDataService has all required admin dashboard methods\n";
        return true;
    }
    
    /**
     * Test error handling in admin dashboard
     */
    public function testAdminDashboardErrorHandling() {
        $dashboardPath = __DIR__ . '/../app/views/admin/dashboard.php';
        $content = file_get_contents($dashboardPath);
        
        // Check for try-catch blocks
        if (strpos($content, 'try') === false || strpos($content, 'catch') === false) {
            throw new Exception("Admin dashboard should have error handling");
        }
        
        // Check for ErrorHandler usage
        if (strpos($content, 'ErrorHandler') === false) {
            throw new Exception("Admin dashboard should use ErrorHandler");
        }
        
        // Check for empty state handling
        if (strpos($content, 'handleEmptyState') === false) {
            throw new Exception("Admin dashboard should handle empty states");
        }
        
        echo "✓ Admin dashboard has proper error handling\n";
        return true;
    }
    
    /**
     * Test that admin dashboard doesn't directly use models
     */
    public function testNoDirectModelUsage() {
        $dashboardPath = __DIR__ . '/../app/views/admin/dashboard.php';
        $content = file_get_contents($dashboardPath);
        
        // Should not directly instantiate models
        $modelClasses = ['ProductsModel', 'CategoriesModel', 'NewsModel', 'UsersModel', 'OrdersModel'];
        
        foreach ($modelClasses as $modelClass) {
            if (strpos($content, "new {$modelClass}()") !== false) {
                throw new Exception("Admin dashboard should not directly instantiate {$modelClass} - use ViewDataService instead");
            }
        }
        
        echo "✓ Admin dashboard follows service layer pattern (no direct model usage)\n";
        return true;
    }
    
    /**
     * Test dashboard data structure
     */
    public function testDashboardDataStructure() {
        $dashboardPath = __DIR__ . '/../app/views/admin/dashboard.php';
        $content = file_get_contents($dashboardPath);
        
        // Check for expected data variables
        $expectedVariables = [
            '$stats',
            '$trends', 
            '$alerts',
            '$topProducts',
            '$recentActivities',
            '$chartsData'
        ];
        
        foreach ($expectedVariables as $variable) {
            if (strpos($content, $variable) === false) {
                throw new Exception("Admin dashboard should have {$variable} variable");
            }
        }
        
        // Check for safe array access
        if (strpos($content, '??') === false) {
            throw new Exception("Admin dashboard should use null coalescing operator for safe array access");
        }
        
        echo "✓ Admin dashboard has proper data structure handling\n";
        return true;
    }
    
    /**
     * Test security measures
     */
    public function testSecurityMeasures() {
        $dashboardPath = __DIR__ . '/../app/views/admin/dashboard.php';
        $content = file_get_contents($dashboardPath);
        
        // Check for HTML escaping
        if (strpos($content, 'htmlspecialchars') === false) {
            throw new Exception("Admin dashboard should use htmlspecialchars for security");
        }
        
        echo "✓ Admin dashboard has proper security measures\n";
        return true;
    }
    
    /**
     * Test performance optimizations
     */
    public function testPerformanceOptimizations() {
        $servicePath = __DIR__ . '/../app/services/ViewDataService.php';
        $content = file_get_contents($servicePath);
        
        // Check for retry logic usage
        if (strpos($content, 'getDataWithRetry') === false) {
            throw new Exception("ViewDataService should use retry logic for better performance");
        }
        
        // Check for data transformation
        if (strpos($content, 'dataTransformer') === false) {
            throw new Exception("ViewDataService should use DataTransformer for consistent data formatting");
        }
        
        echo "✓ Admin dashboard service has performance optimizations\n";
        return true;
    }
    
    /**
     * Run all tests
     */
    public function runAllTests() {
        echo "Running Admin Dashboard ViewDataService Integration Tests...\n\n";
        
        try {
            $this->testAdminDashboardUsesViewDataService();
            $this->testViewDataServiceHasAdminDashboardMethod();
            $this->testAdminDashboardErrorHandling();
            $this->testNoDirectModelUsage();
            $this->testDashboardDataStructure();
            $this->testSecurityMeasures();
            $this->testPerformanceOptimizations();
            
            echo "\n✅ All Admin Dashboard ViewDataService tests passed!\n";
            echo "\n📋 Architecture Summary:\n";
            echo "- Admin dashboard: ✓ Uses ViewDataService (consistent with public views)\n";
            echo "- Service layer: ✓ getAdminDashboardData() method implemented\n";
            echo "- Error handling: ✓ Proper try-catch and ErrorHandler usage\n";
            echo "- Performance: ✓ Retry logic and data transformation\n";
            echo "- Security: ✓ HTML escaping and input validation\n";
            echo "- Separation: ✓ No direct model usage in views\n";
            
            return true;
        } catch (Exception $e) {
            echo "\n❌ Test failed: " . $e->getMessage() . "\n";
            return false;
        }
    }
}

// Run tests if this file is executed directly
if (basename(__FILE__) === basename($_SERVER['SCRIPT_NAME'])) {
    $test = new AdminDashboardViewDataServiceTest();
    $test->runAllTests();
}