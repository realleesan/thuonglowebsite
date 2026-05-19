<?php

require_once __DIR__ . '/../services/AuthService.php';
require_once __DIR__ . '/../models/HeroSectionModel.php';
require_once __DIR__ . '/../models/HeroButtonModel.php';
require_once __DIR__ . '/../../core/view_init.php';

class HeroSectionController {
    private $authService;
    private $heroSectionModel;
    private $heroButtonModel;

    public function __construct() {
        $this->authService = new AuthService();
        $this->heroSectionModel = new HeroSectionModel();
        $this->heroButtonModel = new HeroButtonModel();
    }

    /**
     * Display hero section management page
     */
    public function index(): void {
        if (!$this->requireAdmin()) {
            return;
        }

        try {
            $heroSections = $this->heroSectionModel->getAllForAdmin();
            
            $data = [
                'title' => 'Quản lý Hero Section',
                'heroSections' => $heroSections,
                'user' => $this->getCurrentUser()
            ];

            $this->renderView('admin/homepage/hero_section/index', $data);
        } catch (Exception $e) {
            error_log("Error in heroSection index: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            $this->setFlashMessage('error', 'Có lỗi xảy ra khi tải danh sách Hero Section: ' . $e->getMessage());
            $this->redirect('?page=admin&module=dashboard');
        }
    }

    /**
     * Display create hero section form - DISABLED
     */
    public function create(): void {
        // Hero sections cannot be created - only edit existing one
        $this->setFlashMessage('error', 'Chỉ có thể chỉnh sửa Hero Section hiện tại, không thể tạo mới.');
        $this->redirect('?page=admin&module=hero-section');
        return;
    }

    /**
     * Store new hero section - DISABLED
     */
    public function store(): void {
        // Hero sections cannot be created - only edit existing one
        if ($this->isAjaxRequest()) {
            $this->sendJsonResponse(['success' => false, 'message' => 'Không thể tạo Hero Section mới. Vui lòng chỉnh sửa Hero Section hiện tại.']);
        } else {
            $this->setFlashMessage('error', 'Không thể tạo Hero Section mới. Vui lòng chỉnh sửa Hero Section hiện tại.');
            $this->redirect('?page=admin&module=hero-section');
        }
        return;
    }

    /**
     * Display edit hero section form
     */
    public function edit($id): void {
        if (!$this->requireAdmin()) {
            return;
        }

        try {
            $heroSection = $this->heroSectionModel->getWithButtons($id);
            
            if (!$heroSection) {
                $this->setFlashMessage('error', 'Hero Section không tồn tại');
                $this->redirect('?page=admin&module=hero-section');
                return;
            }

            $data = [
                'title' => 'Chỉnh sửa Hero Section',
                'heroSection' => $heroSection,
                'user' => $this->getCurrentUser()
            ];

            $this->renderView('admin/homepage/hero_section/edit', $data);
        } catch (Exception $e) {
            error_log("Error in heroSection edit: " . $e->getMessage());
            $this->setFlashMessage('error', 'Có lỗi xảy ra khi tải Hero Section');
            $this->redirect('?page=admin&module=hero-section');
        }
    }

    /**
     * Update hero section
     */
    public function update($id): void {
        if (!$this->requireAdmin()) {
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('?page=admin&module=hero-section');
            return;
        }

        try {
            $data = $this->getRequestData();
            
            // Validate required fields
            $errors = $this->validateHeroSectionData($data, true);
            if (!empty($errors)) {
                $message = implode(', ', $errors);
                if ($this->isAjaxRequest()) {
                    $this->sendJsonResponse(['success' => false, 'message' => $message]);
                } else {
                    $this->setFlashMessage('error', $message);
                    $this->redirect('?page=admin&module=hero-section&action=edit&id=' . $id);
                }
                return;
            }

            // Update hero section
            $result = $this->heroSectionModel->updateHeroSection($id, $data);
            
            if ($result) {
                $message = 'Hero Section đã được cập nhật thành công';
                if ($this->isAjaxRequest()) {
                    $this->sendJsonResponse(['success' => true, 'message' => $message]);
                } else {
                    $this->setFlashMessage('success', $message);
                    $this->redirect('?page=admin&module=hero-section&action=edit&id=' . $id);
                }
            } else {
                $message = 'Không thể cập nhật Hero Section';
                if ($this->isAjaxRequest()) {
                    $this->sendJsonResponse(['success' => false, 'message' => $message]);
                } else {
                    $this->setFlashMessage('error', $message);
                    $this->redirect('?page=admin&module=hero-section&action=edit&id=' . $id);
                }
            }

        } catch (Exception $e) {
            error_log("Error in heroSection update: " . $e->getMessage());
            $message = 'Có lỗi xảy ra khi cập nhật Hero Section';
            if ($this->isAjaxRequest()) {
                $this->sendJsonResponse(['success' => false, 'message' => $message]);
            } else {
                $this->setFlashMessage('error', $message);
                $this->redirect('?page=admin&module=hero-section&action=edit&id=' . $id);
            }
        }
    }

    /**
     * Delete hero section - DISABLED
     */
    public function delete($id): void {
        // Hero sections cannot be deleted - only edit existing one
        if ($this->isAjaxRequest()) {
            $this->sendJsonResponse(['success' => false, 'message' => 'Không thể xóa Hero Section. Vui lòng chỉ chỉnh sửa nội dung hiện tại.']);
        } else {
            $this->setFlashMessage('error', 'Không thể xóa Hero Section. Vui lòng chỉ chỉnh sửa nội dung hiện tại.');
            $this->redirect('?page=admin&module=hero-section');
        }
        return;
    }

    /**
     * Toggle hero section status
     */
    public function toggleStatus($id): void {
        if (!$this->requireAdmin()) {
            return;
        }

        try {
            $result = $this->heroSectionModel->toggleStatus($id);
            
            if ($result) {
                $this->sendJsonResponse(['success' => true, 'message' => 'Trạng thái đã được cập nhật']);
            } else {
                $this->sendJsonResponse(['success' => false, 'message' => 'Không thể cập nhật trạng thái']);
            }

        } catch (Exception $e) {
            error_log("Error in toggleStatus: " . $e->getMessage());
            $this->sendJsonResponse(['success' => false, 'message' => 'Có lỗi xảy ra']);
        }
    }

    // ========== BUTTON MANAGEMENT ==========

    /**
     * Create new button
     */
    public function createButton(): void {
        if (!$this->requireAdmin()) {
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->sendJsonResponse(['success' => false, 'message' => 'Method not allowed']);
            return;
        }

        try {
            $data = $_POST;
            
            // Validate button data
            $errors = $this->heroButtonModel->validateButtonData($data);
            if (!empty($errors)) {
                $this->sendJsonResponse(['success' => false, 'message' => implode(', ', $errors)]);
                return;
            }

            $buttonId = $this->heroButtonModel->createButton($data);
            
            if ($buttonId) {
                $button = $this->heroButtonModel->getForApi($buttonId);
                $this->sendJsonResponse(['success' => true, 'button' => $button, 'message' => 'Button đã được tạo thành công']);
            } else {
                $this->sendJsonResponse(['success' => false, 'message' => 'Không thể tạo button']);
            }

        } catch (Exception $e) {
            error_log("Error in createButton: " . $e->getMessage());
            $this->sendJsonResponse(['success' => false, 'message' => 'Có lỗi xảy ra']);
        }
    }

    /**
     * Update button
     */
    public function updateButton($id): void {
        if (!$this->requireAdmin()) {
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->sendJsonResponse(['success' => false, 'message' => 'Method not allowed']);
            return;
        }

        try {
            $data = $_POST;
            
            // Validate button data
            $errors = $this->heroButtonModel->validateButtonData($data, true);
            if (!empty($errors)) {
                $this->sendJsonResponse(['success' => false, 'message' => implode(', ', $errors)]);
                return;
            }

            $result = $this->heroButtonModel->updateButton($id, $data);
            
            if ($result) {
                $button = $this->heroButtonModel->getForApi($id);
                $this->sendJsonResponse(['success' => true, 'button' => $button, 'message' => 'Button đã được cập nhật']);
            } else {
                $this->sendJsonResponse(['success' => false, 'message' => 'Không thể cập nhật button']);
            }

        } catch (Exception $e) {
            error_log("Error in updateButton: " . $e->getMessage());
            $this->sendJsonResponse(['success' => false, 'message' => 'Có lỗi xảy ra']);
        }
    }

    /**
     * Update multiple buttons at once
     */
    public function updateButtons(): void {
        // Clean any previous output
        if (ob_get_level() > 0) {
            ob_end_clean();
        }
        
        if (!$this->requireAdmin()) {
            $this->sendJsonResponse(['success' => false, 'message' => 'Unauthorized']);
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->sendJsonResponse(['success' => false, 'message' => 'Method not allowed']);
            return;
        }

        try {
            // Get JSON input
            $jsonInput = file_get_contents('php://input');
            
            if (empty($jsonInput)) {
                $this->sendJsonResponse(['success' => false, 'message' => 'No data received']);
                return;
            }
            
            $input = json_decode($jsonInput, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $this->sendJsonResponse(['success' => false, 'message' => 'Invalid JSON: ' . json_last_error_msg()]);
                return;
            }
            
            if (!isset($input['hero_section_id']) || !isset($input['buttons'])) {
                $this->sendJsonResponse(['success' => false, 'message' => 'Dữ liệu không hợp lệ']);
                return;
            }

            $heroSectionId = (int)$input['hero_section_id'];
            $buttons = $input['buttons'];

            // Validate hero section exists
            $heroSection = $this->heroSectionModel->find($heroSectionId);
            if (!$heroSection) {
                $this->sendJsonResponse(['success' => false, 'message' => 'Hero Section không tồn tại']);
                return;
            }

            // Clear existing buttons for this hero section
            $this->heroButtonModel->deleteByHeroSectionId($heroSectionId);

            // Insert new buttons
            $insertedCount = 0;
            foreach ($buttons as $buttonData) {
                $buttonData['hero_section_id'] = $heroSectionId;
                
                // Validate button data
                $errors = $this->heroButtonModel->validateButtonData($buttonData, false);
                if (!empty($errors)) {
                    continue; // Skip invalid buttons
                }

                $result = $this->heroButtonModel->createButton($buttonData);
                if ($result) {
                    $insertedCount++;
                }
            }

            if ($insertedCount > 0) {
                $this->sendJsonResponse([
                    'success' => true, 
                    'message' => "Đã cập nhật {$insertedCount} nút bấm thành công"
                ]);
            } else {
                $this->sendJsonResponse(['success' => false, 'message' => 'Không thể cập nhật nút bấm']);
            }

        } catch (Exception $e) {
            error_log("Error in updateButtons: " . $e->getMessage());
            $this->sendJsonResponse(['success' => false, 'message' => 'Có lỗi xảy ra: ' . $e->getMessage()]);
        }
    }

    
    /**
     * Delete button
     */
    public function deleteButton($id): void {
        if (!$this->requireAdmin()) {
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->sendJsonResponse(['success' => false, 'message' => 'Method not allowed']);
            return;
        }

        try {
            $result = $this->heroButtonModel->deleteButton($id);
            
            if ($result) {
                $this->sendJsonResponse(['success' => true, 'message' => 'Button đã được xóa thành công']);
            } else {
                $this->sendJsonResponse(['success' => false, 'message' => 'Không thể xóa button']);
            }

        } catch (Exception $e) {
            error_log("Error in deleteButton: " . $e->getMessage());
            $this->sendJsonResponse(['success' => false, 'message' => 'Có lỗi xảy ra']);
        }
    }

    /**
     * Reorder buttons
     */
    public function reorderButtons(): void {
        if (!$this->requireAdmin()) {
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->sendJsonResponse(['success' => false, 'message' => 'Method not allowed']);
            return;
        }

        try {
            $heroSectionId = $_POST['hero_section_id'] ?? null;
            $buttonIds = $_POST['button_ids'] ?? [];
            
            if (!$heroSectionId || empty($buttonIds)) {
                $this->sendJsonResponse(['success' => false, 'message' => 'Dữ liệu không hợp lệ']);
                return;
            }

            $result = $this->heroButtonModel->reorderButtons($heroSectionId, $buttonIds);
            
            if ($result) {
                $this->sendJsonResponse(['success' => true, 'message' => 'Thứ tự đã được cập nhật']);
            } else {
                $this->sendJsonResponse(['success' => false, 'message' => 'Không thể cập nhật thứ tự']);
            }

        } catch (Exception $e) {
            error_log("Error in reorderButtons: " . $e->getMessage());
            $this->sendJsonResponse(['success' => false, 'message' => 'Có lỗi xảy ra']);
        }
    }

    // ========== API METHODS ==========

    /**
     * Get hero section for API
     */
    public function getApi($id = null): void {
        try {
            $heroSection = $this->heroSectionModel->getForApi($id);
            
            if ($heroSection) {
                $this->sendJsonResponse(['success' => true, 'data' => $heroSection]);
            } else {
                $this->sendJsonResponse(['success' => false, 'message' => 'Hero Section không tồn tại']);
            }

        } catch (Exception $e) {
            error_log("Error in getApi: " . $e->getMessage());
            $this->sendJsonResponse(['success' => false, 'message' => 'Có lỗi xảy ra']);
        }
    }

    /**
     * Upload image via AJAX
     */
    public function uploadImage(): void {
        if (!$this->requireAdmin()) {
            return;
        }

        try {
            if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
                $this->sendJsonResponse(['success' => false, 'message' => 'Không có file được tải lên hoặc lỗi file']);
                return;
            }

            $file = $_FILES['image'];
            $fileName = time() . '_' . basename($file['name']);
            $targetDir = __DIR__ . '/../../assets/images/home/';

            // Create directory if not exists
            if (!is_dir($targetDir)) {
                mkdir($targetDir, 0755, true);
            }

            $targetPath = $targetDir . $fileName;

            if (move_uploaded_file($file['tmp_name'], $targetPath)) {
                $relativeUrl = 'home/' . $fileName;
                $this->sendJsonResponse(['success' => true, 'url' => $relativeUrl]);
            } else {
                $this->sendJsonResponse(['success' => false, 'message' => 'Không thể lưu file tải lên']);
            }

        } catch (Exception $e) {
            error_log("Error in heroSection uploadImage: " . $e->getMessage());
            $this->sendJsonResponse(['success' => false, 'message' => 'Có lỗi xảy ra khi tải ảnh']);
        }
    }

    // ========== HELPER METHODS ==========

    /**
     * Require admin authentication
     */
    private function requireAdmin(): bool {
        if (!$this->authService->isLoggedIn() || !$this->authService->hasRole('admin')) {
            $this->redirect('?page=login');
            return false;
        }
        return true;
    }

    /**
     * Get current user
     */
    private function getCurrentUser() {
        return $this->authService->getCurrentUser();
    }

    /**
     * Get request data (handles JSON and POST)
     */
    private function getRequestData(): array {
        $contentType = $_SERVER["CONTENT_TYPE"] ?? '';
        
        if (stripos($contentType, 'application/json') !== false) {
            $content = trim(file_get_contents("php://input"));
            $decoded = json_decode($content, true);
            return is_array($decoded) ? $decoded : [];
        }
        
        return $_POST;
    }

    /**
     * Check if request is AJAX
     */
    private function isAjaxRequest(): bool {
        return (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') ||
               (stripos($_SERVER['CONTENT_TYPE'] ?? '', 'application/json') !== false);
    }

    /**
     * Validate hero section data
     */
    private function validateHeroSectionData($data, $isUpdate = false): array {
        $errors = [];

        if (empty($data['title_main'])) {
            $errors[] = 'Tiêu đề là bắt buộc';
        }

        // Validate color format
        $colorFields = ['background_color', 'text_color', 'highlight_color'];
        foreach ($colorFields as $field) {
            if (!empty($data[$field]) && !$this->isValidColor($data[$field])) {
                $errors[] = ucfirst(str_replace('_', ' ', $field)) . ' phải là màu hợp lệ';
            }
        }

        return $errors;
    }

    /**
     * Check if color is valid
     */
    private function isValidColor($color): bool {
        // Hex color
        if (preg_match('/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/', $color)) {
            return true;
        }
        
        // RGB/RGBA color
        if (preg_match('/^rgba?\(\s*\d+\s*,\s*\d+\s*,\s*\d+\s*(,\s*[\d.]+\s*)?\)$/', $color)) {
            return true;
        }
        
        // Common color names
        $commonColors = ['red', 'green', 'blue', 'white', 'black', 'gray', 'grey', 'yellow', 'orange', 'purple', 'pink', 'brown', 'transparent'];
        if (in_array(strtolower($color), $commonColors)) {
            return true;
        }
        
        return false;
    }

    /**
     * Render view with layout
     */
    private function renderView(string $view, array $data = []): void {
        // Load view_init to make services available
        require_once __DIR__ . '/../../core/view_init.php';
        
        $viewPath = __DIR__ . '/../views/' . $view . '.php';
        
        error_log("HeroSection renderView - Looking for: " . $viewPath);
        error_log("HeroSection renderView - File exists: " . (file_exists($viewPath) ? 'YES' : 'NO'));
        
        if (!file_exists($viewPath)) {
            throw new Exception("View not found: $view at path: $viewPath");
        }

        // Extract data for view and layout
        extract($data);
        
        // Set global flag for admin layout
        global $useAdminLayout;
        $useAdminLayout = true;
        
        // Set content variable for admin layout
        $content = $viewPath;
        
        // Render view content first to extract variables
        ob_start();
        include $viewPath;
        $viewContent = ob_get_clean();
        
        // Set content as rendered HTML
        $content = $viewContent;
        
        // Include admin layout
        $layoutPath = __DIR__ . '/../views/_layout/admin_master.php';
        if (file_exists($layoutPath)) {
            include $layoutPath;
        } else {
            echo $content;
        }
    }

    /**
     * Set flash message
     */
    public function setFlashMessage(string $type, string $message): void {
        $_SESSION["flash_{$type}"] = $message;
    }
    
    /**
     * Redirect to URL
     */
    private function redirect(string $url): void {
        if (strpos($url, '?') === 0) {
            // Already a query string, use as-is
            header("Location: $url");
        } else {
            // Convert to query string format
            header("Location: ?page=" . ltrim($url, '/'));
        }
        exit;
    }
    
    /**
     * Send JSON response
     */
    public function sendJsonResponse(array $data): void {
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}
