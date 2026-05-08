# Hướng Dẫn Sử Dụng Website ThuongLo.com

## Giới Thiệu

ThuongLo.com là nền tảng thương mại xuyên biên giới chuyên cung cấp data nguồn hàng chất lượng cao, dịch vụ vận chuyển chính ngạch và các giải pháp toàn diện cho doanh nghiệp. Website được thiết kế với 4 đối tượng người dùng chính: khách hàng chưa đăng nhập, người dùng đã đăng nhập, đại lý và quản trị viên.

---

## A. Hướng Dẫn Cho Người Dùng Chưa Đăng Nhập

### I. Sitemap Website

#### 1. Trang Chủ (Home)
- **URL**: `?page=home`
- **Mục đích**: Giới thiệu tổng quan về dịch vụ và sản phẩm nổi bật
- **Các section chính**:
  - Hero Section: Banner giới thiệu với nút "Đăng ký miễn phí" và "Xem sản phẩm"
  - Sản phẩm nổi bật: Hiển thị các sản phẩm premium với slider
  - Danh mục nổi bật: Grid hiển thị các danh mục chính
  - Thương hiệu nổi bật: Các thương hiệu đối tác
  - Tại sao chọn ThuongLo: 6 lợi ích chính
- **Tương tác**: Click vào sản phẩm/danh mục để xem chi tiết, nút đăng ký để tạo tài khoản

#### 2. Sản Phẩm (Products)
- **URL**: `?page=products`
- **Mục đích**: Liệt kê và tìm kiếm sản phẩm
- **Các section chính**:
  - Header: Tiêu đề "Sản phẩm" và nút bộ lọc
  - Top bar: Số lượng kết quả và sắp xếp
  - Products Grid: Hiển thị sản phẩm theo lưới
  - Sidebar: Bộ lọc theo danh mục, thương hiệu, giá
  - Pagination: Phân trang sản phẩm
- **Tương tác**: 
  - Lọc sản phẩm theo danh mục, thương hiệu, khoảng giá
  - Sắp xếp theo mới nhất, tên, giá
  - Click "Xem chi tiết" để xem thông tin sản phẩm
  - Click icon trái tim để thêm vào yêu thích

#### 3. Chi Tiết Sản Phẩm (Product Details)
- **URL**: `?page=details&id={product_id}`
- **Mục đích**: Hiển thị thông tin chi tiết sản phẩm
- **Các section chính**:
  - Product Header: Tên sản phẩm, danh mục, giá
  - Product Image: Hình ảnh sản phẩm
  - Product Info: Mô tả chi tiết, số lượng records
  - Supplier Info: Thông tin nhà cung cấp
  - Action Buttons: "Mua ngay", "Thêm vào giỏ hàng"
- **Tương tác**: Mua sản phẩm, thêm vào giỏ hàng, xem thông tin nhà cung cấp

#### 4. Danh Mục (Categories)
- **URL**: `?page=categories`
- **Mục đích**: Hiển thị tất cả danh mục sản phẩm
- **Các section chính**:
  - Category Grid: Hiển thị danh mục theo lưới
  - Category Info: Số lượng sản phẩm mỗi danh mục
- **Tương tác**: Click vào danh mục để xem sản phẩm tương ứng

#### 5. Thương Hiệu (Brands)
- **URL**: `?page=brands`
- **Mục đích**: Hiển thị các thương hiệu đối tác
- **Các section chính**:
  - Brand Grid: Hiển thị thương hiệu theo lưới
  - Brand Info: Mô tả và số lượng sản phẩm
- **Tương tác**: Click vào thương hiệu để xem sản phẩm tương ứng

#### 6. Tin Tức (News)
- **URL**: `?page=news`
- **Mục đích**: Cập nhật tin tức và thông tin hữu ích
- **Các section chính**:
  - News Grid: Hiển thị bài viết theo lưới
  - News Excerpt: Tóm tắt nội dung bài viết
- **Tương tác**: Click vào bài viết để xem chi tiết

#### 7. Chi Tiết Tin Tức (News Details)
- **URL**: `?page=news-details&id={news_id}`
- **Mục đích**: Hiển thị nội dung đầy đủ của bài viết
- **Các section chính**:
  - Article Header: Tiêu đề, ngày đăng, tác giả
  - Article Content: Nội dung chi tiết
  - Related News: Các bài viết liên quan
- **Tương tác**: Đọc bài viết, xem các bài liên quan

#### 8. Giới Thiệu (About)
- **URL**: `?page=about`
- **Mục đích**: Giới thiệu về công ty và dịch vụ
- **Các section chính**:
  - Company Info: Lịch sử và sứ mệnh
  - Services: Các dịch vụ chính
  - Team: Đội ngũ nhân viên
- **Tương tác**: Tìm hiểu về công ty

#### 9. Liên Hệ (Contact)
- **URL**: `?page=contact`
- **Mục đích**: Cung cấp thông tin liên hệ và gửi tin nhắn
- **Các section chính**:
  - Contact Form: Form gửi tin nhắn
  - Contact Info: Địa chỉ, email, điện thoại
  - Map: Bản đồ vị trí
- **Tương tác**: Điền form và gửi tin nhắn

#### 10. Đăng Nhập (Login)
- **URL**: `?page=login`
- **Mục đích**: Đăng nhập vào tài khoản
- **Các section chính**:
  - Login Form: Email/phone/tên đăng nhập, mật khẩu
  - Remember Me: Ghi nhớ đăng nhập
  - Links: "Quên mật khẩu?", "Đăng ký ngay"
- **Tương tác**: Nhập thông tin và đăng nhập

#### 11. Đăng Ký (Register)
- **URL**: `?page=register`
- **Mục đích**: Tạo tài khoản mới
- **Các section chính**:
  - Registration Form: Thông tin cá nhân, tài khoản, mật khẩu
  - Referral Code: Mã giới thiệu (nếu có)
  - Terms: Điều khoản sử dụng
- **Tương tác**: Điền thông tin và tạo tài khoản

#### 12. Quên Mật Khẩu (Forgot Password)
- **URL**: `?page=forgot`
- **Mục đích**: Khôi phục mật khẩu đã quên
- **Các section chính**:
  - Email Form: Nhập email để nhận link reset
- **Tương tác**: Nhập email và nhận hướng dẫn reset

---

## B. Hướng Dẫn Cho Người Dùng Đã Đăng Nhập (Role: User)

### I. Mục Đích Đăng Nhập

Người dùng đăng nhập để:
- Mua sản phẩm data nguồn hàng
- Quản lý đơn hàng đã mua
- Lưu sản phẩm yêu thích
- Quản lý thông tin cá nhân
- Theo dõi lịch sử mua hàng
- Nâng cấp thành đại lý

### II. Dashboard Người Dùng

#### 1. Tổng Quan (Dashboard)
- **URL**: `?page=users&module=dashboard`
- **Mục đích**: Hiển thị tổng quan tài khoản
- **Các section chính**:
  - Welcome Message: Chào mừng người dùng
  - Stats Cards: Tổng đơn hàng, tổng chi tiêu, data đã mua
  - Charts: Phân bố đơn hàng, chi tiêu theo tháng
  - Quick Actions: Các thao tác nhanh
- **Tương tác**: Xem thống kê, truy cập nhanh các chức năng

#### 2. Thông Tin Tài Khoản (Account)
- **URL**: `?page=users&module=account`
- **Mục đích**: Quản lý thông tin cá nhân
- **Các section chính**:
  - Profile Info: Họ tên, email, điện thoại
  - Change Password: Đổi mật khẩu
  - Account Settings: Cài đặt tài khoản
- **Tương tác**: Cập nhật thông tin, đổi mật khẩu

#### 3. Đơn Hàng (Orders)
- **URL**: `?page=users&module=orders`
- **Mục đích**: Quản lý đơn hàng đã mua
- **Các section chính**:
  - Orders List: Danh sách đơn hàng
  - Order Details: Chi tiết từng đơn hàng
  - Order Status: Trạng thái đơn hàng
- **Tương tác**: Xem lịch sử, theo dõi trạng thái, tải lại sản phẩm

#### 4. Giỏ Hàng (Cart)
- **URL**: `?page=users&module=cart`
- **Mục đích**: Quản lý sản phẩm trong giỏ hàng
- **Các section chính**:
  - Cart Items: Sản phẩm trong giỏ
  - Cart Summary: Tổng tiền
  - Checkout Button: Nút thanh toán
- **Tương tác**: Thêm/xóa sản phẩm, cập nhật số lượng, thanh toán

#### 5. Yêu Thích (Wishlist)
- **URL**: `?page=users&module=wishlist`
- **Mục đích**: Lưu sản phẩm yêu thích
- **Các section chính**:
  - Wishlist Items: Danh sách sản phẩm yêu thích
  - Add to Cart: Thêm vào giỏ hàng
- **Tương tác**: Xem danh sách, thêm vào giỏ, xóa khỏi yêu thích

#### 6. Quản Lý Truy Cập (Access)
- **URL**: `?page=users&module=access`
- **Mục đích**: Quản lý phiên đăng nhập
- **Các section chính**:
  - Active Sessions: Các phiên đang hoạt động
  - Login History: Lịch sử đăng nhập
- **Tương tác**: Xem phiên, đăng xuất khỏi thiết bị khác

### III. Quy Trình Mua Hàng

#### 1. Tìm Kiếm Sản Phẩm
1. Truy cập trang Sản phẩm (`?page=products`)
2. Sử dụng bộ lọc để tìm sản phẩm phù hợp
3. Click "Xem chi tiết" để xem thông tin

#### 2. Thêm Vào Giỏ Hàng
1. Tại trang chi tiết sản phẩm, click "Thêm vào giỏ hàng"
2. Hoặc click "Mua ngay" để chuyển thẳng đến thanh toán

#### 3. Thanh Toán
1. Truy cập giỏ hàng (`?page=users&module=cart`)
2. Kiểm tra thông tin đơn hàng
3. Click "Thanh toán"
4. Chọn phương thức thanh toán (SePay)
5. Hoàn tất thanh toán

#### 4. Nhận Sản Phẩm
1. Sau khi thanh toán thành công, vào Đơn hàng (`?page=users&module=orders`)
2. Tìm đơn hàng đã hoàn thành
3. Click "Tải về" để nhận data

### IV. Quy Trình Đăng Ký Đại Lý

#### 1. Nâng Cấp Thành Đại Lý
1. Đăng nhập vào tài khoản
2. Tìm link "Đăng ký làm đại lý" ở sidebar hoặc footer
3. Điền form đăng ký đại lý:
   - Thông tin cá nhân
   - Kinh nghiệm kinh doanh
   - Mục tiêu kinh doanh
4. Chờ admin phê duyệt (thường 1-2 ngày làm việc)

#### 2. Lợi Ích Khi Làm Đại Lý
- Hoa hồng 10% trên mỗi đơn hàng thành công
- Link giới thiệu riêng
- Dashboard quản lý riêng
- Hỗ trợ marketing materials
- Rút tiền hoa hồng linh hoạt

#### 3. Hoa Hồng và Thanh Toán
- **Tỷ lệ hoa hồng**: 10% trên giá trị đơn hàng
- **Tính hoa hồng**: Tự động khi khách hàng thanh toán thành công
- **Rút tiền**: Yêu cầu rút tiền từ dashboard đại lý
- **Phương thức rút**: Chuyển khoản ngân hàng qua PayOS
- **Thời gian xử lý**: 1-3 ngày làm việc

---

## C. Hướng Dẫn Cho Đại Lý (Role: Agent)

### I. Mục Đích Làm Đại Lý

Đại lý tham gia để:
- Kiếm thu nhập thụ động từ hoa hồng
- Xây dựng mạng lưới khách hàng
- Nhận sản phẩm miễn phí/giảm giá
- Hỗ trợ từ đội ngũ ThuongLo
- Phát triển kinh doanh online

### II. Cách Trở Thành Đại Lý

#### 1. Điều Kiện Đăng Ký
- Có tài khoản người dùng trên ThuongLo.com
- Có kinh nghiệm kinh doanh hoặc marketing
- Cam kết tuân thủ chính sách đại lý

#### 2. Quy Trình Đăng Ký
1. Đăng nhập tài khoản
2. Truy cập trang đăng ký đại lý
3. Điền đầy đủ thông tin
4. Chờ admin xét duyệt
5. Nhận email xác nhận khi được duyệt

### III. Dashboard Đại Lý

#### 1. Tổng Quan (Dashboard)
- **URL**: `?page=affiliate&module=dashboard`
- **Mục đích**: Hiển thị tổng quan kinh doanh
- **Các section chính**:
  - Stats Cards: Doanh số tổng, tuần, tháng, tổng khách hàng
  - Affiliate Info: Link giới thiệu, mã giới thiệu
  - Charts: Doanh thu theo tuần, lượt click, tỉ lệ chuyển đổi
  - Recent Customers: Khách hàng mới
  - Commission Status: Trạng thái hoa hồng
- **Tương tác**: Sao chép link giới thiệu, xem thống kê

#### 2. Khách Hàng (Customers)
- **URL**: `?page=affiliate&module=customers`
- **Mục đích**: Quản lý khách hàng giới thiệu
- **Các section chính**:
  - Customers List: Danh sách khách hàng
  - Customer Details: Thông tin chi tiết
  - Order History: Lịch sử đơn hàng
- **Tương tác**: Xem thông tin, theo dõi đơn hàng

#### 3. Hoa Hồng (Commissions)
- **URL**: `?page=affiliate&module=commissions`
- **Mục đích**: Quản lý hoa hồng
- **Các section chính**:
  - Commission List: Danh sách hoa hồng
  - Commission Status: Chờ thanh toán, đã thanh toán
  - Withdrawal History: Lịch sử rút tiền
- **Tương tác**: Xem chi tiết hoa hồng, yêu cầu rút tiền

#### 4. Rút Tiền (Withdrawals)
- **URL**: `?page=affiliate&module=withdrawals`
- **Mục đích**: Yêu cầu rút tiền hoa hồng
- **Các section chính**:
  - Withdrawal Form: Form yêu cầu rút tiền
  - Bank Info: Thông tin ngân hàng
  - Withdrawal History: Lịch sử rút tiền
- **Tương tác**: Yêu cầu rút tiền, cập nhật thông tin ngân hàng

#### 5. Marketing Materials
- **URL**: `?page=affiliate&module=marketing`
- **Mục đích**: Tải tài liệu marketing
- **Các section chính**:
  - Banners: Banner quảng cáo
  - Email Templates: Mẫu email
  - Social Media: Nội dung mạng xã hội
- **Tương tác**: Tải tài liệu, sử dụng cho marketing

### IV. Cách Rút Tiền Hoa Hồng

#### 1. Điều Kiện Rút Tiền
- Số dư hoa hồng tối thiểu: 50,000 VNĐ
- Tài khoản ngân hàng đã xác thực
- Không có yêu cầu rút tiền đang xử lý

#### 2. Quy Trình Rút Tiền
1. Đăng nhập dashboard đại lý
2. Truy cập "Rút tiền" (`?page=affiliate&module=withdrawals`)
3. Nhập số tiền muốn rút
4. Chọn tài khoản ngân hàng
5. Xác nhận yêu cầu
6. Chờ admin xử lý (1-3 ngày làm việc)

#### 3. Phương Thức Thanh Toán
- **Chuyển khoản ngân hàng**: Qua PayOS
- **Thời gian xử lý**: 1-3 ngày làm việc
- **Phí rút tiền**: Miễn phí

---

## D. Hướng Dẫn Cho Admin (Role: Admin)

### I. Giới Thiệu Admin Panel

Admin Panel là trung tâm quản lý toàn bộ website, cho phép admin:
- Quản lý sản phẩm, danh mục, thương hiệu
- Quản lý người dùng và đại lý
- Xem thống kê và báo cáo
- Quản lý đơn hàng và thanh toán
- Cấu hình hệ thống

### II. Dashboard Admin

#### 1. Tổng Quan (Dashboard)
- **URL**: `?page=admin&module=dashboard`
- **Mục đích**: Hiển thị tổng quan hệ thống
- **Các section chính**:
  - KPI Cards: Tổng sản phẩm, doanh thu, tin tức, sự kiện
  - Charts: Doanh thu theo thời gian, top sản phẩm, phân loại đơn hàng
  - Recent Activities: Hoạt động gần đây
  - Quick Actions: Thao tác nhanh
- **Tương tác**: Xem thống kê, truy cập nhanh các module

### III. Quản Lý Sản Phẩm

#### 1. Danh Sách Sản Phẩm
- **URL**: `?page=admin&module=products`
- **Mục đích**: Quản lý tất cả sản phẩm
- **Chức năng**:
  - Xem danh sách sản phẩm
  - Tìm kiếm, lọc, sắp xếp
  - Thêm/sửa/xóa sản phẩm
  - Import/export sản phẩm
- **Sử dụng**:
  1. Truy cập module Products
  2. Sử dụng bộ lọc để tìm sản phẩm
  3. Click "Thêm" để tạo sản phẩm mới
  4. Click "Sửa" để cập nhật sản phẩm
  5. Click "Xóa" để xóa sản phẩm

#### 2. Thêm Sản Phẩm Mới
1. Click "Thêm sản phẩm"
2. Điền thông tin:
   - Tên sản phẩm
   - Danh mục
   - Giá
   - Mô tả
   - Hình ảnh
   - Số lượng records
3. Click "Lưu"

#### 3. Quản Lý Dữ Liệu Sản Phẩm
- **URL**: `?page=admin&module=products&action=data`
- **Mục đích**: Import/export dữ liệu sản phẩm
- **Chức năng**:
  - Import từ Excel/CSV
  - Export dữ liệu
  - Quản lý records
- **Sử dụng**:
  1. Chọn sản phẩm
  2. Click "Import dữ liệu"
  3. Upload file Excel/CSV
  4. Xác nhận import

### IV. Quản Lý Danh Mục

#### 1. Danh Sách Danh Mục
- **URL**: `?page=admin&module=categories`
- **Mục đích**: Quản lý danh mục sản phẩm
- **Chức năng**:
  - Xem cây danh mục
  - Thêm/sửa/xóa danh mục
  - Sắp xếp thứ tự
- **Sử dụng**:
  1. Truy cập module Categories
  2. Xem cấu trúc cây danh mục
  3. Click "Thêm" để tạo danh mục mới
  4. Kéo-thả để sắp xếp thứ tự

### V. Quản Lý Thương Hiệu

#### 1. Danh Sách Thương Hiệu
- **URL**: `?page=admin&module=brands`
- **Mục đích**: Quản lý thương hiệu đối tác
- **Chức năng**:
  - Xem danh sách thương hiệu
  - Thêm/sửa/xóa thương hiệu
  - Cập nhật logo và thông tin
- **Sử dụng**:
  1. Truy cập module Brands
  2. Click "Thêm" để tạo thương hiệu mới
  3. Upload logo và điền thông tin

### VI. Quản Lý Tin Tức

#### 1. Danh Sách Tin Tức
- **URL**: `?page=admin&module=news`
- **Mục đích**: Quản lý nội dung tin tức
- **Chức năng**:
  - Xem danh sách bài viết
  - Thêm/sửa/xóa bài viết
  - Xuất bản/lưu nháp
- **Sử dụng**:
  1. Truy cập module News
  2. Click "Thêm" để viết bài mới
  3. Sử dụng editor để soạn thảo
  4. Click "Xuất bản" để đăng bài

### VII. Quản Lý Người Dùng

#### 1. Danh Sách Người Dùng
- **URL**: `?page=admin&module=users`
- **Mục đích**: Quản lý tài khoản người dùng
- **Chức năng**:
  - Xem danh sách người dùng
  - Tìm kiếm, lọc theo role
  - Kích hoạt/vô hóa tài khoản
  - Reset mật khẩu
- **Sử dụng**:
  1. Truy cập module Users
  2. Sử dụng bộ lọc để tìm người dùng
  3. Click vào user để xem chi tiết
  4. Sử dụng hành động để quản lý

### VIII. Quản Lý Đại Lý

#### 1. Danh Sách Đại Lý
- **URL**: `?page=admin&module=affiliates`
- **Mục đích**: Quản lý đại lý
- **Chức năng**:
  - Xem danh sách đại lý
  - Phê duyệt đăng ký đại lý
  - Xem hiệu suất đại lý
  - Quản lý hoa hồng
- **Sử dụng**:
  1. Truy cập module Affiliates
  2. Xem các yêu cầu chờ duyệt
  3. Click "Duyệt" để chấp nhận đại lý
  4. Xem thống kê hiệu suất

#### 2. Quản Lý Yêu Cầu Đại Lý
- **URL**: `?page=admin&module=agent_management`
- **Mục đích**: Xử lý yêu cầu đăng ký đại lý
- **Chức năng**:
  - Xem yêu cầu mới
  - Phê duyệt/từ chối
  - Gửi email thông báo
- **Sử dụng**:
  1. Kiểm tra yêu cầu mới
  2. Xem thông tin ứng viên
  3. Quyết định duyệt/từ chối
  4. Gửi thông báo tự động

### IX. Quản Lý Đơn Hàng

#### 1. Danh Sách Đơn Hàng
- **URL**: `?page=admin&module=orders`
- **Mục đích**: Quản lý đơn hàng khách hàng
- **Chức năng**:
  - Xem tất cả đơn hàng
  - Cập nhật trạng thái
  - Xem chi tiết đơn hàng
  - Xử lý hoàn trả
- **Sử dụng**:
  1. Truy cập module Orders
  2. Sử dụng bộ lọc để tìm đơn hàng
  3. Click vào đơn hàng để xem chi tiết
  4. Cập nhật trạng thái xử lý

### X. Quản Lý Thanh Toán

#### 1. Cấu Hình Thanh Toán
- **URL**: `?page=admin&module=settings&action=payment`
- **Mục đích**: Cấu hình gateway thanh toán
- **Chức năng**:
  - Cấu hình SePay
  - Cấu hình PayOS
  - Xem lịch sử giao dịch
- **Sử dụng**:
  1. Truy cập Settings > Payment
  2. Cập nhật API keys
  3. Test kết nối
  4. Lưu cấu hình

### XI. Quản Lý Nội Dung

#### 1. Quản Lý Trang
- **URL**: `?page=admin&module=pages`
- **Mục đích**: Quản lý các trang tĩnh
- **Chức năng**:
  - Sửa trang Giới thiệu
  - Cập nhật trang Liên hệ
  - Quản lý điều khoản
- **Sử dụng**:
  1. Chọn trang cần sửa
  2. Sử dụng editor để cập nhật
  3. Lưu thay đổi

### XII. Báo Cáo và Thống Kê

#### 1. Báo Cáo Doanh Thu
- **URL**: `?page=admin&module=revenue`
- **Mục đích**: Xem báo cáo tài chính
- **Chức năng**:
  - Báo cáo doanh thu theo ngày/tháng/năm
  - Báo cáo sản phẩm bán chạy
  - Báo cáo khách hàng
- **Sử dụng**:
  1. Chọn khoảng thời gian
  2. Xem biểu đồ và số liệu
  3. Export báo cáo

### XIII. Cấu Hình Hệ Thống

#### 1. Cài Đặt Chung
- **URL**: `?page=admin&module=settings`
- **Mục đích**: Cấu hình hệ thống
- **Chức năng**:
  - Cài đặt website
  - Cấu hình email
  - Quản lý SEO
- **Sử dụng**:
  1. Truy cập module Settings
  2. Cập nhật thông tin website
  3. Cấu hình email settings
  4. Lưu thay đổi

### XIV. Phân Quyền Admin

#### 1. Quyền Hạn Admin
- **Quản trị toàn bộ**: Truy cập tất cả các module
- **Quản trị sản phẩm**: Chỉ quản lý sản phẩm, danh mục
- **Quản trị nội dung**: Chỉ quản lý tin tức, trang
- **Quản trị người dùng**: Chỉ quản lý user, đại lý

#### 2. Tạo Admin Mới
1. Truy cập module Users
2. Click "Thêm người dùng"
3. Chọn role "Admin"
4. Gán quyền phù hợp
5. Lưu thông tin

---

## E. Lời Kết

Website ThuongLo.com được thiết kế để phục vụ đa dạng đối tượng người dùng với các chức năng phù hợp. Với hướng dẫn này, người dùng có thể dễ dàng tìm hiểu và sử dụng các tính năng của website một cách hiệu quả.

Để được hỗ trợ thêm, vui lòng liên hệ:
- **Email**: support@thuonglo.com
- **Điện thoại**: [Số điện thoại]
- **Địa chỉ**: [Địa chỉ công ty]

Cảm ơn đã sử dụng dịch vụ của ThuongLo.com!
