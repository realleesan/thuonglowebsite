# Danh sách công việc: Chuyển đổi JSON sang MySQL

* [x] **Phase 1: Khởi tạo Core Database**
  * [x] Cập nhật config.php: Thêm thông tin kết nối MySQL
  * [x] Tạo class core/Database.php: Wrapper PDO, Pattern Singleton
  * [x] Implement Query Builder cơ bản: `table()`, `select()`, `where()`, `get()`, `first()`, `insert()`, `update()`, `delete()`
  * [x] Test kết nối Database
* [x] **Phase 2: Hệ thống Migration (Quản lý Schema)**
  * [x] Tạo bảng `migrations` để theo dõi version
  * [x] Tạo script `scripts/migrate.php` để chạy migration
  * [x] Tạo file migration tạo bảng `users`
  * [x] Tạo file migration tạo bảng `products`, `categories`
  * [x] Tạo file migration tạo bảng `orders`, `order_items`
  * [x] Tạo file migration cho các bảng khác (`news`, `settings`, `contact`, etc.)
* [x] **Phase 3: Hệ thống Seeder (Chuyển dữ liệu)**
  * [x] Tạo script `scripts/seed.php` để chạy seeder
  * [x] Viết Seeder: Chuyển data từ `app/data/users.json` -> table `users`
  * [x] Viết Seeder: Chuyển data từ `app/data/products.json` -> table `products`
  * [x] Viết Seeder: Chuyển data từ `app/data/categories.json` -> table `categories`
  * [x] Viết Seeder cho các dữ liệu còn lại
* [x] **Phase 4: Refactoring Models (Kết nối App với DB)**
  * [x] Refactor `UsersModel`: Thay thế code đọc JSON bằng gọi `Database`
  * [x] Refactor `ProductsModel`: Thay thế code đọc JSON bằng gọi `Database`
  * [x] Refactor `OrdersModel`: Thay thế code đọc JSON bằng gọi `Database`
  * [x] Refactor các Controller/Model khác nếu có dùng trực tiếp JSON
* [x] **Phase 5: Chuyển đổi Views từ JSON sang SQL**
  * [x] Chuyển đổi Admin Views: Users, Products, Orders, Categories, News, Settings, Contacts, Affiliates
  * [x] Chuyển đổi User Dashboard và Auth Views
  * [x] Kiểm tra và sửa các hardcode data
  * [x] Tạo scripts kiểm tra và dọn dẹp
  * [x] Backup và chuẩn bị xóa các file JSON cũ
