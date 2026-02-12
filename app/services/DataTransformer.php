<?php
/**
 * Data Transformer
 * Chuyển đổi raw database data thành format phù hợp cho views
 */

require_once __DIR__ . '/ViewSecurityHelper.php';

class DataTransformer {
    private $security;
    
    public function __construct() {
        $this->security = new ViewSecurityHelper();
    }
    
    /**
     * Transform single product data for display
     */
    public function transformProduct($product): array {
        if (!$product) {
            return [];
        }
        
        return [
            'id' => (int) $product['id'],
            'name' => $this->security->escapeHtml($product['name']),
            'slug' => $product['slug'] ?? '',
            'price' => (float) $product['price'],
            'sale_price' => $product['sale_price'] ? (float) $product['sale_price'] : null,
            'image' => $product['image'] ?? '/assets/images/default-product.jpg',
            'category_name' => $this->security->escapeHtml($product['category_name'] ?? ''),
            'status' => $product['status'] ?? 'active',
            'featured' => (bool) ($product['featured'] ?? false),
            'formatted_price' => $this->security->formatMoney($product['price']),
            'formatted_sale_price' => $product['sale_price'] ? $this->security->formatMoney($product['sale_price']) : null,
            'discount_percent' => $this->calculateDiscountPercent($product['price'], $product['sale_price'] ?? null),
            'short_description' => $this->security->escapeHtml($product['short_description'] ?? ''),
            'stock' => (int) ($product['stock'] ?? 0),
            'in_stock' => ($product['stock'] ?? 0) > 0
        ];
    }
    
    /**
     * Transform multiple products
     */
    public function transformProducts($products): array {
        if (!is_array($products)) {
            return [];
        }
        
        return array_map([$this, 'transformProduct'], $products);
    }
    
    /**
     * Transform single category data for display
     */
    public function transformCategory($category): array {
        if (!$category) {
            return [];
        }
        
        return [
            'id' => (int) $category['id'],
            'name' => $this->security->escapeHtml($category['name']),
            'slug' => $category['slug'] ?? '',
            'image' => $category['image'] ?? '/assets/images/default-category.jpg',
            'product_count' => (int) ($category['product_count'] ?? 0),
            'description' => $this->security->escapeHtml($category['description'] ?? '')
        ];
    }
    
    /**
     * Transform multiple categories
     */
    public function transformCategories($categories): array {
        if (!is_array($categories)) {
            return [];
        }
        
        return array_map([$this, 'transformCategory'], $categories);
    }
    
    /**
     * Transform single user data for display
     */
    public function transformUser($user): array {
        if (!$user) {
            return [];
        }
        
        return [
            'id' => (int) $user['id'],
            'name' => $this->security->escapeHtml($user['name']),
            'email' => $this->security->escapeHtml($user['email']),
            'phone' => $this->security->escapeHtml($user['phone'] ?? ''),
            'role' => $user['role'] ?? 'user',
            'status' => $user['status'] ?? 'active',
            'avatar' => $user['avatar'] ?? '/assets/images/default-avatar.jpg',
            'points' => (int) ($user['points'] ?? 0),
            'level' => $user['level'] ?? 'Bronze',
            'orders_count' => (int) ($user['orders_count'] ?? 0),
            'total_spent' => $this->security->formatMoney($user['total_spent'] ?? 0),
            'formatted_points' => number_format($user['points'] ?? 0),
            'created_at' => $this->formatDate($user['created_at'] ?? null)
        ];
    }
    
    /**
     * Transform multiple users
     */
    public function transformUsers($users): array {
        if (!is_array($users)) {
            return [];
        }
        
        return array_map([$this, 'transformUser'], $users);
    }
    
    /**
     * Transform single order data for display
     */
    public function transformOrder($order): array {
        if (!$order) {
            return [];
        }
        
        return [
            'id' => (int) $order['id'],
            'order_number' => $this->security->escapeHtml($order['order_number'] ?? ''),
            'status' => $order['status'],
            'total' => (float) $order['total'],
            'formatted_total' => $this->security->formatMoney($order['total']),
            'user_name' => $this->security->escapeHtml($order['user_name'] ?? ''),
            'created_at' => $this->formatDate($order['created_at']),
            'status_label' => $this->getOrderStatusLabel($order['status'])
        ];
    }
    
    /**
     * Transform multiple orders
     */
    public function transformOrders($orders): array {
        if (!is_array($orders)) {
            return [];
        }
        
        return array_map([$this, 'transformOrder'], $orders);
    }
    
    /**
     * Transform single news data for display
     */
    public function transformNews($news): array {
        if (!$news) {
            return [];
        }
        
        return [
            'id' => (int) $news['id'],
            'title' => $this->security->escapeHtml($news['title']),
            'slug' => $news['slug'],
            'excerpt' => $this->security->escapeHtml($news['excerpt'] ?? ''),
            'image' => $news['image'] ?? '/assets/images/default-news.jpg',
            'category_name' => $this->security->escapeHtml($news['category_name'] ?? ''),
            'created_at' => $this->formatDate($news['created_at']),
            'formatted_date' => $this->formatDateForDisplay($news['created_at'])
        ];
    }
    
    /**
     * Transform multiple news
     */
    public function transformNewsItems($newsItems): array {
        if (!is_array($newsItems)) {
            return [];
        }
        
        return array_map([$this, 'transformNews'], $newsItems);
    }
    
    /**
     * Transform single affiliate data for display
     */
    public function transformAffiliate($affiliate): array {
        if (!$affiliate) {
            return [];
        }
        
        return [
            'id' => (int) $affiliate['id'],
            'name' => $this->security->escapeHtml($affiliate['name']),
            'email' => $this->security->escapeHtml($affiliate['email']),
            'commission_rate' => (float) ($affiliate['commission_rate'] ?? 0),
            'total_earnings' => $this->security->formatMoney($affiliate['total_earnings'] ?? 0),
            'customers_count' => (int) ($affiliate['customers_count'] ?? 0),
            'status' => $affiliate['status'],
            'created_at' => $this->formatDate($affiliate['created_at'])
        ];
    }
    
    /**
     * Transform commission data
     */
    public function transformCommissions($commissions): array {
        if (!is_array($commissions)) {
            return [];
        }
        
        return array_map(function($commission) {
            return [
                'id' => (int) $commission['id'],
                'amount' => $this->security->formatMoney($commission['amount']),
                'order_id' => (int) $commission['order_id'],
                'status' => $commission['status'],
                'created_at' => $this->formatDate($commission['created_at'])
            ];
        }, $commissions);
    }
    
    /**
     * Format display data based on type
     */
    public function formatDisplayData($data, $type): array {
        switch ($type) {
            case 'product':
                return $this->transformProduct($data);
            case 'category':
                return $this->transformCategory($data);
            case 'user':
                return $this->transformUser($data);
            case 'order':
                return $this->transformOrder($data);
            case 'news':
                return $this->transformNews($data);
            case 'affiliate':
                return $this->transformAffiliate($data);
            default:
                return $data;
        }
    }
    
    /**
     * Calculate discount percentage
     */
    private function calculateDiscountPercent($originalPrice, $salePrice) {
        if (!$salePrice || $salePrice >= $originalPrice) {
            return null;
        }
        
        return round((($originalPrice - $salePrice) / $originalPrice) * 100);
    }
    
    /**
     * Format date for database storage
     */
    private function formatDate($date) {
        if (!$date) {
            return null;
        }
        
        return date('Y-m-d H:i:s', strtotime($date));
    }
    
    /**
     * Format date for display
     */
    private function formatDateForDisplay($date) {
        if (!$date) {
            return '';
        }
        
        return date('d/m/Y', strtotime($date));
    }
    
    /**
     * Get order status label in Vietnamese
     */
    private function getOrderStatusLabel($status) {
        $labels = [
            'pending' => 'Chờ xử lý',
            'processing' => 'Đang xử lý',
            'shipped' => 'Đã gửi hàng',
            'delivered' => 'Đã giao hàng',
            'cancelled' => 'Đã hủy',
            'refunded' => 'Đã hoàn tiền'
        ];
        
        return $labels[$status] ?? $status;
    }
    
    /**
     * Transform single contact data for display
     */
    public function transformContact($contact): array {
        if (!$contact) {
            return [];
        }
        
        return [
            'id' => (int) $contact['id'],
            'name' => $this->security->escapeHtml($contact['name']),
            'email' => $this->security->escapeHtml($contact['email']),
            'phone' => $this->security->escapeHtml($contact['phone'] ?? ''),
            'subject' => $this->security->escapeHtml($contact['subject']),
            'message' => $this->security->escapeHtml($contact['message']),
            'status' => $contact['status'] ?? 'new',
            'created_at' => $contact['created_at'],
            'formatted_date' => $this->formatDateForDisplay($contact['created_at']),
            'status_label' => $this->getContactStatusLabel($contact['status'] ?? 'new')
        ];
    }
    
    /**
     * Transform multiple contacts
     */
    public function transformContacts($contacts): array {
        if (!is_array($contacts)) {
            return [];
        }
        
        return array_map([$this, 'transformContact'], $contacts);
    }
    
    /**
     * Get contact status label
     */
    private function getContactStatusLabel($status): string {
        $labels = [
            'new' => 'Mới',
            'read' => 'Đã đọc',
            'replied' => 'Đã trả lời'
        ];
        
        return $labels[$status] ?? $status;
    }
    
    /**
     * Transform single setting data for display
     */
    public function transformSetting($setting): array {
        if (!$setting) {
            return [];
        }
        
        return [
            'id' => (int) $setting['id'],
            'key' => $this->security->escapeHtml($setting['key']),
            'value' => $this->security->escapeHtml($setting['value']),
            'description' => $this->security->escapeHtml($setting['description'] ?? ''),
            'type' => $setting['type'] ?? 'text',
            'created_at' => $setting['created_at'],
            'updated_at' => $setting['updated_at'] ?? null,
            'formatted_date' => $this->formatDateForDisplay($setting['created_at'])
        ];
    }
    
    /**
     * Transform multiple settings
     */
    public function transformSettings($settings): array {
        if (!is_array($settings)) {
            return [];
        }
        
        return array_map([$this, 'transformSetting'], $settings);
    }

    /**
     * Transform single event data
     */
    public function transformEvent($event): array {
        if (!$event) {
            return [];
        }
        
        return [
            'id' => (int) ($event['id'] ?? 0),
            'title' => $this->security->escapeHtml($event['title'] ?? ''),
            'description' => $this->security->escapeHtml($event['description'] ?? ''),
            'location' => $this->security->escapeHtml($event['location'] ?? ''),
            'start_date' => $event['start_date'] ?? null,
            'end_date' => $event['end_date'] ?? null,
            'status' => $event['status'] ?? 'upcoming',
            'max_participants' => (int) ($event['max_participants'] ?? 0),
            'current_participants' => (int) ($event['current_participants'] ?? 0),
            'image' => $event['image'] ?? '',
            'created_at' => $event['created_at'] ?? null,
            'updated_at' => $event['updated_at'] ?? null,
            'formatted_start_date' => isset($event['start_date']) ? $this->formatDateForDisplay($event['start_date']) : '',
            'formatted_end_date' => isset($event['end_date']) ? $this->formatDateForDisplay($event['end_date']) : '',
        ];
    }

    /**
     * Transform multiple events
     */
    public function transformEvents($events): array {
        if (!is_array($events)) {
            return [];
        }
        
        return array_map([$this, 'transformEvent'], $events);
    }
}