<?php
// Debug script to test autoloader
define('ARSOL_PROJECTS_PLUGIN_DIR', __DIR__ . '/');

// Test the autoloader logic
$class = 'Arsol_Projects_For_Woo\Core\Assets';
$prefix = 'Arsol_Projects_For_Woo\\';
$base_dir = ARSOL_PROJECTS_PLUGIN_DIR . 'includes/';

$len = strlen($prefix);
if (strncmp($prefix, $class, $len) !== 0) {
    echo "Prefix doesn't match\n";
    exit;
}

$relative_class = substr($class, $len);

// Convert namespace to file path, handling case sensitivity
$file_parts = explode('\\', $relative_class);
$file_path = '';

foreach ($file_parts as $part) {
    if ($file_path !== '') {
        $file_path .= '/';
    }
    // Convert to lowercase to match actual directory structure
    $file_path .= strtolower($part);
}

$file = $base_dir . $file_path . '.php';

echo "Looking for class: $class\n";
echo "Prefix: $prefix\n";
echo "Base dir: $base_dir\n";
echo "Relative class: $relative_class\n";
echo "File parts: " . implode(', ', $file_parts) . "\n";
echo "File path: $file\n";
echo "File exists: " . (file_exists($file) ? 'YES' : 'NO') . "\n";

if (file_exists($file)) {
    echo "SUCCESS: File found!\n";
} else {
    echo "File not found!\n";
} 