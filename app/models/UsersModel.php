<?php
/**
 * Users Model
 * Handles user data operations with database
 */

require_once __DIR__ . '/BaseModel.php';

class UsersModel extends BaseModel {
    protected $table = 'users';
    protected $fillable = [
        'name', 'username', 'email', 'phone', 'password', 'role', 'status', 
        'address', 'avatar', 'points', 'level', 'agent_request_status', 'agent_request_date'
    ];
    protected $hidden = ['password', 'remember_token'];
    
    /**
     * Authenticate user login with enhanced security features
     * Requirements: 2.5, 3.1, 3.2, 3.3, 8.2
     */
    public function authenticate($login, $password) {
        // Check for rate limiting first
        if ($this->isAccountLocked($login)) {
            throw new Exception('Account is temporarily locked due to too many failed attempts');
        }
        
        // Login can be email, phone, or username
        $user = $this->db->query(
            "SELECT *, agent_request_status FROM {$this->table} WHERE (email = ? OR phone = ? OR username = ?) AND status = 'active'",
            [$login, $login, $login]
        );
        
        if (empty($user)) {
            $this->incrementFailedLogins($login);
            return false;
        }
        
        $user = $user[0];
        
        if (password_verify($password, $user['password'])) {
            // Reset failed login attempts on successful login
            $this->resetFailedLogins($login);
            
            // Update last login time
            $this->updateLastLogin($user['id']);
            
            return $this->hideFields($user);
        } else {
            // Increment failed login attempts
            $this->incrementFailedLogins($login);
            return false;
        }
    }
    
    /**
     * Register new user with enhanced security
     * Requirements: 1.1, 1.2, 1.4, 8.2
     */
    public function register($data) {
        // Check if email already exists
        if ($this->emailExists($data['email'])) {
            throw new Exception('Email already exists');
        }
        
        // Check if username already exists (if provided)
        if (!empty($data['username']) && $this->usernameExists($data['username'])) {
            throw new Exception('Username already exists');
        }
        
        // Check if phone already exists (if provided)
        if (!empty($data['phone']) && $this->phoneExists($data['phone'])) {
            throw new Exception('Phone number already exists');
        }
        
        // Use PasswordHasher for secure hashing
        require_once __DIR__ . '/../services/PasswordHasher.php';
        $passwordHasher = new PasswordHasher();
        $data['password'] = $passwordHasher->hash($data['password']);
        
        // Set default values
        $data['role'] = $data['role'] ?? 'user';
        $data['status'] = $data['status'] ?? 'active';
        $data['points'] = 0;
        $data['level'] = 'Bronze';
        
        $userId = $this->create($data);
        
        if ($userId) {
            // Get the created user
            $user = $this->find($userId);
            return $this->hideFields($user);
        }
        
        return false;
    }
    
    /**
     * Update user password (legacy method - use updatePasswordSecure for new code)
     */
    public function updatePassword($userId, $newPassword) {
        return $this->updatePasswordSecure($userId, $newPassword, true);
    }
    
    /**
     * Get users by role
     */
    public function getByRole($role) {
        $users = $this->where('role', $role)->get();
        return $this->hideFields($users);
    }
    
    /**
     * Get active users
     */
    public function getActive() {
        $users = $this->where('status', 'active')->get();
        return $this->hideFields($users);
    }
    
    /**
     * Search users
     */
    public function searchUsers($query) {
        return $this->search($query, ['name', 'email', 'phone']);
    }
    
    /**
     * Get user statistics
     */
    public function getStats() {
        $stats = [];
        
        // Total users
        $stats['total'] = $this->count();
        
        // Users by role
        $roles = ['admin', 'user', 'agent'];
        foreach ($roles as $role) {
            $count = $this->db->query("SELECT COUNT(*) as count FROM {$this->table} WHERE role = ?", [$role]);
            $stats['by_role'][$role] = $count[0]['count'] ?? 0;
        }
        
        // Users by status
        $statuses = ['active', 'inactive', 'banned'];
        foreach ($statuses as $status) {
            $count = $this->db->query("SELECT COUNT(*) as count FROM {$this->table} WHERE status = ?", [$status]);
            $stats['by_status'][$status] = $count[0]['count'] ?? 0;
        }
        
        // Recent registrations (last 30 days)
        $recentCount = $this->db->query(
            "SELECT COUNT(*) as count FROM {$this->table} WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)"
        );
        $stats['recent_registrations'] = $recentCount[0]['count'] ?? 0;
        
        return $stats;
    }
    
    /**
     * Update user points
     */
    public function addPoints($userId, $points) {
        $user = $this->find($userId);
        if (!$user) {
            return false;
        }
        
        $newPoints = $user['points'] + $points;
        $newLevel = $this->calculateLevel($newPoints);
        
        return $this->update($userId, [
            'points' => $newPoints,
            'level' => $newLevel
        ]);
    }
    
    /**
     * Calculate user level based on points
     */
    private function calculateLevel($points) {
        if ($points >= 10000) return 'Diamond';
        if ($points >= 5000) return 'Platinum';
        if ($points >= 2000) return 'Gold';
        if ($points >= 500) return 'Silver';
        return 'Bronze';
    }
    
    /**
     * Get user with orders count
     */
    public function getUserWithOrdersCount($userId) {
        $sql = "
            SELECT u.*, 
                   COUNT(o.id) as orders_count,
                   COALESCE(SUM(o.total), 0) as total_spent
            FROM {$this->table} u
            LEFT JOIN orders o ON u.id = o.user_id
            WHERE u.id = ?
            GROUP BY u.id
        ";
        
        $result = $this->db->query($sql, [$userId]);
        return $result ? $this->hideFields($result[0]) : null;
    }
    
    /**
     * Override find to hide sensitive fields
     */
    public function find($id, $columns = '*') {
        $user = parent::find($id, $columns);
        return $user ? $this->hideFields($user) : null;
    }
    
    public function findById($id, $columns = '*') {
        return $this->find($id, $columns);
    }
    
    /**
     * Override all to hide sensitive fields
     */
    public function all($columns = '*') {
        $users = parent::all($columns);
        return $this->hideFields($users);
    }
    
    // ========== Authentication Enhancement Methods ==========
    
    /**
     * Create password reset token for user
     * Requirements: 3.1, 3.2
     */
    public function createPasswordResetToken($email): ?string {
        // Check if user exists
        $user = $this->findBy('email', $email);
        if (!$user) {
            return null;
        }
        
        // Generate secure token
        $token = bin2hex(random_bytes(32));
        $expiresAt = date('Y-m-d H:i:s', time() + 3600); // 1 hour
        
        // Insert token into password_reset_tokens table
        $sql = "INSERT INTO password_reset_tokens (email, token, expires_at, created_at) VALUES (?, ?, ?, NOW())";
        $result = $this->db->query($sql, [$email, $token, $expiresAt]);
        
        return $result ? $token : null;
    }
    
    /**
     * Validate password reset token
     * Requirements: 3.1, 3.2, 3.3
     */
    public function validatePasswordResetToken($token): ?array {
        $sql = "SELECT * FROM password_reset_tokens WHERE token = ? AND used_at IS NULL AND expires_at > NOW()";
        $result = $this->db->query($sql, [$token]);
        
        return $result ? $result[0] : null;
    }
    
    /**
     * Clear password reset token (mark as used)
     * Requirements: 3.2, 3.3
     */
    public function clearPasswordResetToken($userId): bool {
        // Get user email
        $user = $this->find($userId);
        if (!$user) {
            return false;
        }
        
        // Mark token as used
        $sql = "UPDATE password_reset_tokens SET used_at = NOW() WHERE email = ? AND used_at IS NULL";
        return $this->db->query($sql, [$user['email']]) !== false;
    }
    
    /**
     * Update last login time
     * Requirements: 2.5, 8.2
     */
    public function updateLastLogin($userId): bool {
        $sql = "UPDATE {$this->table} SET updated_at = NOW() WHERE id = ?";
        return $this->db->query($sql, [$userId]) !== false;
    }
    
    /**
     * Increment failed login attempts
     * Requirements: 2.5
     */
    public function incrementFailedLogins($identifier): void {
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        
        // Check if record exists
        $existing = $this->db->query(
            "SELECT * FROM login_attempts WHERE identifier = ? AND ip_address = ?",
            [$identifier, $ipAddress]
        );
        
        if ($existing) {
            // Update existing record
            $attempts = $existing[0]['attempts'] + 1;
            $lockedUntil = null;
            
            // Lock account if too many attempts
            if ($attempts >= 5) {
                $lockedUntil = date('Y-m-d H:i:s', time() + 900); // 15 minutes
            }
            
            $sql = "UPDATE login_attempts SET attempts = ?, last_attempt = NOW(), locked_until = ?, updated_at = NOW() WHERE id = ?";
            $this->db->query($sql, [$attempts, $lockedUntil, $existing[0]['id']]);
        } else {
            // Create new record
            $sql = "INSERT INTO login_attempts (identifier, ip_address, attempts, last_attempt, created_at) VALUES (?, ?, 1, NOW(), NOW())";
            $this->db->query($sql, [$identifier, $ipAddress]);
        }
    }
    
    /**
     * Reset failed login attempts
     * Requirements: 2.5
     */
    public function resetFailedLogins($identifier): void {
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        
        $sql = "DELETE FROM login_attempts WHERE identifier = ? AND ip_address = ?";
        $this->db->query($sql, [$identifier, $ipAddress]);
    }
    
    /**
     * Get failed login count
     * Requirements: 2.5
     */
    public function getFailedLoginCount($identifier): int {
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        
        $result = $this->db->query(
            "SELECT attempts FROM login_attempts WHERE identifier = ? AND ip_address = ?",
            [$identifier, $ipAddress]
        );
        
        return $result ? (int)$result[0]['attempts'] : 0;
    }
    
    /**
     * Check if account is locked
     * Requirements: 2.5
     */
    public function isAccountLocked($identifier): bool {
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        
        $result = $this->db->query(
            "SELECT locked_until FROM login_attempts WHERE identifier = ? AND ip_address = ? AND locked_until > NOW()",
            [$identifier, $ipAddress]
        );
        
        return !empty($result);
    }
    
    /**
     * Get account lock time remaining
     * Requirements: 2.5
     */
    public function getLockTimeRemaining($identifier): int {
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        
        $result = $this->db->query(
            "SELECT UNIX_TIMESTAMP(locked_until) - UNIX_TIMESTAMP(NOW()) as remaining FROM login_attempts WHERE identifier = ? AND ip_address = ? AND locked_until > NOW()",
            [$identifier, $ipAddress]
        );
        
        return $result ? max(0, (int)$result[0]['remaining']) : 0;
    }
    
    /**
     * Clean up expired login attempts and tokens
     * Requirements: 2.5, 3.3
     */
    public function cleanupExpiredData(): void {
        // Clean up expired login attempts (older than 24 hours)
        $this->db->query("DELETE FROM login_attempts WHERE last_attempt < DATE_SUB(NOW(), INTERVAL 24 HOUR)");
        
        // Clean up expired password reset tokens (older than 24 hours)
        $this->db->query("DELETE FROM password_reset_tokens WHERE created_at < DATE_SUB(NOW(), INTERVAL 24 HOUR)");
    }
    
    /**
     * Get user by email or phone
     * Requirements: 8.2
     */
    public function findByLogin($login): ?array {
        $result = $this->db->query(
            "SELECT * FROM {$this->table} WHERE email = ? OR phone = ? OR username = ?",
            [$login, $login, $login]
        );
        
        return $result ? $this->hideFields($result[0]) : null;
    }
    
    /**
     * Update user password with security checks
     * Requirements: 3.2, 3.4
     */
    public function updatePasswordSecure($userId, $newPassword, $invalidateAllSessions = true): bool {
        // Hash the new password
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        
        // Update password
        $result = $this->update($userId, [
            'password' => $hashedPassword,
            'updated_at' => date('Y-m-d H:i:s')
        ]);
        
        if ($result && $invalidateAllSessions) {
            // Clear all password reset tokens for this user
            $user = $this->find($userId);
            if ($user) {
                $this->db->query(
                    "UPDATE password_reset_tokens SET used_at = NOW() WHERE email = ? AND used_at IS NULL",
                    [$user['email']]
                );
            }
        }
        
        return $result;
    }
    
    /**
     * Check if user exists by email
     * Requirements: 1.2, 8.2
     */
    public function emailExists($email): bool {
        $result = $this->db->query("SELECT id FROM {$this->table} WHERE email = ?", [$email]);
        return !empty($result);
    }
    
    /**
     * Check if user exists by phone
     * Requirements: 1.2, 8.2
     */
    public function phoneExists($phone): bool {
        if (empty($phone)) {
            return false;
        }
        
        $result = $this->db->query("SELECT id FROM {$this->table} WHERE phone = ?", [$phone]);
        return !empty($result);
    }
    
    /**
     * Check if user exists by username
     * Requirements: 1.2, 8.2
     */
    public function usernameExists($username): bool {
        if (empty($username)) {
            return false;
        }
        
        $result = $this->db->query("SELECT id FROM {$this->table} WHERE username = ?", [$username]);
        return !empty($result);
    }
    
    /**
     * Get user security info
     * Requirements: 8.2
     */
    public function getUserSecurityInfo($userId): ?array {
        $user = $this->find($userId);
        if (!$user) {
            return null;
        }
        
        // Get failed login attempts
        $failedAttempts = $this->getFailedLoginCount($user['email']);
        
        // Check if account is locked
        $isLocked = $this->isAccountLocked($user['email']);
        $lockTimeRemaining = $isLocked ? $this->getLockTimeRemaining($user['email']) : 0;
        
        // Get active password reset tokens
        $activeTokens = $this->db->query(
            "SELECT COUNT(*) as count FROM password_reset_tokens WHERE email = ? AND used_at IS NULL AND expires_at > NOW()",
            [$user['email']]
        );
        
        return [
            'user_id' => $userId,
            'email' => $user['email'],
            'status' => $user['status'],
            'failed_attempts' => $failedAttempts,
            'is_locked' => $isLocked,
            'lock_time_remaining' => $lockTimeRemaining,
            'active_reset_tokens' => $activeTokens ? (int)$activeTokens[0]['count'] : 0,
            'last_updated' => $user['updated_at'] ?? null,
        ];
    }

    /**
     * Get users by role and status for admin management
     * Requirements: 3.1
     */
    public function getUsersByRoleAndStatus($role, $status) {
        $sql = "SELECT id, name, email, role, status, created_at
                FROM users
                WHERE role = ? AND status = ?
                ORDER BY created_at DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$role, $status]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get users by agent request status
     * Requirements: 3.2
     */
    public function getUsersByAgentStatus($agentStatus) {
        $sql = "SELECT id, name, email, role, status, agent_request_status, agent_request_date, agent_approved_date
                FROM users
                WHERE agent_request_status = ?
                ORDER BY agent_request_date DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$agentStatus]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get user by ID
     */
    public function getUserById($userId) {
        $sql = "SELECT * FROM users WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Update agent request status
     * Requirements: 3.3, 3.5
     */
    public function updateAgentStatus($userId, $status) {
        $sql = "UPDATE users
                SET agent_request_status = ?,
                    agent_approved_date = CASE WHEN ? = 'approved' THEN NOW() ELSE agent_approved_date END
                WHERE id = ?";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$status, $status, $userId]);
    }

    /**
     * Update user role
     * Requirements: 3.5
     */
    public function updateUserRole($userId, $role) {
        $sql = "UPDATE users SET role = ? WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$role, $userId]);
    }
}

