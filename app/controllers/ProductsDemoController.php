<?php
/**
 * Products Demo Controller
 * Controller cho sản phẩm demo để test thanh toán
 */

require_once __DIR__ . '/../models/ProductsDemoModel.php';

class ProductsDemoController {
    private $productModel;
    
    public function __construct() {
        $this->productModel = new ProductsDemoModel();
    }
    
    /**
     * Display products list
     */
    public function index() {
        try {
            $products = $this->productModel->getAllActive();
            
            // Format products data
            foreach ($products as &$product) {
                $product['formatted_price'] = number_format($product['price'], 0, ',', '.') . 'đ';
                $product['in_stock'] = $product['stock'] > 0;
            }
            
            return [
                'success' => true,
                'products' => $products
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'products' => []
            ];
        }
    }
    
    /**
     * Display product details
     */
    public function details($productId) {
        try {
            $product = $this->productModel->getById($productId);
            
            if (!$product) {
                throw new Exception('Không tìm thấy sản phẩm demo');
            }
            
            // Increment views
            $this->productModel->incrementViews($productId);
            
            // Get related products
            $relatedProducts = $this->productModel->getRelated($productId, 4);
            foreach ($relatedProducts as &$related) {
                $related['formatted_price'] = number_format($related['price'], 0, ',', '.') . 'đ';
                $related['in_stock'] = $related['stock'] > 0;
            }
            
            return [
                'success' => true,
                'product' => $product,
                'related_products' => $relatedProducts
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'product' => null
            ];
        }
    }
}
