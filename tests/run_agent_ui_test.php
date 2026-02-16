<?php

// Simple test runner for Agent Navigation UI Property Test
require_once __DIR__ . '/../config.php';

// Mock PHPUnit TestCase if not available
if (!class_exists('PHPUnit\Framework\TestCase')) {
    class TestCase {
        protected function setUp(): void {}
        protected function tearDown(): void {}
        
        protected function assertEquals($expected, $actual, $message = '') {
            if ($expected !== $actual) {
                throw new Exception($message ?: "Expected '$expected', got '$actual'");
            }
        }
        
        protected function assertTrue($condition, $message = '') {
            if (!$condition) {
                throw new Exception($message ?: "Assertion failed");
            }
        }
        
        protected function assertArrayHasKey($key, $array, $message = '') {
            if (!array_key_exists($key, $array)) {
                throw new Exception($message ?: "Array does not have key '$key'");
            }
        }
        
        protected function assertNotEmpty($value, $message = '') {
            if (empty($value)) {
                throw new Exception($message ?: "Value is empty");
            }
        }
        
        protected function assertContains($needle, $haystack, $message = '') {
            if (!in_array($needle, $haystack)) {
                throw new Exception($message ?: "Array does not contain '$needle'");
            }
        }
    }
}

// Include the test class
require_once __DIR__ . '/AgentNavigationUIPropertyTest.php';

// Run the tests
echo "Running Agent Navigation UI Property Tests...\n\n";

$test = new AgentNavigationUIPropertyTest();

$tests = [
    'testProperty1NavigationRedirectsWorkConsistently',
    'testProperty5PendingUsersSeeProcessingMessagesConsistently',
    'testNavigationBehaviorForAllUserStates',
    'testProcessingMessageConsistencyAcrossDurations'
];

$passed = 0;
$failed = 0;

foreach ($tests as $testMethod) {
    echo "Running $testMethod... ";
    
    try {
        $test->setUp();
        $test->$testMethod();
        $test->tearDown();
        echo "PASSED\n";
        $passed++;
    } catch (Exception $e) {
        echo "FAILED: " . $e->getMessage() . "\n";
        $failed++;
        try {
            $test->tearDown();
        } catch (Exception $cleanupError) {
            echo "  Cleanup error: " . $cleanupError->getMessage() . "\n";
        }
    }
}

echo "\n";
echo "Results: $passed passed, $failed failed\n";

if ($failed > 0) {
    echo "Some tests failed!\n";
    exit(1);
} else {
    echo "All tests passed!\n";
    exit(0);
}