# Kế Hoạch Triển Khai Trang Quản Trị Admin ThuongLo

## Mục Tiêu

Xây dựng trang quản trị admin chuyên nghiệp cho website ThuongLo với các tính năng:

* **Dashboard** tổng hợp với biểu đồ Chart.js
* **10 modules quản lý** : Products, Categories, News, Events, Orders, Users, Affiliates, Contact, Revenue, Settings
* **Giao diện đồng bộ** với website chính (màu sắc, font chữ, icon FontAwesome)
* **Responsive design** trên mọi thiết bị
* **Dữ liệu demo** từ fake_data.json

## Hệ Thống Thiết Kế

### Màu Sắc (từ website chính)

* **Primary Color** : `#356DF1` (màu xanh chủ đạo)
* **Secondary Color** : `#000000` (màu đen hover)
* **Text Color** : `#374151` (màu chữ chính)
* **Text Secondary** : `#6B7280` (màu chữ phụ)
* **Background** : `#ffffff` (nền trắng)
* **Border** : `#E5E7EB` (viền nhẹ)
* **Success** : `#10B981` (xanh lá)
* **Warning** : `#F59E0B` (cam)
* **Danger** : `#EF4444` (đỏ)
* **Info** : `#3B82F6` (xanh dương)

### Typography

* **Font Family** : `'Inter', sans-serif`
* **Font Sizes** : 14px (base), 16px, 18px, 20px, 24px, 36px, 48px
* **Font Weights** : 400 (normal), 500 (medium), 600 (semibold), 700 (bold)

### Icons

* **FontAwesome 5.x** (solid, regular, brands)
* Sử dụng class `fas`, `far`, `fab`

### Spacing

* **Gap/Padding** : 8px, 12px, 16px, 20px, 24px, 32px, 40px, 60px, 80px
* **Border Radius** : 4px, 8px, 10px, 12px

## Cấu Trúc Dữ Liệu Fake Data

### fake_data.json Schema

<pre><div node="[object Object]" class="relative whitespace-pre-wrap word-break-all my-2 rounded-lg bg-list-hover-subtle border border-gray-500/20"><div class="min-h-7 relative box-border flex flex-row items-center justify-between rounded-t border-b border-gray-500/20 px-2 py-0.5"><div class="font-sans text-sm text-ide-text-color opacity-60">json</div><div class="flex flex-row gap-2 justify-end"><div class="cursor-pointer opacity-70 hover:opacity-100"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" class="lucide lucide-copy h-3.5 w-3.5"><rect width="14" height="14" x="8" y="8" rx="2" ry="2"></rect><path d="M4 16c-1.1 0-2-.9-2-2V4c0-1.1.9-2 2-2h10c1.1 0 2 .9 2 2"></path></svg></div></div></div><div class="p-3"><div class="w-full h-full text-xs cursor-text"><div class="code-block"><div class="code-line" data-line-number="1" data-line-start="1" data-line-end="1"><div class="line-content"><span class="mtk1">{</span></div></div><div class="code-line" data-line-number="2" data-line-start="2" data-line-end="2"><div class="line-content"><span class="mtk1"></span><span class="mtk3">"products"</span><span class="mtk1">: [</span></div></div><div class="code-line" data-line-number="3" data-line-start="3" data-line-end="3"><div class="line-content"><span class="mtk1">    {</span></div></div><div class="code-line" data-line-number="4" data-line-start="4" data-line-end="4"><div class="line-content"><span class="mtk1"></span><span class="mtk3">"id"</span><span class="mtk1">: </span><span class="mtk5">1</span><span class="mtk1">,</span></div></div><div class="code-line" data-line-number="5" data-line-start="5" data-line-end="5"><div class="line-content"><span class="mtk1"></span><span class="mtk3">"name"</span><span class="mtk1">: </span><span class="mtk9">"Gói Data Nguồn Hàng Premium"</span><span class="mtk1">,</span></div></div><div class="code-line" data-line-number="6" data-line-start="6" data-line-end="6"><div class="line-content"><span class="mtk1"></span><span class="mtk3">"category_id"</span><span class="mtk1">: </span><span class="mtk5">1</span><span class="mtk1">,</span></div></div><div class="code-line" data-line-number="7" data-line-start="7" data-line-end="7"><div class="line-content"><span class="mtk1"></span><span class="mtk3">"price"</span><span class="mtk1">: </span><span class="mtk5">5000000</span><span class="mtk1">,</span></div></div><div class="code-line" data-line-number="8" data-line-start="8" data-line-end="8"><div class="line-content"><span class="mtk1"></span><span class="mtk3">"stock"</span><span class="mtk1">: </span><span class="mtk5">100</span><span class="mtk1">,</span></div></div><div class="code-line" data-line-number="9" data-line-start="9" data-line-end="9"><div class="line-content"><span class="mtk1"></span><span class="mtk3">"status"</span><span class="mtk1">: </span><span class="mtk9">"active"</span><span class="mtk1">,</span></div></div><div class="code-line" data-line-number="10" data-line-start="10" data-line-end="10"><div class="line-content"><span class="mtk1"></span><span class="mtk3">"description"</span><span class="mtk1">: </span><span class="mtk9">"..."</span><span class="mtk1">,</span></div></div><div class="code-line" data-line-number="11" data-line-start="11" data-line-end="11"><div class="line-content"><span class="mtk1"></span><span class="mtk3">"image"</span><span class="mtk1">: </span><span class="mtk9">"..."</span><span class="mtk1">,</span></div></div><div class="code-line" data-line-number="12" data-line-start="12" data-line-end="12"><div class="line-content"><span class="mtk1"></span><span class="mtk3">"created_at"</span><span class="mtk1">: </span><span class="mtk9">"2024-01-01 10:00:00"</span></div></div><div class="code-line" data-line-number="13" data-line-start="13" data-line-end="13"><div class="line-content"><span class="mtk1">    }</span></div></div><div class="code-line" data-line-number="14" data-line-start="14" data-line-end="14"><div class="line-content"><span class="mtk1">  ],</span></div></div><div class="code-line" data-line-number="15" data-line-start="15" data-line-end="15"><div class="line-content"><span class="mtk1"></span><span class="mtk3">"categories"</span><span class="mtk1">: [</span></div></div><div class="code-line" data-line-number="16" data-line-start="16" data-line-end="16"><div class="line-content"><span class="mtk1">    {</span></div></div><div class="code-line" data-line-number="17" data-line-start="17" data-line-end="17"><div class="line-content"><span class="mtk1"></span><span class="mtk3">"id"</span><span class="mtk1">: </span><span class="mtk5">1</span><span class="mtk1">,</span></div></div><div class="code-line" data-line-number="18" data-line-start="18" data-line-end="18"><div class="line-content"><span class="mtk1"></span><span class="mtk3">"name"</span><span class="mtk1">: </span><span class="mtk9">"Data Nguồn Hàng"</span><span class="mtk1">,</span></div></div><div class="code-line" data-line-number="19" data-line-start="19" data-line-end="19"><div class="line-content"><span class="mtk1"></span><span class="mtk3">"slug"</span><span class="mtk1">: </span><span class="mtk9">"data-nguon-hang"</span><span class="mtk1">,</span></div></div><div class="code-line" data-line-number="20" data-line-start="20" data-line-end="20"><div class="line-content"><span class="mtk1"></span><span class="mtk3">"description"</span><span class="mtk1">: </span><span class="mtk9">"..."</span><span class="mtk1">,</span></div></div><div class="code-line" data-line-number="21" data-line-start="21" data-line-end="21"><div class="line-content"><span class="mtk1"></span><span class="mtk3">"status"</span><span class="mtk1">: </span><span class="mtk9">"active"</span><span class="mtk1">,</span></div></div><div class="code-line" data-line-number="22" data-line-start="22" data-line-end="22"><div class="line-content"><span class="mtk1"></span><span class="mtk3">"created_at"</span><span class="mtk1">: </span><span class="mtk9">"2024-01-01 10:00:00"</span></div></div><div class="code-line" data-line-number="23" data-line-start="23" data-line-end="23"><div class="line-content"><span class="mtk1">    }</span></div></div><div class="code-line" data-line-number="24" data-line-start="24" data-line-end="24"><div class="line-content"><span class="mtk1">  ],</span></div></div><div class="code-line" data-line-number="25" data-line-start="25" data-line-end="25"><div class="line-content"><span class="mtk1"></span><span class="mtk3">"news"</span><span class="mtk1">: [</span><span class="mtk11">...</span><span class="mtk1">],</span></div></div><div class="code-line" data-line-number="26" data-line-start="26" data-line-end="26"><div class="line-content"><span class="mtk1"></span><span class="mtk3">"events"</span><span class="mtk1">: [</span><span class="mtk11">...</span><span class="mtk1">],</span></div></div><div class="code-line" data-line-number="27" data-line-start="27" data-line-end="27"><div class="line-content"><span class="mtk1"></span><span class="mtk3">"orders"</span><span class="mtk1">: [</span></div></div><div class="code-line" data-line-number="28" data-line-start="28" data-line-end="28"><div class="line-content"><span class="mtk1">    {</span></div></div><div class="code-line" data-line-number="29" data-line-start="29" data-line-end="29"><div class="line-content"><span class="mtk1"></span><span class="mtk3">"id"</span><span class="mtk1">: </span><span class="mtk5">1</span><span class="mtk1">,</span></div></div><div class="code-line" data-line-number="30" data-line-start="30" data-line-end="30"><div class="line-content"><span class="mtk1"></span><span class="mtk3">"user_id"</span><span class="mtk1">: </span><span class="mtk5">1</span><span class="mtk1">,</span></div></div><div class="code-line" data-line-number="31" data-line-start="31" data-line-end="31"><div class="line-content"><span class="mtk1"></span><span class="mtk3">"product_id"</span><span class="mtk1">: </span><span class="mtk5">1</span><span class="mtk1">,</span></div></div><div class="code-line" data-line-number="32" data-line-start="32" data-line-end="32"><div class="line-content"><span class="mtk1"></span><span class="mtk3">"quantity"</span><span class="mtk1">: </span><span class="mtk5">1</span><span class="mtk1">,</span></div></div><div class="code-line" data-line-number="33" data-line-start="33" data-line-end="33"><div class="line-content"><span class="mtk1"></span><span class="mtk3">"total"</span><span class="mtk1">: </span><span class="mtk5">5000000</span><span class="mtk1">,</span></div></div><div class="code-line" data-line-number="34" data-line-start="34" data-line-end="34"><div class="line-content"><span class="mtk1"></span><span class="mtk3">"status"</span><span class="mtk1">: </span><span class="mtk9">"pending|processing|completed|cancelled"</span><span class="mtk1">,</span></div></div><div class="code-line" data-line-number="35" data-line-start="35" data-line-end="35"><div class="line-content"><span class="mtk1"></span><span class="mtk3">"created_at"</span><span class="mtk1">: </span><span class="mtk9">"..."</span></div></div><div class="code-line" data-line-number="36" data-line-start="36" data-line-end="36"><div class="line-content"><span class="mtk1">    }</span></div></div><div class="code-line" data-line-number="37" data-line-start="37" data-line-end="37"><div class="line-content"><span class="mtk1">  ],</span></div></div><div class="code-line" data-line-number="38" data-line-start="38" data-line-end="38"><div class="line-content"><span class="mtk1"></span><span class="mtk3">"users"</span><span class="mtk1">: [</span></div></div><div class="code-line" data-line-number="39" data-line-start="39" data-line-end="39"><div class="line-content"><span class="mtk1">    {</span></div></div><div class="code-line" data-line-number="40" data-line-start="40" data-line-end="40"><div class="line-content"><span class="mtk1"></span><span class="mtk3">"id"</span><span class="mtk1">: </span><span class="mtk5">1</span><span class="mtk1">,</span></div></div><div class="code-line" data-line-number="41" data-line-start="41" data-line-end="41"><div class="line-content"><span class="mtk1"></span><span class="mtk3">"name"</span><span class="mtk1">: </span><span class="mtk9">"Admin"</span><span class="mtk1">,</span></div></div><div class="code-line" data-line-number="42" data-line-start="42" data-line-end="42"><div class="line-content"><span class="mtk1"></span><span class="mtk3">"email"</span><span class="mtk1">: </span><span class="mtk9">"admin@thuonglo.com"</span><span class="mtk1">,</span></div></div><div class="code-line" data-line-number="43" data-line-start="43" data-line-end="43"><div class="line-content"><span class="mtk1"></span><span class="mtk3">"role"</span><span class="mtk1">: </span><span class="mtk9">"admin|user|agent"</span><span class="mtk1">,</span></div></div><div class="code-line" data-line-number="44" data-line-start="44" data-line-end="44"><div class="line-content"><span class="mtk1"></span><span class="mtk3">"status"</span><span class="mtk1">: </span><span class="mtk9">"active"</span><span class="mtk1">,</span></div></div><div class="code-line" data-line-number="45" data-line-start="45" data-line-end="45"><div class="line-content"><span class="mtk1"></span><span class="mtk3">"created_at"</span><span class="mtk1">: </span><span class="mtk9">"..."</span></div></div><div class="code-line" data-line-number="46" data-line-start="46" data-line-end="46"><div class="line-content"><span class="mtk1">    }</span></div></div><div class="code-line" data-line-number="47" data-line-start="47" data-line-end="47"><div class="line-content"><span class="mtk1">  ],</span></div></div><div class="code-line" data-line-number="48" data-line-start="48" data-line-end="48"><div class="line-content"><span class="mtk1"></span><span class="mtk3">"affiliates"</span><span class="mtk1">: [</span><span class="mtk11">...</span><span class="mtk1">],</span></div></div><div class="code-line" data-line-number="49" data-line-start="49" data-line-end="49"><div class="line-content"><span class="mtk1"></span><span class="mtk3">"contacts"</span><span class="mtk1">: [</span></div></div><div class="code-line" data-line-number="50" data-line-start="50" data-line-end="50"><div class="line-content"><span class="mtk1">    {</span></div></div><div class="code-line" data-line-number="51" data-line-start="51" data-line-end="51"><div class="line-content"><span class="mtk1"></span><span class="mtk3">"id"</span><span class="mtk1">: </span><span class="mtk5">1</span><span class="mtk1">,</span></div></div><div class="code-line" data-line-number="52" data-line-start="52" data-line-end="52"><div class="line-content"><span class="mtk1"></span><span class="mtk3">"name"</span><span class="mtk1">: </span><span class="mtk9">"..."</span><span class="mtk1">,</span></div></div><div class="code-line" data-line-number="53" data-line-start="53" data-line-end="53"><div class="line-content"><span class="mtk1"></span><span class="mtk3">"email"</span><span class="mtk1">: </span><span class="mtk9">"..."</span><span class="mtk1">,</span></div></div><div class="code-line" data-line-number="54" data-line-start="54" data-line-end="54"><div class="line-content"><span class="mtk1"></span><span class="mtk3">"phone"</span><span class="mtk1">: </span><span class="mtk9">"..."</span><span class="mtk1">,</span></div></div><div class="code-line" data-line-number="55" data-line-start="55" data-line-end="55"><div class="line-content"><span class="mtk1"></span><span class="mtk3">"message"</span><span class="mtk1">: </span><span class="mtk9">"..."</span><span class="mtk1">,</span></div></div><div class="code-line" data-line-number="56" data-line-start="56" data-line-end="56"><div class="line-content"><span class="mtk1"></span><span class="mtk3">"status"</span><span class="mtk1">: </span><span class="mtk9">"new|read|replied"</span><span class="mtk1">,</span></div></div><div class="code-line" data-line-number="57" data-line-start="57" data-line-end="57"><div class="line-content"><span class="mtk1"></span><span class="mtk3">"created_at"</span><span class="mtk1">: </span><span class="mtk9">"..."</span></div></div><div class="code-line" data-line-number="58" data-line-start="58" data-line-end="58"><div class="line-content"><span class="mtk1">    }</span></div></div><div class="code-line" data-line-number="59" data-line-start="59" data-line-end="59"><div class="line-content"><span class="mtk1">  ],</span></div></div><div class="code-line" data-line-number="60" data-line-start="60" data-line-end="60"><div class="line-content"><span class="mtk1"></span><span class="mtk3">"settings"</span><span class="mtk1">: [</span></div></div><div class="code-line" data-line-number="61" data-line-start="61" data-line-end="61"><div class="line-content"><span class="mtk1">    {</span></div></div><div class="code-line" data-line-number="62" data-line-start="62" data-line-end="62"><div class="line-content"><span class="mtk1"></span><span class="mtk3">"key"</span><span class="mtk1">: </span><span class="mtk9">"site_name"</span><span class="mtk1">,</span></div></div><div class="code-line" data-line-number="63" data-line-start="63" data-line-end="63"><div class="line-content"><span class="mtk1"></span><span class="mtk3">"value"</span><span class="mtk1">: </span><span class="mtk9">"ThuongLo"</span><span class="mtk1">,</span></div></div><div class="code-line" data-line-number="64" data-line-start="64" data-line-end="64"><div class="line-content"><span class="mtk1"></span><span class="mtk3">"type"</span><span class="mtk1">: </span><span class="mtk9">"text"</span></div></div><div class="code-line" data-line-number="65" data-line-start="65" data-line-end="65"><div class="line-content"><span class="mtk1">    }</span></div></div><div class="code-line" data-line-number="66" data-line-start="66" data-line-end="66"><div class="line-content"><span class="mtk1">  ]</span></div></div><div class="code-line" data-line-number="67" data-line-start="67" data-line-end="67"><div class="line-content"><span class="mtk1">}</span></div></div></div></div></div></div></pre>

---

## Phase 1: Foundation & Layout

### 1.1 Fake Data (data/fake_data.json)

Tạo file JSON với dữ liệu demo đầy đủ cho tất cả modules (50+ items mỗi loại).

### 1.2 Admin Master Layout

#### [NEW]

admin_master.php

* DOCTYPE HTML5, charset UTF-8
* Include CSS: admin_sidebar.css, admin_header.css, admin_footer.css, admin_breadcrumb.css, admin_dashboard.css, admin_pages.css
* Include JS: Chart.js CDN, admin_sidebar.js, admin_header.js, admin_footer.js, admin_breadcrumb.js, admin_pages.js
* Structure: sidebar + main content area (header + breadcrumb + content + footer)

#### [NEW]

admin_sidebar.php

* Logo ThuongLo
* Menu items với icon FontAwesome:
  * Dashboard (fas fa-tachometer-alt)
  * Sản phẩm (fas fa-box)
  * Danh mục (fas fa-tags)
  * Tin tức (fas fa-newspaper)
  * Sự kiện (fas fa-calendar)
  * Đơn hàng (fas fa-shopping-cart)
  * Người dùng (fas fa-users)
  * Đại lý (fas fa-handshake)
  * Liên hệ (fas fa-envelope)
  * Doanh thu (fas fa-chart-line)
  * Cài đặt (fas fa-cog)
* Active state highlighting
* Collapse/expand functionality

#### [NEW]

admin_header.php

* Toggle sidebar button
* Search bar
* Notifications dropdown
* User profile dropdown (avatar, name, logout)

#### [NEW]

admin_footer.php

* Copyright info
* Version info
* Quick links

#### [NEW]

admin_breadcrumb.php

* Dynamic breadcrumb based on current page
* Home icon + current module + current action

### 1.3 Layout CSS Files

#### [NEW]

admin_sidebar.css

* Fixed sidebar (250px width)
* Smooth transitions
* Hover effects
* Active state styling
* Responsive collapse

#### [NEW]

admin_header.css

* Fixed header
* Flexbox layout
* Dropdown styles
* Search bar styling

#### [NEW]

admin_footer.css

* Simple footer styling
* Flex layout for links

#### [NEW]

admin_breadcrumb.css

* Breadcrumb navigation styling
* Separator icons

### 1.4 Layout JS Files

#### [NEW]

admin_sidebar.js

* Toggle sidebar collapse
* Active menu highlighting
* Submenu expand/collapse

#### [NEW]

admin_header.js

* Dropdown toggle
* Search functionality
* Notifications

#### [NEW]

admin_footer.js

* Minimal JS if needed

#### [NEW]

admin_breadcrumb.js

* Dynamic breadcrumb generation

---

## Phase 2: Dashboard với Chart.js

### 2.1 Dashboard Page

#### [MODIFY]

dashboard.php

Mở rộng dashboard hiện tại với:

* **KPI Cards** : Tổng sản phẩm, doanh thu, đơn hàng, người dùng (với trend %)
* **Biểu đồ doanh thu** : Line chart theo tháng (Chart.js)
* **Biểu đồ sản phẩm** : Bar chart top 10 sản phẩm bán chạy
* **Biểu đồ đơn hàng** : Doughnut chart phân loại trạng thái
* **Biểu đồ người dùng** : Line chart người dùng mới theo tuần
* **Hoạt động gần đây** : Timeline activities
* **Thông báo** : Alerts (low stock, pending orders, etc.)

#### [NEW]

admin_dashboard.css

* Grid layout cho charts
* KPI card styling
* Chart container styling
* Responsive grid

---

## Phase 3-12: Management Modules

Mỗi module sẽ có cấu trúc tương tự:

### Module Structure (ví dụ: Products)

#### [NEW]

products/index.php

* Load data từ fake_data.json
* Table với columns: ID, Image, Name, Category, Price, Stock, Status, Actions
* Search & filter
* Pagination
* Bulk actions
* Add new button

#### [NEW]

products/add.php

* Form với validation
* Fields: Name, Category, Price, Stock, Description, Image, Status
* Save button (demo - không lưu thật)

#### [NEW]

products/edit.php

* Pre-filled form
* Update button (demo)

#### [NEW]

products/view.php

* Display all product details
* Edit/Delete buttons

#### [NEW]

products/delete.php

* Confirmation modal
* Delete action (demo)

### Các Module Khác

Áp dụng cấu trúc tương tự cho:

* Categories
* News
* Events
* Orders (không có add.php)
* Users
* Affiliates
* Contact (không có add.php)
* Revenue (chỉ có index.php và view.php)
* Settings

---

## Phase 13: Common Styles & Scripts

### [NEW]

admin_pages.css

* Common table styles
* Form styles
* Button styles
* Badge styles
* Modal styles
* Alert styles
* Card styles
* Responsive utilities

### [NEW]

admin_pages.js

* Table sorting
* Search/filter
* Pagination
* Modal toggle
* Form validation
* Confirmation dialogs
* Toast notifications

---

## Verification Plan

### Automated Tests

Không có automated tests (chỉ demo với fake data).

### Manual Verification

#### 1. Layout & Navigation

* [ ] Truy cập `http://localhost/thuonglowebsite/index.php?page=admin`
* [ ] Kiểm tra sidebar hiển thị đầy đủ 11 menu items
* [ ] Click từng menu item, kiểm tra active state
* [ ] Click toggle button, kiểm tra sidebar collapse/expand
* [ ] Kiểm tra header hiển thị search bar, notifications, user dropdown
* [ ] Kiểm tra breadcrumb cập nhật đúng khi chuyển trang
* [ ] Kiểm tra footer hiển thị ở cuối trang

#### 2. Dashboard

* [ ] Truy cập dashboard, kiểm tra 4 KPI cards hiển thị đúng số liệu
* [ ] Kiểm tra 4 biểu đồ Chart.js render đúng
* [ ] Kiểm tra biểu đồ doanh thu (line chart) có dữ liệu 12 tháng
* [ ] Kiểm tra biểu đồ sản phẩm (bar chart) có top 10 items
* [ ] Kiểm tra biểu đồ đơn hàng (doughnut chart) có 4 trạng thái
* [ ] Kiểm tra biểu đồ người dùng (line chart) có dữ liệu 4 tuần
* [ ] Kiểm tra alerts hiển thị (low stock, pending orders)
* [ ] Kiểm tra hoạt động gần đây hiển thị 5 items

#### 3. Products Module

* [ ] Truy cập `?page=admin&module=products`
* [ ] Kiểm tra table hiển thị danh sách sản phẩm từ fake_data.json
* [ ] Kiểm tra search box hoạt động
* [ ] Kiểm tra filter by category hoạt động
* [ ] Kiểm tra pagination (nếu > 10 items)
* [ ] Click "Add New", kiểm tra form hiển thị đầy đủ fields
* [ ] Click "Edit" trên 1 item, kiểm tra form pre-filled
* [ ] Click "View" trên 1 item, kiểm tra chi tiết hiển thị
* [ ] Click "Delete", kiểm tra confirmation modal

#### 4. Categories Module

* [ ] Truy cập `?page=admin&module=categories`
* [ ] Kiểm tra CRUD tương tự Products

#### 5. News Module

* [ ] Truy cập `?page=admin&module=news`
* [ ] Kiểm tra CRUD tương tự Products

#### 6. Events Module

* [ ] Truy cập `?page=admin&module=events`
* [ ] Kiểm tra CRUD tương tự Products

#### 7. Orders Module

* [ ] Truy cập `?page=admin&module=orders`
* [ ] Kiểm tra table hiển thị đơn hàng
* [ ] Kiểm tra không có "Add New" button
* [ ] Kiểm tra "View" hiển thị chi tiết đơn hàng
* [ ] Kiểm tra "Edit" cho phép cập nhật trạng thái

#### 8. Users Module

* [ ] Truy cập `?page=admin&module=users`
* [ ] Kiểm tra CRUD tương tự Products

#### 9. Affiliates Module

* [ ] Truy cập `?page=admin&module=affiliates`
* [ ] Kiểm tra CRUD tương tự Products

#### 10. Contact Module

* [ ] Truy cập `?page=admin&module=contact`
* [ ] Kiểm tra table hiển thị liên hệ
* [ ] Kiểm tra không có "Add New" button
* [ ] Kiểm tra "View" hiển thị chi tiết
* [ ] Kiểm tra "Edit" cho phép cập nhật trạng thái

#### 11. Revenue Module

* [ ] Truy cập `?page=admin&module=revenue`
* [ ] Kiểm tra biểu đồ doanh thu theo tháng/quý/năm
* [ ] Kiểm tra filter by date range
* [ ] Click "View Details", kiểm tra chi tiết doanh thu

#### 12. Settings Module

* [ ] Truy cập `?page=admin&module=settings`
* [ ] Kiểm tra CRUD tương tự Products

#### 13. Responsive Design

* [ ] Resize browser xuống 768px, kiểm tra sidebar collapse tự động
* [ ] Kiểm tra table responsive (horizontal scroll nếu cần)
* [ ] Kiểm tra charts responsive
* [ ] Kiểm tra forms responsive
* [ ] Resize xuống 480px, kiểm tra mobile layout

#### 14. Design Consistency

* [ ] Kiểm tra tất cả buttons sử dụng màu #356DF1
* [ ] Kiểm tra hover state chuyển sang #000000
* [ ] Kiểm tra font chữ là Inter
* [ ] Kiểm tra icons là FontAwesome
* [ ] Kiểm tra border-radius nhất quán (10px)
* [ ] Kiểm tra spacing nhất quán

#### 15. Performance

* [ ] Kiểm tra trang load < 2s
* [ ] Kiểm tra không có console errors
* [ ] Kiểm tra Chart.js load đúng từ CDN

---

## Notes

* Tất cả CSS/JS phải external, không inline
* Fonts đặt tại `assets/fonts`
* Logs ghi vào `logs/` (nếu cần)
* Images tĩnh tại `assets/images`, uploads tại `uploads/`
* Không sử dụng emoji/icon nhảm
* Tuân thủ cấu trúc MVC
