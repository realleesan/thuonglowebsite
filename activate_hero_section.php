<?php
/**
 * Activate Hero Section Script
 */

define('THUONGLO_INIT', true);
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/core/database.php';

echo "<h1>Activate Hero Section</h1>";

try {
    // Initialize database
    $database = Database::getInstance();
    $connection = $database->getPdo();
    
    if (!$connection) {
        throw new Exception("Database connection failed");
    }
    
    echo "<h2>1. Current Status</h2>";
    
    // Show current hero section
    $section = $connection->query("SELECT id, title_main, is_active FROM hero_sections ORDER BY id DESC LIMIT 1")->fetch(PDO::FETCH_ASSOC);
    
    if ($section) {
        $status = $section['is_active'] ? "<span style='color: green;'>Active</span>" : "<span style='color: red;'>Inactive</span>";
        echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
        echo "<tr><th>ID</th><th>Title</th><th>Status</th></tr>";
        echo "<tr>
            <td>{$section['id']}</td>
            <td>" . htmlspecialchars(strip_tags($section['title_main'])) . "</td>
            <td>$status</td>
        </tr>";
        echo "</table>";
        
        if ($section['is_active'] == 0) {
            echo "<h2>2. Activating Hero Section</h2>";
            
            // Update to active
            $stmt = $connection->prepare("UPDATE hero_sections SET is_active = 1, updated_at = NOW() WHERE id = ?");
            $result = $stmt->execute([$section['id']]);
            
            if ($result) {
                echo "<p style='color: green;'>✓ Hero Section ID {$section['id']} has been activated!</p>";
                
                // Verify the update
                $updated = $connection->query("SELECT is_active FROM hero_sections WHERE id = {$section['id']}")->fetchColumn();
                echo "<p>Verified status: " . ($updated ? "<span style='color: green;'>Active (1)</span>" : "<span style='color: red;'>Still Inactive (0)</span>") . "</p>";
                
                echo "<h2>3. Test Homepage Logic</h2>";
                
                // Test if homepage will show hero section
                $activeHero = $connection->query("SELECT * FROM hero_sections WHERE is_active = 1 ORDER BY id DESC LIMIT 1")->fetch(PDO::FETCH_ASSOC);
                
                if ($activeHero) {
                    echo "<p style='color: green;'>✓ Homepage will now show custom hero section!</p>";
                    echo "<p>Title: " . htmlspecialchars(strip_tags($activeHero['title_main'])) . "</p>";
                    echo "<p>Background: " . htmlspecialchars($activeHero['background_color'] ?? 'N/A') . "</p>";
                    
                    // Check buttons
                    $buttons = $connection->query("SELECT COUNT(*) FROM hero_buttons WHERE hero_section_id = {$activeHero['id']} AND is_active = 1")->fetchColumn();
                    echo "<p>Buttons: $buttons active buttons</p>";
                } else {
                    echo "<p style='color: red;'>✗ Still no active hero section found</p>";
                }
                
            } else {
                echo "<p style='color: red;'>✗ Failed to activate hero section</p>";
            }
        } else {
            echo "<p>Hero section is already active!</p>";
        }
        
    } else {
        echo "<p style='color: red;'>No hero section found!</p>";
    }
    
    echo "<h2>4. Next Steps</h2>";
    echo "<div style='background: #f0f8ff; padding: 15px; border-left: 4px solid #356DF1;'>";
    echo "<p><strong>What to do now:</strong></p>";
    echo "<ol>";
    echo "<li>✅ Hero section is now active</li>";
    echo "<li>🏠 Visit your homepage to see the custom hero section</li>";
    echo "<li>⚙️ Go to admin panel to manage: <code>?page=admin&module=hero-section</code></li>";
    echo "<li>👁️ Test the toggle status button (eye icon)</li>";
    echo "<li>🔄 Toggle should now work to hide/show hero section</li>";
    echo "</ol>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div style='background: #ffe6e6; padding: 15px; border-left: 4px solid #ff0000;'>";
    echo "<h3>Error:</h3>";
    echo "<p style='color: red;'>" . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p>File: " . htmlspecialchars($e->getFile()) . "</p>";
    echo "<p>Line: " . $e->getLine() . "</p>";
    echo "</div>";
}

echo "<hr>";
echo "<p><small>Activation completed at: " . date('Y-m-d H:i:s') . "</small></p>";
?>
