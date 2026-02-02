# Hosting Path Configuration - Requirements

## Overview
Cấu hình lại hệ thống đường dẫn của website Thuong Lo để hoạt động đúng trên hosting với domain https://test1.web3b.com/, nơi toàn bộ source code được đặt trong thư mục public_html.

## Business Context
- Website hiện tại đang chạy local với đường dẫn tương đối
- Đã deploy lên hosting shared với domain https://test1.web3b.com/
- Source code được đặt trong public_html (root directory của domain)
- Cần đảm bảo tất cả đường dẫn hoạt động đúng trên môi trường hosting

## User Stories

### 1. Base URL Configuration
**As a** system administrator  
**I want** to configure base URL cho hosting environment  
**So that** tất cả đường dẫn trong website hoạt động đúng trên domain https://test1.web3b.com/

**Acceptance Criteria:**
- 1.1 Tạo file config.php với cấu hình base URL
- 1.2 Base URL phải tự động detect môi trường (local vs hosting)
- 1.3 Hỗ trợ cả HTTP và HTTPS
- 1.4 Có thể dễ dàng thay đổi domain khi cần

### 2. Static Assets Path Configuration
**As a** user  
**I want** tất cả static assets (CSS, JS, images) load đúng  
**So that** website hiển thị và hoạt động bình thường trên hosting

**Acceptance Criteria:**
- 2.1 Tất cả đường dẫn CSS trong layout load đúng
- 2.2 Tất cả đường dẫn JavaScript load đúng
- 2.3 Tất cả đường dẫn hình ảnh hiển thị đúng
- 2.4 Đường dẫn logo và favicon hoạt động
- 2.5 Font files load đúng

### 3. Internal Links and Navigation
**As a** user  
**I want** tất cả liên kết nội bộ hoạt động đúng  
**So that** có thể điều hướng trong website một cách bình thường

**Acceptance Criteria:**
- 3.1 Menu navigation links hoạt động đúng
- 3.2 Breadcrumb links hoạt động đúng
- 3.3 Product detail links hoạt động đúng
- 3.4 News detail links hoạt động đúng
- 3.5 Form action URLs hoạt động đúng

### 4. URL Rewriting and Routing
**As a** user  
**I want** URLs thân thiện và clean  
**So that** website có SEO tốt và URLs dễ nhớ

**Acceptance Criteria:**
- 4.1 Cấu hình .htaccess cho URL rewriting
- 4.2 Remove index.php khỏi URLs
- 4.3 Handle 404 errors properly
- 4.4 Redirect www to non-www (hoặc ngược lại)
- 4.5 Force HTTPS nếu có SSL certificate

### 5. Environment Detection
**As a** developer  
**I want** hệ thống tự động detect môi trường  
**So that** không cần thay đổi code khi deploy

**Acceptance Criteria:**
- 5.1 Detect local development environment
- 5.2 Detect hosting environment
- 5.3 Sử dụng cấu hình phù hợp cho từng môi trường
- 5.4 Debug mode chỉ bật ở local
- 5.5 Error reporting khác nhau giữa local và production

## Technical Requirements

### Performance
- Tất cả static assets phải có cache headers phù hợp
- Minimize HTTP requests bằng cách combine CSS/JS nếu cần
- Optimize images loading

### Security
- Prevent direct access to sensitive files
- Secure file upload paths
- Validate all user inputs in URLs

### Compatibility
- Hoạt động trên shared hosting environment
- Tương thích với PHP 7.4+
- Không yêu cầu special server configurations

## Constraints
- Không thể thay đổi server configuration
- Phải sử dụng .htaccess cho URL rewriting
- Không có quyền truy cập root server
- Phải maintain backward compatibility với code hiện tại

## Success Metrics
- Website load hoàn toàn trên https://test1.web3b.com/
- Tất cả pages accessible và hiển thị đúng
- No broken links hoặc missing assets
- Page load time < 3 seconds
- All forms và functionalities hoạt động bình thường