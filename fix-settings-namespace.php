<?php
/**
 * Script to fix Settings\General namespace references
 */

// Get all PHP files in the project
function getPhpFiles($dir) {
    $files = [];
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS)
    );
    
    foreach ($iterator as $file) {
        if ($file->isFile() && $file->getExtension() === 'php') {
            $files[] = $file->getPathname();
        }
    }
    
    return $files;
}

// Update a single file
function updateFile($filepath) {
    $content = file_get_contents($filepath);
    if ($content === false) {
        echo "Could not read file: $filepath\n";
        return false;
    }
    
    $original = $content;
    $content = str_replace('Settings\General', 'Settings\\General', $content);
    
    if ($content !== $original) {
        $result = file_put_contents($filepath, $content);
        if ($result === false) {
            echo "Could not write file: $filepath\n";
            return false;
        }
        echo "Updated: $filepath\n";
        return true;
    }
    
    return false;
}

// Main execution
$projectDir = __DIR__;
$phpFiles = getPhpFiles($projectDir);

echo "Found " . count($phpFiles) . " PHP files\n";
echo "Fixing Settings\General namespace references...\n\n";

$updatedCount = 0;
foreach ($phpFiles as $file) {
    if (updateFile($file)) {
        $updatedCount++;
    }
}

echo "\nUpdated $updatedCount files\n";
echo "Done!\n"; 