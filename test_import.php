<?php
/**
 * Test script to diagnose Excel Import & Image Extraction issues
 */
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: text/html; charset=utf-8');

echo '<h1>Chương Trình Kiểm Tra Lỗi Import Excel</h1>';
echo '<pre>';

// 1. Check PHP Version
echo "1. PHP Version: " . PHP_VERSION . "\n";
echo "   PHP SAPI: " . PHP_SAPI . "\n";

// 2. Check extensions
echo "\n2. Kiểm tra các extension cần thiết:\n";
$extensions = ['zip', 'gd', 'xml', 'mbstring', 'openssl', 'pdo_mysql'];
foreach ($extensions as $ext) {
    echo "   - Extension '$ext': " . (extension_loaded($ext) ? 'Đã cài đặt [OK]' : 'CHƯA CÀI ĐẶT [LỖI]') . "\n";
}

// 3. Check ZipArchive class
echo "   - Class ZipArchive: " . (class_exists('ZipArchive') ? 'Có sẵn [OK]' : 'Không có sẵn [LỖI]') . "\n";

// 4. Try loading vendor/autoload.php
echo "\n3. Nạp autoload.php:\n";
try {
    if (file_exists(__DIR__ . '/vendor/autoload.php')) {
        require_once __DIR__ . '/vendor/autoload.php';
        echo "   - Nạp vendor/autoload.php: Thành công [OK]\n";
    } else {
        echo "   - Nạp vendor/autoload.php: File không tồn tại [LỖI]\n";
    }
} catch (Throwable $e) {
    echo "   - Nạp vendor/autoload.php thất bại với lỗi: " . $e->getMessage() . "\n";
    echo "     File: " . $e->getFile() . " (Dòng " . $e->getLine() . ")\n";
}

// 5. Check PhpSpreadsheet classes
echo "\n4. Kiểm tra PhpSpreadsheet:\n";
echo "   - IOFactory class: " . (class_exists('PhpOffice\PhpSpreadsheet\IOFactory') ? 'Có sẵn [OK]' : 'Không có sẵn [LỖI]') . "\n";

// 6. Check directory permissions
echo "\n5. Kiểm tra quyền ghi thư mục upload:\n";
$uploadDir = __DIR__ . '/assets/uploads/product_data/';
echo "   - Thư mục: $uploadDir\n";
if (!is_dir($uploadDir)) {
    echo "   - Thư mục không tồn tại, đang thử tạo...\n";
    $created = mkdir($uploadDir, 0755, true);
    echo "   - Tạo thư mục: " . ($created ? 'Thành công [OK]' : 'Thất bại [LỖI]') . "\n";
} else {
    echo "   - Thư mục tồn tại [OK]\n";
}
if (is_writable($uploadDir)) {
    echo "   - Quyền ghi: Có quyền ghi [OK]\n";
} else {
    echo "   - Quyền ghi: KHÔNG CÓ QUYỀN GHI [LỖI]\n";
}

// Local helper definitions to avoid importing the whole procedural import.php file
function localNormalizeHeader($header) {
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

function localExtractEmbeddedImages($filePath) {
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
                    $basename = basename($targetPath);
                    $zipPath = 'xl/media/' . $basename;
                    
                    $imgContent = $zip->getFromName($zipPath);
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

function localExtractExcel365CellImages($filePath) {
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

function localParseXLSX($filePath, $uploadDir) {
    if (!class_exists('PhpOffice\PhpSpreadsheet\IOFactory')) {
        throw new Exception('Thư viện PhpSpreadsheet chưa được cài đặt.');
    }
    
    $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($filePath);
    $sheet = $spreadsheet->getActiveSheet();
    
    // 1. Extract Drawings
    $drawings = $sheet->getDrawingCollection();
    $extractedImages = [];
    foreach ($drawings as $drawing) {
        $coordinates = $drawing->getCoordinates();
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
            $newFileName = 'imported_' . uniqid() . '_' . time() . '.' . $extension;
            if (file_put_contents($uploadDir . $newFileName, $imageContent) !== false) {
                $extractedImages[$coordinates] = 'assets/uploads/product_data/' . $newFileName;
            }
        }
    }
    
    // 2. Extract Embedded (WPS style)
    $embeddedImages = localExtractEmbeddedImages($filePath);
    
    // 3. Extract Excel 365 Rich Data Cell Images
    $excel365Images = localExtractExcel365CellImages($filePath);
    foreach ($excel365Images as $coordinate => $imgData) {
        $extension = strtolower($imgData['extension'] ?: 'png');
        $newFileName = 'imported_incell365_' . uniqid() . '_' . time() . '.' . $extension;
        if (file_put_contents($uploadDir . $newFileName, $imgData['content']) !== false) {
            $extractedImages[$coordinate] = 'assets/uploads/product_data/' . $newFileName;
        }
    }
    
    // 3. Read cells
    $rows = $sheet->toArray(null, true, true, true);
    if (empty($rows)) {
        return [];
    }
    
    $firstKey = key($rows);
    $headerRow = $rows[$firstKey];
    unset($rows[$firstKey]);
    $headers = [];
    foreach ($headerRow as $colLetter => $val) {
        $headers[$colLetter] = localNormalizeHeader($val);
    }
    
    $storeImageCol = array_search('store_image', $headers);
    $wechatQrCol = array_search('wechat_qr', $headers);
    
    $result = [];
    foreach ($rows as $rowIndex => $row) {
        // Process embedded images
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
                $newFileName = 'imported_incell_' . uniqid() . '_' . time() . '.' . $extension;
                if (file_put_contents($uploadDir . $newFileName, $imgData['content']) !== false) {
                    $extractedImages[$colLetter . $rowIndex] = 'assets/uploads/product_data/' . $newFileName;
                }
            }
        }
        
        $hasContent = false;
        foreach ($row as $colLetter => $val) {
            $cleanedVal = trim(strval($val));
            if ($cleanedVal !== '' && $cleanedVal !== '#NAME?' && $cleanedVal !== '#VALUE!' && strpos($cleanedVal, 'DISPIMG') === false) {
                $hasContent = true;
                break;
            }
        }
        if (!$hasContent) {
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
                if (strpos($value, 'DISPIMG') !== false || isset($embeddedImages[$value]) || $value === '#NAME?' || $value === '#VALUE!') {
                    $value = '';
                }
                $item[$header] = $value;
            }
            
            if ($storeImageCol !== false && isset($extractedImages[$storeImageCol . $rowIndex])) {
                $item['store_image'] = $extractedImages[$storeImageCol . $rowIndex];
            } else {
                $item['store_image'] = $item['store_image'] ?? '';
            }
            if ($wechatQrCol !== false && isset($extractedImages[$wechatQrCol . $rowIndex])) {
                $item['wechat_qr'] = $extractedImages[$wechatQrCol . $rowIndex];
            } else {
                $item['wechat_qr'] = $item['wechat_qr'] ?? '';
            }
            
            $result[] = $item;
        }
    }
    return $result;
}

// Check if a file was uploaded for testing
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['test_xlsx_file'])) {
    $file = $_FILES['test_xlsx_file'];
    echo "\n========================================\n";
    echo "Đang xử lý file upload: " . basename($file['name']) . " (" . round($file['size']/1024, 2) . " KB)\n";
    
    if ($file['error'] !== UPLOAD_ERR_OK) {
        echo "Lỗi upload file: " . $file['error'] . "\n";
    } else {
        $testFile = $file['tmp_name'];
        try {
            // 1. List files inside ZIP
            echo "   - Danh sách file hình ảnh/cấu trúc liên quan trong ZIP:\n";
            $zip = new ZipArchive();
            if ($zip->open($testFile) === true) {
                for ($i = 0; $i < $zip->numFiles; $i++) {
                    $stat = $zip->statIndex($i);
                    echo "     * " . $stat['name'] . " (" . round($stat['size']/1024, 2) . " KB)\n";
                }
                
                // Read and display richData XML files
                echo "\n   --- CHI TIẾT RICH DATA XML ---\n";
                $richFiles = [
                    'xl/metadata.xml',
                    'xl/richData/richValueRel.xml',
                    'xl/richData/rdrichvalue.xml',
                    'xl/richData/rdrichvaluestructure.xml',
                    'xl/richData/_rels/richValueRel.xml.rels'
                ];
                foreach ($richFiles as $rf) {
                    $content = $zip->getFromName($rf);
                    if ($content !== false) {
                        echo "   * File: $rf\n";
                        echo "     Content: " . htmlspecialchars($content) . "\n\n";
                    } else {
                        echo "   * File: $rf (Không tồn tại)\n\n";
                    }
                }
                
                // Read sheet1.xml (cell values F2 and G2)
                $sheetContent = $zip->getFromName('xl/worksheets/sheet1.xml');
                if ($sheetContent !== false) {
                    echo "   * File: xl/worksheets/sheet1.xml (First 2000 chars):\n";
                    echo "     Content: " . htmlspecialchars(substr($sheetContent, 0, 2000)) . "\n\n";
                }
                
                $zip->close();
            } else {
                echo "     * Không thể mở file dưới dạng ZIP!\n";
            }

            echo "   - Bắt đầu trích xuất ảnh nhúng (extractEmbeddedImages)...\n";
            $embedded = localExtractEmbeddedImages($testFile);
            echo "   - Số lượng ảnh nhúng tìm thấy: " . count($embedded) . "\n";
            if (!empty($embedded)) {
                echo "     Danh sách ID ảnh: " . implode(', ', array_keys($embedded)) . "\n";
            }
            
            echo "   - Bắt đầu trích xuất ảnh nhúng Excel 365 (localExtractExcel365CellImages)...\n";
            $excel365 = localExtractExcel365CellImages($testFile);
            echo "   - Số lượng ảnh nhúng Excel 365 tìm thấy: " . count($excel365) . "\n";
            if (!empty($excel365)) {
                echo "     Danh sách tọa độ ảnh: " . implode(', ', array_keys($excel365)) . "\n";
            }
            
            // 2. Diagnostics on PhpSpreadsheet Drawings & Raw Data
            try {
                $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($testFile);
                $sheet = $spreadsheet->getActiveSheet();
                $drawings = $sheet->getDrawingCollection();
                echo "   - Số lượng drawings (ảnh nổi) từ PhpSpreadsheet: " . count($drawings) . "\n";
                foreach ($drawings as $idx => $d) {
                    echo "     * Drawing #$idx: Tọa độ=" . $d->getCoordinates() . ", Class=" . get_class($d) . "\n";
                }
                $rawRows = $sheet->toArray(null, true, true, true);
                if (!empty($rawRows)) {
                    $firstKey = key($rawRows);
                    echo "   - Dòng tiêu đề thô (Row $firstKey):\n";
                    print_r($rawRows[$firstKey]);
                    
                    next($rawRows);
                    $secondKey = key($rawRows);
                    if ($secondKey) {
                        echo "   - Dòng dữ liệu thô đầu tiên (Row $secondKey):\n";
                        print_r($rawRows[$secondKey]);
                    }
                }
            } catch (Exception $ex) {
                echo "   - Lỗi đọc drawings/raw: " . $ex->getMessage() . "\n";
            }
            
            echo "   - Bắt đầu parse file XLSX đầy đủ (parseXLSX)...\n";
            $data = localParseXLSX($testFile, $uploadDir);
            echo "   - Parse thành công! Số lượng dòng đọc được: " . count($data) . "\n";
            if (!empty($data)) {
                echo "   - Dữ liệu dòng đầu tiên làm mẫu sau khi map và clean:\n";
                print_r($data[0]);
            }
        } catch (Throwable $e) {
            echo "   - [LỖI FATAL] Quá trình parse thất bại với lỗi: " . $e->getMessage() . "\n";
            echo "     Tại file: " . $e->getFile() . " (Dòng " . $e->getLine() . ")\n";
            echo "     Trace:\n" . $e->getTraceAsString() . "\n";
        }
    }
    echo "========================================\n";
}

echo '</pre>';

// Show upload form
?>
<h2>Upload file Excel (.xlsx) để chạy thử nghiệm import trực tiếp:</h2>
<form method="POST" enctype="multipart/form-data" style="padding: 20px; border: 1px solid #ccc; background: #f9f9f9; display: inline-block;">
    <input type="file" name="test_xlsx_file" accept=".xlsx" required />
    <button type="submit" style="padding: 5px 15px; margin-left: 10px; cursor: pointer;">Chạy Kiểm Tra Parse File</button>
</form>
