<?php
/**
 * View Security Helper
 * Đảm bảo data được escape và validate trước khi hiển thị
 */

class ViewSecurityHelper {
    
    /**
     * Escape HTML để tránh XSS
     */
    public function escapeHtml($data): string {
        if (is_null($data)) {
            return '';
        }
        
        return htmlspecialchars((string) $data, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }
    
    /**
     * Validate và sanitize user input
     */
    public function sanitizeInput($input): string {
        if (is_null($input)) {
            return '';
        }
        
        // Remove null bytes
        $input = str_replace(chr(0), '', $input);
        
        // Trim whitespace
        $input = trim($input);
        
        // Remove control characters except tab, newline, and carriage return
        $input = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $input);
        
        return $input;
    }
    
    /**
     * Format monetary values safely
     */
    public function formatMoney($amount): string {
        if (!is_numeric($amount)) {
            return '0đ';
        }
        
        $amount = (float) $amount;
        
        // Format with thousands separator
        $formatted = number_format($amount, 0, ',', '.');
        
        return $formatted . 'đ';
    }
    
    /**
     * Validate email format
     */
    public function validateEmail($email): bool {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
    
    /**
     * Validate phone number (Vietnamese format)
     */
    public function validatePhone($phone): bool {
        // Remove all non-digit characters
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        // Check Vietnamese phone number patterns
        $patterns = [
            '/^(03|05|07|08|09)[0-9]{8}$/',  // Mobile numbers
            '/^(024|028|0[2-9][0-9])[0-9]{7,8}$/'  // Landline numbers
        ];
        
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $phone)) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Validate and sanitize URL
     */
    public function sanitizeUrl($url): string {
        if (empty($url)) {
            return '';
        }
        
        // Add protocol if missing
        if (!preg_match('/^https?:\/\//', $url)) {
            $url = 'http://' . $url;
        }
        
        // Validate URL
        $sanitized = filter_var($url, FILTER_SANITIZE_URL);
        
        if (filter_var($sanitized, FILTER_VALIDATE_URL)) {
            return $sanitized;
        }
        
        return '';
    }
    
    /**
     * Clean and validate integer
     */
    public function sanitizeInt($value, $min = null, $max = null): int {
        $value = filter_var($value, FILTER_SANITIZE_NUMBER_INT);
        $value = (int) $value;
        
        if ($min !== null && $value < $min) {
            $value = $min;
        }
        
        if ($max !== null && $value > $max) {
            $value = $max;
        }
        
        return $value;
    }
    
    /**
     * Clean and validate float
     */
    public function sanitizeFloat($value, $min = null, $max = null): float {
        $value = filter_var($value, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
        $value = (float) $value;
        
        if ($min !== null && $value < $min) {
            $value = $min;
        }
        
        if ($max !== null && $value > $max) {
            $value = $max;
        }
        
        return $value;
    }
    
    /**
     * Validate data type
     */
    public function validateDataType($value, $expectedType): bool {
        switch ($expectedType) {
            case 'string':
                return is_string($value);
            case 'int':
            case 'integer':
                return is_int($value) || (is_string($value) && ctype_digit($value));
            case 'float':
            case 'double':
                return is_float($value) || is_numeric($value);
            case 'bool':
            case 'boolean':
                return is_bool($value) || in_array($value, [0, 1, '0', '1', 'true', 'false']);
            case 'array':
                return is_array($value);
            case 'email':
                return $this->validateEmail($value);
            case 'phone':
                return $this->validatePhone($value);
            case 'url':
                return filter_var($value, FILTER_VALIDATE_URL) !== false;
            default:
                return true;
        }
    }
    
    /**
     * Sanitize array of data
     */
    public function sanitizeArray($data, $rules = []): array {
        if (!is_array($data)) {
            return [];
        }
        
        $sanitized = [];
        
        foreach ($data as $key => $value) {
            $key = $this->sanitizeInput($key);
            
            if (isset($rules[$key])) {
                $rule = $rules[$key];
                
                switch ($rule['type'] ?? 'string') {
                    case 'string':
                        $sanitized[$key] = $this->sanitizeInput($value);
                        break;
                    case 'int':
                        $sanitized[$key] = $this->sanitizeInt($value, $rule['min'] ?? null, $rule['max'] ?? null);
                        break;
                    case 'float':
                        $sanitized[$key] = $this->sanitizeFloat($value, $rule['min'] ?? null, $rule['max'] ?? null);
                        break;
                    case 'email':
                        $sanitized[$key] = $this->validateEmail($value) ? $this->sanitizeInput($value) : '';
                        break;
                    case 'phone':
                        $sanitized[$key] = $this->validatePhone($value) ? $this->sanitizeInput($value) : '';
                        break;
                    case 'url':
                        $sanitized[$key] = $this->sanitizeUrl($value);
                        break;
                    default:
                        $sanitized[$key] = $this->sanitizeInput($value);
                }
            } else {
                // Default sanitization
                if (is_array($value)) {
                    $sanitized[$key] = $this->sanitizeArray($value);
                } else {
                    $sanitized[$key] = $this->sanitizeInput($value);
                }
            }
        }
        
        return $sanitized;
    }
    
    /**
     * Generate CSRF token
     */
    public function generateCsrfToken(): string {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $token = bin2hex(random_bytes(32));
        $_SESSION['csrf_token'] = $token;
        
        return $token;
    }
    
    /**
     * Verify CSRF token
     */
    public function verifyCsrfToken($token): bool {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }
    
    /**
     * Escape data for JSON output
     */
    public function escapeJson($data): string {
        return json_encode($data, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE);
    }
    
    /**
     * Clean filename for safe file operations
     */
    public function sanitizeFilename($filename): string {
        // Remove path traversal attempts
        $filename = basename($filename);
        
        // Remove dangerous characters
        $filename = preg_replace('/[^a-zA-Z0-9._-]/', '', $filename);
        
        // Limit length
        $filename = substr($filename, 0, 255);
        
        return $filename;
    }
}