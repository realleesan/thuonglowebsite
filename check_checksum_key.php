<?php
// Check current PayOS configuration
$config = require __DIR__ . '/config.php';

echo "Current PayOS Configuration:\n";
echo "========================\n";
echo "Client ID: " . $config['payos']['client_id'] . "\n";
echo "API Key: " . $config['payos']['api_key'] . "\n";
echo "Checksum Key: " . $config['payos']['checksum_key'] . "\n";
echo "Checksum Key Length: " . strlen($config['payos']['checksum_key']) . "\n";
echo "API URL: " . $config['payos']['api_url'] . "\n";
echo "Test Mode: " . ($config['payos']['test_mode'] ? 'YES' : 'NO') . "\n";

echo "\nInstructions:\n";
echo "=============\n";
echo "1. Go to my.payos.vn\n";
echo "2. Select your payment channel\n";
echo "3. Look for PAYOUT/LỆNH CHI section\n";
echo "4. Copy the CHECKSUM KEY from payout section (not payment section)\n";
echo "5. Update your .env file with: PAYOS_CHECKSUM_KEY=your_payout_checksum_key\n";
echo "6. The payout checksum key is DIFFERENT from payment checksum key!\n";
?>
