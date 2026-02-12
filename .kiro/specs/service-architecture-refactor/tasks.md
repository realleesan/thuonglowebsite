# Kế Hoạch Triển Khai: Tái Cấu Trúc Kiến Trúc Service

## Tổng Quan

Kế hoạch này chia việc tái cấu trúc thành 4 phases rõ ràng, mỗi phase có thể test độc lập và đảm bảo backward compatibility. Ưu tiên sự đơn giản, ổn định và dễ maintain.

## Tasks

### Phase 1: Tạo Infrastructure Cơ Bản

- [ ] 1. Tạo base classes và interfaces
  - Tạo ServiceInterface với methods cần thiết
  - Tạo BaseService abstract class với common functionality
  - Tạo ErrorHandler class cho logging và error management
  - _Requirements: US4.1, US3.4_

  - [ ]* 1.1 Viết property test cho ServiceInterface
    - **Property 1: Service Type Validation**
    - **Validates: Requirements US4.2**

- [ ] 2. Tạo ServiceManager class
  - Implement getService() method với lazy loading
  - Implement service caching mechanism
  - Add error handling và fallback logic
  - _Requirements: US4.1, US4.2, US4.3, US4.4_

  - [ ]* 2.1 Viết property test cho ServiceManager lazy loading
    - **Property 2: Lazy Loading Consistency**
    - **Validates: Requirements US2.1, US2.2**

  - [ ]* 2.2 Viết unit tests cho ServiceManager
    - Test getService() với các type khác nhau
    - Test fallback mechanism khi service lỗi
    - _Requirements: US4.2, US4.3_

- [ ] 3. Tạo FallbackService class
  - Implement methods trả về empty data
  - Ensure graceful degradation khi service chính lỗi
  - Add logging cho fallback usage
  - _Requirements: US3.2, US4.3_

  - [ ]* 3.1 Viết property test cho error handling
    - **Property 4: Error Graceful Handling**
    - **Validates: Requirements US3.1, US3.2, US3.4**

- [ ] 4. Checkpoint Phase 1 - Test Infrastructure
  - Ensure all tests pass, ask the user if questions arise.

### Phase 2: Tạo Service Classes Chính

- [ ] 5. Tạo PublicService class
  - Migrate methods từ ViewDataService cho public pages
  - Implement lazy loading cho Models (ProductsModel, CategoriesModel, NewsModel, ContactsModel)
  - Add error handling cho từng method
  - _Requirements: US1.1, US2.1, US2.2, US3.1_

  - [ ]* 5.1 Viết property test cho model caching
    - **Property 3: Model Caching**
    - **Validates: Requirements US2.3**

  - [ ]* 5.2 Viết unit tests cho PublicService
    - Test getHomeData(), getProductsData(), getCategoriesData()
    - Test error scenarios với mock Models
    - _Requirements: US1.1, US3.2_

- [ ] 6. Tạo UserService class
  - Migrate methods cho user dashboard và account management
  - Implement lazy loading cho Models (UsersModel, OrdersModel, ProductsModel)
  - Add authentication checks trong methods
  - _Requirements: US1.2, US2.1, US2.2, US3.1_

  - [ ]* 6.1 Viết property test cho service isolation
    - **Property 1: Service Isolation**
    - **Validates: Requirements US3.2**

  - [ ]* 6.2 Viết unit tests cho UserService
    - Test getDashboardData(), getAccountData(), getOrdersData()
    - Test với user authentication scenarios
    - _Requirements: US1.2, US3.2_

- [ ] 7. Tạo AdminService class
  - Migrate methods cho admin functionality
  - Implement lazy loading cho tất cả Models cần thiết
  - Add admin permission checks
  - _Requirements: US1.3, US2.1, US2.2, US3.1_

  - [ ]* 7.1 Viết property test cho method isolation
    - **Property 7: Service Method Isolation**
    - **Validates: Requirements US1.5**

  - [ ]* 7.2 Viết unit tests cho AdminService
    - Test admin dashboard, products, users management
    - Test permission checks và error handling
    - _Requirements: US1.3, US3.2_

- [ ] 8. Tạo AffiliateService class
  - Migrate methods cho affiliate functionality
  - Implement lazy loading cho Models (AffiliateModel, OrdersModel, UsersModel)
  - Add affiliate permission checks
  - _Requirements: US1.4, US2.1, US2.2, US3.1_

  - [ ]* 8.1 Viết unit tests cho AffiliateService
    - Test affiliate dashboard, commissions, reports
    - Test permission và error scenarios
    - _Requirements: US1.4, US3.2_

- [ ] 9. Checkpoint Phase 2 - Test All Services
  - Ensure all tests pass, ask the user if questions arise.

### Phase 3: Update Views và Routing

- [ ] 10. Cập nhật view_init.php
  - Tạo ServiceManager instance
  - Setup error handling cho service creation
  - Remove old ViewDataService initialization
  - _Requirements: US5.1, US5.2_

  - [ ]* 10.1 Viết property test cho dependency injection
    - **Property 8: Dependency Injection**
    - **Validates: Requirements US5.1, US5.2**

- [ ] 11. Cập nhật routing trong index.php
  - Pass appropriate service instance vào từng view
  - Map routes với correct service types
  - Add fallback routing cho error cases
  - _Requirements: US5.1, US5.3_

- [ ] 12. Cập nhật Public Views (14 trang)
  - [ ] 12.1 Update home, about, products, categories views
    - Replace $viewDataService với $publicService
    - Add empty data handling
    - _Requirements: US5.1, US5.4_

  - [ ] 12.2 Update news, contact, auth views  
    - Replace service calls với PublicService methods
    - Handle service errors gracefully
    - _Requirements: US5.1, US5.4_

  - [ ] 12.3 Update checkout, payment views
    - Integrate với PublicService
    - Add error fallbacks
    - _Requirements: US5.1, US5.4_

  - [ ]* 12.4 Viết property test cho view data handling
    - **Property 6: View Data Handling**
    - **Validates: Requirements US3.3, US5.4**

- [ ] 13. Cập nhật User Views (5 trang)
  - Update dashboard, account, orders, cart, wishlist views
  - Replace với UserService calls
  - Add authentication error handling
  - _Requirements: US5.1, US5.3, US5.4_

  - [ ]* 13.1 Viết integration tests cho User views
    - Test views với empty data từ UserService
    - Test authentication scenarios
    - _Requirements: US3.3, US5.4_

- [ ] 14. Cập nhật Admin Views (12 modules)
  - [ ] 14.1 Update admin dashboard, products, categories
    - Replace với AdminService calls
    - Add admin permission checks
    - _Requirements: US5.1, US5.3, US5.4_

  - [ ] 14.2 Update users, orders, settings management
    - Integrate với AdminService methods
    - Handle service errors trong admin interface
    - _Requirements: US5.1, US5.3, US5.4_

  - [ ]* 14.3 Viết integration tests cho Admin views
    - Test admin functionality với service errors
    - Test permission handling
    - _Requirements: US3.3, US5.4_

- [ ] 15. Cập nhật Affiliate Views (7 modules)
  - Update dashboard, commissions, customers, reports views
  - Replace với AffiliateService calls
  - Add affiliate permission handling
  - _Requirements: US5.1, US5.3, US5.4_

  - [ ]* 15.1 Viết integration tests cho Affiliate views
    - Test affiliate functionality với empty data
    - Test permission scenarios
    - _Requirements: US3.3, US5.4_

- [ ] 16. Checkpoint Phase 3 - Test All Views
  - Ensure all tests pass, ask the user if questions arise.

### Phase 4: Testing, Cleanup và Finalization

- [ ] 17. Comprehensive Testing
  - [ ] 17.1 Test error scenarios
    - Mock database errors, model failures
    - Verify fallback services work correctly
    - Test website stability khi services lỗi
    - _Requirements: US3.1, US3.2, US4.3_

  - [ ] 17.2 Performance testing
    - Verify lazy loading improves performance
    - Test memory usage với new architecture
    - Compare với old ViewDataService
    - _Requirements: US2.1, US2.2_

  - [ ]* 17.3 Viết end-to-end tests
    - Test complete user journeys
    - Test admin workflows
    - Test affiliate processes
    - _Requirements: US1.1, US1.2, US1.3, US1.4_

- [ ] 18. Code cleanup và documentation
  - Remove old ViewDataService.php file
  - Clean up unused methods và dependencies
  - Update code comments và documentation
  - _Requirements: US5.2_

- [ ] 19. Final verification
  - [ ] 19.1 Verify backward compatibility
    - Ensure no breaking changes
    - Test all existing functionality works
    - _Requirements: Constraints_

  - [ ] 19.2 Security review
    - Check permission handling trong new services
    - Verify error messages don't leak sensitive info
    - _Requirements: US3.4_

- [ ] 20. Final Checkpoint - Complete System Test
  - Ensure all tests pass, ask the user if questions arise.

## Notes

### Migration Strategy
- Mỗi phase có thể rollback nếu có vấn đề
- Giữ ViewDataService cũ cho đến khi hoàn thành migration
- Test kỹ từng phase trước khi chuyển sang phase tiếp theo
- Ưu tiên stability hơn performance optimization

### Testing Approach
- Tasks marked với `*` là optional và có thể skip cho faster MVP
- Property tests validate universal correctness properties
- Unit tests validate specific examples và edge cases
- Integration tests ensure components work together
- Minimum 100 iterations per property test

### Error Handling Priority
1. Website không được crash dù service nào lỗi
2. User experience phải smooth với fallback data
3. Errors phải được log đầy đủ cho debugging
4. Admin interface phải có error notifications

### Performance Considerations
- Lazy loading giảm memory usage và startup time
- Service caching tránh tạo lại instances
- Model caching trong mỗi service
- Error handling không được impact performance