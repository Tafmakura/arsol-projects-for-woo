<?php

namespace Arsol_Projects_For_Woo\Custom_Post_Types\ProjectProposal\Admin;

if (!defined('ABSPATH')) exit;

class Proposal {
    public function __construct() {
        // Add meta boxes for single proposal admin screen
        add_action('add_meta_boxes', array($this, 'add_proposal_details_meta_box'));
        
        // Add styles to hide metaboxes initially
        add_action('admin_head', array($this, 'hide_metaboxes_initially'));
        // Save proposal data
        add_action('save_post', array($this, 'save_proposal_details'));
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
            __('Project Actions', 'arsol-pfw'),
            array($this, 'render_proposal_details_meta_box'),
            'arsol-pfw-proposal',
            'side',
            'default'
        );
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
        $author_dropdown = \Arsol_Projects_For_Woo\Woocommerce_Helper::generate_customer_dropdown(
            'post_author_override',
            $post->post_author,
            array('class' => 'widefat')
        );

        ?>
        <div class="proposal-details">
        </div>
        <div class="major-actions">
            <div class="arsol-pfw-admin-project-actions" style="display: flex; justify-content: space-between; align-items: center;">
                <input type="submit" id="publish" name="publish" class="button button-primary" value="<?php echo ($post->post_status === 'publish') ? __('Update', 'arsol-pfw') : __('Publish', 'arsol-pfw'); ?>">
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

        // Save secondary status
        if (isset($_POST['proposal_secondary_status'])) {
            $secondary_status = sanitize_text_field($_POST['proposal_secondary_status']);
            // Validate the value is one of the allowed options
            if (in_array($secondary_status, ['ready_for_review', 'processing'])) {
                update_post_meta($post_id, '_proposal_secondary_status', $secondary_status);
            }
        }

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
        // Removed duplicate script - functionality handled by global admin.js
        // This was causing double-triggering of conversion confirmations
    }
}