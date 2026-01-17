# Dự án Website THUONGLO.COM

**THUONGLO.COM** là một nền tảng thương mại điện tử chuyên biệt cung cấp các gói dữ liệu (nguồn hàng), dịch vụ vận chuyển chính ngạch và ủy thác xuất nhập khẩu. Website được thiết kế với trọng tâm là sự tự động hóa quy trình thanh toán - mở khóa nội dung và hệ thống đại lý (Affiliate) mạnh mẽ.

---

## 🚀 Tính năng cốt lõi

### 1. Thương mại điện tử & Tự động hóa
*   **Bán gói sản phẩm:** Cung cấp các gói dữ liệu nguồn hàng đa dạng theo ngành hàng.
*   **Thanh toán tự động:** Tích hợp thanh toán qua QR Code (Sepay/Ngân hàng). 
*   **Auto-Unlock:** Hệ thống tự động mở khóa nội dung dữ liệu ngay sau khi xác nhận thanh toán thành công mà không cần can thiệp thủ công.

### 2. Hệ thống Đại lý (Affiliate)
*   **Cơ chế lưu giữ:** Ghi nhận khách hàng qua link giới thiệu (Affiliate Link) bằng Cookie/Session.
*   **Quản lý hoa hồng:** Đại lý có Dashboard riêng để theo dõi doanh số, danh sách khách hàng và trạng thái hoa hồng vĩnh viễn.

### 3. Bảo mật dữ liệu (Anti-Resell)
*   **Watermark động:** Tự động chèn thông tin định danh người dùng (ID, SĐT, Thời gian) vào nội dung để chống quay phim, chụp ảnh màn hình.
*   **Kiểm soát thiết bị:** Giới hạn số thiết bị đăng nhập cùng lúc và phát hiện đăng nhập bất thường từ IP lạ.
*   **Chống sao chép:** Tích hợp các kỹ thuật chặn Copy, chặn chuột phải và phím tắt kỹ thuật trên Frontend.

---

## 🛠 Tech Stack

*   **Ngôn ngữ chính:** PHP
*   **Kiến trúc:** Semi-MVC (Modular Hybrid)
*   **Cơ sở dữ liệu:** MySQL (PDO)
*   **Frontend:** HTML5, CSS3, JavaScript (Vanilla/jQuery)
*   **Giao diện:** Responsive (Tối ưu hóa cho thiết bị di động)
*   **Web Server:** Apache (cấu hình qua `.htaccess`)

---

## 📁 Cấu trúc thư mục (File Structure)

Dự án được tổ chức theo mô hình **Semi-MVC Modular** giúp tách biệt rõ ràng giữa giao diện, dữ liệu và xử lý log:

```text
thuongloWebsite/
├── index.php           # Front Controller (Cửa ngõ chính)
├── api.php             # Xử lý các yêu cầu AJAX và Webhook
├── config.php          # Cấu hình hệ thống (DB, API Keys)
├── core/               # Thư viện lõi (Database, Session, Security, Router)
├── app/                # Nghiệp vụ ứng dụng
│   ├── controllers/    # Các tệp điều hướng xử lý
│   ├── models/         # Các tệp truy vấn cơ sở dữ liệu
│   └── views/          # Giao diện người dùng (phân theo module)
├── api/                # Các file xử lý logic API ngầm (Sepay Webhook, etc.)
├── assets/             # Tài nguyên tĩnh (CSS, JS, Img, Fonts)
├── database/           # Quản lý Database (Schema, Migrations)
├── docs/               # Tài liệu dự án và hướng dẫn
├── logs/               # Nhật ký hệ thống (Security, Payment)
└── errors/             # Các trang thông báo lỗi (404, 403)
```

---

## 📋 Hướng dẫn cài đặt sơ bộ

1.  **Môi trường:** PHP 7.4+ và MySQL.
2.  **Database:** Import file `database/schema/tables.sql` vào cơ sở dữ liệu của bạn.
3.  **Cấu hình:** Chỉnh sửa thông tin kết nối trong file `config.php`.
4.  **Rewrite:** Đảm bảo module `mod_rewrite` trên Apache đã được bật để nhận cấu hình từ `.htaccess`.

---
*Dự án được thực hiện bởi Misty Team.*
