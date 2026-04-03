<?php
/**
 * Test script for reject affiliate request functionality
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/app/services/AdminService.php';
require_once __DIR__ . '/app/models/AffiliateModel.php';
require_once __DIR__ . '/app/models/UsersModel.php';

echo "<h1>Test Reject Affiliate Request</h1>";

// Test 1: Check if affiliate record exists
echo "<h2>Test 1: Check affiliate records</h2>";
$affiliateModel = new AffiliateModel();
$affiliates = $affiliateModel->query("SELECT id, user_id, status FROM affiliates ORDER BY id DESC LIMIT 5");
echo "<pre>";
print_r($affiliates);
echo "</pre>";

// Test 2: Check fillable fields
echo "<h2>Test 2: AffiliateModel fillable fields</h2>";
echo "<pre>";
print_r($affiliateModel->fillable);
echo "</pre>";

// Test 3: Try to update status directly
echo "<h2>Test 3: Direct status update test</h2>";
$testId = 11; // Change this to a valid affiliate ID
try {
    $affiliate = $affiliateModel->find($testId);
    echo "Found affiliate: ";
    print_r($affiliate);
    
    if ($affiliate) {
        echo "<br>Current status: " . $affiliate['status'] . "<br>";
        
        // Try direct SQL update
        $sql = "UPDATE affiliates SET status = 'rejected' WHERE id = {$testId}";
        echo "SQL: " . $sql . "<br>";
        $result = $affiliateModel->query($sql);
        echo "Update result: ";
        print_r($result);
        
        // Verify update
        $affiliateAfter = $affiliateModel->find($testId);
        echo "<br>After update: ";
        print_r($affiliateAfter);
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "<br>";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "<br>";
}

// Test 4: Test AdminService reject method
echo "<h2>Test 4: AdminService rejectAffiliateRequest</h2>";
try {
    $adminService = new AdminService(null, 'admin');
    $result = $adminService->rejectAffiliateRequest($testId, 'Test rejection');
    echo "Result: ";
    print_r($result);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "<br>";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "<br>";
}

// Test 5: Check BaseModel update method
echo "<h2>Test 5: BaseModel update method test</h2>";
try {
    $affiliateModel2 = new AffiliateModel();
    $affiliate = $affiliateModel2->find($testId);
    if ($affiliate) {
        echo "Before update: status = " . $affiliate['status'] . "<br>";
        
        // Try update with only status field
        $updateResult = $affiliateModel2->update($testId, ['status' => 'pending']);
        echo "Update result: ";
        print_r($updateResult);
        
        // Verify
        $affiliateAfter = $affiliateModel2->find($testId);
        echo "<br>After update: status = " . $affiliateAfter['status'] . "<br>";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "<br>";
}
?>
