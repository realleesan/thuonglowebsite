<?php

require_once __DIR__ . '/ServiceInterface.php';
require_once __DIR__ . '/ErrorHandler.php';

/**
 * BaseService
 *
 * Cung cấp các chức năng chung cho tất cả service:
 * - Lazy loading & caching models
 * - Xử lý lỗi tập trung qua ErrorHandler
 * - Chuẩn hóa empty/fallback data structure
 */
abstract class BaseService implements ServiceInterface
{
    /**
     * Cache các model đã được khởi tạo, key là tên class (ví dụ: ProductsModel).
     *
     * @var array<string, mixed>
     */
    protected array $models = [];

    /**
     * Error handler dùng chung cho tất cả services.
     */
    protected ErrorHandler $errorHandler;

    /**
     * Loại service (public, user, admin, affiliate, ...).
     * Dùng cho logging & phân tích lỗi.
     */
    protected string $serviceType;

    public function __construct(?ErrorHandler $errorHandler = null, string $serviceType = 'generic')
    {
        $this->errorHandler = $errorHandler ?: new ErrorHandler();
        $this->serviceType = $serviceType;
    }

    /**
     * Entry-point chuẩn để gọi method trong service với error handling.
     *
     * @inheritDoc
     */
    public function getData(string $method, array $params = []): array
    {
        if (!method_exists($this, $method)) {
            $this->errorHandler->logWarning('Service method not found', [
                'service' => static::class,
                'service_type' => $this->serviceType,
                'method' => $method,
                'params' => array_keys($params),
            ]);

            return $this->getEmptyData();
        }

        try {
            /** @var array $result */
            $result = call_user_func_array([$this, $method], $params);

            // Đảm bảo luôn trả về array để view không bị crash.
            return is_array($result) ? $result : $this->getEmptyData();
        } catch (\Exception $e) {
            return $this->handleError($e, [
                'method' => $method,
                'params' => $params,
            ]);
        }
    }

    /**
     * Lazy-load & cache model theo tên class.
     *
     * @inheritDoc
     */
    public function getModel(string $modelName)
    {
        if (isset($this->models[$modelName])) {
            return $this->models[$modelName];
        }

        try {
            // Tự require file model nếu chưa có.
            if (!class_exists($modelName)) {
                $modelPath = __DIR__ . '/../models/' . $modelName . '.php';
                if (file_exists($modelPath)) {
                    require_once $modelPath;
                }
            }

            if (!class_exists($modelName)) {
                throw new \Exception("Model class {$modelName} not found");
            }

            $this->models[$modelName] = new $modelName();
            return $this->models[$modelName];
        } catch (\Exception $e) {
            // Log chi tiết qua ErrorHandler, nhưng không cho crash.
            $this->errorHandler->handleModelError($e, $modelName, '__construct');
            return null;
        }
    }

    /**
     * Helper chung để gọi method trên 1 model với error handling.
     *
     * @param string $modelName Tên class model (ví dụ: ProductsModel)
     * @param string $method    Tên method cần gọi
     * @param array  $params    Tham số truyền vào
     * @param mixed  $default   Giá trị trả về khi lỗi
     * @return mixed
     */
    protected function callModelMethod(string $modelName, string $method, array $params = [], $default = [])
    {
        $model = $this->getModel($modelName);
        if (!$model || !method_exists($model, $method)) {
            return $default;
        }

        try {
            return call_user_func_array([$model, $method], $params);
        } catch (\Exception $e) {
            $this->errorHandler->handleModelError($e, $modelName, $method, $params);
            return $default;
        }
    }

    /**
     * Xử lý lỗi chung cho service: log + trả về empty data chuẩn.
     *
     * @inheritDoc
     */
    public function handleError(\Exception $e, array $context = []): array
    {
        $this->errorHandler->logError(
            'Service error: ' . $e->getMessage(),
            array_merge($context, [
                'service' => static::class,
                'service_type' => $this->serviceType,
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ])
        );

        return $this->getEmptyData();
    }

    /**
     * Cấu trúc empty data chuẩn khi service lỗi hoặc không có dữ liệu.
     *
     * Phù hợp với spec:
     * [
     *   'success' => false,
     *   'data' => [],
     *   'message' => 'Service temporarily unavailable',
     *   'fallback' => true
     * ]
     */
    protected function getEmptyData(): array
    {
        return [
            'success' => false,
            'data' => [],
            'message' => 'Service temporarily unavailable',
            'fallback' => true,
        ];
    }
}

