<?php
/**
 * Script để sửa lỗi getModel trong AgentRegistrationService
 */

echo "Fixing AgentRegistrationService getModel issues...\n";

$filePath = 'app/services/AgentRegistrationService.php';
$content = file_get_contents($filePath);

if (!$content) {
    echo "Error: Could not read file {$filePath}\n";
    exit(1);
}

// Backup original file
file_put_contents($filePath . '.backup', $content);
echo "Backup created: {$filePath}.backup\n";

// Replace all getModel calls with direct instantiation
$replacements = [
    // In upgradeExistingUserToAgent method
    '$usersModel = $this->getModel(\'UsersModel\');' => '$usersModel = new UsersModel();',
    
    // In checkExistingRequest method  
    '$affiliateModel = $this->getModel(\'AffiliateModel\');' => '$affiliateModel = new AffiliateModel();',
    
    // In approveAgentRequest method
    '$usersModel = $this->getModel(\'UsersModel\');
            $affiliateModel = $this->getModel(\'AffiliateModel\');' => '$usersModel = new UsersModel();
            $affiliateModel = new AffiliateModel();',
    
    // In rejectAgentRequest method
    '$usersModel = $this->getModel(\'UsersModel\');
            $affiliateModel = $this->getModel(\'AffiliateModel\');' => '$usersModel = new UsersModel();
            $affiliateModel = new AffiliateModel();',
    
    // In canAccessAgentFeatures method
    '$usersModel = $this->getModel(\'UsersModel\');' => '$usersModel = new UsersModel();',
    
    // In canManageAgentRequests method
    '$usersModel = $this->getModel(\'UsersModel\');' => '$usersModel = new UsersModel();'
];

foreach ($replacements as $search => $replace) {
    $newContent = str_replace($search, $replace, $content);
    if ($newContent !== $content) {
        $content = $newContent;
        echo "✓ Replaced: " . substr($search, 0, 50) . "...\n";
    }
}

// Write the fixed content back
if (file_put_contents($filePath, $content)) {
    echo "✓ File updated successfully: {$filePath}\n";
} else {
    echo "✗ Error: Could not write to file {$filePath}\n";
    exit(1);
}

echo "Fix completed!\n";
?>