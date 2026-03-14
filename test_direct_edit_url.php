<?php
/**
 * Simple test to load edit page through index.php
 * Just redirect to the edit page and show any errors
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Just redirect to the edit page
$editUrl = '?page=admin&module=products&action=edit&id=34';

echo "<h1>Redirecting to edit page...</h1>";
echo "<p>URL: $editUrl</p>";
echo "<p><a href='$editUrl'>Click here to go to edit page</a></p>";
echo "<p>If the edit page is blank, check the PHP error log.</p>";

// Or use meta refresh
header("Refresh: 2;url=$editUrl");
