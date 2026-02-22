# Requirements Document

## Introduction

Hệ thống trang quản trị admin hiện tại đang sử dụng dữ liệu hardcode trong các file PHP views và JavaScript của admin panel. Tính năng này sẽ chuyển đổi toàn bộ dữ liệu hardcode trong admin area thành dữ liệu động từ database, tạo API endpoints để cung cấp dữ liệu thời gian thực, và cải thiện hiệu suất thông qua caching.

## Glossary

- **Admin_Dashboard**: Trang tổng quan quản trị chính hiển thị các biểu đồ và thống kê
- **Admin_Header**: Header của trang admin với notifications và user menu
- **Admin_Sidebar**: Menu điều hướng bên trái của admin panel
- **Chart_Data**: Dữ liệu được sử dụng để hiển thị các biểu đồ (revenue, products, orders, users)
- **API_Endpoint**: Điểm cuối API cung cấp dữ liệu JSON cho frontend
- **Cache_System**: Hệ thống lưu trữ tạm thời để cải thiện hiệu suất
- **Real_Time_Data**: Dữ liệu được cập nhật từ database thay vì hardcode
- **AJAX_Request**: Yêu cầu bất đồng bộ để tải dữ liệu mà không reload trang
- **AdminService**: Service class hiện tại xử lý logic admin
- **Chart_JS**: Thư viện JavaScript hiện tại được sử dụng để hiển thị biểu đồ
- **Hardcode_Data**: Dữ liệu được viết cứng trong admin views thay vì lấy từ database

## Requirements

### Requirement 1: Revenue Chart Data Integration

**User Story:** Là một admin, tôi muốn xem biểu đồ doanh thu với dữ liệu thực từ database, để có thể theo dõi hiệu suất kinh doanh chính xác.

#### Acceptance Criteria

1. WHEN admin truy cập dashboard, THE Admin_Dashboard SHALL hiển thị biểu đồ doanh thu với dữ liệu từ bảng orders
2. WHEN admin thay đổi kỳ báo cáo (7 ngày, 30 ngày, 12 tháng), THE Chart_Data SHALL được cập nhật tương ứng từ database
3. WHEN không có dữ liệu doanh thu, THE Admin_Dashboard SHALL hiển thị biểu đồ trống với thông báo phù hợp
4. THE API_Endpoint SHALL trả về dữ liệu doanh thu theo định dạng JSON cho Chart_JS
5. THE Cache_System SHALL lưu trữ dữ liệu doanh thu trong 5 phút để cải thiện hiệu suất

### Requirement 2: Top Products Chart Data Integration

**User Story:** Là một admin, tôi muốn xem biểu đồ top sản phẩm bán chạy với dữ liệu thực, để hiểu được sản phẩm nào đang được khách hàng ưa chuộng.

#### Acceptance Criteria

1. WHEN admin xem dashboard, THE Admin_Dashboard SHALL hiển thị top 5 sản phẩm bán chạy từ dữ liệu orders và products
2. WHEN tính toán top products, THE Chart_Data SHALL được sắp xếp theo số lượng bán trong kỳ được chọn
3. THE API_Endpoint SHALL cung cấp thông tin tên sản phẩm, số lượng bán, và doanh thu cho mỗi sản phẩm
4. WHEN sản phẩm bị xóa khỏi database, THE Chart_Data SHALL loại bỏ sản phẩm đó khỏi thống kê
5. THE Cache_System SHALL lưu trữ dữ liệu top products trong 10 phút

### Requirement 3: Orders Status Chart Data Integration

**User Story:** Là một admin, tôi muốn xem phân bố trạng thái đơn hàng với dữ liệu thực, để quản lý quy trình xử lý đơn hàng hiệu quả.

#### Acceptance Criteria

1. WHEN admin xem dashboard, THE Admin_Dashboard SHALL hiển thị biểu đồ phân bố trạng thái đơn hàng từ bảng orders
2. THE Chart_Data SHALL bao gồm các trạng thái: completed, processing, pending, cancelled
3. WHEN trạng thái đơn hàng thay đổi, THE Real_Time_Data SHALL phản ánh sự thay đổi trong biểu đồ
4. THE API_Endpoint SHALL trả về số lượng và phần trăm cho mỗi trạng thái đơn hàng
5. WHEN không có đơn hàng nào, THE Admin_Dashboard SHALL hiển thị thông báo "Chưa có đơn hàng"

### Requirement 4: New Users Chart Data Integration

**User Story:** Là một admin, tôi muốn theo dõi số lượng người dùng mới theo thời gian với dữ liệu thực, để đánh giá hiệu quả marketing và tăng trưởng.

#### Acceptance Criteria

1. WHEN admin xem dashboard, THE Admin_Dashboard SHALL hiển thị biểu đồ người dùng mới theo 4 tuần gần nhất
2. THE Chart_Data SHALL được tính từ trường created_at trong bảng users
3. WHEN có người dùng mới đăng ký, THE Real_Time_Data SHALL cập nhật biểu đồ khi refresh
4. THE API_Endpoint SHALL cung cấp dữ liệu số lượng người dùng mới theo tuần/tháng
5. THE Cache_System SHALL lưu trữ dữ liệu người dùng mới trong 15 phút

### Requirement 5: Dashboard Statistics Cards Integration

**User Story:** Là một admin, tôi muốn xem các thẻ thống kê (KPI cards) với dữ liệu thực, để có cái nhìn tổng quan nhanh về hiệu suất hệ thống.

#### Acceptance Criteria

1. THE Admin_Dashboard SHALL hiển thị tổng số sản phẩm từ bảng products với status = 'active'
2. THE Admin_Dashboard SHALL hiển thị tổng doanh thu từ các đơn hàng có status = 'completed'
3. THE Admin_Dashboard SHALL hiển thị số tin tức đã xuất bản từ bảng news với status = 'published'
4. THE Admin_Dashboard SHALL hiển thị số sự kiện sắp tới từ bảng events với start_date > NOW()
5. THE Real_Time_Data SHALL cập nhật các thống kê này khi có thay đổi trong database

### Requirement 6: AJAX Data Loading Implementation

**User Story:** Là một admin, tôi muốn dashboard tải dữ liệu bất đồng bộ, để trang không bị chậm khi có nhiều dữ liệu cần xử lý.

#### Acceptance Criteria

1. WHEN dashboard load lần đầu, THE AJAX_Request SHALL được gửi để tải dữ liệu biểu đồ
2. WHEN admin thay đổi kỳ báo cáo, THE AJAX_Request SHALL được gửi để cập nhật dữ liệu mà không reload trang
3. WHEN AJAX request thất bại, THE Admin_Dashboard SHALL hiển thị thông báo lỗi và fallback data
4. THE AJAX_Request SHALL có timeout 30 giây để tránh treo trang
5. WHEN dữ liệu đang loading, THE Admin_Dashboard SHALL hiển thị loading indicator

### Requirement 7: API Endpoints Creation

**User Story:** Là một developer, tôi muốn có các API endpoints chuẩn để cung cấp dữ liệu dashboard, để frontend có thể tích hợp dễ dàng.

#### Acceptance Criteria

1. THE API_Endpoint `/api/admin/dashboard/revenue` SHALL trả về dữ liệu doanh thu theo kỳ
2. THE API_Endpoint `/api/admin/dashboard/top-products` SHALL trả về top sản phẩm bán chạy
3. THE API_Endpoint `/api/admin/dashboard/orders-status` SHALL trả về phân bố trạng thái đơn hàng
4. THE API_Endpoint `/api/admin/dashboard/new-users` SHALL trả về dữ liệu người dùng mới
5. THE API_Endpoint `/api/admin/dashboard/stats` SHALL trả về tất cả thống kê tổng quan
6. WHEN API request không hợp lệ, THE API_Endpoint SHALL trả về HTTP 400 với thông báo lỗi rõ ràng
7. THE API_Endpoint SHALL yêu cầu authentication admin để truy cập

### Requirement 8: Caching System Implementation

**User Story:** Là một admin, tôi muốn dashboard load nhanh ngay cả khi có nhiều dữ liệu, để có thể làm việc hiệu quả.

#### Acceptance Criteria

1. THE Cache_System SHALL lưu trữ dữ liệu dashboard với key dựa trên kỳ báo cáo và thời gian
2. WHEN cache hit, THE API_Endpoint SHALL trả về dữ liệu từ cache thay vì query database
3. WHEN cache miss, THE Cache_System SHALL query database và lưu kết quả vào cache
4. THE Cache_System SHALL tự động xóa cache khi có cập nhật dữ liệu liên quan
5. WHEN cache bị lỗi, THE API_Endpoint SHALL fallback về query database trực tiếp
6. THE Cache_System SHALL có TTL (Time To Live) khác nhau cho từng loại dữ liệu

### Requirement 9: Error Handling and Fallback

**User Story:** Là một admin, tôi muốn dashboard vẫn hoạt động khi có lỗi xảy ra, để không bị gián đoạn công việc.

#### Acceptance Criteria

1. WHEN database connection thất bại, THE Admin_Dashboard SHALL hiển thị thông báo lỗi và dữ liệu fallback
2. WHEN API endpoint trả về lỗi, THE Chart_JS SHALL hiển thị biểu đồ trống với thông báo phù hợp
3. THE AdminService SHALL log tất cả lỗi vào error log để debug
4. WHEN timeout xảy ra, THE AJAX_Request SHALL retry tối đa 2 lần trước khi báo lỗi
5. THE Admin_Dashboard SHALL có graceful degradation khi một số tính năng không khả dụng

### Requirement 10: Revenue Page Data Integration

**User Story:** Là một admin, tôi muốn trang báo cáo doanh thu sử dụng dữ liệu thực thay vì hardcode, để có báo cáo chính xác.

#### Acceptance Criteria

1. THE Revenue_Page SHALL sử dụng dữ liệu từ AdminService::getRevenueData() thay vì hardcode
2. WHEN admin thay đổi bộ lọc ngày tháng, THE Chart_Data SHALL được cập nhật từ database
3. THE Revenue_Chart SHALL hiển thị dữ liệu doanh thu theo ngày/tháng/quý/năm
4. THE Status_Chart SHALL hiển thị phân bố doanh thu theo trạng thái đơn hàng
5. THE Top_Products_Table SHALL hiển thị sản phẩm bán chạy với dữ liệu thực

### Requirement 11: JavaScript Files Cleanup

**User Story:** Là một developer, tôi muốn loại bỏ tất cả dữ liệu hardcode trong JavaScript files, để code dễ maintain và mở rộng.

#### Acceptance Criteria

1. THE admin_dashboard.js SHALL loại bỏ tất cả dữ liệu hardcode trong biến data arrays
2. THE admin_events.js SHALL loại bỏ fallback data hardcode và sử dụng API calls
3. THE admin_news.js SHALL loại bỏ fallback data hardcode và sử dụng API calls
4. WHEN JavaScript cần dữ liệu, THE AJAX_Request SHALL được sử dụng thay vì hardcode
5. THE JavaScript files SHALL có error handling khi API calls thất bại

### Requirement 12: Performance Optimization

**User Story:** Là một admin, tôi muốn dashboard load nhanh và mượt mà, để có trải nghiệm làm việc tốt.

#### Acceptance Criteria

1. THE API_Endpoint SHALL trả về response trong vòng 2 giây cho queries bình thường
2. THE Database_Query SHALL được optimize với proper indexing cho các trường thường dùng
3. THE AJAX_Request SHALL sử dụng compression để giảm bandwidth
4. THE Chart_Data SHALL được paginate khi có quá nhiều data points
5. THE Admin_Dashboard SHALL load progressive (hiển thị từng phần khi data sẵn sàng)

### Requirement 13: Admin Header Data Integration

**User Story:** Là một admin, tôi muốn header admin hiển thị thông báo và thông tin user thực tế từ database, để có thể theo dõi hệ thống hiệu quả.

#### Acceptance Criteria

1. THE Admin_Header SHALL hiển thị notifications thực tế từ bảng notifications thay vì hardcode
2. THE Notifications_Count SHALL được tính từ số thông báo chưa đọc trong database
3. THE User_Info SHALL hiển thị thông tin admin thực tế từ session/database
4. THE Notification_Items SHALL hiển thị 3 thông báo mới nhất với icon, message, time thực tế
5. THE Cache_System SHALL lưu trữ dữ liệu notifications trong 5 phút

### Requirement 14: Admin Sidebar Data Integration

**User Story:** Là một admin, tôi muốn sidebar menu có thể cấu hình từ database, để dễ dàng quản lý quyền truy cập.

#### Acceptance Criteria

1. THE Admin_Sidebar SHALL hiển thị menu items từ bảng admin_menus thay vì hardcode
2. THE Menu_Items SHALL có thể bật/tắt theo role của admin
3. THE Menu_Order SHALL có thể sắp xếp từ database
4. THE Menu_Icons SHALL có thể thay đổi từ admin panel
5. THE Active_State SHALL được tính toán dựa trên current page/module

### Requirement 15: Admin JavaScript Configuration Integration

**User Story:** Là một developer, tôi muốn loại bỏ tất cả configuration hardcode trong admin JavaScript files.

#### Acceptance Criteria

1. THE admin_dashboard.js SHALL loại bỏ tất cả dữ liệu biểu đồ hardcode
2. THE admin_pages.js SHALL loại bỏ bulk actions hardcode và lấy từ API
3. THE admin_affiliates.js SHALL loại bỏ notification settings hardcode
4. THE admin_events.js và admin_news.js SHALL loại bỏ fallback data hardcode
5. THE Color_Palettes SHALL được chuyển sang CSS variables hoặc config API