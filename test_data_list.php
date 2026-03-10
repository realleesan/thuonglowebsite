<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "Test data_list.php<br>";

// Simulate being in root directory
$_GET['id'] = 2;
$_GET['token'] = 'test_token_123';
$_GET['page'] = 1;

require_once __DIR__ . '/app/views/products/data_list.php';

echo "data_list loaded OK<br>";
