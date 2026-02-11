## Giai đoạn 0: Nền tảng & Công cụ (Shared)

* [X] Tạo file `core/view_init.php` (Chuẩn hóa nạp Service)
* [X] Cập nhật

  core/functions.php (Bọc an toàn cho toàn bộ helper)

## Giai đoạn 1: Giao diện Khách hàng (Member 1 & 2 làm song song)

### Thành viên 1: Module Sản phẩm & Bán hàng

* [ ]

  app/views/home/home.php (Đã xong mẫu)
* [ ]

  app/views/products/products.php
* [ ]

  app/views/products/details.php
* [ ]

  app/views/categories/categories.php
* [ ] `app/views/payment/` (checkout.php, success.php, cancel.php)

### Thành viên 2: Module Tin tức & Giới thiệu

* [ ] `app/views/news/news.php`
* [ ] `app/views/news/news-details.php`
* [ ] `app/views/events/` (list.php, detail.php)
* [ ] `app/views/about/about.php`
* [ ] `app/views/contact/contact.php`

## Giai đoạn 2: User & Affiliate (Member 1 & 2 làm song song)

### Thành viên 1: Module Affiliate (Đại lý)

* [ ] Toàn bộ 14 file trong `app/views/affiliate/` (Dashboard, Money, Network, v.v.)

### Thành viên 2: Module User (Người dùng)

* [ ] Toàn bộ 5 file trong `app/views/auth/` (Login, Register, Reset password, v.v.)
* [ ] Toàn bộ 19 file trong `app/views/users/` (Profile, Orders, Notification, v.v.)

## Giai đoạn 3: Hệ thống Quản trị - Admin (Khối lượng lớn)

### Thành viên 1: Admin - Quản lý Kinh doanh

* [ ] Admin: Quản lý Sản phẩm & Danh mục
* [ ] Admin: Quản lý Đơn hàng & Thanh toán
* [ ] Admin: Quản lý Affiliate

### Thành viên 2: Admin - Quản lý Hệ thống & Nội dung

* [ ] Admin: Quản lý User & Phân quyền
* [ ] Admin: Quản lý Bài viết & Tin tức
* [ ] Admin: Cấu hình Website & Logs

## Giai đoạn 4: Layout & Tối ưu hóa (Shared)

* [ ] Chuẩn hóa `app/views/_layout/` (Header, Footer, Sidebar - 20 file)
* [ ] Kiểm tra SEO, Meta Tags và Breadcrumbs chung
* [ ] Chạy nghiệm thu kỹ thuật toàn sàn bằng `check_files.php`
