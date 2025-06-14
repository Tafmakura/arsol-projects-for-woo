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
        $budget_data = get_post_meta($post->ID, '_proposal_budget', true);
        $recurring_budget_data = get_post_meta($post->ID, '_proposal_recurring_budget', true);

        $budget_amount = !empty($budget_data['amount']) ? $budget_data['amount'] : '';
        $recurring_budget_amount = !empty($recurring_budget_data['amount']) ? $recurring_budget_data['amount'] : '';

        // Determine currency - they should share the same currency
        $currency_code = !empty($budget_data['currency']) 
            ? $budget_data['currency'] 
            : (!empty($recurring_budget_data['currency']) ? $recurring_budget_data['currency'] : get_woocommerce_currency());

        $start_date = get_post_meta($post->ID, '_proposal_start_date', true);
        $delivery_date = get_post_meta($post->ID, '_proposal_delivery_date', true);
        $billing_interval = get_post_meta($post->ID, '_proposal_billing_interval', true);
        $billing_period = get_post_meta($post->ID, '_proposal_billing_period', true);
        $recurring_start_date = get_post_meta($post->ID, '_proposal_recurring_start_date', true);

        // Get original request data
        $original_budget = get_post_meta($post->ID, '_original_request_budget', true);
        $original_start_date = get_post_meta($post->ID, '_original_request_start_date', true);
        $original_delivery_date = get_post_meta($post->ID, '_original_request_delivery_date', true);

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

            <?php if ($original_budget || $original_start_date || $original_delivery_date) : ?>
            <div class="arsol-pfw-original-request-details">
                <h4><?php _e('Original Request Details', 'arsol-pfw'); ?></h4>
                <?php if (!empty($original_budget) && is_array($original_budget)) : ?>
                <p>
                    <label><?php _e('Budget:', 'arsol-pfw'); ?></label>
                    <strong><?php echo wc_price($original_budget['amount'], array('currency' => $original_budget['currency'])); ?></strong>
                </p>
                <?php elseif (!empty($original_budget)) : // Backwards compatibility ?>
                <p>
                    <label><?php _e('Budget:', 'arsol-pfw'); ?></label>
                    <strong><?php echo wc_price($original_budget); ?></strong>
                </p>
                <?php endif; ?>
                <?php if ($original_start_date) : ?>
                <p>
                    <label><?php _e('Start Date:', 'arsol-pfw'); ?></label>
                    <strong><?php echo esc_html(date_i18n(get_option('date_format'), strtotime($original_start_date))); ?></strong>
                </p>
                <?php endif; ?>
                <?php if ($original_delivery_date) : ?>
                <p>
                    <label><?php _e('Delivery Date:', 'arsol-pfw'); ?></label>
                    <strong><?php echo esc_html(date_i18n(get_option('date_format'), strtotime($original_delivery_date))); ?></strong>
                </p>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <p>
                <label for="post_author_override" style="display:block;margin-bottom:5px;"><?php _e('Customer:', 'arsol-pfw'); ?></label>
                <?php echo $author_dropdown; ?>
            </p>

            <p>
                <label for="proposal_budget" style="display:block;margin-bottom:5px;"><?php echo sprintf(__('Proposed Budget (%s):', 'arsol-pfw'), $currency_code); ?></label>
                <input type="text"
                       id="proposal_budget"
                       name="proposal_budget"
                       value="<?php echo esc_attr($budget_amount); ?>"
                       class="widefat arsol-money-input"
                       inputmode="decimal">
            </p>

            <p>
                <label for="proposal_recurring_budget" style="display:block;margin-bottom:5px;"><?php echo sprintf(__('Proposed Recurring Budget (%s):', 'arsol-pfw'), $currency_code); ?></label>
                <input type="text"
                       id="proposal_recurring_budget"
                       name="proposal_recurring_budget"
                       value="<?php echo esc_attr($recurring_budget_amount); ?>"
                       class="widefat arsol-money-input"
                       inputmode="decimal">
            </p>

            <div id="recurring_billing_cycle_wrapper" style="<?php echo empty($recurring_budget_amount) || $recurring_budget_amount <= 0 ? 'display: none;' : ''; ?>">
                <p>
                    <label for="proposal_billing_cycle" style="display:block;margin-bottom:5px;"><?php _e('Recurring Billing Cycle:', 'arsol-pfw'); ?></label>
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
                <p>
                    <label for="proposal_recurring_start_date" style="display:block;margin-bottom:5px;"><?php _e('Recurring Start Date:', 'arsol-pfw'); ?></label>
                    <input type="date"
                           id="proposal_recurring_start_date"
                           name="proposal_recurring_start_date"
                           value="<?php echo esc_attr($recurring_start_date); ?>"
                           class="widefat">
                </p>
            </div>

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

        // Determine the currency for this proposal
        $budget_data = get_post_meta($post_id, '_proposal_budget', true);
        $recurring_budget_data = get_post_meta($post_id, '_proposal_recurring_budget', true);
        $currency = !empty($budget_data['currency']) 
            ? $budget_data['currency'] 
            : (!empty($recurring_budget_data['currency']) ? $recurring_budget_data['currency'] : get_woocommerce_currency());

        // Save budget
        if (isset($_POST['proposal_budget'])) {
            $amount = preg_replace('/[^\d.]/', '', sanitize_text_field($_POST['proposal_budget']));
            if (empty($amount)) {
                delete_post_meta($post_id, '_proposal_budget');
            } else {
                update_post_meta($post_id, '_proposal_budget', array('amount' => $amount, 'currency' => $currency));
            }
        }

        if (isset($_POST['proposal_recurring_budget'])) {
            $amount = preg_replace('/[^\d.]/', '', sanitize_text_field($_POST['proposal_recurring_budget']));
            if (empty($amount)) {
                delete_post_meta($post_id, '_proposal_recurring_budget');
            } else {
                update_post_meta($post_id, '_proposal_recurring_budget', array('amount' => $amount, 'currency' => $currency));
            }
        }

        if (isset($_POST['proposal_billing_interval'])) {
            update_post_meta($post_id, '_proposal_billing_interval', sanitize_text_field($_POST['proposal_billing_interval']));
        }

        if (isset($_POST['proposal_billing_period'])) {
            update_post_meta($post_id, '_proposal_billing_period', sanitize_text_field($_POST['proposal_billing_period']));
        }

        if (isset($_POST['proposal_recurring_start_date'])) {
            update_post_meta($post_id, '_proposal_recurring_start_date', sanitize_text_field($_POST['proposal_recurring_start_date']));
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