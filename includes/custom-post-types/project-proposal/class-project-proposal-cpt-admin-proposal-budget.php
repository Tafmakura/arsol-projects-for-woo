<?php
namespace Arsol_Projects_For_Woo\Custom_Post_Types\ProjectProposal\Admin;

if (!defined('ABSPATH')) exit;

class Proposal_Budget {

    public function __construct() {
        add_action('add_meta_boxes', array($this, 'add_budget_estimates_meta_box'));
    }

    public function add_budget_estimates_meta_box() {
        // We only add this metabox on the proposal post type screen.
        global $post;
        if (get_post_type($post) !== 'arsol-pfw-proposal') {
            return;
        }

        add_meta_box(
            'arsol_budget_estimates_metabox',
            __('Budget Estimates', 'arsol-pfw'),
            array($this, 'render_budget_estimates_meta_box'),
            'arsol-pfw-proposal',
            'normal',
            'high'
        );
    }

    public function render_budget_estimates_meta_box($post) {
        // Get current values
        $budget_data = get_post_meta($post->ID, '_proposal_budget', true);
        $recurring_budget_data = get_post_meta($post->ID, '_proposal_recurring_budget', true);

        $budget_amount = !empty($budget_data['amount']) ? $budget_data['amount'] : '';
        $recurring_budget_amount = !empty($recurring_budget_data['amount']) ? $recurring_budget_data['amount'] : '';

        // Determine currency
        $currency_code = !empty($budget_data['currency']) 
            ? $budget_data['currency'] 
            : (!empty($recurring_budget_data['currency']) ? $recurring_budget_data['currency'] : get_woocommerce_currency());

        $billing_interval = get_post_meta($post->ID, '_proposal_billing_interval', true);
        $billing_period = get_post_meta($post->ID, '_proposal_billing_period', true);
        $recurring_start_date = get_post_meta($post->ID, '_proposal_recurring_start_date', true);

        // Default billing period to 'month' for new proposals
        if (empty($billing_period)) {
            $billing_period = 'month';
        }
        ?>
        <style>
            .budget-estimates-flex-container {
                display: flex;
                gap: 20px;
                align-items: flex-end;
                flex-wrap: wrap;
                padding: 10px 0;
            }
            .budget-estimates-flex-container .budget-field {
                display: flex;
                flex-direction: column;
                flex-grow: 1;
            }
            .budget-estimates-flex-container label {
                margin-bottom: 5px;
                font-weight: bold;
            }
            .budget-estimates-flex-container input,
            .budget-estimates-flex-container select {
                width: 100%;
            }
            .billing-cycle-fields {
                display: flex;
                gap: 10px;
            }
            .billing-cycle-fields .budget-field {
                flex-basis: 50%;
            }
        </style>
        <div class="budget-estimates-flex-container">
            <div class="budget-field">
                <label for="proposal_budget"><?php echo sprintf(__('Proposed Budget (%s)', 'arsol-pfw'), $currency_code); ?></label>
                <input type="text"
                    id="proposal_budget"
                    name="proposal_budget"
                    value="<?php echo esc_attr($budget_amount); ?>"
                    class="arsol-money-input"
                    inputmode="decimal">
            </div>

            <div class="budget-field">
                <label for="proposal_recurring_budget"><?php echo sprintf(__('Proposed Recurring Budget (%s)', 'arsol-pfw'), $currency_code); ?></label>
                <input type="text"
                    id="proposal_recurring_budget"
                    name="proposal_recurring_budget"
                    value="<?php echo esc_attr($recurring_budget_amount); ?>"
                    class="arsol-money-input"
                    inputmode="decimal">
            </div>
            
            <div class="budget-field" id="recurring_billing_cycle_wrapper" style="flex-grow: 2; <?php echo (empty($recurring_budget_amount) || $recurring_budget_amount <= 0) ? 'display: none;' : ''; ?>">
                <label for="proposal_billing_cycle"><?php _e('Recurring Billing Cycle', 'arsol-pfw'); ?></label>
                <div class="billing-cycle-fields">
                    <div class="budget-field">
                         <select id="proposal_billing_interval" name="proposal_billing_interval">
                            <?php
                            $intervals = array('1' => __('Every', 'arsol-pfw'), '2' => __('Every 2nd', 'arsol-pfw'), '3' => __('Every 3rd', 'arsol-pfw'), '4' => __('Every 4th', 'arsol-pfw'), '5' => __('Every 5th', 'arsol-pfw'), '6' => __('Every 6th', 'arsol-pfw'));
                            foreach ($intervals as $value => $label) {
                                echo '<option value="' . esc_attr($value) . '"' . selected($billing_interval, $value, false) . '>' . esc_html($label) . '</option>';
                            }
                            ?>
                        </select>
                    </div>
                    <div class="budget-field">
                        <select id="proposal_billing_period" name="proposal_billing_period">
                            <?php
                            $periods = array('day' => __('Day', 'arsol-pfw'), 'week' => __('Week', 'arsol-pfw'), 'month' => __('Month', 'arsol-pfw'), 'year' => __('Year', 'arsol-pfw'));
                            foreach ($periods as $value => $label) {
                                echo '<option value="' . esc_attr($value) . '"' . selected($billing_period, $value, false) . '>' . esc_html($label) . '</option>';
                            }
                            ?>
                        </select>
                    </div>
                </div>
            </div>

            <div class="budget-field" id="recurring_start_date_wrapper" style="<?php echo (empty($recurring_budget_amount) || $recurring_budget_amount <= 0) ? 'display: none;' : ''; ?>">
                 <label for="proposal_recurring_start_date"><?php _e('Recurring Start Date', 'arsol-pfw'); ?></label>
                 <input type="date"
                    id="proposal_recurring_start_date"
                    name="proposal_recurring_start_date"
                    value="<?php echo esc_attr($recurring_start_date); ?>">
            </div>
        </div>
        <hr>
        <!-- Notes Section -->
        <div class="line-items-container">
            <h3><?php _e('Notes', 'arsol-pfw'); ?></h3>
            <?php
            $notes_content = get_post_meta($post->ID, '_arsol_proposal_notes', true);
            wp_editor(
                $notes_content,
                'arsol_proposal_notes_budget', // Use a unique ID to avoid conflicts
                array(
                    'textarea_name' => 'arsol_proposal_notes', // Keep the same name for saving
                    'textarea_rows' => 8,
                    'media_buttons' => false,
                    'tinymce' => array(
                        'toolbar1' => 'bold,italic,underline,bullist,numlist,link,unlink',
                        'toolbar2' => ''
                    ),
                )
            );
            ?>
             <p class="description"><?php _e('These notes will be displayed on the frontend proposal view.', 'arsol-pfw'); ?></p>
        </div>
         <script>
            jQuery(document).ready(function($) {
                // Also, a small fix for the recurring budget fields visibility within the budget estimates section
                function toggleRecurringFields() {
                    var recurringBudget = $('#proposal_recurring_budget').val();
                    if (recurringBudget && parseFloat(recurringBudget) > 0) {
                        $('#recurring_billing_cycle_wrapper, #recurring_start_date_wrapper').show();
                    } else {
                        $('#recurring_billing_cycle_wrapper, #recurring_start_date_wrapper').hide();
                    }
                }

                $('#proposal_recurring_budget').on('input', function() {
                    toggleRecurringFields();
                });

                // Initial check
                toggleRecurringFields();
            });
        </script>
        <?php
    }
}
