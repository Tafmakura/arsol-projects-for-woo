<?php
// Simple test to isolate the issue
define('ARSOL_PFW_PLUGIN_DIR', __DIR__ . '/');
define('ARSOL_PROJECTS_PLUGIN_FILE', __DIR__ . '/arsol-projects-for-woo.php');
define('ARSOL_PROJECTS_PLUGIN_BASENAME', 'arsol-projects-for-woo/arsol-projects-for-woo.php');

// Mock WordPress functions to avoid hanging
if (!function_exists('add_action')) {
    function add_action($hook, $callback, $priority = 10, $accepted_args = 1) {
        // Mock function - do nothing
    }
}

if (!function_exists('plugin_dir_url')) {
    function plugin_dir_url($file) {
        return 'http://example.com/wp-content/plugins/arsol-projects-for-woo/';
    }
}

if (!function_exists('wp_register_style')) {
    function wp_register_style($handle, $src, $deps = array(), $ver = false, $media = 'all') {
        // Mock function - do nothing
    }
}

if (!function_exists('wp_register_script')) {
    function wp_register_script($handle, $src, $deps = array(), $ver = false, $in_footer = false) {
        // Mock function - do nothing
    }
}

if (!function_exists('file_exists')) {
    function file_exists($filename) {
        return false; // Mock to avoid file operations
    }
}

if (!function_exists('filemtime')) {
    function filemtime($filename) {
        return '1.0.0'; // Mock version
    }
}

// Setup autoloader
spl_autoload_register(function ($class) {
    $prefix = 'Arsol_Projects_For_Woo\\';
    $base_dir = ARSOL_PFW_PLUGIN_DIR . 'includes/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

    if (file_exists($file)) {
        require $file;
    }
});

echo "Testing Assets class...\n";
try {
    new \Arsol_Projects_For_Woo\Core\Assets();
    echo "Assets loaded successfully\n";
} catch (Exception $e) {
    echo "Assets error: " . $e->getMessage() . "\n";
}

echo "Testing Capabilities class...\n";
try {
    new \Arsol_Projects_For_Woo\Core\Capabilities();
    echo "Capabilities loaded successfully\n";
} catch (Exception $e) {
    echo "Capabilities error: " . $e->getMessage() . "\n";
}

echo "Test completed\n"; 