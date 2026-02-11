<?php
/**
 * News Seeder
 * Seeds news table with data from JSON files
 */

require_once __DIR__ . '/BaseSeeder.php';

class NewsSeeder extends BaseSeeder {
    protected $tableName = 'news';
    
    public function run() {
        echo "🌱 Seeding news table...\n";
        
        // Truncate table first
        $this->truncateTable();
        
        // Load fake data
        $fakeData = $this->loadJsonData(__DIR__ . '/../../app/views/admin/data/fake_data.json');
        
        $insertedCount = 0;
        
        if (isset($fakeData['news'])) {
            foreach ($fakeData['news'] as $news) {
                $newsData = [
                    'title' => $news['title'],
                    'slug' => $news['slug'] ?? $this->generateSlug($news['title']),
                    'excerpt' => $news['excerpt'] ?? null,
                    'content' => $news['content'] ?? null,
                    'image' => $news['image'] ?? null,
                    'status' => $news['status'] ?? 'published',
                    'featured' => $news['featured'] ?? false,
                    'author_id' => 1, // Admin user
                    'views' => $news['views'] ?? 0,
                    'meta_title' => $news['title'],
                    'meta_description' => $news['excerpt'] ?? null,
                    'published_at' => $this->formatDateTime($news['created_at'] ?? null),
                    'created_at' => $this->formatDateTime($news['created_at'] ?? null),
                    'updated_at' => $this->formatDateTime($news['created_at'] ?? null)
                ];
                
                $this->insertData('news', $newsData);
                $insertedCount++;
                echo "   ✓ Inserted news: {$news['title']}\n";
            }
        }
        
        echo "   📊 Total news inserted: {$insertedCount}\n\n";
    }
}