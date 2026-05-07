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
            // Get all headers for debugging
            $allHeaders = getallheaders();
            
            // Get raw POST data
            $rawData = file_get_contents('php://input');
            
            // Debug request info
            $method = $_SERVER['REQUEST_METHOD'] ?? 'UNKNOWN';
            $contentType = $_SERVER['CONTENT_TYPE'] ?? 'not set';
            $contentLength = $_SERVER['CONTENT_LENGTH'] ?? 'not set';
            
            // Log raw data immediately
            @file_put_contents($debugLogFile, 
                "\n" . str_repeat('=', 80) . "\n" . 
                date('Y-m-d H:i:s') . " - Webhook received\n" .
                "Method: " . $method . "\n" .
                "Content-Type: " . $contentType . "\n" .
                "Content-Length: " . $contentLength . "\n" .
                "Authorization: " . ($allHeaders['Authorization'] ?? $allHeaders['authorization'] ?? 'NOT SET') . "\n" .
                "Raw Data: " . ($rawData ?: '[EMPTY]') . "\n",
                FILE_APPEND
            );
            
            $webhookData = json_decode($rawData, true);
            @file_put_contents($debugLogFile, "JSON decode result: " . (is_array($webhookData) ? "SUCCESS" : "FAIL") . "\n", FILE_APPEND);
            
            // Get headers
            $headers = $this->getRequestHeaders();
            @file_put_contents($debugLogFile, "Headers extracted\n", FILE_APPEND);
            
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
            
            // Check API Key if configured
            $apiKey = $allHeaders['Authorization'] ?? $allHeaders['authorization'] ?? '';
            @file_put_contents($debugLogFile, "Loading sepay config...\n", FILE_APPEND);
            
            $sepayConfig = [];
            $configPath = __DIR__ . '/../../config/sepay.php';
            if (file_exists($configPath)) {
                try {
                    $sepayConfig = require $configPath;
                    @file_put_contents($debugLogFile, "Sepay config loaded\n", FILE_APPEND);
                } catch (Exception $e) {
                    @file_put_contents($debugLogFile, "Config load error: " . $e->getMessage() . "\n", FILE_APPEND);
                }
            } else {
                @file_put_contents($debugLogFile, "Config file not found: {$configPath}\n", FILE_APPEND);
            }
            
            $expectedApiKey = 'Apikey ' . ($sepayConfig['api_token'] ?? '');
            
            @file_put_contents($debugLogFile, "API Key received: " . substr($apiKey, 0, 30) . "...\n", FILE_APPEND);
            @file_put_contents($debugLogFile, "API Key expected: " . substr($expectedApiKey, 0, 30) . "...\n", FILE_APPEND);
            
            // For now, accept even if API key doesn't match (log only)
            // This helps debugging - you can enable strict check later
            
            // Validate webhook data
            if (!$webhookData || !is_array($webhookData)) {
                $logData['processing_error'] = 'Invalid JSON data: ' . json_last_error_msg();
                
                @file_put_contents($debugLogFile, "ERROR: Invalid JSON data - " . json_last_error_msg() . "\n", FILE_APPEND);
                
                try {
                    $this->webhookLogModel->logWebhook($logData);
                } catch (Exception $e) {
                    @file_put_contents($debugLogFile, "ERROR saving to DB: " . $e->getMessage() . "\n", FILE_APPEND);
                }
                
                $this->jsonResponse([
                    'success' => false,
                    'error' => 'Invalid webhook data: ' . json_last_error_msg()
                ], 400);
                return;
            }
            
            @file_put_contents($debugLogFile, "Parsed Data: " . json_encode($webhookData, JSON_PRETTY_PRINT) . "\n", FILE_APPEND);
            
            // Extract transaction details from SePay format
            $transactionId = $webhookData['id'] ?? $webhookData['transaction_id'] ?? null;
            
            // SePay sends content as long string, need to extract reference code
            $content = $webhookData['content'] ?? $webhookData['reference_code'] ?? '';
            @file_put_contents($debugLogFile, "Content before extract: {$content}\n", FILE_APPEND);
            
            try {
                $referenceCode = $this->extractReferenceFromContent($content);
                @file_put_contents($debugLogFile, "Reference Code: {$referenceCode}\n", FILE_APPEND);
            } catch (Exception $e) {
                @file_put_contents($debugLogFile, "ERROR extracting reference: " . $e->getMessage() . "\n", FILE_APPEND);
                $referenceCode = null;
            }
            
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

            
            // Verify webhook signature if available (optional - don't block processing)
            $signature = $headers['X-Sepay-Signature'] ?? $headers['x-sepay-signature'] ?? null;
            $signatureVerified = false;
            
            if ($signature && is_array($webhookData)) {
                try {
                    $signatureResult = $this->sepayService->verifyWebhookSignature($webhookData, $signature);
                    $signatureVerified = is_bool($signatureResult) ? $signatureResult : false;
                } catch (Exception $e) {
                    // Log but don't fail - continue processing
                    @file_put_contents($debugLogFile, "Signature verification error: " . $e->getMessage() . "\n", FILE_APPEND);
                }
                $logData['signature'] = $signature;
                $logData['signature_verified'] = $signatureVerified ? 1 : 0;
            }
            
            @file_put_contents($debugLogFile, "Signature verified: " . ($signatureVerified ? 'Yes' : 'No') . " (optional, continuing regardless)\n", FILE_APPEND);
            
            // Determine webhook type and extract IDs (but don't save to log to avoid FK issues)
            $orderId = null;
            $withdrawalId = null;
            if ($transferType === 'in' || strpos($referenceCode, 'DH') === 0 || strpos($referenceCode, 'ORD_') === 0) {
                // Payment IN - customer paying for order
                $logData['webhook_type'] = SepayWebhookLogModel::TYPE_PAYMENT_IN;
                $orderId = $this->extractOrderIdFromReference($referenceCode);
                // Don't set order_id in log to avoid FK constraint error
                // $logData['order_id'] = $orderId;
                @file_put_contents($debugLogFile, "Webhook Type: payment_in, Order ID: {$orderId}\n", FILE_APPEND);
            } elseif ($transferType === 'out' || strpos($referenceCode, 'RUT') === 0) {
                // Payment OUT - withdrawal to affiliate
                $logData['webhook_type'] = SepayWebhookLogModel::TYPE_PAYMENT_OUT;
                $withdrawalId = $this->extractWithdrawalIdFromReference($referenceCode);
                // Don't set withdrawal_id in log to avoid FK constraint error
                // $logData['withdrawal_id'] = $withdrawalId;
                @file_put_contents($debugLogFile, "Webhook Type: payment_out, Withdrawal ID: {$withdrawalId}\n", FILE_APPEND);
            }
            
            @file_put_contents($debugLogFile, "Attempting to save to database...\n", FILE_APPEND);
            
            // Save webhook log (optional - don't fail if DB error)
            $logId = null;
            try {
                $logId = $this->webhookLogModel->logWebhook($logData);
                @file_put_contents($debugLogFile, "✅ Saved to database with ID: {$logId}\n", FILE_APPEND);
            } catch (Exception $e) {
                @file_put_contents($debugLogFile, "⚠️ Database save failed (continuing): " . $e->getMessage() . "\n", FILE_APPEND);
                // Continue processing even if log save fails
                $logId = 0;
            }
            
            // Process webhook based on type
            $success = false;
            $error = null;
            
            try {
                @file_put_contents($debugLogFile, "Processing webhook type: " . $logData['webhook_type'] . "\n", FILE_APPEND);
                @file_put_contents($debugLogFile, "Order ID: " . ($orderId ?? 'null') . ", Withdrawal ID: " . ($withdrawalId ?? 'null') . "\n", FILE_APPEND);
                
                if ($logData['webhook_type'] === SepayWebhookLogModel::TYPE_PAYMENT_IN) {
                    @file_put_contents($debugLogFile, "Processing payment IN...\n", FILE_APPEND);
                    $success = $this->processPaymentIn($webhookData, $logData, $orderId);
                } elseif ($logData['webhook_type'] === SepayWebhookLogModel::TYPE_PAYMENT_OUT) {
                    @file_put_contents($debugLogFile, "Processing payment OUT...\n", FILE_APPEND);
                    $success = $this->processPaymentOut($webhookData, $logData, $withdrawalId);
                } else {
                    $error = 'Unknown webhook type: ' . $logData['webhook_type'];
                    @file_put_contents($debugLogFile, "❌ Unknown webhook type\n", FILE_APPEND);
                }
            } catch (Exception $e) {
                $error = $e->getMessage();
                @file_put_contents($debugLogFile, "❌ Processing error: {$error}\n", FILE_APPEND);
                @file_put_contents($debugLogFile, "Stack trace: " . $e->getTraceAsString() . "\n", FILE_APPEND);
                $success = false;
            }
            
            // Update webhook log with processing result (only if we have a valid logId)
            if ($logId > 0) {
                try {
                    $this->webhookLogModel->markProcessed($logId, $success, $error);
                } catch (Exception $e) {
                    @file_put_contents($debugLogFile, "⚠️ Failed to update log (ignoring): " . $e->getMessage() . "\n", FILE_APPEND);
                }
            }
            
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
    private function processPaymentIn(array $webhookData, array $logData, ?int $orderId): bool {
        $debugLogFile = __DIR__ . '/../../logs/webhook_debug.log';
        @file_put_contents($debugLogFile, "processPaymentIn called with orderId: " . var_export($orderId, true) . "\n", FILE_APPEND);
        
        if (!$orderId) {
            throw new Exception('Order ID not found in reference code');
        }
        
        // Get order
        $order = $this->ordersModel->find($orderId);
        if (!$order) {
            throw new Exception('Order not found for ID: ' . $orderId);
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
        
        // Update order status to processing (paid, awaiting fulfillment)
        $this->ordersModel->updateStatus($orderId, 'completed');
        
        // If order has affiliate, process commission
        if (!empty($order['affiliate_id'])) {
            require_once __DIR__ . '/../services/CommissionService.php';
            require_once __DIR__ . '/../services/ErrorHandler.php';
            $errorHandler = new ErrorHandler();
            $commissionService = new CommissionService($errorHandler);
            $commissionResult = $commissionService->processOrderCommission($orderId);
            
            if (!$commissionResult['success']) {
                error_log('Failed to process commission for order ' . $orderId . ': ' . ($commissionResult['message'] ?? 'Unknown error'));
            } else {
                error_log('Commission processed successfully for order ' . $orderId . ': ' . ($commissionResult['commission'] ?? 0) . ' VND');
            }
        }
        
        return true;
    }
    
    /**
     * Process payment OUT webhook (withdrawal to affiliate)
     */
    private function processPaymentOut(array $webhookData, array $logData, ?int $withdrawalId): bool {
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
     * Or: "THANHTOAN ORD_02d358a8-CHUYEN TIEN-..."
     * We need to extract: DH1 or ORD_02d358a8
     */
    private function extractReferenceFromContent(string $content): ?string {
        // Try to find ORD_{code} pattern with underscore (new format from my system)
        if (preg_match('/(ORD_[a-zA-Z0-9_]+)/', $content, $matches)) {
            return $matches[1];
        }
        
        // Try to find ORD{code} pattern WITHOUT underscore (real SePay format)
        if (preg_match('/(ORD[a-zA-Z0-9]+)/', $content, $matches)) {
            return $matches[1];
        }
        
        // Try to find DH{number} or RUT{code} pattern (old format)
        if (preg_match('/(DH\d+|RUT\d{10})/', $content, $matches)) {
            return $matches[1];
        }
        
        // Fallback: return original content
        return $content;
    }
    
    /**
     * Extract order ID from reference code
     * Support formats: "DH123" -> 123, "ORD_02d358a8" -> find by order_number
     */
    private function extractOrderIdFromReference(string $referenceCode): ?int {
        // Old format: DH{number}
        if (preg_match('/DH(\d+)/', $referenceCode, $matches)) {
            return (int)$matches[1];
        }
        
        // New format: ORD_{code} with underscore - find order by order_number
        if (strpos($referenceCode, 'ORD_') === 0) {
            $order = $this->ordersModel->findByOrderNumber($referenceCode);
            if ($order) {
                return (int)$order['id'];
            }
            
            // Try to extract from content if order_number is wrapped in other text
            if (preg_match('/(ORD_[a-zA-Z0-9_]+)/', $referenceCode, $matches)) {
                $order = $this->ordersModel->findByOrderNumber($matches[1]);
                if ($order) {
                    return (int)$order['id'];
                }
            }
        }
        
        // Real SePay format: ORD{code} WITHOUT underscore
        // SePay sends: "THANHTOAN ORD473b21e2-..." -> reference = "ORD473b21e2"
        if (strpos($referenceCode, 'ORD') === 0 && strpos($referenceCode, 'ORD_') !== 0) {
            // Add underscore to match order_number format in DB
            $orderNumberWithUnderscore = 'ORD_' . substr($referenceCode, 3);
            $order = $this->ordersModel->findByOrderNumber($orderNumberWithUnderscore);
            if ($order) {
                return (int)$order['id'];
            }
            
            // Try without underscore (if DB stores as ORDxxx)
            $order = $this->ordersModel->findByOrderNumber($referenceCode);
            if ($order) {
                return (int)$order['id'];
            }
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
    
    /**
     * Handle PayOS payout webhook callback
     * This endpoint receives POST requests from PayOS when payout status changes
     */
    public function handlePayOSWebhook(): void {
        $debugLogFile = __DIR__ . '/../../logs/payos_webhook.log';
        
        try {
            // Get raw POST data
            $rawData = file_get_contents('php://input');
            $webhookData = json_decode($rawData, true);
            
            // Log incoming webhook
            @file_put_contents($debugLogFile, 
                "\n" . str_repeat('=', 80) . "\n" . 
                date('Y-m-d H:i:s') . " - PayOS Webhook received\n" .
                "Raw Data: " . $rawData . "\n",
                FILE_APPEND
            );
            
            // Verify webhook signature
            $headers = $this->getRequestHeaders();
            $signature = $headers['X-Signature'] ?? $headers['x-signature'] ?? ($webhookData['signature'] ?? '');
            
            require_once __DIR__ . '/../services/PayOSService.php';
            $payosService = new PayOSService();
            
            if (!$payosService->verifyWebhookSignature($webhookData, $signature)) {
                @file_put_contents($debugLogFile, "❌ Invalid signature\n", FILE_APPEND);
                $this->jsonResponse(['success' => false, 'error' => 'Invalid signature'], 401);
                return;
            }
            
            // Parse webhook data
            $parsedData = $payosService->parsePayoutWebhook($webhookData);
            
            @file_put_contents($debugLogFile, 
                "Parsed - Payout ID: {$parsedData['payout_id']}, Reference: {$parsedData['reference_id']}, Status: {$parsedData['status']}\n",
                FILE_APPEND
            );
            
            // Find withdrawal by reference_id (withdraw_code)
            $withdrawal = $this->withdrawalModel->getByWithdrawCode($parsedData['reference_id']);
            
            if (!$withdrawal) {
                @file_put_contents($debugLogFile, "❌ Withdrawal not found for code: {$parsedData['reference_id']}\n", FILE_APPEND);
                $this->jsonResponse(['success' => false, 'error' => 'Withdrawal not found'], 404);
                return;
            }
            
            // Update PayOS status in withdrawal record
            $this->withdrawalModel->update($withdrawal['id'], [
                'payos_status' => $parsedData['status'],
                'payos_webhook_data' => json_encode($webhookData),
                'payos_webhook_received_at' => date('Y-m-d H:i:s')
            ]);
            
            // Process based on status
            if (in_array($parsedData['status'], ['COMPLETED', 'SUCCEEDED'], true)) {
                // Complete withdrawal if not already completed
                if ($withdrawal['status'] !== WithdrawalRequestModel::STATUS_COMPLETED) {
                    $this->walletService->completeWithdrawal($withdrawal['id']);
                    @file_put_contents($debugLogFile, "✅ Withdrawal {$withdrawal['id']} completed\n", FILE_APPEND);
                }
            } elseif (in_array($parsedData['status'], ['FAILED', 'CANCELLED'], true)) {
                // Cancel withdrawal and return money to affiliate
                $this->walletService->cancelWithdrawal($withdrawal['id'], 'PayOS payout failed: ' . $parsedData['status']);
                @file_put_contents($debugLogFile, "❌ Withdrawal {$withdrawal['id']} cancelled due to payout failure\n", FILE_APPEND);
            }
            
            $this->jsonResponse(['success' => true, 'message' => 'Webhook processed']);
            
        } catch (Exception $e) {
            @file_put_contents($debugLogFile, "❌ ERROR: " . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n", FILE_APPEND);
            error_log('PayOS webhook error: ' . $e->getMessage());
            
            $this->jsonResponse([
                'success' => false,
                'error' => 'Internal server error'
            ], 500);
        }
    }
}
