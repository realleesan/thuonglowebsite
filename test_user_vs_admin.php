<?php
/**
 * Compare User vs Admin Data Directly
 */

require_once __DIR__ . '/core/view_init.php';
require_once __DIR__ . '/app/services/FilterConfigService.php';

echo "<!DOCTYPE html>
<html lang='vi'>
<head>
    <meta charset='UTF-8'>
    <title>🔍 User vs Admin Data Comparison</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background: #f5f5f5; }
        .container { max-width: 1200px; margin: 0 auto; }
        .section { background: white; margin: 20px 0; padding: 20px; border-radius: 8px; }
        .side-by-side { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .user-side, .admin-side { padding: 15px; }
        .user-side { background: #e8f5e8; border-left: 4px solid #4caf50; }
        .admin-side { background: #e3f2fd; border-left: 4px solid #2196f3; }
        .side-title { font-weight: bold; margin-bottom: 10px; font-size: 18px; }
        .brand-item { padding: 8px 12px; margin: 3px 0; background: white; border-radius: 4px; display: flex; justify-content: space-between; align-items: center; }
        .brand-name { font-weight: 500; }
        .brand-count { background: #2196f3; color: white; padding: 2px 8px; border-radius: 12px; font-size: 12px; }
        .console { background: #263238; color: #aed581; padding: 15px; border-radius: 8px; font-family: monospace; font-size: 12px; white-space: pre; }
        .stats { background: #f8f9fa; padding: 10px; border-radius: 4px; margin-bottom: 10px; }
    </style>
</head>
<body>
    <div class='container'>
        <h1>🔍 User vs Admin Data Direct Comparison</h1>";

// Get Admin Data
$filterConfigService = new FilterConfigService();
$adminBrands = $filterConfigService->getBrandsForFilter();

// Get User Data - EXACT same logic as products.php
$userBrandsData = [];
$publicService = $publicService ?? null;
if ($publicService && method_exists($publicService, 'getBrandsForFilter')) {
    $userBrandsData = $publicService->getBrandsForFilter();
}
$userBrands = $userBrandsData['brands'] ?? [];

echo "<div class='section'>
    <h2>🏷️ Brands Direct Comparison</h2>
    <div class='side-by-side'>
        <div class='user-side'>
            <div class='side-title'>👥 User Sidebar (Products Page)</div>
            <div class='stats'>Total: " . count($userBrands) . " brands</div>";
            
echo "<div class='console'>
=== USER BRANDS (PublicService) ===
Service exists: " . ($publicService ? 'YES' : 'NO') . "
Method exists: " . (method_exists($publicService, 'getBrandsForFilter') ? 'YES' : 'NO') . "
BrandsData count: " . count($userBrandsData) . "
Brands count: " . count($userBrands) . "

";

foreach ($userBrands as $brand) {
    echo "Brand: " . $brand['name'] . " | Count: " . ($brand['product_count'] ?? 0) . " | Sort: " . ($brand['sort_order'] ?? 0) . "\n";
}
echo "</div>";

foreach ($userBrands as $brand) {
    echo "<div class='brand-item'>
            <span class='brand-name'>" . htmlspecialchars($brand['name']) . "</span>
            <span class='brand-count'>" . ($brand['product_count'] ?? 0) . "</span>
          </div>";
}

echo "</div>";

echo "<div class='admin-side'>
    <div class='side-title'>⚙️ Admin Filter Config</div>
    <div class='stats'>Total: " . count($adminBrands) . " brands</div>";

echo "<div class='console'>
=== ADMIN BRANDS (FilterConfigService) ===
";

foreach ($adminBrands as $brand) {
    echo "Brand: " . $brand['name'] . " | Count: " . ($brand['count'] ?? 0) . " | Sort: " . ($brand['sort_order'] ?? 0) . "\n";
}
echo "</div>";

foreach ($adminBrands as $brand) {
    echo "<div class='brand-item'>
            <span class='brand-name'>" . htmlspecialchars($brand['name']) . "</span>
            <span class='brand-count'>" . ($brand['count'] ?? 0) . "</span>
          </div>";
}

echo "</div></div>";

echo "<div class='section'>
    <h2>🔍 Analysis</h2>
    <div class='console'>
=== COMPARISON ANALYSIS ===
User brands count: " . count($userBrands) . "
Admin brands count: " . count($adminBrands) . "

=== BRANDS MATCH CHECK ===
";

$userBrandNames = array_map(function($b) { return $b['name']; }, $userBrands);
$adminBrandNames = array_map(function($b) { return $b['name']; }, $adminBrands);

$missingInUser = array_diff($adminBrandNames, $userBrandNames);
$missingInAdmin = array_diff($userBrandNames, $adminBrandNames);

if (empty($missingInUser) && empty($missingInAdmin)) {
    echo "✅ BRANDS MATCH PERFECTLY!\n";
    echo "All " . count($userBrands) . " brands are present in both sides\n";
} else {
    echo "❌ BRANDS MISMATCH!\n";
    if (!empty($missingInUser)) {
        echo "Missing in User: " . implode(', ', $missingInUser) . "\n";
    }
    if (!empty($missingInAdmin)) {
        echo "Missing in Admin: " . implode(', ', $missingInAdmin) . "\n";
    }
}

echo "\n=== POSSIBLE ISSUES ===
1. Cache issue - User page might be cached
2. Different service logic in PublicService
3. Template-level filtering in products.php
4. Different database connection
5. Session/environment differences
</div>
</div>";

echo "</div></body></html>";
?>
