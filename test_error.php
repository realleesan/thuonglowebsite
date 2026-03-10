<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "Test error reporting<br>";

require_once __DIR__ . '/app/models/ProductDataModel.php';

echo "ProductDataModel loaded OK<br>";

$model = new ProductDataModel();
echo "Instance created OK<br>";
