# View Data Conversion Infrastructure

This directory contains the infrastructure classes for converting hardcoded HTML data in PHP views to dynamic database-driven content.

## Classes Overview

### ViewDataService
**Purpose**: Centralized service for preparing data for views
**Location**: `app/services/ViewDataService.php`

**Key Methods**:
- `getHomePageData()`: Prepares data for home page (featured products, categories, news)
- `getProductListingData($filters)`: Prepares product listing with pagination
- `getCategoryPageData($categoryId)`: Prepares category page data
- `getAdminDashboardData()`: Prepares admin dashboard statistics
- `getUserDashboardData($userId)`: Prepares user dashboard data
- `getAffiliateDashboardData($affiliateId)`: Prepares affiliate dashboard data
- `handleEmptyState($type)`: Returns appropriate empty state data

**Features**:
- Retry logic for database operations
- Graceful error handling
- Empty state management
- Centralized data preparation

### DataTransformer
**Purpose**: Transform raw database data into view-ready format
**Location**: `app/services/DataTransformer.php`

**Key Methods**:
- `transformProduct($product)`: Formats product data with price formatting, discount calculation
- `transformProducts($products)`: Transforms array of products
- `transformCategory($category)`: Formats category data
- `transformUser($user)`: Formats user data with points, spending
- `transformOrder($order)`: Formats order data with status labels
- `transformNews($news)`: Formats news data
- `transformAffiliate($affiliate)`: Formats affiliate data

**Features**:
- Automatic HTML escaping via ViewSecurityHelper
- Price formatting (e.g., "100.000đ")
- Discount percentage calculation
- Date formatting for display
- Status label translation (Vietnamese)

### ViewSecurityHelper
**Purpose**: Security and data validation for views
**Location**: `app/services/ViewSecurityHelper.php`

**Key Methods**:
- `escapeHtml($data)`: Prevents XSS attacks
- `sanitizeInput($input)`: Cleans user input
- `formatMoney($amount)`: Formats monetary values safely
- `validateEmail($email)`: Validates email format
- `validatePhone($phone)`: Validates Vietnamese phone numbers
- `sanitizeArray($data, $rules)`: Sanitizes array data with rules
- `generateCsrfToken()`: Generates CSRF tokens
- `verifyCsrfToken($token)`: Verifies CSRF tokens

**Features**:
- XSS prevention
- Input sanitization
- Data type validation
- Vietnamese phone number validation
- CSRF protection
- Safe file name handling

### ErrorHandler
**Purpose**: Centralized error handling and logging
**Location**: `app/services/ErrorHandler.php`

**Key Methods**:
- `handleDatabaseError($exception)`: Handles database errors
- `handleModelError($exception, $model, $method)`: Handles model errors
- `handleViewError($exception, $view)`: Handles view rendering errors
- `handleValidationError($errors)`: Handles validation errors
- `handleNotFoundError($resource, $id)`: Handles 404 errors
- `logError($message, $context, $level)`: Logs errors with context

**Features**:
- Structured error logging
- User-friendly error messages in Vietnamese
- Error categorization
- Context preservation
- Log rotation and cleanup

## Usage Examples

### Basic View Data Preparation
```php
require_once 'app/services/ViewDataService.php';

$viewDataService = new ViewDataService();

// Get home page data
$homeData = $viewDataService->getHomePageData();

// Get product listing with filters
$productData = $viewDataService->getProductListingData([
    'category_id' => 1,
    'page' => 1,
    'limit' => 12
]);
```

### Data Transformation
```php
require_once 'app/services/DataTransformer.php';

$transformer = new DataTransformer();

// Transform single product
$product = ['id' => 1, 'name' => 'Test', 'price' => 100000];
$transformed = $transformer->transformProduct($product);
// Result: ['formatted_price' => '100.000đ', 'name' => 'Test', ...]

// Transform multiple products
$products = [$product1, $product2];
$transformed = $transformer->transformProducts($products);
```

### Security and Validation
```php
require_once 'app/services/ViewSecurityHelper.php';

$security = new ViewSecurityHelper();

// Escape HTML
$safe = $security->escapeHtml('<script>alert("xss")</script>');

// Format money
$formatted = $security->formatMoney(1234567); // "1.234.567đ"

// Validate email
$isValid = $security->validateEmail('test@example.com'); // true
```

### Error Handling
```php
require_once 'app/services/ErrorHandler.php';

$errorHandler = new ErrorHandler();

try {
    // Some database operation
} catch (Exception $e) {
    $result = $errorHandler->handleDatabaseError($e);
    // Returns: ['success' => false, 'message' => 'User-friendly message']
}
```

## Integration with Models

The infrastructure classes work with enhanced model methods:

### ProductsModel Extensions
- `getFeaturedForHome($limit)`: Get featured products for home page
- `getLatestForHome($limit)`: Get latest products for home page
- `getByCategoryPaginated($categoryId, $page, $limit)`: Paginated category products

### CategoriesModel Extensions
- `getWithProductCounts()`: Categories with product counts
- `getFeaturedCategories($limit)`: Featured categories for home page

### NewsModel Extensions
- `getLatestForHome($limit)`: Latest news for home page
- `getWithCategories($limit)`: News with category information

## Testing

Run infrastructure tests:
```bash
php tests/InfrastructureTest.php
```

Tests cover:
- HTML escaping and XSS prevention
- Data transformation and formatting
- Input validation and sanitization
- Error handling scenarios
- Money formatting
- Email and phone validation

## Security Features

1. **XSS Prevention**: All user data is escaped before display
2. **Input Sanitization**: User input is cleaned and validated
3. **CSRF Protection**: Token generation and verification
4. **Data Validation**: Type checking and format validation
5. **Safe File Handling**: Filename sanitization
6. **Error Logging**: Secure error logging without exposing sensitive data

## Performance Considerations

1. **Retry Logic**: Automatic retry for transient database errors
2. **Efficient Queries**: Models use optimized queries with joins
3. **Pagination**: Large datasets are paginated automatically
4. **Caching Ready**: Infrastructure supports caching layer (to be implemented)
5. **Minimal Overhead**: Lightweight transformation and validation

## Next Steps

1. **Database Connection**: Ensure database is running for full functionality
2. **View Integration**: Start converting views to use these services
3. **Caching Layer**: Implement caching for frequently accessed data
4. **Property-Based Testing**: Add comprehensive property-based tests
5. **Performance Monitoring**: Add query counting and performance metrics