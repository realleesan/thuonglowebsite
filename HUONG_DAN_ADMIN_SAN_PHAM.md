# Hướng Dẫn Quản Lý Sản Phẩm Và Dữ Liệu - Admin

## Mục Lục
1. [Tổng Quan](#tổng-quan)
2. [Quản Lý Sản Phẩm](#quản-lý-sản-phẩm)
   - [Danh Sách Sản Phẩm](#danh-sách-sản-phẩm)
   - [Thêm Sản Phẩm Mới](#thêm-sản-phẩm-mới)
   - [Chỉnh Sửa Sản Phẩm](#chỉnh-sửa-sản-phẩm)
   - [Xóa Sản Phẩm](#xóa-sản-phẩm)
   - [Xem Chi Tiết](#xem-chi-tiết)
3. [Quản Lý Dữ Liệu Sản Phẩm](#quản-lý-dữ-liệu-sản-phẩm)
   - [Truy Cập Trang Dữ Liệu](#truy-cập-trang-dữ-liệu)
   - [Thêm Dữ Liệu Thủ Công](#thêm-dữ-liệu-thủ-công)
   - [Chỉnh Sửa Dữ Liệu](#chỉnh-sửa-dữ-liệu)
   - [Xóa Dữ Liệu](#xóa-dữ-liệu)
   - [Import Dữ Liệu Từ Excel](#import-dữ-liệu-từ-excel)
4. [Cấu Trúc File Excel Import](#cấu-trúc-file-excel-import)
5. [Các Thao Tác Khác](#các-thao-tác-khác)

---

## Tổng Quan

Hệ thống quản lý sản phẩm và dữ liệu của ThuongLo bao gồm hai phần chính:

1. **Quản lý sản phẩm**: Quản lý thông tin cơ bản của sản phẩm (tên, giá, danh mục, mô tả, hình ảnh...)
2. **Quản lý dữ liệu**: Quản lý dữ liệu bổ sung như thông tin nhà cung cấp, địa chỉ, số điện thoại, WeChat...

### Menu Sidebar

```
📦 Sản phẩm
   ├── 📋 Danh sách     → Danh sách sản phẩm
   └── 🗄️ Dữ liệu      → Quản lý dữ liệu sản phẩm
```

---

## Quản Lý Sản Phẩm

### Danh Sách Sản Phẩm

**Đường dẫn**: `?page=admin&module=products`

**Tính năng**:
- Hiển thị danh sách tất cả sản phẩm
- Tìm kiếm sản phẩm theo tên hoặc SKU
- Lọc theo danh mục
- Lọc theo trạng thái (Hoạt động/Không hoạt động)
- Phân trang (15 sản phẩm/trang)

**Các cột hiển thị**:
| Cột | Mô tả |
|-----|-------|
| ID | Mã sản phẩm |
| Hình Ảnh | Hình đại diện sản phẩm |
| Tên Sản Phẩm | Tên sản phẩm |
| Danh Mục | Danh mục sản phẩm |
| Loại | Loại sản phẩm (Data/Khóa học/Công cụ...) |
| Giá | Giá bán (VNĐ) |
| Trạng Thái | Hoạt động/Không hoạt động |
| Ngày Tạo | Ngày tạo sản phẩm |
| Thao Tác | Các nút hành động |

**Nút thao tác**:
- 👁️ **Xem**: Xem chi tiết sản phẩm
- ✏️ **Sửa**: Chỉnh sửa thông tin sản phẩm
- 🗑️ **Xóa**: Xóa sản phẩm

---

### Thêm Sản Phẩm Mới

**Đường dẫn**: `?page=admin&module=products&action=add`

**Các trường thông tin cần nhập**:

| Trường | Bắt buộc | Mô tả |
|--------|----------|-------|
| Tên sản phẩm | ✅ | Tên sản phẩm |
| SKU | ✅ | Mã sản phẩm (duy nhất) |
| Danh mục | ✅ | Chọn danh mục sản phẩm |
| Loại sản phẩm | ✅ | Data Nguồn Hàng/Khóa Học/Công Cụ/Dịch Vụ/Vận Chuyển |
| Giá bán | ✅ | Giá sản phẩm (VNĐ) |
| Giá gốc | | Giá gốc để tính giảm giá |
| Giảm giá (%) | | Phần trăm giảm giá |
| Mô tả ngắn | | Mô tả ngắn về sản phẩm |
| Mô tả chi tiết | | Mô tả đầy đủ (hỗ trợ HTML) |
| Hình ảnh | | Upload hình đại diện sản phẩm |
| Album | | Thư viện hình ảnh sản phẩm |
| Trạng thái | | Hoạt động/Không hoạt động |

**Các trường bổ sung (tùy loại)**:

*Data Nguồn Hàng:*
- Số lượng data
- Nguồn data
- Định dạng file
- Dung lượng

*Khóa Học:*
- Số bài học
- Thời lượng
- Cấp độ
- Giảng viên

**Sau khi thêm**:
- Nhấn "Lưu sản phẩm" để lưu
- Hệ thống sẽ chuyển về danh sách sản phẩm
- Thông báo thành công hoặc lỗi

---

### Chỉnh Sửa Sản Phẩm

**Đường dẫn**: `?page=admin&module=products&action=edit&id={ID}`

**Các tab thông tin**:

1. **Tab 1 - Thông tin cơ bản**: Tên, SKU, danh mục, loại, giá
2. **Tab 2 - Mô tả**: Mô tả ngắn, mô tả chi tiết
3. **Tab 3 - Hình ảnh**: Hình đại diện, album
4. **Tab 4 - SEO**: Meta title, meta description, từ khóa
5. **Tab 5 - Kho & Giá**: Số lượng, giá gốc, giảm giá
6. **Tab 6 - Vận chuyển**: Cân nặng, kích thước, phí vận chuyển
7. **Tab 7 - Thuộc tính**: Các thuộc tính tùy chỉnh
8. **Tab 9 - Review**: Quản lý đánh giá sản phẩm

**Lưu ý**: Tab 8 (Dữ liệu) đã được chuyển sang trang quản lý dữ liệu riêng.

---

### Xóa Sản Phẩm

**Cách thực hiện**:
1. Từ danh sách sản phẩm, nhấn nút 🗑️ **Xóa** trên sản phẩm cần xóa
2. Hệ thống hiển thị thông báo xác nhận
3. Nhấn "Xác nhận xóa" để hoặc "Hủy" để hủy thao tác

**Lưu ý**:
- Khi xóa sản phẩm, dữ liệu sản phẩm cũng bị xóa theo
- Không thể khôi phục sau khi xóa

---

### Xem Chi Tiết

**Đường dẫn**: `?page=admin&module=products&action=view&id={ID}`

Hiển thị toàn bộ thông tin sản phẩm ở chế độ chỉ đọc.

---

## Quản Lý Dữ Liệu Sản Phẩm

### Truy Cập Trang Dữ Liệu

**Đường dẫn**: `?page=admin&module=products&action=data`

**Luồng hoạt động**:

```
1. Admin truy cập menu "Dữ liệu" trong sidebar
   ↓
2. Hiển thị danh sách sản phẩm (nếu chưa chọn sản phẩm)
   ↓
3. Admin chọn sản phẩm cần quản lý dữ liệu
   ↓
4. Hiển thị giao diện quản lý dữ liệu cho sản phẩm đó
```

**Giao diện gồm 3 tab**:
1. **Thêm mới**: Thêm dữ liệu thủ công
2. **Danh sách**: Xem danh sách dữ liệu đã thêm
3. **Import**: Import từ file Excel

---

### Thêm Dữ Liệu Thủ Công

**Các trường dữ liệu**:

| Trường | Mô tả | Ví dụ |
|--------|-------|-------|
| Nhà cung cấp | Tên nhà cung cấp | Công ty ABC |
| Địa chỉ | Địa chỉ liên hệ | 123 Đường Nguyễn Trãi, Quận 1, TP.HCM |
| WeChat | Tài khoản WeChat | wechat123 |
| Số điện thoại | Số điện thoại liên hệ | 0912345678 |
| QR WeChat | URL hình ảnh QR WeChat | https://example.com/qr/wechat.png |

**Cách thực hiện**:
1. Chọn sản phẩm từ danh sách
2. Nhấn tab "Thêm mới"
3. Điền thông tin vào các trường
4. Nhấn "Lưu" để lưu dữ liệu

---

### Chỉnh Sửa Dữ Liệu

**Cách thực hiện**:
1. Từ tab "Danh sách", nhấn nút ✏️ **Sửa** trên dòng dữ liệu cần sửa
2. Form chỉnh sửa hiển thị với thông tin hiện tại
3. Thay đổi thông tin cần thiết
4. Nhấn "Cập nhật" để lưu thay đổi

---

### Xóa Dữ Liệu

**Cách thực hiện**:
1. Từ tab "Danh sách", nhấn nút 🗑️ **Xóa** trên dòng dữ liệu cần xóa
2. Hệ thống hiển thị thông báo xác nhận
3. Nhấn "Xác nhận" để xóa

---

### Import Dữ Liệu Từ Excel

**Định dạng file hỗ trợ**:
- `.xlsx` (Excel 2007 trở lên)
- `.csv` (Comma Separated Values)

**Cách thực hiện**:
1. Chọn sản phẩm cần import dữ liệu
2. Nhấn tab "Import"
3. Chọn file Excel (.xlsx hoặc .csv)
4. Nhấn "Tải lên và xem trước"
5. Hệ thống hiển thị preview dữ liệu
6. Kiểm tra dữ liệu, nhấn "Xác nhận import" để hoàn tất

---

## Cấu Trúc File Excel Import

### Header bắt buộc

File Excel cần có các cột sau (tên tiếng Việt hoặc tiếng Anh):

| Tên tiếng Việt | Tên tiếng Anh | Mô tả |
|----------------|---------------|-------|
| Nhà cung cấp | Supplier/Vendor | Tên nhà cung cấp |
| Địa chỉ | Address/Location | Địa chỉ liên hệ |
| WeChat | WeChat/WX | Tài khoản WeChat |
| Số điện thoại | Phone/Mobile | Số điện thoại liên hệ |
| QR WeChat URL | QR WeChat URL | URL hình ảnh QR WeChat |

### Ví dụ file Excel

```excel
| Nhà cung cấp     | Địa chỉ                    | WeChat    | Số điện thoại | QR WeChat URL                    |
|-------------------|----------------------------|-----------|---------------|----------------------------------|
| Công ty ABC       | TP.HCM, Việt Nam           | abc123    | 0912345678    | https://example.com/qr/abc.png   |
| Công ty XYZ       | Hà Nội, Việt Nam           | xyz456    | 0987654321    | https://example.com/qr/xyz.png   |
```

### Quy tắt đặt tên cột

- Tên cột có thể viết hoa hoặc thường
- Có thể có hoặc không dấu
- Hệ thống tự động nhận diện các cột thông qua việc tìm kiếm từ khóa

### Lưu ý khi import

1. **Dòng đầu tiên** phải là header (tên cột)
2. **Cột Nhà cung cấp** là bắt buộc - dòng nào không có nhà cung cấp sẽ bị bỏ qua
3. **Hệ thống xử lý từng dòng** - dòng hợp lệ được import, dòng lỗi được bỏ qua
4. **Thông báo kết quả** hiển thị sau khi import hoàn tất

---

## Các Thao Tác Khác

### Tìm Kiếm

**Trong danh sách sản phẩm**:
- Nhập từ khóa vào ô tìm kiếm
- Tìm theo tên sản phẩm hoặc SKU
- Nhấn Enter hoặc nút tìm kiếm

**Trong trang dữ liệu**:
- Nhập từ khóa để lọc sản phẩm
- Lọc theo danh mục sản phẩm

### Lọc

**Lọc theo danh mục**:
- Chọn danh mục từ dropdown
- Áp dụng ngay lập tức

**Lọc theo trạng thái** (trong danh sách sản phẩm):
- Hoạt động
- Không hoạt động
- Tất cả

### Phân Trang

- Sản phẩm: 15 sản phẩm/trang
- Dữ liệu: 20 sản phẩm/trang (danh sách), 10 dữ liệu/trang (chi tiết)
- Sử dụng các nút "Trước", "Sau" hoặc số trang để di chuyển

---

## Mẹo Và Lưu Ý

1. **Sau khi tạo sản phẩm mới**, hãy thêm dữ liệu bổ sung trong mục "Dữ liệu" để cung cấp thông tin đầy đủ cho khách hàng

2. **Import Excel** tiện lợi khi cần thêm nhiều dữ liệu cùng lúc

3. **Kiểm tra kỹ** dữ liệu trước khi import để tránh lỗi

4. **Sử dụng SKU duy nhất** cho mỗi sản phẩm để tránh trùng lặp

5. **Cập nhật thường xuyên** dữ liệu sản phẩm để đảm bảo thông tin chính xác

---

## Giải Quyết Sự Cố

### Lỗi thường gặp

| Lỗi | Nguyên nhân | Cách khắc phục |
|------|-------------|----------------|
| Không tìm thấy trang | Sai đường dẫn | Kiểm tra lại URL |
| Import thất bại | File không đúng định dạng | Sử dụng file .xlsx hoặc .csv |
| Dữ liệu trống | Header không khớp | Kiểm tra tên cột trong file Excel |
| Không lưu được | Thiếu trường bắt buộc | Điền đầy đủ các trường bắt buộc |

---

## Liên Hệ Hỗ Trợ

Nếu gặp vấn đề khi sử dụng, vui lòng liên hệ đội kỹ thuật để được hỗ trợ.
