# TÀI LIỆU ĐẶC TẢ VÀ PHÂN TÍCH NGHIỆP VỤ HỆ THỐNG THUONGLO.COM

## 1. MỤC TIÊU DỰ ÁN (PROJECT OBJECTIVES)

Xây dựng nền tảng thương mại điện tử chuyên biệt để bán các gói dữ liệu (data nguồn hàng) và dịch vụ logistics. Trọng tâm là sự **tự động hóa** (thanh toán - mở khóa) và **bảo mật dữ liệu** (chống copy/bán lại).

---

## 2. NHÓM NGƯỜI DÙNG (USER GROUPS)

### A. Khách hàng lẻ (End-User)

- **Hành vi:** Xem sản phẩm public -> Đăng ký/Đăng nhập (bắt buộc mã giới thiệu) -> Mua gói -> Thanh toán QR -> Xem dữ liệu đã mở khóa.
- **Quyền hạn:** Truy cập các data đã mua, quản lý thông tin cá nhân, ghi chú/yêu thích trên data.

### B. Đại lý/Affiliate (Agent)

- **Hành vi:** Đăng ký tài khoản đại lý -> Lấy link giới thiệu (AFF Link) -> Chia sẻ -> Theo dõi doanh số/hoa hồng trên Dashboard.
- **Quyền hạn:** Có Dashboard riêng, quản lý danh sách khách hàng đã giới thiệu, xem trạng thái hoa hồng.

### C. Quản trị viên (Admin)

- **Hành vi:** Quản lý sản phẩm, duyệt đơn hàng (nếu cần thủ công), quản lý đại lý, cấu hình hệ thống (tỷ lệ hoa hồng, bật/tắt mã GT).
- **Quyền hạn:** Toàn quyền hệ thống, xem Audit Log.

---

## 3. SITEMAP WEBSITE

### 3.1. Trang Công cộng (Public)

- **Home:** Giới thiệu dịch vụ, sản phẩm nổi bật.
- **Shop/Sản phẩm:** Danh sách các gói data (dạng Grid/List).
- **Chi tiết sản phẩm (Lock):** Hiển thị mô tả sơ lược, giá, nút "Mua ngay".
- **Đăng ký / Đăng nhập:** Form tích hợp (SĐT, Password, Mã giới thiệu).

### 3.2. Trang Khách hàng (User Workspace)

- **Dashboard KH:** Danh sách sản phẩm đang sở hữu.
- **Chi tiết sản phẩm (Unlock):** Nội dung data thật, có Watermark động, tính năng Note/Favorite.
- **Giỏ hàng & Thanh toán:** Trang quét mã QR.
- **Lịch sử giao dịch:** Trạng thái đơn hàng.

### 3.3. Trang Đại lý (Affiliate Dashboard)

- **Tổng quan:** Doanh số tổng, hoa hồng hiện tại, số KH đã giới thiệu.
- **Marketing Tools:** Lấy Link AFF / Mã QR giới thiệu.
- **Báo cáo:** Danh sách đơn hàng từ link giới thiệu, trạng thái đối soát.

### 3.4. Quản trị (Admin Panel)

- **Quản lý SP:** Tạo/Sửa gói data, set giá.
- **Quản lý User:** KH và Đại lý.
- **Quản lý Đơn hàng:** Xác nhận thanh toán (tích hợp API tự động).
- **Cấu hình:** Rule hoa hồng, Log hệ thống.

---

## 4. CÁC LUỒNG NGHIỆP VỤ CHÍNH (KEY BUSINESS FLOWS)

### 4.1. Luồng Mua hàng & Mở khóa tự động

1. KH chọn gói SP -> Giỏ hàng.
2. Checkout -> Hệ thống tạo mã QR (kèm nội dung chuyển khoản định danh).
3. KH thanh toán -> Webhook (từ ngân hàng/Sepay) gửi tín hiệu về Backend.
4. Backend xác nhận mã đơn hàng -> Cập nhật trạng thái `Success`.
5. Hệ thống tự động cấp quyền truy cập (không can thiệp tay).
6. KH nhận thông báo và xem được data ngay lập tức.

### 4.2. Luồng Affiliate (Ghi nhận doanh số)

1. Đại lý gửi Link `thuonglo.com?ref=ID_DAILY`.
2. KH click link -> Hệ thống lưu Cookie/Session (30 ngày hoặc vĩnh viễn tùy cấu hình).
3. KH đăng ký tài khoản -> Hệ thống tự động điền mã giới thiệu từ Cookie.
4. KH mua hàng -> Hệ thống check mã reference -> Trích hoa hồng cho Đại lý ứng với đơn hàng đó.

---

## 5. ĐẶC TẢ BẢO MẬT & CHỐNG BÁN LẠI (CRITICAL)

### 5.1. Bảo mật tài khoản

- **Thiết bị:** Giới hạn 1-2 thiết bị đăng nhập cùng lúc. Một user login mới sẽ logout user cũ.
- **IP Tracking:** Ghi log IP và User-Agent. Nếu phát hiện IP xa nhau trong thời gian ngắn -> Flag "Nghi vấn" (Hạn chế quyền xem data nâng cao).

### 5.2. Bảo vệ nội dung Data

- **Watermark động:**
  - Render chìm (dưới văn bản) hoặc chồng (opacity thấp) thông tin: `[User_ID] - [SĐT] - [Time]`.
  - Watermark này thay đổi theo thời gian thực mỗi khi load trang.
- **Anti-Scraping:**
  - Không cung cấp tính năng "Export Excel/PDF" cho toàn bộ database.
  - Data hiển thị theo phân trang (Pagination) nhỏ.
  - API trả dữ liệu theo từng lớp: Chỉ trả field nhạy cảm (VD: SĐT nhà cung cấp) khi user đã mua gói đó.
- **Lý thuyết tầng dữ liệu:**
  - Lớp 1 (Public): Tên SP, mô tả sơ lược.
  - Lớp 2 (Member): Thông tin chi tiết hơn.
  - Lớp 3 (Paid): Dữ liệu "vàng" (Nguồn hàng, giá gốc, link liên hệ...).

---

## 6. YÊU CẦU KỸ THUẬT GIAI ĐOẠN ĐẦU (MVP)

- **Backend:** Ưu tiên xử lý Logic (PHP).
- **Database:** MySQL (Thiết kế Schema hỗ trợ phân quyền theo row/field).
- **Frontend:** Responsive (Mobile là hướng tiếp cận chính vì KH thường dùng điện thoại quét mã).
- **Integration:** API Ngân hàng/Cổng thanh toán QR.

---

*Ghi chú: Tài liệu này là bản dịch thuật và phân tích từ yêu cầu của khách hàng sang ngôn ngữ kỹ thuật để đội ngũ dễ dàng triển khai.*
