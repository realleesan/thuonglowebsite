<?php

require_once __DIR__ . '/../services/AuthService.php';
require_once __DIR__ . '/../models/HeroSectionModel.php';
require_once __DIR__ . '/../models/HeroButtonModel.php';
require_once __DIR__ . '/../models/FeaturedProductsSectionModel.php';
require_once __DIR__ . '/../models/LatestProductsSectionModel.php';
require_once __DIR__ . '/../models/BudgetProductsSectionModel.php';
require_once __DIR__ . '/../models/SaleProductsSectionModel.php';
require_once __DIR__ . '/../models/FeaturedCategoriesSectionModel.php';
require_once __DIR__ . '/../models/FeaturedBrandsSectionModel.php';
require_once __DIR__ . '/../models/LatestNewsSectionModel.php';
require_once __DIR__ . '/../models/WhyChooseSectionModel.php';
require_once __DIR__ . '/../models/WhyChooseItemModel.php';
require_once __DIR__ . '/../models/CustomCategorySectionModel.php';
require_once __DIR__ . '/../models/CategoriesModel.php';
require_once __DIR__ . '/../models/CtaSectionModel.php';
require_once __DIR__ . '/../models/TopBannerModel.php';
require_once __DIR__ . '/../../core/view_init.php';

class HomepageController {
    private $authService;
    private $heroSectionModel;
    private $heroButtonModel;
    private $featuredProductsSectionModel;
    private $latestProductsSectionModel;
    private $budgetProductsSectionModel;
    private $saleProductsSectionModel;
    private $featuredCategoriesSectionModel;
    private $featuredBrandsSectionModel;
    private $latestNewsSectionModel;
    private $whyChooseSectionModel;
    private $whyChooseItemModel;
    private $customCategorySectionModel;
    private $categoriesModel;
    private $ctaSectionModel;
    private $topBannerModel;

    public function __construct() {
        $this->authService = new AuthService();
        $this->heroSectionModel = new HeroSectionModel();
        $this->heroButtonModel = new HeroButtonModel();
        $this->featuredProductsSectionModel = new FeaturedProductsSectionModel();
        $this->latestProductsSectionModel = new LatestProductsSectionModel();
        $this->budgetProductsSectionModel = new BudgetProductsSectionModel();
        $this->saleProductsSectionModel = new SaleProductsSectionModel();
        $this->featuredCategoriesSectionModel = new FeaturedCategoriesSectionModel();
        $this->featuredBrandsSectionModel = new FeaturedBrandsSectionModel();
        $this->latestNewsSectionModel = new LatestNewsSectionModel();
        $this->whyChooseSectionModel = new WhyChooseSectionModel();
        $this->whyChooseItemModel = new WhyChooseItemModel();
        $this->customCategorySectionModel = new CustomCategorySectionModel();
        $this->categoriesModel = new CategoriesModel();
        $this->ctaSectionModel = new CtaSectionModel();
        $this->topBannerModel = new TopBannerModel();
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
            
            // Initialize featured categories section
            $featuredCategoriesSection = null;
            try {
                $featuredCategoriesSection = $this->featuredCategoriesSectionModel->getFirst();
                
                if (!$featuredCategoriesSection) {
                    $this->featuredCategoriesSectionModel->createSection([
                        'title' => '<h2 class="section-title">Danh mục <span class="highlight">Nổi bật</span></h2>',
                        'is_active' => 1
                    ]);
                    $featuredCategoriesSection = $this->featuredCategoriesSectionModel->getFirst();
                }
            } catch (Exception $e) {
                error_log("Featured categories section error: " . $e->getMessage());
                $featuredCategoriesSection = [
                    'id' => 0,
                    'title' => '<h2 class="section-title">Danh mục <span class="highlight">Nổi bật</span></h2>',
                    'is_active' => 1
                ];
            }
            
            // Initialize featured brands section
            $featuredBrandsSection = null;
            try {
                $featuredBrandsSection = $this->featuredBrandsSectionModel->getFirst();
                
                if (!$featuredBrandsSection) {
                    $this->featuredBrandsSectionModel->createSection([
                        'title' => '<h2 class="section-title">Thương hiệu <span class="highlight">Nổi bật</span></h2>',
                        'is_active' => 1
                    ]);
                    $featuredBrandsSection = $this->featuredBrandsSectionModel->getFirst();
                }
            } catch (Exception $e) {
                error_log("Featured brands section error: " . $e->getMessage());
                $featuredBrandsSection = [
                    'id' => 0,
                    'title' => '<h2 class="section-title">Thương hiệu <span class="highlight">Nổi bật</span></h2>',
                    'is_active' => 1
                ];
            }
            
            // Initialize latest news section
            $latestNewsSection = null;
            try {
                $latestNewsSection = $this->latestNewsSectionModel->getFirst();
                
                if (!$latestNewsSection) {
                    $this->latestNewsSectionModel->createSection([
                        'title' => '<h2 class="section-title">Tin tức <span class="highlight">Mới nhất</span></h2>',
                        'is_active' => 1
                    ]);
                    $latestNewsSection = $this->latestNewsSectionModel->getFirst();
                }
            } catch (Exception $e) {
                error_log("Latest news section error: " . $e->getMessage());
                $latestNewsSection = [
                    'id' => 0,
                    'title' => '<h2 class="section-title">Tin tức <span class="highlight">Mới nhất</span></h2>',
                    'is_active' => 1
                ];
            }

            // Initialize why choose section
            $whyChooseSection = null;
            try {
                $whyChooseSection = $this->whyChooseSectionModel->getFirst();
                
                if (!$whyChooseSection) {
                    $this->whyChooseSectionModel->createSection([
                        'title' => '<h2 class="section-title">Tại sao chọn <span class="highlight">ThuongLo?</span></h2>',
                        'is_active' => 1
                    ]);
                    $whyChooseSection = $this->whyChooseSectionModel->getFirst();
                }
            } catch (Exception $e) {
                error_log("Why choose section error: " . $e->getMessage());
                $whyChooseSection = [
                    'id' => 0,
                    'title' => '<h2 class="section-title">Tại sao chọn <span class="highlight">ThuongLo?</span></h2>',
                    'is_active' => 1
                ];
            }
            
            // Fetch custom category sections
            $customCategorySections = [];
            try {
                $customCategorySections = $this->customCategorySectionModel->getAllWithCategory();
            } catch (Exception $e) {
                error_log("Error fetching custom category sections: " . $e->getMessage());
            }

            // Initialize CTA Section
            $ctaSection = null;
            try {
                $ctaSection = $this->ctaSectionModel->getFirst();
                if (!$ctaSection) {
                    $this->ctaSectionModel->createSection([
                        'title' => 'Trở thành một trong <span class="highlight">500+</span>',
                        'subtitle' => 'Đại Lý Affiliate ThuongLo',
                        'content' => 'Tham gia cùng chúng tôi và kiếm thu nhập thụ động từ việc giới thiệu dịch vụ thương mại xuyên biên giới hàng đầu Việt Nam',
                        'button_text' => 'Đăng ký ngay',
                        'button_url' => '?page=agent',
                        'background_color' => '#ECEDEF',
                        'image_url' => 'home/cta-final-1.png',
                        'is_active' => 1
                    ]);
                    $ctaSection = $this->ctaSectionModel->getFirst();
                }
            } catch (Exception $e) {
                error_log("CTA section error: " . $e->getMessage());
                $ctaSection = [
                    'id' => 0,
                    'title' => 'Trở thành một trong <span class="highlight">500+</span>',
                    'subtitle' => 'Đại Lý Affiliate ThuongLo',
                    'content' => 'Tham gia cùng chúng tôi và kiếm thu nhập thụ động từ việc giới thiệu dịch vụ thương mại xuyên biên giới hàng đầu Việt Nam',
                    'button_text' => 'Đăng ký ngay',
                    'button_url' => '?page=agent',
                    'background_color' => '#ECEDEF',
                    'image_url' => 'home/cta-final-1.png',
                    'is_active' => 1
                ];
            }

            // Initialize Top Banner
            $topBanner = null;
            try {
                $topBanner = $this->topBannerModel->getFirst();
                if (!$topBanner) {
                    $this->topBannerModel->createBanner([
                        'content' => 'Chào mừng đến với ThuongLo! Nền tảng data nguồn hàng và dịch vụ thương mại xuyên biên giới hàng đầu.',
                        'button_text' => 'Khám phá ngay!',
                        'button_url' => '?page=products',
                        'is_active' => 1
                    ]);
                    $topBanner = $this->topBannerModel->getFirst();
                }
            } catch (Exception $e) {
                error_log("Top banner error: " . $e->getMessage());
                $topBanner = [
                    'id' => 0,
                    'content' => 'Chào mừng đến với ThuongLo! Nền tảng data nguồn hàng và dịch vụ thương mại xuyên biên giới hàng đầu.',
                    'button_text' => 'Khám phá ngay!',
                    'button_url' => '?page=products',
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
                'featuredCategoriesSection' => $featuredCategoriesSection,
                'featuredBrandsSection' => $featuredBrandsSection,
                'latestNewsSection' => $latestNewsSection,
                'whyChooseSection' => $whyChooseSection,
                'customCategorySections' => $customCategorySections,
                'ctaSection' => $ctaSection,
                'topBanner' => $topBanner,
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

    /**
     * Edit CTA section
     */
    public function editCta(): void {
        if (!$this->requireAdmin()) {
            return;
        }

        try {
            $section = $this->ctaSectionModel->getFirst();
            if (!$section) {
                $this->ctaSectionModel->createSection([
                    'title' => 'Trở thành một trong <span class="highlight">500+</span>',
                    'subtitle' => 'Đại Lý Affiliate ThuongLo',
                    'content' => 'Tham gia cùng chúng tôi và kiếm thu nhập thụ động từ việc giới thiệu dịch vụ thương mại xuyên biên giới hàng đầu Việt Nam',
                    'button_text' => 'Đăng ký ngay',
                    'button_url' => '?page=agent',
                    'background_color' => '#ECEDEF',
                    'image_url' => 'home/cta-final-1.png',
                    'is_active' => 1
                ]);
                $section = $this->ctaSectionModel->getFirst();
            }

            $data = [
                'title' => 'Chỉnh sửa Section CTA',
                'section' => $section,
                'user' => $this->getCurrentUser()
            ];
            $this->renderView('admin/homepage/edit_cta', $data);
        } catch (Exception $e) {
            error_log("Error in editCta: " . $e->getMessage());
            $this->setFlashMessage('error', 'Có lỗi xảy ra: ' . $e->getMessage());
            $this->redirect('?page=admin&module=homepage');
        }
    }

    /**
     * Update CTA section
     */
    public function updateCta(): void {
        if (!$this->requireAdmin()) {
            return;
        }

        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                $this->redirect('?page=admin&module=homepage');
            }

            $id = $_POST['id'] ?? 0;
            if ($id > 0) {
                $title = $_POST['title'] ?? '';
                $subtitle = $_POST['subtitle'] ?? '';
                $content = $_POST['content'] ?? '';
                $button_text = $_POST['button_text'] ?? '';
                $button_url = $_POST['button_url'] ?? '';
                $background_color = $_POST['background_color'] ?? '#ECEDEF';
                $is_active = isset($_POST['is_active']) ? 1 : 0;
                $image_url = $_POST['existing_image'] ?? '';

                // Handle image upload if a file was provided
                if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                    $fileTmpPath = $_FILES['image']['tmp_name'];
                    $fileName = $_FILES['image']['name'];
                    $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                    
                    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'svg', 'webp'];
                    if (in_array($fileExtension, $allowedExtensions)) {
                        $uploadDir = __DIR__ . '/../../uploads/home/';
                        if (!is_dir($uploadDir)) {
                            mkdir($uploadDir, 0777, true);
                        }
                        
                        $newFileName = 'cta_' . time() . '.' . $fileExtension;
                        $dest_path = $uploadDir . $newFileName;
                        
                        if (move_uploaded_file($fileTmpPath, $dest_path)) {
                            $image_url = 'uploads/home/' . $newFileName;
                        }
                    }
                }

                $updateData = [
                    'title' => $title,
                    'subtitle' => $subtitle,
                    'content' => $content,
                    'button_text' => $button_text,
                    'button_url' => $button_url,
                    'background_color' => $background_color,
                    'image_url' => $image_url,
                    'is_active' => $is_active
                ];

                $result = $this->ctaSectionModel->updateSection($id, $updateData);

                if ($result) {
                    $this->setFlashMessage('success', 'Đã cập nhật Section CTA thành công');
                } else {
                    $this->setFlashMessage('error', 'Có lỗi xảy ra khi cập nhật');
                }
                $this->redirect('?page=admin&module=homepage');
            } else {
                $this->setFlashMessage('error', 'ID không hợp lệ');
                $this->redirect('?page=admin&module=homepage');
            }
        } catch (Exception $e) {
            error_log("Error in updateCta: " . $e->getMessage());
            $this->setFlashMessage('error', 'Có lỗi xảy ra: ' . $e->getMessage());
            $this->redirect('?page=admin&module=homepage');
        }
    }

    /**
     * Toggle CTA status
     */
    public function toggleCtaStatus(): void {
        if (!$this->requireAdmin()) {
            return;
        }

        try {
            $data = json_decode(file_get_contents('php://input'), true);
            $id = $data['id'] ?? 0;

            if ($id > 0) {
                $result = $this->ctaSectionModel->toggleStatus($id);
                $this->sendJsonResponse(['success' => $result, 'message' => $result ? 'Đã cập nhật trạng thái' : 'Có lỗi xảy ra']);
            } else {
                $this->sendJsonResponse(['success' => false, 'message' => 'Invalid ID']);
            }
        } catch (Exception $e) {
            error_log("Error in toggleCtaStatus: " . $e->getMessage());
            $this->sendJsonResponse(['success' => false, 'message' => 'Có lỗi xảy ra']);
        }
    }

    /**
     * Edit Top Banner
     */
    public function editTopBanner(): void {
        if (!$this->requireAdmin()) {
            return;
        }

        try {
            $banner = $this->topBannerModel->getFirst();
            if (!$banner) {
                $this->topBannerModel->createBanner([
                    'content' => 'Chào mừng đến với ThuongLo! Nền tảng data nguồn hàng và dịch vụ thương mại xuyên biên giới hàng đầu.',
                    'button_text' => 'Khám phá ngay!',
                    'button_url' => '?page=products',
                    'is_active' => 1
                ]);
                $banner = $this->topBannerModel->getFirst();
            }

            $data = [
                'title' => 'Chỉnh sửa Top Banner',
                'banner' => $banner,
                'user' => $this->getCurrentUser()
            ];
            $this->renderView('admin/homepage/edit_top_banner', $data);
        } catch (Exception $e) {
            error_log("Error in editTopBanner: " . $e->getMessage());
            $this->setFlashMessage('error', 'Có lỗi xảy ra: ' . $e->getMessage());
            $this->redirect('?page=admin&module=homepage');
        }
    }

    /**
     * Update Top Banner
     */
    public function updateTopBanner(): void {
        if (!$this->requireAdmin()) {
            return;
        }

        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                $this->redirect('?page=admin&module=homepage');
            }

            $id = $_POST['id'] ?? 0;
            if ($id > 0) {
                $content = $_POST['content'] ?? '';
                $button_text = $_POST['button_text'] ?? '';
                $button_url = $_POST['button_url'] ?? '';
                $is_active = isset($_POST['is_active']) ? 1 : 0;

                $updateData = [
                    'content' => $content,
                    'button_text' => $button_text,
                    'button_url' => $button_url,
                    'is_active' => $is_active
                ];

                $result = $this->topBannerModel->updateBanner($id, $updateData);

                if ($result) {
                    $this->setFlashMessage('success', 'Đã cập nhật Top Banner thành công');
                } else {
                    $this->setFlashMessage('error', 'Có lỗi xảy ra khi cập nhật');
                }
                $this->redirect('?page=admin&module=homepage');
            } else {
                $this->setFlashMessage('error', 'ID không hợp lệ');
                $this->redirect('?page=admin&module=homepage');
            }
        } catch (Exception $e) {
            error_log("Error in updateTopBanner: " . $e->getMessage());
            $this->setFlashMessage('error', 'Có lỗi xảy ra: ' . $e->getMessage());
            $this->redirect('?page=admin&module=homepage');
        }
    }

    /**
     * Toggle Top Banner status
     */
    public function toggleTopBannerStatus(): void {
        if (!$this->requireAdmin()) {
            return;
        }

        try {
            $data = json_decode(file_get_contents('php://input'), true);
            $id = $data['id'] ?? 0;

            if ($id > 0) {
                $result = $this->topBannerModel->toggleStatus($id);
                $this->sendJsonResponse(['success' => $result, 'message' => $result ? 'Đã cập nhật trạng thái' : 'Có lỗi xảy ra']);
            } else {
                $this->sendJsonResponse(['success' => false, 'message' => 'Invalid ID']);
            }
        } catch (Exception $e) {
            error_log("Error in toggleTopBannerStatus: " . $e->getMessage());
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
     * Edit featured categories section
     */
    public function editFeaturedCategories(): void {
        if (!$this->requireAdmin()) {
            return;
        }

        try {
            $id = $_GET['id'] ?? 0;
            if ($id > 0) {
                $section = $this->featuredCategoriesSectionModel->getFirst();
                if ($section && $section['id'] == $id) {
                    $data = [
                        'title' => 'Chỉnh sửa Section Danh mục Nổi bật',
                        'featuredCategoriesSection' => $section,
                        'user' => $this->getCurrentUser()
                    ];
                    $this->renderView('admin/homepage/edit_featured_categories', $data);
                } else {
                    $this->setFlashMessage('error', 'Section không tồn tại');
                    $this->redirect('?page=admin&module=homepage');
                }
            } else {
                $this->setFlashMessage('error', 'ID không hợp lệ');
                $this->redirect('?page=admin&module=homepage');
            }
        } catch (Exception $e) {
            error_log("Error in editFeaturedCategories: " . $e->getMessage());
            $this->setFlashMessage('error', 'Có lỗi xảy ra: ' . $e->getMessage());
            $this->redirect('?page=admin&module=homepage');
        }
    }

    /**
     * Update featured categories section
     */
    public function updateFeaturedCategories(): void {
        if (!$this->requireAdmin()) {
            return;
        }

        try {
            $id = $_POST['id'] ?? 0;
            $title = $_POST['title'] ?? '';
            $isActive = isset($_POST['is_active']) && $_POST['is_active'] == '1' ? 1 : 0;

            if ($id > 0) {
                $result = $this->featuredCategoriesSectionModel->updateSection($id, [
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
            error_log("Error in updateFeaturedCategories: " . $e->getMessage());
            if ($this->isAjaxRequest()) {
                $this->sendJsonResponse(['success' => false, 'message' => 'Có lỗi xảy ra: ' . $e->getMessage()]);
            } else {
                $this->setFlashMessage('error', 'Có lỗi xảy ra: ' . $e->getMessage());
                $this->redirect('?page=admin&module=homepage');
            }
        }
    }

    /**
     * Toggle featured categories section status
     */
    public function toggleFeaturedCategoriesStatus(): void {
        if (!$this->requireAdmin()) {
            return;
        }

        try {
            $data = json_decode(file_get_contents('php://input'), true);
            $id = $data['id'] ?? 0;

            if ($id > 0) {
                $result = $this->featuredCategoriesSectionModel->toggleStatus($id);
                $this->sendJsonResponse(['success' => $result, 'message' => $result ? 'Đã cập nhật trạng thái' : 'Có lỗi xảy ra']);
            } else {
                $this->sendJsonResponse(['success' => false, 'message' => 'Invalid ID']);
            }
        } catch (Exception $e) {
            error_log("Error in toggleFeaturedCategoriesStatus: " . $e->getMessage());
            $this->sendJsonResponse(['success' => false, 'message' => 'Có lỗi xảy ra']);
        }
    }

    /**
     * Edit featured brands section
     */
    public function editFeaturedBrands(): void {
        if (!$this->requireAdmin()) {
            return;
        }

        try {
            // Debug: Check if model exists
            error_log("editFeaturedBrands: Starting...");
            if (!$this->featuredBrandsSectionModel) {
                error_log("editFeaturedBrands: featuredBrandsSectionModel is null");
                throw new Exception("Model not initialized");
            }
            
            // Get or create featured brands section
            error_log("editFeaturedBrands: Getting section...");
            $section = $this->featuredBrandsSectionModel->getFirst();
            if (!$section) {
                // Create default section if not exists
                $this->featuredBrandsSectionModel->createSection([
                    'title' => '<h2 class="section-title">Thương hiệu <span class="highlight">Nổi bật</span></h2>',
                    'is_active' => 1
                ]);
                $section = $this->featuredBrandsSectionModel->getFirst();
            }

            if ($section) {
                $data = [
                    'title' => 'Chỉnh sửa Section Thương hiệu Nổi bật',
                    'featuredBrandsSection' => $section,
                    'user' => $this->getCurrentUser()
                ];
                $this->renderView('admin/homepage/edit_featured_brands', $data);
            } else {
                $this->setFlashMessage('error', 'Không thể tạo section');
                $this->redirect('?page=admin&module=homepage');
            }
        } catch (Exception $e) {
            error_log("Error in editFeaturedBrands: " . $e->getMessage());
            $this->setFlashMessage('error', 'Có lỗi xảy ra: ' . $e->getMessage());
            $this->redirect('?page=admin&module=homepage');
        }
    }

    /**
     * Update featured brands section
     */
    public function updateFeaturedBrands(): void {
        if (!$this->requireAdmin()) {
            return;
        }

        try {
            $id = $_POST['id'] ?? 0;
            $title = $_POST['title'] ?? '';
            $isActive = isset($_POST['is_active']) && $_POST['is_active'] == '1' ? 1 : 0;

            if ($id > 0) {
                $result = $this->featuredBrandsSectionModel->updateSection($id, [
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
            error_log("Error in updateFeaturedBrands: " . $e->getMessage());
            if ($this->isAjaxRequest()) {
                $this->sendJsonResponse(['success' => false, 'message' => 'Có lỗi xảy ra: ' . $e->getMessage()]);
            } else {
                $this->setFlashMessage('error', 'Có lỗi xảy ra: ' . $e->getMessage());
                $this->redirect('?page=admin&module=homepage');
            }
        }
    }

    /**
     * Toggle featured brands section status
     */
    public function toggleFeaturedBrandsStatus(): void {
        if (!$this->requireAdmin()) {
            return;
        }

        try {
            $data = json_decode(file_get_contents('php://input'), true);
            $id = $data['id'] ?? 0;

            if ($id > 0) {
                $result = $this->featuredBrandsSectionModel->toggleStatus($id);
                $this->sendJsonResponse(['success' => $result, 'message' => $result ? 'Đã cập nhật trạng thái' : 'Có lỗi xảy ra']);
            } else {
                $this->sendJsonResponse(['success' => false, 'message' => 'Invalid ID']);
            }
        } catch (Exception $e) {
            error_log("Error in toggleFeaturedBrandsStatus: " . $e->getMessage());
            $this->sendJsonResponse(['success' => false, 'message' => 'Có lỗi xảy ra']);
        }
    }

    /**
     * Edit latest news section
     */
    public function editLatestNews(): void {
        if (!$this->requireAdmin()) {
            return;
        }

        try {
            // Get or create latest news section
            $section = $this->latestNewsSectionModel->getFirst();
            if (!$section) {
                // Create default section if not exists
                $this->latestNewsSectionModel->createSection([
                    'title' => '<h2 class="section-title">Tin tức <span class="highlight">Mới nhất</span></h2>',
                    'is_active' => 1
                ]);
                $section = $this->latestNewsSectionModel->getFirst();
            }

            if ($section) {
                $data = [
                    'title' => 'Chỉnh sửa Section Tin tức Mới nhất',
                    'latestNewsSection' => $section,
                    'user' => $this->getCurrentUser()
                ];
                $this->renderView('admin/homepage/edit_latest_news', $data);
            } else {
                $this->setFlashMessage('error', 'Không thể tạo section');
                $this->redirect('?page=admin&module=homepage');
            }
        } catch (Exception $e) {
            error_log("Error in editLatestNews: " . $e->getMessage());
            $this->setFlashMessage('error', 'Có lỗi xảy ra: ' . $e->getMessage());
            $this->redirect('?page=admin&module=homepage');
        }
    }

    /**
     * Update latest news section
     */
    public function updateLatestNews(): void {
        if (!$this->requireAdmin()) {
            return;
        }

        try {
            $id = $_POST['id'] ?? 0;
            $title = $_POST['title'] ?? '';
            $isActive = isset($_POST['is_active']) && $_POST['is_active'] == '1' ? 1 : 0;

            if ($id > 0) {
                $result = $this->latestNewsSectionModel->updateSection($id, [
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
            error_log("Error in updateLatestNews: " . $e->getMessage());
            if ($this->isAjaxRequest()) {
                $this->sendJsonResponse(['success' => false, 'message' => 'Có lỗi xảy ra: ' . $e->getMessage()]);
            } else {
                $this->setFlashMessage('error', 'Có lỗi xảy ra: ' . $e->getMessage());
                $this->redirect('?page=admin&module=homepage');
            }
        }
    }

    /**
     * Toggle latest news section status
     */
    public function toggleLatestNewsStatus(): void {
        if (!$this->requireAdmin()) {
            return;
        }

        try {
            $data = json_decode(file_get_contents('php://input'), true);
            $id = $data['id'] ?? 0;
            if ($id > 0) {
                $result = $this->latestNewsSectionModel->toggleStatus($id);
                $this->sendJsonResponse([
                    'success' => $result,
                    'message' => $result ? 'Đã cập nhật trạng thái thành công' : 'Có lỗi xảy ra khi cập nhật trạng thái'
                ]);
            } else {
                $this->sendJsonResponse(['success' => false, 'message' => 'Invalid ID']);
            }
        } catch (Exception $e) {
            error_log("Error in toggleLatestNewsStatus: " . $e->getMessage());
            $this->sendJsonResponse(['success' => false, 'message' => 'Có lỗi xảy ra']);
        }
    }

    /**
     * Edit Why Choose section
     */
    public function editWhyChoose(): void {
        if (!$this->requireAdmin()) {
            return;
        }

        try {
            $section = $this->whyChooseSectionModel->getWithItems();
            if (!$section) {
                // Create default section and items if not exists
                $this->whyChooseSectionModel->createSection([
                    'title' => '<h2 class="section-title">Tại sao chọn <span class="highlight">ThuongLo?</span></h2>',
                    'is_active' => 1
                ]);
                $section = $this->whyChooseSectionModel->getWithItems();
                
                if ($section) {
                    $defaultItems = [
                        ['title' => 'Kinh nghiệm dày dặn', 'content' => 'Hơn 10 năm kinh nghiệm trong lĩnh vực thương mại xuyên biên giới, hiểu rõ thị trường và quy trình', 'sort_order' => 1],
                        ['title' => 'Dịch vụ toàn diện', 'content' => 'Cung cấp giải pháp từ A-Z cho thương mại xuyên biên giới, từ tìm nguồn hàng đến vận chuyển', 'sort_order' => 2],
                        ['title' => 'Hỗ trợ 24/7', 'content' => 'Đội ngũ hỗ trợ chuyên nghiệp sẵn sàng giải đáp mọi thắc mắc và hỗ trợ khách hàng mọi lúc', 'sort_order' => 3],
                        ['title' => 'Giá cả cạnh tranh', 'content' => 'Cam kết mang đến giá cả tốt nhất thị trường với chất lượng dịch vụ cao nhất', 'sort_order' => 4],
                        ['title' => 'Đội ngũ chuyên nghiệp', 'content' => 'Đội ngũ nhân viên giàu kinh nghiệm, nhiệt tình và tận tâm với khách hàng', 'sort_order' => 5],
                        ['title' => 'Uy tín và đáng tin cậy', 'content' => 'Được hàng ngàn khách hàng tin tưởng và lựa chọn trong nhiều năm', 'sort_order' => 6]
                    ];
                    foreach ($defaultItems as $item) {
                        $item['section_id'] = $section['id'];
                        $this->whyChooseItemModel->createItem($item);
                    }
                    $section = $this->whyChooseSectionModel->getWithItems();
                }
            }

            if ($section) {
                $data = [
                    'title' => 'Chỉnh sửa Section Tại sao chọn ThuongLo?',
                    'whyChooseSection' => $section,
                    'user' => $this->getCurrentUser()
                ];
                $this->renderView('admin/homepage/edit_why_choose', $data);
            } else {
                $this->setFlashMessage('error', 'Không thể tạo section');
                $this->redirect('?page=admin&module=homepage');
            }
        } catch (Exception $e) {
            error_log("Error in editWhyChoose: " . $e->getMessage());
            $this->setFlashMessage('error', 'Có lỗi xảy ra: ' . $e->getMessage());
            $this->redirect('?page=admin&module=homepage');
        }
    }

    /**
     * Update Why Choose section
     */
    public function updateWhyChoose(): void {
        if (!$this->requireAdmin()) {
            return;
        }

        try {
            $id = $_POST['id'] ?? 0;
            $title = $_POST['title'] ?? '';
            $isActive = isset($_POST['is_active']) && $_POST['is_active'] == '1' ? 1 : 0;

            if ($id > 0) {
                // Start transaction to ensure atomic updates
                $this->whyChooseSectionModel->beginTransaction();

                // 1. Update section title and status
                $result = $this->whyChooseSectionModel->updateSection($id, [
                    'title' => $title,
                    'is_active' => $isActive
                ]);

                // 2. Sync items
                $submittedItems = $_POST['items'] ?? [];
                $keepItemIds = [];

                foreach ($submittedItems as $itemData) {
                    $itemId = isset($itemData['id']) ? intval($itemData['id']) : 0;
                    $itemTitle = $itemData['title'] ?? '';
                    $itemContent = $itemData['content'] ?? '';
                    $itemSortOrder = isset($itemData['sort_order']) ? intval($itemData['sort_order']) : 0;

                    if ($itemId > 0) {
                        // Update existing item
                        $this->whyChooseItemModel->updateItem($itemId, [
                            'title' => $itemTitle,
                            'content' => $itemContent,
                            'sort_order' => $itemSortOrder
                        ]);
                        $keepItemIds[] = $itemId;
                    } else {
                        // Insert new item
                        $newItemId = $this->whyChooseItemModel->createItem([
                            'section_id' => $id,
                            'title' => $itemTitle,
                            'content' => $itemContent,
                            'sort_order' => $itemSortOrder
                        ]);
                        if ($newItemId) {
                            $keepItemIds[] = $newItemId;
                        }
                    }
                }

                // 3. Delete items that were removed
                $existingItems = $this->whyChooseItemModel->getBySection($id);
                foreach ($existingItems as $existingItem) {
                    if (!in_array($existingItem['id'], $keepItemIds)) {
                        $this->whyChooseItemModel->deleteItem($existingItem['id']);
                    }
                }

                $this->whyChooseSectionModel->commit();

                if ($this->isAjaxRequest()) {
                    $this->sendJsonResponse([
                        'success' => true,
                        'message' => 'Đã cập nhật section thành công'
                    ]);
                } else {
                    $this->setFlashMessage('success', 'Đã cập nhật section thành công');
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
            try {
                $this->whyChooseSectionModel->rollback();
            } catch (Exception $rollbackEx) {}

            error_log("Error in updateWhyChoose: " . $e->getMessage());
            if ($this->isAjaxRequest()) {
                $this->sendJsonResponse(['success' => false, 'message' => 'Có lỗi xảy ra: ' . $e->getMessage()]);
            } else {
                $this->setFlashMessage('error', 'Có lỗi xảy ra: ' . $e->getMessage());
                $this->redirect('?page=admin&module=homepage');
            }
        }
    }

    /**
     * Toggle Why Choose section status
     */
    public function toggleWhyChooseStatus(): void {
        if (!$this->requireAdmin()) {
            return;
        }

        try {
            $data = json_decode(file_get_contents('php://input'), true);
            $id = $data['id'] ?? 0;
            if ($id > 0) {
                $result = $this->whyChooseSectionModel->toggleStatus($id);
                $this->sendJsonResponse([
                    'success' => $result,
                    'message' => $result ? 'Đã cập nhật trạng thái thành công' : 'Có lỗi xảy ra khi cập nhật trạng thái'
                ]);
            } else {
                $this->sendJsonResponse(['success' => false, 'message' => 'Invalid ID']);
            }
        } catch (Exception $e) {
            error_log("Error in toggleWhyChooseStatus: " . $e->getMessage());
            $this->sendJsonResponse(['success' => false, 'message' => 'Có lỗi xảy ra']);
        }
    }

    /**
     * View/Edit/Add dynamic category sections
     */
    public function editCustomCategory(): void {
        if (!$this->requireAdmin()) {
            return;
        }

        try {
            $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
            $section = null;
            
            if ($id > 0) {
                $section = $this->customCategorySectionModel->getById($id);
                if (!$section) {
                    $this->setFlashMessage('error', 'Section không tồn tại');
                    $this->redirect('?page=admin&module=homepage');
                    return;
                }
            }

            // Fetch categories for selection
            $categories = $this->categoriesModel->getActiveProductCategories();
            
            $data = [
                'title' => $id > 0 ? 'Sửa Section Danh mục Tùy chỉnh' : 'Thêm Section Danh mục Tùy chỉnh',
                'section' => $section,
                'categories' => $categories,
                'user' => $this->getCurrentUser()
            ];

            $this->renderView('admin/homepage/edit_custom_category', $data);
        } catch (Exception $e) {
            error_log("Error in editCustomCategory: " . $e->getMessage());
            $this->setFlashMessage('error', 'Có lỗi xảy ra: ' . $e->getMessage());
            $this->redirect('?page=admin&module=homepage');
        }
    }

    /**
     * Save dynamic category section
     */
    public function saveCustomCategory(): void {
        if (!$this->requireAdmin()) {
            return;
        }

        try {
            $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
            $title = isset($_POST['title']) ? trim($_POST['title']) : '';
            $categoryId = isset($_POST['category_id']) ? intval($_POST['category_id']) : 0;
            $displayType = isset($_POST['display_type']) ? trim($_POST['display_type']) : 'featured';
            $sortOrder = isset($_POST['sort_order']) ? intval($_POST['sort_order']) : 0;
            $isActive = isset($_POST['is_active']) ? 1 : 0;

            if (empty($title)) {
                $this->setFlashMessage('error', 'Tiêu đề không được để trống');
                $this->redirect('?page=admin&module=homepage&action=edit-custom-category' . ($id > 0 ? '&id=' . $id : ''));
                return;
            }

            if ($categoryId <= 0) {
                $this->setFlashMessage('error', 'Vui lòng chọn danh mục hợp lệ');
                $this->redirect('?page=admin&module=homepage&action=edit-custom-category' . ($id > 0 ? '&id=' . $id : ''));
                return;
            }

            // Enforce limit of 5 sections when creating a NEW section
            if ($id <= 0) {
                $currentCount = $this->customCategorySectionModel->getCount();
                if ($currentCount >= 5) {
                    $this->setFlashMessage('error', 'Hệ thống chỉ cho phép tạo tối đa 5 section danh mục tùy chỉnh.');
                    $this->redirect('?page=admin&module=homepage');
                    return;
                }
            }

            $sectionData = [
                'title' => $title,
                'category_id' => $categoryId,
                'display_type' => $displayType,
                'sort_order' => $sortOrder,
                'is_active' => $isActive
            ];

            if ($id > 0) {
                $result = $this->customCategorySectionModel->updateSection($id, $sectionData);
                $msg = $result ? 'Cập nhật section thành công!' : 'Không có thay đổi nào được thực hiện hoặc có lỗi xảy ra.';
            } else {
                $result = $this->customCategorySectionModel->createSection($sectionData);
                $msg = $result ? 'Thêm section mới thành công!' : 'Có lỗi xảy ra khi tạo section.';
            }

            if ($result) {
                $this->setFlashMessage('success', $msg);
                $this->redirect('?page=admin&module=homepage');
            } else {
                $this->setFlashMessage('error', $msg);
                $this->redirect('?page=admin&module=homepage&action=edit-custom-category' . ($id > 0 ? '&id=' . $id : ''));
            }
        } catch (Exception $e) {
            error_log("Error in saveCustomCategory: " . $e->getMessage());
            $this->setFlashMessage('error', 'Có lỗi xảy ra: ' . $e->getMessage());
            $this->redirect('?page=admin&module=homepage');
        }
    }

    /**
     * Delete dynamic category section
     */
    public function deleteCustomCategory(): void {
        if (!$this->requireAdmin()) {
            return;
        }

        try {
            $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
            if ($id > 0) {
                $result = $this->customCategorySectionModel->deleteSection($id);
                if ($result) {
                    $this->setFlashMessage('success', 'Xóa section thành công');
                } else {
                    $this->setFlashMessage('error', 'Có lỗi xảy ra khi xóa section');
                }
            } else {
                $this->setFlashMessage('error', 'ID không hợp lệ');
            }
        } catch (Exception $e) {
            error_log("Error in deleteCustomCategory: " . $e->getMessage());
            $this->setFlashMessage('error', 'Có lỗi xảy ra: ' . $e->getMessage());
        }
        $this->redirect('?page=admin&module=homepage');
    }

    /**
     * Toggle dynamic category section status
     */
    public function toggleCustomCategoryStatus(): void {
        if (!$this->requireAdmin()) {
            return;
        }

        try {
            $data = json_decode(file_get_contents('php://input'), true);
            $id = $data['id'] ?? 0;
            if ($id > 0) {
                $result = $this->customCategorySectionModel->toggleStatus($id);
                $this->sendJsonResponse([
                    'success' => $result,
                    'message' => $result ? 'Đã cập nhật trạng thái thành công' : 'Có lỗi xảy ra khi cập nhật trạng thái'
                ]);
            } else {
                $this->sendJsonResponse(['success' => false, 'message' => 'ID không hợp lệ']);
            }
        } catch (Exception $e) {
            error_log("Error in toggleCustomCategoryStatus: " . $e->getMessage());
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
