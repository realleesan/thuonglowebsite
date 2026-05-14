<?php
/**
 * Debug Hero Section Controller
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Controller Debug Test</h1>";

try {
    // Load required files
    require_once __DIR__ . '/../core/functions.php';
    require_once __DIR__ . '/../core/view_init.php';
    require_once __DIR__ . '/../services/AuthService.php';
    require_once __DIR__ . '/../models/HeroSectionModel.php';
    require_once __DIR__ . '/../models/HeroButtonModel.php';
    
    echo "✅ Files loaded successfully<br>";
    
    // Try to instantiate AuthService
    $authService = new AuthService();
    echo "✅ AuthService instantiated<br>";
    
    // Try to instantiate HeroSectionModel
    $heroSectionModel = new HeroSectionModel();
    echo "✅ HeroSectionModel instantiated<br>";
    
    // Try to instantiate HeroButtonModel
    $heroButtonModel = new HeroButtonModel();
    echo "✅ HeroButtonModel instantiated<br>";
    
    // Try to instantiate HeroSectionController
    $heroSectionController = new HeroSectionController();
    echo "✅ HeroSectionController instantiated<br>";
    
    // Test database connection
    if (function_exists('getConnection')) {
        $conn = getConnection();
        if ($conn) {
            echo "✅ Database connection works<br>";
            
            // Test getWithButtons method
            if (method_exists($heroSectionModel, 'getWithButtons')) {
                echo "✅ getWithButtons method exists<br>";
                
                try {
                    $result = $heroSectionModel->getWithButtons(1);
                    echo "✅ getWithButtons executed successfully<br>";
                    echo "Result: " . print_r($result, true) . "<br>";
                } catch (Exception $e) {
                    echo "❌ getWithButtons error: " . $e->getMessage() . "<br>";
                    echo "Stack trace:<br><pre>" . $e->getTraceAsString() . "</pre>";
                }
            } else {
                echo "❌ getWithButtons method missing<br>";
            }
        } else {
            echo "❌ Database connection failed<br>";
        }
    } else {
        echo "❌ getConnection function missing<br>";
    }
    
} catch (Error $e) {
    echo "❌ Fatal error: " . $e->getMessage() . "<br>";
    echo "Stack trace:<br><pre>" . $e->getTraceAsString() . "</pre>";
} catch (Exception $e) {
    echo "❌ Exception: " . $e->getMessage() . "<br>";
    echo "Stack trace:<br><pre>" . $e->getTraceAsString() . "</pre>";
}
?>
