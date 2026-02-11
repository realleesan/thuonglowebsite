<?php
/**
 * Contacts Seeder
 * Seeds contacts table with data from JSON files
 */

require_once __DIR__ . '/BaseSeeder.php';

class ContactsSeeder extends BaseSeeder {
    protected $tableName = 'contacts';
    
    public function run() {
        echo "🌱 Seeding contacts table...\n";
        
        // Truncate table first
        $this->truncateTable();
        
        // Load fake data
        $fakeData = $this->loadJsonData(__DIR__ . '/../../app/views/admin/data/fake_data.json');
        
        $insertedCount = 0;
        
        if (isset($fakeData['contacts'])) {
            foreach ($fakeData['contacts'] as $contact) {
                $contactData = [
                    'name' => $contact['name'],
                    'email' => $contact['email'],
                    'phone' => $contact['phone'] ?? null,
                    'subject' => $contact['subject'],
                    'message' => $contact['message'],
                    'status' => $contact['status'] ?? 'new',
                    'priority' => $contact['priority'] ?? 'normal',
                    'ip_address' => '127.0.0.1',
                    'created_at' => $this->formatDateTime($contact['created_at'] ?? null),
                    'updated_at' => $this->formatDateTime($contact['created_at'] ?? null)
                ];
                
                $this->insertData('contacts', $contactData);
                $insertedCount++;
                echo "   ✓ Inserted contact: {$contact['name']}\n";
            }
        }
        
        echo "   📊 Total contacts inserted: {$insertedCount}\n\n";
    }
}