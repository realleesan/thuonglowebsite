<?php
/**
 * Test script to simulate direct access to admin and affiliate pages
 */

echo "<h2>Direct Access Test</h2>";

echo "<h3>Test Links (Open in new tabs to test):</h3>";
echo "<ul>";
echo "<li><a href='?page=admin' target='_blank'>Direct Admin Access</a> - Should redirect to login if not authenticated</li>";
echo "<li><a href='?page=affiliate' target='_blank'>Direct Affiliate Access</a> - Should redirect to login if not authenticated</li>";
echo "<li><a href='?page=users' target='_blank'>Direct User Dashboard Access</a> - Should redirect to login if not authenticated</li>";
echo "<li><a href='?page=login' target='_blank'>Login Page</a> - Should work normally</li>";
echo "</ul>";

echo "<h3>Current Session Status:</h3>";
session_start();
echo "Logged in: " . (isset($_SESSION['user_id']) ? 'Yes (ID: ' . $_SESSION['user_id'] . ')' : 'No') . "<br>";
echo "Role: " . ($_SESSION['user_role'] ?? 'Not set') . "<br>";

echo "<h3>Instructions:</h3>";
echo "<ol>";
echo "<li>If you're not logged in, clicking admin/affiliate links should redirect to login</li>";
echo "<li>If you're logged in as regular user, admin/affiliate should redirect to user dashboard</li>";
echo "<li>If you're logged in as admin, admin should work, affiliate should work (admin can access affiliate)</li>";
echo "<li>If you're logged in as affiliate, affiliate should work, admin should redirect</li>";
echo "</ol>";

echo "<br><a href='./'>Back to Home</a>";
?>