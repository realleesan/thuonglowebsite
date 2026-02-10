<?php
/**
 * Script chuyển đổi tất cả affiliate views từ AffiliateDataLoader sang Models
 */

echo "=== CHUYỂN ĐỔI AFFILIATE VIEWS TỪ AFFILIATEDATALOADER SANG MODELS ===\n\n";

// Danh sách các file cần chuyển đổi
$filesToConvert = [
    // Finance files
    'app/views/affiliate/finance/withdraw.php' => [
        'old' => '// Load data
require_once __DIR__ . \'/../../../../core/AffiliateDataLoader.php\';
$dataLoader = new AffiliateDataLoader();
$financeData = $dataLoader->getData(\'finance\');

$wallet = $financeData[\'wallet\'];
$bankAccounts = $financeData[\'bank_accounts\'];
$withdrawalSettings = $financeData[\'withdrawal_settings\'];',
        'new' => '// Load Models
require_once __DIR__ . \'/../../../../models/AffiliateModel.php\';

$affiliateModel = new AffiliateModel();

// Get current affiliate ID from session
$affiliateId = $_SESSION[\'user_id\'] ?? 1;

// Get affiliate data from database
$affiliateInfo = $affiliateModel->getWithUser($affiliateId);
if (!$affiliateInfo) {
    $affiliateInfo = [\'pending_commission\' => 0, \'paid_commission\' => 0];
}

$wallet = [
    \'balance\' => $affiliateInfo[\'pending_commission\'],
    \'total_withdrawn\' => $affiliateInfo[\'paid_commission\']
];

$bankAccounts = []; // Demo - in real app get from database
$withdrawalSettings = [\'min_amount\' => 100000, \'fee_percentage\' => 2];'
    ],
    
    'app/views/affiliate/finance/webhook_demo.php' => [
        'old' => '// Load data
require_once __DIR__ . \'/../../../../core/AffiliateDataLoader.php\';
$dataLoader = new AffiliateDataLoader();
$financeData = $dataLoader->getData(\'finance\');

$wallet = $financeData[\'wallet\'];
$withdrawals = $financeData[\'withdrawals\'];',
        'new' => '// Load Models
require_once __DIR__ . \'/../../../../models/AffiliateModel.php\';

$affiliateModel = new AffiliateModel();

// Get current affiliate ID from session
$affiliateId = $_SESSION[\'user_id\'] ?? 1;

// Get affiliate data from database
$affiliateInfo = $affiliateModel->getWithUser($affiliateId);
if (!$affiliateInfo) {
    $affiliateInfo = [\'pending_commission\' => 0];
}

$wallet = [\'balance\' => $affiliateInfo[\'pending_commission\']];
$withdrawals = []; // Demo - in real app get from database'
    ],
    
    // Reports files
    'app/views/affiliate/reports/orders.php' => [
        'old' => '// Load data
require_once __DIR__ . \'/../../../../core/AffiliateDataLoader.php\';
$dataLoader = new AffiliateDataLoader();
$reportsData = $dataLoader->getData(\'reports\');

$ordersData = $reportsData[\'orders\'];
$totalOrders = $ordersData[\'total\'];
$totalRevenue = $ordersData[\'total_revenue\'];
$totalCommission = $ordersData[\'total_commission\'];
$avgOrderValue = $ordersData[\'average_order_value\'];
$ordersByDate = $ordersData[\'by_date\'];
$ordersByProduct = $ordersData[\'by_product\'];',
        'new' => '// Load Models
require_once __DIR__ . \'/../../../../models/AffiliateModel.php\';
require_once __DIR__ . \'/../../../../models/OrdersModel.php\';

$affiliateModel = new AffiliateModel();
$ordersModel = new OrdersModel();

// Get current affiliate ID from session
$affiliateId = $_SESSION[\'user_id\'] ?? 1;

// Get affiliate data from database
$affiliateInfo = $affiliateModel->getWithUser($affiliateId);
$dashboardData = $affiliateModel->getDashboardData($affiliateId);

$totalOrders = count($dashboardData[\'recent_orders\']);
$totalRevenue = $affiliateInfo[\'total_sales\'] ?? 0;
$totalCommission = $affiliateInfo[\'total_commission\'] ?? 0;
$avgOrderValue = $totalOrders > 0 ? $totalRevenue / $totalOrders : 0;
$ordersByDate = []; // Demo - generate from database
$ordersByProduct = []; // Demo - generate from database'
    ],
    
    'app/views/affiliate/reports/clicks.php' => [
        'old' => '// Load data
require_once __DIR__ . \'/../../../../core/AffiliateDataLoader.php\';
$dataLoader = new AffiliateDataLoader();
$reportsData = $dataLoader->getData(\'reports\');

$clicksData = $reportsData[\'clicks\'];
$totalClicks = $clicksData[\'total\'];
$uniqueClicks = $clicksData[\'unique\'];
$clicksByDate = $clicksData[\'by_date\'];
$clicksBySource = $clicksData[\'by_source\'];',
        'new' => '// Load Models
require_once __DIR__ . \'/../../../../models/AffiliateModel.php\';

$affiliateModel = new AffiliateModel();

// Get current affiliate ID from session
$affiliateId = $_SESSION[\'user_id\'] ?? 1;

// Demo clicks data (in real app, get from database)
$totalClicks = rand(1000, 5000);
$uniqueClicks = rand(500, 2000);
$clicksByDate = []; // Demo - generate from database
$clicksBySource = []; // Demo - generate from database'
    ],
    
    // Profile files
    'app/views/affiliate/profile/settings.php' => [
        'old' => '// Load data
require_once __DIR__ . \'/../../../../core/AffiliateDataLoader.php\';
$dataLoader = new AffiliateDataLoader();
$profileData = $dataLoader->getData(\'profile\');',
        'new' => '// Load Models
require_once __DIR__ . \'/../../../../models/AffiliateModel.php\';
require_once __DIR__ . \'/../../../../models/UsersModel.php\';

$affiliateModel = new AffiliateModel();
$usersModel = new UsersModel();

// Get current affiliate ID from session
$affiliateId = $_SESSION[\'user_id\'] ?? 1;

// Get profile data from database
$profileData = $affiliateModel->getWithUser($affiliateId);
if (!$profileData) {
    $profileData = [\'name\' => \'Demo User\', \'email\' => \'demo@example.com\'];
}'
    ],
    
    // Layout files
    'app/views/_layout/affiliate_header.php' => [
        'old' => '// Load affiliate info from session or demo data
require_once __DIR__ . \'/../../../core/AffiliateDataLoader.php\';
$dataLoader = new AffiliateDataLoader();

$affiliateInfo = $dataLoader->getData(\'affiliate_info\');',
        'new' => '// Load Models
require_once __DIR__ . \'/../../../models/AffiliateModel.php\';

$affiliateModel = new AffiliateModel();

// Get current affiliate ID from session
$affiliateId = $_SESSION[\'user_id\'] ?? 1;

// Get affiliate info from database
$affiliateInfo = $affiliateModel->getWithUser($affiliateId);
if (!$affiliateInfo) {
    $affiliateInfo = [\'name\' => \'Demo User\', \'email\' => \'demo@example.com\'];
}'
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