<?php
/**
 * Run Sub Pages Table Migration and Seeding
 */
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/app/models/SubPageModel.php';

header('Content-Type: text/plain; charset=utf-8');

try {
    // 1. Connect to Database using native PDO
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    
    echo "--- Bat dau chay migration cho sub_pages ---\n";
    
    // 2. Read and execute the SQL file
    $sqlPath = __DIR__ . '/database/migrations/051_create_sub_pages_table.sql';
    if (!file_exists($sqlPath)) {
        throw new Exception("Khong tim thay file SQL tai: " . $sqlPath);
    }
    
    $sql = file_get_contents($sqlPath);
    $pdo->exec($sql);
    echo "1. Chay file SQL tao bang va seed thanh cong!\n";
    
    // 3. Verify seeding using SubPageModel
    $model = new SubPageModel();
    $seeded = $model->seedDefaultSubPages(true); // Force seed to ensure all values are up-to-date
    
    if ($seeded) {
        echo "2. Chay Model Seed default pages thanh cong!\n";
    } else {
        echo "2. Chu y: Model Seed co the da bo qua do bang da co du lieu.\n";
    }
    
    echo "\n--- HOAN THANH MIGRATION CONG TRINH SUB_PAGES CONG TIEN! ---\n";
    
} catch (Exception $e) {
    echo "LOI: " . $e->getMessage() . "\n";
}
