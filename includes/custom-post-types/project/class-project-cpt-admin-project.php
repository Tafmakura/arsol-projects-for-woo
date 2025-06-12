<?php

namespace Arsol_Projects_For_Woo\Custom_Post_Types\Project\Admin;

if (!defined('ABSPATH')) exit;

class Project {
    public function __construct() {
        
        // Save project data
        add_action('save_post_arsol-project', array($this, 'save_project_details'));
    }

    /**
     */

    /**
     * Save project details
     */
    public function save_project_details($post_id) {
        // Check if our nonce is set
        if (!isset($_POST['project_header_nonce'])) {
            return;
        }

        // Verify that the nonce is valid
        if (!wp_verify_nonce($_POST['project_header_nonce'], 'project_header')) {
            return;
        }

        // If this is an autosave, our form has not been submitted, so we don't want to do anything
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        // Check the user's permissions
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        // Handle project status change
        if (isset($_POST['project_status'])) {
            $new_status = sanitize_text_field($_POST['project_status']);
            
            // Get the status before this save
            $current_status_terms = wp_get_object_terms($post_id, 'arsol-project-status', array('fields' => 'slugs'));
            $old_status = !empty($current_status_terms) ? $current_status_terms[0] : 'not-started';

            // Set start date on the first transition to 'in-progress'
            if ($new_status === 'in-progress' && $old_status !== 'in-progress') {
                if (empty(get_post_meta($post_id, '_project_start_date', true))) {
                    update_post_meta($post_id, '_project_start_date', current_time('mysql'));
                }
            }
            
            wp_set_object_terms($post_id, $new_status, 'arsol-project-status', false);
        }

        // Save project lead
        if (isset($_POST['project_lead'])) {
            update_post_meta($post_id, '_project_lead', sanitize_text_field($_POST['project_lead']));
        }

        // Save due date
        if (isset($_POST['project_due_date'])) {
            update_post_meta($post_id, '_project_due_date', sanitize_text_field($_POST['project_due_date']));
        }
    }
        // Save start date
        if (isset($_POST["project_start_date"])) {
            update_post_meta($post_id, "_project_start_date", sanitize_text_field($_POST["project_start_date"]));
        }
    }
}
