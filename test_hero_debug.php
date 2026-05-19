<?php
/**
 * Test script to check Hero Section usage
 */

define('THUONGLO_INIT', true);
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/core/database.php';
require_once __DIR__ . '/app/models/HeroSectionModel.php';
require_once __DIR__ . '/app/models/HeroButtonModel.php';

echo "<h1>Hero Section Debug Test</h1>";

try {
    // Initialize database using Database singleton
    $database = Database::getInstance();
    $connection = $database->getPdo();
    
    if (!$connection) {
        throw new Exception("Database connection failed");
    }
    
    echo "<h2>1. Database Connection</h2>";
    echo "<p style='color: green;'>✓ Database connected successfully</p>";
    
    // Check hero_sections table
    echo "<h2>2. Hero Sections Table Check</h2>";
    $result = $connection->query("SHOW TABLES LIKE 'hero_sections'");
    if ($result && $result->rowCount() > 0) {
        echo "<p style='color: green;'>✓ Table 'hero_sections' exists</p>";
        
        // Count records
        $countResult = $connection->query("SELECT COUNT(*) as count FROM hero_sections");
        $count = $countResult->fetchColumn();
        echo "<p>Total hero sections: <strong>$count</strong></p>";
        
        // Show all hero sections
        $sectionsResult = $connection->query("SELECT * FROM hero_sections ORDER BY id DESC");
        echo "<table border='1' cellpadding='5' style='border-collapse: collapse; margin-top: 10px;'>";
        echo "<tr><th>ID</th><th>Title</th><th>Active</th><th>Created</th></tr>";
        while ($row = $sectionsResult->fetch(PDO::FETCH_ASSOC)) {
            $status = $row['is_active'] ? "<span style='color: green;'>Active</span>" : "<span style='color: red;'>Inactive</span>";
            echo "<tr>
                <td>{$row['id']}</td>
                <td>" . htmlspecialchars($row['title_main'] ?? 'N/A') . "</td>
                <td>$status</td>
                <td>{$row['created_at']}</td>
            </tr>";
        }
        echo "</table>";
    } else {
        echo "<p style='color: red;'>✗ Table 'hero_sections' does not exist</p>";
    }
    
    // Check hero_buttons table
    echo "<h2>3. Hero Buttons Table Check</h2>";
    $result = $connection->query("SHOW TABLES LIKE 'hero_buttons'");
    if ($result && $result->rowCount() > 0) {
        echo "<p style='color: green;'>✓ Table 'hero_buttons' exists</p>";
        
        // Count records
        $countResult = $connection->query("SELECT COUNT(*) as count FROM hero_buttons");
        $count = $countResult->fetchColumn();
        echo "<p>Total hero buttons: <strong>$count</strong></p>";
        
        // Show all buttons
        $buttonsResult = $connection->query("SELECT hb.*, hs.title_main FROM hero_buttons hb LEFT JOIN hero_sections hs ON hb.hero_section_id = hs.id ORDER BY hb.hero_section_id, hb.sort_order");
        echo "<table border='1' cellpadding='5' style='border-collapse: collapse; margin-top: 10px;'>";
        echo "<tr><th>ID</th><th>Hero Section ID</th><th>Button Text</th><th>URL</th><th>Active</th><th>Sort Order</th></tr>";
        while ($row = $buttonsResult->fetch(PDO::FETCH_ASSOC)) {
            $status = $row['is_active'] ? "<span style='color: green;'>Active</span>" : "<span style='color: red;'>Inactive</span>";
            echo "<tr>
                <td>{$row['id']}</td>
                <td>{$row['hero_section_id']}</td>
                <td>" . htmlspecialchars($row['button_text'] ?? 'N/A') . "</td>
                <td>" . htmlspecialchars($row['button_url'] ?? 'N/A') . "</td>
                <td>$status</td>
                <td>{$row['sort_order']}</td>
            </tr>";
        }
        echo "</table>";
    } else {
        echo "<p style='color: red;'>✗ Table 'hero_buttons' does not exist</p>";
    }
    
    // Test HeroSectionModel
    echo "<h2>4. HeroSectionModel Test</h2>";
    $heroModel = new HeroSectionModel();
    
    // Test getActive()
    $activeHero = $heroModel->getActive();
    if ($activeHero) {
        echo "<p style='color: green;'>✓ Active hero section found</p>";
        echo "<table border='1' cellpadding='5' style='border-collapse: collapse; margin-top: 10px;'>";
        echo "<tr><th>Field</th><th>Value</th></tr>";
        foreach ($activeHero as $key => $value) {
            if (is_array($value)) {
                $value = print_r($value, true);
            }
            echo "<tr><td>$key</td><td>" . htmlspecialchars($value ?? 'NULL') . "</td></tr>";
        }
        echo "</table>";
    } else {
        echo "<p style='color: orange;'>⚠ No active hero section found</p>";
    }
    
    // Test getWithButtons()
    $heroWithButtons = $heroModel->getWithButtons();
    if ($heroWithButtons) {
        echo "<h3>Hero Section with Buttons</h3>";
        echo "<p><strong>Title:</strong> " . htmlspecialchars($heroWithButtons['title_main'] ?? 'N/A') . "</p>";
        echo "<p><strong>Subtitle:</strong> " . htmlspecialchars($heroWithButtons['subtitle'] ?? 'N/A') . "</p>";
        echo "<p><strong>Buttons Count:</strong> " . count($heroWithButtons['buttons'] ?? []) . "</p>";
        
        if (!empty($heroWithButtons['buttons'])) {
            echo "<h4>Buttons:</h4>";
            echo "<table border='1' cellpadding='5' style='border-collapse: collapse; margin-top: 10px;'>";
            echo "<tr><th>Text</th><th>URL</th><th>Style</th><th>Active</th></tr>";
            foreach ($heroWithButtons['buttons'] as $button) {
                $status = $button['is_active'] ? "<span style='color: green;'>Active</span>" : "<span style='color: red;'>Inactive</span>";
                echo "<tr>
                    <td>" . htmlspecialchars($button['button_text'] ?? 'N/A') . "</td>
                    <td>" . htmlspecialchars($button['button_url'] ?? 'N/A') . "</td>
                    <td>" . htmlspecialchars($button['button_style'] ?? 'N/A') . "</td>
                    <td>$status</td>
                </tr>";
            }
            echo "</table>";
        }
    } else {
        echo "<p style='color: orange;'>⚠ No hero section with buttons found</p>";
    }
    
    // Check home.php file
    echo "<h2>5. Home.php Hero Section Usage</h2>";
    $homeFile = __DIR__ . '/app/views/home/home.php';
    if (file_exists($homeFile)) {
        echo "<p style='color: green;'>✓ home.php file exists</p>";
        
        $homeContent = file_get_contents($homeFile);
        
        // Check for HeroSectionModel usage
        if (strpos($homeContent, 'HeroSectionModel') !== false) {
            echo "<p style='color: green;'>✓ HeroSectionModel is used in home.php</p>";
        } else {
            echo "<p style='color: red;'>✗ HeroSectionModel is NOT used in home.php</p>";
        }
        
        // Check for getWithButtons usage
        if (strpos($homeContent, 'getWithButtons') !== false) {
            echo "<p style='color: green;'>✓ getWithButtons() is called in home.php</p>";
        } else {
            echo "<p style='color: red;'>✗ getWithButtons() is NOT called in home.php</p>";
        }
        
        // Check for hero section rendering
        if (strpos($homeContent, 'hero-section') !== false) {
            echo "<p style='color: green;'>✓ Hero section HTML is present in home.php</p>";
        } else {
            echo "<p style='color: red;'>✗ Hero section HTML is NOT present in home.php</p>";
        }
        
        // Show the hero section code block
        $startPos = strpos($homeContent, '// Get Hero Section from database');
        $endPos = strpos($homeContent, '} catch (Exception $e)', $startPos);
        if ($startPos !== false && $endPos !== false) {
            $heroCode = substr($homeContent, $startPos, $endPos - $startPos + 20);
            echo "<h4>Hero Section Code Block:</h4>";
            echo "<pre style='background: #f5f5f5; padding: 10px; border: 1px solid #ccc;'>" . htmlspecialchars($heroCode) . "</pre>";
        }
    } else {
        echo "<p style='color: red;'>✗ home.php file does not exist</p>";
    }
    
    echo "<h2>6. Summary</h2>";
    echo "<div style='background: #f0f8ff; padding: 15px; border-left: 4px solid #356DF1;'>";
    echo "<p><strong>Current Status:</strong></p>";
    echo "<ul>";
    echo "<li>Database tables exist: " . ($result && $result->rowCount() > 0 ? "✓" : "✗") . "</li>";
    echo "<li>Hero sections available: " . ($count ?? 0) . "</li>";
    echo "<li>Active hero section: " . ($activeHero ? "✓ Found" : "✗ None") . "</li>";
    echo "<li>Home.php integration: " . (file_exists($homeFile) && strpos(file_get_contents($homeFile), 'HeroSectionModel') !== false ? "✓ Integrated" : "✗ Not integrated") . "</li>";
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
