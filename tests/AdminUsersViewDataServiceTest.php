<?php
/**
 * Test Admin Users Views with ViewDataService Integration
 * Validates that admin user management views work correctly with ViewDataService
 */

require_once __DIR__ . '/../app/services/ViewDataService.php';
require_once __DIR__ . '/../app/services/ErrorHandler.php';

class AdminUsersViewDataServiceTest {
    private $viewDataService;
    private $testResults = [];
    
    public function __construct() {
        $this->viewDataService = new ViewDataService();
    }
    
    public function runAllTests() {
        echo "=== Admin Users ViewDataService Integration Tests ===\n\n";
        
        $this->testAdminUsersDataRetrieval();
        $this->testAdminUserDetailsData();
        $this->testUsersDataPagination();
        $this->testUsersDataFiltering();
        $this->testEmptyStateHandling();
        
        $this->printResults();
    }
    
    /**
     * Test admin users data retrieval
     */
    private function testAdminUsersDataRetrieval() {
        echo "Testing admin users data retrieval...\n";
        
        try {
            $usersData = $this->viewDataService->getAdminUsersData(1, 10, []);
            
            // Check structure
            $requiredKeys = ['users', 'pagination', 'filters', 'total'];
            foreach ($requiredKeys as $key) {
                if (!array_key_exists($key, $usersData)) {
                    throw new Exception("Missing key: {$key}");
                }
            }
            
            // Check pagination structure
            $paginationKeys = ['current_page', 'per_page', 'total', 'last_page'];
            foreach ($paginationKeys as $key) {
                if (!array_key_exists($key, $usersData['pagination'])) {
                    throw new Exception("Missing pagination key: {$key}");
                }
            }
            
            $this->testResults[] = ['test' => 'Admin Users Data Retrieval', 'status' => 'PASS'];
            echo "✓ Admin users data structure is correct\n";
            
        } catch (Exception $e) {
            $this->testResults[] = ['test' => 'Admin Users Data Retrieval', 'status' => 'FAIL', 'error' => $e->getMessage()];
            echo "✗ Admin users data retrieval failed: " . $e->getMessage() . "\n";
        }
    }
    
    /**
     * Test admin user details data
     */
    private function testAdminUserDetailsData() {
        echo "Testing admin user details data...\n";
        
        try {
            // Test with a valid user ID (assuming user ID 1 exists)
            $userDetailsData = $this->viewDataService->getAdminUserDetailsData(1);
            
            // Check structure
            if (!array_key_exists('user', $userDetailsData)) {
                throw new Exception("Missing 'user' key in user details data");
            }
            
            // If user exists, check user data structure
            if ($userDetailsData['user'] !== null) {
                $user = $userDetailsData['user'];
                $requiredUserKeys = ['id', 'name', 'email'];
                foreach ($requiredUserKeys as $key) {
                    if (!array_key_exists($key, $user)) {
                        throw new Exception("Missing user key: {$key}");
                    }
                }
                echo "✓ User details data structure is correct\n";
            } else {
                echo "✓ User details correctly returns null for non-existent user\n";
            }
            
            $this->testResults[] = ['test' => 'Admin User Details Data', 'status' => 'PASS'];
            
        } catch (Exception $e) {
            $this->testResults[] = ['test' => 'Admin User Details Data', 'status' => 'FAIL', 'error' => $e->getMessage()];
            echo "✗ Admin user details data failed: " . $e->getMessage() . "\n";
        }
    }
    
    /**
     * Test users data pagination
     */
    private function testUsersDataPagination() {
        echo "Testing users data pagination...\n";
        
        try {
            // Test different page sizes
            $page1Data = $this->viewDataService->getAdminUsersData(1, 5, []);
            $page2Data = $this->viewDataService->getAdminUsersData(2, 5, []);
            
            // Check pagination calculations
            if ($page1Data['pagination']['current_page'] !== 1) {
                throw new Exception("Page 1 current_page should be 1");
            }
            
            if ($page2Data['pagination']['current_page'] !== 2) {
                throw new Exception("Page 2 current_page should be 2");
            }
            
            if ($page1Data['pagination']['per_page'] !== 5) {
                throw new Exception("Per page should be 5");
            }
            
            $this->testResults[] = ['test' => 'Users Data Pagination', 'status' => 'PASS'];
            echo "✓ Users pagination works correctly\n";
            
        } catch (Exception $e) {
            $this->testResults[] = ['test' => 'Users Data Pagination', 'status' => 'FAIL', 'error' => $e->getMessage()];
            echo "✗ Users pagination failed: " . $e->getMessage() . "\n";
        }
    }
    
    /**
     * Test users data filtering
     */
    private function testUsersDataFiltering() {
        echo "Testing users data filtering...\n";
        
        try {
            // Test search filter
            $searchFilters = ['search' => 'test'];
            $searchData = $this->viewDataService->getAdminUsersData(1, 10, $searchFilters);
            
            if ($searchData['filters']['search'] !== 'test') {
                throw new Exception("Search filter not preserved");
            }
            
            // Test role filter
            $roleFilters = ['role' => 'admin'];
            $roleData = $this->viewDataService->getAdminUsersData(1, 10, $roleFilters);
            
            if ($roleData['filters']['role'] !== 'admin') {
                throw new Exception("Role filter not preserved");
            }
            
            // Test status filter
            $statusFilters = ['status' => 'active'];
            $statusData = $this->viewDataService->getAdminUsersData(1, 10, $statusFilters);
            
            if ($statusData['filters']['status'] !== 'active') {
                throw new Exception("Status filter not preserved");
            }
            
            $this->testResults[] = ['test' => 'Users Data Filtering', 'status' => 'PASS'];
            echo "✓ Users filtering works correctly\n";
            
        } catch (Exception $e) {
            $this->testResults[] = ['test' => 'Users Data Filtering', 'status' => 'FAIL', 'error' => $e->getMessage()];
            echo "✗ Users filtering failed: " . $e->getMessage() . "\n";
        }
    }
    
    /**
     * Test empty state handling
     */
    private function testEmptyStateHandling() {
        echo "Testing empty state handling...\n";
        
        try {
            // Test with non-existent user ID
            $nonExistentUserData = $this->viewDataService->getAdminUserDetailsData(99999);
            
            if ($nonExistentUserData['user'] !== null) {
                throw new Exception("Non-existent user should return null");
            }
            
            // Test empty users list structure
            $emptyUsersData = $this->viewDataService->getAdminUsersData(999, 10, []);
            
            if (!is_array($emptyUsersData['users'])) {
                throw new Exception("Users should always be an array");
            }
            
            if ($emptyUsersData['total'] < 0) {
                throw new Exception("Total should never be negative");
            }
            
            $this->testResults[] = ['test' => 'Empty State Handling', 'status' => 'PASS'];
            echo "✓ Empty state handling works correctly\n";
            
        } catch (Exception $e) {
            $this->testResults[] = ['test' => 'Empty State Handling', 'status' => 'FAIL', 'error' => $e->getMessage()];
            echo "✗ Empty state handling failed: " . $e->getMessage() . "\n";
        }
    }
    
    /**
     * Print test results summary
     */
    private function printResults() {
        echo "\n=== Test Results Summary ===\n";
        
        $passed = 0;
        $failed = 0;
        
        foreach ($this->testResults as $result) {
            $status = $result['status'] === 'PASS' ? '✓' : '✗';
            echo "{$status} {$result['test']}: {$result['status']}";
            
            if ($result['status'] === 'FAIL' && isset($result['error'])) {
                echo " - {$result['error']}";
            }
            echo "\n";
            
            if ($result['status'] === 'PASS') {
                $passed++;
            } else {
                $failed++;
            }
        }
        
        echo "\nTotal: " . count($this->testResults) . " tests\n";
        echo "Passed: {$passed}\n";
        echo "Failed: {$failed}\n";
        
        if ($failed === 0) {
            echo "\n🎉 All admin users ViewDataService integration tests passed!\n";
        } else {
            echo "\n⚠️  Some tests failed. Please review the errors above.\n";
        }
    }
}

// Run tests if called directly
if (basename(__FILE__) === basename($_SERVER['SCRIPT_NAME'])) {
    $test = new AdminUsersViewDataServiceTest();
    $test->runAllTests();
}