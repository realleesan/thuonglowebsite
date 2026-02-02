# Vietnamese Encoding Fix - Requirements

## Overview
Fix Vietnamese character encoding issues across the entire website where Vietnamese text is being converted to HTML entities instead of displaying properly.

## Problem Statement
- Vietnamese characters are being converted to HTML entities (e.g., &#7873; instead of ữ)
- Only some pages (products, details, categories) display Vietnamese correctly
- Other pages show garbled Vietnamese text
- Issue occurs when files are uploaded to hosting environment

## User Stories

### 1. Content Display
**As a** website visitor  
**I want** to see Vietnamese text displayed correctly on all pages  
**So that** I can read the content naturally without seeing HTML entities

**Acceptance Criteria:**
- 1.1 All Vietnamese characters display as proper Unicode characters
- 1.2 No HTML entities (&#xxxx;) appear in visible text
- 1.3 Vietnamese text renders consistently across all pages
- 1.4 Font rendering supports Vietnamese diacritics properly

### 2. File Encoding Consistency
**As a** developer  
**I want** all PHP files to have consistent UTF-8 encoding  
**So that** Vietnamese content is preserved during file operations

**Acceptance Criteria:**
- 2.1 All PHP files are saved with UTF-8 encoding (without BOM)
- 2.2 Database connections use UTF-8 charset
- 2.3 HTTP headers specify UTF-8 encoding
- 2.4 Meta tags in HTML specify UTF-8 charset

### 3. Database Configuration
**As a** system administrator  
**I want** database to handle Vietnamese characters correctly  
**So that** stored content maintains proper encoding

**Acceptance Criteria:**
- 3.1 Database connection charset is set to utf8mb4
- 3.2 Database tables use utf8mb4_unicode_ci collation
- 3.3 Data retrieval preserves Vietnamese characters
- 3.4 Data insertion handles Vietnamese characters properly

### 4. Hosting Environment Compatibility
**As a** developer  
**I want** the encoding fix to work on the hosting environment  
**So that** Vietnamese text displays correctly in production

**Acceptance Criteria:**
- 4.1 Encoding works consistently across different hosting environments
- 4.2 File upload process preserves UTF-8 encoding
- 4.3 Server configuration supports UTF-8
- 4.4 .htaccess rules enforce UTF-8 encoding

## Technical Requirements

### File Encoding
- All PHP files must be UTF-8 encoded without BOM
- HTML meta charset must be UTF-8
- HTTP Content-Type headers must specify UTF-8

### Database Configuration
- Connection charset: utf8mb4
- Collation: utf8mb4_unicode_ci
- PDO charset attribute properly set

### Server Configuration
- .htaccess rules for UTF-8 encoding
- PHP default charset set to UTF-8
- Output buffering configured for UTF-8

## Success Criteria
- Vietnamese text displays correctly on all pages
- No HTML entities visible in rendered content
- Consistent encoding across development and production
- All new content maintains proper Vietnamese encoding

## Out of Scope
- Translation of existing English content to Vietnamese
- Font family changes (only encoding fixes)
- Performance optimization unrelated to encoding