<?php
/**
 * Settings Model
 * Handles application settings with database
 */

require_once __DIR__ . '/BaseModel.php';

class SettingsModel extends BaseModel {
    protected $table = 'settings';
    protected $fillable = ['key', 'value', 'type', 'group', 'description', 'is_public'];
    
    private static $cache = [];
    
    /**
     * Get setting value by key
     */
    public function get($key, $default = null) {
        // Check cache first
        if (isset(self::$cache[$key])) {
            return self::$cache[$key];
        }
        
        $setting = $this->findBy('key', $key);
        
        if (!$setting) {
            return $default;
        }
        
        $value = $this->castValue($setting['value'], $setting['type']);
        
        // Cache the value
        self::$cache[$key] = $value;
        
        return $value;
    }
    
    /**
     * Get setting by key (returns full setting record)
     */
    public function getByKey($key) {
        return $this->findBy('key', $key);
    }
    
    /**
     * Set setting value
     */
    public function set($key, $value, $type = 'text', $group = 'general', $description = null) {
        $existing = $this->findBy('key', $key);
        
        $data = [
            'key' => $key,
            'value' => $this->prepareValue($value, $type),
            'type' => $type,
            'group' => $group,
            'description' => $description
        ];
        
        if ($existing) {
            $result = $this->update($existing['id'], $data);
        } else {
            $result = $this->create($data);
        }
        
        // Update cache
        if ($result) {
            self::$cache[$key] = $value;
        }
        
        return $result;
    }
    
    /**
     * Get settings by group
     */
    public function getByGroup($group) {
        $settings = $this->where('group', $group)->get();
        
        $result = [];
        foreach ($settings as $setting) {
            $result[$setting['key']] = $this->castValue($setting['value'], $setting['type']);
        }
        
        return $result;
    }
    
    /**
     * Get all public settings (for frontend)
     */
    public function getPublicSettings() {
        $settings = $this->where('is_public', true)->get();
        
        $result = [];
        foreach ($settings as $setting) {
            $result[$setting['key']] = $this->castValue($setting['value'], $setting['type']);
        }
        
        return $result;
    }
    
    /**
     * Get all settings grouped
     */
    public function getAllGrouped() {
        $settings = $this->all();
        
        $grouped = [];
        foreach ($settings as $setting) {
            $group = $setting['group'];
            if (!isset($grouped[$group])) {
                $grouped[$group] = [];
            }
            
            $grouped[$group][$setting['key']] = [
                'value' => $this->castValue($setting['value'], $setting['type']),
                'type' => $setting['type'],
                'description' => $setting['description'],
                'is_public' => $setting['is_public']
            ];
        }
        
        return $grouped;
    }
    
    /**
     * Update multiple settings at once
     */
    public function updateMultiple($settings) {
        $this->beginTransaction();
        
        try {
            foreach ($settings as $key => $value) {
                $existing = $this->findBy('key', $key);
                
                if ($existing) {
                    $this->update($existing['id'], [
                        'value' => $this->prepareValue($value, $existing['type'])
                    ]);
                    
                    // Update cache
                    self::$cache[$key] = $value;
                }
            }
            
            $this->commit();
            return true;
            
        } catch (Exception $e) {
            $this->rollback();
            throw $e;
        }
    }
    
    /**
     * Delete setting
     */
    public function deleteSetting($key) {
        $setting = $this->findBy('key', $key);
        
        if ($setting) {
            $result = $this->delete($setting['id']);
            
            // Remove from cache
            if ($result && isset(self::$cache[$key])) {
                unset(self::$cache[$key]);
            }
            
            return $result;
        }
        
        return false;
    }
    
    /**
     * Get contact settings for contact page
     */
    public function getContactSettings() {
        $contactKeys = [
            'office_address',
            'phone', 
            'hotline',
            'email',
            'working_hours_weekday',
            'working_hours_weekend'
        ];
        
        $settings = [];
        foreach ($contactKeys as $key) {
            $value = $this->get($key);
            if ($value !== null) {
                $settings[$key] = $value;
            }
        }
        
        return $settings;
    }
    
    /**
     * Cast value to appropriate type
     */
    private function castValue($value, $type) {
        switch ($type) {
            case 'boolean':
                return (bool) $value;
            case 'number':
                return is_numeric($value) ? (float) $value : 0;
            case 'json':
                return json_decode($value, true) ?: [];
            default:
                return $value;
        }
    }
    
    /**
     * Prepare value for storage
     */
    private function prepareValue($value, $type) {
        switch ($type) {
            case 'boolean':
                return $value ? '1' : '0';
            case 'json':
                return json_encode($value);
            default:
                return (string) $value;
        }
    }
    
    /**
     * Clear cache
     */
    public function clearCache() {
        self::$cache = [];
    }
    
    /**
     * Initialize default settings
     */
    public function initializeDefaults() {
        $defaults = [
            // Site settings
            'site_name' => ['ThuongLo', 'text', 'site', 'Tên website', true],
            'site_description' => ['Nền tảng kinh doanh online', 'textarea', 'site', 'Mô tả website', true],
            'site_logo' => ['assets/images/logo.png', 'file', 'site', 'Logo website', true],
            'site_favicon' => ['assets/images/favicon.ico', 'file', 'site', 'Favicon', false],
            
            // Contact settings
            'contact_email' => ['contact@thuonglo.com', 'email', 'contact', 'Email liên hệ', true],
            'contact_phone' => ['1900-1234', 'text', 'contact', 'Số điện thoại', true],
            'contact_address' => ['Hà Nội, Việt Nam', 'textarea', 'contact', 'Địa chỉ', true],
            
            // Social settings
            'facebook_url' => ['https://facebook.com/thuonglo', 'url', 'social', 'Facebook URL', true],
            'youtube_url' => ['', 'url', 'social', 'YouTube URL', true],
            'zalo_url' => ['', 'url', 'social', 'Zalo URL', true],
            
            // Business settings
            'commission_rate' => [10, 'number', 'business', 'Tỷ lệ hoa hồng mặc định (%)', false],
            'min_withdrawal' => [100000, 'number', 'business', 'Số tiền rút tối thiểu', false],
            'currency' => ['VND', 'text', 'business', 'Đơn vị tiền tệ', false],
            
            // Email settings
            'smtp_host' => ['', 'text', 'email', 'SMTP Host', false],
            'smtp_port' => [587, 'number', 'email', 'SMTP Port', false],
            'smtp_username' => ['', 'text', 'email', 'SMTP Username', false],
            'smtp_password' => ['', 'text', 'email', 'SMTP Password', false],
            
            // SEO settings
            'meta_title' => ['ThuongLo - Nền tảng kinh doanh online', 'text', 'seo', 'Meta Title', false],
            'meta_description' => ['Nền tảng cung cấp data nguồn hàng và khóa học kinh doanh online', 'textarea', 'seo', 'Meta Description', false],
            'meta_keywords' => ['dropshipping, kinh doanh online, data nguồn hàng', 'textarea', 'seo', 'Meta Keywords', false],
        ];
        
        foreach ($defaults as $key => $config) {
            $existing = $this->findBy('key', $key);
            
            if (!$existing) {
                $this->create([
                    'key' => $key,
                    'value' => $this->prepareValue($config[0], $config[1]),
                    'type' => $config[1],
                    'group' => $config[2],
                    'description' => $config[3],
                    'is_public' => $config[4]
                ]);
            }
        }
    }
}