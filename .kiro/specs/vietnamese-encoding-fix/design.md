# Vietnamese Encoding Fix - Design Document

## Architecture Overview

The encoding fix will be implemented through a multi-layered approach:
1. **File Level**: Ensure all PHP files use UTF-8 encoding
2. **Database Level**: Configure proper charset and collation
3. **HTTP Level**: Set correct headers and meta tags
4. **Server Level**: Configure .htaccess for UTF-8 support

## Technical Design

### 1. File Encoding Strategy

#### Current State Analysis
- Some files (products, details, categories) work correctly
- Other files show HTML entities instead of Vietnamese characters
- Inconsistent encoding across the codebase

#### Solution Design
```php
// Add to all PHP files that output Vietnamese content
header('Content-Type: text/html; charset=UTF-8');
mb_internal_encoding('UTF-8');
```

### 2. Database Configuration

#### Connection Setup
```php
// In core/database.php
$dsn = "mysql:host={$host};dbname={$dbname};charset=utf8mb4";
$pdo = new PDO($dsn, $username, $password, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
]);
```

#### Table Configuration
- Ensure all tables use `utf8mb4_unicode_ci` collation
- Convert existing data if necessary

### 3. HTML Meta Configuration

#### Master Layout Update
```html
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <!-- other meta tags -->
</head>
```

### 4. Server Configuration

#### .htaccess Rules
```apache
# Force UTF-8 encoding
AddDefaultCharset UTF-8
AddCharset UTF-8 .php .html .css .js

# Set Content-Type headers
<FilesMatch "\.(php|html)$">
    Header set Content-Type "text/html; charset=UTF-8"
</FilesMatch>
```

### 5. PHP Configuration

#### Core Functions Update
```php
// In core/functions.php
function setUTF8Headers() {
    if (!headers_sent()) {
        header('Content-Type: text/html; charset=UTF-8');
        ini_set('default_charset', 'UTF-8');
        mb_internal_encoding('UTF-8');
    }
}
```

## Implementation Strategy

### Phase 1: Core Infrastructure
1. Update database connection configuration
2. Modify core/encoding.php for UTF-8 handling
3. Update master layout with proper meta tags
4. Configure .htaccess rules

### Phase 2: File Conversion
1. Identify files with encoding issues
2. Convert files to UTF-8 without BOM
3. Add UTF-8 headers to PHP files
4. Test Vietnamese character display

### Phase 3: Content Validation
1. Create validation script to check encoding
2. Test all pages for proper Vietnamese display
3. Verify database operations preserve encoding
4. Validate hosting environment compatibility

### Phase 4: Deployment Preparation
1. Create backup of current files
2. Prepare deployment script
3. Test on staging environment
4. Document deployment process

## File Structure Changes

### New Files
- `core/encoding.php` - Enhanced encoding utilities
- `scripts/validate_encoding.php` - Encoding validation script
- `scripts/convert_encoding.php` - File encoding conversion utility

### Modified Files
- `core/database.php` - Database charset configuration
- `core/functions.php` - UTF-8 helper functions
- `app/views/_layout/master.php` - HTML meta tags
- `.htaccess` - Server encoding rules
- All view files with Vietnamese content

## Testing Strategy

### Unit Tests
- Database connection charset verification
- File encoding detection
- Header output validation

### Integration Tests
- End-to-end Vietnamese character display
- Cross-browser compatibility
- Hosting environment validation

### Property-Based Tests
- **Property 1.1**: Vietnamese Input Preservation
  - For any Vietnamese text input, the output should contain the same characters
  - No HTML entities should appear in rendered output
- **Property 1.2**: Encoding Consistency
  - All files should maintain UTF-8 encoding after processing
  - Database operations should preserve Vietnamese characters

## Correctness Properties

### 1. Character Preservation Property
```
∀ vietnamese_text ∈ VietnameseStrings:
  display(vietnamese_text) = vietnamese_text ∧
  ¬contains(display(vietnamese_text), "&#")
```

### 2. Encoding Consistency Property
```
∀ file ∈ PHPFiles:
  encoding(file) = "UTF-8" ∧
  ¬has_BOM(file)
```

### 3. Database Integrity Property
```
∀ data ∈ DatabaseOperations:
  charset(connection) = "utf8mb4" ∧
  collation(tables) = "utf8mb4_unicode_ci"
```

## Risk Mitigation

### Data Loss Prevention
- Create full backup before any changes
- Test conversion on copy of production data
- Implement rollback procedures

### Compatibility Issues
- Test on multiple hosting environments
- Verify browser compatibility
- Check mobile device rendering

### Performance Impact
- Monitor page load times after changes
- Optimize encoding operations
- Cache UTF-8 headers where possible

## Success Metrics

### Functional Metrics
- 100% of pages display Vietnamese correctly
- 0 HTML entities in visible content
- All database operations preserve Vietnamese characters

### Technical Metrics
- All PHP files use UTF-8 encoding without BOM
- Database connection uses utf8mb4 charset
- HTTP headers specify UTF-8 encoding

### User Experience Metrics
- Vietnamese text is readable on all pages
- Consistent font rendering across pages
- No character corruption in forms or content