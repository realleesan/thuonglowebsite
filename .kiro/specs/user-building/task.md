# Kế hoạch xây dựng Trang tài khoản Users - ThuongLo

## Tổng quan

Xây dựng hệ thống trang tài khoản users với đầy đủ chức năng quản lý, theo cấu trúc module, đồng bộ với thiết kế hiện tại của website ThuongLo.

## Các module chính

### 1. Dashboard (Tổng quan)  - Done

* [X] Tạo layout chính với sidebar
* [X] Xây dựng dashboard với charts thống kê
* [X] Hiển thị thông tin tổng quan user
* [X] Tích hợp Chart.js cho biểu đồ

### 2. Account (Tài khoản) - Done 

* [X] index.php - Xem thông tin tài khoản
* [X] edit.php - Chỉnh sửa thông tin
* [X] view.php - Xem chi tiết profile
* [X] delete.php - Xóa tài khoản

### 3. Orders (Đơn hàng)

* [x] index.php - Danh sách đơn hàng
* [x] view.php - Chi tiết đơn hàng
* [x] edit.php - Thông báo không thể chỉnh sửa đơn hàng
* [x] delete.php - Hủy đơn hàng






### 4. Cart (Giỏ hàng)

* [ ] index.php - Danh sách giỏ hàng
* [ ] add.php - Thêm vào giỏ
* [ ] edit.php - Cập nhật số lượng
* [ ] view.php - Xem chi tiết
* [ ] delete.php - Xóa khỏi giỏ

### 5. Wishlist (Yêu thích)

* [ ] index.php - Danh sách yêu thích
* [ ] add.php - Thêm sản phẩm
* [ ] view.php - Xem chi tiết
* [ ] edit.php - Chỉnh sửa
* [ ] delete.php - Xóa khỏi danh sách

## Layout & Components

### Layout Files

* [ ] user_sidebar.php - Sidebar điều hướng
* [ ] Tích hợp breadcrumb
* [ ] Sử dụng header/footer chung

### CSS Files

* [ ] user_dashboard.css - Dashboard styling
* [ ] user_sidebar.css - Sidebar styling
* [ ] user_account.css - Account pages
* [ ] user_orders.css - Orders pages
* [ ] user_cart.css - Cart pages
* [ ] user_wishlist.css - Wishlist pages

### JavaScript Files

* [ ] user_dashboard.js - Dashboard logic + Charts
* [ ] user_sidebar.js - Sidebar interactions
* [ ] user_account.js - Account functionality
* [ ] user_orders.js - Orders management
* [ ] user_cart.js - Cart operations
* [ ] user_wishlist.js - Wishlist features

## Data & Integration

### Fake Data

* [ ] user_fake_data.json - Dữ liệu demo đầy đủ

### Routing

* [ ] Cập nhật index.php với routing cho users
* [ ] Xử lý module và action parameters

## Design Requirements

* Đồng bộ màu sắc: #356DF1 (primary), #000000 (hover)
* Font: Inter (như hiện tại)
* Icons: FontAwesome 5.15.4
* Responsive design
* Animations và transitions mượt mà
* Charts: Chart.js cho dashboard
