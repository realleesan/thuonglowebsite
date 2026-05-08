<?php
/**
 * Script cập nhật và bổ sung thương hiệu
 * - Đổi tên thương hiệu hiện tại
 * - Thêm 9 thương hiệu mới
 * - Gán sản phẩm hiện có vào thương hiệu
 */

require_once 'config.php';

try {
    // Kết nối database
    $pdo = new PDO(
        "mysql:host={$config['database']['host']};dbname={$config['database']['name']};charset=utf8mb4",
        $config['database']['username'],
        $config['database']['password'],
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
    
    echo "<h2>Bắt đầu cập nhật thương hiệu...</h2>";
    
    // 1. Kiểm tra thương hiệu hiện tại
    $stmt = $pdo->query("SELECT id, name, slug FROM brands ORDER BY id");
    $existingBrands = $stmt->fetchAll();
    
    echo "<h3>Thương hiệu hiện tại:</h3>";
    foreach ($existingBrands as $brand) {
        echo "- ID: {$brand['id']}, Name: {$brand['name']}, Slug: {$brand['slug']}<br>";
    }
    
    // 2. Kiểm tra sản phẩm hiện tại
    $stmt = $pdo->query("SELECT id, name, brand_id FROM products ORDER BY id");
    $existingProducts = $stmt->fetchAll();
    
    echo "<h3>Sản phẩm hiện tại:</h3>";
    foreach ($existingProducts as $product) {
        echo "- ID: {$product['id']}, Name: {$product['name']}, Brand ID: {$product['brand_id']}<br>";
    }
    
    // 3. Cập nhật thương hiệu hiện tại (nếu có)
    if (!empty($existingBrands)) {
        $firstBrand = $existingBrands[0];
        $updateSql = "
            UPDATE brands SET 
                name = 'ZARA FASHION',
                slug = 'zara-fashion',
                description = 'Thương hiệu thời trang quốc tế hàng đầu với các bộ sưu tập mới nhất mỗi tuần. Chuyên về trang phục nữ, nam và trẻ em.',
                website = 'https://www.zara.com',
                image = 'https://images.unsplash.com/photo-1578632292335-df3abbb0d586?w=800&h=600&fit=crop&crop=entropy',
                status = 'active',
                sort_order = 1,
                show_in_filter = 1,
                is_featured = 1
            WHERE id = ?
        ";
        $stmt = $pdo->prepare($updateSql);
        $stmt->execute([$firstBrand['id']]);
        echo "<h3>✅ Đã cập nhật thương hiệu {$firstBrand['name']} → ZARA FASHION</h3>";
    }
    
    // 4. Thêm 14 thương hiệu thời trang mới
    $newBrands = [
        [
            'name' => 'H&M FASHION',
            'slug' => 'hm-fashion',
            'description' => 'Thương hiệu thời trang Thụy Điển nổi tiếng với phong cách tối giản và bền vững. Cung cấp trang phục chất lượng cao giá cả phải chăng.',
            'website' => 'https://www.hm.com',
            'status' => 'active',
            'sort_order' => 2,
            'show_in_filter' => 1,
            'is_featured' => 1
        ],
        [
            'name' => 'UNIQLO STYLE',
            'slug' => 'uniqlo-style',
            'description' => 'Thương hiệu thời trang Nhật Bản với công nghệ LifeWear. Chuyên về trang phục cơ bản, thoải mái và chất lượng cao.',
            'website' => 'https://www.uniqlo.com',
            'status' => 'active',
            'sort_order' => 3,
            'show_in_filter' => 1,
            'is_featured' => 1
        ],
        [
            'name' => 'GUCCI VIETNAM',
            'slug' => 'gucci-vietnam',
            'description' => 'Thương hiệu xa xỉ hàng đầu nước Ý. Chuyên về túi xách, giày dép, trang phục và phụ kiện thời trang cao cấp.',
            'website' => 'https://www.gucci.com',
            'status' => 'active',
            'sort_order' => 4,
            'show_in_filter' => 1,
            'is_featured' => 1
        ],
        [
            'name' => 'NIKE SPORTS',
            'slug' => 'nike-sports',
            'description' => 'Thương hiệu thể thao hàng đầu thế giới. Cung cấp giày dép, trang phục và dụng cụ thể thao chất lượng cao.',
            'website' => 'https://www.nike.com',
            'status' => 'active',
            'sort_order' => 5,
            'show_in_filter' => 1,
            'is_featured' => 0
        ],
        [
            'name' => 'ADIDAS PERFORMANCE',
            'slug' => 'adidas-performance',
            'description' => 'Thương hiệu thể thao đa quốc gia của Đức. Chuyên về trang phục và giày dép thể thao, thời trang thể thao.',
            'website' => 'https://www.adidas.com',
            'status' => 'active',
            'sort_order' => 6,
            'show_in_filter' => 1,
            'is_featured' => 0
        ],
        [
            'name' => 'MANGO FASHION',
            'slug' => 'mango-fashion',
            'description' => 'Thương hiệu thời trang Tây Ban Nha. Chuyên về trang phục nữ, nam và trẻ em với phong cách châu Âu hiện đại.',
            'website' => 'https://shop.mango.com',
            'status' => 'active',
            'sort_order' => 7,
            'show_in_filter' => 1,
            'is_featured' => 0
        ],
        [
            'name' => 'PUMA SPORTS',
            'slug' => 'puma-sports',
            'description' => 'Thương hiệu thể thao đa quốc gia của Đức. Cung cấp giày dép, trang phục thể thao và thời trang đường phố.',
            'website' => 'https://about.puma.com',
            'status' => 'active',
            'sort_order' => 8,
            'show_in_filter' => 1,
            'is_featured' => 0
        ],
        [
            'name' => 'CALVIN KLEIN',
            'slug' => 'calvin-klein',
            'description' => 'Thương hiệu thời trang Mỹ nổi tiếng với đồ lót, nước hoa và trang phục tối giản hiện đại.',
            'website' => 'https://www.calvinklein.com',
            'status' => 'active',
            'sort_order' => 9,
            'show_in_filter' => 1,
            'is_featured' => 0
        ],
        [
            'name' => 'TOMMY HILFIGER',
            'slug' => 'tommy-hilfiger',
            'description' => 'Thương hiệu thời trang Mỹ với phong cách preppy cổ điển. Chuyên về trang phục casual, jeans và phụ kiện.',
            'website' => 'https://usa.tommy.com',
            'status' => 'active',
            'sort_order' => 10,
            'show_in_filter' => 1,
            'is_featured' => 0
        ],
        [
            'name' => 'LEVI\'S DENIM',
            'slug' => 'levis-denim',
            'description' => 'Thương hiệu denim huyền thoại của Mỹ. Chuyên về quần jeans, áo khoác và trang phục denim chất lượng cao.',
            'website' => 'https://www.levi.com',
            'status' => 'active',
            'sort_order' => 11,
            'show_in_filter' => 1,
            'is_featured' => 0
        ],
        [
            'name' => 'BERSHKA STYLE',
            'slug' => 'bershka-style',
            'description' => 'Thương hiệu thời trang trẻ của Tây Ban Nha. Chuyên về trang phục xu hướng cho giới trẻ với giá cả phải chăng.',
            'website' => 'https://www.bershka.com',
            'status' => 'active',
            'sort_order' => 12,
            'show_in_filter' => 1,
            'is_featured' => 0
        ],
        [
            'name' => 'PULL&BEAR',
            'slug' => 'pull-bear',
            'description' => 'Thương hiệu thời trang trẻ của Inditex. Chuyên về trang phục casual, jeans và phụ kiện cho giới trẻ.',
            'website' => 'https://www.pullandbear.com',
            'status' => 'active',
            'sort_order' => 13,
            'show_in_filter' => 1,
            'is_featured' => 0
        ],
        [
            'name' => 'STRADIVARIUS',
            'slug' => 'stradivarius',
            'description' => 'Thương hiệu thời trang nữ của Inditex. Chuyên về trang phục nữ với phong cách nữ tính, hiện đại và thời trang.',
            'website' => 'https://www.stradivarius.com',
            'status' => 'active',
            'sort_order' => 14,
            'show_in_filter' => 1,
            'is_featured' => 0
        ],
        [
            'name' => 'MASSIMO DUTTI',
            'slug' => 'massimo-dutti',
            'description' => 'Thương hiệu thời trang cao cấp của Inditex. Chuyên về trang phục chất lượng cao với phong cách sang trọng, tinh tế.',
            'website' => 'https://www.massimodutti.com',
            'status' => 'active',
            'sort_order' => 15,
            'show_in_filter' => 1,
            'is_featured' => 0
        ]
    ];
    
    // Gán ảnh Unsplash cho tất cả thương hiệu
    $imageUrls = [
        'https://images.unsplash.com/photo-1578632292335-df3abbb0d586?w=800&h=600&fit=crop&crop=entropy',
        'https://images.unsplash.com/photo-1578632292335-df3abbb0d586?w=800&h=600&fit=crop&crop=entropy',
        'https://images.unsplash.com/photo-1578632292335-df3abbb0d586?w=800&h=600&fit=crop&crop=entropy',
        'https://images.unsplash.com/photo-1578632292335-df3abbb0d586?w=800&h=600&fit=crop&crop=entropy',
        'https://images.unsplash.com/photo-1578632292335-df3abbb0d586?w=800&h=600&fit=crop&crop=entropy',
        'https://images.unsplash.com/photo-1578632292335-df3abbb0d586?w=800&h=600&fit=crop&crop=entropy',
        'https://images.unsplash.com/photo-1578632292335-df3abbb0d586?w=800&h=600&fit=crop&crop=entropy',
        'https://images.unsplash.com/photo-1578632292335-df3abbb0d586?w=800&h=600&fit=crop&crop=entropy',
        'https://images.unsplash.com/photo-1578632292335-df3abbb0d586?w=800&h=600&fit=crop&crop=entropy',
        'https://images.unsplash.com/photo-1578632292335-df3abbb0d586?w=800&h=600&fit=crop&crop=entropy',
        'https://images.unsplash.com/photo-1578632292335-df3abbb0d586?w=800&h=600&fit=crop&crop=entropy',
        'https://images.unsplash.com/photo-1578632292335-df3abbb0d586?w=800&h=600&fit=crop&crop=entropy',
        'https://images.unsplash.com/photo-1578632292335-df3abbb0d586?w=800&h=600&fit=crop&crop=entropy',
        'https://images.unsplash.com/photo-1578632292335-df3abbb0d586?w=800&h=600&fit=crop&crop=entropy'
    ];
    
    $insertedBrandIds = [];
    
    foreach ($newBrands as $index => $brand) {
        // Gán ảnh cho thương hiệu
        $brand['image'] = $imageUrls[$index] ?? $imageUrls[0];
        
        $insertSql = "
            INSERT INTO brands (name, slug, description, website, image, status, sort_order, show_in_filter, is_featured, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ";
        
        $stmt = $pdo->prepare($insertSql);
        $stmt->execute([
            $brand['name'],
            $brand['slug'],
            $brand['description'],
            $brand['website'],
            $brand['image'],
            $brand['status'],
            $brand['sort_order'],
            $brand['show_in_filter'],
            $brand['is_featured']
        ]);
        
        $brandId = $pdo->lastInsertId();
        $insertedBrandIds[] = $brandId;
        
        echo "<h3>✅ Đã thêm thương hiệu: {$brand['name']} (ID: {$brandId})</h3>";
    }
    
    // 6. Gán sản phẩm hiện có vào các thương hiệu
    $allBrandIds = [];
    if (!empty($existingBrands)) {
        $allBrandIds[] = $existingBrands[0]['id']; // Thương hiệu đã cập nhật
    }
    $allBrandIds = array_merge($allBrandIds, $insertedBrandIds);
    
    if (!empty($existingProducts) && !empty($allBrandIds)) {
        foreach ($existingProducts as $product) {
            // Random gán vào một thương hiệu
            $randomBrandId = $allBrandIds[array_rand($allBrandIds)];
            
            $updateProductSql = "UPDATE products SET brand_id = ? WHERE id = ?";
            $stmt = $pdo->prepare($updateProductSql);
            $stmt->execute([$randomBrandId, $product['id']]);
            
            // Lấy tên thương hiệu để hiển thị
            $brandStmt = $pdo->prepare("SELECT name FROM brands WHERE id = ?");
            $brandStmt->execute([$randomBrandId]);
            $brandName = $brandStmt->fetchColumn();
            
            echo "<h4>📦 Gán sản phẩm '{$product['name']}' → thương hiệu '{$brandName}'</h4>";
        }
    }
    
    // 7. Hiển thị kết quả cuối cùng
    echo "<h2>🎉 Kết quả cuối cùng:</h2>";
    
    $stmt = $pdo->query("
        SELECT b.*, COUNT(p.id) as product_count 
        FROM brands b 
        LEFT JOIN products p ON b.id = p.brand_id 
        WHERE b.status = 'active' 
        GROUP BY b.id 
        ORDER BY b.sort_order
    ");
    $finalBrands = $stmt->fetchAll();
    
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>ID</th><th>Tên</th><th>Slug</th><th>Số sản phẩm</th><th>Featured</th><th>Ảnh</th></tr>";
    
    foreach ($finalBrands as $brand) {
        $featured = $brand['is_featured'] ? '✅' : '❌';
        echo "<tr>";
        echo "<td>{$brand['id']}</td>";
        echo "<td><strong>{$brand['name']}</strong></td>";
        echo "<td>{$brand['slug']}</td>";
        echo "<td>{$brand['product_count']}</td>";
        echo "<td>{$featured}</td>";
        echo "<td><img src='{$brand['image']}' width='50' height='50' onerror='this.src=\"https://via.placeholder.com/50\"'></td>";
        echo "</tr>";
    }
    
    echo "</table>";
    
    echo "<h2>✅ Hoàn thành cập nhật thương hiệu!</h2>";
    echo "<p><a href='?page=brands'>Xem trang thương hiệu</a> | ";
    echo "<a href='?page=products'>Xem trang sản phẩm</a></p>";
    
} catch (Exception $e) {
    echo "<h2>❌ Lỗi:</h2>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}
?>
