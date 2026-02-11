<?php
/**
 * Categories Seeder
 * Seeds categories table with data from JSON files
 */

require_once __DIR__ . '/BaseSeeder.php';

class CategoriesSeeder extends BaseSeeder {
    protected $tableName = 'categories';
    
    public function run() {
        echo "🌱 Seeding categories table...\n";
        
        // Truncate table first
        $this->truncateTable();
        
        // Load fake data
        $fakeData = $this->loadJsonData(__DIR__ . '/../../app/views/admin/data/fake_data.json');
        
        $insertedCount = 0;
        
        if (isset($fakeData['categories'])) {
            foreach ($fakeData['categories'] as $category) {
                $categoryData = [
                    'name' => $category['name'],
                    'slug' => $category['slug'] ?? $this->generateSlug($category['name']),
                    'description' => $category['description'] ?? null,
                    'status' => $category['status'] ?? 'active',
                    'sort_order' => $category['sort_order'] ?? 0,
                    'created_at' => $this->formatDateTime($category['created_at'] ?? null),
                    'updated_at' => $this->formatDateTime($category['created_at'] ?? null)
                ];
                
                $this->insertData('categories', $categoryData);
                $insertedCount++;
                echo "   ✓ Inserted category: {$category['name']}\n";
            }
        }
        
        echo "   📊 Total categories inserted: {$insertedCount}\n\n";
    }
}