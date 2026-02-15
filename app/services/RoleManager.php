<?php
/**
 * RoleManager Service
 * Handles role-based access control and permissions
 */

class RoleManager {
    private array $roleHierarchy;
    private array $permissions;
    
    public function __construct() {
        $this->initializeRoleHierarchy();
        $this->initializePermissions();
    }
    
    /**
     * Initialize role hierarchy (higher roles inherit lower role permissions)
     */
    private function initializeRoleHierarchy(): void {
        $this->roleHierarchy = [
            'admin' => ['admin', 'affiliate', 'user'],
            'affiliate' => ['affiliate', 'user'],
            'user' => ['user'],
        ];
    }
    
    /**
     * Initialize permissions for each role
     */
    private function initializePermissions(): void {
        $this->permissions = [
            'admin' => [
                'admin.dashboard',
                'admin.users.view',
                'admin.users.create',
                'admin.users.edit',
                'admin.users.delete',
                'admin.products.view',
                'admin.products.create',
                'admin.products.edit',
                'admin.products.delete',
                'admin.orders.view',
                'admin.orders.edit',
                'admin.orders.delete',
                'admin.categories.view',
                'admin.categories.create',
                'admin.categories.edit',
                'admin.categories.delete',
                'admin.news.view',
                'admin.news.create',
                'admin.news.edit',
                'admin.news.delete',
                'admin.events.view',
                'admin.events.create',
                'admin.events.edit',
                'admin.events.delete',
                'admin.settings.view',
                'admin.settings.edit',
                'admin.affiliates.view',
                'admin.affiliates.create',
                'admin.affiliates.edit',
                'admin.affiliates.delete',
                'admin.revenue.view',
                'admin.contacts.view',
                'admin.contacts.edit',
                'admin.contacts.delete',
                'user.dashboard',
                'user.profile.view',
                'user.profile.edit',
                'user.orders.view',
                'affiliate.dashboard',
                'affiliate.commissions.view',
                'affiliate.customers.view',
                'affiliate.reports.view',
                'affiliate.marketing.view',
            ],
            'affiliate' => [
                'affiliate.dashboard',
                'affiliate.commissions.view',
                'affiliate.customers.view',
                'affiliate.reports.view',
                'affiliate.marketing.view',
                'affiliate.profile.view',
                'affiliate.profile.edit',
                'user.dashboard',
                'user.profile.view',
                'user.profile.edit',
                'user.orders.view',
            ],
            'user' => [
                'user.dashboard',
                'user.profile.view',
                'user.profile.edit',
                'user.orders.view',
                'user.orders.create',
            ],
        ];
    }
    
    /**
     * Check if user has specific role
     */
    public function hasRole(array $user, string $role): bool {
        if (!isset($user['role'])) {
            return false;
        }
        
        $userRole = $user['role'];
        
        // Check if user's role is in the hierarchy for the required role
        return in_array($role, $this->roleHierarchy[$userRole] ?? []);
    }
    
    /**
     * Check if user has specific permission
     */
    public function hasPermission(array $user, string $permission): bool {
        if (!isset($user['role'])) {
            return false;
        }
        
        $userRole = $user['role'];
        $userPermissions = $this->permissions[$userRole] ?? [];
        
        return in_array($permission, $userPermissions);
    }
    
    /**
     * Get all permissions for a role
     */
    public function getRolePermissions(string $role): array {
        return $this->permissions[$role] ?? [];
    }
    
    /**
     * Check if user can access a resource
     */
    public function canAccess(array $user, string $resource): bool {
        // Admin can access everything
        if ($this->hasRole($user, 'admin')) {
            return true;
        }
        
        // Check specific permission
        return $this->hasPermission($user, $resource);
    }
    
    /**
     * Get redirect path based on user role
     */
    public function getRedirectPath(array $user): string {
        if (!isset($user['role'])) {
            return '?page=login';
        }
        
        switch ($user['role']) {
            case 'admin':
                return '?page=admin';
            case 'affiliate':
                return '?page=affiliate';
            case 'user':
                return '?page=users';
            default:
                return '/';
        }
    }
    
    /**
     * Get dashboard path for role
     */
    public function getDashboardPath(string $role): string {
        switch ($role) {
            case 'admin':
                return '?page=admin';
            case 'affiliate':
                return '?page=affiliate';
            case 'user':
                return '?page=users';
            default:
                return '/';
        }
    }
    
    /**
     * Check if role is valid
     */
    public function isValidRole(string $role): bool {
        return in_array($role, ['admin', 'affiliate', 'user']);
    }
    
    /**
     * Get all available roles
     */
    public function getAllRoles(): array {
        return ['admin', 'affiliate', 'user'];
    }
    
    /**
     * Get role display name
     */
    public function getRoleDisplayName(string $role): string {
        $displayNames = [
            'admin' => 'Quản trị viên',
            'affiliate' => 'Đối tác',
            'user' => 'Người dùng',
        ];
        
        return $displayNames[$role] ?? $role;
    }
    
    /**
     * Check if user can manage other user
     */
    public function canManageUser(array $currentUser, array $targetUser): bool {
        // Admin can manage everyone
        if ($this->hasRole($currentUser, 'admin')) {
            return true;
        }
        
        // Users can only manage themselves
        return $currentUser['id'] === $targetUser['id'];
    }
    
    /**
     * Get accessible menu items for user role
     */
    public function getAccessibleMenuItems(array $user): array {
        $role = $user['role'] ?? 'user';
        
        $menuItems = [
            'admin' => [
                'dashboard' => '/admin/dashboard',
                'users' => '/admin/users',
                'products' => '/admin/products',
                'orders' => '/admin/orders',
                'categories' => '/admin/categories',
                'news' => '/admin/news',
                'events' => '/admin/events',
                'affiliates' => '/admin/affiliates',
                'settings' => '/admin/settings',
                'revenue' => '/admin/revenue',
                'contacts' => '/admin/contact',
            ],
            'affiliate' => [
                'dashboard' => '/affiliate/dashboard',
                'commissions' => '/affiliate/commissions',
                'customers' => '/affiliate/customers',
                'reports' => '/affiliate/reports',
                'marketing' => '/affiliate/marketing',
                'profile' => '/affiliate/profile',
            ],
            'user' => [
                'dashboard' => '/users/dashboard',
                'profile' => '/users/profile',
                'orders' => '/users/orders',
            ],
        ];
        
        return $menuItems[$role] ?? [];
    }
}