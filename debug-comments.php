<?php
/**
 * Debug script to trace comment visibility logic
 */

// Simulate the comment checking logic
function debug_comments_logic($post_id) {
    echo "<h2>Debug Comments Logic for Post ID: $post_id</h2>";
    
    $post_type = get_post_type($post_id);
    echo "<p><strong>Post Type:</strong> $post_type</p>";
    
    // Check 1: CPT supports comments
    $cpt_supports_comments = post_type_supports($post_type, 'comments');
    echo "<p><strong>CPT Supports Comments:</strong> " . ($cpt_supports_comments ? 'YES' : 'NO') . "</p>";
    
    if (!$cpt_supports_comments) {
        echo "<p style='color: red;'>❌ <strong>FAILED:</strong> CPT does not support comments</p>";
        return false;
    }
    
    // Check general settings
    $general_settings = get_option('arsol_pfw_general_settings', array());
    echo "<p><strong>General Settings:</strong></p>";
    echo "<ul>";
    echo "<li>enable_project_comments: " . (isset($general_settings['enable_project_comments']) ? ($general_settings['enable_project_comments'] ? 'YES' : 'NO') : 'NOT SET') . "</li>";
    echo "<li>enable_project_request_comments: " . (isset($general_settings['enable_project_request_comments']) ? ($general_settings['enable_project_request_comments'] ? 'YES' : 'NO') : 'NOT SET') . "</li>";
    echo "<li>enable_project_proposal_comments: " . (isset($general_settings['enable_project_proposal_comments']) ? ($general_settings['enable_project_proposal_comments'] ? 'YES' : 'NO') : 'NOT SET') . "</li>";
    echo "</ul>";
    
    // Check 2: Get phase type
    $phase_type_map = array(
        'arsol-pfw-request' => 'request',
        'arsol-pfw-proposal' => 'proposal',
        'arsol-pfw-project' => 'project'
    );
    
    $phase_type = isset($phase_type_map[$post_type]) ? $phase_type_map[$post_type] : false;
    echo "<p><strong>Phase Type:</strong> " . ($phase_type ? $phase_type : 'NOT FOUND') . "</p>";
    
    if (!$phase_type) {
        echo "<p style='color: red;'>❌ <strong>FAILED:</strong> Could not determine phase type</p>";
        return false;
    }
    
    // Check 3: Get current stage
    $taxonomy_map = array(
        'arsol-pfw-project' => 'arsol-pfw-project-stage',
        'arsol-pfw-proposal' => 'arsol-pfw-proposal-stage',
        'arsol-pfw-request' => 'arsol-pfw-request-stage'
    );
    
    $taxonomy = isset($taxonomy_map[$post_type]) ? $taxonomy_map[$post_type] : '';
    echo "<p><strong>Stage Taxonomy:</strong> $taxonomy</p>";
    
    $stage_terms = wp_get_object_terms($post_id, $taxonomy, array('fields' => 'ids'));
    $current_stage_id = (!is_wp_error($stage_terms) && !empty($stage_terms)) ? intval($stage_terms[0]) : 0;
    echo "<p><strong>Current Stage ID:</strong> $current_stage_id</p>";
    
    if ($current_stage_id > 0) {
        $stage_term = get_term($current_stage_id);
        echo "<p><strong>Current Stage:</strong> " . ($stage_term ? $stage_term->name : 'Unknown') . "</p>";
    }
    
    // Check 4: Display settings
    $display_settings = get_option('arsol_pfw_display_comments_settings', array());
    echo "<p><strong>Display Settings:</strong></p>";
    echo "<pre>" . print_r($display_settings, true) . "</pre>";
    
    $visibility_key = $phase_type . '_visibility';
    $stages_key = $phase_type . '_stages';
    
    $visibility = isset($display_settings[$visibility_key]) ? $display_settings[$visibility_key] : 'hide';
    $selected_stages = isset($display_settings[$stages_key]) ? $display_settings[$stages_key] : array();
    
    echo "<p><strong>Visibility Key:</strong> $visibility_key = $visibility</p>";
    echo "<p><strong>Stages Key:</strong> $stages_key = " . print_r($selected_stages, true) . "</p>";
    
    // Check 5: Apply visibility rules
    echo "<p><strong>Applying Visibility Rules:</strong></p>";
    $selected_stages = array_map('intval', $selected_stages);
    $current_stage_id = intval($current_stage_id);
    
    echo "<ul>";
    echo "<li>Visibility: $visibility</li>";
    echo "<li>Selected stages (converted to int): " . print_r($selected_stages, true) . "</li>";
    echo "<li>Current stage ID (converted to int): $current_stage_id</li>";
    echo "</ul>";
    
    if ($visibility === 'hide') {
        if (empty($selected_stages)) {
            echo "<p style='color: green;'>✅ <strong>RESULT:</strong> Hide rule with no stages = Show on all stages = TRUE</p>";
            return true;
        } else {
            $result = !in_array($current_stage_id, $selected_stages);
            echo "<p style='color: " . ($result ? 'green' : 'red') . ";'>" . ($result ? '✅' : '❌') . " <strong>RESULT:</strong> Hide on selected stages = " . ($result ? 'TRUE' : 'FALSE') . "</p>";
            return $result;
        }
    } else { // 'show'
        if (empty($selected_stages)) {
            echo "<p style='color: red;'>❌ <strong>RESULT:</strong> Show rule with no stages = Show on no stages = FALSE</p>";
            return false;
        } else {
            $result = in_array($current_stage_id, $selected_stages);
            echo "<p style='color: " . ($result ? 'green' : 'red') . ";'>" . ($result ? '✅' : '❌') . " <strong>RESULT:</strong> Show only on selected stages = " . ($result ? 'TRUE' : 'FALSE') . "</p>";
            return $result;
        }
    }
}

// If running from command line with post ID
if (isset($argv[1])) {
    $post_id = intval($argv[1]);
    debug_comments_logic($post_id);
} else {
    echo "Usage: php debug-comments.php <post_id>\n";
    echo "Or add this to a test page and call debug_comments_logic(\$post_id)\n";
} 