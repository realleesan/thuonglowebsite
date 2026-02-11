<?php
/**
 * Contacts Model
 * Handles contact form submissions and inquiries
 */

require_once __DIR__ . '/BaseModel.php';

class ContactsModel extends BaseModel {
    protected $table = 'contacts';
    protected $fillable = [
        'name', 'email', 'phone', 'subject', 'message', 'status',
        'priority', 'assigned_to', 'user_id', 'ip_address', 'user_agent'
    ];
    
    /**
     * Create new contact submission
     */
    public function createSubmission($data) {
        // Add IP address and user agent
        $data['ip_address'] = $_SERVER['REMOTE_ADDR'] ?? null;
        $data['user_agent'] = $_SERVER['HTTP_USER_AGENT'] ?? null;
        
        // Set default status and priority
        $data['status'] = $data['status'] ?? 'new';
        $data['priority'] = $data['priority'] ?? 'normal';
        
        return $this->create($data);
    }
    
    /**
     * Get contacts by status
     */
    public function getByStatus($status, $limit = null) {
        $query = $this->where('status', $status)->orderBy('created_at', 'DESC');
        
        if ($limit) {
            $query->limit($limit);
        }
        
        return $query->get();
    }
    
    /**
     * Get contacts by priority
     */
    public function getByPriority($priority, $limit = null) {
        $query = $this->where('priority', $priority)->orderBy('created_at', 'DESC');
        
        if ($limit) {
            $query->limit($limit);
        }
        
        return $query->get();
    }
    
    /**
     * Get unread contacts
     */
    public function getUnread($limit = null) {
        return $this->getByStatus('new', $limit);
    }
    
    /**
     * Get contacts assigned to user
     */
    public function getAssignedTo($userId, $limit = null) {
        $query = $this->where('assigned_to', $userId)->orderBy('created_at', 'DESC');
        
        if ($limit) {
            $query->limit($limit);
        }
        
        return $query->get();
    }
    
    /**
     * Mark contact as read
     */
    public function markAsRead($contactId) {
        return $this->update($contactId, ['status' => 'read']);
    }
    
    /**
     * Mark contact as replied
     */
    public function markAsReplied($contactId) {
        return $this->update($contactId, [
            'status' => 'replied',
            'replied_at' => date('Y-m-d H:i:s')
        ]);
    }
    
    /**
     * Close contact
     */
    public function closeContact($contactId) {
        return $this->update($contactId, ['status' => 'closed']);
    }
    
    /**
     * Assign contact to user
     */
    public function assignTo($contactId, $userId) {
        return $this->update($contactId, ['assigned_to' => $userId]);
    }
    
    /**
     * Update priority
     */
    public function updatePriority($contactId, $priority) {
        return $this->update($contactId, ['priority' => $priority]);
    }
    
    /**
     * Search contacts
     */
    public function searchContacts($query, $limit = 50) {
        return $this->search($query, ['name', 'email', 'subject', 'message'], $limit);
    }
    
    /**
     * Get contact statistics
     */
    public function getStats() {
        $stats = [];
        
        // Total contacts
        $stats['total'] = $this->count();
        
        // By status
        $statuses = ['new', 'read', 'replied', 'closed'];
        foreach ($statuses as $status) {
            $count = $this->db->query("SELECT COUNT(*) as count FROM {$this->table} WHERE status = ?", [$status]);
            $stats['by_status'][$status] = $count[0]['count'] ?? 0;
        }
        
        // By priority
        $priorities = ['low', 'normal', 'high', 'urgent'];
        foreach ($priorities as $priority) {
            $count = $this->db->query("SELECT COUNT(*) as count FROM {$this->table} WHERE priority = ?", [$priority]);
            $stats['by_priority'][$priority] = $count[0]['count'] ?? 0;
        }
        
        // Recent contacts (last 7 days)
        $recentCount = $this->db->query("
            SELECT COUNT(*) as count FROM {$this->table} 
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
        ");
        $stats['recent'] = $recentCount[0]['count'] ?? 0;
        
        // Response rate (replied / total)
        $repliedCount = $stats['by_status']['replied'] ?? 0;
        $stats['response_rate'] = $stats['total'] > 0 ? round(($repliedCount / $stats['total']) * 100, 2) : 0;
        
        return $stats;
    }
    
    /**
     * Get contacts with user info (if logged in user submitted)
     */
    public function getWithUserInfo($limit = null) {
        $sql = "
            SELECT c.*, u.name as user_name, u.email as user_email
            FROM {$this->table} c
            LEFT JOIN users u ON c.user_id = u.id
            ORDER BY c.created_at DESC
        ";
        
        if ($limit) {
            $sql .= " LIMIT {$limit}";
        }
        
        return $this->db->query($sql);
    }
    
    /**
     * Get recent contacts for dashboard
     */
    public function getRecentForDashboard($limit = 5) {
        return $this->where('status', 'new')
                   ->orderBy('created_at', 'DESC')
                   ->limit($limit)
                   ->get();
    }
    
    /**
     * Get contacts by date range
     */
    public function getByDateRange($startDate, $endDate) {
        $sql = "
            SELECT * FROM {$this->table}
            WHERE created_at >= ? AND created_at <= ?
            ORDER BY created_at DESC
        ";
        
        return $this->db->query($sql, [$startDate, $endDate]);
    }
    
    /**
     * Get monthly contact counts
     */
    public function getMonthlyStats($year = null) {
        $year = $year ?: date('Y');
        
        $sql = "
            SELECT 
                MONTH(created_at) as month,
                COUNT(*) as count,
                SUM(CASE WHEN status = 'replied' THEN 1 ELSE 0 END) as replied_count
            FROM {$this->table}
            WHERE YEAR(created_at) = ?
            GROUP BY MONTH(created_at)
            ORDER BY month
        ";
        
        return $this->db->query($sql, [$year]);
    }
}