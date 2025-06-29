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
        
        // Add customer feedback sections via hook (after header)
        add_action('edit_form_after_title', array($this, 'render_customer_feedback_sections'), 15);
    }

    /**
     * Render request details meta box
     */
    public function render_request_details_meta_box($post) {
        // Add nonce for security
        wp_nonce_field('request_details_meta_box', 'request_details_meta_box_nonce');

        // Get current values
        $current_stage = wp_get_object_terms($post->ID, 'arsol-pfw-request-stage', array('fields' => 'slugs'));
        $current_status = !empty($current_status) ? $current_status[0] : 'pending';
        $current_stage = wp_get_object_terms($post->ID, 'arsol-pfw-request-stage', array('fields' => 'slugs'));
        ?>
        <div class="notice notice-warning" style="margin: 10px 0;">
            <p><strong><?php _e('Important:', 'arsol-pfw'); ?></strong></p>
            <?php _e('This action will create a new project proposal based on this request and permanently delete the original request. The request stage must be set to "Approved" before conversion. This action cannot be undone.', 'arsol-pfw'); ?>
        </div>
        
        <div class="arsol-pfw-convert-section">
            <div class="arsol-pfw-convert-actions">
                <div class="arsol-pfw-convert-button-container">
                    <?php 
                    $can_convert = !empty($current_stage) && !is_wp_error($current_stage) && $current_stage[0] === 'approved';
                    $button_class = $can_convert ? 'button-primary' : 'button-secondary';
                    $button_disabled = $can_convert ? '' : 'disabled="disabled"';
                    $button_text = $can_convert 
                        ? __('Convert Request to Proposal', 'arsol-pfw') 
                        : __('The request must be in approved stage before it can be converted.', 'arsol-pfw');
                    ?>
                    <button 
                        type="submit" 
                        name="arsol_convert_request" 
                        class="arsol-pfw-convert-button <?php echo esc_attr($button_class); ?>" 
                        <?php echo $button_disabled; ?>
                        data-post-id="<?php echo esc_attr($post->ID); ?>"
                        title="<?php echo esc_attr($button_text); ?>">
                        <?php echo $can_convert 
                            ? __('🔀 Convert to Proposal', 'arsol-pfw') 
                            : __('❌ Cannot Convert (Stage Required)', 'arsol-pfw'); ?>
                    </button>
                    
                    <?php if (!$can_convert): ?>
                    <p class="description" style="margin-top: 5px; color: #d63638;">
                        <?php echo $button_text; ?>
                    </p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Render customer feedback sections (via hook)
     */
    public function render_customer_feedback_sections() {
        global $post;
        
        // Only show for requests on the edit screen
        if (!$post || $post->post_type !== 'arsol-pfw-request') {
            return;
        }
        
        // On-hold feedback section
        ?>
        <div id="arsol_request_onhold_feedback_section" class="arsol-pfw-project postbox arsol-pfw-show-if-request-stage-is-on-hold" style="display: none;">
            <div class="postbox-header">
                <h2 class="hndle ui-sortable-handle"><?php _e('On-Hold Feedback', 'arsol-pfw'); ?></h2>
            </div>
            <div class="inside">
                <p><?php _e('Please provide feedback about why this request is on hold:', 'arsol-pfw'); ?></p>
                <?php
                wp_editor(
                    get_post_meta($post->ID, '_arsol_pfw_request_onhold_feedback', true),
                    'arsol_request_onhold_feedback',
                    array(
                        'textarea_name' => 'arsol_request_onhold_feedback',
                        'textarea_rows' => 5,
                        'media_buttons' => false,
                        'teeny' => true,
                        'quicktags' => false
                    )
                );
                ?>
                <input type="hidden" 
                    class="arsol-pfw-validation-field" 
                    data-validation-type="conditional" 
                    data-validation-condition="request_stage"
                    data-validation-value="on-hold"
                    data-validation-required="arsol_request_onhold_feedback"
                    data-validation-message="<?php esc_attr_e('On-Hold feedback is required when request stage is on-hold.', 'arsol-pfw'); ?>">
            </div>
        </div>
        
        <div id="arsol_request_underreview_feedback_section" class="arsol-pfw-project postbox arsol-pfw-show-if-request-stage-is-under-review" style="display: none;">
            <div class="postbox-header">
                <h2 class="hndle ui-sortable-handle"><?php _e('Under Review Notes', 'arsol-pfw'); ?></h2>
            </div>
            <div class="inside">
                <p><?php _e('Add any notes or comments about the review process:', 'arsol-pfw'); ?></p>
                <?php
                wp_editor(
                    get_post_meta($post->ID, '_arsol_pfw_request_underreview_feedback', true),
                    'arsol_request_underreview_feedback',
                    array(
                        'textarea_name' => 'arsol_request_underreview_feedback',
                        'textarea_rows' => 5,
                        'media_buttons' => false,
                        'teeny' => true,
                        'quicktags' => false
                    )
                );
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

        // Save request stage from column 1
        if (isset($_POST['request_stage'])) {
            wp_set_object_terms($post_id, sanitize_text_field($_POST['request_stage']), 'arsol-pfw-request-stage', false);
        }
        
        // Save feedback for all three metaboxes
        
        // Save on-hold feedback
        if (isset($_POST['arsol_request_onhold_feedback'])) {
            $feedback = wp_kses_post($_POST['arsol_request_onhold_feedback']);
            update_post_meta($post_id, '_arsol_pfw_request_onhold_feedback', $feedback);
        }
        
        // Save under review feedback
        if (isset($_POST['arsol_request_underreview_feedback'])) {
            $feedback = wp_kses_post($_POST['arsol_request_underreview_feedback']);
            update_post_meta($post_id, '_arsol_pfw_request_underreview_feedback', $feedback);
        }
        
        // Handle conversion after save (WordPress-native approach)
        if (isset($_POST['arsol_convert_after_save']) && !empty($_POST['arsol_convert_after_save'])) {
            // Check if request is in approved stage for conversion
            $current_stage = wp_get_object_terms($post_id, 'arsol-pfw-request-stage', array('fields' => 'slugs'));
            
            if (empty($current_stage) || is_wp_error($current_stage) || $current_stage[0] !== 'approved') {
                $stage_display = !empty($current_stage) && !is_wp_error($current_stage) ? ucfirst(str_replace('-', ' ', $current_stage[0])) : 'Unknown';
                
                wp_die('
                    <h1>' . __('Conversion Error', 'arsol-pfw') . '</h1>
                    <p>' . sprintf(__('Cannot convert request. Stage is "%s", must be "approved".', 'arsol-pfw'), $stage_display) . '</p>
                    <p><a href="' . get_edit_post_link($post_id) . '">' . __('Return to Request', 'arsol-pfw') . '</a></p>
                ');
            }
        }
    }
}