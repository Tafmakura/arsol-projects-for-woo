<?php
/**
 * Arsol Projects for Woo - Sidebar Hooks Examples
 * 
 * This file contains examples of how to use the sidebar hooks
 * to add custom content to project, proposal, and request sidebars.
 */

// Example 1: Add Custom Field to Project Sidebar
add_action('arsol_pfw_sidebar_fields_end', 'add_custom_project_field', 10, 2);
function add_custom_project_field($type, $data) {
    if ($type === 'project') {
        $custom_field = get_post_meta($data['project_id'], '_custom_field', true);
        if ($custom_field) {
            echo '<div class="custom-field-section">';
            echo '<p><strong>' . __('Custom Field:', 'arsol-pfw') . '</strong></p>';
            echo '<p>' . esc_html($custom_field) . '</p>';
            echo '</div>';
        }
    }
}

// Example 2: Add Custom Button to Proposal Sidebar
add_action('arsol_pfw_sidebar_buttons_end', 'add_custom_proposal_button', 10, 2);
function add_custom_proposal_button($type, $data) {
    if ($type === 'proposal') {
        echo '<div class="button-item">';
        echo '<a href="#" class="button button-secondary">' . __('Custom Action', 'arsol-pfw') . '</a>';
        echo '</div>';
    }
}