<?php

namespace Arsol_Projects_For_Woo\Custom_Post_Types\Request\Admin;

if (!defined('ABSPATH')) exit;

class Single_Controller {
    public function __construct() {
        // Add meta boxes for single request admin screen
        add_action('add_meta_boxes', array($this, 'add_request_details_meta_box'));
        // Save request data
        add_action('save_post_arsol-pfw-request', array($this, 'save_request_details'));
        // Prevent request deletion if tied proposals exist
        add_action('before_delete_post', array($this, 'prevent_request_deletion_with_proposals'));
    }

    /**
     * Add request actions meta box
     */
    public function add_request_details_meta_box() {
        ob_start();
        add_meta_box(
            'arsol-pfw-request-actions-metabox',
            __('Request Actions', 'arsol-pfw'),
            array($this, 'render_request_actions_metabox'),
            'arsol-pfw-request',
            'side',
            'high'
        );
        
        // Customer Notice metabox
        add_meta_box(
            'arsol-pfw-request-customer-notice-metabox',
            __('Customer Notice', 'arsol-pfw'),
            array($this, 'render_customer_notice_metabox'),
            'arsol-pfw-request',
            'normal',
            'high'
        );
    }

    /**
     * Render request actions metabox
     */
    public function render_request_actions_metabox($post) {
        // Add nonce for security
        wp_nonce_field('arsol-pfw-request-actions-metabox', 'arsol_pfw_request_actions_metabox_nonce');

        // Check if request is published for conversion eligibility  
        $is_disabled = $post->post_status !== 'publish';
        ?>
        <div class="major-actions">
            <?php if ($post->post_status === 'publish'): ?>
                <?php submit_button(__('Update', 'arsol-pfw'), 'primary', 'save', false, array('id' => 'save-post')); ?>
            <?php else: ?>
                <?php submit_button(__('Publish', 'arsol-pfw'), 'primary', 'publish', false, array('id' => 'publish')); ?>
            <?php endif; ?>
            
            <?php
            $convert_url = admin_url('admin-post.php?action=arsol_convert_to_proposal&request_id=' . $post->ID);
            $convert_url = wp_nonce_url($convert_url, 'arsol_convert_to_proposal_nonce');
            $confirm_message = esc_attr(__('Are you sure you want to convert this request to a proposal? This action cannot be undone and will delete your current request.', 'arsol-pfw'));
            $tooltip_text = $is_disabled
                ? __('The request must be published before it can be converted.', 'arsol-pfw')
                : __('Converts this request into a new proposal.', 'arsol-pfw');
            ?>
            <?php if (!$is_disabled): ?>
                <a href="<?php echo esc_url($convert_url); ?>" 
                   class="button button-secondary" 
                   onclick="return confirm('<?php echo $confirm_message; ?>');">
                    <?php _e('Convert to Proposal', 'arsol-pfw'); ?>
                </a>
            <?php else: ?>
            <span title="<?php echo esc_attr($tooltip_text); ?>">
                    <button class="button button-secondary" disabled><?php _e('Convert to Proposal', 'arsol-pfw'); ?></button>
            </span>
            <?php endif; ?>
        </div>
        <?php
    }

    /**
     * Render customer notice metabox
     */
    public function render_customer_notice_metabox($post) {
        $this->render_customer_notice_content($post);
    }

    /**
     * Render customer notice content
     */
    public function render_customer_notice_content($post) {
        // Add nonce for security
        wp_nonce_field('request_customer_notice_section', 'request_customer_notice_section_nonce');

        // Get current values using Request entity
        $request = new \Arsol_Projects_For_Woo\Custom_Post_Types\Arsol_PFW_Request($post->ID);
        $notice = $request->get_customer_notice();
        ?>
        <div>
            <p class="description">
                <?php _e('Add any important notices or updates that should be communicated to the customer regarding this request.', 'arsol-pfw'); ?>
            </p>
            
            <div class="arsol-request-feedback-editor">
                <?php
                $editor_settings = array(
                    'textarea_name' => 'arsol_pfw_request_customer_notice',
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
                
                wp_editor($notice, 'arsol_pfw_request_customer_notice', $editor_settings);
                ?>
            </div>
        </div>
        <?php
    }

    /**
     * Save request details
     */
    public function save_request_details($post_id) {
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
        
        // Get request object to use setter methods
        $request = new \Arsol_Projects_For_Woo\Custom_Post_Types\Arsol_PFW_Request($post_id);
        
        if (isset($_POST['request_budget'])) {
            $request->set_requested_project_budget(array('amount' => sanitize_text_field($_POST['request_budget'])));
        }
        
        if (isset($_POST['request_start_date'])) {
            $request->set_requested_project_start_date(sanitize_text_field($_POST['request_start_date']));
        }
        
        if (isset($_POST['request_due_date'])) {
            $request->set_requested_project_due_date(sanitize_text_field($_POST['request_due_date']));
        }
        
        // Save customer notice
        if (isset($_POST['request_customer_notice_section_nonce']) && wp_verify_nonce($_POST['request_customer_notice_section_nonce'], 'request_customer_notice_section')) {
            if (isset($_POST['arsol_pfw_request_customer_notice'])) {
                $request->set_customer_notice(wp_kses_post($_POST['arsol_pfw_request_customer_notice']));
            }
        }
        
        // Save customer ID from customer_id field
        if (isset($_POST['customer_id']) && !empty($_POST['customer_id'])) {
            $request->set_customer_id(intval($_POST['customer_id']));
        }
        
        // Save all changes
        $request->save();
        
        // Handle conversion after save (WordPress-native approach)
        if (isset($_POST['arsol_convert_after_save']) && !empty($_POST['arsol_convert_after_save'])) {
            // Check if request is published for conversion
            if (get_post_status($post_id) === 'publish') {
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
                // Show error notice if not published
                add_action('admin_notices', function() {
                    echo '<div class="notice notice-error is-dismissible">
                        <p>' . __('Cannot convert request. Request must be published before conversion.', 'arsol-pfw') . '</p>
                    </div>';
                });
            }
        }
    }

    /**
     * Prevent request deletion if tied proposals exist
     */
    public function prevent_request_deletion_with_proposals($post_id) {
        // Check if the post being deleted is a request
        if (get_post_type($post_id) === 'arsol-pfw-request') {
            // Get proposals associated with the request
            $proposals = get_posts(array(
                'post_type' => 'arsol-pfw-proposal',
                'meta_key' => '_arsol_pfw_request_id',
                'meta_value' => $post_id,
                'post_status' => 'any'
            ));

            // If there are proposals associated with the request, prevent deletion
            if (!empty($proposals)) {
                // Show error notice
                add_action('admin_notices', function() {
                    echo '<div class="notice notice-error is-dismissible">
                        <p>' . __('Cannot delete request. There are proposals associated with this request.', 'arsol-pfw') . '</p>
                    </div>';
                });
                return false; // Prevent deletion
            }
        }
        return true; // Allow deletion
    }
}