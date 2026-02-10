<?php
/**
 * Products Seeder
 * Seeds products table with data from JSON files
 */

require_once __DIR__ . '/BaseSeeder.php';

class ProductsSeeder extends BaseSeeder {
    protected $tableName = 'products';
    
    public function run() {
        echo "🌱 Seeding products table...\n";
        
        // Truncate table first
        $this->truncateTable();
        
        // Load fake data
        $fakeData = $this->loadJsonData(__DIR__ . '/../../app/views/admin/data/fake_data.json');
        
        $insertedCount = 0;
        
        if (isset($fakeData['products'])) {
            foreach ($fakeData['products'] as $product) {
                // Determine product type based on category or name
                $type = $this->determineProductType($product['name']);
                
                $productData = [
                    'name' => $product['name'],
                    'slug' => $this->generateSlug($product['name']),
                    'category_id' => $product['category_id'],
                    'price' => $product['price'],
                    'stock' => $product['stock'] ?? 0,
                    'status' => $product['status'] ?? 'active',
                    'type' => $type,
                    'description' => $product['description'] ?? null,
                    'short_description' => $this->generateShortDescription($product['description'] ?? ''),
                    'image' => $product['image'] ?? null,
                    'featured' => $product['featured'] ?? false,
                    'digital' => true, // Most products are digital
                    'downloadable' => true,
                    'views' => $product['views'] ?? 0,
                    'sales_count' => $product['sales_count'] ?? 0,
                    'created_at' => $this->formatDateTime($product['created_at'] ?? null),
                    'updated_at' => $this->formatDateTime($product['created_at'] ?? null)
                ];
                
                $this->insertData('products', $productData);
                $insertedCount++;
                echo "   ✓ Inserted product: {$product['name']}\n";
            }
        }
        
        echo "   📊 Total products inserted: {$insertedCount}\n\n";
    }
    
    /**
     * Determine product type based on name/category
     */
    private function determineProductType($name) {
        $name = strtolower($name);
        
        if (strpos($name, 'data') !== false || strpos($name, 'nguồn hàng') !== false) {
            return 'data_nguon_hang';
        } elseif (strpos($name, 'khóa học') !== false || strpos($name, 'course') !== false) {
            return 'khoa_hoc';
        } elseif (strpos($name, 'tool') !== false || strpos($name, 'phần mềm') !== false) {
            return 'tool';
        } elseif (strpos($name, 'dịch vụ') !== false || strpos($name, 'service') !== false) {
            return 'dich_vu';
        } else {
            return 'data_nguon_hang'; // Default
        }
    }
    
    /**
     * Generate short description from full description
     */
    private function generateShortDescription($description) {
        if (empty($description)) {
            return null;
        }
        
        // Take first 150 characters
        $short = substr($description, 0, 150);
        
        // Find last complete word
        $lastSpace = strrpos($short, ' ');
        if ($lastSpace !== false) {
            $short = substr($short, 0, $lastSpace);
        }
        
        return $short . '...';
    }
}