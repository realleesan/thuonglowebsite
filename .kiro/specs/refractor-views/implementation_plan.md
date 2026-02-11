# Kế hoạch Triển khai: Chuẩn hóa Toàn diện View

Kế hoạch này bao phủ toàn bộ 13 module trong `app/views` (từ Home đến Admin/Affiliate) để 2 thành viên có thể triển khai song song.

## 1. Tại sao website bị lỗi trắng trang? (Nhắc lại để TV nắm rõ)

Lỗi xảy ra do sự chồng chéo khi nạp file qua `index.php`:

* **Lỗi 1:** Định nghĩa hàm trùng tên (`img_url`) khi nạp nhiều View.
* **Lỗi 2:** Nạp lại `config.php` làm sập hàm môi trường.
* **Lỗi 3:** Khởi tạo `new Service` bừa bãi trong View gây cạn kiệt tài nguyên hoặc xung đột Model.

**Cách fix (Mẫu từ home.php):** Dùng `if (!function_exists)` cho hàm và `if (!isset($service))` cho Service.

## 2. Chiến lược thực hiện

### Bước 1: Tạo "Záp Sắt" cho View (`core/view_init.php`)

Đây là file quan trọng nhất. Tất cả các View khác chỉ cần thêm 1 dòng này ở đầu: `<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/core/view_init.php'; ?>`

### Bước 2: Phân chia 2 Thành viên (Parallel Work)

#### Nhóm 1 (Kinh doanh & Vận hành): Thành viên 1

* **Focus:** Các module tạo ra doanh thu và quản lý tiền.
* **Danh sách folder:** `home/`, `products/`, `categories/`, `payment/`, `affiliate/`.
* **Admin tương ứng:** Các trang quản lý kho, đơn hàng, tiền nong trong `admin/`.

#### Nhóm 2 (Nội dung & Người dùng): Thành viên 2

* **Focus:** Các module tương tác người dùng và tin tức.
* **Danh sách folder:** `news/`, `events/`, `about/`, `contact/`, `auth/`, `users/`.
* **Admin tương ứng:** Các trang quản lý bài viết, banner, phân quyền trong `admin/`.

## 3. Danh sách kiểm tra chi tiết cho mỗi Phase

| Giai đoạn               | Thành viên 1                                   | Thành viên 2                                          |
| ------------------------- | ------------------------------------------------ | ------------------------------------------------------- |
| **GĐ 1: Public**   | Fix home, product, list sản phẩm, thanh toán. | Fix tin tức, sự kiện, trang giới thiệu, liên hệ. |
| **GĐ 2: Personal** | Fix 14 file Affiliate (đối tác kiếm tiền).  | Fix 5 file Auth + 19 file User Profile.                 |
| **GĐ 3: Admin**    | Fix các module Kho, Đơn hàng, Affiliate.     | Fix module Bài viết, Phân quyền, User, Cấu hình.  |
| **GĐ 4: Layout**   | Chuẩn hóa Header, Footer, Sidebar, Breadcrumb. | Chuẩn hóa Pagination, News_related, Pusher.           |

## 4. Công cụ hỗ trợ

* File `tests/check_files.php`: Dùng để quét lỗi cú pháp tự động sau mỗi Phase.
* File `tests/emergency_debug.php`: Dùng để ép hiện lỗi nếu trang vẫn bị trắng.
