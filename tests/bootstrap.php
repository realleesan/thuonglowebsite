<?php

// Bootstrap file for PHPUnit tests

// Set error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Define constants
define('TEST_MODE', true);

// Set up autoloader (simple version since we don't have composer autoload)
spl_autoload_register(function ($class) {
    // Convert namespace to file path
    $file = str_replace('\\', DIRECTORY_SEPARATOR, $class) . '.php';
    
    // Look in app directory
    $appFile = __DIR__ . '/../app/' . $file;
    if (file_exists($appFile)) {
        require_once $appFile;
        return;
    }
    
    // Look in tests directory
    $testFile = __DIR__ . '/' . $file;
    if (file_exists($testFile)) {
        require_once $testFile;
        return;
    }
});

// Include required files
require_once __DIR__ . '/../core/database.php';