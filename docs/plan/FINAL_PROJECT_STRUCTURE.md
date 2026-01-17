# FINAL BLUEPRINT & PROJECT TREE - THUONGLO.COM (SEMI-MVC)

Đây là tài liệu tổng kết cấu trúc file cuối cùng dựa trên hệ thống thực tế bạn đã xây dựng. Cấu trúc này tối ưu cho việc bảo lưu logic nghiệp vụ, bảo mật dữ liệu và triển khai tự động hóa.

## 1. SƠ ĐỒ CẤU TRÚC CHI TIẾT (FINAL PROJECT TREE)

```text
thuongloWebsite/
├── index.php                       # Front Controller (Cửa ngõ duy nhất)
├── config.php                      # File cấu hình (DB, App Key, Sepay Key)
├── api.php                         # Dispatcher dành cho API/AJAX/Webhook
├── .htaccess                       # Điều hướng URL đẹp & Bảo mật folder
│
├── core/                           # LÕI HỆ THỐNG (CORE LOGIC)
│   ├── database.php                # Kết nối PDO (Singleton/Static)
│   ├── functions.php               # Các hàm tiện ích (Format, Redirect, etc.)
│   ├── router.php                  # Logic xử lý mvc-routing
│   ├── security.php                # Chống copy, Watermark động, Sanitize
│   └── session.php                 # Quản lý Đăng nhập & Check Fingerprint thiết bị
│
├── app/                            # NGHIỆP VỤ ỨNG DỤNG (APPLICATION LOGIC)
│   ├── controllers/                # ĐIỀU KHIỂN
│   │   ├── AdminController.php     # Quản lý nội bộ (Users, Products, Orders)
│   │   ├── AffiliateController.php # Quản lý Link REF & Hoa hồng đại lý
│   │   ├── AuthController.php      # Xử lý Đăng ký, Đăng nhập, Logout
│   │   ├── OrdersController.php    # Xử lý Đơn hàng, Giỏ hàng
│   │   ├── PaymentController.php   # Xử lý luồng thanh toán QR
│   │   └── UserController.php      # Dashboard & Thông tin cá nhân khách hàng
│   │
│   ├── models/                     # DỮ LIỆU (MODELS)
│   │   ├── AffiliateModel.php      # Query bảng affiliate_logs, commissions
│   │   ├── OrdersModel.php         # Query bảng orders, payment_logs
│   │   ├── ProductsModel.php       # Query bảng products, content_protected
│   │   └── UsersModel.php          # Query bảng users, device_logs
│   │
│   └── views/                      # GIAO DIỆN (VIEWS)
│       ├── _layout/                # Các thành phần tái sử dụng
│       │   ├── header.php, footer.php, sidebar.php, master.php
│       │   ├── pageheader.php, pagination.php, cta.php
│       ├── home/                   # Trang chủ (index.php)
│       ├── about/, contact/        # Trang giới thiệu, liên hệ
│       ├── auth/                   # login.php, register.php, forgot.php, logout.php
│       ├── products/               # list.php, detail.php (có logic check lock/unlock)
│       ├── users/                  # dashboard, account, cart, orders, wishlist
│       ├── affiliate/              # dashboard thống kê đại lý
│       ├── payment/                # qr_checkout.php, success.php
│       ├── news/                   # Tin tức, hướng dẫn
│       └── admin/                  # Dashboard quản trị
│
├── api/                            # XỬ LÝ KHÔNG GIAO DIỆN (JSON ONLY)
│   ├── sepay_webhook.php           # Tự động Confirm thanh toán từ Sepay
│   ├── check_order_status.php      # AJAX gọi từ Frontend để auto-redirect
│   └── api_handler.php             # Xử lý các tác vụ AJAX khác (Wishlist, Cart)
│
├── assets/                         # TÀI NGUYÊN TĨNH (PUBLIC ASSETS)
│   ├── css/, js/, img/, fonts/
│   ├── vendor/                     # Thư viện ngoài (Bootstrap, jQuery...)
│   └── uploads/                    # Ảnh/File sản phẩm (Cần bảo mật cao)
│
├── database/                       # QUẢN TRỊ CƠ SỞ DỮ LIỆU
│   ├── schema/                     # File tables.sql khởi tạo bảng
│   └── migrations/                 # Các file cập nhật DB theo thời gian
│
├── errors/                         # TRANG BÁO LỖI (403, 404, 500)
└── logs/                           # NHẬT KÝ HỆ THỐNG
    ├── error.log, payment.log, security.log
```

---

## 2. ĐÁNH GIÁ TỔNG QUAN (FINAL EVALUATION)

*   **Tính Tổ Chức:** Rất cao. Việc bạn đã tạo sẵn đầy đủ các Controller và Model cho thấy khung logic đã khép kín. Việc tách biệt `app/views/users` ra các submodule nhỏ (`account`, `cart`, `orders`) rất thông minh.
*   **Tính Bảo Mật:** Với file `core/security.php` và `logs/security.log`, hệ thống đã sẵn sàng cho các tính năng bảo mật then chốt (Watermark, chống copy).
*   **Tính Tự Động Hóa:** Thư mục `api/` dành riêng cho Webhook Sepay là phương án tối ưu nhất để thực hiện tính năng "Auto-unlock" sản phẩm.

## 3. LƯU Ý KHI TIẾP TỤC BỔ SUNG
1.  **Watermark:** Nên xử lý tại `core/security.php` và gọi ra trong `app/views/products/detail.php` khi user đã được `OrdersController` xác nhận thanh toán.
2.  **Affiliate:** Logic gắn Cookie/Session mã giới thiệu nên đặt ở `core/session.php` hoặc `AuthController.php` để đảm bảo khách hàng vãng lai được ghi nhận ngay khi click link.
3.  **Routing:** File `core/router.php` cần ánh xạ đúng URL thân thiện (Friendly URL) vào các phương thức (methods) trong Controller.

---
*Dự án hiện tại đang có một nền móng cực kỳ vững chắc và chuẩn mực. Đây là bản Blueprint cuối cùng để bạn làm căn cứ phát triển các tính năng chi tiết.*
