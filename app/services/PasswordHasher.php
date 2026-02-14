<?php
/**
 * PasswordHasher Service
 * Handles password hashing, verification and reset token generation
 * Implements secure password hashing using PHP's password_hash()
 * Requirements: 1.4, 3.1, 3.2
 */

class PasswordHasher {
    private string $algorithm;
    private array $options;
    private int $tokenLength;
    private int $tokenLifetime;
    
    public function __construct() {
        // Load configuration
        $config = include 'config.php';
        
        $this->algorithm = $config['security']['password_hash_algo'] ?? PASSWORD_DEFAULT;
        $this->options = [
            'cost' => $config['security']['password_hash_cost'] ?? 12,
        ];
        $this->tokenLength = 32; // 32 bytes = 64 hex characters
        $this->tokenLifetime = $config['security']['reset_token_lifetime'] ?? 3600; // 1 hour
    }
    
    /**
     * Hash a password using secure algorithm
     * Implements secure password hashing per Requirement 1.4
     */
    public function hash(string $password): string {
        if (empty($password)) {
            throw new InvalidArgumentException('Password cannot be empty');
        }
        
        $hash = password_hash($password, $this->algorithm, $this->options);
        
        if ($hash === false) {
            throw new RuntimeException('Password hashing failed');
        }
        
        return $hash;
    }
    
    /**
     * Verify password against hash with timing-safe comparison
     * Implements secure password verification per Requirement 1.4
     */
    public function verify(string $password, string $hash): bool {
        if (empty($password) || empty($hash)) {
            return false;
        }
        
        return password_verify($password, $hash);
    }
    
    /**
     * Check if hash needs rehashing (algorithm or cost changed)
     * Ensures passwords are rehashed when security parameters change
     */
    public function needsRehash(string $hash): bool {
        if (empty($hash)) {
            return true;
        }
        
        return password_needs_rehash($hash, $this->algorithm, $this->options);
    }
    
    /**
     * Rehash password if needed
     * Updates password hash when security parameters change
     */
    public function rehashIfNeeded(string $password, string $currentHash): ?string {
        if ($this->needsRehash($currentHash)) {
            return $this->hash($password);
        }
        
        return null;
    }
    
    /**
     * Generate secure reset token
     * Implements secure token generation per Requirements 3.1, 3.2
     */
    public function generateResetToken(): string {
        try {
            $token = bin2hex(random_bytes($this->tokenLength));
            
            if (strlen($token) !== ($this->tokenLength * 2)) {
                throw new RuntimeException('Token generation failed - invalid length');
            }
            
            return $token;
        } catch (Exception $e) {
            throw new RuntimeException('Failed to generate secure reset token: ' . $e->getMessage());
        }
    }
    
    /**
     * Verify reset token (timing-safe comparison)
     * Implements secure token verification per Requirements 3.1, 3.2
     */
    public function verifyResetToken(string $token, string $storedToken): bool {
        if (empty($token) || empty($storedToken)) {
            return false;
        }
        
        // Ensure both tokens are same length to prevent timing attacks
        if (strlen($token) !== strlen($storedToken)) {
            return false;
        }
        
        return hash_equals($storedToken, $token);
    }
    
    /**
     * Generate secure session token
     * Creates cryptographically secure tokens for session management
     */
    public function generateSessionToken(): string {
        try {
            return bin2hex(random_bytes($this->tokenLength));
        } catch (Exception $e) {
            throw new RuntimeException('Failed to generate secure session token: ' . $e->getMessage());
        }
    }
    
    /**
     * Generate password reset token with expiration
     * Creates token data structure for password reset functionality
     */
    public function generatePasswordResetData(string $email): array {
        $token = $this->generateResetToken();
        $expiresAt = date('Y-m-d H:i:s', time() + $this->tokenLifetime);
        
        return [
            'token' => $token,
            'email' => $email,
            'expires_at' => $expiresAt,
            'created_at' => date('Y-m-d H:i:s'),
        ];
    }
    
    /**
     * Validate password reset token data
     * Checks token validity and expiration
     */
    public function validateResetTokenData(array $tokenData, string $providedToken): array {
        $result = [
            'valid' => false,
            'expired' => false,
            'used' => false,
            'message' => ''
        ];
        
        // Check if token exists
        if (empty($tokenData)) {
            $result['message'] = 'Token không tồn tại';
            return $result;
        }
        
        // Check if token was already used
        if (!empty($tokenData['used_at'])) {
            $result['used'] = true;
            $result['message'] = 'Token đã được sử dụng';
            return $result;
        }
        
        // Check if token is expired
        if (strtotime($tokenData['expires_at']) < time()) {
            $result['expired'] = true;
            $result['message'] = 'Token đã hết hạn';
            return $result;
        }
        
        // Verify token
        if (!$this->verifyResetToken($providedToken, $tokenData['token'])) {
            $result['message'] = 'Token không hợp lệ';
            return $result;
        }
        
        $result['valid'] = true;
        $result['message'] = 'Token hợp lệ';
        return $result;
    }
    
    /**
     * Generate secure random string for various purposes
     */
    public function generateSecureRandomString(int $length = 32): string {
        try {
            return bin2hex(random_bytes($length));
        } catch (Exception $e) {
            throw new RuntimeException('Failed to generate secure random string: ' . $e->getMessage());
        }
    }
    
    /**
     * Hash sensitive data (not passwords)
     * For hashing non-password sensitive data like tokens
     */
    public function hashSensitiveData(string $data, string $salt = ''): string {
        if (empty($salt)) {
            $salt = $this->generateSecureRandomString(16);
        }
        
        return hash('sha256', $salt . $data) . ':' . $salt;
    }
    
    /**
     * Verify sensitive data hash
     */
    public function verifySensitiveDataHash(string $data, string $hash): bool {
        $parts = explode(':', $hash);
        if (count($parts) !== 2) {
            return false;
        }
        
        [$storedHash, $salt] = $parts;
        $computedHash = hash('sha256', $salt . $data);
        
        return hash_equals($storedHash, $computedHash);
    }
}