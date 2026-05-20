<?php

require_once __DIR__ . '/../services/AuthService.php';
require_once __DIR__ . '/../models/HeroSectionModel.php';
require_once __DIR__ . '/../models/HeroButtonModel.php';
require_once __DIR__ . '/../models/FeaturedProductsSectionModel.php';
require_once __DIR__ . '/../models/LatestProductsSectionModel.php';
require_once __DIR__ . '/../models/BudgetProductsSectionModel.php';
require_once __DIR__ . '/../models/SaleProductsSectionModel.php';
require_once __DIR__ . '/../../core/view_init.php';

class HomepageController {
    private $authService;
    private $heroSectionModel;
    private $heroButtonModel;
    private $featuredProductsSectionModel;
    private $latestProductsSectionModel;
    private $budgetProductsSectionModel;
    private $saleProductsSectionModel;

    public function __construct() {
        $this->authService = new AuthService();
        $this->heroSectionModel = new HeroSectionModel();
        $this->heroButtonModel = new HeroButtonModel();
        $this->featuredProductsSectionModel = new FeaturedProductsSectionModel();
        $this->latestProductsSectionModel = new LatestProductsSectionModel();
        $this->budgetProductsSectionModel = new BudgetProductsSectionModel();
        $this->saleProductsSectionModel = new SaleProductsSectionModel();
    }

    /**
     * Display homepage management page (both Hero Section and Featured Products Section)
     */
    public function index(): void {
        if (!$this->requireAdmin()) {
            return;
        }

        try {
            $heroSections = $this->heroSectionModel->getAllForAdmin();
            
            // Try to get featured products section, create if not exists
            $featuredProductsSection = null;
            try {
                $featuredProductsSection = $this->featuredProductsSectionModel->getFirst();
                
                // If no section exists, create default one
                if (!$featuredProductsSection) {
                    $this->featuredProductsSectionModel->createSection([
                        'title' => '<h2 class="section-title">Sản phẩm <span class="highlight">Nổi bật</span></h2>',
                        'is_active' => 1
                    ]);
                    $featuredProductsSection = $this->featuredProductsSectionModel->getFirst();
                }
            } catch (Exception $e) {
                error_log("Featured products section error: " . $e->getMessage());
                // Set default data if table doesn't exist yet
                $featuredProductsSection = [
                    'id' => 0,
                    'title' => '<h2 class="section-title">Sản phẩm <span class="highlight">Nổi bật</span></h2>',
                    'is_active' => 1
                ];
            }
            
            // Initialize latest products section
            $latestProductsSection = null;
            try {
                $latestProductsSection = $this->latestProductsSectionModel->getFirst();
                
                if (!$latestProductsSection) {
                    $this->latestProductsSectionModel->createSection([
                        'title' => '<h2 class="section-title">Sản phẩm <span class="highlight">Mới nhất</span></h2>',
                        'is_active' => 1
                    ]);
                    $latestProductsSection = $this->latestProductsSectionModel->getFirst();
                }
            } catch (Exception $e) {
                error_log("Latest products section error: " . $e->getMessage());
                $latestProductsSection = [
                    'id' => 0,
                    'title' => '<h2 class="section-title">Sản phẩm <span class="highlight">Mới nhất</span></h2>',
                    'is_active' => 1
                ];
            }
            
            // Initialize budget products section
            $budgetProductsSection = null;
            try {
                $budgetProductsSection = $this->budgetProductsSectionModel->getFirst();
                
                if (!$budgetProductsSection) {
                    $this->budgetProductsSectionModel->createSection([
                        'title' => '<h2 class="section-title">Sản phẩm <span class="highlight">Giá rẻ</span></h2>',
                        'is_active' => 1
                    ]);
                    $budgetProductsSection = $this->budgetProductsSectionModel->getFirst();
                }
            } catch (Exception $e) {
                error_log("Budget products section error: " . $e->getMessage());
                $budgetProductsSection = [
                    'id' => 0,
                    'title' => '<h2 class="section-title">Sản phẩm <span class="highlight">Giá rẻ</span></h2>',
                    'is_active' => 1
                ];
            }
            
            // Initialize sale products section
            $saleProductsSection = null;
            try {
                $saleProductsSection = $this->saleProductsSectionModel->getFirst();
                
                if (!$saleProductsSection) {
                    $this->saleProductsSectionModel->createSection([
                        'title' => '<h2 class="section-title">Sản phẩm <span class="highlight">Giảm giá</span></h2>',
                        'is_active' => 1
                    ]);
                    $saleProductsSection = $this->saleProductsSectionModel->getFirst();
                }
            } catch (Exception $e) {
                error_log("Sale products section error: " . $e->getMessage());
                $saleProductsSection = [
                    'id' => 0,
                    'title' => '<h2 class="section-title">Sản phẩm <span class="highlight">Giảm giá</span></h2>',
                    'is_active' => 1
                ];
            }
            
            $data = [
                'title' => 'Quản lý Trang chủ',
                'heroSections' => $heroSections,
                'featuredProductsSection' => $featuredProductsSection,
                'latestProductsSection' => $latestProductsSection,
                'budgetProductsSection' => $budgetProductsSection,
                'saleProductsSection' => $saleProductsSection,
                'user' => $this->getCurrentUser()
            ];

            $this->renderView('admin/homepage/index', $data);
        } catch (Exception $e) {
            error_log("Error in homepage index: " . $e->getMessage());
            $this->setFlashMessage('error', 'Có lỗi xảy ra khi tải trang quản lý: ' . $e->getMessage());
            $this->redirect('?page=admin&module=dashboard');
        }
    }

    /**
     * Display edit hero section form
     */
    public function editHero($id): void {
        if (!$this->requireAdmin()) {
            return;
        }

        try {
            $heroSection = $this->heroSectionModel->getWithButtons($id);
            
            if (!$heroSection) {
                $this->setFlashMessage('error', 'Hero Section không tồn tại');
                $this->redirect('?page=admin&module=homepage');
                return;
            }

            $data = [
                'title' => 'Chỉnh sửa Hero Section',
                'heroSection' => $heroSection,
                'user' => $this->getCurrentUser()
            ];

            $this->renderView('admin/homepage/hero_section/edit', $data);
        } catch (Exception $e) {
            error_log("Error in editHero: " . $e->getMessage());
            $this->setFlashMessage('error', 'Có lỗi xảy ra khi tải Hero Section');
            $this->redirect('?page=admin&module=homepage');
        }
    }

    /**
     * Update hero section
     */
    public function updateHero($id): void {
        if (!$this->requireAdmin()) {
            return;
        }

        try {
            // Delegate to HeroSectionController
            require_once __DIR__ . '/HeroSectionController.php';
            $heroController = new HeroSectionController();
            $heroController->update($id);
        } catch (Exception $e) {
            error_log("Error in updateHero: " . $e->getMessage());
            $this->setFlashMessage('error', 'Có lỗi xảy ra khi cập nhật Hero Section');
            $this->redirect('?page=admin&module=homepage');
        }
    }

    /**
     * Toggle hero section status
     */
    /**
     * Toggle hero status
     */
    public function toggleHeroStatus(): void {
        if (!$this->requireAdmin()) {
            return;
        }

        try {
            $data = json_decode(file_get_contents('php://input'), true);
            $id = $data['id'] ?? 0;

            if ($id > 0) {
                $result = $this->heroSectionModel->toggleStatus($id);
                $this->sendJsonResponse(['success' => $result, 'message' => $result ? 'Đã cập nhật trạng thái' : 'Có lỗi xảy ra']);
            } else {
                $this->sendJsonResponse(['success' => false, 'message' => 'Invalid ID']);
            }
        } catch (Exception $e) {
            error_log("Error in toggleHeroStatus: " . $e->getMessage());
            $this->sendJsonResponse(['success' => false, 'message' => 'Có lỗi xảy ra']);
        }
    }

    /**
     * Display edit featured products section form
     */
    public function editFeaturedProducts(): void {
        if (!$this->requireAdmin()) {
            return;
        }

        try {
            $featuredProductsSection = $this->featuredProductsSectionModel->getFirst();
            
            if (!$featuredProductsSection) {
                // Create default section if not exists
                $this->featuredProductsSectionModel->createSection([
                    'title' => '<h2 class="section-title">Sản phẩm <span class="highlight">Nổi bật</span></h2>',
                    'is_active' => 1
                ]);
                $featuredProductsSection = $this->featuredProductsSectionModel->getFirst();
            }

            $data = [
                'title' => 'Chỉnh sửa Section Sản phẩm Nổi bật',
                'featuredProductsSection' => $featuredProductsSection,
                'user' => $this->getCurrentUser()
            ];

            $this->renderView('admin/homepage/edit_featured_products', $data);
        } catch (Exception $e) {
            error_log("Error in editFeaturedProducts: " . $e->getMessage());
            $this->setFlashMessage('error', 'Có lỗi xảy ra khi tải section');
            $this->redirect('?page=admin&module=homepage');
        }
    }

    /**
     * Update featured products section
     */
    public function updateFeaturedProducts(): void {
        if (!$this->requireAdmin()) {
            return;
        }

        try {
            $id = $_POST['id'] ?? 0;
            $title = $_POST['title'] ?? '';
            $isActive = isset($_POST['is_active']) && $_POST['is_active'] == '1' ? 1 : 0;

            if ($id > 0) {
                $result = $this->featuredProductsSectionModel->updateSection($id, [
                    'title' => $title,
                    'is_active' => $isActive
                ]);

                if ($this->isAjaxRequest()) {
                    $this->sendJsonResponse([
                        'success' => $result,
                        'message' => $result ? 'Đã cập nhật section thành công' : 'Có lỗi xảy ra khi cập nhật'
                    ]);
                } else {
                    if ($result) {
                        $this->setFlashMessage('success', 'Đã cập nhật section thành công');
                    } else {
                        $this->setFlashMessage('error', 'Có lỗi xảy ra khi cập nhật');
                    }
                    $this->redirect('?page=admin&module=homepage');
                }
            } else {
                if ($this->isAjaxRequest()) {
                    $this->sendJsonResponse(['success' => false, 'message' => 'Invalid ID']);
                } else {
                    $this->setFlashMessage('error', 'Invalid ID');
                    $this->redirect('?page=admin&module=homepage');
                }
            }
        } catch (Exception $e) {
            error_log("Error in updateFeaturedProducts: " . $e->getMessage());
            if ($this->isAjaxRequest()) {
                $this->sendJsonResponse(['success' => false, 'message' => 'Có lỗi xảy ra: ' . $e->getMessage()]);
            } else {
                $this->setFlashMessage('error', 'Có lỗi xảy ra: ' . $e->getMessage());
                $this->redirect('?page=admin&module=homepage');
            }
        }
    }

    /**
     * Toggle featured products section status
     */
    public function toggleFeaturedProductsStatus(): void {
        if (!$this->requireAdmin()) {
            return;
        }

        try {
            $data = json_decode(file_get_contents('php://input'), true);
            $id = $data['id'] ?? 0;

            if ($id > 0) {
                $result = $this->featuredProductsSectionModel->toggleStatus($id);
                $this->sendJsonResponse(['success' => $result, 'message' => $result ? 'Đã cập nhật trạng thái' : 'Có lỗi xảy ra']);
            } else {
                $this->sendJsonResponse(['success' => false, 'message' => 'Invalid ID']);
            }
        } catch (Exception $e) {
            error_log("Error in toggleFeaturedProductsStatus: " . $e->getMessage());
            $this->sendJsonResponse(['success' => false, 'message' => 'Có lỗi xảy ra']);
        }
    }

    // Helper methods from HeroSectionController
    private function requireAdmin(): bool {
        if (!$this->authService->isLoggedIn()) {
            header('Location: ?page=login');
            return false;
        }

        $user = $this->authService->getCurrentUser();
        if ($user['role'] !== 'admin') {
            header('Location: ?page=home');
            return false;
        }

        return true;
    }

    private function getCurrentUser() {
        return $this->authService->getCurrentUser();
    }

    private function setFlashMessage($type, $message): void {
        $_SESSION['flash_' . $type] = $message;
    }

    private function redirect($url): void {
        header('Location: ' . $url);
        exit;
    }

    private function sendJsonResponse($data): void {
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    private function renderView($view, $data = []): void {
        $viewPath = __DIR__ . '/../views/' . $view . '.php';
        
        if (!file_exists($viewPath)) {
            throw new Exception("View not found: $view");
        }

        // Extract data for view
        extract($data);
        
        // Start output buffering
        ob_start();
        include $viewPath;
        $content = ob_get_clean();
        
        // Include admin layout
        $layoutPath = __DIR__ . '/../views/_layout/admin_master.php';
        if (file_exists($layoutPath)) {
            include $layoutPath;
        } else {
            echo $content;
        }
    }

    public function createButton(): void {
        require_once __DIR__ . '/HeroSectionController.php';
        $heroController = new HeroSectionController();
        $heroController->createButton();
    }

    public function updateButton($id): void {
        require_once __DIR__ . '/HeroSectionController.php';
        $heroController = new HeroSectionController();
        $heroController->updateButton($id);
    }

    public function deleteButton($id): void {
        require_once __DIR__ . '/HeroSectionController.php';
        $heroController = new HeroSectionController();
        $heroController->deleteButton($id);
    }

    public function updateButtons(): void {
        require_once __DIR__ . '/HeroSectionController.php';
        $heroController = new HeroSectionController();
        $heroController->updateButtons();
    }

    public function uploadImage(): void {
        require_once __DIR__ . '/HeroSectionController.php';
        $heroController = new HeroSectionController();
        $heroController->uploadImage();
    }

    public function reorderButtons(): void {
        require_once __DIR__ . '/HeroSectionController.php';
        $heroController = new HeroSectionController();
        $heroController->reorderButtons();
    }

    /**
     * Edit latest products section
     */
    public function editLatestProducts(): void {
        if (!$this->requireAdmin()) {
            return;
        }

        try {
            $id = $_GET['id'] ?? 0;
            if ($id > 0) {
                $section = $this->latestProductsSectionModel->getFirst();
                if ($section && $section['id'] == $id) {
                    $data = [
                        'title' => 'Chỉnh sửa Section Sản phẩm Mới nhất',
                        'section' => $section,
                        'user' => $this->getCurrentUser()
                    ];
                    $this->renderView('admin/homepage/edit_latest_products', $data);
                } else {
                    $this->setFlashMessage('error', 'Section không tồn tại');
                    $this->redirect('?page=admin&module=homepage');
                }
            } else {
                $this->setFlashMessage('error', 'ID không hợp lệ');
                $this->redirect('?page=admin&module=homepage');
            }
        } catch (Exception $e) {
            error_log("Error in editLatestProducts: " . $e->getMessage());
            $this->setFlashMessage('error', 'Có lỗi xảy ra: ' . $e->getMessage());
            $this->redirect('?page=admin&module=homepage');
        }
    }

    /**
     * Edit budget products section
     */
    public function editBudgetProducts(): void {
        if (!$this->requireAdmin()) {
            return;
        }

        try {
            $id = $_GET['id'] ?? 0;
            if ($id > 0) {
                $section = $this->budgetProductsSectionModel->getFirst();
                if ($section && $section['id'] == $id) {
                    $data = [
                        'title' => 'Chỉnh sửa Section Sản phẩm Giá rẻ',
                        'section' => $section,
                        'user' => $this->getCurrentUser()
                    ];
                    $this->renderView('admin/homepage/edit_budget_products', $data);
                } else {
                    $this->setFlashMessage('error', 'Section không tồn tại');
                    $this->redirect('?page=admin&module=homepage');
                }
            } else {
                $this->setFlashMessage('error', 'ID không hợp lệ');
                $this->redirect('?page=admin&module=homepage');
            }
        } catch (Exception $e) {
            error_log("Error in editBudgetProducts: " . $e->getMessage());
            $this->setFlashMessage('error', 'Có lỗi xảy ra: ' . $e->getMessage());
            $this->redirect('?page=admin&module=homepage');
        }
    }

    /**
     * Edit sale products section
     */
    public function editSaleProducts(): void {
        if (!$this->requireAdmin()) {
            return;
        }

        try {
            $id = $_GET['id'] ?? 0;
            if ($id > 0) {
                $section = $this->saleProductsSectionModel->getFirst();
                if ($section && $section['id'] == $id) {
                    $data = [
                        'title' => 'Chỉnh sửa Section Sản phẩm Giảm giá',
                        'section' => $section,
                        'user' => $this->getCurrentUser()
                    ];
                    $this->renderView('admin/homepage/edit_sale_products', $data);
                } else {
                    $this->setFlashMessage('error', 'Section không tồn tại');
                    $this->redirect('?page=admin&module=homepage');
                }
            } else {
                $this->setFlashMessage('error', 'ID không hợp lệ');
                $this->redirect('?page=admin&module=homepage');
            }
        } catch (Exception $e) {
            error_log("Error in editSaleProducts: " . $e->getMessage());
            $this->setFlashMessage('error', 'Có lỗi xảy ra: ' . $e->getMessage());
            $this->redirect('?page=admin&module=homepage');
        }
    }

    /**
     * Update latest products section
     */
    public function updateLatestProducts(): void {
        if (!$this->requireAdmin()) {
            return;
        }

        try {
            $id = $_POST['id'] ?? 0;
            $title = $_POST['title'] ?? '';
            $isActive = isset($_POST['is_active']) && $_POST['is_active'] == '1' ? 1 : 0;

            if ($id > 0) {
                $result = $this->latestProductsSectionModel->updateSection($id, [
                    'title' => $title,
                    'is_active' => $isActive
                ]);

                if ($this->isAjaxRequest()) {
                    $this->sendJsonResponse([
                        'success' => $result,
                        'message' => $result ? 'Đã cập nhật section thành công' : 'Có lỗi xảy ra khi cập nhật'
                    ]);
                } else {
                    if ($result) {
                        $this->setFlashMessage('success', 'Đã cập nhật section thành công');
                    } else {
                        $this->setFlashMessage('error', 'Có lỗi xảy ra khi cập nhật');
                    }
                    $this->redirect('?page=admin&module=homepage');
                }
            } else {
                if ($this->isAjaxRequest()) {
                    $this->sendJsonResponse(['success' => false, 'message' => 'Invalid ID']);
                } else {
                    $this->setFlashMessage('error', 'Invalid ID');
                    $this->redirect('?page=admin&module=homepage');
                }
            }
        } catch (Exception $e) {
            error_log("Error in updateLatestProducts: " . $e->getMessage());
            if ($this->isAjaxRequest()) {
                $this->sendJsonResponse(['success' => false, 'message' => 'Có lỗi xảy ra: ' . $e->getMessage()]);
            } else {
                $this->setFlashMessage('error', 'Có lỗi xảy ra: ' . $e->getMessage());
                $this->redirect('?page=admin&module=homepage');
            }
        }
    }

    /**
     * Update budget products section
     */
    public function updateBudgetProducts(): void {
        if (!$this->requireAdmin()) {
            return;
        }

        try {
            $id = $_POST['id'] ?? 0;
            $title = $_POST['title'] ?? '';
            $isActive = isset($_POST['is_active']) && $_POST['is_active'] == '1' ? 1 : 0;

            if ($id > 0) {
                $result = $this->budgetProductsSectionModel->updateSection($id, [
                    'title' => $title,
                    'is_active' => $isActive
                ]);

                if ($this->isAjaxRequest()) {
                    $this->sendJsonResponse([
                        'success' => $result,
                        'message' => $result ? 'Đã cập nhật section thành công' : 'Có lỗi xảy ra khi cập nhật'
                    ]);
                } else {
                    if ($result) {
                        $this->setFlashMessage('success', 'Đã cập nhật section thành công');
                    } else {
                        $this->setFlashMessage('error', 'Có lỗi xảy ra khi cập nhật');
                    }
                    $this->redirect('?page=admin&module=homepage');
                }
            } else {
                if ($this->isAjaxRequest()) {
                    $this->sendJsonResponse(['success' => false, 'message' => 'Invalid ID']);
                } else {
                    $this->setFlashMessage('error', 'Invalid ID');
                    $this->redirect('?page=admin&module=homepage');
                }
            }
        } catch (Exception $e) {
            error_log("Error in updateBudgetProducts: " . $e->getMessage());
            if ($this->isAjaxRequest()) {
                $this->sendJsonResponse(['success' => false, 'message' => 'Có lỗi xảy ra: ' . $e->getMessage()]);
            } else {
                $this->setFlashMessage('error', 'Có lỗi xảy ra: ' . $e->getMessage());
                $this->redirect('?page=admin&module=homepage');
            }
        }
    }

    /**
     * Update sale products section
     */
    public function updateSaleProducts(): void {
        if (!$this->requireAdmin()) {
            return;
        }

        try {
            $id = $_POST['id'] ?? 0;
            $title = $_POST['title'] ?? '';
            $isActive = isset($_POST['is_active']) && $_POST['is_active'] == '1' ? 1 : 0;

            if ($id > 0) {
                $result = $this->saleProductsSectionModel->updateSection($id, [
                    'title' => $title,
                    'is_active' => $isActive
                ]);

                if ($this->isAjaxRequest()) {
                    $this->sendJsonResponse([
                        'success' => $result,
                        'message' => $result ? 'Đã cập nhật section thành công' : 'Có lỗi xảy ra khi cập nhật'
                    ]);
                } else {
                    if ($result) {
                        $this->setFlashMessage('success', 'Đã cập nhật section thành công');
                    } else {
                        $this->setFlashMessage('error', 'Có lỗi xảy ra khi cập nhật');
                    }
                    $this->redirect('?page=admin&module=homepage');
                }
            } else {
                if ($this->isAjaxRequest()) {
                    $this->sendJsonResponse(['success' => false, 'message' => 'Invalid ID']);
                } else {
                    $this->setFlashMessage('error', 'Invalid ID');
                    $this->redirect('?page=admin&module=homepage');
                }
            }
        } catch (Exception $e) {
            error_log("Error in updateSaleProducts: " . $e->getMessage());
            if ($this->isAjaxRequest()) {
                $this->sendJsonResponse(['success' => false, 'message' => 'Có lỗi xảy ra: ' . $e->getMessage()]);
            } else {
                $this->setFlashMessage('error', 'Có lỗi xảy ra: ' . $e->getMessage());
                $this->redirect('?page=admin&module=homepage');
            }
        }
    }

    /**
     * Toggle latest products section status
     */
    public function toggleLatestProductsStatus(): void {
        if (!$this->requireAdmin()) {
            return;
        }

        try {
            $data = json_decode(file_get_contents('php://input'), true);
            $id = $data['id'] ?? 0;

            if ($id > 0) {
                $result = $this->latestProductsSectionModel->toggleStatus($id);
                $this->sendJsonResponse(['success' => $result, 'message' => $result ? 'Đã cập nhật trạng thái' : 'Có lỗi xảy ra']);
            } else {
                $this->sendJsonResponse(['success' => false, 'message' => 'Invalid ID']);
            }
        } catch (Exception $e) {
            error_log("Error in toggleLatestProductsStatus: " . $e->getMessage());
            $this->sendJsonResponse(['success' => false, 'message' => 'Có lỗi xảy ra']);
        }
    }

    /**
     * Toggle budget products section status
     */
    public function toggleBudgetProductsStatus(): void {
        if (!$this->requireAdmin()) {
            return;
        }

        try {
            $data = json_decode(file_get_contents('php://input'), true);
            $id = $data['id'] ?? 0;

            if ($id > 0) {
                $result = $this->budgetProductsSectionModel->toggleStatus($id);
                $this->sendJsonResponse(['success' => $result, 'message' => $result ? 'Đã cập nhật trạng thái' : 'Có lỗi xảy ra']);
            } else {
                $this->sendJsonResponse(['success' => false, 'message' => 'Invalid ID']);
            }
        } catch (Exception $e) {
            error_log("Error in toggleBudgetProductsStatus: " . $e->getMessage());
            $this->sendJsonResponse(['success' => false, 'message' => 'Có lỗi xảy ra']);
        }
    }

    /**
     * Toggle sale products section status
     */
    public function toggleSaleProductsStatus(): void {
        if (!$this->requireAdmin()) {
            return;
        }

        try {
            $data = json_decode(file_get_contents('php://input'), true);
            $id = $data['id'] ?? 0;

            if ($id > 0) {
                $result = $this->saleProductsSectionModel->toggleStatus($id);
                $this->sendJsonResponse(['success' => $result, 'message' => $result ? 'Đã cập nhật trạng thái' : 'Có lỗi xảy ra']);
            } else {
                $this->sendJsonResponse(['success' => false, 'message' => 'Invalid ID']);
            }
        } catch (Exception $e) {
            error_log("Error in toggleSaleProductsStatus: " . $e->getMessage());
            $this->sendJsonResponse(['success' => false, 'message' => 'Có lỗi xảy ra']);
        }
    }

    /**
     * Check if request is AJAX
     */
    private function isAjaxRequest(): bool {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest';
    }
}
