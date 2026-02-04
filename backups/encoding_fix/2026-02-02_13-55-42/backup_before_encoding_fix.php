<?php
/**
 * Backup Script Before Encoding Fix
 * Thuong Lo Website - UTF-8 Encoding Fix
 */

require_once __DIR__ . '/../core/encoding.php';

echo "=== Backup Before Encoding Fix ===\n";
echo "Generated: " . date('Y-m-d H:i:s') . "\n\n";

$timestamp = date('Y-m-d_H-i-s');
$backupDir = "backups/encoding_fix_$timestamp";

// Create backup directory
if (!is_dir('backups')) {
    mkdir('backups', 0755, true);
}

if (!mkdir($backupDir, 0755, true)) {
    echo "❌ Failed to create backup directory: $backupDir\n";
    exit(1);
}

echo "Backup directory: $backupDir\n\n";

// Files and directories to backup
$itemsToBackup = [
    '.htaccess',
    'app/',
    'core/',
    'api/',
    'config.php',
    'index.php'
];

$backedUpFiles = 0;
$failedFiles = 0;

foreach ($itemsToBackup as $item) {
    if (!file_exists($item)) {
        echo "⚠️  Item not found: $item\n";
        continue;
    }
    
    $backupPath = $backupDir . '/' . $item;
    
    if (is_file($item)) {
        // Backup single file
        $backupFileDir = dirname($backupPath);
        if (!is_dir($backupFileDir)) {
            mkdir($backupFileDir, 0755, true);
        }
        
        if (copy($item, $backupPath)) {
            echo "✅ Backed up file: $item\n";
            $backedUpFiles++;
        } else {
            echo "❌ Failed to backup file: $item\n";
            $failedFiles++;
        }
    } elseif (is_dir($item)) {
        // Backup directory recursively
        if (copyDirectory($item, $backupPath)) {
            echo "✅ Backed up directory: $item\n";
            $backedUpFiles++;
        } else {
            echo "❌ Failed to backup directory: $item\n";
            $failedFiles++;
        }
    }
}

// Create backup manifest
$manifest = [
    'timestamp' => $timestamp,
    'date' => date('Y-m-d H:i:s'),
    'purpose' => 'Backup before UTF-8 encoding fix',
    'items_backed_up' => $backedUpFiles,
    'items_failed' => $failedFiles,
    'backup_directory' => $backupDir,
    'items' => $itemsToBackup
];

file_put_contents($backupDir . '/backup_manifest.json', json_encode($manifest, JSON_PRETTY_PRINT));

// Create restore script
$restoreScript = "<?php\n";
$restoreScript .= "// Restore script generated on " . date('Y-m-d H:i:s') . "\n";
$restoreScript .= "// To restore from this backup, run: php restore_from_backup.php $timestamp\n\n";
$restoreScript .= "echo \"Restoring from backup: $backupDir\\n\";\n\n";

foreach ($itemsToBackup as $item) {
    if (is_file($item)) {
        $restoreScript .= "if (file_exists('$backupDir/$item')) {\n";
        $restoreScript .= "    copy('$backupDir/$item', '$item');\n";
        $restoreScript .= "    echo \"Restored: $item\\n\";\n";
        $restoreScript .= "}\n\n";
    } elseif (is_dir($item)) {
        $restoreScript .= "if (is_dir('$backupDir/$item')) {\n";
        $restoreScript .= "    copyDirectory('$backupDir/$item', '$item');\n";
        $restoreScript .= "    echo \"Restored: $item\\n\";\n";
        $restoreScript .= "}\n\n";
    }
}

file_put_contents($backupDir . '/restore_script.php', $restoreScript);

echo "\n=== Backup Summary ===\n";
echo "Items backed up: $backedUpFiles\n";
echo "Items failed: $failedFiles\n";
echo "Backup location: $backupDir\n";
echo "Manifest: $backupDir/backup_manifest.json\n";
echo "Restore script: $backupDir/restore_script.php\n";

if ($failedFiles === 0) {
    echo "\n✅ BACKUP COMPLETED SUCCESSFULLY\n";
    echo "You can now proceed with encoding fixes.\n";
    exit(0);
} else {
    echo "\n⚠️  BACKUP COMPLETED WITH WARNINGS\n";
    echo "Some items failed to backup. Review before proceeding.\n";
    exit(1);
}

/**
 * Copy directory recursively
 */
function copyDirectory($source, $destination) {
    if (!is_dir($source)) {
        return false;
    }
    
    if (!is_dir($destination)) {
        if (!mkdir($destination, 0755, true)) {
            return false;
        }
    }
    
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($source, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );
    
    foreach ($iterator as $item) {
        $target = $destination . DIRECTORY_SEPARATOR . $iterator->getSubPathName();
        
        if ($item->isDir()) {
            if (!is_dir($target)) {
                mkdir($target, 0755, true);
            }
        } else {
            $targetDir = dirname($target);
            if (!is_dir($targetDir)) {
                mkdir($targetDir, 0755, true);
            }
            copy($item, $target);
        }
    }
    
    return true;
}