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

        // Get current values
        $current_status_terms = wp_get_object_terms($post->ID, 'arsol-project-status', array('fields' => 'slugs'));
        $current_status = !empty($current_status_terms) ? $current_status_terms[0] : 'not-started';

        $due_date = get_post_meta($post->ID, '_project_due_date', true);
        $standard_order_id = get_post_meta($post->ID, '_standard_order_id', true);
        $recurring_order_id = get_post_meta($post->ID, '_recurring_order_id', true);
        
        // Get statuses, excluding 'not-started' if the current status is not 'not-started'
        $statuses_args = array(
            'taxonomy'   => 'arsol-project-status',
            'hide_empty' => false,
        );
        if ($current_status !== 'not-started') {
            $not_started_term = get_term_by('slug', 'not-started', 'arsol-project-status');
            if ($not_started_term) {
                $statuses_args['exclude'] = array($not_started_term->term_id);
            }
        }
        $statuses = get_terms($statuses_args);

        // Get author dropdown
        $author_dropdown = wp_dropdown_users(array(
            'name' => 'post_author_override',
            'selected' => $post->post_author,
            'include_selected' => true,
            'echo' => false,
            'class' => 'widefat'
        ));
        ?>
        <div class="project-details">
            <p>
                <label for="project_id" style="display:block;margin-bottom:5px;"><?php _e('Project ID:', 'arsol-pfw'); ?></label>
                <input type="text" 
                       id="project_id" 
                       value="<?php echo esc_attr($post->ID); ?>"
                       disabled
                       class="widefat">
            </p>

            <p>
                <label for="post_author_override"><?php _e('Customer:', 'arsol-pfw'); ?></label>
                <?php echo $author_dropdown; ?>
            </p>

            <p>
                <label for="project_status"><?php _e('Project Status:', 'arsol-pfw'); ?></label>
                <select name="project_status" id="project_status" style="width:100%">
                    <?php foreach ($statuses as $status) : ?>
                        <option value="<?php echo esc_attr($status->slug); ?>" <?php selected($current_status, $status->slug); ?>>
                            <?php echo esc_html($status->name); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </p>

            <p>
                <label for="project_due_date"><?php _e('Due Date:', 'arsol-pfw'); ?></label>
                <input type="date" 
                       id="project_due_date" 
                       name="project_due_date" 
                       value="<?php echo esc_attr($due_date); ?>"
                       style="width:100%">
            </p>

            <?php if ($standard_order_id || $recurring_order_id) : ?>
                <p>
                    <label><?php _e('Proposal Invoices:', 'arsol-pfw'); ?></label>
                    <span class="arsol-pfw-invoice-links">
                        <?php 
                        $links = array();
                        if ($standard_order_id) {
                            $links[] = '<a href="' . get_edit_post_link($standard_order_id) . '">#' . esc_html($standard_order_id) . '</a>';
                        }
                        if ($recurring_order_id) {
                             $links[] = '<a href="' . get_edit_post_link($recurring_order_id) . '">#' . esc_html($recurring_order_id) . '</a>';
                        }
                        echo implode(', ', $links);
                        ?>
                    </span>
                </p>
            <?php endif; ?>
            
            <div class="major-actions" style="margin-top: 20px;">
                <input type="submit" class="button button-primary" value="<?php echo ($post->post_status === 'publish') ? __('Update', 'arsol-pfw') : __('Publish', 'arsol-pfw'); ?>" style="width: 100%;">
            </div>
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
                if (empty(get_post_meta($post_id, '_project_start_date', true))) {
                    update_post_meta($post_id, '_project_start_date', current_time('mysql'));
                }
            }
            
            wp_set_object_terms($post_id, $new_status, 'arsol-project-status', false);
        }

        // Save project lead (from header column)
        if (isset($_POST['project_lead'])) {
            update_post_meta($post_id, '_project_lead', sanitize_text_field($_POST['project_lead']));
        }

        // Save project start date (from header column)
        if (isset($_POST['project_start_date'])) {
            update_post_meta($post_id, '_project_start_date', sanitize_text_field($_POST['project_start_date']));
        }

        // Save due date
        if (isset($_POST['project_due_date'])) {
            update_post_meta($post_id, '_project_due_date', sanitize_text_field($_POST['project_due_date']));
        }
    }
}