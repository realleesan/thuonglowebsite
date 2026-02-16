<?php
/**
 * SpamPreventionServiceTest - Unit tests for SpamPreventionService
 * Tests spam prevention logic and rate limiting
 */

require_once __DIR__ . '/../app/services/SpamPreventionService.php';

class SpamPreventionServiceTest {
    private SpamPreventionService $service;
    
    public function __construct() {
        $this->service = new SpamPreventionService();
        
        // Start session for testing
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }
    
    public function testRateLimitingForUser() {
        $userId = 123;
        
        // Initially should not be rate limited
        assert($this->service->isRateLimited($userId) === false, 'User should not be rate limited initially');
        
        // Record multiple submissions
        for ($i = 0; $i < 3; $i++) {
            $this->service->recordSubmission($userId);
        }
        
        // Should be rate limited after max attempts
        assert($this->service->isRateLimited($userId) === true, 'User should be rate limited after max attempts');
        
        echo "✓ User rate limiting tests passed\n";
    }
    
    public function testExistingPendingRequest() {
        // Mock user with pending request
        $userId = 456;
        
        // This would normally check database, but for testing we'll test the method exists
        $result = $this->service->hasExistingPendingRequest($userId);
        assert(is_bool($result), 'hasExistingPendingRequest should return boolean');
        
        echo "✓ Existing pending request tests passed\n";
    }
    
    public function testGetDataInterface() {
        // Test ServiceInterface implementation
        $result = $this->service->getData('checkRateLimit', ['user_id' => 789]);
        assert(isset($result['is_rate_limited']), 'getData should return rate limit status');
        assert(is_bool($result['is_rate_limited']), 'Rate limit status should be boolean');
        
        $result = $this->service->getData('checkExistingRequest', ['user_id' => 789]);
        assert(isset($result['has_existing_request']), 'getData should return existing request status');
        assert(is_bool($result['has_existing_request']), 'Existing request status should be boolean');
        
        echo "✓ ServiceInterface implementation tests passed\n";
    }
    
    public function testRateLimitStatus() {
        $userId = 999;
        
        // Get rate limit status
        $status = $this->service->getRateLimitStatus($userId);
        
        assert(isset($status['hourly_attempts']), 'Status should include hourly attempts');
        assert(isset($status['daily_attempts']), 'Status should include daily attempts');
        assert(isset($status['max_hourly']), 'Status should include max hourly limit');
        assert(isset($status['max_daily']), 'Status should include max daily limit');
        assert(isset($status['is_rate_limited']), 'Status should include rate limited flag');
        
        echo "✓ Rate limit status tests passed\n";
    }
    
    public function testResetRateLimit() {
        $userId = 888;
        
        // Record some attempts
        $this->service->recordSubmission($userId);
        $this->service->recordSubmission($userId);
        
        // Reset rate limit
        $result = $this->service->resetRateLimit($userId);
        assert($result === true, 'Reset rate limit should return true');
        
        // Should not be rate limited after reset
        assert($this->service->isRateLimited($userId) === false, 'User should not be rate limited after reset');
        
        echo "✓ Reset rate limit tests passed\n";
    }
    
    public function testErrorHandling() {
        // Test error handling
        $result = $this->service->getData('invalid_method', []);
        assert(isset($result['error']), 'Invalid method should return error');
        assert($result['error'] === true, 'Error flag should be true');
        
        echo "✓ Error handling tests passed\n";
    }
    
    public function runAllTests() {
        echo "Running SpamPreventionService tests...\n";
        $this->testRateLimitingForUser();
        $this->testExistingPendingRequest();
        $this->testGetDataInterface();
        $this->testRateLimitStatus();
        $this->testResetRateLimit();
        $this->testErrorHandling();
        echo "All SpamPreventionService tests passed! ✓\n";
    }
}

// Run tests if called directly
if (basename(__FILE__) === basename($_SERVER['SCRIPT_NAME'])) {
    $test = new SpamPreventionServiceTest();
    $test->runAllTests();
}