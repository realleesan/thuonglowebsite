<?php

require_once __DIR__ . '/ServiceInterface.php';
require_once __DIR__ . '/BaseService.php';
require_once __DIR__ . '/FallbackService.php';
require_once __DIR__ . '/ErrorHandler.php';

/**
 * ServiceManager
 *
 * Quản lý lifecycle của tất cả service:
 * - Lazy loading
 * - Caching instance theo type + name
 * - Centralized error handling & fallback
 *
 * Spec chính:
 *  - getService(string $type, string $name = 'default'): ServiceInterface
 *  - getFallbackService(string $type): ServiceInterface
 */
class ServiceManager
{
    /**
     * Cache các service đã khởi tạo.
     *
     * Key dạng: "{$type}_{$name}"
     *
     * @var array<string, ServiceInterface>
     */
    private array $services = [];

    /**
     * Error handler dùng chung cho tất cả services.
     */
    private ErrorHandler $errorHandler;

    public function __construct(?ErrorHandler $errorHandler = null)
    {
        $this->errorHandler = $errorHandler ?: new ErrorHandler();
    }

    /**
     * Lấy service theo type + name với lazy loading và caching.
     *
     * @param string $type Loại service: public, user, admin, affiliate, ...
     * @param string $name Tên cấu hình / biến thể (mặc định: default)
     * @return ServiceInterface
     */
    public function getService(string $type, string $name = 'default'): ServiceInterface
    {
        $key = $this->buildKey($type, $name);

        if (isset($this->services[$key])) {
            return $this->services[$key];
        }

        try {
            $this->services[$key] = $this->createService($type, $name);
        } catch (\Exception $e) {
            return $this->handleServiceError($e, $type);
        }

        return $this->services[$key];
    }

    /**
     * Lấy fallback service cho 1 type cụ thể.
     *
     * @param string $type
     * @return ServiceInterface
     */
    public function getFallbackService(string $type): ServiceInterface
    {
        $key = $this->buildKey('fallback', $type);

        if (!isset($this->services[$key])) {
            $this->services[$key] = new FallbackService($this->errorHandler, $type);
        }

        return $this->services[$key];
    }

    /**
     * Tạo service thật dựa trên type.
     *
     * Lưu ý: Ở Phase 1 chúng ta mới chỉ tạo hạ tầng, nên có thể chưa có
     * PublicService/UserService/AdminService/AffiliateService.
     * Vì vậy, hàm này được thiết kế an toàn:
     * - Chỉ require file/class nếu tồn tại
     * - Ném exception nếu type không hợp lệ → sẽ được chuyển sang fallback
     *
     * @param string $type
     * @param string $name
     * @return ServiceInterface
     * @throws \Exception
     */
    private function createService(string $type, string $name): ServiceInterface
    {
        $normalizedType = strtolower($type);

        switch ($normalizedType) {
            case 'public':
                $class = 'PublicService';
                $file = __DIR__ . '/PublicService.php';
                break;
            case 'user':
                $class = 'UserService';
                $file = __DIR__ . '/UserService.php';
                break;
            case 'admin':
                $class = 'AdminService';
                $file = __DIR__ . '/AdminService.php';
                break;
            case 'affiliate':
                $class = 'AffiliateService';
                $file = __DIR__ . '/AffiliateService.php';
                break;
            default:
                throw new \Exception("Unknown service type: {$type}");
        }

        if (!class_exists($class)) {
            if (file_exists($file)) {
                require_once $file;
            }
        }

        if (!class_exists($class)) {
            throw new \Exception("Service class {$class} not found for type {$type}");
        }

        // Các service mới sẽ extends BaseService và nhận ErrorHandler + serviceType.
        /** @var ServiceInterface $service */
        $service = new $class($this->errorHandler, $normalizedType);
        return $service;
    }

    /**
     * Xử lý lỗi khi tạo service và fallback sang FallbackService.
     *
     * @param \Exception $e
     * @param string     $type
     * @return ServiceInterface
     */
    private function handleServiceError(\Exception $e, string $type): ServiceInterface
    {
        $this->errorHandler->logError('ServiceManager::createService error: ' . $e->getMessage(), [
            'service_type' => $type,
            'file' => $e->getFile(),
            'line' => $e->getLine(),
        ]);

        return $this->getFallbackService($type);
    }

    /**
     * Build key cache cho service.
     */
    private function buildKey(string $type, string $name): string
    {
        return strtolower($type) . '_' . strtolower($name);
    }
}

