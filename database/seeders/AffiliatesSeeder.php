<?php
/**
 * Affiliates Seeder
 * Seeds affiliates table with data from JSON files
 */

require_once __DIR__ . '/BaseSeeder.php';

class AffiliatesSeeder extends BaseSeeder {
    protected $tableName = 'affiliates';
    
    public function run() {
        echo "🌱 Seeding affiliates table...\n";
        
        // Truncate table first
        $this->truncateTable();
        
        // Load fake data
        $fakeData = $this->loadJsonData(__DIR__ . '/../../app/views/admin/data/fake_data.json');
        
        $insertedCount = 0;
        
        if (isset($fakeData['affiliates'])) {
            foreach ($fakeData['affiliates'] as $affiliate) {
                $affiliateData = [
                    'user_id' => $affiliate['user_id'],
                    'referral_code' => $affiliate['referral_code'],
                    'commission_rate' => $affiliate['commission_rate'],
                    'total_sales' => $affiliate['total_sales'],
                    'total_commission' => $affiliate['total_commission'],
                    'paid_commission' => 0, // Default
                    'pending_commission' => $affiliate['total_commission'],
                    'status' => $affiliate['status'],
                    'approved_at' => $affiliate['status'] === 'active' ? $this->formatDateTime($affiliate['created_at']) : null,
                    'approved_by' => $affiliate['status'] === 'active' ? 1 : null, // Admin user
                    'created_at' => $this->formatDateTime($affiliate['created_at'] ?? null),
                    'updated_at' => $this->formatDateTime($affiliate['created_at'] ?? null)
                ];
                
                $this->insertData('affiliates', $affiliateData);
                $insertedCount++;
                echo "   ✓ Inserted affiliate: {$affiliate['referral_code']}\n";
            }
        }
        
        echo "   📊 Total affiliates inserted: {$insertedCount}\n\n";
    }
}