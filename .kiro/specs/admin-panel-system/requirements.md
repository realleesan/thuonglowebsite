# Tài liệu Yêu cầu - Hệ thống Admin Panel

## Giới thiệu

Hệ thống admin panel là một giao diện quản trị toàn diện cho website PHP, cho phép quản trị viên quản lý các nội dung và chức năng của website thông qua một giao diện thân thiện và có tổ chức.

## Thuật ngữ

- **Admin_Panel**: Hệ thống giao diện quản trị
- **Sidebar**: Thanh điều hướng bên trái chứa các menu chính
- **Module**: Một chức năng quản lý cụ thể (sản phẩm, danh mục, tin tức, sự kiện)
- **Dashboard**: Trang tổng quan hiển thị thống kê và thông tin tổng hợp
- **Fake_Data**: Dữ liệu mẫu được lưu trong file JSON để demo
- **MVC_Structure**: Cấu trúc Model-View-Controller đã có sẵn trong hệ thống

## Yêu cầu

### Yêu cầu 1: Xây dựng Sidebar Navigation

**User Story:** Là một quản trị viên, tôi muốn có một thanh điều hướng sidebar với các menu chính, để tôi có thể dễ dàng truy cập các chức năng quản lý khác nhau.

#### Tiêu chí chấp nhận

1. KHI quản trị viên truy cập trang admin THÌ hệ thống SẼ hiển thị sidebar với các menu: dashboard, sản phẩm, danh mục, tin tức, sự kiện
2. KHI quản trị viên click vào một menu item THÌ hệ thống SẼ điều hướng đến trang tương ứng và highlight menu đang active
3. KHI sidebar được hiển thị THÌ hệ thống SẼ load CSS từ admin_sidebar.css và JavaScript từ admin_sidebar.js
4. KHI sidebar được render THÌ hệ thống SẼ sử dụng file admin_sidebar.php từ thư mục _layout
5. KHI trên thiết bị mobile THÌ sidebar SẼ có thể thu gọn/mở rộng thông qua nút toggle

### Yêu cầu 2: Tổ chức Cấu trúc File và Thư mục

**User Story:** Là một developer, tôi muốn có cấu trúc thư mục có tổ chức, để tôi có thể dễ dàng bảo trì và phát triển hệ thống.

#### Tiêu chí chấp nhận

1. KHI tạo cấu trúc admin THÌ hệ thống SẼ tạo folder riêng cho mỗi module quản lý
2. KHI tạo module folder THÌ hệ thống SẼ chứa các file: index.php, delete.php, change.php
3. KHI tổ chức CSS/JS THÌ hệ thống SẼ tách riêng: admin.css, admin.js, admin_sidebar.css, admin_sidebar.js
4. KHI sử dụng dữ liệu demo THÌ hệ thống SẼ load từ app/views/admin/data/fake_data.json
5. KHI tạo cấu trúc THÌ hệ thống SẼ tuân thủ pattern MVC hiện có

### Yêu cầu 3: Module Quản lý Sản phẩm

**User Story:** Là một quản trị viên, tôi muốn quản lý sản phẩm, để tôi có thể xem, thêm, sửa, xóa thông tin sản phẩm.

#### Tiêu chí chấp nhận

1. KHI truy cập module sản phẩm THÌ hệ thống SẼ hiển thị danh sách sản phẩm từ fake_data.json
2. KHI hiển thị danh sách THÌ hệ thống SẼ show thông tin: tên, giá, danh mục, trạng thái
3. KHI click nút sửa THÌ hệ thống SẼ chuyển đến trang change.php với dữ liệu sản phẩm
4. KHI click nút xóa THÌ hệ thống SẼ hiển thị confirm và xử lý trong delete.php
5. KHI thực hiện các thao tác THÌ hệ thống SẼ cập nhật dữ liệu trong fake_data.json

### Yêu cầu 4: Module Quản lý Danh mục

**User Story:** Là một quản trị viên, tôi muốn quản lý danh mục sản phẩm, để tôi có thể tổ chức sản phẩm theo nhóm.

#### Tiêu chí chấp nhận

1. KHI truy cập module danh mục THÌ hệ thống SẼ hiển thị danh sách danh mục từ fake_data.json
2. KHI hiển thị danh mục THÌ hệ thống SẼ show: tên danh mục, mô tả, số lượng sản phẩm
3. KHI thêm/sửa danh mục THÌ hệ thống SẼ validate tên danh mục không được trống
4. KHI xóa danh mục THÌ hệ thống SẼ kiểm tra không có sản phẩm nào đang sử dụng
5. KHI quản lý danh mục THÌ hệ thống SẼ cập nhật quan hệ với sản phẩm trong fake_data.json

### Yêu cầu 5: Module Quản lý Tin tức

**User Story:** Là một quản trị viên, tôi muốn quản lý tin tức, để tôi có thể đăng và cập nhật thông tin cho người dùng.

#### Tiêu chí chấp nhận

1. KHI truy cập module tin tức THÌ hệ thống SẼ hiển thị danh sách bài viết từ fake_data.json
2. KHI hiển thị tin tức THÌ hệ thống SẼ show: tiêu đề, tóm tắt, ngày đăng, trạng thái
3. KHI tạo/sửa tin tức THÌ hệ thống SẼ có form với: tiêu đề, nội dung, hình ảnh, trạng thái
4. KHI lưu tin tức THÌ hệ thống SẼ validate tiêu đề và nội dung không được trống
5. KHI xóa tin tức THÌ hệ thống SẼ hiển thị xác nhận trước khi xóa

### Yêu cầu 6: Module Quản lý Sự kiện

**User Story:** Là một quản trị viên, tôi muốn quản lý sự kiện, để tôi có thể thông báo các hoạt động sắp tới cho người dùng.

#### Tiêu chí chấp nhận

1. KHI truy cập module sự kiện THÌ hệ thống SẼ hiển thị danh sách sự kiện từ fake_data.json
2. KHI hiển thị sự kiện THÌ hệ thống SẼ show: tên sự kiện, ngày bắt đầu, ngày kết thúc, địa điểm
3. KHI tạo sự kiện THÌ hệ thống SẼ validate ngày bắt đầu phải trước ngày kết thúc
4. KHI sửa sự kiện THÌ hệ thống SẼ load dữ liệu hiện tại vào form
5. KHI xóa sự kiện THÌ hệ thống SẼ xác nhận và cập nhật fake_data.json

### Yêu cầu 7: Bảo mật Admin Area

**User Story:** Là một quản trị viên, tôi muốn admin area được bảo mật, để chỉ những người có quyền mới truy cập được.

#### Tiêu chí chấp nhận

1. KHI truy cập admin area THÌ hệ thống SẼ kiểm tra session đăng nhập admin
2. KHI chưa đăng nhập THÌ hệ thống SẼ redirect về trang login
3. KHI session hết hạn THÌ hệ thống SẼ yêu cầu đăng nhập lại
4. KHI thực hiện thao tác quan trọng THÌ hệ thống SẼ xác thực quyền admin
5. KHI logout THÌ hệ thống SẼ xóa session và redirect về trang chủ

### Yêu cầu 8: Responsive Design

**User Story:** Là một quản trị viên, tôi muốn sử dụng admin panel trên các thiết bị khác nhau, để tôi có thể quản lý website mọi lúc mọi nơi.

#### Tiêu chí chấp nhận

1. KHI truy cập trên desktop THÌ hệ thống SẼ hiển thị sidebar cố định bên trái
2. KHI truy cập trên tablet THÌ hệ thống SẼ điều chỉnh layout phù hợp với màn hình
3. KHI truy cập trên mobile THÌ hệ thống SẼ có sidebar có thể thu gọn
4. KHI thay đổi kích thước màn hình THÌ giao diện SẼ tự động điều chỉnh
5. KHI sử dụng trên touch device THÌ các nút và link SẼ có kích thước phù hợp để touch

### Yêu cầu 9: Tích hợp với Hệ thống Hiện tại

**User Story:** Là một developer, tôi muốn admin panel tích hợp mượt mà với hệ thống hiện tại, để không ảnh hưởng đến các chức năng đã có.

#### Tiêu chí chấp nhận

1. KHI tích hợp THÌ hệ thống SẼ sử dụng cấu trúc MVC hiện có
2. KHI load admin pages THÌ hệ thống SẼ sử dụng master.php layout với admin sidebar
3. KHI truy cập database THÌ hệ thống SẼ sử dụng core/database.php hiện có
4. KHI xử lý URL THÌ hệ thống SẼ sử dụng UrlBuilder.php hiện có
5. KHI sử dụng functions THÌ hệ thống SẼ tận dụng core/functions.php hiện có

### Yêu cầu 10: Quản lý Dữ liệu Fake

**User Story:** Là một developer, tôi muốn sử dụng dữ liệu fake để demo, để có thể test các chức năng mà không cần database thật.

#### Tiêu chí chấp nhận

1. KHI load dữ liệu THÌ hệ thống SẼ đọc từ app/views/admin/data/fake_data.json
2. KHI cập nhật dữ liệu THÌ hệ thống SẼ ghi lại vào fake_data.json
3. KHI fake_data.json không tồn tại THÌ hệ thống SẼ tạo file với dữ liệu mặc định
4. KHI đọc JSON THÌ hệ thống SẼ xử lý lỗi nếu file bị corrupt
5. KHI ghi JSON THÌ hệ thống SẼ đảm bảo format đúng và có backup