<?php
define('THUONGLO_INIT', true);
$config = require_once __DIR__ . '/config.php';
try {
    $db = new PDO(
        "mysql:host=" . $config['database']['host'] . ";dbname=" . $config['database']['name'] . ";charset=" . $config['database']['charset'],
        $config['database']['username'],
        $config['database']['password'],
        $config['database']['options']
    );
    $stmt = $db->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "Tables in database:\n";
    print_r($tables);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
