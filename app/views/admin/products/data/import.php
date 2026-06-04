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
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, must-revalidate');

// Allow CORS
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-Requested-With');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Khá»Ÿi táº¡o cÃ¡c Ä‘Æ°á»ng dáº«n tÆ°Æ¡ng Ä‘á»‘i chÃ­nh xÃ¡c (5 cáº¥p tá»« app/views/admin/products/data/import.php Ä‘áº¿n root)
require_once __DIR__ . '/../../../../../core/view_init.php';
require_once __DIR__ . '/../../../../models/ProductDataModel.php';
require_once __DIR__ . '/../../../../../vendor/autoload.php';

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
    jsonResponse(false, 'Thiáº¿u ID sáº£n pháº©m', [], 400);
}

// Check if file was uploaded (Support both import_file and excel_file)
$fileKey = isset($_FILES['import_file']) ? 'import_file' : (isset($_FILES['excel_file']) ? 'excel_file' : null);
if (!$fileKey || $_FILES[$fileKey]['error'] !== UPLOAD_ERR_OK) {
    $error = $fileKey ? $_FILES[$fileKey]['error'] : 'No file uploaded';
    jsonResponse(false, 'Lỗi upload file: ' . $error, [], 400);
}

$file = $_FILES[$fileKey];
$filename = $file['name'];
$tmpFile = $file['tmp_name'];
$fileSize = $file['size'];

// Validate file extension
$allowedExtensions = ['xlsx', 'csv'];
$ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

if (!in_array($ext, $allowedExtensions)) {
    jsonResponse(false, 'Äá»‹nh dáº¡ng file khÃ´ng há»£p lá»‡. Chá»‰ cháº¥p nháº­n xlsx vÃ  csv', [], 400);
}

// Validate file size (max 10MB)
$maxSize = 10 * 1024 * 1024; // 10MB
if ($fileSize > $maxSize) {
    jsonResponse(false, 'File quÃ¡ lá»›n. KÃ­ch thÆ°á»›c tá»‘i Ä‘a lÃ  10MB', [], 400);
}

// Validate file is not empty
if ($fileSize === 0) {
    jsonResponse(false, 'File rá»—ng', [], 400);
}

// Check for preview mode (GET request or preview parameter)
$previewMode = isset($_GET['preview']) || isset($_POST['preview']);

try {
    // Parse Excel file
    $data = parseExcelFile($tmpFile, $ext);
    
    // Dump for debugging
    $debugText = "=== RAW HEADERS ===\n" . print_r(!empty($data) ? array_keys($data[0]) : [], true) . "\n\n=== DATA ===\n" . print_r($data, true);
    file_put_contents('d:/xampp/htdocs/thuonglowebsite/last_import_debug.txt', $debugText);
    
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
    
    // Transform data for import (Ensures all rows have consistent keys)
    $importData = transformDataForImport($data, $productId);
    
    // Bulk insert
    $success = $productDataModel->bulkInsert($importData);
    $inserted = $success ? count($importData) : 0;
    
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
    if ($ext === 'csv') {
        return parseCSV($filePath);
    } else {
        return parseXLSX($filePath);
    }
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
        
        $row = parseCSVLine($line);
        
        if ($firstRow) {
            $headers = array_map('trim', $row);
            $headers = array_map(function($h) {
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
                $i++;
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
 * Parse XLSX file using PhpSpreadsheet and extract Drawing Layer images
 */
/**
 * Extract embedded (Place in Cell) images from XLSX Zip Archive
 */
function extractEmbeddedImages($filePath) {
    $embeddedImages = [];
    if (!class_exists('ZipArchive')) {
        return [];
    }
    
    $zip = new ZipArchive();
    if ($zip->open($filePath) !== true) {
        return [];
    }
    
    $cellImagesEntry = $zip->getFromName('xl/cellimages.xml');
    $relsEntry = $zip->getFromName('xl/_rels/cellimages.xml.rels');
    
    if ($cellImagesEntry !== false && $relsEntry !== false) {
        $relMap = [];
        if (preg_match_all('/<Relationship[^>]+Id=["\']([^"\']+)["\'][^>]+Target=["\']([^"\']+)["\']/i', $relsEntry, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $relMap[$match[1]] = $match[2];
            }
        }
        
        if (preg_match_all('/<(?:etc:)?cellImage[^>]*>(.*?)<\/(?:etc:)?cellImage>/is', $cellImagesEntry, $blocks)) {
            foreach ($blocks[1] as $block) {
                $name = '';
                $embedId = '';
                
                if (preg_match('/<cNvPr[^>]+name=["\']([^"\']+)["\']/i', $block, $nameMatch)) {
                    $name = $nameMatch[1];
                }
                
                if (preg_match('/embed=["\']([^"\']+)["\']/i', $block, $embedMatch)) {
                    $embedId = $embedMatch[1];
                }
                
                if (!empty($name) && !empty($embedId) && isset($relMap[$embedId])) {
                    $targetPath = $relMap[$embedId];
                    // Clean path and ensure it maps to xl/media/
                    $basename = basename($targetPath);
                    $zipPath = 'xl/media/' . $basename;
                    
                    $imgContent = $zip->getFromName($zipPath);
                    // Fallback to relative path if not found in xl/media/
                    if ($imgContent === false) {
                        $zipPath = 'xl/' . ltrim($targetPath, '/');
                        $imgContent = $zip->getFromName($zipPath);
                    }
                    
                    if ($imgContent !== false) {
                        $embeddedImages[$name] = [
                            'content' => $imgContent,
                            'extension' => pathinfo($targetPath, PATHINFO_EXTENSION) ?: 'png'
                        ];
                    }
                }
            }
        }
    }
    
    $zip->close();
    return $embeddedImages;
}

/**
 * Extract embedded Excel 365 rich value images (Place in Cell) from XLSX Zip Archive
 */
function extractExcel365CellImages($filePath) {
    if (!class_exists('ZipArchive')) {
        return [];
    }
    
    $zip = new ZipArchive();
    if ($zip->open($filePath) !== true) {
        return [];
    }
    
    $cellImages = [];
    
    // 1. Read sheet1.xml to get cells with vm attribute
    $sheet1XmlStr = $zip->getFromName('xl/worksheets/sheet1.xml');
    if ($sheet1XmlStr) {
        $sheetXml = simplexml_load_string($sheet1XmlStr);
        if ($sheetXml) {
            $nodes = $sheetXml->xpath('//*[local-name()="c" and @vm]');
            if ($nodes) {
                // 2. Read metadata.xml and related XMLs
                $metadataXmlStr = $zip->getFromName('xl/metadata.xml');
                $rdRichValueXmlStr = $zip->getFromName('xl/richData/rdrichvalue.xml');
                $richValueRelXmlStr = $zip->getFromName('xl/richData/richValueRel.xml');
                $relsXmlStr = $zip->getFromName('xl/richData/_rels/richValueRel.xml.rels');
                
                if ($metadataXmlStr && $rdRichValueXmlStr && $richValueRelXmlStr && $relsXmlStr) {
                    $metadataXml = simplexml_load_string($metadataXmlStr);
                    $rdRichValueXml = simplexml_load_string($rdRichValueXmlStr);
                    $richValueRelXml = simplexml_load_string($richValueRelXmlStr);
                    $relsXml = simplexml_load_string($relsXmlStr);
                    
                    if ($metadataXml && $rdRichValueXml && $richValueRelXml && $relsXml) {
                        $valueMetadataBk = $metadataXml->xpath('//*[local-name()="valueMetadata"]/*[local-name()="bk"]');
                        $futureMetadataBk = $metadataXml->xpath('//*[local-name()="futureMetadata" and @name="XLRICHVALUE"]/*[local-name()="bk"]');
                        $rvNodes = $rdRichValueXml->xpath('//*[local-name()="rv"]');
                        $relNodes = $richValueRelXml->xpath('//*[local-name()="rel"]');
                        
                        $relIdToTarget = [];
                        $relationshipNodes = $relsXml->xpath('//*[local-name()="Relationship"]');
                        foreach ($relationshipNodes as $rNode) {
                            $id = (string)$rNode['Id'];
                            $target = (string)$rNode['Target'];
                            if (strpos($target, '../') === 0) {
                                $target = 'xl/' . substr($target, 3);
                            }
                            $relIdToTarget[$id] = $target;
                        }
                        
                        foreach ($nodes as $node) {
                            $coordinate = (string)$node['r'];
                            $vmIndex = (int)$node['vm'];
                            
                            $vmIdxZeroBased = $vmIndex - 1;
                            if (isset($valueMetadataBk[$vmIdxZeroBased])) {
                                $rcNodes = $valueMetadataBk[$vmIdxZeroBased]->xpath('.//*[local-name()="rc"]');
                                if (!empty($rcNodes)) {
                                    $futureMetadataIndex = (int)$rcNodes[0]['v'];
                                    if (isset($futureMetadataBk[$futureMetadataIndex])) {
                                        $rvbNodes = $futureMetadataBk[$futureMetadataIndex]->xpath('.//*[local-name()="rvb"]');
                                        if (!empty($rvbNodes)) {
                                            $richValueIndex = (int)$rvbNodes[0]['i'];
                                            if (isset($rvNodes[$richValueIndex])) {
                                                $vNodes = $rvNodes[$richValueIndex]->xpath('.//*[local-name()="v"]');
                                                if (!empty($vNodes)) {
                                                    $localImageId = (int)$vNodes[0];
                                                    if (isset($relNodes[$localImageId])) {
                                                        $attrs = $relNodes[$localImageId]->attributes('http://schemas.openxmlformats.org/officeDocument/2006/relationships');
                                                        $rId = (string)$attrs['id'];
                                                        if (isset($relIdToTarget[$rId])) {
                                                            $mediaPath = $relIdToTarget[$rId];
                                                            $imgContent = $zip->getFromName($mediaPath);
                                                            if ($imgContent !== false) {
                                                                $cellImages[$coordinate] = [
                                                                    'content' => $imgContent,
                                                                    'extension' => pathinfo($mediaPath, PATHINFO_EXTENSION) ?: 'png'
                                                                ];
                                                            }
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }
    
    $zip->close();
    return $cellImages;
}

/**
 * Parse XLSX file using PhpSpreadsheet and extract Drawing Layer & Embedded Cell images
 */
function parseXLSX($filePath) {
    if (!class_exists('PhpOffice\PhpSpreadsheet\IOFactory')) {
        throw new Exception('Thư viện PhpSpreadsheet chưa được cài đặt hoặc load thành công.');
    }
    
    try {
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($filePath);
        $sheet = $spreadsheet->getActiveSheet();
        
        // Target upload directory (5 levels back to root assets)
        $uploadDir = __DIR__ . '/../../../../../assets/uploads/product_data/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        // 1. Extract Drawings (Images) from Drawing Layer
        $drawings = $sheet->getDrawingCollection();
        $extractedImages = [];
        
        foreach ($drawings as $drawing) {
            $coordinates = $drawing->getCoordinates(); // e.g., "G2", "H3"
            $imageContent = '';
            $extension = '';
            
            if ($drawing instanceof \PhpOffice\PhpSpreadsheet\Worksheet\Drawing) {
                $filename = $drawing->getPath();
                if (file_exists($filename)) {
                    $imageContent = file_get_contents($filename);
                    $extension = $drawing->getExtension();
                }
            } elseif ($drawing instanceof \PhpOffice\PhpSpreadsheet\Worksheet\MemoryDrawing) {
                ob_start();
                call_user_func($drawing->getRenderingFunction(), $drawing->getImageResource());
                $imageContent = ob_get_contents();
                ob_end_clean();
                $extension = 'png';
            }
            
            if (!empty($imageContent)) {
                $extension = strtolower($extension ?: 'png');
                if (!in_array($extension, ['png', 'jpg', 'jpeg', 'gif', 'webp'])) {
                    $extension = 'png';
                }
                
                $newFileName = 'imported_' . uniqid() . '_' . time() . '.' . $extension;
                $savePath = $uploadDir . $newFileName;
                
                if (file_put_contents($savePath, $imageContent) !== false) {
                    $extractedImages[$coordinates] = 'assets/uploads/product_data/' . $newFileName;
                }
            }
        }
        
        // 2. Extract Embedded (Place in Cell) Images (WPS style)
        $embeddedImages = extractEmbeddedImages($filePath);
        
        // 2b. Extract Excel 365 Rich Value Cell Images (Excel 365 style)
        $excel365Images = extractExcel365CellImages($filePath);
        foreach ($excel365Images as $coordinate => $imgData) {
            $extension = strtolower($imgData['extension'] ?: 'png');
            if (!in_array($extension, ['png', 'jpg', 'jpeg', 'gif', 'webp'])) {
                $extension = 'png';
            }
            $newFileName = 'imported_incell365_' . uniqid() . '_' . time() . '.' . $extension;
            $savePath = $uploadDir . $newFileName;
            if (file_put_contents($savePath, $imgData['content']) !== false) {
                $extractedImages[$coordinate] = 'assets/uploads/product_data/' . $newFileName;
            }
        }
        
        // 3. Read cell values and match headers
        $rows = $sheet->toArray(null, true, true, true); // Returns array indexed by row number, then column letter
        
        if (empty($rows)) {
            return [];
        }
        
        // Extract headers from the first row
        $firstKey = key($rows);
        $headerRow = $rows[$firstKey];
        unset($rows[$firstKey]);
        $headers = [];
        foreach ($headerRow as $colLetter => $val) {
            $headers[$colLetter] = normalizeHeader($val);
        }
        
        // Map store_image and wechat_qr column letters based on normalization
        $storeImageCol = array_search('store_image', $headers);
        $wechatQrCol = array_search('wechat_qr', $headers);
        
        $result = [];
        foreach ($rows as $rowIndex => $row) {
            // First, process any cells in this row that might have embedded images
            foreach ($headers as $colLetter => $header) {
                $cell = $sheet->getCell($colLetter . $rowIndex);
                $rawVal = trim(strval($cell->getValue()));
                
                $imageId = '';
                if (preg_match('/DISPIMG\s*\(\s*["\']([^"\']+)["\']/i', $rawVal, $matches)) {
                    $imageId = $matches[1];
                } elseif (isset($embeddedImages[$rawVal])) {
                    $imageId = $rawVal;
                }
                
                if (!empty($imageId) && isset($embeddedImages[$imageId])) {
                    $imgData = $embeddedImages[$imageId];
                    $extension = strtolower($imgData['extension'] ?: 'png');
                    if (!in_array($extension, ['png', 'jpg', 'jpeg', 'gif', 'webp'])) {
                        $extension = 'png';
                    }
                    $newFileName = 'imported_incell_' . uniqid() . '_' . time() . '.' . $extension;
                    $savePath = $uploadDir . $newFileName;
                    if (file_put_contents($savePath, $imgData['content']) !== false) {
                        $extractedImages[$colLetter . $rowIndex] = 'assets/uploads/product_data/' . $newFileName;
                    }
                }
            }

            // Check if the row contains any content (text or image coordinates)
            $hasContent = false;
            foreach ($row as $colLetter => $val) {
                $cleanedVal = trim(strval($val));
                if ($cleanedVal !== '' && $cleanedVal !== '#NAME?' && $cleanedVal !== '#VALUE!' && strpos($cleanedVal, 'DISPIMG') === false) {
                    $hasContent = true;
                    break;
                }
            }
            if (!$hasContent) {
                // Also check if any drawing layer or embedded image was extracted for this row
                foreach ($headers as $colLetter => $header) {
                    if (isset($extractedImages[$colLetter . $rowIndex])) {
                        $hasContent = true;
                        break;
                    }
                }
            }
            
            if ($hasContent) {
                $item = [];
                foreach ($headers as $colLetter => $header) {
                    $value = isset($row[$colLetter]) ? trim(strval($row[$colLetter])) : '';
                    // If it was a formula string like DISPIMG, or error codes, don't store it as text!
                    if (strpos($value, 'DISPIMG') !== false || isset($embeddedImages[$value]) || $value === '#NAME?' || $value === '#VALUE!') {
                        $value = '';
                    }
                    $item[$header] = $value;
                }
                
                // Overlay extracted store image path if exists
                if ($storeImageCol !== false && isset($extractedImages[$storeImageCol . $rowIndex])) {
                    $item['store_image'] = $extractedImages[$storeImageCol . $rowIndex];
                } else {
                    $item['store_image'] = $item['store_image'] ?? '';
                }
                
                // Overlay extracted wechat QR path if exists
                if ($wechatQrCol !== false && isset($extractedImages[$wechatQrCol . $rowIndex])) {
                    $item['wechat_qr'] = $extractedImages[$wechatQrCol . $rowIndex];
                } else {
                    $item['wechat_qr'] = $item['wechat_qr'] ?? '';
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
 * Normalize header name to map variations to database columns
 */
function normalizeHeader($header) {
    // 1. Lowercase using mb_strtolower
    $header = mb_strtolower(trim($header), 'UTF-8');
    
    // 2. Strip NFD combining marks (diacritics) using unicode range
    $header = preg_replace('/[\x{0300}-\x{036f}]/u', '', $header);
    
    // 3. Replace precomposed characters using explicit UTF-8 hex byte arrays
    $co_dau = [
        "\xc3\xa0","\xc3\xa1","\xe1\xba\xa1","\xe1\xba\xa3","\xc3\xa3","\xc3\xa2","\xe1\xba\xa7","\xe1\xba\xa5","\xe1\xba\xa9","\xe1\xba\xad","\xe1\xba\xab","\xc4\x83","\xe1\xba\xb1","\xe1\xba\xaf","\xe1\xba\xb7","\xe1\xba\xb3","\xe1\xba\xb5",
        "\xc3\xa8","\xc3\xa9","\xe1\xba\xb9","\xe1\xba\xbb","\xe1\xba\xbd","\xc3\xaa","\xe1\xbb\x81","\xe1\xba\xbf","\xe1\xbb\x87","\xe1\xbb\x83","\xe1\xbb\x85",
        "\xc3\xac","\xc3\xad","\xe1\xbb\x8b","\xe1\xbb\x89","\xc4\xa9",
        "\xc3\xb2","\xc3\xb3","\xe1\xbb\x8d","\xe1\xbb\x8f","\xc3\xb5","\xc3\xb4","\xe1\xbb\x93","\xe1\xbb\x91","\xe1\xbb\x99","\xe1\xbb\x95","\xe1\xbb\x97","\xc6\xa1","\xe1\xbb\x9d","\xe1\xbb\x9b","\xe1\xbb\xa3","\xe1\xbb\x9f","\xe1\xbb\xa1",
        "\xc3\xb9","\xc3\xba","\xe1\xbb\xa5","\xe1\xbb\xa7","\xc5\xa9","\xc6\xb0","\xe1\xbb\xab","\xe1\xbb\xa9","\xe1\xbb\xb1","\xe1\xbb\xad","\xe1\xbb\xaf",
        "\xe1\xbb\xb3","\xc3\xbd","\xe1\xbb\xb5","\xe1\xbb\xb7","\xe1\xbb\xb9",
        "\xc4\x91"
    ];
    
    $khong_dau = [
        "a","a","a","a","a","a","a","a","a","a","a","a","a","a","a","a","a",
        "e","e","e","e","e","e","e","e","e","e","e",
        "i","i","i","i","i",
        "o","o","o","o","o","o","o","o","o","o","o","o","o","o","o","o","o",
        "u","u","u","u","u","u","u","u","u","u","u",
        "y","y","y","y","y",
        "d"
    ];
    
    $header = str_replace($co_dau, $khong_dau, $header);
    
    // 4. Clean formatting
    $header = preg_replace('/[^a-z0-9_]/u', '_', $header);
    $header = preg_replace('/_+/', '_', $header);
    $header = trim($header, '_');
    
    $mappings = [
        'supplier' => 'supplier_name',
        'supplier_name' => 'supplier_name',
        'nha_cung_cap' => 'supplier_name',
        'ten_nha_cung_cap' => 'supplier_name',
        'ncc' => 'supplier_name',
        'address' => 'address',
        'dia_chi' => 'address',
        'location' => 'address',
        'wechat' => 'wechat_account',
        'wechat_account' => 'wechat_account',
        'tai_khoan_wechat' => 'wechat_account',
        'wx' => 'wechat_account',
        'zalo' => 'wechat_account',
        'phone' => 'phone',
        'dien_thoai' => 'phone',
        'so_dien_thoai' => 'phone',
        'mobile' => 'phone',
        'tel' => 'phone',
        'sdt' => 'phone',
        'qr' => 'wechat_qr',
        'qr_wechat' => 'wechat_qr',
        'wechat_qr' => 'wechat_qr',
        'wx_qr' => 'wechat_qr',
        'anh_cua_hang' => 'store_image',
        'store_image' => 'store_image',
        'image' => 'store_image',
        'anh' => 'store_image',
        'phan_loai_phong_cach' => 'style_classification',
        'style_classification' => 'style_classification',
        'style' => 'style_classification',
        'classification' => 'style_classification',
        'phong_cach' => 'style_classification'
    ];
    
    return $mappings[$header] ?? $header;
}

/**
 * Validate data before import
 */
function validateData($data) {
    $headers = !empty($data) ? array_keys($data[0]) : [];
    
    // Check for required fields
    $validFields = ['supplier_name', 'address', 'wechat_account', 'phone', 'wechat_qr', 'store_image', 'style_classification'];
    $hasAnyField = false;
    
    foreach ($data as $row) {
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
            'errors' => ['Không tìm thấy trường dữ liệu hợp lệ (supplier_name, address, wechat_account, phone, wechat_qr, store_image, style_classification)'],
        ];
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
        'errors' => []
    ];
}

/**
 * Transform data for database import (Guarantees every row contains exactly the same set of keys)
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
            'store_image' => $row['store_image'] ?? '',
            'style_classification' => $row['style_classification'] ?? '',
            'row_index' => $index + 1
        ];
        
        $result[] = $item;
    }
    
    return $result;
}
