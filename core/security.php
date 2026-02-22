<?php
/**
 * Security Configuration
 * Handles HTTPS enforcement and security headers
 */

// Prevent direct access
if (!defined('THUONGLO_INIT')) {
    die('Direct access not permitted');
}

/**
 * Force HTTPS on hosting environment
 */
function force_https() {
    global $config;
    
    // Only force HTTPS in hosting environment
    if (!isset($config['url']['force_https']) || !$config['url']['force_https']) {
        return;
    }
    
    // Check if already on HTTPS
    $is_https = false;
    
    if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
        $is_https = true;
    } elseif (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443) {
        $is_https = true;
    } elseif (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
        $is_https = true;
    } elseif (isset($_SERVER['HTTP_X_FORWARDED_SSL']) && $_SERVER['HTTP_X_FORWARDED_SSL'] === 'on') {
        $is_https = true;
    }
    
    // Redirect to HTTPS if not already
    if (!$is_https) {
        $redirect_url = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        header('HTTP/1.1 301 Moved Permanently');
        header('Location: ' . $redirect_url);
        exit();
    }
}

/**
 * Set security headers
 */
function set_security_headers() {
    global $config;
    
    // Only set strict headers in hosting environment
    if (isset($config['url']['force_https']) && $config['url']['force_https']) {
        // Force HTTPS for all future requests (1 year)
        header('Strict-Transport-Security: max-age=31536000; includeSubDomains; preload');
        
        // Note: CSP is now handled by SecurityHeaders service
        // header('Content-Security-Policy: upgrade-insecure-requests');
    }
    
    // General security headers (for all environments)
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    header('X-XSS-Protection: 1; mode=block');
    header('Referrer-Policy: strict-origin-when-cross-origin');
}

// Execute security functions
force_https();
set_security_headers();
