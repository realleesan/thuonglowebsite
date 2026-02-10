<?php
/**
 * Settings Seeder
 * Seeds settings table with data from JSON files
 */

require_once __DIR__ . '/BaseSeeder.php';

class SettingsSeeder extends BaseSeeder {
    protected $tableName = 'settings';
    
    public function run() {
        echo "🌱 Seeding settings table...\n";
        
        // Truncate table first
        $this->truncateTable();
        
        // Load fake data
        $fakeData = $this->loadJsonData(__DIR__ . '/../../app/views/admin/data/fake_data.json');
        
        $insertedCount = 0;
        
        if (isset($fakeData['settings'])) {
            foreach ($fakeData['settings'] as $setting) {
                $settingData = [
                    'key' => $setting['key'],
                    'value' => $setting['value'],
                    'type' => $setting['type'] ?? 'text',
                    'group' => $this->determineGroup($setting['key']),
                    'description' => $setting['description'] ?? null,
                    'is_public' => $this->isPublicSetting($setting['key']),
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ];
                
                $this->insertData('settings', $settingData);
                $insertedCount++;
                echo "   ✓ Inserted setting: {$setting['key']}\n";
            }
        }
        
        echo "   📊 Total settings inserted: {$insertedCount}\n\n";
    }
    
    /**
     * Determine setting group based on key
     */
    private function determineGroup($key) {
        if (strpos($key, 'site_') === 0) {
            return 'site';
        } elseif (strpos($key, 'contact_') === 0) {
            return 'contact';
        } elseif (strpos($key, 'facebook') !== false || strpos($key, 'social') !== false) {
            return 'social';
        } elseif (strpos($key, 'commission') !== false || strpos($key, 'affiliate') !== false) {
            return 'affiliate';
        } else {
            return 'general';
        }
    }
    
    /**
     * Determine if setting should be public
     */
    private function isPublicSetting($key) {
        $publicSettings = [
            'site_name',
            'site_description',
            'contact_email',
            'contact_phone',
            'facebook_url'
        ];
        
        return in_array($key, $publicSettings);
    }
}