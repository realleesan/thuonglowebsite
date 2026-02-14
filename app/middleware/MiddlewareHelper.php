<?php
/**
 * Middleware Helper
 * Provides easy-to-use functions for applying middleware in controllers
 */

require_once __DIR__ . '/AuthMiddleware.php';

class MiddlewareHelper {
    private static ?AuthMiddleware $authMiddleware = null;
    
    /**
     * Get AuthMiddleware instance (singleton)
     */
    private static function getAuthMiddleware(): AuthMiddleware {
        if (self::$authMiddleware === null) {
            self::$authMiddleware = new AuthMiddleware();
        }
        
        return self::$authMiddleware;
    }
    
    /**
     * Apply authentication middleware
     * Usage: MiddlewareHelper::requireAuth()
     */
    public static function requireAuth(): bool {
        return self::getAuthMiddleware()->requireAuth();
    }
    
    /**
     * Apply role-based middleware
     * Usage: MiddlewareHelper::requireRole('admin')
     */
    public static function requireRole(string $role): bool {
        return self::getAuthMiddleware()->requireRole($role);
    }
    
    /**
     * Apply permission-based middleware
     * Usage: MiddlewareHelper::requirePermission('users.edit')
     */
    public static function requirePermission(string $permission): bool {
        return self::getAuthMiddleware()->requirePermission($permission);
    }
    
    /**
     * Apply admin middleware
     * Usage: MiddlewareHelper::requireAdmin()
     */
    public static function requireAdmin(): bool {
        return self::getAuthMiddleware()->requireAdmin();
    }
    
    /**
     * Apply affiliate middleware
     * Usage: MiddlewareHelper::requireAffiliate()
     */
    public static function requireAffiliate(): bool {
        return self::getAuthMiddleware()->requireAffiliate();
    }
    
    /**
     * Apply guest middleware (not authenticated)
     * Usage: MiddlewareHelper::requireGuest()
     */
    public static function requireGuest(): bool {
        return self::getAuthMiddleware()->requireGuest();
    }
    
    /**
     * Apply CSRF protection
     * Usage: MiddlewareHelper::requireCsrfToken()
     */
    public static function requireCsrfToken(): bool {
        return self::getAuthMiddleware()->requireCsrfToken();
    }
    
    /**
     * Apply rate limiting
     * Usage: MiddlewareHelper::requireRateLimit('login', 5, 300)
     */
    public static function requireRateLimit(string $action = 'general', int $maxAttempts = 10, int $timeWindow = 300): bool {
        return self::getAuthMiddleware()->requireRateLimit($action, $maxAttempts, $timeWindow);
    }
    
    /**
     * Check session timeout
     * Usage: MiddlewareHelper::checkSessionTimeout()
     */
    public static function checkSessionTimeout(): bool {
        return self::getAuthMiddleware()->checkSessionTimeout();
    }
    
    /**
     * Apply multiple middleware at once
     * Usage: MiddlewareHelper::apply(['auth', 'admin', 'csrf'])
     */
    public static function apply(array $middlewares): bool {
        return self::getAuthMiddleware()->combineMiddleware($middlewares);
    }
    
    /**
     * Check if user can access resource
     * Usage: MiddlewareHelper::canAccess('admin.users')
     */
    public static function canAccess(string $resource): bool {
        return self::getAuthMiddleware()->canAccess($resource);
    }
    
    /**
     * Get current user
     * Usage: $user = MiddlewareHelper::getCurrentUser()
     */
    public static function getCurrentUser(): ?array {
        return self::getAuthMiddleware()->getCurrentUser();
    }
    
    /**
     * Check if user has role
     * Usage: MiddlewareHelper::hasRole('admin')
     */
    public static function hasRole(string $role): bool {
        return self::getAuthMiddleware()->hasRole($role);
    }
    
    /**
     * Check if user has permission
     * Usage: MiddlewareHelper::hasPermission('users.edit')
     */
    public static function hasPermission(string $permission): bool {
        return self::getAuthMiddleware()->hasPermission($permission);
    }
    
    /**
     * Get CSRF token
     * Usage: $token = MiddlewareHelper::getCsrfToken()
     */
    public static function getCsrfToken(): string {
        return self::getAuthMiddleware()->getCsrfToken();
    }
}

/**
 * Global helper functions for easy middleware usage
 */

if (!function_exists('require_auth')) {
    function require_auth(): bool {
        return MiddlewareHelper::requireAuth();
    }
}

if (!function_exists('require_role')) {
    function require_role(string $role): bool {
        return MiddlewareHelper::requireRole($role);
    }
}

if (!function_exists('require_admin')) {
    function require_admin(): bool {
        return MiddlewareHelper::requireAdmin();
    }
}

if (!function_exists('require_affiliate')) {
    function require_affiliate(): bool {
        return MiddlewareHelper::requireAffiliate();
    }
}

if (!function_exists('require_guest')) {
    function require_guest(): bool {
        return MiddlewareHelper::requireGuest();
    }
}

if (!function_exists('require_csrf')) {
    function require_csrf(): bool {
        return MiddlewareHelper::requireCsrfToken();
    }
}

if (!function_exists('current_user')) {
    function current_user(): ?array {
        return MiddlewareHelper::getCurrentUser();
    }
}

if (!function_exists('has_role')) {
    function has_role(string $role): bool {
        return MiddlewareHelper::hasRole($role);
    }
}

if (!function_exists('has_permission')) {
    function has_permission(string $permission): bool {
        return MiddlewareHelper::hasPermission($permission);
    }
}

if (!function_exists('csrf_token')) {
    function csrf_token(): string {
        return MiddlewareHelper::getCsrfToken();
    }
}

if (!function_exists('can_access')) {
    function can_access(string $resource): bool {
        return MiddlewareHelper::canAccess($resource);
    }
}