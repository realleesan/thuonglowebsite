<?php
/**
 * Script dọn dẹp các file JSON cũ sau khi chuyển đổi sang SQL
 */

echo "=== DỌN DẸP CÁC FILE JSON CŨ ===\n\n";

// Tạo thư mục backup
$backupDir = 'backups/json_backup_' . date('Y-m-d_H-i-s');
if (!is_dir('backups')) {
    mkdir('backups', 0755, true);
}
mkdir($backupDir, 0755, true);

echo "📁 Tạo thư mục backup: $backupDir\n\n";

// Danh sách các file JSON cần backup và xóa
$jsonFiles = [
    'app/views/admin/data/fake_data.json',
    'app/views/auth/data/demo_accounts.json',
    'app/views/users/data/user_fake_data.json'
];

$backedUp = 0;
$deleted = 0;

foreach ($jsonFiles as $file) {
    if (file_exists($file)) {
        // Backup file
        $backupFile = $backupDir . '/' . basename($file);
        if (copy($file, $backupFile)) {
            echo "✅ Đã backup: $file -> $backupFile\n";
            $backedUp++;
            
            // Xóa file gốc
            if (unlink($file)) {
                echo "🗑️  Đã xóa: $file\n";
                $deleted++;
            } else {
                echo "❌ Không thể xóa: $file\n";
            }
        } else {
            echo "❌ Không thể backup: $file\n";
        }
    } else {
        echo "⚠️  File không tồn tại: $file\n";
    }
    echo "\n";
}

// Xóa các thư mục data trống
$dataDirs = [
    'app/views/admin/data',
    'app/views/auth/data',
    'app/views/users/data'
];

foreach ($dataDirs as $dir) {
    if (is_dir($dir)) {
        $files = scandir($dir);
        $files = array_diff($files, ['.', '..']);
        
        if (empty($files)) {
            if (rmdir($dir)) {
                echo "🗑️  Đã xóa thư mục trống: $dir\n";
            } else {
                echo "❌ Không thể xóa thư mục: $dir\n";
            }
        } else {
            echo "⚠️  Thư mục không trống: $dir (còn " . count($files) . " file)\n";
        }
    }
}

echo "\n=== KẾT QUẢ ===\n";
echo "Đã backup: $backedUp file\n";
echo "Đã xóa: $deleted file\n";
echo "Backup location: $backupDir\n";
echo "=== HOÀN THÀNH ===\n";