# Kế hoạch Chuyển đổi Database: JSON sang MySQL

## Mục tiêu

Chuyển đổi toàn bộ hệ thống lưu trữ dữ liệu từ các file JSON tĩnh sang cơ sở dữ liệu MySQL thông qua thư viện PDO. Xây dựng một lớp Wrapper (Database Class) để thao tác với SQL dễ dàng, giống phong cách jQuery/Query Builder hiện đại.

## Yêu cầu người dùng review

IMPORTANT

 **Backup Dữ liệu** : Trước khi chạy Seeder ở Phase 3, hãy backup toàn bộ folder `app/data` chứa các file JSON hiện tại.  **Thông tin Database** : Cần cập nhật đúng thông tin host, username, password, dbname trong

config.php.

## Kiến trúc đề xuất

### 1. Database Wrapper (

core/Database.php)

Sử dụng **PDO** (PHP Data Objects) để kết nối an toàn. Xây dựng theo **Singleton Pattern** để chỉ có 1 kết nối duy nhất. Tích hợp **Query Builder** đơn giản:

<pre><div node="[object Object]" class="relative whitespace-pre-wrap word-break-all my-2 rounded-lg bg-list-hover-subtle border border-gray-500/20"><div class="min-h-7 relative box-border flex flex-row items-center justify-between rounded-t border-b border-gray-500/20 px-2 py-0.5"><div class="font-sans text-sm text-ide-text-color opacity-60">php</div><div class="flex flex-row gap-2 justify-end"><div class="cursor-pointer opacity-70 hover:opacity-100"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" class="lucide lucide-copy h-3.5 w-3.5"><rect width="14" height="14" x="8" y="8" rx="2" ry="2"></rect><path d="M4 16c-1.1 0-2-.9-2-2V4c0-1.1.9-2 2-2h10c1.1 0 2 .9 2 2"></path></svg></div></div></div><div class="p-3"><div class="w-full h-full text-xs cursor-text"><div class="code-block"><div class="code-line" data-line-number="1" data-line-start="1" data-line-end="1"><div class="line-content"><span class="mtk1">// Ví dụ thay vì viết SQL thuần:</span></div></div><div class="code-line" data-line-number="2" data-line-start="2" data-line-end="2"><div class="line-content"><span class="mtk1">$users = $db->query("SELECT * FROM users WHERE active = 1");</span></div></div><div class="code-line" data-line-number="3" data-line-start="3" data-line-end="3"><div class="line-content"><span class="mtk1"></span></div></div><div class="code-line" data-line-number="4" data-line-start="4" data-line-end="4"><div class="line-content"><span class="mtk1">// Có thể viết kiểu "jQuery-like":</span></div></div><div class="code-line" data-line-number="5" data-line-start="5" data-line-end="5"><div class="line-content"><span class="mtk1">$users = $db->table('users')->where('active', 1)->get();</span></div></div><div class="code-line" data-line-number="6" data-line-start="6" data-line-end="6"><div class="line-content"><span class="mtk1">$user = $db->table('users')->find(1);</span></div></div><div class="code-line" data-line-number="7" data-line-start="7" data-line-end="7"><div class="line-content"><span class="mtk1">$db->table('products')->insert(['name' => 'ABC', 'price' => 100]);</span></div></div></div></div></div></div></pre>

### 2. Cấu trúc thư mục mới

* core/Database.php: Class xử lý chính.
* `database/migrations/`: Chứa các file SQL tạo bảng (VD: `001_create_users_table.sql`).
* `database/seeders/`: Chứa các class PHP để convert JSON sang SQL.
* `scripts/`: Chứa file `migrate.php` và `seed.php` để chạy lệnh.

## Lộ trình thực hiện (5 Phases)

### Phase 1: Core & Configuration

Thiết lập nền móng.

* **File** : `config.php` (Thêm config DB)
* **File** : `core/Database.php` (Viết class xử lý)
* **Hành động** : Đảm bảo kết nối thành công tới MySQL.

### Phase 2: Migration System (Cấu trúc DB)

Xây dựng khung xương cho Database. Thay vì tạo 1 file SQL khổng lồ, ta chia nhỏ để dễ quản lý.

* **Công cụ** : Script `scripts/migrate.php` để tự động chạy các file sql mới trong folder migrations.
* **Migrations** :
* `users` (id, username, password, email, role, ...)
* `categories` (id, name, slug, description, ...)
* `products` (id, category_id, name, price, image, content, ...)
* `orders` & `order_items`
* `settings`, `news`, `contact`...

### Phase 3: Seeder (Convert Data)

Chuyển dữ liệu "thật" từ JSON sang MySQL để hệ thống hoạt động ngay với dữ liệu cũ.

* **Công cụ** : Script `scripts/seed.php`.
* **Logic** : Đọc file `app/data/*.json`, loop qua từng item và insert vào bảng tương ứng trong MySQL.

### Phase 4: Refactoring (Cập nhật Code)

Sửa code PHP hiện tại để ngừng đọc JSON và bắt đầu đọc từ DB.

* **Ưu tiên** : Sửa các file Model trong `app/models/`.
* Thực hiện cuốn chiếu: Sửa xong `UsersModel` -> Test -> Sửa tiếp `ProductsModel`.

### Phase 5: Testing & Verification

Kiểm tra toàn diện.

* **Automated** : Chạy thử các script migrate/seed không lỗi.
* **Manual** :
* Đăng nhập/Đăng ký.
* Xem danh sách sản phẩm, chi tiết, danh mục.
* Đặt hàng thử.
* Vào Admin Panel thêm/sửa/xóa sản phẩm.

## Kế hoạch kiểm thử (Verification Plan)

* **Script Test** : `php scripts/migrate.php` -> Phải báo thành công, check phpMyAdmin thấy đủ bảng. `php scripts/seed.php` -> Phải báo thành công, check bảng thấy có dữ liệu từ JSON.
* **Chức năng chính** :

1. Trang chủ load được sản phẩm hot?
2. Admin đăng nhập được không?
3. Tạo đơn hàng mới có lưu vào bảng `orders` không?
