<?php
/**
 * Test Admin Users Views Structure (No Database Required)
 * Validates that admin user management views have correct structure and use ViewDataService
 */

class AdminUsersViewStructureTest {
    private $testResults = [];
    
    public function runAllTests() {
        echo "=== Admin Users View Structure Tests ===\n\n";
        
        $this->testViewFilesExist();
        $this->testViewDataServiceUsage();
        $this->testErrorHandlingUsage();
        
        $this->printResults();
    }
    
    /**
     * Test that all admin user view files exist
     */
    private function testViewFilesExist() {
        echo "Testing admin user view files existence...\n";
        
        $viewFiles = [
            'app/views/admin/users/index.php',
            'app/views/admin/users/view.php',
            'app/views/admin/users/edit.php',
            'app/views/admin/users/add.php'
        ];
        
        $missingFiles = [];
        foreach ($viewFiles as $file) {
            if (!file_exists($file)) {
                $missingFiles[] = $file;
            }
        }
        
        if (empty($missingFiles)) {
            $this->testResults[] = ['test' => 'View Files Existence', 'status' => 'PASS'];
            echo "✓ All admin user view files exist\n";
        } else {
            $this->testResults[] = ['test' => 'View Files Existence', 'status' => 'FAIL', 'error' => 'Missing files: ' . implode(', ', $missingFiles)];
            echo "✗ Missing view files: " . implode(', ', $missingFiles) . "\n";
        }
    }
    
    /**
     * Test that views use ViewDataService
     */
    private function testViewDataServiceUsage() {
        echo "Testing ViewDataService usage in views...\n";
        
        $viewsToCheck = [
            'app/views/admin/users/index.php' => ['ViewDataService', 'getAdminUsersData'],
            'app/views/admin/users/view.php' => ['ViewDataService', 'getAdminUserDetailsData'],
            'app/views/admin/users/edit.php' => ['ViewDataService', 'getAdminUserDetailsData']
        ];
        
        $errors = [];
        
        foreach ($viewsToCheck as $file => $requiredElements) {
            if (!file_exists($file)) {
                $errors[] = "File {$file} does not exist";
                continue;
            }
            
            $content = file_get_contents($file);
            
            foreach ($requiredElements as $element) {
                if (strpos($content, $element) === false) {
                    $errors[] = "File {$file} does not contain {$element}";
                }
            }
        }
        
        if (empty($errors)) {
            $this->testResults[] = ['test' => 'ViewDataService Usage', 'status' => 'PASS'];
            echo "✓ All views correctly use ViewDataService\n";
        } else {
            $this->testResults[] = ['test' => 'ViewDataService Usage', 'status' => 'FAIL', 'error' => implode('; ', $errors)];
            echo "✗ ViewDataService usage issues: " . implode('; ', $errors) . "\n";
        }
    }
    
    /**
     * Test that views use ErrorHandler
     */
    private function testErrorHandlingUsage() {
        echo "Testing ErrorHandler usage in views...\n";
        
        $viewsToCheck = [
            'app/views/admin/users/index.php',
            'app/views/admin/users/view.php',
            'app/views/admin/users/edit.php',
            'app/views/admin/users/add.php'
        ];
        
        $errors = [];
        
        foreach ($viewsToCheck as $file) {
            if (!file_exists($file)) {
                $errors[] = "File {$file} does not exist";
                continue;
            }
            
            $content = file_get_contents($file);
            
            if (strpos($content, 'ErrorHandler') === false) {
                $errors[] = "File {$file} does not use ErrorHandler";
            }
        }
        
        if (empty($errors)) {
            $this->testResults[] = ['test' => 'ErrorHandler Usage', 'status' => 'PASS'];
            echo "✓ All views correctly use ErrorHandler\n";
        } else {
            $this->testResults[] = ['test' => 'ErrorHandler Usage', 'status' => 'FAIL', 'error' => implode('; ', $errors)];
            echo "✗ ErrorHandler usage issues: " . implode('; ', $errors) . "\n";
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
            echo "\n🎉 All admin users view structure tests passed!\n";
        } else {
            echo "\n⚠️  Some tests failed. Please review the errors above.\n";
        }
    }
}

// Run tests if called directly
if (basename(__FILE__) === basename($_SERVER['SCRIPT_NAME'])) {
    $test = new AdminUsersViewStructureTest();
    $test->runAllTests();
}