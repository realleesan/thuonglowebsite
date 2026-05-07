<?php
/**
 * PayOS Service
 * Handles PayOS API integration for payout/withdrawal operations
 * 
 * Features:
 * - Create payout request to transfer money to affiliate bank account
 * - Check payout status
 * - Verify webhook signatures for payout callbacks
 * - Handle PayOS API authentication
 */

require_once __DIR__ . '/BaseService.php';

class PayOSService extends BaseService
{
    private array $config;
    private string $apiUrl;
    private string $clientId;
    private string $apiKey;
    private string $checksumKey;
    private bool $testMode;
    
    public function __construct(?ErrorHandler $errorHandler = null)
    {
        parent::__construct($errorHandler, 'payos');
        
        // Load config
        $globalConfig = require __DIR__ . '/../../config.php';
        $this->config = $globalConfig['payos'] ?? [];
        
        // Set properties
        $this->testMode = $this->config['test_mode'] ?? true;
        $this->apiUrl = $this->config['api_url'] ?? 'https://api-merchant.payos.vn';
        $this->clientId = $this->config['client_id'] ?? '';
        $this->apiKey = $this->config['api_key'] ?? '';
        $this->checksumKey = $this->config['checksum_key'] ?? '';
        
        // Validate required config
        if (empty($this->clientId) || empty($this->apiKey)) {
            if ($this->errorHandler) {
                $this->errorHandler->logWarning('PayOS credentials not configured');
            }
        }
    }
    
    /**
     * Create payout request to transfer money to affiliate bank account
     * 
     * @param string $withdrawCode Unique withdrawal code (e.g., RUT12345)
     * @param float $amount Amount in VND
     * @param array $bankInfo Bank information ['bank_name', 'bank_code', 'account_number', 'account_holder']
     * @param string $description Payment description
     * @return array ['success' => bool, 'payout_id' => string, 'status' => string, 'message' => string]
     */
    public function createPayout(string $withdrawCode, float $amount, array $bankInfo, string $description = ''): array
    {
        try {
            // Validate bank info
            if (empty($bankInfo['account_number']) || empty($bankInfo['bank_code'])) {
                return [
                    'success' => false,
                    'message' => 'Missing required bank information: account_number or bank_code'
                ];
            }
            
            // Convert amount to integer (VND, no decimal)
            $amountInt = (int)round($amount);
            
            // Prepare request data
            $requestData = [
                'referenceId' => $withdrawCode,
                'amount' => $amountInt,
                'description' => $description ?: "Rut tien hoa hong {$withdrawCode}",
                'toBin' => $bankInfo['bank_code'],        // Bank BIN code (e.g., 970422 for MB)
                'toAccountNumber' => $bankInfo['account_number'],
                'category' => ['affiliate_withdrawal']
            ];
            
            // Generate signature
            $signature = $this->generatePayoutSignature($requestData);
            $idempotencyKey = $this->generateIdempotencyKey($withdrawCode);
            
            // In test mode, return mock response
            if ($this->testMode) {
                return $this->createMockPayoutResponse($withdrawCode, $amountInt, $bankInfo);
            }
            
            // Call PayOS API
            $response = $this->callAPI('/v1/payouts', 'POST', $requestData, [
                'x-signature' => $signature,
                'x-idempotency-key' => $idempotencyKey,
            ]);
            
            if (!$response['success']) {
                return [
                    'success' => false,
                    'message' => $response['message'] ?? 'Failed to create payout',
                    'error_code' => $response['code'] ?? null
                ];
            }
            
            $responseData = $response['data'] ?? [];
            
            // Log success
            $this->logPayout('payout_created', [
                'withdraw_code' => $withdrawCode,
                'amount' => $amountInt,
                'bank_info' => $bankInfo,
                'payout_id' => $responseData['id'] ?? null,
                'status' => $responseData['approvalState'] ?? 'PROCESSING'
            ]);
            
            return [
                'success' => true,
                'payout_id' => $responseData['id'] ?? null,
                'reference_id' => $responseData['referenceId'] ?? $withdrawCode,
                'status' => $responseData['approvalState'] ?? 'PROCESSING',
                'transactions' => $responseData['transactions'] ?? [],
                'message' => 'Payout request created successfully'
            ];
            
        } catch (Exception $e) {
            $this->errorHandler->logError('Failed to create payout', [
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
     * Check payout status
     * 
     * @param string $payoutId PayOS payout ID
     * @return array ['success' => bool, 'status' => string, 'data' => array]
     */
    public function checkPayoutStatus(string $payoutId): array
    {
        try {
            if ($this->testMode) {
                return [
                    'success' => true,
                    'status' => 'COMPLETED',
                    'data' => [
                        'id' => $payoutId,
                        'approvalState' => 'COMPLETED'
                    ]
                ];
            }
            
            $response = $this->callAPI("/v1/payouts/{$payoutId}", 'GET');
            
            if (!$response['success']) {
                return [
                    'success' => false,
                    'message' => $response['message'] ?? 'Failed to check payout status'
                ];
            }
            
            $data = $response['data'] ?? [];
            
            return [
                'success' => true,
                'status' => $data['approvalState'] ?? 'UNKNOWN',
                'data' => $data
            ];
            
        } catch (Exception $e) {
            $this->errorHandler->logError('Failed to check payout status', [
                'payout_id' => $payoutId,
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Verify webhook signature from PayOS
     * 
     * @param array $data Webhook data
     * @param string $signature Signature from header
     * @return bool
     */
    public function verifyWebhookSignature(array $data, string $signature): bool
    {
        try {
            if (empty($signature)) {
                $signature = $data['signature'] ?? '';
            }
            if (empty($signature)) {
                return false;
            }

            $payloadData = $data['data'] ?? $data;
            $computedSignature = $this->generatePayoutSignature($payloadData);
            return hash_equals($computedSignature, $signature);
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Parse webhook data for payout callback
     * 
     * @param array $webhookData Raw webhook data
     * @return array Parsed data
     */
    public function parsePayoutWebhook(array $webhookData): array
    {
        $payload = $webhookData['data'] ?? $webhookData;
        $approvalState = $payload['approvalState'] ?? null;

        return [
            'payout_id' => $payload['id'] ?? null,
            'reference_id' => $payload['referenceId'] ?? null,
            'status' => $approvalState,
            'amount' => $payload['amount'] ?? 0,
            'description' => $payload['description'] ?? null,
            'transactions' => $payload['transactions'] ?? [],
            'raw_data' => $webhookData
        ];
    }
    
    /**
     * Get bank list supported by PayOS
     * 
     * @return array List of supported banks
     */
    public function getSupportedBanks(): array
    {
        // Common Vietnamese bank BIN codes
        return [
            ['code' => '970422', 'name' => 'MB Bank', 'short_name' => 'MB'],
            ['code' => '970436', 'name' => 'Vietcombank', 'short_name' => 'VCB'],
            ['code' => '970418', 'name' => 'BIDV', 'short_name' => 'BIDV'],
            ['code' => '970405', 'name' => 'Agribank', 'short_name' => 'AGB'],
            ['code' => '970448', 'name' => 'OCB', 'short_name' => 'OCB'],
            ['code' => '970454', 'name' => 'VietinBank', 'short_name' => 'CTG'],
            ['code' => '970403', 'name' => 'Sacombank', 'short_name' => 'STB'],
            ['code' => '970407', 'name' => 'Techcombank', 'short_name' => 'TCB'],
            ['code' => '970409', 'name' => 'ACB', 'short_name' => 'ACB'],
            ['code' => '970416', 'name' => 'DongA Bank', 'short_name' => 'DAB'],
            ['code' => '970423', 'name' => 'TPBank', 'short_name' => 'TPB'],
            ['code' => '970437', 'name' => 'HDBank', 'short_name' => 'HDB'],
            ['code' => '970441', 'name' => 'VPBank', 'short_name' => 'VPB'],
            ['code' => '970443', 'name' => 'SHB', 'short_name' => 'SHB'],
            ['code' => '970452', 'name' => 'VietCapital Bank', 'short_name' => 'VCB'],
        ];
    }
    
    /**
     * Map bank name to BIN code
     * 
     * @param string $bankName Bank name
     * @return string|null BIN code or null if not found
     */
    public function getBankBinByName(string $bankName): ?string
    {
        $banks = $this->getSupportedBanks();
        
        // Try exact match first
        foreach ($banks as $bank) {
            if (strcasecmp($bank['name'], $bankName) === 0 || 
                strcasecmp($bank['short_name'], $bankName) === 0) {
                return $bank['code'];
            }
        }
        
        // Try partial match
        foreach ($banks as $bank) {
            if (stripos($bank['name'], $bankName) !== false || 
                stripos($bankName, $bank['short_name']) !== false) {
                return $bank['code'];
            }
        }
        
        return null;
    }
    
    // ==================== PRIVATE METHODS ====================
    
    /**
     * Call PayOS API
     */
    private function callAPI(string $endpoint, string $method = 'GET', array $data = [], array $extraHeaders = []): array
    {
        $url = $this->apiUrl . $endpoint;
        
        $headers = [
            'x-client-id: ' . $this->clientId,
            'x-api-key: ' . $this->apiKey,
            'Content-Type: application/json',
        ];
        
        foreach ($extraHeaders as $headerKey => $headerValue) {
            if ($headerValue !== null && $headerValue !== '') {
                $headers[] = $headerKey . ': ' . $headerValue;
            }
        }
        
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
        
        if ($httpCode < 200 || $httpCode >= 300) {
            return [
                'success' => false,
                'code' => $httpCode,
                'message' => $responseData['desc'] ?? $responseData['message'] ?? 'HTTP Error ' . $httpCode
            ];
        }
        
        // Check PayOS specific response code
        if (isset($responseData['code']) && $responseData['code'] !== '00') {
            return [
                'success' => false,
                'code' => $responseData['code'],
                'message' => $responseData['desc'] ?? 'PayOS API Error'
            ];
        }
        
        return [
            'success' => true,
            'data' => $responseData['data'] ?? $responseData
        ];
    }
    
    /**
     * Generate signature for request authentication
     */
    private function generatePayoutSignature(array $data): string
    {
        // Sort data by key
        ksort($data);
        
        // Create data string with key=value pairs joined by '&'
        $pairs = [];
        foreach ($data as $key => $value) {
            if ($value === null) {
                $value = '';
            }
            if (is_array($value)) {
                foreach ($value as $arrayValue) {
                    $pairs[] = $key . '=' . rawurlencode((string)$arrayValue);
                }
                continue;
            }
            $pairs[] = $key . '=' . rawurlencode((string)$value);
        }

        $dataString = implode('&', $pairs);
        return hash_hmac('sha256', $dataString, $this->checksumKey);
    }

    /**
     * Generate deterministic idempotency key for payout request
     */
    private function generateIdempotencyKey(string $withdrawCode): string
    {
        return 'wd-' . strtolower($withdrawCode);
    }
    
    /**
     * Create mock payout response for testing
     */
    private function createMockPayoutResponse(string $withdrawCode, int $amount, array $bankInfo): array
    {
        $payoutId = 'payout_' . uniqid();
        
        return [
            'success' => true,
            'payout_id' => $payoutId,
            'reference_id' => $withdrawCode,
            'status' => 'PROCESSING',
            'transactions' => [
                [
                    'id' => 'txn_' . uniqid(),
                    'referenceId' => $withdrawCode,
                    'amount' => $amount,
                    'description' => "Rut tien {$withdrawCode}",
                    'toBin' => $bankInfo['bank_code'] ?? '970422',
                    'toAccountNumber' => $bankInfo['account_number'],
                    'toAccountName' => $bankInfo['account_holder'] ?? '',
                    'state' => 'PROCESSING'
                ]
            ],
            'message' => '[TEST MODE] Payout request created (mock)'
        ];
    }
    
    /**
     * Log payout activity
     */
    private function logPayout(string $action, array $data): void
    {
        $logFile = __DIR__ . '/../../logs/payos_payout.log';
        $logEntry = date('Y-m-d H:i:s') . " | $action | " . json_encode($data) . PHP_EOL;
        @file_put_contents($logFile, $logEntry, FILE_APPEND);
    }
}
