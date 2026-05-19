<?php
/**
 * Test Hero Section Toggle Status
 */

define('THUONGLO_INIT', true);
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/core/database.php';
require_once __DIR__ . '/app/models/HeroSectionModel.php';

echo "<h1>Test Hero Section Toggle Status</h1>";

try {
    // Initialize database
    $database = Database::getInstance();
    $connection = $database->getPdo();
    
    if (!$connection) {
        throw new Exception("Database connection failed");
    }
    
    $heroModel = new HeroSectionModel();
    
    echo "<h2>1. Current Status</h2>";
    
    // Get current hero section
    $currentHero = $heroModel->getActive();
    if ($currentHero) {
        echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
        echo "<tr><th>ID</th><th>Title</th><th>Active Status</th><th>Will Show on Homepage</th></tr>";
        $status = $currentHero['is_active'] ? "<span style='color: green;'>Active (1)</span>" : "<span style='color: red;'>Inactive (0)</span>";
        $willShow = $currentHero['is_active'] ? "<span style='color: green;'>✓ Yes</span>" : "<span style='color: red;'>✗ No (Fallback will show)</span>";
        echo "<tr>
            <td>{$currentHero['id']}</td>
            <td>" . htmlspecialchars(strip_tags($currentHero['title_main'])) . "</td>
            <td>$status</td>
            <td>$willShow</td>
        </tr>";
        echo "</table>";
    } else {
        echo "<p style='color: red;'>No hero section found</p>";
    }
    
    echo "<h2>2. Test Toggle Function</h2>";
    
    if ($currentHero) {
        $originalStatus = $currentHero['is_active'];
        $newStatus = $originalStatus ? 0 : 1;
        
        echo "<p>Current status: " . ($originalStatus ? "Active" : "Inactive") . "</p>";
        echo "<p>Will toggle to: " . ($newStatus ? "Active" : "Inactive") . "</p>";
        
        // Test the toggle
        echo "<h3>Testing toggleStatus() method...</h3>";
        $result = $heroModel->toggleStatus($currentHero['id']);
        
        if ($result) {
            echo "<p style='color: green;'>✓ Toggle executed successfully</p>";
            
            // Check the result
            $updatedHero = $heroModel->find($currentHero['id']);
            if ($updatedHero) {
                $updatedStatus = $updatedHero['is_active'];
                echo "<p>Updated status: " . ($updatedStatus ? "Active (1)" : "Inactive (0)") . "</p>";
                
                if ($updatedStatus != $originalStatus) {
                    echo "<p style='color: green;'>✓ Status changed correctly!</p>";
                    
                    // Toggle back to original
                    echo "<p>Toggling back to original status...</p>";
                    $heroModel->toggleStatus($currentHero['id']);
                    echo "<p style='color: green;'>✓ Restored to original status</p>";
                } else {
                    echo "<p style='color: red;'>✗ Status did not change!</p>";
                }
            }
        } else {
            echo "<p style='color: red;'>✗ Toggle failed!</p>";
        }
    }
    
    echo "<h2>3. Homepage Logic Test</h2>";
    
    // Simulate homepage logic
    echo "<h3>Simulating home.php logic:</h3>";
    
    $heroSection = $heroModel->getWithButtons();
    echo "<p>getWithButtons() returned: " . ($heroSection ? "Hero section found" : "No hero section") . "</p>";
    
    if ($heroSection) {
        echo "<p>Hero section active status: " . ($heroSection['is_active'] ? "Active" : "Inactive") . "</p>";
        echo "<p>Condition (heroSection && heroSection['is_active']): " . (($heroSection && $heroSection['is_active']) ? "TRUE - Will show custom hero" : "FALSE - Will show fallback hero") . "</p>";
    }
    
    echo "<h2>4. AJAX Test</h2>";
    echo "<p>To test the AJAX toggle (like the admin panel):</p>";
    echo "<ol>";
    echo "<li>Go to admin panel: <code>?page=admin&module=hero-section</code></li>";
    echo "<li>Click the eye icon (toggle status) next to the hero section</li>";
    echo "<li>Check the homepage to see if it shows the fallback hero section</li>";
    echo "<li>Click the eye icon again to restore</li>";
    echo "</ol>";
    
    echo "<h2>5. Expected Behavior</h2>";
    echo "<div style='background: #f0f8ff; padding: 15px; border-left: 4px solid #356DF1;'>";
    echo "<h4>When Hero Section is Active (is_active = 1):</h4>";
    echo "<ul>";
    echo "<li>Homepage shows custom hero section with your content</li>";
    echo "<li>Admin panel shows green 'Đang hiện' badge</li>";
    echo "<li>Toggle button shows eye-slash icon (Tạm ẩn)</li>";
    echo "</ul>";
    
    echo "<h4>When Hero Section is Inactive (is_active = 0):</h4>";
    echo "<ul>";
    echo "<li>Homepage shows fallback hero section (hardcoded default)</li>";
    echo "<li>Admin panel shows gray 'Đang ẩn' badge</li>";
    echo "<li>Toggle button shows eye icon (Hiển thị)</li>";
    echo "</ul>";
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
echo "<p><small>Test completed at: " . date('Y-m-d H:i:s') . "</small></p>";
?>
