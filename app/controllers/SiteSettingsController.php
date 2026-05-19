<?php
/**
 * Site Settings Controller
 * Handles logo and favicon management
 */

require_once __DIR__ . '/../services/SiteSettingsService.php';

class SiteSettingsController {
    private $service;
    
    public function __construct() {
        $this->service = new SiteSettingsService();
    }
    
    /**
     * Display logo settings page
     */
    public function index() {
        try {
            $result = $this->service->getLogoSettings();
            
            if ($result['success']) {
                // Make logoSettings available globally for the view
                $GLOBALS['logoSettings'] = $result['data'];
                
                // Render view
                $page_title = 'Quản lý Logo & Favicon';
                $content = __DIR__ . '/../views/admin/site_settings/index.php';
                
                include __DIR__ . '/../views/_layout/admin_master.php';
            } else {
                $_SESSION['flash_error'] = $result['message'];
                header('Location: ?page=admin&module=dashboard');
                exit;
            }
        } catch (Exception $e) {
            error_log("Error in SiteSettingsController::index: " . $e->getMessage());
            $_SESSION['flash_error'] = 'Có lỗi xảy ra';
            header('Location: ?page=admin&module=dashboard');
            exit;
        }
    }
    
    /**
     * Update logo setting
     */
    public function update() {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                header('Location: ?page=admin&module=site-settings');
                exit;
            }
            
            $settingKey = $_POST['setting_key'] ?? '';
            $file = $_FILES['logo_file'] ?? null;
            
            if (!$settingKey) {
                $_SESSION['flash_error'] = 'Thiếu thông tin cài đặt';
                header('Location: ?page=admin&module=site-settings');
                exit;
            }
            
            $result = $this->service->updateLogoSetting($settingKey, $file);
            
            if ($result['success']) {
                $_SESSION['flash_success'] = $result['message'];
            } else {
                $_SESSION['flash_error'] = $result['message'];
            }
            
            header('Location: ?page=admin&module=site-settings');
            exit;
        } catch (Exception $e) {
            error_log("Error in SiteSettingsController::update: " . $e->getMessage());
            $_SESSION['flash_error'] = 'Có lỗi xảy ra: ' . $e->getMessage();
            header('Location: ?page=admin&module=site-settings');
            exit;
        }
    }
    
    /**
     * Get logo value (API endpoint)
     */
    public function getLogo() {
        try {
            $key = $_GET['key'] ?? '';
            
            if (!$key) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Thiếu key'
                ]);
                exit;
            }
            
            $result = $this->service->getLogoSetting($key);
            echo json_encode($result);
            exit;
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
            exit;
        }
    }
}
