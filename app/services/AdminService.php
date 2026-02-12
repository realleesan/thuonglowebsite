<?php

require_once __DIR__ . '/BaseService.php';
require_once __DIR__ . '/DataTransformer.php';

/**
 * AdminService
 *
 * Service chuyên xử lý data cho khu vực admin:
 * - Dashboard
 * - Products, Categories, News, Orders, Users
 * - Affiliates, Events, Contacts, Settings, Revenue
 *
 * Phase 4: Đã loại bỏ hoàn toàn dependency vào ViewDataService.
 * Sử dụng trực tiếp BaseService::getModel() với lazy loading.
 */
class AdminService extends BaseService
{
    protected DataTransformer $transformer;

    public function __construct(?ErrorHandler $errorHandler = null, string $serviceType = 'admin')
    {
        parent::__construct($errorHandler, $serviceType);
        $this->transformer = new DataTransformer();
    }

    // ==================== DASHBOARD ====================

    public function getDashboardData(): array
    {
        try {
            $data = [];

            // Product statistics
            $data['product_stats'] = $this->callModelMethod('ProductsModel', 'getStats', [], []);
            $data['stats'] = [
                'total_products' => $data['product_stats']['total'] ?? 0,
                'total_revenue' => 0,
            ];

            // User statistics
            $data['user_stats'] = $this->callModelMethod('UsersModel', 'getStats', [], []);

            // Recent products
            $recentProducts = $this->callModelMethod('ProductsModel', 'getWithCategory', [5], []);
            $data['recent_products'] = $this->transformer->transformProducts($recentProducts);

            // Recent users
            $recentUsers = $this->callModelMethod('UsersModel', 'all', ['*'], []);
            $data['recent_users'] = $this->transformer->transformUsers($recentUsers);

            // Top products, recent activities, charts (placeholders)
            $data['top_products'] = [];
            $data['recent_activities'] = [];
            $data['charts_data'] = [];
            $data['trends'] = [];
            $data['alerts'] = [];

            return $data;
        } catch (\Exception $e) {
            return $this->handleError($e, ['method' => 'getDashboardData']);
        }
    }

    // ==================== PRODUCTS ====================

    public function getProductsData(int $page = 1, int $perPage = 10, array $filters = []): array
    {
        try {
            $productsModel = $this->getModel('ProductsModel');
            $categoriesModel = $this->getModel('CategoriesModel');

            if (!$productsModel || !$categoriesModel) {
                return $this->getEmptyData();
            }

            // Get categories for filter dropdown
            $categories = $this->callModelMethod('CategoriesModel', 'getActive', [], []);

            // Build search conditions
            $conditions = [];
            $bindings = [];

            if (!empty($filters['search'])) {
                $conditions[] = "(name LIKE ? OR description LIKE ?)";
                $searchTerm = "%{$filters['search']}%";
                $bindings = array_merge($bindings, [$searchTerm, $searchTerm]);
            }

            if (!empty($filters['category_id'])) {
                $conditions[] = "category_id = ?";
                $bindings[] = $filters['category_id'];
            }

            if (!empty($filters['status'])) {
                $conditions[] = "status = ?";
                $bindings[] = $filters['status'];
            }

            // Get total count
            $whereClause = !empty($conditions) ? 'WHERE ' . implode(' AND ', $conditions) : '';
            $countSql = "SELECT COUNT(*) as total FROM products {$whereClause}";
            $totalResult = $productsModel->db->query($countSql, $bindings);
            $total = $totalResult[0]['total'] ?? 0;

            // Get products with pagination
            $offset = ($page - 1) * $perPage;
            $productsSql = "SELECT * FROM products {$whereClause} ORDER BY created_at DESC LIMIT {$perPage} OFFSET {$offset}";
            $products = $productsModel->db->query($productsSql, $bindings);

            $transformedProducts = [];
            foreach ($products as $product) {
                $transformedProducts[] = $this->transformer->transformProduct($product);
            }

            return [
                'products' => $transformedProducts,
                'categories' => $categories,
                'pagination' => $this->calculatePagination($page, $perPage, $total),
                'filters' => $filters,
                'total' => $total,
            ];
        } catch (\Exception $e) {
            return $this->handleError($e, ['method' => 'getProductsData']);
        }
    }

    public function getProductDetailsData(int $productId): array
    {
        try {
            $productsModel = $this->getModel('ProductsModel');
            $categoriesModel = $this->getModel('CategoriesModel');

            if (!$productsModel) {
                return ['product' => null, 'categories' => []];
            }

            $product = $this->callModelMethod('ProductsModel', 'findById', [$productId]);
            if (!$product) {
                return ['product' => null, 'categories' => []];
            }

            $categories = $this->callModelMethod('CategoriesModel', 'getActive', [], []);

            return [
                'product' => $this->transformer->transformProduct($product),
                'categories' => $categories,
            ];
        } catch (\Exception $e) {
            return $this->handleError($e, ['method' => 'getProductDetailsData', 'product_id' => $productId]);
        }
    }

    /**
     * Create a new product
     */
    public function createProduct(array $data): bool
    {
        try {
            $productsModel = $this->getModel('ProductsModel');
            if (!$productsModel) {
                return false;
            }
            $result = $productsModel->create($data);
            return $result !== false;
        } catch (\Exception $e) {
            return $this->handleError($e, ['method' => 'createProduct']) !== null;
        }
    }

    /**
     * Update an existing product
     */
    public function updateProduct(int $productId, array $data): bool
    {
        try {
            $productsModel = $this->getModel('ProductsModel');
            if (!$productsModel) {
                return false;
            }
            $result = $productsModel->update($productId, $data);
            return $result !== false;
        } catch (\Exception $e) {
            return $this->handleError($e, ['method' => 'updateProduct', 'product_id' => $productId]) !== null;
        }
    }

    /**
     * Delete a product
     */
    public function deleteProduct(int $productId): bool
    {
        try {
            $productsModel = $this->getModel('ProductsModel');
            if (!$productsModel) {
                return false;
            }
            $result = $productsModel->delete($productId);
            return $result !== false;
        } catch (\Exception $e) {
            return $this->handleError($e, ['method' => 'deleteProduct', 'product_id' => $productId]) !== null;
        }
    }

    /**
     * Check if product has orders
     */
    public function checkProductHasOrders(int $productId): array
    {
        try {
            $ordersModel = $this->getModel('OrdersModel');
            if (!$ordersModel) {
                return ['has_orders' => false];
            }
            $orders = $ordersModel->findBy('product_id', $productId);
            return ['has_orders' => !empty($orders)];
        } catch (\Exception $e) {
            return $this->handleError($e, ['method' => 'checkProductHasOrders', 'product_id' => $productId]);
        }
    }

    /**
     * Get active categories for dropdowns (add, delete modals, etc.)
     */
    public function getActiveCategoriesForDropdown(): array
    {
        try {
            $categories = $this->callModelMethod('CategoriesModel', 'getActive', [], []);
            return [
                'categories' => $this->transformer->transformCategories($categories),
            ];
        } catch (\Exception $e) {
            return $this->handleError($e, ['method' => 'getActiveCategoriesForDropdown']);
        }
    }

    // ==================== USERS ====================

    public function getUsersData(int $page = 1, int $perPage = 10, array $filters = []): array
    {
        try {
            $usersModel = $this->getModel('UsersModel');
            if (!$usersModel) {
                return $this->getEmptyData();
            }

            $conditions = [];
            $bindings = [];

            if (!empty($filters['search'])) {
                $conditions[] = "(name LIKE ? OR email LIKE ? OR phone LIKE ?)";
                $searchTerm = "%{$filters['search']}%";
                $bindings = array_merge($bindings, [$searchTerm, $searchTerm, $searchTerm]);
            }

            if (!empty($filters['role'])) {
                $conditions[] = "role = ?";
                $bindings[] = $filters['role'];
            }

            if (!empty($filters['status'])) {
                $conditions[] = "status = ?";
                $bindings[] = $filters['status'];
            }

            $whereClause = !empty($conditions) ? 'WHERE ' . implode(' AND ', $conditions) : '';
            $countSql = "SELECT COUNT(*) as total FROM users {$whereClause}";
            $totalResult = $usersModel->db->query($countSql, $bindings);
            $total = $totalResult[0]['total'] ?? 0;

            $offset = ($page - 1) * $perPage;
            $usersSql = "SELECT * FROM users {$whereClause} ORDER BY created_at DESC LIMIT {$perPage} OFFSET {$offset}";
            $users = $usersModel->db->query($usersSql, $bindings);

            $transformedUsers = [];
            foreach ($users as $user) {
                $transformedUsers[] = $this->transformer->transformUser($user);
            }

            return [
                'users' => $transformedUsers,
                'pagination' => $this->calculatePagination($page, $perPage, $total),
                'filters' => $filters,
                'total' => $total,
            ];
        } catch (\Exception $e) {
            return $this->handleError($e, ['method' => 'getUsersData']);
        }
    }

    public function getUserDetailsData(int $userId): array
    {
        try {
            $user = $this->callModelMethod('UsersModel', 'findById', [$userId]);
            if (!$user) {
                return ['user' => null];
            }
            return [
                'user' => $this->transformer->transformUser($user),
            ];
        } catch (\Exception $e) {
            return $this->handleError($e, ['method' => 'getUserDetailsData', 'user_id' => $userId]);
        }
    }

    public function getUserAdditionalData(int $userId): array
    {
        try {
            $orders = $this->callModelMethod('OrdersModel', 'getByUser', [$userId], []);
            $affiliate = $this->callModelMethod('AffiliateModel', 'findBy', ['user_id', $userId]);

            return [
                'orders' => $this->transformer->transformOrders($orders),
                'affiliate' => $affiliate ? $this->transformer->transformAffiliate($affiliate) : null,
            ];
        } catch (\Exception $e) {
            return $this->handleError($e, ['method' => 'getUserAdditionalData', 'user_id' => $userId]);
        }
    }

    // ==================== CATEGORIES ====================

    public function getCategoriesData(int $page = 1, int $perPage = 10, array $filters = []): array
    {
        try {
            $categoriesModel = $this->getModel('CategoriesModel');
            $productsModel = $this->getModel('ProductsModel');

            if (!$categoriesModel) {
                return $this->getEmptyData();
            }

            $conditions = [];
            $bindings = [];

            if (!empty($filters['search'])) {
                $conditions[] = "(name LIKE ? OR description LIKE ? OR slug LIKE ?)";
                $searchTerm = "%{$filters['search']}%";
                $bindings = array_merge($bindings, [$searchTerm, $searchTerm, $searchTerm]);
            }

            if (!empty($filters['status'])) {
                $conditions[] = "status = ?";
                $bindings[] = $filters['status'];
            }

            $whereClause = !empty($conditions) ? 'WHERE ' . implode(' AND ', $conditions) : '';
            $countSql = "SELECT COUNT(*) as total FROM categories {$whereClause}";
            $totalResult = $categoriesModel->db->query($countSql, $bindings);
            $total = $totalResult[0]['total'] ?? 0;

            $offset = ($page - 1) * $perPage;
            $categoriesSql = "SELECT * FROM categories {$whereClause} ORDER BY created_at DESC LIMIT {$perPage} OFFSET {$offset}";
            $categories = $categoriesModel->db->query($categoriesSql, $bindings);

            // Get product counts
            if ($productsModel) {
                foreach ($categories as &$category) {
                    $productCountSql = "SELECT COUNT(*) as count FROM products WHERE category_id = ?";
                    $countResult = $productsModel->db->query($productCountSql, [$category['id']]);
                    $category['products_count'] = $countResult[0]['count'] ?? 0;
                }
            }

            $transformedCategories = [];
            foreach ($categories as $category) {
                $transformedCategories[] = $this->transformer->transformCategory($category);
            }

            return [
                'categories' => $transformedCategories,
                'pagination' => $this->calculatePagination($page, $perPage, $total),
                'filters' => $filters,
                'total' => $total,
            ];
        } catch (\Exception $e) {
            return $this->handleError($e, ['method' => 'getCategoriesData']);
        }
    }

    public function getCategoryDetailsData(int $categoryId): array
    {
        try {
            $category = $this->callModelMethod('CategoriesModel', 'find', [$categoryId]);
            if (!$category) {
                return ['category' => null, 'products' => []];
            }

            $products = $this->callModelMethod('ProductsModel', 'getByCategory', [$categoryId, 10], []);

            // Get product count
            $productsModel = $this->getModel('ProductsModel');
            if ($productsModel) {
                $productCountSql = "SELECT COUNT(*) as count FROM products WHERE category_id = ?";
                $countResult = $productsModel->db->query($productCountSql, [$categoryId]);
                $category['products_count'] = $countResult[0]['count'] ?? 0;
            }

            return [
                'category' => $this->transformer->transformCategory($category),
                'products' => $this->transformer->transformProducts($products),
            ];
        } catch (\Exception $e) {
            return $this->handleError($e, ['method' => 'getCategoryDetailsData', 'category_id' => $categoryId]);
        }
    }

    /**
     * Create a new category
     */
    public function createCategory(array $data): bool
    {
        try {
            $categoriesModel = $this->getModel('CategoriesModel');
            if (!$categoriesModel) {
                return false;
            }
            $result = $categoriesModel->create($data);
            return $result !== false;
        } catch (\Exception $e) {
            return $this->handleError($e, ['method' => 'createCategory']) !== null;
        }
    }

    /**
     * Update an existing category
     */
    public function updateCategory(int $categoryId, array $data): bool
    {
        try {
            $categoriesModel = $this->getModel('CategoriesModel');
            if (!$categoriesModel) {
                return false;
            }
            $result = $categoriesModel->update($categoryId, $data);
            return $result !== false;
        } catch (\Exception $e) {
            return $this->handleError($e, ['method' => 'updateCategory', 'category_id' => $categoryId]) !== null;
        }
    }

    /**
     * Delete a category
     */
    public function deleteCategory(int $categoryId): bool
    {
        try {
            $categoriesModel = $this->getModel('CategoriesModel');
            if (!$categoriesModel) {
                return false;
            }
            $result = $categoriesModel->delete($categoryId);
            return $result !== false;
        } catch (\Exception $e) {
            return $this->handleError($e, ['method' => 'deleteCategory', 'category_id' => $categoryId]) !== null;
        }
    }

    // ==================== NEWS ====================

    public function getNewsData(int $page = 1, int $perPage = 10, array $filters = []): array
    {
        try {
            $newsModel = $this->getModel('NewsModel');
            if (!$newsModel) {
                return $this->getEmptyData();
            }

            $conditions = [];
            $bindings = [];

            if (!empty($filters['search'])) {
                $conditions[] = "(n.title LIKE ? OR n.content LIKE ? OR n.excerpt LIKE ?)";
                $searchTerm = "%{$filters['search']}%";
                $bindings = array_merge($bindings, [$searchTerm, $searchTerm, $searchTerm]);
            }

            if (!empty($filters['status'])) {
                $conditions[] = "n.status = ?";
                $bindings[] = $filters['status'];
            }

            $whereClause = !empty($conditions) ? 'WHERE ' . implode(' AND ', $conditions) : '';
            $countSql = "SELECT COUNT(*) as total FROM news n {$whereClause}";
            $totalResult = $newsModel->db->query($countSql, $bindings);
            $total = $totalResult[0]['total'] ?? 0;

            $offset = ($page - 1) * $perPage;
            $newsSql = "
                SELECT n.*, u.name as author_name
                FROM news n
                LEFT JOIN users u ON n.author_id = u.id
                {$whereClause}
                ORDER BY n.created_at DESC 
                LIMIT {$perPage} OFFSET {$offset}
            ";
            $news = $newsModel->db->query($newsSql, $bindings);

            $transformedNews = [];
            foreach ($news as $article) {
                $transformedNews[] = $this->transformer->transformNews($article);
            }

            $stats = $this->getNewsStatistics($newsModel);

            return [
                'news' => $transformedNews,
                'pagination' => $this->calculatePagination($page, $perPage, $total),
                'filters' => $filters,
                'total' => $total,
                'stats' => $stats,
            ];
        } catch (\Exception $e) {
            return $this->handleError($e, ['method' => 'getNewsData']);
        }
    }

    public function getNewsDetailsData(int $newsId): array
    {
        try {
            $news = $this->callModelMethod('NewsModel', 'find', [$newsId]);
            if (!$news) {
                return ['news' => null, 'author' => null];
            }

            $author = null;
            if (!empty($news['author_id'])) {
                $author = $this->callModelMethod('UsersModel', 'getById', [$news['author_id']]);
            }

            return [
                'news' => $this->transformer->transformNews($news),
                'author' => $author ? $this->transformer->transformUser($author) : null,
            ];
        } catch (\Exception $e) {
            return $this->handleError($e, ['method' => 'getNewsDetailsData', 'news_id' => $newsId]);
        }
    }

    private function getNewsStatistics($newsModel): array
    {
        try {
            $stats = [
                'total' => 0, 'published' => 0, 'draft' => 0, 'archived' => 0,
                'today' => 0, 'this_month' => 0,
            ];

            $statusSql = "SELECT status, COUNT(*) as count FROM news GROUP BY status";
            $statusResults = $newsModel->db->query($statusSql);
            foreach ($statusResults as $result) {
                $stats[$result['status']] = (int)$result['count'];
                $stats['total'] += (int)$result['count'];
            }

            $todaySql = "SELECT COUNT(*) as count FROM news WHERE DATE(created_at) = CURDATE()";
            $todayResult = $newsModel->db->query($todaySql);
            $stats['today'] = $todayResult[0]['count'] ?? 0;

            $monthSql = "SELECT COUNT(*) as count FROM news WHERE YEAR(created_at) = YEAR(NOW()) AND MONTH(created_at) = MONTH(NOW())";
            $monthResult = $newsModel->db->query($monthSql);
            $stats['this_month'] = $monthResult[0]['count'] ?? 0;

            return $stats;
        } catch (\Exception $e) {
            return ['total' => 0, 'published' => 0, 'draft' => 0, 'archived' => 0, 'today' => 0, 'this_month' => 0];
        }
    }

    // ==================== ORDERS ====================

    public function getOrdersData(int $page = 1, int $perPage = 10, array $filters = []): array
    {
        try {
            $ordersModel = $this->getModel('OrdersModel');
            if (!$ordersModel) {
                return $this->getEmptyData();
            }

            $conditions = [];
            $bindings = [];

            if (!empty($filters['search'])) {
                $conditions[] = "(o.order_number LIKE ? OR u.name LIKE ? OR u.email LIKE ?)";
                $searchTerm = "%{$filters['search']}%";
                $bindings = array_merge($bindings, [$searchTerm, $searchTerm, $searchTerm]);
            }

            if (!empty($filters['status'])) {
                $conditions[] = "o.status = ?";
                $bindings[] = $filters['status'];
            }

            if (!empty($filters['payment_method'])) {
                $conditions[] = "o.payment_method = ?";
                $bindings[] = $filters['payment_method'];
            }

            if (!empty($filters['date_from'])) {
                $conditions[] = "DATE(o.created_at) >= ?";
                $bindings[] = $filters['date_from'];
            }

            if (!empty($filters['date_to'])) {
                $conditions[] = "DATE(o.created_at) <= ?";
                $bindings[] = $filters['date_to'];
            }

            $whereClause = !empty($conditions) ? 'WHERE ' . implode(' AND ', $conditions) : '';
            $countSql = "SELECT COUNT(*) as total FROM orders o LEFT JOIN users u ON o.user_id = u.id {$whereClause}";
            $totalResult = $ordersModel->db->query($countSql, $bindings);
            $total = $totalResult[0]['total'] ?? 0;

            $offset = ($page - 1) * $perPage;
            $ordersSql = "
                SELECT o.*, u.name as user_name, u.email as user_email,
                       COUNT(oi.id) as items_count
                FROM orders o
                LEFT JOIN users u ON o.user_id = u.id
                LEFT JOIN order_items oi ON o.id = oi.order_id
                {$whereClause}
                GROUP BY o.id 
                ORDER BY o.created_at DESC 
                LIMIT {$perPage} OFFSET {$offset}
            ";
            $orders = $ordersModel->db->query($ordersSql, $bindings);

            $transformedOrders = [];
            foreach ($orders as $order) {
                $transformedOrders[] = $this->transformer->transformOrder($order);
            }

            $stats = $this->getOrderStatistics($ordersModel);

            return [
                'orders' => $transformedOrders,
                'pagination' => $this->calculatePagination($page, $perPage, $total),
                'filters' => $filters,
                'total' => $total,
                'stats' => $stats,
            ];
        } catch (\Exception $e) {
            return $this->handleError($e, ['method' => 'getOrdersData']);
        }
    }

    public function getOrderDetailsData(int $orderId): array
    {
        try {
            $ordersModel = $this->getModel('OrdersModel');
            $productsModel = $this->getModel('ProductsModel');

            if (!$ordersModel) {
                return ['order' => null, 'user' => null, 'order_items' => []];
            }

            $order = $this->callModelMethod('OrdersModel', 'getById', [$orderId]);
            if (!$order) {
                return ['order' => null, 'user' => null, 'order_items' => []];
            }

            $user = null;
            if (!empty($order['user_id'])) {
                $user = $this->callModelMethod('UsersModel', 'getById', [$order['user_id']]);
            }

            $orderItems = $this->callModelMethod('OrdersModel', 'getOrderItems', [$orderId], []);

            // Get product details for each item
            if ($productsModel) {
                foreach ($orderItems as &$item) {
                    if (!empty($item['product_id'])) {
                        $product = $this->callModelMethod('ProductsModel', 'getById', [$item['product_id']]);
                        $item['product'] = $product ? $this->transformer->transformProduct($product) : null;
                    }
                }
            }

            return [
                'order' => $this->transformer->transformOrder($order),
                'user' => $user ? $this->transformer->transformUser($user) : null,
                'order_items' => $orderItems,
            ];
        } catch (\Exception $e) {
            return $this->handleError($e, ['method' => 'getOrderDetailsData', 'order_id' => $orderId]);
        }
    }

    private function getOrderStatistics($ordersModel): array
    {
        try {
            $stats = [
                'total' => 0, 'pending' => 0, 'processing' => 0, 'completed' => 0, 'cancelled' => 0,
                'today' => 0, 'this_month' => 0, 'total_revenue' => 0,
            ];

            $statusSql = "SELECT status, COUNT(*) as count FROM orders GROUP BY status";
            $statusResults = $ordersModel->db->query($statusSql);
            foreach ($statusResults as $result) {
                $stats[$result['status']] = (int)$result['count'];
                $stats['total'] += (int)$result['count'];
            }

            $todaySql = "SELECT COUNT(*) as count FROM orders WHERE DATE(created_at) = CURDATE()";
            $todayResult = $ordersModel->db->query($todaySql);
            $stats['today'] = $todayResult[0]['count'] ?? 0;

            $monthSql = "SELECT COUNT(*) as count FROM orders WHERE YEAR(created_at) = YEAR(NOW()) AND MONTH(created_at) = MONTH(NOW())";
            $monthResult = $ordersModel->db->query($monthSql);
            $stats['this_month'] = $monthResult[0]['count'] ?? 0;

            $revenueSql = "SELECT SUM(total) as revenue FROM orders WHERE status = 'completed'";
            $revenueResult = $ordersModel->db->query($revenueSql);
            $stats['total_revenue'] = $revenueResult[0]['revenue'] ?? 0;

            return $stats;
        } catch (\Exception $e) {
            return ['total' => 0, 'pending' => 0, 'processing' => 0, 'completed' => 0, 'cancelled' => 0, 'today' => 0, 'this_month' => 0, 'total_revenue' => 0];
        }
    }

    // ==================== SETTINGS ====================

    public function getSettingsData(int $page = 1, int $perPage = 10, array $filters = []): array
    {
        try {
            $settingsModel = $this->getModel('SettingsModel');
            if (!$settingsModel) {
                return $this->getEmptyData();
            }

            $conditions = [];
            $bindings = [];

            if (!empty($filters['search'])) {
                $conditions[] = "(`key` LIKE ? OR description LIKE ? OR value LIKE ?)";
                $searchTerm = "%{$filters['search']}%";
                $bindings = array_merge($bindings, [$searchTerm, $searchTerm, $searchTerm]);
            }

            if (!empty($filters['type'])) {
                $conditions[] = "type = ?";
                $bindings[] = $filters['type'];
            }

            $whereClause = !empty($conditions) ? 'WHERE ' . implode(' AND ', $conditions) : '';
            $countSql = "SELECT COUNT(*) as total FROM settings {$whereClause}";
            $totalResult = $settingsModel->db->query($countSql, $bindings);
            $total = $totalResult[0]['total'] ?? 0;

            $offset = ($page - 1) * $perPage;
            $settingsSql = "SELECT * FROM settings {$whereClause} ORDER BY `key` LIMIT {$perPage} OFFSET {$offset}";
            $settings = $settingsModel->db->query($settingsSql, $bindings);

            $typesSql = "SELECT DISTINCT type FROM settings ORDER BY type";
            $typesResult = $settingsModel->db->query($typesSql);
            $types = array_column($typesResult, 'type');

            return [
                'settings' => $settings,
                'types' => $types,
                'pagination' => $this->calculatePagination($page, $perPage, $total),
                'filters' => $filters,
                'total' => $total,
            ];
        } catch (\Exception $e) {
            return $this->handleError($e, ['method' => 'getSettingsData']);
        }
    }

    public function getSettingDetailsData(string $settingKey): array
    {
        try {
            $setting = $this->callModelMethod('SettingsModel', 'getByKey', [$settingKey]);
            if (!$setting) {
                return ['setting' => null];
            }
            return [
                'setting' => $this->transformer->transformSetting($setting),
            ];
        } catch (\Exception $e) {
            return $this->handleError($e, ['method' => 'getSettingDetailsData', 'key' => $settingKey]);
        }
    }

    // ==================== CONTACTS ====================

    public function getContactsData(int $page = 1, int $perPage = 10, array $filters = []): array
    {
        try {
            $contactsModel = $this->getModel('ContactsModel');
            if (!$contactsModel) {
                return $this->getEmptyData();
            }

            $conditions = [];
            $bindings = [];

            if (!empty($filters['search'])) {
                $conditions[] = "(name LIKE ? OR email LIKE ? OR subject LIKE ? OR message LIKE ?)";
                $searchTerm = "%{$filters['search']}%";
                $bindings = array_merge($bindings, [$searchTerm, $searchTerm, $searchTerm, $searchTerm]);
            }

            if (!empty($filters['status'])) {
                $conditions[] = "status = ?";
                $bindings[] = $filters['status'];
            }

            $whereClause = !empty($conditions) ? 'WHERE ' . implode(' AND ', $conditions) : '';
            $countSql = "SELECT COUNT(*) as total FROM contacts {$whereClause}";
            $totalResult = $contactsModel->db->query($countSql, $bindings);
            $total = $totalResult[0]['total'] ?? 0;

            $offset = ($page - 1) * $perPage;
            $contactsSql = "SELECT * FROM contacts {$whereClause} ORDER BY created_at DESC LIMIT {$perPage} OFFSET {$offset}";
            $contacts = $contactsModel->db->query($contactsSql, $bindings);

            $stats = $this->getContactStatistics($contactsModel);

            return [
                'contacts' => $contacts,
                'pagination' => $this->calculatePagination($page, $perPage, $total),
                'filters' => $filters,
                'total' => $total,
                'stats' => $stats,
            ];
        } catch (\Exception $e) {
            return $this->handleError($e, ['method' => 'getContactsData']);
        }
    }

    public function getContactDetailsData(int $contactId): array
    {
        try {
            $contact = $this->callModelMethod('ContactsModel', 'find', [$contactId]);
            if (!$contact) {
                return ['contact' => null];
            }
            return [
                'contact' => $this->transformer->transformContact($contact),
            ];
        } catch (\Exception $e) {
            return $this->handleError($e, ['method' => 'getContactDetailsData', 'contact_id' => $contactId]);
        }
    }

    /**
     * Update contact status
     */
    public function updateContactStatus(int $contactId, string $status, ?string $adminNotes = null): bool
    {
        try {
            $contactsModel = $this->getModel('ContactsModel');
            if (!$contactsModel) {
                return false;
            }
            $data = ['status' => $status];
            if ($adminNotes !== null) {
                $data['admin_notes'] = $adminNotes;
            }
            $result = $contactsModel->update($contactId, $data);
            return $result !== false;
        } catch (\Exception $e) {
            return $this->handleError($e, ['method' => 'updateContactStatus', 'contact_id' => $contactId]) !== null;
        }
    }

    /**
     * Delete a contact
     */
    public function deleteContact(int $contactId): bool
    {
        try {
            $contactsModel = $this->getModel('ContactsModel');
            if (!$contactsModel) {
                return false;
            }
            $result = $contactsModel->delete($contactId);
            return $result !== false;
        } catch (\Exception $e) {
            return $this->handleError($e, ['method' => 'deleteContact', 'contact_id' => $contactId]) !== null;
        }
    }

    private function getContactStatistics($contactsModel): array
    {
        try {
            $stats = [
                'total' => 0, 'new' => 0, 'read' => 0, 'replied' => 0,
                'today' => 0, 'this_month' => 0,
            ];

            $statusSql = "SELECT status, COUNT(*) as count FROM contacts GROUP BY status";
            $statusResults = $contactsModel->db->query($statusSql);
            foreach ($statusResults as $result) {
                $stats[$result['status']] = (int)$result['count'];
                $stats['total'] += (int)$result['count'];
            }

            $todaySql = "SELECT COUNT(*) as count FROM contacts WHERE DATE(created_at) = CURDATE()";
            $todayResult = $contactsModel->db->query($todaySql);
            $stats['today'] = $todayResult[0]['count'] ?? 0;

            $monthSql = "SELECT COUNT(*) as count FROM contacts WHERE YEAR(created_at) = YEAR(NOW()) AND MONTH(created_at) = MONTH(NOW())";
            $monthResult = $contactsModel->db->query($monthSql);
            $stats['this_month'] = $monthResult[0]['count'] ?? 0;

            return $stats;
        } catch (\Exception $e) {
            return ['total' => 0, 'new' => 0, 'read' => 0, 'replied' => 0, 'today' => 0, 'this_month' => 0];
        }
    }

    // ==================== AFFILIATES ====================

    public function getAffiliatesData(int $page = 1, int $perPage = 10, array $filters = []): array
    {
        try {
            $affiliateModel = $this->getModel('AffiliateModel');
            if (!$affiliateModel) {
                return $this->getEmptyData();
            }

            $conditions = [];
            $bindings = [];

            if (!empty($filters['search'])) {
                $conditions[] = "(u.name LIKE ? OR u.email LIKE ? OR a.referral_code LIKE ?)";
                $searchTerm = "%{$filters['search']}%";
                $bindings = array_merge($bindings, [$searchTerm, $searchTerm, $searchTerm]);
            }

            if (!empty($filters['status'])) {
                $conditions[] = "a.status = ?";
                $bindings[] = $filters['status'];
            }

            $whereClause = !empty($conditions) ? 'WHERE ' . implode(' AND ', $conditions) : '';
            $countSql = "SELECT COUNT(*) as total FROM affiliates a LEFT JOIN users u ON a.user_id = u.id {$whereClause}";
            $totalResult = $affiliateModel->db->query($countSql, $bindings);
            $total = $totalResult[0]['total'] ?? 0;

            $offset = ($page - 1) * $perPage;
            $affiliatesSql = "
                SELECT a.*, u.name as user_name, u.email as user_email, u.phone as user_phone
                FROM affiliates a
                LEFT JOIN users u ON a.user_id = u.id
                {$whereClause}
                ORDER BY a.created_at DESC 
                LIMIT {$perPage} OFFSET {$offset}
            ";
            $affiliates = $affiliateModel->db->query($affiliatesSql, $bindings);

            $transformedAffiliates = [];
            foreach ($affiliates as $affiliate) {
                $transformedAffiliates[] = $this->transformer->transformAffiliate($affiliate);
            }

            $stats = $this->getAffiliateStatistics($affiliateModel);

            return [
                'affiliates' => $transformedAffiliates,
                'pagination' => $this->calculatePagination($page, $perPage, $total),
                'filters' => $filters,
                'total' => $total,
                'stats' => $stats,
            ];
        } catch (\Exception $e) {
            return $this->handleError($e, ['method' => 'getAffiliatesData']);
        }
    }

    public function getAffiliateDetailsData(int $affiliateId): array
    {
        try {
            $affiliateModel = $this->getModel('AffiliateModel');
            $ordersModel = $this->getModel('OrdersModel');

            if (!$affiliateModel) {
                return ['affiliate' => null, 'orders' => [], 'performance_data' => ['labels' => [], 'sales' => [], 'commission' => []]];
            }

            $affiliateSql = "
                SELECT a.*, u.name as user_name, u.email as user_email, u.phone as user_phone, u.address as user_address
                FROM affiliates a
                LEFT JOIN users u ON a.user_id = u.id
                WHERE a.id = ?
            ";
            $result = $affiliateModel->db->query($affiliateSql, [$affiliateId]);
            $affiliate = $result ? $result[0] : null;

            if (!$affiliate) {
                return ['affiliate' => null, 'orders' => [], 'performance_data' => ['labels' => [], 'sales' => [], 'commission' => []]];
            }

            $orders = [];
            if ($ordersModel) {
                $ordersSql = "SELECT * FROM orders WHERE affiliate_id = ? ORDER BY created_at DESC LIMIT 10";
                $orders = $ordersModel->db->query($ordersSql, [$affiliateId]);
            }

            $performanceData = [
                'labels' => ['T1', 'T2', 'T3', 'T4', 'T5', 'T6'],
                'sales' => [5000000, 7500000, 12000000, 8500000, 15000000, 18000000],
                'commission' => [500000, 750000, 1200000, 850000, 1500000, 1800000],
            ];

            return [
                'affiliate' => $this->transformer->transformAffiliate($affiliate),
                'orders' => $this->transformer->transformOrders($orders),
                'performance_data' => $performanceData,
            ];
        } catch (\Exception $e) {
            return $this->handleError($e, ['method' => 'getAffiliateDetailsData', 'affiliate_id' => $affiliateId]);
        }
    }

    /**
     * Create a new affiliate
     */
    public function createAffiliate(array $data): bool
    {
        try {
            $affiliateModel = $this->getModel('AffiliateModel');
            if (!$affiliateModel) {
                return false;
            }
            $result = $affiliateModel->create($data);
            return $result !== false;
        } catch (\Exception $e) {
            return $this->handleError($e, ['method' => 'createAffiliate']) !== null;
        }
    }

    /**
     * Update an existing affiliate
     */
    public function updateAffiliate(int $affiliateId, array $data): bool
    {
        try {
            $affiliateModel = $this->getModel('AffiliateModel');
            if (!$affiliateModel) {
                return false;
            }
            $result = $affiliateModel->update($affiliateId, $data);
            return $result !== false;
        } catch (\Exception $e) {
            return $this->handleError($e, ['method' => 'updateAffiliate', 'affiliate_id' => $affiliateId]) !== null;
        }
    }

    /**
     * Delete an affiliate
     */
    public function deleteAffiliate(int $affiliateId): bool
    {
        try {
            $affiliateModel = $this->getModel('AffiliateModel');
            if (!$affiliateModel) {
                return false;
            }
            $result = $affiliateModel->delete($affiliateId);
            return $result !== false;
        } catch (\Exception $e) {
            return $this->handleError($e, ['method' => 'deleteAffiliate', 'affiliate_id' => $affiliateId]) !== null;
        }
    }

    private function getAffiliateStatistics($affiliateModel): array
    {
        try {
            $stats = [
                'total' => 0, 'active' => 0, 'inactive' => 0, 'pending' => 0,
                'total_sales' => 0, 'total_commission' => 0,
            ];

            $statusSql = "SELECT status, COUNT(*) as count FROM affiliates GROUP BY status";
            $statusResults = $affiliateModel->db->query($statusSql);
            foreach ($statusResults as $result) {
                $stats[$result['status']] = (int)$result['count'];
                $stats['total'] += (int)$result['count'];
            }

            $salesSql = "SELECT SUM(total_sales) as sales, SUM(total_commission) as commission FROM affiliates";
            $salesResult = $affiliateModel->db->query($salesSql);
            if ($salesResult && $salesResult[0]) {
                $stats['total_sales'] = $salesResult[0]['sales'] ?? 0;
                $stats['total_commission'] = $salesResult[0]['commission'] ?? 0;
            }

            return $stats;
        } catch (\Exception $e) {
            return ['total' => 0, 'active' => 0, 'inactive' => 0, 'pending' => 0, 'total_sales' => 0, 'total_commission' => 0];
        }
    }

    /**
     * Check if a referral code already exists
     */
    public function checkReferralCodeExists(string $referralCode, ?int $excludeAffiliateId = null): bool
    {
        try {
            $affiliateModel = $this->getModel('AffiliateModel');
            if (!$affiliateModel) {
                return false;
            }
            
            $sql = "SELECT COUNT(*) as count FROM affiliates WHERE referral_code = ?";
            $bindings = [strtoupper($referralCode)];
            
            if ($excludeAffiliateId !== null) {
                $sql .= " AND id != ?";
                $bindings[] = $excludeAffiliateId;
            }
            
            $result = $affiliateModel->db->query($sql, $bindings);
            return ($result[0]['count'] ?? 0) > 0;
        } catch (\Exception $e) {
            return $this->handleError($e, ['method' => 'checkReferralCodeExists', 'referral_code' => $referralCode]) !== null;
        }
    }

    /**
     * Get available users for affiliate (users without affiliate accounts)
     */
    public function getAvailableUsersForAffiliate(): array
    {
        try {
            $usersModel = $this->getModel('UsersModel');
            if (!$usersModel) {
                return ['users' => []];
            }

            $sql = "
                SELECT u.* FROM users u
                LEFT JOIN affiliates a ON u.id = a.user_id
                WHERE a.id IS NULL AND u.role IN ('user', 'agent')
                ORDER BY u.name
            ";
            $users = $usersModel->db->query($sql);

            return [
                'users' => $this->transformer->transformUsers($users ?: []),
            ];
        } catch (\Exception $e) {
            return $this->handleError($e, ['method' => 'getAvailableUsersForAffiliate']);
        }
    }

    // ==================== EVENTS ====================

    public function getEventsData(int $page = 1, int $perPage = 10, array $filters = []): array
    {
        try {
            $eventsModel = $this->getModel('EventsModel');
            if (!$eventsModel) {
                return $this->getEmptyData();
            }

            $conditions = [];
            $bindings = [];

            if (!empty($filters['search'])) {
                $conditions[] = "(title LIKE ? OR description LIKE ? OR location LIKE ?)";
                $searchTerm = "%{$filters['search']}%";
                $bindings = array_merge($bindings, [$searchTerm, $searchTerm, $searchTerm]);
            }

            if (!empty($filters['status'])) {
                $conditions[] = "status = ?";
                $bindings[] = $filters['status'];
            }

            $whereClause = !empty($conditions) ? 'WHERE ' . implode(' AND ', $conditions) : '';
            $countSql = "SELECT COUNT(*) as total FROM events {$whereClause}";
            $totalResult = $eventsModel->db->query($countSql, $bindings);
            $total = $totalResult[0]['total'] ?? 0;

            $offset = ($page - 1) * $perPage;
            $eventsSql = "SELECT * FROM events {$whereClause} ORDER BY start_date DESC LIMIT {$perPage} OFFSET {$offset}";
            $events = $eventsModel->db->query($eventsSql, $bindings);

            $transformedEvents = [];
            foreach ($events as $event) {
                $transformedEvents[] = $this->transformer->transformEvent($event);
            }

            $stats = $this->getEventStatistics($eventsModel);

            return [
                'events' => $transformedEvents,
                'pagination' => $this->calculatePagination($page, $perPage, $total),
                'filters' => $filters,
                'total' => $total,
                'stats' => $stats,
            ];
        } catch (\Exception $e) {
            return $this->handleError($e, ['method' => 'getEventsData']);
        }
    }

    public function getEventDetailsData(int $eventId): array
    {
        try {
            $event = $this->callModelMethod('EventsModel', 'find', [$eventId]);
            if (!$event) {
                return ['event' => null];
            }
            return [
                'event' => $this->transformer->transformEvent($event),
            ];
        } catch (\Exception $e) {
            return $this->handleError($e, ['method' => 'getEventDetailsData', 'event_id' => $eventId]);
        }
    }

    private function getEventStatistics($eventsModel): array
    {
        try {
            $stats = [
                'total' => 0, 'upcoming' => 0, 'ongoing' => 0, 'completed' => 0, 'cancelled' => 0,
                'this_month' => 0, 'total_participants' => 0,
            ];

            $statusSql = "SELECT status, COUNT(*) as count FROM events GROUP BY status";
            $statusResults = $eventsModel->db->query($statusSql);
            foreach ($statusResults as $result) {
                $stats[$result['status']] = (int)$result['count'];
                $stats['total'] += (int)$result['count'];
            }

            $monthSql = "SELECT COUNT(*) as count FROM events WHERE YEAR(start_date) = YEAR(NOW()) AND MONTH(start_date) = MONTH(NOW())";
            $monthResult = $eventsModel->db->query($monthSql);
            $stats['this_month'] = $monthResult[0]['count'] ?? 0;

            $participantsSql = "SELECT SUM(current_participants) as total FROM events";
            $participantsResult = $eventsModel->db->query($participantsSql);
            $stats['total_participants'] = $participantsResult[0]['total'] ?? 0;

            return $stats;
        } catch (\Exception $e) {
            return ['total' => 0, 'upcoming' => 0, 'ongoing' => 0, 'completed' => 0, 'cancelled' => 0, 'this_month' => 0, 'total_participants' => 0];
        }
    }

    // ==================== REVENUE ====================

    public function getRevenueData(array $filters = []): array
    {
        try {
            $ordersModel = $this->getModel('OrdersModel');
            $productsModel = $this->getModel('ProductsModel');
            $usersModel = $this->getModel('UsersModel');
            $affiliateModel = $this->getModel('AffiliateModel');

            if (!$ordersModel) {
                return $this->getEmptyData();
            }

            $dateFrom = $filters['date_from'] ?? date('Y-m-01');
            $dateTo = $filters['date_to'] ?? date('Y-m-d');

            $conditions = ["DATE(created_at) BETWEEN ? AND ?"];
            $bindings = [$dateFrom, $dateTo];

            if (!empty($filters['product_id'])) {
                $conditions[] = "product_id = ?";
                $bindings[] = $filters['product_id'];
            }

            if (!empty($filters['user_id'])) {
                $conditions[] = "user_id = ?";
                $bindings[] = $filters['user_id'];
            }

            if (!empty($filters['affiliate_id'])) {
                $conditions[] = "affiliate_id = ?";
                $bindings[] = $filters['affiliate_id'];
            }

            $whereClause = implode(' AND ', $conditions);
            $ordersSql = "SELECT * FROM orders WHERE {$whereClause} ORDER BY created_at DESC";
            $orders = $ordersModel->db->query($ordersSql, $bindings);

            // Build lookups
            $products = $productsModel ? $this->callModelMethod('ProductsModel', 'all', [], []) : [];
            $users = $usersModel ? $this->callModelMethod('UsersModel', 'all', [], []) : [];
            $affiliates = $affiliateModel ? $this->callModelMethod('AffiliateModel', 'all', [], []) : [];

            $productLookup = [];
            foreach ($products as $product) {
                $productLookup[$product['id']] = $product;
            }

            $userLookup = [];
            foreach ($users as $user) {
                $userLookup[$user['id']] = $user;
            }

            $affiliateLookup = [];
            foreach ($affiliates as $affiliate) {
                $affiliateLookup[$affiliate['id']] = $affiliate;
            }

            $stats = $this->calculateRevenueStatistics($orders);
            $revenueByProduct = $this->calculateRevenueByProduct($orders, $productLookup);
            $revenueByDate = $this->calculateRevenueByDate($orders, $dateFrom, $dateTo);

            return [
                'orders' => $orders,
                'products' => $productLookup,
                'users' => $userLookup,
                'affiliates' => $affiliateLookup,
                'stats' => $stats,
                'revenue_by_product' => $revenueByProduct,
                'revenue_by_date' => $revenueByDate,
                'filters' => $filters,
            ];
        } catch (\Exception $e) {
            return $this->handleError($e, ['method' => 'getRevenueData']);
        }
    }

    private function calculateRevenueStatistics($orders): array
    {
        $stats = [
            'total_revenue' => 0, 'completed_revenue' => 0, 'pending_revenue' => 0,
            'total_orders' => count($orders), 'completed_orders' => 0, 'pending_orders' => 0,
            'processing_orders' => 0, 'cancelled_orders' => 0,
            'revenue_by_status' => ['completed' => 0, 'processing' => 0, 'pending' => 0, 'cancelled' => 0],
        ];

        foreach ($orders as $order) {
            $total = $order['total'] ?? 0;
            $status = $order['status'] ?? 'pending';
            $stats['total_revenue'] += $total;
            if (isset($stats['revenue_by_status'][$status])) {
                $stats['revenue_by_status'][$status] += $total;
            }

            switch ($status) {
                case 'completed':
                    $stats['completed_orders']++;
                    $stats['completed_revenue'] += $total;
                    break;
                case 'processing':
                    $stats['processing_orders']++;
                    break;
                case 'pending':
                    $stats['pending_orders']++;
                    $stats['pending_revenue'] += $total;
                    break;
                case 'cancelled':
                    $stats['cancelled_orders']++;
                    break;
            }
        }

        return $stats;
    }

    private function calculateRevenueByProduct($orders, $productLookup): array
    {
        $revenueByProduct = [];

        foreach ($orders as $order) {
            $productId = $order['product_id'] ?? 0;
            if (!isset($revenueByProduct[$productId])) {
                $revenueByProduct[$productId] = [
                    'product' => $productLookup[$productId] ?? null,
                    'revenue' => 0,
                    'orders' => 0,
                ];
            }
            $revenueByProduct[$productId]['revenue'] += $order['total'] ?? 0;
            $revenueByProduct[$productId]['orders']++;
        }

        uasort($revenueByProduct, function ($a, $b) {
            return $b['revenue'] - $a['revenue'];
        });

        return $revenueByProduct;
    }

    private function calculateRevenueByDate($orders, $dateFrom, $dateTo): array
    {
        $revenueByDate = [];

        $currentDate = strtotime($dateFrom);
        $endDate = strtotime($dateTo);

        while ($currentDate <= $endDate) {
            $dateStr = date('Y-m-d', $currentDate);
            $revenueByDate[$dateStr] = 0;
            $currentDate = strtotime('+1 day', $currentDate);
        }

        foreach ($orders as $order) {
            $date = date('Y-m-d', strtotime($order['created_at'] ?? ''));
            if (isset($revenueByDate[$date])) {
                $revenueByDate[$date] += $order['total'] ?? 0;
            }
        }

        return $revenueByDate;
    }

    // ==================== HELPERS ====================

    /**
     * Calculate pagination info (same logic as ViewDataService)
     */
    protected function calculatePagination(int $currentPage, int $perPage, int $total): array
    {
        return [
            'current_page' => $currentPage,
            'per_page' => $perPage,
            'total' => $total,
            'last_page' => $perPage > 0 ? (int)ceil($total / $perPage) : 1,
            'from' => ($currentPage - 1) * $perPage + 1,
            'to' => min($currentPage * $perPage, $total),
        ];
    }

    /**
     * Handle empty state data for admin views
     */
    public function handleEmptyState(string $type): array
    {
        $emptyStates = [
            'admin_dashboard' => [
                'product_stats' => ['total' => 0],
                'user_stats' => ['total' => 0],
                'recent_products' => [],
                'recent_users' => [],
                'stats' => [],
                'trends' => [],
                'alerts' => [],
                'top_products' => [],
                'recent_activities' => [],
                'charts_data' => [],
                'message' => 'Đang tải dữ liệu dashboard',
            ],
        ];

        return $emptyStates[$type] ?? ['message' => 'Không có dữ liệu'];
    }
}
