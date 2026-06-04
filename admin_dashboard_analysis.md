# Báo cáo Phân tích Đồng bộ Dữ liệu & Thống kê Admin Dashboard

Báo cáo này tập trung vào việc đối chiếu các khối thống kê (KPI cards), biểu đồ và bảng dữ liệu giữa hai giao diện chính của Admin: **Admin Dashboard** (`dashboard.php`) và **Báo cáo Doanh thu** (`revenue/index.php`), nhằm chỉ ra các điểm bất đồng bộ dữ liệu và các lỗi kỹ thuật hiện có.

---

## 1. Bảng Đối Chiếu Đồng Bộ Dữ Liệu & Thống Kê

| Chỉ số / Thành phần | Admin Dashboard (`dashboard.php`) | Báo cáo Doanh thu (`revenue/index.php`) | Đánh giá & Điểm bất hợp lý |
| :--- | :--- | :--- | :--- |
| **Tổng Doanh Thu** | Hiển thị doanh thu **Hoàn thành (completed)** dưới tên nhãn chung chung là `"Doanh thu (VNĐ)"`. | Hiển thị doanh thu **Của tất cả đơn hàng (tất cả trạng thái)** dưới tên `"Tổng Doanh Thu"`, và tách riêng `"Doanh Thu Hoàn Thành"`. | **Bất đồng bộ Cao**: Nhãn `"Doanh thu (VNĐ)"` trên Dashboard dễ gây hiểu lầm là tổng doanh thu, nhưng số liệu thực tế lại chỉ bằng `"Doanh Thu Hoàn Thành"` của trang Báo cáo. |
| **Định dạng Tiền tệ** | Sử dụng định dạng rút gọn không đồng nhất:<br>- Bản PHP: `15.2M`<br>- Bản AJAX/JS: `15.2 triệu` / `15.2 tỷ` / `15K`. | Luôn định dạng đầy đủ chữ số kèm đơn vị VND:<br>Ví dụ: `15.200.000 VNĐ`. | **Bất hợp lý về UX**: Các con số hiển thị khác kiểu dễ gây cảm giác dữ liệu không khớp khi đối chiếu nhanh giữa 2 tab. |
| **Top Sản Phẩm** | **Bị lỗi hiển thị**: Luôn báo *"Chưa có dữ liệu"* do backend PHP gán mặc định `$data['top_products'] = []`. <br><br>Nếu có dữ liệu, bảng chỉ cố hiển thị *Tên*, *Giá bán* và *Trạng thái hoạt động* (luôn là `active`). | Hiển thị đầy đủ bảng xếp hạng hiệu suất gồm:<br>- Thứ hạng (Rank badge)<br>- Tên & giá sản phẩm<br>- Số đơn đã bán (`sales_count`)<br>- Doanh thu mang lại (`revenue`)<br>- Tỷ lệ đóng góp (`percentage`) | **Lỗi Nghiêm trọng**: Bảng top sản phẩm trên Dashboard bị trống hoàn toàn. Định dạng cột của Dashboard cũng thiếu thông tin quan trọng nhất là số lượng đã bán và doanh thu thực tế của sản phẩm đó. |
| **Thẻ Thống Kê Sự Kiện** | **Bị ẩn trên giao diện**: Lưới thống kê trên Dashboard chỉ hiển thị 3 thẻ. Trong khi backend và JS vẫn tải và cố cập nhật dữ liệu vào phần tử `#stat-upcoming-events`. | Không hiển thị (phù hợp mục tiêu trang báo cáo tài chính). | **Lỗi Layout**: Làm mất cân đối giao diện (lưới 4 cột bị trống 1 cột) và lãng phí tài nguyên truy vấn dữ liệu từ database. |
| **Thống Kê Trạng Thái Đơn Hàng** | Biểu đồ Doughnut hiển thị tỷ lệ %, nhưng phần text chi tiết số đơn hàng của từng trạng thái bị lỗi hiển thị (do file JS cố gắng cập nhật các phần tử HTML `#orders-completed-count`... không tồn tại). | Hiển thị rõ ràng số đơn hàng cụ thể bên dưới các thẻ thống kê doanh thu theo trạng thái. | **Lỗi Javascript**: Cố cập nhật DOM của các phần tử không tồn tại trên Dashboard. |

---

## 2. Chi Tiết Lỗi Kỹ Thuật & Nguyên Nhân Code

### A. Lỗi bảng "Top sản phẩm" trên Dashboard bị trống
* **Nguyên nhân**: Trong `AdminService::getDashboardData()`, dòng 65 thiết lập:
  ```php
  $data['top_products'] = [];
  ```
  Nhưng file Javascript `admin_dashboard.js` chỉ vẽ biểu đồ cột `topProductsChart` chứ hoàn toàn **không** cập nhật phần danh sách HTML `.top-products-list`. Do đó, danh sách này mãi mãi trống rỗng.
* **Nguyên nhân thứ hai**: Dù có truyền dữ liệu từ PHP, hàm `buildTopProductsData()` trong `AdminService.php` không đưa trường `price` vào mảng kết quả `$products`, khiến mã PHP hiển thị bị lỗi định dạng giá.

### B. Thiếu Thẻ Thống Kê Thứ 4 ("Sự kiện sắp diễn ra")
* **Nguyên nhân**: Trong file `dashboard.php`, thẻ thống kê thứ 4 đã bị xóa bỏ hoặc bỏ quên, chỉ còn lại 3 thẻ: *Tổng sản phẩm*, *Doanh thu (VNĐ)*, *Tin tức đã xuất bản*. Tuy nhiên, trong file JS `admin_dashboard.js` vẫn có dòng cập nhật:
  ```javascript
  setText('stat-upcoming-events', stats.upcoming_events ?? 0);
  ```

### C. Lỗi Cập Nhật Số Lượng Đơn Hàng Trên Dashboard
* **Nguyên nhân**: File JS cố gắng thực hiện cập nhật số lượng đơn hàng chi tiết:
  ```javascript
  ['completed', 'processing', 'pending', 'cancelled'].forEach(status => {
      setText('orders-' + status + '-count', totals[status] ?? 0);
  });
  ```
  Tuy nhiên, trong file HTML `dashboard.php` không hề có các phần tử mang ID này, dẫn đến việc dữ liệu số lượng đơn hàng không được hiển thị cho quản trị viên.

---

## 3. Đề Xuất Giải Pháp Đồng Bộ & Hoàn Thiện

1. **Đồng bộ tên gọi & công thức Doanh thu**:
   - Đổi nhãn `"Doanh thu (VNĐ)"` trên Dashboard thành `"Doanh thu hoàn thành (VNĐ)"` để khớp chính xác với giá trị thực tế của nó (chỉ tính đơn hàng `completed`), giúp quản trị viên phân biệt rõ với `"Tổng Doanh Thu"` (bao gồm cả đơn hàng chờ xử lý, đang xử lý) trong trang Báo cáo Doanh thu.
   - Thống nhất định dạng tiền tệ: Hiển thị định dạng đầy đủ (ví dụ: `15.200.000 VNĐ`) hoặc định dạng rút gọn nhất quán kèm chú thích rõ ràng.

2. **Khôi phục thẻ thống kê sự kiện**:
   - Thêm thẻ thống kê thứ 4 mang tên `"Sự kiện sắp diễn ra"` vào giao diện `dashboard.php` để hiển thị dữ liệu `upcoming_events` đã được tính toán từ backend, giúp lưới giao diện hiển thị cân đối 4 cột như thiết kế ban đầu.

3. **Sửa lỗi và cập nhật bảng Top Sản Phẩm**:
   - Truyền dữ liệu `top_products` từ backend PHP bằng cách gọi hàm `$this->getDashboardTopProducts(5, '7days')` ngay trong `getDashboardData()`.
   - Bổ sung trường `price` vào mảng kết quả của `buildTopProductsData()`.
   - Cập nhật cấu trúc hiển thị của bảng Top Sản Phẩm trên Dashboard: Thay vì hiển thị trạng thái `active` vô ích, hãy hiển thị hiệu suất bán hàng thực tế (ví dụ: `"Đã bán: X đơn"` hoặc `"Doanh thu: Y VNĐ"`) tương tự như bảng của trang Báo cáo Doanh thu.

4. **Sửa lỗi DOM cập nhật số lượng đơn hàng**:
   - Bổ sung các phần tử HTML nhỏ bên dưới biểu đồ `"Phân loại đơn hàng"` trên Dashboard để hiển thị số lượng đơn hàng thực tế của từng trạng thái (ví dụ: *Hoàn thành: 10*, *Chờ xử lý: 3*...), tạo điểm neo cho file Javascript cập nhật đúng đích.
