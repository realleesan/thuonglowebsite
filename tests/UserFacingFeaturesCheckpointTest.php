<?php

/**
 * Checkpoint Test for User-Facing Features
 * Agent Registration System - Task 8
 * 
 * This test validates all user-facing features implemented in tasks 1-7:
 * - Navigation menu functionality
 * - Agent registration popup
 * - Processing status messages
 * - UI behavior consistency
 * - Integration with existing authentication system
 */
class UserFacingFeaturesCheckpointTest
{
    private $testResults = [];
    private $errors = [];
    
    public function runAllTests()
    {
        echo "=== Agent Registration System - User-Facing Features Checkpoint ===\n\n";
        
        // Test 1: Navigation Menu Integration
        $this->testNavigationMenuIntegration();
        
        // Test 2: Agent Registration Popup
        $this->testAgentRegistrationPopup();
        
        // Test 3: Processing Status Messages
        $this->testProcessingStatusMessages();
        
        // Test 4: UI Components Consistency
        $this->testUIComponentsConsistency();
        
        // Test 5: JavaScript Functions
        $this->testJavaScriptFunctions();
        
        // Test 6: CSS Styles
        $this->testCSSStyles();
        
        // Test 7: Session Integration
        $this->testSessionIntegration();
        
        // Test 8: File Structure Integrity
        $this->testFileStructureIntegrity();
        
        // Generate report
        $this->generateReport();
        
        return count($this->errors) === 0;
    }
    
    private function testNavigationMenuIntegration()
    {
        echo "Testing Navigation Menu Integration...\n";
        
        try {
            // Check if header.php has been updated with agent navigation logic
            $headerContent = file_get_contents('app/views/_layout/header.php');
            
            $this->assert(
                strpos($headerContent, 'agent_request_status') !== false,
                "Header should check agent_request_status"
            );
            
            $this->assert(
                strpos($headerContent, 'showAgentRegistrationPopup') !== false,
                "Header should call showAgentRegistrationPopup function"
            );
            
            $this->assert(
                strpos($headerContent, 'showAgentProcessingMessage') !== false,
                "Header should call showAgentProcessingMessage function"
            );
            
            $this->testResults['navigation_menu'] = 'PASSED';
            echo "  ✓ Navigation menu integration: PASSED\n";
            
        } catch (Exception $e) {
            $this->errors[] = "Navigation Menu: " . $e->getMessage();
            $this->testResults['navigation_menu'] = 'FAILED';
            echo "  ✗ Navigation menu integration: FAILED - " . $e->getMessage() . "\n";
        }
    }
    
    private function testAgentRegistrationPopup()
    {
        echo "Testing Agent Registration Popup...\n";
        
        try {
            // Check if popup view exists and has correct structure
            $popupContent = file_get_contents('app/views/affiliate/registration_popup.php');
            
            $this->assert(
                strpos($popupContent, 'agent-popup-overlay') !== false,
                "Popup should have agent-popup-overlay class"
            );
            
            $this->assert(
                strpos($popupContent, 'agent_email') !== false,
                "Popup should have agent_email input field"
            );
            
            $this->assert(
                strpos($popupContent, '@gmail.com') !== false,
                "Popup should enforce Gmail validation"
            );
            
            $this->assert(
                strpos($popupContent, 'closeAgentPopup') !== false,
                "Popup should have close functionality"
            );
            
            $this->testResults['registration_popup'] = 'PASSED';
            echo "  ✓ Agent registration popup: PASSED\n";
            
        } catch (Exception $e) {
            $this->errors[] = "Registration Popup: " . $e->getMessage();
            $this->testResults['registration_popup'] = 'FAILED';
            echo "  ✗ Agent registration popup: FAILED - " . $e->getMessage() . "\n";
        }
    }
    
    private function testProcessingStatusMessages()
    {
        echo "Testing Processing Status Messages...\n";
        
        try {
            // Check if processing message view exists and has correct structure
            $processingContent = file_get_contents('app/views/affiliate/processing_message.php');
            
            $this->assert(
                strpos($processingContent, 'agent-processing-container') !== false,
                "Processing message should have container class"
            );
            
            $this->assert(
                strpos($processingContent, 'pending') !== false,
                "Processing message should handle pending status"
            );
            
            $this->assert(
                strpos($processingContent, 'approved') !== false,
                "Processing message should handle approved status"
            );
            
            $this->assert(
                strpos($processingContent, 'rejected') !== false,
                "Processing message should handle rejected status"
            );
            
            $this->assert(
                strpos($processingContent, '24 giờ') !== false,
                "Processing message should mention 24 hour timeframe"
            );
            
            $this->testResults['processing_messages'] = 'PASSED';
            echo "  ✓ Processing status messages: PASSED\n";
            
        } catch (Exception $e) {
            $this->errors[] = "Processing Messages: " . $e->getMessage();
            $this->testResults['processing_messages'] = 'FAILED';
            echo "  ✗ Processing status messages: FAILED - " . $e->getMessage() . "\n";
        }
    }
    
    private function testUIComponentsConsistency()
    {
        echo "Testing UI Components Consistency...\n";
        
        try {
            // Check if all UI components use consistent styling
            $cssContent = file_get_contents('assets/css/header_user_menu.css');
            
            $this->assert(
                strpos($cssContent, 'agent-popup-overlay') !== false,
                "CSS should include agent popup styles"
            );
            
            $this->assert(
                strpos($cssContent, 'agent-message') !== false,
                "CSS should include agent message styles"
            );
            
            $this->assert(
                strpos($cssContent, 'fadeIn') !== false,
                "CSS should include animations"
            );
            
            $this->assert(
                strpos($cssContent, '@media') !== false,
                "CSS should include responsive design"
            );
            
            $this->testResults['ui_consistency'] = 'PASSED';
            echo "  ✓ UI components consistency: PASSED\n";
            
        } catch (Exception $e) {
            $this->errors[] = "UI Consistency: " . $e->getMessage();
            $this->testResults['ui_consistency'] = 'FAILED';
            echo "  ✗ UI components consistency: FAILED - " . $e->getMessage() . "\n";
        }
    }
    
    private function testJavaScriptFunctions()
    {
        echo "Testing JavaScript Functions...\n";
        
        try {
            // Check if JavaScript functions are properly implemented
            $jsContent = file_get_contents('assets/js/header_user_menu.js');
            
            $this->assert(
                strpos($jsContent, 'showAgentRegistrationPopup') !== false,
                "JS should have showAgentRegistrationPopup function"
            );
            
            $this->assert(
                strpos($jsContent, 'showAgentProcessingMessage') !== false,
                "JS should have showAgentProcessingMessage function"
            );
            
            $this->assert(
                strpos($jsContent, 'closeAgentPopup') !== false,
                "JS should have closeAgentPopup function"
            );
            
            $this->assert(
                strpos($jsContent, 'submitAgentRegistration') !== false,
                "JS should have submitAgentRegistration function"
            );
            
            $this->assert(
                strpos($jsContent, 'fetch') !== false,
                "JS should use fetch for AJAX requests"
            );
            
            $this->testResults['javascript_functions'] = 'PASSED';
            echo "  ✓ JavaScript functions: PASSED\n";
            
        } catch (Exception $e) {
            $this->errors[] = "JavaScript Functions: " . $e->getMessage();
            $this->testResults['javascript_functions'] = 'FAILED';
            echo "  ✗ JavaScript functions: FAILED - " . $e->getMessage() . "\n";
        }
    }
    
    private function testCSSStyles()
    {
        echo "Testing CSS Styles...\n";
        
        try {
            $cssContent = file_get_contents('assets/css/header_user_menu.css');
            
            // Check for essential CSS classes
            $requiredClasses = [
                '.agent-popup-overlay',
                '.agent-popup',
                '.agent-popup-header',
                '.agent-popup-content',
                '.agent-message',
                '.form-group',
                '.btn-submit',
                '.btn-cancel'
            ];
            
            foreach ($requiredClasses as $class) {
                $this->assert(
                    strpos($cssContent, $class) !== false,
                    "CSS should include $class"
                );
            }
            
            // Check for responsive design
            $this->assert(
                strpos($cssContent, '@media (max-width: 768px)') !== false,
                "CSS should include mobile responsive styles"
            );
            
            $this->testResults['css_styles'] = 'PASSED';
            echo "  ✓ CSS styles: PASSED\n";
            
        } catch (Exception $e) {
            $this->errors[] = "CSS Styles: " . $e->getMessage();
            $this->testResults['css_styles'] = 'FAILED';
            echo "  ✗ CSS styles: FAILED - " . $e->getMessage() . "\n";
        }
    }
    
    private function testSessionIntegration()
    {
        echo "Testing Session Integration...\n";
        
        try {
            // Check if SessionManager has been updated
            $sessionContent = file_get_contents('app/services/SessionManager.php');
            
            $this->assert(
                strpos($sessionContent, 'agent_request_status') !== false,
                "SessionManager should handle agent_request_status"
            );
            
            // Check if UsersModel has been updated
            $usersModelContent = file_get_contents('app/models/UsersModel.php');
            
            $this->assert(
                strpos($usersModelContent, 'agent_request_status') !== false,
                "UsersModel should include agent_request_status in queries"
            );
            
            $this->testResults['session_integration'] = 'PASSED';
            echo "  ✓ Session integration: PASSED\n";
            
        } catch (Exception $e) {
            $this->errors[] = "Session Integration: " . $e->getMessage();
            $this->testResults['session_integration'] = 'FAILED';
            echo "  ✗ Session integration: FAILED - " . $e->getMessage() . "\n";
        }
    }
    
    private function testFileStructureIntegrity()
    {
        echo "Testing File Structure Integrity...\n";
        
        try {
            // Check if all required files exist
            $requiredFiles = [
                'app/views/_layout/header.php',
                'app/views/affiliate/registration_popup.php',
                'app/views/affiliate/processing_message.php',
                'assets/js/header_user_menu.js',
                'assets/css/header_user_menu.css',
                'app/services/SessionManager.php',
                'app/models/UsersModel.php'
            ];
            
            foreach ($requiredFiles as $file) {
                $this->assert(
                    file_exists($file),
                    "Required file should exist: $file"
                );
            }
            
            $this->testResults['file_structure'] = 'PASSED';
            echo "  ✓ File structure integrity: PASSED\n";
            
        } catch (Exception $e) {
            $this->errors[] = "File Structure: " . $e->getMessage();
            $this->testResults['file_structure'] = 'FAILED';
            echo "  ✗ File structure integrity: FAILED - " . $e->getMessage() . "\n";
        }
    }
    
    private function generateReport()
    {
        echo "\n=== CHECKPOINT REPORT ===\n";
        
        $totalTests = count($this->testResults);
        $passedTests = count(array_filter($this->testResults, function($result) {
            return $result === 'PASSED';
        }));
        $failedTests = $totalTests - $passedTests;
        
        echo "Total Tests: $totalTests\n";
        echo "Passed: $passedTests\n";
        echo "Failed: $failedTests\n\n";
        
        if ($failedTests > 0) {
            echo "FAILED TESTS:\n";
            foreach ($this->errors as $error) {
                echo "  ✗ $error\n";
            }
            echo "\n";
        }
        
        echo "DETAILED RESULTS:\n";
        foreach ($this->testResults as $test => $result) {
            $status = $result === 'PASSED' ? '✓' : '✗';
            echo "  $status " . ucwords(str_replace('_', ' ', $test)) . ": $result\n";
        }
        
        echo "\n";
        
        if ($failedTests === 0) {
            echo "🎉 ALL USER-FACING FEATURES CHECKPOINT PASSED!\n";
            echo "The agent registration system user interface is ready for the next phase.\n";
        } else {
            echo "❌ CHECKPOINT FAILED!\n";
            echo "Please fix the issues above before proceeding to admin features.\n";
        }
    }
    
    private function assert($condition, $message)
    {
        if (!$condition) {
            throw new Exception($message);
        }
    }
}

// Run the checkpoint test
$checkpoint = new UserFacingFeaturesCheckpointTest();
$success = $checkpoint->runAllTests();

exit($success ? 0 : 1);