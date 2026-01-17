# CHI TIẾT CẤU TRÚC LOGIC VÀ CƠ SỞ DỮ LIỆU (TECHNICAL DESIGN DETAIL)

Tài liệu này chi tiết hóa các thành phần kỹ thuật cốt lõi để đáp ứng yêu cầu của khách hàng A Sinh, tập trung vào **Bảo mật**, **Affiliate** và **Tự động hóa**.

---

## 1. THIẾT KẾ CƠ SỞ DỮ LIỆU (DATABASE SCHEMA)

Dự kiến sử dụng MySQL với các bảng chính sau:

### 1.1. Bảng `users` (Quản lý người dùng)
- `id` (INT, PK): ID duy nhất.
- `phone` (VARCHAR, Unique): SĐT đăng nhập.
- `password` (VARCHAR): Hash password (bcrypt).
- `full_name` (VARCHAR): Tên đầy đủ.
- `role` (ENUM): 'customer', 'agent', 'admin'.
- `ref_code` (VARCHAR): Mã giới thiệu riêng (nếu là agent).
- `referred_by` (INT, FK): ID của agent đã giới thiệu user này.
- `current_device_id` (VARCHAR): ID thiết bị đang đăng nhập để kiểm soát số thiết bị.
- `status` (TINYINT): 1: Active, 0: Flagged (Nghi vấn), -1: Banned.

### 1.2. Bảng `products` (Gói sản phẩm/data)
- `id` (INT, PK).
- `title` (VARCHAR): Tên gói.
- `description_short` (TEXT): Mô tả demo (Public).
- `content_protected` (LONGTEXT): Dữ liệu nhạy cảm (Chỉ hiện khi đã mua).
- `price` (DECIMAL): Giá bán.
- `category_id` (INT): Ngành hàng (Quần áo, điện tử, v.v.).

### 1.3. Bảng `orders` (Đơn hàng & Thanh toán)
- `id` (INT, PK).
- `user_id` (INT, FK).
- `product_id` (INT, FK).
- `order_code` (VARCHAR, Unique): Mã đơn hàng (VD: TL12345).
- `amount` (DECIMAL).
- `payment_status` (ENUM): 'pending', 'success', 'failed'.
- `created_at` (TIMESTAMP).
- `activated_at` (TIMESTAMP): Thời điểm hệ thống tự động mở khóa.

### 1.4. Bảng `affiliate_logs` (Ghi nhận hoa hồng)
- `id` (INT, PK).
- `agent_id` (INT, FK): ID người nhận hoa hồng.
- `order_id` (INT, FK): Từ đơn hàng nào.
- `commission_amount` (DECIMAL): Số tiền được hưởng.
- `payout_status` (ENUM): 'unpaid', 'paid'.

### 1.5. Bảng `security_logs` (Bảo mật)
- `id` (INT, PK).
- `user_id` (INT, FK).
- `ip_address` (VARCHAR).
- `user_agent` (TEXT).
- `action` (VARCHAR): 'login', 'access_paid_content', 'failed_payment'.

---

## 2. CHI TIẾT CÁC LOGIC "XƯƠNG SỐNG"

### 2.1. Logic Kiểm soát Thiết bị (Device Management)
Để thực hiện yêu cầu "1 tài khoản 1 người dùng":
- **Cơ chế:** Khi đăng nhập, trình duyệt tạo một vân tay (Fingerprint) hoặc Server tạo một UUID lưu vào Cookie/LocalStorage của user.
- **Backend check:** 
    - Mỗi khi user truy cập nội dung trả phí, so sánh `current_device_id` trong Session với `current_device_id` trong DB.
    - Nếu khác nhau -> Logout thiết bị cũ hoặc yêu cầu verify lại SĐT.

### 2.2. Logic Watermark Động (PHP GD/CSS)
Để chống quay chụp màn hình:
- **Phương án 1 (Overlay):** Dùng CSS `pointer-events: none` tạo một lớp phủ mờ đè lên nội dung với text lặp lại: `User: 0914... - ID: 88 - 17/01/2026`.
- **Phương án 2 (Image Generation):** Nếu dữ liệu là hình ảnh/PDF, dùng thư viện `PHP GD` chèn text trực tiếp vào file trước khi trả về trình duyệt.
- **Cơ chế:** Nội dung watermark được lấy từ thông tin Session của chính người đang xem.

### 2.3. Luồng Thanh toán Sepay (Tự động)
1. User nhấn "Thanh toán" -> Hệ thống tạo đơn hàng `pending`.
2. Hiện mã QR (VietQR) với nội dung: `TL12345` (TL + Mã đơn hàng).
3. Khi User chuyển khoản thành công, Sepay gửi Webhook về `thuonglo.com/api/payment-webhook`.
4. API check:
    - Tìm đơn hàng có mã `TL12345`.
    - Kiểm tra số tiền khớp chưa.
    - Cập nhật `orders.payment_status = 'success'`.
    - Cập nhật quyền truy cập cho User.

---

## 3. CÁC ĐIỂM "DETAIL" TRONG CONTROLLERS

### 3.1. `Controller.php` (Base)
Chứa các hàm dùng chung:
- `jsonResponse($data, $status)`: Trả về JSON chuẩn cho frontend/API.
- `checkPermission($role)`: Kiểm tra quyền truy cập nhanh.

### 3.2. `AuthController.php`
- `register()`: Bắt buộc check `ref_code`. Nếu không có, gán vào agent mặc định của Công ty (ID=1).
- `login()`: Ghi log IP và đánh dấu `device_id`.

### 3.3. `ProductController.php`
- `show($id)`:
    - Nếu `orders` của user này cho `product_id` này là `success` -> Load `content_protected`.
    - Nếu không -> Chỉ load `description_short` và hiện nút "Mua dịch vụ".

---

## 4. CHI TIẾT FRONTEND (UI/UX)
Sử dụng HTML5, CSS3 và Vanilla JS/jQuery:
- **Mobile First:** Mọi bảng dữ liệu (Table) phải có thuộc tính `responsive` để đại lý và khách dễ xem trên điện thoại.
- **Copy Protection:**
    - Vô hiệu hóa chuột phải (Right-click disable).
    - Vô hiệu hóa phím tắt `Ctrl+C` / `Ctrl+U`.
    - (Dù không ngăn chặn hoàn toàn được người rành công nghệ, nhưng sẽ làm nản lòng 90% người dùng thông thường muốn copy data).

---
*Bản chi tiết này đóng vai trò là "Blueprint" để bắt đầu viết những dòng code đầu tiên một cách chính xác.*
