    <!-- Top Banner -->
<?php
// Initialize authentication state for header using AuthService (includes device validation)
require_once __DIR__ . '/../../services/AuthService.php';
$authService = new AuthService();
$isAuthenticated = $authService->isAuthenticated();

// Get current page
$currentPage = isset($_GET['page']) ? $_GET['page'] : 'home';

// Also check if we're on the root path (index.php or /)
$requestUri = $_SERVER['REQUEST_URI'] ?? '';
$scriptName = $_SERVER['SCRIPT_NAME'] ?? '';

// If accessing root or index.php directly without page param, set to home
if (empty($_GET['page']) && ($requestUri === '/' || $requestUri === '/index.php' || preg_match('#^/[^/]*\.php$#', $requestUri))) {
    $currentPage = 'home';
}

// Define page groups for dropdown menus
$guidePages = ['about', 'guide', 'contact', 'faq'];
$newsPages = ['news'];
$productPages = ['products', 'details', 'course-details']; // Removed 'categories' from here

// Try to get categories from global $publicService
$headerCategories = [];
$newsCategories = [];
$headerBrands = []; // Brands for header dropdown

// Check if publicService is available in global scope
global $publicService;
if (isset($publicService) && is_object($publicService)) {
    // Get product categories with hierarchy (cha-con)
    try {
        $headerCategories = $publicService->getCategoriesHierarchy();
    } catch (Exception $e) {
        error_log('Header categories error: ' . $e->getMessage());
    }
    
    // Get brands for header dropdown
    try {
        if (method_exists($publicService, 'getBrandsForFilter')) {
            $brandsData = $publicService->getBrandsForFilter();
            $headerBrands = $brandsData['brands'] ?? [];
        }
    } catch (Exception $e) {
        error_log('Header brands error: ' . $e->getMessage());
    }
}

// Load news categories from database (same method as news.php)
if (!class_exists('CategoriesModel')) {
    require_once __DIR__ . '/../../models/CategoriesModel.php';
}
if (class_exists('CategoriesModel')) {
    try {
        $categoriesModel = new CategoriesModel();
        $allCategories = $categoriesModel->all();
        if (is_array($allCategories)) {
            // Filter to only include categories with type = 'news' (same as news.php)
            $newsCategoriesList = [];
            foreach ($allCategories as $cat) {
                if (isset($cat['type']) && $cat['type'] === 'news') {
                    $newsCategoriesList[] = [
                        'id' => $cat['id'] ?? null,
                        'slug' => $cat['slug'],
                        'name' => $cat['name'] ?? ucfirst(str_replace('-', ' ', $cat['slug']))
                    ];
                }
            }
            $newsCategories = $newsCategoriesList;
        }
    } catch (Exception $e) {
        error_log('Header news categories error: ' . $e->getMessage());
    }
}

// Pre-compute user and cart state for mobile header and desktop header
$currentUser = null;
$cartCount = 0;
if ($isAuthenticated) {
    $currentUser = [
        'id' => $_SESSION['user_id'],
        'name' => $_SESSION['user_name'] ?? 'User',
        'username' => $_SESSION['username'] ?? '',
        'email' => $_SESSION['user_email'] ?? '',
        'role' => $_SESSION['user_role'] ?? 'user'
    ];
    
    if (!class_exists('CartModel')) {
        require_once __DIR__ . '/../../models/CartModel.php';
    }
    if (class_exists('CartModel')) {
        try {
            $cartModel = new CartModel();
            $cartCount = $cartModel->getItemCount($currentUser['id']);
        } catch (Exception $e) {
            error_log('Header cart count error: ' . $e->getMessage());
        }
    }
}
?>

<?php
// Initialize dynamic Top Banner settings
$topBanner = null;
try {
    if (!class_exists('TopBannerModel')) {
        require_once __DIR__ . '/../../models/TopBannerModel.php';
    }
    if (class_exists('TopBannerModel')) {
        $topBannerModel = new TopBannerModel();
        // Retrieve the first record (including inactive ones to check status)
        $topBanner = $topBannerModel->getFirst();
    }
} catch (Exception $e) {
    error_log("Top banner frontend load error: " . $e->getMessage());
}

// Fallback to static banner if DB connection fails or record is not created yet
if (!$topBanner) {
    $topBanner = [
        'id' => 0,
        'content' => 'Chào mừng đến với ThuongLo! Nền tảng data nguồn hàng và dịch vụ thương mại xuyên biên giới hàng đầu.',
        'button_text' => 'Khám phá ngay!',
        'button_url' => '?page=products',
        'is_active' => 1
    ];
}

// Only render top banner if it's set to active
if (isset($topBanner['is_active']) && $topBanner['is_active']):
?>
    <div class="top-banner">
        <div class="container">
            <p>
                <?php echo htmlspecialchars($topBanner['content']); ?>
                <?php if (!empty($topBanner['button_text'])): ?>
                    <a href="<?php echo htmlspecialchars($topBanner['button_url'] ?? '?page=products'); ?>">
                        <?php echo htmlspecialchars($topBanner['button_text']); ?>
                    </a>
                <?php endif; ?>
            </p>
        </div>
    </div>
<?php endif; ?>

    <!-- Main Header -->
    <header class="main-header">
        <div class="container">
            <div class="header-content">
                <!-- Logo -->
                <div class="logo">
                    <a href="<?php echo base_url(); ?>">
                        <img src="<?php echo icon_url(get_logo('logo_header', 'logo/logo.svg')); ?>" alt="Thuonglo" width="160" height="36">
                    </a>
                </div>

                <!-- Mobile Header Right Actions (Hamburger & Cart) -->
                <div class="mobile-header-right">
                    <?php if ($isAuthenticated): ?>
                        <a href="?page=users&module=cart" class="mobile-cart-icon" title="Giỏ hàng">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" xmlns="http://www.w3.org/2000/svg">
                                <circle cx="9" cy="21" r="1"></circle>
                                <circle cx="20" cy="21" r="1"></circle>
                                <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                            </svg>
                            <?php if ($cartCount > 0): ?>
                                <span class="mobile-cart-badge"><?php echo $cartCount; ?></span>
                            <?php endif; ?>
                        </a>
                    <?php endif; ?>
                    
                    <button type="button" class="mobile-menu-toggle" id="mobileMenuToggle" aria-label="Toggle Navigation">
                        <span></span>
                        <span></span>
                        <span></span>
                    </button>
                </div>

                <!-- Products Mega Menu in Top Header Row -->
                <div class="categories-dropdown has-dropdown <?php echo (in_array($currentPage, $productPages)) ? 'active' : ''; ?>">
                    <a href="<?php echo nav_url('products'); ?>" class="categories-btn">
                        <span>Sản phẩm </span>
                        <svg width="10" height="6" viewBox="0 0 10 6" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M1 1L5 5L9 1" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </a>
                    <div class="categories-menu categories-mega-menu">
                        <div class="categories-mega-grid">

                            <!-- Dynamic Categories Columns -->
                            <?php if (!empty($headerCategories)): ?>
                                <?php 
                                // Distribute parent categories into exactly 3 columns (SePay style)
                                $columns = [[], [], []];
                                $colIndex = 0;
                                foreach ($headerCategories as $parentCat) {
                                    $columns[$colIndex % 3][] = $parentCat;
                                    $colIndex++;
                                }
                                
                                foreach ($columns as $columnCats): 
                                ?>
                                    <div class="mega-column">
                                        <?php foreach ($columnCats as $parentCat): 
                                            $parentHasChildren = !empty($parentCat['children']);
                                        ?>
                                            <!-- Parent Category Block -->
                                            <div class="mega-parent-block">
                                                <!-- Parent Category Header -->
                                                <div class="mega-parent-header" style="display: flex; align-items: center; gap: 8px;">
                                                    <?php if (!empty($parentCat['icon'])): ?>
                                                        <span class="mega-parent-icon" style="color: #356df1; font-size: 1.1rem; display: inline-flex; align-items: center; justify-content: center; flex-shrink: 0;"><?php if (strpos($parentCat['icon'], '.svg') !== false || strpos($parentCat['icon'], '/') !== false): ?><img src="<?php echo htmlspecialchars($parentCat['icon']); ?>" alt="" style="width: 1.1rem; height: 1.1rem; object-fit: contain;"><?php else: ?><i class="<?php echo htmlspecialchars($parentCat['icon']); ?>"></i><?php endif; ?></span>
                                                    <?php endif; ?>
                                                    <a href="<?php echo page_url('products', ['category' => $parentCat['id']]); ?>" class="mega-parent-title">
                                                        <?php echo htmlspecialchars($parentCat['name']); ?>
                                                    </a>
                                                </div>
                                                
                                                <?php if ($parentHasChildren): ?>
                                                    <div class="mega-child-list">
                                                        <?php foreach ($parentCat['children'] as $childCat): 
                                                            $childHasChildren = !empty($childCat['children']);
                                                        ?>
                                                            <div class="mega-child-item">
                                                                <a href="<?php echo page_url('products', ['category' => $childCat['id']]); ?>" class="mega-child-link-group" style="display: flex; align-items: flex-start; gap: 8px;">
                                                                    <?php if (!empty($childCat['icon'])): ?>
                                                                        <span class="mega-child-icon" style="color: #356df1; font-size: 1rem; display: inline-flex; align-items: center; justify-content: center; flex-shrink: 0; margin-top: 2px;"><?php if (strpos($childCat['icon'], '.svg') !== false || strpos($childCat['icon'], '/') !== false): ?><img src="<?php echo htmlspecialchars($childCat['icon']); ?>" alt="" style="width: 1rem; height: 1rem; object-fit: contain;"><?php else: ?><i class="<?php echo htmlspecialchars($childCat['icon']); ?>"></i><?php endif; ?></span>
                                                                    <?php endif; ?>
                                                                    <div class="mega-child-info">
                                                                        <span class="mega-child-name"><?php echo htmlspecialchars($childCat['name']); ?></span>
                                                                        <?php 
                                                                        $showDesc = false;
                                                                        if (!empty($childCat['description'])) {
                                                                            $nameL = mb_strtolower(trim($childCat['name']), 'UTF-8');
                                                                            $descL = mb_strtolower(trim($childCat['description']), 'UTF-8');
                                                                            
                                                                            if ($nameL !== $descL) {
                                                                                if (mb_strlen($descL, 'UTF-8') > 20) {
                                                                                    if (mb_strpos($descL, $nameL) !== false) {
                                                                                        if (mb_strlen($descL, 'UTF-8') > mb_strlen($nameL, 'UTF-8') + 15) {
                                                                                            $showDesc = true;
                                                                                        }
                                                                                    } else {
                                                                                        $showDesc = true;
                                                                                    }
                                                                                }
                                                                            }
                                                                        }
                                                                        if ($showDesc): ?>
                                                                            <span class="mega-child-desc"><?php echo htmlspecialchars($childCat['description']); ?></span>
                                                                        <?php endif; ?>
                                                                    </div>
                                                                </a>
                                                                
                                                                <?php if ($childHasChildren): ?>
                                                                    <div class="mega-grandchild-list">
                                                                        <?php foreach ($childCat['children'] as $grandchildCat): ?>
                                                                            <a href="<?php echo page_url('products', ['category' => $grandchildCat['id']]); ?>" class="mega-grandchild-link" style="display: inline-flex; align-items: center; gap: 6px;">
                                                                                <?php if (!empty($grandchildCat['icon'])): ?>
                                                                                    <span class="mega-grandchild-icon" style="color: #356df1; font-size: 0.85rem; display: inline-flex; align-items: center; justify-content: center; flex-shrink: 0;"><?php if (strpos($grandchildCat['icon'], '.svg') !== false || strpos($grandchildCat['icon'], '/') !== false): ?><img src="<?php echo htmlspecialchars($grandchildCat['icon']); ?>" alt="" style="width: 0.85rem; height: 0.85rem; object-fit: contain;"><?php else: ?><i class="<?php echo htmlspecialchars($grandchildCat['icon']); ?>"></i><?php endif; ?></span>
                                                                                <?php endif; ?>
                                                                                <span><?php echo htmlspecialchars($grandchildCat['name']); ?></span>
                                                                            </a>
                                                                        <?php endforeach; ?>
                                                                    </div>
                                                                <?php endif; ?>
                                                            </div>
                                                        <?php endforeach; ?>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <!-- Search Bar -->
                <div class="search-bar">
                    <form method="get" action="<?php echo base_url(); ?>">
                        <button type="submit" class="search-btn">
                            <svg width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M12.5 11H11.71L11.43 10.73C12.41 9.59 13 8.11 13 6.5C13 2.91 10.09 0 6.5 0C2.91 0 0 2.91 0 6.5C0 10.09 2.91 13 6.5 13C8.11 13 9.59 12.41 10.73 11.43L11 11.71V12.5L16 17.49L17.49 16L12.5 11ZM6.5 11C4.01 11 2 8.99 2 6.5C2 4.01 4.01 2 6.5 2C8.99 2 11 4.01 11 6.5C11 8.99 8.99 11 6.5 11Z" fill="#6B7280"/>
                            </svg>
                        </button>
                        <input type="text" name="search" placeholder="Tìm kiếm dịch vụ, data nguồn hàng..." class="search-input">
                        <input type="hidden" value="products" name="page">
                    </form>
                </div>
            </div>
        </div>
    </header>

    <!-- Navigation -->
    <nav class="main-nav">
        <div class="container">
            <div class="nav-content">
                <!-- Main Menu -->

                <ul class="main-menu">
                    <li class="<?php echo ($currentPage == 'home') ? 'active' : ''; ?>"><a href="<?php echo base_url(); ?>">Trang chủ</a></li>

                    <li class="has-dropdown <?php echo (in_array($currentPage, $guidePages)) ? 'active' : ''; ?>">
                        <button type="button" class="dropdown-btn">Hướng dẫn <svg width="10" height="6" viewBox="0 0 10 6" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M1 1L5 5L9 1" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg></button>
                        <div class="dropdown-menu">
                            <a href="<?php echo nav_url('about'); ?>">Giới thiệu</a>
                            
                            <a href="<?php echo nav_url('contact'); ?>">Liên hệ hỗ trợ</a>
                            <a href="<?php echo nav_url('faq'); ?>">Câu hỏi thường gặp</a>
                        </div>
                    </li>
                    <li class="has-dropdown <?php echo (in_array($currentPage, $newsPages)) ? 'active' : ''; ?>">
                        <a href="<?php echo nav_url('news'); ?>">Tin tức <svg width="10" height="6" viewBox="0 0 10 6" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M1 1L5 5L9 1" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg></a>
                        <div class="dropdown-menu">
                            <?php if (!empty($newsCategories)): ?>
                                <?php foreach ($newsCategories as $cat): ?>
                                    <a href="<?php echo page_url('news', ['category' => $cat['id']]); ?>"><?php echo htmlspecialchars($cat['name']); ?></a>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </li>
                    <li class="has-dropdown <?php echo ($currentPage == 'brands') ? 'active' : ''; ?>">
                        <a href="<?php echo nav_url('brands'); ?>">Thương hiệu <svg width="10" height="6" viewBox="0 0 10 6" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M1 1L5 5L9 1" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg></a>
                        <div class="dropdown-menu brands-mega-menu">
                            <?php if (!empty($headerBrands)): ?>
                                <div class="brands-mega-grid">
                                    <?php 
                                    // Distribute brands into exactly 4 columns for balanced height
                                    $brandCols = [[], [], [], []];
                                    $bIndex = 0;
                                    foreach ($headerBrands as $brand) {
                                        $brandCols[$bIndex % 4][] = $brand;
                                        $bIndex++;
                                    }
                                    
                                    foreach ($brandCols as $colBrands): 
                                    ?>
                                        <div class="brands-column">
                                            <?php foreach ($colBrands as $brand): ?>
                                                <a href="<?php echo page_url('products', ['brand' => $brand['id']]); ?>" class="brand-mega-link">
                                                    <span class="brand-name"><?php echo htmlspecialchars($brand['name']); ?></span>
                                                </a>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <div class="brands-empty">Không có thương hiệu</div>
                            <?php endif; ?>
                        </div>
                    </li>
                    <li class="has-dropdown <?php echo ($currentPage == 'affiliate' || $currentPage == 'agent' || $currentPage == 'agent-page') ? 'active' : ''; ?>">
                        <?php if ($isAuthenticated): ?>
                            <?php
                            // Check user's agent status from database for accuracy
                            $agentLink = '?page=agent'; // Default to registration page
                            
                            try {
                                $userId = $_SESSION['user_id'] ?? null;
                                if ($userId) {
                                    // Load UsersModel if not already loaded
                                    if (!class_exists('UsersModel')) {
                                        require_once __DIR__ . '/../../models/UsersModel.php';
                                    }
                                    if (!class_exists('AffiliateModel')) {
                                        require_once __DIR__ . '/../../models/AffiliateModel.php';
                                    }
                                    
                                    if (class_exists('UsersModel')) {
                                        $usersModel = new UsersModel();
                                        $user = $usersModel->find($userId);
                                        
                                        if ($user) {
                                            $userRole = $user['role'] ?? 'user';
                                            $agentRequestStatus = $user['agent_request_status'] ?? 'none';
                                            
                                            // Check if user is an approved agent (role is 'affiliate' or agent_request_status is 'approved')
                                            if ($userRole === 'affiliate' || $agentRequestStatus === 'approved') {
                                                // User is approved agent - redirect to affiliate dashboard
                                                $agentLink = '?page=affiliate';
                                            } elseif ($agentRequestStatus === 'pending') {
                                                // User has pending request - show processing message
                                                $agentLink = '?page=agent&action=processing';
                                            }
                                            // else: user has no request or rejected - show registration form
                                        }
                                    }
                                }
                            } catch (Exception $e) {
                                error_log('Header agent status check error: ' . $e->getMessage());
                                // Fallback to session-based check
                                $userRole = $_SESSION['user_role'] ?? 'user';
                                $agentStatus = $_SESSION['agent_request_status'] ?? 'none';
                                if (($userRole === 'agent' || $userRole === 'affiliate') && $agentStatus === 'approved') {
                                    $agentLink = '?page=affiliate';
                                } elseif ($agentStatus === 'pending') {
                                    $agentLink = '?page=agent&action=processing';
                                }
                            }
                            
                            echo '<a href="' . htmlspecialchars($agentLink) . '">Đại lý <svg width="10" height="6" viewBox="0 0 10 6" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M1 1L5 5L9 1" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg></a>';
                            ?>
                        <?php else: ?>
                            <a href="?page=agent">Đại lý <svg width="10" height="6" viewBox="0 0 10 6" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M1 1L5 5L9 1" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg></a>
                        <?php endif; ?>

                        <style>
                        .agent-mega-menu {
                            min-width: 540px !important;
                            padding: 20px !important;
                            box-shadow: 0 10px 30px rgba(0,0,0,0.08) !important;
                            border-radius: 12px !important;
                            border: 1px solid #e2e8f0 !important;
                            margin-top: 8px !important;
                            background: white !important;
                        }
                        .agent-mega-grid {
                            display: grid !important;
                            grid-template-columns: repeat(2, 1fr) !important;
                            gap: 16px !important;
                        }
                        .agent-mega-item {
                            display: flex !important;
                            align-items: flex-start !important;
                            gap: 12px !important;
                            padding: 10px !important;
                            border-radius: 8px !important;
                            text-decoration: none !important;
                            transition: all 0.2s ease-in-out !important;
                        }
                        .agent-mega-item:hover {
                            background-color: #f8fafc !important;
                        }
                        .agent-mega-item:hover .agent-mega-title {
                            color: #356df1 !important;
                        }
                        .agent-mega-item:hover .agent-mega-icon {
                            transform: scale(1.05);
                        }
                        </style>
                        <div class="dropdown-menu agent-mega-menu">
                            <div class="agent-mega-grid">
                                <a href="?page=agent-page&key=chuong_trinh" class="agent-mega-item">
                                    <span class="agent-mega-icon" style="color: #356df1; font-size: 1.15rem; display: inline-flex; background: #eef2ff; width: 36px; height: 36px; align-items: center; justify-content: center; border-radius: 8px; transition: transform 0.2s;"><i class="fas fa-handshake"></i></span>
                                    <div class="agent-mega-info">
                                        <span class="agent-mega-title" style="display: block; font-weight: 600; color: #1e293b; font-size: 13.5px; margin-bottom: 2px; transition: color 0.2s;">Chương trình đại lý</span>
                                        <span class="agent-mega-desc" style="display: block; color: #64748b; font-size: 11px; line-height: 1.4; font-weight: normal;">Hợp tác phát triển cùng Thuong Lo</span>
                                    </div>
                                </a>
                                <a href="?page=agent-page&key=huong_dan" class="agent-mega-item">
                                    <span class="agent-mega-icon" style="color: #10b981; font-size: 1.15rem; display: inline-flex; background: #ecfdf5; width: 36px; height: 36px; align-items: center; justify-content: center; border-radius: 8px; transition: transform 0.2s;"><i class="fas fa-book-open"></i></span>
                                    <div class="agent-mega-info">
                                        <span class="agent-mega-title" style="display: block; font-weight: 600; color: #1e293b; font-size: 13.5px; margin-bottom: 2px; transition: color 0.2s;">Hướng dẫn đăng ký</span>
                                        <span class="agent-mega-desc" style="display: block; color: #64748b; font-size: 11px; line-height: 1.4; font-weight: normal;">Các bước tham gia dễ dàng nhất</span>
                                    </div>
                                </a>
                                <a href="?page=agent-page&key=chinh_sach" class="agent-mega-item">
                                    <span class="agent-mega-icon" style="color: #f59e0b; font-size: 1.15rem; display: inline-flex; background: #fffbeb; width: 36px; height: 36px; align-items: center; justify-content: center; border-radius: 8px; transition: transform 0.2s;"><i class="fas fa-shield-alt"></i></span>
                                    <div class="agent-mega-info">
                                        <span class="agent-mega-title" style="display: block; font-weight: 600; color: #1e293b; font-size: 13.5px; margin-bottom: 2px; transition: color 0.2s;">Chính sách đại lý</span>
                                        <span class="agent-mega-desc" style="display: block; color: #64748b; font-size: 11px; line-height: 1.4; font-weight: normal;">Quyền lợi & Hoa hồng hấp dẫn</span>
                                    </div>
                                </a>
                                <a href="?page=agent-page&key=tai_nguyen" class="agent-mega-item">
                                    <span class="agent-mega-icon" style="color: #8b5cf6; font-size: 1.15rem; display: inline-flex; background: #f5f3ff; width: 36px; height: 36px; align-items: center; justify-content: center; border-radius: 8px; transition: transform 0.2s;"><i class="fas fa-folder-open"></i></span>
                                    <div class="agent-mega-info">
                                        <span class="agent-mega-title" style="display: block; font-weight: 600; color: #1e293b; font-size: 13.5px; margin-bottom: 2px; transition: color 0.2s;">Tài nguyên tiếp thị</span>
                                        <span class="agent-mega-desc" style="display: block; color: #64748b; font-size: 11px; line-height: 1.4; font-weight: normal;">Kho tài liệu và banner quảng bá</span>
                                    </div>
                                </a>
                            </div>
                        </div>
                    </li>
                </ul>

                <!-- Right Side Buttons -->
                <div class="header-buttons">
                    <?php
                    // Use the already-set $isAuthenticated from AuthService (line 4)
                    // If not authenticated, $currentUser remains null
                    $currentUser = null;
                    
                    if ($isAuthenticated) {
                        $currentUser = [
                            'id' => $_SESSION['user_id'],
                            'name' => $_SESSION['user_name'] ?? 'User',
                            'username' => $_SESSION['username'] ?? '',
                            'email' => $_SESSION['user_email'] ?? '',
                            'role' => $_SESSION['user_role'] ?? 'user'
                        ];
                        
                        // Get cart item count
                        $cartCount = 0;
                        if (!class_exists('CartModel')) {
                            require_once __DIR__ . '/../../models/CartModel.php';
                        }
                        if (class_exists('CartModel')) {
                            try {
                                $cartModel = new CartModel();
                                $cartCount = $cartModel->getItemCount($currentUser['id']);
                            } catch (Exception $e) {
                                error_log('Header cart count error: ' . $e->getMessage());
                            }
                        }
                    }
                    
                    if ($isAuthenticated): ?>
                        <!-- Cart Icon (visible only when logged in) -->
                        <div class="header-cart-wrapper has-dropdown">
                            <a href="?page=users&module=cart" class="header-cart-icon" title="Giỏ hàng">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M9 22C9.55228 22 10 21.5523 10 21C10 20.4477 9.55228 20 9 20C8.44772 20 8 20.4477 8 21C8 21.5523 8.44772 22 9 22Z" stroke="currentColor" stroke-width="1.5" fill="none"/>
                                    <path d="M20 22C20.5523 22 21 21.5523 21 21C21 20.4477 20.5523 20 20 20C19.4477 20 19 20.4477 19 21C19 21.5523 19.4477 22 20 22Z" stroke="currentColor" stroke-width="1.5" fill="none"/>
                                    <path d="M1 1H5L7.68 14.39C7.77144 14.8504 8.02191 15.264 8.38755 15.5583C8.75318 15.8526 9.2107 16.009 9.68 16H19.4C19.8693 16.009 20.3268 15.8526 20.6925 15.5583C21.0581 15.264 21.3086 14.8504 21.4 14.39L23 6H6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" fill="none"/>
                                </svg>
                                <?php if ($cartCount > 0): ?>
                                    <span id="cart-count-badge" class="cart-count-badge"><?php echo $cartCount; ?></span>
                                <?php else: ?>
                                    <span id="cart-count-badge" class="cart-count-badge" style="display: none;">0</span>
                                <?php endif; ?>
                            </a>
                            <!-- Cart Dropdown -->
                            <div class="dropdown-menu cart-dropdown">
                                <div class="cart-dropdown-header">
                                    <h4>Giỏ hàng của bạn</h4>
                                    <span class="cart-dropdown-count"><?php echo $cartCount; ?> sản phẩm</span>
                                </div>
                                <div class="cart-dropdown-content" id="header-cart-items">
                                    <!-- Cart items will be loaded via AJAX -->
                                    <div class="cart-dropdown-loading">
                                        <i class="fas fa-spinner fa-spin"></i> Đang tải...
                                    </div>
                                </div>
                                <div class="cart-dropdown-footer">
                                    <a href="?page=users&module=cart" class="btn-view-cart">Xem giỏ hàng</a>
                                </div>
                            </div>
                        </div>
                        <!-- Authenticated User Menu -->
                        <div class="user-menu has-dropdown">
                            <button type="button" class="user-btn dropdown-btn">
                                <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M8 8C10.21 8 12 6.21 12 4C12 1.79 10.21 0 8 0C5.79 0 4 1.79 4 4C4 6.21 5.79 8 8 8ZM8 10C5.33 10 0 11.34 0 14V16H16V14C16 11.34 10.67 10 8 10Z" stroke="currentColor" stroke-width="1" fill="none"/>
                                </svg>
                                <?php echo htmlspecialchars($currentUser['username'] ?: $currentUser['name']); ?>
                                <svg width="10" height="6" viewBox="0 0 10 6" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M1 1L5 5L9 1" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </button>
                            <div class="dropdown-menu user-dropdown">
                                <a href="<?php echo nav_url('users'); ?>">Tài khoản của tôi</a>
                                <?php if ($currentUser['role'] === 'admin'): ?>
                                    <a href="<?php echo nav_url('affiliate'); ?>">Đại lý</a>
                                    <a href="<?php echo nav_url('admin'); ?>">Quản trị</a>
                                <?php elseif ($currentUser['role'] === 'agent'): ?>
                                    <a href="<?php echo nav_url('affiliate'); ?>">Đại lý</a>
                                <?php endif; ?>
                                <hr>
                                <a href="?page=logout">Đăng xuất</a>
                            </div>
                        </div>
                    <?php else: ?>
                        <!-- Guest User Buttons -->
                        <a href="<?php echo nav_url('register'); ?>" class="btn-get-started">Đăng ký</a>
                        <a href="<?php echo nav_url('login'); ?>" class="btn-login">
                            <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M8 8C10.21 8 12 6.21 12 4C12 1.79 10.21 0 8 0C5.79 0 4 1.79 4 4C4 6.21 5.79 8 8 8ZM8 10C5.33 10 0 11.34 0 14V16H16V14C16 11.34 10.67 10 8 10Z" stroke="currentColor" stroke-width="1" fill="none"/>
                            </svg>
                            Đăng nhập
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <!-- Mobile Navigation Drawer -->
    <div class="mobile-nav-drawer" id="mobileNavDrawer">
        <div class="drawer-header">
            <div class="drawer-logo">
                <a href="<?php echo base_url(); ?>">
                    <img src="<?php echo icon_url(get_logo('logo_header', 'logo/logo.svg')); ?>" alt="Thuonglo" width="130" height="30">
                </a>
            </div>
            <button type="button" class="drawer-close-btn" id="drawerCloseBtn" aria-label="Close Navigation">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>
        
        <div class="drawer-content">
            <!-- Search bar inside mobile drawer -->
            <div class="drawer-search">
                <form method="get" action="<?php echo base_url(); ?>">
                    <input type="text" name="search" placeholder="Tìm kiếm dịch vụ, data..." class="drawer-search-input">
                    <input type="hidden" value="products" name="page">
                    <button type="submit" class="drawer-search-btn">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="11" cy="11" r="8"></circle>
                            <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                        </svg>
                    </button>
                </form>
            </div>

            <ul class="drawer-menu">
                <li class="<?php echo ($currentPage == 'home') ? 'drawer-active' : ''; ?>"><a href="<?php echo base_url(); ?>">Trang chủ</a></li>
                
                <!-- Products Submenu -->
                <li class="drawer-has-submenu <?php echo (in_array($currentPage, $productPages)) ? 'drawer-active' : ''; ?>">
                    <button type="button" class="drawer-submenu-toggle">
                        Sản phẩm
                        <svg width="10" height="6" viewBox="0 0 10 6" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 1L5 5L9 1"/></svg>
                    </button>
                    <ul class="drawer-submenu">
                        <li><a href="<?php echo nav_url('products'); ?>">Tất cả sản phẩm</a></li>
                        <?php if (!empty($headerCategories)): ?>
                            <?php foreach ($headerCategories as $parentCat): ?>
                                <li><a href="<?php echo page_url('products', ['category' => $parentCat['id']]); ?>"><?php echo htmlspecialchars($parentCat['name']); ?></a></li>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </ul>
                </li>

                <!-- Guides Submenu -->
                <li class="drawer-has-submenu <?php echo (in_array($currentPage, $guidePages)) ? 'drawer-active' : ''; ?>">
                    <button type="button" class="drawer-submenu-toggle">
                        Hướng dẫn
                        <svg width="10" height="6" viewBox="0 0 10 6" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 1L5 5L9 1"/></svg>
                    </button>
                    <ul class="drawer-submenu">
                        <li><a href="<?php echo nav_url('about'); ?>">Giới thiệu</a></li>
                        <li><a href="<?php echo nav_url('contact'); ?>">Liên hệ hỗ trợ</a></li>
                        <li><a href="<?php echo nav_url('faq'); ?>">Câu hỏi thường gặp</a></li>
                    </ul>
                </li>

                <!-- News Submenu -->
                <li class="drawer-has-submenu <?php echo (in_array($currentPage, $newsPages)) ? 'drawer-active' : ''; ?>">
                    <button type="button" class="drawer-submenu-toggle">
                        Tin tức
                        <svg width="10" height="6" viewBox="0 0 10 6" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 1L5 5L9 1"/></svg>
                    </button>
                    <ul class="drawer-submenu">
                        <li><a href="<?php echo nav_url('news'); ?>">Tất cả tin tức</a></li>
                        <?php if (!empty($newsCategories)): ?>
                            <?php foreach ($newsCategories as $cat): ?>
                                <li><a href="<?php echo page_url('news', ['category' => $cat['id']]); ?>"><?php echo htmlspecialchars($cat['name']); ?></a></li>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </ul>
                </li>
                
                <li class="<?php echo ($currentPage == 'brands') ? 'drawer-active' : ''; ?>"><a href="<?php echo nav_url('brands'); ?>">Thương hiệu</a></li>
                
                <!-- Agent Submenu -->
                <li class="drawer-has-submenu <?php echo ($currentPage == 'affiliate' || $currentPage == 'agent' || $currentPage == 'agent-page') ? 'drawer-active' : ''; ?>">
                    <button type="button" class="drawer-submenu-toggle">
                        Đại lý
                        <svg width="10" height="6" viewBox="0 0 10 6" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 1L5 5L9 1"/></svg>
                    </button>
                    <ul class="drawer-submenu">
                        <li><a href="?page=agent-page&key=chuong_trinh">Chương trình đại lý</a></li>
                        <li><a href="?page=agent-page&key=huong_dan">Hướng dẫn đăng ký đại lý</a></li>
                        <li><a href="?page=agent-page&key=chinh_sach">Chính sách đại lý</a></li>
                        <li><a href="?page=agent-page&key=tai_nguyen">Tài nguyên - tài liệu đại lý</a></li>
                    </ul>
                </li>
                
                <?php if ($isAuthenticated): ?>
                    <li class="<?php echo ($currentPage == 'users') ? 'drawer-active' : ''; ?>"><a href="<?php echo nav_url('users'); ?>">Tài khoản của tôi</a></li>
                    <?php if ($currentUser['role'] === 'admin'): ?>
                        <li><a href="<?php echo nav_url('affiliate'); ?>">Đại lý</a></li>
                        <li><a href="<?php echo nav_url('admin'); ?>">Quản trị</a></li>
                    <?php elseif ($currentUser['role'] === 'agent' || $currentUser['role'] === 'affiliate'): ?>
                        <li><a href="<?php echo nav_url('affiliate'); ?>">Đại lý</a></li>
                    <?php endif; ?>
                    <li><a href="?page=logout" class="drawer-logout-btn">Đăng xuất</a></li>
                <?php else: ?>
                    <li><a href="<?php echo nav_url('login'); ?>" class="drawer-login-btn">Đăng nhập</a></li>
                    <li><a href="<?php echo nav_url('register'); ?>" class="drawer-register-btn">Đăng ký</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
    <div class="mobile-drawer-overlay" id="mobileDrawerOverlay"></div>

<script>
/**
 * Update cart count in header - called after adding to cart
 */
function updateHeaderCartCount() {
    const cartBadge = document.getElementById('cart-count-badge');
    const mobileCartBadge = document.querySelector('.mobile-cart-badge');
    if (!cartBadge && !mobileCartBadge) return;
    
    fetch('api.php?action=getUserData')
        .then(response => response.json())
        .then(data => {
            if (data.success && data.cart) {
                const count = data.cart.length;
                
                // Update desktop badge
                if (cartBadge) {
                    cartBadge.textContent = count;
                    if (count > 0) {
                        cartBadge.style.display = 'inline-block';
                    } else {
                        cartBadge.style.display = 'none';
                    }
                }
                
                // Update mobile badge
                if (mobileCartBadge) {
                    mobileCartBadge.textContent = count;
                    if (count > 0) {
                        mobileCartBadge.style.display = 'inline-block';
                    } else {
                        mobileCartBadge.style.display = 'none';
                    }
                }
                
                // Update dropdown count too
                const countEl = document.querySelector('.cart-dropdown-count');
                if (countEl) {
                    countEl.textContent = count + ' sản phẩm';
                }
            }
        })
        .catch(error => console.error('Error updating cart count:', error));
}

/**
 * Load cart items for dropdown preview
 */
function loadHeaderCartItems() {
    const container = document.getElementById('header-cart-items');
    if (!container) return;
    
    // Don't reload if already loaded
    if (container.dataset.loaded === 'true') return;
    
    container.innerHTML = '<div class="cart-dropdown-loading"><i class="fas fa-spinner fa-spin"></i> Đang tải...</div>';
    
    fetch('api.php?action=getUserData')
        .then(response => response.json())
        .then(data => {
            if (data.success && data.cart && data.cart.length > 0) {
                let html = '';
                data.cart.forEach(item => {
                    const imageUrl = item.image || 'assets/images/no-image.png';
                    const price = new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(item.price);
                    html += `
                        <div class="cart-dropdown-item">
                            <div class="cart-dropdown-item-image">
                                <img src="${imageUrl}" alt="${item.name || 'Sản phẩm'}" onerror="this.src='assets/images/no-image.png'">
                            </div>
                            <div class="cart-dropdown-item-info">
                                <h5 class="cart-dropdown-item-name">${item.name || 'Sản phẩm'}</h5>
                                <div class="cart-dropdown-item-price">${price}</div>
                                <div class="cart-dropdown-item-quantity">Số lượng: ${item.quantity || 1}</div>
                            </div>
                        </div>
                    `;
                });
                container.innerHTML = html;
                container.dataset.loaded = 'true';
                
                // Update count in header
                const countEl = document.querySelector('.cart-dropdown-count');
                if (countEl) {
                    countEl.textContent = data.cart.length + ' sản phẩm';
                }
            } else {
                container.innerHTML = `
                    <div class="cart-dropdown-empty">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <path d="M9 22C9.55228 22 10 21.5523 10 21C10 20.4477 9.55228 20 9 20C8.44772 20 8 20.4477 8 21C8 21.5523 8.44772 22 9 22Z" stroke="currentColor" fill="none"/>
                            <path d="M20 22C20.5523 22 21 21.5523 21 21C21 20.4477 20.5523 20 20 20C19.4477 20 19 20.4477 19 21C19 21.5523 19.4477 22 20 22Z" stroke="currentColor" fill="none"/>
                            <path d="M1 1H5L7.68 14.39C7.77144 14.8504 8.02191 15.264 8.38755 15.5583C8.75318 15.8526 9.2107 16.009 9.68 16H19.4C19.8693 16.009 20.3268 15.8526 20.6925 15.5583C21.0581 15.264 21.3086 14.8504 21.4 14.39L23 6H6" stroke="currentColor" fill="none"/>
                        </svg>
                        <p>Giỏ hàng trống</p>
                        <a href="?page=products" class="btn-view-cart">Mua sắm ngay</a>
                    </div>
                `;
                container.dataset.loaded = 'true';
            }
        })
        .catch(error => {
            console.error('Error loading cart items:', error);
            container.innerHTML = '<div class="cart-dropdown-empty"><p>Không thể tải giỏ hàng</p></div>';
        });
}

// Load cart items on hover
document.addEventListener('DOMContentLoaded', function() {
    const cartWrapper = document.querySelector('.header-cart-wrapper');
    if (cartWrapper) {
        cartWrapper.addEventListener('mouseenter', loadHeaderCartItems);
    }
    
    // Override addToCart if exists, or make it globally available
    if (typeof window.originalAddToCart === 'undefined') {
        window.originalAddToCart = window.addToCart;
    }
    
    window.addToCart = function(productId, quantity) {
        // Call original function
        const args = arguments;
        fetch('api.php?path=cart/add', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                product_id: productId,
                quantity: quantity || 1
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.require_login) {
                window.location.href = '?page=login&redirect=' + encodeURIComponent(window.location.href);
            } else if (data.success) {
                // Update cart count immediately
                updateHeaderCartCount();
                // Reload cart items in dropdown
                const container = document.getElementById('header-cart-items');
                if (container) {
                    container.dataset.loaded = 'false';
                    loadHeaderCartItems();
                }
                if (data.message) {
                    alert(data.message);
                }
            } else {
                alert(data.message || 'Có lỗi xảy ra');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Có lỗi xảy ra, vui lòng thử lại');
        });
    };


});
</script>