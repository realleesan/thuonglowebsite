<?php
/**
 * Check WebhookController version
 */

$content = file_get_contents(__DIR__ . '/app/controllers/WebhookController.php');

// Check for the new regex pattern
$hasNewRegex = strpos($content, 'ORD[a-zA-Z0-9]+') !== false;
$hasUnderscoreLogic = strpos($content, "ORD_' . substr(") !== false;

echo "=== WebhookController Check ===\n\n";
echo "Has new regex (ORD without underscore): " . ($hasNewRegex ? "YES" : "NO") . "\n";
echo "Has underscore conversion logic: " . ($hasUnderscoreLogic ? "YES" : "NO") . "\n\n";

if ($hasNewRegex && $hasUnderscoreLogic) {
    echo "✅ Code is up to date with SePay format support\n";
} else {
    echo "❌ Code is NOT up to date - need to redeploy\n";
}
