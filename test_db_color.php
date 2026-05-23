<?php
require '/app/core/database.php';
$db = Database::getInstance();
$cats = $db->query("SELECT id, name, color FROM categories");
print_r($cats);
