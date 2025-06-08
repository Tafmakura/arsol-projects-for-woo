<?php

namespace Arsol_Projects_For_Woo\Custom_Post_Types\ProjectProposal\Admin;

if (!defined('ABSPATH')) exit;

class Proposal {
    public function __construct() {
        // Add meta boxes for single proposal admin screen
        add_action('add_meta_boxes', array($this, 'add_proposal_details_meta_box'));
        // Save proposal data
        add_action('save_post_arsol-pfw-proposal', array($this, 'save_proposal_details'));
        // Action to set review status when a proposal is published
        add_action('transition_post_status', array($this, 'set_proposal_review_status'), 10, 3);
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
     * Render proposal details meta box
     */
    public function render_proposal_details_meta_box($post) {
        // Add nonce for security
        wp_nonce_field('proposal_details_meta_box', 'proposal_details_meta_box_nonce');

        // Get current values
        $budget = get_post_meta($post->ID, '_proposal_budget', true);
        $recurring_budget = get_post_meta($post->ID, '_proposal_recurring_budget', true);
        $start_date = get_post_meta($post->ID, '_proposal_start_date', true);
        $delivery_date = get_post_meta($post->ID, '_proposal_delivery_date', true);
        $billing_interval = get_post_meta($post->ID, '_proposal_billing_interval', true);
        $billing_period = get_post_meta($post->ID, '_proposal_billing_period', true);
        
        // Default billing period to 'month' for new proposals
        if (empty($billing_period)) {
            $billing_period = 'month';
        }
        
        // Get invoice product settings
        $settings = get_option('arsol_projects_settings', array());
        $invoice_product_id = isset($settings['proposal_invoice_product']) ? $settings['proposal_invoice_product'] : '';
        $recurring_invoice_product_id = isset($settings['proposal_recurring_invoice_product']) ? $settings['proposal_recurring_invoice_product'] : '';
        
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
                <label for="proposal_budget" style="display:block;margin-bottom:5px;"><?php _e('Proposed Budget:', 'arsol-pfw'); ?></label>
                <input type="number" 
                       id="proposal_budget" 
                       name="proposal_budget" 
                       value="<?php echo esc_attr($budget); ?>"
                       class="widefat"
                       step="0.01"
                       min="0">
            </p>

            <p>
                <label for="proposal_recurring_budget" style="display:block;margin-bottom:5px;"><?php _e('Proposed Recurring Budget:', 'arsol-pfw'); ?></label>
                <input type="number" 
                       id="proposal_recurring_budget" 
                       name="proposal_recurring_budget" 
                       value="<?php echo esc_attr($recurring_budget); ?>"
                       class="widefat"
                       step="0.01"
                       min="0">
            </p>

            <div id="recurring_billing_cycle_wrapper" style="<?php echo empty($recurring_budget) || $recurring_budget <= 0 ? 'display: none;' : ''; ?>">
                <p>
                    <span style="display: flex; justify-content: space-between;">
                        <select id="proposal_billing_interval" name="proposal_billing_interval" style="width: 48%;">
                            <?php
                            $intervals = array(
                                '1' => __('Every', 'arsol-pfw'),
                                '2' => __('Every 2nd', 'arsol-pfw'),
                                '3' => __('Every 3rd', 'arsol-pfw'),
                                '4' => __('Every 4th', 'arsol-pfw'),
                                '5' => __('Every 5th', 'arsol-pfw'),
                                '6' => __('Every 6th', 'arsol-pfw'),
                            );
                            foreach ($intervals as $value => $label) {
                                echo '<option value="' . esc_attr($value) . '"' . selected($billing_interval, $value, false) . '>' . esc_html($label) . '</option>';
                            }
                            ?>
                        </select>
                        <select id="proposal_billing_period" name="proposal_billing_period" style="width: 48%;">
                            <?php
                            $periods = array(
                                'day' => __('Day', 'arsol-pfw'),
                                'week' => __('Week', 'arsol-pfw'),
                                'month' => __('Month', 'arsol-pfw'),
                                'year' => __('Year', 'arsol-pfw'),
                            );
                            foreach ($periods as $value => $label) {
                                echo '<option value="' . esc_attr($value) . '"' . selected($billing_period, $value, false) . '>' . esc_html($label) . '</option>';
                            }
                            ?>
                        </select>
                    </span>
                </p>
            </div>


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
        </div>
        <div class="major-actions" style="padding-top:10px; border-top: 1px solid #ddd; margin-top: 10px;">
            <?php if (!empty($invoice_product_id) && get_post_meta($post->ID, '_invoice_created', true) !== 'yes') : ?>
                <p>
                    <label for="create_invoice">
                        <input type="checkbox" id="create_invoice" name="create_invoice">
                        <?php _e('Create invoice for budget', 'arsol-pfw'); ?>
                    </label>
                </p>
            <?php endif; ?>
            <?php if (!empty($recurring_invoice_product_id) && get_post_meta($post->ID, '_recurring_invoice_created', true) !== 'yes') : ?>
                <p>
                    <label for="create_recurring_invoice">
                        <input type="checkbox" id="create_recurring_invoice" name="create_recurring_invoice">
                        <?php _e('Create invoice for recurring budget', 'arsol-pfw'); ?>
                    </label>
                </p>
            <?php endif; ?>

            <?php
            if ($post->post_status == 'publish') {
                $convert_url = admin_url('admin-post.php?action=arsol_convert_to_project&proposal_id=' . $post->ID);
                $convert_url = wp_nonce_url($convert_url, 'arsol_convert_to_project_nonce');
                $confirm_message = esc_js(__('Are you sure you want to convert this proposal to a project? This will create a new project and delete the original proposal. Invoices will be created if selected.', 'arsol-pfw'));
                ?>
                <div class="arsol-pfw-admin-project-actions">
                    <input type="button" class="button button-secondary arsol-confirm-conversion" value="<?php _e('Convert to Project', 'arsol-pfw'); ?>" data-url="<?php echo esc_url($convert_url); ?>" data-message="<?php echo $confirm_message; ?>" />
                </div>
                <?php
            }
            ?>
        </div>
        <script type="text/javascript">
            jQuery(document).ready(function($) {
                function toggleRecurringCycle() {
                    var recurringBudget = $('#proposal_recurring_budget').val();
                    if (recurringBudget && parseFloat(recurringBudget) > 0) {
                        $('#recurring_billing_cycle_wrapper').show();
                    } else {
                        $('#recurring_billing_cycle_wrapper').hide();
                    }
                }

                // Initial check
                toggleRecurringCycle();

                // Check on input change
                $('#proposal_recurring_budget').on('input', function() {
                    toggleRecurringCycle();
                });
            });
        </script>
        <?php
    }

    /**
     * Save proposal details
     */
    public function save_proposal_details($post_id) {
        // Check if our nonce is set
        if (!isset($_POST['proposal_details_meta_box_nonce'])) {
            return;
        }

        // Verify that the nonce is valid
        if (!wp_verify_nonce($_POST['proposal_details_meta_box_nonce'], 'proposal_details_meta_box')) {
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

        // Save budget
        if (isset($_POST['proposal_budget'])) {
            update_post_meta($post_id, '_proposal_budget', sanitize_text_field($_POST['proposal_budget']));
        }

        if (isset($_POST['proposal_recurring_budget'])) {
            update_post_meta($post_id, '_proposal_recurring_budget', sanitize_text_field($_POST['proposal_recurring_budget']));
        }

        if (isset($_POST['proposal_billing_interval'])) {
            update_post_meta($post_id, '_proposal_billing_interval', sanitize_text_field($_POST['proposal_billing_interval']));
        }

        if (isset($_POST['proposal_billing_period'])) {
            update_post_meta($post_id, '_proposal_billing_period', sanitize_text_field($_POST['proposal_billing_period']));
        }

        // Save checkbox states
        update_post_meta($post_id, '_create_invoice_checked', isset($_POST['create_invoice']));
        update_post_meta($post_id, '_create_recurring_invoice_checked', isset($_POST['create_recurring_invoice']));

        // Save start date
        if (isset($_POST['proposal_start_date'])) {
            update_post_meta($post_id, '_proposal_start_date', sanitize_text_field($_POST['proposal_start_date']));
        }

        // Save delivery date
        if (isset($_POST['proposal_delivery_date'])) {
            update_post_meta($post_id, '_proposal_delivery_date', sanitize_text_field($_POST['proposal_delivery_date']));
        }
    }
}