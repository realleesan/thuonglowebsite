<?php

require_once __DIR__ . '/BaseService.php';
require_once __DIR__ . '/DataTransformer.php';
require_once __DIR__ . '/ViewDataService.php';

/**
 * AdminService
 *
 * Service chuyên xử lý data cho khu vực admin:
 * - Dashboard
 * - Products, Categories, News, Orders, Users
 * - Affiliates, Events, Contacts, Settings, Revenue
 *
 * Phase 2: sử dụng lại logic ViewDataService nhưng bọc việc khởi tạo
 * trong try/catch để tránh WSoD khi models lỗi.
 */
class AdminService extends BaseService
{
    protected DataTransformer $transformer;

    public function __construct(?ErrorHandler $errorHandler = null, string $serviceType = 'admin')
    {
        parent::__construct($errorHandler, $serviceType);
        $this->transformer = new DataTransformer();
    }

    /**
     * Helper: tạo ViewDataService cũ trong try/catch để tránh WSoD.
     */
    private function getLegacyViewService(): ?ViewDataService
    {
        try {
            return new ViewDataService();
        } catch (\Exception $e) {
            $this->errorHandler->handleModelError($e, 'ViewDataService', '__construct');
            return null;
        }
    }

    public function getDashboardData(): array
    {
        try {
            $legacy = $this->getLegacyViewService();
            if (!$legacy) {
                return $this->getEmptyData();
            }
            $data = $legacy->getAdminDashboardData();
            return is_array($data) ? $data : $this->getEmptyData();
        } catch (\Exception $e) {
            return $this->handleError($e, ['method' => 'getDashboardData']);
        }
    }

    public function getProductsData(int $page = 1, int $perPage = 10, array $filters = []): array
    {
        try {
            $legacy = $this->getLegacyViewService();
            if (!$legacy) {
                return $this->getEmptyData();
            }
            $data = $legacy->getAdminProductsData($page, $perPage, $filters);
            return is_array($data) ? $data : $this->getEmptyData();
        } catch (\Exception $e) {
            return $this->handleError($e, ['method' => 'getProductsData']);
        }
    }

    public function getProductDetailsData(int $productId): array
    {
        try {
            $legacy = $this->getLegacyViewService();
            if (!$legacy) {
                return $this->getEmptyData();
            }
            $data = $legacy->getAdminProductDetailsData($productId);
            return is_array($data) ? $data : $this->getEmptyData();
        } catch (\Exception $e) {
            return $this->handleError($e, ['method' => 'getProductDetailsData', 'product_id' => $productId]);
        }
    }

    public function getUsersData(int $page = 1, int $perPage = 10, array $filters = []): array
    {
        try {
            $legacy = $this->getLegacyViewService();
            if (!$legacy) {
                return $this->getEmptyData();
            }
            $data = $legacy->getAdminUsersData($page, $perPage, $filters);
            return is_array($data) ? $data : $this->getEmptyData();
        } catch (\Exception $e) {
            return $this->handleError($e, ['method' => 'getUsersData']);
        }
    }

    public function getUserDetailsData(int $userId): array
    {
        try {
            $legacy = $this->getLegacyViewService();
            if (!$legacy) {
                return $this->getEmptyData();
            }
            $data = $legacy->getAdminUserDetailsData($userId);
            return is_array($data) ? $data : $this->getEmptyData();
        } catch (\Exception $e) {
            return $this->handleError($e, ['method' => 'getUserDetailsData', 'user_id' => $userId]);
        }
    }

    public function getUserAdditionalData(int $userId): array
    {
        try {
            $legacy = $this->getLegacyViewService();
            if (!$legacy) {
                return $this->getEmptyData();
            }
            $data = $legacy->getAdminUserAdditionalData($userId);
            return is_array($data) ? $data : $this->getEmptyData();
        } catch (\Exception $e) {
            return $this->handleError($e, ['method' => 'getUserAdditionalData', 'user_id' => $userId]);
        }
    }

    public function getCategoriesData(int $page = 1, int $perPage = 10, array $filters = []): array
    {
        try {
            $legacy = $this->getLegacyViewService();
            if (!$legacy) {
                return $this->getEmptyData();
            }
            $data = $legacy->getAdminCategoriesData($page, $perPage, $filters);
            return is_array($data) ? $data : $this->getEmptyData();
        } catch (\Exception $e) {
            return $this->handleError($e, ['method' => 'getCategoriesData']);
        }
    }

    public function getCategoryDetailsData(int $categoryId): array
    {
        try {
            $legacy = $this->getLegacyViewService();
            if (!$legacy) {
                return $this->getEmptyData();
            }
            $data = $legacy->getAdminCategoryDetailsData($categoryId);
            return is_array($data) ? $data : $this->getEmptyData();
        } catch (\Exception $e) {
            return $this->handleError($e, ['method' => 'getCategoryDetailsData', 'category_id' => $categoryId]);
        }
    }

    public function getNewsData(int $page = 1, int $perPage = 10, array $filters = []): array
    {
        try {
            $legacy = $this->getLegacyViewService();
            if (!$legacy) {
                return $this->getEmptyData();
            }
            $data = $legacy->getAdminNewsData($page, $perPage, $filters);
            return is_array($data) ? $data : $this->getEmptyData();
        } catch (\Exception $e) {
            return $this->handleError($e, ['method' => 'getNewsData']);
        }
    }

    public function getNewsDetailsData(int $newsId): array
    {
        try {
            $legacy = $this->getLegacyViewService();
            if (!$legacy) {
                return $this->getEmptyData();
            }
            $data = $legacy->getAdminNewsDetailsData($newsId);
            return is_array($data) ? $data : $this->getEmptyData();
        } catch (\Exception $e) {
            return $this->handleError($e, ['method' => 'getNewsDetailsData', 'news_id' => $newsId]);
        }
    }

    public function getOrdersData(int $page = 1, int $perPage = 10, array $filters = []): array
    {
        try {
            $legacy = $this->getLegacyViewService();
            if (!$legacy) {
                return $this->getEmptyData();
            }
            $data = $legacy->getAdminOrdersData($page, $perPage, $filters);
            return is_array($data) ? $data : $this->getEmptyData();
        } catch (\Exception $e) {
            return $this->handleError($e, ['method' => 'getOrdersData']);
        }
    }

    public function getOrderDetailsData(int $orderId): array
    {
        try {
            $legacy = $this->getLegacyViewService();
            if (!$legacy) {
                return $this->getEmptyData();
            }
            $data = $legacy->getAdminOrderDetailsData($orderId);
            return is_array($data) ? $data : $this->getEmptyData();
        } catch (\Exception $e) {
            return $this->handleError($e, ['method' => 'getOrderDetailsData', 'order_id' => $orderId]);
        }
    }

    public function getSettingsData(int $page = 1, int $perPage = 10, array $filters = []): array
    {
        try {
            $legacy = $this->getLegacyViewService();
            if (!$legacy) {
                return $this->getEmptyData();
            }
            $data = $legacy->getAdminSettingsData($page, $perPage, $filters);
            return is_array($data) ? $data : $this->getEmptyData();
        } catch (\Exception $e) {
            return $this->handleError($e, ['method' => 'getSettingsData']);
        }
    }

    public function getSettingDetailsData(string $settingKey): array
    {
        try {
            $legacy = $this->getLegacyViewService();
            if (!$legacy) {
                return $this->getEmptyData();
            }
            $data = $legacy->getAdminSettingDetailsData($settingKey);
            return is_array($data) ? $data : $this->getEmptyData();
        } catch (\Exception $e) {
            return $this->handleError($e, ['method' => 'getSettingDetailsData', 'key' => $settingKey]);
        }
    }

    public function getContactsData(int $page = 1, int $perPage = 10, array $filters = []): array
    {
        try {
            $legacy = $this->getLegacyViewService();
            if (!$legacy) {
                return $this->getEmptyData();
            }
            $data = $legacy->getAdminContactsData($page, $perPage, $filters);
            return is_array($data) ? $data : $this->getEmptyData();
        } catch (\Exception $e) {
            return $this->handleError($e, ['method' => 'getContactsData']);
        }
    }

    public function getContactDetailsData(int $contactId): array
    {
        try {
            $legacy = $this->getLegacyViewService();
            if (!$legacy) {
                return $this->getEmptyData();
            }
            $data = $legacy->getAdminContactDetailsData($contactId);
            return is_array($data) ? $data : $this->getEmptyData();
        } catch (\Exception $e) {
            return $this->handleError($e, ['method' => 'getContactDetailsData', 'contact_id' => $contactId]);
        }
    }

    public function getAffiliatesData(int $page = 1, int $perPage = 10, array $filters = []): array
    {
        try {
            $legacy = $this->getLegacyViewService();
            if (!$legacy) {
                return $this->getEmptyData();
            }
            $data = $legacy->getAdminAffiliatesData($page, $perPage, $filters);
            return is_array($data) ? $data : $this->getEmptyData();
        } catch (\Exception $e) {
            return $this->handleError($e, ['method' => 'getAffiliatesData']);
        }
    }

    public function getAffiliateDetailsData(int $affiliateId): array
    {
        try {
            $legacy = $this->getLegacyViewService();
            if (!$legacy) {
                return $this->getEmptyData();
            }
            $data = $legacy->getAdminAffiliateDetailsData($affiliateId);
            return is_array($data) ? $data : $this->getEmptyData();
        } catch (\Exception $e) {
            return $this->handleError($e, ['method' => 'getAffiliateDetailsData', 'affiliate_id' => $affiliateId]);
        }
    }

    public function getEventsData(int $page = 1, int $perPage = 10, array $filters = []): array
    {
        try {
            $legacy = $this->getLegacyViewService();
            if (!$legacy) {
                return $this->getEmptyData();
            }
            $data = $legacy->getAdminEventsData($page, $perPage, $filters);
            return is_array($data) ? $data : $this->getEmptyData();
        } catch (\Exception $e) {
            return $this->handleError($e, ['method' => 'getEventsData']);
        }
    }

    public function getEventDetailsData(int $eventId): array
    {
        try {
            $legacy = $this->getLegacyViewService();
            if (!$legacy) {
                return $this->getEmptyData();
            }
            $data = $legacy->getAdminEventDetailsData($eventId);
            return is_array($data) ? $data : $this->getEmptyData();
        } catch (\Exception $e) {
            return $this->handleError($e, ['method' => 'getEventDetailsData', 'event_id' => $eventId]);
        }
    }

    public function getRevenueData(array $filters = []): array
    {
        try {
            $legacy = $this->getLegacyViewService();
            if (!$legacy) {
                return $this->getEmptyData();
            }
            $data = $legacy->getAdminRevenueData($filters);
            return is_array($data) ? $data : $this->getEmptyData();
        } catch (\Exception $e) {
            return $this->handleError($e, ['method' => 'getRevenueData']);
        }
    }
}

