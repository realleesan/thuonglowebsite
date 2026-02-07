# Design Document: Affiliate Building Frontend

## Overview

Hệ thống affiliate frontend được xây dựng bằng PHP thuần, sử dụng kiến trúc MVC đơn giản với pattern tương tự admin system. Hệ thống load dữ liệu từ JSON files, sử dụng Chart.js cho visualization, và AJAX cho tương tác động.

**Kiến trúc tổng thể:**
- Frontend: PHP views với HTML/CSS/JavaScript
- Data layer: JSON files (demo_data.json)
- Styling: CSS modules (affiliate_style.css, affiliate_components.css, affiliate_responsive.css)
- Scripting: JavaScript modules (affiliate_main.js, affiliate_chart_config.js, affiliate_ajax_actions.js)
- Layout: Shared components (sidebar, breadcrumb, header, footer)
- Routing: Centralized trong index.php

**Nguyên tắc thiết kế:**
- Separation of concerns: PHP chỉ xử lý logic và render HTML, không inline CSS/JS
- Reusability: Sử dụng layout components từ _layout folder
- Consistency: **Tuân theo HOÀN TOÀN design system của admin** (màu sắc, typography, spacing, components)
- Progressive enhancement: AJAX cho UX tốt hơn, fallback về form submission
- Mobile-first: Responsive design từ đầu

## Design System (Giống Admin)

### Color Palette

**Primary Colors:**
```css
--primary: #356DF1;        /* Blue - Primary actions, links */
--secondary: #000000;      /* Black - Hover states, emphasis */
```

**Status Colors:**
```css
--success: #10B981;        /* Green - Success states */
--warning: #F59E0B;        /* Orange - Warning states */
--danger: #EF4444;         /* Red - Error states */
--info: #3B82F6;           /* Light Blue - Info states */
```

**Neutral Colors:**
```css
--gray-50: #F9FAFB;        /* Background */
--gray-100: #F3F4F6;       /* Light background */
--gray-200: #E5E7EB;       /* Borders */
--gray-300: #D1D5DB;       /* Disabled */
--gray-400: #9CA3AF;       /* Placeholder */
--gray-500: #6B7280;       /* Secondary text */
--gray-600: #4B5563;       /* Body text */
--gray-700: #374151;       /* Headings */
--gray-800: #1F2937;       /* Dark text */
--gray-900: #111827;       /* Primary text */
--white: #FFFFFF;          /* White */
```

### Typography

**Font Family:**
```css
font-family: 'Inter', sans-serif;
```

**Font Sizes:**
```css
--text-xs: 12px;           /* Small labels */
--text-sm: 14px;           /* Body text, buttons */
--text-base: 16px;         /* Default */
--text-lg: 18px;           /* Subheadings */
--text-xl: 20px;           /* Section titles */
--text-2xl: 24px;          /* Page titles */
--text-3xl: 28px;          /* Dashboard title */
--text-4xl: 32px;          /* Large numbers */
```

**Font Weights:**
```css
--font-normal: 400;
--font-medium: 500;
--font-semibold: 600;
--font-bold: 700;
```

### Spacing System

```css
--space-1: 4px;
--space-2: 8px;
--space-3: 12px;
--space-4: 16px;
--space-5: 20px;
--space-6: 24px;
--space-8: 32px;
--space-10: 40px;
--space-12: 48px;
```

### Border Radius

```css
--radius-sm: 6px;          /* Small elements */
--radius-md: 8px;          /* Buttons, inputs */
--radius-lg: 10px;         /* Cards, dropdowns */
--radius-xl: 12px;         /* Large cards */
--radius-full: 9999px;     /* Pills, avatars */
```

### Shadows

```css
--shadow-sm: 0 1px 2px rgba(0, 0, 0, 0.05);
--shadow-md: 0 4px 6px rgba(0, 0, 0, 0.1);
--shadow-lg: 0 10px 25px rgba(0, 0, 0, 0.1);
--shadow-xl: 0 20px 40px rgba(0, 0, 0, 0.15);
```

### Component Styles (Giống Admin)

**Buttons:**
```css
.btn-primary {
    background: #356DF1;
    color: #ffffff;
    padding: 8px 16px;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 500;
    transition: all 0.3s ease;
}

.btn-primary:hover {
    background: #000000;
    transform: translateY(-1px);
}
```

**Cards:**
```css
.card {
    background: #ffffff;
    border: 1px solid #E5E7EB;
    border-radius: 12px;
    padding: 24px;
    transition: all 0.3s ease;
}

.card:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
}
```

**Stat Cards:**
```css
.stat-card {
    background: #ffffff;
    border-radius: 12px;
    padding: 24px;
    border: 1px solid #E5E7EB;
    display: flex;
    align-items: center;
}

.stat-icon {
    width: 60px;
    height: 60px;
    border-radius: 12px;
    background: linear-gradient(135deg, #356df1, #4f46e5);
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 20px;
}

.stat-icon i {
    font-size: 24px;
    color: #ffffff;
}
```

**Badges:**
```css
.badge {
    display: inline-block;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 500;
}

.badge-success {
    background: #d1fae5;
    color: #065f46;
}

.badge-warning {
    background: #fef3c7;
    color: #92400e;
}
```

**Tables:**
```css
.table {
    width: 100%;
    border-collapse: collapse;
}

.table th {
    background: #F9FAFB;
    padding: 12px 16px;
    text-align: left;
    font-size: 12px;
    font-weight: 600;
    color: #6B7280;
    text-transform: uppercase;
    border-bottom: 1px solid #E5E7EB;
}

.table td {
    padding: 16px;
    border-bottom: 1px solid #F3F4F6;
    font-size: 14px;
    color: #374151;
}
```

## Architecture

### Directory Structure

```
app/
├── views/
│   ├── affiliate/
│   │   ├── dashboard.php                 # Trang tổng quan
│   │   ├── commissions/
│   │   │   ├── index.php                 # Danh sách hoa hồng
│   │   │   ├── history.php               # Lịch sử hoa hồng
│   │   │   └── policy.php                # Chính sách hoa hồng
│   │   ├── customers/
│   │   │   ├── index.php                 # Danh sách khách hàng
│   │   │   ├── list.php                  # Alias cho index
│   │   │   └── detail.php                # Chi tiết khách hàng
│   │   ├── finance/
│   │   │   ├── index.php                 # Tổng quan tài chính
│   │   │   ├── balance.php               # Số dư tài khoản
│   │   │   └── withdraw.php              # Rút tiền
│   │   ├── marketing/
│   │   │   ├── index.php                 # Tổng quan marketing
│   │   │   ├── campaigns.php             # Chiến dịch
│   │   │   └── tools.php                 # Công cụ (link, banner, QR)
│   │   ├── profile/
│   │   │   ├── index.php                 # Hồ sơ
│   │   │   └── settings.php              # Cài đặt
│   │   ├── reports/
│   │   │   ├── index.php                 # Tổng quan báo cáo
│   │   │   ├── clicks.php                # Báo cáo click
│   │   │   └── orders.php                # Báo cáo đơn hàng
│   │   └── data/
│   │       └── demo_data.json            # Dữ liệu demo
│   └── _layout/
│       ├── sidebar.php                   # Sidebar navigation
│       ├── breadcrumb.php                # Breadcrumb
│       ├── header.php                    # Header
│       └── footer.php                    # Footer
├── assets/
│   ├── css/
│   │   ├── affiliate_style.css           # Styling chính
│   │   ├── affiliate_components.css      # Component styles
│   │   └── affiliate_responsive.css      # Responsive styles
│   └── js/
│       ├── affiliate_main.js             # Main JavaScript
│       ├── affiliate_chart_config.js     # Chart.js config
│       └── affiliate_ajax_actions.js     # AJAX handlers
└── index.php                             # Routing entry point
```

### Routing System

Routing được xử lý trong `index.php`:

```php
// Pseudo-code for routing
$route = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$segments = explode('/', trim($route, '/'));

if ($segments[0] === 'affiliate') {
    $module = $segments[1] ?? 'dashboard';
    $action = $segments[2] ?? 'index';
    
    $file = "app/views/affiliate/{$module}/{$action}.php";
    if (file_exists($file)) {
        include $file;
    } else {
        include "app/views/errors/404.php";
    }
}
```

### Data Flow

```
User Request → index.php (Routing) → Module PHP File
                                           ↓
                                    Load demo_data.json
                                           ↓
                                    Parse JSON to PHP array
                                           ↓
                                    Include _layout components
                                           ↓
                                    Render HTML with data
                                           ↓
                                    Load CSS/JS assets
                                           ↓
                                    Initialize Chart.js/AJAX
                                           ↓
                                    Display to User
```

## Components and Interfaces

### 1. Data Loader Component

**Purpose:** Load và parse dữ liệu từ JSON files

**Interface:**
```php
class DataLoader {
    // Load JSON file và return PHP array
    function loadData(string $filename): array
    
    // Get specific data by key
    function getData(string $key): mixed
    
    // Check if data exists
    function hasData(string $key): bool
}
```

**Usage:**
```php
$loader = new DataLoader('app/views/affiliate/data/demo_data.json');
$dashboard_data = $loader->getData('dashboard');
$commissions = $loader->getData('commissions');
```

### 2. Layout Components

**Sidebar Navigation:**
```php
// sidebar.php
function renderSidebar(string $active_module): string
```

Hiển thị menu với các module:
- Dashboard
- Commissions (History, Policy)
- Customers (List, Detail)
- Finance (Balance, Withdraw)
- Marketing (Campaigns, Tools)
- Profile (Settings)
- Reports (Clicks, Orders)

**Breadcrumb:**
```php
// breadcrumb.php
function renderBreadcrumb(array $path): string
```

Hiển thị đường dẫn: Home > Module > Action

**Header:**
```php
// header.php
function renderHeader(array $user_info): string
```

Hiển thị thông tin đại lý, notifications, logout

**Footer:**
```php
// footer.php
function renderFooter(): string
```

Hiển thị copyright, links

### 3. Chart Component

**Purpose:** Render biểu đồ sử dụng Chart.js

**Interface:**
```php
class ChartRenderer {
    // Render revenue chart
    function renderRevenueChart(array $data): string
    
    // Render clicks chart
    function renderClicksChart(array $data): string
    
    // Render conversion rate chart
    function renderConversionChart(array $data): string
}
```

**JavaScript Integration:**
```javascript
// affiliate_chart_config.js
const ChartConfig = {
    revenue: {
        type: 'line',
        options: { /* Chart.js options */ }
    },
    clicks: {
        type: 'bar',
        options: { /* Chart.js options */ }
    },
    conversion: {
        type: 'doughnut',
        options: { /* Chart.js options */ }
    }
};
```

### 4. AJAX Handler Component

**Purpose:** Xử lý các tương tác AJAX

**Interface:**
```javascript
// affiliate_ajax_actions.js
class AjaxHandler {
    // Filter data
    filterData(filters, callback)
    
    // Sort data
    sortData(column, direction, callback)
    
    // Search data
    searchData(query, callback)
    
    // Load more data (pagination)
    loadMore(page, callback)
}
```

### 5. Table Component

**Purpose:** Render bảng dữ liệu với sorting, filtering

**Interface:**
```php
class TableRenderer {
    // Render table with data
    function renderTable(array $data, array $columns, array $options): string
    
    // Render pagination
    function renderPagination(int $total, int $per_page, int $current): string
}
```

### 6. Card Component

**Purpose:** Render stat cards cho dashboard

**Interface:**
```php
class CardRenderer {
    // Render stat card
    function renderStatCard(string $title, mixed $value, string $icon, string $trend): string
    
    // Render info card
    function renderInfoCard(string $title, string $content): string
}
```

### 7. Form Component

**Purpose:** Render forms với validation

**Interface:**
```php
class FormRenderer {
    // Render form field
    function renderField(string $type, string $name, array $options): string
    
    // Render form
    function renderForm(array $fields, string $action, string $method): string
}
```

## Data Models

### Demo Data JSON Structure

```json
{
  "dashboard": {
    "stats": {
      "total_revenue": 125000000,
      "weekly_revenue": 18500000,
      "monthly_revenue": 45000000,
      "total_clicks": 15420,
      "pending_commission": 8500000,
      "paid_commission": 36500000,
      "conversion_rate": 3.2
    },
    "affiliate_info": {
      "affiliate_id": "AFF123",
      "affiliate_link": "https://thuonglo.com/ref/AFF123",
      "referral_code": "AFF123"
    },
    "revenue_chart": {
      "labels": ["T1", "T2", "T3", "T4", "T5", "T6"],
      "data": [15000000, 18000000, 22000000, 19000000, 25000000, 26000000]
    },
    "clicks_chart": {
      "labels": ["T1", "T2", "T3", "T4", "T5", "T6"],
      "data": [2100, 2400, 2800, 2300, 3200, 2600]
    },
    "recent_customers": [
      {
        "id": 1,
        "name": "Nguyễn Văn A",
        "email": "nguyenvana@example.com",
        "registered_date": "2024-01-15",
        "total_orders": 3,
        "total_spent": 5400000,
        "commission_earned": 540000
      }
    ],
    "commission_status": {
      "pending": 8500000,
      "paid": 36500000,
      "pending_count": 12,
      "paid_count": 156
    }
  },
  "commissions": {
    "history": [
      {
        "id": 1,
        "date": "2024-01-20",
        "order_id": "ORD-001",
        "customer_name": "Nguyễn Văn A",
        "order_amount": 1800000,
        "commission_rate": 10,
        "commission_amount": 180000,
        "status": "paid"
      }
    ],
    "policy": {
      "tiers": [
        {
          "level": 1,
          "min_revenue": 0,
          "max_revenue": 50000000,
          "rate": 8
        },
        {
          "level": 2,
          "min_revenue": 50000001,
          "max_revenue": 100000000,
          "rate": 10
        },
        {
          "level": 3,
          "min_revenue": 100000001,
          "max_revenue": null,
          "rate": 12
        }
      ]
    }
  },
  "customers": [
    {
      "id": 1,
      "name": "Nguyễn Văn A",
      "email": "nguyenvana@example.com",
      "phone": "0901234567",
      "registered_date": "2024-01-15",
      "total_orders": 3,
      "total_spent": 5400000,
      "orders": [
        {
          "id": "ORD-001",
          "date": "2024-01-20",
          "amount": 1800000,
          "status": "completed"
        }
      ]
    }
  ],
  "finance": {
    "balance": {
      "available": 12500000,
      "pending": 8500000,
      "total_earned": 45000000,
      "total_withdrawn": 24000000
    },
    "transactions": [
      {
        "id": 1,
        "date": "2024-01-25",
        "type": "commission",
        "amount": 180000,
        "status": "completed",
        "description": "Hoa hồng đơn hàng ORD-001"
      },
      {
        "id": 2,
        "date": "2024-01-20",
        "type": "withdrawal",
        "amount": -5000000,
        "status": "completed",
        "description": "Rút tiền về tài khoản"
      }
    ],
    "withdrawals": [
      {
        "id": 1,
        "date": "2024-01-20",
        "amount": 5000000,
        "bank_name": "Vietcombank",
        "account_number": "1234567890",
        "status": "completed",
        "processed_date": "2024-01-21"
      }
    ]
  },
  "marketing": {
    "affiliate_link": "https://example.com/ref/AFF123",
    "affiliate_id": "AFF123",
    "campaigns": [
      {
        "id": 1,
        "name": "Tết 2024",
        "start_date": "2024-01-01",
        "end_date": "2024-02-15",
        "clicks": 5420,
        "conversions": 156,
        "revenue": 28000000,
        "status": "active"
      }
    ],
    "banners": [
      {
        "id": 1,
        "name": "Banner 728x90",
        "size": "728x90",
        "url": "/assets/images/banners/banner-728x90.jpg"
      }
    ]
  },
  "profile": {
    "id": 1,
    "name": "Nguyễn Văn B",
    "email": "affiliate@example.com",
    "phone": "0987654321",
    "address": "123 Đường ABC, Quận 1, TP.HCM",
    "affiliate_id": "AFF123",
    "referral_code": "AFF123",
    "affiliate_link": "https://thuonglo.com/ref/AFF123",
    "bank_info": {
      "bank_name": "Vietcombank",
      "account_number": "1234567890",
      "account_holder": "NGUYEN VAN B"
    },
    "joined_date": "2023-06-15",
    "status": "active"
  },
  "reports": {
    "clicks": {
      "total": 15420,
      "by_date": [
        {
          "date": "2024-01-20",
          "clicks": 245,
          "unique_clicks": 198
        }
      ],
      "by_source": [
        {
          "source": "Facebook",
          "clicks": 6800,
          "percentage": 44
        },
        {
          "source": "Website",
          "clicks": 5200,
          "percentage": 34
        }
      ]
    },
    "orders": {
      "total": 492,
      "total_revenue": 125000000,
      "total_commission": 12500000,
      "by_date": [
        {
          "date": "2024-01-20",
          "orders": 8,
          "revenue": 14400000,
          "commission": 1440000
        }
      ],
      "by_product": [
        {
          "product_id": 1,
          "product_name": "Sản phẩm A",
          "orders": 156,
          "revenue": 42000000,
          "commission": 4200000
        }
      ]
    }
  }
}
```

### PHP Data Models

```php
// Dashboard Stats
class DashboardStats {
    public int $total_revenue;
    public int $weekly_revenue;
    public int $monthly_revenue;
    public int $total_clicks;
    public int $pending_commission;
    public int $paid_commission;
    public float $conversion_rate;
}

// Affiliate Info
class AffiliateInfo {
    public string $affiliate_id;
    public string $affiliate_link;
    public string $referral_code;
}

// Commission Status
class CommissionStatus {
    public int $pending;
    public int $paid;
    public int $pending_count;
    public int $paid_count;
}

// Commission
class Commission {
    public int $id;
    public string $date;
    public string $order_id;
    public string $customer_name;
    public int $order_amount;
    public float $commission_rate;
    public int $commission_amount;
    public string $status; // paid, pending, cancelled
}

// Customer
class Customer {
    public int $id;
    public string $name;
    public string $email;
    public string $phone;
    public string $registered_date;
    public int $total_orders;
    public int $total_spent;
    public array $orders; // Order[]
}

// Order
class Order {
    public string $id;
    public string $date;
    public int $amount;
    public string $status; // completed, pending, cancelled
}

// Balance
class Balance {
    public int $available;
    public int $pending;
    public int $total_earned;
    public int $total_withdrawn;
}

// Transaction
class Transaction {
    public int $id;
    public string $date;
    public string $type; // commission, withdrawal
    public int $amount;
    public string $status;
    public string $description;
}

// Withdrawal
class Withdrawal {
    public int $id;
    public string $date;
    public int $amount;
    public string $bank_name;
    public string $account_number;
    public string $status; // pending, completed, rejected
    public ?string $processed_date;
}

// Campaign
class Campaign {
    public int $id;
    public string $name;
    public string $start_date;
    public string $end_date;
    public int $clicks;
    public int $conversions;
    public int $revenue;
    public string $status; // active, paused, ended
}

// Profile
class Profile {
    public int $id;
    public string $name;
    public string $email;
    public string $phone;
    public string $address;
    public string $affiliate_id;
    public string $referral_code;
    public string $affiliate_link;
    public BankInfo $bank_info;
    public string $joined_date;
    public string $status; // active, inactive
}

// Bank Info
class BankInfo {
    public string $bank_name;
    public string $account_number;
    public string $account_holder;
}
```



## Correctness Properties

*A property is a characteristic or behavior that should hold true across all valid executions of a system—essentially, a formal statement about what the system should do. Properties serve as the bridge between human-readable specifications and machine-verifiable correctness guarantees.*

### Property Reflection

Sau khi phân tích acceptance criteria, tôi đã xác định các properties có thể test. Tuy nhiên, nhiều properties có sự trùng lặp:

**Redundancy Analysis:**

1. **JSON Loading Properties (1.8, 2.4, 3.5, 4.6, 5.6, 6.4, 7.5, 11.5)**: Tất cả đều test việc load dữ liệu từ JSON file. Có thể gộp thành một property tổng quát về JSON round-trip.

2. **Dashboard Display Properties (1.1-1.7)**: Tất cả đều test việc hiển thị các elements khác nhau trên dashboard. Có thể gộp thành một property về dashboard completeness.

3. **Chart Display Properties (11.1-11.3)**: Tất cả đều test việc hiển thị biểu đồ Chart.js. Có thể gộp thành một property về chart rendering.

4. **Data Display Properties**: Nhiều properties test việc hiển thị dữ liệu trong HTML (2.1, 2.2, 3.1, 3.2, 4.1-4.3, 5.1-5.5, 7.1-7.4). Có thể gộp thành một property tổng quát về data rendering.

5. **Layout Component Properties (8.1-8.4)**: Test việc hiển thị các layout components. Có thể gộp thành một property về layout completeness.

**Consolidated Properties:**

Sau khi loại bỏ redundancy, tôi xác định các properties độc lập sau:

### Property 1: JSON Round-Trip Consistency

*For any* valid demo data structure, when serialized to JSON and then parsed back to PHP array, the resulting data should be equivalent to the original data.

**Validates: Requirements 1.8, 2.4, 3.5, 4.6, 5.6, 6.4, 7.5, 10.4, 11.5**

**Rationale:** Đây là round-trip property cơ bản cho serialization. Đảm bảo dữ liệu không bị mất hoặc thay đổi khi load từ JSON.

### Property 2: Dashboard Completeness

*For any* valid dashboard data from JSON, the rendered HTML output should contain all required elements: revenue chart canvas, clicks display, pending commission display, conversion rate display, customer list, and commission status.

**Validates: Requirements 1.1, 1.2, 1.3, 1.4, 1.5, 1.6, 1.7**

**Rationale:** Đảm bảo dashboard hiển thị đầy đủ thông tin cần thiết cho đại lý.

### Property 3: Data Rendering Completeness

*For any* data object (commission, customer, transaction, campaign, etc.) and its corresponding render function, the rendered HTML should contain all required fields from the data object.

**Validates: Requirements 2.1, 2.2, 3.1, 3.2, 4.1, 4.2, 4.3, 5.4, 5.5, 7.1, 7.4**

**Rationale:** Đảm bảo mọi dữ liệu được hiển thị đầy đủ trong HTML output.

### Property 4: Chart Rendering Completeness

*For any* chart data (revenue, clicks, conversion), the rendered HTML should contain a canvas element with appropriate id/class and Chart.js initialization script.

**Validates: Requirements 1.1, 7.2, 11.1, 11.2, 11.3**

**Rationale:** Đảm bảo biểu đồ được render đúng cách với Chart.js.

### Property 5: Layout Component Inclusion

*For any* page in the affiliate system, the rendered HTML should include all layout components: sidebar navigation, breadcrumb, header, and footer.

**Validates: Requirements 8.1, 8.2, 8.3, 8.4**

**Rationale:** Đảm bảo mọi trang có layout nhất quán.

### Property 6: Customer Detail Completeness

*For any* customer object with orders array, the detail page HTML should contain both customer information and all orders from the orders array.

**Validates: Requirements 3.4**

**Rationale:** Đảm bảo trang chi tiết khách hàng hiển thị đầy đủ thông tin.

### Property 7: Commission Policy Display

*For any* commission policy with tiers array, the rendered HTML should contain all tiers with their level, revenue range, and rate.

**Validates: Requirements 2.3**

**Rationale:** Đảm bảo chính sách hoa hồng được hiển thị đầy đủ.

### Property 8: Marketing Tools Completeness

*For any* affiliate data with affiliate_link and affiliate_id, the marketing tools page should display the link, verify the link contains the affiliate_id, and include banners and QR code elements.

**Validates: Requirements 5.1, 5.2, 5.3**

**Rationale:** Đảm bảo công cụ marketing cung cấp đầy đủ tài nguyên cho đại lý.

### Property 9: Withdrawal Form Presence

*For any* withdrawal page render, the HTML should contain a form element with required fields: amount, bank selection, and account number.

**Validates: Requirements 4.4**

**Rationale:** Đảm bảo form rút tiền có đầy đủ fields cần thiết.

### Property 10: Withdrawal History Display

*For any* withdrawals array, the rendered HTML should contain all withdrawal records with their date, amount, bank info, and status.

**Validates: Requirements 4.5**

**Rationale:** Đảm bảo lịch sử rút tiền được hiển thị đầy đủ.

### Property 11: Profile Information Display

*For any* profile object with bank_info, the settings page should display all profile fields and bank information.

**Validates: Requirements 6.1, 6.2, 6.3**

**Rationale:** Đảm bảo thông tin profile và ngân hàng được hiển thị đầy đủ.

### Property 12: Invalid JSON Error Handling

*For any* malformed JSON string, when attempting to parse it, the system should return an error message and not crash.

**Validates: Requirements 10.2**

**Rationale:** Đảm bảo hệ thống xử lý lỗi JSON một cách graceful.

### Property 13: Data Display Format

*For any* data array and table/card renderer, the rendered HTML should contain either table elements or card elements (not both) with the data.

**Validates: Requirements 10.5**

**Rationale:** Đảm bảo dữ liệu được hiển thị trong format nhất quán.

### Property 14: No Inline CSS in PHP Files

*For any* PHP file in the affiliate module, parsing the file should not find any inline style attributes or style tags.

**Validates: Requirements 13.4**

**Rationale:** Đảm bảo separation of concerns giữa PHP và CSS.

### Property 15: No Inline JavaScript in PHP Files

*For any* PHP file in the affiliate module, parsing the file should not find any inline script tags (except for data initialization in specific format).

**Validates: Requirements 13.5**

**Rationale:** Đảm bảo separation of concerns giữa PHP và JavaScript.

### Example-Based Tests

Một số requirements phù hợp hơn với example-based testing thay vì property-based testing:

**Example 1: Routing to Dashboard**
- Input: URL `/affiliate/dashboard`
- Expected: Router includes `app/views/affiliate/dashboard.php`
- **Validates: Requirements 14.2**

**Example 2: Routing to Commission History**
- Input: URL `/affiliate/commissions/history`
- Expected: Router includes `app/views/affiliate/commissions/history.php`
- **Validates: Requirements 14.3**

**Example 3: Routing to Customer List**
- Input: URL `/affiliate/customers/list`
- Expected: Router includes `app/views/affiliate/customers/list.php`
- **Validates: Requirements 14.4**

**Example 4: Routing to 404**
- Input: URL `/affiliate/nonexistent/page`
- Expected: Router includes `app/views/errors/404.php`
- **Validates: Requirements 14.5**

**Example 5: Customer Detail Navigation**
- Input: Click on customer with id=1
- Expected: Navigate to `/affiliate/customers/detail?id=1`
- **Validates: Requirements 3.3**

**Example 6: Menu Navigation**
- Input: Click on "Commissions" menu item
- Expected: Navigate to `/affiliate/commissions/`
- **Validates: Requirements 8.5**

### Edge Cases

**Edge Case 1: Empty JSON Data**
- Input: Empty JSON object `{}`
- Expected: Display "Không có dữ liệu" message
- **Validates: Requirements 10.3**

**Edge Case 2: Empty Arrays in Data**
- Input: Dashboard data with empty `recent_customers` array
- Expected: Display "Chưa có khách hàng" message instead of empty table

**Edge Case 3: Missing Optional Fields**
- Input: Customer object without `phone` field
- Expected: Display customer info with phone field showing "N/A" or empty



## Error Handling

### JSON Loading Errors

**Error Type:** File not found
```php
if (!file_exists($json_file)) {
    return [
        'error' => true,
        'message' => 'Không tìm thấy file dữ liệu',
        'data' => []
    ];
}
```

**Error Type:** Invalid JSON syntax
```php
$data = json_decode($json_content, true);
if (json_last_error() !== JSON_ERROR_NONE) {
    return [
        'error' => true,
        'message' => 'Dữ liệu JSON không hợp lệ: ' . json_last_error_msg(),
        'data' => []
    ];
}
```

**Error Type:** Empty JSON
```php
if (empty($data)) {
    return [
        'error' => false,
        'message' => 'Không có dữ liệu',
        'data' => []
    ];
}
```

### Routing Errors

**Error Type:** Module not found
```php
if (!file_exists($module_path)) {
    http_response_code(404);
    include 'app/views/errors/404.php';
    exit;
}
```

**Error Type:** Invalid URL format
```php
if (!preg_match('/^\/affiliate\/[a-z]+\/[a-z]+$/', $url)) {
    http_response_code(400);
    include 'app/views/errors/400.php';
    exit;
}
```

### Data Validation Errors

**Error Type:** Missing required fields
```php
function validateCustomer($customer) {
    $required = ['id', 'name', 'email'];
    foreach ($required as $field) {
        if (!isset($customer[$field])) {
            return [
                'valid' => false,
                'message' => "Thiếu trường bắt buộc: {$field}"
            ];
        }
    }
    return ['valid' => true];
}
```

**Error Type:** Invalid data type
```php
function validateAmount($amount) {
    if (!is_numeric($amount) || $amount < 0) {
        return [
            'valid' => false,
            'message' => 'Số tiền không hợp lệ'
        ];
    }
    return ['valid' => true];
}
```

### Display Errors

**Error Type:** Empty data arrays
```php
if (empty($customers)) {
    echo '<div class="empty-state">';
    echo '<p>Chưa có khách hàng nào</p>';
    echo '</div>';
    return;
}
```

**Error Type:** Missing layout components
```php
if (!file_exists('app/views/_layout/sidebar.php')) {
    error_log('Missing sidebar component');
    // Continue without sidebar but log error
}
```

### Chart Rendering Errors

**Error Type:** Invalid chart data
```php
function validateChartData($data) {
    if (!isset($data['labels']) || !isset($data['data'])) {
        return [
            'valid' => false,
            'message' => 'Dữ liệu biểu đồ không đầy đủ'
        ];
    }
    if (count($data['labels']) !== count($data['data'])) {
        return [
            'valid' => false,
            'message' => 'Số lượng labels và data không khớp'
        ];
    }
    return ['valid' => true];
}
```

### Error Display Strategy

**User-facing errors:** Hiển thị thông báo thân thiện bằng tiếng Việt
```php
<div class="alert alert-error">
    <i class="icon-error"></i>
    <span><?php echo htmlspecialchars($error_message); ?></span>
</div>
```

**Developer errors:** Log chi tiết vào error log
```php
error_log("Affiliate System Error: {$error_details}");
```

**Graceful degradation:** Hệ thống tiếp tục hoạt động với dữ liệu mặc định
```php
$data = loadData() ?? getDefaultData();
```

## Testing Strategy

### Dual Testing Approach

Hệ thống sử dụng kết hợp **unit tests** và **property-based tests** để đảm bảo correctness toàn diện:

- **Unit tests**: Kiểm tra các ví dụ cụ thể, edge cases, và error conditions
- **Property tests**: Kiểm tra các properties tổng quát trên nhiều inputs ngẫu nhiên

### Property-Based Testing

**Library:** Sử dụng [Eris](https://github.com/giorgiosironi/eris) cho PHP property-based testing

**Configuration:**
- Minimum 100 iterations per property test
- Each test references design document property
- Tag format: `@group Feature: affiliate-building, Property {number}: {property_text}`

**Example Property Test:**
```php
use Eris\Generator;

/**
 * @group Feature: affiliate-building, Property 1: JSON Round-Trip Consistency
 */
public function testJsonRoundTripConsistency()
{
    $this->forAll(
        Generator\associative([
            'dashboard' => Generator\associative([
                'stats' => Generator\associative([
                    'total_revenue' => Generator\nat(),
                    'total_clicks' => Generator\nat(),
                    'pending_commission' => Generator\nat(),
                    'conversion_rate' => Generator\float()
                ])
            ])
        ])
    )
    ->then(function ($data) {
        $json = json_encode($data);
        $parsed = json_decode($json, true);
        
        $this->assertEquals($data, $parsed);
    });
}

/**
 * @group Feature: affiliate-building, Property 2: Dashboard Completeness
 */
public function testDashboardCompleteness()
{
    $this->forAll(
        Generator\associative([
            'stats' => Generator\associative([
                'total_revenue' => Generator\nat(),
                'total_clicks' => Generator\nat(),
                'pending_commission' => Generator\nat(),
                'conversion_rate' => Generator\float()
            ]),
            'recent_customers' => Generator\seq(
                Generator\associative([
                    'id' => Generator\nat(),
                    'name' => Generator\string(),
                    'email' => Generator\string()
                ])
            )
        ])
    )
    ->then(function ($dashboardData) {
        $html = renderDashboard($dashboardData);
        
        // Check for required elements
        $this->assertStringContainsString('revenue-chart', $html);
        $this->assertStringContainsString($dashboardData['stats']['total_clicks'], $html);
        $this->assertStringContainsString($dashboardData['stats']['pending_commission'], $html);
        $this->assertStringContainsString($dashboardData['stats']['conversion_rate'], $html);
        
        foreach ($dashboardData['recent_customers'] as $customer) {
            $this->assertStringContainsString($customer['name'], $html);
        }
    });
}

/**
 * @group Feature: affiliate-building, Property 12: Invalid JSON Error Handling
 */
public function testInvalidJsonErrorHandling()
{
    $this->forAll(
        Generator\string()
    )
    ->then(function ($invalidJson) {
        // Skip valid JSON strings
        if (json_decode($invalidJson) !== null) {
            return;
        }
        
        $result = parseJsonData($invalidJson);
        
        $this->assertTrue($result['error']);
        $this->assertNotEmpty($result['message']);
        $this->assertIsArray($result['data']);
    });
}
```

### Unit Testing

**Framework:** PHPUnit

**Focus Areas:**
1. Specific examples demonstrating correct behavior
2. Edge cases (empty data, missing fields)
3. Error conditions (invalid JSON, missing files)
4. Integration between components

**Example Unit Tests:**
```php
class AffiliateSystemTest extends TestCase
{
    /**
     * Example 1: Routing to Dashboard
     */
    public function testRoutingToDashboard()
    {
        $router = new Router();
        $result = $router->route('/affiliate/dashboard');
        
        $this->assertEquals('app/views/affiliate/dashboard.php', $result['file']);
    }
    
    /**
     * Example 4: Routing to 404
     */
    public function testRoutingTo404()
    {
        $router = new Router();
        $result = $router->route('/affiliate/nonexistent/page');
        
        $this->assertEquals('app/views/errors/404.php', $result['file']);
        $this->assertEquals(404, $result['status_code']);
    }
    
    /**
     * Edge Case 1: Empty JSON Data
     */
    public function testEmptyJsonData()
    {
        $loader = new DataLoader();
        $result = $loader->loadData('{}');
        
        $this->assertFalse($result['error']);
        $this->assertEquals('Không có dữ liệu', $result['message']);
        $this->assertEmpty($result['data']);
    }
    
    /**
     * Edge Case 2: Empty Arrays in Data
     */
    public function testEmptyCustomersArray()
    {
        $data = ['recent_customers' => []];
        $html = renderDashboard($data);
        
        $this->assertStringContainsString('Chưa có khách hàng', $html);
    }
    
    /**
     * Test commission policy display
     */
    public function testCommissionPolicyDisplay()
    {
        $policy = [
            'tiers' => [
                ['level' => 1, 'min_revenue' => 0, 'max_revenue' => 50000000, 'rate' => 8],
                ['level' => 2, 'min_revenue' => 50000001, 'max_revenue' => 100000000, 'rate' => 10]
            ]
        ];
        
        $html = renderCommissionPolicy($policy);
        
        foreach ($policy['tiers'] as $tier) {
            $this->assertStringContainsString("Level {$tier['level']}", $html);
            $this->assertStringContainsString("{$tier['rate']}%", $html);
        }
    }
    
    /**
     * Test no inline CSS in PHP files
     */
    public function testNoInlineCssInPhpFiles()
    {
        $phpFiles = glob('app/views/affiliate/**/*.php');
        
        foreach ($phpFiles as $file) {
            $content = file_get_contents($file);
            
            // Check for inline style attributes
            $this->assertStringNotContainsString('style=', $content, 
                "File {$file} contains inline style attribute");
            
            // Check for style tags
            $this->assertStringNotContainsString('<style>', $content,
                "File {$file} contains inline style tag");
        }
    }
    
    /**
     * Test no inline JavaScript in PHP files
     */
    public function testNoInlineJavaScriptInPhpFiles()
    {
        $phpFiles = glob('app/views/affiliate/**/*.php');
        
        foreach ($phpFiles as $file) {
            $content = file_get_contents($file);
            
            // Allow data initialization scripts but not inline event handlers
            $this->assertStringNotContainsString('onclick=', $content,
                "File {$file} contains inline onclick handler");
            $this->assertStringNotContainsString('onload=', $content,
                "File {$file} contains inline onload handler");
        }
    }
}
```

### Integration Testing

**Focus:** Kiểm tra tương tác giữa các components

```php
class AffiliateIntegrationTest extends TestCase
{
    /**
     * Test full page rendering with layout components
     */
    public function testFullPageRendering()
    {
        $page = new AffiliatePage('dashboard');
        $html = $page->render();
        
        // Check layout components
        $this->assertStringContainsString('sidebar', $html);
        $this->assertStringContainsString('breadcrumb', $html);
        $this->assertStringContainsString('header', $html);
        $this->assertStringContainsString('footer', $html);
        
        // Check main content
        $this->assertStringContainsString('dashboard', $html);
    }
    
    /**
     * Test data flow from JSON to HTML
     */
    public function testDataFlowFromJsonToHtml()
    {
        // Create test JSON file
        $testData = [
            'customers' => [
                ['id' => 1, 'name' => 'Test Customer', 'email' => 'test@example.com']
            ]
        ];
        file_put_contents('test_data.json', json_encode($testData));
        
        // Load and render
        $loader = new DataLoader('test_data.json');
        $data = $loader->getData('customers');
        $html = renderCustomerList($data);
        
        // Verify
        $this->assertStringContainsString('Test Customer', $html);
        $this->assertStringContainsString('test@example.com', $html);
        
        // Cleanup
        unlink('test_data.json');
    }
}
```

### Test Organization

```
tests/
├── Unit/
│   ├── DataLoaderTest.php
│   ├── RouterTest.php
│   ├── RenderersTest.php
│   └── ValidationTest.php
├── Property/
│   ├── JsonRoundTripTest.php
│   ├── DashboardCompletenessTest.php
│   ├── DataRenderingTest.php
│   └── ErrorHandlingTest.php
└── Integration/
    ├── PageRenderingTest.php
    └── DataFlowTest.php
```

### Test Coverage Goals

- **Unit tests**: 80% code coverage
- **Property tests**: All 15 properties implemented
- **Integration tests**: All major user flows covered
- **Edge cases**: All identified edge cases tested

### Continuous Testing

- Run unit tests on every commit
- Run property tests (100 iterations) on every commit
- Run full property tests (1000 iterations) before release
- Monitor test execution time and optimize slow tests

### Test Data Management

**Demo Data:**
- Use `demo_data.json` for manual testing
- Generate random data for property tests
- Create minimal fixtures for unit tests

**Test Isolation:**
- Each test uses its own data
- No shared state between tests
- Clean up test files after execution

