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
            __('Budget', 'arsol-pfw'),
            array($this, 'render_budget_estimates_meta_box'),
            'arsol-pfw-proposal',
            'normal',
            'high'
        );
    }

    public function render_budget_estimates_meta_box($post) {
        wp_nonce_field('arsol_proposal_budget_save', 'arsol_proposal_budget_nonce');
        
        // Get current values
        $budget_data = get_post_meta($post->ID, '_proposal_budget', true);
        $recurring_budget_data = get_post_meta($post->ID, '_proposal_recurring_budget', true);

        $budget_amount = !empty($budget_data['amount']) ? $budget_data['amount'] : '';
        $recurring_budget_amount = !empty($recurring_budget_data['amount']) ? $recurring_budget_data['amount'] : '';

        // Determine currency
        $currency_code = !empty($budget_data['currency']) 
            ? $budget_data['currency'] 
            : (!empty($recurring_budget_data['currency']) ? $recurring_budget_data['currency'] : get_woocommerce_currency());

        $billing_interval = get_post_meta($post->ID, '_proposal_billing_interval', true) ?: '1';
        $billing_period = get_post_meta($post->ID, '_proposal_billing_period', true) ?: 'month';
        $recurring_start_date = get_post_meta($post->ID, '_proposal_recurring_start_date', true);
        ?>
        <div id="proposal_budget_builder">
            <!-- Budget Section -->
            <div class="line-items-container">
                <table class="widefat" id="budget-line-items">
                    <thead>
                        <tr>
                            <th class="arsol-description-column"><?php _e('Description', 'arsol-pfw'); ?></th>
                            <th class="arsol-date-column"><?php _e('Start Date', 'arsol-pfw'); ?></th>
                            <th class="arsol-amount-column"><?php _e('Amount', 'arsol-pfw'); ?></th>
                            <th class="arsol-billing-cycle-column"><?php _e('Billing Cycle', 'arsol-pfw'); ?></th>
                            <th class="arsol-subtotal-column"><?php _e('Subtotal', 'arsol-pfw'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- One-Time Budget Row -->
                        <tr class="arsol-line-item arsol-budget-item">
                            <td class="arsol-description-column">
                                <div class="arsol-flex-container">
                                    <strong class="arsol-flex-fixed arsol-budget-description"><?php _e('One-Time Budget', 'arsol-pfw'); ?></strong>
                                    <input type="text" class="arsol-description-input js-details-input" name="proposal_budget_details" value="<?php echo esc_attr(get_post_meta($post->ID, '_proposal_budget_details', true)); ?>" placeholder="<?php esc_attr_e('Additional details...', 'arsol-pfw'); ?>">
                                </div>
                            </td>
                            <td class="arsol-date-column">
                                <span class="arsol-not-applicable">—</span>
                            </td>
                            <td class="arsol-amount-column">
                                <input type="text" class="arsol-amount-input js-amount-input arsol-budget-amount wc_input_price" name="proposal_budget" value="<?php echo esc_attr($budget_amount); ?>" placeholder="0.00" min="0.01" step="0.01">
                            </td>
                            <td class="arsol-billing-cycle-column">
                                <span class="arsol-not-applicable">—</span>
                            </td>
                            <td class="arsol-subtotal-column">
                                <span class="js-total-display budget-total-display"><?php echo wc_price($budget_amount ? $budget_amount : 0); ?></span>
                            </td>
                        </tr>
                        
                        <!-- Recurring Budget Row -->
                        <tr class="arsol-line-item arsol-budget-item recurring-budget-row">
                            <td class="arsol-description-column">
                                <div class="arsol-flex-container">
                                    <strong class="arsol-flex-fixed arsol-budget-description"><?php _e('Recurring Budget', 'arsol-pfw'); ?></strong>
                                    <input type="text" class="arsol-description-input js-details-input" name="proposal_recurring_budget_details" value="<?php echo esc_attr(get_post_meta($post->ID, '_proposal_recurring_budget_details', true)); ?>" placeholder="<?php esc_attr_e('Additional details...', 'arsol-pfw'); ?>">
                                </div>
                            </td>
                            <td class="arsol-date-column">
                                <input type="date" class="arsol-date-input js-date-input start-date-input" name="proposal_recurring_start_date" value="<?php echo esc_attr($recurring_start_date); ?>">
                            </td>
                            <td class="arsol-amount-column">
                                <input type="text" class="arsol-amount-input js-amount-input arsol-budget-amount recurring-budget-amount-input wc_input_price" name="proposal_recurring_budget" value="<?php echo esc_attr($recurring_budget_amount); ?>" placeholder="0.00">
                            </td>
                            <td class="arsol-billing-cycle-column">
                                <select name="proposal_billing_interval" class="arsol-billing-select js-billing-input billing-interval">
                            <?php
                                    $intervals = array('1' => __('every', 'arsol-pfw'), '2' => __('every 2nd', 'arsol-pfw'), '3' => __('every 3rd', 'arsol-pfw'), '4' => __('every 4th', 'arsol-pfw'), '5' => __('every 5th', 'arsol-pfw'), '6' => __('every 6th', 'arsol-pfw'));
                            foreach ($intervals as $value => $label) {
                                echo '<option value="' . esc_attr($value) . '"' . selected($billing_interval, $value, false) . '>' . esc_html($label) . '</option>';
                            }
                            ?>
                        </select>
                                <select name="proposal_billing_period" class="arsol-billing-select js-billing-input billing-period">
                            <?php
                                    $periods = array('day' => __('day', 'arsol-pfw'), 'week' => __('week', 'arsol-pfw'), 'month' => __('month', 'arsol-pfw'), 'year' => __('year', 'arsol-pfw'));
                            foreach ($periods as $value => $label) {
                                echo '<option value="' . esc_attr($value) . '"' . selected($billing_period, $value, false) . '>' . esc_html($label) . '</option>';
                            }
                            ?>
                        </select>
                            </td>
                            <td class="arsol-subtotal-column">
                                <span class="js-total-display recurring-budget-total-display"><?php echo $recurring_budget_amount ? wc_price($recurring_budget_amount) : wc_price(0); ?></span> <span class="arsol-billing-period billing-period-display">/<?php echo $billing_period === 'month' ? 'mo' : ($billing_period === 'year' ? 'yr' : ($billing_period === 'week' ? 'wk' : ($billing_period === 'day' ? 'day' : $billing_period))); ?></span>
                            </td>
                        </tr>
                    </tbody>
                </table>
                
                <div class="arsol-totals-container arsol-budget-totals">
                    <div class="arsol-totals-left">
                        <!-- Empty space for consistency -->
                    </div>
                    <div class="arsol-totals-right">
                        <table class="arsol-totals-table">
                            <tr class="arsol-total-row">
                                <td class="arsol-total-label"><?php _e('One-Time Total:', 'arsol-pfw'); ?></td>
                                <td class="arsol-total-amount">
                                    <span class="js-total-display" id="budget-onetime-total-display"><?php echo wc_price($budget_amount ? $budget_amount : 0); ?></span>
                                </td>
                                </tr>
                            <tr class="arsol-total-row">
                                <td class="arsol-total-label"><?php _e('Recurring Total:', 'arsol-pfw'); ?></td>
                                <td class="arsol-total-amount">
                                    <span class="js-total-display" id="budget-recurring-total-display"><?php echo $recurring_budget_amount ? wc_price($recurring_budget_amount) : wc_price(0); ?></span> 
                                    <span class="arsol-billing-period" id="budget-recurring-period">/<?php echo $billing_period === 'month' ? 'mo' : ($billing_period === 'year' ? 'yr' : ($billing_period === 'week' ? 'wk' : ($billing_period === 'day' ? 'day' : $billing_period))); ?></span>
                                </td>
                                </tr>
                            </table>
                        </div>
                    </div>
            </div>
        </div>
        <hr>
        <!-- Notes Section -->
        <div class="line-items-container">
            <h3><?php _e('Notes', 'arsol-pfw'); ?></h3>
            <p class="description"><?php _e('These notes will be displayed to the customer on the project proposal.', 'arsol-pfw'); ?></p>
            <?php
            $notes_content = get_post_meta($post->ID, '_arsol_proposal_notes', true);
            wp_editor(
                $notes_content,
                    'arsol_proposal_notes_budget',
                array(
                        'textarea_name' => 'arsol_proposal_notes',
                    'textarea_rows' => 8,
                    'media_buttons' => false,
                    'tinymce' => array(
                        'toolbar1' => 'bold,italic,underline,bullist,numlist,link,unlink',
                        'toolbar2' => ''
                    ),
                )
            );
            ?>
        </div>
        </div>
        <?php
    }
}
