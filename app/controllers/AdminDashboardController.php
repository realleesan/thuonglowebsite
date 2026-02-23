<?php

/**
 * AdminDashboardController
 *
 * Cung cấp API endpoints cho admin dashboard:
 *  GET /api/admin/dashboard/revenue        - dữ liệu biểu đồ doanh thu
 *  GET /api/admin/dashboard/top-products   - top sản phẩm bán chạy
 *  GET /api/admin/dashboard/orders-status  - phân bố trạng thái đơn hàng
 *  GET /api/admin/dashboard/new-users      - người dùng mới theo tuần
 *  GET /api/admin/dashboard/statistics     - KPI tổng quan
 *  GET /api/admin/dashboard/all            - tất cả charts trong 1 request
 *  GET /api/admin/dashboard/notifications  - thông báo admin header
 *  POST /api/admin/dashboard/cache/flush   - xóa cache
 */

require_once __DIR__ . '/../services/AdminService.php';
require_once __DIR__ . '/../services/AuthService.php';

class AdminDashboardController
{
    private AdminService $adminService;
    private AuthService  $authService;

    public function __construct()
    {
        $this->adminService = new AdminService();
        $this->authService  = new AuthService();
    }

    // ========================= MIDDLEWARE =========================

    /**
     * Xác thực admin session trước khi xử lý API.
     * Trả về false và gửi 401 nếu không hợp lệ.
     */
    private function requireAdminAuth(): bool
    {
        if (!$this->authService->isLoggedIn()) {
            $this->jsonError('Unauthorized', 401);
            return false;
        }

        $user = $this->authService->getCurrentUser();
        if (empty($user) || !in_array($user['role'] ?? '', ['admin', 'superadmin'], true)) {
            $this->jsonError('Forbidden - chỉ admin mới được truy cập', 403);
            return false;
        }

        return true;
    }

    // ========================= PUBLIC ENDPOINTS =========================

    /**
     * GET /api/admin/dashboard/revenue
     * Params: period=7days|30days|12months, date_from, date_to
     */
    public function revenue(): void
    {
        if (!$this->requireAdminAuth()) return;

        $filters = $this->getQueryFilters();
        $data    = $this->adminService->getDashboardRevenueData($filters);

        $this->jsonSuccess($data);
    }

    /**
     * GET /api/admin/dashboard/top-products
     * Params: limit=5, period=30days
     */
    public function topProducts(): void
    {
        if (!$this->requireAdminAuth()) return;

        $limit  = (int)($_GET['limit'] ?? 5);
        $period = $_GET['period'] ?? '30days';
        $data   = $this->adminService->getDashboardTopProducts($limit, $period);

        $this->jsonSuccess($data);
    }

    /**
     * GET /api/admin/dashboard/orders-status
     */
    public function ordersStatus(): void
    {
        if (!$this->requireAdminAuth()) return;

        $data = $this->adminService->getDashboardOrdersStatus();
        $this->jsonSuccess($data);
    }

    /**
     * GET /api/admin/dashboard/new-users
     * Params: period=4weeks
     */
    public function newUsers(): void
    {
        if (!$this->requireAdminAuth()) return;

        $period = $_GET['period'] ?? '4weeks';
        $data   = $this->adminService->getDashboardNewUsers($period);

        $this->jsonSuccess($data);
    }

    /**
     * GET /api/admin/dashboard/statistics
     */
    public function statistics(): void
    {
        if (!$this->requireAdminAuth()) return;

        $data = $this->adminService->getDashboardStatistics();
        $this->jsonSuccess($data);
    }

    /**
     * GET /api/admin/dashboard/all
     * Tổng hợp tất cả dữ liệu charts trong 1 request để giảm round-trip.
     * Params: period=30days
     */
    public function allCharts(): void
    {
        if (!$this->requireAdminAuth()) return;

        $filters = $this->getQueryFilters();
        $data    = $this->adminService->getDashboardChartsData($filters);

        $this->jsonSuccess($data);
    }

    /**
     * GET /api/admin/dashboard/notifications
     * Params: limit=3
     */
    public function notifications(): void
    {
        if (!$this->requireAdminAuth()) return;

        $limit = (int)($_GET['limit'] ?? 3);
        $data  = $this->adminService->getAdminNotifications($limit);

        $this->jsonSuccess($data);
    }

    /**
     * GET /api/admin/dashboard/menus
     */
    public function menus(): void
    {
        if (!$this->requireAdminAuth()) return;

        $data = $this->adminService->getAdminMenus();
        $this->jsonSuccess($data);
    }

    /**
     * GET /api/admin/dashboard/search
     * Params: q=query, limit=5
     */
    public function search(): void
    {
        if (!$this->requireAdminAuth()) return;

        $query = $_GET['q'] ?? '';
        $limit = (int)($_GET['limit'] ?? 5);

        if (mb_strlen($query) < 2) {
            $this->jsonSuccess([]);
            return;
        }

        $data = $this->adminService->globalSearch($query, $limit);
        $this->jsonSuccess($data);
    }

    /**
     * POST /api/admin/dashboard/cache/flush
     * Flush cache theo pattern hoặc tất cả.
     * Body JSON: { "pattern": "dashboard:revenue" }
     */
    public function flushCache(): void
    {
        if (!$this->requireAdminAuth()) return;

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonError('Chỉ chấp nhận POST request', 405);
            return;
        }

        $body    = $this->getJsonBody();
        $pattern = $body['pattern'] ?? '';

        $cache   = new \CacheService();
        $flushed = $cache->flush($pattern);

        if ($flushed) {
            $this->jsonSuccess([
                'message' => 'Cache đã được xóa thành công',
                'pattern' => $pattern ?: 'all',
            ]);
        } else {
            $this->jsonError('Không thể xóa cache', 500);
        }
    }

    // ========================= HELPERS =========================

    private function getQueryFilters(): array
    {
        $allowed = ['period', 'date_from', 'date_to', 'product_id', 'user_id', 'affiliate_id'];
        $filters = [];
        foreach ($allowed as $key) {
            if (isset($_GET[$key]) && $_GET[$key] !== '') {
                $filters[$key] = htmlspecialchars(trim($_GET[$key]), ENT_QUOTES, 'UTF-8');
            }
        }
        return $filters;
    }

    private function getJsonBody(): array
    {
        $raw = file_get_contents('php://input');
        if (empty($raw)) return [];
        $decoded = json_decode($raw, true);
        return is_array($decoded) ? $decoded : [];
    }

    private function jsonSuccess(array $data, int $code = 200): void
    {
        $this->sendJson([
            'success'    => true,
            'data'       => $data,
            'timestamp'  => date('c'),
        ], $code);
    }

    private function jsonError(string $message, int $code = 400): void
    {
        $this->sendJson([
            'success' => false,
            'error'   => $message,
            'timestamp' => date('c'),
        ], $code);
    }

    private function sendJson(array $payload, int $statusCode): void
    {
        if (!headers_sent()) {
            http_response_code($statusCode);
            header('Content-Type: application/json; charset=utf-8');
            header('Cache-Control: no-cache, must-revalidate');
            header('X-Content-Type-Options: nosniff');
        }
        echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit();
    }
}
