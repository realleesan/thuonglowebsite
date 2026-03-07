    <!-- Top Banner -->
<?php
// Initialize authentication state for header using AuthService (includes device validation)
require_once __DIR__ . '/../../services/AuthService.php';
$authService = new AuthService();
$isAuthenticated = $authService->isAuthenticated();

// Try to get categories from global $publicService
$headerCategories = [];
$newsCategories = [];

// Check if publicService is available in global scope
global $publicService;
if (isset($publicService) && is_object($publicService)) {
    // Get product categories
    try {
        $categoriesResult = $publicService->getCategoriesWithProductCounts();
        if (isset($categoriesResult['categories']) && is_array($categoriesResult['categories'])) {
            $headerCategories = $categoriesResult['categories'];
        }
    } catch (Exception $e) {
        error_log('Header categories error: ' . $e->getMessage());
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
?>

    <div class="top-banner">
        <div class="container">
            <p>Chào mừng đến với ThuongLo! Nền tảng data nguồn hàng và dịch vụ thương mại xuyên biên giới hàng đầu. <a href="?page=products">Khám phá ngay!</a></p>
        </div>
    </div>

    <!-- Main Header -->
    <header class="main-header">
        <div class="container">
            <div class="header-content">
                <!-- Logo -->
                <div class="logo">
                    <a href="<?php echo base_url(); ?>">
                        <img src="<?php echo icon_url('logo/logo.svg'); ?>" alt="Thuonglo" width="160" height="36">
                    </a>
                </div>

                <!-- Categories Dropdown -->
                <div class="categories-dropdown <?php echo ($currentPage == 'categories') ? 'active' : ''; ?>">
                    <a href="<?php echo nav_url('categories'); ?>" class="categories-btn">
                        <span>Danh mục </span>
                        <svg width="10" height="6" viewBox="0 0 10 6" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M1 1L5 5L9 1" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </a>
                    <div class="categories-menu">
                        <?php if (!empty($headerCategories)): ?>
                            <?php foreach ($headerCategories as $cat): ?>
                                <a href="<?php echo page_url('products', ['category' => $cat['id']]); ?>"><?php echo htmlspecialchars($cat['name']); ?></a>
                            <?php endforeach; ?>
                        <?php endif; ?>
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
                <?php
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
                ?>
                <ul class="main-menu">
                    <li class="<?php echo ($currentPage == 'home') ? 'active' : ''; ?>"><a href="<?php echo base_url(); ?>">Trang chủ</a></li>
                    <li class="has-dropdown <?php echo (in_array($currentPage, $productPages)) ? 'active' : ''; ?>">
                        <a href="<?php echo nav_url('products'); ?>">Sản phẩm <svg width="10" height="6" viewBox="0 0 10 6" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M1 1L5 5L9 1" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg></a>
                        <div class="dropdown-menu">
                            <a href="<?php echo page_url('products', ['order_by' => 'post_date']); ?>">Mới nhất</a>
                            <a href="<?php echo page_url('products', ['order_by' => 'popular']); ?>">Phổ biến</a>
                        </div>
                    </li>
                    <li class="has-dropdown <?php echo (in_array($currentPage, $guidePages)) ? 'active' : ''; ?>">
                        <button type="button" class="dropdown-btn">Hướng dẫn <svg width="10" height="6" viewBox="0 0 10 6" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M1 1L5 5L9 1" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg></button>
                        <div class="dropdown-menu">
                            <a href="<?php echo nav_url('about'); ?>">Giới thiệu</a>
                            <a href="<?php echo page_url('guide', ['type' => 'how-to-order']); ?>">Cách đặt hàng</a>
                            <a href="<?php echo page_url('guide', ['type' => 'payment']); ?>">Hướng dẫn thanh toán</a>
                            <a href="<?php echo page_url('guide', ['type' => 'shipping']); ?>">Quy trình vận chuyển</a>
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
                    <li class="<?php echo ($currentPage == 'affiliate' || $currentPage == 'agent') ? 'active' : ''; ?>">
                        <?php if ($isAuthenticated): ?>
                            <?php
                            // Check user's agent status from session or database
                            $userRole = $_SESSION['user_role'] ?? 'user';
                            $agentStatus = $_SESSION['agent_request_status'] ?? 'none';
                            
                            if ($userRole === 'agent' && $agentStatus === 'approved') {
                                // User is approved agent - go to affiliate dashboard
                                echo '<a href="' . nav_url('affiliate') . '">Đại lý</a>';
                            } elseif ($agentStatus === 'pending') {
                                // User has pending request - show processing message
                                echo '<a href="?page=agent&action=processing">Đại lý</a>';
                            } else {
                                // User can register as agent - show popup
                                echo '<a href="?page=agent">Đại lý</a>';
                            }
                            ?>
                        <?php else: ?>
                            <a href="?page=agent">Đại lý</a>
                        <?php endif; ?>
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

<script>
/**
 * Update cart count in header - called after adding to cart
 */
function updateHeaderCartCount() {
    const cartBadge = document.getElementById('cart-count-badge');
    if (!cartBadge) return;
    
    fetch('api.php?action=getUserData')
        .then(response => response.json())
        .then(data => {
            if (data.success && data.cart) {
                const count = data.cart.length;
                cartBadge.textContent = count;
                if (count > 0) {
                    cartBadge.style.display = 'inline-block';
                } else {
                    cartBadge.style.display = 'none';
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