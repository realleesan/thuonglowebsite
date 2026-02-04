# Kế hoạch Implementation: Hệ thống Admin Panel

## Tổng quan

Kế hoạch implementation này chia việc xây dựng hệ thống admin panel thành các bước incremental, từ việc thiết lập cấu trúc cơ bản, xây dựng sidebar navigation, đến từng module quản lý cụ thể. Mỗi bước đều có validation và testing để đảm bảo chất lượng.

## Tasks

- [x] 1. Thiết lập cấu trúc cơ bản và dữ liệu fake
  - Tạo cấu trúc thư mục admin theo thiết kế
  - Tạo file fake_data.json với dữ liệu mẫu đầy đủ
  - Thiết lập CSS và JS files cơ bản
  - _Requirements: 2.1, 2.3, 10.1_

- [ ]* 1.1 Viết property test cho cấu trúc thư mục
  - **Property 4: Module Structure Consistency**
  - **Validates: Requirements 2.2**

- [x] 2. Xây dựng Admin Sidebar Navigation
  - [x] 2.1 Tạo admin_sidebar.php với HTML structure
    - Implement sidebar HTML với navigation menu
    - Tích hợp responsive toggle button
    - _Requirements: 1.1, 1.4_

  - [x] 2.2 Implement admin_sidebar.css
    - Styling cho desktop layout (fixed sidebar)
    - Responsive breakpoints cho tablet và mobile
    - Active state styling cho menu items
    - _Requirements: 1.3, 8.1, 8.2, 8.3_

  - [x] 2.3 Implement admin_sidebar.js
    - Toggle functionality cho mobile
    - Active menu highlighting logic
    - Touch-friendly interactions
    - _Requirements: 1.2, 1.5, 8.5_

  - [ ]* 2.4 Viết property test cho sidebar navigation
    - **Property 1: Sidebar Menu Completeness**
    - **Property 2: Navigation State Consistency**
    - **Validates: Requirements 1.1, 1.2**

  - [ ]* 2.5 Viết property test cho responsive design
    - **Property 9: Responsive Layout Adaptation**
    - **Validates: Requirements 8.1, 8.2, 8.3, 8.4**

- [ ] 3. Tích hợp Admin Layout với Master Template
  - [ ] 3.1 Modify master.php để detect admin pages
    - Thêm logic detect admin pages
    - Conditional loading admin CSS/JS
    - Admin layout wrapper implementation
    - _Requirements: 9.2, 1.3_

  - [ ] 3.2 Implement admin authentication middleware
    - Session checking cho admin pages
    - Redirect logic cho unauthorized access
    - Admin role validation
    - _Requirements: 7.1, 7.2, 7.4_

  - [ ]* 3.3 Viết property test cho authentication
    - **Property 8: Authentication Gate**
    - **Validates: Requirements 7.1, 7.2**

  - [ ]* 3.4 Viết property test cho system integration
    - **Property 10: System Integration Consistency**
    - **Validates: Requirements 9.2, 9.3, 9.4, 9.5**

- [ ] 4. Checkpoint - Kiểm tra cấu trúc cơ bản
  - Đảm bảo tất cả tests pass, hỏi user nếu có vấn đề phát sinh.

- [x] 5. Implement Module Quản lý Sản phẩm
  - [x] 5.1 Tạo products/index.php
    - Load và hiển thị danh sách sản phẩm từ fake_data.json
    - Implement pagination và search functionality
    - Action buttons (edit, delete) cho mỗi sản phẩm
    - _Requirements: 3.1, 3.2_

  - [x] 5.2 Tạo products/change.php
    - Form thêm/sửa sản phẩm với validation
    - Load existing data cho edit mode
    - Save changes vào fake_data.json
    - _Requirements: 3.3, 3.5_

  - [x] 5.3 Tạo products/delete.php
    - Confirmation dialog implementation
    - Delete logic với JSON update
    - Error handling và success messages
    - _Requirements: 3.4, 3.5_

  - [ ]* 5.4 Viết property test cho products module
    - **Property 5: Data Loading Round Trip**
    - **Property 6: Data Display Completeness**
    - **Validates: Requirements 3.1, 3.2, 3.5**

  - [ ]* 5.5 Viết property test cho deletion flow
    - **Property 12: Deletion Confirmation Flow**
    - **Validates: Requirements 3.4**

- [x] 6. Implement Module Quản lý Danh mục
  - [x] 6.1 Tạo categories/index.php
    - Hiển thị danh sách danh mục với hierarchy
    - Show product count cho mỗi danh mục
    - Category management actions
    - _Requirements: 4.1, 4.2_

  - [x] 6.2 Tạo categories/change.php
    - Form quản lý danh mục với parent selection
    - Validation cho tên danh mục required
    - Update relationships trong JSON
    - _Requirements: 4.3, 4.5_

  - [x] 6.3 Tạo categories/delete.php
    - Dependency check (products using category)
    - Safe deletion với relationship cleanup
    - _Requirements: 4.4, 4.5_

  - [ ]* 6.4 Viết property test cho categories module
    - **Property 7: Form Validation Consistency**
    - **Validates: Requirements 4.3**

- [x] 7. Implement Module Quản lý Tin tức
  - [x] 7.1 Tạo news/index.php
    - Danh sách bài viết với status filter
    - Display title, summary, date, status
    - News management actions
    - _Requirements: 5.1, 5.2_

  - [x] 7.2 Tạo news/change.php
    - Rich form với title, content, image, status
    - Validation cho required fields
    - Content editor integration (basic textarea)
    - _Requirements: 5.3, 5.4_

  - [x] 7.3 Tạo news/delete.php
    - Confirmation dialog cho news deletion
    - Soft delete option implementation
    - _Requirements: 5.5_

  - [ ]* 7.4 Viết unit tests cho news module
    - Test specific news operations
    - Test content validation
    - _Requirements: 5.3, 5.4_

- [x] 8. Implement Module Quản lý Sự kiện
  - [x] 8.1 Tạo events/index.php
    - Danh sách sự kiện với date sorting
    - Display event details (name, dates, location)
    - Event status indicators
    - _Requirements: 6.1, 6.2_

  - [x] 8.2 Tạo events/change.php
    - Event form với date pickers
    - Date validation (start < end)
    - Location và participant management
    - _Requirements: 6.3, 6.4_

  - [x] 8.3 Tạo events/delete.php
    - Event cancellation handling
    - Update event status thay vì hard delete
    - _Requirements: 6.5_

  - [ ]* 8.4 Viết property test cho events module
    - **Property 7: Form Validation Consistency** (date logic)
    - **Validates: Requirements 6.3**

- [ ] 9. Implement JSON Data Management Layer
  - [ ] 9.1 Tạo data helper functions
    - JSON read/write với error handling
    - Backup mechanism cho data safety
    - Default data initialization
    - _Requirements: 10.3, 10.4, 10.5_

  - [ ] 9.2 Integrate data layer với tất cả modules
    - Replace direct JSON operations với helper functions
    - Consistent error handling across modules
    - Data validation và sanitization
    - _Requirements: 10.1, 10.2_

  - [ ]* 9.3 Viết property test cho JSON data integrity
    - **Property 11: JSON Data Integrity**
    - **Validates: Requirements 10.3, 10.4, 10.5**

- [x] 10. Implement Dashboard Module
  - [x] 10.1 Tạo dashboard/index.php
    - Statistics overview (product count, category count, etc.)
    - Recent activities display
    - Quick action buttons
    - Charts và graphs (basic implementation)
    - _Requirements: Dashboard functionality_

  - [x] 10.2 Dashboard styling và interactivity
    - Responsive dashboard layout
    - Interactive elements
    - Data visualization styling
    - _Requirements: 8.1, 8.2, 8.3_

  - [ ]* 10.3 Viết unit tests cho dashboard
    - Test statistics calculation
    - Test data aggregation
    - _Requirements: Dashboard functionality_

- [ ] 11. Final Integration và Polish
  - [ ] 11.1 Cross-module integration testing
    - Test navigation giữa các modules
    - Data consistency across modules
    - User experience flow testing
    - _Requirements: All integration requirements_

  - [ ] 11.2 Performance optimization
    - CSS/JS minification và optimization
    - JSON loading optimization
    - Mobile performance tuning
    - _Requirements: 8.4, 8.5_

  - [ ] 11.3 Security hardening
    - Input sanitization across all forms
    - CSRF protection implementation
    - Session security enhancements
    - _Requirements: 7.1, 7.2, 7.3, 7.4, 7.5_

  - [ ]* 11.4 Viết integration tests
    - End-to-end workflow testing
    - Cross-module data integrity
    - Security testing
    - _Requirements: All security và integration requirements_

- [ ] 12. Final Checkpoint - Đảm bảo tất cả tests pass
  - Chạy full test suite
  - Verify tất cả requirements được đáp ứng
  - User acceptance testing preparation
  - Đảm bảo tất cả tests pass, hỏi user nếu có vấn đề phát sinh.

## Ghi chú

- Tasks được đánh dấu `*` là optional và có thể skip để tạo MVP nhanh hơn
- Mỗi task reference đến specific requirements để đảm bảo traceability
- Checkpoints đảm bảo validation incremental
- Property tests validate universal correctness properties
- Unit tests validate specific examples và edge cases
- Tất cả modules sử dụng fake_data.json cho demo phase
- Dashboard được implement cuối cùng như yêu cầu