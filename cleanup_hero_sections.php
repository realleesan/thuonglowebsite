<?php
/**
 * Cleanup Hero Sections Script
 * Keep only the best hero section (ID 4) and delete others
 * Add buttons to the kept hero section
 */

define('THUONGLO_INIT', true);
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/core/database.php';

echo "<h1>Cleanup Hero Sections</h1>";
echo "<p><strong>Target:</strong> Keep Hero Section ID 4 (best one) and delete others</p>";

try {
    // Initialize database
    $database = Database::getInstance();
    $connection = $database->getPdo();
    
    if (!$connection) {
        throw new Exception("Database connection failed");
    }
    
    echo "<h2>1. Current Status</h2>";
    
    // Show current hero sections
    $sectionsResult = $connection->query("SELECT id, title_main, is_active FROM hero_sections ORDER BY id");
    echo "<table border='1' cellpadding='5' style='border-collapse: collapse; margin-top: 10px;'>";
    echo "<tr><th>ID</th><th>Title</th><th>Status</th><th>Action</th></tr>";
    
    $sectionsToDelete = [];
    while ($row = $sectionsResult->fetch(PDO::FETCH_ASSOC)) {
        $status = $row['is_active'] ? "<span style='color: green;'>Active</span>" : "<span style='color: red;'>Inactive</span>";
        $action = ($row['id'] == 4) ? "<span style='color: green;'>KEEP</span>" : "<span style='color: red;'>DELETE</span>";
        
        if ($row['id'] != 4) {
            $sectionsToDelete[] = $row['id'];
        }
        
        echo "<tr>
            <td>{$row['id']}</td>
            <td>" . htmlspecialchars($row['title_main'] ?? 'N/A') . "</td>
            <td>$status</td>
            <td>$action</td>
        </tr>";
    }
    echo "</table>";
    
    echo "<h2>2. Deleting Old Hero Sections</h2>";
    
    if (!empty($sectionsToDelete)) {
        echo "<p>Deleting hero sections: " . implode(', ', $sectionsToDelete) . "</p>";
        
        // Start transaction
        $connection->beginTransaction();
        
        try {
            foreach ($sectionsToDelete as $id) {
                // Delete buttons first
                $deleteButtons = $connection->prepare("DELETE FROM hero_buttons WHERE hero_section_id = ?");
                $deleteButtons->execute([$id]);
                
                // Delete hero section
                $deleteSection = $connection->prepare("DELETE FROM hero_sections WHERE id = ?");
                $deleteSection->execute([$id]);
                
                echo "<p style='color: green;'>✓ Deleted Hero Section ID $id and its buttons</p>";
            }
            
            $connection->commit();
            echo "<p style='color: green;'><strong>All old hero sections deleted successfully!</strong></p>";
            
        } catch (Exception $e) {
            $connection->rollback();
            throw new Exception("Failed to delete hero sections: " . $e->getMessage());
        }
    } else {
        echo "<p style='color: orange;'>No hero sections to delete</p>";
    }
    
    echo "<h2>3. Adding Buttons to Hero Section ID 4</h2>";
    
    // Check if buttons already exist for hero section 4
    $existingButtons = $connection->prepare("SELECT COUNT(*) FROM hero_buttons WHERE hero_section_id = 4");
    $existingButtons->execute();
    $buttonCount = $existingButtons->fetchColumn();
    
    if ($buttonCount == 0) {
        echo "<p>Adding buttons to Hero Section ID 4...</p>";
        
        // Insert buttons for hero section 4
        $buttons = [
            [
                'hero_section_id' => 4,
                'button_text' => 'Đăng ký miễn phí',
                'button_url' => '?page=register',
                'button_style' => 'primary',
                'background_color' => '#356DF1',
                'text_color' => '#ffffff',
                'border_color' => '#356DF1',
                'hover_background_color' => '#2855c7',
                'hover_text_color' => '#ffffff',
                'font_size' => '16px',
                'padding' => '12px 24px',
                'border_radius' => '6px',
                'sort_order' => 1,
                'is_active' => 1
            ],
            [
                'hero_section_id' => 4,
                'button_text' => 'Xem sản phẩm',
                'button_url' => '?page=products',
                'button_style' => 'secondary',
                'background_color' => 'transparent',
                'text_color' => '#356DF1',
                'border_color' => '#356DF1',
                'hover_background_color' => '#356DF1',
                'hover_text_color' => '#ffffff',
                'font_size' => '16px',
                'padding' => '12px 24px',
                'border_radius' => '6px',
                'sort_order' => 2,
                'is_active' => 1
            ]
        ];
        
        $insertButton = $connection->prepare("
            INSERT INTO hero_buttons (
                hero_section_id, button_text, button_url, button_style,
                background_color, text_color, border_color,
                hover_background_color, hover_text_color,
                font_size, padding, border_radius, sort_order, is_active,
                created_at, updated_at
            ) VALUES (
                ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW()
            )
        ");
        
        foreach ($buttons as $button) {
            $insertButton->execute([
                $button['hero_section_id'],
                $button['button_text'],
                $button['button_url'],
                $button['button_style'],
                $button['background_color'],
                $button['text_color'],
                $button['border_color'],
                $button['hover_background_color'],
                $button['hover_text_color'],
                $button['font_size'],
                $button['padding'],
                $button['border_radius'],
                $button['sort_order'],
                $button['is_active']
            ]);
            
            echo "<p style='color: green;'>✓ Added button: {$button['button_text']}</p>";
        }
        
        echo "<p style='color: green;'><strong>Buttons added successfully!</strong></p>";
        
    } else {
        echo "<p style='color: orange;'>Hero Section ID 4 already has $buttonCount buttons</p>";
    }
    
    echo "<h2>4. Final Status</h2>";
    
    // Show final hero sections
    $finalSections = $connection->query("SELECT * FROM hero_sections ORDER BY id");
    echo "<h3>Hero Sections:</h3>";
    echo "<table border='1' cellpadding='5' style='border-collapse: collapse; margin-top: 10px;'>";
    echo "<tr><th>ID</th><th>Title</th><th>Active</th><th>Created</th></tr>";
    
    while ($row = $finalSections->fetch(PDO::FETCH_ASSOC)) {
        $status = $row['is_active'] ? "<span style='color: green;'>Active</span>" : "<span style='color: red;'>Inactive</span>";
        echo "<tr>
            <td>{$row['id']}</td>
            <td>" . htmlspecialchars($row['title_main'] ?? 'N/A') . "</td>
            <td>$status</td>
            <td>{$row['created_at']}</td>
        </tr>";
    }
    echo "</table>";
    
    // Show final buttons
    $finalButtons = $connection->query("SELECT * FROM hero_buttons ORDER BY hero_section_id, sort_order");
    echo "<h3>Hero Buttons:</h3>";
    echo "<table border='1' cellpadding='5' style='border-collapse: collapse; margin-top: 10px;'>";
    echo "<tr><th>ID</th><th>Hero Section ID</th><th>Button Text</th><th>URL</th><th>Style</th><th>Active</th></tr>";
    
    while ($row = $finalButtons->fetch(PDO::FETCH_ASSOC)) {
        $status = $row['is_active'] ? "<span style='color: green;'>Active</span>" : "<span style='color: red;'>Inactive</span>";
        echo "<tr>
            <td>{$row['id']}</td>
            <td>{$row['hero_section_id']}</td>
            <td>" . htmlspecialchars($row['button_text'] ?? 'N/A') . "</td>
            <td>" . htmlspecialchars($row['button_url'] ?? 'N/A') . "</td>
            <td>" . htmlspecialchars($row['button_style'] ?? 'N/A') . "</td>
            <td>$status</td>
        </tr>";
    }
    echo "</table>";
    
    echo "<div style='background: #d4edda; padding: 15px; border-left: 4px solid #28a745; margin-top: 20px;'>";
    echo "<h3>✅ Cleanup Completed Successfully!</h3>";
    echo "<p><strong>What was done:</strong></p>";
    echo "<ul>";
    echo "<li>Deleted old hero sections: " . implode(', ', $sectionsToDelete) . "</li>";
    echo "<li>Kept Hero Section ID 4 (the best one)</li>";
    echo "<li>Added 2 buttons to Hero Section ID 4</li>";
    echo "<li>Home page will now use the clean, updated hero section</li>";
    echo "</ul>";
    echo "<p><strong>Next steps:</strong></p>";
    echo "<ul>";
    echo "<li>Visit your homepage to see the updated hero section</li>";
    echo "<li>Go to admin panel to manage hero section: <code>?page=admin&module=hero-section&action=edit&id=4</code></li>";
    echo "</ul>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div style='background: #f8d7da; padding: 15px; border-left: 4px solid #dc3545;'>";
    echo "<h3>❌ Error:</h3>";
    echo "<p style='color: red;'>" . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p>File: " . htmlspecialchars($e->getFile()) . "</p>";
    echo "<p>Line: " . $e->getLine() . "</p>";
    echo "</div>";
}

echo "<hr>";
echo "<p><small>Cleanup completed at: " . date('Y-m-d H:i:s') . "</small></p>";
?>
