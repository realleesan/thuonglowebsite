<?php
/**
 * Withdrawal Export Service
 * Xuất file Excel/CSV cho chuyển tiền hàng loạt qua app ngân hàng
 */

require_once __DIR__ . '/../models/WithdrawalRequestModel.php';

class WithdrawalExportService {
    
    private $withdrawalModel;
    
    public function __construct() {
        $this->withdrawalModel = new WithdrawalRequestModel();
    }
    
    /**
     * Export pending withdrawals to CSV format (MB Bank, TPBank, Vietcombank compatible)
     * 
     * @param array $withdrawalIds Array of withdrawal IDs to export
     * @return array ['success' => bool, 'content' => string, 'filename' => string]
     */
    public function exportToBankCSV(array $withdrawalIds): array {
        try {
            if (empty($withdrawalIds)) {
                throw new Exception('Không có yêu cầu rút tiền nào được chọn');
            }
            
            // Get withdrawal details
            $withdrawals = [];
            foreach ($withdrawalIds as $id) {
                $withdrawal = $this->withdrawalModel->getWithDetails($id);
                if ($withdrawal && $withdrawal['status'] === 'pending') {
                    $withdrawals[] = $withdrawal;
                }
            }
            
            if (empty($withdrawals)) {
                throw new Exception('Không tìm thấy yêu cầu rút tiền hợp lệ');
            }
            
            // Generate CSV content (MB Bank format - most common)
            $csv = $this->generateMBBankCSV($withdrawals);
            
            $filename = 'chuyen_tien_hang_loat_' . date('Ymd_His') . '.csv';
            
            return [
                'success' => true,
                'content' => $csv,
                'filename' => $filename,
                'count' => count($withdrawals),
                'total_amount' => array_sum(array_column($withdrawals, 'net_amount'))
            ];
            
        } catch (Exception $e) {
            error_log('Export withdrawal error: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Generate CSV in MB Bank format
     * Format: STT, Tên người thụ hưởng, Số tài khoản, Tên ngân hàng, Số tiền, Nội dung
     */
    private function generateMBBankCSV(array $withdrawals): string {
        $lines = [];
        
        // Header
        $lines[] = "STT,Ten nguoi thu huong,So tai khoan,Ten ngan hang,So tien,Noi dung chuyen khoan";
        
        // Data rows
        $stt = 1;
        foreach ($withdrawals as $withdrawal) {
            $tenThuHuong = $this->cleanForCSV($withdrawal['account_holder'] ?? '');
            $soTaiKhoan = $withdrawal['bank_account'] ?? '';
            $tenNganHang = $this->cleanForCSV($this->normalizeBankName($withdrawal['bank_name'] ?? ''));
            $soTien = $withdrawal['net_amount'] ?? 0;
            $noiDung = $this->cleanForCSV("Rut tien hoa hong " . ($withdrawal['withdraw_code'] ?? ''));
            
            $lines[] = "{$stt},{$tenThuHuong},{$soTaiKhoan},{$tenNganHang},{$soTien},{$noiDung}";
            $stt++;
        }
        
        return implode("\n", $lines);
    }
    
    /**
     * Generate CSV in TPBank format
     * Format: STT, Tên người nhận, Số TK người nhận, Ngân hàng, Số tiền, Nội dung
     */
    public function exportToTPBankCSV(array $withdrawalIds): array {
        try {
            if (empty($withdrawalIds)) {
                throw new Exception('Không có yêu cầu rút tiền nào được chọn');
            }
            
            $withdrawals = [];
            foreach ($withdrawalIds as $id) {
                $withdrawal = $this->withdrawalModel->getWithDetails($id);
                if ($withdrawal && $withdrawal['status'] === 'pending') {
                    $withdrawals[] = $withdrawal;
                }
            }
            
            if (empty($withdrawals)) {
                throw new Exception('Không tìm thấy yêu cầu rút tiền hợp lệ');
            }
            
            $lines = [];
            $lines[] = "STT,Ten nguoi nhan,So TK nguoi nhan,Ngan hang,So tien,Noi dung";
            
            $stt = 1;
            foreach ($withdrawals as $withdrawal) {
                $tenNguoiNhan = $this->cleanForCSV($withdrawal['account_holder'] ?? '');
                $soTK = $withdrawal['bank_account'] ?? '';
                $nganHang = $this->cleanForCSV($this->normalizeBankName($withdrawal['bank_name'] ?? ''));
                $soTien = $withdrawal['net_amount'] ?? 0;
                $noiDung = $this->cleanForCSV("Rut tien " . ($withdrawal['withdraw_code'] ?? ''));
                
                $lines[] = "{$stt},{$tenNguoiNhan},{$soTK},{$nganHang},{$soTien},{$noiDung}";
                $stt++;
            }
            
            return [
                'success' => true,
                'content' => implode("\n", $lines),
                'filename' => 'tpbank_chuyen_tien_' . date('Ymd_His') . '.csv',
                'count' => count($withdrawals),
                'total_amount' => array_sum(array_column($withdrawals, 'net_amount'))
            ];
            
        } catch (Exception $e) {
            error_log('Export TPBank CSV error: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Generate CSV in Vietcombank format
     */
    public function exportToVietcombankCSV(array $withdrawalIds): array {
        try {
            if (empty($withdrawalIds)) {
                throw new Exception('Không có yêu cầu rút tiền nào được chọn');
            }
            
            $withdrawals = [];
            foreach ($withdrawalIds as $id) {
                $withdrawal = $this->withdrawalModel->getWithDetails($id);
                if ($withdrawal && $withdrawal['status'] === 'pending') {
                    $withdrawals[] = $withdrawal;
                }
            }
            
            if (empty($withdrawals)) {
                throw new Exception('Không tìm thấy yêu cầu rút tiền hợp lệ');
            }
            
            $lines = [];
            $lines[] = "Beneficiary Name,Account Number,Bank Name,Amount,Description";
            
            foreach ($withdrawals as $withdrawal) {
                $beneficiary = $this->cleanForCSV($withdrawal['account_holder'] ?? '');
                $account = $withdrawal['bank_account'] ?? '';
                $bank = $this->cleanForCSV($this->normalizeBankName($withdrawal['bank_name'] ?? ''));
                $amount = $withdrawal['net_amount'] ?? 0;
                $desc = $this->cleanForCSV("Rut tien " . ($withdrawal['withdraw_code'] ?? ''));
                
                $lines[] = "{$beneficiary},{$account},{$bank},{$amount},{$desc}";
            }
            
            return [
                'success' => true,
                'content' => implode("\n", $lines),
                'filename' => 'vietcombank_chuyen_tien_' . date('Ymd_His') . '.csv',
                'count' => count($withdrawals),
                'total_amount' => array_sum(array_column($withdrawals, 'net_amount'))
            ];
            
        } catch (Exception $e) {
            error_log('Export Vietcombank CSV error: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Get all pending withdrawals for bulk export
     */
    public function getPendingWithdrawals(): array {
        return $this->withdrawalModel->getByStatus('pending', 1000);
    }
    
    /**
     * Clean string for CSV export
     */
    private function cleanForCSV(string $text): string {
        // Remove newlines and tabs
        $text = str_replace(["\n", "\r", "\t"], ' ', $text);
        // Remove commas
        $text = str_replace(',', ' ', $text);
        // Remove quotes
        $text = str_replace(['"', "'"], '', $text);
        // Trim
        $text = trim($text);
        return $text;
    }
    
    /**
     * Normalize bank name to standard format for banking apps
     */
    private function normalizeBankName(string $bankName): string {
        $bankMapping = [
            // Vietcombank variations
            'vietcombank' => 'Vietcombank',
            'vcb' => 'Vietcombank',
            'ngan hang ngoai thuong' => 'Vietcombank',
            
            // MB Bank variations  
            'mbbank' => 'MB Bank',
            'mb' => 'MB Bank',
            'quân đội' => 'MB Bank',
            
            // TPBank variations
            'tpbank' => 'TPBank',
            'tiên phong' => 'TPBank',
            
            // VietinBank
            'vietinbank' => 'VietinBank',
            'công thương' => 'VietinBank',
            
            // BIDV
            'bidv' => 'BIDV',
            'đầu tư và phát triển' => 'BIDV',
            
            // ACB
            'acb' => 'ACB',
            'á châu' => 'ACB',
            
            // Techcombank
            'techcombank' => 'Techcombank',
            'kỹ thương' => 'Techcombank',
            
            // Sacombank
            'sacombank' => 'Sacombank',
            'sài gòn thương tín' => 'Sacombank',
            
            // VPBank
            'vpbank' => 'VPBank',
            'việt nam thịnh vượng' => 'VPBank',
            
            // Agribank
            'agribank' => 'Agribank',
            'nông nghiệp' => 'Agribank',
            
            // MSB
            'msb' => 'MSB',
            'hàng hải' => 'MSB',
            
            // Eximbank
            'eximbank' => 'Eximbank',
            'xuất nhập khẩu' => 'Eximbank',
            
            // SHB
            'shb' => 'SHB',
            'sài gòn hà nội' => 'SHB',
            
            // VIB
            'vib' => 'VIB',
            'quốc tế' => 'VIB',
            
            // OCB
            'ocb' => 'OCB',
            'orient' => 'OCB',
            
            // SeABank
            'seabank' => 'SeABank',
            'đông nam á' => 'SeABank',
        ];
        
        $normalized = mb_strtolower(trim($bankName), 'UTF-8');
        
        // Check exact match first
        if (isset($bankMapping[$normalized])) {
            return $bankMapping[$normalized];
        }
        
        // Check partial match
        foreach ($bankMapping as $key => $value) {
            if (strpos($normalized, $key) !== false) {
                return $value;
            }
        }
        
        // Return original if no match
        return $bankName;
    }
}
