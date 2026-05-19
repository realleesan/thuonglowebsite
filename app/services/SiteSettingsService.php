<?php
/**
 * Site Settings Service
 * Business logic for managing site settings including logos and favicon
 */

require_once __DIR__ . '/../models/SiteSettingsModel.php';

class SiteSettingsService {
    private $model;
    private $uploadPath = 'assets/icons/';
    
    public function __construct() {
        $this->model = new SiteSettingsModel();
    }
    
    /**
     * Get all logo settings
     */
    public function getLogoSettings() {
        try {
            $settings = $this->model->getLogoSettings();
            return [
                'success' => true,
                'data' => $settings
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Lỗi khi lấy cài đặt logo: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Get specific logo setting
     */
    public function getLogoSetting($key) {
        try {
            $setting = $this->model->getByKey($key);
            if (!$setting) {
                return [
                    'success' => false,
                    'message' => 'Không tìm thấy cài đặt'
                ];
            }
            return [
                'success' => true,
                'data' => $setting
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Lỗi khi lấy cài đặt: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Update logo setting with file upload
     */
    public function updateLogoSetting($key, $file = null) {
        try {
            // Validate key
            $validKeys = [
                'logo_header', 'logo_footer', 'logo_admin_full', 
                'logo_admin_mini', 'logo_affiliate_full', 'logo_affiliate_mini', 'favicon'
            ];
            
            if (!in_array($key, $validKeys)) {
                return [
                    'success' => false,
                    'message' => 'Key không hợp lệ'
                ];
            }
            
            // Get current setting
            $currentSetting = $this->model->getByKey($key);
            if (!$currentSetting) {
                return [
                    'success' => false,
                    'message' => 'Không tìm thấy cài đặt'
                ];
            }
            
            $newValue = $currentSetting['setting_value'];
            
            // Handle file upload if provided
            if ($file && isset($file['tmp_name']) && $file['error'] === UPLOAD_ERR_OK) {
                $uploadResult = $this->handleFileUpload($file, $key);
                if (!$uploadResult['success']) {
                    return $uploadResult;
                }
                $newValue = $uploadResult['path'];
                
                // Delete old file if it exists and is different
                if ($currentSetting['setting_value'] && $currentSetting['setting_value'] !== $newValue) {
                    $this->deleteOldFile($currentSetting['setting_value']);
                }
            }
            
            // Update setting
            $updated = $this->model->updateSetting($key, $newValue);
            
            if ($updated) {
                return [
                    'success' => true,
                    'message' => 'Cập nhật thành công',
                    'data' => [
                        'setting_key' => $key,
                        'setting_value' => $newValue
                    ]
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Lỗi khi cập nhật cài đặt'
                ];
            }
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Lỗi: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Handle file upload
     */
    private function handleFileUpload($file, $key) {
        try {
            // Validate file type
            $allowedTypes = ['image/svg+xml', 'image/png', 'image/jpeg', 'image/jpg', 'image/gif', 'image/webp'];
            $fileType = $file['type'];
            
            if (!in_array($fileType, $allowedTypes)) {
                return [
                    'success' => false,
                    'message' => 'Chỉ chấp nhận file ảnh (SVG, PNG, JPG, GIF, WEBP)'
                ];
            }
            
            // Validate file size (max 2MB)
            if ($file['size'] > 2 * 1024 * 1024) {
                return [
                    'success' => false,
                    'message' => 'File không được vượt quá 2MB'
                ];
            }
            
            // Generate unique filename
            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = $key . '_' . time() . '.' . $extension;
            
            // Create upload directory if not exists
            $uploadDir = $this->uploadPath . 'logo/';
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            $uploadFile = $uploadDir . $filename;
            
            // Move uploaded file
            if (move_uploaded_file($file['tmp_name'], $uploadFile)) {
                return [
                    'success' => true,
                    'path' => 'logo/' . $filename
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Lỗi khi upload file'
                ];
            }
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Lỗi upload: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Delete old file
     */
    private function deleteOldFile($path) {
        try {
            $fullPath = $this->uploadPath . $path;
            if (file_exists($fullPath) && !is_dir($fullPath)) {
                // Don't delete default logos
                if (strpos($path, 'logo.svg') === false && strpos($path, 'logo_mini.svg') === false) {
                    unlink($fullPath);
                }
            }
        } catch (Exception $e) {
            error_log("Error deleting old file: " . $e->getMessage());
        }
    }
    
    /**
     * Get logo value by key
     */
    public function getLogoValue($key, $default = 'logo/logo.svg') {
        try {
            return $this->model->getValue($key, $default);
        } catch (Exception $e) {
            error_log("Error getting logo value: " . $e->getMessage());
            return $default;
        }
    }
    
    /**
     * Batch update multiple settings
     */
    public function batchUpdateSettings($settings) {
        try {
            $updated = $this->model->batchUpdate($settings);
            if ($updated) {
                return [
                    'success' => true,
                    'message' => 'Cập nhật thành công'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Lỗi khi cập nhật'
                ];
            }
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Lỗi: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Get all settings grouped
     */
    public function getAllSettingsGrouped() {
        try {
            $settings = $this->model->getAllSettings();
            $grouped = [];
            
            foreach ($settings as $setting) {
                $group = $setting['setting_group'];
                if (!isset($grouped[$group])) {
                    $grouped[$group] = [];
                }
                $grouped[$group][] = $setting;
            }
            
            return [
                'success' => true,
                'data' => $grouped
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Lỗi: ' . $e->getMessage()
            ];
        }
    }
}
