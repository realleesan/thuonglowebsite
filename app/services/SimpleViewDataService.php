<?php
/**
 * Simple View Data Service - Minimal version for login page
 */

class SimpleViewDataService {
    
    public function __construct() {
        // Minimal constructor - no model initialization
    }
    
    /**
     * Authenticate user for login
     */
    public function authenticateUser($login, $password) {
        try {
            // Simple authentication without full model system
            require_once __DIR__ . '/../../core/database.php';
            
            $db = Database::getInstance();
            
            // Check if login is email or phone
            $field = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'phone';
            
            $stmt = $db->table('users')
                      ->where($field, $login)
                      ->where('status', 'active')
                      ->first();
            
            if ($stmt && password_verify($password, $stmt['password'])) {
                return [
                    'success' => true,
                    'user' => $stmt
                ];
            }
            
            return ['success' => false, 'message' => 'Invalid credentials'];
            
        } catch (Exception $e) {
            error_log("Auth error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Authentication failed'];
        }
    }
    
    /**
     * Register new user
     */
    public function registerUser($userData) {
        try {
            require_once __DIR__ . '/../../core/database.php';
            
            $db = Database::getInstance();
            
            // Check if user exists
            $existing = $db->table('users')
                          ->where('email', $userData['email'])
                          ->orWhere('phone', $userData['phone'])
                          ->first();
            
            if ($existing) {
                return ['success' => false, 'message' => 'User already exists'];
            }
            
            // Hash password
            $hashedPassword = password_hash($userData['password'], PASSWORD_DEFAULT);
            
            // Insert user
            $userId = $db->table('users')->insert([
                'name' => $userData['name'],
                'email' => $userData['email'],
                'phone' => $userData['phone'],
                'password' => $hashedPassword,
                'role' => 'user',
                'status' => 'active',
                'created_at' => date('Y-m-d H:i:s')
            ]);
            
            if ($userId) {
                $user = $db->table('users')->find($userId);
                return [
                    'success' => true,
                    'user' => $user
                ];
            }
            
            return ['success' => false, 'message' => 'Registration failed'];
            
        } catch (Exception $e) {
            error_log("Registration error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Registration failed'];
        }
    }
    
    /**
     * Get auth login page data
     */
    public function getAuthLoginData() {
        return [
            'page_title' => 'Đăng nhập vào hệ thống',
            'form_action' => '?page=login',
            'remembered_phone' => $_COOKIE['remember_phone'] ?? '',
            'remembered_role' => $_COOKIE['remember_role'] ?? ''
        ];
    }
    
    /**
     * Get home page data - minimal version
     */
    public function getHomePageData() {
        return [
            'featured_products' => [],
            'categories' => [],
            'latest_news' => [],
            'stats' => [
                'total_products' => 0,
                'total_users' => 0,
                'total_orders' => 0
            ]
        ];
    }
    
    /**
     * Get categories data - minimal version
     */
    public function getCategoriesPageData() {
        return [
            'categories' => [],
            'featured_categories' => []
        ];
    }
    
    /**
     * Get products data - minimal version
     */
    public function getProductsPageData() {
        return [
            'products' => [],
            'categories' => [],
            'pagination' => [
                'current_page' => 1,
                'total_pages' => 1,
                'total_items' => 0
            ]
        ];
    }
    
    /**
     * Fallback method for any missing methods
     */
    public function __call($method, $args) {
        // Log the missing method call
        error_log("SimpleViewDataService: Missing method '$method' called");
        
        // Return empty array for get* methods
        if (strpos($method, 'get') === 0) {
            return [];
        }
        
        // Return false for other methods
        return false;
    }
}
?>