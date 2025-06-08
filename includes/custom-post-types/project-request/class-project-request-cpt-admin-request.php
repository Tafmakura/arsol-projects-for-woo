<?php

namespace Arsol_Projects_For_Woo\Custom_Post_Types\ProjectRequest\Admin;

if (!defined('ABSPATH')) exit;

class Request {
    public function __construct() {
        // Add meta boxes
        add_action('add_meta_boxes', array($this, 'add_request_details_meta_box'));
        // Save request details
        add_action('save_post_arsol-pfw-request', array($this, 'save_request_details'));
    }

    /**
     * Add request details meta box
     */
    public function add_request_details_meta_box() {
        ob_start();
        add_meta_box(
            'request_details',
            __('Request Details', 'arsol-pfw'),
            array($this, 'render_request_details_meta_box'),
            'arsol-pfw-request',
            'side',
            'default'
        );
    }

    /**
     * Render request details meta box
     */
    public function render_request_details_meta_box($post) {
        // Add nonce for security
        wp_nonce_field('request_details_meta_box', 'request_details_meta_box_nonce');

        // Get current values
        $current_status = wp_get_object_terms($post->ID, 'arsol-request-status', array('fields' => 'slugs'));
        $current_status = !empty($current_status) ? $current_status[0] : 'pending';
        $budget = get_post_meta($post->ID, '_request_budget', true);
        $start_date = get_post_meta($post->ID, '_request_start_date', true);
        $delivery_date = get_post_meta($post->ID, '_request_delivery_date', true);
        
        // Get statuses
        $statuses = get_terms(array(
            'taxonomy' => 'arsol-request-status',
            'hide_empty' => false,
        ));

        // Get author dropdown
        $author_dropdown = wp_dropdown_users(array(
            'name' => 'post_author_override',
            'selected' => $post->post_author,
            'include_selected' => true,
            'echo' => false,
            'class' => 'widefat'
        ));
        ?>
        <div class="request-details">
            <p>
                <label for="request_code" style="display:block;margin-bottom:5px;"><?php _e('Request Code:', 'arsol-pfw'); ?></label>
                <input type="text" 
                       id="request_code" 
                       value="<?php echo esc_attr($post->ID); ?>"
                       disabled
                       class="widefat">
            </p>

            <p>
                <label for="post_author_override" style="display:block;margin-bottom:5px;"><?php _e('Customer:', 'arsol-pfw'); ?></label>
                <?php echo $author_dropdown; ?>
            </p>

            <p>
                <label for="request_status" style="display:block;margin-bottom:5px;"><?php _e('Request Status:', 'arsol-pfw'); ?></label>
                <select name="request_status" id="request_status" class="widefat">
                    <?php foreach ($statuses as $status) : ?>
                        <option value="<?php echo esc_attr($status->slug); ?>" <?php selected($current_status, $status->slug); ?>>
                            <?php echo esc_html($status->name); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </p>

            <p>
                <label for="request_budget" style="display:block;margin-bottom:5px;"><?php _e('Budget:', 'arsol-pfw'); ?></label>
                <input type="number" 
                       id="request_budget" 
                       name="request_budget" 
                       value="<?php echo esc_attr($budget); ?>"
                       class="widefat"
                       step="0.01"
                       min="0">
            </p>

            <p>
                <label for="request_start_date" style="display:block;margin-bottom:5px;"><?php _e('Required Start Date:', 'arsol-pfw'); ?></label>
                <input type="date" 
                       id="request_start_date" 
                       name="request_start_date" 
                       value="<?php echo esc_attr($start_date); ?>"
                       class="widefat">
            </p>

            <p>
                <label for="request_delivery_date" style="display:block;margin-bottom:5px;"><?php _e('Required Delivery Date:', 'arsol-pfw'); ?></label>
                <input type="date" 
                       id="request_delivery_date" 
                       name="request_delivery_date" 
                       value="<?php echo esc_attr($delivery_date); ?>"
                       class="widefat">
            </p>
        </div>
        <div class="major-actions" style="padding-top:10px; border-top: 1px solid #ddd; margin-top: 10px;">
            <?php
                $convert_url = admin_url('admin-post.php?action=arsol_convert_to_proposal&request_id=' . $post->ID);
                $convert_url = wp_nonce_url($convert_url, 'arsol_convert_to_proposal_nonce');
            ?>
            <a href="<?php echo esc_url($convert_url); ?>" class="button button-secondary"><?php _e('Convert to Proposal', 'arsol-pfw'); ?></a>
        </div>
        <?php
    }

    /**
     * Save request details
     */
    public function save_request_details($post_id) {
        // Check if our nonce is set
        if (!isset($_POST['request_details_meta_box_nonce'])) {
            return;
        }

        // Verify that the nonce is valid
        if (!wp_verify_nonce($_POST['request_details_meta_box_nonce'], 'request_details_meta_box')) {
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

        // Save request status
        if (isset($_POST['request_status'])) {
            wp_set_object_terms($post_id, sanitize_text_field($_POST['request_status']), 'arsol-request-status', false);
        }

        // Save budget
        if (isset($_POST['request_budget'])) {
            update_post_meta($post_id, '_request_budget', sanitize_text_field($_POST['request_budget']));
        }

        // Save start date
        if (isset($_POST['request_start_date'])) {
            update_post_meta($post_id, '_request_start_date', sanitize_text_field($_POST['request_start_date']));
        }

        // Save delivery date
        if (isset($_POST['request_delivery_date'])) {
            update_post_meta($post_id, '_request_delivery_date', sanitize_text_field($_POST['request_delivery_date']));
        }
    }
}