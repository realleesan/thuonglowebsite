<?php
/**
 * AgentRegistrationData - Data Transfer Object for Agent Registration
 * Manages agent registration data with validation
 * Requirements: 2.2, 5.3
 */

class AgentRegistrationData {
    public ?int $userId;
    public string $email;
    public ?array $additionalInfo;
    public string $requestType; // 'new_user' or 'existing_user'
    public string $status; // 'pending', 'approved', 'rejected'
    public ?string $submittedAt;
    public ?string $processedAt;
    
    public function __construct(array $data = []) {
        $this->userId = $data['user_id'] ?? null;
        $this->email = $data['email'] ?? '';
        $this->additionalInfo = $data['additional_info'] ?? null;
        $this->requestType = $data['request_type'] ?? 'existing_user';
        $this->status = $data['status'] ?? 'pending';
        $this->submittedAt = $data['submitted_at'] ?? null;
        $this->processedAt = $data['processed_at'] ?? null;
    }
    
    /**
     * Validate Gmail address requirement
     * Requirements: 2.2
     */
    public function validateGmail(): bool {
        if (empty($this->email)) {
            return false;
        }
        
        // Check if email ends with @gmail.com (compatible with older PHP versions)
        return substr(strtolower($this->email), -10) === '@gmail.com';
    }
    
    /**
     * Validate all required fields
     */
    public function validate(): array {
        $errors = [];
        
        // Email validation
        if (empty($this->email)) {
            $errors[] = 'Email là bắt buộc';
        } elseif (!filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Email không hợp lệ';
        } elseif (!$this->validateGmail()) {
            $errors[] = 'Chỉ chấp nhận địa chỉ Gmail (@gmail.com)';
        }
        
        // Request type validation
        if (!in_array($this->requestType, ['new_user', 'existing_user'])) {
            $errors[] = 'Loại yêu cầu không hợp lệ';
        }
        
        // Status validation
        if (!in_array($this->status, ['pending', 'approved', 'rejected'])) {
            $errors[] = 'Trạng thái không hợp lệ';
        }
        
        // User ID validation for existing users
        if ($this->requestType === 'existing_user' && empty($this->userId)) {
            $errors[] = 'User ID là bắt buộc cho người dùng hiện tại';
        }
        
        return $errors;
    }
    
    /**
     * Check if data is valid
     */
    public function isValid(): bool {
        return empty($this->validate());
    }
    
    /**
     * Convert to array for database operations
     */
    public function toArray(): array {
        return [
            'user_id' => $this->userId,
            'email' => $this->email,
            'additional_info' => $this->additionalInfo ? json_encode($this->additionalInfo) : null,
            'request_type' => $this->requestType,
            'status' => $this->status,
            'submitted_at' => $this->submittedAt,
            'processed_at' => $this->processedAt
        ];
    }
    
    /**
     * Create from database row
     */
    public static function fromArray(array $data): self {
        $instance = new self();
        $instance->userId = $data['user_id'] ?? null;
        $instance->email = $data['email'] ?? '';
        $instance->additionalInfo = isset($data['additional_info']) ? 
            json_decode($data['additional_info'], true) : null;
        $instance->requestType = $data['request_type'] ?? 'existing_user';
        $instance->status = $data['status'] ?? 'pending';
        $instance->submittedAt = $data['submitted_at'] ?? null;
        $instance->processedAt = $data['processed_at'] ?? null;
        
        return $instance;
    }
    
    /**
     * Sanitize input data
     */
    public function sanitize(): void {
        $this->email = filter_var(trim($this->email), FILTER_SANITIZE_EMAIL);
        
        if (is_array($this->additionalInfo)) {
            // Sanitize additional info array
            array_walk_recursive($this->additionalInfo, function(&$value) {
                if (is_string($value)) {
                    $value = htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');
                }
            });
        }
    }
    
    /**
     * Get validation error messages in Vietnamese
     */
    public function getValidationErrors(): array {
        return $this->validate();
    }
}