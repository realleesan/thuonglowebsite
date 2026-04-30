<?php
/**
 * Script reset quan hệ cha-con của danh mục
 * Đặt tất cả danh mục về cấp 1 (parent_id = NULL)
 * 
 * Usage: Chạy trực tiếp file này trong browser hoặc CLI
 */

require_once __DIR__ . '/core/database.php';

echo "=== RESET DANH MỤC VỀ CẤP 1 ===\n\n";

try {
    $db = Database::getInstance();
    
    // Lấy tổng số danh mục trước khi reset
    $countBefore = $db->query("SELECT COUNT(*) as total FROM categories");
    $totalCategories = $countBefore[0]['total'] ?? 0;
    
    // Đếm số danh mục có parent_id
    $withParent = $db->query("SELECT COUNT(*) as count FROM categories WHERE parent_id IS NOT NULL");
    $categoriesWithParent = $withParent[0]['count'] ?? 0;
    
    echo "Tổng số danh mục: {$totalCategories}\n";
    echo "Số danh mục có cha (sẽ bị reset): {$categoriesWithParent}\n\n";
    
    if ($categoriesWithParent === 0) {
        echo "Không có danh mục nào có quan hệ cha-con. Không cần reset.\n";
        exit;
    }
    
    // Backup trước khi reset (optional - lưu vào log)
    $categoriesWithRelations = $db->query("
        SELECT c.id, c.name, c.parent_id, p.name as parent_name 
        FROM categories c 
        LEFT JOIN categories p ON c.parent_id = p.id 
        WHERE c.parent_id IS NOT NULL
    ");
    
    echo "Danh mục sẽ bị reset:\n";
    foreach ($categoriesWithRelations as $cat) {
        echo "  - [ID: {$cat['id']}] {$cat['name']} (cha: {$cat['parent_name']})\n";
    }
    echo "\n";
    
    // Thực hiện reset
    $result = $db->query("UPDATE categories SET parent_id = NULL");
    
    // Kiểm tra kết quả
    $afterReset = $db->query("SELECT COUNT(*) as count FROM categories WHERE parent_id IS NOT NULL");
    $remainingWithParent = $afterReset[0]['count'] ?? 0;
    
    if ($remainingWithParent === 0) {
        echo "✅ Reset thành công! Tất cả {$totalCategories} danh mục đã được đặt về cấp 1.\n";
        echo "   - Không còn quan hệ cha-con nào.\n";
        echo "   - Tất cả parent_id đã được set thành NULL.\n";
    } else {
        echo "⚠️ Có lỗi: Vẫn còn {$remainingWithParent} danh mục có parent_id.\n";
    }
    
    echo "\n=== HOÀN TẤT ===\n";
    
} catch (Exception $e) {
    echo "❌ LỖI: " . $e->getMessage() . "\n";
    exit(1);
}
