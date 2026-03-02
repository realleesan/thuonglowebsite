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
        try {
            $cartModel = $this->getModel('CartModel');
            $productsModel = $this->getModel('ProductsModel');
            
            if (!$cartModel) {
                return [
                    'items' => [],
                    'summary' => [
                        'total_items' => 0,
                        'total_amount' => 0,
                    ],
                ];
            }
            
            $cartItems = $cartModel->getByUser($userId);
            $items = [];
            $totalAmount = 0;
            
            foreach ($cartItems as $item) {
                $product = null;
                if ($productsModel) {
                    $product = $productsModel->find($item['product_id']);
                }
                
                $price = $item['price'] ?? ($product['price'] ?? 0);
                $quantity = $item['quantity'] ?? 1;
                $subtotal = $price * $quantity;
                $totalAmount += $subtotal;
                
                $items[] = [
                    'id' => $item['id'],
                    'product_id' => $item['product_id'],
                    'name' => $product['name'] ?? 'Sản phẩm',
                    'price' => $price,
                    'original_price' => $product['original_price'] ?? $price,
                    'image' => $product['image'] ?? '',
                    'short_description' => $product['short_description'] ?? '',
                    'sku' => $product['sku'] ?? '',
                    'stock' => $product['stock'] ?? 0,
                    'quantity' => $quantity,
                    'subtotal' => $subtotal,
                    'created_at' => $item['created_at'],
                ];
            }
            
            return [
                'items' => $items,
                'summary' => [
                    'total_items' => count($items),
                    'total_amount' => $totalAmount,
                ],
            ];
        } catch (\Exception $e) {
            return $this->handleError($e, ['method' => 'getCartData', 'user_id' => $userId]);
        }
    }

    /**
     * Thêm sản phẩm vào giỏ hàng.
     */
    public function addToCart(int $userId, int $productId, int $quantity = 1, float $price = 0): bool
    {
        try {
            $cartModel = $this->getModel('CartModel');
            
            if (!$cartModel) {
                return false;
            }
            
            return $cartModel->addItem($userId, $productId, $quantity, $price);
        } catch (\Exception $e) {
            $this->handleError($e, ['method' => 'addToCart', 'user_id' => $userId, 'product_id' => $productId]);
            return false;
        }
    }

    /**
     * Cập nhật số lượng sản phẩm trong giỏ hàng.
     */
    public function updateCartItem(int $userId, $itemId, int $quantity): bool
    {
        try {
            $cartModel = $this->getModel('CartModel');
            
            if (!$cartModel) {
                return false;
            }
            
            return $cartModel->updateQuantity($itemId, $quantity);
        } catch (\Exception $e) {
            $this->handleError($e, ['method' => 'updateCartItem', 'user_id' => $userId, 'item_id' => $itemId]);
            return false;
        }
    }

    /**
     * Xóa sản phẩm khỏi giỏ hàng.
     */
    public function removeFromCart(int $userId, $itemId): bool
    {
        try {
            $cartModel = $this->getModel('CartModel');
            
            if (!$cartModel) {
                return false;
            }
            
            return $cartModel->removeItem($itemId);
        } catch (\Exception $e) {
            $this->handleError($e, ['method' => 'removeFromCart', 'user_id' => $userId, 'item_id' => $itemId]);
            return false;
        }
    }

    /**
     * Data cho wishlist của user.
     */
    public function getWishlistData(int $userId): array
    {
        try {
            $wishlistModel = $this->getModel('WishlistModel');
            $productsModel = $this->getModel('ProductsModel');
            
            if (!$wishlistModel) {
                return [
                    'items' => [],
                    'total_items' => 0,
                ];
            }
            
            $wishlistItems = $wishlistModel->getByUser($userId);
            $items = [];
            
            foreach ($wishlistItems as $item) {
                $product = null;
                if ($productsModel) {
                    $product = $productsModel->find($item['product_id']);
                }
                
                $items[] = [
                    'id' => $item['id'],
                    'product_id' => $item['product_id'],
                    'name' => $product['name'] ?? 'Sản phẩm',
                    'price' => $product['price'] ?? 0,
                    'original_price' => $product['original_price'] ?? ($product['price'] ?? 0),
                    'image' => $product['image'] ?? '',
                    'short_description' => $product['short_description'] ?? '',
                    'category' => $product['category_name'] ?? '',
                    'stock' => $product['stock'] ?? 0,
                    'sku' => $product['sku'] ?? '',
                    'notes' => $item['notes'] ?? '',
                    'created_at' => $item['created_at'],
                ];
            }
            
            return [
                'items' => $items,
                'total_items' => count($items),
            ];
        } catch (\Exception $e) {
            return $this->handleError($e, ['method' => 'getWishlistData', 'user_id' => $userId]);
        }
    }

    /**
     * Thêm sản phẩm vào wishlist.
     */
    public function addToWishlist(int $userId, int $productId, string $notes = ''): bool
    {
        try {
            $wishlistModel = $this->getModel('WishlistModel');
            
            if (!$wishlistModel) {
                return false;
            }
            
            return $wishlistModel->addProduct($userId, $productId, $notes);
        } catch (\Exception $e) {
            $this->handleError($e, ['method' => 'addToWishlist', 'user_id' => $userId, 'product_id' => $productId]);
            return false;
        }
    }

    /**
     * Xóa sản phẩm khỏi wishlist.
     */
    public function removeFromWishlist(int $userId, $itemId): bool
    {
        try {
            $wishlistModel = $this->getModel('WishlistModel');
            
            if (!$wishlistModel) {
                return false;
            }
            
            return $wishlistModel->removeProduct($itemId);
        } catch (\Exception $e) {
            $this->handleError($e, ['method' => 'removeFromWishlist', 'user_id' => $userId, 'item_id' => $itemId]);
            return false;
        }
    }

    /**
     * Cập nhật ghi chú wishlist.
     */
    public function updateWishlistNotes(int $userId, $itemId, string $notes): bool
    {
        try {
            $wishlistModel = $this->getModel('WishlistModel');
            
            if (!$wishlistModel) {
                return false;
            }
            
            return $wishlistModel->updateNotes($itemId, $notes);
        } catch (\Exception $e) {
            $this->handleError($e, ['method' => 'updateWishlistNotes', 'user_id' => $userId, 'item_id' => $itemId]);
            return false;
        }
    }
}
