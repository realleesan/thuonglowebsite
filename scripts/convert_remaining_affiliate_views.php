<?php
/**
 * Script chuyển đổi các affiliate views còn lại
 */

echo "=== CHUYỂN ĐỔI CÁC AFFILIATE VIEWS CÒN LẠI ===\n\n";

// Danh sách các file cần chuyển đổi
$filesToConvert = [
    // Commission files
    'app/views/affiliate/commissions/index.php' => [
        'old' => '// Load data từ AffiliateDataLoader
require_once __DIR__ . \'/../../../../core/AffiliateDataLoader.php\';
require_once __DIR__ . \'/../../../../core/AffiliateErrorHandler.php\';

try {
    $loader = new AffiliateDataLoader();
    $commissionsData = $loader->getCommissionsData();
    $overview = $commissionsData[\'overview\'] ?? [];',
        'new' => '// Load Models
require_once __DIR__ . \'/../../../../models/AffiliateModel.php\';

$affiliateModel = new AffiliateModel();

try {
    // Get current affiliate ID from session
    $affiliateId = $_SESSION[\'user_id\'] ?? 1;
    
    // Get affiliate data from database
    $affiliateInfo = $affiliateModel->getWithUser($affiliateId);
    if (!$affiliateInfo) {
        $affiliateInfo = [\'total_commission\' => 0, \'pending_commission\' => 0, \'paid_commission\' => 0];
    }
    
    $overview = [
        \'total_commission\' => $affiliateInfo[\'total_commission\'],
        \'pending_commission\' => $affiliateInfo[\'pending_commission\'],
        \'paid_commission\' => $affiliateInfo[\'paid_commission\']
    ];'
    ],
    
    'app/views/affiliate/commissions/history.php' => [
        'old' => '// Load data từ AffiliateDataLoader
require_once __DIR__ . \'/../../../../core/AffiliateDataLoader.php\';
require_once __DIR__ . \'/../../../../core/AffiliateErrorHandler.php\';

try {
    $loader = new AffiliateDataLoader();
    $commissionsData = $loader->getCommissionsData();
    $history = $commissionsData[\'history\'] ?? [];',
        'new' => '// Load Models
require_once __DIR__ . \'/../../../../models/AffiliateModel.php\';
require_once __DIR__ . \'/../../../../models/OrdersModel.php\';

$affiliateModel = new AffiliateModel();
$ordersModel = new OrdersModel();

try {
    // Get current affiliate ID from session
    $affiliateId = $_SESSION[\'user_id\'] ?? 1;
    
    // Get commission history from database
    $dashboardData = $affiliateModel->getDashboardData($affiliateId);
    $history = $dashboardData[\'recent_orders\'] ?? [];'
    ],
    
    'app/views/affiliate/commissions/policy.php' => [
        'old' => '// Load data từ AffiliateDataLoader
require_once __DIR__ . \'/../../../../core/AffiliateDataLoader.php\';
require_once __DIR__ . \'/../../../../core/AffiliateErrorHandler.php\';

try {
    $loader = new AffiliateDataLoader();
    $commissionsData = $loader->getCommissionsData();
    $policy = $commissionsData[\'policy\'] ?? [];',
        'new' => '// Load Models
require_once __DIR__ . \'/../../../../models/SettingsModel.php\';

$settingsModel = new SettingsModel();

try {
    // Get commission policy from settings
    $policy = [
        \'commission_rate\' => $settingsModel->get(\'commission_rate\', 10),
        \'min_withdrawal\' => $settingsModel->get(\'min_withdrawal\', 100000),
        \'payment_schedule\' => \'monthly\'
    ];'
    ],
    
    // Customer files
    'app/views/affiliate/customers/list.php' => [
        'old' => '// Load data từ AffiliateDataLoader
require_once __DIR__ . \'/../../../../core/AffiliateDataLoader.php\';
require_once __DIR__ . \'/../../../../core/AffiliateErrorHandler.php\';

try {
    $loader = new AffiliateDataLoader();
    $customers = $loader->getData(\'customers\') ?? [];',
        'new' => '// Load Models
require_once __DIR__ . \'/../../../../models/AffiliateModel.php\';
require_once __DIR__ . \'/../../../../models/UsersModel.php\';

$affiliateModel = new AffiliateModel();
$usersModel = new UsersModel();

try {
    // Get current affiliate ID from session
    $affiliateId = $_SESSION[\'user_id\'] ?? 1;
    
    // Get customers from database (demo - in real app get referred customers)
    $customers = $usersModel->getAll(); // Demo data'
    ],
    
    'app/views/affiliate/customers/detail.php' => [
        'old' => '// Load data từ AffiliateDataLoader
require_once __DIR__ . \'/../../../../core/AffiliateDataLoader.php\';
require_once __DIR__ . \'/../../../../core/AffiliateErrorHandler.php\';

try {
    $loader = new AffiliateDataLoader();
    $customers = $loader->getData(\'customers\') ?? [];',
        'new' => '// Load Models
require_once __DIR__ . \'/../../../../models/AffiliateModel.php\';
require_once __DIR__ . \'/../../../../models/UsersModel.php\';
require_once __DIR__ . \'/../../../../models/OrdersModel.php\';

$affiliateModel = new AffiliateModel();
$usersModel = new UsersModel();
$ordersModel = new OrdersModel();

try {
    // Get customer ID from URL
    $customerId = (int)($_GET[\'id\'] ?? 0);
    
    // Get customer data from database
    $customer = $usersModel->getById($customerId);
    $customers = [$customer]; // For compatibility'
    ]
];

// Chuyển đổi từng file
$convertedCount = 0;
$errorCount = 0;

foreach ($filesToConvert as $file => $replacement) {
    echo "Đang chuyển đổi: $file\n";
    
    if (!file_exists($file)) {
        echo "  ❌ File không tồn tại\n";
        $errorCount++;
        continue;
    }
    
    $content = file_get_contents($file);
    
    if (strpos($content, $replacement['old']) === false) {
        echo "  ⚠️  Không tìm thấy pattern cần thay thế\n";
        $errorCount++;
        continue;
    }
    
    $newContent = str_replace($replacement['old'], $replacement['new'], $content);
    
    if (file_put_contents($file, $newContent)) {
        echo "  ✅ Chuyển đổi thành công\n";
        $convertedCount++;
    } else {
        echo "  ❌ Lỗi ghi file\n";
        $errorCount++;
    }
}

echo "\n=== KẾT QUẢ ===\n";
echo "✅ Đã chuyển đổi: $convertedCount files\n";
echo "❌ Lỗi: $errorCount files\n";
echo "📊 Tổng: " . count($filesToConvert) . " files\n";

echo "\n=== HOÀN THÀNH ===\n";