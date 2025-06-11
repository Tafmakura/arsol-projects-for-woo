<?php

namespace Arsol_Projects_For_Woo\Custom_Post_Types\ProjectProposal\Admin;

if (!defined('ABSPATH')) exit;

class Proposal {
    public function __construct() {
        // Add meta boxes for single proposal admin screen
        add_action('add_meta_boxes', array($this, 'add_proposal_details_meta_box'));
        add_action('add_meta_boxes', array($this, 'add_customer_request_details_meta_box'));
        // Add styles to hide metaboxes initially
        add_action('admin_head-post.php', array($this, 'hide_metaboxes_initially'));
        add_action('admin_head-post-new.php', array($this, 'hide_metaboxes_initially'));
        // Save proposal data
        add_action('save_post_arsol-pfw-proposal', array($this, 'save_proposal_details'));
        // Action to set review status when a proposal is published
        add_action('transition_post_status', array($this, 'set_proposal_review_status'), 10, 3);

        // Setup confirm conversion script
        add_action('admin_footer', array($this, 'output_confirm_conversion_script'));
    }

    public function set_proposal_review_status($new_status, $old_status, $post) {
        if ($post->post_type === 'arsol-pfw-proposal' && $new_status === 'publish' && $old_status !== 'publish') {
            // Set the review status to 'under-review'
            wp_set_object_terms($post->ID, 'under-review', 'arsol-review-status');
        }
    }

    /**
     * Add proposal details meta box
     */
    public function add_proposal_details_meta_box() {
        add_meta_box(
            'proposal_details_meta_box',
            __('Proposal Details', 'arsol-pfw'),
            array($this, 'render_proposal_details_meta_box'),
            'arsol-pfw-proposal',
            'side',
            'default'
        );
    }

    /**
     * Add customer request details meta box (only for proposals converted from requests)
     */
    public function add_customer_request_details_meta_box() {
        global $post;
        
        // Only add if this proposal has original request data
        if ($post && $this->has_original_request_data($post->ID)) {
            add_meta_box(
                'arsol_customer_request_details',
                __('Customer Request Details', 'arsol-pfw'),
                array($this, 'render_customer_request_details_meta_box'),
                'arsol-pfw-proposal',
                'normal',
                'high' // High priority to appear at top
            );
        }
    }

    /**
     * Check if proposal has original request data
     */
    private function has_original_request_data($post_id) {
        $original_budget = get_post_meta($post_id, '_original_request_budget', true);
        $original_start_date = get_post_meta($post_id, '_original_request_start_date', true);
        $original_delivery_date = get_post_meta($post_id, '_original_request_delivery_date', true);
        $original_request_date = get_post_meta($post_id, '_original_request_date', true);
        $original_request_attachments = get_post_meta($post_id, '_original_request_attachments', true);
        
        return !empty($original_budget) || !empty($original_start_date) || !empty($original_delivery_date) || !empty($original_request_date) || !empty($original_request_attachments);
    }

    /**
     * Render proposal details meta box
     */
    public function render_proposal_details_meta_box($post) {
        // Add nonce for security
        wp_nonce_field('proposal_details_meta_box', 'proposal_details_meta_box_nonce');

        // Get current values
        $cost_proposal_type = get_post_meta($post->ID, '_cost_proposal_type', true);
        if (empty($cost_proposal_type)) {
            $cost_proposal_type = 'none'; // Default to none
        }

        $start_date = get_post_meta($post->ID, '_proposal_start_date', true);
        $delivery_date = get_post_meta($post->ID, '_proposal_delivery_date', true);
        $expiration_date = get_post_meta($post->ID, '_proposal_expiration_date', true);

        // Get original request data
        $original_budget = get_post_meta($post->ID, '_original_request_budget', true);
        $original_start_date = get_post_meta($post->ID, '_original_request_start_date', true);
        $original_delivery_date = get_post_meta($post->ID, '_original_request_delivery_date', true);

        // Get author dropdown
        $author_dropdown = wp_dropdown_users(array(
            'name' => 'post_author_override',
            'selected' => $post->post_author,
            'include_selected' => true,
            'echo' => false,
            'class' => 'widefat'
        ));

        ?>
        <div class="proposal-details">
            <p>
                <label for="proposal_id" style="display:block;margin-bottom:5px;"><?php _e('Proposal ID:', 'arsol-pfw'); ?></label>
                <input type="text" 
                       id="proposal_id" 
                       value="<?php echo esc_attr($post->ID); ?>"
                       disabled
                       class="widefat">
            </p>

            <p>
                <label for="post_author_override" style="display:block;margin-bottom:5px;"><?php _e('Customer:', 'arsol-pfw'); ?></label>
                <?php echo $author_dropdown; ?>
            </p>

            <p>
                <label for="cost_proposal_type" style="display:block;margin-bottom:5px;"><?php _e('Cost Proposal:', 'arsol-pfw'); ?></label>
                <select id="cost_proposal_type" name="cost_proposal_type" class="widefat">
                    <option value="none" <?php selected($cost_proposal_type, 'none'); ?>><?php _e('None', 'arsol-pfw'); ?></option>
                    <option value="budget_estimates" <?php selected($cost_proposal_type, 'budget_estimates'); ?>><?php _e('Budget Estimates', 'arsol-pfw'); ?></option>
                    <option value="invoice_line_items" <?php selected($cost_proposal_type, 'invoice_line_items'); ?>><?php _e('Invoice Line Items', 'arsol-pfw'); ?></option>
                </select>
            </p>

            <p>
                <label for="proposal_start_date" style="display:block;margin-bottom:5px;"><?php _e('Proposed Start Date:', 'arsol-pfw'); ?></label>
                <input type="date" 
                       id="proposal_start_date" 
                       name="proposal_start_date" 
                       value="<?php echo esc_attr($start_date); ?>"
                       class="widefat">
            </p>

            <p>
                <label for="proposal_delivery_date" style="display:block;margin-bottom:5px;"><?php _e('Proposed Delivery Date:', 'arsol-pfw'); ?></label>
                <input type="date" 
                       id="proposal_delivery_date" 
                       name="proposal_delivery_date" 
                       value="<?php echo esc_attr($delivery_date); ?>"
                       class="widefat">
            </p>

            <p>
                <label for="proposal_expiration_date" style="display:block;margin-bottom:5px;"><?php _e('Proposal Expiration Date:', 'arsol-pfw'); ?></label>
                <input type="date" 
                       id="proposal_expiration_date" 
                       name="proposal_expiration_date" 
                       value="<?php echo esc_attr($expiration_date); ?>"
                       class="widefat">
            </p>
        </div>
        <div class="major-actions">
            <div class="arsol-pfw-admin-project-actions">
            <?php
            $is_disabled = $post->post_status !== 'publish';
            $convert_url = admin_url('admin-post.php?action=arsol_convert_to_project&proposal_id=' . $post->ID);
            $convert_url = wp_nonce_url($convert_url, 'arsol_convert_to_project_nonce');
            $confirm_message = esc_js(__('Are you sure you want to convert this proposal to a project? This will create a new project and delete the original proposal. Invoices will be created if selected.', 'arsol-pfw'));
            $tooltip_text = $is_disabled
                ? __('The proposal must be published before it can be converted.', 'arsol-pfw')
                : __('Converts this proposal into a new project.', 'arsol-pfw');
            ?>
            <span title="<?php echo esc_attr($tooltip_text); ?>">
                <input type="button" 
                       class="button button-secondary arsol-confirm-conversion" 
                       value="<?php _e('Convert to Project', 'arsol-pfw'); ?>" 
                       data-url="<?php echo esc_url($convert_url); ?>" 
                       data-message="<?php echo $confirm_message; ?>"
                       <?php disabled($is_disabled, true); ?> />
            </span>
            </div>
        </div>
        <script>
            jQuery(document).ready(function($) {
                function toggleCostProposalSections() {
                    var selectedType = $('#cost_proposal_type').val();
                    
                    $('#arsol_budget_estimates_metabox').hide();
                    $('#arsol_proposal_invoice_metabox').hide();

                    if (selectedType === 'budget_estimates') {
                        $('#arsol_budget_estimates_metabox').show();
                    } else if (selectedType === 'invoice_line_items') {
                        $('#arsol_proposal_invoice_metabox').show();
                    }
                }

                // Initial toggle on page load
                toggleCostProposalSections();

                // Toggle when dropdown changes
                $('#cost_proposal_type').on('change', function() {
                    toggleCostProposalSections();
                });
            });
        </script>
        <?php
    }

    /**
     * Render customer request details meta box
     */
    public function render_customer_request_details_meta_box($post) {
        // Get original request data
        $original_budget = get_post_meta($post->ID, '_original_request_budget', true);
        $original_start_date = get_post_meta($post->ID, '_original_request_start_date', true);
        $original_delivery_date = get_post_meta($post->ID, '_original_request_delivery_date', true);
        $original_request_date = get_post_meta($post->ID, '_original_request_date', true);
        $original_request_title = get_post_meta($post->ID, '_original_request_title', true);
        $original_request_content = get_post_meta($post->ID, '_original_request_content', true);
        $original_request_attachments = get_post_meta($post->ID, '_original_request_attachments', true);
        
        ?>
        <div class="arsol-customer-request-details">
            <style>
                .arsol-customer-request-details {
                    background: #f9f9f9;
                    border: 1px solid #ddd;
                    padding: 15px;
                    border-radius: 4px;
                }
                .arsol-request-details-grid {
                    display: grid;
                    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
                    gap: 15px;
                    margin-bottom: 15px;
                }
                .arsol-request-detail-item {
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                    padding: 8px 0;
                    border-bottom: 1px solid #eee;
                }
                .arsol-request-detail-item:last-child {
                    border-bottom: none;
                }
                .arsol-request-detail-label {
                    font-weight: 600;
                    color: #333;
                    flex: 0 0 auto;
                    margin-right: 10px;
                }
                .arsol-request-detail-value {
                    color: #666;
                    text-align: right;
                    flex: 1;
                }
                .arsol-request-description {
                    margin-top: 15px;
                    padding-top: 15px;
                    border-top: 1px solid #ddd;
                }
                .arsol-request-description h4 {
                    margin: 0 0 10px 0;
                    color: #333;
                }
                .arsol-request-description-content {
                    background: white;
                    border: 1px solid #ddd;
                    padding: 12px;
                    border-radius: 4px;
                    line-height: 1.5;
                    color: #555;
                }
                .arsol-request-attachments {
                    margin-top: 15px;
                    padding-top: 15px;
                    border-top: 1px solid #ddd;
                }
                .arsol-request-attachments h4 {
                    margin: 0 0 10px 0;
                    color: #333;
                }
                .arsol-request-attachments-list {
                    background: white;
                    border: 1px solid #ddd;
                    border-radius: 4px;
                    padding: 8px 12px;
                }
                .arsol-attachment-item:last-child {
                    border-bottom: none !important;
                }
            </style>

            <?php if ($original_request_title) : ?>
                <h3 style="margin: 0 0 15px 0; color: #333;">
                    <?php echo esc_html($original_request_title); ?>
                </h3>
            <?php endif; ?>

            <div class="arsol-request-details-grid">
                <?php if ($original_request_date) : ?>
                <div class="arsol-request-detail-item">
                    <span class="arsol-request-detail-label"><?php _e('Request Date:', 'arsol-pfw'); ?></span>
                    <span class="arsol-request-detail-value">
                        <?php echo esc_html(date_i18n(get_option('date_format'), strtotime($original_request_date))); ?>
                    </span>
                </div>
                <?php endif; ?>

                <?php if (!empty($original_budget)) : ?>
                <div class="arsol-request-detail-item">
                    <span class="arsol-request-detail-label"><?php _e('Budget:', 'arsol-pfw'); ?></span>
                    <span class="arsol-request-detail-value">
                        <?php if (is_array($original_budget)) : ?>
                            <?php echo wc_price($original_budget['amount'], array('currency' => $original_budget['currency'])); ?>
                        <?php else : ?>
                            <?php echo wc_price($original_budget); ?>
                        <?php endif; ?>
                    </span>
                </div>
                <?php endif; ?>

                <?php if ($original_start_date) : ?>
                <div class="arsol-request-detail-item">
                    <span class="arsol-request-detail-label"><?php _e('Start Date:', 'arsol-pfw'); ?></span>
                    <span class="arsol-request-detail-value">
                        <?php echo esc_html(date_i18n(get_option('date_format'), strtotime($original_start_date))); ?>
                    </span>
                </div>
                <?php endif; ?>

                <?php if ($original_delivery_date) : ?>
                <div class="arsol-request-detail-item">
                    <span class="arsol-request-detail-label"><?php _e('Delivery Date:', 'arsol-pfw'); ?></span>
                    <span class="arsol-request-detail-value">
                        <?php echo esc_html(date_i18n(get_option('date_format'), strtotime($original_delivery_date))); ?>
                    </span>
                </div>
                <?php endif; ?>
            </div>

            <?php if ($original_request_content) : ?>
            <div class="arsol-request-description">
                <h4><?php _e('Original Request Description:', 'arsol-pfw'); ?></h4>
                <div class="arsol-request-description-content">
                    <?php echo wp_kses_post(wpautop($original_request_content)); ?>
                </div>
            </div>
            <?php endif; ?>

            <?php if ($original_request_attachments && is_array($original_request_attachments)) : ?>
            <div class="arsol-request-attachments">
                <h4><?php _e('Original Request Attachments:', 'arsol-pfw'); ?></h4>
                <div class="arsol-request-attachments-list">
                    <?php foreach ($original_request_attachments as $attachment_id) : ?>
                        <?php 
                        $file = get_post($attachment_id);
                        if ($file) :
                            $file_url = wp_get_attachment_url($attachment_id);
                            $file_name = basename(get_attached_file($attachment_id));
                            $file_size = size_format(filesize(get_attached_file($attachment_id)));
                        ?>
                        <div class="arsol-attachment-item" style="display: flex; align-items: center; padding: 8px 0; border-bottom: 1px solid #eee;">
                            <span class="arsol-attachment-icon" style="margin-right: 10px;">
                                📎
                            </span>
                            <div class="arsol-attachment-info" style="flex: 1;">
                                <a href="<?php echo esc_url($file_url); ?>" target="_blank" style="text-decoration: none; color: #0073aa;">
                                    <?php echo esc_html($file_name); ?>
                                </a>
                                <small style="display: block; color: #666; margin-top: 2px;">
                                    <?php echo esc_html($file_size); ?>
                                </small>
                            </div>
                        </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
        <?php
    }

    /**
     * Adds inline styles to the admin head to hide conditional metaboxes by default.
     */
    public function hide_metaboxes_initially() {
        global $post;
        if (isset($post->post_type) && $post->post_type === 'arsol-pfw-proposal') {
            echo '<style>
                #arsol_budget_estimates_metabox,
                #arsol_proposal_invoice_metabox {
                    display: none;
                }
            </style>';
        }
    }

    /**
     * Save proposal details
     */
    public function save_proposal_details($post_id) {
        // Check if our nonce is set.
        if (!isset($_POST['proposal_details_meta_box_nonce'])) {
            return;
        }

        // Verify that the nonce is valid.
        if (!wp_verify_nonce($_POST['proposal_details_meta_box_nonce'], 'proposal_details_meta_box')) {
            return;
        }

        // If this is an autosave, our form has not been submitted, so we don't want to do anything.
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        // Check the user's permissions.
        if (isset($_POST['post_type']) && 'arsol-pfw-proposal' == $_POST['post_type']) {
        if (!current_user_can('edit_post', $post_id)) {
            return;
            }
        }
        
        // It's safe for us to save the data now.
        $cost_proposal_type = isset($_POST['cost_proposal_type']) ? sanitize_text_field($_POST['cost_proposal_type']) : 'none';
        update_post_meta($post_id, '_cost_proposal_type', $cost_proposal_type);

        // Get currency
        $currency = get_woocommerce_currency();

        // Conditionally save/delete budget data
        if ($cost_proposal_type === 'budget_estimates') {
            // Sanitize and save the budget amount
            if (isset($_POST['proposal_budget'])) {
                $budget_amount = wc_format_decimal(sanitize_text_field($_POST['proposal_budget']));
                $budget_data = array(
                    'amount' => $budget_amount,
                    'currency' => $currency
                );
                update_post_meta($post_id, '_proposal_budget', $budget_data);
            }

            // Sanitize and save the recurring budget amount
        if (isset($_POST['proposal_recurring_budget'])) {
                $recurring_budget_amount = wc_format_decimal(sanitize_text_field($_POST['proposal_recurring_budget']));
                $recurring_budget_data = array(
                    'amount' => $recurring_budget_amount,
                    'currency' => $currency
                );
                update_post_meta($post_id, '_proposal_recurring_budget', $recurring_budget_data);
            } else {
                delete_post_meta($post_id, '_proposal_recurring_budget');
        }

            // Save billing cycle if recurring budget is set
            if (!empty($_POST['proposal_recurring_budget']) && $_POST['proposal_recurring_budget'] > 0) {
        if (isset($_POST['proposal_billing_interval'])) {
            update_post_meta($post_id, '_proposal_billing_interval', sanitize_text_field($_POST['proposal_billing_interval']));
        }
        if (isset($_POST['proposal_billing_period'])) {
            update_post_meta($post_id, '_proposal_billing_period', sanitize_text_field($_POST['proposal_billing_period']));
        }
        if (isset($_POST['proposal_recurring_start_date'])) {
            update_post_meta($post_id, '_proposal_recurring_start_date', sanitize_text_field($_POST['proposal_recurring_start_date']));
                }
            } else {
                // If there's no recurring budget, delete the meta
                delete_post_meta($post_id, '_proposal_billing_interval');
                delete_post_meta($post_id, '_proposal_billing_period');
                delete_post_meta($post_id, '_proposal_recurring_start_date');
            }
        } else {
            // If not budget estimates, delete all budget meta to keep things clean
            delete_post_meta($post_id, '_proposal_budget');
            delete_post_meta($post_id, '_proposal_recurring_budget');
            delete_post_meta($post_id, '_proposal_billing_interval');
            delete_post_meta($post_id, '_proposal_billing_period');
            delete_post_meta($post_id, '_proposal_recurring_start_date');
        }

        // Conditionally delete invoice data if it's not the selected type
        if ($cost_proposal_type !== 'invoice_line_items') {
             delete_post_meta($post_id, '_arsol_proposal_line_items');
             delete_post_meta($post_id, '_arsol_proposal_one_time_total');
             delete_post_meta($post_id, '_arsol_proposal_recurring_totals_grouped');
        }

        // Save start date
        if (isset($_POST['proposal_start_date'])) {
            update_post_meta($post_id, '_proposal_start_date', sanitize_text_field($_POST['proposal_start_date']));
        }

        // Save delivery date
        if (isset($_POST['proposal_delivery_date'])) {
            update_post_meta($post_id, '_proposal_delivery_date', sanitize_text_field($_POST['proposal_delivery_date']));
        }

        // Save expiration date
        if (isset($_POST['proposal_expiration_date'])) {
            update_post_meta($post_id, '_proposal_expiration_date', sanitize_text_field($_POST['proposal_expiration_date']));
        }
    }

    /**
     * Output confirm conversion script
     */
    public function output_confirm_conversion_script() {
        global $post;
        if (!$post || $post->post_type !== 'arsol-pfw-proposal') {
            return;
        }
        ?>
        <script type="text/javascript">
        jQuery(document).ready(function($) {
            $('.arsol-confirm-conversion').on('click', function(e) {
                e.preventDefault();
                var message = $(this).data('message');
                var url = $(this).data('url');
                
                if (confirm(message)) {
                    window.location.href = url;
                }
            });
        });
        </script>
        <?php
    }
}