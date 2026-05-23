<?php
/**
 * Diagnostic Tool: Product Saving and Database Integrity Test
 * Run this file in your browser: https://test1.web3b.com/test_save_product.php
 */

// Enable maximum error logging and display
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
ini_set('log_errors', 1);

header('Content-Type: text/html; charset=utf-8');

echo '<html><head><title>Product Save Diagnostics</title>';
echo '<style>
    body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; line-height: 1.5; color: #333; max-width: 1000px; margin: 0 auto; padding: 20px; background: #f9fafb; }
    h1 { color: #1e3a8a; border-bottom: 2px solid #e5e7eb; padding-bottom: 10px; }
    h2 { color: #1f2937; margin-top: 30px; border-left: 4px solid #3b82f6; padding-left: 10px; }
    .status { padding: 10px 15px; border-radius: 6px; font-weight: bold; margin-bottom: 15px; display: inline-block; }
    .success { background: #d1fae5; color: #065f46; border: 1px solid #a7f3d0; }
    .fail { background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; }
    .warning { background: #fef3c7; color: #92400e; border: 1px solid #fde68a; }
    pre { background: #1e293b; color: #f8fafc; padding: 15px; border-radius: 8px; overflow-x: auto; font-size: 14px; }
    .info-table { width: 100%; border-collapse: collapse; margin: 15px 0; }
    .info-table th, .info-table td { border: 1px solid #e5e7eb; padding: 12px; text-align: left; }
    .info-table th { background: #f3f4f6; color: #4b5563; font-weight: 600; }
    .card { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); margin-bottom: 20px; border: 1px solid #f3f4f6; }
</style></head><body>';

echo '<h1>Product Save & Database Diagnostics</h1>';

// Step 1: Environment & System Config
echo '<h2>1. System Environment Details</h2>';
echo '<div class="card">';
echo '<table class="info-table">';
echo '<tr><th>Parameter</th><th>Value</th></tr>';
echo '<tr><td>PHP Version</td><td>' . PHP_VERSION . '</td></tr>';
echo '<tr><td>Interface</td><td>' . PHP_SAPI . '</td></tr>';
echo '<tr><td>Current Directory</td><td>' . htmlspecialchars(__DIR__) . '</td></tr>';
echo '<tr><td>Memory Limit</td><td>' . ini_get('memory_limit') . '</td></tr>';
echo '<tr><td>Post Max Size</td><td>' . ini_get('post_max_size') . '</td></tr>';
echo '<tr><td>Upload Max Filesize</td><td>' . ini_get('upload_max_filesize') . '</td></tr>';
echo '</table>';
echo '</div>';

// Step 2: Bootstrap & Database Connection
echo '<h2>2. Loading Configurations & DB Bootstrap</h2>';
echo '<div class="card">';
try {
    define('THUONGLO_INIT', true);
    
    // Load config
    if (!file_exists(__DIR__ . '/config.php')) {
        throw new Exception("config.php file not found at " . __DIR__ . '/config.php');
    }
    $config = require __DIR__ . '/config.php';
    echo '<p><span class="status success">SUCCESS</span> config.php loaded successfully.</p>';
    
    // Connect Database
    require_once __DIR__ . '/core/database.php';
    $db = Database::getInstance();
    if ($db->testConnection()) {
        echo '<p><span class="status success">SUCCESS</span> Connected to database: <strong>' . htmlspecialchars($config['database']['name']) . '</strong></p>';
    } else {
        throw new Exception("Database test connection failed.");
    }
} catch (Exception $e) {
    echo '<p><span class="status fail">FATAL</span> Loading failed: ' . htmlspecialchars($e->getMessage()) . '</p>';
    echo '<pre>' . htmlspecialchars($e->getTraceAsString()) . '</pre>';
    echo '</body></html>';
    exit;
}
echo '</div>';

// Step 3: Inspect Database Tables Schema
echo '<h2>3. Inspecting Table Structures</h2>';
echo '<div class="card">';

function inspectTable($db, $tableName) {
    echo "<h3>Table: {$tableName}</h3>";
    try {
        $columns = $db->query("SHOW COLUMNS FROM `{$tableName}`");
        if (empty($columns)) {
            echo '<p><span class="status fail">FAIL</span> Table does not exist or has no columns.</p>';
            return false;
        }
        
        echo '<table class="info-table">';
        echo '<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>';
        foreach ($columns as $col) {
            echo '<tr>';
            echo '<td><strong>' . htmlspecialchars($col['Field']) . '</strong></td>';
            echo '<td>' . htmlspecialchars($col['Type']) . '</td>';
            echo '<td>' . htmlspecialchars($col['Null']) . '</td>';
            echo '<td>' . htmlspecialchars($col['Key']) . '</td>';
            echo '<td>' . htmlspecialchars($col['Default'] ?? 'NULL') . '</td>';
            echo '<td>' . htmlspecialchars($col['Extra']) . '</td>';
            echo '</tr>';
        }
        echo '</table>';
        return true;
    } catch (Exception $e) {
        echo '<p><span class="status fail">FAIL</span> Error reading columns: ' . htmlspecialchars($e->getMessage()) . '</p>';
        return false;
    }
}

$hasProductsTable = inspectTable($db, 'products');
$hasProductCategoriesTable = inspectTable($db, 'product_categories');

echo '</div>';

// Step 4: Validate ProductsModel and BaseModel Methods
echo '<h2>4. Loading and Inspecting Products Model</h2>';
echo '<div class="card">';
try {
    require_once __DIR__ . '/app/models/ProductsModel.php';
    $productsModel = new ProductsModel();
    echo '<p><span class="status success">SUCCESS</span> ProductsModel loaded and instantiated successfully.</p>';
    
    // Check if critical functions exist
    $criticalMethods = ['create', 'update', 'updateProductCategories', 'getProductCategories'];
    echo '<ul>';
    foreach ($criticalMethods as $method) {
        if (method_exists($productsModel, $method)) {
            echo "<li>Method <strong>{$method}()</strong> exists.</li>";
        } else {
            echo "<li><span style='color:red;'>Method <strong>{$method}()</strong> DOES NOT EXIST!</span></li>";
        }
    }
    echo '</ul>';
} catch (Exception $e) {
    echo '<p><span class="status fail">FAIL</span> Error loading model: ' . htmlspecialchars($e->getMessage()) . '</p>';
    echo '<pre>' . htmlspecialchars($e->getTraceAsString()) . '</pre>';
}
echo '</div>';

// Step 5: Test Simulated Product Insertion (Safe transaction rollback)
echo '<h2>5. Simulated Save & Insert (Rollback Safe)</h2>';
echo '<div class="card">';
if ($hasProductsTable) {
    try {
        echo '<p>Beginning safe database transaction...</p>';
        $pdo = $db->getPdo();
        $pdo->beginTransaction();
        
        // Define a fully populated dummy dataset similar to standard admin forms
        $testProductData = [
            'name'             => 'Test Diagnostic Data ' . time(),
            'slug'             => 'test-diagnostic-data-' . time(),
            'category_id'      => 1, // assumes category 1 exists or defaults
            'price'            => 150000,
            'sale_price'       => 120000,
            'stock'            => 150,
            'sku'              => 'TEST-SKU-' . time(),
            'status'           => 'active',
            'type'             => 'data_nguon_hang',
            'description'      => 'Chi tiết mô tả data test chẩn đoán.',
            'short_description'=> 'Mô tả ngắn data test chẩn đoán.',
            'expiry_days'      => 30,
            'record_count'     => 150,
            'data_size'        => '1.5 MB',
            'data_format'      => 'Excel',
            'data_source'      => 'Việt Nam',
            'reliability'      => '99%',
            'quota'            => 100,
            'quota_per_usage'  => 10,
            'digital'          => 1,
            'featured'         => 0,
            'downloadable'     => 0,
            'image'            => 'assets/images/products/product_test_placeholder.jpg'
        ];
        
        echo '<p>Attempting to call <code>$productsModel->create($testProductData)</code>...</p>';
        $newId = $productsModel->create($testProductData);
        
        if ($newId) {
            echo '<p><span class="status success">SUCCESS</span> Product created inside transaction with ID: <strong>' . $newId . '</strong></p>';
            
            // Check if categories mapping works
            if ($hasProductCategoriesTable) {
                echo '<p>Attempting to call <code>$productsModel->updateProductCategories($newId, [1, 2])</code>...</p>';
                $catSuccess = $productsModel->updateProductCategories($newId, [1, 2]);
                if ($catSuccess) {
                    echo '<p><span class="status success">SUCCESS</span> Categories mapping inserted successfully.</p>';
                } else {
                    echo '<p><span class="status fail">FAIL</span> Categories mapping insertion failed.</p>';
                }
            }
            
            // Test update
            echo '<p>Attempting to update the newly created product inside transaction...</p>';
            $updateData = [
                'name' => 'Updated Test Diagnostic Data ' . time(),
                'price' => 180000
            ];
            $updated = $productsModel->update($newId, $updateData);
            if ($updated) {
                echo '<p><span class="status success">SUCCESS</span> Product update succeeded.</p>';
                echo '<pre>Resulting product record: ' . htmlspecialchars(print_r($updated, true)) . '</pre>';
            } else {
                echo '<p><span class="status fail">FAIL</span> Product update failed.</p>';
            }
            
        } else {
            echo '<p><span class="status fail">FAIL</span> Product insert returned false/null.</p>';
        }
        
        // Rollback for safety so we do not pollute database
        echo '<p>Rolling back transaction safely...</p>';
        $pdo->rollBack();
        echo '<p><span class="status success">SUCCESS</span> Transaction rolled back. DB state is perfectly untouched.</p>';
        
    } catch (Exception $e) {
        // Rollback on exception
        if (isset($pdo) && $pdo->inTransaction()) {
            $pdo->rollBack();
            echo '<p><span class="status warning">WARNING</span> Transaction rolled back due to error.</p>';
        }
        echo '<p><span class="status fail">DATABASE TRANSACTION ERROR</span>: ' . htmlspecialchars($e->getMessage()) . '</p>';
        echo '<pre>' . htmlspecialchars($e->getTraceAsString()) . '</pre>';
    }
} else {
    echo '<p><span class="status fail">BLOCKED</span> Products table is missing, cannot test insertion.</p>';
}
echo '</div>';

echo '<h2>Summary & Recommendations</h2>';
echo '<div class="card">';
echo '<p><strong>If you saw any FATAL or FAIL status above, that is the exact reason for the White Screen of Death (WSOD).</strong> Please copy the contents of this page and send them to me so I can give you the perfect solution.</p>';
echo '</div>';

echo '</body></html>';
