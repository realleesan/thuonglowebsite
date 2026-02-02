<?php
/**
 * Unit Tests for Environment Detection
 * Basic tests to verify environment detection works correctly
 */

require_once __DIR__ . '/../config.php';

class EnvironmentTest {
    
    /**
     * Test environment detection with hosting domain
     */
    public function testHostingEnvironmentDetection() {
        // Backup original SERVER values
        $originalHost = $_SERVER['HTTP_HOST'] ?? null;
        
        // Set hosting domain
        $_SERVER['HTTP_HOST'] = 'test1.web3b.com';
        
        $environment = detect_environment();
        
        // Restore original values
        if ($originalHost !== null) {
            $_SERVER['HTTP_HOST'] = $originalHost;
        } else {
            unset($_SERVER['HTTP_HOST']);
        }
        
        return $environment === 'hosting';
    }
    
    /**
     * Test environment detection with localhost
     */
    public function testLocalEnvironmentDetection() {
        // Backup original SERVER values
        $originalHost = $_SERVER['HTTP_HOST'] ?? null;
        
        // Test localhost
        $_SERVER['HTTP_HOST'] = 'localhost';
        $environment1 = detect_environment();
        
        // Test 127.0.0.1
        $_SERVER['HTTP_HOST'] = '127.0.0.1';
        $environment2 = detect_environment();
        
        // Test .local domain
        $_SERVER['HTTP_HOST'] = 'thuonglo.local';
        $environment3 = detect_environment();
        
        // Restore original values
        if ($originalHost !== null) {
            $_SERVER['HTTP_HOST'] = $originalHost;
        } else {
            unset($_SERVER['HTTP_HOST']);
        }
        
        return $environment1 === 'local' && 
               $environment2 === 'local' && 
               $environment3 === 'local';
    }
    
    /**
     * Test environment detection consistency
     */
    public function testEnvironmentDetectionConsistency() {
        // Backup original SERVER values
        $originalHost = $_SERVER['HTTP_HOST'] ?? null;
        
        // Set test domain
        $_SERVER['HTTP_HOST'] = 'test1.web3b.com';
        
        // Run detection multiple times
        $env1 = detect_environment();
        $env2 = detect_environment();
        $env3 = detect_environment();
        
        // Restore original values
        if ($originalHost !== null) {
            $_SERVER['HTTP_HOST'] = $originalHost;
        } else {
            unset($_SERVER['HTTP_HOST']);
        }
        
        return $env1 === $env2 && $env2 === $env3;
    }
    
    /**
     * Test default environment fallback
     */
    public function testDefaultEnvironmentFallback() {
        // Backup original SERVER values
        $originalHost = $_SERVER['HTTP_HOST'] ?? null;
        
        // Set unknown domain
        $_SERVER['HTTP_HOST'] = 'unknown-domain.com';
        
        $environment = detect_environment();
        
        // Restore original values
        if ($originalHost !== null) {
            $_SERVER['HTTP_HOST'] = $originalHost;
        } else {
            unset($_SERVER['HTTP_HOST']);
        }
        
        // Should default to hosting for safety
        return $environment === 'hosting';
    }
    
    /**
     * Run all tests
     */
    public function runAllTests() {
        $tests = [
            'Hosting Environment Detection' => $this->testHostingEnvironmentDetection(),
            'Local Environment Detection' => $this->testLocalEnvironmentDetection(),
            'Environment Detection Consistency' => $this->testEnvironmentDetectionConsistency(),
            'Default Environment Fallback' => $this->testDefaultEnvironmentFallback(),
        ];
        
        echo "<h1>Environment Detection Tests</h1>";
        
        $passed = 0;
        $total = count($tests);
        
        foreach ($tests as $testName => $result) {
            $status = $result ? 'PASS' : 'FAIL';
            $color = $result ? 'green' : 'red';
            echo "<p style='color: $color;'>$testName: <strong>$status</strong></p>";
            
            if ($result) {
                $passed++;
            }
        }
        
        echo "<hr>";
        echo "<p><strong>Results: $passed/$total tests passed</strong></p>";
        
        return $passed === $total;
    }
}

// Run tests if this file is accessed directly
if (basename($_SERVER['PHP_SELF']) === 'EnvironmentTest.php') {
    $test = new EnvironmentTest();
    $allPassed = $test->runAllTests();
    
    if ($allPassed) {
        echo "<p style='color: green; font-weight: bold;'>All tests passed! ✅</p>";
    } else {
        echo "<p style='color: red; font-weight: bold;'>Some tests failed! ❌</p>";
    }
}
?>