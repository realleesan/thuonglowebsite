<?php
/**
 * Users Seeder
 * Seeds users table with data from JSON files
 */

require_once __DIR__ . '/BaseSeeder.php';

class UsersSeeder extends BaseSeeder {
    protected $tableName = 'users';
    
    public function run() {
        echo "🌱 Seeding users table...\n";
        
        // Truncate table first
        $this->truncateTable();
        
        // Load demo accounts
        $demoAccounts = $this->loadJsonData(__DIR__ . '/../../app/views/auth/data/demo_accounts.json');
        
        // Load fake users data
        $fakeData = $this->loadJsonData(__DIR__ . '/../../app/views/admin/data/fake_data.json');
        
        $insertedCount = 0;
        
        // Insert demo accounts first
        foreach ($demoAccounts as $role => $account) {
            $userData = [
                'name' => $account['full_name'],
                'email' => $account['email'],
                'phone' => $account['phone'],
                'password' => $this->hashPassword($account['password']),
                'role' => $account['role'],
                'status' => 'active',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            $this->insertData('users', $userData);
            $insertedCount++;
            echo "   ✓ Inserted demo {$role}: {$account['full_name']}\n";
        }
        
        // Insert fake users data
        if (isset($fakeData['users'])) {
            foreach ($fakeData['users'] as $user) {
                // Skip if email already exists (demo accounts)
                $existing = $this->db->table('users')->where('email', $user['email'])->first();
                if ($existing) {
                    echo "   ⏭  Skipping existing user: {$user['email']}\n";
                    continue;
                }
                
                $userData = [
                    'name' => $user['name'],
                    'email' => $user['email'],
                    'phone' => $user['phone'] ?? null,
                    'password' => $this->hashPassword('123456'), // Default password
                    'role' => $user['role'],
                    'status' => $user['status'],
                    'address' => $user['address'] ?? null,
                    'created_at' => $this->formatDateTime($user['created_at']),
                    'updated_at' => $this->formatDateTime($user['created_at'])
                ];
                
                $this->insertData('users', $userData);
                $insertedCount++;
                echo "   ✓ Inserted user: {$user['name']}\n";
            }
        }
        
        echo "   📊 Total users inserted: {$insertedCount}\n\n";
    }
}