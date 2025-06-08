<?php
/**
 * Temporary debugging script for comment functionality
 * 
 * Place this file in the plugin root and access via browser to debug comment issues
 * Remove after troubleshooting is complete
 */

// Include WordPress
require_once '../../../wp-config.php';

// Get plugin settings
$settings = get_option('arsol_projects_settings', array());

echo "<h1>Arsol Projects Comment Debug</h1>";

echo "<h2>Plugin Settings</h2>";
echo "<pre>";
print_r($settings);
echo "</pre>";

echo "<h2>Comment Settings Check</h2>";
echo "<ul>";
echo "<li>Enable Project Comments: " . (isset($settings['enable_project_comments']) ? ($settings['enable_project_comments'] ? 'YES' : 'NO') : 'NOT SET') . "</li>";
echo "<li>Enable Request Comments: " . (isset($settings['enable_project_request_comments']) ? ($settings['enable_project_request_comments'] ? 'YES' : 'NO') : 'NOT SET') . "</li>";
echo "<li>Enable Proposal Comments: " . (isset($settings['enable_project_proposal_comments']) ? ($settings['enable_project_proposal_comments'] ? 'YES' : 'NO') : 'NOT SET') . "</li>";
echo "<li>Comment Roles: " . (isset($settings['comment_roles']) ? implode(', ', $settings['comment_roles']) : 'NOT SET') . "</li>";
echo "<li>Require Moderation: " . (isset($settings['require_comment_moderation']) ? ($settings['require_comment_moderation'] ? 'YES' : 'NO') : 'NOT SET') . "</li>";
echo "<li>Enable Notifications: " . (isset($settings['enable_comment_notifications']) ? ($settings['enable_comment_notifications'] ? 'YES' : 'NO') : 'NOT SET') . "</li>";
echo "</ul>";

// Check post type support
echo "<h2>Post Type Comment Support</h2>";
$post_types = array('arsol-project', 'arsol-pfw-request', 'arsol-pfw-proposal');
foreach ($post_types as $post_type) {
    $supports_comments = post_type_supports($post_type, 'comments');
    $is_enabled = \Arsol_Projects_For_Woo\Admin\Settings_General::is_comments_enabled_for_post_type($post_type);
    echo "<li><strong>$post_type:</strong> Support: " . ($supports_comments ? 'YES' : 'NO') . " | Enabled: " . ($is_enabled ? 'YES' : 'NO') . "</li>";
}

// Check sample posts
echo "<h2>Sample Posts Comment Status</h2>";
foreach ($post_types as $post_type) {
    $posts = get_posts(array(
        'post_type' => $post_type,
        'numberposts' => 3,
        'post_status' => 'publish'
    ));
    
    echo "<h3>$post_type Posts:</h3>";
    if (empty($posts)) {
        echo "<p>No posts found for this type</p>";
    } else {
        foreach ($posts as $test_post) {
            $comments_open = comments_open($test_post->ID);
            $comment_count = get_comments_number($test_post->ID);
            echo "<li>Post #{$test_post->ID} '{$test_post->post_title}': Comments Open: " . ($comments_open ? 'YES' : 'NO') . " | Count: $comment_count</li>";
        }
    }
}

// Check current user
echo "<h2>Current User</h2>";
$current_user = wp_get_current_user();
if ($current_user->ID == 0) {
    echo "<p>Not logged in</p>";
} else {
    echo "<p>User ID: {$current_user->ID}</p>";
    echo "<p>Roles: " . implode(', ', $current_user->roles) . "</p>";
    echo "<p>Can manage projects: " . (\Arsol_Projects_For_Woo\Admin\Admin_Capabilities::can_manage_projects($current_user->ID) ? 'YES' : 'NO') . "</p>";
}

// Check WordPress comment settings
echo "<h2>WordPress Comment Settings</h2>";
echo "<ul>";
echo "<li>Default comment status: " . get_option('default_comment_status') . "</li>";
echo "<li>Comment registration required: " . (get_option('comment_registration') ? 'YES' : 'NO') . "</li>";
echo "<li>Close comments for old posts: " . (get_option('close_comments_for_old_posts') ? 'YES' : 'NO') . "</li>";
echo "</ul>";

echo "<p><em>Debug completed. Remember to delete this file after troubleshooting.</em></p>";
?> 