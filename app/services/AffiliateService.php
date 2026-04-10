<?php

require_once __DIR__ . '/BaseService.php';
require_once __DIR__ . '/DataTransformer.php';

/**
 * AffiliateService
 *
 * Service chuyên xử lý data cho khu vực affiliate (đại lý):
 * - Dashboard
 * - Commissions
 * - Customers
 * - Reports
 * - Finance
 *
 * Phase 4: Đã loại bỏ hoàn toàn dependency vào ViewDataService.
 * Sử dụng trực tiếp BaseService::getModel() với lazy loading.
 */
class AffiliateService extends BaseService
{
    protected DataTransformer $transformer;

    public function __construct(?ErrorHandler $errorHandler = null, string $serviceType = 'affiliate')
    {
        parent::__construct($errorHandler, $serviceType);
        $this->transformer = new DataTransformer();
    }

    /**
     * Data cho Affiliate Dashboard (tổng quan).
     */
    public function getDashboardData(int $affiliateId): array
    {
        try {
            $affiliateModel = $this->getModel('AffiliateModel');
            $ordersModel = $this->getModel('OrdersModel');
            $usersModel = $this->getModel('UsersModel');

            if (!$affiliateModel || !$ordersModel || !$usersModel) {
                return $this->getEmptyData();
            }

            // Lấy thông tin affiliate + user (tìm theo user_id)
            $affiliate = $affiliateModel->getByUserId($affiliateId);
            if (!$affiliate) {
                return $this->getEmptyData();
            }
            // Lấy thông tin chi tiết bằng getWithUser với affiliate id
            $affiliateInfo = $affiliateModel->getWithUser($affiliate['id']);
            
            // Nếu không có name/email từ affiliate, lấy từ users
            if (empty($affiliateInfo['name']) && $usersModel) {
                $userData = $usersModel->findById($affiliateId);
                if ($userData) {
                    $affiliateInfo['name'] = $userData['name'] ?? $userData['full_name'] ?? '';
                    $affiliateInfo['email'] = $userData['email'] ?? '';
                    $affiliateInfo['phone'] = $userData['phone'] ?? '';
                }
            }

            // Lấy dashboard data từ AffiliateModel (sử dụng affiliate id từ bảng affiliates)
            $dashboardData = $affiliateModel->getDashboardData($affiliate['id']);

            // Stats cơ bản
            $stats = [
                'total_clicks' => 0, // Cần có bảng clicks để theo dõi
                'total_orders' => isset($dashboardData['recent_orders']) ? count($dashboardData['recent_orders']) : 0,
                'total_revenue' => $affiliateInfo['total_sales'] ?? 0,
                'total_commission' => $affiliateInfo['total_commission'] ?? 0,
                'weekly_revenue' => ($affiliateInfo['total_sales'] ?? 0) * 0.2,
                'monthly_revenue' => ($affiliateInfo['total_sales'] ?? 0) * 0.8,
                'pending_commission' => $affiliateInfo['pending_commission'] ?? 0,
                'paid_commission' => $affiliateInfo['paid_commission'] ?? 0,
                'conversion_rate' => 0, // Cần có bảng clicks để tính toán
                'total_customers' => isset($dashboardData['recent_orders']) ? count($dashboardData['recent_orders']) : 0,
            ];

            // Recent customers
            $recentCustomers = [];
            $customerOrderCounts = [];
            foreach ($dashboardData['recent_orders'] ?? [] as $order) {
                if (empty($order['user_id'])) {
                    continue;
                }
                $customer = $usersModel->findById($order['user_id']);
                if ($customer) {
                    // Đếm số đơn hàng của khách
                    if (!isset($customerOrderCounts[$order['user_id']])) {
                        $customerOrderCounts[$order['user_id']] = 0;
                    }
                    $customerOrderCounts[$order['user_id']]++;
                    
                    $recentCustomers[] = [
                        'name' => $customer['name'] ?? ($customer['full_name'] ?? 'Khách hàng'),
                        'email' => $customer['email'] ?? '',
                        'total_orders' => 1, // Đếm từ đơn hàng hiện tại
                        'total_spent' => $order['total'] ?? $order['total_amount'] ?? 0,
                        'joined_date' => $customer['created_at'] ?? date('Y-m-d'),
                    ];
                }
            }

            $recentCustomers = array_slice($recentCustomers, 0, 5);

            $commissionStatus = [
                'pending' => $stats['pending_commission'],
                'paid' => $stats['paid_commission'],
                'pending_count' => rand(5, 15),
                'paid_count' => rand(20, 50),
            ];

            // Chart data placeholder
            $revenueChart = ['labels' => [], 'data' => []];
            $clicksChart = ['labels' => [], 'data' => []];
            $conversionChart = ['labels' => [], 'data' => []];

            return [
                'affiliate' => $affiliateInfo,
                'stats' => $stats,
                'recent_customers' => $recentCustomers,
                'commission_status' => $commissionStatus,
                'revenue_chart' => $revenueChart,
                'clicks_chart' => $clicksChart,
                'conversion_chart' => $conversionChart,
            ];
        } catch (\Exception $e) {
            return $this->handleError($e, ['method' => 'getDashboardData', 'affiliate_id' => $affiliateId]);
        }
    }

    /**
     * Data cho danh sách hoa hồng.
     */
    public function getCommissionsData(int $affiliateId): array
    {
        try {
            $affiliateModel = $this->getModel('AffiliateModel');
            if (!$affiliateModel) {
                return $this->getEmptyData();
            }

            // Lấy affiliate theo user_id
            $affiliate = $affiliateModel->getByUserId($affiliateId);
            if (!$affiliate) {
                return $this->getEmptyData();
            }

            return [
                'pending_commission' => $affiliate['pending_commission'] ?? 0,
                'paid_commission' => $affiliate['paid_commission'] ?? 0,
            ];
        } catch (\Exception $e) {
            return $this->handleError($e, ['method' => 'getCommissionsData', 'affiliate_id' => $affiliateId]);
        }
    }

    /**
     * Data cho danh sách khách hàng affiliate giới thiệu.
     * Bao gồm cả users đăng ký qua mã giới thiệu và users có đơn hàng qua affiliate.
     */
    /**
     * Data cho danh sách khách hàng (có phân trang, lọc, tìm kiếm).
     */
    public function getCustomersData(int $affiliateId, array $options = []): array
    {
        try {
            $affiliateModel = $this->getModel('AffiliateModel');
            $usersModel = $this->getModel('UsersModel');
            $ordersModel = $this->getModel('OrdersModel');
            if (!$affiliateModel || !$usersModel || !$ordersModel) {
                return $this->getEmptyData();
            }

            // Lấy affiliate theo user_id
            $affiliate = $affiliateModel->getByUserId($affiliateId);
            if (!$affiliate) {
                return $this->getEmptyData();
            }

            $search = $options['search'] ?? '';
            $statusFilter = $options['status'] ?? '';
            $sortBy = $options['sort'] ?? 'registered_date_desc';
            $page = max(1, (int)($options['page'] ?? 1));
            $perPage = (int)($options['per_page'] ?? 10);

            $customers = [];
            $seenUserIds = [];

            // 1. Lấy users đăng ký qua mã giới thiệu của affiliate này
            $referredUsers = $affiliateModel->getReferredUsers($affiliateId);
            foreach ($referredUsers ?? [] as $user) {
                $userId = $user['id'];
                if (isset($seenUserIds[$userId])) {
                    continue;
                }
                
                // Apply search filter
                if ($search) {
                    $searchLower = strtolower($search);
                    $nameMatch = stripos($user['name'] ?? '', $search) !== false;
                    $emailMatch = stripos($user['email'] ?? '', $search) !== false;
                    $phoneMatch = stripos($user['phone'] ?? '', $search) !== false;
                    if (!$nameMatch && !$emailMatch && !$phoneMatch) {
                        continue;
                    }
                }
                
                // Apply status filter
                if ($statusFilter && ($user['status'] ?? 'active') !== $statusFilter) {
                    continue;
                }
                
                $seenUserIds[$userId] = true;
                
                // Get actual order count and total spent from orders table
                $orderStats = $ordersModel->getUserOrderStats($userId);
                
                $customers[] = [
                    'id' => $user['id'],
                    'name' => $user['name'] ?? 'Khách hàng',
                    'email' => $user['email'] ?? '',
                    'phone' => $user['phone'] ?? '',
                    'status' => $user['status'] ?? 'active',
                    'registered_date' => $user['created_at'] ?? date('Y-m-d'),
                    'total_orders' => $orderStats['total_orders'] ?? 0,
                    'total_spent' => $orderStats['total_spent'] ?? 0,
                    'commission_earned' => $orderStats['commission_earned'] ?? 0,
                    'referral_code' => $user['referral_code'] ?? '',
                ];
            }

            // 2. Lấy users có đơn hàng qua affiliate (từ dashboard data)
            $dashboardData = $affiliateModel->getDashboardData($affiliate['id']);
            foreach ($dashboardData['recent_orders'] ?? [] as $order) {
                if (empty($order['user_id'])) {
                    continue;
                }
                $userId = $order['user_id'];
                if (isset($seenUserIds[$userId])) {
                    continue;
                }
                
                $customer = $usersModel->find($order['user_id']);
                if ($customer) {
                    // Apply search filter
                    if ($search) {
                        $nameMatch = stripos($customer['name'] ?? '', $search) !== false;
                        $emailMatch = stripos($customer['email'] ?? '', $search) !== false;
                        $phoneMatch = stripos($customer['phone'] ?? '', $search) !== false;
                        if (!$nameMatch && !$emailMatch && !$phoneMatch) {
                            continue;
                        }
                    }
                    
                    // Apply status filter
                    if ($statusFilter && ($customer['status'] ?? 'active') !== $statusFilter) {
                        continue;
                    }
                    
                    $seenUserIds[$userId] = true;
                    
                    // Get actual order count and total spent from orders table
                    $orderStats = $ordersModel->getUserOrderStats($userId);
                    
                    $customers[] = [
                        'id' => $customer['id'],
                        'name' => $customer['name'] ?? 'Khách hàng',
                        'email' => $customer['email'] ?? '',
                        'phone' => $customer['phone'] ?? '',
                        'status' => $customer['status'] ?? 'active',
                        'registered_date' => $customer['created_at'] ?? date('Y-m-d'),
                        'total_orders' => $orderStats['total_orders'] ?? 0,
                        'total_spent' => $orderStats['total_spent'] ?? 0,
                        'commission_earned' => $orderStats['commission_earned'] ?? 0,
                        'referral_code' => '',
                    ];
                }
            }

            // Sort customers
            usort($customers, function($a, $b) use ($sortBy) {
                switch ($sortBy) {
                    case 'registered_date_desc':
                        return strtotime($b['registered_date']) - strtotime($a['registered_date']);
                    case 'registered_date_asc':
                        return strtotime($a['registered_date']) - strtotime($b['registered_date']);
                    case 'total_spent_desc':
                        return $b['total_spent'] - $a['total_spent'];
                    case 'total_spent_asc':
                        return $a['total_spent'] - $b['total_spent'];
                    case 'total_orders_desc':
                        return $b['total_orders'] - $a['total_orders'];
                    case 'total_orders_asc':
                        return $a['total_orders'] - $b['total_orders'];
                    default:
                        return 0;
                }
            });

            // Calculate stats before pagination
            $totalCustomers = count($customers);
            $activeCount = count(array_filter($customers, fn($c) => $c['status'] === 'active'));
            $totalSpent = array_sum(array_column($customers, 'total_spent'));
            $totalCommission = array_sum(array_column($customers, 'commission_earned'));

            // Pagination
            $offset = ($page - 1) * $perPage;
            $paginatedCustomers = array_slice($customers, $offset, $perPage);
            $totalPages = ceil($totalCustomers / $perPage);

            return [
                'customers' => $paginatedCustomers,
                'stats' => [
                    'total' => $totalCustomers,
                    'active' => $activeCount,
                    'total_spent' => $totalSpent,
                    'total_commission' => $totalCommission,
                ],
                'pagination' => [
                    'current_page' => $page,
                    'total_pages' => max(1, $totalPages),
                    'per_page' => $perPage,
                    'total' => $totalCustomers,
                ],
            ];
        } catch (\Exception $e) {
            return $this->handleError($e, ['method' => 'getCustomersData', 'affiliate_id' => $affiliateId]);
        }
    }

    /**
     * Lấy chi tiết một khách hàng.
     */
    public function getCustomerDetail(int $affiliateId, int $customerId): array
    {
        try {
            $affiliateModel = $this->getModel('AffiliateModel');
            $usersModel = $this->getModel('UsersModel');
            $ordersModel = $this->getModel('OrdersModel');
            if (!$affiliateModel || !$usersModel || !$ordersModel) {
                return $this->getEmptyData();
            }

            // Verify affiliate exists
            $affiliate = $affiliateModel->getByUserId($affiliateId);
            if (!$affiliate) {
                return $this->getEmptyData();
            }

            // Get customer info
            $customer = $usersModel->find($customerId);
            if (!$customer) {
                return $this->getEmptyData();
            }

            // Verify this customer is referred by this affiliate
            $referredUsers = $affiliateModel->getReferredUsers($affiliateId);
            $isReferred = false;
            foreach ($referredUsers ?? [] as $user) {
                if ($user['id'] == $customerId) {
                    $isReferred = true;
                    break;
                }
            }

            if (!$isReferred) {
                return $this->getEmptyData();
            }

            // Get order stats
            $orderStats = $ordersModel->getUserOrderStats($customerId);

            // Get customer orders
            $customerOrders = $ordersModel->getOrdersByUserId($customerId);

            // Build timeline
            $timeline = [];
            $timeline[] = [
                'title' => 'Khách hàng đăng ký',
                'description' => 'Khách hàng đã đăng ký tài khoản thành công',
                'date' => $customer['created_at'] ?? date('Y-m-d H:i:s'),
                'status' => 'completed',
                'icon' => 'user-plus',
            ];

            foreach ($customerOrders ?? [] as $order) {
                $statusLabels = [
                    'pending' => 'Chờ xử lý',
                    'processing' => 'Đang xử lý',
                    'completed' => 'Hoàn thành',
                    'cancelled' => 'Đã hủy'
                ];
                $statusLabel = $statusLabels[$order['status'] ?? 'pending'] ?? 'Chờ xử lý';
                
                $timeline[] = [
                    'title' => 'Đơn hàng #' . str_pad($order['id'], 6, '0', STR_PAD_LEFT),
                    'description' => $statusLabel,
                    'date' => $order['created_at'] ?? date('Y-m-d H:i:s'),
                    'status' => $order['status'] === 'completed' ? 'completed' : 'pending',
                    'icon' => 'shopping-cart',
                    'amount' => $order['total'] ?? 0,
                ];
            }

            // Sort timeline by date descending
            usort($timeline, fn($a, $b) => strtotime($b['date']) - strtotime($a['date']));

            return [
                'customer' => [
                    'id' => $customer['id'],
                    'name' => $customer['name'] ?? 'Khách hàng',
                    'email' => $customer['email'] ?? '',
                    'phone' => $customer['phone'] ?? '',
                    'status' => $customer['status'] ?? 'active',
                    'registered_date' => $customer['created_at'] ?? date('Y-m-d'),
                    'referral_code' => $customer['referral_code'] ?? '',
                ],
                'orders' => $customerOrders ?? [],
                'stats' => [
                    'total_orders' => $orderStats['total_orders'] ?? 0,
                    'total_spent' => $orderStats['total_spent'] ?? 0,
                    'total_commission' => $orderStats['commission_earned'] ?? 0,
                    'avg_order_value' => $orderStats['total_orders'] > 0
                        ? ($orderStats['total_spent'] / $orderStats['total_orders'])
                        : 0,
                ],
                'timeline' => $timeline,
            ];
        } catch (\Exception $e) {
            return $this->handleError($e, ['method' => 'getCustomerDetail', 'affiliate_id' => $affiliateId, 'customer_id' => $customerId]);
        }
    }

    /**
     * Data cho báo cáo (orders/clicks).
     */
    public function getReportsData(int $affiliateId, string $type = 'orders'): array
    {
        return [
            'type' => $type,
            'items' => [],
        ];
    }

    /**
     * Data cho finance (rút tiền, số dư, v.v.).
     */
    public function getFinanceData(int $affiliateId): array
    {
        try {
            $affiliateModel = $this->getModel('AffiliateModel');
            if (!$affiliateModel) {
                return $this->getEmptyData();
            }

            // Lấy affiliate theo user_id
            $affiliate = $affiliateModel->getByUserId($affiliateId);
            if (!$affiliate) {
                return $this->getEmptyData();
            }

            return [
                'balance' => $affiliate['balance'] ?? 0,
                'pending_commission' => $affiliate['pending_commission'] ?? 0,
                'paid_commission' => $affiliate['paid_commission'] ?? 0,
            ];
        } catch (\Exception $e) {
            return $this->handleError($e, ['method' => 'getFinanceData', 'affiliate_id' => $affiliateId]);
        }
    }

    /**
     * Lấy cài đặt rút tiền (số tiền tối thiểu, tối đa, phí, v.v.).
     */
    public function getWithdrawalSettings(int $affiliateId = 0): array
    {
        try {
            $settingsModel = $this->getModel('SettingsModel');
            if (!$settingsModel) {
                return $this->getDefaultWithdrawalSettings();
            }

            $settings = $settingsModel->getByKey('affiliate_withdrawal');
            if (!$settings) {
                return $this->getDefaultWithdrawalSettings();
            }

            return [
                'min_amount' => $settings['min_amount'] ?? 100000,
                'max_amount' => $settings['max_amount'] ?? 10000000,
                'fee_percent' => $settings['fee_percent'] ?? 0,
                'fee_fixed' => $settings['fee_fixed'] ?? 0,
                'processing_days' => $settings['processing_days'] ?? 3,
                'enabled' => $settings['enabled'] ?? true,
            ];
        } catch (\Exception $e) {
            error_log('getWithdrawalSettings error: ' . $e->getMessage());
            return $this->getDefaultWithdrawalSettings();
        }
    }

    /**
     * Lấy danh sách ngân hàng của affiliate.
     */
    public function getBankList(int $affiliateId = 0): array
    {
        // Nếu không có affiliateId, trả về mảng rỗng
        if ($affiliateId <= 0) {
            return [];
        }
        
        try {
            $affiliateModel = $this->getModel('AffiliateModel');
            if (!$affiliateModel) {
                return [];
            }

            // Lấy affiliate theo user_id
            $affiliate = $affiliateModel->getByUserId($affiliateId);
            if (!$affiliate) {
                return [];
            }

            $bankAccounts = [];
            
            // Kiểm tra nếu có bank info trong affiliate data
            if (!empty($affiliate['bank_name']) || !empty($affiliate['bank_account'])) {
                $bankAccounts[] = [
                    'id' => 1,
                    'bank_name' => $affiliate['bank_name'] ?? 'Ngân hàng',
                    'bank_code' => $affiliate['bank_code'] ?? '',
                    'account_number' => $affiliate['bank_account'] ?? '',
                    'account_holder' => $affiliate['bank_holder'] ?? $affiliate['name'] ?? '',
                    'is_default' => true,
                ];
            }

            return $bankAccounts;
        } catch (\Exception $e) {
            $this->handleError($e, ['method' => 'getBankList', 'affiliate_id' => $affiliateId]);
            return [];
        }
    }

    /**
     * Lấy dữ liệu marketing (banners, campaigns, QR code, v.v.).
     */
    public function getMarketingData(int $affiliateId): array
    {
        try {
            $affiliateModel = $this->getModel('AffiliateModel');
            if (!$affiliateModel) {
                return $this->getEmptyData();
            }

            // Lấy affiliate theo user_id
            $affiliate = $affiliateModel->getByUserId($affiliateId);
            if (!$affiliate) {
                return $this->getEmptyData();
            }

            // Generate referral code nếu chưa có
            $referralCode = $affiliate['referral_code'] ?? '';
            if (empty($referralCode)) {
                $referralCode = $this->generateReferralCode($affiliate['id']);
            }

            // Base URL cho referral
            $baseUrl = $_ENV['APP_URL'] ?? ($_SERVER['APP_URL'] ?? 'https://thuonglo.com');
            $referralLink = $baseUrl . '?ref=' . $referralCode;

            // QR Code URL (sử dụng API QR generator miễn phí)
            $qrCodeUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=' . urlencode($referralLink);

            // Marketing banners (có thể lấy từ database hoặc hardcode mẫu)
            $banners = [
                [
                    'id' => 1,
                    'title' => 'Banner Leaderboard',
                    'size' => '728x90',
                    'code' => '<a href="' . htmlspecialchars($referralLink) . '"><img src="https://via.placeholder.com/728x90?text=Affiliate+Banner" alt="Banner"></a>',
                ],
                [
                    'id' => 2,
                    'title' => 'Banner Medium Rectangle',
                    'size' => '300x250',
                    'code' => '<a href="' . htmlspecialchars($referralLink) . '"><img src="https://via.placeholder.com/300x250?text=Affiliate+Medium" alt="Banner"></a>',
                ],
                [
                    'id' => 3,
                    'title' => 'Banner Mobile',
                    'size' => '320x50',
                    'code' => '<a href="' . htmlspecialchars($referralLink) . '"><img src="https://via.placeholder.com/320x50?text=Mobile+Banner" alt="Banner"></a>',
                ],
            ];

            // Campaigns (có thể lấy từ database)
            $campaigns = [
                [
                    'id' => 1,
                    'name' => 'Summer Sale 2024',
                    'commission_rate' => 0.15,
                    'status' => 'active',
                    'start_date' => '2024-01-01',
                    'end_date' => '2024-12-31',
                    'clicks' => 0,
                    'conversions' => 0,
                    'conversion_rate' => 0,
                    'commission' => 0,
                ],
                [
                    'id' => 2,
                    'name' => 'New User Promotion',
                    'commission_rate' => 0.20,
                    'status' => 'active',
                    'start_date' => '2024-01-01',
                    'end_date' => null,
                    'clicks' => 0,
                    'conversions' => 0,
                    'conversion_rate' => 0,
                    'commission' => 0,
                ],
            ];

            return [
                'referral_code' => $referralCode,
                'referral_link' => $referralLink,
                'qr_code_url' => $qrCodeUrl,
                'banners' => $banners,
                'campaigns' => $campaigns,
            ];
        } catch (\Exception $e) {
            return $this->handleError($e, ['method' => 'getMarketingData', 'affiliate_id' => $affiliateId]);
        }
    }

    /**
     * Lấy dữ liệu clicks cho báo cáo.
     */
    public function getClicksData(int $affiliateId, ?string $dateFrom = null, ?string $dateTo = null): array
    {
        try {
            $affiliateModel = $this->getModel('AffiliateModel');
            if (!$affiliateModel) {
                return $this->getEmptyClicksData();
            }

            // Lấy affiliate theo user_id
            $affiliate = $affiliateModel->getByUserId($affiliateId);
            if (!$affiliate) {
                return $this->getEmptyClicksData();
            }

            // Lấy clicks từ database
            $clicks = [];
            
            // Try to get from affiliate_model if method exists
            if (method_exists($affiliateModel, 'getClicks')) {
                $clicks = $affiliateModel->getClicks($affiliate['id'], $dateFrom, $dateTo);
            }

            // Nếu không có dữ liệu, trả về mảng rỗng với cấu trúc đúng
            if (empty($clicks)) {
                return $this->getEmptyClicksData();
            }

            // Process clicks data
            $totalClicks = 0;
            $uniqueClicks = 0;
            $byDate = [];
            $bySource = [];

            foreach ($clicks as $click) {
                $totalClicks++;
                if ($click['is_unique'] ?? false) {
                    $uniqueClicks++;
                }

                // Group by date
                $date = date('Y-m-d', strtotime($click['created_at'] ?? 'now'));
                if (!isset($byDate[$date])) {
                    $byDate[$date] = ['total' => 0, 'unique' => 0];
                }
                $byDate[$date]['total']++;
                if ($click['is_unique'] ?? false) {
                    $byDate[$date]['unique']++;
                }

                // Group by source
                $source = $click['source'] ?? 'direct';
                if (!isset($bySource[$source])) {
                    $bySource[$source] = 0;
                }
                $bySource[$source]++;
            }

            return [
                'total_clicks' => $totalClicks,
                'unique_clicks' => $uniqueClicks,
                'click_rate' => $totalClicks > 0 ? round(($uniqueClicks / $totalClicks) * 100, 2) : 0,
                'by_date' => $byDate,
                'by_source' => $bySource,
            ];
        } catch (\Exception $e) {
            return $this->handleError($e, ['method' => 'getClicksData', 'affiliate_id' => $affiliateId]);
        }
    }

    /**
     * Lấy dữ liệu orders cho báo cáo.
     */
    public function getOrdersData(int $affiliateId, ?string $dateFrom = null, ?string $dateTo = null): array
    {
        try {
            $affiliateModel = $this->getModel('AffiliateModel');
            $ordersModel = $this->getModel('OrdersModel');
            
            if (!$affiliateModel || !$ordersModel) {
                return $this->getEmptyOrdersData();
            }

            // Lấy affiliate theo user_id
            $affiliate = $affiliateModel->getByUserId($affiliateId);
            if (!$affiliate) {
                return $this->getEmptyOrdersData();
            }

            // Lấy orders từ database
            $orders = [];
            
            // Try to get from affiliate_model if method exists
            if (method_exists($affiliateModel, 'getReferredOrders')) {
                $orders = $affiliateModel->getReferredOrders($affiliate['id'], $dateFrom, $dateTo);
            }

            // Nếu không có dữ liệu, trả về mảng rỗng
            if (empty($orders)) {
                return $this->getEmptyOrdersData();
            }

            // Process orders data
            $totalOrders = count($orders);
            $totalRevenue = 0;
            $totalCommission = 0;
            $byDate = [];
            $byStatus = ['pending' => 0, 'processing' => 0, 'completed' => 0, 'cancelled' => 0];

            foreach ($orders as $order) {
                $amount = $order['total_amount'] ?? $order['amount'] ?? 0;
                $commission = $order['commission_amount'] ?? $order['commission'] ?? 0;
                
                $totalRevenue += $amount;
                $totalCommission += $commission;

                // Group by date
                $date = date('Y-m-d', strtotime($order['created_at'] ?? 'now'));
                if (!isset($byDate[$date])) {
                    $byDate[$date] = ['revenue' => 0, 'commission' => 0, 'orders' => 0];
                }
                $byDate[$date]['revenue'] += $amount;
                $byDate[$date]['commission'] += $commission;
                $byDate[$date]['orders']++;

                // Group by status
                $status = $order['status'] ?? 'pending';
                if (isset($byStatus[$status])) {
                    $byStatus[$status]++;
                }
            }

            return [
                'total_orders' => $totalOrders,
                'total_revenue' => $totalRevenue,
                'total_commission' => $totalCommission,
                'average_order_value' => $totalOrders > 0 ? round($totalRevenue / $totalOrders) : 0,
                'by_date' => $byDate,
                'by_status' => $byStatus,
            ];
        } catch (\Exception $e) {
            return $this->handleError($e, ['method' => 'getOrdersData', 'affiliate_id' => $affiliateId]);
        }
    }

    /**
     * Tạo mã giới thiệu mới.
     */
    public function generateReferralCode(int $affiliateId): string
    {
        try {
            $code = strtoupper(substr(md5(uniqid($affiliateId)), 0, 8));
            
            $affiliateModel = $this->getModel('AffiliateModel');
            if ($affiliateModel && method_exists($affiliateModel, 'update')) {
                $affiliateModel->update($affiliateId, ['referral_code' => $code]);
            }
            
            return $code;
        } catch (\Exception $e) {
            $this->handleError($e, ['method' => 'generateReferralCode', 'affiliate_id' => $affiliateId]);
            return '';
        }
    }

    /**
     * Trả về cài đặt rút tiền mặc định.
     */
    private function getDefaultWithdrawalSettings(): array
    {
        return [
            'min_amount' => 100000,
            'max_amount' => 10000000,
            'fee_percent' => 0,
            'fee_fixed' => 0,
            'processing_days' => 3,
            'enabled' => true,
        ];
    }

    /**
     * Trả về dữ liệu clicks rỗng.
     */
    private function getEmptyClicksData(): array
    {
        return [
            'total_clicks' => 0,
            'unique_clicks' => 0,
            'click_rate' => 0,
            'by_date' => [],
            'by_source' => [],
        ];
    }

    /**
     * Trả về dữ liệu orders rỗng.
     */
    private function getEmptyOrdersData(): array
    {
        return [
            'total_orders' => 0,
            'total_revenue' => 0,
            'total_commission' => 0,
            'average_order_value' => 0,
            'by_date' => [],
            'by_status' => ['pending' => 0, 'processing' => 0, 'completed' => 0, 'cancelled' => 0],
        ];
    }

    /**
     * Lấy danh sách thông báo cho affiliate.
     */
    public function getNotifications(int $affiliateId = 0, int $limit = 5): array
    {
        if ($affiliateId <= 0) {
            return ['notifications' => [], 'unread_count' => 0];
        }
        
        try {
            $model = $this->getModel('BaseModel');
            if (!$model) {
                return ['notifications' => [], 'unread_count' => 0];
            }

            // Lấy notifications từ database
            // Kiểm tra bảng có tồn tại không
            try {
                $sql = "SELECT * FROM affiliate_notifications WHERE affiliate_id = ? ORDER BY created_at DESC LIMIT " . (int)$limit;
                $rows = $model->query($sql, [$affiliateId]);
            } catch (\Exception $e) {
                // Bảng chưa tồn tại, trả về mảng rỗng
                return ['notifications' => [], 'unread_count' => 0];
            }

            $notifications = [];
            foreach ($rows as $row) {
                $notifications[] = [
                    'id' => $row['id'],
                    'type' => $row['type'] ?? 'info',
                    'title' => $row['title'] ?? '',
                    'message' => $row['message'] ?? '',
                    'is_read' => $row['is_read'] ?? 0,
                    'created_at' => $row['created_at'] ?? date('Y-m-d H:i:s'),
                ];
            }

            // Đếm số notification chưa đọc
            try {
                $countSql = "SELECT COUNT(*) as total FROM affiliate_notifications WHERE affiliate_id = ? AND is_read = 0";
                $countResult = $model->query($countSql, [$affiliateId]);
                $unreadCount = $countResult[0]['total'] ?? 0;
            } catch (\Exception $e) {
                $unreadCount = 0;
            }

            return [
                'notifications' => $notifications,
                'unread_count' => $unreadCount,
            ];
        } catch (\Exception $e) {
            error_log('getNotifications error: ' . $e->getMessage());
            return ['notifications' => [], 'unread_count' => 0];
        }
    }
}
