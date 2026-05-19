<?php
/**
 * Site Settings Model
 * Manages dynamic site settings including logos and favicon
 */

require_once __DIR__ . '/BaseModel.php';

class SiteSettingsModel extends BaseModel {
    protected $table = 'site_settings';
    
    public function __construct() {
        parent::__construct();
    }
    
    /**
     * Get setting by key
     */
    public function getByKey($key) {
        try {
            return $this->db->table($this->table)
                ->where('setting_key', $key)
                ->first();
        } catch (Exception $e) {
            error_log("Error getting setting by key: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Get setting value by key
     */
    public function getValue($key, $default = null) {
        $setting = $this->getByKey($key);
        return $setting ? $setting['setting_value'] : $default;
    }
    
    /**
     * Get all settings by group
     */
    public function getByGroup($group) {
        try {
            return $this->db->table($this->table)
                ->where('setting_group', $group)
                ->orderBy('setting_key', 'ASC')
                ->get();
        } catch (Exception $e) {
            error_log("Error getting settings by group: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get all logo settings
     */
    public function getLogoSettings() {
        return $this->getByGroup('logo');
    }
    
    /**
     * Update setting value
     */
    public function updateSetting($key, $value) {
        try {
            return $this->db->table($this->table)
                ->where('setting_key', $key)
                ->update([
                    'setting_value' => $value,
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
        } catch (Exception $e) {
            error_log("Error updating setting: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Create new setting
     */
    public function createSetting($data) {
        try {
            return $this->db->table($this->table)->insert([
                'setting_key' => $data['setting_key'],
                'setting_value' => $data['setting_value'] ?? null,
                'setting_type' => $data['setting_type'] ?? 'text',
                'setting_group' => $data['setting_group'] ?? 'general',
                'description' => $data['description'] ?? null
            ]);
        } catch (Exception $e) {
            error_log("Error creating setting: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Delete setting
     */
    public function deleteSetting($key) {
        try {
            return $this->db->table($this->table)
                ->where('setting_key', $key)
                ->delete();
        } catch (Exception $e) {
            error_log("Error deleting setting: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get all settings
     */
    public function getAllSettings() {
        try {
            return $this->db->table($this->table)
                ->orderBy('setting_group', 'ASC')
                ->orderBy('setting_key', 'ASC')
                ->get();
        } catch (Exception $e) {
            error_log("Error getting all settings: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get settings as key-value pairs
     */
    public function getSettingsAsArray($group = null) {
        try {
            if ($group) {
                $settings = $this->getByGroup($group);
            } else {
                $settings = $this->getAllSettings();
            }
            
            $result = [];
            foreach ($settings as $setting) {
                $result[$setting['setting_key']] = $setting['setting_value'];
            }
            return $result;
        } catch (Exception $e) {
            error_log("Error getting settings as array: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Batch update settings
     */
    public function batchUpdate($settings) {
        try {
            $this->db->beginTransaction();
            
            foreach ($settings as $key => $value) {
                $this->updateSetting($key, $value);
            }
            
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Error batch updating settings: " . $e->getMessage());
            return false;
        }
    }
}
