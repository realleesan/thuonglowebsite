<?php
/**
 * Users Model
 * Handles user data operations with database
 */

require_once __DIR__ . '/BaseModel.php';

class UsersModel extends BaseModel {
    protected $table = 'users';
    protected $fillable = [
        'name', 'email', 'phone', 'password', 'role', 'status', 
        'address', 'avatar', 'points', 'level'
    ];
    protected $hidden = ['password', 'remember_token'];
    
    /**
     * Authenticate user login
     */
    public function authenticate($login, $password) {
        // Login can be email or phone
        $user = $this->db->table($this->table)
                        ->where('email', $login)
                        ->orWhere('phone', $login)
                        ->first();
        
        if ($user && password_verify($password, $user['password'])) {
            return $this->hideFields($user);
        }
        
        return false;
    }
    
    /**
     * Register new user
     */
    public function register($data) {
        // Check if email already exists
        if ($this->findBy('email', $data['email'])) {
            throw new Exception('Email already exists');
        }
        
        // Check if phone already exists (if provided)
        if (!empty($data['phone']) && $this->findBy('phone', $data['phone'])) {
            throw new Exception('Phone number already exists');
        }
        
        // Hash password
        $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        
        // Set default values
        $data['role'] = $data['role'] ?? 'user';
        $data['status'] = $data['status'] ?? 'active';
        $data['points'] = 0;
        $data['level'] = 'Bronze';
        
        $user = $this->create($data);
        return $this->hideFields($user);
    }
    
    /**
     * Update user password
     */
    public function updatePassword($userId, $newPassword) {
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        return $this->update($userId, ['password' => $hashedPassword]);
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
    
    /**
     * Override all to hide sensitive fields
     */
    public function all($columns = '*') {
        $users = parent::all($columns);
        return $this->hideFields($users);
    }
}