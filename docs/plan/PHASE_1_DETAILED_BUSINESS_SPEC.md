# BÁO CÁO CHI TIẾT NGHIỆP VỤ GIAI ĐOẠN 1 (XÁC THỰC THEO YÊU CẦU GỐC)

Tài liệu này được rà soát đối chiếu 1:1 với tất cả các tài liệu trao đổi (Job, Bảo mật, Kỹ thuật) để đảm bảo không bỏ sót bất kỳ yêu cầu nào từ khách hàng.

---

## 1. SITEMAP CHI TIẾT & PHÂN LỚP DỮ LIỆU

### 1.1. Cấu trúc trang Sản phẩm (Phân tầng Bảo mật)
Dữ liệu không hiển thị tràn lan mà theo luồng: **Ngành hàng → Phân khúc → Điều kiện → Thời điểm**.
*   **Lớp 1 (Public):** Tên sản phẩm, mô tả chung (dành cho khách vãng lai).
*   **Lớp 2 (Hạn chế):** Thông tin chi tiết hơn (dành cho thành viên đã đăng nhập).
*   **Lớp 3 (Mở khóa - Paid):** Dữ liệu nguồn hàng "nhạy cảm", link NCC, giá gốc. **Bắt buộc có Watermark động** (gồm: User ID, SĐT/Email, Thời gian thực).

### 1.2. Hệ thống Dịch vụ Phụ (Logistics)
Ngoài bán gói data, Sitemap phải có các trang giới thiệu dịch vụ:
*   Vận chuyển chính ngạch.
*   Mua hàng trọn gói (Giao hàng tận nơi).
*   Thanh toán quốc tế.
*   Dịch vụ đánh hàng (Phiên dịch, đi lại, ăn ở).

---

## 2. LUỒNG NGHIỆP VỤ CHUẨN XÁC

### 2.1. Đăng ký & Mã giới thiệu (Bật/Tắt linh hoạt)
*   Hệ thống cho phép Admin **Bật/Tắt** tính năng "Bắt buộc có mã giới thiệu mới được đăng ký".
*   Giao diện đăng ký: SĐT, Password, Mã giới thiệu.
*   Ghi nhận: Cookie/Session ghi nhận link REF của đại lý để tự động điền mã giới thiệu.

### 2.2. Thanh toán & "Nạp tiền"
*   Tài liệu gốc nhắc đến cả: **Thanh toán ngay** và **QR Code - Nạp tiền**.
*   Luồng mở khóa: Phải là tự động 100% qua tín hiệu ngân hàng (Sepay Webhook), không có nút mở khóa "bằng tay" để tránh gian lận.

### 2.3. Dashboard Đại lý (Agent Portal)
Phải hiển thị đủ:
*   Danh sách khách hàng đã sale thành công.
*   Doanh số tổng & Doanh số theo tuần/tháng.
*   Tình trạng thanh toán hoa hồng.
*   Công cụ lấy 1 Link Affiliate duy nhất đã gắn ID.

---

## 3. QUY TẮC BẢO MẬT TUYỆT ĐỐI (TECHNICAL RULES)

### 3.1. Quản lý Phiên & Thiết bị
*   **Giới hạn thiết bị:** Mỗi tài khoản dùng cho 1 cá nhân/nhóm hợp lệ.
*   **Logout khi login mới:** Login máy sau sẽ tự động đẩy máy trước ra.
*   **Detect IP:** Nếu đăng nhập ở nhiều nơi xa nhau trong thời gian ngắn -> **Gắn trạng thái Flag**. 
    - *Lưu ý:* Không khóa tài khoản ngay để tránh trải nghiệm xấu, chỉ tạm hạn chế quyền xem dữ liệu nâng cao.

### 3.2. Chống Sao chép & Bán lại (CẤM TUYỆT ĐỐI)
*   **Không Export All:** Không hỗ trợ tải toàn bộ file, không xuất file danh sách đầy đủ.
*   **Không API List_All:** API chỉ trả về dữ liệu cần thiết cho trang đang xem (Pagination là bắt buộc).
*   **Personalized Data:** Mỗi user có mục Ghi chú riêng, Yêu thích riêng, Lịch sử xem riêng. Nếu dùng chung tài khoản, hành vi này sẽ bị lộn xộn, làm giảm giá trị sử dụng chung.

---

## 4. QUẢN TRỊ (ADMIN) & LOGGING

*   **Quản lý cấu hình:** Admin có quyền cấu hình Quy tắc hoa hồng và Bật/tắt trạng thái mã giới thiệu.
*   **Audit Log:** Mọi thao tác của Admin và sự cố thanh toán đều được ghi Log.
*   **Tính bất biến:** Log không cho phép sửa hoặc xóa qua giao diện UI.

---

## 5. PHƯƠNG ÁN CÔNG NGHỆ CHỐT (PHP)

*   **Lý do chọn:** Chi phí tối ưu (9tr), triển khai nhanh (1 tháng), dễ vận hành trên Shared Hosting/VPS giá rẻ.
*   **Lõi:** Sử dụng PHP kết hợp MySQL (PDO), cơ chế Session PHP gắn Device_ID.
*   **Frontend:** HTML/CSS/JS thuần để dễ dàng tùy biến Watermark động và xử lý chống copy.

---
*Tài liệu này đã bao quát 100% các ý trong JOBS, BẢO MẬT và TÁC VỤ KỸ THUẬT của khách hàng.*
