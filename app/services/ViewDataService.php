<?php
/**
 * View Data Service
 * Centralized service để chuẩn bị data cho views
 */

require_once __DIR__ . '/../models/ProductsModel.php';
require_once __DIR__ . '/../models/CategoriesModel.php';
require_once __DIR__ . '/../models/NewsModel.php';
require_once __DIR__ . '/../models/UsersModel.php';
require_once __DIR__ . '/../models/OrdersModel.php';
require_once __DIR__ . '/../models/AffiliateModel.php';
require_once __DIR__ . '/../models/ContactsModel.php';
require_once __DIR__ . '/../models/SettingsModel.php';
require_once __DIR__ . '/DataTransformer.php';

class ViewDataService {
    private $productsModel;
    private $categoriesModel;
    private $newsModel;
    private $usersModel;
    private $ordersModel;
    private $affiliateModel;
    private $contactsModel;
    private $settingsModel;
    private $transformer;
    
    public function __construct() {
        echo "<!-- Debug: Initializing Models -->";
        $this->productsModel = new ProductsModel();
        $this->categoriesModel = new CategoriesModel();
        $this->newsModel = new NewsModel();
        $this->usersModel = new UsersModel();
        $this->ordersModel = new OrdersModel();
        $this->affiliateModel = new AffiliateModel();
        $this->contactsModel = new ContactsModel();
        $this->settingsModel = new SettingsModel();
        $this->transformer = new DataTransformer();
        echo "<!-- Debug: Models Initialized -->";
    }
    
    public function getHomePageData(): array {
        try {
            $data = [];
            
            // Featured products
            $featuredProducts = $this->getDataWithRetry(
                [$this->productsModel, 'getFeatured'], 
                [8]
            );
            $data['featured_products'] = $this->transformer->transformProducts($featuredProducts);
            
            // Latest products
            $latestProducts = $this->getDataWithRetry(
                [$this->productsModel, 'getWithCategory'], 
                [8]
            );
            $data['latest_products'] = $this->transformer->transformProducts($latestProducts);
            
            // Featured categories
            $featuredCategories = $this->getDataWithRetry(
                [$this->categoriesModel, 'getFeaturedCategories'], 
                [9]
            );
            $data['featured_categories'] = $this->transformer->transformCategories($featuredCategories);
            
            // Latest news
            $latestNews = $this->getDataWithRetry(
                [$this->newsModel, 'getLatestForHome'], 
                [8]
            );
            $data['latest_news'] = $this->transformer->transformNews($latestNews);
            
            return $data;
            
        } catch (Exception $e) {
            error_log("ViewDataService::getHomePageData error: " . $e->getMessage());
            return $this->handleEmptyState('home');
        }
    }
    
    /**
     * Chuẩn bị data cho product listings
     */
    public function getProductListingData($filters = []): array {
        try {
            $page = $filters['page'] ?? 1;
            $limit = $filters['limit'] ?? 12;
            $categoryId = $filters['category_id'] ?? null;
            $orderBy = $filters['order_by'] ?? 'post_date';
            $search = $filters['search'] ?? '';
            
            // Get products based on filters
            if ($categoryId) {
                $products = $this->getDataWithRetry(
                    [$this->productsModel, 'getByCategory'], 
                    [$categoryId, $limit * 10] // Get more for sorting
                );
            } else {
                $products = $this->getDataWithRetry(
                    [$this->productsModel, 'getWithCategory'], 
                    [$limit * 10] // Get more for sorting
                );
            }
            
            // Apply search filter
            if ($search) {
                $products = array_filter($products, function($product) use ($search) {
                    return stripos($product['name'], $search) !== false || 
                           stripos($product['description'] ?? '', $search) !== false;
                });
            }
            
            // Apply sorting
            $products = $this->sortProducts($products, $orderBy);
            
            // Calculate total before pagination
            $total = count($products);
            
            // Apply pagination
            $offset = ($page - 1) * $limit;
            $paginatedProducts = array_slice($products, $offset, $limit);
            
            return [
                'products' => $this->transformer->transformProducts($paginatedProducts),
                'pagination' => $this->calculatePagination($page, $limit, $total),
                'filters' => $filters
            ];
            
        } catch (Exception $e) {
            error_log("ViewDataService::getProductListingData error: " . $e->getMessage());
            return $this->handleEmptyState('products');
        }
    }
    
    /**
     * Chuẩn bị data cho category pages
     */
    public function getCategoryPageData($categoryId): array {
        try {
            // Get category info
            $category = $this->getDataWithRetry(
                [$this->categoriesModel, 'find'], 
                [$categoryId]
            );
            
            if (!$category) {
                return $this->handleEmptyState('category');
            }
            
            // Get products in category
            $products = $this->getDataWithRetry(
                [$this->productsModel, 'getByCategory'], 
                [$categoryId, 12]
            );
            
            return [
                'category' => $this->transformer->transformCategory($category),
                'products' => $this->transformer->transformProducts($products)
            ];
            
        } catch (Exception $e) {
            error_log("ViewDataService::getCategoryPageData error: " . $e->getMessage());
            return $this->handleEmptyState('category');
        }
    }
    
    /**
     * Chuẩn bị data cho product details page
     */
    public function getProductDetailsData($productId): array {
        try {
            // Get product info
            $product = $this->getDataWithRetry(
                [$this->productsModel, 'getById'], 
                [$productId]
            );
            
            if (!$product) {
                return $this->handleEmptyState('product_details');
            }
            
            // Get category info
            $category = null;
            if (!empty($product['category_id'])) {
                $category = $this->getDataWithRetry(
                    [$this->categoriesModel, 'find'], 
                    [$product['category_id']]
                );
            }
            
            // Get related products (same category, excluding current product)
            $relatedProducts = [];
            if (!empty($product['category_id'])) {
                $allCategoryProducts = $this->getDataWithRetry(
                    [$this->productsModel, 'getByCategory'], 
                    [$product['category_id'], 8]
                );
                
                // Filter out current product
                $relatedProducts = array_filter($allCategoryProducts, function($p) use ($productId) {
                    return $p['id'] != $productId;
                });
                
                // Limit to 4 related products
                $relatedProducts = array_slice($relatedProducts, 0, 4);
            }
            
            return [
                'product' => $this->transformer->transformProduct($product),
                'category' => $category ? $this->transformer->transformCategory($category) : null,
                'related_products' => $this->transformer->transformProducts($relatedProducts)
            ];
            
        } catch (Exception $e) {
            error_log("ViewDataService::getProductDetailsData error: " . $e->getMessage());
            return $this->handleEmptyState('product_details');
        }
    }
    
    /**
     * Chuẩn bị data cho admin dashboard
     */
    public function getAdminDashboardData(): array {
        try {
            $data = [];
            
            // Statistics
            $data['product_stats'] = $this->getDataWithRetry(
                [$this->productsModel, 'getStats'], 
                []
            );
            
            $data['user_stats'] = $this->getDataWithRetry(
                [$this->usersModel, 'getStats'], 
                []
            );
            
            // Recent data
            $data['recent_products'] = $this->transformer->transformProducts(
                $this->getDataWithRetry([$this->productsModel, 'getWithCategory'], [5])
            );
            
            $data['recent_users'] = $this->transformer->transformUsers(
                $this->getDataWithRetry([$this->usersModel, 'all'], ['*'])
            );
            
            return $data;
            
        } catch (Exception $e) {
            error_log("ViewDataService::getAdminDashboardData error: " . $e->getMessage());
            return $this->handleEmptyState('admin_dashboard');
        }
    }
    
    /**
     * Chuẩn bị data cho user dashboard
     */
    public function getUserDashboardData($userId): array {
        try {
            // Get user info with orders count
            $user = $this->getDataWithRetry(
                [$this->usersModel, 'getUserWithOrdersCount'], 
                [$userId]
            );
            
            if (!$user) {
                return $this->handleEmptyState('user_dashboard');
            }
            
            // Get recent orders
            $recentOrders = $this->getDataWithRetry(
                [$this->ordersModel, 'getByUser'], 
                [$userId, 5]
            );
            
            return [
                'user' => $this->transformer->transformUser($user),
                'recent_orders' => $this->transformer->transformOrders($recentOrders)
            ];
            
        } catch (Exception $e) {
            error_log("ViewDataService::getUserDashboardData error: " . $e->getMessage());
            return $this->handleEmptyState('user_dashboard');
        }
    }
    
    /**
     * Chuẩn bị data cho affiliate dashboard
     */
    public function getAffiliateDashboardData($affiliateId): array {
        try {
            // Get affiliate info
            $affiliate = $this->getDataWithRetry(
                [$this->affiliateModel, 'find'], 
                [$affiliateId]
            );
            
            if (!$affiliate) {
                return $this->handleEmptyState('affiliate_dashboard');
            }
            
            // Get commission data
            $commissions = $this->getDataWithRetry(
                [$this->affiliateModel, 'getCommissions'], 
                [$affiliateId]
            );
            
            return [
                'affiliate' => $this->transformer->transformAffiliate($affiliate),
                'commissions' => $this->transformer->transformCommissions($commissions)
            ];
            
        } catch (Exception $e) {
            error_log("ViewDataService::getAffiliateDashboardData error: " . $e->getMessage());
            return $this->handleEmptyState('affiliate_dashboard');
        }
    }

    /**
     * Chuẩn bị data cho trang Liên hệ
     */
    public function getContactPageData(): array {
        try {
            $contactSettings = $this->getDataWithRetry(
                [$this->settingsModel, 'getContactSettings'],
                []
            );

            // Default fallback values
            $defaultContact = [
                'office_address' => 'Tầng 12, Tòa nhà ABC, 123 Đường Nguyễn Huệ<br>Quận 1, TP. Hồ Chí Minh',
                'phone' => '(+84) 28 - 3825 - 6789',
                'hotline' => '1900 - 1234',
                'email' => 'contact@thuonglo.com',
                'working_hours_weekday' => 'Thứ 2 - Thứ 6: 08:00 - 18:00',
                'working_hours_weekend' => 'Thứ 7 & Chủ nhật: 09:00 - 17:00'
            ];

            return [
                'contact' => array_merge($defaultContact, $contactSettings ?: [])
            ];

        } catch (Exception $e) {
            error_log("ViewDataService::getContactPageData error: " . $e->getMessage());
            return $this->handleEmptyState('contact');
        }
    }
    
    /**
     * Get categories with product counts for filtering
     */
    public function getCategoriesWithProductCounts(): array {
        try {
            $categories = $this->getDataWithRetry(
                [$this->categoriesModel, 'getWithProductCounts'], 
                []
            );
            
            return [
                'categories' => $this->transformer->transformCategories($categories)
            ];
            
        } catch (Exception $e) {
            error_log("ViewDataService::getCategoriesWithProductCounts error: " . $e->getMessage());
            return ['categories' => []];
        }
    }
    
    /**
     * Get active categories for dropdowns
     */
    public function getActiveCategoriesForDropdown(): array {
        try {
            $categories = $this->getDataWithRetry(
                [$this->categoriesModel, 'getActive'], 
                []
            );
            
            return [
                'categories' => $this->transformer->transformCategories($categories)
            ];
            
        } catch (Exception $e) {
            error_log("ViewDataService::getActiveCategoriesForDropdown error: " . $e->getMessage());
            return ['categories' => []];
        }
    }
    
    /**
     * Get admin affiliates page data with pagination and filtering
     */
    public function getAdminAffiliatesData($page = 1, $perPage = 10, $filters = []): array {
        try {
            $affiliateModel = new AffiliateModel();
            $usersModel = new UsersModel();
            
            // Build search conditions
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
            
            // Get total count for pagination
            $whereClause = !empty($conditions) ? 'WHERE ' . implode(' AND ', $conditions) : '';
            $countSql = "SELECT COUNT(*) as total FROM affiliates a LEFT JOIN users u ON a.user_id = u.id {$whereClause}";
            $totalResult = $affiliateModel->db->query($countSql, $bindings);
            $total = $totalResult[0]['total'] ?? 0;
            
            // Get affiliates with pagination
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
            
            // Transform affiliates data
            $transformedAffiliates = [];
            foreach ($affiliates as $affiliate) {
                $transformedAffiliates[] = $this->transformer->transformAffiliate($affiliate);
            }
            
            // Get affiliate statistics
            $stats = $this->getAffiliateStatistics();
            
            return [
                'affiliates' => $transformedAffiliates,
                'pagination' => $this->calculatePagination($page, $perPage, $total),
                'filters' => $filters,
                'total' => $total,
                'stats' => $stats
            ];
            
        } catch (Exception $e) {
            error_log("ViewDataService::getAdminAffiliatesData error: " . $e->getMessage());
            return [
                'affiliates' => [],
                'pagination' => ['current_page' => 1, 'total' => 0],
                'filters' => $filters,
                'total' => 0,
                'stats' => ['total' => 0, 'active' => 0, 'inactive' => 0, 'pending' => 0]
            ];
        }
    }
    
    /**
     * Get admin affiliate details data
     */
    public function getAdminAffiliateDetailsData($affiliateId): array {
        try {
            $affiliateModel = new AffiliateModel();
            $usersModel = new UsersModel();
            $ordersModel = new OrdersModel();
            
            // Get affiliate details with user info
            $affiliateSql = "
                SELECT a.*, u.name as user_name, u.email as user_email, u.phone as user_phone, u.address as user_address
                FROM affiliates a
                LEFT JOIN users u ON a.user_id = u.id
                WHERE a.id = ?
            ";
            $result = $affiliateModel->db->query($affiliateSql, [$affiliateId]);
            $affiliate = $result ? $result[0] : null;
            
            if (!$affiliate) {
                throw new Exception('Affiliate not found');
            }
            
            // Get affiliate orders
            $ordersSql = "SELECT * FROM orders WHERE affiliate_id = ? ORDER BY created_at DESC LIMIT 10";
            $orders = $ordersModel->db->query($ordersSql, [$affiliateId]);
            
            // Transform data
            $transformedAffiliate = $this->transformer->transformAffiliate($affiliate);
            $transformedOrders = $this->transformer->transformOrders($orders);
            
            // Generate performance data (demo)
            $performanceData = $this->generateAffiliatePerformanceData($affiliateId);
            
            return [
                'affiliate' => $transformedAffiliate,
                'orders' => $transformedOrders,
                'performance_data' => $performanceData
            ];
            
        } catch (Exception $e) {
            error_log("ViewDataService::getAdminAffiliateDetailsData error: " . $e->getMessage());
            return [
                'affiliate' => null,
                'orders' => [],
                'performance_data' => ['labels' => [], 'sales' => [], 'commission' => []]
            ];
        }
    }
    
    /**
     * Get affiliate statistics for admin dashboard
     */
    private function getAffiliateStatistics(): array {
        try {
            $affiliateModel = new AffiliateModel();
            
            $stats = [
                'total' => 0,
                'active' => 0,
                'inactive' => 0,
                'pending' => 0,
                'total_sales' => 0,
                'total_commission' => 0
            ];
            
            // Get total affiliates by status
            $statusSql = "SELECT status, COUNT(*) as count FROM affiliates GROUP BY status";
            $statusResults = $affiliateModel->db->query($statusSql);
            
            foreach ($statusResults as $result) {
                $stats[$result['status']] = (int)$result['count'];
                $stats['total'] += (int)$result['count'];
            }
            
            // Get total sales and commission
            $salesSql = "SELECT SUM(total_sales) as sales, SUM(total_commission) as commission FROM affiliates";
            $salesResult = $affiliateModel->db->query($salesSql);
            if ($salesResult && $salesResult[0]) {
                $stats['total_sales'] = $salesResult[0]['sales'] ?? 0;
                $stats['total_commission'] = $salesResult[0]['commission'] ?? 0;
            }
            
            return $stats;
            
        } catch (Exception $e) {
            error_log("ViewDataService::getAffiliateStatistics error: " . $e->getMessage());
            return [
                'total' => 0,
                'active' => 0,
                'inactive' => 0,
                'pending' => 0,
                'total_sales' => 0,
                'total_commission' => 0
            ];
        }
    }
    
    /**
     * Generate affiliate performance data for charts
     */
    private function generateAffiliatePerformanceData($affiliateId): array {
        // Demo data - in real app, this would query actual performance metrics
        return [
            'labels' => ['T1', 'T2', 'T3', 'T4', 'T5', 'T6'],
            'sales' => [5000000, 7500000, 12000000, 8500000, 15000000, 18000000],
            'commission' => [500000, 750000, 1200000, 850000, 1500000, 1800000]
        ];
    }
    
    /**
     * Get admin events page data with pagination and filtering
     */
    public function getAdminEventsData($page = 1, $perPage = 10, $filters = []): array {
        try {
            require_once __DIR__ . '/../models/EventsModel.php';
            $eventsModel = new EventsModel();
            
            // Build search conditions
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
            
            // Get total count for pagination
            $whereClause = !empty($conditions) ? 'WHERE ' . implode(' AND ', $conditions) : '';
            $countSql = "SELECT COUNT(*) as total FROM events {$whereClause}";
            $totalResult = $eventsModel->db->query($countSql, $bindings);
            $total = $totalResult[0]['total'] ?? 0;
            
            // Get events with pagination
            $offset = ($page - 1) * $perPage;
            $eventsSql = "SELECT * FROM events {$whereClause} ORDER BY start_date DESC LIMIT {$perPage} OFFSET {$offset}";
            $events = $eventsModel->db->query($eventsSql, $bindings);
            
            // Transform events data
            $transformedEvents = [];
            foreach ($events as $event) {
                $transformedEvents[] = $this->transformer->transformEvent($event);
            }
            
            // Get event statistics
            $stats = $this->getEventStatistics();
            
            return [
                'events' => $transformedEvents,
                'pagination' => $this->calculatePagination($page, $perPage, $total),
                'filters' => $filters,
                'total' => $total,
                'stats' => $stats
            ];
            
        } catch (Exception $e) {
            error_log("ViewDataService::getAdminEventsData error: " . $e->getMessage());
            return [
                'events' => [],
                'pagination' => ['current_page' => 1, 'total' => 0],
                'filters' => $filters,
                'total' => 0,
                'stats' => ['total' => 0, 'upcoming' => 0, 'ongoing' => 0, 'completed' => 0, 'cancelled' => 0]
            ];
        }
    }
    
    /**
     * Get admin event details data
     */
    public function getAdminEventDetailsData($eventId): array {
        try {
            require_once __DIR__ . '/../models/EventsModel.php';
            $eventsModel = new EventsModel();
            
            // Get event details
            $event = $this->getDataWithRetry([$eventsModel, 'find'], [$eventId]);
            
            if (!$event) {
                throw new Exception('Event not found');
            }
            
            // Transform event data
            $transformedEvent = $this->transformer->transformEvent($event);
            
            return [
                'event' => $transformedEvent
            ];
            
        } catch (Exception $e) {
            error_log("ViewDataService::getAdminEventDetailsData error: " . $e->getMessage());
            return [
                'event' => null
            ];
        }
    }
    
    /**
     * Get event statistics for admin dashboard
     */
    private function getEventStatistics(): array {
        try {
            require_once __DIR__ . '/../models/EventsModel.php';
            $eventsModel = new EventsModel();
            
            $stats = [
                'total' => 0,
                'upcoming' => 0,
                'ongoing' => 0,
                'completed' => 0,
                'cancelled' => 0,
                'this_month' => 0,
                'total_participants' => 0
            ];
            
            // Get total events by status
            $statusSql = "SELECT status, COUNT(*) as count FROM events GROUP BY status";
            $statusResults = $eventsModel->db->query($statusSql);
            
            foreach ($statusResults as $result) {
                $stats[$result['status']] = (int)$result['count'];
                $stats['total'] += (int)$result['count'];
            }
            
            // Get this month's events
            $monthSql = "SELECT COUNT(*) as count FROM events WHERE YEAR(start_date) = YEAR(NOW()) AND MONTH(start_date) = MONTH(NOW())";
            $monthResult = $eventsModel->db->query($monthSql);
            $stats['this_month'] = $monthResult[0]['count'] ?? 0;
            
            // Get total participants
            $participantsSql = "SELECT SUM(current_participants) as total FROM events";
            $participantsResult = $eventsModel->db->query($participantsSql);
            $stats['total_participants'] = $participantsResult[0]['total'] ?? 0;
            
            return $stats;
            
        } catch (Exception $e) {
            error_log("ViewDataService::getEventStatistics error: " . $e->getMessage());
            return [
                'total' => 0,
                'upcoming' => 0,
                'ongoing' => 0,
                'completed' => 0,
                'cancelled' => 0,
                'this_month' => 0,
                'total_participants' => 0
            ];
        }
    }
    
    /**
     * Get available users for affiliate (users without affiliate accounts)
     */
    public function getAvailableUsersForAffiliate(): array {
        try {
            $usersModel = new UsersModel();
            
            // Get users that are not already affiliates
            $sql = "
                SELECT u.* FROM users u
                LEFT JOIN affiliates a ON u.id = a.user_id
                WHERE a.id IS NULL AND u.role IN ('user', 'agent')
                ORDER BY u.name
            ";
            $users = $usersModel->db->query($sql);
            
            return [
                'users' => $this->transformer->transformUsers($users)
            ];
            
        } catch (Exception $e) {
            error_log("ViewDataService::getAvailableUsersForAffiliate error: " . $e->getMessage());
            return ['users' => []];
        }
    }
    
    /**
     * Get categories page data with pagination and sorting
     */
    public function getCategoriesPageData($page = 1, $perPage = 12, $orderBy = 'name'): array {
        try {
            $categoriesModel = new CategoriesModel();
            
            // Get all categories with product counts
            $allCategories = $this->getDataWithRetry(
                [$categoriesModel, 'getWithProductCounts']
            );
            
            // Apply sorting
            $sortedCategories = $this->sortCategories($allCategories, $orderBy);
            
            // Calculate pagination
            $total = count($sortedCategories);
            $offset = ($page - 1) * $perPage;
            $categories = array_slice($sortedCategories, $offset, $perPage);
            
            // Get category statistics
            $stats = $this->getDataWithRetry(
                [$categoriesModel, 'getStats']
            );
            
            // Transform data for view
            $transformedCategories = [];
            foreach ($categories as $category) {
                $transformedCategories[] = $this->dataTransformer->transformCategory($category);
            }
            
            return [
                'categories' => $transformedCategories,
                'pagination' => $this->calculatePagination($page, $perPage, $total),
                'stats' => $stats,
                'total_categories' => $total,
                'displayed_count' => count($transformedCategories),
                'current_sort' => $orderBy
            ];
            
        } catch (Exception $e) {
            error_log("ViewDataService::getCategoriesPageData error: " . $e->getMessage());
            return $this->handleEmptyState('categories');
        }
    }
    
    /**
     * Sort categories based on order criteria
     */
    private function sortCategories($categories, $orderBy): array {
        switch ($orderBy) {
            case 'name_desc':
                usort($categories, function($a, $b) { 
                    return strcmp($b['name'], $a['name']); 
                });
                break;
            case 'course_count':
            case 'product_count':
                usort($categories, function($a, $b) { 
                    return ($b['products_count'] ?? 0) - ($a['products_count'] ?? 0); 
                });
                break;
            case 'course_count_desc':
            case 'product_count_desc':
                usort($categories, function($a, $b) { 
                    return ($a['products_count'] ?? 0) - ($b['products_count'] ?? 0); 
                });
                break;
            case 'popular':
                // Sort by product count as popularity indicator
                usort($categories, function($a, $b) { 
                    return ($b['products_count'] ?? 0) - ($a['products_count'] ?? 0); 
                });
                break;
            default: // name
                usort($categories, function($a, $b) { 
                    return strcmp($a['name'], $b['name']); 
                });
                break;
        }
        
        return $categories;
    }
    
    /**
     * Get admin products page data with pagination and filtering
     */
    public function getAdminProductsData($page = 1, $perPage = 10, $filters = []): array {
        try {
            $productsModel = new ProductsModel();
            $categoriesModel = new CategoriesModel();
            
            // Get categories for filter dropdown
            $categories = $this->getDataWithRetry([$categoriesModel, 'getActive']);
            
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
            
            // Get total count for pagination
            $whereClause = !empty($conditions) ? 'WHERE ' . implode(' AND ', $conditions) : '';
            $countSql = "SELECT COUNT(*) as total FROM products {$whereClause}";
            $totalResult = $productsModel->db->query($countSql, $bindings);
            $total = $totalResult[0]['total'] ?? 0;
            
            // Get products with pagination
            $offset = ($page - 1) * $perPage;
            $productsSql = "SELECT * FROM products {$whereClause} ORDER BY created_at DESC LIMIT {$perPage} OFFSET {$offset}";
            $products = $productsModel->db->query($productsSql, $bindings);
            
            // Transform products data
            $transformedProducts = [];
            foreach ($products as $product) {
                $transformedProducts[] = $this->dataTransformer->transformProduct($product);
            }
            
            return [
                'products' => $transformedProducts,
                'categories' => $categories,
                'pagination' => $this->calculatePagination($page, $perPage, $total),
                'filters' => $filters,
                'total' => $total
            ];
            
        } catch (Exception $e) {
            error_log("ViewDataService::getAdminProductsData error: " . $e->getMessage());
            return [
                'products' => [],
                'categories' => [],
                'pagination' => ['current_page' => 1, 'total' => 0],
                'filters' => $filters,
                'total' => 0
            ];
        }
    }
    
    /**
     * Get admin product details data
     */
    public function getAdminProductDetailsData($productId): array {
        try {
            $productsModel = new ProductsModel();
            $categoriesModel = new CategoriesModel();
            
            // Get product details
            $product = $this->getDataWithRetry([$productsModel, 'findById'], [$productId]);
            
            if (!$product) {
                throw new Exception('Product not found');
            }
            
            // Get active categories for dropdown
            $categories = $this->getDataWithRetry([$categoriesModel, 'getActive']);
            
            // Transform product data
            $transformedProduct = $this->transformer->transformProduct($product);
            
            return [
                'product' => $transformedProduct,
                'categories' => $categories
            ];
            
        } catch (Exception $e) {
            error_log("ViewDataService::getAdminProductDetailsData error: " . $e->getMessage());
            return [
                'product' => null,
                'categories' => []
            ];
        }
    }
    
    /**
     * Get admin users page data with pagination and filtering
     */
    public function getAdminUsersData($page = 1, $perPage = 10, $filters = []): array {
        try {
            $usersModel = new UsersModel();
            
            // Build search conditions
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
            
            // Get total count for pagination
            $whereClause = !empty($conditions) ? 'WHERE ' . implode(' AND ', $conditions) : '';
            $countSql = "SELECT COUNT(*) as total FROM users {$whereClause}";
            $totalResult = $usersModel->db->query($countSql, $bindings);
            $total = $totalResult[0]['total'] ?? 0;
            
            // Get users with pagination
            $offset = ($page - 1) * $perPage;
            $usersSql = "SELECT * FROM users {$whereClause} ORDER BY created_at DESC LIMIT {$perPage} OFFSET {$offset}";
            $users = $usersModel->db->query($usersSql, $bindings);
            
            // Transform users data
            $transformedUsers = [];
            foreach ($users as $user) {
                $transformedUsers[] = $this->dataTransformer->transformUser($user);
            }
            
            return [
                'users' => $transformedUsers,
                'pagination' => $this->calculatePagination($page, $perPage, $total),
                'filters' => $filters,
                'total' => $total
            ];
            
        } catch (Exception $e) {
            error_log("ViewDataService::getAdminUsersData error: " . $e->getMessage());
            return [
                'users' => [],
                'pagination' => ['current_page' => 1, 'total' => 0],
                'filters' => $filters,
                'total' => 0
            ];
        }
    }
    
    /**
     * Get admin user details data
     */
    public function getAdminUserDetailsData($userId): array {
        try {
            $usersModel = new UsersModel();
            
            // Get user details
            $user = $this->getDataWithRetry([$usersModel, 'findById'], [$userId]);
            
            if (!$user) {
                throw new Exception('User not found');
            }
            
            // Transform user data
            $transformedUser = $this->dataTransformer->transformUser($user);
            
            return [
                'user' => $transformedUser
            ];
            
        } catch (Exception $e) {
            error_log("ViewDataService::getAdminUserDetailsData error: " . $e->getMessage());
            return [
                'user' => null
            ];
        }
    }
    
    /**
     * Get admin user additional data (orders and affiliate info)
     */
    public function getAdminUserAdditionalData($userId): array {
        try {
            $ordersModel = new OrdersModel();
            $affiliateModel = new AffiliateModel();
            
            // Get user orders
            $orders = $this->getDataWithRetry([$ordersModel, 'getByUser'], [$userId]);
            
            // Get user affiliate info
            $affiliate = $this->getDataWithRetry([$affiliateModel, 'findBy'], ['user_id', $userId]);
            
            return [
                'orders' => $this->transformer->transformOrders($orders),
                'affiliate' => $affiliate ? $this->transformer->transformAffiliate($affiliate) : null
            ];
            
        } catch (Exception $e) {
            error_log("ViewDataService::getAdminUserAdditionalData error: " . $e->getMessage());
            return [
                'orders' => [],
                'affiliate' => null
            ];
        }
    }
    
    /**
     * Get admin categories page data with pagination and filtering
     */
    public function getAdminCategoriesData($page = 1, $perPage = 10, $filters = []): array {
        try {
            $categoriesModel = new CategoriesModel();
            $productsModel = new ProductsModel();
            
            // Build search conditions
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
            
            // Get total count for pagination
            $whereClause = !empty($conditions) ? 'WHERE ' . implode(' AND ', $conditions) : '';
            $countSql = "SELECT COUNT(*) as total FROM categories {$whereClause}";
            $totalResult = $categoriesModel->db->query($countSql, $bindings);
            $total = $totalResult[0]['total'] ?? 0;
            
            // Get categories with pagination
            $offset = ($page - 1) * $perPage;
            $categoriesSql = "SELECT * FROM categories {$whereClause} ORDER BY created_at DESC LIMIT {$perPage} OFFSET {$offset}";
            $categories = $categoriesModel->db->query($categoriesSql, $bindings);
            
            // Get product counts for each category
            foreach ($categories as &$category) {
                $productCountSql = "SELECT COUNT(*) as count FROM products WHERE category_id = ?";
                $countResult = $productsModel->db->query($productCountSql, [$category['id']]);
                $category['products_count'] = $countResult[0]['count'] ?? 0;
            }
            
            // Transform categories data
            $transformedCategories = [];
            foreach ($categories as $category) {
                $transformedCategories[] = $this->transformer->transformCategory($category);
            }
            
            return [
                'categories' => $transformedCategories,
                'pagination' => $this->calculatePagination($page, $perPage, $total),
                'filters' => $filters,
                'total' => $total
            ];
            
        } catch (Exception $e) {
            error_log("ViewDataService::getAdminCategoriesData error: " . $e->getMessage());
            return [
                'categories' => [],
                'pagination' => ['current_page' => 1, 'total' => 0],
                'filters' => $filters,
                'total' => 0
            ];
        }
    }
    
    /**
     * Get admin category details data
     */
    public function getAdminCategoryDetailsData($categoryId): array {
        try {
            $categoriesModel = new CategoriesModel();
            $productsModel = new ProductsModel();
            
            // Get category details
            $category = $this->getDataWithRetry([$categoriesModel, 'find'], [$categoryId]);
            
            if (!$category) {
                throw new Exception('Category not found');
            }
            
            // Get products in this category
            $products = $this->getDataWithRetry([$productsModel, 'getByCategory'], [$categoryId, 10]);
            
            // Get category statistics
            $productCountSql = "SELECT COUNT(*) as count FROM products WHERE category_id = ?";
            $countResult = $productsModel->db->query($productCountSql, [$categoryId]);
            $category['products_count'] = $countResult[0]['count'] ?? 0;
            
            // Transform data
            $transformedCategory = $this->transformer->transformCategory($category);
            $transformedProducts = $this->transformer->transformProducts($products);
            
            return [
                'category' => $transformedCategory,
                'products' => $transformedProducts
            ];
            
        } catch (Exception $e) {
            error_log("ViewDataService::getAdminCategoryDetailsData error: " . $e->getMessage());
            return [
                'category' => null,
                'products' => []
            ];
        }
    }
    /**
     * Get admin news page data with pagination and filtering
     */
    public function getAdminNewsData($page = 1, $perPage = 10, $filters = []): array {
        try {
            $newsModel = new NewsModel();
            $usersModel = new UsersModel();
            
            // Build search conditions
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
            
            // Get total count for pagination
            $whereClause = !empty($conditions) ? 'WHERE ' . implode(' AND ', $conditions) : '';
            $countSql = "SELECT COUNT(*) as total FROM news n {$whereClause}";
            $totalResult = $newsModel->db->query($countSql, $bindings);
            $total = $totalResult[0]['total'] ?? 0;
            
            // Get news with pagination and author info
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
            
            // Transform news data
            $transformedNews = [];
            foreach ($news as $article) {
                $transformedNews[] = $this->transformer->transformNews($article);
            }
            
            // Get news statistics
            $stats = $this->getNewsStatistics();
            
            return [
                'news' => $transformedNews,
                'pagination' => $this->calculatePagination($page, $perPage, $total),
                'filters' => $filters,
                'total' => $total,
                'stats' => $stats
            ];
            
        } catch (Exception $e) {
            error_log("ViewDataService::getAdminNewsData error: " . $e->getMessage());
            return [
                'news' => [],
                'pagination' => ['current_page' => 1, 'total' => 0],
                'filters' => $filters,
                'total' => 0,
                'stats' => ['total' => 0, 'published' => 0, 'draft' => 0, 'archived' => 0]
            ];
        }
    }
    
    /**
     * Get admin news details data
     */
    public function getAdminNewsDetailsData($newsId): array {
        try {
            $newsModel = new NewsModel();
            $usersModel = new UsersModel();
            
            // Get news details
            $news = $this->getDataWithRetry([$newsModel, 'find'], [$newsId]);
            
            if (!$news) {
                throw new Exception('News not found');
            }
            
            // Get author info
            $author = null;
            if (!empty($news['author_id'])) {
                $author = $this->getDataWithRetry([$usersModel, 'getById'], [$news['author_id']]);
            }
            
            // Transform data
            $transformedNews = $this->transformer->transformNews($news);
            $transformedAuthor = $author ? $this->transformer->transformUser($author) : null;
            
            return [
                'news' => $transformedNews,
                'author' => $transformedAuthor
            ];
            
        } catch (Exception $e) {
            error_log("ViewDataService::getAdminNewsDetailsData error: " . $e->getMessage());
            return [
                'news' => null,
                'author' => null
            ];
        }
    }
    
    /**
     * Get news statistics for admin dashboard
     */
    private function getNewsStatistics(): array {
        try {
            $newsModel = new NewsModel();
            
            $stats = [
                'total' => 0,
                'published' => 0,
                'draft' => 0,
                'archived' => 0,
                'today' => 0,
                'this_month' => 0
            ];
            
            // Get total news by status
            $statusSql = "SELECT status, COUNT(*) as count FROM news GROUP BY status";
            $statusResults = $newsModel->db->query($statusSql);
            
            foreach ($statusResults as $result) {
                $stats[$result['status']] = (int)$result['count'];
                $stats['total'] += (int)$result['count'];
            }
            
            // Get today's news
            $todaySql = "SELECT COUNT(*) as count FROM news WHERE DATE(created_at) = CURDATE()";
            $todayResult = $newsModel->db->query($todaySql);
            $stats['today'] = $todayResult[0]['count'] ?? 0;
            
            // Get this month's news
            $monthSql = "SELECT COUNT(*) as count FROM news WHERE YEAR(created_at) = YEAR(NOW()) AND MONTH(created_at) = MONTH(NOW())";
            $monthResult = $newsModel->db->query($monthSql);
            $stats['this_month'] = $monthResult[0]['count'] ?? 0;
            
            return $stats;
            
        } catch (Exception $e) {
            error_log("ViewDataService::getNewsStatistics error: " . $e->getMessage());
            return [
                'total' => 0,
                'published' => 0,
                'draft' => 0,
                'archived' => 0,
                'today' => 0,
                'this_month' => 0
            ];
        }
    }

    /**
     * Get admin orders page data with pagination and filtering
     */
    public function getAdminOrdersData($page = 1, $perPage = 10, $filters = []): array {
        try {
            $ordersModel = new OrdersModel();
            $usersModel = new UsersModel();
            $productsModel = new ProductsModel();
            
            // Build search conditions
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
            
            // Get total count for pagination
            $whereClause = !empty($conditions) ? 'WHERE ' . implode(' AND ', $conditions) : '';
            $countSql = "SELECT COUNT(*) as total FROM orders o LEFT JOIN users u ON o.user_id = u.id {$whereClause}";
            $totalResult = $ordersModel->db->query($countSql, $bindings);
            $total = $totalResult[0]['total'] ?? 0;
            
            // Get orders with pagination
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
            
            // Transform orders data
            $transformedOrders = [];
            foreach ($orders as $order) {
                $transformedOrders[] = $this->transformer->transformOrder($order);
            }
            
            // Get order statistics
            $stats = $this->getOrderStatistics();
            
            return [
                'orders' => $transformedOrders,
                'pagination' => $this->calculatePagination($page, $perPage, $total),
                'filters' => $filters,
                'total' => $total,
                'stats' => $stats
            ];
            
        } catch (Exception $e) {
            error_log("ViewDataService::getAdminOrdersData error: " . $e->getMessage());
            return [
                'orders' => [],
                'pagination' => ['current_page' => 1, 'total' => 0],
                'filters' => $filters,
                'total' => 0,
                'stats' => ['total' => 0, 'pending' => 0, 'processing' => 0, 'completed' => 0, 'cancelled' => 0]
            ];
        }
    }
    
    /**
     * Get admin order details data
     */
    public function getAdminOrderDetailsData($orderId): array {
        try {
            $ordersModel = new OrdersModel();
            $usersModel = new UsersModel();
            $productsModel = new ProductsModel();
            
            // Get order details
            $order = $this->getDataWithRetry([$ordersModel, 'getById'], [$orderId]);
            
            if (!$order) {
                throw new Exception('Order not found');
            }
            
            // Get related user
            $user = null;
            if (!empty($order['user_id'])) {
                $user = $this->getDataWithRetry([$usersModel, 'getById'], [$order['user_id']]);
            }
            
            // Get order items with product details
            $orderItems = $this->getDataWithRetry([$ordersModel, 'getOrderItems'], [$orderId]);
            
            // Get product details for each item
            foreach ($orderItems as &$item) {
                if (!empty($item['product_id'])) {
                    $product = $this->getDataWithRetry([$productsModel, 'getById'], [$item['product_id']]);
                    $item['product'] = $product ? $this->transformer->transformProduct($product) : null;
                }
            }
            
            // Transform data
            $transformedOrder = $this->transformer->transformOrder($order);
            $transformedUser = $user ? $this->transformer->transformUser($user) : null;
            
            return [
                'order' => $transformedOrder,
                'user' => $transformedUser,
                'order_items' => $orderItems
            ];
            
        } catch (Exception $e) {
            error_log("ViewDataService::getAdminOrderDetailsData error: " . $e->getMessage());
            return [
                'order' => null,
                'user' => null,
                'order_items' => []
            ];
        }
    }
    
    /**
     * Get order statistics for admin dashboard
     */
    private function getOrderStatistics(): array {
        try {
            $ordersModel = new OrdersModel();
            
            $stats = [
                'total' => 0,
                'pending' => 0,
                'processing' => 0,
                'completed' => 0,
                'cancelled' => 0,
                'today' => 0,
                'this_month' => 0,
                'total_revenue' => 0
            ];
            
            // Get total orders by status
            $statusSql = "SELECT status, COUNT(*) as count FROM orders GROUP BY status";
            $statusResults = $ordersModel->db->query($statusSql);
            
            foreach ($statusResults as $result) {
                $stats[$result['status']] = (int)$result['count'];
                $stats['total'] += (int)$result['count'];
            }
            
            // Get today's orders
            $todaySql = "SELECT COUNT(*) as count FROM orders WHERE DATE(created_at) = CURDATE()";
            $todayResult = $ordersModel->db->query($todaySql);
            $stats['today'] = $todayResult[0]['count'] ?? 0;
            
            // Get this month's orders
            $monthSql = "SELECT COUNT(*) as count FROM orders WHERE YEAR(created_at) = YEAR(NOW()) AND MONTH(created_at) = MONTH(NOW())";
            $monthResult = $ordersModel->db->query($monthSql);
            $stats['this_month'] = $monthResult[0]['count'] ?? 0;
            
            // Get total revenue
            $revenueSql = "SELECT SUM(total) as revenue FROM orders WHERE status = 'completed'";
            $revenueResult = $ordersModel->db->query($revenueSql);
            $stats['total_revenue'] = $revenueResult[0]['revenue'] ?? 0;
            
            return $stats;
            
        } catch (Exception $e) {
            error_log("ViewDataService::getOrderStatistics error: " . $e->getMessage());
            return [
                'total' => 0,
                'pending' => 0,
                'processing' => 0,
                'completed' => 0,
                'cancelled' => 0,
                'today' => 0,
                'this_month' => 0,
                'total_revenue' => 0
            ];
        }
    }

    private function sortProducts($products, $orderBy): array {
        switch ($orderBy) {
            case 'post_title':
                usort($products, function($a, $b) { 
                    return strcmp($a['name'], $b['name']); 
                });
                break;
            case 'post_title_desc':
                usort($products, function($a, $b) { 
                    return strcmp($b['name'], $a['name']); 
                });
                break;
            case 'price':
                usort($products, function($a, $b) { 
                    return ($b['price'] ?? 0) - ($a['price'] ?? 0); 
                });
                break;
            case 'price_low':
                usort($products, function($a, $b) { 
                    return ($a['price'] ?? 0) - ($b['price'] ?? 0); 
                });
                break;
            case 'popular':
                usort($products, function($a, $b) { 
                    return ($b['view_count'] ?? 0) - ($a['view_count'] ?? 0); 
                });
                break;
            case 'rating':
                usort($products, function($a, $b) { 
                    return ($b['rating'] ?? 0) - ($a['rating'] ?? 0); 
                });
                break;
            default: // post_date
                usort($products, function($a, $b) { 
                    return strtotime($b['created_at'] ?? '0') - strtotime($a['created_at'] ?? '0'); 
                });
                break;
        }
        
        return $products;
    }
    
    /**
     * Xử lý empty states
     */
    public function handleEmptyState($type): array {
        $emptyStates = [
            'home' => [
                'featured_products' => [],
                'latest_products' => [],
                'featured_categories' => [],
                'latest_news' => [],
                'message' => 'Đang cập nhật dữ liệu, vui lòng thử lại sau'
            ],
            'products' => [
                'products' => [],
                'pagination' => ['current_page' => 1, 'total' => 0],
                'message' => 'Chưa có sản phẩm nào'
            ],
            'categories' => [
                'categories' => [],
                'pagination' => ['current_page' => 1, 'total' => 0],
                'stats' => ['total' => 0, 'active' => 0, 'parent_categories' => 0, 'with_products' => 0],
                'total_categories' => 0,
                'displayed_count' => 0,
                'current_sort' => 'name',
                'message' => 'Chưa có danh mục nào'
            ],
            'category' => [
                'category' => null,
                'products' => [],
                'message' => 'Danh mục không tồn tại hoặc chưa có sản phẩm'
            ],
            'admin_dashboard' => [
                'product_stats' => ['total' => 0],
                'user_stats' => ['total' => 0],
                'recent_products' => [],
                'recent_users' => [],
                'message' => 'Đang tải dữ liệu dashboard'
            ],
            'user_dashboard' => [
                'user' => null,
                'recent_orders' => [],
                'message' => 'Không thể tải thông tin người dùng'
            ],
            'affiliate_dashboard' => [
                'affiliate' => null,
                'commissions' => [],
                'message' => 'Không thể tải thông tin affiliate'
            ],
            'product_details' => [
                'product' => null,
                'category' => null,
                'related_products' => [],
                'message' => 'Không tìm thấy sản phẩm'
            ],
            'contact' => [
                'contact' => [
                    'office_address' => 'Đang cập nhật',
                    'phone' => 'Đang cập nhật',
                    'hotline' => 'Đang cập nhật',
                    'email' => 'Đang cập nhật',
                    'working_hours_weekday' => 'Đang cập nhật',
                    'working_hours_weekend' => 'Đang cập nhật'
                ],
                'message' => 'Không thể tải thông tin liên hệ'
            ]
        ];
        
        return $emptyStates[$type] ?? ['message' => 'Không có dữ liệu'];
    }
    
    /**
     * Get admin settings page data with pagination and filtering
     */
    public function getAdminSettingsData($page = 1, $perPage = 10, $filters = []): array {
        try {
            require_once __DIR__ . '/../models/SettingsModel.php';
            $settingsModel = new SettingsModel();
            
            // Build search conditions
            $conditions = [];
            $bindings = [];
            
            if (!empty($filters['search'])) {
                $conditions[] = "(key LIKE ? OR description LIKE ? OR value LIKE ?)";
                $searchTerm = "%{$filters['search']}%";
                $bindings = array_merge($bindings, [$searchTerm, $searchTerm, $searchTerm]);
            }
            
            if (!empty($filters['type'])) {
                $conditions[] = "type = ?";
                $bindings[] = $filters['type'];
            }
            
            // Get total count for pagination
            $whereClause = !empty($conditions) ? 'WHERE ' . implode(' AND ', $conditions) : '';
            $countSql = "SELECT COUNT(*) as total FROM settings {$whereClause}";
            $totalResult = $settingsModel->db->query($countSql, $bindings);
            $total = $totalResult[0]['total'] ?? 0;
            
            // Get settings with pagination
            $offset = ($page - 1) * $perPage;
            $settingsSql = "SELECT * FROM settings {$whereClause} ORDER BY key LIMIT {$perPage} OFFSET {$offset}";
            $settings = $settingsModel->db->query($settingsSql, $bindings);
            
            // Get unique types for filter
            $typesSql = "SELECT DISTINCT type FROM settings ORDER BY type";
            $typesResult = $settingsModel->db->query($typesSql);
            $types = array_column($typesResult, 'type');
            
            return [
                'settings' => $settings,
                'types' => $types,
                'pagination' => $this->calculatePagination($page, $perPage, $total),
                'filters' => $filters,
                'total' => $total
            ];
            
        } catch (Exception $e) {
            error_log("ViewDataService::getAdminSettingsData error: " . $e->getMessage());
            return [
                'settings' => [],
                'types' => [],
                'pagination' => ['current_page' => 1, 'total' => 0],
                'filters' => $filters,
                'total' => 0
            ];
        }
    }
    
    /**
     * Get admin setting details data
     */
    public function getAdminSettingDetailsData($settingKey): array {
        try {
            // Get setting details
            $setting = $this->getDataWithRetry([$this->settingsModel, 'getByKey'], [$settingKey]);
            
            if (!$setting) {
                throw new Exception('Setting not found');
            }
            
            // Transform setting data
            $transformedSetting = $this->transformer->transformSetting($setting);
            
            return [
                'setting' => $transformedSetting
            ];
            
        } catch (Exception $e) {
            error_log("ViewDataService::getAdminSettingDetailsData error: " . $e->getMessage());
            return [
                'setting' => null
            ];
        }
    }
    
    /**
     * Get admin contacts page data with pagination and filtering
     */
    public function getAdminContactsData($page = 1, $perPage = 10, $filters = []): array {
        try {
            require_once __DIR__ . '/../models/ContactsModel.php';
            $contactsModel = new ContactsModel();
            
            // Build search conditions
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
            
            // Get total count for pagination
            $whereClause = !empty($conditions) ? 'WHERE ' . implode(' AND ', $conditions) : '';
            $countSql = "SELECT COUNT(*) as total FROM contacts {$whereClause}";
            $totalResult = $contactsModel->db->query($countSql, $bindings);
            $total = $totalResult[0]['total'] ?? 0;
            
            // Get contacts with pagination
            $offset = ($page - 1) * $perPage;
            $contactsSql = "SELECT * FROM contacts {$whereClause} ORDER BY created_at DESC LIMIT {$perPage} OFFSET {$offset}";
            $contacts = $contactsModel->db->query($contactsSql, $bindings);
            
            // Get contact statistics
            $stats = $this->getContactStatistics();
            
            return [
                'contacts' => $contacts,
                'pagination' => $this->calculatePagination($page, $perPage, $total),
                'filters' => $filters,
                'total' => $total,
                'stats' => $stats
            ];
            
        } catch (Exception $e) {
            error_log("ViewDataService::getAdminContactsData error: " . $e->getMessage());
            return [
                'contacts' => [],
                'pagination' => ['current_page' => 1, 'total' => 0],
                'filters' => $filters,
                'total' => 0,
                'stats' => ['total' => 0, 'new' => 0, 'read' => 0, 'replied' => 0]
            ];
        }
    }
    
    /**
     * Get contact statistics for admin dashboard
     */
    private function getContactStatistics(): array {
        try {
            require_once __DIR__ . '/../models/ContactsModel.php';
            $contactsModel = new ContactsModel();
            
            $stats = [
                'total' => 0,
                'new' => 0,
                'read' => 0,
                'replied' => 0,
                'today' => 0,
                'this_month' => 0
            ];
            
            // Get total contacts by status
            $statusSql = "SELECT status, COUNT(*) as count FROM contacts GROUP BY status";
            $statusResults = $contactsModel->db->query($statusSql);
            
            foreach ($statusResults as $result) {
                $stats[$result['status']] = (int)$result['count'];
                $stats['total'] += (int)$result['count'];
            }
            
            // Get today's contacts
            $todaySql = "SELECT COUNT(*) as count FROM contacts WHERE DATE(created_at) = CURDATE()";
            $todayResult = $contactsModel->db->query($todaySql);
            $stats['today'] = $todayResult[0]['count'] ?? 0;
            
            // Get this month's contacts
            $monthSql = "SELECT COUNT(*) as count FROM contacts WHERE YEAR(created_at) = YEAR(NOW()) AND MONTH(created_at) = MONTH(NOW())";
            $monthResult = $contactsModel->db->query($monthSql);
            $stats['this_month'] = $monthResult[0]['count'] ?? 0;
            
            return $stats;
            
        } catch (Exception $e) {
            error_log("ViewDataService::getContactStatistics error: " . $e->getMessage());
            return [
                'total' => 0,
                'new' => 0,
                'read' => 0,
                'replied' => 0,
                'today' => 0,
                'this_month' => 0
            ];
        }
    }
    
    /**
     * Get admin contact details data
     */
    public function getAdminContactDetailsData($contactId): array {
        try {
            // Get contact details
            $contact = $this->getDataWithRetry([$this->contactsModel, 'find'], [$contactId]);
            
            if (!$contact) {
                throw new Exception('Contact not found');
            }
            
            // Transform contact data
            $transformedContact = $this->transformer->transformContact($contact);
            
            return [
                'contact' => $transformedContact
            ];
            
        } catch (Exception $e) {
            error_log("ViewDataService::getAdminContactDetailsData error: " . $e->getMessage());
            return [
                'contact' => null
            ];
        }
    }
    
    /**
     * Get admin revenue data with date filtering
     */
    public function getAdminRevenueData($filters = []): array {
        try {
            $ordersModel = new OrdersModel();
            $productsModel = new ProductsModel();
            $usersModel = new UsersModel();
            $affiliateModel = new AffiliateModel();
            
            // Date filter
            $dateFrom = $filters['date_from'] ?? date('Y-m-01');
            $dateTo = $filters['date_to'] ?? date('Y-m-d');
            
            // Build filter conditions
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
            
            // Get filtered orders
            $whereClause = implode(' AND ', $conditions);
            $ordersSql = "SELECT * FROM orders WHERE {$whereClause} ORDER BY created_at DESC";
            $orders = $ordersModel->db->query($ordersSql, $bindings);
            
            // Get all products, users, and affiliates for lookups
            $products = $this->getDataWithRetry([$productsModel, 'all']);
            $users = $this->getDataWithRetry([$usersModel, 'all']);
            $affiliates = $this->getDataWithRetry([$affiliateModel, 'all']);
            
            // Create lookups
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
            
            // Calculate statistics
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
                'filters' => $filters
            ];
            
        } catch (Exception $e) {
            error_log("ViewDataService::getAdminRevenueData error: " . $e->getMessage());
            return [
                'orders' => [],
                'products' => [],
                'users' => [],
                'affiliates' => [],
                'stats' => ['total_revenue' => 0, 'total_orders' => 0],
                'revenue_by_product' => [],
                'revenue_by_date' => [],
                'filters' => $filters
            ];
        }
    }
    
    /**
     * Calculate revenue statistics
     */
    private function calculateRevenueStatistics($orders): array {
        $stats = [
            'total_revenue' => 0,
            'completed_revenue' => 0,
            'pending_revenue' => 0,
            'total_orders' => count($orders),
            'completed_orders' => 0,
            'pending_orders' => 0,
            'processing_orders' => 0,
            'cancelled_orders' => 0,
            'revenue_by_status' => [
                'completed' => 0,
                'processing' => 0,
                'pending' => 0,
                'cancelled' => 0
            ]
        ];
        
        foreach ($orders as $order) {
            $stats['total_revenue'] += $order['total'];
            $stats['revenue_by_status'][$order['status']] += $order['total'];
            
            switch ($order['status']) {
                case 'completed':
                    $stats['completed_orders']++;
                    $stats['completed_revenue'] += $order['total'];
                    break;
                case 'processing':
                    $stats['processing_orders']++;
                    break;
                case 'pending':
                    $stats['pending_orders']++;
                    $stats['pending_revenue'] += $order['total'];
                    break;
                case 'cancelled':
                    $stats['cancelled_orders']++;
                    break;
            }
        }
        
        return $stats;
    }
    
    /**
     * Calculate revenue by product
     */
    private function calculateRevenueByProduct($orders, $productLookup): array {
        $revenueByProduct = [];
        
        foreach ($orders as $order) {
            $productId = $order['product_id'];
            if (!isset($revenueByProduct[$productId])) {
                $revenueByProduct[$productId] = [
                    'product' => $productLookup[$productId] ?? null,
                    'revenue' => 0,
                    'orders' => 0
                ];
            }
            $revenueByProduct[$productId]['revenue'] += $order['total'];
            $revenueByProduct[$productId]['orders']++;
        }
        
        // Sort by revenue (descending)
        uasort($revenueByProduct, function($a, $b) {
            return $b['revenue'] - $a['revenue'];
        });
        
        return $revenueByProduct;
    }
    
    /**
     * Calculate revenue by date
     */
    private function calculateRevenueByDate($orders, $dateFrom, $dateTo): array {
        $revenueByDate = [];
        
        // Initialize all dates with 0
        $currentDate = strtotime($dateFrom);
        $endDate = strtotime($dateTo);
        
        while ($currentDate <= $endDate) {
            $dateStr = date('Y-m-d', $currentDate);
            $revenueByDate[$dateStr] = 0;
            $currentDate = strtotime('+1 day', $currentDate);
        }
        
        // Add actual revenue data
        foreach ($orders as $order) {
            $date = date('Y-m-d', strtotime($order['created_at']));
            if (isset($revenueByDate[$date])) {
                $revenueByDate[$date] += $order['total'];
            }
        }
        
        return $revenueByDate;
    }
    
    /**
     * Execute method with retry logic
     */
    private function getDataWithRetry($method, $params = [], $maxRetries = 1) {
        $attempts = 0;
        
        while ($attempts <= $maxRetries) {
            try {
                return call_user_func_array($method, $params);
            } catch (Exception $e) {
                $attempts++;
                if ($attempts > $maxRetries) {
                    throw $e;
                }
                // Wait before retry
                usleep(100000); // 100ms
            }
        }
    }
    
    /**
     * Calculate pagination info
     */
    private function calculatePagination($currentPage, $perPage, $total) {
        return [
            'current_page' => $currentPage,
            'per_page' => $perPage,
            'total' => $total,
            'last_page' => ceil($total / $perPage),
            'from' => ($currentPage - 1) * $perPage + 1,
            'to' => min($currentPage * $perPage, $total)
        ];
    }
}