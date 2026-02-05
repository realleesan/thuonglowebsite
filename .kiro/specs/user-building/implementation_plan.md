
# Kế hoạch Triển khai Hệ thống Tài khoản Users - ThuongLo

## Mục tiêu

Xây dựng hệ thống trang tài khoản users hoàn chỉnh cho website ThuongLo với các chức năng:

* Dashboard tổng quan với biểu đồ thống kê (Chart.js)
* Quản lý thông tin tài khoản
* Quản lý đơn hàng (data nguồn hàng, vận chuyển, dịch vụ)
* Quản lý giỏ hàng
* Quản lý danh sách yêu thích

Thiết kế đồng bộ với style hiện tại của website (màu sắc #356DF1, font Inter, FontAwesome icons).

## User Review Required

IMPORTANT

 **Cấu trúc Module** : Mỗi module (account, orders, cart, wishlist) sẽ có file index.php tổng hợp các chức năng. Bạn có muốn thay đổi cấu trúc này không?

IMPORTANT

 **Dashboard Charts** : Tôi sẽ sử dụng Chart.js để tạo các biểu đồ thống kê. Các loại biểu đồ cần thiết:

* Biểu đồ doanh thu theo tháng (Line Chart)
* Biểu đồ phân loại đơn hàng theo sản phẩm (Doughnut Chart)
* Biểu đồ số lượng đơn hàng theo trạng thái (Bar Chart)
* Biểu đồ xu hướng mua hàng (Area Chart)

Bạn có muốn thêm/bớt loại biểu đồ nào không?

IMPORTANT

 **Fake Data Structure** : Tôi sẽ tạo dữ liệu JSON demo với:

* Thông tin user (profile, avatar, contact)
* Đơn hàng (data nguồn hàng, vận chuyển, dịch vụ TT, dịch vụ đánh hàng)
* Giỏ hàng hiện tại
* Danh sách yêu thích
* Dữ liệu thống kê cho charts

Bạn có yêu cầu gì đặc biệt về cấu trúc data không?

## Proposed Changes

### Component 1: Core Layout & Navigation

#### [NEW]

user_sidebar.php

Sidebar điều hướng cho user dashboard với các menu:

* Dashboard (tổng quan)
* Tài khoản
* Đơn hàng
* Giỏ hàng
* Yêu thích
* Đăng xuất

Sử dụng cấu trúc tương tự

admin_sidebar.php với active state detection.

#### [MODIFY]

index.php

Cập nhật routing cho user pages:

* Thêm xử lý module parameter (`$_GET['module']`)
* Thêm xử lý action parameter (`$_GET['action']`)
* Load CSS/JS tương ứng cho từng module
* Thiết lập breadcrumbs động

---

### Component 2: Dashboard Module

#### [MODIFY]

dashboard.php

Trang tổng quan với:

* Welcome section với thông tin user
* Stats cards (tổng đơn hàng, tổng chi tiêu, data đã mua, điểm tích lũy)
* 4 biểu đồ Chart.js:
  * Revenue trend (Line chart)
  * Order distribution (Doughnut chart)
  * Order status (Bar chart)
  * Purchase trend (Area chart)
* Recent orders table
* Quick actions buttons

#### [NEW]

user_dashboard.css

Styling cho dashboard:

* Layout grid cho stats cards
* Chart containers với responsive design
* Card styling đồng bộ với admin dashboard
* Animations và hover effects

#### [NEW]

user_dashboard.js

Logic và Chart.js integration:

* Load data từ JSON
* Khởi tạo 4 charts với Chart.js
* Responsive chart resizing
* Interactive tooltips
* Data filtering by date range

---

### Component 3: Account Module

#### [MODIFY]

index.php

Hiển thị thông tin tài khoản:

* Avatar upload preview
* Personal information display
* Contact information
* Security settings link
* Edit button

#### [MODIFY]

edit.php

Form chỉnh sửa thông tin:

* Avatar upload
* Full name, email, phone
* Address (shipping address)
* Password change section
* Save/Cancel buttons

#### [NEW]

view.php

Xem chi tiết profile (read-only view)

#### [NEW]

delete.php

Xác nhận xóa tài khoản với warning

#### [NEW]

user_account.css

Styling cho account pages

#### [NEW]

user_account.js

Form validation và avatar preview

---

### Component 4: Orders Module

#### [MODIFY]

index.php

Danh sách đơn hàng:

* Filter by status (pending, processing, completed, cancelled)
* Filter by product type (data nguồn hàng, vận chuyển, dịch vụ)
* Search functionality
* Pagination
* Order cards với status badges

#### [MODIFY]

view.php

Chi tiết đơn hàng:

* Order timeline/status tracker
* Product details
* Payment information
* Shipping information (nếu có)
* Download invoice button

#### [MODIFY]

edit.php

Chỉnh sửa đơn hàng (chỉ khi status = pending)

#### [MODIFY]

delete.php

Hủy đơn hàng với lý do

#### [NEW]

user_orders.css

Styling cho orders pages với status colors

#### [NEW]

user_orders.js

Filter, search, và pagination logic

---

### Component 5: Cart Module

#### [MODIFY]

index.php

Giỏ hàng:

* Cart items list
* Quantity adjustment
* Remove item
* Subtotal calculation
* Checkout button

#### [MODIFY]

add.php

Thêm sản phẩm vào giỏ

#### [MODIFY]

edit.php

Cập nhật số lượng

#### [MODIFY]

view.php

Xem chi tiết item trong giỏ

#### [MODIFY]

delete.php

Xóa item khỏi giỏ

#### [NEW]

user_cart.css

Styling cho cart pages

#### [NEW]

user_cart.js

Cart operations và calculations

---

### Component 6: Wishlist Module

#### [MODIFY]

index.php

Danh sách yêu thích:

* Product grid layout
* Add to cart button
* Remove from wishlist
* Share wishlist

#### [MODIFY]

add.php

Thêm vào wishlist

#### [MODIFY]

view.php

Xem chi tiết sản phẩm trong wishlist

#### [MODIFY]

edit.php

Chỉnh sửa note cho sản phẩm

#### [MODIFY]

delete.php

Xóa khỏi wishlist

#### [NEW]

user_wishlist.css

Styling cho wishlist pages

#### [NEW]

user_wishlist.js

Wishlist operations

---

### Component 7: Shared Components

#### [NEW]

user_sidebar.css

Sidebar styling tương tự admin sidebar nhưng với user theme

#### [NEW]

user_sidebar.js

Sidebar collapse/expand, active menu detection

---

### Component 8: Data Layer

#### [MODIFY]

user_fake_data.json

Dữ liệu demo đầy đủ bao gồm:

<pre><div node="[object Object]" class="relative whitespace-pre-wrap word-break-all my-2 rounded-lg bg-list-hover-subtle border border-gray-500/20"><div class="min-h-7 relative box-border flex flex-row items-center justify-between rounded-t border-b border-gray-500/20 px-2 py-0.5"><div class="font-sans text-sm text-ide-text-color opacity-60">json</div><div class="flex flex-row gap-2 justify-end"><div class="cursor-pointer opacity-70 hover:opacity-100"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" class="lucide lucide-copy h-3.5 w-3.5"><rect width="14" height="14" x="8" y="8" rx="2" ry="2"></rect><path d="M4 16c-1.1 0-2-.9-2-2V4c0-1.1.9-2 2-2h10c1.1 0 2 .9 2 2"></path></svg></div></div></div><div class="p-3"><div class="w-full h-full text-xs cursor-text"><div class="code-block"><div class="code-line" data-line-number="1" data-line-start="1" data-line-end="1"><div class="line-content"><span class="mtk1">{</span></div></div><div class="code-line" data-line-number="2" data-line-start="2" data-line-end="2"><div class="line-content"><span class="mtk1"></span><span class="mtk10">"user"</span><span class="mtk1">: { </span><span class="mtk11">profile,</span><span class="mtk1"></span><span class="mtk11">avatar,</span><span class="mtk1"></span><span class="mtk11">contact,</span><span class="mtk1"></span><span class="mtk11">stats</span><span class="mtk1"> },</span></div></div><div class="code-line" data-line-number="3" data-line-start="3" data-line-end="3"><div class="line-content"><span class="mtk1"></span><span class="mtk10">"orders"</span><span class="mtk1">: [ </span><span class="mtk11">array</span><span class="mtk1"></span><span class="mtk11">of</span><span class="mtk1"></span><span class="mtk11">orders</span><span class="mtk1"></span><span class="mtk11">with</span><span class="mtk1"></span><span class="mtk11">different</span><span class="mtk1"></span><span class="mtk11">product</span><span class="mtk1"></span><span class="mtk11">types</span><span class="mtk1"> ],</span></div></div><div class="code-line" data-line-number="4" data-line-start="4" data-line-end="4"><div class="line-content"><span class="mtk1"></span><span class="mtk10">"cart"</span><span class="mtk1">: [ </span><span class="mtk11">current</span><span class="mtk1"></span><span class="mtk11">cart</span><span class="mtk1"></span><span class="mtk11">items</span><span class="mtk1"> ],</span></div></div><div class="code-line" data-line-number="5" data-line-start="5" data-line-end="5"><div class="line-content"><span class="mtk1"></span><span class="mtk10">"wishlist"</span><span class="mtk1">: [ </span><span class="mtk11">favorite</span><span class="mtk1"></span><span class="mtk11">products</span><span class="mtk1"> ],</span></div></div><div class="code-line" data-line-number="6" data-line-start="6" data-line-end="6"><div class="line-content"><span class="mtk1"></span><span class="mtk10">"chartData"</span><span class="mtk1">: {</span></div></div><div class="code-line" data-line-number="7" data-line-start="7" data-line-end="7"><div class="line-content"><span class="mtk1"></span><span class="mtk10">"revenue"</span><span class="mtk1">: </span><span class="mtk11">monthly</span><span class="mtk1"></span><span class="mtk11">data</span><span class="mtk1">,</span></div></div><div class="code-line" data-line-number="8" data-line-start="8" data-line-end="8"><div class="line-content"><span class="mtk1"></span><span class="mtk10">"orderDistribution"</span><span class="mtk1">: </span><span class="mtk11">by</span><span class="mtk1"></span><span class="mtk11">product</span><span class="mtk1"></span><span class="mtk11">type</span><span class="mtk1">,</span></div></div><div class="code-line" data-line-number="9" data-line-start="9" data-line-end="9"><div class="line-content"><span class="mtk1"></span><span class="mtk10">"orderStatus"</span><span class="mtk1">: </span><span class="mtk11">by</span><span class="mtk1"></span><span class="mtk11">status</span><span class="mtk1">,</span></div></div><div class="code-line" data-line-number="10" data-line-start="10" data-line-end="10"><div class="line-content"><span class="mtk1"></span><span class="mtk10">"purchaseTrend"</span><span class="mtk1">: </span><span class="mtk11">timeline</span><span class="mtk1"></span><span class="mtk11">data</span></div></div><div class="code-line" data-line-number="11" data-line-start="11" data-line-end="11"><div class="line-content"><span class="mtk1">  }</span></div></div><div class="code-line" data-line-number="12" data-line-start="12" data-line-end="12"><div class="line-content"><span class="mtk1">}</span></div></div></div></div></div></div></pre>

## Verification Plan

### Automated Tests

Không có automated tests cho giai đoạn demo này (sử dụng fake data JSON).

### Manual Verification

#### 1. Kiểm tra Layout & Navigation

* [ ] Truy cập `http://localhost/thuonglowebsite/?page=users`
* [ ] Verify sidebar hiển thị đầy đủ menu items
* [ ] Click từng menu item, verify active state
* [ ] Verify breadcrumb hiển thị đúng
* [ ] Verify header/footer chung hiển thị

#### 2. Kiểm tra Dashboard

* [ ] Verify 4 stats cards hiển thị dữ liệu
* [ ] Verify 4 charts render đúng với Chart.js
* [ ] Resize browser, verify responsive charts
* [ ] Hover vào charts, verify tooltips
* [ ] Verify recent orders table

#### 3. Kiểm tra Account Module

* [ ] Truy cập `?page=users&module=account`
* [ ] Verify thông tin user hiển thị
* [ ] Click Edit, verify form hiển thị
* [ ] Test form validation
* [ ] Verify avatar preview khi upload

#### 4. Kiểm tra Orders Module

* [ ] Truy cập `?page=users&module=orders`
* [ ] Verify danh sách đơn hàng
* [ ] Test filter by status
* [ ] Test search functionality
* [ ] Click vào order, verify detail page
* [ ] Verify status badges với màu đúng

#### 5. Kiểm tra Cart Module

* [ ] Truy cập `?page=users&module=cart`
* [ ] Verify cart items hiển thị
* [ ] Test quantity adjustment
* [ ] Verify subtotal calculation
* [ ] Test remove item

#### 6. Kiểm tra Wishlist Module

* [ ] Truy cập `?page=users&module=wishlist`
* [ ] Verify product grid layout
* [ ] Test add to cart from wishlist
* [ ] Test remove from wishlist

#### 7. Kiểm tra Design Consistency

* [ ] Verify màu sắc: #356DF1 (primary), #000000 (hover)
* [ ] Verify font Inter được sử dụng
* [ ] Verify FontAwesome icons
* [ ] Verify transitions mượt mà
* [ ] Test responsive trên mobile/tablet

#### 8. Kiểm tra Data Integration

* [ ] Verify data load từ

  user_fake_data.json
* [ ] Verify tất cả modules đọc đúng data
* [ ] Verify charts render đúng data

## Technical Notes

### Design Tokens

* Primary Color: `#356DF1`
* Hover Color: `#000000`
* Text Color: `#374151`
* Background: `#f8fafc`
* Border: `#E5E7EB`
* Font: `'Inter', sans-serif`

### Chart.js Configuration

* Version: 3.x (CDN)
* Responsive: true
* Maintain aspect ratio: false
* Animation: smooth transitions

### File Structure

<pre><div node="[object Object]" class="relative whitespace-pre-wrap word-break-all my-2 rounded-lg bg-list-hover-subtle border border-gray-500/20"><div class="min-h-7 relative box-border flex flex-row items-center justify-between rounded-t border-b border-gray-500/20 px-2 py-0.5"><div class="font-sans text-sm text-ide-text-color opacity-60"></div><div class="flex flex-row gap-2 justify-end"><div class="cursor-pointer opacity-70 hover:opacity-100"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" class="lucide lucide-copy h-3.5 w-3.5"><rect width="14" height="14" x="8" y="8" rx="2" ry="2"></rect><path d="M4 16c-1.1 0-2-.9-2-2V4c0-1.1.9-2 2-2h10c1.1 0 2 .9 2 2"></path></svg></div></div></div><div class="p-3"><div class="w-full h-full text-xs cursor-text"><div class="code-block"><div class="code-line" data-line-number="1" data-line-start="1" data-line-end="1"><div class="line-content"><span class="mtk1">app/views/users/</span></div></div><div class="code-line" data-line-number="2" data-line-start="2" data-line-end="2"><div class="line-content"><span class="mtk1">├── dashboard.php</span></div></div><div class="code-line" data-line-number="3" data-line-start="3" data-line-end="3"><div class="line-content"><span class="mtk1">├── account/</span></div></div><div class="code-line" data-line-number="4" data-line-start="4" data-line-end="4"><div class="line-content"><span class="mtk1">│   ├── index.php</span></div></div><div class="code-line" data-line-number="5" data-line-start="5" data-line-end="5"><div class="line-content"><span class="mtk1">│   ├── edit.php</span></div></div><div class="code-line" data-line-number="6" data-line-start="6" data-line-end="6"><div class="line-content"><span class="mtk1">│   ├── view.php</span></div></div><div class="code-line" data-line-number="7" data-line-start="7" data-line-end="7"><div class="line-content"><span class="mtk1">│   └── delete.php</span></div></div><div class="code-line" data-line-number="8" data-line-start="8" data-line-end="8"><div class="line-content"><span class="mtk1">├── orders/</span></div></div><div class="code-line" data-line-number="9" data-line-start="9" data-line-end="9"><div class="line-content"><span class="mtk1">│   ├── index.php</span></div></div><div class="code-line" data-line-number="10" data-line-start="10" data-line-end="10"><div class="line-content"><span class="mtk1">│   ├── view.php</span></div></div><div class="code-line" data-line-number="11" data-line-start="11" data-line-end="11"><div class="line-content"><span class="mtk1">│   ├── edit.php</span></div></div><div class="code-line" data-line-number="12" data-line-start="12" data-line-end="12"><div class="line-content"><span class="mtk1">│   └── delete.php</span></div></div><div class="code-line" data-line-number="13" data-line-start="13" data-line-end="13"><div class="line-content"><span class="mtk1">├── cart/</span></div></div><div class="code-line" data-line-number="14" data-line-start="14" data-line-end="14"><div class="line-content"><span class="mtk1">│   ├── index.php</span></div></div><div class="code-line" data-line-number="15" data-line-start="15" data-line-end="15"><div class="line-content"><span class="mtk1">│   ├── add.php</span></div></div><div class="code-line" data-line-number="16" data-line-start="16" data-line-end="16"><div class="line-content"><span class="mtk1">│   ├── edit.php</span></div></div><div class="code-line" data-line-number="17" data-line-start="17" data-line-end="17"><div class="line-content"><span class="mtk1">│   ├── view.php</span></div></div><div class="code-line" data-line-number="18" data-line-start="18" data-line-end="18"><div class="line-content"><span class="mtk1">│   └── delete.php</span></div></div><div class="code-line" data-line-number="19" data-line-start="19" data-line-end="19"><div class="line-content"><span class="mtk1">├── wishlist/</span></div></div><div class="code-line" data-line-number="20" data-line-start="20" data-line-end="20"><div class="line-content"><span class="mtk1">│   ├── index.php</span></div></div><div class="code-line" data-line-number="21" data-line-start="21" data-line-end="21"><div class="line-content"><span class="mtk1">│   ├── add.php</span></div></div><div class="code-line" data-line-number="22" data-line-start="22" data-line-end="22"><div class="line-content"><span class="mtk1">│   ├── view.php</span></div></div><div class="code-line" data-line-number="23" data-line-start="23" data-line-end="23"><div class="line-content"><span class="mtk1">│   ├── edit.php</span></div></div><div class="code-line" data-line-number="24" data-line-start="24" data-line-end="24"><div class="line-content"><span class="mtk1">│   └── delete.php</span></div></div><div class="code-line" data-line-number="25" data-line-start="25" data-line-end="25"><div class="line-content"><span class="mtk1">└── data/</span></div></div><div class="code-line" data-line-number="26" data-line-start="26" data-line-end="26"><div class="line-content"><span class="mtk1">    └── user_fake_data.json</span></div></div></div></div></div></div></pre>

**Comment**Ctrl+Alt+M
