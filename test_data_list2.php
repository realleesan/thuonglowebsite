<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session and set user_id to simulate logged in user
session_start();
$_SESSION['user_id'] = 1; // Adjust as needed
$_SESSION['user'] = ['id' => 1, 'name' => 'Test User'];

echo "Session set: user_id = " . $_SESSION['user_id'] . "<br>";

// Set GET parameters
$_GET['id'] = 2;
$_GET['token'] = 'b89c0eae2c90bbf548747500eeff340357e9101b7212fc071fcb1faf5c968deb';
$_GET['page'] = 1;

echo "Loading data_list.php...<br>";

require_once __DIR__ . '/app/views/products/data_list.php';

echo "data_list loaded OK<br>";
