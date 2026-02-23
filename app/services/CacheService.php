<?php

/**
 * CacheService
 *
 * File-based caching với TTL support cho admin dashboard data.
 * Lưu cache vào thư mục storage/cache dưới dạng JSON files.
 *
 * TTL mặc định:
 *  - revenue data      : 300s  (5 phút)
 *  - top products      : 600s  (10 phút)
 *  - orders status     : 300s  (5 phút)
 *  - new users         : 900s  (15 phút)
 *  - dashboard stats   : 300s  (5 phút)
 *  - notifications     : 300s  (5 phút)
 */
class CacheService
{
    private string $cacheDir;
    private array $ttlConfig;

    /** @var array<string, array> Bộ nhớ đệm trong một request để tránh đọc file 2 lần */
    private array $memoryCache = [];

    public function __construct(?string $cacheDir = null)
    {
        $this->cacheDir = $cacheDir ?? __DIR__ . '/../../storage/cache/admin';
        $this->ttlConfig = [
            'dashboard:revenue'      => 300,
            'dashboard:top_products' => 600,
            'dashboard:orders_status'=> 300,
            'dashboard:new_users'    => 900,
            'dashboard:stats'        => 300,
            'dashboard:notifications'=> 300,
        ];
        $this->ensureCacheDir();
    }

    // ========================= PUBLIC API =========================

    /**
     * Lấy dữ liệu từ cache.
     * Trả về null nếu cache miss hoặc đã hết hạn.
     */
    public function get(string $key): ?array
    {
        // Kiểm tra memory cache trước
        if (isset($this->memoryCache[$key])) {
            return $this->memoryCache[$key];
        }

        $file = $this->getFilePath($key);
        if (!file_exists($file)) {
            return null;
        }

        try {
            $raw = file_get_contents($file);
            if ($raw === false) {
                return null;
            }

            $cached = json_decode($raw, true);
            if (!is_array($cached) || !isset($cached['expires_at'], $cached['data'])) {
                return null;
            }

            // Kiểm tra TTL
            if (time() > $cached['expires_at']) {
                @unlink($file);
                return null;
            }

            $this->memoryCache[$key] = $cached['data'];
            return $cached['data'];
        } catch (\Throwable $e) {
            error_log('[CacheService] get error for key ' . $key . ': ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Lưu dữ liệu vào cache.
     * TTL = 0 có nghĩa là dùng TTL mặc định từ $ttlConfig.
     */
    public function set(string $key, array $data, int $ttl = 0): bool
    {
        if ($ttl <= 0) {
            $ttl = $this->getTtlForKey($key);
        }

        $cached = [
            'key'        => $key,
            'data'       => $data,
            'created_at' => time(),
            'expires_at' => time() + $ttl,
            'ttl'        => $ttl,
        ];

        try {
            $file   = $this->getFilePath($key);
            $result = file_put_contents($file, json_encode($cached, JSON_UNESCAPED_UNICODE), LOCK_EX);
            if ($result !== false) {
                $this->memoryCache[$key] = $data;
                return true;
            }
            return false;
        } catch (\Throwable $e) {
            error_log('[CacheService] set error for key ' . $key . ': ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Xóa một cache entry.
     */
    public function delete(string $key): bool
    {
        unset($this->memoryCache[$key]);
        $file = $this->getFilePath($key);
        if (file_exists($file)) {
            return @unlink($file);
        }
        return true;
    }

    /**
     * Xóa toàn bộ cache hoặc các entry theo pattern glob.
     * Pattern rỗng => xóa tất cả.
     */
    public function flush(string $pattern = ''): bool
    {
        $this->memoryCache = [];
        try {
            if (empty($pattern)) {
                $files = glob($this->cacheDir . '/*.cache');
            } else {
                $safePattern = str_replace([':', '/'], '_', $pattern);
                $files = glob($this->cacheDir . '/' . $safePattern . '*.cache');
            }

            if ($files === false) {
                return false;
            }

            foreach ($files as $file) {
                @unlink($file);
            }
            return true;
        } catch (\Throwable $e) {
            error_log('[CacheService] flush error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Tạo cache key chuẩn hóa.
     *
     * @param string $type   Ví dụ: 'dashboard:revenue'
     * @param array  $params Ví dụ: ['period' => '30days', 'date_from' => '2024-01-01']
     */
    public function generateKey(string $type, array $params = []): string
    {
        if (empty($params)) {
            return $type;
        }
        ksort($params);
        $suffix = md5(serialize($params));
        return $type . ':' . $suffix;
    }

    /**
     * Lấy hoặc sinh dữ liệu nếu cache miss.
     *
     * @param string   $key
     * @param callable $callback  Hàm sinh dữ liệu khi cache miss
     * @param int      $ttl       Giây; 0 = dùng TTL từ config
     */
    public function remember(string $key, callable $callback, int $ttl = 0): array
    {
        $cached = $this->get($key);
        if ($cached !== null) {
            return $cached;
        }

        try {
            $data = $callback();
            if (is_array($data)) {
                $this->set($key, $data, $ttl);
            }
            return is_array($data) ? $data : [];
        } catch (\Throwable $e) {
            error_log('[CacheService] remember callback error for key ' . $key . ': ' . $e->getMessage());
            return [];
        }
    }

    // ========================= PRIVATE HELPERS =========================

    private function ensureCacheDir(): void
    {
        if (!is_dir($this->cacheDir)) {
            @mkdir($this->cacheDir, 0755, true);
        }
    }

    private function getFilePath(string $key): string
    {
        $safeKey = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $key);
        return $this->cacheDir . '/' . $safeKey . '.cache';
    }

    private function getTtlForKey(string $key): int
    {
        foreach ($this->ttlConfig as $prefix => $ttl) {
            if (strpos($key, $prefix) === 0) {
                return $ttl;
            }
        }
        return 300; // default 5 minutes
    }
}
