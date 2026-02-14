# Authentication Middleware Usage Examples

## Overview

Hệ thống middleware authentication cung cấp nhiều cách để bảo vệ routes và kiểm soát quyền truy cập trong ứng dụng PHP.

## Basic Usage

### 1. Using MiddlewareHelper Class

```php
<?php
require_once __DIR__ . '/../middleware/MiddlewareHelper.php';

class AdminController {
    public function dashboard() {
        // Require admin role
        if (!MiddlewareHelper::requireAdmin()) {
            return; // Middleware handles redirect
        }
        
        // Admin dashboard logic here
        $this->renderView('admin/dashboard');
    }
    
    public function users() {
        // Multiple middleware checks
        if (!MiddlewareHelper::apply(['auth', 'admin', 'csrf'])) {
            return;
        }
        
        // Users management logic
        $this->renderView('admin/users');
    }
}
```

### 2. Using Global Helper Functions

```php
<?php
require_once __DIR__ . '/../middleware/MiddlewareHelper.php';

class UserController {
    public function profile() {
        // Simple authentication check
        if (!require_auth()) {
            return;
        }
        
        $user = current_user();
        $this->renderView('users/profile', ['user' => $user]);
    }
    
    public function editProfile() {
        // Authentication + CSRF protection
        if (!require_auth() || !require_csrf()) {
            return;
        }
        
        // Profile editing logic
    }
}
```

### 3. Using Direct AuthMiddleware

```php
<?php
require_once __DIR__ . '/../middleware/AuthMiddleware.php';

class AffiliateController {
    private AuthMiddleware $middleware;
    
    public function __construct() {
        $this->middleware = new AuthMiddleware();
    }
    
    public function dashboard() {
        // Require affiliate role or higher
        if (!$this->middleware->requireAffiliate()) {
            return;
        }
        
        $this->renderView('affiliate/dashboard');
    }
}
```

## Advanced Usage Examples

### 1. Resource-Based Access Control

```php
class ProductController {
    public function edit($productId) {
        // Check if user can edit this specific product
        if (!can_access("products.edit.{$productId}")) {
            return;
        }
        
        // Edit product logic
    }
    
    public function delete($productId) {
        // Multiple checks: auth, permission, CSRF
        if (!require_auth() || 
            !has_permission('products.delete') || 
            !require_csrf()) {
            return;
        }
        
        // Delete product logic
    }
}
```

### 2. Rate Limiting for Sensitive Actions

```php
class AuthController {
    public function processLogin() {
        // Rate limit login attempts
        if (!MiddlewareHelper::requireRateLimit('login', 5, 300)) {
            return; // Too many attempts
        }
        
        // Login processing logic
    }
    
    public function processForgotPassword() {
        // Rate limit password reset requests
        if (!MiddlewareHelper::requireRateLimit('password_reset', 3, 600)) {
            return;
        }
        
        // Password reset logic
    }
}
```

### 3. Session Timeout Handling

```php
class BaseController {
    public function __construct() {
        // Check session timeout on every request
        MiddlewareHelper::checkSessionTimeout();
    }
}
```

### 4. Guest-Only Pages

```php
class PublicController {
    public function login() {
        // Redirect authenticated users to dashboard
        if (!require_guest()) {
            return;
        }
        
        $this->renderView('auth/login');
    }
    
    public function register() {
        if (!require_guest()) {
            return;
        }
        
        $this->renderView('auth/register');
    }
}
```

## Middleware Combinations

### Common Patterns

```php
// Admin pages
MiddlewareHelper::apply(['auth', 'admin', 'timeout']);

// Affiliate pages
MiddlewareHelper::apply(['auth', 'affiliate', 'timeout']);

// User pages with CSRF protection
MiddlewareHelper::apply(['auth', 'csrf', 'timeout']);

// Public forms with rate limiting
MiddlewareHelper::apply(['guest', 'csrf', 'rate_limit']);

// API endpoints
MiddlewareHelper::apply(['auth', 'permission:api.access']);
```

### Custom Role/Permission Checks

```php
// Custom role
MiddlewareHelper::apply(['role:moderator']);

// Custom permission
MiddlewareHelper::apply(['permission:posts.publish']);

// Custom resource access
MiddlewareHelper::apply(['resource:admin.settings']);
```

## Integration with Existing Controllers

### Updating AdminController

```php
<?php
require_once __DIR__ . '/../middleware/MiddlewareHelper.php';

class AdminController {
    public function __construct() {
        // Apply middleware to all admin actions
        if (!require_admin()) {
            return;
        }
    }
    
    public function dashboard() {
        $this->renderView('admin/dashboard');
    }
    
    public function users() {
        // Additional CSRF check for user management
        if (!require_csrf()) {
            return;
        }
        
        $this->renderView('admin/users');
    }
}
```

### Updating UserController

```php
<?php
require_once __DIR__ . '/../middleware/MiddlewareHelper.php';

class UserController {
    public function dashboard() {
        if (!require_auth()) {
            return;
        }
        
        $user = current_user();
        $this->renderView('users/dashboard', ['user' => $user]);
    }
    
    public function orders() {
        if (!require_auth()) {
            return;
        }
        
        // Check if user can view orders
        if (!has_permission('orders.view')) {
            MiddlewareHelper::setFlashMessage('error', 'Không có quyền xem đơn hàng');
            return;
        }
        
        $this->renderView('users/orders');
    }
}
```

## View Integration

### Using Middleware Data in Views

```php
// In controller
public function dashboard() {
    if (!require_auth()) {
        return;
    }
    
    $user = current_user();
    $canEditProfile = has_permission('profile.edit');
    $csrfToken = csrf_token();
    
    $this->renderView('dashboard', [
        'user' => $user,
        'canEditProfile' => $canEditProfile,
        'csrfToken' => $csrfToken
    ]);
}
```

```html
<!-- In view file -->
<h1>Xin chào, <?= htmlspecialchars($user['name']) ?>!</h1>

<?php if ($canEditProfile): ?>
    <a href="/profile/edit">Chỉnh sửa hồ sơ</a>
<?php endif; ?>

<form method="POST" action="/profile/update">
    <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
    <!-- Form fields -->
</form>
```

## Error Handling

### Custom Error Pages

```php
class ErrorController {
    public function unauthorized() {
        http_response_code(401);
        $this->renderView('errors/401');
    }
    
    public function forbidden() {
        http_response_code(403);
        $this->renderView('errors/403');
    }
}
```

### Middleware Error Handling

```php
class CustomAuthMiddleware extends AuthMiddleware {
    protected function handleUnauthorized() {
        // Custom unauthorized handling
        $this->logSecurityEvent('unauthorized_access');
        parent::handleUnauthorized();
    }
    
    protected function handleForbidden() {
        // Custom forbidden handling
        $this->logSecurityEvent('forbidden_access');
        parent::handleForbidden();
    }
}
```

## Best Practices

1. **Apply middleware early**: Check authentication/authorization at the beginning of controller methods
2. **Use appropriate middleware**: Don't over-protect public pages or under-protect sensitive pages
3. **Combine middleware efficiently**: Use `apply()` method for multiple checks
4. **Handle errors gracefully**: Provide clear error messages and appropriate redirects
5. **Log security events**: Track authentication failures and unauthorized access attempts
6. **Test thoroughly**: Ensure middleware works correctly with different user roles and scenarios

## Testing Middleware

```php
// Example test for middleware
class MiddlewareTest {
    public function testRequireAuth() {
        // Test unauthenticated user
        $_SESSION = [];
        $result = MiddlewareHelper::requireAuth();
        $this->assertFalse($result);
        
        // Test authenticated user
        $_SESSION['user_id'] = 1;
        $result = MiddlewareHelper::requireAuth();
        $this->assertTrue($result);
    }
    
    public function testRequireAdmin() {
        // Test regular user
        $_SESSION['user'] = ['role' => 'user'];
        $result = MiddlewareHelper::requireAdmin();
        $this->assertFalse($result);
        
        // Test admin user
        $_SESSION['user'] = ['role' => 'admin'];
        $result = MiddlewareHelper::requireAdmin();
        $this->assertTrue($result);
    }
}
```