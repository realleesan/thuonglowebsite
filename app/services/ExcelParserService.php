<?php
/**
 * Excel Parser Service
 * Parse Excel (.xlsx, .xls) and CSV files
 */

require_once __DIR__ . '/../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class ExcelParserService {
    
    /**
     * Column mapping - map Vietnamese column names to English
     */
    private $columnMapping = [
        // Supplier Name
        'tên nhà cung cấp' => 'supplier_name',
        'ten nha cung cap' => 'supplier_name',
        'nhà cung cấp' => 'supplier_name',
        'nha cung cap' => 'supplier_name',
        'supplier' => 'supplier_name',
        'supplier_name' => 'supplier_name',
        'tên' => 'supplier_name',
        'ten' => 'supplier_name',
        
        // Address
        'địa chỉ' => 'address',
        'dia chi' => 'address',
        'address' => 'address',
        'diachi' => 'address',
        
        // WeChat Account
        'tài khoản wechat' => 'wechat_account',
        'tai khoan wechat' => 'wechat_account',
        'wechat' => 'wechat_account',
        'wechat id' => 'wechat_account',
        'wechat_id' => 'wechat_account',
        'wechataccount' => 'wechat_account',
        
        // Phone
        'số điện thoại' => 'phone',
        'so dien thoai' => 'phone',
        'sdt' => 'phone',
        'phone' => 'phone',
        'mobile' => 'phone',
        'dien thoai' => 'phone',
        
        // WeChat QR
        'qr wechat' => 'wechat_qr',
        'qrwechat' => 'wechat_qr',
        'qr_code' => 'wechat_qr',
        'qrcode' => 'wechat_qr',
        'link qr' => 'wechat_qr',
        'linkqr' => 'wechat_qr',
        'url qr' => 'wechat_qr',
        'urlqr' => 'wechat_qr',
    ];
    
    /**
     * Required columns
     */
    private $requiredColumns = ['supplier_name', 'address', 'wechat_account', 'phone'];
    
    /**
     * Parse Excel/CSV file
     * 
     * @param string $filePath Path to file
     * @return array Result with success status and data/errors
     */
    public function parse($filePath) {
        try {
            // Check file exists
            if (!file_exists($filePath)) {
                return ['success' => false, 'error' => 'File không tồn tại'];
            }
            
            // Get file extension
            $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
            
            // Parse based on extension
            switch ($extension) {
                case 'xlsx':
                case 'xls':
                    return $this->parseExcel($filePath);
                case 'csv':
                    return $this->parseCSV($filePath);
                default:
                    return ['success' => false, 'error' => 'Định dạng file không được hỗ trợ. Vui lòng upload file .xlsx, .xls hoặc .csv'];
            }
            
        } catch (Exception $e) {
            return ['success' => false, 'error' => 'Lỗi khi đọc file: ' . $e->getMessage()];
        }
    }
    
    /**
     * Parse Excel file (.xlsx, .xls)
     */
    private function parseExcel($filePath) {
        try {
            $inputFileType = IOFactory::identify($filePath);
            $reader = IOFactory::createReader($inputFileType);
            $spreadsheet = $reader->load($filePath);
            
            $worksheet = $spreadsheet->getActiveSheet();
            $rows = $worksheet->toArray();
            
            if (empty($rows)) {
                return ['success' => false, 'error' => 'File Excel trống'];
            }
            
            return $this->processRows($rows);
            
        } catch (Exception $e) {
            return ['success' => false, 'error' => 'Lỗi khi đọc Excel: ' . $e->getMessage()];
        }
    }
    
    /**
     * Parse CSV file
     */
    private function parseCSV($filePath) {
        try {
            $rows = [];
            
            // Try to detect encoding and read file
            $content = file_get_contents($filePath);
            $content = mb_convert_encoding($content, 'UTF-8', 'UTF-8,ISO-8859-1');
            
            $handle = fopen('php://memory', 'r+');
            fwrite($handle, $content);
            rewind($handle);
            
            // Read with different delimiters
            $delimiters = [',', ';', '\t', '|'];
            $maxColumns = 0;
            $bestDelimiter = ',';
            
            // First pass: detect delimiter
            foreach ($delimiters as $delimiter) {
                rewind($handle);
                $testRow = fgetcsv($handle, 0, $delimiter);
                if ($testRow && count($testRow) > $maxColumns) {
                    $maxColumns = count($testRow);
                    $bestDelimiter = $delimiter;
                }
            }
            
            // Second pass: read all rows
            rewind($handle);
            while (($row = fgetcsv($handle, 0, $bestDelimiter)) !== false) {
                // Clean up each cell
                $row = array_map('trim', $row);
                if (!empty(array_filter($row))) {
                    $rows[] = $row;
                }
            }
            
            fclose($handle);
            
            if (empty($rows)) {
                return ['success' => false, 'error' => 'File CSV trống'];
            }
            
            return $this->processRows($rows);
            
        } catch (Exception $e) {
            return ['success' => false, 'error' => 'Lỗi khi đọc CSV: ' . $e->getMessage()];
        }
    }
    
    /**
     * Process rows: map columns and validate
     */
    private function processRows($rows) {
        if (count($rows) < 2) {
            return ['success' => false, 'error' => 'File cần có ít nhất 1 dòng dữ liệu (ngoài header)'];
        }
        
        // First row is header
        $headerRow = array_map('strtolower', array_map('trim', $rows[0]));
        
        // Map header to column names
        $columnMap = $this->mapHeader($headerRow);
        
        // Check required columns
        $missingColumns = [];
        foreach ($this->requiredColumns as $required) {
            if (!isset($columnMap[$required])) {
                $missingColumns[] = $required;
            }
        }
        
        if (!empty($missingColumns)) {
            return [
                'success' => false,
                'error' => 'Thiếu cột bắt buộc: ' . implode(', ', $missingColumns) . '. Vui lòng đảm bảo file có các cột: Tên nhà cung cấp, Địa chỉ, Tài khoản WeChat, Số điện thoại'
            ];
        }
        
        // Process data rows
        $data = [];
        $errors = [];
        
        for ($i = 1; $i < count($rows); $i++) {
            $row = $rows[$i];
            
            // Skip empty rows
            if (empty(array_filter($row))) {
                continue;
            }
            
            $item = [];
            $hasData = false;
            
            foreach ($this->requiredColumns as $field) {
                $colIndex = $columnMap[$field] ?? null;
                $value = $colIndex !== null ? ($row[$colIndex] ?? '') : '';
                $value = trim($value);
                
                // Validate required fields
                if (empty($value) && in_array($field, $this->requiredColumns)) {
                    $errors[] = "Dòng " . ($i + 1) . ": Thiếu {$field}";
                    continue 2; // Skip this row
                }
                
                $item[$field] = $value;
                if (!empty($value)) {
                    $hasData = true;
                }
            }
            
            // Get optional fields
            if (isset($columnMap['wechat_qr'])) {
                $colIndex = $columnMap['wechat_qr'];
                $item['wechat_qr'] = isset($row[$colIndex]) ? trim($row[$colIndex]) : '';
            } else {
                $item['wechat_qr'] = '';
            }
            
            if ($hasData) {
                $data[] = $item;
            }
        }
        
        if (empty($data)) {
            return ['success' => false, 'error' => 'Không có dữ liệu hợp lệ trong file'];
        }
        
        return [
            'success' => true,
            'data' => $data,
            'warnings' => $errors,
            'total_rows' => count($data)
        ];
    }
    
    /**
     * Map header row to column names
     */
    private function mapHeader($header) {
        $map = [];
        
        foreach ($header as $index => $columnName) {
            // Clean the column name
            $cleanName = strtolower(trim($columnName));
            $cleanName = preg_replace('/[^a-z0-9\s]/', '', $cleanName); // Remove special chars
            $cleanName = preg_replace('/\s+/', ' ', $cleanName); // Normalize spaces
            
            // Check mapping
            if (isset($this->columnMapping[$cleanName])) {
                $map[$this->columnMapping[$cleanName]] = $index;
            } else {
                // Try partial match
                foreach ($this->columnMapping as $key => $value) {
                    if (strpos($cleanName, $key) !== false) {
                        $map[$value] = $index;
                        break;
                    }
                }
            }
        }
        
        return $map;
    }
    
    /**
     * Generate sample template
     */
    public static function generateTemplate() {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // Set header
        $headers = ['Tên nhà cung cấp', 'Địa chỉ', 'Tài khoản WeChat', 'Số điện thoại', 'QR Wechat (URL)'];
        $sheet->fromArray($headers, null, 'A1');
        
        // Add sample data
        $sampleData = [
            ['Cửa hàng ABC', '123 Đường Nguyễn Trãi, Quận 1, TP.HCM', 'abc_shop', '0912345678', 'https://example.com/qr1.jpg'],
            ['Cửa hàng XYZ', '456 Đường Lê Lợi, Quận 3, TP.HCM', 'xyz_vn', '0987654321', 'https://example.com/qr2.jpg'],
        ];
        $sheet->fromArray($sampleData, null, 'A2');
        
        // Auto-size columns
        foreach (range('A', 'E') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }
        
        return $spreadsheet;
    }
}
