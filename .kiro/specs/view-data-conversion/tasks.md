# Implementation Plan: View Data Conversion

## Overview

Chuyển đổi tất cả các view PHP từ hardcoded HTML data sang dynamic data từ database. Kế hoạch này sẽ thực hiện từng bước một cách có hệ thống, đảm bảo tính ổn định và khả năng rollback.

## Tasks

- [x] 1. Set up infrastructure and helper classes
  - Create ViewDataService class for centralized data preparation
  - Create DataTransformer class for data formatting
  - Create ViewSecurityHelper class for security and validation
  - Set up error handling utilities
  - _Requirements: 1.1, 2.1, 2.2, 2.3, 2.4, 6.1, 6.2, 6.3, 6.4_

- [ ]* 1.1 Write property test for infrastructure classes
  - **Property 3: Security Data Escaping**
  - **Validates: Requirements 2.1, 2.2**

- [ ]* 1.2 Write property test for data transformation
  - **Property 4: Data Validation and Formatting**
  - **Validates: Requirements 2.3, 2.4**

- [x] 2. Enhance existing models with view-specific methods
  - [x] 2.1 Extend ProductsModel with view helper methods
    - Add getFeaturedForHome() method
    - Add getByCategory() with pagination
    - Add getProductStats() for admin views
    - _Requirements: 1.3, 4.2, 8.3_
  
  - [ ]* 2.2 Write property test for ProductsModel extensions
    - **Property 1: Database Model Usage Consistency**
    - **Validates: Requirements 1.3, 4.2**
  
  - [x] 2.3 Extend CategoriesModel with view helper methods
    - Add getWithProductCounts() method
    - Add getFeaturedCategories() method
    - _Requirements: 4.2, 8.3_
  
  - [ ]* 2.4 Write property test for CategoriesModel extensions
    - **Property 1: Database Model Usage Consistency**
    - **Validates: Requirements 4.2**
  
  - [x] 2.5 Extend NewsModel with view helper methods
    - Add getLatestForHome() method
    - Add getWithCategories() method
    - _Requirements: 1.1, 8.1_
  
  - [ ]* 2.6 Write unit tests for NewsModel extensions
    - Test getLatestForHome() with various data scenarios
    - Test empty state handling
    - _Requirements: 1.1, 3.1_

- [ ] 3. Checkpoint - Ensure infrastructure is working
  - Ensure all tests pass, ask the user if questions arise.

- [x] 4. Convert home page (app/views/home/home.php)
  - [x] 4.1 Replace hardcoded product listings with ProductsModel data
    - Convert "Sản phẩm Nổi bật" section to use getFeaturedForHome()
    - Convert "Sản phẩm Mới nhất" section to use getLatestForHome()
    - Implement proper error handling and empty states
    - _Requirements: 1.1, 1.3, 3.1, 5.1, 5.3_
  
  - [ ]* 4.2 Write property test for home page data usage
    - **Property 2: Database Data Display**
    - **Validates: Requirements 1.1, 1.3**
  
  - [x] 4.3 Replace hardcoded category listings with CategoriesModel data
    - Convert "Danh mục Nổi bật" section to use getFeaturedCategories()
    - Maintain existing HTML structure and CSS classes
    - _Requirements: 1.1, 5.1, 5.3_
  
  - [ ]* 4.4 Write property test for category data display
    - **Property 6: UI Structure Preservation**
    - **Validates: Requirements 5.1, 5.3**
  
  - [x] 4.5 Replace hardcoded testimonials with database data
    - Create TestimonialsModel if needed or use existing model
    - Convert customer reviews section to dynamic data
    - _Requirements: 1.1, 2.1, 2.2_
  
  - [ ]* 4.6 Write unit tests for testimonials display
    - Test testimonial rendering with real data
    - Test XSS prevention in testimonial content
    - _Requirements: 2.1, 2.2_

- [x] 5. Convert product-related views
  - [x] 5.1 Convert app/views/products/products.php
    - Replace hardcoded product grid with ProductsModel data
    - Implement pagination using model methods
    - Add search and filter functionality
    - _Requirements: 1.3, 4.2, 7.2_
  
  - [ ]* 5.2 Write property test for product listing
    - **Property 9: Pagination Implementation**
    - **Validates: Requirements 7.2**
  
  - [x] 5.3 Convert app/views/products/details.php
    - Replace hardcoded product details with database data
    - Implement related products section
    - Add proper error handling for non-existent products
    - _Requirements: 1.3, 6.1, 6.2_
  
  - [ ]* 5.4 Write property test for product details
    - **Property 7: Error Handling and Recovery**
    - **Validates: Requirements 6.1, 6.2**

- [x] 6. Convert category views
  - [x] 6.1 Convert app/views/categories/categories.php
    - Replace hardcoded category listings with CategoriesModel data
    - Show real product counts for each category
    - Implement category-based product filtering
    - _Requirements: 1.1, 4.2, 8.3_
  
  - [ ]* 6.2 Write property test for category views
    - **Property 2: Database Data Display**
    - **Validates: Requirements 1.1, 4.2**

- [x] 7. Checkpoint - Ensure public views are working
  - Ensure all tests pass, ask the user if questions arise.

- [x] 8. Convert admin views
  - [x] 8.1 Update admin dashboard (app/views/admin/dashboard.php)
    - Remove any remaining hardcoded statistics
    - Ensure all metrics come from database queries
    - Optimize database queries for performance
    - _Requirements: 8.1, 7.1, 7.3_
  
  - [ ]* 8.2 Write property test for admin dashboard
    - **Property 8: Performance Optimization**
    - **Validates: Requirements 7.1, 7.3**
  
  - [x] 8.3 Convert admin product management views
    - Update app/views/admin/products/index.php
    - Update app/views/admin/products/view.php
    - Update app/views/admin/products/edit.php
    - Update app/views/admin/products/add.php
    - _Requirements: 8.2, 8.3, 5.4_
  
  - [ ]* 8.4 Write property test for admin product views
    - **Property 2: Database Data Display**
    - **Validates: Requirements 8.2, 8.3**
  
  - [x] 8.5 Convert admin user management views
    - Update app/views/admin/users/index.php
    - Update app/views/admin/users/view.php
    - Update app/views/admin/users/edit.php
    - Update app/views/admin/users/add.php
    - _Requirements: 8.2, 4.1_
  
  - [ ]* 8.6 Write unit tests for admin user views
    - Test user listing with pagination
    - Test user search functionality
    - Test empty state handling
    - _Requirements: 8.2, 3.1, 7.2_
  
  - [x] 8.7 Convert admin category management views
    - Update app/views/admin/categories/index.php
    - Update app/views/admin/categories/view.php
    - Update app/views/admin/categories/edit.php
    - Update app/views/admin/categories/add.php
    - _Requirements: 8.2, 8.3, 4.2_
  
  - [ ]* 8.8 Write unit tests for admin category views
    - Test category listing with pagination
    - Test category search functionality
    - Test product count display
    - _Requirements: 8.2, 4.2, 7.2_
  
  - [x] 8.9 Convert admin order management views
    - Update app/views/admin/orders/index.php
    - Update app/views/admin/orders/view.php
    - Update app/views/admin/orders/edit.php
    - Update app/views/admin/orders/add.php
    - _Requirements: 8.2, 8.3, 9.2_
  
  - [ ]* 8.10 Write unit tests for admin order views
    - Test order listing with pagination and filters
    - Test order status updates
    - Test order search functionality
    - _Requirements: 8.2, 9.2, 7.2_
  
  - [x] 8.11 Convert admin news management views
    - Update app/views/admin/news/index.php
    - Update app/views/admin/news/view.php
    - Update app/views/admin/news/edit.php
    - Update app/views/admin/news/add.php
    - _Requirements: 8.2, 8.1, 1.1_
  
  - [ ]* 8.12 Write unit tests for admin news views
    - Test news listing with pagination
    - Test news status management
    - Test news search functionality
    - _Requirements: 8.2, 8.1, 7.2_
  
  - [x] 8.13 Convert admin affiliate management views
    - Update app/views/admin/affiliates/index.php
    - Update app/views/admin/affiliates/view.php
    - Update app/views/admin/affiliates/edit.php
    - Update app/views/admin/affiliates/add.php
    - _Requirements: 8.2, 10.1, 10.2_
  
  - [ ]* 8.14 Write unit tests for admin affiliate views
    - Test affiliate listing with pagination
    - Test commission calculations
    - Test affiliate search functionality
    - _Requirements: 8.2, 10.1, 10.2_
  
  - [x] 8.15 Convert admin events management views
    - Update app/views/admin/events/index.php
    - Update app/views/admin/events/view.php
    - Update app/views/admin/events/edit.php
    - Update app/views/admin/events/add.php
    - _Requirements: 8.2, 8.1_
  
  - [ ]* 8.16 Write unit tests for admin events views
    - Test events listing with pagination
    - Test event date filtering
    - Test event search functionality
    - _Requirements: 8.2, 8.1, 7.2_
  
  - [x] 8.17 Convert admin settings management views
    - Update app/views/admin/settings/index.php
    - Update app/views/admin/settings/view.php
    - Update app/views/admin/settings/edit.php
    - Update app/views/admin/settings/add.php
    - _Requirements: 8.2, 4.5, 4.6_
  
  - [ ]* 8.18 Write unit tests for admin settings views
    - Test settings listing and management
    - Test settings validation
    - Test settings search functionality
    - _Requirements: 8.2, 4.5, 4.6_
  
  - [x] 8.19 Convert admin contact management views
    - Update app/views/admin/contact/index.php
    - Update app/views/admin/contact/view.php
    - Update app/views/admin/contact/edit.php
    - _Requirements: 8.2, 4.5, 4.6_
  
  - [ ]* 8.20 Write unit tests for admin contact views
    - Test contact messages listing
    - Test contact status management
    - Test contact search functionality
    - _Requirements: 8.2, 4.5, 4.6_
  
  - [ ] 8.21 Convert admin notifications management views
    - Update app/views/admin/notifications/index.php
    - Update app/views/admin/notifications/view.php
    - Update app/views/admin/notifications/delete.php
    - _Requirements: 8.2, 8.1_
  
  - [ ]* 8.22 Write unit tests for admin notifications views
    - Test notifications listing
    - Test notification status management
    - Test notification search functionality
    - _Requirements: 8.2, 8.1, 7.2_
  
  - [ ] 8.23 Convert admin profile management views
    - Update app/views/admin/profile/index.php
    - Update app/views/admin/profile/view.php
    - Update app/views/admin/profile/edit.php
    - _Requirements: 8.2, 4.1_
  
  - [ ]* 8.24 Write unit tests for admin profile views
    - Test profile display and editing
    - Test profile validation
    - Test profile security
    - _Requirements: 8.2, 4.1, 2.1_
  
  - [x] 8.25 Convert admin revenue management views
    - Update app/views/admin/revenue/index.php
    - Update app/views/admin/revenue/view.php
    - Update app/views/admin/revenue/reports.php
    - _Requirements: 8.2, 7.1, 7.3_
  
  - [ ]* 8.26 Write unit tests for admin revenue views
    - Test revenue calculations
    - Test revenue reports generation
    - Test revenue data filtering
    - _Requirements: 8.2, 7.1, 7.3_

- [ ] 9. Convert user dashboard and account views
  - [ ] 9.1 Ensure user dashboard is fully dynamic
    - Verify app/views/users/dashboard.php uses only database data
    - Remove any remaining hardcoded elements
    - Optimize user-specific queries
    - _Requirements: 9.1, 9.2, 9.3, 9.4_
  
  - [ ]* 9.2 Write property test for user dashboard
    - **Property 2: Database Data Display**
    - **Validates: Requirements 9.1, 9.2, 9.3, 9.4**
  
  - [ ] 9.3 Convert user account management views
    - Update app/views/users/account/ views
    - Update app/views/users/orders/ views
    - Update app/views/users/cart/ views
    - Update app/views/users/wishlist/ views
    - _Requirements: 9.2, 9.3, 9.4_
  
  - [ ]* 9.4 Write unit tests for user account views
    - Test order history display
    - Test account settings functionality
    - Test cart and wishlist operations
    - _Requirements: 9.2, 9.3, 9.4_

- [ ] 10. Convert affiliate views
  - [ ] 10.1 Convert affiliate dashboard
    - Update app/views/affiliate/dashboard.php
    - Ensure all commission data comes from database
    - Implement real earnings calculations
    - _Requirements: 10.1, 10.3_
  
  - [ ]* 10.2 Write property test for affiliate dashboard
    - **Property 2: Database Data Display**
    - **Validates: Requirements 10.1, 10.3**
  
  - [ ] 10.3 Convert affiliate management views
    - Update app/views/affiliate/customers/list.php
    - Update app/views/affiliate/commissions/ views
    - Update app/views/affiliate/reports/ views
    - _Requirements: 10.2, 10.4_
  
  - [ ]* 10.4 Write unit tests for affiliate views
    - Test customer list display
    - Test commission calculations
    - Test report generation
    - _Requirements: 10.2, 10.4_

- [ ] 11. Convert remaining views
  - [ ] 11.1 Convert news and blog views
    - Update app/views/news/news.php
    - Update app/views/news/details.php
    - Implement dynamic news listing and details
    - _Requirements: 1.1, 8.1_
  
  - [ ] 11.2 Convert contact and about views
    - Update app/views/contact/contact.php
    - Update app/views/about/about.php
    - Use ContactsModel and SettingsModel for dynamic content
    - _Requirements: 4.5, 4.6_
  
  - [ ] 11.3 Convert authentication views
    - Update app/views/auth/ views if needed
    - Ensure proper error handling and validation
    - _Requirements: 2.3, 6.2_
  
  - [ ]* 11.4 Write integration tests for remaining views
    - Test news listing and details
    - Test contact form functionality
    - Test authentication flows
    - _Requirements: 1.1, 4.5, 2.3_

- [ ] 12. Implement caching layer
  - [ ] 12.1 Add caching for frequently accessed data
    - Implement caching in ViewDataService
    - Cache home page data, featured products, categories
    - Add cache invalidation logic
    - _Requirements: 7.4_
  
  - [ ]* 12.2 Write property test for caching behavior
    - **Property 10: Caching Behavior**
    - **Validates: Requirements 7.4**

- [ ] 13. Performance optimization and cleanup
  - [ ] 13.1 Optimize database queries
    - Review and optimize all model methods
    - Implement proper joins to reduce query count
    - Add database indexes if needed
    - _Requirements: 7.1, 7.3_
  
  - [ ]* 13.2 Write property test for query optimization
    - **Property 8: Performance Optimization**
    - **Validates: Requirements 7.1, 7.3**
  
  - [ ] 13.3 Clean up unused code and files
    - Remove any remaining hardcoded data
    - Clean up unused variables and functions
    - Update documentation and comments
    - _Requirements: 1.1_

- [ ] 14. Final testing and validation
  - [ ] 14.1 Run comprehensive test suite
    - Execute all property-based tests
    - Execute all unit tests
    - Execute integration tests
    - _Requirements: All_
  
  - [ ]* 14.2 Write end-to-end property tests
    - **Property 5: Empty State Handling**
    - **Validates: Requirements 3.1**
  
  - [ ] 14.3 Perform manual testing
    - Test all major user flows
    - Verify UI consistency and functionality
    - Test error scenarios and edge cases
    - _Requirements: 5.1, 5.2, 5.3, 5.4_

- [ ] 15. Final checkpoint - Ensure all tests pass
  - Ensure all tests pass, ask the user if questions arise.

## Notes

- Tasks marked with `*` are optional and can be skipped for faster MVP
- Each task references specific requirements for traceability
- Checkpoints ensure incremental validation
- Property tests validate universal correctness properties
- Unit tests validate specific examples and edge cases
- Focus on maintaining existing UI/UX while converting data sources
- Prioritize security and performance throughout the conversion process