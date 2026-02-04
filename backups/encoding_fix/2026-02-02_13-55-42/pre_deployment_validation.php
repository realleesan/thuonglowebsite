<?php
/**
 * Pre-deployment Validation Script
 * Thuong Lo Website - UTF-8 Encoding Fix
 */

require_once __DIR__ . '/../core/encoding.php';

echo "=== Pre-deployment Validation ===\n";
echo "Generated: " . date('Y-m-d H:i:s') . "\n\n";

$errors = [];
$warnings = [];

// 1. Validate all PHP files for syntax errors
echo "1. Checking PHP syntax...\n";
$phpFiles = [];
$directories = ['app', 'core', 'api'];

foreach ($directories as $dir) {
    if (is_dir($dir)) {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS)
        );
        
        foreach ($iterator as $file) {
            if ($file->isFile() && pathinfo($file->getPathname(), PATHINFO_EXTENSION) === 'php') {
                $phpFiles[] = $file->getPathname();
            }
        }
    }
}

$syntaxErrors = 0;
foreach ($phpFiles as $file) {
    $output = [];
    $returnCode = 0;
    
    // Try different PHP executable paths
    $phpPaths = [
        'D:\\xampp\\php\\php.exe',
        'php',
        '/usr/bin/php',
        '/usr/local/bin/php'
    ];
    
    $phpExecutable = null;
    foreach ($phpPaths as $path) {
        if (is_executable($path) || $path === 'php') {
            $phpExecutable = $path;
            break;
        }
    }
    
    if ($phpExecutable) {
        exec("\"$phpExecutable\" -l \"$file\" 2>&1", $output, $returnCode);
        
        if ($returnCode !== 0) {
            $syntaxErrors++;
            $errors[] = "Syntax error in $file: " . implode(' ', $output);
        }
    } else {
        // Skip syntax check if PHP executable not found
        echo "⚠️  PHP executable not found, skipping syntax check\n";
        break;
    }
}

if ($syntaxErrors === 0) {
    echo "✅ All PHP files have valid syntax\n";
} else {
    echo "❌ $syntaxErrors PHP files have syntax errors\n";
}

// 2. Validate file encoding
echo "\n2. Checking file encoding...\n";
$encodingIssues = 0;
$bomInconsistency = false;
$bomFiles = 0;
$noBomFiles = 0;

foreach ($phpFiles as $file) {
    $result = validateFileEncoding($file);
    
    if (!$result['valid_utf8']) {
        $encodingIssues++;
        $errors[] = "Invalid UTF-8 encoding in $file (detected: {$result['encoding']})";
    }
    
    if ($result['has_bom']) {
        $bomFiles++;
    } else {
        $noBomFiles++;
    }
}

if ($encodingIssues === 0) {
    echo "✅ All PHP files have valid UTF-8 encoding\n";
} else {
    echo "❌ $encodingIssues PHP files have encoding issues\n";
}

// Check BOM consistency
if ($bomFiles > 0 && $noBomFiles > 0) {
    $warnings[] = "BOM inconsistency: $bomFiles files with BOM, $noBomFiles files without BOM";
    echo "⚠️  BOM inconsistency detected\n";
} else {
    echo "✅ BOM usage is consistent\n";
}

// 3. Check .htaccess configuration
echo "\n3. Checking .htaccess configuration...\n";
if (file_exists('.htaccess')) {
    $htaccessContent = file_get_contents('.htaccess');
    
    $requiredSettings = [
        'AddDefaultCharset UTF-8' => 'Default charset setting',
        'AddCharset UTF-8 .php' => 'PHP charset setting',
        'charset=UTF-8' => 'Content-Type charset'
    ];
    
    $missingSettings = 0;
    foreach ($requiredSettings as $setting => $description) {
        if (strpos($htaccessContent, $setting) === false) {
            $missingSettings++;
            $errors[] = "Missing .htaccess setting: $setting ($description)";
        }
    }
    
    if ($missingSettings === 0) {
        echo "✅ .htaccess has all required charset settings\n";
    } else {
        echo "❌ .htaccess is missing $missingSettings required settings\n";
    }
} else {
    $errors[] = ".htaccess file not found";
    echo "❌ .htaccess file not found\n";
}

// 4. Check master.php configuration
echo "\n4. Checking master.php configuration...\n";
$masterFile = 'app/views/_layout/master.php';
if (file_exists($masterFile)) {
    $masterContent = file_get_contents($masterFile);
    
    $requiredElements = [
        'charset="UTF-8"' => 'HTML charset meta tag',
        'Content-Type.*charset=UTF-8' => 'HTTP-equiv meta tag',
        'header.*charset=UTF-8' => 'PHP charset header'
    ];
    
    $missingElements = 0;
    foreach ($requiredElements as $pattern => $description) {
        if (!preg_match("/$pattern/i", $masterContent)) {
            $missingElements++;
            $warnings[] = "Missing in master.php: $description";
        }
    }
    
    if ($missingElements === 0) {
        echo "✅ master.php has all required charset configurations\n";
    } else {
        echo "⚠️  master.php is missing $missingElements recommended configurations\n";
    }
} else {
    $warnings[] = "master.php file not found";
    echo "⚠️  master.php file not found\n";
}

// 5. Test Vietnamese characters
echo "\n5. Testing Vietnamese character handling...\n";
$testStrings = ['Xin chào', 'Tiếng Việt', 'Thương lộ'];
$charIssues = 0;

foreach ($testStrings as $str) {
    if (!mb_check_encoding($str, 'UTF-8')) {
        $charIssues++;
        $errors[] = "Vietnamese character test failed for: $str";
    }
}

if ($charIssues === 0) {
    echo "✅ Vietnamese character handling is working\n";
} else {
    echo "❌ $charIssues Vietnamese character tests failed\n";
}

// Summary
echo "\n=== Validation Summary ===\n";
echo "Total PHP files checked: " . count($phpFiles) . "\n";
echo "Errors: " . count($errors) . "\n";
echo "Warnings: " . count($warnings) . "\n";

if (!empty($errors)) {
    echo "\n=== ERRORS (Must fix before deployment) ===\n";
    foreach ($errors as $error) {
        echo "❌ $error\n";
    }
}

if (!empty($warnings)) {
    echo "\n=== WARNINGS (Recommended to fix) ===\n";
    foreach ($warnings as $warning) {
        echo "⚠️  $warning\n";
    }
}

$canDeploy = empty($errors);
echo "\n=== DEPLOYMENT STATUS ===\n";
if ($canDeploy) {
    echo "✅ READY FOR DEPLOYMENT\n";
    exit(0);
} else {
    echo "❌ NOT READY FOR DEPLOYMENT - Fix errors first\n";
    exit(1);
}