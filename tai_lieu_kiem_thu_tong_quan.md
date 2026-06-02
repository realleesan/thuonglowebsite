# TÀI LIỆU KIỂM THỬ MASTER TỔNG QUAN HỆ THỐNG (TEST CHECKLIST)
## WEBSITE THƯƠNG LỘ (THUONGLO.COM)
*Ngày lập: 01/06/2026 | Phiên bản: 2.0 (Cập nhật Toàn diện) | Trạng thái: Sẵn sàng thử nghiệm*

---

> [!NOTE]
> Tài liệu này được nâng cấp để bao phủ **100% phạm vi công việc**, bao gồm các giai đoạn trong **Hợp đồng gốc (GĐ1 - GĐ5)**, chức năng **Phát sinh (PayOS Payout)** và các tính năng **Hỗ trợ thêm (CMS, Phân cấp Danh mục Cha-Con)**.
> 
> **Hướng dẫn ký hiệu kết quả test:**
> * `OK`: Tính năng hoạt động hoàn hảo, đúng nghiệp vụ cam kết.
>   * `BUG`: Phát hiện lỗi cần lập trình viên sửa đổi.
>   * `REQ`: Đề xuất thêm/sửa/xóa bổ sung ngoài thiết kế ban đầu để hai bên chốt sau.

---

## 🛠️ PHẦN 1: TỔNG QUAN PHẠM VI KIỂM THỬ THEO LỘ TRÌNH DỰ ÁN

| Giai Đoạn Hợp Đồng | Trọng Tâm Nghiệp Vụ | Nội Dung Tính Năng Cần Test | Trạng Thái Bản Bản |
| :--- | :--- | :--- | :---: |
| **GĐ1 - Planning** | Phân tích nghiệp vụ | Sitemap, luồng di chuyển, quy tắc mở khóa & hoa hồng | `[x] Đã thiết lập` |
| **GĐ2 - UI/UX** | Giao diện cơ bản | Tương thích di động (Mobile Responsive), căn chỉnh Logo, Sidebar | `[x] Đã thiết lập` |
| **GĐ3 - Core Web** | Khách hàng & Thanh toán | Đăng ký/đăng nhập, giỏ hàng, SePay thanh toán QR, cơ chế chống cào | `[x] Đã thiết lập` |
| **GĐ4 - Affiliate** | Đại lý & Quản trị | Link ref, dashboard đại lý, hoa hồng, admin quản lý sản phẩm/KH/đại lý/đơn hàng | `[x] Đã thiết lập` |
| **GĐ5 - Go-live** | Vận hành & Giám sát | Deploy hệ thống, tối ưu hiệu năng, trang giám sát lỗi Admin panel | `[x] Đã thiết lập` |
| **Phát sinh (PayOS)**| Chi hộ tự động | Giải ngân hoa hồng 24/7 Napas qua API PayOS, đóng băng số dư | `[x] Đã thiết lập` |
| **Hỗ trợ (CMS)** | Quản trị nội dung | Admin sửa Banner, Slider, CTA, dynamic subtitle các trang tĩnh | `[x] Đã thiết lập` |
| **Hỗ trợ (Product)**| Danh mục Cha - Con | Nâng cấp cấu hình danh mục phân cấp đa tầng đệ quy | `[x] Đã thiết lập` |

---

## 📋 PHẦN 2: CÁC BẢNG KIỂM THỬ CHI TIẾT (CHECKLIST TABLES)

### BẢNG 1: PHÂN HỆ CÔNG CỘNG & XÁC THỰC (GUEST & AUTHENTICATION - GĐ3)
*Kiểm tra các trang giới thiệu công cộng, luồng đăng ký tài khoản mới và đăng nhập.*

| STT | Mục Kiểm Thử | Hành Vi Kỳ Vọng | Tiến Độ | Kết Quả | Ghi Chú Chi Tiết |
| :---: | :--- | :--- | :---: | :---: | :--- |
| **1.1** | Giao diện Trang Chủ (`?page=home`) | Hiển thị Banner, Logo, khối sản phẩm nổi bật, CTA đầy đủ, đúng font chữ. | `[ ]` | | |
| **1.2** | Danh sách gói hàng (`?page=products`) | Hiển thị đúng danh sách lĩnh vực nguồn hàng, có phân trang mượt mà. | `[ ]` | | |
| **1.3** | Chi tiết gói hàng (`?page=details&id=...`) | Khách vãng lai thấy tiêu đề, giá, mô tả và nút **"Đăng nhập để mua ngay"**. | `[ ]` | | |
| **1.4** | Các trang thông tin tĩnh | Hỏi đáp FAQ, Hướng dẫn mua hàng hiển thị bài viết chuẩn cấu hình SEO. | `[ ]` | | |
| **1.5** | Form Đăng Ký Tài Khoản | Ràng buộc Email/SĐT hợp lệ; checkbox điều khoản dịch vụ hoạt động tốt. | `[ ]` | | |
| **1.6** | Nhận diện mã giới thiệu (`ref`) | Truy cập bằng link `?ref=AGENT_CODE` -> Lưu cookie -> Đăng ký -> Ghi nhận đúng CTV giới thiệu. | `[ ]` | | |
| **1.7** | Đăng Nhập & Quên Mật Khẩu | Đăng nhập bằng Email/SĐT; khôi phục mật khẩu qua Email OTP hoạt động chuẩn. | `[ ]` | | |

---

### BẢNG 2: PHÂN HỆ THÀNH VIÊN ĐÃ ĐĂNG NHẬP (USER DASHBOARD & PURCHASING - GĐ3)
*Kiểm tra quy trình đặt mua gói, thanh toán tự động và lịch sử hoạt động.*

| STT | Mục Kiểm Thử | Hành Vi Kỳ Vọng | Tiến Độ | Kết Quả | Ghi Chú Chi Tiết |
| :---: | :--- | :--- | :---: | :---: | :--- |
| **2.1** | Trang cá nhân (`dashboard.php`) | Liệt kê đúng các gói dữ liệu đã mua và quota lượt xem còn lại của người dùng. | `[ ]` | | |
| **2.2** | Giỏ hàng & Danh sách yêu thích | Thêm/sửa/xóa sản phẩm trong giỏ hàng; lưu danh sách yêu thích (Wishlist) chuẩn. | `[ ]` | | |
| **2.3** | Luồng Thanh toán QR (SePay) | Nhấp Checkout -> Tạo đơn hàng `PENDING` -> Sinh mã QR VietQR đúng số tiền + nội dung chuyển khoản (Ví dụ: `DH1024`). | `[ ]` | | |
| **2.4** | Nhận diện Webhook SePay | Chuyển khoản thành công -> Webhook SePay gửi dữ liệu -> Verify chữ ký SHA256 -> Tự động chuyển đơn hàng sang `COMPLETED`. | `[ ]` | | |
| **2.5** | Phản hồi giao diện Real-time | Màn hình thanh toán tự động chuyển sang thông báo "Thanh toán thành công" trong 3-5 giây (Polling ngầm) không cần F5. | `[ ]` | | |
| **2.6** | Cấp quyền Quota xem nguồn hàng | Sau khi thanh toán, tài khoản được cộng số lượt quota xem chính xác tương ứng gói đã mua. | `[ ]` | | |
| **2.7** | Xem lịch sử đơn hàng | Hiển thị bảng danh sách đơn hàng đã mua, số tiền, ngày tạo và hóa đơn chi tiết. | `[ ]` | | |

---

### BẢNG 3: CƠ CHẾ BẢO MẬT & CHỐNG CÀO DỮ LIỆU NGUỒN HÀNG (ANTI-COPY HARDENING - GĐ3 CỐT LÕI)
*Kiểm thử các giải pháp phòng vệ thông tin trên trang xem dữ liệu nhạy cảm.*

| STT | Mục Kiểm Thử | Hành Vi Kỳ Vọng | Tiến Độ | Kết Quả | Ghi Chú Chi Tiết |
| :---: | :--- | :--- | :---: | :---: | :--- |
| **3.1** | Hạn mức thời gian xem dữ liệu | Nhấp xem dữ liệu -> Tạo token xem có thời hạn 15 phút. Quá 15 phút từ chối cho phép xem tiếp. | `[ ]` | | |
| **3.2** | Làm mờ cột lũy tiến (90 Giây) | Cứ sau **90 giây**, các cột thông tin nhạy cảm bên phải (QR WeChat, SĐT, Địa chỉ) tự động mờ dần qua thuộc tính CSS Blur. | `[ ]` | | |
| **3.3** | Trừ Quota khi F5 tải lại trang | Tải lại trang (F5) để khôi phục độ rõ nét -> Hệ thống trừ đi **1 lượt quota** xem trong tài khoản của User. | `[ ]` | | |
| **3.4** | Vô hiệu hóa chuột phải & bôi đen| Bị chặn chuột phải hoàn toàn (`contextmenu`). Bị chặn kéo thả hình ảnh. Không thể bôi đen quét chọn văn bản. | `[ ]` | | |
| **3.5** | Chặn chụp màn hình (PrintScreen)| Nhấn phím `Print Screen` -> Hiển thị lớp phủ màu đen tuyền bảo mật (`protection-overlay`) và xóa clipboard bộ nhớ tạm. | `[ ]` | | |
| **3.6** | Chặn mất tiêu điểm (Window Blur) | Chuyển sang tab khác, mở ứng dụng khác (Excel, Zalo...) -> Bảng dữ liệu tự động biến thành màu đen xì để bảo vệ thông tin. | `[ ]` | | |
| **3.7** | Xem mã QR WeChat (Modal 5 Giây) | Bấm vào biểu tượng xem ảnh QR WeChat của nhà xưởng -> Ảnh phóng to chỉ hiện trong **5 giây** rồi tự động đóng và xóa thẻ `src`. | `[ ]` | | |

---

### BẢNG 4: PHÂN HỆ ĐẠI LÝ / CỘNG TÁC VIÊN (AFFILIATE MARKETING - GĐ4)
*Kiểm thử đăng ký, Dashboard hoa hồng và yêu cầu thanh toán của CTV.*

| STT | Mục Kiểm Thử | Hành Vi Kỳ Vọng | Tiến Độ | Kết Quả | Ghi Chú Chi Tiết |
| :---: | :--- | :--- | :---: | :---: | :--- |
| **4.1** | Form Đăng ký đại lý | Form hiển thị các thông tin: Kênh tiếp thị, SĐT... Trường Email mặc định khóa chỉ đọc (`readonly`) để chống giả mạo. | `[ ]` | | |
| **4.2** | Trạng thái Chờ xét duyệt | Gửi form thành công -> Tài khoản chuyển trạng thái là `pending` -> Chuyển hướng tới trang thông báo chờ phê duyệt chuyên nghiệp. | `[ ]` | | |
| **4.3** | Kích hoạt CTV & Mã giới thiệu | Sau khi Admin duyệt -> Tài khoản CTV đăng nhập hiển thị menu Đại Lý và Mã Referral độc quyền 8 ký tự (Ví dụ: `TL87F3B9`). | `[ ]` | | |
| **4.4** | Chỉ số Dashboard Đại Lý | Hiển thị chính xác: Tổng số click, Khách hàng giới thiệu, Tỷ lệ chuyển đổi, Số dư khả dụng, Hoa hồng chờ duyệt. | `[ ]` | | |
| **4.5** | Khai thác Tài nguyên Marketing | Sao chép liên kết tiếp thị, tải Mã QR giới thiệu tự sinh, mã nhúng banner quảng cáo hoạt động tốt. | `[ ]` | | |
| **4.6** | Theo dõi Khách hàng giới thiệu | Xem danh sách người dùng đã đăng ký qua link của đại lý, hiển thị doanh số đơn hàng họ đã mua và số hoa hồng nhận được. | `[ ]` | | |
| **4.7** | Lập lệnh yêu cầu rút tiền hoa hồng | CTV chọn ngân hàng thụ hưởng, nhập STK, nhập Tên chủ khoản viết **VIẾT HOA KHÔNG DẤU**. Giới hạn tiền rút tối thiểu 5.000 VNĐ. | `[ ]` | | |
| **4.8** | Logic Đóng băng số dư khả dụng | Gửi lệnh rút thành công -> Số tiền rút bị khóa ngay lập tức (không cho phép CTV tạo tiếp lệnh rút trùng lặp khi lệnh cũ chưa xử lý). | `[ ]` | | |

---

### BẢNG 5: PHÂN HỆ QUẢN TRỊ VIÊN - ADMIN AREA (ADMIN CONSOLE - GĐ4)
*Kiểm thử các chức năng quản lý, phân quyền và đối soát dữ liệu của Admin.*

| STT | Mục Kiểm Thử | Hành Vi Kỳ Vọng | Tiến Độ | Kết Quả | Ghi Chú Chi Tiết |
| :---: | :--- | :--- | :---: | :---: | :--- |
| **5.1** | Dashboard tổng quan Admin | Xem biểu đồ doanh thu, số đơn thành công, tổng số đại lý mới, thành viên mới đăng ký hệ thống trực quan. | `[ ]` | | |
| **5.2** | Quản lý gói sản phẩm | Thêm, sửa, xóa các gói dữ liệu sản phẩm (tải lên ảnh đại diện, viết nội dung chi tiết bằng editor trực quan). | `[ ]` | | |
| **5.3** | Nhập liệu nguồn hàng Excel | Tải file Excel mẫu -> Điền thông tin nhà xưởng -> Upload import hàng loạt thành công, tự động ánh xạ với gói sản phẩm. | `[ ]` | | |
| **5.4** | Xem/Xóa dữ liệu chi tiết | Cho phép xem và xóa nhanh các dòng thông tin nhà xưởng đã import trực tiếp ngay trên trang danh sách dữ liệu. | `[ ]` | | |
| **5.5** | Phê duyệt/Từ chối Đại lý mới | Xem danh sách yêu cầu đại lý. Phê duyệt (chuyển agent, cấp mã giới thiệu), Từ chối (gửi lý do từ chối về email người dùng). | `[ ]` | | |
| **5.6** | Quản lý đơn hàng & người dùng | Tìm kiếm đơn hàng theo mã đơn, cập nhật trạng thái đơn thủ công. Xem danh sách thành viên, khóa tài khoản lạm dụng. | `[ ]` | | |

---

### BẢNG 6: GIAO DỊCH CHI HỘ TỰ ĐỘNG PAYOS (AUTOMATED PAYOUT - PHÁT SINH THÊM)
*Kiểm thử hệ thống giải ngân siêu tốc 24/7 trực tiếp qua API PayOS.*

| STT | Mục Kiểm Thử | Hành Vi Kỳ Vọng | Tiến Độ | Kết Quả | Ghi Chú Chi Tiết |
| :---: | :--- | :--- | :---: | :---: | :--- |
| **6.1** | Khởi tạo lệnh chi hộ PayOS | Admin nhấn nút **"Phê Duyệt & Giải Ngân Lập Tức (PayOS)"** -> Hệ thống gọi API `PayOSService->createPayout()` tự động. | `[ ]` | | |
| **6.2** | Chuẩn hóa thông tin ngân hàng | Hệ thống đối chiếu đúng mã BIN ngân hàng thụ hưởng Napas của đại lý để thực hiện lệnh chuyển khoản liên ngân hàng. | `[ ]` | | |
| **6.3** | Ký số bảo mật HMAC-SHA256 | Lệnh chi hộ gửi sang PayOS bắt buộc đi kèm chữ ký số xác thực SHA256 mã hóa bằng Checksum Key bảo mật, tránh giả mạo request. | `[ ]` | | |
| **6.4** | Giới hạn ký tự Napas | Nội dung chuyển khoản tự động chuẩn hóa không dấu và cắt ngắn **tối đa 25 ký tự** tránh lỗi Napas (Ví dụ: `RUT2045 DUYET AFFILIATE`). | `[ ]` | | |
| **6.5** | Xử lý Callback hoàn tất giao dịch| Chuyển tiền thành công -> PayOS gửi callback COMPLETED -> Cập nhật trạng thái đơn rút thành công, trừ số dư đóng băng của CTV. | `[ ]` | | |
| **6.6** | Theo dõi lịch sử giao dịch PayOS| Lịch sử giao dịch rút tiền hiển thị rõ ràng mã giao dịch PayOS, ngày giờ giải ngân và biên lai chuyển khoản. | `[ ]` | | |

---

### BẢNG 7: PHÂN CẤP DANH MỤC SẢN PHẨM CHA - CON (PRODUCT CATEGORY HIERARCHY - HỖ TRỢ THÊM)
*Kiểm thử cấu trúc phân cấp đa tầng cho danh mục gói nguồn hàng.*

| STT | Mục Kiểm Thử | Hành Vi Kỳ Vọng | Tiến Độ | Kết Quả | Ghi Chú Chi Tiết |
| :---: | :--- | :--- | :---: | :---: | :--- |
| **7.1** | Tạo mới danh mục Cha | Thêm danh mục mới, bỏ trống mục "Danh mục cha" -> Lưu trữ danh mục ở cấp độ gốc (Cấp 1). | `[ ]` | | |
| **7.2** | Tạo mới danh mục Con | Thêm danh mục mới, chọn "Danh mục cha" -> Lưu trữ ID cha (`parent_id`) chính xác vào cơ sở dữ liệu (Cấp 2, Cấp 3). | `[ ]` | | |
| **7.3** | Thụt lề hiển thị phân cấp | Giao diện thêm/sửa có dropdown Danh mục cha hiển thị phân cấp thụt đầu dòng chuyên nghiệp: `— Tên danh mục con (Cấp 2)`. | `[ ]` | | |
| **7.4** | Cơ chế chống vòng lặp đệ quy | Giao diện sửa danh mục (`edit.php`) **chặn không cho chọn danh mục con làm cha của chính nó** để loại bỏ hoàn toàn lỗi lặp vô hạn. | `[ ]` | | |
| **7.5** | Hiển thị ngoài giao diện Public | Bộ lọc danh mục ngoài trang chủ và trang danh sách sản phẩm hiển thị chuẩn cấu trúc phân tầng Cha-Con. | `[ ]` | | |

---

### BẢNG 8: QUẢN TRỊ NỘI DUNG WEBSITE - CMS PANEL (HOME & SUBPAGES CMS - HỖ TRỢ THÊM)
*Kiểm thử việc Admin cập nhật trực quan nội dung, hình ảnh các mục trên website.*

| STT | Mục Kiểm Thử | Hành Vi Kỳ Vọng | Tiến Độ | Kết Quả | Ghi Chú Chi Tiết |
| :---: | :--- | :--- | :---: | :---: | :--- |
| **8.1** | Cập nhật Hero Section & Slider | Admin chỉnh sửa tiêu đề chính, tiêu đề phụ, các nút điều hướng và tải lên ảnh nền của Slider trang chủ hoạt động tốt. | `[ ]` | | |
| **8.2** | Cập nhật Why Choose Us | Chỉnh sửa các khối thông tin "Tại sao chọn chúng tôi" (icon, tiêu đề khối, mô tả ngắn) trực tiếp từ trang Admin. | `[ ]` | | |
| **8.3** | Cập nhật Kêu gọi hành động CTA | Chỉnh sửa nội dung text, đổi ảnh nền khối CTA (`edit_cta.php`). Ảnh tải lên thư mục `/assets/images/home/` hiển thị tốt. | `[ ]` | | |
| **8.4** | Chỉnh sửa sản phẩm trang chủ | Cấu hình nhanh các gói sản phẩm nào hiển thị ở mục Bán chạy (Featured), mục Giá tốt (Budget), mục Khuyến mãi (Sale) trên Home. | `[ ]` | | |
| **8.5** | CMS Trang tĩnh & Subtitle động | Admin chỉnh sửa nội dung trang giới thiệu, FAQ. Cập nhật trường **Tiêu đề phụ động (Dynamic Subtitle)** hiển thị chuẩn ngoài giao diện. | `[ ]` | | |

---

### BẢNG 9: TƯƠNG THÍCH DI ĐỘNG & UI/UX (MOBILE RESPONSIVE - GĐ2 & GĐ5)
*Kiểm thử hiển thị giao diện mượt mà và đồng bộ trên các kích thước màn hình thiết bị.*

| STT | Mục Kiểm Thử | Hành Vi Kỳ Vọng | Tiến Độ | Kết Quả | Ghi Chú Chi Tiết |
| :---: | :--- | :--- | :---: | :---: | :--- |
| **9.1** | Thanh menu điều hướng di động | Trên màn hình di động, menu thu gọn lại thành biểu tượng Hamburger, nhấp mở/đóng mượt mà không bị lỗi layout. | `[ ]` | | |
| **9.2** | Tỷ lệ Logo trên Sidebar | Logo thương hiệu trên thanh Sidebar khi thu nhỏ (collapsed) hoặc phóng to trên Admin/Affiliate panel hiển thị cân đối, không bị méo. | `[ ]` | | |
| **9.3** | Bảng lịch sử đơn hàng di động | Danh sách đơn hàng trên Mobile tự động co giãn, chuyển đổi sang layout dạng danh sách thẻ (card) hoặc cuộn ngang, không chồng đè chữ. | `[ ]` | | |
| **9.4** | Bảng chống cào dữ liệu di động | Bảng dữ liệu nhạy cảm hiển thị tốt trên Mobile; cơ chế mờ dần, chặn copy, và tối màn hình hoạt động chính xác trên Chrome/Safari mobile. | `[ ]` | | |

---

### BẢNG 10: GIÁM SÁT LỖI HỆ THỐNG ĐẠI LÝ & THANH TOÁN (ERROR MONITORING - GĐ5)
*Kiểm thử hệ thống ghi nhận lỗi tự động phục vụ công tác vận hành và bảo trì website.*

| STT | Mục Kiểm Thử | Hành Vi Kỳ Vọng | Tiến Độ | Kết Quả | Ghi Chú Chi Tiết |
| :---: | :--- | :--- | :---: | :---: | :--- |
| **10.1** | Giao diện Giám sát lỗi Admin | Truy cập `/admin/agent_error_monitoring.php` hiển thị đầy đủ các thống kê lỗi trong 24 giờ qua. | `[ ]` | | |
| **10.2** | Ghi nhận lỗi phân loại loại hình | Hệ thống gom nhóm và phân tích đúng các loại lỗi: `agent_registration_error`, `agent_database_error`, `agent_spam_prevention`... | `[ ]` | | |
| **10.3** | Xem chi tiết lỗi bằng Popup Modal | Nhấp nút **"Details"** hiển thị Popup Modal liệt kê đầy đủ thông tin: loại lỗi, nội dung lỗi chi tiết phục vụ cho IT đối soát. | `[ ]` | | |
| **10.4** | Đề xuất sức khỏe hệ thống | Dựa trên số lượng lỗi thực tế, hiển thị các khối đề xuất cảnh báo sức khỏe hệ thống tự động (Critical / Warning / Healthy). | `[ ]` | | |
| **10.5** | Cơ chế tự động làm mới (Refresh) | Trang tự động thực hiện tải lại lấy dữ liệu lỗi mới sau mỗi **5 phút** hoạt động liên tục. | `[ ]` | | |

---

## 📝 PHẦN 3: BẢNG GHI NHẬN LỖI (BUG REPORT) & YÊU CẦU THAY ĐỔI (CHANGE REQUEST)
*Tester sử dụng bảng này để ghi nhận chi tiết các lỗi phát hiện và các yêu cầu chỉnh sửa phát sinh trong buổi test.*

### 1. Danh Sách Lỗi Phát Hiện (Bugs Found)
*Dành cho các tính năng hoạt động không đúng thiết kế, bị lỗi giao diện hoặc lỗi logic ngầm.*

| ID Lỗi | Mã Hạng Mục | Tên Tính Năng | Mô Tả Chi Tiết Lỗi & Cách Tái Hiện | Mức Độ Nghiêm Trọng<br>(Cao / Trung bình / Thấp) | Trạng Thái Sửa Lỗi<br>(Chờ sửa / Đang sửa / Đã sửa) |
| :---: | :---: | :--- | :--- | :---: | :---: |
| *B01* | *Ví dụ: 3.5* | *Chụp màn hình* | *Nhấn PrintScreen trên trình duyệt Edge không hiện màn phủ đen.* | *Trung bình* | *Chờ sửa* |
| | | | | | |
| | | | | | |

### 2. Danh Sách Yêu Cầu Thay Đổi / Thêm / Xóa (Change Requests)
*Dành cho các đề xuất chỉnh sửa ngoài thiết kế ban đầu hoặc các yêu cầu phát triển thêm chức năng mới để hoàn thiện web.*

| ID Yêu Cầu | Mã Hạng Mục | Tên Tính Năng | Chi Tiết Đề Xuất Thay Đổi (Thêm/Sửa/Xóa) | Lý Do Cần Thiết | Đánh Giá Khả Thi & Thời Gian Dự Kiến |
| :---: | :---: | :--- | :--- | :--- | :--- |
| *R01* | *Ví dụ: 4.7* | *Yêu cầu rút tiền* | *Thêm ô hiển thị số dư khả dụng ngay bên cạnh trường nhập số tiền.* | *Giúp đại lý dễ đối chiếu số dư khi đang nhập lệnh.* | *Rất khả thi - 15 phút* |
| | | | | | |
| | | | | | |

---

## 🤝 PHẦN 4: THỎA THUẬN KÝ KẾT NGHIỆM THU CHI TIẾT
*Biên bản ghi nhận kết quả buổi kiểm thử để làm cơ sở bàn giao website.*

Sau khi hoàn thành buổi kiểm thử hệ thống tổng quát ngày ..../..../2026, hai bên thống nhất các nội dung sau:

1. **Tổng số hạng mục kiểm thử:** `65` hạng mục (Gồm cả CMS, Danh mục Cha-Con và Tương thích Mobile).
2. **Số hạng mục đạt tiêu chuẩn (OK):** ......... / 65 hạng mục.
3. **Số hạng mục còn lỗi cần khắc phục (BUG):** ......... hạng mục (Chi tiết tại bảng ghi nhận lỗi).
4. **Số hạng mục đề xuất bổ sung/thay đổi (REQ):** ......... hạng mục (Hai bên sẽ thảo luận lộ trình triển khai sau).

**Cam kết khắc phục lỗi:**
Đội ngũ phát triển cam kết tiến hành khắc phục toàn bộ các lỗi thuộc danh sách `BUG` trong vòng ......... ngày kể từ ngày ký biên bản này. Sau khi khắc phục xong, hai bên sẽ tiến hành bàn giao mã nguồn và tài khoản quản trị tối cao của website **ThuongLo.com**.

| ĐẠI DIỆN KHÁCH HÀNG<br>*(Ký & ghi rõ họ tên)* | ĐẠI DIỆN ĐỘI NGŨ PHÁT TRIỂN<br>*(Ký & ghi rõ họ tên)* |
| :---: | :---: |
| <br><br><br><br>**............................................................** | <br><br><br><br>**............................................................** |
