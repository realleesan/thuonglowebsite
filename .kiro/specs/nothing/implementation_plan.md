# Kế Hoạch Chuyển Đổi Database & Refactor MVC - ThuongLo.com

## Tổng Quan

Hệ thống hiện tại đang sử dụng dữ liệu JSON tĩnh. Kế hoạch này bao gồm 3 giai đoạn chính:

1.**Database Migration**: Chuyển từ JSON sang MySQL với PDO, sử dụng migrations và seeders

2.**MVC Refactoring**: Tách cấu trúc thành MVC + API + Route + Core + Config + Services

3.**Business Logic Implementation**: Watermark, blur security, data partitioning, Excel upload

---

## PHASE 1: DATABASE MIGRATION

### 1.1. Database Setup & Configuration

**Files:**

-`config.php` - Cập nhật database config

-`core/database.php` - Implement PDO connection với Singleton pattern

-`database/schema/` - Folder chứa schema tổng thể

-`database/migrations/` - Folder chứa các migration files

-`database/seeders/` - Folder chứa các seeder files

**Cấu trúc Database:**

```sql

-- Core Tables

users (id, phone, password, full_name, role, ref_code, referred_by, current_device_id, status, created_at, updated_at)

products (id, title, description_short, price, category_id, status, created_at, updated_at)

categories (id, name, slug, description, status, created_at, updated_at)

orders (id, user_id, product_id, order_code, amount, payment_status, created_at, activated_at, updated_at)


-- Data Tables (Partitioned)

product_data_rows (id, product_id, row_index, created_at)

product_data_columns (id, product_id, column_name, column_type, is_blur_when_not_purchased, display_order, created_at)

product_data_values (id, row_id, column_id, value, created_at)


-- Security & Affiliate

affiliate_logs (id, agent_id, order_id, commission_amount, payout_status, created_at)

security_logs (id, user_id, ip_address, user_agent, action, created_at)

device_sessions (id, user_id, device_id, ip_address, user_agent, last_activity, created_at)


-- User Features

user_notes (id, user_id, product_id, row_id, note_content, created_at)

user_favorites (id, user_id, product_id, created_at)

user_history (id, user_id, product_id, action, created_at)

```

### 1.2. Migration System

**Files:**

-`core/Migration.php` - Base migration class

-`core/Migrator.php` - Migration runner

-`database/migrations/001_create_users_table.php`

-`database/migrations/002_create_products_table.php`

-`database/migrations/003_create_data_tables.php`

-`database/migrations/004_create_affiliate_tables.php`

-`database/migrations/005_create_security_tables.php`

-`database/migrations/006_create_user_features_tables.php`

**Migration Naming Convention:**

- Format: `{timestamp}_{description}.php`
- Example: `20240201_001_create_users_table.php`

**Migration Structure:**

```php

classCreateUsersTableextendsMigration {

    publicfunctionup() {

        // CREATE TABLE logic

    }

  

    publicfunctiondown() {

        // DROP TABLE logic

    }

}

```

### 1.3. Seeder System

**Files:**

-`core/Seeder.php` - Base seeder class

-`database/seeders/UsersSeeder.php`

-`database/seeders/ProductsSeeder.php`

-`database/seeders/ProductDataSeeder.php`

-`database/seeders/DatabaseSeeder.php` - Main seeder

**Seeder Structure:**

```php

classProductsSeederextendsSeeder {

    publicfunctionrun() {

        // Insert sample products

    }

}

```

### 1.4. Data Migration Script

**File:**`scripts/migrate_json_to_sql.php`

Script để:

- Đọc JSON files hiện tại
- Parse và validate data
- Insert vào database theo đúng schema
- Log migration progress

---

## PHASE 2: MVC REFACTORING

### 2.1. Cấu Trúc Thư Mục Mới

```

app/

├── controllers/          # Điều khiển (giữ nguyên)

├── models/             # Models (giữ nguyên)

├── views/              # Views (giữ nguyên)

├── services/           # [NEW] Business Logic Layer

│   ├── ProductService.php

│   ├── OrderService.php

│   ├── AuthService.php

│   ├── AffiliateService.php

│   ├── DataService.php

│   └── WatermarkService.php

├── middleware/         # [NEW] Request Middleware

│   ├── AuthMiddleware.php

│   ├── RoleMiddleware.php

│   └── SecurityMiddleware.php

└── validators/         # [NEW] Input Validation

    ├── ProductValidator.php

    └── OrderValidator.php


api/

├── v1/                 # [NEW] API Versioning

│   ├── products.php

│   ├── orders.php

│   ├── auth.php

│   └── data.php

└── webhooks/           # [NEW] Webhook Handlers

    └── payment.php


routes/

├── web.php             # [NEW] Web Routes

├── api.php             # [NEW] API Routes

└── Route.php           # [NEW] Route Handler


core/

├── database.php        # [UPDATE] PDO Connection

├── router.php          # [UPDATE] Enhanced Router

├── security.php        # [UPDATE] Watermark, Blur

├── session.php         # [UPDATE] Device Management

└── BaseController.php  # [NEW] Base Controller


config/

├── database.php        # [NEW] Database Config

├── app.php             # [NEW] App Config

└── security.php        # [NEW] Security Config

```

### 2.2. Services Layer

**Purpose:** Tách business logic khỏi Controllers

**Example: ProductService.php**

```php

classProductService {

    publicfunctiongetProductData($productId, $userId) {

        // Check purchase status

        // Load data with proper blur logic

        // Apply watermark

        // Return formatted data

    }

  

    publicfunctionuploadExcelData($productId, $file) {

        // Parse Excel

        // Validate columns

        // Insert into partitioned tables

    }

}

```

### 2.3. Route System

**File: `routes/web.php`**

```php

Route::get('/', 'HomeController@index');

Route::get('/products', 'ProductsController@index');

Route::get('/products/{id}', 'ProductsController@show');

Route::post('/cart/add', 'CartController@add');

```

**File: `routes/api.php`**

```php

Route::api('/api/v1/products', 'Api\ProductsController@index');

Route::api('/api/v1/data/{productId}', 'Api\DataController@getData');

```

### 2.4. Middleware System

**AuthMiddleware:** Check login status

**RoleMiddleware:** Check user role

**SecurityMiddleware:** Device check, IP validation

---

## PHASE 3: BUSINESS LOGIC IMPLEMENTATION

### 3.1. Data Partitioning System

**Tables:**

-`product_data_rows`: Lưu từng dòng data

-`product_data_columns`: Định nghĩa cột (tên, loại, blur config)

-`product_data_values`: Lưu giá trị theo row_id + column_id

**Benefits:**

- Dễ dàng thêm/xóa cột
- Cấu hình blur theo cột
- Query linh hoạt

**Service: `DataService.php`**

```php

classDataService {

    publicfunctiongetProductData($productId, $userId, $filters= []) {

        // Check purchase

        // Load rows

        // Load columns with blur config

        // Load values

        // Merge and format

        // Apply blur based on purchase status

    }

}

```

### 3.2. Blur Security Implementation

**Backend Approach (An toàn nhất):**

**File: `app/services/DataService.php`**

```php

publicfunctiongetProductDataForUser($productId, $userId) {

    $hasPurchased=$this->checkPurchase($userId, $productId);

  

    if (!$hasPurchased) {

        // Chỉ trả về cột "ngành hàng"

        return$this->getPublicColumns($productId);

    }

  

    // Đã mua nhưng chưa đăng nhập -> blur một số cột

    if (!isLoggedIn()) {

        return$this->getBlurredColumns($productId);

    }

  

    // Đã mua và đã đăng nhập -> full data

    return$this->getFullData($productId);

}

```

**Frontend:** Chỉ hiển thị data mà backend trả về. Không có data trong HTML nếu không có quyền.

### 3.3. Watermark System

**File: `core/WatermarkService.php`**

**Server-side Watermark (PHP GD):**

```php

classWatermarkService {

    publicfunctionapplyWatermark($content, $userId, $productId) {

        // Generate watermark text: "User: {phone} - ID: {userId} - {datetime}"

        // For images: Use GD library

        // For text: Overlay CSS + server-side rendering

        // Return watermarked content

    }

}

```

**Implementation:**

- Text data: Render với watermark overlay (CSS + server-side)
- Images: PHP GD library chèn watermark vào image
- PDF: Sử dụng FPDF hoặc TCPDF

### 3.4. Excel Upload & Bulk Import

**File: `app/services/ProductDataService.php`**

**Features:**

- Upload Excel/CSV file
- Parse và validate
- Map columns (admin chọn cột nào blur)
- Bulk insert vào partitioned tables
- Progress tracking

**Library:** PhpSpreadsheet (Composer)

**Admin Interface:**

- Upload form
- Column mapping interface
- Blur configuration per column
- Preview before import
- Import progress

### 3.5. Admin Features

**Files:**

-`app/views/admin/products/data_upload.php` - Excel upload interface

-`app/controllers/AdminController.php` - Handle upload

-`app/services/ExcelImportService.php` - Parse Excel

**Features:**

- Upload Excel/CSV
- Configure column blur settings
- Bulk product creation
- Data preview
- Import validation

---

## IMPLEMENTATION ORDER

### Week 1: Database Foundation

1. Setup PDO connection
2. Create migration system
3. Create base migrations (users, products, categories)
4. Create seeder system
5. Migrate JSON data to SQL

### Week 2: Data Partitioning

1. Create data partitioning tables
2. Implement DataService
3. Update ProductsModel
4. Test data query & merge

### Week 3: MVC Refactoring

1. Create Services layer
2. Refactor Controllers to use Services
3. Setup Route system
4. Create Middleware
5. Move configs to config/ folder

### Week 4: Security & Features

1. Implement blur security (backend-only)
2. Implement watermark system
3. Create Excel upload feature
4. Admin interface for data management
5. Testing & optimization

---

## TECHNICAL DECISIONS

### Database

-**Type:** MySQL/MariaDB (XAMPP compatible)

-**Library:** PDO (prepared statements, security)

-**Connection:** Singleton pattern trong `core/database.php`

### Migrations

-**Format:** Timestamped files

-**Structure:** Up/Down methods

-**Tracking:**`migrations` table để track applied migrations

### Seeders

-**Separate folder:**`database/seeders/`

-**Main seeder:**`DatabaseSeeder.php` chạy tất cả

-**Individual seeders:** Mỗi entity một seeder

### Services

-**Purpose:** Business logic, data transformation

-**Dependency:** Services có thể inject Models

-**Example:** ProductService uses ProductsModel

### Security

-**Blur:** Backend không trả data nếu không có quyền

-**Watermark:** Server-side rendering (PHP GD)

-**Data:** Partitioned tables, không export full

---

## FILES TO CREATE/MODIFY

### New Files (30+)

-`core/Migration.php`

-`core/Migrator.php`

-`core/Seeder.php`

-`core/BaseController.php`

-`core/WatermarkService.php`

-`app/services/*.php` (6 files)

-`database/migrations/*.php` (6+ files)

-`database/seeders/*.php` (4+ files)

-`routes/web.php`

-`routes/api.php`

-`config/database.php`

-`config/app.php`

-`scripts/migrate_json_to_sql.php`

### Modified Files

-`config.php` - Update database config

-`core/database.php` - PDO implementation

-`core/router.php` - Enhanced routing

-`app/models/*.php` - Update to use PDO

-`app/controllers/*.php` - Use Services layer

---

## TESTING CHECKLIST

- [ ] Migrations run successfully
- [ ] Seeders populate data correctly
- [ ] Data partitioning works
- [ ] Blur security prevents data leak
- [ ] Watermark appears on data
- [ ] Excel upload works
- [ ] Bulk import successful
- [ ] Admin can configure blur columns
- [ ] API endpoints work
- [ ] Services layer functions correctly
