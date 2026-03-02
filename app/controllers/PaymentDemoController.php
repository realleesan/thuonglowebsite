<?php
/**
 * Payment Demo Controller
 * Controller xử lý thanh toán demo với SePay
 */

require_once __DIR__ . '/../models/OrdersDemoModel.php';
require_once __DIR__ . '/../models/ProductsDemoModel.php';
require_once __DIR__ . '/../services/SepayService.php';

class PaymentDemoController {
    private $orderModel;
    private $productModel;
    private $sepayService;
    
    public function __construct() {
        $this->orderModel = new OrdersDemoModel();
        $this->productModel = new ProductsDemoModel();
        $this->sepayService = new SepayService();
    }
    
    /**
     * Display checkout page
     */
    public function checkout() {
        try {
            $productId = $_GET['product_id'] ?? null;
            
            if (!$productId) {
                throw new Exception('Vui lòng chọn sản phẩm');
            }
            
            $product = $this->productModel->getById($productId);
            
            if (!$product) {
                throw new Exception('Không tìm thấy sản phẩm');
            }
            
            return [
                'success' => true,
                'product' => $product,
                'total' => $product['price']
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Process payment
     */
    public function processPayment() {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('Invalid request method');
            }
            
            $productId = $_POST['product_id'] ?? null;
            $customerName = $_POST['customer_name'] ?? 'Khách hàng demo';
            $customerEmail = $_POST['customer_email'] ?? '';
            $customerPhone = $_POST['customer_phone'] ?? '';
            
            if (!$productId) {
                throw new Exception('Vui lòng chọn sản phẩm');
            }
            
            $product = $this->productModel->getById($productId);
            
            if (!$product) {
                throw new Exception('Không tìm thấy sản phẩm');
            }
            
            // Create order
            $orderData = [
                'user_id' => $_SESSION['user_id'] ?? null,
                'status' => 'pending',
                'payment_status' => 'pending',
                'payment_method' => 'sepay',
                'customer_name' => $customerName,
                'customer_email' => $customerEmail,
                'customer_phone' => $customerPhone,
                'notes' => 'Đơn hàng demo test thanh toán'
            ];
            
            $items = [[
                'product_id' => $product['id'],
                'product_name' => $product['name'],
                'product_sku' => $product['sku'],
                'quantity' => 1,
                'price' => $product['price']
            ]];
            
            $order = $this->orderModel->createOrder($orderData, $items);
            
            if (!$order) {
                throw new Exception('Không thể tạo đơn hàng');
            }
            
            // Generate QR code from SePay
            $qrResult = $this->sepayService->generatePaymentQR(
                $order['id'],
                $order['total'],
                'Thanh toán đơn hàng demo ' . $order['order_number']
            );
            
            if (!$qrResult['success']) {
                // Log chi tiết lỗi để debug
                $errorDetail = $qrResult['message'] ?? 'Unknown error';
                error_log('SePay QR Generation Failed: ' . $errorDetail);
                throw new Exception('Không thể tạo mã QR thanh toán: ' . $errorDetail);
            }
            
            // Store order info in session
            $_SESSION['demo_order_id'] = $order['id'];
            $_SESSION['demo_order_number'] = $order['order_number'];
            
            return [
                'success' => true,
                'order' => $order,
                'qr_data' => $qrResult
            ];
            
        } catch (Exception $e) {
            // Log lỗi chi tiết
            error_log('Payment Demo Error: ' . $e->getMessage());
            error_log('Stack trace: ' . $e->getTraceAsString());
            
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Check payment status (AJAX)
     */
    public function checkPaymentStatus() {
        try {
            $orderId = $_GET['order_id'] ?? $_SESSION['demo_order_id'] ?? null;
            
            if (!$orderId) {
                throw new Exception('Không tìm thấy đơn hàng');
            }
            
            $order = $this->orderModel->find($orderId);
            
            if (!$order) {
                throw new Exception('Không tìm thấy đơn hàng');
            }
            
            return [
                'success' => true,
                'payment_status' => $order['payment_status'],
                'order_status' => $order['status']
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Payment success page
     */
    public function success() {
        try {
            $orderNumber = $_GET['order_number'] ?? $_SESSION['demo_order_number'] ?? null;
            
            if (!$orderNumber) {
                throw new Exception('Không tìm thấy thông tin đơn hàng');
            }
            
            $order = $this->orderModel->getByOrderNumber($orderNumber);
            
            if (!$order) {
                throw new Exception('Không tìm thấy đơn hàng');
            }
            
            return [
                'success' => true,
                'order' => $order
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
}
