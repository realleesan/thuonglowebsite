<?php
/**
 * Test Runner
 * Runs all configuration tests
 */

echo "<!DOCTYPE html>";
echo "<html><head><title>Configuration Tests</title></head><body>";

echo "<h1>Configuration System Tests</h1>";
echo "<p>Running all tests for the configuration system...</p>";

$allTestsPassed = true;

// Run Environment Tests
echo "<div style='border: 1px solid #ccc; padding: 10px; margin: 10px 0;'>";
require_once 'EnvironmentTest.php';
$envTest = new EnvironmentTest();
$envPassed = $envTest->runAllTests();
$allTestsPassed = $allTestsPassed && $envPassed;
echo "</div>";

// Run UrlBuilder Tests
echo "<div style='border: 1px solid #ccc; padding: 10px; margin: 10px 0;'>";
require_once 'UrlBuilderTest.php';
$urlTest = new UrlBuilderTest();
$urlPassed = $urlTest->runAllTests();
$allTestsPassed = $allTestsPassed && $urlPassed;
echo "</div>";

// Overall Results
echo "<hr>";
echo "<h2>Overall Test Results</h2>";

if ($allTestsPassed) {
    echo "<p style='color: green; font-size: 18px; font-weight: bold;'>";
    echo "🎉 All configuration tests passed! The system is ready for use.";
    echo "</p>";
} else {
    echo "<p style='color: red; font-size: 18px; font-weight: bold;'>";
    echo "❌ Some tests failed. Please review the issues above.";
    echo "</p>";
}

echo "<h3>Next Steps</h3>";
echo "<ul>";
echo "<li><a href='../test_config.php'>Test Configuration in Browser</a></li>";
echo "<li><a href='../index.php'>Go to Main Website</a></li>";
echo "</ul>";

echo "</body></html>";
?>