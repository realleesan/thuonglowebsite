# CẤU TRÚC CHI TIẾT TỪNG FILE TRONG HỆ THỐNG (GRANULAR FILE STRUCTURE)

Tài liệu này mô tả chi tiết vị trí và nhiệm vụ của từng file trong dự án Thuonglo.com để bạn có thể bắt tay vào code ngay lập tức.

---

## 1. CÂY THƯ MỤC CHI TIẾT (FULL TREE)

```text
thuongloWebsite/
├── .htaccess                       # Chặn truy cập trái phép vào các folder con
├── config/
│   ├── app.php                     # Tên app, URL gốc, múi giờ
│   ├── database.php                # Thông tin kết nối MySQL (host, user, pass)
│   ├── auth.php                    # Cấu hình session, thời gian sống cookie
│   └── payment.php                 # API Key Sepay, số tài khoản nhận tiền
├── core/                           # Framework lõi tự xây dựng
│   ├── App.php                     # Khởi tạo ứng dụng
│   ├── Router.php                  # Phân tích URL (VD: /product/1 -> ProductController)
│   ├── Controller.php              # Lớp cha của các Controller
│   ├── Model.php                   # Lớp cha của các Model (Chứa PDO)
│   ├── Request.php                 # Xử lý dữ liệu GET, POST, Sanitize input
│   ├── Response.php                # Điều hướng, render view, trả JSON
│   └── Session.php                 # Quản lý phiên làm việc & Bảo mật thiết bị
├── app/
│   ├── Controllers/
│   │   ├── HomeController.php      # Quản lý Trang chủ, Giới thiệu
│   │   ├── AuthController.php      # Login, Register, Logout, Reset Pass
│   │   ├── ProductController.php   # List SP, Chi tiết SP, Tìm kiếm
│   │   ├── OrderController.php     # Giỏ hàng, Checkout, Webhook thanh toán
│   │   ├── AffiliateController.php # Dashboard đại lý, tạo link rút gọn
│   │   ├── CustomerController.php  # Trang cá nhân KH, SP đã mua
│   │   └── Admin/
│   │       ├── DashboardController.php
│   │       ├── UserController.php
│   │       └── ProductManagerController.php
│   └── Models/
│       ├── User.php                # Query bảng users
│       ├── Product.php             # Query bảng products
│       ├── Order.php               # Query bảng orders, payment_logs
│       ├── Affiliate.php           # Query bảng affiliate_logs, commissions
│       └── Security.php            # Query bảng security_logs
├── helpers/
│   ├── functions.php               # Các hàm global dùng toàn hệ thống
│   ├── SecurityHelper.php          # Xử lý Watermark, Chống copy, Hash
│   ├── FormatHelper.php            # Format tiền VNĐ, format ngày tháng
│   └── UploadHelper.php            # Xử lý upload ảnh sản phẩm & resize
├── views/
│   ├── layouts/                    # Các phần dùng chung
│   │   ├── header.php
│   │   ├── footer.php
│   │   ├── sidebar_admin.php
│   │   └── main.php                # Layout tổng hợp (Master layout)
│   ├── auth/
│   │   ├── login.php
│   │   └── register.php
│   ├── home/
│   │   └── index.php
│   ├── products/
│   │   ├── list.php
│   │   └── detail.php              # Chứa logic hiển thị data trả phí
│   ├── customer/
│   │   ├── dashboard.php
│   │   └── my_products.php
│   ├── agent/                      # Giao diện cho Đại lý
│   │   ├── dashboard.php
│   │   └── statistics.php
│   └── admin/                      # Giao diện cho Quản trị
│       ├── users/index.php
│       └── orders/index.php
├── public/                         # THƯ MỤC GỐC KHI CÀI TRÊN HOSTING
│   ├── index.php                   # File duy nhất xử lý mọi request
│   ├── .htaccess                   # RewriteEngine On (Quan trọng)
│   ├── assets/
│   │   ├── css/
│   │   │   ├── style.css           # CSS chính
│   │   │   └── mobile.css          # CSS cho responsive
│   │   ├── js/
│   │   │   ├── app.js              # JS chung
│   │   │   ├── payment.js          # JS xử lý check trạng thái QR tự động
│   │   │   └── anti-copy.js        # JS chặn chuột phải, Ctrl+U
│   │   ├── img/
│   │   └── vendor/                 # Bootstrap, jQuery, FontAwesome
│   └── uploads/                    # Ảnh sản phẩm
└── database/
    └── init.sql                    # File khởi tạo Database
```

---

## 2. CHI TIẾT MỘT SỐ FILE QUAN TRỌNG

### 2.1. `public/.htaccess`
Đây là file "cửa ngõ" để biến URL từ `index.php?controller=product&id=1` thành `/product/1`.
```apache
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php?url=$1 [QSA,L]
```

### 2.2. `core/Session.php` (Nhiệm vụ bảo mật)
File này sẽ chứa hàm `checkDevice()`:
- Lấy `session_id()` và `user_agent`.
- So sánh với `current_device_id` trong DB.
- Nếu sai, sẽ xóa session và đá user về trang Login để bảo vệ dữ liệu khách hàng.

### 2.3. `helpers/SecurityHelper.php` (Nhiệm vụ chống bán lại)
Chứa hàm tạo Watermark:
```php
function injectWatermark($textContent, $userId) {
    $watermark = "<div class='wm'>User ID: $userId - thuonglo.com</div>";
    // Inject vào các đoạn ngẫu nhiên trong nội dung trả phí
    return str_replace("</p>", "$watermark</p>", $textContent);
}
```

---

## 3. QUY TRÌNH PHÁT TRIỂN TIẾP THEO

1.  **Bước 1:** Tạo cấu trúc thư mục như trên.
2.  **Bước 2:** Viết các file trong `core/` để hình thành bộ khung Framework.
3.  **Bước 3:** Tạo Database từ `database/init.sql`.
4.  **Bước 4:** Code chức năng Auth (Đăng ký/Đăng nhập) và Affiliate (Gắn Cookie).
5.  **Bước 5:** Code chức năng Sản phẩm và Thanh toán.

---
*Cấu trúc này được tối ưu cho việc bảo trì lâu dài, giúp bạn dễ dàng tìm lỗi ở từng lớp (Layer) cụ thể.*
