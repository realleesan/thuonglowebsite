# Design Document

## Overview

Hệ thống hiện tại có vấn đề nghiêm trọng: các file JSON fake data đã được loại bỏ nhưng các view PHP vẫn chứa hardcoded HTML data demo. Thiết kế này sẽ chuyển đổi tất cả các view PHP để sử dụng dynamic data từ database thông qua các model đã có, đảm bảo website hoạt động với dữ liệu thực tế.

## Architecture

### Current State Analysis

**Problematic Patterns Identified:**
- Hardcoded product listings với thông tin giả (tên, giá, mô tả)
- Hardcoded category data với số lượng sản phẩm giả
- Hardcoded testimonials và customer reviews
- Hardcoded statistics và metrics
- Hardcoded news/blog items
- Mixed approach: một số view đã được chuyển đổi (dashboard) nhưng vẫn có hardcoded data

**Available Infrastructure:**
- Complete model layer với BaseModel và specialized models
- Database connection through Database class
- Existing methods: getAll(), find(), getWithCategory(), getFeatured(), etc.
- Security features: fillable fields, hidden fields, data filtering

### Target Architecture

```
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│   View Layer    │    │   Model Layer   │    │   Database      │
│                 │    │                 │    │                 │
│ • Dynamic PHP   │───▶│ • ProductsModel │───▶│ • products      │
│ • Data binding  │    │ • UsersModel    │    │ • users         │
│ • Error handling│    │ • OrdersModel   │    │ • orders        │
│ • Empty states  │    │ • CategoriesModel│   │ • categories    │
│                 │    │ • NewsModel     │    │ • news          │
└─────────────────┘    └─────────────────┘    └─────────────────┘
```

## Components and Interfaces

### 1. View Data Service Layer

**Purpose**: Centralized service để chuẩn bị data cho views

```php
class ViewDataService {
    // Chuẩn bị data cho home page
    public function getHomePageData(): array
    
    // Chuẩn bị data cho product listings
    public function getProductListingData($filters = []): array
    
    // Chuẩn bị data cho category pages
    public function getCategoryPageData($categoryId): array
    
    // Xử lý empty states
    public function handleEmptyState($type): array
}
```

### 2. Data Transformation Layer

**Purpose**: Chuyển đổi raw database data thành format phù hợp cho views

```php
class DataTransformer {
    // Transform product data for display
    public function transformProduct($product): array
    
    // Transform user data for display
    public function transformUser($user): array
    
    // Format prices, dates, etc.
    public function formatDisplayData($data, $type): array
}
```

### 3. Security and Validation Layer

**Purpose**: Đảm bảo data được escape và validate trước khi hiển thị

```php
class ViewSecurityHelper {
    // Escape HTML để tránh XSS
    public function escapeHtml($data): string
    
    // Validate và sanitize user input
    public function sanitizeInput($input): string
    
    // Format monetary values safely
    public function formatMoney($amount): string
}
```

## Data Models

### Enhanced Model Methods

**ProductsModel Extensions:**
```php
// Get featured products for home page
public function getFeaturedForHome($limit = 8): array

// Get products by category with pagination
public function getByCategory($categoryId, $page = 1, $limit = 12): array

// Get product statistics for admin
public function getProductStats(): array
```

**CategoriesModel Extensions:**
```php
// Get categories with product counts
public function getWithProductCounts(): array

// Get featured categories for home page
public function getFeaturedCategories($limit = 9): array
```

**NewsModel Extensions:**
```php
// Get latest news for home page
public function getLatestForHome($limit = 8): array

// Get news with categories
public function getWithCategories($limit = null): array
```

### Data Structure Standards

**Product Display Format:**
```php
[
    'id' => int,
    'name' => string,
    'slug' => string,
    'price' => float,
    'sale_price' => float|null,
    'image' => string,
    'category_name' => string,
    'status' => string,
    'featured' => boolean,
    'formatted_price' => string, // "500.000đ"
    'discount_percent' => int|null
]
```

**Category Display Format:**
```php
[
    'id' => int,
    'name' => string,
    'slug' => string,
    'image' => string,
    'product_count' => int,
    'description' => string
]
```

## Correctness Properties

*A property is a characteristic or behavior that should hold true across all valid executions of a system-essentially, a formal statement about what the system should do. Properties serve as the bridge between human-readable specifications and machine-verifiable correctness guarantees.*

Trước khi viết các correctness properties, tôi cần phân tích các acceptance criteria từ requirements document:

<function_calls>
<invoke name="prework">
<parameter name="featureName">view-data-conversion

### Property Reflection

Sau khi phân tích prework, tôi nhận thấy có một số properties có thể được gộp lại để tránh redundancy:

**Redundancy Analysis:**
- Properties 1.2, 1.3, 1.4, 1.5 và 4.1, 4.2, 4.3, 4.4, 4.5, 4.6 đều về việc sử dụng đúng model → có thể gộp thành một property tổng quát về model usage
- Properties 8.1, 8.2, 8.3, 8.4, 8.5 và 9.1, 9.2, 9.3, 9.4 và 10.1, 10.2, 10.3, 10.4 đều về việc hiển thị real data → có thể gộp thành property về database data usage
- Properties 2.1, 2.2 đều về security escaping → có thể gộp lại
- Properties 6.2, 6.3, 6.4 đều về error handling → có thể gộp lại

**Consolidated Properties:**
1. Model usage consistency (thay thế 1.2-1.5, 4.1-4.6)
2. Database data display (thay thế 8.1-8.5, 9.1-9.4, 10.1-10.4)
3. Security escaping (thay thế 2.1-2.2)
4. Error handling (thay thế 6.2-6.4)

### Correctness Properties

**Property 1: Database Model Usage Consistency**
*For any* view that needs to display data, the view should call the appropriate model method rather than using hardcoded data
**Validates: Requirements 1.1, 1.2, 1.3, 1.4, 1.5, 4.1, 4.2, 4.3, 4.4, 4.5, 4.6**

**Property 2: Database Data Display**
*For any* view that displays information, all displayed data should come from database queries through models rather than hardcoded values
**Validates: Requirements 8.1, 8.2, 8.3, 8.4, 8.5, 9.1, 9.2, 9.3, 9.4, 10.1, 10.2, 10.3, 10.4**

**Property 3: Security Data Escaping**
*For any* data displayed in views, all user input and database content should be properly escaped to prevent XSS attacks
**Validates: Requirements 2.1, 2.2**

**Property 4: Data Validation and Formatting**
*For any* sensitive data processed by views, the data should be validated for type and format before display
**Validates: Requirements 2.3, 2.4**

**Property 5: Empty State Handling**
*For any* view that queries database, when no data is returned, an appropriate empty state message should be displayed
**Validates: Requirements 3.1**

**Property 6: UI Structure Preservation**
*For any* view converted from hardcoded to dynamic data, the HTML structure and CSS classes should remain unchanged
**Validates: Requirements 5.1, 5.3, 5.4**

**Property 7: Error Handling and Recovery**
*For any* view that encounters data retrieval errors, the system should log the error, attempt retry once, and display fallback content
**Validates: Requirements 6.2, 6.3, 6.4**

**Property 8: Performance Optimization**
*For any* view that loads data, the number of database queries should be minimized through efficient model usage and appropriate joins
**Validates: Requirements 7.1, 7.3**

**Property 9: Pagination Implementation**
*For any* view that displays lists of data, large datasets should be paginated to avoid loading excessive data
**Validates: Requirements 7.2**

**Property 10: Caching Behavior**
*For any* view that accesses frequently requested data, the data should be cached when caching is available
**Validates: Requirements 7.4**

## Error Handling

### Error Categories

**1. Database Connection Errors**
- Connection timeout
- Authentication failure
- Database server unavailable

**2. Model Method Errors**
- Invalid parameters
- SQL syntax errors
- Constraint violations

**3. Data Validation Errors**
- Invalid data types
- Missing required fields
- Format validation failures

**4. View Rendering Errors**
- Template not found
- Missing variables
- Syntax errors in PHP

### Error Handling Strategy

**Graceful Degradation:**
```php
try {
    $products = $productsModel->getFeatured(8);
} catch (DatabaseException $e) {
    // Log error
    error_log("Database error in home page: " . $e->getMessage());
    
    // Show fallback content
    $products = [];
    $showErrorMessage = true;
}
```

**Retry Logic:**
```php
public function getDataWithRetry($modelMethod, $params = [], $maxRetries = 1) {
    $attempts = 0;
    
    while ($attempts <= $maxRetries) {
        try {
            return call_user_func_array($modelMethod, $params);
        } catch (Exception $e) {
            $attempts++;
            if ($attempts > $maxRetries) {
                throw $e;
            }
            // Wait before retry
            usleep(100000); // 100ms
        }
    }
}
```

**User-Friendly Error Messages:**
- Database errors: "Đang có lỗi kỹ thuật, vui lòng thử lại sau"
- Empty data: "Chưa có dữ liệu để hiển thị"
- Validation errors: "Dữ liệu không hợp lệ"

## Testing Strategy

### Dual Testing Approach

**Unit Tests:**
- Test specific view data preparation methods
- Test error handling scenarios
- Test data transformation functions
- Test security escaping functions
- Test empty state handling

**Property-Based Tests:**
- Test universal properties across all views
- Generate random view scenarios and verify properties hold
- Test with various database states (empty, full, error conditions)
- Verify security properties with random input data
- Test performance properties with different data sizes

### Property-Based Testing Configuration

**Testing Framework:** PHPUnit with Eris (property-based testing library for PHP)
**Minimum Iterations:** 100 per property test
**Test Tagging:** Each property test must reference its design document property

**Example Property Test Structure:**
```php
/**
 * Feature: view-data-conversion, Property 1: Database Model Usage Consistency
 * For any view that needs to display data, the view should call the appropriate 
 * model method rather than using hardcoded data
 */
public function testDatabaseModelUsageConsistency() {
    $this->forAll(
        Generator\elements(['home', 'products', 'users', 'admin']),
        Generator\choose(1, 100)
    )->then(function ($viewType, $dataCount) {
        // Test implementation
        $this->assertViewUsesModel($viewType, $dataCount);
    });
}
```

### Testing Categories

**Security Testing:**
- XSS prevention with malicious input
- SQL injection prevention
- Data sanitization verification

**Performance Testing:**
- Query count optimization
- Page load time measurement
- Memory usage monitoring

**Integration Testing:**
- End-to-end view rendering
- Database interaction testing
- Error scenario simulation

**Visual Regression Testing:**
- HTML structure comparison
- CSS class preservation
- Layout consistency verification