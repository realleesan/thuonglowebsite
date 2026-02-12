<?php

require_once __DIR__ . '/BaseService.php';
require_once __DIR__ . '/DataTransformer.php';

/**
 * UserService
 *
 * Service chuyên xử lý data cho khu vực tài khoản người dùng:
 * - Dashboard
 * - Account
 * - Orders
 * - Cart
 * - Wishlist
 *
 * Phase 4: Đã loại bỏ hoàn toàn dependency vào ViewDataService.
 * Sử dụng trực tiếp BaseService::getModel() với lazy loading.
 */
class UserService extends BaseService
{
    protected DataTransformer $transformer;

    public function __construct(?ErrorHandler $errorHandler = null, string $serviceType = 'user')
    {
        parent::__construct($errorHandler, $serviceType);
        $this->transformer = new DataTransformer();
    }

    /**
     * Data cho User Dashboard.
     */
    public function getDashboardData(int $userId): array
    {
        try {
            $usersModel = $this->getModel('UsersModel');
            $ordersModel = $this->getModel('OrdersModel');

            if (!$usersModel || !$ordersModel) {
                return $this->getEmptyData();
            }

            $user = $usersModel->getUserWithOrdersCount($userId);
            if (!$user) {
                $user = ['name' => 'Người dùng'];
            } else {
                if (!isset($user['name']) && isset($user['full_name'])) {
                    $user['name'] = $user['full_name'];
                } elseif (!isset($user['name'])) {
                    $user['name'] = 'Người dùng';
                }
            }

            $recentOrdersRaw = $ordersModel->getByUser($userId, 5);
            $recentOrders = [];
            foreach ($recentOrdersRaw as $order) {
                $recentOrders[] = [
                    'id' => $order['id'] ?? rand(1000, 9999),
                    'product_name' => $order['product_name'] ?? 'Sản phẩm',
                    'date' => $order['created_at'] ?? date('Y-m-d'),
                    'amount' => $order['total_amount'] ?? ($order['amount'] ?? 0),
                    'status' => $order['status'] ?? 'completed',
                ];
            }

            $stats = [
                'total_orders' => $user['orders_count'] ?? 0,
                'total_spent' => $user['total_spent'] ?? 0,
                'loyalty_points' => $user['points'] ?? 0,
                'user_level' => $user['level'] ?? 'Bronze',
                'data_purchased' => count($recentOrders),
            ];

            $trends = [
                'orders' => [
                    'value' => max(0, $stats['total_orders'] - 5),
                    'direction' => $stats['total_orders'] > 5 ? 'up' : 'down',
                ],
                'spending' => [
                    'value' => max(0, round(($stats['total_spent'] ?? 0) / 1000000, 1)),
                    'direction' => ($stats['total_spent'] ?? 0) > 0 ? 'up' : 'down',
                ],
                'data' => [
                    'value' => $stats['data_purchased'],
                    'direction' => $stats['data_purchased'] > 0 ? 'up' : 'down',
                ],
                'points' => [
                    'value' => max(0, ($stats['loyalty_points'] ?? 0) - 100),
                    'direction' => ($stats['loyalty_points'] ?? 0) > 100 ? 'up' : 'down',
                ],
            ];

            return [
                'user' => $user,
                'stats' => $stats,
                'recent_orders' => $recentOrders,
                'trends' => $trends,
            ];
        } catch (\Exception $e) {
            return $this->handleError($e, ['method' => 'getDashboardData', 'user_id' => $userId]);
        }
    }

    /**
     * Data cho trang thông tin tài khoản.
     */
    public function getAccountData(int $userId): array
    {
        try {
            $usersModel = $this->getModel('UsersModel');
            if (!$usersModel) {
                return $this->getEmptyData();
            }

            $user = $usersModel->find($userId);
            if (!$user) {
                return $this->getEmptyData();
            }

            return [
                'user' => $this->transformer->transformUser($user),
            ];
        } catch (\Exception $e) {
            return $this->handleError($e, ['method' => 'getAccountData', 'user_id' => $userId]);
        }
    }

    /**
     * Data cho danh sách đơn hàng của user.
     */
    public function getOrdersData(int $userId, int $limit = 20): array
    {
        try {
            $ordersModel = $this->getModel('OrdersModel');
            if (!$ordersModel) {
                return $this->getEmptyData();
            }

            $orders = $ordersModel->getByUser($userId, $limit);

            return [
                'orders' => $this->transformer->transformOrders($orders),
            ];
        } catch (\Exception $e) {
            return $this->handleError($e, ['method' => 'getOrdersData', 'user_id' => $userId]);
        }
    }

    /**
     * Data cho giỏ hàng của user.
     */
    public function getCartData(int $userId): array
    {
        return [
            'items' => [],
            'summary' => [
                'total_items' => 0,
                'total_amount' => 0,
            ],
        ];
    }

    /**
     * Data cho wishlist của user.
     */
    public function getWishlistData(int $userId): array
    {
        return [
            'items' => [],
            'total_items' => 0,
        ];
    }
}
