# BÁO CÁO KẾT THÚC GIAI ĐOẠN 1: KHỞI ĐỘNG & PHÂN TÍCH NGHIỆP VỤ

Tài liệu này tổng hợp toàn bộ các quyết định về nghiệp vụ, kỹ thuật và luồng vận hành của dự án **THUONGLO.COM** sau khi kết thúc giai đoạn phân tích.

---

## 1. CHỐT SITEMAP TỔNG THỂ

Hệ thống được tổ chức thành 4 phân vùng giao diện chính:

### 1.1. Phân vùng Công cộng (Public)
*   **Trang chủ:** Giới thiệu tổng quan về nguồn hàng và dịch vụ.
*   **Danh sách sản phẩm:** Lưới các gói dữ liệu (Có phân loại theo Ngành hàng).
*   **Chi tiết sản phẩm (Bản Lock):** Hiển thị mô tả, giá, và nút "Mua ngay".
*   **Tin tức/Hướng dẫn:** Các bài viết về nhập hàng, thanh toán quốc tế.
*   **Hỗ trợ:** Trang liên hệ/về chúng tôi.

### 1.2. Phân vùng Khách hàng (User Workspace)
*   **Dashboard Khách hàng:** Quản lý sản phẩm đã mua, lịch sử nạp tiền.
*   **Chi tiết sản phẩm (Bản Unlock):** Nội dung nguồn hàng thực tế + Watermark định danh.
*   **Thanh toán:** Trang QR Code và kiểm tra trạng thái thanh toán tự động.
*   **Cá nhân:** Quản lý tài khoản, mật khẩu, danh sách yêu thích.

### 1.3. Phân vùng Đại lý (Affiliate Portal)
*   **Dashboard Đại lý:** Thống kê doanh số, số khách hàng đã giới thiệu.
*   **Công cụ Marketing:** Lấy link giới thiệu (REF Link) và mã QR giới thiệu.
*   **Lịch sử hoa hồng:** Trạng thái tiền về và lịch sử đối soát.

### 1.4. Phân vùng Quản trị (Admin Panel)
*   **Quản lý Nội dung:** CRUD sản phẩm, danh mục, tin tức.
*   **Quản lý User:** Quản trị khách hàng và đại lý.
*   **Quản lý Giao dịch:** Theo dõi doanh thu, thủ công xác nhận (nếu cần).
*   **Cấu hình:** Rule hoa hồng, mã giới thiệu, log bảo mật.

---

## 2. PHÂN TÍCH VÀ CHỐT LUỒNG NGHIỆP VỤ

### 2.1. Luồng Khách hàng (Customer Journey)
`Vào web` -> `Đăng ký (Bắt buộc mã GT)` -> `Chọn gói SP` -> `Thanh toán QR` -> `Tự động mở khóa` -> `Xem dữ liệu có Watermark`.

### 2.2. Luồng Đại lý (Affiliate Journey)
`Lấy link REF` -> `Chia sẻ cho KH` -> `KH click link (Gắn Cookie vĩnh viễn)` -> `KH mua hàng` -> `Hệ thống cộng hoa hồng tự động` -> `Đại lý rút tiền/đối soát`.

### 2.3. Luồng Quản trị (Admin Journey)
`Đăng sản phẩm` -> `Cấu hình tỷ lệ hoa hồng` -> `Giám sát Log (Security/Payment)` -> `Xử lý khiếu nại/Hỗ trợ khách hàng`.

---

## 3. CHỐT QUY TẮC NGHIỆP VỤ CỐT LÕI

### 3.1. Quy tắc Mở khóa Sản phẩm (Auto-Unlock)
*   **Tín hiệu:** Thông qua Webhook từ Sepay (API Ngân hàng).
*   **Điều kiện:** Mã đơn hàng (VD: TL12345) trong nội dung chuyển khoản phải khớp với bản ghi trong Database.
*   **Hành động:** Hệ thống cập nhật `status = Success` trong bảng `orders` và ngay lập tức cấp quyền truy cập vào `content_protected` cho user.

### 3.2. Quy tắc Mã giới thiệu (Referral Rule)
*   **Độ ưu tiên:** Click Link REF (Cookie) > Nhập mã tay khi đăng ký.
*   **Thời hạn:** Cookie gắn khách hàng với đại lý là **Vĩnh viễn** sau lần mua/đăng ký đầu tiên.
*   **Phân cấp:** Chỉ áp dụng 1 cấp độ đại lý để đảm bảo tính minh bạch và dễ quản trị.

### 3.3. Quy tắc Hoa hồng Đại lý (Commission Rule)
*   **Tỷ lệ:** Cấu hình linh hoạt theo từng gói sản phẩm hoặc cấu hình chung (Ví dụ: 10% - 30%).
*   **Thời điểm tính:** Ngay khi đơn hàng chuyển sang trạng thái `Success`.
*   **Trạng thái:** `Chờ đối soát` -> `Đã thanh toán`.

---

## 4. CHỐT PHƯƠNG ÁN CÔNG NGHỆ TRIỂN KHAI

*   **Kiến trúc:** Semi-MVC Modular (Tối ưu cho bảo trì và bảo mật).
*   **Backend:** PHP 7.4+ (Thuần kết hợp Class Core) đảm bảo tốc độ phản hồi nhanh nhất.
*   **CSDL:** MySQL (PDO Engine) hỗ trợ giao dịch (Transactions) trong thanh toán.
*   **Bảo mật:**
    *   **Watermark:** Render phía Server (PHP) kết hợp CSS Overlay.
    *   **Anti-Copy:** JavaScript mã hóa + Chặn chuột phải/phím tắt.
    *   **Session Security:** Check vân tay thiết bị (Device Fingerprint) để chống dùng chung tài khoản.
*   **Tích hợp:** Sepay Webhook API cho tự động hóa thanh toán.

---
**Kết luận:** Giai đoạn 1 đã hoàn thành đầy đủ các chỉ số phân tích. Hệ thống đã sẵn sàng bước vào Giai đoạn 2 (Thiết kế UI/UX và Wireframe).
