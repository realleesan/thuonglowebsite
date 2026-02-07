# Requirements Document

## Introduction

Hệ thống frontend affiliate (đại lý) cho phép các đại lý quản lý hoạt động marketing, theo dõi doanh thu, hoa hồng, khách hàng và công cụ marketing. Hệ thống sử dụng PHP thuần, dữ liệu demo từ JSON, và tích hợp với layout/routing hiện có.

## Glossary

- **Affiliate_System**: Hệ thống quản lý đại lý
- **Dashboard**: Trang tổng quan hiển thị thống kê và biểu đồ
- **Commission**: Hoa hồng được tính từ doanh số bán hàng
- **Affiliate_Link**: Link giới thiệu có gắn ID đại lý
- **Demo_Data**: Dữ liệu mẫu từ file JSON
- **Layout_Component**: Các thành phần giao diện (sidebar, breadcrumb, header, footer)
- **Module**: Nhóm chức năng (commissions, customers, finance, marketing, profile, reports)
- **Conversion_Rate**: Tỉ lệ chuyển đổi từ click thành đơn hàng
- **Withdrawal**: Yêu cầu rút tiền hoa hồng

## Requirements

### Requirement 1: Dashboard Overview

**User Story:** Là một đại lý, tôi muốn xem tổng quan hoạt động của mình, để có thể nắm bắt nhanh tình hình kinh doanh.

#### Acceptance Criteria

1. WHEN đại lý truy cập dashboard, THE Affiliate_System SHALL hiển thị biểu đồ doanh thu theo thời gian
2. WHEN đại lý truy cập dashboard, THE Affiliate_System SHALL hiển thị số lượt click vào Affiliate_Link
3. WHEN đại lý truy cập dashboard, THE Affiliate_System SHALL hiển thị tổng hoa hồng tạm tính (pending commission)
4. WHEN đại lý truy cập dashboard, THE Affiliate_System SHALL hiển thị Conversion_Rate
5. WHEN đại lý truy cập dashboard, THE Affiliate_System SHALL hiển thị danh sách khách hàng đã sale thành công
6. WHEN đại lý truy cập dashboard, THE Affiliate_System SHALL hiển thị doanh số tổng (total revenue)
7. WHEN đại lý truy cập dashboard, THE Affiliate_System SHALL hiển thị doanh số theo tuần (weekly revenue) riêng biệt
8. WHEN đại lý truy cập dashboard, THE Affiliate_System SHALL hiển thị doanh số theo tháng (monthly revenue) riêng biệt
9. WHEN đại lý truy cập dashboard, THE Affiliate_System SHALL hiển thị tình trạng thanh toán hoa hồng (chờ thanh toán/đã thanh toán)
10. WHEN đại lý truy cập dashboard, THE Affiliate_System SHALL hiển thị Link AFF (Affiliate Link) duy nhất
11. WHEN đại lý truy cập dashboard, THE Affiliate_System SHALL hiển thị mã giới thiệu (Referral Code/ID đại lý)
12. THE Dashboard SHALL load dữ liệu từ Demo_Data JSON file

### Requirement 2: Commission Management

**User Story:** Là một đại lý, tôi muốn quản lý hoa hồng của mình, để theo dõi thu nhập và chính sách hoa hồng.

#### Acceptance Criteria

1. WHEN đại lý truy cập lịch sử hoa hồng, THE Affiliate_System SHALL hiển thị danh sách các khoản Commission đã nhận
2. WHEN hiển thị lịch sử hoa hồng, THE Affiliate_System SHALL bao gồm ngày tháng, số tiền, trạng thái thanh toán
3. WHEN đại lý truy cập chính sách hoa hồng, THE Affiliate_System SHALL hiển thị các mức hoa hồng theo sản phẩm/doanh số
4. THE Affiliate_System SHALL load dữ liệu hoa hồng từ Demo_Data JSON file

### Requirement 3: Customer Management

**User Story:** Là một đại lý, tôi muốn quản lý khách hàng đã giới thiệu, để theo dõi hiệu quả marketing.

#### Acceptance Criteria

1. WHEN đại lý truy cập danh sách khách hàng, THE Affiliate_System SHALL hiển thị tất cả khách hàng đã giới thiệu
2. WHEN hiển thị danh sách khách hàng, THE Affiliate_System SHALL bao gồm tên, email, ngày đăng ký, tổng đơn hàng
3. WHEN đại lý click vào một khách hàng, THE Affiliate_System SHALL hiển thị chi tiết khách hàng
4. WHEN hiển thị chi tiết khách hàng, THE Affiliate_System SHALL bao gồm lịch sử đơn hàng và doanh số
5. THE Affiliate_System SHALL load dữ liệu khách hàng từ Demo_Data JSON file

### Requirement 4: Financial Management

**User Story:** Là một đại lý, tôi muốn quản lý tài chính, để theo dõi số dư và thực hiện rút tiền.

#### Acceptance Criteria

1. WHEN đại lý truy cập trang số dư, THE Affiliate_System SHALL hiển thị số dư khả dụng
2. WHEN đại lý truy cập trang số dư, THE Affiliate_System SHALL hiển thị số tiền đang chờ thanh toán
3. WHEN đại lý truy cập trang số dư, THE Affiliate_System SHALL hiển thị lịch sử giao dịch
4. WHEN đại lý truy cập trang rút tiền, THE Affiliate_System SHALL hiển thị form yêu cầu Withdrawal
5. WHEN đại lý truy cập trang rút tiền, THE Affiliate_System SHALL hiển thị lịch sử các lần rút tiền
6. THE Affiliate_System SHALL load dữ liệu tài chính từ Demo_Data JSON file

### Requirement 5: Marketing Tools

**User Story:** Là một đại lý, tôi muốn sử dụng công cụ marketing, để quảng bá sản phẩm hiệu quả.

#### Acceptance Criteria

1. WHEN đại lý truy cập công cụ marketing, THE Affiliate_System SHALL hiển thị Affiliate_Link duy nhất có gắn ID đại lý
2. WHEN đại lý truy cập công cụ marketing, THE Affiliate_System SHALL cung cấp banner quảng cáo để tải về
3. WHEN đại lý truy cập công cụ marketing, THE Affiliate_System SHALL cung cấp QR code cho Affiliate_Link
4. WHEN đại lý truy cập chiến dịch marketing, THE Affiliate_System SHALL hiển thị danh sách các chiến dịch đang chạy
5. WHEN hiển thị chiến dịch marketing, THE Affiliate_System SHALL bao gồm tên chiến dịch, thời gian, hiệu suất
6. THE Affiliate_System SHALL load dữ liệu marketing từ Demo_Data JSON file

### Requirement 6: Profile Management

**User Story:** Là một đại lý, tôi muốn quản lý hồ sơ cá nhân, để cập nhật thông tin tài khoản.

#### Acceptance Criteria

1. WHEN đại lý truy cập cài đặt tài khoản, THE Affiliate_System SHALL hiển thị thông tin cá nhân hiện tại
2. WHEN đại lý truy cập cài đặt tài khoản, THE Affiliate_System SHALL hiển thị mã giới thiệu (Referral Code/ID đại lý) của đại lý
3. WHEN đại lý truy cập cài đặt tài khoản, THE Affiliate_System SHALL hiển thị Link AFF (Affiliate Link) duy nhất
4. WHEN đại lý truy cập cài đặt tài khoản, THE Affiliate_System SHALL hiển thị form cập nhật thông tin
5. WHEN đại lý truy cập cài đặt tài khoản, THE Affiliate_System SHALL hiển thị thông tin ngân hàng để nhận hoa hồng
6. THE Affiliate_System SHALL load dữ liệu profile từ Demo_Data JSON file

### Requirement 7: Reports and Analytics

**User Story:** Là một đại lý, tôi muốn xem báo cáo chi tiết, để phân tích hiệu quả hoạt động.

#### Acceptance Criteria

1. WHEN đại lý truy cập báo cáo lượt click, THE Affiliate_System SHALL hiển thị số lượt click theo thời gian
2. WHEN đại lý truy cập báo cáo lượt click, THE Affiliate_System SHALL hiển thị biểu đồ xu hướng click
3. WHEN đại lý truy cập báo cáo đơn hàng, THE Affiliate_System SHALL hiển thị danh sách đơn hàng từ Affiliate_Link
4. WHEN đại lý truy cập báo cáo đơn hàng, THE Affiliate_System SHALL hiển thị tổng doanh số và hoa hồng theo đơn hàng
5. THE Affiliate_System SHALL load dữ liệu báo cáo từ Demo_Data JSON file

### Requirement 8: Layout and Navigation

**User Story:** Là một đại lý, tôi muốn điều hướng dễ dàng giữa các trang, để truy cập nhanh các chức năng.

#### Acceptance Criteria

1. THE Affiliate_System SHALL hiển thị sidebar navigation với tất cả các Module
2. THE Affiliate_System SHALL hiển thị breadcrumb cho mỗi trang
3. THE Affiliate_System SHALL hiển thị header với thông tin đại lý
4. THE Affiliate_System SHALL hiển thị footer với thông tin hệ thống
5. WHEN đại lý click vào menu item, THE Affiliate_System SHALL điều hướng đến trang tương ứng
6. THE Affiliate_System SHALL sử dụng Layout_Component từ _layout folder

### Requirement 9: Responsive Design

**User Story:** Là một đại lý, tôi muốn truy cập hệ thống trên nhiều thiết bị, để làm việc linh hoạt.

#### Acceptance Criteria

1. THE Affiliate_System SHALL hiển thị giao diện responsive trên desktop
2. THE Affiliate_System SHALL hiển thị giao diện responsive trên tablet
3. THE Affiliate_System SHALL hiển thị giao diện responsive trên mobile
4. THE Affiliate_System SHALL sử dụng CSS từ affiliate_responsive.css

### Requirement 10: Data Loading and Display

**User Story:** Là một đại lý, tôi muốn dữ liệu được load nhanh và chính xác, để làm việc hiệu quả.

#### Acceptance Criteria

1. WHEN trang được load, THE Affiliate_System SHALL đọc dữ liệu từ demo_data.json
2. WHEN dữ liệu JSON không hợp lệ, THE Affiliate_System SHALL hiển thị thông báo lỗi
3. WHEN dữ liệu JSON trống, THE Affiliate_System SHALL hiển thị thông báo không có dữ liệu
4. THE Affiliate_System SHALL parse JSON data thành PHP array
5. THE Affiliate_System SHALL hiển thị dữ liệu trong HTML table hoặc card layout

### Requirement 11: Chart and Visualization

**User Story:** Là một đại lý, tôi muốn xem dữ liệu dưới dạng biểu đồ, để dễ dàng phân tích xu hướng.

#### Acceptance Criteria

1. WHEN dashboard được load, THE Affiliate_System SHALL hiển thị biểu đồ doanh thu sử dụng Chart.js
2. WHEN dashboard được load, THE Affiliate_System SHALL hiển thị biểu đồ lượt click sử dụng Chart.js
3. WHEN dashboard được load, THE Affiliate_System SHALL hiển thị biểu đồ Conversion_Rate sử dụng Chart.js
4. THE Affiliate_System SHALL sử dụng cấu hình từ affiliate_chart_config.js
5. THE Affiliate_System SHALL load dữ liệu biểu đồ từ Demo_Data JSON file

### Requirement 12: AJAX Interactions

**User Story:** Là một đại lý, tôi muốn tương tác với hệ thống không cần reload trang, để trải nghiệm mượt mà.

#### Acceptance Criteria

1. WHEN đại lý thực hiện hành động (filter, sort, search), THE Affiliate_System SHALL sử dụng AJAX
2. WHEN AJAX request thành công, THE Affiliate_System SHALL cập nhật nội dung trang
3. WHEN AJAX request thất bại, THE Affiliate_System SHALL hiển thị thông báo lỗi
4. THE Affiliate_System SHALL sử dụng functions từ affiliate_ajax_actions.js

### Requirement 13: CSS and Styling

**User Story:** Là một đại lý, tôi muốn giao diện đẹp và nhất quán, để dễ sử dụng.

#### Acceptance Criteria

1. THE Affiliate_System SHALL sử dụng CSS từ affiliate_style.css cho styling chính
2. THE Affiliate_System SHALL sử dụng CSS từ affiliate_components.css cho các component
3. THE Affiliate_System SHALL sử dụng CSS từ affiliate_responsive.css cho responsive
4. THE Affiliate_System SHALL NOT có inline CSS trong file PHP
5. THE Affiliate_System SHALL NOT có inline JavaScript trong file PHP

### Requirement 14: Routing Integration

**User Story:** Là một đại lý, tôi muốn URL rõ ràng và dễ nhớ, để truy cập trực tiếp các trang.

#### Acceptance Criteria

1. THE Affiliate_System SHALL tích hợp với routing system trong index.php
2. WHEN đại lý truy cập /affiliate/dashboard, THE Affiliate_System SHALL hiển thị dashboard
3. WHEN đại lý truy cập /affiliate/commissions/history, THE Affiliate_System SHALL hiển thị lịch sử hoa hồng
4. WHEN đại lý truy cập /affiliate/customers/list, THE Affiliate_System SHALL hiển thị danh sách khách hàng
5. WHEN đại lý truy cập URL không tồn tại, THE Affiliate_System SHALL hiển thị trang 404

### Requirement 15: Module Structure Pattern

**User Story:** Là một developer, tôi muốn cấu trúc module nhất quán, để dễ bảo trì và mở rộng.

#### Acceptance Criteria

1. THE Affiliate_System SHALL có index.php cho mỗi module hiển thị danh sách
2. WHERE module cần hiển thị chi tiết, THE Affiliate_System SHALL có view.php
3. WHERE module cần thêm/sửa, THE Affiliate_System SHALL có change.php
4. WHERE module cần xóa, THE Affiliate_System SHALL có delete.php
5. THE Affiliate_System SHALL tuân theo pattern giống admin modules

### Requirement 13: Design System và Styling

**User Story:** Là một developer, tôi muốn affiliate system có giao diện nhất quán với admin system, để đảm bảo trải nghiệm người dùng đồng nhất.

#### Acceptance Criteria

1. THE Affiliate_System SHALL sử dụng HOÀN TOÀN design system của admin (màu sắc, typography, spacing, components)
2. THE Affiliate_System SHALL sử dụng color palette giống admin:
   - Primary: #356DF1 (Blue)
   - Secondary: #000000 (Black)
   - Success: #10B981, Warning: #F59E0B, Danger: #EF4444, Info: #3B82F6
   - Gray scale: #F9FAFB đến #111827
3. THE Affiliate_System SHALL sử dụng font family 'Inter' giống admin
4. THE Affiliate_System SHALL KHÔNG có inline CSS trong file PHP
5. THE Affiliate_System SHALL KHÔNG có inline JavaScript trong file PHP (trừ data initialization)
6. THE Affiliate_System SHALL sử dụng icon style giống admin (Font Awesome 5)
7. THE Affiliate_System SHALL sử dụng button styles giống admin:
   - Primary button: background #356DF1, hover #000000
   - Border radius: 8px
   - Padding: 8px 16px
   - Font size: 14px, font weight: 500
8. THE Affiliate_System SHALL sử dụng card styles giống admin:
   - Background: #ffffff
   - Border: 1px solid #E5E7EB
   - Border radius: 12px
   - Padding: 24px
   - Hover effect: translateY(-2px) + shadow
9. THE Affiliate_System SHALL sử dụng stat card styles giống admin:
   - Icon container: 60x60px, gradient background
   - Number font size: 32px, font weight: 700
   - Trend indicator với màu success/danger
10. THE Affiliate_System SHALL sử dụng table styles giống admin:
    - Header background: #F9FAFB
    - Border color: #E5E7EB
    - Row hover: #F8FAFC
11. THE Affiliate_System SHALL sử dụng badge styles giống admin:
    - Success: background #d1fae5, color #065f46
    - Warning: background #fef3c7, color #92400e
    - Border radius: 20px (pill shape)
12. THE Affiliate_System SHALL sử dụng form input styles giống admin:
    - Border: 1px solid #D1D5DB
    - Border radius: 8px
    - Focus: border #356DF1 + shadow rgba(53, 109, 241, 0.1)
13. THE Affiliate_System SHALL sử dụng sidebar styles giống admin:
    - Width: 250px (expanded), 70px (collapsed)
    - Background: #ffffff
    - Active item: background #356DF1, color #ffffff
    - Active indicator: 4px left border #000000
14. THE Affiliate_System SHALL sử dụng header styles giống admin:
    - Height: 70px
    - Background: #ffffff
    - Border bottom: 1px solid #E5E7EB
15. THE Affiliate_System SHALL sử dụng Chart.js styles giống admin:
    - Font family: 'Inter'
    - Colors: primary #356DF1, success #10B981, etc.
    - Tooltip: white background, border #E5E7EB
16. THE Affiliate_System SHALL có responsive breakpoints giống admin:
    - Mobile: < 768px
    - Tablet: 768px - 1024px
    - Desktop: > 1024px
17. THE Affiliate_System SHALL có transition effects giống admin:
    - Duration: 0.3s
    - Easing: ease
18. THE Affiliate_System SHALL có hover effects giống admin:
    - Buttons: translateY(-1px)
    - Cards: translateY(-2px) + shadow
    - Links: color change to #000000
