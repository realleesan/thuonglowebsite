# Implementation Plan: Admin Panel Hardcode Data Integration

## Overview

Chuyển đổi toàn bộ trang quản trị admin từ dữ liệu hardcode sang dữ liệu động từ database. Bao gồm admin dashboard, admin header, admin sidebar, admin revenue page và tất cả JavaScript configuration của admin panel. Tận dụng cơ sở hạ tầng hiện có (AdminService, Models, ErrorHandler) và mở rộng để hỗ trợ complete admin data integration.

## Tasks

- [ ] 1. Create Cache Service Infrastructure
  - Tạo CacheService class với methods get, set, delete, flush
  - Implement file-based caching với TTL support
  - Tạo cache key generation logic cho dashboard data
  - _Requirements: 8.1, 8.2, 8.3, 8.6_

- [ ]* 1.1 Write property test for Cache Service
  - **Property 6: Cache Behavior Consistency**
  - **Validates: Requirements 1.5, 2.5, 4.5, 8.1, 8.2, 8.3, 8.5, 8.6**

- [ ] 2. Enhance AdminService with Dashboard Methods
  - [ ] 2.1 Implement getDashboardRevenueData method
    - Query orders table với date filtering và status = 'completed'
    - Group data theo period (daily, weekly, monthly)
    - Return formatted data cho Chart.js
    - _Requirements: 1.1, 1.2, 1.4_

  - [ ]* 2.2 Write property test for revenue data
    - **Property 1: Dashboard Data Integration (Revenue)**
    - **Property 2: Period Filter Consistency**
    - **Validates: Requirements 1.1, 1.2, 10.1**

  - [ ] 2.3 Implement getDashboardTopProducts method
    - Query orders + products với JOIN để tính sales count
    - Sort theo sales volume và limit results
    - Handle deleted products exclusion
    - _Requirements: 2.1, 2.2, 2.4_

  - [ ]* 2.4 Write property test for top products
    - **Property 4: Top Products Ranking Accuracy**
    - **Validates: Requirements 2.2, 2.4**

  - [ ] 2.5 Implement getDashboardOrdersStatus method
    - Query orders table group by status
    - Calculate counts và percentages cho mỗi status
    - Include all required statuses (completed, processing, pending, cancelled)
    - _Requirements: 3.1, 3.2, 3.4_

  - [ ]* 2.6 Write property test for orders status
    - **Property 5: Order Status Distribution Completeness**
    - **Validates: Requirements 3.2, 3.4**

  - [ ] 2.7 Implement getDashboardNewUsers method
    - Query users table với created_at filtering
    - Group by weeks/months theo period parameter
    - Return time series data cho line chart
    - _Requirements: 4.1, 4.2, 4.4_

  - [ ] 2.8 Implement getDashboardStatistics method
    - Calculate tổng số products (status = 'active')
    - Calculate tổng revenue từ completed orders
    - Count published news và upcoming events
    - Calculate trends comparison với previous period
    - _Requirements: 5.1, 5.2, 5.3, 5.4_

- [ ]* 2.9 Write property test for dashboard statistics
  - **Property 1: Dashboard Data Integration (Statistics)**
  - **Validates: Requirements 5.1, 5.2, 5.3, 5.4**

- [ ] 3. Create AdminDashboardController
  - [ ] 3.1 Create base controller structure
    - Extend BaseController với authentication middleware
    - Inject AdminService và CacheService dependencies
    - Setup error handling với ErrorHandler
    - _Requirements: 7.7_

  - [ ] 3.2 Implement getRevenueData endpoint
    - Handle period parameter validation
    - Check cache trước khi query database
    - Return JSON response theo Chart.js format
    - _Requirements: 1.4, 7.1_

  - [ ] 3.3 Implement getTopProductsData endpoint
    - Handle limit parameter với default = 5
    - Implement caching với 10-minute TTL
    - Return product data với sales metrics
    - _Requirements: 2.3, 7.2_

  - [ ] 3.4 Implement getOrdersStatusData endpoint
    - Return order status distribution data
    - Include both counts và percentages
    - Cache results với appropriate TTL
    - _Requirements: 3.4, 7.3_

  - [ ] 3.5 Implement getNewUsersData endpoint
    - Handle period parameter (4weeks default)
    - Return time series data cho user growth
    - Implement caching với 15-minute TTL
    - _Requirements: 4.4, 7.4_

  - [ ] 3.6 Implement getDashboardStats endpoint
    - Return all dashboard statistics in single call
    - Include trends data và comparisons
    - Cache aggregated stats data
    - _Requirements: 7.5_

  - [ ] 3.7 Implement getAllDashboardData endpoint
    - Aggregate all dashboard data in single response
    - Optimize với parallel data fetching
    - Handle partial failures gracefully
    - _Requirements: 6.1_

- [ ]* 3.8 Write property tests for API endpoints
  - **Property 3: API Response Format Consistency**
  - **Property 8: API Authentication and Authorization**
  - **Property 9: API Error Handling**
  - **Validates: Requirements 1.4, 7.1, 7.2, 7.3, 7.4, 7.5, 7.6, 7.7**

- [ ] 4. Checkpoint - Ensure backend API tests pass
  - Ensure all tests pass, ask the user if questions arise.

- [ ] 5. Update Admin Dashboard View
  - [ ] 5.1 Remove hardcoded data from dashboard.php
    - Remove static arrays cho stats, trends, alerts
    - Remove hardcoded quick actions (5 buttons)
    - Keep view structure nhưng use dynamic data placeholders
    - Add loading states cho charts
    - _Requirements: 1.1, 2.1, 3.1, 4.1_

  - [ ] 5.2 Add AJAX loading indicators
    - Add skeleton loading cho charts
    - Show loading spinners during data fetch
    - Handle loading states cho different chart types
    - _Requirements: 6.5_

  - [ ] 5.3 Implement error state handling
    - Add error message displays cho failed API calls
    - Show fallback data khi appropriate
    - Provide retry mechanisms cho failed requests
    - _Requirements: 9.1, 9.2_

- [ ] 6. Update JavaScript Files
  - [ ] 6.1 Refactor admin_dashboard.js
    - Remove all hardcoded data arrays
    - Implement DashboardDataLoader class
    - Add AJAX calls cho all chart data
    - Implement error handling và retry logic
    - _Requirements: 11.1, 11.4, 6.4, 9.4_

  - [ ]* 6.2 Write property test for JavaScript data loading
    - **Property 13: JavaScript Hardcode Elimination**
    - **Property 10: AJAX Timeout and Retry Behavior**
    - **Validates: Requirements 11.1, 11.4, 6.4, 9.4**

  - [ ] 6.3 Update admin_events.js
    - Remove fallback hardcoded data
    - Implement API calls cho events data
    - Add error handling cho API failures
    - _Requirements: 11.2, 11.5_

  - [ ] 6.4 Update admin_news.js
    - Remove fallback hardcoded data
    - Implement API calls cho news data
    - Add error handling cho API failures
    - _Requirements: 11.3, 11.5_

  - [ ]* 6.5 Write property test for frontend error handling
    - **Property 14: Frontend Error Handling**
    - **Validates: Requirements 6.3, 9.2, 11.5**

- [ ] 7. Implement Chart Data Adapters
  - [ ] 7.1 Create ChartDataAdapter class
    - Implement adaptRevenueData method cho Chart.js format
    - Implement adaptTopProductsData method
    - Implement adaptOrdersStatusData method
    - Implement adaptNewUsersData method
    - _Requirements: 1.4, 2.3, 3.4, 4.4_

  - [ ] 7.2 Update chart initialization code
    - Use adapter methods để format API data
    - Handle empty data states gracefully
    - Implement dynamic chart updates
    - _Requirements: 1.3, 3.5_

- [ ] 8. Update Revenue Page Integration
  - [ ] 8.1 Remove hardcoded data from revenue/index.php
    - Use AdminService::getRevenueData() method
    - Remove static chart data arrays
    - Implement dynamic filtering
    - _Requirements: 10.1, 10.2_

  - [ ] 8.2 Update revenue page JavaScript
    - Remove hardcoded chart data
    - Implement AJAX calls cho revenue charts
    - Add period filter change handlers
    - _Requirements: 10.3, 10.4_

- [ ]* 8.3 Write property test for revenue page integration
  - **Property 1: Dashboard Data Integration (Revenue Page)**
  - **Validates: Requirements 10.1, 10.2, 10.3, 10.4, 10.5**

- [ ] 9. Implement Cache Invalidation
  - [ ] 9.1 Add cache invalidation triggers
    - Invalidate revenue cache khi orders updated
    - Invalidate products cache khi products/orders updated
    - Invalidate users cache khi users created
    - Invalidate stats cache khi any related data updated
    - _Requirements: 3.3, 4.3, 5.5, 8.4_

  - [ ]* 9.2 Write property test for cache invalidation
    - **Property 7: Cache Invalidation on Data Changes**
    - **Validates: Requirements 3.3, 4.3, 5.5, 8.4**

- [ ] 10. Add Error Logging and Monitoring
  - [ ] 10.1 Enhance error logging
    - Log all API errors với context information
    - Log cache errors và fallback usage
    - Log performance metrics cho slow queries
    - _Requirements: 9.3_

  - [ ]* 10.2 Write property test for error logging
    - **Property 11: Error Logging Completeness**
    - **Validates: Requirements 9.3**

- [ ] 11. Performance Optimization
  - [ ] 11.1 Add database indexes
    - Index orders.created_at, orders.status
    - Index products.status, products.created_at
    - Index users.created_at
    - Index events.start_date, news.status
    - _Requirements: 12.2_

  - [ ] 11.2 Implement response compression
    - Add gzip compression cho API responses
    - Configure AJAX requests để accept compression
    - _Requirements: 12.3_

  - [ ] 11.3 Add data pagination
    - Implement pagination cho large datasets
    - Add limit parameters cho API endpoints
    - Handle pagination in frontend charts
    - _Requirements: 12.4_

  - [ ]* 11.4 Write property test for performance requirements
    - **Property 12: Performance Response Time**
    - **Validates: Requirements 12.1, 12.4**

- [ ] 12. Final Integration Testing
  - [ ] 12.1 Test complete dashboard workflow
    - Test initial dashboard load với all charts
    - Test period filter changes
    - Test error scenarios và fallback behavior
    - _Requirements: 6.1, 6.2, 6.3_

  - [ ]* 12.2 Write integration tests for empty states
    - **Example 1: Empty State Handling**
    - **Example 2: No Orders Empty State**
    - **Validates: Requirements 1.3, 3.5**

  - [ ]* 12.3 Write integration tests for AJAX behavior
    - **Example 3: Initial Dashboard Load**
    - **Example 4: Period Filter Change**
    - **Example 5: Loading State Display**
    - **Validates: Requirements 6.1, 6.2, 6.5**

- [ ] 13. Admin Header Data Integration
  - [ ] 13.1 Create admin notifications system
    - Create admin_notifications table với fields: id, type, title, message, icon, read_status, created_at
    - Update AdminService với getAdminNotifications method
    - Create API endpoint để lấy notifications data
    - _Requirements: 13.1, 13.2, 13.4_

  - [ ] 13.2 Update admin_header.php to use dynamic data
    - Remove hardcoded 3 notifications (Đơn hàng mới #1001, Sản phẩm sắp hết hàng, Liên hệ mới từ khách hàng)
    - Remove hardcoded user info "Admin ThuongLo", "admin@thuonglo.com"
    - Implement AJAX loading cho notifications
    - Add cache cho notifications data (5 minutes TTL)
    - _Requirements: 13.3, 13.5_

  - [ ]* 13.3 Write property test for admin notifications
    - **Property: Admin Notifications Consistency**
    - **Validates: Requirements 13.1, 13.2, 13.3, 13.4, 13.5**

- [ ] 14. Admin Sidebar Data Integration
  - [ ] 14.1 Create admin menu management system
    - Create admin_menus table cho 11 menu items với fields: id, name, icon, url, order, role_required, status
    - Seed data cho existing menu items (Dashboard, Sản phẩm, Danh mục, Tin tức, Sự kiện, Đơn hàng, Người dùng, Đại lý, Liên hệ, Doanh thu, Cài đặt)
    - Update AdminService với getAdminMenus method
    - _Requirements: 14.1, 14.2, 14.3_

  - [ ] 14.2 Update admin_sidebar.php to use dynamic data
    - Remove hardcoded 11 menu items
    - Implement role-based menu visibility
    - Add configurable menu order và icons
    - Implement dynamic active state calculation
    - _Requirements: 14.4, 14.5_

  - [ ]* 14.3 Write property test for admin menu system
    - **Property: Admin Menu Configuration Consistency**
    - **Validates: Requirements 14.1, 14.2, 14.3, 14.4, 14.5**

- [ ] 15. Admin JavaScript Configuration Integration
  - [ ] 15.1 Update admin_dashboard.js
    - Remove all hardcoded chart data arrays
    - Implement DashboardDataLoader class cho admin
    - Add AJAX calls cho all admin chart data
    - Implement error handling và retry logic
    - _Requirements: 15.1, 6.4, 9.4_

  - [ ] 15.2 Update admin_pages.js
    - Remove hardcoded bulk actions (activate, deactivate, delete)
    - Create API endpoint cho bulk actions configuration
    - Implement dynamic bulk actions loading
    - Add admin-specific notification handling
    - _Requirements: 15.2_

  - [ ] 15.3 Update admin_affiliates.js
    - Remove hardcoded notification settings
    - Remove hardcoded commission calculator configuration
    - Implement API calls cho affiliate management data
    - Add error handling cho admin operations
    - _Requirements: 15.3_

  - [ ] 15.4 Update admin_events.js và admin_news.js
    - Remove fallback hardcoded data
    - Implement API calls cho admin events/news data
    - Add error handling cho admin API failures
    - Use consistent admin error handling patterns
    - _Requirements: 15.4_

  - [ ] 15.5 Create admin configuration API
    - Create admin_config table cho admin-specific settings
    - Create API endpoint /api/admin/config để serve admin configuration
    - Move admin color palettes sang CSS variables
    - Implement admin-specific chart configurations
    - _Requirements: 15.5_

  - [ ]* 15.6 Write property test for admin JS configuration
    - **Property: Admin JavaScript Configuration Consistency**
    - **Validates: Requirements 15.1, 15.2, 15.3, 15.4, 15.5**

- [ ] 16. Final Admin Integration Testing
  - [ ] 16.1 Test complete admin panel workflow
    - Test admin dashboard với all charts và dynamic data
    - Test admin header với dynamic notifications và user info
    - Test admin sidebar với dynamic menu system
    - Test admin revenue page với dynamic data
    - Test all admin JavaScript files với API integration
    - _Requirements: 6.1, 6.2, 6.3, 13.5, 14.5, 15.1-15.5_

  - [ ]* 16.2 Write integration tests for admin panel
    - **Integration Test: Complete Admin Panel Hardcode Elimination**
    - **Validates: All admin requirements 1-15**

- [ ] 17. Final checkpoint - Ensure all admin tests pass and no hardcode remains
  - Ensure all tests pass, ask the user if questions arise.
  - Verify no hardcode data remains in any admin PHP or JavaScript files.

## Notes

- Tasks marked with `*` are optional and can be skipped for faster MVP
- Each task references specific requirements for traceability
- Checkpoints ensure incremental validation
- Property tests validate universal correctness properties
- Integration tests validate complete workflows
- Performance optimization tasks ensure scalability
- **SCOPE**: This spec covers ONLY admin panel hardcode, not public pages