<?php

require_once __DIR__ . '/BaseService.php';
require_once __DIR__ . '/DataTransformer.php';
require_once __DIR__ . '/CacheService.php';

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
    protected CacheService $cache;

    public function __construct(?ErrorHandler $errorHandler = null, string $serviceType = 'admin')
    {
        parent::__construct($errorHandler, $serviceType);
        $this->transformer = new DataTransformer();
        $this->cache = new CacheService();
    }

    // ==================== DASHBOARD ====================

    public function getDashboardData(): array
    {
        try {
            $data = [];

            // Lấy thống kê tổng quan từ builder (có cache)
            $statsResult = $this->getDashboardStatistics();
            $statsData   = $statsResult['data'] ?? [];

            $data['stats'] = [
                'total_products'  => $statsData['total_products']  ?? 0,
                'total_revenue'   => $statsData['total_revenue']   ?? 0,
                'published_news'  => $statsData['published_news']  ?? 0,
                'upcoming_events' => $statsData['upcoming_events'] ?? 0,
            ];

            $data['trends'] = $statsData['trends'] ?? [];

            // Product statistics (for backward compat)
            $data['product_stats'] = $this->callModelMethod('ProductsModel', 'getStats', [], []);

            // User statistics
            $data['user_stats'] = $this->callModelMethod('UsersModel', 'getStats', [], []);

            // Recent products
            $recentProducts       = $this->callModelMethod('ProductsModel', 'getWithCategory', [5], []);
            $data['recent_products'] = $this->transformer->transformProducts($recentProducts);

            // Recent users
            $recentUsers          = $this->callModelMethod('UsersModel', 'all', ['*'], []);
            $data['recent_users'] = $this->transformer->transformUsers($recentUsers);

            // Charts data - tải qua AJAX thay vì server-side
            $data['top_products']     = [];
            $data['recent_activities']= [];
            $data['charts_data']      = [];
            $data['alerts']           = [];

            return $data;
        } catch (\Exception $e) {
            return $this->handleError($e, ['method' => 'getDashboardData']);
        }
    }


    /**
     * API: Dữ liệu doanh thu theo kỳ (7days, 30days, 12months).
     * Cache 5 phút.
     *
     * @param array $filters ['period' => '30days', 'date_from' => '2024-01-01', 'date_to' => '2024-01-31']
     * @return array
     */
    public function getDashboardRevenueData(array $filters = []): array
    {
        $period   = $filters['period'] ?? '30days';
        $dateFrom = $filters['date_from'] ?? $this->getDateFrom($period);
        $dateTo   = $filters['date_to']   ?? date('Y-m-d');

        $cacheKey = $this->cache->generateKey('dashboard:revenue', [
            'period'    => $period,
            'date_from' => $dateFrom,
            'date_to'   => $dateTo,
        ]);

        return $this->cache->remember($cacheKey, function () use ($period, $dateFrom, $dateTo) {
            return $this->buildRevenueChartData($period, $dateFrom, $dateTo);
        }, 300);
    }

    /**
     * API: Top N sản phẩm bán chạy.
     * Cache 10 phút.
     */
    public function getDashboardTopProducts(int $limit = 5, string $period = '30days'): array
    {
        $dateFrom = $this->getDateFrom($period);
        $dateTo   = date('Y-m-d');

        $cacheKey = $this->cache->generateKey('dashboard:top_products', [
            'limit'     => $limit,
            'date_from' => $dateFrom,
            'date_to'   => $dateTo,
        ]);

        return $this->cache->remember($cacheKey, function () use ($limit, $dateFrom, $dateTo) {
            return $this->buildTopProductsData($limit, $dateFrom, $dateTo);
        }, 600);
    }

    /**
     * API: Phân bố trạng thái đơn hàng.
     * Không cache dài vì cần real-time.
     */
    public function getDashboardOrdersStatus(): array
    {
        $cacheKey = $this->cache->generateKey('dashboard:orders_status');

        return $this->cache->remember($cacheKey, function () {
            return $this->buildOrdersStatusData();
        }, 300);
    }

    /**
     * API: Người dùng mới theo tuần/tháng.
     * Cache 15 phút.
     */
    public function getDashboardNewUsers(string $period = '4weeks'): array
    {
        $cacheKey = $this->cache->generateKey('dashboard:new_users', ['period' => $period]);

        return $this->cache->remember($cacheKey, function () use ($period) {
            return $this->buildNewUsersData($period);
        }, 900);
    }

    /**
     * API: Thống kê tổng quan (KPI cards).
     * Cache 5 phút.
     */
    public function getDashboardStatistics(): array
    {
        $cacheKey = $this->cache->generateKey('dashboard:stats:general');

        return $this->cache->remember($cacheKey, function () {
            return $this->buildDashboardStatistics();
        }, 300);
    }

    /**
     * API: Lấy tất cả dữ liệu chart trong một lần gọi.
     */
    public function getDashboardChartsData(array $filters = []): array
    {
        try {
            return [
                'revenue'      => $this->getDashboardRevenueData($filters),
                'top_products' => $this->getDashboardTopProducts(5, $filters['period'] ?? '30days'),
                'orders_status'=> $this->getDashboardOrdersStatus(),
                'new_users'    => $this->getDashboardNewUsers('4weeks'),
                'stats'        => $this->getDashboardStatistics(),
            ];
        } catch (\Exception $e) {
            return $this->handleError($e, ['method' => 'getDashboardChartsData']);
        }
    }

    /**
     * Lấy thông báo admin (cho header).
     * Cache 5 phút.
     */
    public function getAdminNotifications(int $limit = 3): array
    {
        $cacheKey = $this->cache->generateKey('dashboard:notifications', ['limit' => $limit]);

        return $this->cache->remember($cacheKey, function () use ($limit) {
            return $this->buildAdminNotifications($limit);
        }, 300);
    }

    public function markNotificationAsRead(int $id): bool
    {
        try {
            $sql = "UPDATE admin_notifications SET is_read = 1 WHERE id = ?";
            $result = $this->getModel('BaseModel')->execute($sql, [$id]);
            if ($result) {
                $this->cache->delete('dashboard:notifications*');
            }
            return $result !== false;
        } catch (\Exception $e) {
            return $this->handleError($e, ['method' => 'markNotificationAsRead', 'id' => $id]) !== null;
        }
    }

    public function markAllNotificationsAsRead(): bool
    {
        try {
            $sql = "UPDATE admin_notifications SET is_read = 1";
            $result = $this->getModel('BaseModel')->execute($sql);
            if ($result) {
                $this->cache->delete('dashboard:notifications*');
            }
            return $result !== false;
        } catch (\Exception $e) {
            return $this->handleError($e, ['method' => 'markAllNotificationsAsRead']) !== null;
        }
    }

    public function deleteNotification(int $id): bool
    {
        try {
            $sql = "DELETE FROM admin_notifications WHERE id = ?";
            $result = $this->getModel('BaseModel')->execute($sql, [$id]);
            if ($result) {
                $this->cache->delete('dashboard:notifications*');
            }
            return $result !== false;
        } catch (\Exception $e) {
            return $this->handleError($e, ['method' => 'deleteNotification', 'id' => $id]) !== null;
        }
    }

    public function getAllNotifications(int $page = 1, int $perPage = 20): array
    {
        try {
            $model = $this->getModel('BaseModel');
            
            $countSql = "SELECT COUNT(*) as total FROM admin_notifications";
            $totalResult = $model->query($countSql);
            $total = $totalResult[0]['total'] ?? 0;
            
            $offset = ($page - 1) * $perPage;
            $sql = "SELECT * FROM admin_notifications ORDER BY created_at DESC LIMIT {$perPage} OFFSET {$offset}";
            $rows = $model->query($sql);
            
            $notifications = [];
            foreach ($rows as $row) {
                $notifications[] = [
                    'id'       => $row['id'],
                    'type'     => $row['type'],
                    'icon'     => $row['icon'],
                    'message'  => $row['message'],
                    'is_read'  => $row['is_read'],
                    'time'     => $row['created_at'],
                    'time_ago' => $this->timeAgo($row['created_at']),
                    'link'     => $row['link']
                ];
            }
            
            return [
                'notifications' => $notifications,
                'pagination' => $this->calculatePagination($page, $perPage, $total),
                'total' => $total
            ];
        } catch (\Exception $e) {
            return $this->handleError($e, ['method' => 'getAllNotifications']);
        }
    }

    /**
     * Lấy Menu Admin động.
     * Cache 60 phút.
     */
    public function getAdminMenus(): array
    {
        $cacheKey = $this->cache->generateKey('admin:menus');

        return $this->cache->remember($cacheKey, function () {
            try {
                $sql = "SELECT * FROM admin_menus WHERE status = 1 ORDER BY sort_order ASC";
                $menus = $this->getModel('BaseModel')->query($sql);
                
                // Nesting logic (nếu có submenus) có thể thêm ở đây
                return ['success' => true, 'data' => $menus];
            } catch (\Exception $e) {
                return ['success' => false, 'message' => $e->getMessage()];
            }
        }, 3600);
    }

    /**
     * Xóa sạch các cache liên quan đến dashboard.
     * Gọi khi có thay đổi dữ liệu (Order mới, Product mới, v.v.)
     */
    public function flushDashboardCache(): bool
    {
        return $this->cache->flush('dashboard:*');
    }

    // ==================== DASHBOARD PRIVATE BUILDERS ====================

    private function getDateFrom(string $period): string
    {
        switch ($period) {
            case '7days':
                return date('Y-m-d', strtotime('-7 days'));
            case '12months':
                return date('Y-m-d', strtotime('-12 months'));
            case '4weeks':
                return date('Y-m-d', strtotime('-4 weeks'));
            case '30days':
            default:
                return date('Y-m-d', strtotime('-30 days'));
        }
    }

    private function buildRevenueChartData(string $period, string $dateFrom, string $dateTo): array
    {
        try {
            $ordersModel = $this->getModel('OrdersModel');
            if (!$ordersModel) {
                return $this->emptyRevenueData();
            }

            // Query doanh thu theo ngày
            $sql = "
                SELECT DATE(created_at) as date, SUM(`total`) as revenue, COUNT(*) as orders_count
                FROM orders
                WHERE status = 'completed'
                  AND DATE(created_at) >= ?
                  AND DATE(created_at) <= ?
                GROUP BY DATE(created_at)
                ORDER BY date ASC
            ";
            $rows = $ordersModel->query($sql, [$dateFrom, $dateTo]);

            // Index theo date
            $byDate = [];
            foreach ($rows as $row) {
                $byDate[$row['date']] = (float)$row['revenue'];
            }

            // Gom data theo period
            [$labels, $data] = $this->groupRevenueByPeriod($byDate, $period, $dateFrom, $dateTo);

            $total = array_sum($data);
            $totalMillion = round($total / 1000000, 2);

            return [
                'success' => true,
                'data' => [
                    'labels'  => $labels,
                    'datasets' => [[
                        'label'  => 'Doanh thu (triệu VNĐ)',
                        'data'   => $data,
                        'period' => $period,
                        'total'  => $totalMillion,
                        'currency' => 'VND',
                    ]],
                ],
                'meta' => [
                    'period'       => $period,
                    'date_from'    => $dateFrom,
                    'date_to'      => $dateTo,
                    'generated_at' => date('c'),
                    'cache_hit'    => false,
                ],
            ];
        } catch (\Exception $e) {
            $this->errorHandler->logError('buildRevenueChartData error: ' . $e->getMessage());
            return $this->emptyRevenueData();
        }
    }

    private function groupRevenueByPeriod(array $byDate, string $period, string $dateFrom, string $dateTo): array
    {
        $labels = [];
        $data   = [];

        if ($period === '12months') {
            // Gom theo tháng
            $current = strtotime(date('Y-m-01', strtotime($dateFrom)));
            $end     = strtotime(date('Y-m-01', strtotime($dateTo)));
            while ($current <= $end) {
                $monthKey = date('Y-m', $current);
                $labels[] = 'T' . date('n', $current);
                $monthRevenue = 0;
                foreach ($byDate as $date => $revenue) {
                    if (strpos($date, $monthKey) === 0) {
                        $monthRevenue += $revenue;
                    }
                }
                $data[]  = round($monthRevenue / 1000000, 2);
                $current = strtotime('+1 month', $current);
            }
        } elseif ($period === '7days') {
            // Gom theo ngày
            $current = strtotime($dateFrom);
            $end     = strtotime($dateTo);
            while ($current <= $end) {
                $dateStr  = date('Y-m-d', $current);
                $labels[] = date('d/m', $current);
                $data[]   = round(($byDate[$dateStr] ?? 0) / 1000000, 2);
                $current  = strtotime('+1 day', $current);
            }
        } else {
            // 30days - gom theo tuần
            $current = strtotime($dateFrom);
            $end     = strtotime($dateTo);
            $week    = 1;
            while ($current <= $end) {
                $weekEnd = min(strtotime('+6 days', $current), $end);
                $labels[] = 'Tuần ' . $week;
                $weekRevenue = 0;
                $temp = $current;
                while ($temp <= $weekEnd) {
                    $dateStr = date('Y-m-d', $temp);
                    $weekRevenue += $byDate[$dateStr] ?? 0;
                    $temp = strtotime('+1 day', $temp);
                }
                $data[]  = round($weekRevenue / 1000000, 2);
                $current = strtotime('+7 days', $current);
                $week++;
            }
        }

        return [$labels, $data];
    }

    private function emptyRevenueData(): array
    {
        return [
            'success' => true,
            'data' => [
                'labels'  => ['Không có dữ liệu'],
                'datasets' => [['label' => 'Doanh thu', 'data' => [0]]],
            ],
            'meta' => ['generated_at' => date('c'), 'cache_hit' => false],
        ];
    }

    private function buildTopProductsData(int $limit, string $dateFrom, string $dateTo): array
    {
        try {
            $ordersModel = $this->getModel('OrdersModel');
            if (!$ordersModel) {
                return ['success' => true, 'data' => ['products' => [], 'total_products' => 0]];
            }

            $sql = "
                SELECT
                    p.id, p.name, p.price,
                    COUNT(o.id) as sales_count,
                    SUM(o.total) as revenue
                FROM products p
                JOIN orders o ON p.id = o.product_id
                WHERE o.status = 'completed'
                  AND DATE(o.created_at) >= ?
                  AND DATE(o.created_at) <= ?
                GROUP BY p.id, p.name, p.price
                ORDER BY sales_count DESC
                LIMIT ?
            ";
            $rows = $ordersModel->query($sql, [$dateFrom, $dateTo, $limit]);

            if (empty($rows)) {
                return [
                    'success' => true,
                    'data' => [
                        'products'      => [],
                        'total_products'=> 0,
                        'message'       => 'Chưa có dữ liệu sản phẩm',
                    ],
                ];
            }

            $totalSales   = array_sum(array_column($rows, 'sales_count'));
            $totalRevenue = array_sum(array_column($rows, 'revenue'));

            $products = [];
            foreach ($rows as $row) {
                $percentage = $totalSales > 0 ? round($row['sales_count'] / $totalSales * 100, 1) : 0;
                $products[] = [
                    'id'          => (int)$row['id'],
                    'name'        => $row['name'],
                    'sales_count' => (int)$row['sales_count'],
                    'revenue'     => (float)$row['revenue'],
                    'percentage'  => $percentage,
                ];
            }

            return [
                'success' => true,
                'data' => [
                    'products'      => $products,
                    'total_products'=> count($products),
                    'total_sales'   => (int)$totalSales,
                    'total_revenue' => (float)$totalRevenue,
                ],
                'meta' => ['generated_at' => date('c'), 'cache_hit' => false],
            ];
        } catch (\Exception $e) {
            $this->errorHandler->logError('buildTopProductsData error: ' . $e->getMessage());
            return ['success' => true, 'data' => ['products' => [], 'total_products' => 0]];
        }
    }

    private function buildOrdersStatusData(): array
    {
        try {
            $ordersModel = $this->getModel('OrdersModel');
            if (!$ordersModel) {
                return $this->emptyOrdersStatusData();
            }

            $statusLabels = [
                'completed'  => 'Hoàn thành',
                'processing' => 'Đang xử lý',
                'pending'    => 'Chờ xử lý',
                'cancelled'  => 'Đã hủy',
            ];

            $sql = "SELECT status, COUNT(*) as count FROM orders GROUP BY status";
            $rows = $ordersModel->query($sql);

            $totals = ['completed' => 0, 'processing' => 0, 'pending' => 0, 'cancelled' => 0];
            foreach ($rows as $row) {
                if (isset($totals[$row['status']])) {
                    $totals[$row['status']] = (int)$row['count'];
                }
            }

            $grandTotal = array_sum($totals);

            if ($grandTotal === 0) {
                return $this->emptyOrdersStatusData();
            }

            $dataValues = [
                $grandTotal > 0 ? round($totals['completed']  / $grandTotal * 100, 1) : 0,
                $grandTotal > 0 ? round($totals['processing'] / $grandTotal * 100, 1) : 0,
                $grandTotal > 0 ? round($totals['pending']    / $grandTotal * 100, 1) : 0,
                $grandTotal > 0 ? round($totals['cancelled']  / $grandTotal * 100, 1) : 0,
            ];

            return [
                'success' => true,
                'data' => [
                    'labels'   => array_values($statusLabels),
                    'datasets' => [[
                        'data'            => $dataValues,
                        'backgroundColor' => ['#10B981', '#3B82F6', '#F59E0B', '#EF4444'],
                    ]],
                    'totals'   => $totals,
                    'grand_total' => $grandTotal,
                ],
                'meta' => ['generated_at' => date('c'), 'cache_hit' => false],
            ];
        } catch (\Exception $e) {
            $this->errorHandler->logError('buildOrdersStatusData error: ' . $e->getMessage());
            return $this->emptyOrdersStatusData();
        }
    }

    private function emptyOrdersStatusData(): array
    {
        return [
            'success' => true,
            'data' => [
                'labels'   => ['Chưa có đơn hàng'],
                'datasets' => [['data' => [0], 'backgroundColor' => ['#E5E7EB']]],
                'totals'   => ['completed' => 0, 'processing' => 0, 'pending' => 0, 'cancelled' => 0],
                'grand_total' => 0,
            ],
            'meta' => ['generated_at' => date('c'), 'cache_hit' => false],
        ];
    }

    private function buildNewUsersData(string $period): array
    {
        try {
            $usersModel = $this->getModel('UsersModel');
            if (!$usersModel) {
                return $this->emptyNewUsersData();
            }

            $weeks = 4;
            $labels = [];
            $data   = [];

            for ($i = $weeks - 1; $i >= 0; $i--) {
                $weekStart = date('Y-m-d', strtotime("-{$i} weeks last monday"));
                if ($i === 0) {
                    $weekStart = date('Y-m-d', strtotime('last monday'));
                }
                $weekEnd = date('Y-m-d', strtotime($weekStart . ' +6 days'));

                $sql  = "SELECT COUNT(*) as count FROM users WHERE DATE(created_at) BETWEEN ? AND ?";
                $rows = $usersModel->query($sql, [$weekStart, $weekEnd]);
                $count = (int)($rows[0]['count'] ?? 0);

                $labels[] = 'Tuần ' . ($weeks - $i);
                $data[]   = $count;
            }

            return [
                'success' => true,
                'data' => [
                    'labels'   => $labels,
                    'datasets' => [[
                        'label' => 'Người dùng mới',
                        'data'  => $data,
                        'period'=> $period,
                    ]],
                ],
                'meta' => ['period' => $period, 'generated_at' => date('c'), 'cache_hit' => false],
            ];
        } catch (\Exception $e) {
            $this->errorHandler->logError('buildNewUsersData error: ' . $e->getMessage());
            return $this->emptyNewUsersData();
        }
    }

    private function emptyNewUsersData(): array
    {
        return [
            'success' => true,
            'data' => [
                'labels'   => ['Tuần 1', 'Tuần 2', 'Tuần 3', 'Tuần 4'],
                'datasets' => [['label' => 'Người dùng mới', 'data' => [0, 0, 0, 0]]],
            ],
            'meta' => ['generated_at' => date('c'), 'cache_hit' => false],
        ];
    }

    private function buildDashboardStatistics(): array
    {
        try {
            $productsModel = $this->getModel('ProductsModel');
            $ordersModel   = $this->getModel('OrdersModel');
            $newsModel     = $this->getModel('NewsModel');
            $eventsModel   = $this->getModel('EventsModel');

            $totalProducts = 0;
            if ($productsModel) {
                $rows = $productsModel->query("SELECT COUNT(*) as cnt FROM products WHERE status = 'active'");
                $totalProducts = (int)($rows[0]['cnt'] ?? 0);
            }

            $totalRevenue = 0;
            if ($ordersModel) {
                $rows = $ordersModel->query("SELECT SUM(`total`) as rev FROM orders WHERE status = 'completed'");
                $totalRevenue = (float)($rows[0]['rev'] ?? 0);
            }

            $publishedNews = 0;
            if ($newsModel) {
                $rows = $newsModel->query("SELECT COUNT(*) as cnt FROM news WHERE status = 'published'");
                $publishedNews = (int)($rows[0]['cnt'] ?? 0);
            }

            $upcomingEvents = 0;
            if ($eventsModel) {
                $rows = $eventsModel->query("SELECT COUNT(*) as cnt FROM events WHERE start_date > NOW()");
                $upcomingEvents = (int)($rows[0]['cnt'] ?? 0);
            }

            // Trends: so sánh với kỳ trước
            $trends = $this->calculateTrends($ordersModel, $productsModel, $newsModel);

            return [
                'success' => true,
                'data' => [
                    'total_products'  => $totalProducts,
                    'total_revenue'   => $totalRevenue,
                    'published_news'  => $publishedNews,
                    'upcoming_events' => $upcomingEvents,
                    'trends'          => $trends,
                ],
                'meta' => ['generated_at' => date('c'), 'cache_hit' => false],
            ];
        } catch (\Exception $e) {
            $this->errorHandler->logError('buildDashboardStatistics error: ' . $e->getMessage());
            return [
                'success' => true,
                'data' => [
                    'total_products'  => 0,
                    'total_revenue'   => 0,
                    'published_news'  => 0,
                    'upcoming_events' => 0,
                    'trends'          => [
                        'products' => ['direction' => 'up', 'value' => 0],
                        'revenue'  => ['direction' => 'up', 'value' => 0],
                        'news'     => ['direction' => 'up', 'value' => 0],
                        'events'   => ['direction' => 'up', 'value' => 0],
                    ],
                ],
                'meta' => ['generated_at' => date('c'), 'cache_hit' => false],
            ];
        }
    }

    private function calculateTrends($ordersModel, $productsModel, $newsModel): array
    {
        $trends = [
            'products' => ['direction' => 'up', 'value' => 0],
            'revenue'  => ['direction' => 'up', 'value' => 0],
            'news'     => ['direction' => 'up', 'value' => 0],
            'events'   => ['direction' => 'up', 'value' => 0],
            'users'    => ['direction' => 'up', 'value' => 0],
            'sales'    => ['direction' => 'up', 'value' => 0],
        ];

        try {
            if ($ordersModel) {
                $currentRevenue  = (float)($ordersModel->query("SELECT SUM(`total`) as rev FROM orders WHERE status = 'completed' AND DATE(created_at) >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)")[0]['rev'] ?? 0);
                $previousRevenue = (float)($ordersModel->query("SELECT SUM(`total`) as rev FROM orders WHERE status = 'completed' AND DATE(created_at) BETWEEN DATE_SUB(CURDATE(), INTERVAL 60 DAY) AND DATE_SUB(CURDATE(), INTERVAL 30 DAY)")[0]['rev'] ?? 0);
                if ($previousRevenue > 0) {
                    $pct = round(($currentRevenue - $previousRevenue) / $previousRevenue * 100, 1);
                    $trends['revenue'] = ['direction' => $pct >= 0 ? 'up' : 'down', 'value' => abs($pct)];
                }
            }
        } catch (\Exception $e) {
            // bỏ qua lỗi trend
        }

        return $trends;
    }

    private function buildAdminNotifications(int $limit): array
    {
        try {
            $notifications = [];
            
            // 1. Lấy từ bảng admin_notifications trước
            $notifModel = $this->getModel('BaseModel'); // Dùng generic model
            $sql = "SELECT * FROM admin_notifications WHERE is_read = 0 ORDER BY created_at DESC LIMIT " . (int)$limit;
            $rows = $notifModel->query($sql);
            
            if (!empty($rows)) {
                foreach ($rows as $row) {
                    $notifications[] = [
                        'type'     => $row['type'],
                        'icon'     => $row['icon'],
                        'message'  => $row['message'],
                        'time'     => $row['created_at'],
                        'time_ago' => $this->timeAgo($row['created_at']),
                        'link'     => $row['link']
                    ];
                }
            }

            // 2. Nếu vẫn còn trống, fallback/bổ sung bằng logic tự động (như cũ)
            if (count($notifications) < $limit) {
                $ordersModel   = $this->getModel('OrdersModel');
                $contactsModel = $this->getModel('ContactsModel');

                // Đơn hàng mới (chờ xử lý)
                if ($ordersModel) {
                    $orderRows = $ordersModel->query("SELECT id, created_at FROM orders WHERE status = 'pending' ORDER BY created_at DESC LIMIT 1");
                    if (!empty($orderRows)) {
                        $order = $orderRows[0];
                        $msg = 'Đơn hàng mới #' . str_pad($order['id'], 4, '0', STR_PAD_LEFT);
                        // Tránh trùng lặp nếu bảng admin_notifications đã có
                        if (!$this->hasNotification($notifications, $msg)) {
                            $notifications[] = [
                                'type'    => 'order',
                                'icon'    => 'fas fa-shopping-cart text-success',
                                'message' => $msg,
                                'time'    => $order['created_at'],
                                'time_ago'=> $this->timeAgo($order['created_at']),
                            ];
                        }
                    }
                }
            }

            return [
                'success' => true,
                'data' => [
                    'notifications' => array_slice($notifications, 0, $limit),
                    'unread_count'  => count($notifications), 
                ],
            ];
        } catch (\Exception $e) {
            $this->errorHandler->logError('buildAdminNotifications error: ' . $e->getMessage());
            return ['success' => true, 'data' => ['notifications' => [], 'unread_count' => 0]];
        }
    }

    private function hasNotification(array $list, string $message): bool
    {
        foreach ($list as $item) {
            if ($item['message'] === $message) return true;
        }
        return false;
    }

    private function timeAgo(string $datetime): string
    {
        $time = strtotime($datetime);
        $diff = time() - $time;

        if ($diff < 60)         return 'Vừa xong';
        if ($diff < 3600)       return floor($diff / 60) . ' phút trước';
        if ($diff < 86400)      return floor($diff / 3600) . ' giờ trước';
        if ($diff < 604800)     return floor($diff / 86400) . ' ngày trước';
        return date('d/m/Y', $time);
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
            $totalResult = $productsModel->query($countSql, $bindings);
            $total = $totalResult[0]['total'] ?? 0;

            // Get products with pagination
            $offset = ($page - 1) * $perPage;
            $productsSql = "SELECT * FROM products {$whereClause} ORDER BY created_at DESC LIMIT {$perPage} OFFSET {$offset}";
            $products = $productsModel->query($productsSql, $bindings);

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

    public function createProduct(array $data): bool
    {
        try {
            $productsModel = $this->getModel('ProductsModel');
            if (!$productsModel) {
                return false;
            }
            $result = $productsModel->create($data);
            if ($result !== false) {
                $this->flushDashboardCache();
            }
            return $result !== false;
        } catch (\Exception $e) {
            return $this->handleError($e, ['method' => 'createProduct']) !== null;
        }
    }

    public function updateProduct(int $productId, array $data): bool
    {
        try {
            $productsModel = $this->getModel('ProductsModel');
            if (!$productsModel) {
                return false;
            }
            $result = $productsModel->update($productId, $data);
            if ($result !== false) {
                $this->flushDashboardCache();
            }
            return $result !== false;
        } catch (\Exception $e) {
            return $this->handleError($e, ['method' => 'updateProduct', 'product_id' => $productId]) !== null;
        }
    }

    public function deleteProduct(int $productId): bool
    {
        try {
            $productsModel = $this->getModel('ProductsModel');
            if (!$productsModel) {
                return false;
            }
            $result = $productsModel->delete($productId);
            if ($result !== false) {
                $this->flushDashboardCache();
            }
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
            $totalResult = $usersModel->query($countSql, $bindings);
            $total = $totalResult[0]['total'] ?? 0;

            $offset = ($page - 1) * $perPage;
            $usersSql = "SELECT * FROM users {$whereClause} ORDER BY created_at DESC LIMIT {$perPage} OFFSET {$offset}";
            $users = $usersModel->query($usersSql, $bindings);

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
            $totalResult = $categoriesModel->query($countSql, $bindings);
            $total = $totalResult[0]['total'] ?? 0;

            $offset = ($page - 1) * $perPage;
            $categoriesSql = "SELECT * FROM categories {$whereClause} ORDER BY created_at DESC LIMIT {$perPage} OFFSET {$offset}";
            $categories = $categoriesModel->query($categoriesSql, $bindings);

            // Get product counts
            if ($productsModel) {
                foreach ($categories as &$category) {
                    $productCountSql = "SELECT COUNT(*) as count FROM products WHERE category_id = ?";
                    $countResult = $productsModel->query($productCountSql, [$category['id']]);
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
                $countResult = $productsModel->query($productCountSql, [$categoryId]);
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

    public function createCategory(array $data): bool
    {
        try {
            $categoriesModel = $this->getModel('CategoriesModel');
            if (!$categoriesModel) {
                return false;
            }
            $result = $categoriesModel->create($data);
            if ($result !== false) {
                $this->flushDashboardCache();
            }
            return $result !== false;
        } catch (\Exception $e) {
            return $this->handleError($e, ['method' => 'createCategory']) !== null;
        }
    }

    public function updateCategory(int $categoryId, array $data): bool
    {
        try {
            $categoriesModel = $this->getModel('CategoriesModel');
            if (!$categoriesModel) {
                return false;
            }
            $result = $categoriesModel->update($categoryId, $data);
            if ($result !== false) {
                $this->flushDashboardCache();
            }
            return $result !== false;
        } catch (\Exception $e) {
            return $this->handleError($e, ['method' => 'updateCategory', 'category_id' => $categoryId]) !== null;
        }
    }

    public function deleteCategory(int $categoryId): bool
    {
        try {
            $categoriesModel = $this->getModel('CategoriesModel');
            if (!$categoriesModel) {
                return false;
            }
            $result = $categoriesModel->delete($categoryId);
            if ($result !== false) {
                $this->flushDashboardCache();
            }
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
            $totalResult = $newsModel->query($countSql, $bindings);
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
            $news = $newsModel->query($newsSql, $bindings);

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

    public function createNews(array $data): bool
    {
        $result = $this->callModelMethod('NewsModel', 'create', [$data], null);
        if ($result) {
            $this->flushDashboardCache();
        }
        return $result !== false;
    }

    public function updateNews(int $newsId, array $data): bool
    {
        $result = $this->callModelMethod('NewsModel', 'update', [$newsId, $data], null);
        if ($result) {
            $this->flushDashboardCache();
        }
        return $result !== false;
    }

    public function deleteNews(int $newsId): bool
    {
        $result = $this->callModelMethod('NewsModel', 'delete', [$newsId], null);
        if ($result) {
            $this->flushDashboardCache();
        }
        return $result !== false;
    }

    private function getNewsStatistics($newsModel): array
    {
        try {
            $stats = [
                'total' => 0, 'published' => 0, 'draft' => 0, 'archived' => 0,
                'today' => 0, 'this_month' => 0,
            ];

            $statusSql = "SELECT status, COUNT(*) as count FROM news GROUP BY status";
            $statusResults = $newsModel->query($statusSql);
            foreach ($statusResults as $result) {
                $stats[$result['status']] = (int)$result['count'];
                $stats['total'] += (int)$result['count'];
            }

            $todaySql = "SELECT COUNT(*) as count FROM news WHERE DATE(created_at) = CURDATE()";
            $todayResult = $newsModel->query($todaySql);
            $stats['today'] = $todayResult[0]['count'] ?? 0;

            $monthSql = "SELECT COUNT(*) as count FROM news WHERE YEAR(created_at) = YEAR(NOW()) AND MONTH(created_at) = MONTH(NOW())";
            $monthResult = $newsModel->query($monthSql);
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
            $totalResult = $ordersModel->query($countSql, $bindings);
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
            $orders = $ordersModel->query($ordersSql, $bindings);

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

    public function updateOrder(int $orderId, array $data): bool
    {
        try {
            $ordersModel = $this->getModel('OrdersModel');
            if (!$ordersModel) {
                return false;
            }
            $result = $ordersModel->update($orderId, $data);
            if ($result !== false) {
                $this->flushDashboardCache();
            }
            return $result !== false;
        } catch (\Exception $e) {
            return $this->handleError($e, ['method' => 'updateOrder', 'order_id' => $orderId]) !== null;
        }
    }

    public function deleteOrder(int $orderId): bool
    {
        try {
            $ordersModel = $this->getModel('OrdersModel');
            if (!$ordersModel) {
                return false;
            }
            $result = $ordersModel->delete($orderId);
            if ($result !== false) {
                $this->flushDashboardCache();
            }
            return $result !== false;
        } catch (\Exception $e) {
            return $this->handleError($e, ['method' => 'deleteOrder', 'order_id' => $orderId]) !== null;
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
            $statusResults = $ordersModel->query($statusSql);
            foreach ($statusResults as $result) {
                $stats[$result['status']] = (int)$result['count'];
                $stats['total'] += (int)$result['count'];
            }

            $todaySql = "SELECT COUNT(*) as count FROM orders WHERE DATE(created_at) = CURDATE()";
            $todayResult = $ordersModel->query($todaySql);
            $stats['today'] = $todayResult[0]['count'] ?? 0;

            $monthSql = "SELECT COUNT(*) as count FROM orders WHERE YEAR(created_at) = YEAR(NOW()) AND MONTH(created_at) = MONTH(NOW())";
            $monthResult = $ordersModel->query($monthSql);
            $stats['this_month'] = $monthResult[0]['count'] ?? 0;

            $revenueSql = "SELECT SUM(total) as revenue FROM orders WHERE status = 'completed'";
            $revenueResult = $ordersModel->query($revenueSql);
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
            $totalResult = $settingsModel->query($countSql, $bindings);
            $total = $totalResult[0]['total'] ?? 0;

            $offset = ($page - 1) * $perPage;
            $settingsSql = "SELECT * FROM settings {$whereClause} ORDER BY `key` LIMIT {$perPage} OFFSET {$offset}";
            $settings = $settingsModel->query($settingsSql, $bindings);

            $typesSql = "SELECT DISTINCT type FROM settings ORDER BY type";
            $typesResult = $settingsModel->query($typesSql);
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
            $totalResult = $contactsModel->query($countSql, $bindings);
            $total = $totalResult[0]['total'] ?? 0;

            $offset = ($page - 1) * $perPage;
            $contactsSql = "SELECT * FROM contacts {$whereClause} ORDER BY created_at DESC LIMIT {$perPage} OFFSET {$offset}";
            $contacts = $contactsModel->query($contactsSql, $bindings);

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
            if ($result !== false) {
                $this->flushDashboardCache();
                // Task 9.1: Invalidate notifications cache specifically too if needed
                $this->cache->delete('dashboard:notifications*');
            }
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
            $statusResults = $contactsModel->query($statusSql);
            foreach ($statusResults as $result) {
                $stats[$result['status']] = (int)$result['count'];
                $stats['total'] += (int)$result['count'];
            }

            $todaySql = "SELECT COUNT(*) as count FROM contacts WHERE DATE(created_at) = CURDATE()";
            $todayResult = $contactsModel->query($todaySql);
            $stats['today'] = $todayResult[0]['count'] ?? 0;

            $monthSql = "SELECT COUNT(*) as count FROM contacts WHERE YEAR(created_at) = YEAR(NOW()) AND MONTH(created_at) = MONTH(NOW())";
            $monthResult = $contactsModel->query($monthSql);
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
            $totalResult = $affiliateModel->query($countSql, $bindings);
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
            $affiliates = $affiliateModel->query($affiliatesSql, $bindings);

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
            $result = $affiliateModel->query($affiliateSql, [$affiliateId]);
            $affiliate = $result ? $result[0] : null;

            if (!$affiliate) {
                return ['affiliate' => null, 'orders' => [], 'performance_data' => ['labels' => [], 'sales' => [], 'commission' => []]];
            }

            $orders = [];
            if ($ordersModel) {
                $ordersSql = "SELECT * FROM orders WHERE affiliate_id = ? ORDER BY created_at DESC LIMIT 10";
                $orders = $ordersModel->query($ordersSql, [$affiliateId]);
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
            $statusResults = $affiliateModel->query($statusSql);
            foreach ($statusResults as $result) {
                $stats[$result['status']] = (int)$result['count'];
                $stats['total'] += (int)$result['count'];
            }

            $salesSql = "SELECT SUM(total_sales) as sales, SUM(total_commission) as commission FROM affiliates";
            $salesResult = $affiliateModel->query($salesSql);
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
            
            $result = $affiliateModel->query($sql, $bindings);
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
            $users = $usersModel->query($sql);

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
            $totalResult = $eventsModel->query($countSql, $bindings);
            $total = $totalResult[0]['total'] ?? 0;

            $offset = ($page - 1) * $perPage;
            $eventsSql = "SELECT * FROM events {$whereClause} ORDER BY start_date DESC LIMIT {$perPage} OFFSET {$offset}";
            $events = $eventsModel->query($eventsSql, $bindings);

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

    public function createEvent(array $data): bool
    {
        $result = $this->callModelMethod('EventsModel', 'create', [$data], null);
        if ($result) {
            $this->flushDashboardCache();
        }
        return $result !== false;
    }

    public function updateEvent(int $eventId, array $data): bool
    {
        $result = $this->callModelMethod('EventsModel', 'update', [$eventId, $data], null);
        if ($result) {
            $this->flushDashboardCache();
        }
        return $result !== false;
    }

    public function deleteEvent(int $eventId): bool
    {
        $result = $this->callModelMethod('EventsModel', 'delete', [$eventId], null);
        if ($result) {
            $this->flushDashboardCache();
        }
        return $result !== false;
    }

    private function getEventStatistics($eventsModel): array
    {
        try {
            $stats = [
                'total' => 0, 'upcoming' => 0, 'ongoing' => 0, 'completed' => 0, 'cancelled' => 0,
                'this_month' => 0, 'total_participants' => 0,
            ];

            $statusSql = "SELECT status, COUNT(*) as count FROM events GROUP BY status";
            $statusResults = $eventsModel->query($statusSql);
            foreach ($statusResults as $result) {
                $stats[$result['status']] = (int)$result['count'];
                $stats['total'] += (int)$result['count'];
            }

            $monthSql = "SELECT COUNT(*) as count FROM events WHERE YEAR(start_date) = YEAR(NOW()) AND MONTH(start_date) = MONTH(NOW())";
            $monthResult = $eventsModel->query($monthSql);
            $stats['this_month'] = $monthResult[0]['count'] ?? 0;

            $participantsSql = "SELECT SUM(current_participants) as total FROM events";
            $participantsResult = $eventsModel->query($participantsSql);
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
            $orders = $ordersModel->query($ordersSql, $bindings);

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

    public function globalSearch(string $query, int $limit = 5): array
    {
        try {
            $results = [];
            $searchTerm = "%{$query}%";

            // 1. Tìm Sản phẩm
            $products = $this->getModel('ProductsModel')->query(
                "SELECT id, name, price, image FROM products WHERE name LIKE ? OR description LIKE ? LIMIT ?",
                [$searchTerm, $searchTerm, $limit]
            );
            foreach ($products as $p) {
                $results[] = [
                    'type'  => 'product',
                    'title' => $p['name'],
                    'info'  => number_format($p['price'], 0, ',', '.') . ' VNĐ',
                    'link'  => "?page=admin&module=products&action=view&id={$p['id']}",
                    'icon'  => 'fas fa-box'
                ];
            }

            // 2. Tìm Đơn hàng (theo ID hoặc Email)
            $orders = $this->getModel('OrdersModel')->query(
                "SELECT o.id, o.total, u.name as user_name 
                 FROM orders o 
                 LEFT JOIN users u ON o.user_id = u.id 
                 WHERE o.id LIKE ? OR u.name LIKE ? OR u.email LIKE ? LIMIT ?",
                [$searchTerm, $searchTerm, $searchTerm, $limit]
            );
            foreach ($orders as $o) {
                $results[] = [
                    'type'  => 'order',
                    'title' => '#' . str_pad($o['id'], 6, '0', STR_PAD_LEFT),
                    'info'  => ($o['user_name'] ?? 'Khách lẻ') . ' - ' . number_format($o['total'], 0, ',', '.') . ' VNĐ',
                    'link'  => "?page=admin&module=orders&action=view&id={$o['id']}",
                    'icon'  => 'fas fa-shopping-cart'
                ];
            }

            // 3. Tìm Tin tức
            $news = $this->getModel('NewsModel')->query(
                "SELECT id, title FROM news WHERE title LIKE ? OR excerpt LIKE ? LIMIT ?",
                [$searchTerm, $searchTerm, $limit]
            );
            foreach ($news as $n) {
                $results[] = [
                    'type'  => 'news',
                    'title' => $n['title'],
                    'info'  => 'Tin tức',
                    'link'  => "?page=admin&module=news&action=edit&id={$n['id']}",
                    'icon'  => 'fas fa-newspaper'
                ];
            }

            return ['success' => true, 'data' => $results];
        } catch (\Exception $e) {
            return $this->handleError($e, ['method' => 'globalSearch', 'query' => $query]);
        }
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
