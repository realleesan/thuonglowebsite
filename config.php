<?php
/**
 * Configuration File for Thuong Lo Website
 * Handles environment detection and base configuration
 */

// Prevent direct access
if (!defined('THUONGLO_INIT')) {
    define('THUONGLO_INIT', true);
}

// Load environment variables from .env file
require_once __DIR__ . '/core/env.php';

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
        'debug' => true, // Debug only in local - enabled for testing
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
        'session_name' => 'THUONGLO_AUTH_SESSION',
        'session_lifetime' => 3600, // 1 hour
        'session_timeout' => 1800, // 30 minutes inactivity
        'csrf_protection' => true,
        'password_hash_algo' => PASSWORD_DEFAULT,
        'password_hash_cost' => 12,
        'max_login_attempts' => 5,
        'lockout_duration' => 900, // 15 minutes
        'reset_token_lifetime' => 3600, // 1 hour
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
    ],
    
    // SePay Payment Gateway Configuration
    'sepay' => [
        'enabled' => true,
        'api_key' => Env::get('SEPAY_API_KEY', 'YOUR_SEPAY_API_KEY_HERE'),
        'api_secret' => Env::get('SEPAY_API_SECRET', 'YOUR_SEPAY_API_SECRET_HERE'),
        'account_number' => Env::get('SEPAY_ACCOUNT_NUMBER', 'YOUR_ACCOUNT_NUMBER_HERE'),
        'account_name' => 'THUONG LO',
        'bank_code' => 'MB', // MB Bank, VCB, TCB, etc.
        'api_url' => 'https://my.sepay.vn/userapi',
        'webhook_secret' => Env::get('SEPAY_WEBHOOK_SECRET', 'YOUR_WEBHOOK_SECRET_HERE'),
        'payment_timeout' => 120, // 120 seconds (2 minutes)
        'qr_timeout' => 120, // QR code expiration time
        'order_prefix' => 'DH', // Order prefix: DH[OrderId]
        'withdrawal_prefix' => 'RUT', // Withdrawal prefix: RUT[Code]
        'test_mode' => ($environment === 'local'), // Enable test mode in local
    ],
    
    // Commission & Wallet Configuration
    'commission' => [
        'enabled' => true,
        'default_rate' => 10.00, // 10% default commission rate
        'min_order_for_commission' => 0, // Minimum order amount to earn commission
        'auto_credit' => true, // Auto credit commission when order is paid
        'allow_negative_balance' => false, // Don't allow negative balance (for refunds)
    ],
    
    // Withdrawal Configuration
    'withdrawal' => [
        'enabled' => true,
        'min_amount' => 5000, // Minimum 5,000 VND
        'max_amount' => 50000000, // Maximum 50,000,000 VND per request
        'fee' => 0, // Withdrawal fee (0 = free)
        'fee_type' => 'fixed', // 'fixed' or 'percentage'
        'require_bank_verification' => true, // Require OTP when changing bank info
        'otp_expiry' => 300, // OTP expires in 5 minutes
        'auto_approve' => false, // Require admin approval
        'daily_limit' => 100000000, // Daily withdrawal limit per affiliate
        'monthly_limit' => 500000000, // Monthly withdrawal limit per affiliate
    ],
    
    // Email Configuration (PHPMailer)
    'email' => [
        'enabled' => true,
        'driver' => 'smtp', // 'smtp' or 'mail'
        'smtp_host' => Env::get('SMTP_HOST', 'smtp.gmail.com'),
        'smtp_port' => Env::get('SMTP_PORT', 587),
        'smtp_encryption' => 'tls', // 'tls' or 'ssl'
        'smtp_username' => Env::get('SMTP_USERNAME', 'your-email@gmail.com'),
        'smtp_password' => Env::get('SMTP_PASSWORD', 'your-app-password'),
        'from_email' => Env::get('MAIL_FROM_EMAIL', 'noreply@thuonglo.com'),
        'from_name' => Env::get('MAIL_FROM_NAME', 'Thuong Lo'),
        'reply_to' => 'support@thuonglo.com',
        'charset' => 'UTF-8',
        'timeout' => 30,
        // Email templates
        'templates' => [
            'order_confirmation' => 'emails/order_confirmation.php',
            'payment_success' => 'emails/payment_success.php',
            'payment_failed' => 'emails/payment_failed.php',
            'commission_earned' => 'emails/commission_earned.php',
            'withdrawal_request' => 'emails/withdrawal_request.php',
            'withdrawal_approved' => 'emails/withdrawal_approved.php',
            'withdrawal_completed' => 'emails/withdrawal_completed.php',
            'withdrawal_rejected' => 'emails/withdrawal_rejected.php',
            'bank_info_changed' => 'emails/bank_info_changed.php',
            'otp_verification' => 'emails/otp_verification.php',
        ],
    ],
    
    // Webhook Configuration
    'webhook' => [
        'enabled' => true,
        'verify_signature' => true,
        'log_all_webhooks' => true,
        'retry_failed' => true,
        'max_retries' => 3,
        'retry_delay' => 60, // seconds
        'allowed_ips' => [
            // SePay webhook IPs (update with actual IPs)
            '0.0.0.0', // Allow all for now, restrict in production
        ],
    ],
    
    // Logging Configuration
    'logging' => [
        'payment' => [
            'enabled' => true,
            'file' => 'logs/payment.log',
            'level' => 'info', // debug, info, warning, error
        ],
        'webhook' => [
            'enabled' => true,
            'file' => 'logs/webhook.log',
            'level' => 'debug',
        ],
        'commission' => [
            'enabled' => true,
            'file' => 'logs/commission.log',
            'level' => 'info',
        ],
        'withdrawal' => [
            'enabled' => true,
            'file' => 'logs/withdrawal.log',
            'level' => 'info',
        ],
    ],
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