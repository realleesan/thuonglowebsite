<?php
/**
 * Events Seeder
 * Seeds events table with data from JSON files
 */

require_once __DIR__ . '/BaseSeeder.php';

class EventsSeeder extends BaseSeeder {
    protected $tableName = 'events';
    
    public function run() {
        echo "🌱 Seeding events table...\n";
        
        // Truncate table first
        $this->truncateTable();
        
        // Load fake data
        $fakeData = $this->loadJsonData(__DIR__ . '/../../app/views/admin/data/fake_data.json');
        
        $insertedCount = 0;
        
        if (isset($fakeData['events'])) {
            foreach ($fakeData['events'] as $event) {
                $eventData = [
                    'title' => $event['title'],
                    'slug' => $event['slug'] ?? $this->generateSlug($event['title']),
                    'description' => $event['description'] ?? null,
                    'image' => $event['image'] ?? null,
                    'start_date' => $this->formatDateTime($event['start_date']),
                    'end_date' => $this->formatDateTime($event['end_date']),
                    'location' => $event['location'] ?? null,
                    'price' => $event['price'] ?? 0,
                    'max_participants' => $event['max_participants'] ?? null,
                    'current_participants' => $event['current_participants'] ?? 0,
                    'status' => $event['status'] ?? 'upcoming',
                    'featured' => $event['featured'] ?? false,
                    'organizer_id' => 1, // Admin user
                    'meta_title' => $event['title'],
                    'meta_description' => $event['description'] ?? null,
                    'created_at' => $this->formatDateTime($event['created_at'] ?? null),
                    'updated_at' => $this->formatDateTime($event['created_at'] ?? null)
                ];
                
                $this->insertData('events', $eventData);
                $insertedCount++;
                echo "   ✓ Inserted event: {$event['title']}\n";
            }
        }
        
        echo "   📊 Total events inserted: {$insertedCount}\n\n";
    }
}