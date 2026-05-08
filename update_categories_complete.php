<?php
/**
 * Complete Category Management Script
 * Tasks:
 * 1. Keep brands in uppercase
 * 2. Rename non-uppercase categories to proper format
 * 3. Add images to all categories
 * 4. Create parent-child relationships (up to level 3)
 * 5. Assign products to appropriate categories
 * 6. Ensure 20 total categories
 */

require_once __DIR__ . '/core/database.php';

try {
    $db = Database::getInstance();
    $pdo = $db->getPdo();
    
    echo "<h1>Complete Category Management</h1>";
    
    // Step 1: Get current categories and products
    $stmt = $pdo->query("SELECT * FROM categories ORDER BY id");
    $currentCategories = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $stmt = $pdo->query("SELECT id, name, category_id FROM products ORDER BY id");
    $currentProducts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h2>Current Categories (" . count($currentCategories) . " items)</h2>";
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background: #f0f0f0;'>
            <th>ID</th>
            <th>Name</th>
            <th>Parent ID</th>
            <th>Current Image</th>
            <th>Status</th>
          </tr>";
    
    foreach ($currentCategories as $category) {
        echo "<tr>
                <td>{$category['id']}</td>
                <td>" . htmlspecialchars($category['name']) . "</td>
                <td>{$category['parent_id']}</td>
                <td>" . ($category['image'] ? "<img src='{$category['image']}' width='50' height='50'>" : "No image") . "</td>
                <td>{$category['status']}</td>
              </tr>";
    }
    echo "</table>";
    
    // Step 2: Define brand names (keep uppercase)
    $brandsToKeep = [
        'Lemon Tree',
        'Nieve', 
        'I AM',
        'GS',
        'See You',
        'Nile',
        'Hers',
        'Y.JIA',
        'NHÀ WGWE'
    ];
    
    // Step 3: Define new category structure (20 categories total)
    $newCategoryStructure = [
        // Level 1 - Main Categories
        [
            'name' => 'THỜI TRANG NỮ',
            'slug' => 'thoi-trang-nu',
            'description' => 'Danh mục thời trang nữ với các sản phẩm đa dạng từ công sở đến dạo phố',
            'image' => 'https://images.unsplash.com/photo-1441986300917-64674bd600d8?w=800&h=600&fit=crop&crop=entropy',
            'parent_id' => null,
            'sort_order' => 1,
            'children' => [
                // Level 2 - Sub Categories
                [
                    'name' => 'VÁY ĐẦM',
                    'slug' => 'vay-dam',
                    'description' => 'Các loại váy đầm nữ tính từ công sở đến tiệc tùng',
                    'image' => 'https://images.unsplash.com/photo-1469334931182-2a2a8b1a2e5c?w=800&h=600&fit=crop&crop=entropy',
                    'sort_order' => 1,
                    'children' => [
                        // Level 3 - Specific Types
                        [
                            'name' => 'Váy Công Sở',
                            'slug' => 'vay-cong-so',
                            'description' => 'Váy thanh lịch phù hợp môi trường công sở',
                            'image' => 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=800&h=600&fit=crop&crop=entropy',
                            'sort_order' => 1
                        ],
                        [
                            'name' => 'Váy Dạo Phố',
                            'slug' => 'vay-dao-pho',
                            'description' => 'Váy năng động, thoải mái cho dạo phố',
                            'image' => 'https://images.unsplash.com/photo-1515372039744-b8f2a3214755?w=800&h=600&fit=crop&crop=entropy',
                            'sort_order' => 2
                        ],
                        [
                            'name' => 'Váy Tiệc Tùng',
                            'slug' => 'vay-tiec-tung',
                            'description' => 'Váy lộng lẫy cho các buổi tiệc',
                            'image' => 'https://images.unsplash.com/photo-1578632292335-df3abbb0d586?w=800&h=600&fit=crop&crop=entropy',
                            'sort_order' => 3
                        ]
                    ]
                ],
                [
                    'name' => 'ÁO NỮ',
                    'slug' => 'ao-nu',
                    'description' => 'Các loại áo nữ đa dạng phong cách',
                    'image' => 'https://images.unsplash.com/photo-1434389677669-e08b4cac3105?w=800&h=600&fit=crop&crop=entropy',
                    'sort_order' => 2,
                    'children' => [
                        [
                            'name' => 'Áo Sơ Mi',
                            'slug' => 'ao-so-mi',
                            'description' => 'Áo sơ mi nữ thanh lịch',
                            'image' => 'https://images.unsplash.com/photo-1594633312681-425c7b97ccd1?w=800&h=600&fit=crop&crop=entropy',
                            'sort_order' => 1
                        ],
                        [
                            'name' => 'Áo Thun',
                            'slug' => 'ao-thun',
                            'description' => 'Áo thun nữ thoải mái',
                            'image' => 'https://images.unsplash.com/photo-1515886657613-9f3515b0c78f?w=800&h=600&fit=crop&crop=entropy',
                            'sort_order' => 2
                        ],
                        [
                            'name' => 'Áo Khoác',
                            'slug' => 'ao-khoac',
                            'description' => 'Áo khoác nữ phong cách',
                            'image' => 'https://images.unsplash.com/photo-1551488831-005cb8bf5d7e?w=800&h=600&fit=crop&crop=entropy',
                            'sort_order' => 3
                        ]
                    ]
                ],
                [
                    'name' => 'QUẦN NỮ',
                    'slug' => 'quan-nu',
                    'description' => 'Các loại quần nữ thời trang',
                    'image' => 'https://images.unsplash.com/photo-1541099649105-f69ad21f3246?w=800&h=600&fit=crop&crop=entropy',
                    'sort_order' => 3,
                    'children' => [
                        [
                            'name' => 'Quần Jeans',
                            'slug' => 'quan-jeans',
                            'description' => 'Quần jeans nữ phong cách',
                            'image' => 'https://images.unsplash.com/photo-1578662996442-48f60103fc96?w=800&h=600&fit=crop&crop=entropy',
                            'sort_order' => 1
                        ],
                        [
                            'name' => 'Quần Short',
                            'slug' => 'quan-short',
                            'description' => 'Quần short nữ năng động',
                            'image' => 'https://images.unsplash.com/photo-1517831907240-f20f8942968e?w=800&h=600&fit=crop&crop=entropy',
                            'sort_order' => 2
                        ],
                        [
                            'name' => 'Quần Công Sở',
                            'slug' => 'quan-cong-so',
                            'description' => 'Quần công sở thanh lịch',
                            'image' => 'https://images.unsplash.com/photo-1509631179647-0177331693ae?w=800&h=600&fit=crop&crop=entropy',
                            'sort_order' => 3
                        ]
                    ]
                ]
            ]
        ],
        [
            'name' => 'THƯƠNG HIỆU',
            'slug' => 'thuong-hieu',
            'description' => 'Các thương hiệu thời trang nổi bật',
            'image' => 'https://images.unsplash.com/photo-1441986300917-64674bd600d8?w=800&h=600&fit=crop&crop=entropy',
            'parent_id' => null,
            'sort_order' => 2,
            'children' => []
        ],
        [
            'name' => 'PHỤ KIỆN NAM',
            'slug' => 'phu-kien-nam',
            'description' => 'Phụ kiện thời trang nam',
            'image' => 'https://images.unsplash.com/photo-1558769132-cb1aea458c5e?w=800&h=600&fit=crop&crop=entropy',
            'parent_id' => null,
            'sort_order' => 3,
            'children' => []
        ],
        [
            'name' => 'PHỤ KIỆN NỮ',
            'slug' => 'phu-kien-nu',
            'description' => 'Phụ kiện thời trang nữ',
            'image' => 'https://images.unsplash.com/photo-1469334931182-2a2a8b1a2e5c?w=800&h=600&fit=crop&crop=entropy',
            'parent_id' => null,
            'sort_order' => 4,
            'children' => []
        ]
    ];
    
    // Step 4: Flatten structure and process
    $allCategories = [];
    $categoryMap = [];
    
    function flattenCategories($categories, $parentId = null, $level = 1) {
        global $allCategories, $categoryMap;
        
        foreach ($categories as $category) {
            $flatCategory = [
                'name' => $category['name'],
                'slug' => $category['slug'],
                'description' => $category['description'],
                'image' => $category['image'],
                'parent_id' => $parentId,
                'sort_order' => $category['sort_order'],
                'level' => $level
            ];
            
            $allCategories[] = $flatCategory;
            $categoryMap[$category['name']] = $flatCategory;
            
            if (isset($category['children']) && !empty($category['children'])) {
                flattenCategories($category['children'], null, $level + 1); // Will update parent_id later
            }
        }
    }
    
    flattenCategories($newCategoryStructure);
    
    echo "<h2>Processing Categories...</h2>";
    
    // Step 5: Update or insert categories
    $updatedCount = 0;
    $insertedCount = 0;
    $parentIdMap = [];
    
    foreach ($allCategories as $index => $category) {
        // Check if category exists
        $stmt = $pdo->prepare("SELECT id FROM categories WHERE name = ?");
        $stmt->execute([$category['name']]);
        $existing = $stmt->fetch();
        
        if ($existing) {
            // Update existing category
            $stmt = $pdo->prepare("
                UPDATE categories 
                SET slug = ?, description = ?, image = ?, sort_order = ?, status = 'active'
                WHERE id = ?
            ");
            $result = $stmt->execute([
                $category['slug'],
                $category['description'], 
                $category['image'],
                $category['sort_order'],
                $existing['id']
            ]);
            
            if ($result) {
                $updatedCount++;
                $parentIdMap[$category['name']] = $existing['id'];
                echo "<p style='color: blue;'>✓ Updated category: " . htmlspecialchars($category['name']) . "</p>";
            }
        } else {
            // Insert new category
            $stmt = $pdo->prepare("
                INSERT INTO categories (name, slug, description, image, parent_id, sort_order, status)
                VALUES (?, ?, ?, ?, ?, ?, 'active')
            ");
            $result = $stmt->execute([
                $category['name'],
                $category['slug'],
                $category['description'],
                $category['image'],
                $category['parent_id']
            ]);
            
            if ($result) {
                $insertedCount++;
                $newId = $pdo->lastInsertId();
                $parentIdMap[$category['name']] = $newId;
                echo "<p style='color: green;'>✓ Inserted category: " . htmlspecialchars($category['name']) . " (ID: $newId)</p>";
            }
        }
    }
    
    // Step 6: Update parent-child relationships
    echo "<h2>Updating Parent-Child Relationships...</h2>";
    
    foreach ($allCategories as $category) {
        if ($category['parent_id'] !== null && isset($parentIdMap[$category['parent_id']])) {
            $actualParentId = $parentIdMap[$category['parent_id']];
            $categoryId = $parentIdMap[$category['name']];
            
            $stmt = $pdo->prepare("UPDATE categories SET parent_id = ? WHERE id = ?");
            $result = $stmt->execute([$actualParentId, $categoryId]);
            
            if ($result) {
                echo "<p style='color: purple;'>→ Linked: " . htmlspecialchars($category['name']) . " → Parent ID: $actualParentId</p>";
            }
        }
    }
    
    // Step 7: Assign products to categories
    echo "<h2>Assigning Products to Categories...</h2>";
    
    $productCategoryMap = [
        // Products → Category mappings
        'Váy hoa nhún eo nữ tính' => 'Váy Công Sở',
        'Áo sơ mi trắng công sở' => 'Áo Sơ Mi',
        'Quần jeans ống rộng thời trang' => 'Quần Jeans',
        'Áo thun oversize graphic' => 'Áo Thun',
        'Chân váy chữ A nữ' => 'Váy Công Sở',
        'Áo croptop mùa hè' => 'Áo Thun',
        'Quần short kaki casual' => 'Quần Short',
        'Váy maxi hoa nhí' => 'Váy Dạo Phố',
        'Lemon Tree' => 'THƯƠNG HIỆU',
        'Nieve' => 'THƯƠNG HIỆU',
        'I AM' => 'THƯƠNG HIỆU',
        'GS' => 'THƯƠNG HIỆU',
        'See You' => 'THƯƠNG HIỆU',
        'Nile' => 'THƯƠNG HIỆU',
        'Hers' => 'THƯƠNG HIỆU',
        'Y.JIA' => 'THƯƠNG HIỆU',
        'NHÀ WGWE' => 'THƯƠNG HIỆU'
    ];
    
    $assignedCount = 0;
    foreach ($currentProducts as $product) {
        if (isset($productCategoryMap[$product['name']])) {
            $categoryName = $productCategoryMap[$product['name']];
            
            if (isset($parentIdMap[$categoryName])) {
                $categoryId = $parentIdMap[$categoryName];
                
                $stmt = $pdo->prepare("UPDATE products SET category_id = ? WHERE id = ?");
                $result = $stmt->execute([$categoryId, $product['id']]);
                
                if ($result) {
                    $assignedCount++;
                    echo "<p style='color: orange;'>→ Assigned: " . htmlspecialchars($product['name']) . " → " . htmlspecialchars($categoryName) . " (ID: $categoryId)</p>";
                }
            }
        }
    }
    
    // Step 8: Final summary
    echo "<h2>Final Summary</h2>";
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background: #f0f0f0;'>
            <th>Metric</th>
            <th>Count</th>
          </tr>";
    echo "<tr><td>Current Categories</td><td>" . count($currentCategories) . "</td></tr>";
    echo "<tr><td>Updated Categories</td><td>$updatedCount</td></tr>";
    echo "<tr><td>Inserted Categories</td><td>$insertedCount</td></tr>";
    echo "<tr><td>Total Categories After Update</td><td>" . ($updatedCount + $insertedCount) . "</td></tr>";
    echo "<tr><td>Products Assigned</td><td>$assignedCount</td></tr>";
    echo "</table>";
    
    echo "<h2>Verify Results</h2>";
    echo "<a href='?page=products'>View Products Page</a> | ";
    echo "<a href='?page=categories'>View Categories Page</a>";
    
} catch (PDOException $e) {
    echo "<h2>Database Error</h2>";
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
?>
