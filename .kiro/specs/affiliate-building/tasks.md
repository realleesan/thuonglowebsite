# Xây dựng Trang Affiliate (Đại Lý) cho ThuongLo Website

## Tổng quan

Xây dựng hệ thống frontend affiliate (đại lý) bằng PHP thuần, bao gồm dashboard, quản lý hoa hồng, khách hàng, tài chính, marketing tools, profile và reports. Hệ thống sử dụng dữ liệu demo từ JSON, Chart.js cho biểu đồ, và AJAX cho tương tác động.

**Lưu ý quan trọng:**
- Không được inline CSS/JS trong file PHP
- Sử dụng dữ liệu từ demo_data.json (không cần database)
- Layout bao gồm: sidebar, breadcrumb, header, footer (dùng chung)
- Tất cả file được tổng hợp ở affiliate master layout
- Routing được khai báo trong index.php

---

## Phase 1: Thiết lập Nền tảng & Dữ liệu Demo

* [x] Tạo cấu trúc thư mục cho affiliate module
* [x] Tạo file demo_data.json với dữ liệu mẫu đầy đủ
  - Thông tin đại lý (affiliate info)
  - Dashboard statistics (revenue, clicks, commission, conversion)
  - Commission history data
  - Commission policy tiers
  - Customers list và detail
  - Finance data (balance, transactions, withdrawals)
  - Marketing tools (affiliate link, banners, campaigns)
  - Profile information
  - Reports data (clicks, orders)
* [x] Tạo DataLoader class để load và parse JSON data
* [x] Setup error handling utilities

---

## Phase 2: Layout Components (Sidebar, Header, Footer, Breadcrumb)

* [x] Xây dựng affiliate_master.php (layout chính)
  - Include sidebar, header, footer, breadcrumb
  - Load CSS/JS assets theo module
  - Setup routing logic
* [x] Xây dựng affiliate_sidebar.php
  - Menu items cho tất cả modules
  - Highlight active menu item
  - Icon cho mỗi menu
* [x] Xây dựng affiliate_header.php
  - Hiển thị thông tin đại lý (tên, avatar)
  - Notifications dropdown
  - Logout button
* [x] Xây dựng affiliate_footer.php
  - Copyright information
  - Quick links
* [x] Xây dựng affiliate_breadcrumb.php
  - Render breadcrumb path từ route
  - Link về các trang cha
* [x] Tạo CSS cho layout
  - affiliate_style.css (layout chính, sidebar, header, footer)
  - affiliate_components.css (buttons, badges, alerts, modals)
* [x] Tạo JS cho layout
  - affiliate_main.js (navigation, event handlers)

---

## Phase 3: Dashboard với Chart.js ✅ HOÀN THÀNH

* [x] Xây dựng dashboard.php
  - Load dashboard data từ JSON
  - Render stat cards (revenue, clicks, commission, conversion rate)
  - Render recent customers table
  - Render commission status
  - Render affiliate info với copy buttons
* [x] Tích hợp Chart.js
  - Setup Chart.js library
  - Tạo affiliate_chart_config.js
* [x] Tạo biểu đồ doanh thu theo thời gian
  - Revenue chart (line chart)
  - Smooth curve với gradient fill
* [x] Tạo biểu đồ số lượt click
  - Clicks chart (bar chart)
  - Rounded corners
* [x] Tạo biểu đồ tỉ lệ chuyển đổi
  - Conversion rate chart (doughnut chart)
  - 3 segments: Hoàn thành/Đang xử lý/Đã hủy
* [x] Tạo CSS cho dashboard
  - Stat cards styling
  - Charts container styling
  - Recent activities styling
  - Commission status styling
  - Responsive design

---

## Phase 4: Module Quản Lý Hoa Hồng (Commissions) ✅ HOÀN THÀNH

* [x] commissions/index.php - Tổng quan hoa hồng
  - 3 stat cards (Tổng/Pending/Paid)
  - Commission breakdown (Subscription vs Logistics)
  - Quick actions
  - Recent commissions table
* [x] commissions/history.php - Lịch sử hoa hồng
  - Table với 8 cột
  - Filters (Tháng/Năm/Trạng thái/Loại)
  - Badge phân biệt Data vs Logistics
  - Empty state
  - Pagination UI
* [x] commissions/policy.php - Chính sách hoa hồng
  - Lifetime Commission info
  - Commission types cards
  - Tiers table (4 cấp độ)
  - How it works (4 bước)
  - FAQs
* [x] Cập nhật demo_data.json
  - Thêm product_type (data_subscription/logistics_service)
  - Thêm description chi tiết
  - Thêm from_subscription/from_logistics
  - Thêm lifetime_info và commission_types
* [x] Cập nhật CSS
  - Badge colors (purple/orange)
  - Filters styling
  - Commission components
  - Tiers table
  - How it works
  - FAQs
  - Responsive design
* [x] Cập nhật routing trong index.php
  - Load commission overview từ JSON
  - Display total earned, pending, paid
  - Quick stats cards
* [ ] commissions/history.php - Lịch sử hoa hồng
  - Load commission history từ JSON
  - Render table với date, order_id, amount, status
  - Pagination
  - Filter by status, date range
* [ ] commissions/policy.php - Chính sách hoa hồng
  - Load commission policy từ JSON
  - Render policy tiers table
  - Display commission rates by tier
  - Display requirements for each tier

---

## Phase 5: Module Quản Lý Khách Hàng (Customers)

* [ ] customers/index.php - Redirect to list.php
* [ ] customers/list.php - Danh sách khách hàng
  - Load customers data từ JSON
  - Render table với name, email, registered_date, total_orders, total_spent
  - Search functionality
  - Filter by status, date
  - Sort by columns
* [ ] customers/detail.php - Chi tiết khách hàng
  - Load customer detail từ JSON by ID
  - Display customer information
  - Display orders history table
  - Display total commission earned from customer

---

## Phase 6: Module Quản Lý Tài Chính (Finance)

* [ ] finance/index.php - Tổng quan tài chính
  - Load finance overview từ JSON
  - Display balance cards
  - Recent transactions
* [ ] finance/balance.php - Số dư và giao dịch
  - Load balance data từ JSON
  - Render balance cards (available, pending, total earned, total withdrawn)
  - Render transactions history table
  - Filter by type, date
* [ ] finance/withdraw.php - Rút tiền
  - Load withdrawal data từ JSON
  - Render withdrawal form (amount, bank, account number)
  - Display minimum withdrawal amount
  - Render withdrawal history table
  - Display withdrawal status

---

## Phase 7: Module Marketing Tools

* [ ] marketing/index.php - Tổng quan marketing
  - Load marketing overview từ JSON
  - Display quick stats
* [ ] marketing/tools.php - Công cụ marketing
  - Load affiliate link từ JSON
  - Display affiliate link với copy button
  - Verify link contains affiliate_id
  - Display banners gallery với download links
  - QR code generator cho affiliate link
  - Social media share buttons
* [ ] marketing/campaigns.php - Chiến dịch marketing
  - Load campaigns data từ JSON
  - Render campaigns table (name, start_date, end_date, clicks, conversions, revenue)
  - Display campaign performance metrics

---

## Phase 8: Module Profile (Hồ Sơ)

* [ ] profile/index.php - Redirect to settings.php
* [ ] profile/settings.php - Cài đặt hồ sơ
  - Load profile data từ JSON
  - Display profile information (name, email, phone, address)
  - Profile update form
  - Display bank information
  - Bank information update form
  - Change password form

---

## Phase 9: Module Báo Cáo (Reports)

* [ ] reports/index.php - Tổng quan báo cáo
  - Load reports overview từ JSON
  - Display quick stats
  - Links to detailed reports
* [ ] reports/clicks.php - Báo cáo lượt click
  - Load clicks report data từ JSON
  - Display clicks statistics
  - Render clicks chart by date
  - Render clicks breakdown by source (direct, social, email, etc.)
  - Export functionality
* [ ] reports/orders.php - Báo cáo đơn hàng
  - Load orders report data từ JSON
  - Display orders statistics table
  - Render orders breakdown by date và product
  - Display total revenue và commission
  - Export functionality

---

## Phase 10: AJAX Functionality

* [ ] Tạo affiliate_ajax_actions.js
  - Implement AjaxHandler class
  - filterData method (filter tables)
  - sortData method (sort tables)
  - searchData method (search in tables)
  - loadMore method (pagination)
* [ ] Integrate AJAX với các tables
  - Customer list table
  - Commission history table
  - Transaction history table
  - Reports tables

---

## Phase 11: Responsive Design

* [ ] Tạo affiliate_responsive.css
  - Define breakpoints
  - Mobile styles (< 768px)
    - Collapse sidebar to hamburger menu
    - Stack stat cards vertically
    - Responsive tables (scroll or card view)
  - Tablet styles (768px - 1024px)
    - 2-column layout for stat cards
    - Sidebar visible
  - Desktop styles (> 1024px)
    - Full layout với sidebar
    - Multi-column layouts
* [ ] Test responsive trên các devices
  - Mobile phones
  - Tablets
  - Desktop screens

---

## Phase 12: Helper Functions & Utilities

* [ ] Tạo display helper functions
  - formatCurrency() - Format VND currency
  - formatDate() - Format dates
  - formatPercentage() - Format percentages
  - formatNumber() - Format numbers với thousand separators
* [ ] Tạo validation functions
  - validateJSON() - Validate JSON syntax
  - validateAmount() - Validate amount fields
  - validateRequired() - Validate required fields
* [ ] Tạo rendering components
  - TableRenderer - Render tables với pagination
  - CardRenderer - Render stat cards
  - FormRenderer - Render forms
  - ChartRenderer - Render charts

---

## Phase 13: Routing & Integration

* [ ] Setup routing trong index.php
  - Parse URL parameters (page, module, action, id)
  - Route to appropriate PHP file
  - Handle 404 errors
* [ ] Tạo 404 error page
  - Design 404 layout
  - Link back to dashboard
* [ ] Wire all modules together
  - Test navigation between modules
  - Ensure layout components load on all pages
  - Ensure CSS/JS assets load correctly
* [ ] Test complete user flows
  - Dashboard → Commissions → History
  - Dashboard → Customers → Detail
  - Finance → Withdraw
  - Marketing → Tools
  - Profile → Settings
  - Reports → Clicks/Orders

---

## Phase 14: Testing & Validation

* [ ] Validate JSON data structure
  - Test với valid JSON
  - Test với invalid JSON
  - Test với empty JSON
  - Test với missing fields
* [ ] Test error handling
  - File not found errors
  - Invalid data errors
  - Empty data arrays
* [ ] Test chart rendering
  - Valid chart data
  - Invalid chart data
  - Empty chart data
* [ ] Test responsive design
  - Mobile devices
  - Tablets
  - Desktop screens
* [ ] Verify no inline CSS/JS
  - Scan all PHP files
  - Ensure separation of concerns

---

## Phase 15: Hoàn Thiện & Tối Ưu

* [ ] Kiểm tra tính nhất quán
  - Màu sắc
  - Font chữ
  - Icon style
  - Button styles
  - Spacing và alignment
* [ ] Tối ưu hóa hiệu suất
  - Minify CSS/JS (optional)
  - Optimize images
  - Lazy load charts
* [ ] Documentation
  - Code comments
  - README cho affiliate module
  - API documentation cho JSON structure
* [ ] Final testing
  - Test tất cả chức năng
  - Test trên nhiều browsers
  - Test responsive design
  - Verify requirements đã đáp ứng

---

## Ghi chú

- Mỗi Phase nên được hoàn thành trước khi chuyển sang Phase tiếp theo
- Test thường xuyên trong quá trình phát triển
- Tham khảo admin module để đảm bảo tính nhất quán về style và structure
- Sử dụng fake data từ JSON, không cần database trong giai đoạn này
- Focus vào separation of concerns: PHP cho logic, CSS cho styling, JS cho interactivity
