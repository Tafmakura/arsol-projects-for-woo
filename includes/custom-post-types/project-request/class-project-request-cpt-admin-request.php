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
        add_meta_box(
            'request_details',
            __('Request Details', 'arsol-pfw'),
            array($this, 'render_request_details_meta_box'),
            'arsol-pfw-request',
            'normal',
            'high'
        );
    }

    /**
     * Render request details meta box
     */
    public function render_request_details_meta_box($post) {
        // Add nonce for security
        wp_nonce_field('request_details_nonce', 'request_details_nonce');

        // Get request details
        $request_code = get_post_meta($post->ID, '_request_code', true);
        $customer = get_user_by('id', $post->post_author);
        $status = wp_get_object_terms($post->ID, 'arsol-request-status', array('fields' => 'names'));
        $budget = get_post_meta($post->ID, '_request_budget', true);
        $timeline = get_post_meta($post->ID, '_request_timeline', true);

        // Output fields
        ?>
        <div class="request-details">
            <p>
                <label for="request_code"><?php _e('Request Code:', 'arsol-pfw'); ?></label>
                <input type="text" id="request_code" name="request_code" value="<?php echo esc_attr($request_code); ?>" readonly>
            </p>

            <p>
                <label for="customer"><?php _e('Customer:', 'arsol-pfw'); ?></label>
                <input type="text" id="customer" value="<?php echo esc_attr($customer ? $customer->display_name : ''); ?>" readonly>
            </p>

            <p>
                <label for="request_status"><?php _e('Status:', 'arsol-pfw'); ?></label>
                <select id="request_status" name="request_status">
                    <?php
                    $statuses = get_terms('arsol-request-status', array('hide_empty' => false));
                    if (!empty($statuses) && !is_wp_error($statuses)) {
                        foreach ($statuses as $status_term) {
                            printf(
                                '<option value="%s" %s>%s</option>',
                                esc_attr($status_term->slug),
                                selected(!empty($status) && $status[0] === $status_term->name, true, false),
                                esc_html($status_term->name)
                            );
                        }
                    }
                    ?>
                </select>
            </p>

            <p>
                <label for="request_budget"><?php _e('Budget:', 'arsol-pfw'); ?></label>
                <input type="number" id="request_budget" name="request_budget" value="<?php echo esc_attr($budget); ?>" step="0.01" min="0">
            </p>

            <p>
                <label for="request_timeline"><?php _e('Timeline (days):', 'arsol-pfw'); ?></label>
                <input type="number" id="request_timeline" name="request_timeline" value="<?php echo esc_attr($timeline); ?>" min="1">
            </p>
        </div>
        <?php
    }

    /**
     * Save request details
     */
    public function save_request_details($post_id) {
        // Check if nonce is set
        if (!isset($_POST['request_details_nonce'])) {
            return;
        }

        // Verify nonce
        if (!wp_verify_nonce($_POST['request_details_nonce'], 'request_details_nonce')) {
            return;
        }

        // Check if this is an autosave
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        // Check user permissions
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        // Save status
        if (isset($_POST['request_status'])) {
            wp_set_object_terms($post_id, sanitize_text_field($_POST['request_status']), 'arsol-request-status');
        }

        // Save budget
        if (isset($_POST['request_budget'])) {
            update_post_meta($post_id, '_request_budget', floatval($_POST['request_budget']));
        }

        // Save timeline
        if (isset($_POST['request_timeline'])) {
            update_post_meta($post_id, '_request_timeline', intval($_POST['request_timeline']));
        }
    }
}
