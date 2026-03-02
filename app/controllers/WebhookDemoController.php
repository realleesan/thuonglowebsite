<?php
/**
 * Webhook Demo Controller
 * Xử lý webhook từ SePay cho đơn hàng demo
 */

require_once __DIR__ . '/../models/OrdersDemoModel.php';
require_once __DIR__ . '/../models/ProductsDemoModel.php';
require_once __DIR__ . '/../models/SepayWebhookLogModel.php';
require_once __DIR__ . '/../services/SepayService.php';

class WebhookDemoController {
    private $orderModel;
    private $productModel;
    private $webhookLogModel;
    private $sepayService;
    
    public function __construct() {
        $this->orderModel = new OrdersDemoModel();
        $this->productModel = new ProductsDemoModel();
        $this->webhookLogModel = new SepayWebhookLogModel();
        $this->sepayService = new SepayService();
    }
    
    /**
     * Handle SePay webhook for demo orders
     */
    public function handleWebhook() {
        $logId = null;
        
        try {
            // Get webhook data
            $rawData = file_get_contents('php://input');
            $webhookData = json_decode($rawData, true);
            
            if (!$webhookData) {
                throw new Exception('Invalid webhook data');
            }
            
            // Parse webhook data first
            $parsedData = $this->sepayService->parseWebhookData($webhookData);
            if (empty($parsedData['amount']) && isset($webhookData['transferAmount'])) {
                $parsedData['amount'] = $webhookData['transferAmount'];
            }
            
            // Log webhook with parsed data
            $logId = $this->webhookLogModel->logWebhook([
                'webhook_type' => 'payment_in',
                'transaction_id' => $parsedData['transaction_id'],
                'reference_code' => $parsedData['reference_code'],
                'amount' => $parsedData['amount'],
                'content' => $parsedData['reference_code'],
                'raw_data' => $webhookData,
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
                'status' => 'received'
            ]);
            
            // Extract order ID from content
            // Format: "DEMO[OrderId]" hoặc chỉ là order_id
            $content = $parsedData['reference_code'];
            
            if (empty($content)) {
                throw new Exception('Missing reference code in webhook');
            }
            
            // Try to extract order ID
            if (preg_match('/DH(\d+)/', $content, $matches)) {
                $orderId = $matches[1];
            } elseif (is_numeric($content)) {
                $orderId = $content;
            } else {
                throw new Exception('Invalid order reference format: ' . $content);
            }
            
            // Get order
            $order = $this->orderModel->find($orderId);
            
            if (!$order) {
                throw new Exception('Order not found: ' . $orderId);
            }
            
            // Link webhook to order
            if ($logId) {
                $this->webhookLogModel->linkToOrder($logId, $orderId);
            }
            
            // Check if already paid
            if ($order['payment_status'] === 'paid') {
                if ($logId) {
                    $this->webhookLogModel->markProcessed($logId, true);
                }
                
                return [
                    'success' => true,
                    'message' => 'Order already paid',
                    'order_id' => $orderId
                ];
            }
            
            // Verify amount
            if ($parsedData['amount'] != $order['total']) {
                throw new Exception('Amount mismatch: expected ' . $order['total'] . ', got ' . $parsedData['amount']);
            }
            
            // Update order status
            $this->orderModel->updatePaymentStatus(
                $orderId,
                'paid',
                $parsedData['transaction_id']
            );
            
            // Update product sales count
            $orderWithItems = $this->orderModel->getOrderWithItems($orderId);
            if (!empty($orderWithItems['items'])) {
                foreach ($orderWithItems['items'] as $item) {
                    $this->productModel->incrementSales($item['product_id'], $item['quantity']);
                }
            }
            
            // Mark webhook as processed
            if ($logId) {
                $this->webhookLogModel->markProcessed($logId, true);
            }
            
            return [
                'success' => true,
                'message' => 'Webhook processed successfully',
                'order_id' => $orderId
            ];
            
        } catch (Exception $e) {
            // Log error
            if ($logId) {
                $this->webhookLogModel->markProcessed($logId, false, $e->getMessage());
            }
            
            // Return error response
            http_response_code(500);
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
}
