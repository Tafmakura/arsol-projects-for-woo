<?php
/**
 * Create Project Page
 *
 * This template acts as the main container for the create project form.
 * It implements endpoint-level override detection for optimal performance.
 *
 * @package Arsol_Projects_For_Woo
 * @version 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

// Detect if we're in edit mode
$is_edit_mode = !empty($_GET['edit']) && !empty($_GET['post_id']);
$post_id = $is_edit_mode ? intval($_GET['post_id']) : null;

// Endpoint-level override detection for performance
if ($is_edit_mode) {
    // Check for specific edit project form override first
    $override = \Arsol_Projects_For_Woo\Frontend_Template_Overrides::get_shortcode_override('[arsol_pfw_edit_project_form]');
    
    if ($override) {
        // Performance benefit: Complete bypass of plugin logic
        echo do_shortcode($override . ($post_id ? ' post_id="' . $post_id . '"' : ''));
    } else {
        // Check for general project form override
        $general_override = \Arsol_Projects_For_Woo\Frontend_Template_Overrides::get_shortcode_override('[arsol_pfw_project_form]');
        
        if ($general_override) {
            // Use general override with edit parameter
            echo do_shortcode($general_override . ' is_edit="true"' . ($post_id ? ' post_id="' . $post_id . '"' : ''));
        } else {
            // Use default plugin shortcode with edit mode
            echo do_shortcode('[arsol_pfw_project_form is_edit="true"' . ($post_id ? ' post_id="' . $post_id . '"' : '') . ']');
        }
    }
} else {
    // Create mode - check for project form override
    $override = \Arsol_Projects_For_Woo\Frontend_Template_Overrides::get_shortcode_override('[arsol_pfw_project_form]');
    
    if ($override) {
        // Performance benefit: Complete bypass of plugin logic  
        echo do_shortcode($override);
    } else {
        // Use default plugin shortcode for create mode
        echo do_shortcode('[arsol_pfw_project_form]');
    }
}
