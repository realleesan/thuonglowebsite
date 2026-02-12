# Tái Cấu Trúc Kiến Trúc Service - Yêu Cầu

## Tổng Quan
Hệ thống hiện tại có vấn đề nghiêm trọng: khi 1 service lỗi thì toàn bộ website bị "white screen of death". Cần tái cấu trúc để tách biệt services, đảm bảo 1 service lỗi không ảnh hưởng đến các service khác.

## Vấn Đề Hiện Tại
- ViewDataService khổng lồ tạo tất cả 8+ Model classes trong constructor
- 1 Model lỗi → toàn bộ ViewDataService crash → website chết
- Tất cả trang phụ thuộc vào 1 service duy nhất
- Không có error handling và fallback mechanism
- Eager loading gây chậm và không cần thiết

## Mục Tiêu
- Tách ViewDataService thành nhiều service nhỏ theo chức năng
- Mỗi service chỉ load Model cần thiết (lazy loading)
- 1 service lỗi chỉ ảnh hưởng trang/chức năng đó
- Có fallback data khi service lỗi
- Dễ maintain và mở rộng

## User Stories

### US1: Tách Service Theo Nhóm Chức Năng
**Là một** developer  
**Tôi muốn** tách ViewDataService thành các service nhỏ theo nhóm chức năng  
**Để** dễ quản lý và debug khi có lỗi  

**Acceptance Criteria:**
- Có PublicService cho các trang công khai (home, products, news, contact, auth, payment)
- Có UserService cho dashboard người dùng (dashboard, orders, cart, wishlist)
- Có AdminService cho quản trị (dashboard, products, users, orders, settings)
- Có AffiliateService cho đại lý (dashboard, commissions, customers, reports)
- Mỗi service chỉ chứa methods liên quan đến chức năng của nó

### US2: Lazy Loading Models
**Là một** developer  
**Tôi muốn** Models chỉ được tạo khi thực sự cần sử dụng  
**Để** tránh load không cần thiết và giảm risk lỗi  

**Acceptance Criteria:**
- Models không được tạo trong constructor
- Models được tạo trong method getModel() khi cần
- Có caching để tránh tạo lại Model đã tồn tại
- Try-catch khi tạo Model, return null nếu lỗi

### US3: Error Handling và Fallback
**Là một** user  
**Tôi muốn** website vẫn hoạt động dù một số chức năng bị lỗi  
**Để** có trải nghiệm tốt hơn thay vì trang trắng  

**Acceptance Criteria:**
- Mọi service call được wrap trong try-catch
- Khi service lỗi, return empty data thay vì crash
- Views phải handle empty data gracefully
- Log errors nhưng không hiển thị cho user

### US4: ServiceManager
**Là một** developer  
**Tôi muốn** có 1 manager để quản lý tất cả services  
**Để** dễ dàng lấy service cần thiết và handle lỗi tập trung  

**Acceptance Criteria:**
- ServiceManager có method getService($type, $name)
- Support 4 loại service: public, user, admin, affiliate
- Có fallback service khi service chính lỗi
- Lazy loading services - chỉ tạo khi cần

### US5: Refactor Views
**Là một** developer  
**Tôi muốn** views sử dụng service cụ thể thay vì ViewDataService  
**Để** rõ ràng về dependency và dễ debug  

**Acceptance Criteria:**
- Views nhận service instance từ routing
- Không còn global $viewDataService
- Mỗi view chỉ sử dụng service cần thiết
- Views handle empty data từ service

## Phạm Vi Công Việc

### Các Trang Cần Refactor:

**Public Pages:**
- home, about, products, categories, details, news, news-details, contact
- login, register, forgot, checkout, payment, payment_success

**User Pages:**
- users/dashboard, users/account, users/orders, users/cart, users/wishlist

**Admin Pages:**
- admin/dashboard, admin/products, admin/categories, admin/news, admin/events
- admin/orders, admin/users, admin/affiliates, admin/contact, admin/revenue, admin/settings

**Affiliate Pages:**
- affiliate/dashboard, affiliate/commissions, affiliate/customers, affiliate/finance
- affiliate/marketing, affiliate/reports, affiliate/profile

### Files Cần Tạo/Sửa:
- Tạo ServiceManager.php
- Tạo các service classes mới
- Tạo fallback service classes
- Sửa view_init.php
- Sửa index.php routing
- Sửa tất cả view files

## Constraints
- Phải đảm bảo backward compatibility trong quá trình migration
- Không được làm gián đoạn website đang chạy
- Phải test kỹ từng phase trước khi chuyển sang phase tiếp theo
- Ưu tiên sự đơn giản và dễ hiểu hơn là kiến trúc phức tạp

## Success Criteria
- 1 service lỗi chỉ ảnh hưởng trang/chức năng đó, không crash toàn website
- Website load nhanh hơn do lazy loading
- Code dễ maintain và debug hơn
- Dễ dàng thêm chức năng mới mà không ảnh hưởng code cũ