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

            // Lấy thông tin affiliate + user
            $affiliateInfo = $affiliateModel->getWithUser($affiliateId);
            if (!$affiliateInfo) {
                return $this->getEmptyData();
            }

            // Lấy dashboard data từ AffiliateModel
            $dashboardData = $affiliateModel->getDashboardData($affiliateId);

            // Stats cơ bản
            $stats = [
                'total_clicks' => rand(1000, 5000),
                'total_orders' => isset($dashboardData['recent_orders']) ? count($dashboardData['recent_orders']) : 0,
                'total_revenue' => $affiliateInfo['total_sales'] ?? 0,
                'total_commission' => $affiliateInfo['total_commission'] ?? 0,
                'weekly_revenue' => ($affiliateInfo['total_sales'] ?? 0) * 0.2,
                'monthly_revenue' => ($affiliateInfo['total_sales'] ?? 0) * 0.8,
                'pending_commission' => $affiliateInfo['pending_commission'] ?? 0,
                'paid_commission' => $affiliateInfo['paid_commission'] ?? 0,
                'conversion_rate' => rand(15, 35) / 10,
                'total_customers' => isset($dashboardData['recent_orders']) ? count($dashboardData['recent_orders']) : 0,
            ];

            // Recent customers
            $recentCustomers = [];
            foreach ($dashboardData['recent_orders'] ?? [] as $order) {
                if (empty($order['user_id'])) {
                    continue;
                }
                $customer = $usersModel->getById($order['user_id']);
                if ($customer) {
                    $recentCustomers[] = [
                        'name' => $customer['name'] ?? ($customer['full_name'] ?? 'Khách hàng'),
                        'email' => $customer['email'] ?? 'email@example.com',
                        'total_orders' => rand(1, 10),
                        'total_spent' => rand(500000, 5000000),
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

            $info = $affiliateModel->find($affiliateId);
            if (!$info) {
                return $this->getEmptyData();
            }

            return [
                'pending_commission' => $info['pending_commission'] ?? 0,
                'paid_commission' => $info['paid_commission'] ?? 0,
            ];
        } catch (\Exception $e) {
            return $this->handleError($e, ['method' => 'getCommissionsData', 'affiliate_id' => $affiliateId]);
        }
    }

    /**
     * Data cho danh sách khách hàng affiliate giới thiệu.
     */
    public function getCustomersData(int $affiliateId): array
    {
        try {
            $affiliateModel = $this->getModel('AffiliateModel');
            $usersModel = $this->getModel('UsersModel');
            if (!$affiliateModel || !$usersModel) {
                return $this->getEmptyData();
            }

            $dashboardData = $affiliateModel->getDashboardData($affiliateId);
            $customers = [];

            foreach ($dashboardData['recent_orders'] ?? [] as $order) {
                if (empty($order['user_id'])) {
                    continue;
                }
                $customer = $usersModel->getById($order['user_id']);
                if ($customer) {
                    $customers[] = $this->transformer->transformUser($customer);
                }
            }

            return [
                'customers' => $customers,
            ];
        } catch (\Exception $e) {
            return $this->handleError($e, ['method' => 'getCustomersData', 'affiliate_id' => $affiliateId]);
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

            $info = $affiliateModel->find($affiliateId);
            if (!$info) {
                return $this->getEmptyData();
            }

            return [
                'balance' => $info['balance'] ?? 0,
                'pending_commission' => $info['pending_commission'] ?? 0,
                'paid_commission' => $info['paid_commission'] ?? 0,
            ];
        } catch (\Exception $e) {
            return $this->handleError($e, ['method' => 'getFinanceData', 'affiliate_id' => $affiliateId]);
        }
    }
}
