<?php
define('THUONGLO_INIT', true);
require_once 'config.php';
require_once 'core/database.php';
require_once 'app/models/CartModel.php';
require_once 'app/models/ProductsModel.php';

$db = Database::getInstance();
$cartModel = new CartModel();
$productsModel = new ProductsModel();

// 1. Create a test product
$productId = $productsModel->create([
    'name' => 'Sản phẩm Test Cart Sync',
    'price' => 100000,
    'status' => 'active',
    'slug' => 'san-pham-test-cart-sync-' . time(),
    'sku' => 'TESTCARTSYNC'
]);

echo "Created test product ID: $productId\n";

// 2. Put it in user's cart (let's use a dummy user ID like 99999)
$userId = 99999;
// Clear any existing cart items for this test user first
$db->query("DELETE FROM carts WHERE user_id = :user_id", [':user_id' => $userId]);

// Manually insert into carts table
$db->table('carts')->insert([
    'user_id' => $userId,
    'product_id' => $productId,
    'quantity' => 1,
    'price' => 100000,
    'created_at' => date('Y-m-d H:i:s'),
    'updated_at' => date('Y-m-d H:i:s')
]);

echo "Inserted product into cart for user $userId\n";

// 3. Verify it is returned by getByUser
$items = $cartModel->getByUser($userId);
echo "Cart item count before deactivation: " . count($items) . "\n";

// 4. Update product to inactive
$productsModel->update($productId, ['status' => 'inactive']);
echo "Updated product status to inactive\n";

// 5. Retrieve cart again (should trigger auto-cleanup)
$itemsAfter = $cartModel->getByUser($userId);
echo "Cart item count after deactivation: " . count($itemsAfter) . "\n";

// Check if item is also gone from database
$dbItem = $db->table('carts')
    ->where('user_id', $userId)
    ->where('product_id', $productId)
    ->first();
echo "Is item still in database: " . ($dbItem ? 'YES' : 'NO') . "\n";

// 6. Clean up database
$productsModel->delete($productId);
$db->query("DELETE FROM carts WHERE user_id = :user_id", [':user_id' => $userId]);
echo "Database cleaned up.\n";
