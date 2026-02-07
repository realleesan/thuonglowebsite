<?php
/**
 * Affiliate Data Loader
 * Load và parse dữ liệu từ JSON file cho affiliate system
 */

class AffiliateDataLoader {
    private $dataFile;
    private $data;
    private $error;
    
    /**
     * Constructor
     * @param string $dataFile Path to JSON data file
     */
    public function __construct($dataFile = null) {
        if ($dataFile === null) {
            $dataFile = __DIR__ . '/../app/views/affiliate/data/demo_data.json';
        }
        $this->dataFile = $dataFile;
        $this->data = null;
        $this->error = null;
        
        $this->loadData();
    }
    
    /**
     * Load data from JSON file
     * @return bool Success status
     */
    private function loadData() {
        // Check if file exists
        if (!file_exists($this->dataFile)) {
            $this->error = [
                'code' => 'FILE_NOT_FOUND',
                'message' => 'Không tìm thấy file dữ liệu: ' . $this->dataFile
            ];
            $this->data = [];
            return false;
        }
        
        // Read file content
        $jsonContent = file_get_contents($this->dataFile);
        if ($jsonContent === false) {
            $this->error = [
                'code' => 'FILE_READ_ERROR',
                'message' => 'Không thể đọc file dữ liệu'
            ];
            $this->data = [];
            return false;
        }
        
        // Parse JSON
        $this->data = json_decode($jsonContent, true);
        
        // Check for JSON errors
        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->error = [
                'code' => 'JSON_PARSE_ERROR',
                'message' => 'Dữ liệu JSON không hợp lệ: ' . json_last_error_msg()
            ];
            $this->data = [];
            return false;
        }
        
        // Check if data is empty
        if (empty($this->data)) {
            $this->error = [
                'code' => 'EMPTY_DATA',
                'message' => 'Không có dữ liệu'
            ];
            return false;
        }
        
        return true;
    }
    
    /**
     * Get all data
     * @return array All data from JSON file
     */
    public function getAllData() {
        return $this->data ?? [];
    }
    
    /**
     * Get specific data by key
     * @param string $key Data key (e.g., 'dashboard', 'commissions')
     * @return mixed Data for the specified key, or null if not found
     */
    public function getData($key) {
        if ($this->hasError()) {
            return null;
        }
        
        return $this->data[$key] ?? null;
    }
    
    /**
     * Check if data exists for a key
     * @param string $key Data key
     * @return bool True if data exists
     */
    public function hasData($key) {
        return isset($this->data[$key]);
    }
    
    /**
     * Check if there was an error loading data
     * @return bool True if error occurred
     */
    public function hasError() {
        return $this->error !== null;
    }
    
    /**
     * Get error information
     * @return array|null Error details or null if no error
     */
    public function getError() {
        return $this->error;
    }
    
    /**
     * Get error message
     * @return string|null Error message or null if no error
     */
    public function getErrorMessage() {
        return $this->error['message'] ?? null;
    }
    
    /**
     * Validate data structure for a specific module
     * @param string $module Module name (dashboard, commissions, etc.)
     * @return array Validation result with 'valid' and 'message' keys
     */
    public function validateModule($module) {
        if ($this->hasError()) {
            return [
                'valid' => false,
                'message' => $this->getErrorMessage()
            ];
        }
        
        if (!$this->hasData($module)) {
            return [
                'valid' => false,
                'message' => "Không tìm thấy dữ liệu cho module: {$module}"
            ];
        }
        
        $data = $this->getData($module);
        if (!is_array($data)) {
            return [
                'valid' => false,
                'message' => "Dữ liệu module {$module} không hợp lệ"
            ];
        }
        
        return [
            'valid' => true,
            'message' => 'Dữ liệu hợp lệ'
        ];
    }
    
    /**
     * Get nested data using dot notation
     * @param string $path Dot notation path (e.g., 'dashboard.stats.total_revenue')
     * @return mixed Data at the specified path, or null if not found
     */
    public function getNestedData($path) {
        if ($this->hasError()) {
            return null;
        }
        
        $keys = explode('.', $path);
        $data = $this->data;
        
        foreach ($keys as $key) {
            if (!isset($data[$key])) {
                return null;
            }
            $data = $data[$key];
        }
        
        return $data;
    }
    
    /**
     * Format currency (VND)
     * @param int $amount Amount in VND
     * @return string Formatted currency string
     */
    public static function formatCurrency($amount) {
        return number_format($amount, 0, ',', '.') . ' VNĐ';
    }
    
    /**
     * Format date
     * @param string $date Date string
     * @param string $format Output format (default: 'd/m/Y')
     * @return string Formatted date string
     */
    public static function formatDate($date, $format = 'd/m/Y') {
        $timestamp = strtotime($date);
        if ($timestamp === false) {
            return $date;
        }
        return date($format, $timestamp);
    }
    
    /**
     * Format percentage
     * @param float $value Percentage value
     * @param int $decimals Number of decimal places
     * @return string Formatted percentage string
     */
    public static function formatPercentage($value, $decimals = 1) {
        return number_format($value, $decimals, ',', '.') . '%';
    }
    
    /**
     * Format number with thousand separators
     * @param int|float $number Number to format
     * @param int $decimals Number of decimal places
     * @return string Formatted number string
     */
    public static function formatNumber($number, $decimals = 0) {
        return number_format($number, $decimals, ',', '.');
    }
    
    /**
     * Get dashboard data
     * @return array Dashboard data including stats, charts, customers, etc.
     */
    public function getDashboardData() {
        return $this->getData('dashboard') ?? [];
    }
    
    /**
     * Get commissions data
     * @return array Commissions data
     */
    public function getCommissionsData() {
        return $this->getData('commissions') ?? [];
    }
    
    /**
     * Get customers data
     * @return array Customers data
     */
    public function getCustomersData() {
        return $this->getData('customers') ?? [];
    }
    
    /**
     * Get finance data
     * @return array Finance data
     */
    public function getFinanceData() {
        return $this->getData('finance') ?? [];
    }
    
    /**
     * Get marketing data
     * @return array Marketing data
     */
    public function getMarketingData() {
        return $this->getData('marketing') ?? [];
    }
    
    /**
     * Get profile data
     * @return array Profile data
     */
    public function getProfileData() {
        return $this->getData('profile') ?? [];
    }
    
    /**
     * Get reports data
     * @return array Reports data
     */
    public function getReportsData() {
        return $this->getData('reports') ?? [];
    }
}
