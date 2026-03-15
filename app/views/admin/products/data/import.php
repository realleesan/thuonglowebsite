<?php
/**
 * Excel Import Handler for Product Data
 * Xử lý import dữ liệu từ file Excel (xlsx, csv)
 */

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Set headers for JSON response
header('Content-Type: application/json');
header('Cache-Control: no-cache, must-revalidate');

// Allow CORS
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-Requested-With');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Khởi tạo
require_once __DIR__ . '/../../../../core/view_init.php';

require_once __DIR__ . '/../../../models/ProductDataModel.php';

// Response helper
function jsonResponse($success, $message = '', $data = [], $code = 200) {
    http_response_code($code);
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data
    ]);
    exit;
}

// Check authentication
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    jsonResponse(false, 'Unauthorized', [], 401);
}

// Get product ID
$productId = (int)($_POST['product_id'] ?? $_GET['product_id'] ?? 0);

if (!$productId) {
    jsonResponse(false, 'Thiếu ID sản phẩm', [], 400);
}

// Check if file was uploaded
if (!isset($_FILES['excel_file']) || $_FILES['excel_file']['error'] !== UPLOAD_ERR_OK) {
    $error = $_FILES['excel_file']['error'] ?? 'No file uploaded';
    jsonResponse(false, 'Lỗi upload file: ' . $error, [], 400);
}

$file = $_FILES['excel_file'];
$filename = $file['name'];
$tmpFile = $file['tmp_name'];
$fileSize = $file['size'];

// Validate file extension
$allowedExtensions = ['xlsx', 'csv'];
$ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

if (!in_array($ext, $allowedExtensions)) {
    jsonResponse(false, 'Định dạng file không hợp lệ. Chỉ chấp nhận xlsx và csv', [], 400);
}

// Validate file size (max 10MB)
$maxSize = 10 * 1024 * 1024; // 10MB
if ($fileSize > $maxSize) {
    jsonResponse(false, 'File quá lớn. Kích thước tối đa là 10MB', [], 400);
}

// Validate file is not empty
if ($fileSize === 0) {
    jsonResponse(false, 'File rỗng', [], 400);
}

// Check for preview mode (GET request or preview parameter)
$previewMode = isset($_GET['preview']) || isset($_POST['preview']);

try {
    // Parse Excel file
    $data = parseExcelFile($tmpFile, $ext);
    
    if (empty($data)) {
        jsonResponse(false, 'File không có dữ liệu hoặc định dạng không đúng', [], 400);
    }
    
    // Validate data structure
    $validation = validateData($data);
    
    if (!$validation['valid']) {
        jsonResponse(false, $validation['error'], ['errors' => $validation['errors']], 400);
    }
    
    // If preview mode, return preview data
    if ($previewMode) {
        $previewData = array_slice($data, 0, 10); // First 10 rows
        jsonResponse(true, 'Preview data', [
            'preview' => $previewData,
            'total_rows' => count($data),
            'headers' => $validation['headers']
        ]);
    }
    
    // Perform actual import
    $productDataModel = new ProductDataModel();
    
    // Clear existing data if requested
    if (isset($_POST['replace']) && $_POST['replace'] === 'true') {
        $productDataModel->deleteByProduct($productId);
    }
    
    // Transform data for import
    $importData = transformDataForImport($data, $productId);
    
    // Bulk insert
    $inserted = $productDataModel->bulkInsert($importData);
    
    $count = $productDataModel->countByProduct($productId);
    
    jsonResponse(true, "Import thành công! Đã thêm {$inserted} dòng dữ liệu.", [
        'inserted' => $inserted,
        'total' => $count
    ]);
    
} catch (Exception $e) {
    error_log('Excel Import Error: ' . $e->getMessage());
    jsonResponse(false, 'Lỗi xử lý file: ' . $e->getMessage(), [], 500);
}

/**
 * Parse Excel file (xlsx or csv)
 */
function parseExcelFile($filePath, $ext) {
    $data = [];
    
    if ($ext === 'csv') {
        // Parse CSV
        $data = parseCSV($filePath);
    } else {
        // Parse XLSX using PHPExcel or similar
        $data = parseXLSX($filePath);
    }
    
    return $data;
}

/**
 * Parse CSV file
 */
function parseCSV($filePath) {
    $data = [];
    
    // Detect encoding and convert to UTF-8 if needed
    $content = file_get_contents($filePath);
    $encoding = mb_detect_encoding($content, ['UTF-8', 'ISO-8859-1', 'Windows-1252'], true);
    
    if ($encoding && $encoding !== 'UTF-8') {
        $content = mb_convert_encoding($content, 'UTF-8', $encoding);
    }
    
    // Handle BOM
    if (substr($content, 0, 3) === "\xEF\xBB\xBF") {
        $content = substr($content, 3);
    }
    
    $lines = explode("\n", $content);
    
    $headers = [];
    $firstRow = true;
    
    foreach ($lines as $line) {
        $line = trim($line);
        if (empty($line)) continue;
        
        // Parse CSV line (handle quoted fields)
        $row = parseCSVLine($line);
        
        if ($firstRow) {
            // First row is headers
            $headers = array_map('trim', $row);
            $headers = array_map(function($h) use ($headers) {
                return normalizeHeader($h);
            }, $headers);
            $firstRow = false;
            continue;
        }
        
        if (count($row) > 0) {
            $data[] = $row;
        }
    }
    
    // Combine headers with data
    $result = [];
    foreach ($data as $row) {
        $item = [];
        foreach ($headers as $index => $header) {
            $item[$header] = isset($row[$index]) ? trim($row[$index]) : '';
        }
        $result[] = $item;
    }
    
    return $result;
}

/**
 * Parse a single CSV line handling quoted fields
 */
function parseCSVLine($line) {
    $result = [];
    $current = '';
    $inQuotes = false;
    
    for ($i = 0; $i < strlen($line); $i++) {
        $char = $line[$i];
        $nextChar = $i + 1 < strlen($line) ? $line[$i + 1] : '';
        
        if ($char === '"') {
            if ($inQuotes && $nextChar === '"') {
                $current .= '"';
                $i++; // Skip next quote
            } else {
                $inQuotes = !$inQuotes;
            }
        } elseif ($char === ',' && !$inQuotes) {
            $result[] = $current;
            $current = '';
        } else {
            $current .= $char;
        }
    }
    
    $result[] = $current;
    return $result;
}

/**
 * Parse XLSX file
 */
function parseXLSX($filePath) {
    // Check if PhpSpreadsheet is available
    if (!class_exists('PhpOffice\PhpSpreadsheet\IOFactory')) {
        // Fallback: try to parse as CSV if extension is wrong
        return parseCSV($filePath);
    }
    
    try {
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($filePath);
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray();
        
        if (empty($rows)) {
            return [];
        }
        
        // First row is headers
        $headers = array_map(function($h) {
            return normalizeHeader($h);
        }, array_shift($rows));
        
        $result = [];
        foreach ($rows as $row) {
            if (count(array_filter($row)) > 0) { // Skip empty rows
                $item = [];
                foreach ($headers as $index => $header) {
                    $value = isset($row[$index]) ? trim(strval($row[$index])) : '';
                    $item[$header] = $value;
                }
                $result[] = $item;
            }
        }
        
        return $result;
        
    } catch (Exception $e) {
        throw new Exception('Không thể đọc file Excel: ' . $e->getMessage());
    }
}

/**
 * Normalize header name
 */
function normalizeHeader($header) {
    $header = strtolower(trim($header));
    $header = preg_replace('/[^a-z0-9_]/', '_', $header);
    $header = preg_replace('/_+/', '_', $header);
    $header = trim($header, '_');
    
    // Map common variations
    $mappings = [
        'supplier' => 'supplier_name',
        'supplier_name' => 'supplier_name',
        'nha_cung_cap' => 'supplier_name',
        'ncc' => 'supplier_name',
        'address' => 'address',
        'dia_chi' => 'address',
        'location' => 'address',
        'wechat' => 'wechat_account',
        'wechat_account' => 'wechat_account',
        'wx' => 'wechat_account',
        'zalo' => 'wechat_account',
        'phone' => 'phone',
        'dien_thoai' => 'phone',
        'mobile' => 'phone',
        'tel' => 'phone',
        'qr' => 'wechat_qr',
        'qr_wechat' => 'wechat_qr',
        'wechat_qr' => 'wechat_qr',
        'wx_qr' => 'wechat_qr'
    ];
    
    return $mappings[$header] ?? $header;
}

/**
 * Validate data before import
 */
function validateData($data) {
    $headers = !empty($data) ? array_keys($data[0]) : [];
    
    // Check for required fields
    $validFields = ['supplier_name', 'address', 'wechat_account', 'phone', 'wechat_qr'];
    $hasAnyField = false;
    
    foreach ($data as $index => $row) {
        foreach ($validFields as $field) {
            if (!empty($row[$field])) {
                $hasAnyField = true;
                break;
            }
        }
        if ($hasAnyField) break;
    }
    
    if (!$hasAnyField) {
        return [
            'valid' => false,
            'error' => 'Dữ liệu không có trường hợp lệ. Vui lòng kiểm tra header của file.',
            'errors' => ['Row 1: Không tìm thấy trường dữ liệu hợp lệ (supplier_name, address, wechat_account, phone, wechat_qr)']
        ];
    }
    
    // Validate individual rows
    $errors = [];
    $maxErrors = 20;
    
    for ($i = 0; $i < min(count($data), 100); $i++) {
        $row = $data[$i];
        $rowNum = $i + 2; // +2 because of header row and 0-index
        
        // Check for duplicate (basic check)
        if (empty($row['supplier_name']) && empty($row['wechat_account']) && empty($row['phone'])) {
            if (count($errors) < $maxErrors) {
                $errors[] = "Dòng {$rowNum}: Bỏ qua dòng trống";
            }
        }
    }
    
    if (count($data) > 1000) {
        return [
            'valid' => false,
            'error' => 'Quá nhiều dòng dữ liệu. Tối đa 1000 dòng.',
            'errors' => ['File có ' . count($data) . ' dòng, vượt quá giới hạn 1000 dòng']
        ];
    }
    
    return [
        'valid' => true,
        'headers' => $headers,
        'errors' => $errors
    ];
}

/**
 * Transform data for database import
 */
function transformDataForImport($data, $productId) {
    $result = [];
    
    foreach ($data as $index => $row) {
        // Skip empty rows
        if (empty($row['supplier_name']) && empty($row['wechat_account']) && empty($row['phone'])) {
            continue;
        }
        
        $item = [
            'product_id' => $productId,
            'supplier_name' => $row['supplier_name'] ?? '',
            'address' => $row['address'] ?? '',
            'wechat_account' => $row['wechat_account'] ?? '',
            'phone' => $row['phone'] ?? '',
            'wechat_qr' => $row['wechat_qr'] ?? '',
            'row_index' => $index + 1
        ];
        
        $result[] = $item;
    }
    
    return $result;
}
