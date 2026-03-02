<?php
/**
 * SePay Service
 * Handles all SePay API integration for payment gateway
 * 
 * Features:
 * - Generate QR code for payment (User → System)
 * - Generate QR code for payout (System → Affiliate)
 * - Verify webhook signatures
 * - Check transaction status
 * - Log all API interactions
 */

require_once __DIR__ . '/BaseService.php';

class SepayService extends BaseService
{
    private array $config;
    private string $apiUrl;
    private string $apiKey;
    private string $apiSecret;
    private string $accountNumber;
    private string $webhookSecret;
    private bool $testMode;
    
    public function __construct(?ErrorHandler $errorHandler = null)
    {
        parent::__construct($errorHandler, 'sepay');
        
        // Load config
        $globalConfig = require __DIR__ . '/../../config.php';
        $this->config = $globalConfig['sepay'] ?? [];
        
        // Set properties
        $this->apiUrl = $this->config['api_url'] ?? 'https://my.sepay.vn/userapi';
        $this->apiKey = $this->config['api_key'] ?? '';
        $this->apiSecret = $this->config['api_secret'] ?? '';
        $this->accountNumber = $this->config['account_number'] ?? '';
        $this->webhookSecret = $this->config['webhook_secret'] ?? '';
        $this->testMode = $this->config['test_mode'] ?? false;
        
        // Validate required config
        if (empty($this->apiKey) || $this->apiKey === 'YOUR_SEPAY_API_KEY_HERE') {
            if ($this->errorHandler) {
                $this->errorHandler->logWarning('SePay API Key not configured');
            }
        }
    }
    
    /**
     * Get account information from SePay
     * 
     * @return array ['success' => bool, 'data' => array, 'message' => string]
     */
    public function getAccountInfo(): array
    {
        try {
            // Call SePay API to get account info
            $response = $this->callAPI('/account-info', 'GET');
            
            if (!$response['success']) {
                return [
                    'success' => false,
                    'message' => $response['message'] ?? 'Failed to get account info',
                    'data' => []
                ];
            }
            
            return [
                'success' => true,
                'data' => $response['data'] ?? [
                    'account_number' => $this->accountNumber,
                    'account_name' => 'SePay Account',
                    'bank_name' => $this->config['bank_code'] ?? 'MB',
                    'balance' => 0
                ],
                'message' => 'Account info retrieved successfully'
            ];
            
        } catch (Exception $e) {
            $this->errorHandler->logError('Failed to get account info', [
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'data' => []
            ];
        }
    }
    
    /**
     * Generate QR code for payment (User pays for order)
     * 
     * @param int $orderId Order ID
     * @param float $amount Amount in VND
     * @param string|null $description Optional description
     * @return array ['success' => bool, 'qr_code' => string, 'qr_url' => string, 'content' => string, 'expires_at' => string]
     */
    public function generatePaymentQR(int $orderId, float $amount, ?string $description = null): array
    {
        try {
            // Generate payment content: DH[OrderId]
            $content = $this->config['order_prefix'] . $orderId;
            
            // Calculate expiration time
            $timeout = $this->config['payment_timeout'] ?? 300;
            $expiresAt = date('Y-m-d H:i:s', time() + $timeout);
            
            // SePay sử dụng public QR API, không cần authentication
            // Format: https://qr.sepay.vn/img?bank=MB&acc=0389654785&amount=10000&des=DH123
            $bankCode = $this->config['bank_code'] ?? 'MB';
            $qrUrl = "https://qr.sepay.vn/img?bank={$bankCode}&acc={$this->accountNumber}&template=compact&amount=" . (int)$amount . "&des={$content}";
            
            // Log success
            $this->logPayment('qr_generated', [
                'order_id' => $orderId,
                'amount' => $amount,
                'content' => $content,
                'expires_at' => $expiresAt,
                'qr_url' => $qrUrl
            ]);
            
            return [
                'success' => true,
                'qr_code' => $qrUrl,
                'qr_url' => $qrUrl,
                'qr_data_url' => $qrUrl,
                'content' => $content,
                'amount' => $amount,
                'account_number' => $this->accountNumber,
                'bank_code' => $bankCode,
                'expires_at' => $expiresAt,
                'timeout' => $timeout,
            ];
            
        } catch (Exception $e) {
            if ($this->errorHandler) {
                $this->errorHandler->logError('Failed to generate payment QR', [
                    'order_id' => $orderId,
                    'amount' => $amount,
                    'error' => $e->getMessage(),
                ]);
            }
            
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }
    
    /**
     * Generate QR code for payout (Admin pays affiliate)
     * 
     * @param string $withdrawCode Withdrawal code (e.g., RUT12345)
     * @param float $amount Amount in VND
     * @param array $bankInfo Bank information ['bank_name', 'account_number', 'account_holder']
     * @return array ['success' => bool, 'qr_code' => string, 'content' => string]
     */
    public function generatePayoutQR(string $withdrawCode, float $amount, array $bankInfo): array
    {
        try {
            // Prepare request data
            $requestData = [
                'amount' => (int)$amount,
                'content' => $withdrawCode,
                'bank_name' => $bankInfo['bank_name'] ?? '',
                'account_number' => $bankInfo['account_number'] ?? '',
                'account_holder' => $bankInfo['account_holder'] ?? '',
            ];
            
            // In test mode, return mock QR
            if ($this->testMode) {
                return $this->generateMockPayoutQR($withdrawCode, $amount, $bankInfo);
            }
            
            // Call SePay API for payout QR
            $response = $this->callAPI('/create-payout-qr', 'POST', $requestData);
            
            if (!$response['success']) {
                throw new Exception($response['message'] ?? 'Failed to generate payout QR');
            }
            
            $qrData = $response['data'] ?? [];
            
            // Log success
            $this->logPayment('payout_qr_generated', [
                'withdraw_code' => $withdrawCode,
                'amount' => $amount,
                'bank_info' => $bankInfo,
            ]);
            
            return [
                'success' => true,
                'qr_code' => $qrData['qr_code'] ?? '',
                'qr_url' => $qrData['qr_url'] ?? '',
                'qr_data_url' => $qrData['qr_data_url'] ?? '',
                'content' => $withdrawCode,
                'amount' => $amount,
                'bank_info' => $bankInfo,
            ];
            
        } catch (Exception $e) {
            $this->errorHandler->logError('Failed to generate payout QR', [
                'withdraw_code' => $withdrawCode,
                'amount' => $amount,
                'error' => $e->getMessage(),
            ]);
            
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }
    
    /**
     * Verify webhook signature from SePay
     * 
     * @param array $data Webhook data
     * @param string $signature Signature from webhook header
     * @return bool True if signature is valid
     */
    public function verifyWebhookSignature(array $data, string $signature): bool
    {
        try {
            // Generate expected signature
            $expectedSignature = $this->generateSignature($data);
            
            // Compare signatures
            $isValid = hash_equals($expectedSignature, $signature);
            
            // Log verification result
            $this->logWebhook('signature_verification', [
                'is_valid' => $isValid,
                'provided_signature' => substr($signature, 0, 20) . '...',
            ]);
            
            return $isValid;
            
        } catch (Exception $e) {
            $this->errorHandler->logError('Webhook signature verification failed', [
                'error' => $e->getMessage(),
            ]);
            
            return false;
        }
    }
    
    /**
     * Check transaction status
     * 
     * @param string $transactionId SePay transaction ID
     * @return array Transaction status data
     */
    public function checkTransactionStatus(string $transactionId): array
    {
        try {
            if ($this->testMode) {
                return [
                    'success' => true,
                    'status' => 'completed',
                    'transaction_id' => $transactionId,
                ];
            }
            
            $response = $this->callAPI('/transaction/' . $transactionId, 'GET');
            
            if (!$response['success']) {
                throw new Exception($response['message'] ?? 'Failed to check transaction');
            }
            
            return [
                'success' => true,
                'data' => $response['data'] ?? [],
            ];
            
        } catch (Exception $e) {
            $this->errorHandler->logError('Failed to check transaction status', [
                'transaction_id' => $transactionId,
                'error' => $e->getMessage(),
            ]);
            
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }
    
    /**
     * Parse webhook data from SePay
     * 
     * @param array $webhookData Raw webhook data
     * @return array Parsed webhook data
     */
    public function parseWebhookData(array $webhookData): array
    {
        return [
            'transaction_id' => $webhookData['transaction_id'] ?? $webhookData['id'] ?? null,
            'reference_code' => $webhookData['content'] ?? $webhookData['reference'] ?? null,
            'amount' => (float)($webhookData['amount'] ?? 0),
            'status' => $webhookData['status'] ?? 'unknown',
            'bank_account' => $webhookData['account_number'] ?? null,
            'transaction_date' => $webhookData['transaction_date'] ?? date('Y-m-d H:i:s'),
            'description' => $webhookData['description'] ?? null,
            'raw_data' => $webhookData,
        ];
    }
    
    /**
     * Determine webhook type (payment_in or payment_out)
     * 
     * @param array $webhookData Webhook data
     * @return string 'payment_in', 'payment_out', or 'unknown'
     */
    public function determineWebhookType(array $webhookData): string
    {
        $content = $webhookData['content'] ?? '';
        
        // Check if it's an order payment (DH prefix)
        if (strpos($content, $this->config['order_prefix']) === 0) {
            return 'payment_in';
        }
        
        // Check if it's a withdrawal (RUT prefix)
        if (strpos($content, $this->config['withdrawal_prefix']) === 0) {
            return 'payment_out';
        }
        
        return 'unknown';
    }
    
    /**
     * Extract order ID from payment content
     * 
     * @param string $content Payment content (e.g., DH123)
     * @return int|null Order ID or null if invalid
     */
    public function extractOrderId(string $content): ?int
    {
        $prefix = $this->config['order_prefix'];
        if (strpos($content, $prefix) === 0) {
            $orderId = substr($content, strlen($prefix));
            return is_numeric($orderId) ? (int)$orderId : null;
        }
        return null;
    }
    
    /**
     * Extract withdrawal code from payout content
     * 
     * @param string $content Payout content (e.g., RUT12345)
     * @return string|null Withdrawal code or null if invalid
     */
    public function extractWithdrawCode(string $content): ?string
    {
        $prefix = $this->config['withdrawal_prefix'];
        if (strpos($content, $prefix) === 0) {
            return $content;
        }
        return null;
    }
    
    // ==================== PRIVATE METHODS ====================
    
    /**
     * Call SePay API
     */
    private function callAPI(string $endpoint, string $method = 'GET', array $data = []): array
    {
        $url = $this->apiUrl . $endpoint;
        
        $headers = [
            'Authorization: Bearer ' . $this->apiKey,
            'Content-Type: application/json',
            'Accept: application/json',
        ];
        
        $ch = curl_init();
        
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        
        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        
        curl_close($ch);
        
        if ($error) {
            throw new Exception('cURL Error: ' . $error);
        }
        
        $responseData = json_decode($response, true);
        
        if ($httpCode !== 200) {
            throw new Exception('API Error: HTTP ' . $httpCode . ' - ' . ($responseData['message'] ?? 'Unknown error'));
        }
        
        return $responseData;
    }
    
    /**
     * Generate signature for webhook verification
     */
    private function generateSignature(array $data): string
    {
        ksort($data);
        $dataString = json_encode($data);
        return hash_hmac('sha256', $dataString, $this->webhookSecret);
    }
    
    /**
     * Generate mock payment QR for testing
     */
    private function generateMockPaymentQR(int $orderId, float $amount, string $content): array
    {
        $timeout = $this->config['payment_timeout'] ?? 120;
        $expiresAt = date('Y-m-d H:i:s', time() + $timeout);
        
        return [
            'success' => true,
            'qr_code' => 'MOCK_QR_CODE_' . $orderId,
            'qr_url' => 'https://mock.sepay.vn/qr/' . $orderId,
            'qr_data_url' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==',
            'content' => $content,
            'amount' => $amount,
            'account_number' => $this->accountNumber,
            'bank_code' => 'MB',
            'expires_at' => $expiresAt,
            'timeout' => $timeout,
            'test_mode' => true,
        ];
    }
    
    /**
     * Generate mock payout QR for testing
     */
    private function generateMockPayoutQR(string $withdrawCode, float $amount, array $bankInfo): array
    {
        return [
            'success' => true,
            'qr_code' => 'MOCK_PAYOUT_QR_' . $withdrawCode,
            'qr_url' => 'https://mock.sepay.vn/payout/' . $withdrawCode,
            'qr_data_url' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==',
            'content' => $withdrawCode,
            'amount' => $amount,
            'bank_info' => $bankInfo,
            'test_mode' => true,
        ];
    }
    
    /**
     * Log payment activity
     */
    private function logPayment(string $action, array $data): void
    {
        $logFile = __DIR__ . '/../../logs/payment.log';
        $logEntry = date('Y-m-d H:i:s') . " | $action | " . json_encode($data) . PHP_EOL;
        @file_put_contents($logFile, $logEntry, FILE_APPEND);
    }
    
    /**
     * Log webhook activity
     */
    private function logWebhook(string $action, array $data): void
    {
        $logFile = __DIR__ . '/../../logs/webhook.log';
        $logEntry = date('Y-m-d H:i:s') . " | $action | " . json_encode($data) . PHP_EOL;
        @file_put_contents($logFile, $logEntry, FILE_APPEND);
    }
}
