<?php
/**
 * Unit Tests for UrlBuilder Class
 * Tests URL generation functionality
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../core/UrlBuilder.php';

class UrlBuilderTest {
    
    private $testConfig;
    
    public function __construct() {
        // Create test configuration
        $this->testConfig = [
            'app' => [
                'environment' => 'hosting',
                'debug' => false,
            ],
            'url' => [
                'force_https' => true,
                'www_redirect' => 'non-www',
                'remove_index_php' => true,
            ],
            'paths' => [
                'assets' => 'assets/',
                'uploads' => 'uploads/',
            ],
            'performance' => [
                'cache_assets' => true,
            ]
        ];
    }
    
    /**
     * Test URL Builder initialization
     */
    public function testUrlBuilderInitialization() {
        $urlBuilder = new UrlBuilder($this->testConfig);
        
        return $urlBuilder instanceof UrlBuilder &&
               $urlBuilder->getEnvironment() === 'hosting';
    }
    
    /**
     * Test base URL generation
     */
    public function testBaseUrlGeneration() {
        // Backup original SERVER values
        $originalHost = $_SERVER['HTTP_HOST'] ?? null;
        $originalHttps = $_SERVER['HTTPS'] ?? null;
        
        // Set test values for hosting environment
        $_SERVER['HTTP_HOST'] = 'test1.web3b.com';
        $_SERVER['HTTPS'] = 'on';
        
        $urlBuilder = new UrlBuilder($this->testConfig);
        $baseUrl = $urlBuilder->getBaseUrl();
        
        // Restore original values
        if ($originalHost !== null) {
            $_SERVER['HTTP_HOST'] = $originalHost;
        } else {
            unset($_SERVER['HTTP_HOST']);
        }
        
        if ($originalHttps !== null) {
            $_SERVER['HTTPS'] = $originalHttps;
        } else {
            unset($_SERVER['HTTPS']);
        }
        
        // Should generate HTTPS URL for hosting environment
        return strpos($baseUrl, 'https://test1.web3b.com/') === 0;
    }
    
    /**
     * Test asset URL generation
     */
    public function testAssetUrlGeneration() {
        // Backup original SERVER values
        $originalHost = $_SERVER['HTTP_HOST'] ?? null;
        
        // Set test values
        $_SERVER['HTTP_HOST'] = 'test1.web3b.com';
        
        $urlBuilder = new UrlBuilder($this->testConfig);
        
        $cssUrl = $urlBuilder->asset('css/main.css');
        $jsUrl = $urlBuilder->asset('js/main.js');
        $imgUrl = $urlBuilder->asset('images/logo.png');
        
        // Restore original values
        if ($originalHost !== null) {
            $_SERVER['HTTP_HOST'] = $originalHost;
        } else {
            unset($_SERVER['HTTP_HOST']);
        }
        
        // Check if URLs contain expected components
        $cssValid = (strpos($cssUrl, 'test1.web3b.com/assets/css/main.css') !== false);
        $jsValid = (strpos($jsUrl, 'test1.web3b.com/assets/js/main.js') !== false);
        $imgValid = (strpos($imgUrl, 'test1.web3b.com/assets/images/logo.png') !== false);
        
        return $cssValid && $jsValid && $imgValid;
    }
    
    /**
     * Test page URL generation
     */
    public function testPageUrlGeneration() {
        // Backup original SERVER values
        $originalHost = $_SERVER['HTTP_HOST'] ?? null;
        
        // Set test values
        $_SERVER['HTTP_HOST'] = 'test1.web3b.com';
        
        $urlBuilder = new UrlBuilder($this->testConfig);
        
        $pageUrl = $urlBuilder->page('products');
        $pageUrlWithParams = $urlBuilder->page('products', ['category' => 'electronics']);
        
        // Restore original values
        if ($originalHost !== null) {
            $_SERVER['HTTP_HOST'] = $originalHost;
        } else {
            unset($_SERVER['HTTP_HOST']);
        }
        
        // Debug: Let's see what we actually get
        $expectedPageUrl = 'https://test1.web3b.com/?page=products';
        $expectedPageUrlWithParams = 'https://test1.web3b.com/?page=products&category=electronics';
        
        // Check if URLs are valid and contain expected components
        $pageUrlValid = (strpos($pageUrl, 'https://test1.web3b.com/') === 0) && 
                       (strpos($pageUrl, 'page=products') !== false);
        
        $pageUrlWithParamsValid = (strpos($pageUrlWithParams, 'https://test1.web3b.com/') === 0) && 
                                 (strpos($pageUrlWithParams, 'page=products') !== false) &&
                                 (strpos($pageUrlWithParams, 'category=electronics') !== false);
        
        return $pageUrlValid && $pageUrlWithParamsValid;
    }
    
    /**
     * Test www redirect handling
     */
    public function testWwwRedirectHandling() {
        // Backup original SERVER values
        $originalHost = $_SERVER['HTTP_HOST'] ?? null;
        
        // Test non-www redirect
        $_SERVER['HTTP_HOST'] = 'www.test1.web3b.com';
        
        $urlBuilder = new UrlBuilder($this->testConfig);
        $baseUrl = $urlBuilder->getBaseUrl();
        
        // Restore original values
        if ($originalHost !== null) {
            $_SERVER['HTTP_HOST'] = $originalHost;
        } else {
            unset($_SERVER['HTTP_HOST']);
        }
        
        // Should remove www and contain test1.web3b.com (not www.test1.web3b.com)
        return (strpos($baseUrl, 'test1.web3b.com/') !== false) && 
               (strpos($baseUrl, 'www.test1.web3b.com') === false);
    }
    
    /**
     * Test environment detection methods
     */
    public function testEnvironmentMethods() {
        $urlBuilder = new UrlBuilder($this->testConfig);
        
        return $urlBuilder->isHosting() === true &&
               $urlBuilder->isLocal() === false &&
               $urlBuilder->getEnvironment() === 'hosting';
    }
    
    /**
     * Test URL validation
     */
    public function testUrlValidation() {
        // Backup original SERVER values
        $originalHost = $_SERVER['HTTP_HOST'] ?? null;
        
        // Set test values
        $_SERVER['HTTP_HOST'] = 'test1.web3b.com';
        
        $urlBuilder = new UrlBuilder($this->testConfig);
        
        $testUrls = [
            $urlBuilder->url(),
            $urlBuilder->url('test-path'),
            $urlBuilder->asset('css/main.css'),
            $urlBuilder->page('products'),
        ];
        
        // Restore original values
        if ($originalHost !== null) {
            $_SERVER['HTTP_HOST'] = $originalHost;
        } else {
            unset($_SERVER['HTTP_HOST']);
        }
        
        // All URLs should be valid
        foreach ($testUrls as $url) {
            if (filter_var($url, FILTER_VALIDATE_URL) === false) {
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Run all tests
     */
    public function runAllTests() {
        $tests = [
            'UrlBuilder Initialization' => $this->testUrlBuilderInitialization(),
            'Base URL Generation' => $this->testBaseUrlGeneration(),
            'Asset URL Generation' => $this->testAssetUrlGeneration(),
            'Page URL Generation' => $this->testPageUrlGeneration(),
            'WWW Redirect Handling' => $this->testWwwRedirectHandling(),
            'Environment Methods' => $this->testEnvironmentMethods(),
            'URL Validation' => $this->testUrlValidation(),
        ];
        
        echo "<h1>UrlBuilder Tests</h1>";
        
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
if (basename($_SERVER['PHP_SELF']) === 'UrlBuilderTest.php') {
    $test = new UrlBuilderTest();
    $allPassed = $test->runAllTests();
    
    if ($allPassed) {
        echo "<p style='color: green; font-weight: bold;'>All tests passed! ✅</p>";
    } else {
        echo "<p style='color: red; font-weight: bold;'>Some tests failed! ❌</p>";
    }
}
?>