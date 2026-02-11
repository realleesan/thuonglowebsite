<?php

require_once __DIR__ . '/BaseModel.php';

class EventsModel extends BaseModel {
    
    protected $table = 'events';
    
    /**
     * Get all events with pagination
     */
    public function getAll($limit = null, $offset = 0, $filters = []) {
        $query = "SELECT e.*, u.name as organizer_name 
                  FROM {$this->table} e 
                  LEFT JOIN users u ON e.organizer_id = u.id";
        
        $conditions = [];
        $params = [];
        
        // Apply filters
        if (!empty($filters['status'])) {
            $conditions[] = "e.status = ?";
            $params[] = $filters['status'];
        }
        
        if (!empty($filters['featured'])) {
            $conditions[] = "e.featured = ?";
            $params[] = $filters['featured'] ? 1 : 0;
        }
        
        if (!empty($filters['search'])) {
            $conditions[] = "(e.title LIKE ? OR e.description LIKE ?)";
            $params[] = '%' . $filters['search'] . '%';
            $params[] = '%' . $filters['search'] . '%';
        }
        
        if (!empty($conditions)) {
            $query .= " WHERE " . implode(" AND ", $conditions);
        }
        
        $query .= " ORDER BY e.start_date DESC";
        
        if ($limit) {
            $query .= " LIMIT ? OFFSET ?";
            $params[] = $limit;
            $params[] = $offset;
        }
        
        return $this->db->query($query, $params);
    }
    
    /**
     * Get event by ID
     */
    public function getById($id) {
        $query = "SELECT e.*, u.name as organizer_name, u.email as organizer_email 
                  FROM {$this->table} e 
                  LEFT JOIN users u ON e.organizer_id = u.id 
                  WHERE e.id = ?";
        
        $result = $this->db->query($query, [$id]);
        return !empty($result) ? $result[0] : null;
    }
    
    /**
     * Get event by slug
     */
    public function getBySlug($slug) {
        $query = "SELECT e.*, u.name as organizer_name, u.email as organizer_email 
                  FROM {$this->table} e 
                  LEFT JOIN users u ON e.organizer_id = u.id 
                  WHERE e.slug = ?";
        
        $result = $this->db->query($query, [$slug]);
        return !empty($result) ? $result[0] : null;
    }
    
    /**
     * Create new event
     */
    public function create($data) {
        $fields = [
            'title', 'slug', 'description', 'content', 'image',
            'start_date', 'end_date', 'location', 'address', 'price',
            'max_participants', 'status', 'featured', 'organizer_id',
            'meta_title', 'meta_description'
        ];
        
        return $this->insert($data, $fields);
    }
    
    /**
     * Update event
     */
    public function update($id, $data) {
        $fields = [
            'title', 'slug', 'description', 'content', 'image',
            'start_date', 'end_date', 'location', 'address', 'price',
            'max_participants', 'current_participants', 'status', 'featured',
            'organizer_id', 'meta_title', 'meta_description'
        ];
        
        return $this->updateById($id, $data, $fields);
    }
    
    /**
     * Delete event
     */
    public function delete($id) {
        return $this->deleteById($id);
    }
    
    /**
     * Get events statistics
     */
    public function getStats() {
        $stats = [
            'total' => 0,
            'by_status' => [],
            'upcoming' => 0,
            'ongoing' => 0,
            'completed' => 0,
            'total_participants' => 0,
            'total_revenue' => 0
        ];
        
        // Total count
        $result = $this->db->query("SELECT COUNT(*) as total FROM {$this->table}");
        $stats['total'] = $result[0]['total'] ?? 0;
        
        // By status
        $result = $this->db->query("SELECT status, COUNT(*) as count FROM {$this->table} GROUP BY status");
        foreach ($result as $row) {
            $stats['by_status'][$row['status']] = $row['count'];
            
            // Set specific status counts
            if ($row['status'] === 'upcoming') {
                $stats['upcoming'] = $row['count'];
            } elseif ($row['status'] === 'ongoing') {
                $stats['ongoing'] = $row['count'];
            } elseif ($row['status'] === 'completed') {
                $stats['completed'] = $row['count'];
            }
        }
        
        // Total participants and revenue
        $result = $this->db->query("SELECT SUM(current_participants) as total_participants, SUM(price * current_participants) as total_revenue FROM {$this->table}");
        if (!empty($result)) {
            $stats['total_participants'] = $result[0]['total_participants'] ?? 0;
            $stats['total_revenue'] = $result[0]['total_revenue'] ?? 0;
        }
        
        return $stats;
    }
    
    /**
     * Get upcoming events
     */
    public function getUpcoming($limit = 5) {
        $query = "SELECT e.*, u.name as organizer_name 
                  FROM {$this->table} e 
                  LEFT JOIN users u ON e.organizer_id = u.id 
                  WHERE e.status = 'upcoming' AND e.start_date > NOW() 
                  ORDER BY e.start_date ASC 
                  LIMIT ?";
        
        return $this->db->query($query, [$limit]);
    }
    
    /**
     * Get featured events
     */
    public function getFeatured($limit = 3) {
        $query = "SELECT e.*, u.name as organizer_name 
                  FROM {$this->table} e 
                  LEFT JOIN users u ON e.organizer_id = u.id 
                  WHERE e.featured = 1 AND e.status IN ('upcoming', 'ongoing') 
                  ORDER BY e.start_date ASC 
                  LIMIT ?";
        
        return $this->db->query($query, [$limit]);
    }
    
    /**
     * Update participant count
     */
    public function updateParticipantCount($id, $count) {
        $query = "UPDATE {$this->table} SET current_participants = ? WHERE id = ?";
        return $this->db->query($query, [$count, $id]);
    }
    
    /**
     * Check if event slug exists
     */
    public function slugExists($slug, $excludeId = null) {
        $query = "SELECT COUNT(*) as count FROM {$this->table} WHERE slug = ?";
        $params = [$slug];
        
        if ($excludeId) {
            $query .= " AND id != ?";
            $params[] = $excludeId;
        }
        
        $result = $this->db->query($query, $params);
        return ($result[0]['count'] ?? 0) > 0;
    }
    
    /**
     * Generate unique slug
     */
    public function generateSlug($title, $excludeId = null) {
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title)));
        $originalSlug = $slug;
        $counter = 1;
        
        while ($this->slugExists($slug, $excludeId)) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }
        
        return $slug;
    }
    
    /**
     * Get events by organizer
     */
    public function getByOrganizer($organizerId, $limit = null) {
        $query = "SELECT * FROM {$this->table} WHERE organizer_id = ? ORDER BY start_date DESC";
        $params = [$organizerId];
        
        if ($limit) {
            $query .= " LIMIT ?";
            $params[] = $limit;
        }
        
        return $this->db->query($query, $params);
    }
    
    /**
     * Search events
     */
    public function search($keyword, $limit = 10) {
        $query = "SELECT e.*, u.name as organizer_name 
                  FROM {$this->table} e 
                  LEFT JOIN users u ON e.organizer_id = u.id 
                  WHERE (e.title LIKE ? OR e.description LIKE ? OR e.location LIKE ?) 
                  AND e.status IN ('upcoming', 'ongoing') 
                  ORDER BY e.start_date ASC 
                  LIMIT ?";
        
        $searchTerm = '%' . $keyword . '%';
        return $this->db->query($query, [$searchTerm, $searchTerm, $searchTerm, $limit]);
    }
}