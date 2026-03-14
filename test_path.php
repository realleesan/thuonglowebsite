<?php
// Test path resolution
$editDir = __DIR__ . '/app/views/admin/products';
$debugPath = $editDir . '/../../../../debug_edit_form.php';
$resolved = realpath($debugPath);

echo "Edit dir: $editDir\n";
echo "Debug path: $debugPath\n";
echo "Resolved: " . ($resolved ? $resolved : "NOT FOUND") . "\n";
echo "File exists: " . (file_exists($resolved) ? "YES" : "NO") . "\n";
