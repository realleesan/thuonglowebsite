# Requirements Document

## Introduction

Hệ thống hiện tại có vấn đề nghiêm trọng: các file JSON fake data đã được loại bỏ nhưng các view PHP vẫn sử dụng hardcoded HTML data demo. Điều này khiến website hiển thị dữ liệu giả thay vì kết nối với database thực tế. Feature này sẽ chuyển đổi tất cả các view PHP để sử dụng dynamic data từ database, đảm bảo website hoạt động với dữ liệu thực.

## Glossary

- **View_System**: Hệ thống hiển thị dữ liệu trong các file PHP view
- **Database_Models**: Các model PHP đã có trong app/models/ để truy cập database
- **Hardcoded_Data**: Dữ liệu HTML tĩnh được viết cứng trong code
- **Dynamic_Data**: Dữ liệu được lấy từ database thông qua models
- **Empty_State**: Trạng thái hiển thị khi không có dữ liệu từ database
- **Security_Filter**: Cơ chế lọc và bảo mật dữ liệu trước khi hiển thị

## Requirements

### Requirement 1: Data Source Conversion

**User Story:** Là một developer, tôi muốn tất cả các view PHP sử dụng dữ liệu từ database thay vì hardcoded data, để website hiển thị thông tin thực tế.

#### Acceptance Criteria

1. WHEN a view file is loaded, THE View_System SHALL retrieve data from appropriate Database_Models instead of using hardcoded HTML
2. WHEN displaying user information, THE View_System SHALL use UsersModel to fetch real user data
3. WHEN displaying product information, THE View_System SHALL use ProductsModel to fetch real product data
4. WHEN displaying order information, THE View_System SHALL use OrdersModel to fetch real order data
5. WHEN displaying affiliate information, THE View_System SHALL use AffiliateModel to fetch real affiliate data

### Requirement 2: Security and Data Validation

**User Story:** Là một system administrator, tôi muốn dữ liệu được hiển thị an toàn và được validate, để tránh các lỗ hổng bảo mật.

#### Acceptance Criteria

1. WHEN displaying user input data, THE View_System SHALL apply Security_Filter to prevent XSS attacks
2. WHEN rendering database content, THE View_System SHALL escape HTML special characters
3. WHEN processing sensitive data, THE View_System SHALL validate data types and formats
4. WHEN displaying monetary values, THE View_System SHALL format them properly and securely

### Requirement 3: Empty State Handling

**User Story:** Là một end user, tôi muốn thấy thông báo rõ ràng khi không có dữ liệu, thay vì trang trống hoặc lỗi.

#### Acceptance Criteria

1. WHEN no data is available from database, THE View_System SHALL display appropriate Empty_State messages
2. WHEN user has no orders, THE View_System SHALL show "Chưa có đơn hàng nào" message
3. WHEN admin views empty tables, THE View_System SHALL show "Không có dữ liệu" with option to add new items
4. WHEN affiliate has no customers, THE View_System SHALL display "Chưa có khách hàng nào" message

### Requirement 4: Model Integration

**User Story:** Là một developer, tôi muốn các view sử dụng đúng model tương ứng, để đảm bảo tính nhất quán trong việc truy cập dữ liệu.

#### Acceptance Criteria

1. WHEN admin views need user data, THE View_System SHALL use UsersModel methods
2. WHEN product pages need product data, THE View_System SHALL use ProductsModel methods
3. WHEN order pages need order data, THE View_System SHALL use OrdersModel methods
4. WHEN affiliate pages need affiliate data, THE View_System SHALL use AffiliateModel methods
5. WHEN contact pages need contact data, THE View_System SHALL use ContactsModel methods
6. WHEN settings pages need configuration data, THE View_System SHALL use SettingsModel methods

### Requirement 5: UI Consistency Preservation

**User Story:** Là một end user, tôi muốn giao diện website giữ nguyên như hiện tại, chỉ thay đổi nguồn dữ liệu.

#### Acceptance Criteria

1. WHEN converting views to dynamic data, THE View_System SHALL maintain existing CSS classes and HTML structure
2. WHEN displaying data, THE View_System SHALL preserve current styling and layout
3. WHEN rendering tables, THE View_System SHALL keep existing table headers and formatting
4. WHEN showing forms, THE View_System SHALL maintain current form structure and validation

### Requirement 6: Error Handling and Logging

**User Story:** Là một system administrator, tôi muốn hệ thống xử lý lỗi gracefully và ghi log để debug.

#### Acceptance Criteria

1. WHEN database connection fails, THE View_System SHALL display user-friendly error message
2. WHEN model methods return errors, THE View_System SHALL log errors and show fallback content
3. WHEN data retrieval fails, THE View_System SHALL attempt retry once before showing error
4. WHEN critical errors occur, THE View_System SHALL log detailed error information for debugging

### Requirement 7: Performance Optimization

**User Story:** Là một end user, tôi muốn website load nhanh và không bị chậm do việc truy vấn database.

#### Acceptance Criteria

1. WHEN loading view pages, THE View_System SHALL minimize database queries through efficient model usage
2. WHEN displaying lists, THE View_System SHALL implement pagination to avoid loading too much data
3. WHEN showing related data, THE View_System SHALL use appropriate joins instead of multiple queries
4. WHEN caching is available, THE View_System SHALL cache frequently accessed data

### Requirement 8: Admin Panel Data Integration

**User Story:** Là một administrator, tôi muốn admin panel hiển thị dữ liệu thực từ database để quản lý hệ thống hiệu quả.

#### Acceptance Criteria

1. WHEN viewing admin dashboard, THE View_System SHALL display real statistics from database
2. WHEN managing users, THE View_System SHALL show actual user data with proper pagination
3. WHEN managing products, THE View_System SHALL display real product information with categories
4. WHEN viewing orders, THE View_System SHALL show actual order data with customer information
5. WHEN managing affiliates, THE View_System SHALL display real affiliate data and commissions

### Requirement 9: User Dashboard Data Integration

**User Story:** Là một registered user, tôi muốn dashboard hiển thị thông tin cá nhân và lịch sử đơn hàng thực tế.

#### Acceptance Criteria

1. WHEN user accesses dashboard, THE View_System SHALL display user's personal information from database
2. WHEN viewing order history, THE View_System SHALL show user's actual orders with details
3. WHEN checking account settings, THE View_System SHALL display current user preferences
4. WHEN viewing profile, THE View_System SHALL show editable user information from database

### Requirement 10: Affiliate Panel Data Integration

**User Story:** Là một affiliate user, tôi muốn affiliate panel hiển thị dữ liệu thực về khách hàng và hoa hồng.

#### Acceptance Criteria

1. WHEN affiliate views dashboard, THE View_System SHALL display real commission data
2. WHEN checking customer list, THE View_System SHALL show actual referred customers
3. WHEN viewing earnings, THE View_System SHALL display real earning statistics
4. WHEN accessing reports, THE View_System SHALL generate reports from actual database data