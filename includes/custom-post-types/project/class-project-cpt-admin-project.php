<?php

namespace Arsol_Projects_For_Woo\Custom_Post_Types\Project\Admin;

if (!defined('ABSPATH')) exit;

class Project {
    public function __construct() {
        // Add meta boxes for single project admin screen
        add_action('add_meta_boxes', array($this, 'add_project_details_meta_box'));
        // Save project data
        add_action('save_post_arsol-project', array($this, 'save_project_details'));
    }

    /**
     * Add project details meta box
     */
    public function add_project_details_meta_box() {
        add_meta_box(
            'project_details_meta_box',
            __('Project Actions', 'arsol-pfw'),
            array($this, 'render_project_details_meta_box'),
            'arsol-project',
            'side',
            'default'
        );
    }

    /**
     * Render project details meta box
     */
    public function render_project_details_meta_box($post) {
        // Add nonce for security
        wp_nonce_field('project_details_meta_box', 'project_details_meta_box_nonce');
        ?>
        <div class="project-details">
            <!-- Main content area for any future project-specific content -->
        </div>
        
        <div class="major-actions">
            <?php if ($post->post_status === 'publish'): ?>
                <input type="submit" id="save-post" name="save" class="button button-primary" value="<?php _e('Update', 'arsol-pfw'); ?>">
            <?php else: ?>
                <input type="submit" id="publish" name="publish" class="button button-primary" value="<?php _e('Publish', 'arsol-pfw'); ?>">
            <?php endif; ?>
        </div>
        <?php
    }

    /**
     * Save project details
     */
    public function save_project_details($post_id) {
        // Check if our nonce is set
        if (!isset($_POST['project_details_meta_box_nonce'])) {
            return;
        }

        // Verify that the nonce is valid
        if (!wp_verify_nonce($_POST['project_details_meta_box_nonce'], 'project_details_meta_box')) {
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
                if (empty(get_post_meta($post_id, '_arsol_pfw_project_start_date', true))) {
                    update_post_meta($post_id, '_arsol_pfw_project_start_date', current_time('mysql'));
                }
            }
            
            wp_set_object_terms($post_id, $new_status, 'arsol-project-status', false);
        }

        // Set default start date if not already set
        if (empty(get_post_meta($post_id, '_arsol_pfw_project_start_date', true))) {
            update_post_meta($post_id, '_arsol_pfw_project_start_date', current_time('mysql'));
        }

        // Save project lead
        if (isset($_POST['project_lead'])) {
            update_post_meta($post_id, '_arsol_pfw_project_lead', sanitize_text_field($_POST['project_lead']));
        }

        // Save project start date
        if (isset($_POST['project_start_date'])) {
            update_post_meta($post_id, '_arsol_pfw_project_start_date', sanitize_text_field($_POST['project_start_date']));
        }

        // Save project due date
        if (isset($_POST['project_due_date'])) {
            update_post_meta($post_id, '_arsol_pfw_project_due_date', sanitize_text_field($_POST['project_due_date']));
        }
    }
}