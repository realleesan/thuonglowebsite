# Thiết Kế Tái Cấu Trúc Kiến Trúc Service

## Tổng Quan

Thiết kế này tái cấu trúc ViewDataService khổng lồ thành hệ thống services nhỏ, độc lập theo chức năng. Mỗi service chỉ load Models cần thiết và có error handling riêng, đảm bảo 1 service lỗi không crash toàn website.

## Kiến Trúc Tổng Thể

### Cấu Trúc Hiện Tại
```
ViewDataService (2375+ dòng)
├── Constructor tạo 8+ Models
├── 50+ methods cho tất cả chức năng
└── 1 lỗi → crash toàn website
```

### Cấu Trúc Mới
```
ServiceManager
├── PublicService (trang công khai)
├── UserService (dashboard người dùng)  
├── AdminService (quản trị)
├── AffiliateService (đại lý)
└── FallbackService (backup khi lỗi)
```

## Các Thành Phần Chính

### 1. ServiceManager
**Vai trò:** Quản lý tất cả services, lazy loading, error handling tập trung

**Interface:**
```php
class ServiceManager {
    public function getService(string $type, string $name = 'default'): ServiceInterface
    public function getFallbackService(string $type): FallbackServiceInterface
    private function createService(string $type, string $name): ServiceInterface
    private function handleServiceError(Exception $e, string $type): ServiceInterface
}
```

**Chức năng:**
- Lazy loading: Services chỉ được tạo khi cần
- Caching: Tránh tạo lại service đã tồn tại
- Error handling: Fallback khi service chính lỗi
- Type safety: Validate service type hợp lệ

### 2. Service Classes

#### PublicService
**Phạm vi:** Trang công khai không cần authentication
```php
class PublicService implements ServiceInterface {
    // Models: ProductsModel, CategoriesModel, NewsModel, ContactsModel
    // Methods: getHomeData(), getProductsData(), getCategoriesData(), 
    //          getNewsData(), getContactData(), getAuthData()
}
```

#### UserService  
**Phạm vi:** Dashboard và chức năng người dùng
```php
class UserService implements ServiceInterface {
    // Models: UsersModel, OrdersModel, ProductsModel
    // Methods: getDashboardData(), getAccountData(), getOrdersData(),
    //          getCartData(), getWishlistData()
}
```

#### AdminService
**Phạm vi:** Quản trị hệ thống
```php
class AdminService implements ServiceInterface {
    // Models: Tất cả models tùy theo chức năng
    // Methods: getDashboardData(), getProductsData(), getUsersData(),
    //          getOrdersData(), getSettingsData(), etc.
}
```

#### AffiliateService
**Phạm vi:** Chức năng đại lý
```php
class AffiliateService implements ServiceInterface {
    // Models: AffiliateModel, OrdersModel, UsersModel
    // Methods: getDashboardData(), getCommissionsData(), getCustomersData(),
    //          getReportsData(), getFinanceData()
}
```

### 3. Base Classes

#### ServiceInterface
```php
interface ServiceInterface {
    public function getData(string $method, array $params = []): array;
    public function getModel(string $modelName): ?BaseModel;
    public function handleError(Exception $e): array;
}
```

#### BaseService
```php
abstract class BaseService implements ServiceInterface {
    protected array $models = [];
    protected ErrorHandler $errorHandler;
    
    protected function getModel(string $modelName): ?BaseModel
    protected function handleError(Exception $e): array
    protected function getEmptyData(): array
}
```

#### FallbackService
```php
class FallbackService implements ServiceInterface {
    // Trả về empty data khi service chính lỗi
    // Đảm bảo website không crash
}
```

## Lazy Loading Strategy

### Model Loading
```php
protected function getModel(string $modelName): ?BaseModel {
    if (!isset($this->models[$modelName])) {
        try {
            $this->models[$modelName] = new $modelName();
        } catch (Exception $e) {
            $this->errorHandler->log($e);
            return null;
        }
    }
    return $this->models[$modelName];
}
```

### Service Loading
```php
public function getService(string $type, string $name = 'default'): ServiceInterface {
    $key = $type . '_' . $name;
    if (!isset($this->services[$key])) {
        try {
            $this->services[$key] = $this->createService($type, $name);
        } catch (Exception $e) {
            return $this->getFallbackService($type);
        }
    }
    return $this->services[$key];
}
```

## Error Handling Strategy

### 3-Layer Error Handling

1. **Model Level:** Try-catch khi tạo Model
2. **Service Level:** Try-catch trong mỗi method
3. **Manager Level:** Fallback service khi service chính lỗi

### Error Flow
```
Service Method Call
├── Try: Execute business logic
├── Catch: Log error + return empty data
└── Fallback: Use FallbackService if needed
```

### Empty Data Structure
```php
protected function getEmptyData(): array {
    return [
        'success' => false,
        'data' => [],
        'message' => 'Service temporarily unavailable',
        'fallback' => true
    ];
}
```

## Migration Strategy

### Phase 1: Tạo Infrastructure
- Tạo ServiceManager
- Tạo base classes và interfaces
- Tạo FallbackService
- Setup error handling

### Phase 2: Tạo Services
- Tạo 4 service classes chính
- Migrate methods từ ViewDataService
- Implement lazy loading
- Add error handling

### Phase 3: Update Views
- Sửa view_init.php sử dụng ServiceManager
- Update routing pass service vào views
- Sửa views sử dụng service thay vì ViewDataService
- Handle empty data trong views

### Phase 4: Testing & Cleanup
- Test từng service độc lập
- Test error scenarios
- Remove ViewDataService cũ
- Cleanup code

## Data Models

### Service-Model Mapping
```php
// PublicService
'ProductsModel', 'CategoriesModel', 'NewsModel', 'ContactsModel'

// UserService  
'UsersModel', 'OrdersModel', 'ProductsModel'

// AdminService
'ProductsModel', 'CategoriesModel', 'UsersModel', 'OrdersModel', 
'NewsModel', 'EventsModel', 'AffiliateModel', 'ContactsModel', 'SettingsModel'

// AffiliateService
'AffiliateModel', 'OrdersModel', 'UsersModel'
```

### Model Loading Rules
- Models chỉ load khi method cần sử dụng
- Cache Model instance để tránh tạo lại
- Return null nếu Model không tạo được
- Log error nhưng không crash service

## Correctness Properties

*Correctness properties là các đặc tính hoặc hành vi phải đúng trong tất cả các trường hợp thực thi hệ thống - về cơ bản là các phát biểu chính thức về những gì hệ thống nên làm.*

### Property 1: Service Isolation
*Đối với bất kỳ* service nào bị lỗi, các service khác vẫn phải hoạt động bình thường và không bị ảnh hưởng
**Validates: Requirements US3.2**

### Property 2: Lazy Loading Consistency  
*Đối với bất kỳ* service nào, Models chỉ được tạo khi method thực sự cần sử dụng, không được tạo trong constructor
**Validates: Requirements US2.1, US2.2**

### Property 3: Model Caching
*Đối với bất kỳ* service nào, khi gọi getModel() nhiều lần với cùng tên Model, phải trả về cùng một instance
**Validates: Requirements US2.3**

### Property 4: Error Graceful Handling
*Đối với bất kỳ* service method nào bị lỗi, phải trả về empty data thay vì crash và phải log error
**Validates: Requirements US3.1, US3.2, US3.4**

### Property 5: Service Type Validation
*Đối với bất kỳ* service type nào được request, ServiceManager phải trả về đúng loại service tương ứng hoặc fallback service
**Validates: Requirements US4.2, US4.3**

### Property 6: View Data Handling
*Đối với bất kỳ* view nào nhận empty data từ service, view phải render được mà không crash
**Validates: Requirements US3.3, US5.4**

### Property 7: Service Method Isolation
*Đối với bất kỳ* service nào, mỗi method chỉ chứa logic liên quan đến chức năng của service đó
**Validates: Requirements US1.5**

### Property 8: Dependency Injection
*Đối với bất kỳ* view nào, phải nhận service instance từ routing thay vì sử dụng global variable
**Validates: Requirements US5.1, US5.2**

## Error Handling

### Error Types
1. **Model Creation Error:** Model constructor fails
2. **Database Connection Error:** Database không kết nối được  
3. **Service Method Error:** Business logic fails
4. **View Rendering Error:** View không render được

### Error Response Format
```php
[
    'success' => false,
    'data' => [],
    'error' => [
        'type' => 'service_error',
        'message' => 'User-friendly message',
        'code' => 'ERROR_CODE'
    ],
    'fallback' => true
]
```

### Logging Strategy
- Log tất cả errors với full stack trace
- Include service name, method name, parameters
- Separate log files cho từng service type
- Daily rotation để tránh file quá lớn

## Testing Strategy

### Dual Testing Approach
Hệ thống sử dụng cả unit tests và property-based tests để đảm bảo tính đúng đắn toàn diện:

**Unit Tests:**
- Test các trường hợp cụ thể và edge cases
- Test integration giữa các components
- Test error conditions và fallback mechanisms
- Verify service creation và method calls

**Property-Based Tests:**
- Verify universal properties across all inputs
- Test với 100+ iterations mỗi property
- Comprehensive input coverage through randomization
- Validate correctness properties từ design document

**Property Test Configuration:**
- Minimum 100 iterations per property test
- Tag format: **Feature: service-architecture-refactor, Property {number}: {property_text}**
- Mỗi correctness property được implement bởi một property-based test
- Sử dụng PHPUnit với faker library cho random data generation

**Testing Balance:**
- Unit tests focus on specific examples, edge cases, error conditions
- Property tests focus on universal properties that hold for all inputs
- Together provide comprehensive coverage (unit tests catch concrete bugs, property tests verify general correctness)