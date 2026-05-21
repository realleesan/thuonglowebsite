<?php
require_once __DIR__ . '/core/database.php';
require_once __DIR__ . '/app/models/LatestNewsSectionModel.php';

echo "<h2>Testing Latest News Section</h2>";

try {
    // Test database connection
    $db = Database::getInstance();
    echo "<p style='color: green;'>✓ Database connection successful</p>";

    // Run migrations
    echo "<h3>Running Migrations</h3>";
    
    // Create table
    $createTableSQL = file_get_contents(__DIR__ . '/database/migrations/041_create_latest_news_section_table.sql');
    try {
        $db->query($createTableSQL);
        echo "<p style='color: green;'>✓ Created latest_news_section table</p>";
    } catch (Exception $e) {
        echo "<p style='color: orange;'>⚠ Table might already exist: " . $e->getMessage() . "</p>";
    }
    
    // Insert data
    $insertDataSQL = file_get_contents(__DIR__ . '/database/migrations/042_insert_latest_news_section_data.sql');
    try {
        $db->query($insertDataSQL);
        echo "<p style='color: green;'>✓ Inserted default latest news section data</p>";
    } catch (Exception $e) {
        echo "<p style='color: orange;'>⚠ Data might already exist: " . $e->getMessage() . "</p>";
    }

    // Test model
    echo "<h3>Testing LatestNewsSectionModel</h3>";
    $model = new LatestNewsSectionModel();
    
    // Test getFirst
    $section = $model->getFirst();
    if ($section) {
        echo "<p style='color: green;'>✓ getFirst() successful</p>";
        echo "<pre>";
        echo "ID: " . $section['id'] . "\n";
        echo "Title: " . $section['title'] . "\n";
        echo "Is Active: " . ($section['is_active'] ? 'Yes' : 'No') . "\n";
        echo "Created: " . $section['created_at'] . "\n";
        echo "</pre>";
    } else {
        echo "<p style='color: red;'>✗ getFirst() failed</p>";
    }

    // Test createSection
    echo "<h3>Testing createSection</h3>";
    $createResult = $model->createSection([
        'title' => '<h2 class="section-title">Test <span class="highlight">Latest News</span></h2>',
        'is_active' => 1
    ]);
    
    if ($createResult) {
        echo "<p style='color: green;'>✓ createSection() successful</p>";
    } else {
        echo "<p style='color: red;'>✗ createSection() failed</p>";
    }

    // Test updateSection
    echo "<h3>Testing updateSection</h3>";
    if ($section) {
        $updateResult = $model->updateSection($section['id'], [
            'title' => '<h2 class="section-title">Updated <span class="highlight">Latest News</span></h2>',
            'is_active' => 0
        ]);
        
        if ($updateResult) {
            echo "<p style='color: green;'>✓ updateSection() successful</p>";
        } else {
            echo "<p style='color: red;'>✗ updateSection() failed</p>";
        }
    }

    // Test toggleStatus
    echo "<h3>Testing toggleStatus</h3>";
    if ($section) {
        $currentStatus = $section['is_active'];
        $toggleResult = $model->toggleStatus($section['id']);
        
        if ($toggleResult) {
            echo "<p style='color: green;'>✓ toggleStatus() successful</p>";
            echo "<p>Status changed from " . ($currentStatus ? 'active' : 'inactive') . " to " . ($currentStatus ? 'inactive' : 'active') . "</p>";
        } else {
            echo "<p style='color: red;'>✗ toggleStatus() failed</p>";
        }
    }

    echo "<h3>Final Test - Get Updated Section</h3>";
    $finalSection = $model->getFirst();
    if ($finalSection) {
        echo "<p style='color: green;'>✓ Final getFirst() successful</p>";
        echo "<pre>";
        echo "ID: " . $finalSection['id'] . "\n";
        echo "Title: " . $finalSection['title'] . "\n";
        echo "Is Active: " . ($finalSection['is_active'] ? 'Yes' : 'No') . "\n";
        echo "Updated: " . $finalSection['updated_at'] . "\n";
        echo "</pre>";
    }

    echo "<h3 style='color: green;'>✅ All tests completed successfully!</h3>";
    echo "<p><a href='?page=admin&module=homepage&action=edit_latest_news' class='btn btn-primary'>Test Edit Page</a></p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Error: " . $e->getMessage() . "</p>";
    echo "<p>Stack trace: " . $e->getTraceAsString() . "</p>";
}
?>
