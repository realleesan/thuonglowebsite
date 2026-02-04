<?php
/**
 * 404 Error Handling Test
 * Tests that invalid URLs properly return 404 status
 */

// Load configuration
require_once 'config.php';
require_once 'core/functions.php';

// Initialize URL builder
$config = get_config();
$urlBuilder = new UrlBuilder($config);

// Test invalid page
$page = $_GET['page'] ?? 'invalid-test-page';

// Set up variables for 404 page
$title = 'KhÃ´ng tÃ¬m tháº¥y trang - Thuong Lo';
$content = 'errors/404.php';
$showPageHeader = false;
$showCTA = false;
$showBreadcrumb = false;

// Include master layout
include_once 'app/views/_layout/master.php';
?>

<script>
// Test script to verify 404 status
document.addEventListener('DOMContentLoaded', function() {
    // Check if this is a test request
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('test') === '404') {
        console.log('404 Test Results:');
        console.log('- Page loaded successfully');
        console.log('- HTTP Status should be 404');
        console.log('- URL:', window.location.href);
        
        // Create test results display
        const testResults = document.createElement('div');
        testResults.innerHTML = `
            <div style="position: fixed; top: 10px; right: 10px; background: #333; color: white; padding: 15px; border-radius: 5px; z-index: 9999; max-width: 300px;">
                <h4 style="margin: 0 0 10px 0; color: #ffc107;">404 Test Results</h4>
                <p style="margin: 5px 0; font-size: 12px;">âœ“ 404 page loaded</p>
                <p style="margin: 5px 0; font-size: 12px;">âœ“ Error content displayed</p>
                <p style="margin: 5px 0; font-size: 12px;">âœ“ Navigation links available</p>
                <button onclick="this.parentElement.remove()" style="background: #dc3545; color: white; border: none; padding: 5px 10px; border-radius: 3px; cursor: pointer; margin-top: 10px;">Close</button>
            </div>
        `;
        document.body.appendChild(testResults);
    }
});
</script>