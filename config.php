<?php
/**
 * Configuration File for Thuong Lo Website
 * Handles environment detection and base configuration
 */

// Prevent direct access
if (!defined('THUONGLO_INIT')) {
    define('THUONGLO_INIT', true);
}

/**
 * Environment Detection Function
 * Automatically detects local vs hosting environment
 */
if (!function_exists('detect_environment')) {
    function detect_environment() {
        // Check for hosting indicators
        if (isset($_SERVER['HTTP_HOST'])) {
            $host = $_SERVER['HTTP_HOST'];
            
            // Check for specific hosting domain
            if (strpos($host, 'test1.web3b.com') !== false) {
                return 'hosting';
            }
            
            // Check for local development indicators
            if (in_array($host, ['localhost', '127.0.0.1']) ||
                strpos($host, '.local') !== false ||
                strpos($host, '.test') !== false ||
                strpos($host, 'localhost:') !== false) {
                return 'local';
            }
        }
        
        // Default to hosting for safety
        return 'hosting';
    }
}

// Detect current environment
$environment = detect_environment();

// Configuration array
$config = [
    'app' => [
        'name' => 'Thuong Lo',
        'environment' => $environment,
        'debug' => ($environment === 'local'), // Debug only in local
        'timezone' => 'Asia/Ho_Chi_Minh',
        'charset' => 'UTF-8',
    ],
    
    'url' => [
        'base' => 'auto', // Will be auto-detected
        'force_https' => ($environment === 'hosting'), // Force HTTPS in hosting
        'www_redirect' => 'non-www', // Redirect www to non-www
        'remove_index_php' => true, // Remove index.php from URLs
    ],
    
    'paths' => [
        'assets' => 'assets/',
        'uploads' => 'uploads/',
        'cache' => 'cache/',
        'logs' => 'logs/',
        'views' => 'app/views/',
        'controllers' => 'app/controllers/',
        'models' => 'app/models/',
    ],
    
    'database' => [
        'host' => 'localhost',
        'name' => 'test1_thuonglowebsite',
        'username' => 'test1_thuonglowebsite',
        'password' => '21042005nhat',
        'charset' => 'utf8mb4',
        'options' => [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ],
    ],
    
    'security' => [
        'session_name' => 'THUONGLO_SESSION',
        'csrf_protection' => true,
        'password_hash_algo' => PASSWORD_DEFAULT,
    ],
    
    'performance' => [
        'cache_assets' => ($environment === 'hosting'),
        'minify_assets' => ($environment === 'hosting'),
        'gzip_compression' => true,
    ],
    
    'error_reporting' => [
        'level' => ($environment === 'local') ? E_ALL : E_ERROR,
        'display_errors' => ($environment === 'local'),
        'log_errors' => true,
        'log_file' => 'logs/error.log',
    ]
];

// Set error reporting based on environment
error_reporting($config['error_reporting']['level']);
ini_set('display_errors', $config['error_reporting']['display_errors'] ? 1 : 0);
ini_set('log_errors', $config['error_reporting']['log_errors'] ? 1 : 0);

// Set timezone
if (function_exists('date_default_timezone_set')) {
    date_default_timezone_set($config['app']['timezone']);
}

// Return configuration
return $config;