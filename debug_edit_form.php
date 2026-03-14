<?php
/**
 * Debug file for edit.php - This should be included at the top of edit.php
 * to debug form submission issues
 */

// Start session if not started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Log file
$debugLogFile = __DIR__ . '/logs/edit_form_debug.log';

// Helper function to log
function debug_log($message) {
    global $debugLogFile;
    $logDir = dirname($debugLogFile);
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[$timestamp] $message\n";
    file_put_contents($debugLogFile, $logMessage, FILE_APPEND);
}

debug_log("=== NEW REQUEST ===");
debug_log("REQUEST_METHOD: " . ($_SERVER['REQUEST_METHOD'] ?? 'NOT SET'));
debug_log("QUERY_STRING: " . ($_SERVER['QUERY_STRING'] ?? 'NOT SET'));
debug_log("REQUEST_URI: " . ($_SERVER['REQUEST_URI'] ?? 'NOT SET'));

// Log POST data (sanitized)
if (!empty($_POST)) {
    $safePost = $_POST;
    // Remove sensitive data
    unset($safePost['password'], $safePost['csrf_token'], $safePost['token']);
    debug_log("POST data: " . print_r($safePost, true));
} else {
    debug_log("POST data: EMPTY");
}

// Log GET data
if (!empty($_GET)) {
    debug_log("GET data: " . print_r($_GET, true));
} else {
    debug_log("GET data: EMPTY");
}

// Log session
debug_log("Session product_saved: " . ($_SESSION['product_saved'] ?? 'NOT SET'));

// If this is a POST request, log what's happening
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dataAction = $_POST['data_action'] ?? '';
    debug_log("data_action value: '$dataAction'");
    debug_log("empty(data_action): " . (empty($dataAction) ? 'true' : 'false'));
    
    if (empty($dataAction)) {
        debug_log("Processing MAIN FORM SAVE (data_action is empty)");
        
        // Log form fields
        $name = $_POST['name'] ?? '';
        $category_id = $_POST['category_id'] ?? '';
        $price = $_POST['price'] ?? '';
        $description = $_POST['description'] ?? '';
        
        debug_log("name: '$name'");
        debug_log("category_id: '$category_id'");
        debug_log("price: '$price'");
        debug_log("description length: " . strlen($description));
        
        // Validation checks
        if (empty($name)) {
            debug_log("VALIDATION ERROR: name is empty");
        }
        if (empty($category_id) || $category_id <= 0) {
            debug_log("VALIDATION ERROR: category_id is invalid ($category_id)");
        }
        if (empty($price) || $price <= 0) {
            debug_log("VALIDATION ERROR: price is invalid ($price)");
        }
        if (empty($description)) {
            debug_log("VALIDATION ERROR: description is empty");
        }
    } else {
        debug_log("Processing DATA ACTION: $dataAction");
    }
}

debug_log("=== END DEBUG ===\n");
