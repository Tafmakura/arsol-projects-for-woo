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
        
        // Add Request Feedback metaboxes for different statuses
        add_meta_box(
            'arsol_request_onhold_feedback_metabox',
            __('On-Hold Feedback', 'arsol-pfw'),
            array($this, 'render_onhold_feedback_metabox'),
            'arsol-pfw-request',
            'normal',
            'high',
            array('__back_compat_meta_box' => false, 'class' => 'arsol-pfw-show-if-request-status-is-on-hold')
        );
        
        add_meta_box(
            'arsol_request_underreview_feedback_metabox',
            __('Under Review Feedback', 'arsol-pfw'),
            array($this, 'render_underreview_feedback_metabox'),
            'arsol-pfw-request',
            'normal',
            'high',
            array('__back_compat_meta_box' => false, 'class' => 'arsol-pfw-show-if-request-status-is-under-review')
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
                <input type="button" 
                       class="button button-secondary arsol-confirm-conversion" 
                       value="<?php _e('Convert to Proposal', 'arsol-pfw'); ?>" 
                       data-url="<?php echo esc_url($convert_url); ?>" 
                       data-message="<?php echo $confirm_message; ?>"
                       <?php disabled($is_disabled, true); ?> />
            </span>
        </div>
        <?php
    }

    /**
     * Render on-hold feedback metabox
     */
    public function render_onhold_feedback_metabox($post) {
        // Add nonce for security
        wp_nonce_field('request_onhold_feedback_metabox', 'request_onhold_feedback_metabox_nonce');

        // Get current values
        $feedback = get_post_meta($post->ID, '_arsol_pfw_request_onhold_feedback', true);
        ?>
        <div>
            <p class="description">
                <?php _e('Provide feedback to the customer explaining why this request is on hold and what actions they need to take.', 'arsol-pfw'); ?>
            </p>
            
            <div class="arsol-request-feedback-editor">
                <?php
                $editor_settings = array(
                    'textarea_name' => 'arsol_pfw_request_onhold_feedback',
                    'textarea_rows' => 8,
                    'media_buttons' => false,
                    'teeny' => false,
                    'quicktags' => array(
                        'buttons' => 'strong,em,ul,ol,li,link,close'
                    ),
                    'tinymce' => array(
                        'toolbar1' => 'bold,italic,bullist,numlist,link,unlink,undo,redo',
                        'toolbar2' => '',
                        'toolbar3' => ''
                    )
                );
                
                wp_editor($feedback, 'arsol_pfw_request_onhold_feedback', $editor_settings);
                ?>
            </div>
            
            <textarea 
                id="arsol_request_onhold_feedback_validation" 
                name="arsol_request_onhold_feedback_validation" 
                class="arsol-pfw-hidden" 
                data-required-when-status="on-hold"
                data-validation-message="<?php esc_attr_e('On-Hold feedback is required when request status is on-hold.', 'arsol-pfw'); ?>">
            </textarea>
        </div>
        <?php
    }

    /**
     * Render under review feedback metabox
     */
    public function render_underreview_feedback_metabox($post) {
        // Add nonce for security
        wp_nonce_field('request_underreview_feedback_metabox', 'request_underreview_feedback_metabox_nonce');

        // Get current values
        $feedback = get_post_meta($post->ID, '_arsol_pfw_request_underreview_feedback', true);
        ?>
        <div>
            <p class="description">
                <?php _e('Provide feedback to the customer about the current review process and any additional information needed.', 'arsol-pfw'); ?>
            </p>
            
            <div class="arsol-request-feedback-editor">
                <?php
                $editor_settings = array(
                    'textarea_name' => 'arsol_pfw_request_underreview_feedback',
                    'textarea_rows' => 8,
                    'media_buttons' => false,
                    'teeny' => false,
                    'quicktags' => array(
                        'buttons' => 'strong,em,ul,ol,li,link,close'
                    ),
                    'tinymce' => array(
                        'toolbar1' => 'bold,italic,bullist,numlist,link,unlink,undo,redo',
                        'toolbar2' => '',
                        'toolbar3' => ''
                    )
                );
                
                wp_editor($feedback, 'arsol_pfw_request_underreview_feedback', $editor_settings);
                ?>
            </div>
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
        
        // Save feedback for all three metaboxes
        
        // Save on-hold feedback
        if (isset($_POST['request_onhold_feedback_metabox_nonce']) && wp_verify_nonce($_POST['request_onhold_feedback_metabox_nonce'], 'request_onhold_feedback_metabox')) {
            if (isset($_POST['arsol_pfw_request_onhold_feedback'])) {
                $feedback = wp_kses_post($_POST['arsol_pfw_request_onhold_feedback']);
                update_post_meta($post_id, '_arsol_pfw_request_onhold_feedback', $feedback);
            }
        }
        
        // Save under review feedback
        if (isset($_POST['request_underreview_feedback_metabox_nonce']) && wp_verify_nonce($_POST['request_underreview_feedback_metabox_nonce'], 'request_underreview_feedback_metabox')) {
            if (isset($_POST['arsol_pfw_request_underreview_feedback'])) {
                $feedback = wp_kses_post($_POST['arsol_pfw_request_underreview_feedback']);
                update_post_meta($post_id, '_arsol_pfw_request_underreview_feedback', $feedback);
            }
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