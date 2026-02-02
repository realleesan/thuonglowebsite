# File Encoding Fix - Design Document

## 1. Kiến trúc giải pháp

### 1.1 Tổng quan
Giải pháp sẽ bao gồm 3 thành phần chính:
1. **Server Configuration**: Cấu hình .htaccess cho charset UTF-8
2. **File Encoding**: Ensure tất cả files sử dụng UTF-8 encoding
3. **Validation Tools**: Scripts để kiểm tra và fix encoding issues

### 1.2 Luồng xử lý
```
Local Files (UTF-8) → Deploy → Server (.htaccess charset) → Editor (UTF-8) → Display Correctly
```

## 2. Chi tiết thiết kế

### 2.1 Server Configuration (.htaccess)

#### 2.1.1 Default Charset
```apache
# Set default charset to UTF-8
AddDefaultCharset UTF-8

# Force UTF-8 for PHP files
<FilesMatch "\.(php|phtml)$">
    AddCharset UTF-8 .php
    AddCharset UTF-8 .phtml
</FilesMatch>
```

#### 2.1.2 Content-Type Headers
```apache
# Ensure proper content-type headers
<FilesMatch "\.(php|html|htm)$">
    Header set Content-Type "text/html; charset=UTF-8"
</FilesMatch>
```

#### 2.1.3 PHP Configuration
```apache
# PHP charset settings
php_value default_charset "UTF-8"
php_value internal_encoding "UTF-8"
php_value input_encoding "UTF-8"
php_value output_encoding "UTF-8"
```

### 2.2 File Encoding Strategy

#### 2.2.1 UTF-8 with BOM
Một số hosting editors cần BOM để nhận diện UTF-8:
```php
// Add BOM to PHP files if needed
$bom = "\xEF\xBB\xBF";
$content = $bom . $original_content;
```

#### 2.2.2 Encoding Validation Script
```php
function validateFileEncoding($filepath) {
    $content = file_get_contents($filepath);
    
    // Check if file is valid UTF-8
    if (!mb_check_encoding($content, 'UTF-8')) {
        return false;
    }
    
    // Check for BOM
    $bom = substr($content, 0, 3);
    $hasBOM = ($bom === "\xEF\xBB\xBF");
    
    return [
        'valid_utf8' => true,
        'has_bom' => $hasBOM,
        'encoding' => mb_detect_encoding($content)
    ];
}
```

#### 2.2.3 Encoding Conversion Script
```php
function convertToUTF8WithBOM($filepath) {
    $content = file_get_contents($filepath);
    
    // Convert to UTF-8 if needed
    $encoding = mb_detect_encoding($content);
    if ($encoding !== 'UTF-8') {
        $content = mb_convert_encoding($content, 'UTF-8', $encoding);
    }
    
    // Add BOM if not present
    if (substr($content, 0, 3) !== "\xEF\xBB\xBF") {
        $content = "\xEF\xBB\xBF" . $content;
    }
    
    file_put_contents($filepath, $content);
}
```

### 2.3 HTML Meta Configuration

#### 2.3.1 Charset Declaration
Trong `master.php`:
```html
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <!-- Other meta tags -->
</head>
```

#### 2.3.2 PHP Header
Trong các PHP files:
```php
<?php
// Set charset header
header('Content-Type: text/html; charset=UTF-8');

// Set internal encoding
mb_internal_encoding('UTF-8');
mb_http_output('UTF-8');
?>
```

### 2.4 Deployment Strategy

#### 2.4.1 Pre-deployment Validation
```bash
#!/bin/bash
# Script to validate encoding before deployment

find . -name "*.php" -exec php -l {} \;
find . -name "*.php" -exec file -bi {} \; | grep -v "utf-8"
```

#### 2.4.2 Post-deployment Validation
```php
// Script to run after deployment
function validateDeployment() {
    $files = glob('**/*.php', GLOB_BRACE);
    $issues = [];
    
    foreach ($files as $file) {
        $validation = validateFileEncoding($file);
        if (!$validation['valid_utf8']) {
            $issues[] = $file;
        }
    }
    
    return $issues;
}
```

## 3. Implementation Plan

### 3.1 Phase 1: Server Configuration
1. Cập nhật `.htaccess` với charset settings
2. Test charset headers trên hosting
3. Verify PHP encoding settings

### 3.2 Phase 2: File Encoding
1. Tạo encoding validation script
2. Scan và validate tất cả PHP files
3. Convert files sang UTF-8 with BOM nếu cần

### 3.3 Phase 3: Testing
1. Test editing files trên hosting
2. Verify tiếng Việt hiển thị đúng
3. Test website functionality

### 3.4 Phase 4: Deployment Process
1. Tạo deployment checklist
2. Automated validation scripts
3. Backup và recovery procedures

## 4. Correctness Properties

### 4.1 Encoding Properties
1. **UTF-8 Validity**: Tất cả PHP files phải là valid UTF-8
2. **BOM Consistency**: Files có BOM nếu hosting editor yêu cầu
3. **Header Correctness**: Server phải trả về đúng charset headers
4. **Display Accuracy**: Tiếng Việt hiển thị đúng trong editor

### 4.2 Functionality Properties
1. **Code Integrity**: Code vẫn hoạt động sau khi fix encoding
2. **Website Display**: Website hiển thị tiếng Việt đúng
3. **Edit Safety**: Có thể edit file mà không bị corrupt

## 5. Testing Strategy

### 5.1 Encoding Tests
- Validate UTF-8 encoding của files
- Test BOM detection
- Check charset headers

### 5.2 Functionality Tests
- Test website sau khi fix encoding
- Verify PHP syntax
- Check tiếng Việt display

### 5.3 Editor Tests
- Test editing files trên hosting
- Verify save functionality
- Check character display

## 6. Recovery Procedures

### 6.1 Backup Strategy
- Backup files trước khi convert encoding
- Version control cho tracking changes
- Automated backup scripts

### 6.2 Recovery Steps
1. Restore từ backup
2. Re-run encoding conversion
3. Validate và test
4. Deploy lại nếu cần