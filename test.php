<?php
/**
 * Debug Test File for Hero Section
 */

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

echo "<h1>Hero Section Debug Test</h1>";

// Test 1: Basic PHP functionality
echo "<h2>1. Basic PHP Test</h2>";
echo "PHP Version: " . phpversion() . "<br>";
echo "Current working directory: " . getcwd() . "<br>";

// Test 2: Check if required files exist
echo "<h2>2. File Existence Test</h2>";

$requiredFiles = [
    'index.php' => 'Main index file',
    'app/controllers/HeroSectionController.php' => 'Hero Section Controller',
    'app/models/HeroSectionModel.php' => 'Hero Section Model',
    'app/models/HeroButtonModel.php' => 'Hero Button Model',
    'app/views/admin/homepage/hero_section/index.php' => 'Hero Section Index View',
    'app/views/admin/homepage/hero_section/create.php' => 'Hero Section Create View',
    'app/views/admin/homepage/hero_section/edit.php' => 'Hero Section Edit View',
    'app/views/_layout/admin_master.php' => 'Admin Master Layout',
    'app/views/_layout/admin_sidebar.php' => 'Admin Sidebar',
    'assets/css/admin_hero_section.css' => 'Hero Section CSS',
    'assets/js/admin_hero_section.js' => 'Hero Section JS',
    'core/database.php' => 'Database Config',
    'core/functions.php' => 'Core Functions',
    'core/view_init.php' => 'View Init'
];

foreach ($requiredFiles as $file => $description) {
    if (file_exists($file)) {
        echo "✅ {$description}: {$file} - EXISTS<br>";
    } else {
        echo "❌ {$description}: {$file} - MISSING<br>";
    }
}

// Test 3: Check database connection
echo "<h2>3. Database Connection Test</h2>";
try {
    require_once 'core/database.php';
    if (function_exists('getConnection')) {
        $conn = getConnection();
        if ($conn) {
            echo "✅ Database connection: SUCCESS<br>";
            
            // Check if hero_sections table exists
            $result = $conn->query("SHOW TABLES LIKE 'hero_sections'");
            if ($result && $result->num_rows > 0) {
                echo "✅ Table 'hero_sections': EXISTS<br>";
            } else {
                echo "❌ Table 'hero_sections': MISSING<br>";
            }
            
            // Check if hero_buttons table exists
            $result = $conn->query("SHOW TABLES LIKE 'hero_buttons'");
            if ($result && $result->num_rows > 0) {
                echo "✅ Table 'hero_buttons': EXISTS<br>";
            } else {
                echo "❌ Table 'hero_buttons': MISSING<br>";
            }
        } else {
            echo "❌ Database connection: FAILED<br>";
        }
    } else {
        echo "❌ getConnection function: NOT FOUND<br>";
    }
} catch (Exception $e) {
    echo "❌ Database error: " . $e->getMessage() . "<br>";
}

// Test 4: Check core functions
echo "<h2>4. Core Functions Test</h2>";
try {
    require_once 'core/functions.php';
    
    if (function_exists('css_url')) {
        echo "✅ css_url(): EXISTS<br>";
    } else {
        echo "❌ css_url(): MISSING<br>";
    }
    
    if (function_exists('js_url')) {
        echo "✅ js_url(): EXISTS<br>";
    } else {
        echo "❌ js_url(): MISSING<br>";
    }
    
    if (function_exists('icon_url')) {
        echo "✅ icon_url(): EXISTS<br>";
    } else {
        echo "❌ icon_url(): MISSING<br>";
    }
} catch (Exception $e) {
    echo "❌ Core functions error: " . $e->getMessage() . "<br>";
}

// Test 5: Try to instantiate HeroSectionController
echo "<h2>5. HeroSectionController Test</h2>";
try {
    require_once 'app/controllers/HeroSectionController.php';
    $controller = new HeroSectionController();
    echo "✅ HeroSectionController instantiation: SUCCESS<br>";
} catch (Exception $e) {
    echo "❌ HeroSectionController error: " . $e->getMessage() . "<br>";
} catch (Error $e) {
    echo "❌ HeroSectionController fatal error: " . $e->getMessage() . "<br>";
}

// Test 6: Check URL parameters
echo "<h2>6. URL Parameters Test</h2>";
echo "GET parameters:<br>";
foreach ($_GET as $key => $value) {
    echo "- {$key}: {$value}<br>";
}

// Test 7: Check session
echo "<h2>7. Session Test</h2>";
if (session_status() === PHP_SESSION_NONE) {
    echo "❌ Session: NOT STARTED<br>";
} else {
    echo "✅ Session: STARTED<br>";
}

// Test 8: Check error log
echo "<h2>8. Recent Error Log</h2>";
$errorLog = 'logs/error.log';
if (file_exists($errorLog)) {
    $lines = file($errorLog);
    $recentLines = array_slice($lines, -10); // Last 10 lines
    foreach ($recentLines as $line) {
        echo htmlspecialchars($line) . "<br>";
    }
} else {
    echo "No error log found at: {$errorLog}<br>";
}

// Test 9: Try to simulate the hero section route
echo "<h2>9. Route Simulation Test</h2>";
try {
    // Simulate the route logic from index.php
    $_GET['page'] = 'admin';
    $_GET['module'] = 'hero-section';
    
    echo "Simulating route: page=admin, module=hero-section<br>";
    
    if (class_exists('HeroSectionController')) {
        $controller = new HeroSectionController();
        echo "✅ Controller class exists<br>";
        
        if (method_exists($controller, 'index')) {
            echo "✅ Index method exists<br>";
        } else {
            echo "❌ Index method missing<br>";
        }
    } else {
        echo "❌ Controller class doesn't exist<br>";
    }
} catch (Exception $e) {
    echo "❌ Route simulation error: " . $e->getMessage() . "<br>";
}

// Test 10: Check .htaccess
echo "<h2>10. .htaccess Test</h2>";
if (file_exists('.htaccess')) {
    echo "✅ .htaccess file exists<br>";
    $htaccess = file_get_contents('.htaccess');
    if (strpos($htaccess, 'RewriteEngine') !== false) {
        echo "✅ URL rewriting enabled<br>";
    } else {
        echo "❌ URL rewriting not found<br>";
    }
} else {
    echo "❌ .htaccess file missing<br>";
}

echo "<h2>Test Complete</h2>";
echo "<p><strong>Next steps:</strong></p>";
echo "<ul>";
echo "<li>Check for any ❌ items above</li>";
echo "<li>Fix missing files or database tables</li>";
echo "<li>Check error logs for detailed messages</li>";
echo "<li>Verify file permissions</li>";
echo "</ul>";

// Test direct access to hero section
echo "<h2>11. Direct Access Test</h2>";
echo "<p><a href='?page=admin&module=hero-section'>Test Hero Section Direct Access</a></p>";
?>
