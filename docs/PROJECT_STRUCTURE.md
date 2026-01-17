# CẤU TRÚC THƯ MỤC DỰ ÁN THUONGLO.COM (PROPOSED STRUCTURE)

Để đảm bảo dự án dễ mở rộng, bảo mật và đúng chuẩn MVC (Model-View-Controller) cho PHP, tôi đề xuất cấu trúc tổ chức file như sau:

## 1. SƠ ĐỒ CẤU TRÚC (DIRECTORY TREE)

```text
thuongloWebsite/
├── config/                 # Cấu hình hệ thống
│   ├── database.php        # Kết nối MySQL
│   ├── config.php          # Hằng số, App Key, API Keys (Sepay, etc.)
│   └── constants.php       # Định nghĩa các Role, Trạng thái đơn hàng
├── core/                   # Các lớp core xử lý hệ thống
│   ├── Database.php        # Lớp tương tác DB (PDO)
│   ├── Controller.php      # Base Controller
│   ├── Auth.php            # Xử lý Login, Session, Device ID check
│   └── Router.php          # Xử lý đường dẫn thân thiện (Friendly URL)
├── app/                    # Logic nghiệp vụ (Controllers & Models)
│   ├── Controllers/
│   │   ├── AuthController.php
│   │   ├── ProductController.php
│   │   ├── OrderController.php
│   │   ├── AffiliateController.php
│   │   └── Admin/          # Controller cho Dashboard admin
│   └── Models/
│       ├── User.php
│       ├── Product.php
│       ├── Order.php
│       └── Affiliate.php
├── views/                  # Giao diện (HTML/PHP)
│   ├── layouts/            # Header, Footer dùng chung
│   ├── home/               # Trang chủ
│   ├── products/           # Danh sách & Chi tiết SP
│   ├── customer/           # Dashboard khách hàng
│   ├── agent/              # Dashboard đại lý
│   └── admin/              # Giao diện quản trị
├── public/                 # Thư mục gốc web (Chứa file truy cập trực tiếp)
│   ├── index.php           # Entry point duy nhất
│   ├── assets/             # Tài nguyên tĩnh
│   │   ├── css/
│   │   ├── js/
│   │   ├── img/
│   │   └── fonts/
│   ├── .htaccess           # Cấu hình Redirect cho Router
│   └── uploads/            # Chứa ảnh sản phẩm (cần bảo mật)
├── helpers/                # Các hàm tiện ích
│   ├── format_helper.php   # Định dạng tiền tệ, ngày tháng
│   ├── security_helper.php # Hàm băm (hash), XSS filter
│   └── watermark_helper.php # Logic chèn watermark vào content/image
├── vendor/                 # Thư viện bên thứ 3 (nếu dùng Composer)
├── docs/                   # Tài liệu dự án (Hiện có)
├── logs/                   # Chứa file log IP, lỗi hệ thống (Cần chmod bảo mật)
└── database/               # Chứa các file .sql backup
```

---

## 2. GIẢI THÍCH CHI TIẾT CÁC PHẦN CHÍNH

### 2.1. Thư mục `public/` (Web Root)
- Đây là thư mục duy nhất khách hàng có thể truy cập từ trình duyệt.
- File `index.php` sẽ nhận mọi request và chuyển hướng về `Router` để xử lý. Điều này giúp ngăn chặn việc truy cập trực tiếp vào các file logic `.php` bên trong.

### 2.2. Thư mục `core/` & `app/`
- Tách biệt logic xử lý dữ liệu (Models) và kiểm soát luồng (Controllers).
- **Security:** `Auth.php` trong core sẽ được gọi ở mọi trang nhạy cảm để check `device_id` và `session`.

### 2.3. Thư mục `helpers/`
- **watermark_helper.php:** Sẽ chứa các hàm PHP GD để chèn thông tin User vào ảnh hoặc các đoạn mã JS để chèn "Invisible Watermark" vào trang nội dung đã mở khóa.

### 2.4. Thư mục `config/`
- Lưu trữ thông tin nhạy cảm. Cần đảm bảo file `.htaccess` ở thư mục gốc chặn mọi truy cập vào thư mục này.

---

## 3. LƯU Ý VỀ LUỒNG XỬ LÝ (WORKFLOW)

1.  **Request:** User truy cập `thuonglo.com/san-pham/nguon-hang-trung-quoc`.
2.  **Routing:** `.htaccess` đẩy request về `public/index.php`.
3.  **Controller:** `ProductController` gọi hàm `detail('nguon-hang-trung-quoc')`.
4.  **Middleware/Auth:** Check xem User đã login chưa? Nếu SP này là trả phí, check xem user đã thanh toán (Table `orders`) chưa?
5.  **View:** 
    - Nếu chưa mua: Trả về view "Khóa" (Lock).
    - Nếu đã mua: Trả về view "Mở" (Unlock) + Gọi `WatermarkHelper` để đè thông tin User lên giao diện.

---

## 4. TÀI NGUYÊN SQL (Database)
- Các bảng chính sẽ gồm: `users`, `products`, `orders`, `affiliate_links`, `commissions`, `device_logs`, `content_logs`.

---
*Đề xuất này hướng tới sự cân bằng giữa khả năng bảo mật của một dự án nhỏ và sự mạch lạc của kiến trúc phần mềm chuyên nghiệp.*
