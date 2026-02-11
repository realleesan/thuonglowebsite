# Hướng Dẫn Testing Phase 5: Chuyển Đổi JSON sang SQL

## Tổng Quan
Phase 5 đã hoàn thành việc chuyển đổi các Views từ sử dụng dữ liệu JSON sang sử dụng Models và Database SQL.

## Các Thay Đổi Chính

### 1. Views Đã Chuyển Đổi
- ✅ Admin Dashboard (`app/views/admin/dashboard.php`)
- ✅ Admin Users (index, view, edit) 
- ✅ Admin Products (index, view, edit, add)
- ✅ Admin Orders (index)
- ✅ Admin Categories (index)
- ✅ Admin News (index)
- ✅ Admin Contacts (index)
- ✅ Admin Settings (index)
- ✅ Admin Affiliates (index)
- ✅ Authentication System (`app/views/auth/auth.php`)
- ✅ User Dashboard (`app/views/users/dashboard.php`)

### 2. Models Được Sử Dụng
- `UsersModel` - Quản lý người dùng
- `ProductsModel` - Quản lý sản phẩm
- `OrdersModel` - Quản lý đơn hàng
- `CategoriesModel` - Quản lý danh mục
- `NewsModel` - Quản lý tin tức
- `ContactsModel` - Quản lý liên hệ
- `SettingsModel` - Quản lý cài đặt
- `AffiliateModel` - Quản lý đại lý

## Hướng Dẫn Testing

### Bước 1: Chuẩn Bị Database
```bash
# Chạy migration để tạo bảng
php scripts/migrate.php

# Chạy seeder để có dữ liệu test
php scripts/seed.php
```

### Bước 2: Test Authentication
1. Truy cập trang đăng nhập
2. Test đăng nhập với tài khoản admin/user
3. Kiểm tra session và redirect
4. Test đăng xuất

### Bước 3: Test Admin Panel
1. **Dashboard**: Kiểm tra thống kê hiển thị đúng
2. **Users Management**:
   - Xem danh sách users
   - Xem chi tiết user
   - Chỉnh sửa user
   - Tìm kiếm và lọc
3. **Products Management**:
   - Xem danh sách sản phẩm
   - Thêm sản phẩm mới
   - Chỉnh sửa sản phẩm
   - Xem chi tiết sản phẩm
4. **Orders Management**:
   - Xem danh sách đơn hàng
   - Tìm kiếm đơn hàng
   - Lọc theo trạng thái
5. **Categories**: Xem danh sách danh mục
6. **News**: Xem danh sách tin tức
7. **Contacts**: Xem danh sách liên hệ
8. **Settings**: Xem cài đặt hệ thống

### Bước 4: Test User Dashboard
1. Đăng nhập với tài khoản user
2. Kiểm tra thống kê cá nhân
3. Xem lịch sử đơn hàng
4. Kiểm tra điểm thưởng

### Bước 5: Test Tìm Kiếm và Lọc
1. Test tìm kiếm users theo tên, email, phone
2. Test lọc users theo role, status
3. Test tìm kiếm products theo tên
4. Test lọc products theo category, status
5. Test tìm kiếm orders theo order number, user

### Bước 6: Test Pagination
1. Kiểm tra phân trang hoạt động đúng
2. Test chuyển trang
3. Kiểm tra số lượng records per page

## Các Lỗi Có Thể Gặp

### 1. Database Connection Error
- Kiểm tra config.php
- Đảm bảo MySQL đang chạy
- Kiểm tra thông tin kết nối

### 2. Model Not Found Error
- Kiểm tra đường dẫn require_once
- Đảm bảo file Model tồn tại

### 3. Undefined Variable Error
- Kiểm tra các biến được khởi tạo đúng
- Kiểm tra logic query database

### 4. SQL Error
- Kiểm tra syntax SQL
- Đảm bảo bảng và cột tồn tại
- Kiểm tra migration đã chạy

## Scripts Hỗ Trợ

### Kiểm Tra Tiến Độ Chuyển Đổi
```bash
php scripts/check_json_conversion.php
```

### Báo Cáo Hoàn Thành
```bash
php scripts/phase5_completion_report.php
```

### Dọn Dẹp File JSON Cũ
```bash
php scripts/cleanup_json_files.php
```

## Kết Luận
Phase 5 đã hoàn thành việc chuyển đổi các Views chính từ JSON sang SQL. Hệ thống giờ đây sử dụng database thực sự thay vì dữ liệu tĩnh, tạo nền tảng cho việc phát triển các tính năng động và quản lý dữ liệu hiệu quả hơn.

## Bước Tiếp Theo
1. Hoàn thiện các Views còn lại nếu cần
2. Thêm validation và error handling
3. Tối ưu hóa performance
4. Thêm các tính năng CRUD đầy đủ
5. Implement caching nếu cần thiết