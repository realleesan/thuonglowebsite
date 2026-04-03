<?php
/**
 * Test file cho agent registration service
 * Chạy: http://test1.web3b.com/test_agent_service.php
 */

$base_dir = dirname(__FILE__);

// Load config
$config = require $base_dir . '/config.php';
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Test Agent Service</title>
    <style> 
        body { font-family: Arial, sans-serif; padding: 20px; max-width: 800px; margin: 0 auto; }
        .result { background: #f8f9fa; padding: 15px; margin: 10px 0; border-radius: 5px; }
        .success { background: #d4edda; color: #155724; }
        .error { background: #f8d7da; color: #721c24; }
        h3 { margin-top: 20px; }
    </style>
</head>
<body>
    <h1>Test Agent Registration Service</h1>
    
    <h3>1. Database Connection</h3>
    <div class="result">
<?php
try {
    $dsn = 'mysql:host=' . $config['database']['host'] . ';dbname=' . $config['database']['name'];
    $db = new PDO($dsn, $config['database']['username'], $config['database']['password']);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✓ Database connection: SUCCESS<br>";
    
    $stmt = $db->query("SELECT 1 as test");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "✓ Simple query: SUCCESS<br>";
} catch (PDOException $e) {
    echo "✗ Database connection: FAILED<br>";
    echo "Error: " . $e->getMessage() . "<br>";
}
?>
    </div>
    
    <h3>2. UsersModel</h3>
    <div class="result">
<?php
try {
    require_once $base_dir . '/app/models/UsersModel.php';
    $usersModel = new UsersModel();
    echo "✓ UsersModel created: SUCCESS<br>";
} catch (Exception $e) {
    echo "✗ UsersModel: FAILED<br>";
    echo "Error: " . $e->getMessage() . "<br>";
}
?>
    </div>
    
    <h3>3. AffiliateModel</h3>
    <div class="result">
<?php
try {
    require_once $base_dir . '/app/models/AffiliateModel.php';
    $affiliateModel = new AffiliateModel();
    echo "✓ AffiliateModel created: SUCCESS<br>";
} catch (Exception $e) {
    echo "✗ AffiliateModel: FAILED<br>";
    echo "Error: " . $e->getMessage() . "<br>";
}
?>
    </div>
    
    <h3>4. AgentRegistrationService</h3>
    <div class="result">
<?php
try {
    require_once $base_dir . '/app/services/AgentRegistrationService.php';
    $agentService = new AgentRegistrationService();
    echo "✓ AgentRegistrationService created: SUCCESS<br>";
} catch (Exception $e) {
    echo "✗ AgentRegistrationService: FAILED<br>";
    echo "Error: " . $e->getMessage() . "<br>";
    echo "File: " . $e->getFile() . "<br>";
    echo "Line: " . $e->getLine() . "<br>";
}
?>
    </div>
    
    <h3>5. SpamPreventionService</h3>
    <div class="result">
<?php
try {
    require_once $base_dir . '/app/services/SpamPreventionService.php';
    $spamService = new SpamPreventionService();
    echo "✓ SpamPreventionService created: SUCCESS<br>";
} catch (Exception $e) {
    echo "✗ SpamPreventionService: FAILED<br>";
    echo "Error: " . $e->getMessage() . "<br>";
}
?>
    </div>

</body>
</html>