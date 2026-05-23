# Kế hoạch Triển khai URL Thuần Việt (SEO-friendly)

Tài liệu này vạch ra lộ trình chi tiết để chuyển đổi hệ thống URL cũ (dạng `?page=products`) sang hệ thống URL thuần Việt, chuẩn SEO (dạng `/san-pham`). Quá trình được chia thành 6 giai đoạn nhằm đảm bảo an toàn, dễ kiểm soát và không làm sập hệ thống hiện tại.

---

## Giai đoạn 1: Thiết lập Cấu hình & Trình tạo URL (URL Builder)
**Mục tiêu:** Định nghĩa bộ từ điển dịch URL (Mapping) và cấu hình lại class chịu trách nhiệm sinh ra các đường dẫn trên website.

| File cần sửa | Vị trí sửa đổi | Mục đích / Chi tiết công việc |
| :--- | :--- | :--- |
| `config.php` | Thêm vào cuối mảng config hiện tại | **Mục đích:** Tạo một trung tâm quản lý URL (Routing Map).<br>**Chi tiết:** Khai báo mảng ánh xạ như `'routes' => ['home' => '', 'products' => 'san-pham', 'about' => 'gioi-thieu', 'contact' => 'lien-he', 'cart' => 'gio-hang', ...]` |
| `core/UrlBuilder.php` | Method `page($page, $params = [])` | **Mục đích:** Giúp toàn bộ code gọi hàm tạo URL sẽ tự động sinh ra URL mới.<br>**Chi tiết:** Đọc mảng `routes` từ config. Thay vì return `?page=$page`, hệ thống sẽ return `/{route_cua_page}`. Nếu có tham số (như id, danh mục), nối thêm vào sau dạng `/san-pham/danh-muc-a`. |

---

## Giai đoạn 2: Nâng cấp Bộ Định Tuyến (Front Controller)
**Mục tiêu:** Giúp hệ thống PHP hiểu được URL mới (VD: `/san-pham`) thay vì chỉ hiểu `?page=products` như trước.

| File cần sửa | Vị trí sửa đổi | Mục đích / Chi tiết công việc |
| :--- | :--- | :--- |
| `.htaccess` | Khối `RewriteRule ^(.*)$ index.php [QSA,L]` | **Mục đích:** Chắc chắn mọi Request đều đổ về `index.php`.<br>**Chi tiết:** Code htaccess hiện tại của bạn cơ bản đã ổn, nhưng cần check lại xem có bỏ sót cấu hình tĩnh nào không. Nếu cần, cấu hình bắt regex dạng `RewriteRule ^([a-zA-Z0-9-]+)$ index.php?route=$1 [QSA,L]`. |
| `index.php` | Từ dòng 55 đến 75 (Khu vực gán biến `$page`) | **Mục đích:** Xử lý và dịch ngược URL tiếng Việt thành biến cục bộ hệ thống hiểu được.<br>**Chi tiết:** Đọc `$_SERVER['REQUEST_URI']`, cắt bỏ domain. Dùng mảng map trong `config.php` để đối chiếu (VD: thấy chữ `/san-pham` thì gán biến `$page = 'products'`). Xử lý tiếp các cấp URL sâu hơn (như chi tiết sản phẩm / danh mục). |

---

## Giai đoạn 3: Cập nhật Hệ thống Helpers & Breadcrumb
**Mục tiêu:** Xử lý triệt để các hàm trung gian đang trực tiếp in ra URL cũ.

| File cần sửa | Vị trí sửa đổi | Mục đích / Chi tiết công việc |
| :--- | :--- | :--- |
| `core/functions.php` | Hàm `generate_breadcrumb()` và các hàm tạo breadcrumb khác | **Mục đích:** Giúp đường dẫn phân trang (Breadcrumb) hiển thị link chuẩn SEO.<br>**Chi tiết:** Thay thế mảng hardcode `['url' => '?page=products']` thành `['url' => page_url('products')]` để nó kế thừa sức mạnh của UrlBuilder ở Giai đoạn 1. |
| `core/functions.php` | Các hàm helper (nếu có hardcode) | **Mục đích:** Dọn dẹp nợ kỹ thuật.<br>**Chi tiết:** Kiểm tra lại logic của hàm `get_product_breadcrumb_from_db` và `get_news_breadcrumb_from_db` để loại bỏ các string `?page=...` |

---

## Giai đoạn 4: Tái cấu trúc Giao diện & Template (Nặng nhất)
**Mục tiêu:** Thay đổi toàn bộ giao diện HTML để người dùng và Bot Google khi ấn vào/quét qua sẽ thấy URL mới.

| File cần sửa | Vị trí sửa đổi | Mục đích / Chi tiết công việc |
| :--- | :--- | :--- |
| `app/views/_layout/*.php` | Các file Header, Footer, Sidebar, Menu... | **Mục đích:** Sửa các thành phần hiển thị trên mọi trang.<br>**Chi tiết:** Tìm toàn bộ thẻ `<a href="?page=...">` và đổi thành `<a href="<?= page_url('...') ?>">`. |
| `app/views/**/*.php` | Mọi file view (Home, User, Auth, Products, News, Cart...) | **Mục đích:** Sửa link nội bộ trong nội dung trang.<br>**Chi tiết:** Scan qua hơn 100 vị trí hardcode `?page=` trong mã nguồn (vd: nút Mua ngay, xem giỏ hàng, đọc tiếp tin tức...) và áp dụng chuẩn gọi qua helper `page_url()`. |

---

## Giai đoạn 5: Cập nhật Controller & Chuyển hướng (Redirects)
**Mục tiêu:** Khi người dùng thực hiện xong hành động (Đăng nhập, Thêm vào giỏ, Thanh toán), hệ thống phải chuyển hướng họ sang URL mới.

| File cần sửa | Vị trí sửa đổi | Mục đích / Chi tiết công việc |
| :--- | :--- | :--- |
| `app/controllers/*.php` | Hàm `header("Location: ...")` | **Mục đích:** Đồng bộ logic điều hướng server-side.<br>**Chi tiết:** Tìm các đoạn mã `header('Location: ?page=login')` và sửa thành `header('Location: ' . page_url('login'))`. Nếu chuyển hướng có mang theo param (VD `?page=products&error=1`), cần xử lý để ghép param chuẩn với URL SEO (VD: `/san-pham?error=1`). |

---

## Giai đoạn 6: Kiểm thử & Xử lý Admin / API (Edge Cases)
**Mục tiêu:** Rà soát lại hệ thống để đảm bảo việc viết lại URL cho frontend không làm hỏng backend.

| File / Phân hệ | Vị trí sửa đổi | Mục đích / Chi tiết công việc |
| :--- | :--- | :--- |
| **Trang Admin** | `index.php` (Đoạn bắt `admin/`) | **Mục đích:** Giữ cho URL trang admin (`/admin/dashboard`) hoạt động ổn định.<br>**Chi tiết:** Đảm bảo bộ Router mới ở Giai đoạn 2 bỏ qua (bypass) nếu phân đoạn đầu tiên của URL là `admin`. Giữ nguyên logic xử lý Admin hiện có. |
| **API Backend** | `api.php` | **Mục đích:** API không bị redirect nhầm.<br>**Chi tiết:** Kiểm tra AJAX calls trong thư mục `assets/js/*.js` xem có bị ảnh hưởng bởi định tuyến mới không (thường thì file `.htaccess` đã cô lập `api.php` nên sẽ an toàn, nhưng cần test kĩ). |
| **Trang 404** | Router (`index.php`) | **Mục đích:** Xử lý link chết.<br>**Chi tiết:** Thêm logic vào `index.php` nếu URL người dùng nhập không map được với bất kì Route nào trong cấu hình thì trả về Giao diện 404 Not Found kèm Header HTTP 404. |

---

> **💡 Khuyến nghị khi tiến hành:**
> Nên thực hiện tuần tự từ **Giai đoạn 1 đến Giai đoạn 3** trước, sau đó truy cập thử các link mới trên trình duyệt bằng cách gõ tay. Nếu hệ thống nhận diện đúng, ta mới bắt đầu thực hiện **Giai đoạn 4 và 5** để tiến hành thay đổi giao diện và Controller hàng loạt.
