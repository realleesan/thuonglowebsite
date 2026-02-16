# Implementation Plan: Agent Registration System

## Overview

Triển khai hệ thống đăng ký đại lý tích hợp với cấu trúc PHP MVC hiện tại, bao gồm hai luồng đăng ký (người dùng mới và hiện tại), quản lý admin, và hệ thống email thông báo. Các tasks được sắp xếp theo thứ tự tăng dần, mỗi bước xây dựng trên các bước trước đó.

## Tasks

- [x] 1. Thiết lập cấu trúc dữ liệu và models
  - [x] 1.1 Mở rộng bảng users với các trường agent registration
    - Tạo migration để thêm agent_request_status, agent_request_date, agent_approved_date vào bảng users
    - _Requirements: 1.3, 3.5, 4.1_
  
  - [x] 1.2 Tạo AgentRegistrationData class
    - Tạo class để quản lý dữ liệu đăng ký đại lý với validation
    - _Requirements: 2.2, 5.3_
  
  - [x]* 1.3 Viết property test cho data models
    - **Property 3: Agent account creation with correct status**
    - **Validates: Requirements 1.3**

- [x] 2. Triển khai core services
  - [x] 2.1 Tạo SpamPreventionService
    - Triển khai logic ngăn chặn spam và rate limiting
    - _Requirements: 4.1, 4.2, 4.3, 4.4_
  
  - [x]* 2.2 Viết property test cho spam prevention
    - **Property 10: Duplicate submissions are prevented**
    - **Property 11: Rate limiting is enforced**
    - **Validates: Requirements 4.1, 4.2, 4.3**
  
  - [x] 2.3 Tạo EmailNotificationService
    - Triển khai service gửi email sử dụng PHPMailer hiện có
    - _Requirements: 1.4, 2.3, 3.4, 5.1, 5.2, 5.3, 5.4_
  
  - [x]* 2.4 Viết property test cho email service
    - **Property 4: Email notifications are sent for all registrations**
    - **Property 12: Email error handling works correctly**
    - **Validates: Requirements 1.4, 2.3, 5.1, 5.2, 5.3, 5.4**

- [x] 3. Checkpoint - Kiểm tra core services
  - Đảm bảo tất cả tests pass, hỏi user nếu có vấn đề gì phát sinh.

- [x] 4. Triển khai AgentRegistrationService
  - [x] 4.1 Tạo AgentRegistrationService class
    - Triển khai logic chính cho đăng ký đại lý (cả new user và existing user)
    - _Requirements: 1.3, 2.2, 2.3, 4.1, 4.2_
  
  - [x]* 4.2 Viết property test cho registration service
    - **Property 7: Gmail validation is enforced**
    - **Validates: Requirements 2.2**
  
  - [x] 4.3 Tích hợp với existing authentication system
    - Kết nối với AuthService và RoleManager hiện có
    - _Requirements: 6.2_
  
  - [x]* 4.4 Viết unit tests cho authentication integration
    - Test integration points với existing auth system
    - _Requirements: 6.2_

- [x] 5. Mở rộng AuthController cho new user registration
  - [x] 5.1 Thêm agent option vào registration form
    - Cập nhật register view để hiển thị lựa chọn vai trò
    - _Requirements: 1.2_
  
  - [x] 5.2 Triển khai registerWithAgentOption method
    - Xử lý đăng ký với lựa chọn vai trò đại lý
    - _Requirements: 1.3, 1.4_
  
  - [x]* 5.3 Viết property test cho registration form
    - **Property 2: Registration form displays role options**
    - **Validates: Requirements 1.2**

- [x] 6. Tạo AgentController cho existing users
  - [x] 6.1 Tạo AgentController class
    - Triển khai controller xử lý agent registration cho existing users
    - _Requirements: 2.1, 2.2, 2.3, 2.4_
  
  - [x] 6.2 Triển khai showRegistrationPopup method
    - Hiển thị popup đăng ký đại lý cho existing users
    - _Requirements: 2.1_
  
  - [x] 6.3 Triển khai processRegistration method
    - Xử lý form submission từ popup
    - _Requirements: 2.2, 2.3_
  
  - [x]* 6.4 Viết property test cho existing user flow
    - **Property 6: Existing users get registration popup**
    - **Validates: Requirements 2.1**

- [x] 7. Triển khai navigation và UI components
  - [x] 7.1 Cập nhật navigation menu
    - Thêm "Đại lý" button vào nav menu và CTAs
    - _Requirements: 1.1, 2.1_
  
  - [x] 7.2 Tạo agent registration popup view
    - Tạo popup form cho existing users
    - _Requirements: 2.1, 2.2_
  
  - [x] 7.3 Triển khai processing status messages
    - Hiển thị thông báo xử lý cho users có pending status
    - _Requirements: 1.5, 2.4, 4.4_
  
  - [x]* 7.4 Viết property test cho UI behavior
    - **Property 1: Navigation redirects work consistently**
    - **Property 5: Pending users see processing messages consistently**
    - **Validates: Requirements 1.1, 1.5, 2.4, 4.4**

- [x] 8. Checkpoint - Kiểm tra user-facing features
  - Đảm bảo tất cả tests pass, hỏi user nếu có vấn đề gì phát sinh.

- [x] 9. Mở rộng AdminController cho agent management
  - [x] 9.1 Thêm agent management methods vào AdminController
    - Triển khai manageAgentRequests, approveAgentRequest, updateAgentStatus
    - _Requirements: 3.1, 3.2, 3.3, 3.4, 3.5_
  
  - [x] 9.2 Cập nhật admin views
    - Cập nhật users tab và agents tab để hiển thị thông tin đúng
    - _Requirements: 3.1, 3.2_
  
  - [x]* 9.3 Viết property test cho admin functionality
    - **Property 8: Admin panels display correct user information**
    - **Property 9: Status updates process correctly**
    - **Validates: Requirements 3.1, 3.2, 3.3, 3.5**

- [x] 10. Tích hợp và wiring cuối cùng
  - [x] 10.1 Kết nối tất cả components
    - Wire các services, controllers, và views lại với nhau
    - Cập nhật routing để handle các endpoints mới
    - _Requirements: 1.1, 2.1, 3.1, 3.2_
  
  - [x] 10.2 Triển khai error handling và logging
    - Thêm comprehensive error handling và logging
    - _Requirements: 5.4_
  
  - [x]* 10.3 Viết integration tests
    - Test end-to-end flows cho cả hai luồng đăng ký
    - _Requirements: 1.1, 1.2, 1.3, 1.4, 2.1, 2.2, 2.3_

- [x] 11. Final checkpoint - Đảm bảo tất cả tests pass
  - Đảm bảo tất cả tests pass, hỏi user nếu có vấn đề gì phát sinh.

## Notes

- Tasks được đánh dấu `*` là optional và có thể skip để có MVP nhanh hơn
- Mỗi task reference đến requirements cụ thể để đảm bảo traceability
- Checkpoints đảm bảo validation từng bước
- Property tests validate universal correctness properties
- Unit tests validate specific examples và edge cases
- Hệ thống tích hợp với cấu trúc PHP MVC hiện tại và không làm thay đổi architecture cơ bản