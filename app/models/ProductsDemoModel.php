<?php
/**
 * Products Demo Model
 * Model cho sản phẩm demo để test thanh toán
 */

require_once __DIR__ . '/BaseModel.php';

class ProductsDemoModel extends BaseModel {
    protected $table = 'products_demo';
    protected $fillable = [
        'name', 'slug', 'category_id', 'price', 'sale_price', 'stock', 'sku',
        'status', 'type', 'description', 'short_description', 'image',
        'featured', 'digital', 'sales_count', 'views'
    ];
    
    /**
     * Get all active demo products
     */
    public function getAllActive($limit = null) {
        $sql = "SELECT * FROM {$this->table} WHERE status = 'active' ORDER BY featured DESC, created_at DESC";
        
        if ($limit) {
            $sql .= " LIMIT {$limit}";
        }
        
        $products = $this->db->query($sql);
        
        // Format products
        foreach ($products as &$product) {
            $product['formatted_price'] = number_format($product['price'], 0, ',', '.') . 'đ';
            $product['in_stock'] = $product['stock'] > 0;
        }
        
        return $products;
    }
    
    /**
     * Get product by ID with formatted data
     */
    public function getById($id) {
        $product = $this->find($id);
        
        if ($product) {
            $product['formatted_price'] = number_format($product['price'], 0, ',', '.') . 'đ';
            $product['in_stock'] = $product['stock'] > 0;
        }
        
        return $product;
    }
    
    /**
     * Get product by slug
     */
    public function getBySlug($slug) {
        $sql = "SELECT * FROM {$this->table} WHERE slug = ? AND status = 'active'";
        $result = $this->db->query($sql, [$slug]);
        
        if (!empty($result)) {
            $product = $result[0];
            $product['formatted_price'] = number_format($product['price'], 0, ',', '.') . 'đ';
            $product['in_stock'] = $product['stock'] > 0;
            return $product;
        }
        
        return null;
    }
    
    /**
     * Get related products (other demo products)
     */
    public function getRelated($productId, $limit = 4) {
        $sql = "SELECT * FROM {$this->table} 
                WHERE id != ? AND status = 'active' 
                ORDER BY featured DESC 
                LIMIT {$limit}";
        
        $products = $this->db->query($sql, [$productId]);
        
        // Format products
        foreach ($products as &$product) {
            $product['formatted_price'] = number_format($product['price'], 0, ',', '.') . 'đ';
            $product['in_stock'] = $product['stock'] > 0;
        }
        
        return $products;
    }
    
    /**
     * Update product views
     */
    public function incrementViews($productId) {
        $sql = "UPDATE {$this->table} SET views = views + 1 WHERE id = ?";
        return $this->db->query($sql, [$productId]);
    }
    
    /**
     * Update sales count
     */
    public function incrementSales($productId, $quantity = 1) {
        $sql = "UPDATE {$this->table} SET sales_count = sales_count + ? WHERE id = ?";
        return $this->db->query($sql, [$quantity, $productId]);
    }
    
    /**
     * Update stock
     */
    public function updateStock($productId, $quantity, $operation = 'decrease') {
        $operator = $operation === 'increase' ? '+' : '-';
        $sql = "UPDATE {$this->table} SET stock = stock {$operator} ? WHERE id = ?";
        return $this->db->query($sql, [$quantity, $productId]);
    }
}
