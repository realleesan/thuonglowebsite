<?php
/**
 * Webhook Controller
 * Handles incoming webhooks from SePay payment gateway
 */

require_once __DIR__ . '/../services/SepayService.php';
require_once __DIR__ . '/../services/WalletService.php';
require_once __DIR__ . '/../models/SepayWebhookLogModel.php';
require_once __DIR__ . '/../models/OrdersModel.php';
require_once __DIR__ . '/../models/WithdrawalRequestModel.php';

class WebhookController {
    private SepayService $sepayService;
    private WalletService $walletService;
    private SepayWebhookLogModel $webhookLogModel;
    private OrdersModel $ordersModel;
    private WithdrawalRequestModel $withdrawalModel;
    
    public function __construct() {
        try {
            $this->webhookLogModel = new SepayWebhookLogModel();
            $this->sepayService = new SepayService();
            $this->walletService = new WalletService();
            $this->ordersModel = new OrdersModel();
            $this->withdrawalModel = new WithdrawalRequestModel();
        } catch (Exception $e) {
            // Log error to file if database fails
            $logFile = __DIR__ . '/../../logs/webhook_error.log';
            @file_put_contents($logFile, date('Y-m-d H:i:s') . " Constructor Error: " . $e->getMessage() . "\n", FILE_APPEND);
            throw $e;
        }
    }
    
    /**
     * Handle incoming SePay webhook
     * This endpoint receives POST requests from SePay when transactions occur
     */
    public function handleSepayWebhook(): void {
        // Create log file for debugging
        $debugLogFile = __DIR__ . '/../../logs/webhook_debug.log';
        
        try {
            // Get raw POST data
            $rawData = file_get_contents('php://input');
            
            // Log raw data immediately
            @file_put_contents($debugLogFile, 
                "\n" . str_repeat('=', 80) . "\n" . 
                date('Y-m-d H:i:s') . " - Webhook received\n" .
                "Raw Data: " . $rawData . "\n",
                FILE_APPEND
            );
            
            $webhookData = json_decode($rawData, true);
            
            // Get headers
            $headers = $this->getRequestHeaders();
            
            // Get client IP
            $ipAddress = $_SERVER['REMOTE_ADDR'] ?? null;
            
            // Log the webhook immediately
            $logData = [
                'webhook_type' => 'unknown',
                'raw_data' => $webhookData ?: $rawData,
                'headers' => $headers,
                'ip_address' => $ipAddress,
                'received_at' => date('Y-m-d H:i:s')
            ];
            
            // Validate webhook data
            if (!$webhookData || !is_array($webhookData)) {
                $logData['processing_error'] = 'Invalid JSON data';
                
                @file_put_contents($debugLogFile, "ERROR: Invalid JSON data\n", FILE_APPEND);
                
                try {
                    $this->webhookLogModel->logWebhook($logData);
                } catch (Exception $e) {
                    @file_put_contents($debugLogFile, "ERROR saving to DB: " . $e->getMessage() . "\n", FILE_APPEND);
                }
                
                $this->jsonResponse([
                    'success' => false,
                    'error' => 'Invalid webhook data'
                ], 400);
                return;
            }
            
            @file_put_contents($debugLogFile, "Parsed Data: " . json_encode($webhookData, JSON_PRETTY_PRINT) . "\n", FILE_APPEND);
            
            // Extract transaction details from SePay format
            $transactionId = $webhookData['id'] ?? $webhookData['transaction_id'] ?? null;
            
            // SePay sends content as long string, need to extract reference code
            $content = $webhookData['content'] ?? $webhookData['reference_code'] ?? '';
            $referenceCode = $this->extractReferenceFromContent($content);
            
            @file_put_contents($debugLogFile, "Reference Code: {$referenceCode}\n", FILE_APPEND);
            
            // SePay uses transferAmount instead of amount_in/amount_out
            $amount = $webhookData['transferAmount'] ?? $webhookData['amount_in'] ?? $webhookData['amount_out'] ?? $webhookData['amount'] ?? 0;
            
            // SePay uses transferType: "in" or "out"
            $transferType = $webhookData['transferType'] ?? null;
            $status = 'success'; // If webhook is sent, transaction is successful
            
            $bankAccount = $webhookData['accountNumber'] ?? $webhookData['account_number'] ?? null;
            
            // Update log data
            $logData['transaction_id'] = $transactionId;
            $logData['reference_code'] = $referenceCode;
            $logData['amount'] = $amount;
            $logData['status'] = $status;
            $logData['bank_account'] = $bankAccount;
            $logData['content'] = $content;

            
            // Verify webhook signature if available
            $signature = $headers['X-Sepay-Signature'] ?? $headers['x-sepay-signature'] ?? null;
            $signatureVerified = false;
            
            if ($signature) {
                $signatureVerified = $this->sepayService->verifyWebhookSignature($rawData, $signature);
                $logData['signature'] = $signature;
                $logData['signature_verified'] = $signatureVerified ? 1 : 0;
            }
            
            @file_put_contents($debugLogFile, "Signature verified: " . ($signatureVerified ? 'Yes' : 'No') . "\n", FILE_APPEND);
            
            // Determine webhook type based on transferType or reference code
            if ($transferType === 'in' || strpos($referenceCode, 'DH') === 0) {
                // Payment IN - customer paying for order
                $logData['webhook_type'] = SepayWebhookLogModel::TYPE_PAYMENT_IN;
                $orderId = $this->extractOrderIdFromReference($referenceCode);
                if ($orderId) {
                    $logData['order_id'] = $orderId;
                }
                @file_put_contents($debugLogFile, "Webhook Type: payment_in, Order ID: {$orderId}\n", FILE_APPEND);
            } elseif ($transferType === 'out' || strpos($referenceCode, 'RUT') === 0) {
                // Payment OUT - withdrawal to affiliate
                $logData['webhook_type'] = SepayWebhookLogModel::TYPE_PAYMENT_OUT;
                $withdrawalId = $this->extractWithdrawalIdFromReference($referenceCode);
                if ($withdrawalId) {
                    $logData['withdrawal_id'] = $withdrawalId;
                }
                @file_put_contents($debugLogFile, "Webhook Type: payment_out, Withdrawal ID: {$withdrawalId}\n", FILE_APPEND);
            }
            
            @file_put_contents($debugLogFile, "Attempting to save to database...\n", FILE_APPEND);
            
            // Save webhook log
            try {
                $logId = $this->webhookLogModel->logWebhook($logData);
                @file_put_contents($debugLogFile, "✅ Saved to database with ID: {$logId}\n", FILE_APPEND);
            } catch (Exception $e) {
                @file_put_contents($debugLogFile, "❌ Database save failed: " . $e->getMessage() . "\n", FILE_APPEND);
                throw $e;
            }
            
            // Process webhook based on type
            $success = false;
            $error = null;
            
            try {
                if ($logData['webhook_type'] === SepayWebhookLogModel::TYPE_PAYMENT_IN) {
                    @file_put_contents($debugLogFile, "Processing payment IN...\n", FILE_APPEND);
                    $success = $this->processPaymentIn($webhookData, $logData);
                } elseif ($logData['webhook_type'] === SepayWebhookLogModel::TYPE_PAYMENT_OUT) {
                    @file_put_contents($debugLogFile, "Processing payment OUT...\n", FILE_APPEND);
                    $success = $this->processPaymentOut($webhookData, $logData);
                } else {
                    $error = 'Unknown webhook type';
                    @file_put_contents($debugLogFile, "❌ Unknown webhook type\n", FILE_APPEND);
                }
            } catch (Exception $e) {
                $error = $e->getMessage();
                @file_put_contents($debugLogFile, "❌ Processing error: {$error}\n", FILE_APPEND);
                $success = false;
            }
            
            // Update webhook log with processing result
            $this->webhookLogModel->markProcessed($logId, $success, $error);
            
            @file_put_contents($debugLogFile, "✅ Webhook processing complete. Success: " . ($success ? 'Yes' : 'No') . "\n", FILE_APPEND);
            
            // Return response to SePay
            $this->jsonResponse([
                'success' => $success,
                'message' => $success ? 'Webhook processed successfully' : 'Webhook processing failed',
                'error' => $error
            ], $success ? 200 : 400);
            
        } catch (Exception $e) {
            // Log error
            @file_put_contents($debugLogFile, "❌ FATAL ERROR: " . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n", FILE_APPEND);
            error_log('Webhook error: ' . $e->getMessage());
            
            $this->jsonResponse([
                'success' => false,
                'error' => 'Internal server error'
            ], 500);
        }
    }
    
    /**
     * Process payment IN webhook (customer payment)
     */
    private function processPaymentIn(array $webhookData, array $logData): bool {
        $orderId = $logData['order_id'] ?? null;
        
        if (!$orderId) {
            throw new Exception('Order ID not found in reference code');
        }
        
        // Get order
        $order = $this->ordersModel->find($orderId);
        if (!$order) {
            throw new Exception('Order not found');
        }
        
        // Check if payment amount matches
        $expectedAmount = $order['total'];
        $receivedAmount = $logData['amount'];
        
        if (abs($expectedAmount - $receivedAmount) > 0.01) {
            throw new Exception("Amount mismatch: expected {$expectedAmount}, received {$receivedAmount}");
        }
        
        // Update order payment status
        $this->ordersModel->updatePaymentStatus(
            $orderId,
            'paid',
            $logData['transaction_id']
        );
        
        // Update order status to processing
        $this->ordersModel->updateStatus($orderId, 'processing');
        
        // If order has affiliate, record commission
        if (!empty($order['affiliate_id']) && !empty($order['commission_amount'])) {
            $this->walletService->recordCommission(
                $order['affiliate_id'],
                $order['commission_amount'],
                $orderId,
                'Commission from order ' . $order['order_number']
            );
        }
        
        return true;
    }
    
    /**
     * Process payment OUT webhook (withdrawal to affiliate)
     */
    private function processPaymentOut(array $webhookData, array $logData): bool {
        $withdrawalId = $logData['withdrawal_id'] ?? null;
        
        if (!$withdrawalId) {
            throw new Exception('Withdrawal ID not found in reference code');
        }
        
        // Get withdrawal request
        $withdrawal = $this->withdrawalModel->find($withdrawalId);
        if (!$withdrawal) {
            throw new Exception('Withdrawal request not found');
        }
        
        // Check if already completed
        if ($withdrawal['status'] === WithdrawalRequestModel::STATUS_COMPLETED) {
            return true; // Already processed
        }
        
        // Check if payment amount matches
        $expectedAmount = $withdrawal['net_amount'];
        $receivedAmount = $logData['amount'];
        
        if (abs($expectedAmount - $receivedAmount) > 0.01) {
            throw new Exception("Amount mismatch: expected {$expectedAmount}, received {$receivedAmount}");
        }
        
        // Mark webhook received
        $this->withdrawalModel->markWebhookReceived($withdrawalId, $webhookData);
        
        // Complete withdrawal using WalletService
        $this->walletService->completeWithdrawal($withdrawalId);
        
        return true;
    }
    
    /**
     * Extract reference code from SePay content string
     * Content format: "118650641631-DH1-CHUYEN TIEN-OQCH0007OziG-MOMO118650641631MOMO"
     * We need to extract: DH1
     */
    private function extractReferenceFromContent(string $content): ?string {
        // Try to find DH{number} or RUT{code} pattern
        if (preg_match('/(DH\d+|RUT\d{10})/', $content, $matches)) {
            return $matches[1];
        }
        
        // Fallback: return original content
        return $content;
    }
    
    /**
     * Extract order ID from reference code (e.g., "DH123" -> 123)
     */
    private function extractOrderIdFromReference(string $referenceCode): ?int {
        if (preg_match('/DH(\d+)/', $referenceCode, $matches)) {
            return (int)$matches[1];
        }
        return null;
    }
    
    /**
     * Extract withdrawal ID from reference code
     */
    private function extractWithdrawalIdFromReference(string $referenceCode): ?int {
        // Get withdrawal by withdraw_code
        $withdrawal = $this->withdrawalModel->getByWithdrawCode($referenceCode);
        return $withdrawal ? $withdrawal['id'] : null;
    }
    
    /**
     * Get all request headers
     */
    private function getRequestHeaders(): array {
        $headers = [];
        
        if (function_exists('getallheaders')) {
            $headers = getallheaders();
        } else {
            // Fallback for servers without getallheaders()
            foreach ($_SERVER as $name => $value) {
                if (substr($name, 0, 5) == 'HTTP_') {
                    $headerName = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))));
                    $headers[$headerName] = $value;
                }
            }
        }
        
        return $headers;
    }
    
    /**
     * Send JSON response
     */
    private function jsonResponse(array $data, int $statusCode = 200): void {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    /**
     * Test endpoint to verify webhook is accessible
     */
    public function test(): void {
        $this->jsonResponse([
            'success' => true,
            'message' => 'Webhook endpoint is working',
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    }
}
