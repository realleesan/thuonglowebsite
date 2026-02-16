<?php
/**
 * Email Configuration
 * Cấu hình email cho EmailNotificationService
 */

return [
    // SMTP Configuration
    'smtp_host' => 'smtp.gmail.com',
    'smtp_port' => 587,
    'smtp_username' => 'baominhkpkp@gmail.com', // Thay đổi thành email thực
    'smtp_password' => 'gjvz qdrq pogq sheb', // Thay đổi thành app password
    'smtp_secure' => 'tls', // Will be converted to PHPMailer constant in service
    'smtp_auth' => true,
    'use_smtp' => true,
    
    // Sender Information
    'from_email' => 'noreply@thuonglo.com',
    'from_name' => 'ThuongLo',
    'support_email' => 'support@thuonglo.com',
    
    // Email Settings
    'charset' => 'UTF-8',
    'timeout' => 30,
    
    // Template Settings
    'template_path' => __DIR__ . '/../app/views/emails/',
    
    // Development Settings
    'debug_mode' => false, // Set to true for debugging
    'log_emails' => true, // Log email sending attempts
];