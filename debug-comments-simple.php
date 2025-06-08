<?php
// Simple debug file for comment functionality
// Add this to your functions.php temporarily or run it as a standalone script

// If running standalone, include WordPress
if (!function_exists('get_option')) {
    require_once '../../../wp-config.php';
}

echo "<h1>Comment Debug</h1>";

// Check settings
$settings = get_option('arsol_projects_settings', array());
echo "<h2>Current Settings</h2>";
echo "<pre>";
print_r($settings);
echo "</pre>";

// Force enable comments for testing
echo "<h2>Enabling Comments for Testing</h2>";
$test_settings = array(
    'enable_project_comments' => '1',
    'enable_project_request_comments' => '1',
    'enable_project_proposal_comments' => '1',
    'comment_roles' => array('administrator', 'editor', 'author', 'contributor', 'subscriber'),
    'require_comment_moderation' => '0',
    'enable_comment_notifications' => '1',
);

$updated_settings = array_merge($settings, $test_settings);
update_option('arsol_projects_settings', $updated_settings);

echo "Settings updated. Comments should now be enabled.";

// Check post type support
echo "<h2>Post Type Support Check</h2>";
$post_types = array('arsol-project', 'arsol-pfw-request', 'arsol-pfw-proposal');
foreach ($post_types as $post_type) {
    $supports = post_type_supports($post_type, 'comments');
    $enabled = \Arsol_Projects_For_Woo\Admin\Settings_General::is_comments_enabled_for_post_type($post_type);
    echo "<p><strong>$post_type:</strong> Post Type Supports: " . ($supports ? 'YES' : 'NO') . " | Settings Enabled: " . ($enabled ? 'YES' : 'NO') . "</p>";
}

echo "<p><em>Visit your project pages now to check if comments appear.</em></p>";
?> 