<?php
/**
 * InputValidator Service
 * Handles input validation and sanitization for security
 * Requirements: 1.3, 7.1, 7.2, 7.3, 7.5
 */

require_once __DIR__ . '/SecurityException.php';
require_once __DIR__ . '/SecurityMonitor.php';

class InputValidator {
    private array $errors = [];
    private SecurityMonitor $securityMonitor;
    
    public function __construct() {
        $this->securityMonitor = new SecurityMonitor();
    }
    
    /**
     * Validate login data
     */
    public function validateLogin(array $data): array {
        $this->errors = [];
        
        // Validate login field (email or phone)
        if (empty($data['login'])) {
            $this->errors['login'] = 'Email hoặc số điện thoại là bắt buộc';
        } else {
            $login = $this->sanitizeInput($data['login']);
            if (!$this->validateEmail($login) && !$this->validatePhone($login)) {
                $this->errors['login'] = 'Email hoặc số điện thoại không hợp lệ';
            }
        }
        
        // Validate password
        if (empty($data['password'])) {
            $this->errors['password'] = 'Mật khẩu là bắt buộc';
        }
        
        return [
            'valid' => empty($this->errors),
            'errors' => $this->errors,
            'data' => $this->sanitizeLoginData($data)
        ];
    }
    
    /**
     * Validate registration data
     */
    public function validateRegister(array $data): array {
        $this->errors = [];
        
        // Validate name
        if (empty($data['name'])) {
            $this->errors['name'] = 'Họ tên là bắt buộc';
        } elseif (strlen(trim($data['name'])) < 2) {
            $this->errors['name'] = 'Họ tên phải có ít nhất 2 ký tự';
        }
        
        // Validate email
        if (empty($data['email'])) {
            $this->errors['email'] = 'Email là bắt buộc';
        } elseif (!$this->validateEmail($data['email'])) {
            $this->errors['email'] = 'Email không hợp lệ';
        }
        
        // Validate phone (optional)
        if (!empty($data['phone']) && !$this->validatePhone($data['phone'])) {
            $this->errors['phone'] = 'Số điện thoại không hợp lệ';
        }
        
        // Validate password
        $passwordValidation = $this->validatePassword($data['password'] ?? '');
        if (!$passwordValidation['valid']) {
            $this->errors['password'] = implode(', ', $passwordValidation['errors']);
        }
        
        // Validate password confirmation
        if (empty($data['password_confirmation'])) {
            $this->errors['password_confirmation'] = 'Xác nhận mật khẩu là bắt buộc';
        } elseif ($data['password'] !== $data['password_confirmation']) {
            $this->errors['password_confirmation'] = 'Mật khẩu xác nhận không khớp';
        }
        
        return [
            'valid' => empty($this->errors),
            'errors' => $this->errors,
            'data' => $this->sanitizeRegisterData($data)
        ];
    }
    
    /**
     * Validate password reset data
     */
    public function validatePasswordReset(array $data): array {
        $this->errors = [];
        
        // Validate email
        if (empty($data['email'])) {
            $this->errors['email'] = 'Email là bắt buộc';
        } elseif (!$this->validateEmail($data['email'])) {
            $this->errors['email'] = 'Email không hợp lệ';
        }
        
        return [
            'valid' => empty($this->errors),
            'errors' => $this->errors,
            'data' => ['email' => $this->sanitizeInput($data['email'] ?? '')]
        ];
    }
    
    /**
     * Validate new password data
     */
    public function validateNewPassword(array $data): array {
        $this->errors = [];
        
        // Validate token
        if (empty($data['token'])) {
            $this->errors['token'] = 'Token không hợp lệ';
        }
        
        // Validate password
        $passwordValidation = $this->validatePassword($data['password'] ?? '');
        if (!$passwordValidation['valid']) {
            $this->errors['password'] = implode(', ', $passwordValidation['errors']);
        }
        
        // Validate password confirmation
        if (empty($data['password_confirmation'])) {
            $this->errors['password_confirmation'] = 'Xác nhận mật khẩu là bắt buộc';
        } elseif ($data['password'] !== $data['password_confirmation']) {
            $this->errors['password_confirmation'] = 'Mật khẩu xác nhận không khớp';
        }
        
        return [
            'valid' => empty($this->errors),
            'errors' => $this->errors,
            'data' => [
                'token' => $this->sanitizeInput($data['token'] ?? ''),
                'password' => $data['password'] ?? ''
            ]
        ];
    }
    
    /**
     * Sanitize input to prevent XSS
     * Implements comprehensive input sanitization per Requirement 7.1
     */
    public function sanitizeInput(string $input): string {
        // Remove null bytes
        $input = str_replace(chr(0), '', $input);
        
        // Trim whitespace
        $input = trim($input);
        
        // Check for malicious patterns first and monitor
        $sqlInjectionDetected = $this->detectSqlInjection($input);
        $xssDetected = $this->detectXss($input);
        
        if ($sqlInjectionDetected) {
            // Monitor the attack attempt
            $this->securityMonitor->monitorInputValidation('general', $input, false);
            throw new SecurityException('SQL injection attempt detected');
        }
        
        if ($xssDetected) {
            // Monitor the attack attempt
            $this->securityMonitor->monitorInputValidation('general', $input, false);
            throw new SecurityException('XSS attempt detected');
        }
        
        // Monitor successful validation
        $this->securityMonitor->monitorInputValidation('general', $input, true);
        
        // Convert special characters to HTML entities
        $input = htmlspecialchars($input, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        
        return $input;
    }
    
    /**
     * Sanitize input for database operations
     */
    public function sanitizeForDatabase(string $input): string {
        // Remove null bytes and control characters
        $input = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $input);
        
        // Trim whitespace
        $input = trim($input);
        
        // Check for SQL injection patterns and monitor
        $sqlInjectionDetected = $this->detectSqlInjection($input);
        
        if ($sqlInjectionDetected) {
            // Monitor the attack attempt
            $this->securityMonitor->monitorInputValidation('database', $input, false);
            throw new SecurityException('SQL injection attempt detected');
        }
        
        // Monitor successful validation
        $this->securityMonitor->monitorInputValidation('database', $input, true);
        
        return $input;
    }
    
    /**
     * Sanitize input for HTML output
     */
    public function sanitizeForHtml(string $input): string {
        // Check for XSS patterns and monitor
        $xssDetected = $this->detectXss($input);
        
        if ($xssDetected) {
            // Monitor the attack attempt
            $this->securityMonitor->monitorInputValidation('html', $input, false);
            throw new SecurityException('XSS attempt detected');
        }
        
        // Monitor successful validation
        $this->securityMonitor->monitorInputValidation('html', $input, true);
        
        // Convert special characters
        return htmlspecialchars($input, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }
    
    /**
     * Validate email format
     */
    public function validateEmail(string $email): bool {
        $email = filter_var($email, FILTER_SANITIZE_EMAIL);
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
    
    /**
     * Validate password strength
     */
    public function validatePassword(string $password): array {
        $errors = [];
        
        if (strlen($password) < 8) {
            $errors[] = 'Mật khẩu phải có ít nhất 8 ký tự';
        }
        
        if (!preg_match('/[A-Z]/', $password)) {
            $errors[] = 'Mật khẩu phải có ít nhất 1 chữ hoa';
        }
        
        if (!preg_match('/[a-z]/', $password)) {
            $errors[] = 'Mật khẩu phải có ít nhất 1 chữ thường';
        }
        
        if (!preg_match('/[0-9]/', $password)) {
            $errors[] = 'Mật khẩu phải có ít nhất 1 số';
        }
        
        if (!preg_match('/[^A-Za-z0-9]/', $password)) {
            $errors[] = 'Mật khẩu phải có ít nhất 1 ký tự đặc biệt';
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }
    
    /**
     * Validate Vietnamese phone number
     */
    public function validatePhone(string $phone): bool {
        // Remove all non-digit characters
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        // Vietnamese phone patterns
        $patterns = [
            '/^(84|0)(3[2-9]|5[689]|7[06-9]|8[1-689]|9[0-46-9])[0-9]{7}$/', // Mobile
            '/^(84|0)(2[0-9])[0-9]{8}$/', // Landline
        ];
        
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $phone)) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Check for SQL injection patterns
     * Implements SQL injection protection per Requirement 7.2
     */
    public function detectSqlInjection(string $input): bool {
        $patterns = [
            // SQL keywords
            '/(\bUNION\b|\bSELECT\b|\bINSERT\b|\bUPDATE\b|\bDELETE\b|\bDROP\b|\bCREATE\b|\bALTER\b)/i',
            // SQL injection patterns
            '/(\bOR\b|\bAND\b)\s+\d+\s*=\s*\d+/i',
            '/(\bOR\b|\bAND\b)\s+[\'"]?\w+[\'"]?\s*=\s*[\'"]?\w+[\'"]?/i',
            // Quote patterns
            '/[\'";].*(\bOR\b|\bAND\b|\bUNION\b)/i',
            // Comment patterns
            '/--[^\r\n]*/i',
            '/\/\*.*?\*\//s',
            // Hex patterns
            '/0x[0-9a-f]+/i',
            // Function calls
            '/\b(EXEC|EXECUTE|SP_|XP_)\b/i',
            // Information schema
            '/\bINFORMATION_SCHEMA\b/i',
            // System tables
            '/\b(SYSOBJECTS|SYSCOLUMNS|SYSTABLES)\b/i',
        ];
        
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $input)) {
                // Log the attempt
                error_log("SQL Injection attempt detected: " . $input);
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Check for XSS patterns
     * Implements XSS protection per Requirement 7.3
     */
    public function detectXss(string $input): bool {
        $patterns = [
            // Script tags
            '/<script[^>]*>.*?<\/script>/is',
            '/<script[^>]*>/i',
            // Event handlers
            '/on\w+\s*=/i',
            // JavaScript protocol
            '/javascript:/i',
            // Data protocol
            '/data:/i',
            // VBScript
            '/vbscript:/i',
            // Iframe tags
            '/<iframe[^>]*>.*?<\/iframe>/is',
            '/<iframe[^>]*>/i',
            // Object/embed tags
            '/<(object|embed|applet)[^>]*>/i',
            // Form tags
            '/<form[^>]*>/i',
            // Meta refresh
            '/<meta[^>]*http-equiv[^>]*refresh/i',
            // Link tags with javascript
            '/<link[^>]*href[^>]*javascript:/i',
            // Style with expression
            '/style[^>]*expression\s*\(/i',
            // Import statements
            '/@import/i',
            // Base64 encoded scripts
            '/base64[^>]*script/i',
        ];
        
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $input)) {
                // Log the attempt
                error_log("XSS attempt detected: " . $input);
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Validate and sanitize array of inputs
     */
    public function sanitizeArray(array $inputs): array {
        $sanitized = [];
        
        foreach ($inputs as $key => $value) {
            if (is_string($value)) {
                $sanitized[$key] = $this->sanitizeInput($value);
            } elseif (is_array($value)) {
                $sanitized[$key] = $this->sanitizeArray($value);
            } else {
                $sanitized[$key] = $value;
            }
        }
        
        return $sanitized;
    }
    
    /**
     * Validate file upload
     */
    public function validateFileUpload(array $file, array $allowedTypes = [], int $maxSize = 2097152): array {
        $errors = [];
        
        // Check if file was uploaded
        if (!isset($file['tmp_name']) || empty($file['tmp_name'])) {
            $errors[] = 'Không có file được tải lên';
            return ['valid' => false, 'errors' => $errors];
        }
        
        // Check for upload errors
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errors[] = 'Lỗi khi tải file lên';
            return ['valid' => false, 'errors' => $errors];
        }
        
        // Check file size
        if ($file['size'] > $maxSize) {
            $errors[] = 'File quá lớn. Kích thước tối đa: ' . ($maxSize / 1024 / 1024) . 'MB';
        }
        
        // Check file type
        if (!empty($allowedTypes)) {
            $fileType = mime_content_type($file['tmp_name']);
            if (!in_array($fileType, $allowedTypes)) {
                $errors[] = 'Loại file không được phép';
            }
        }
        
        // Check for malicious content
        if ($this->detectMaliciousFile($file['tmp_name'])) {
            $errors[] = 'File chứa nội dung không an toàn';
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }
    
    /**
     * Detect malicious file content
     */
    private function detectMaliciousFile(string $filePath): bool {
        if (!file_exists($filePath)) {
            return true;
        }
        
        // Read first 1KB of file
        $content = file_get_contents($filePath, false, null, 0, 1024);
        
        if ($content === false) {
            return true;
        }
        
        // Check for script patterns
        $maliciousPatterns = [
            '/<\?php/i',
            '/<script/i',
            '/eval\s*\(/i',
            '/exec\s*\(/i',
            '/system\s*\(/i',
            '/shell_exec\s*\(/i',
            '/passthru\s*\(/i',
        ];
        
        foreach ($maliciousPatterns as $pattern) {
            if (preg_match($pattern, $content)) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Sanitize login data
     */
    private function sanitizeLoginData(array $data): array {
        return [
            'login' => $this->sanitizeInput($data['login'] ?? ''),
            'password' => $data['password'] ?? '', // Don't sanitize password
        ];
    }
    
    /**
     * Enhanced validation for all remaining edge cases
     * Requirement: 7.1
     */
    public function validateAndSanitizeInput(string $input, string $type = 'general'): array {
        $result = [
            'valid' => true,
            'sanitized' => '',
            'errors' => [],
            'threats_detected' => []
        ];
        
        try {
            // Check for null bytes and control characters
            if (strpos($input, "\0") !== false) {
                $result['threats_detected'][] = 'null_byte_injection';
                $result['valid'] = false;
                $result['errors'][] = 'Input contains null bytes';
            }
            
            // Check for directory traversal
            if ($this->detectDirectoryTraversal($input)) {
                $result['threats_detected'][] = 'directory_traversal';
                $result['valid'] = false;
                $result['errors'][] = 'Directory traversal attempt detected';
            }
            
            // Check for LDAP injection
            if ($this->detectLdapInjection($input)) {
                $result['threats_detected'][] = 'ldap_injection';
                $result['valid'] = false;
                $result['errors'][] = 'LDAP injection attempt detected';
            }
            
            // Check for XML injection
            if ($this->detectXmlInjection($input)) {
                $result['threats_detected'][] = 'xml_injection';
                $result['valid'] = false;
                $result['errors'][] = 'XML injection attempt detected';
            }
            
            // Check for NoSQL injection
            if ($this->detectNoSqlInjection($input)) {
                $result['threats_detected'][] = 'nosql_injection';
                $result['valid'] = false;
                $result['errors'][] = 'NoSQL injection attempt detected';
            }
            
            // Check for template injection
            if ($this->detectTemplateInjection($input)) {
                $result['threats_detected'][] = 'template_injection';
                $result['valid'] = false;
                $result['errors'][] = 'Template injection attempt detected';
            }
            
            // If threats detected, monitor them
            if (!empty($result['threats_detected'])) {
                foreach ($result['threats_detected'] as $threat) {
                    $this->securityMonitor->monitorInputValidation($type, $input, false);
                }
                return $result;
            }
            
            // Sanitize based on type
            switch ($type) {
                case 'email':
                    $result['sanitized'] = filter_var($input, FILTER_SANITIZE_EMAIL);
                    break;
                case 'url':
                    $result['sanitized'] = filter_var($input, FILTER_SANITIZE_URL);
                    break;
                case 'int':
                    $result['sanitized'] = filter_var($input, FILTER_SANITIZE_NUMBER_INT);
                    break;
                case 'float':
                    $result['sanitized'] = filter_var($input, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
                    break;
                case 'html':
                    $result['sanitized'] = $this->sanitizeForHtml($input);
                    break;
                case 'database':
                    $result['sanitized'] = $this->sanitizeForDatabase($input);
                    break;
                default:
                    $result['sanitized'] = $this->sanitizeInput($input);
            }
            
            // Monitor successful validation
            $this->securityMonitor->monitorInputValidation($type, $input, true);
            
        } catch (SecurityException $e) {
            $result['valid'] = false;
            $result['errors'][] = $e->getMessage();
            $result['threats_detected'][] = 'security_exception';
        }
        
        return $result;
    }
    
    /**
     * Detect directory traversal attempts
     */
    private function detectDirectoryTraversal(string $input): bool {
        $patterns = [
            '/\.\.\//',
            '/\.\.\\\\/',
            '/%2e%2e%2f/',
            '/%2e%2e\\\\/',
            '/\.\.\%2f/',
            '/\.\.\%5c/',
        ];
        
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $input)) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Detect LDAP injection attempts
     */
    private function detectLdapInjection(string $input): bool {
        $patterns = [
            '/\*\)/',
            '/\(\|/',
            '/\(&/',
            '/\(!\|/',
            '/\(\!\&/',
        ];
        
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $input)) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Detect XML injection attempts
     */
    private function detectXmlInjection(string $input): bool {
        $patterns = [
            '/<\?xml/i',
            '/<!DOCTYPE/i',
            '/<!ENTITY/i',
            '/&[a-zA-Z]+;/',
            '/<!\[CDATA\[/i',
        ];
        
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $input)) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Detect NoSQL injection attempts
     */
    private function detectNoSqlInjection(string $input): bool {
        $patterns = [
            '/\$where/i',
            '/\$ne/i',
            '/\$gt/i',
            '/\$lt/i',
            '/\$regex/i',
            '/\$or/i',
            '/\$and/i',
            '/\$not/i',
            '/\$nor/i',
            '/\$exists/i',
            '/\$in/i',
            '/\$nin/i',
        ];
        
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $input)) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Detect template injection attempts
     */
    private function detectTemplateInjection(string $input): bool {
        $patterns = [
            '/\{\{.*\}\}/',
            '/\{%.*%\}/',
            '/\$\{.*\}/',
            '/<\?.*\?>/',
            '/<%.*%>/',
        ];
        
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $input)) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Sanitize registration data
     */
    private function sanitizeRegisterData(array $data): array {
        return [
            'name' => $this->sanitizeInput($data['name'] ?? ''),
            'email' => $this->sanitizeInput($data['email'] ?? ''),
            'phone' => $this->sanitizeInput($data['phone'] ?? ''),
            'password' => $data['password'] ?? '', // Don't sanitize password
            'address' => $this->sanitizeInput($data['address'] ?? ''),
        ];
    }
}