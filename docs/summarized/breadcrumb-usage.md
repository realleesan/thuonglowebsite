# Breadcrumb Component - Hướng dẫn sử dụng

## Tổng quan

Breadcrumb component được quản lý tập trung tại `index.php` và `master.php`, đảm bảo tính đồng bộ và dễ bảo trì.

## Kiến trúc mới

### 1. Quản lý tập trung tại index.php
- Mỗi trang được cấu hình `$showBreadcrumb = true/false`
- Breadcrumb data được tạo tại index.php
- Không cần code breadcrumb trong từng trang con

### 2. Hiển thị tại master.php
- Breadcrumb được render tự động nếu `$showBreadcrumb = true`
- Vị trí cố định: sau header, trước page content

### 3. Trang con chỉ chứa nội dung chính
- Không cần include functions
- Không cần tạo breadcrumb data
- Code sạch hơn, tập trung vào nội dung

## Cấu trúc files

```
index.php                           # Quản lý breadcrumb tập trung
app/views/_layout/master.php        # Hiển thị breadcrumb có điều kiện
app/views/_layout/breadcrumb.php    # Component chính
assets/css/breadcrumb.css           # Styles
assets/js/breadcrumb.js             # JavaScript functionality
core/functions.php                  # Helper functions
```

## Cách thêm trang mới

### 1. Thêm case mới trong index.php

```php
case 'new-page':
    $title = 'Trang mới - Thuong Lo';
    $content = 'app/views/new-page/new-page.php';
    $showPageHeader = true;
    $showCTA = false;
    $showBreadcrumb = true; // Bật breadcrumb
    $breadcrumbs = generate_breadcrumb('new-page'); // Tạo breadcrumb
    break;
```

### 2. Tạo file trang con

```php
<!-- app/views/new-page/new-page.php -->
<div class="new-page-content">
    <!-- Chỉ chứa nội dung chính, không cần breadcrumb -->
    <h1>Nội dung trang mới</h1>
</div>
```

### 3. Cập nhật helper function (nếu cần)

```php
// Trong core/functions.php
$page_mapping = [
    // ...existing pages
    'new-page' => [
        ['title' => 'Trang mới']
    ],
];
```

## Các loại breadcrumb

### 1. Breadcrumb đơn giản

```php
$showBreadcrumb = true;
$breadcrumbs = generate_breadcrumb('about');
// Kết quả: Trang chủ > Giới thiệu
```

### 2. Breadcrumb với tham số URL

```php
$category = $_GET['category'] ?? 'Default Category';
$breadcrumbs = generate_product_breadcrumb($category);
// Kết quả: Trang chủ > Sản phẩm > [Category]
```

### 3. Breadcrumb từ database

```php
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $breadcrumbs = get_product_breadcrumb_from_db($_GET['id']);
} else {
    $breadcrumbs = generate_product_breadcrumb('Default', 'Default Product');
}
```

### 4. Breadcrumb tùy chỉnh hoàn toàn

```php
$breadcrumbs = [
    ['title' => 'Trang chủ', 'url' => './'],
    ['title' => 'Tài khoản', 'url' => '?page=account'],
    ['title' => 'Cài đặt', 'url' => '?page=account&tab=settings'],
    ['title' => 'Bảo mật']
];
```

## Ví dụ thực tế

### Trang sản phẩm với filter

```php
case 'products':
    $title = 'Sản phẩm - Thuong Lo';
    $content = 'app/views/products/products.php';
    $showBreadcrumb = true;
    
    // Breadcrumb với category filter
    $category = $_GET['category'] ?? '';
    if ($category) {
        $breadcrumbs = [
            ['title' => 'Trang chủ', 'url' => './'],
            ['title' => 'Sản phẩm', 'url' => '?page=products'],
            ['title' => $category]
        ];
    } else {
        $breadcrumbs = generate_breadcrumb('products');
    }
    break;
```

### Trang chi tiết với database

```php
case 'product-details':
    $product_id = $_GET['id'] ?? 0;
    $title = 'Chi tiết sản phẩm - Thuong Lo';
    $content = 'app/views/products/details.php';
    $showBreadcrumb = true;
    
    if ($product_id) {
        // Lấy từ database
        $breadcrumbs = get_product_breadcrumb_from_db($product_id);
        
        // Cập nhật title từ database
        $product = get_product_by_id($product_id);
        if ($product) {
            $title = $product['name'] . ' - Thuong Lo';
        }
    } else {
        $breadcrumbs = generate_breadcrumb('products');
    }
    break;
```

## Lợi ích của kiến trúc mới

### ✅ Ưu điểm

1. **Quản lý tập trung**: Tất cả breadcrumb logic ở một nơi
2. **Code sạch**: Trang con chỉ chứa nội dung chính
3. **Dễ bảo trì**: Thay đổi breadcrumb chỉ cần sửa index.php
4. **Tính nhất quán**: Đảm bảo tất cả trang có cùng format
5. **Linh hoạt**: Dễ dàng bật/tắt breadcrumb cho từng trang
6. **SEO friendly**: Breadcrumb được render server-side
7. **Performance**: Không cần include functions trong mỗi trang

### ✅ So sánh với cách cũ

| Cách cũ | Cách mới |
|---------|----------|
| Mỗi trang tự tạo breadcrumb | Tập trung tại index.php |
| Code lặp lại nhiều lần | Code một lần, dùng nhiều nơi |
| Khó đồng bộ thiết kế | Đảm bảo tính nhất quán |
| Trang con phức tạp | Trang con đơn giản |
| Khó debug và maintain | Dễ debug và maintain |

## Helper Functions

### Database Functions

```php
// Lấy breadcrumb từ database
function get_product_breadcrumb_from_db($product_id) {
    // Implementation với database connection
    // Fallback nếu không lấy được data
}

function get_news_breadcrumb_from_db($news_id) {
    // Implementation với database connection
    // Fallback nếu không lấy được data
}
```

### Utility Functions

```php
// Tạo breadcrumb tự động
function generate_breadcrumb($page, $additional_items = [])

// Tạo breadcrumb cho sản phẩm
function generate_product_breadcrumb($category, $product = '')

// Tạo breadcrumb cho tin tức
function generate_news_breadcrumb($category, $title = '')

// Render breadcrumb
function render_breadcrumb($breadcrumbs, $return = false)
```

## Best Practices

1. **Luôn set `$showBreadcrumb`** cho mỗi trang trong index.php
2. **Trang chủ không cần breadcrumb** (`$showBreadcrumb = false`)
3. **Sử dụng database functions** khi có ID trong URL
4. **Fallback cho trường hợp lỗi** database
5. **Validate input parameters** trước khi tạo breadcrumb
6. **Cache breadcrumb data** nếu cần thiết cho performance

## Troubleshooting

### Breadcrumb không hiển thị
- Kiểm tra `$showBreadcrumb = true` trong index.php
- Kiểm tra `$breadcrumbs` có data không

### Breadcrumb sai nội dung
- Kiểm tra logic tạo breadcrumb trong index.php
- Kiểm tra helper functions

### Database breadcrumb lỗi
- Kiểm tra database connection
- Kiểm tra fallback logic hoạt động