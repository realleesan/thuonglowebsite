<?php
/**
 * Test Brands Filter Conditions
 */

require_once __DIR__ . '/core/view_init.php';
require_once __DIR__ . '/app/models/BrandsModel.php';

echo "<!DOCTYPE html>
<html lang='vi'>
<head>
    <meta charset='UTF-8'>
    <title>🏷️ Brands Filter Test</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background: #f5f5f5; }
        .container { max-width: 1000px; margin: 0 auto; }
        .section { background: white; margin: 20px 0; padding: 20px; border-radius: 8px; }
        .brand-item { padding: 10px; margin: 5px 0; border-radius: 4px; display: flex; justify-content: space-between; align-items: center; }
        .active { background: #d4edda; border-left: 4px solid #28a745; }
        .inactive { background: #f8d7da; border-left: 4px solid #dc3545; }
        .hidden { background: #fff3cd; border-left: 4px solid #ffc107; }
        .visible { background: #d1ecf1; border-left: 4px solid #17a2b8; }
        .brand-name { font-weight: 500; }
        .brand-status { padding: 4px 8px; border-radius: 12px; font-size: 12px; color: white; }
        .status-active { background: #28a745; }
        .status-inactive { background: #dc3545; }
        .filter-yes { background: #17a2b8; }
        .filter-no { background: #ffc107; color: #333; }
        .count { background: #6c757d; color: white; padding: 2px 8px; border-radius: 12px; font-size: 12px; }
        .stats { background: #f8f9fa; padding: 15px; border-radius: 4px; margin-bottom: 15px; }
        .console { background: #263238; color: #aed581; padding: 15px; border-radius: 8px; font-family: monospace; font-size: 12px; }
    </style>
</head>
<body>
    <div class='container'>
        <h1>🏷️ Brands Filter Analysis</h1>
        <p>Kiểm tra điều kiện hiển thị brands ở trang user</p>";

$brandsModel = new BrandsModel();

echo "<div class='section'>
    <h2>📊 All Brands in Database</h2>
    <div class='stats'>";

// Get ALL brands without any filter
$sql = "SELECT b.*, COUNT(p.id) as product_count
        FROM brands b
        LEFT JOIN products p ON b.id = p.brand_id AND p.status = 'active'
        GROUP BY b.id
        ORDER BY b.sort_order ASC, b.name ASC";

$allBrands = $brandsModel->query($sql) ?? [];

echo "Total brands in database: " . count($allBrands) . "</div>";

echo "<div class='console'>";
echo "=== BRANDS ANALYSIS ===\n\n";

foreach ($allBrands as $brand) {
    $status = $brand['status'] ?? 'inactive';
    $showInFilter = $brand['show_in_filter'] ?? 0;
    $productCount = $brand['product_count'] ?? 0;
    
    echo sprintf(
        "Brand: %-25s | Status: %-8s | Show Filter: %-3s | Products: %d\n",
        $brand['name'],
        $status,
        $showInFilter ? 'YES' : 'NO',
        $productCount
    );
}
echo "</div>";

echo "<h3>📋 Breakdown by Status:</h3>";

$activeVisible = [];
$activeHidden = [];
$inactiveVisible = [];
$inactiveHidden = [];

foreach ($allBrands as $brand) {
    $status = $brand['status'] ?? 'inactive';
    $showInFilter = $brand['show_in_filter'] ?? 0;
    $productCount = $brand['product_count'] ?? 0;
    
    $brandData = [
        'name' => $brand['name'],
        'product_count' => $productCount,
        'sort_order' => $brand['sort_order'] ?? 0
    ];
    
    if ($status === 'active' && $showInFilter) {
        $activeVisible[] = $brandData;
    } elseif ($status === 'active' && !$showInFilter) {
        $activeHidden[] = $brandData;
    } elseif ($status !== 'active' && $showInFilter) {
        $inactiveVisible[] = $brandData;
    } else {
        $inactiveHidden[] = $brandData;
    }
}

echo "<div style='display: grid; grid-template-columns: 1fr 1fr; gap: 20px;'>";

echo "<div>
    <h4 style='color: #28a745;'>✅ Active + Visible (User Sidebar)</h4>
    <div class='stats'>Count: " . count($activeVisible) . "</div>";
foreach ($activeVisible as $brand) {
    echo "<div class='brand-item active visible'>
            <span class='brand-name'>" . htmlspecialchars($brand['name']) . "</span>
            <div>
                <span class='count'>" . $brand['product_count'] . "</span>
                <span class='brand-status status-active'>Active</span>
                <span class='brand-status filter-yes'>Show</span>
            </div>
          </div>";
}
echo "</div>";

echo "<div>
    <h4 style='color: #ffc107;'>⚠️ Active + Hidden</h4>
    <div class='stats'>Count: " . count($activeHidden) . "</div>";
foreach ($activeHidden as $brand) {
    echo "<div class='brand-item active hidden'>
            <span class='brand-name'>" . htmlspecialchars($brand['name']) . "</span>
            <div>
                <span class='count'>" . $brand['product_count'] . "</span>
                <span class='brand-status status-active'>Active</span>
                <span class='brand-status filter-no'>Hidden</span>
            </div>
          </div>";
}
echo "</div>";

echo "<div>
    <h4 style='color: #17a2b8;'>🔍 Inactive + Visible</h4>
    <div class='stats'>Count: " . count($inactiveVisible) . "</div>";
foreach ($inactiveVisible as $brand) {
    echo "<div class='brand-item inactive visible'>
            <span class='brand-name'>" . htmlspecialchars($brand['name']) . "</span>
            <div>
                <span class='count'>" . $brand['product_count'] . "</span>
                <span class='brand-status status-inactive'>Inactive</span>
                <span class='brand-status filter-yes'>Show</span>
            </div>
          </div>";
}
echo "</div>";

echo "<div>
    <h4 style='color: #dc3545;'>❌ Inactive + Hidden</h4>
    <div class='stats'>Count: " . count($inactiveHidden) . "</div>";
foreach ($inactiveHidden as $brand) {
    echo "<div class='brand-item inactive hidden'>
            <span class='brand-name'>" . htmlspecialchars($brand['name']) . "</span>
            <div>
                <span class='count'>" . $brand['product_count'] . "</span>
                <span class='brand-status status-inactive'>Inactive</span>
                <span class='brand-status filter-no'>Hidden</span>
            </div>
          </div>";
}
echo "</div>";

echo "</div>";

echo "</div>";

echo "<div class='section'>
    <h2>🔧 Admin Filter Config vs User Sidebar</h2>
    <div class='console'>
echo "=== COMPARISON ===\n";
echo "Admin shows ALL brands: " . count($allBrands) . "\n";
echo "User sidebar shows Active + Visible: " . count($activeVisible) . "\n";
echo "Hidden brands (Active but not in filter): " . count($activeHidden) . "\n";
echo "Inactive brands: " . (count($inactiveVisible) + count($inactiveHidden)) . "\n\n";

echo "RECOMMENDATION:\n";
echo "- Admin should show ALL brands for management\n";
echo "- User sidebar correctly shows only Active + Visible brands\n";
echo "- Use admin panel to toggle show_in_filter for brands\n";
echo "</div>
</div>";

echo "</div></body></html>";
?>
