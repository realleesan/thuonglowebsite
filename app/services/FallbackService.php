<?php

require_once __DIR__ . '/BaseService.php';

/**
 * FallbackService
 *
 * Được sử dụng khi service chính lỗi hoặc không khởi tạo được.
 * Mục tiêu: luôn trả về dữ liệu an toàn cho view, không để website bị crash.
 */
class FallbackService extends BaseService
{
    /**
     * Trả về empty data chuẩn bất kể method nào được gọi.
     *
     * Ví dụ:
     *   $serviceManager->getFallbackService('public')->getData('getHomeData');
     */
    public function getData(string $method, array $params = []): array
    {
        // Ghi log ở mức WARNING để vẫn theo dõi được tần suất fallback.
        $this->errorHandler->logWarning('Using FallbackService for method', [
            'service' => static::class,
            'service_type' => $this->serviceType,
            'original_method' => $method,
            'params' => array_keys($params),
        ]);

        return $this->getEmptyData();
    }

    /**
     * Fallback service không load bất kỳ model nào.
     *
     * @param string $modelName
     * @return null
     */
    public function getModel(string $modelName)
    {
        $this->errorHandler->logWarning('FallbackService::getModel should not be used', [
            'service_type' => $this->serviceType,
            'model' => $modelName,
        ]);

        return null;
    }
}

