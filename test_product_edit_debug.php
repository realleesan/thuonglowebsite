<?php
/**
 * Test file to debug product edit form submission
 * This file helps identify issues with:
 * 1. Form submission
 * 2. Database update
 * 3. Redirect flow
 */

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include database class
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/core/database.php';

echo "<!DOCTYPE html>";
echo "<html><head><title>Debug Product Edit</title>";
echo "<style>
    body { font-family: monospace; padding: 20px; background: #f5f5f5; }
    .debug-section { background: white; padding: 15px; margin: 10px 0; border: 1px solid #ddd; }
    .debug-section h3 { margin-top: 0; color: #333; }
    .success { color: green; }
    .error { color: red; }
    .info { color: blue; }
    pre { background: #f0f0f0; padding: 10px; overflow: auto; }
    table { border-collapse: collapse; width: 100%; }
    th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
    th { background: #f0f0f0; }
</style>";
echo "</head><body>";
echo "<h1>Debug: Product Edit Form Submission</h1>";

// ============================================
// SECTION 1: Request Information
// ============================================
echo "<div class='debug-section'>";
echo "<h3>1. Request Information</h3>";
echo "<table>";
echo "<tr><th>Property</th><th>Value</th></tr>";
echo "<tr><td>REQUEST_METHOD</td><td>" . ($_SERVER['REQUEST_METHOD'] ?? 'NOT SET') . "</td></tr>";
echo "<tr><td>HTTP_REFERER</td><td>" . ($_SERVER['HTTP_REFERER'] ?? 'NOT SET') . "</td></tr>";
echo "<tr><td>QUERY_STRING</td><td>" . ($_SERVER['QUERY_STRING'] ?? 'NOT SET') . "</td></tr>";
echo "</table>";
echo "</div>";

// ============================================
// SECTION 2: GET Parameters
// ============================================
echo "<div class='debug-section'>";
echo "<h3>2. GET Parameters</h3>";
if (!empty($_GET)) {
    echo "<pre>" . print_r($_GET, true) . "</pre>";
} else {
    echo "<p class='info'>No GET parameters</p>";
}
echo "</div>";

// ============================================
// SECTION 3: POST Parameters
// ============================================
echo "<div class='debug-section'>";
echo "<h3>3. POST Parameters</h3>";
if (!empty($_POST)) {
    // Mask sensitive data
    $postData = $_POST;
    echo "<pre>" . print_r($postData, true) . "</pre>";
} else {
    echo "<p class='info'>No POST parameters (GET request)</p>";
}
echo "</div>";

// ============================================
// SECTION 4: Session Variables
// ============================================
echo "<div class='debug-section'>";
echo "<h3>4. Session Variables</h3>";
if (!empty($_SESSION)) {
    echo "<pre>" . print_r($_SESSION, true) . "</pre>";
} else {
    echo "<p class='info'>No session variables</p>";
}
echo "</div>";

// ============================================
// SECTION 5: Database Connection Test
// ============================================
echo "<div class='debug-section'>";
echo "<h3>5. Database Connection Test</h3>";

try {
    $db = Database::getInstance();
    $pdo = $db->getPdo();
    echo "<p class='success'>✓ Database connection successful</p>";
    
    // Test query
    $stmt = $pdo->query("SELECT 1 as test");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "<p>Test query result: " . print_r($result, true) . "</p>";
    
} catch (Exception $e) {
    echo "<p class='error'>✗ Database connection failed: " . $e->getMessage() . "</p>";
}
echo "</div>";

// ============================================
// SECTION 6: Products Table Check
// ============================================
echo "<div class='debug-section'>";
echo "<h3>6. Products Table Check</h3>";

try {
    $db = Database::getInstance();
    $pdo = $db->getPdo();
    
    // Check if products table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'products'");
    if ($stmt->rowCount() > 0) {
        echo "<p class='success'>✓ Products table exists</p>";
        
        // Count products
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM products");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "<p>Total products: " . $result['count'] . "</p>";
        
        // Show sample products
        $stmt = $pdo->query("SELECT id, name, price, status, updated_at FROM products ORDER BY id DESC LIMIT 5");
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<h4>Latest Products:</h4>";
        echo "<table>";
        echo "<tr><th>ID</th><th>Name</th><th>Price</th><th>Status</th><th>Updated</th></tr>";
        foreach ($products as $p) {
            echo "<tr>";
            echo "<td>" . $p['id'] . "</td>";
            echo "<td>" . htmlspecialchars($p['name']) . "</td>";
            echo "<td>" . number_format($p['price']) . "</td>";
            echo "<td>" . $p['status'] . "</td>";
            echo "<td>" . $p['updated_at'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        
    } else {
        echo "<p class='error'>✗ Products table does NOT exist</p>";
    }
    
} catch (Exception $e) {
    echo "<p class='error'>Error: " . $e->getMessage() . "</p>";
}
echo "</div>";

// ============================================
// SECTION 7: Test Database Update Function
// ============================================
echo "<div class='debug-section'>";
echo "<h3>7. Test Database Update Function</h3>";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['test_update'])) {
    try {
        $db = Database::getInstance();
        $pdo = $db->getPdo();
        
        $productId = (int)$_POST['product_id'];
        $newName = 'Test Update ' . date('Y-m-d H:i:s');
        
        $sql = "UPDATE products SET name = ?, updated_at = NOW() WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $result = $stmt->execute([$newName, $productId]);
        
        if ($result) {
            echo "<p class='success'>✓ Update successful! Rows affected: " . $stmt->rowCount() . "</p>";
            
            // Verify the update
            $stmt = $pdo->prepare("SELECT name, updated_at FROM products WHERE id = ?");
            $stmt->execute([$productId]);
            $updated = $stmt->fetch(PDO::FETCH_ASSOC);
            echo "<p>Updated data:</p>";
            echo "<pre>" . print_r($updated, true) . "</pre>";
        } else {
            echo "<p class='error'>✗ Update failed!</p>";
        }
        
    } catch (Exception $e) {
        echo "<p class='error'>Error: " . $e->getMessage() . "</p>";
    }
}

echo "<form method='POST'>";
echo "<input type='hidden' name='test_update' value='1'>";
echo "<label>Product ID to test: <input type='number' name='product_id' value='1'></label>";
echo "<button type='submit'>Test Update</button>";
echo "</form>";
echo "</div>";

// ============================================
// SECTION 8: Test Session
// ============================================
echo "<div class='debug-section'>";
echo "<h3>8. Test Session</h3>";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['test_session'])) {
    $_SESSION['test_value'] = 'Test at ' . date('Y-m-d H:i:s');
    $_SESSION['product_saved'] = $_POST['session_product_id'] ?? 1;
    echo "<p class='success'>✓ Session set: product_saved = " . $_SESSION['product_saved'] . "</p>";
}

echo "<form method='POST'>";
echo "<input type='hidden' name='test_session' value='1'>";
echo "<label>Product ID: <input type='number' name='session_product_id' value='1'></label>";
echo "<button type='submit'>Set Session</button>";
echo "</form>";

if (isset($_SESSION['product_saved'])) {
    echo "<p class='info'>Current session product_saved: " . $_SESSION['product_saved'] . "</p>";
}
echo "</div>";

// ============================================
// SECTION 9: Form to Test Product Edit
// ============================================
echo "<div class='debug-section'>";
echo "<h3>9. Test Product Edit Form Simulation</h3>";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['edit_product'])) {
    $productId = (int)$_POST['product_id'];
    $productName = $_POST['name'] ?? '';
    $productPrice = (float)$_POST['price'];
    
    echo "<h4>Form Submitted:</h4>";
    echo "<p>Product ID: $productId</p>";
    echo "<p>Product Name: " . htmlspecialchars($productName) . "</p>";
    echo "<p>Product Price: $productPrice</p>";
    
    // Try to update
    try {
        $db = Database::getInstance();
        $pdo = $db->getPdo();
        
        $sql = "UPDATE products SET name = ?, price = ?, updated_at = NOW() WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $result = $stmt->execute([$productName, $productPrice, $productId]);
        
        if ($result && $stmt->rowCount() > 0) {
            echo "<p class='success'>✓ Database updated successfully!</p>";
            
            // Show redirect simulation
            $redirectUrl = "?page=admin&module=products&action=edit&id=$productId&saved=1";
            echo "<p>Would redirect to: $redirectUrl</p>";
            echo "<p><a href='$redirectUrl'>Click here to test redirect</a></p>";
        } else {
            echo "<p class='error'>✗ No rows updated. Product might not exist.</p>";
        }
        
    } catch (Exception $e) {
        echo "<p class='error'>Database error: " . $e->getMessage() . "</p>";
    }
} else {
    // Show sample products for testing
    try {
        $db = Database::getInstance();
        $pdo = $db->getPdo();
        
        $stmt = $pdo->query("SELECT id, name, price FROM products ORDER BY id DESC LIMIT 3");
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<form method='POST'>";
        echo "<input type='hidden' name='edit_product' value='1'>";
        echo "<label>Product: <select name='product_id'>";
        foreach ($products as $p) {
            echo "<option value='" . $p['id'] . "'>" . htmlspecialchars($p['name']) . " (ID: " . $p['id'] . ")</option>";
        }
        echo "</select></label><br><br>";
        echo "<label>New Name: <input type='text' name='name' style='width: 300px;'></label><br><br>";
        echo "<label>New Price: <input type='number' name='price' step='1000'></label><br><br>";
        echo "<button type='submit' style='padding: 10px 20px;'>Submit Update</button>";
        echo "</form>";
        
    } catch (Exception $e) {
        echo "<p class='error'>Error: " . $e->getMessage() . "</p>";
    }
}
echo "</div>";

// ============================================
// SECTION 10: Check ProductsModel
// ============================================
echo "<div class='debug-section'>";
echo "<h3>10. ProductsModel Test</h3>";

try {
    require_once __DIR__ . '/app/models/ProductsModel.php';
    
    $productsModel = new ProductsModel();
    
    // Test find method
    $product = $productsModel->find(1);
    if ($product) {
        echo "<p class='success'>✓ ProductsModel->find(1) works</p>";
        echo "<p>Product name: " . htmlspecialchars($product['name']) . "</p>";
        echo "<p>Product price: " . number_format($product['price']) . "</p>";
    } else {
        echo "<p class='error'>✗ Product not found</p>";
    }
    
    // Test update method
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['test_model_update'])) {
        $productId = (int)$_POST['model_product_id'];
        $updateData = [
            'name' => 'Model Test ' . date('Y-m-d H:i:s'),
            'price' => (float)$_POST['model_price']
        ];
        
        $result = $productsModel->update($productId, $updateData);
        
        if ($result) {
            echo "<p class='success'>✓ ProductsModel->update() successful!</p>";
            echo "<p>Updated product ID: $productId</p>";
        } else {
            echo "<p class='error'>✗ ProductsModel->update() failed</p>";
        }
    }
    
    echo "<form method='POST'>";
    echo "<input type='hidden' name='test_model_update' value='1'>";
    echo "<label>Product ID: <input type='number' name='model_product_id' value='1'></label><br><br>";
    echo "<label>New Price: <input type='number' name='model_price' step='1000' value='100000'></label><br><br>";
    echo "<button type='submit'>Test Model Update</button>";
    echo "</form>";
    
} catch (Exception $e) {
    echo "<p class='error'>Error: " . $e->getMessage() . "</p>";
}
echo "</div>";

echo "</body></html>";
