<?php

/**
 * ServiceInterface
 *
 * Interface chung cho tất cả service cung cấp data cho view.
 * Thiết kế bám sát spec trong `.kiro/specs/service-architecture-refactor/design.md`.
 */
interface ServiceInterface
{
    /**
     * Entry-point chuẩn để lấy data từ service theo tên method nghiệp vụ.
     *
     * Ví dụ:
     *   $publicService->getData('getHomeData', ['page' => 1]);
     *
     * @param string $method Tên method nghiệp vụ trong service
     * @param array  $params Tham số truyền vào method
     * @return array Kết quả dạng mảng để render view
     */
    public function getData(string $method, array $params = []): array;

    /**
     * Lấy (hoặc lazy-load) một model theo tên.
     *
     * Lưu ý: không dùng typehint BaseModel để tránh phụ thuộc cứng,
     * vì BaseModel có thể thay đổi tùy project.
     *
     * @param string $modelName Tên class model (ví dụ: 'ProductsModel')
     * @return mixed|null Instance model hoặc null nếu lỗi
     */
    public function getModel(string $modelName);

    /**
     * Xử lý lỗi ở tầng service.
     *
     * @param \Exception $e      Exception phát sinh
     * @param array      $context Thông tin context (method, params, v.v.)
     * @return array Cấu trúc empty data chuẩn để trả về cho view
     */
    public function handleError(\Exception $e, array $context = []): array;
}

