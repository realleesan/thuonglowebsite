<?php
/**
 * Ultra Simple Seeder Script
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Ultra Simple Seeder</h2>";

try {
    // Load config
    require_once __DIR__ . '/config.php';
    
    // Connect to database
    $dsn = "mysql:host={$config['database']['host']};dbname={$config['database']['name']};charset={$config['database']['charset']}";
    $pdo = new PDO($dsn, $config['database']['username'], $config['database']['password'], $config['database']['options']);
    
    echo "<p>✓ Database connected</p>";
    
    // Check if tables exist
    echo "<p>Step 1: Checking tables...</p>";
    $requiredTables = [
        'users', 'categories', 'products', 'orders', 'order_items',
        'news', 'events', 'contacts', 'settings', 'affiliates'
    ];
    
    foreach ($requiredTables as $table) {
        $stmt = $pdo->query("SHOW TABLES LIKE '{$table}'");
        if ($stmt->rowCount() == 0) {
            throw new Exception("Table '{$table}' does not exist. Please run migrations first.");
        }
        echo "<p>✓ Table '{$table}' exists</p>";
    }
    
    echo "<p>Step 2: Seeding data...</p>";
    
    // 1. Seed Users
    echo "<p>🔄 Seeding users...</p>";
    $stmt = $pdo->prepare("INSERT IGNORE INTO users (id, name, email, password, role, status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?)");
    
    $users = [
        [1, 'Administrator', 'admin@thuonglo.com', password_hash('admin123', PASSWORD_DEFAULT), 'admin', 'active', date('Y-m-d H:i:s')],
        [2, 'User One', 'user1@example.com', password_hash('user123', PASSWORD_DEFAULT), 'user', 'active', date('Y-m-d H:i:s')],
        [3, 'Affiliate One', 'affiliate1@example.com', password_hash('affiliate123', PASSWORD_DEFAULT), 'agent', 'active', date('Y-m-d H:i:s')]
    ];
    
    foreach ($users as $user) {
        $stmt->execute($user);
    }
    echo "<p style='color: green;'>✓ Users seeded</p>";
    
    // 2. Seed Categories
    echo "<p>🔄 Seeding categories...</p>";
    $stmt = $pdo->prepare("INSERT IGNORE INTO categories (id, name, slug, description, status, created_at) VALUES (?, ?, ?, ?, ?, ?)");
    
    $categories = [
        [1, 'Electronics', 'electronics', 'Electronic devices and gadgets', 'active', date('Y-m-d H:i:s')],
        [2, 'Clothing', 'clothing', 'Fashion and apparel', 'active', date('Y-m-d H:i:s')],
        [3, 'Books', 'books', 'Books and educational materials', 'active', date('Y-m-d H:i:s')]
    ];
    
    foreach ($categories as $category) {
        $stmt->execute($category);
    }
    echo "<p style='color: green;'>✓ Categories seeded</p>";
    
    // 3. Seed Products
    echo "<p>🔄 Seeding products...</p>";
    $stmt = $pdo->prepare("INSERT IGNORE INTO products (id, name, slug, description, price, category_id, featured, status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    $products = [
        [1, 'Smartphone', 'smartphone', 'Latest smartphone with advanced features', 599.99, 1, 1, 'active', date('Y-m-d H:i:s')],
        [2, 'T-Shirt', 't-shirt', 'Comfortable cotton t-shirt', 19.99, 2, 1, 'active', date('Y-m-d H:i:s')],
        [3, 'Programming Book', 'programming-book', 'Learn programming fundamentals', 39.99, 3, 0, 'active', date('Y-m-d H:i:s')]
    ];
    
    foreach ($products as $product) {
        $stmt->execute($product);
    }
    echo "<p style='color: green;'>✓ Products seeded</p>";
    
    // 4. Seed Orders
    echo "<p>🔄 Seeding orders...</p>";
    $stmt = $pdo->prepare("INSERT IGNORE INTO orders (id, order_number, user_id, status, payment_status, subtotal, total, shipping_name, shipping_email, shipping_phone, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    $orders = [
        [1, 'ORD-2026-001', 2, 'completed', 'paid', 599.99, 599.99, 'User One', 'user1@example.com', '+84 123 456 789', date('Y-m-d H:i:s', strtotime('-1 week'))],
        [2, 'ORD-2026-002', 2, 'processing', 'paid', 79.97, 79.97, 'User One', 'user1@example.com', '+84 123 456 789', date('Y-m-d H:i:s', strtotime('-2 days'))]
    ];
    
    foreach ($orders as $order) {
        $stmt->execute($order);
    }
    echo "<p style='color: green;'>✓ Orders seeded</p>";
    
    // 5. Seed Order Items
    echo "<p>🔄 Seeding order items...</p>";
    $stmt = $pdo->prepare("INSERT IGNORE INTO order_items (id, order_id, product_id, product_name, quantity, price, total, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    
    $orderItems = [
        [1, 1, 1, 'Smartphone', 1, 599.99, 599.99, date('Y-m-d H:i:s', strtotime('-1 week'))],
        [2, 2, 2, 'T-Shirt', 2, 19.99, 39.98, date('Y-m-d H:i:s', strtotime('-2 days'))],
        [3, 2, 3, 'Programming Book', 1, 39.99, 39.99, date('Y-m-d H:i:s', strtotime('-2 days'))]
    ];
    
    foreach ($orderItems as $item) {
        $stmt->execute($item);
    }
    echo "<p style='color: green;'>✓ Order items seeded</p>";
    
    // 7. Seed News
    echo "<p>🔄 Seeding news...</p>";
    $stmt = $pdo->prepare("INSERT IGNORE INTO news (id, title, slug, content, status, created_at) VALUES (?, ?, ?, ?, ?, ?)");
    
    $news = [
        [1, 'Welcome to Thuong Lo', 'welcome-to-thuong-lo', 'Welcome to our new e-commerce platform!', 'published', date('Y-m-d H:i:s')],
        [2, 'New Products Available', 'new-products-available', 'Check out our latest product arrivals.', 'published', date('Y-m-d H:i:s')]
    ];
    
    foreach ($news as $article) {
        $stmt->execute($article);
    }
    echo "<p style='color: green;'>✓ News seeded</p>";
    
    // 8. Seed Events
    echo "<p>🔄 Seeding events...</p>";
    $stmt = $pdo->prepare("INSERT IGNORE INTO events (id, title, slug, description, start_date, end_date, organizer_id, status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    $events = [
        [1, 'Grand Opening', 'grand-opening', 'Join us for our grand opening celebration!', date('Y-m-d H:i:s', strtotime('+1 week')), date('Y-m-d H:i:s', strtotime('+1 week +3 hours')), 1, 'upcoming', date('Y-m-d H:i:s')],
        [2, 'Summer Sale', 'summer-sale', 'Big discounts on summer items.', date('Y-m-d H:i:s', strtotime('+2 weeks')), date('Y-m-d H:i:s', strtotime('+2 weeks +2 hours')), 1, 'upcoming', date('Y-m-d H:i:s')]
    ];
    
    foreach ($events as $event) {
        $stmt->execute($event);
    }
    echo "<p style='color: green;'>✓ Events seeded</p>";
    
    // 9. Seed Contacts
    echo "<p>🔄 Seeding contacts...</p>";
    $stmt = $pdo->prepare("INSERT IGNORE INTO contacts (id, name, email, subject, message, status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?)");
    
    $contacts = [
        [1, 'John Doe', 'john@example.com', 'Product Inquiry', 'I would like to know more about your products.', 'new', date('Y-m-d H:i:s')],
        [2, 'Jane Smith', 'jane@example.com', 'Support Request', 'I need help with my order.', 'read', date('Y-m-d H:i:s')]
    ];
    
    foreach ($contacts as $contact) {
        $stmt->execute($contact);
    }
    echo "<p style='color: green;'>✓ Contacts seeded</p>";
    
    // 10. Seed Settings
    echo "<p>🔄 Seeding settings...</p>";
    $stmt = $pdo->prepare("INSERT IGNORE INTO settings (`key`, `value`, `type`, `group`, created_at) VALUES (?, ?, ?, ?, ?)");
    
    $settings = [
        ['site_name', 'Thuong Lo', 'text', 'general', date('Y-m-d H:i:s')],
        ['site_description', 'Your trusted e-commerce platform', 'textarea', 'general', date('Y-m-d H:i:s')],
        ['contact_email', 'contact@thuonglo.com', 'email', 'contact', date('Y-m-d H:i:s')],
        ['contact_phone', '+84 123 456 789', 'text', 'contact', date('Y-m-d H:i:s')]
    ];
    
    foreach ($settings as $setting) {
        $stmt->execute($setting);
    }
    echo "<p style='color: green;'>✓ Settings seeded</p>";
    
    // 11. Seed Affiliates
    echo "<p>🔄 Seeding affiliates...</p>";
    $stmt = $pdo->prepare("INSERT IGNORE INTO affiliates (id, user_id, referral_code, commission_rate, status, created_at) VALUES (?, ?, ?, ?, ?, ?)");
    
    $affiliates = [
        [1, 3, 'AFF001', 10.00, 'active', date('Y-m-d H:i:s')]
    ];
    
    foreach ($affiliates as $affiliate) {
        $stmt->execute($affiliate);
    }
    echo "<p style='color: green;'>✓ Affiliates seeded</p>";
    
    echo "<h3 style='color: green;'>Seeding completed successfully!</h3>";
    
    // Show final statistics
    echo "<h3>Database Statistics:</h3>";
    echo "<ul>";
    foreach ($requiredTables as $table) {
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM {$table}");
        $count = $stmt->fetch()['count'];
        echo "<li>{$table}: {$count} records</li>";
    }
    echo "</ul>";
    
    echo "<h3 style='color: green;'>🎉 Database setup completed! Your application is ready to use.</h3>";
    
    echo "<h3>Default Login Credentials:</h3>";
    echo "<ul>";
    echo "<li><strong>Admin:</strong> admin@thuonglo.com / admin123</li>";
    echo "<li><strong>User:</strong> user1@example.com / user123</li>";
    echo "<li><strong>Affiliate:</strong> affiliate1@example.com / affiliate123</li>";
    echo "</ul>";
    
} catch (Exception $e) {
    echo "<h3 style='color: red;'>Error:</h3>";
    echo "<p style='color: red;'>" . $e->getMessage() . "</p>";
    echo "<p><strong>File:</strong> " . $e->getFile() . "</p>";
    echo "<p><strong>Line:</strong> " . $e->getLine() . "</p>";
}
?>