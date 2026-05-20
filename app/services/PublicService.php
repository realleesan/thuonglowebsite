<?php

require_once __DIR__ . '/BaseService.php';
require_once __DIR__ . '/DataTransformer.php';

/**
 * PublicService
 *
 * Service chuyên xử lý data cho các trang public:
 * - home, products, categories, product details
 * - news, news details
 * - contact
 * - auth (login/register/forgot)
 *
 * Logic được tách dần từ ViewDataService nhưng sử dụng:
 * - Lazy loading models thông qua BaseService::getModel()
 * - Error handling tập trung qua BaseService::handleError()
 */
class PublicService extends BaseService
{
    protected DataTransformer $transformer;

    public function __construct(?ErrorHandler $errorHandler = null, string $serviceType = 'public')
    {
        parent::__construct($errorHandler, $serviceType);
        $this->transformer = new DataTransformer();
    }

    /**
     * Data cho trang Home.
     *
     * Tách từ ViewDataService::getHomePageData nhưng dùng lazy loading.
     */
    public function getHomePageData(): array
    {
        try {
            $data = [];

            // Featured products
            $featuredProducts = $this->callModelMethod(
                'ProductsModel',
                'getFeatured',
                [8],
                []
            );
            $data['featured_products'] = $this->transformer->transformProducts($featuredProducts);

            // Latest products
            $latestProducts = $this->callModelMethod(
                'ProductsModel',
                'getLatestForHome',
                [8],
                []
            );
            $data['latest_products'] = $this->transformer->transformProducts($latestProducts);
            
            // Budget products
            $budgetProducts = $this->callModelMethod(
                'ProductsModel',
                'getBudgetForHome',
                [8],
                []
            );
            $data['budget_products'] = $this->transformer->transformProducts($budgetProducts);
            
            // Sale products
            $saleProducts = $this->callModelMethod(
                'ProductsModel',
                'getSaleForHome',
                [8],
                []
            );
            $data['sale_products'] = $this->transformer->transformProducts($saleProducts);

            // Featured categories
            $featuredCategories = $this->callModelMethod(
                'CategoriesModel',
                'getFeaturedCategories',
                [9],
                []
            );
            $data['featured_categories'] = $this->transformer->transformCategories($featuredCategories);

            // Latest news
            $latestNews = $this->callModelMethod(
                'NewsModel',
                'getLatestForHome',
                [8],
                []
            );
            // Lưu ý: trong DataTransformer có transformNewsItems()
            $data['latest_news'] = $this->transformer->transformNewsItems($latestNews);

            // Featured brands
            $featuredBrands = $this->callModelMethod(
                'BrandsModel',
                'getFeatured',
                [6],
                []
            );
            $data['featured_brands'] = $this->transformer->transformBrands($featuredBrands);

            return $data;
        } catch (\Exception $e) {
            return $this->handleError($e, ['page' => 'home']);
        }
    }

    /**
     * Alias method for home view compatibility
     */
    public function getHomeData(): array
    {
        $data = $this->getHomePageData();
        
        // Fetch actual section settings from database
        $featuredProductsSection = $this->callModelMethod(
            'FeaturedProductsSectionModel',
            'getFirst',
            [],
            ['is_active' => 1, 'title' => '<h2 class="section-title">Sản phẩm <span class="highlight">Nổi bật</span></h2>']
        );
        
        $latestProductsSection = $this->callModelMethod(
            'LatestProductsSectionModel',
            'getFirst',
            [],
            ['is_active' => 1, 'title' => '<h2 class="section-title">Sản phẩm <span class="highlight">Mới nhất</span></h2>']
        );
        
        $budgetProductsSection = $this->callModelMethod(
            'BudgetProductsSectionModel',
            'getFirst',
            [],
            ['is_active' => 1, 'title' => '<h2 class="section-title">Sản phẩm <span class="highlight">Giá rẻ</span></h2>']
        );
        
        $saleProductsSection = $this->callModelMethod(
            'SaleProductsSectionModel',
            'getFirst',
            [],
            ['is_active' => 1, 'title' => '<h2 class="section-title">Sản phẩm <span class="highlight">Giảm giá</span></h2>']
        );
        
        // Transform keys to match home view expectations
        return [
            'featuredProducts' => $data['featured_products'] ?? [],
            'latestProducts' => $data['latest_products'] ?? [],
            'budgetProducts' => $data['budget_products'] ?? [],
            'saleProducts' => $data['sale_products'] ?? [],
            'featuredCategories' => $data['featured_categories'] ?? [],
            'featuredBrands' => $data['featured_brands'] ?? [],
            'latestNews' => $data['latest_news'] ?? [],
            // Add actual section settings from database
            'featuredProductsSection' => $featuredProductsSection,
            'latestProductsSection' => $latestProductsSection,
            'budgetProductsSection' => $budgetProductsSection,
            'saleProductsSection' => $saleProductsSection
        ];
    }

    /**
     * Data cho trang danh sách sản phẩm.
     *
     * Bám sát ViewDataService::getProductListingData.
     */
    public function getProductListingData(array $filters = []): array
    {
        try {
            $page = $filters['page'] ?? 1;
            $limit = $filters['limit'] ?? 12;
            $categoryId = $filters['category_id'] ?? null;
            $brandId = $filters['brand_id'] ?? null;
            $orderBy = $filters['order_by'] ?? 'post_date';
            $search = $filters['search'] ?? '';
            $priceType = $filters['price_type'] ?? ''; // 'free', 'paid', or empty string
            $minPrice = isset($filters['min_price']) && $filters['min_price'] !== '' ? (float) $filters['min_price'] : null;
            $maxPrice = isset($filters['max_price']) && $filters['max_price'] !== '' ? (float) $filters['max_price'] : null;
            $supplier = $filters['supplier'] ?? '';

            // Lấy danh sách sản phẩm (lấy tất cả rồi lọc thủ công)
            $products = $this->callModelMethod(
                'ProductsModel',
                'getWithCategory',
                [$limit * 10],
                []
            );

            // Apply filters manually (in-memory filtering)
            if (!is_array($products)) {
                $products = [];
            }

            // Filter by category
            if ($categoryId) {
                if (is_array($categoryId)) {
                    $selectedCategoryIds = array_map('intval', $categoryId);
                } else {
                    $selectedCategoryIds = [(int)$categoryId];
                }
                
                $allExpandedCategoryIds = [];
                $categoriesModel = $this->getModel('CategoriesModel');
                foreach ($selectedCategoryIds as $catId) {
                    $allExpandedCategoryIds[] = $catId;
                    if ($categoriesModel && method_exists($categoriesModel, 'getAllChildCategoryIds')) {
                        $childIds = $categoriesModel->getAllChildCategoryIds($catId);
                        if (is_array($childIds)) {
                            $allExpandedCategoryIds = array_merge($allExpandedCategoryIds, $childIds);
                        }
                    }
                }
                $allExpandedCategoryIds = array_unique(array_map('intval', $allExpandedCategoryIds));

                $products = array_filter($products, function ($product) use ($allExpandedCategoryIds) {
                    return in_array((int) ($product['category_id'] ?? 0), $allExpandedCategoryIds, true);
                });
            }

            // Filter by brand
            if ($brandId) {
                if (is_array($brandId)) {
                    $selectedBrandIds = array_map('intval', $brandId);
                } else {
                    $selectedBrandIds = [(int)$brandId];
                }
                $products = array_filter($products, function ($product) use ($selectedBrandIds) {
                    return in_array((int) ($product['brand_id'] ?? 0), $selectedBrandIds, true);
                });
            }

            // Filter by price type (single value: 'free', 'paid', or empty)
            if ($priceType === 'free') {
                // Miễn phí: price = 0 hoặc sale_price = 0
                $products = array_filter($products, function ($product) {
                    $price = floatval($product['price'] ?? 0);
                    $salePrice = floatval($product['sale_price'] ?? 0);
                    return $price == 0 || $salePrice == 0;
                });
            } elseif ($priceType === 'paid') {
                // Có phí: price > 0 hoặc sale_price > 0
                $products = array_filter($products, function ($product) {
                    $price = floatval($product['price'] ?? 0);
                    $salePrice = floatval($product['sale_price'] ?? 0);
                    return $price > 0 || $salePrice > 0;
                });
            }

            // Filter by custom price range
            if ($minPrice !== null || $maxPrice !== null) {
                $products = array_filter($products, function ($product) use ($minPrice, $maxPrice) {
                    $price = floatval($product['price'] ?? 0);
                    $salePrice = floatval($product['sale_price'] ?? 0);
                    $effectivePrice = $salePrice > 0 ? $salePrice : $price;

                    if ($minPrice !== null && $effectivePrice < $minPrice) {
                        return false;
                    }
                    if ($maxPrice !== null && $effectivePrice > $maxPrice) {
                        return false;
                    }

                    return true;
                });
            }

            // Filter by supplier
            if ($supplier) {
                $products = array_filter($products, function ($product) use ($supplier) {
                    return stripos($product['supplier_name'] ?? '', $supplier) !== false;
                });
            }

            // Search
            if ($search && is_array($products)) {
                $products = array_filter($products, function ($product) use ($search) {
                    return stripos($product['name'], $search) !== false
                        || stripos($product['description'] ?? '', $search) !== false;
                });
            }

            // Sorting
            $products = $this->sortProducts($products, $orderBy);

            // Pagination
            $total = count($products);
            $offset = ($page - 1) * $limit;
            $paginatedProducts = array_slice($products, $offset, $limit);

            return [
                'products' => $this->transformer->transformProducts($paginatedProducts),
                'pagination' => $this->calculatePagination($page, $limit, $total),
                'filters' => $filters,
            ];
        } catch (\Exception $e) {
            return $this->handleError($e, ['page' => 'products']);
        }
    }

    /**
     * Data cho trang category cụ thể.
     *
     * Bám sát ViewDataService::getCategoryPageData.
     */
    public function getCategoryPageData($categoryId): array
    {
        try {
            $category = $this->callModelMethod(
                'CategoriesModel',
                'find',
                [$categoryId]
            );

            if (!$category) {
                return $this->handleError(
                    new \Exception('Category not found'),
                    ['page' => 'category', 'category_id' => $categoryId]
                );
            }

            $products = $this->callModelMethod(
                'ProductsModel',
                'getByCategory',
                [$categoryId, 12],
                []
            );

            return [
                'category' => $this->transformer->transformCategory($category),
                'products' => $this->transformer->transformProducts($products),
            ];
        } catch (\Exception $e) {
            return $this->handleError($e, ['page' => 'category', 'category_id' => $categoryId]);
        }
    }

    /**
     * Data cho trang chi tiết sản phẩm.
     *
     * Bám sát ViewDataService::getProductDetailsData.
     */
    public function getProductDetailsData($productId): array
    {
        try {
            $product = $this->callModelMethod(
                'ProductsModel',
                'find',
                [$productId]
            );

            if (!$product) {
                return $this->handleError(
                    new \Exception('Product not found'),
                    ['page' => 'product_details', 'product_id' => $productId]
                );
            }

            $category = null;
            if (!empty($product['category_id'])) {
                $category = $this->callModelMethod(
                    'CategoriesModel',
                    'find',
                    [$product['category_id']]
                );
            }

            $relatedProducts = [];
            if (!empty($product['category_id'])) {
                $allCategoryProducts = $this->callModelMethod(
                    'ProductsModel',
                    'getByCategory',
                    [$product['category_id'], 8],
                    []
                );

                if (is_array($allCategoryProducts)) {
                    $relatedProducts = array_filter($allCategoryProducts, function ($p) use ($productId) {
                        return $p['id'] != $productId;
                    });
                    $relatedProducts = array_slice($relatedProducts, 0, 4);
                }
            }

            // Get reviews for this product
            $reviews = $this->getProductReviews($productId);

            // Parse JSON fields for Logistics/Data data
            $benefits = [];
            if (!empty($product['benefits'])) {
                $decoded = json_decode($product['benefits'], true);
                if (is_array($decoded)) {
                    $benefits = $decoded;
                }
            }

            $dataStructure = [];
            if (!empty($product['data_structure'])) {
                $decoded = json_decode($product['data_structure'], true);
                if (is_array($decoded)) {
                    $dataStructure = $decoded;
                }
            }

            // Parse supplier social
            $supplierSocial = [];
            if (!empty($product['supplier_social'])) {
                $decoded = json_decode($product['supplier_social'], true);
                if (is_array($decoded)) {
                    $supplierSocial = $decoded;
                }
            }

            // Build supplier data
            $supplier = null;
            if (!empty($product['supplier_name'])) {
                $supplier = [
                    'name' => $product['supplier_name'],
                    'title' => $product['supplier_title'] ?? 'Nhà cung cấp',
                    'bio' => $product['supplier_bio'] ?? '',
                    'avatar' => $product['supplier_avatar'] ?? '',
                    'social' => $supplierSocial
                ];
            }

            return [
                'product' => $this->transformer->transformProduct($product),
                'category' => $category ? $this->transformer->transformCategory($category) : null,
                'related_products' => $this->transformer->transformProducts($relatedProducts),
                'benefits' => $benefits,
                'data_structure' => $dataStructure,
                'supplier' => $supplier,
                'reviews' => $reviews,
                'product_meta' => [
                    'record_count' => $product['record_count'] ?? 0,
                    'data_size' => $product['data_size'] ?? '',
                    'data_type' => $product['data_type'] ?? '',
                    'data_format' => $product['data_format'] ?? '',
                    'data_source' => $product['data_source'] ?? '',
                    'reliability' => $product['reliability'] ?? ''
                ]
            ];
        } catch (\Exception $e) {
            return $this->handleError($e, ['page' => 'product_details', 'product_id' => $productId]);
        }
    }

    /**
     * Get product reviews
     */
    private function getProductReviews(int $productId): array
    {
        try {
            $reviews = $this->callModelMethod(
                'ProductsModel',
                'getProductReviews',
                [$productId],
                []
            );
            return is_array($reviews) ? $reviews : [];
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Calculate average rating from reviews
     */
    private function calculateAverageRating(array $reviews): float
    {
        if (empty($reviews)) {
            return 0.0;
        }
        $total = array_sum(array_column($reviews, 'rating'));
        return round($total / count($reviews), 1);
    }

    /**
     * Data cho trang Liên hệ.
     *
     * Bám sát ViewDataService::getContactPageData.
     */
    public function getContactPageData(): array
    {
        try {
            $contactSettings = $this->callModelMethod(
                'SettingsModel',
                'getContactSettings',
                [],
                []
            );

            $defaultContact = [
                'office_address' => 'Tầng 12, Tòa nhà ABC, 123 Đường Nguyễn Huệ<br>Quận 1, TP. Hồ Chí Minh',
                'phone' => '(+84) 28 - 3825 - 6789',
                'hotline' => '1900 - 1234',
                'email' => 'contact@thuonglo.com',
                'working_hours_weekday' => 'Thứ 2 - Thứ 6: 08:00 - 18:00',
                'working_hours_weekend' => 'Thứ 7 & Chủ nhật: 09:00 - 17:00',
            ];

            return [
                'contact' => array_merge($defaultContact, $contactSettings ?: []),
            ];
        } catch (\Exception $e) {
            return $this->handleError($e, ['page' => 'contact']);
        }
    }

    /**
     * Data categories + product counts cho sidebar/filter (products page).
     *
     * Thay thế ViewDataService::getCategoriesWithProductCounts cho public.
     */
    public function getCategoriesWithProductCounts(): array
    {
        try {
            $categories = $this->callModelMethod(
                'CategoriesModel',
                'getWithProductCounts',
                [],
                []
            );

            // Debug: Log categories data
            error_log('DEBUG getCategoriesWithProductCounts: ' . print_r($categories, true));

            return [
                'categories' => $this->transformer->transformCategories($categories),
            ];
        } catch (\Exception $e) {
            error_log('ERROR getCategoriesWithProductCounts: ' . $e->getMessage());
            return [
                'categories' => [],
            ];
        }
    }

    /**
     * Lấy thương hiệu hiển thị ở bộ lọc/header dropdown.
     * Áp dụng thứ tự sắp xếp từ filter_config như sidebar trang sản phẩm
     */
    public function getBrandsForFilter(): array
    {
        try {
            // Sử dụng FilterConfigService để lấy thương hiệu với thứ tự đã cấu hình
            require_once __DIR__ . '/FilterConfigService.php';
            $filterConfigService = new \FilterConfigService();
            
            $brands = $filterConfigService->getBrandsForFilter();
            
            return [
                'brands' => $brands,
            ];
        } catch (\Exception $e) {
            error_log('ERROR getBrandsForFilter: ' . $e->getMessage());
            
            // Fallback to original method if FilterConfigService fails
            try {
                $brands = $this->callModelMethod(
                    'BrandsModel',
                    'getForFilter',
                    [],
                    []
                );

                return [
                    'brands' => $this->transformer->transformBrands($brands),
                ];
            } catch (\Exception $fallbackException) {
                error_log('ERROR getBrandsForFilter fallback: ' . $fallbackException->getMessage());
                return [
                    'brands' => [],
                ];
            }
        }
    }

    /**
     * Lấy thương hiệu nổi bật cho trang chủ.
     */
    public function getFeaturedBrands(int $limit = 6): array
    {
        try {
            $brands = $this->callModelMethod(
                'BrandsModel',
                'getFeatured',
                [$limit],
                []
            );

            return $this->transformer->transformBrands($brands);
        } catch (\Exception $e) {
            error_log('ERROR getFeaturedBrands: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Lấy danh mục theo cấu trúc phân cấp cha-con cho header menu
     * Trả về mảng các danh mục cha với children bên trong
     * Áp dụng thứ tự sắp xếp từ filter_config như sidebar trang sản phẩm
     */
    public function getCategoriesHierarchy(): array
    {
        try {
            // Sử dụng FilterConfigService để lấy danh mục với thứ tự đã cấu hình
            require_once __DIR__ . '/FilterConfigService.php';
            $filterConfigService = new \FilterConfigService();
            
            $allCategories = $filterConfigService->getCategoriesForFilter();
            
            if (!is_array($allCategories)) {
                return [];
            }

            // Transform dữ liệu nếu cần
            $allCategories = $this->transformer->transformCategories($allCategories);

            // Xây dựng cây phân cấp đầy đủ với sắp xếp đúng thứ tự
            $hierarchy = [];
            $categoryMap = [];
            
            // First pass: tạo map tất cả categories và khởi tạo children rỗng
            foreach ($allCategories as $category) {
                $category['children'] = [];
                $categoryMap[$category['id']] = $category;
            }
            
            // Second pass: xây dựng cây phân cấp
            foreach ($allCategories as $category) {
                $parentId = $category['parent_id'] ?? 0;
                
                // Nếu là danh mục cha (parent_id = 0 hoặc null), thêm vào hierarchy
                if ($parentId == 0 || $parentId === null) {
                    $hierarchy[] = &$categoryMap[$category['id']];
                } else {
                    // Nếu là danh mục con, thêm vào children của cha
                    if (isset($categoryMap[$parentId])) {
                        $categoryMap[$parentId]['children'][] = &$categoryMap[$category['id']];
                    }
                }
            }
            
            // Third pass: sắp xếp children theo sort_order
            $sortChildren = function(&$categories) use (&$sortChildren) {
                foreach ($categories as &$category) {
                    if (!empty($category['children'])) {
                        // Sắp xếp children theo sort_order
                        usort($category['children'], function ($a, $b) {
                            $sortA = (int)($a['sort_order'] ?? 999);
                            $sortB = (int)($b['sort_order'] ?? 999);
                            if ($sortA === $sortB) {
                                return strcmp((string)($a['name'] ?? ''), (string)($b['name'] ?? ''));
                            }
                            return $sortA <=> $sortB;
                        });
                        // Đệ quy sắp xếp các cấp con sâu hơn
                        $sortChildren($category['children']);
                    }
                }
            };
            
            $sortChildren($hierarchy);

            return $hierarchy;
        } catch (\Exception $e) {
            error_log('ERROR getCategoriesHierarchy: ' . $e->getMessage());
            
            // Fallback to original method if FilterConfigService fails
            try {
                $allCategories = $this->callModelMethod(
                    'CategoriesModel',
                    'getWithProductCounts',
                    [],
                    []
                );

                if (!is_array($allCategories)) {
                    return [];
                }

                $categories = $this->transformer->transformCategories($allCategories);

                $categoriesModel = $this->getModel('CategoriesModel');
                if ($categoriesModel) {
                    return $categoriesModel->buildTree($categories);
                }
            } catch (\Exception $fallbackException) {
                error_log('ERROR getCategoriesHierarchy fallback: ' . $fallbackException->getMessage());
            }
            
            return [];
        }
    }

    /**
     * Data cho trang danh mục (categories listing).
     *
     * Dựa trên ViewDataService::getCategoriesPageData (phần public).
     */
    public function getCategoriesPageData($page = 1, $perPage = 12, $orderBy = 'name'): array
    {
        try {
            // Dùng getAllWithProductCounts để lấy tất cả danh mục active (không lọc show_in_filter)
            $allCategories = $this->callModelMethod(
                'CategoriesModel',
                'getAllWithProductCounts',
                [],
                []
            );

            if (!is_array($allCategories)) {
                $allCategories = [];
            }

            $sortedCategories = $this->sortCategories($allCategories, $orderBy);

            $total = count($sortedCategories);
            $offset = ($page - 1) * $perPage;
            $categories = array_slice($sortedCategories, $offset, $perPage);

            $stats = $this->callModelMethod(
                'CategoriesModel',
                'getStats',
                [],
                []
            );

            $transformedCategories = [];
            foreach ($categories as $category) {
                $transformedCategories[] = $this->transformer->transformCategory($category);
            }

            return [
                'categories' => $transformedCategories,
                'pagination' => $this->calculatePagination($page, $perPage, $total),
                'stats' => $stats,
                'total_categories' => $total,
                'displayed_count' => count($transformedCategories),
                'current_sort' => $orderBy,
            ];
        } catch (\Exception $e) {
            return $this->handleError($e, ['page' => 'categories']);
        }
    }

    /**
     * Data cho trang login (auth).
     *
     * Bám sát phần ViewDataService::getAuthLoginData (phiên bản mới).
     */
    public function getAuthLoginData(): array
    {
        try {
            $rememberedPhone = $_SESSION['remember_phone'] ?? ($_COOKIE['remember_phone'] ?? '');
            $rememberedRole = $_SESSION['remember_role'] ?? ($_COOKIE['remember_role'] ?? 'user');

            return [
                'remembered_phone' => $rememberedPhone,
                'remembered_role' => $rememberedRole,
                'page_title' => 'Đăng nhập',
                'form_action' => function_exists('form_url') ? form_url() : '?page=login',
            ];
        } catch (\Exception $e) {
            return $this->handleError($e, ['page' => 'auth_login']);
        }
    }

    /**
     * Data cho trang register (auth).
     */
    public function getAuthRegisterData(): array
    {
        try {
            $refCodeFromUrl = '';
            if (isset($_GET['ref']) && !empty($_GET['ref'])) {
                $refCodeFromUrl = htmlspecialchars(strip_tags(trim((string)$_GET['ref'])));
            }

            return [
                'ref_code_from_url' => $refCodeFromUrl,
                'page_title' => 'Đăng ký',
                'form_action' => function_exists('form_url') ? form_url() : '?page=register',
                'login_url' => function_exists('page_url') ? page_url('login') : '?page=login',
            ];
        } catch (\Exception $e) {
            return $this->handleError($e, ['page' => 'auth_register']);
        }
    }

    /**
     * Data cho trang quên mật khẩu (auth).
     */
    public function getAuthForgotData(): array
    {
        try {
            $step = $_SESSION['forgot_step'] ?? 'input';
            $resetContact = $_SESSION['reset_contact'] ?? '';

            return [
                'step' => $step,
                'reset_contact' => $resetContact,
                'page_title' => 'Khôi phục tài khoản',
                'form_action' => function_exists('form_url') ? form_url('forgot') : '?page=forgot',
                'login_url' => function_exists('page_url') ? page_url('login') : '?page=login',
            ];
        } catch (\Exception $e) {
            return $this->handleError($e, ['page' => 'auth_forgot']);
        }
    }

    /**
     * Data cho trang checkout (đơn giản, phục vụ demo/order 1 sản phẩm).
     * Lưu ý: Nên sử dụng checkout.php với UserService để lấy giỏ hàng thực tế.
     *
     * @param mixed $productId
     */
    public function getCheckoutData($productId = null): array
    {
        try {
            $items = [];
            $total = 0;

            if ($productId) {
                $product = $this->callModelMethod(
                    'ProductsModel',
                    'find',
                    [$productId]
                );

                if ($product) {
                    $price = (float) ($product['sale_price'] ?? $product['price'] ?? 0);
                    $items[] = [
                        'id' => $product['id'],
                        'name' => $product['name'],
                        'price' => $price,
                        'image' => $product['image'] ?? 'home/home-banner-top.png',
                        'quantity' => 1,
                    ];
                    $total = $price;
                }
            }

            // Không còn fallback sản phẩm demo - nếu không có sản phẩm, trả về mảng rỗng
            // Hệ thống sẽ chuyển về trang giỏ hàng nếu không có sản phẩm
            return [
                'cart_items' => $items,
                'total_amount' => $total,
            ];
        } catch (\Exception $e) {
            return [
                'cart_items' => [],
                'total_amount' => 0,
            ];
        }
    }

    /**
     * Data cho trang xử lý thanh toán (payment processing).
     */
    public function getPaymentProcessingData(): array
    {
        // Hiện tại chỉ cần trả về mã đơn + số tiền demo an toàn
        $orderId = 'DEMO_' . rand(1000, 9999);
        $amount = 250000;

        return [
            'order_id' => $orderId,
            'amount' => $amount,
        ];
    }

    /**
     * Data cho trang thanh toán thành công.
     *
     * @param mixed $orderId
     */
    public function getPaymentSuccessData($orderId = null): array
    {
        // Trong Phase này, giữ logic demo giống view cũ.
        $orderId = $orderId ?: 'DEMO_' . rand(1000, 9999);
        $order = [
            'id' => $orderId,
            'status' => 'completed',
            'payment_method' => 'sepay',
            'total_amount' => 250000,
            'created_at' => date('Y-m-d H:i:s'),
        ];

        return [
            'order' => $order,
            'order_items' => [],
            'total_amount' => $order['total_amount'],
        ];
    }

    /**
     * Hàm sort products (copy từ ViewDataService, tối ưu cho public listing).
     */
    private function sortProducts(array $products, string $orderBy): array
    {
        switch ($orderBy) {
            case 'post_title':
                usort($products, function ($a, $b) {
                    return strcmp($a['name'], $b['name']);
                });
                break;
            case 'post_title_desc':
                usort($products, function ($a, $b) {
                    return strcmp($b['name'], $a['name']);
                });
                break;
            case 'price':
                usort($products, function ($a, $b) {
                    return ($b['price'] ?? 0) - ($a['price'] ?? 0);
                });
                break;
            case 'price_low':
                usort($products, function ($a, $b) {
                    return ($a['price'] ?? 0) - ($b['price'] ?? 0);
                });
                break;
            case 'popular':
                usort($products, function ($a, $b) {
                    return ($b['view_count'] ?? 0) - ($a['view_count'] ?? 0);
                });
                break;
            case 'rating':
                usort($products, function ($a, $b) {
                    return ($b['rating'] ?? 0) - ($a['rating'] ?? 0);
                });
                break;
            default: // post_date
                usort($products, function ($a, $b) {
                    return strtotime($b['created_at'] ?? '0') - strtotime($a['created_at'] ?? '0');
                });
                break;
        }

        return $products;
    }

    /**
     * Hàm sort categories (copy từ ViewDataService, tối ưu cho public listing).
     */
    private function sortCategories(array $categories, string $orderBy): array
    {
        switch ($orderBy) {
            case 'name_desc':
                usort($categories, function ($a, $b) {
                    return strcmp($b['name'], $a['name']);
                });
                break;
            case 'course_count':
            case 'product_count':
                usort($categories, function ($a, $b) {
                    return ($b['product_count'] ?? 0) - ($a['product_count'] ?? 0);
                });
                break;
            case 'course_count_desc':
            case 'product_count_desc':
                usort($categories, function ($a, $b) {
                    return ($a['product_count'] ?? 0) - ($b['product_count'] ?? 0);
                });
                break;
            case 'popular':
                usort($categories, function ($a, $b) {
                    return ($b['product_count'] ?? 0) - ($a['product_count'] ?? 0);
                });
                break;
            default: // name
                usort($categories, function ($a, $b) {
                    return strcmp($a['name'], $b['name']);
                });
                break;
        }

        return $categories;
    }

    // ==================== AUTH ACTION METHODS ====================

    /**
     * Authenticate user login.
     */
    public function authenticateUser($login, $password)
    {
        try {
            $usersModel = $this->getModel('UsersModel');
            if (!$usersModel) {
                return ['success' => false, 'message' => 'Đã xảy ra lỗi trong quá trình đăng nhập'];
            }

            $user = $usersModel->authenticate($login, $password);

            if ($user) {
                return ['success' => true, 'user' => $user];
            }

            return ['success' => false, 'message' => 'Tên đăng nhập hoặc mật khẩu không đúng'];
        } catch (\Exception $e) {
            $this->errorHandler->logError('PublicService::authenticateUser error: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Đã xảy ra lỗi trong quá trình đăng nhập'];
        }
    }

    /**
     * Register new user.
     */
    public function registerUser($userData)
    {
        try {
            $usersModel = $this->getModel('UsersModel');
            $affiliateModel = $this->getModel('AffiliateModel');

            if (!$usersModel) {
                return ['success' => false, 'message' => 'Không thể tạo tài khoản'];
            }

            // Kiểm tra referral code nếu có
            $referralInfo = null;
            if (!empty($userData['ref_code']) && $affiliateModel) {
                $affiliate = $affiliateModel->getByReferralCode($userData['ref_code']);
                if ($affiliate) {
                    $referralInfo = [
                        'referred_by' => $affiliate['user_id'],
                        'referral_code' => $userData['ref_code'],
                    ];
                }
            }

            $user = $usersModel->register([
                'name' => $userData['name'],
                'email' => $userData['email'],
                'phone' => $userData['phone'],
                'password' => $userData['password'],
                'role' => 'user',
                'status' => 'active',
            ]);

            if ($user) {
                return ['success' => true, 'user' => $user, 'referral_info' => $referralInfo];
            }

            return ['success' => false, 'message' => 'Không thể tạo tài khoản'];
        } catch (\Exception $e) {
            $this->errorHandler->logError('PublicService::registerUser error: ' . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Update user password.
     */
    public function updateUserPassword($userId, $currentPassword, $newPassword)
    {
        try {
            $usersModel = $this->getModel('UsersModel');
            if (!$usersModel) {
                return ['success' => false, 'message' => 'Không thể cập nhật mật khẩu'];
            }

            $user = $usersModel->find($userId);
            if (!$user) {
                return ['success' => false, 'message' => 'Người dùng không tồn tại'];
            }

            if (!password_verify($currentPassword, $user['password'])) {
                return ['success' => false, 'message' => 'Mật khẩu hiện tại không đúng'];
            }

            $result = $usersModel->updatePassword($userId, $newPassword);

            return [
                'success' => $result,
                'message' => $result ? 'Cập nhật mật khẩu thành công' : 'Không thể cập nhật mật khẩu',
            ];
        } catch (\Exception $e) {
            $this->errorHandler->logError('PublicService::updateUserPassword error: ' . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Reset user password.
     */
    public function resetUserPassword($contact, $newPassword)
    {
        try {
            $usersModel = $this->getModel('UsersModel');
            if (!$usersModel) {
                return ['success' => false, 'message' => 'Không thể đặt lại mật khẩu'];
            }

            $user = $usersModel->db->table('users')
                ->where('email', $contact)
                ->orWhere('phone', $contact)
                ->first();

            if (!$user) {
                return ['success' => false, 'message' => 'Không tìm thấy tài khoản với thông tin này'];
            }

            $result = $usersModel->updatePassword($user['id'], $newPassword);

            return [
                'success' => $result,
                'message' => $result ? 'Đặt lại mật khẩu thành công' : 'Không thể đặt lại mật khẩu',
            ];
        } catch (\Exception $e) {
            $this->errorHandler->logError('PublicService::resetUserPassword error: ' . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Send password reset code.
     */
    public function sendPasswordResetCode($contact)
    {
        try {
            $usersModel = $this->getModel('UsersModel');
            if (!$usersModel) {
                return ['success' => false, 'message' => 'Không thể gửi mã xác thực'];
            }

            $user = $usersModel->db->table('users')
                ->where('email', $contact)
                ->orWhere('phone', $contact)
                ->first();

            if (!$user) {
                return ['success' => false, 'message' => 'Không tìm thấy tài khoản với thông tin này'];
            }

            $code = rand(100000, 999999);
            error_log("Reset code for {$contact}: {$code}");

            return ['success' => true, 'code' => $code, 'message' => 'Mã xác thực đã được gửi'];
        } catch (\Exception $e) {
            $this->errorHandler->logError('PublicService::sendPasswordResetCode error: ' . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Handle empty state data.
     */
    public function handleEmptyState(string $type): array
    {
        $emptyStates = [
            'home' => [
                'featured_products' => [], 'latest_products' => [],
                'featured_categories' => [], 'latest_news' => [],
                'message' => 'Đang cập nhật dữ liệu, vui lòng thử lại sau',
            ],
            'products' => [
                'products' => [], 'pagination' => ['current_page' => 1, 'total' => 0],
                'message' => 'Chưa có sản phẩm nào',
            ],
            'product_details' => [
                'product' => null, 'category' => null, 'related_products' => [],
                'message' => 'Không tìm thấy sản phẩm',
            ],
            'contact' => [
                'contact' => [
                    'office_address' => 'Đang cập nhật', 'phone' => 'Đang cập nhật',
                    'hotline' => 'Đang cập nhật', 'email' => 'Đang cập nhật',
                    'working_hours_weekday' => 'Đang cập nhật', 'working_hours_weekend' => 'Đang cập nhật',
                ],
                'message' => 'Không thể tải thông tin liên hệ',
            ],
            'auth_login' => [
                'remembered_phone' => '', 'remembered_role' => 'user',
                'page_title' => 'Đăng nhập', 'form_action' => '#',
            ],
            'auth_register' => [
                'ref_code_from_url' => '', 'page_title' => 'Đăng ký',
                'form_action' => '#', 'login_url' => '#',
            ],
            'auth_forgot' => [
                'step' => 'input', 'reset_contact' => '',
                'page_title' => 'Khôi phục tài khoản', 'form_action' => '#', 'login_url' => '#',
            ],
        ];

        return $emptyStates[$type] ?? ['message' => 'Không có dữ liệu'];
    }

    // ==================== HOMEPAGE SECTION METHODS ====================

    /**
     * Get latest products for homepage section
     */
    public function getLatestProducts(int $limit = 8): array
    {
        try {
            $database = Database::getInstance();
            $pdo = $database->getPdo();
            
            $sql = "
                SELECT p.* FROM products p
                INNER JOIN latest_products_section_products lsp ON p.id = lsp.product_id
                WHERE p.status = 'active'
                ORDER BY lsp.sort_order ASC, p.id DESC
                LIMIT ?
            ";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$limit]);
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return $this->transformer->transformProducts($result);
        } catch (Exception $e) {
            $this->handleError($e, ['method' => 'getLatestProducts', 'limit' => $limit]);
            return [];
        }
    }

    /**
     * Get budget products for homepage section
     */
    public function getBudgetProducts(int $limit = 8): array
    {
        try {
            $database = Database::getInstance();
            $pdo = $database->getPdo();
            
            $sql = "
                SELECT p.* FROM products p
                INNER JOIN budget_products_section_products bsp ON p.id = bsp.product_id
                WHERE p.status = 'active'
                ORDER BY bsp.sort_order ASC, p.price ASC
                LIMIT ?
            ";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$limit]);
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return $this->transformer->transformProducts($result);
        } catch (Exception $e) {
            $this->handleError($e, ['method' => 'getBudgetProducts', 'limit' => $limit]);
            return [];
        }
    }

    /**
     * Get sale products for homepage section
     */
    public function getSaleProducts(int $limit = 8): array
    {
        try {
            $database = Database::getInstance();
            $pdo = $database->getPdo();
            
            $sql = "
                SELECT p.* FROM products p
                INNER JOIN sale_products_section_products ssp ON p.id = ssp.product_id
                WHERE p.status = 'active' AND (p.sale_price IS NOT NULL AND p.sale_price > 0)
                ORDER BY ssp.sort_order ASC, p.sale_price ASC
                LIMIT ?
            ";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$limit]);
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return $this->transformer->transformProducts($result);
        } catch (Exception $e) {
            $this->handleError($e, ['method' => 'getSaleProducts', 'limit' => $limit]);
            return [];
        }
    }

    // ==================== HELPERS ====================

    /**
     * Tính toán pagination giống ViewDataService.
     */
    private function calculatePagination(int $currentPage, int $perPage, int $total): array
    {
        return [
            'current_page' => $currentPage,
            'per_page' => $perPage,
            'total' => $total,
            'last_page' => $perPage > 0 ? (int) ceil($total / $perPage) : 1,
            'from' => $total > 0 ? (($currentPage - 1) * $perPage + 1) : 0,
            'to' => $total > 0 ? min($currentPage * $perPage, $total) : 0,
        ];
    }
}

