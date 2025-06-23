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
            __('Project Actions', 'arsol-pfw'),
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
        ?>
        <p class="request-conversion-description">
            <?php _e('This action will create a new project proposal based on this request and permanently delete the original request. The request status must be set to "Approved" before conversion. This action cannot be undone.', 'arsol-pfw'); ?>
        </p>
        
        <div class="major-actions">
            <?php if ($post->post_status === 'publish'): ?>
                <input type="submit" id="save-post" name="save" class="button button-primary" value="<?php _e('Update', 'arsol-pfw'); ?>">
            <?php else: ?>
                <input type="submit" id="publish" name="publish" class="button button-primary" value="<?php _e('Publish', 'arsol-pfw'); ?>">
            <?php endif; ?>
            
            <?php
            $is_disabled = $current_status !== 'approved';
            $convert_url = admin_url('admin-post.php?action=arsol_convert_to_proposal&request_id=' . $post->ID);
            $convert_url = wp_nonce_url($convert_url, 'arsol_convert_to_proposal_nonce');
            $confirm_message = esc_js(__('Are you sure you want to convert this request to a proposal? This action cannot be undone and will delete your current request request.', 'arsol-pfw'));
            $tooltip_text = $is_disabled
                ? __('The request must be in approved status before it can be converted.', 'arsol-pfw')
                : __('Converts this request into a new proposal.', 'arsol-pfw');
            ?>
            <span title="<?php echo esc_attr($tooltip_text); ?>">
                <a href="#" 
                   class="button button-secondary arsol-confirm-conversion<?php if ($is_disabled) echo ' disabled'; ?>" 
                   data-url="<?php echo esc_url($convert_url); ?>" 
                   data-message="<?php echo $confirm_message; ?>"
                   <?php disabled($is_disabled, true); ?>>
                   <?php _e('Convert to Proposal', 'arsol-pfw'); ?>
                </a>
            </span>
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

        // Save request status from column 1
        if (isset($_POST['request_status'])) {
            wp_set_object_terms($post_id, sanitize_text_field($_POST['request_status']), 'arsol-request-status', false);
        }
        
        // Handle conversion after save (WordPress-native approach)
        if (isset($_POST['arsol_convert_after_save']) && !empty($_POST['arsol_convert_after_save'])) {
            // Check if request is in approved status for conversion
            $current_status = wp_get_object_terms($post_id, 'arsol-request-status', array('fields' => 'slugs'));
            $current_status = !empty($current_status) ? $current_status[0] : '';
            
            if ($current_status === 'approved') {
                // Sanitize and redirect to conversion URL
                $conversion_url = esc_url_raw($_POST['arsol_convert_after_save']);
                
                // Add a small delay to ensure save is complete, then redirect
                add_action('admin_notices', function() use ($conversion_url) {
                    echo '<script type="text/javascript">
                        setTimeout(function() {
                            window.location.href = "' . $conversion_url . '";
                        }, 100);
                    </script>';
                });
            } else {
                // Show error notice if not approved
                add_action('admin_notices', function() use ($current_status) {
                    $status_display = $current_status ?: 'none';
                    echo '<div class="notice notice-error is-dismissible">
                        <p>' . sprintf(__('Cannot convert request. Status is "%s", must be "approved".', 'arsol-pfw'), $status_display) . '</p>
                    </div>';
                });
            }
        }
    }
}