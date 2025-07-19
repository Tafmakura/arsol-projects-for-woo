<?php

namespace Arsol_Projects_For_Woo\Custom_Post_Types\Proposal\Admin;

use Arsol_Projects_For_Woo\Custom_Post_Types\Arsol_PFW_Proposal;
use Arsol_Projects_For_Woo\Integrations\WooCommerce\Integration;

if (!defined('ABSPATH')) exit;

class Quotation_Controller {
    public function __construct() {
        add_action('add_meta_boxes', array($this, 'add_quotation_meta_box'));
        add_action('save_post_arsol-pfw-proposal', array($this, 'save_quotation_meta_box'), 20); // Run after single controller
        // Custom product search for products with prices only
        add_action('wp_ajax_arsol_search_products_with_price', array($this, 'ajax_search_products_with_price'));
        add_action('wp_ajax_arsol_proposal_quotation_ajax_get_product_details', array($this, 'ajax_get_product_details'));
        add_action('admin_footer', array($this, 'render_js_templates_in_footer'));
        
        // Add conditional CSS class to quotation metabox
        add_filter('postbox_classes_arsol-pfw-proposal_arsol_proposal_quotation_metabox', array($this, 'add_quotation_metabox_classes'));
    }

    public function add_quotation_meta_box() {
        add_meta_box(
            'arsol_proposal_quotation_metabox',
            __('Quotation', 'arsol-pfw'),
            array($this, 'render_quotation_meta_box'),
            'arsol-pfw-proposal',
            'normal',
            'high'
        );
    }

    public function render_quotation_meta_box($post) {
        wp_nonce_field('arsol_proposal_quotation_save', 'arsol_proposal_quotation_nonce');
        ?>
        <div id="proposal_quotation_builder">
            <!-- Products Section -->
            <div class="line-items-container">
                <h3><?php _e('Products & Services', 'arsol-pfw'); ?></h3>
                <table class="widefat" id="product-line-items">
                    <thead>
                        <tr>
                                                    <th class="arsol-description-column"><?php _e('Product', 'arsol-pfw'); ?></th>
                        <th class="arsol-date-column"><?php _e('Billing Start Date', 'arsol-pfw'); ?></th>
                        <th class="arsol-quantity-column"><?php _e('Qty', 'arsol-pfw'); ?></th>
                        <th class="arsol-price-column"><?php _e('Price', 'arsol-pfw'); ?></th>
                        <th class="arsol-sale-price-column"><?php _e('Sale Price', 'arsol-pfw'); ?></th>
                        <th class="arsol-subtotal-column"><?php _e('Subtotal', 'arsol-pfw'); ?></th>
                        <th class="arsol-actions-column"></th>
                        </tr>
                    </thead>
                    <tbody id="product-lines-body"></tbody>
                </table>
                <div class="section-footer">
                    <div class="section-footer-left">
                        <button type="button" class="button add-line-item add-product-button" data-type="product"><?php _e('+ Add Product', 'arsol-pfw'); ?></button>
                    </div>
                    <div class="section-footer-right">
                        <div class="section-totals">
                            <table>
                                <tr>
                                    <td class="total-amount"><strong><?php _e('Total:', 'arsol-pfw'); ?></strong> <span id="product-subtotal-display"><?php echo wc_price(0); ?></span></td>
                                </tr>
                                <tr>
                                    <td class="total-amount"><strong><?php _e('Average Recurring Total:', 'arsol-pfw'); ?></strong> <span id="product-avg-monthly-display"><?php echo wc_price(0); ?></span></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <hr>
            <!-- Recurring Fees Section -->
            <div class="line-items-container">
                <h3><?php _e('Recurring Fees', 'arsol-pfw'); ?></h3>
                <table class="widefat" id="recurring-fee-line-items">
                    <thead>
                        <tr>
                                                    <th class="arsol-description-column"><?php _e('Fee Name', 'arsol-pfw'); ?></th>
                        <th class="arsol-amount-column"><?php _e('Amount', 'arsol-pfw'); ?></th>
                        <th class="arsol-billing-cycle-column"><?php _e('Billing Cycle & Start Date', 'arsol-pfw'); ?></th>
                        <th class="arsol-taxable-column"><?php _e('Tax', 'arsol-pfw'); ?></th>
                        <th class="arsol-subtotal-column"><?php _e('Subtotal', 'arsol-pfw'); ?></th>
                        <th class="arsol-actions-column"></th>
                        </tr>
                    </thead>
                    <tbody id="recurring-fee-lines-body"></tbody>
                </table>
                <div class="section-footer">
                    <div class="section-footer-left">
                        <button type="button" class="button add-line-item add-recurring-fee-button" data-type="recurring-fee"><?php _e('+ Add Recurring Fee', 'arsol-pfw'); ?></button>
                    </div>
                    <div class="section-footer-right">
                        <div class="section-totals">
                            <table>
                                <tr>
                                    <td class="total-amount"><strong><?php _e('Average Recurring Total:', 'arsol-pfw'); ?></strong> <span id="recurring-fee-avg-monthly-display"><?php echo wc_price(0); ?></span></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <hr>
            <!-- One-Time Fees Section -->
            <div class="line-items-container">
                <h3><?php _e('One-Time Fees', 'arsol-pfw'); ?></h3>
                <table class="widefat" id="onetime-fee-line-items">
                     <thead>
                        <tr>
                                                    <th class="arsol-description-column"><?php _e('Fee Name', 'arsol-pfw'); ?></th>
                        <th class="arsol-amount-column"><?php _e('Amount', 'arsol-pfw'); ?></th>
                        <th class="arsol-taxable-column"><?php _e('Tax', 'arsol-pfw'); ?></th>
                        <th class="arsol-subtotal-column"><?php _e('Subtotal', 'arsol-pfw'); ?></th>
                        <th class="arsol-actions-column"></th>
                        </tr>
                    </thead>
                    <tbody id="onetime-fee-lines-body"></tbody>
                </table>
                <div class="section-footer">
                    <div class="section-footer-left">
                        <button type="button" class="button add-line-item add-onetime-fee-button" data-type="onetime-fee"><?php _e('+ Add Fee', 'arsol-pfw'); ?></button>
                    </div>
                    <div class="section-footer-right">
                        <div class="section-totals">
                            <table>
                                <tr>
                                    <td class="total-amount"><strong><?php _e('Total:', 'arsol-pfw'); ?></strong> <span id="onetime-fee-subtotal-display"><?php echo wc_price(0); ?></span></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <hr>
            <!-- Shipping Section -->
            <div class="line-items-container">
                <h3><?php _e('Shipping', 'arsol-pfw'); ?></h3>
                <table class="widefat" id="shipping-lines-table">
                     <thead>
                        <tr>
                                                    <th class="arsol-description-column"><?php _e('Description', 'arsol-pfw'); ?></th>
                        <th class="arsol-shipping-class-column"><?php _e('Shipping Class', 'arsol-pfw'); ?></th>
                        <th class="arsol-amount-column"><?php _e('Amount', 'arsol-pfw'); ?></th>
                        <th class="arsol-taxable-column"><?php _e('Tax', 'arsol-pfw'); ?></th>
                        <th class="arsol-subtotal-column"><?php _e('Subtotal', 'arsol-pfw'); ?></th>
                        <th class="arsol-actions-column"></th>
                        </tr>
                    </thead>
                    <tbody id="shipping-lines-body"></tbody>
                </table>
                <div class="section-footer">
                    <div class="section-footer-left">
                        <button type="button" class="button add-line-item add-shipping-fee-button" data-type="shipping-fee"><?php _e('+ Add Shipping Fee', 'arsol-pfw'); ?></button>
                    </div>
                    <div class="section-footer-right">
                        <div class="section-totals">
                            <table>
                                <tr>
                                    <td class="total-amount"><strong><?php _e('Total:', 'arsol-pfw'); ?></strong> <span id="shipping-subtotal-display"><?php echo wc_price(0); ?></span></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <hr>
            <!-- Totals Section -->
            <div class="arsol-totals-container arsol-quotation-totals">
                <div class="arsol-totals-left">
                    <!-- Empty space for consistency -->
                </div>
                <div class="arsol-totals-right">
                    <table class="arsol-totals-table">
                        <tbody>
                            <tr class="arsol-total-row">
                                <td class="arsol-total-label"><?php _e('One-Time Total:', 'arsol-pfw'); ?></td>
                                <td class="arsol-total-amount">
                                    <span id="one-time-total-display"><?php echo wc_price(0); ?></span>
                                </td>
                            </tr>
                            <tr class="arsol-total-row">
                                <td class="arsol-total-label"><?php _e('Average Recurring Total:', 'arsol-pfw'); ?></td>
                                <td class="arsol-total-amount">
                                    <span id="average-monthly-total-display"><?php echo wc_price(0); ?></span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <input type="hidden" name="arsol_pfw_proposal_quotation_onetime_total" id="line_items_one_time_total">
                <input type="hidden" name="line_items_recurring_totals" id="line_items_recurring_totals">
            </div>
            <hr>
            <!-- Notes Section -->
            <div class="line-items-container">
                <h3><?php _e('Notes', 'arsol-pfw'); ?></h3>
                <p class="description"><?php _e('These notes will be displayed on the frontend proposal view.', 'arsol-pfw'); ?></p>
                <?php
                // Use Proposal entity for notes access
                $proposal = new \Arsol_Projects_For_Woo\Custom_Post_Types\Arsol_PFW_Proposal($post->ID);
                $notes_content = $proposal->get_proposal_notes();
                wp_editor(
                    $notes_content,
                    'arsol_proposal_notes',
                    array(
                        'textarea_name' => 'arsol_pfw_proposal_notes',
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
    
    public function render_js_templates_in_footer() {
        // Only render templates on proposal admin pages
        $screen = get_current_screen();
        if (!$screen || $screen->post_type !== 'arsol-pfw-proposal') {
            return;
        }
        
        // Prepare tax classes for dropdowns
        $tax_classes = \WC_Tax::get_tax_classes();
        $tax_class_options = array('no-tax' => __('No Tax', 'arsol-pfw'));
        $tax_class_options[''] = __('Standard', 'arsol-pfw'); // Add standard rate
        if ( ! empty( $tax_classes ) ) {
            foreach ( $tax_classes as $class ) {
                $tax_class_options[ sanitize_title( $class ) ] = $class;
            }
        }
        ?>
        <script type="text/html" id="tmpl-arsol-product-line-item">
            <tr class="arsol-line-item arsol-product-item" data-id="{{ data.id }}" <# if (data.product_type === 'subscription' || data.product_type === 'subscription_variation') { #>data-is-subscription="true" data-billing-interval="{{ data.billing_interval || 1 }}" data-billing-period="{{ data.billing_period || 'month' }}"<# } #>>
                <td class="arsol-product-column">
                    <select class="arsol-description-input" name="line_items[products][{{ data.id }}][product_id]">
                        <option value=""><?php _e('Select a product...', 'arsol-pfw'); ?></option>
                    </select>
                    <textarea class="arsol-product-details" 
                              name="line_items[products][{{ data.id }}][details]" 
                              placeholder="<?php esc_attr_e('Details...', 'arsol-pfw'); ?>"
                              rows="2">{{ data.details || '' }}</textarea>
                </td>
                <td class="arsol-date-column">
                    <span class="arsol-not-applicable">—</span>
                    <input type="date" class="arsol-date-input hidden-start-date" name="line_items[products][{{ data.id }}][billing_start_date]" value="{{ data.billing_start_date || '' }}" style="display: none;">
                </td>
                <td class="arsol-quantity-column"><input type="number" class="arsol-quantity-input" name="line_items[products][{{ data.id }}][quantity]" value="{{ data.quantity || 1 }}" min="1"></td>
                <td class="arsol-price-column"><input type="text" class="arsol-price-input wc_input_price" name="line_items[products][{{ data.id }}][price]" value="{{ data.regular_price || '' }}" required></td>
                <td class="arsol-sale-price-column"><input type="text" class="arsol-sale-price-input wc_input_price" name="line_items[products][{{ data.id }}][sale_price]" value="{{ data.sale_price || '' }}"></td>
                <td class="arsol-subtotal-column">{{{ data.subtotal_formatted || '<?php echo wc_price(0); ?>' }}}</td>
                <td class="arsol-actions-column"><a href="#" class="remove-line-item button button-secondary">&times;</a></td>
            </tr>
        </script>

        <script type="text/html" id="tmpl-arsol-onetime-fee-line-item">
            <tr class="arsol-line-item arsol-fee-item" data-id="{{ data.id }}">
                <td class="arsol-description-column">
                    <input type="text" class="arsol-description-input" name="line_items[one_time_fees][{{ data.id }}][description]" value="{{ data.description || '' }}" placeholder="<?php esc_attr_e('e.g. Setup Fee', 'arsol-pfw'); ?>" required>
                </td>
                <td class="arsol-amount-column">
                    <input type="text" class="arsol-amount-input wc_input_price" name="line_items[one_time_fees][{{ data.id }}][amount]" value="{{ data.amount || '' }}" required>
                </td>
                <td class="arsol-taxable-column">
                    <select name="line_items[one_time_fees][{{ data.id }}][tax_class]">
                        <# _.each(<?php echo json_encode($tax_class_options); ?>, function(label, value) { #>
                            <option value="{{ value }}" <# if (data.tax_class == value) { #>selected="selected"<# } #>>{{ label }}</option>
                        <# }); #>
                    </select>
                </td>
                <td class="arsol-subtotal-column">{{{ data.subtotal_formatted || '<?php echo wc_price(0); ?>' }}}</td>
                <td class="arsol-actions-column"><a href="#" class="remove-line-item button button-secondary">&times;</a></td>
            </tr>
        </script>

        <script type="text/html" id="tmpl-arsol-recurring-fee-line-item">
             <tr class="arsol-line-item arsol-recurring-fee-item" data-id="{{ data.id }}">
                <td class="arsol-description-column">
                    <input type="text" class="arsol-description-input" name="line_items[recurring_fees][{{ data.id }}][description]" value="{{ data.description || '' }}" placeholder="<?php esc_attr_e('e.g. Monthly Maintenance', 'arsol-pfw'); ?>" required>
                </td>
                <td class="arsol-amount-column">
                    <input type="text" class="arsol-amount-input wc_input_price" name="line_items[recurring_fees][{{ data.id }}][amount]" value="{{ data.amount || '' }}" required>
                </td>
                <td class="arsol-billing-cycle-column">
                    <div class="arsol-billing-period" id="arsol-billing-period-{{ data.id }}">
                    <?php
                        $intervals = function_exists('wcs_get_subscription_period_interval_strings') ? wcs_get_subscription_period_interval_strings() : array(1=>1);
                        $periods = function_exists('wcs_get_subscription_period_strings') ? wcs_get_subscription_period_strings() : array('month' => 'month');
                    ?>
                    <select name="line_items[recurring_fees][{{ data.id }}][billing_interval]" class="arsol-billing-select">
                        <# _.each(<?php echo json_encode($intervals); ?>, function(label, value) { #>
                            <option value="{{ value }}" <# if (data.billing_interval == value) { #>selected="selected"<# } #>>{{ label }}</option>
                        <# }); #>
                    </select>
                    <select name="line_items[recurring_fees][{{ data.id }}][billing_period]" class="arsol-billing-select">
                         <# _.each(<?php echo json_encode($periods); ?>, function(label, value) { #>
                            <option value="{{ value }}" <# if (data.billing_period == value) { #>selected="selected"<# } #>>{{ label }}</option>
                        <# }); #>
                    </select>
                    </div>
                    <input type="date" class="arsol-date-input arsol-recurring-date-input" name="line_items[recurring_fees][{{ data.id }}][billing_start_date]" value="{{ data.billing_start_date || '' }}">
                </td>
                <td class="arsol-taxable-column">
                    <select name="line_items[recurring_fees][{{ data.id }}][tax_class]">
                        <# _.each(<?php echo json_encode($tax_class_options); ?>, function(label, value) { #>
                            <option value="{{ value }}" <# if (data.tax_class == value) { #>selected="selected"<# } #>>{{ label }}</option>
                        <# }); #>
                    </select>
                </td>
                <td class="arsol-subtotal-column">{{{ data.subtotal_formatted || '<?php echo wc_price(0); ?>' }}}</td>
                <td class="arsol-actions-column"><a href="#" class="remove-line-item button button-secondary">&times;</a></td>
            </tr>
        </script>

        <script type="text/html" id="tmpl-arsol-shipping-fee-line-item">
            <?php
            // Get WooCommerce shipping classes
            $shipping_classes = array();
            if (function_exists('WC') && WC()->shipping) {
                $wc_shipping_classes = WC()->shipping->get_shipping_classes();
                foreach ($wc_shipping_classes as $shipping_class) {
                    $shipping_classes[$shipping_class->term_id] = $shipping_class->name;
                }
            }
            ?>
            <tr class="arsol-line-item arsol-shipping-fee-item" data-id="{{ data.id }}">
                <td class="arsol-description-column">
                    <input type="text" class="arsol-description-input" name="line_items[shipping_fees][{{ data.id }}][description]" value="{{ data.description || '' }}" placeholder="<?php esc_attr_e('e.g. Express Shipping', 'arsol-pfw'); ?>" required>
                </td>
                <td class="arsol-shipping-class-column">
                    <select class="arsol-select-full" name="line_items[shipping_fees][{{ data.id }}][shipping_class_id]">
                        <option value=""><?php _e('None', 'arsol-pfw'); ?></option>
                        <# _.each(<?php echo json_encode($shipping_classes); ?>, function(name, id) { #>
                            <option value="{{ id }}" <# if (data.shipping_class_id == id) { #>selected="selected"<# } #>>{{ name }}</option>
                        <# }); #>
                    </select>
                </td>
                <td class="arsol-amount-column">
                    <input type="text" class="arsol-amount-input wc_input_price" name="line_items[shipping_fees][{{ data.id }}][amount]" value="{{ data.amount || '' }}" required>
                </td>
                <td class="arsol-taxable-column">
                    <select name="line_items[shipping_fees][{{ data.id }}][tax_class]">
                        <# _.each(<?php echo json_encode($tax_class_options); ?>, function(label, value) { #>
                            <option value="{{ value }}" <# if (data.tax_class == value) { #>selected="selected"<# } #>>{{ label }}</option>
                        <# }); #>
                    </select>
                </td>
                <td class="arsol-subtotal-column">{{{ data.subtotal_formatted || '<?php echo wc_price(0); ?>' }}}</td>
                <td class="arsol-actions-column"><a href="#" class="remove-line-item button button-secondary">&times;</a></td>
            </tr>
        </script>
        <?php
    }

    public function save_quotation_meta_box($post_id) {
        if (!isset($_POST['arsol_proposal_quotation_nonce']) || !wp_verify_nonce($_POST['arsol_proposal_quotation_nonce'], 'arsol_proposal_quotation_save')) {
            return;
        }
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }
        
        // Save Notes
        if (isset($_POST['arsol_pfw_proposal_notes'])) {
            $proposal = new \Arsol_Projects_For_Woo\Custom_Post_Types\Arsol_PFW_Proposal($post_id);
            $proposal->set_proposal_notes(wp_kses_post($_POST['arsol_pfw_proposal_notes']));
        }
        
        $proposal = new \Arsol_Projects_For_Woo\Custom_Post_Types\Arsol_PFW_Proposal($post_id);
        // Get proposal costing type
        $proposal_costing_type = $proposal->get_proposal_costing_type();
        if ($proposal_costing_type !== 'quotation') {
            return;
        }

        // Process and save line items from form submission
        $quotation_data = array();
        
        // Process products - save both product_id and product_name
        if (isset($_POST['line_items']['products']) && is_array($_POST['line_items']['products'])) {
            $quotation_data['products'] = array();
            foreach ($_POST['line_items']['products'] as $id => $product_data) {
                $product_id = isset($product_data['product_id']) ? absint($product_data['product_id']) : 0;
                
                // Validate product exists before saving
                if (!$product_id) {
                    continue; // Skip line items with no product ID
                }
                
                $product = wc_get_product($product_id);
                if (!$product) {
                    continue; // Skip line items with invalid/deleted products
                }
                
                $product_name = $product->get_name();
                $product_type = $product->get_type();
                $is_subscription = in_array($product_type, array('subscription', 'subscription_variation'));
                
                // Build line item data
                $line_item = array(
                    'product_id' => $product_id,
                    'product_name' => $product_name, // Store product name for display
                    'quantity' => isset($product_data['quantity']) ? absint($product_data['quantity']) : 1,
                    'regular_price' => isset($product_data['price']) ? wc_format_decimal($product_data['price']) : '',
                    'sale_price' => isset($product_data['sale_price']) ? wc_format_decimal($product_data['sale_price']) : '',
                    'product_type' => $product_type, // Use actual product type from WooCommerce
                    'details' => isset($product_data['details']) ? sanitize_textarea_field($product_data['details']) : '',
                );
                
                // Only save billing fields for subscription products
                if ($is_subscription) {
                    $line_item['billing_start_date'] = isset($product_data['billing_start_date']) ? sanitize_text_field($product_data['billing_start_date']) : '';
                    $line_item['billing_interval'] = isset($product_data['billing_interval']) ? absint($product_data['billing_interval']) : 1;
                    $line_item['billing_period'] = isset($product_data['billing_period']) ? sanitize_text_field($product_data['billing_period']) : 'month';
                } else {
                    // Ensure billing fields are empty for simple products
                    $line_item['billing_start_date'] = '';
                    $line_item['billing_interval'] = '';
                    $line_item['billing_period'] = '';
                }
                
                $quotation_data['products'][$id] = $line_item;
            }
        }
        
        // Process one-time fees
        if (isset($_POST['line_items']['one_time_fees']) && is_array($_POST['line_items']['one_time_fees'])) {
            $quotation_data['one_time_fees'] = array();
            foreach ($_POST['line_items']['one_time_fees'] as $id => $fee_data) {
                $quotation_data['one_time_fees'][$id] = array(
                    'description' => isset($fee_data['description']) ? sanitize_text_field($fee_data['description']) : '',
                    'amount' => isset($fee_data['amount']) ? wc_format_decimal($fee_data['amount']) : '',
                    'tax_class' => isset($fee_data['tax_class']) ? sanitize_text_field($fee_data['tax_class']) : '',
                );
            }
        }
        
        // Process recurring fees
        if (isset($_POST['line_items']['recurring_fees']) && is_array($_POST['line_items']['recurring_fees'])) {
            $quotation_data['recurring_fees'] = array();
            foreach ($_POST['line_items']['recurring_fees'] as $id => $fee_data) {
                $quotation_data['recurring_fees'][$id] = array(
                    'description' => isset($fee_data['description']) ? sanitize_text_field($fee_data['description']) : '',
                    'amount' => isset($fee_data['amount']) ? wc_format_decimal($fee_data['amount']) : '',
                    'tax_class' => isset($fee_data['tax_class']) ? sanitize_text_field($fee_data['tax_class']) : '',
                    'billing_start_date' => isset($fee_data['billing_start_date']) ? sanitize_text_field($fee_data['billing_start_date']) : '',
                    'billing_interval' => isset($fee_data['billing_interval']) ? absint($fee_data['billing_interval']) : 1,
                    'billing_period' => isset($fee_data['billing_period']) ? sanitize_text_field($fee_data['billing_period']) : 'month',
                );
            }
        }
        
        // Process shipping fees
        if (isset($_POST['line_items']['shipping_fees']) && is_array($_POST['line_items']['shipping_fees'])) {
            $quotation_data['shipping_fees'] = array();
            foreach ($_POST['line_items']['shipping_fees'] as $id => $shipping_data) {
                $quotation_data['shipping_fees'][$id] = array(
                    'description' => isset($shipping_data['description']) ? sanitize_text_field($shipping_data['description']) : '',
                    'shipping_class_id' => isset($shipping_data['shipping_class_id']) ? absint($shipping_data['shipping_class_id']) : 0,
                    'amount' => isset($shipping_data['amount']) ? wc_format_decimal($shipping_data['amount']) : '',
                    'tax_class' => isset($shipping_data['tax_class']) ? sanitize_text_field($shipping_data['tax_class']) : '',
                );
            }
        }
        
        // Save totals to quotation data structure
        $quotation_data['onetime_total'] = isset($_POST['arsol_pfw_proposal_quotation_onetime_total']) ? sanitize_text_field($_POST['arsol_pfw_proposal_quotation_onetime_total']) : '0';
        
        $recurring_totals_json = isset($_POST['line_items_recurring_totals']) ? stripslashes($_POST['line_items_recurring_totals']) : '{}';
        $recurring_totals = json_decode($recurring_totals_json, true);
        $quotation_data['recurring_totals_grouped'] = $recurring_totals;
        
        // Save currency ISO code as the primary source of truth
        $currency_code = get_woocommerce_currency();
        $quotation_data['currency'] = $currency_code;
        $quotation_data['currency_symbol'] = get_woocommerce_currency_symbol($currency_code);
        
        $proposal->set_proposed_project_quotation($quotation_data);
        $proposal->save();
    }

    /**
     * AJAX handler for searching purchasable products
     * Shows: Simple products, variations, external products, subscriptions (if enabled)
     * Excludes: Grouped products, variable products (parents), and other non-purchasable types
     */
    public function ajax_search_products_with_price() {
        check_ajax_referer('search-products', 'security');

        $term = isset($_GET['term']) ? wc_clean(stripslashes($_GET['term'])) : '';
        $limit = isset($_GET['limit']) ? absint($_GET['limit']) : 20;

        if (empty($term)) {
            wp_die();
        }

        // Define allowed product types
        $allowed_types = array('simple', 'external', 'variation');
        
        // Add subscription types if available
        if (class_exists('WC_Subscriptions')) {
            $allowed_types[] = 'subscription';
            $allowed_types[] = 'subscription_variation';
        }

        // Simple query
        $args = array(
            'post_type'      => array('product', 'product_variation'),
            'post_status'    => 'publish',
            'posts_per_page' => $limit,
            's'              => $term,
            'fields'         => 'ids'
        );

        $products = get_posts($args);
        $found_products = array();

        if ($products) {
            foreach ($products as $product_id) {
                $product = wc_get_product($product_id);
                if (!$product) {
                    continue;
                }

                // Only include allowed types
                if (!in_array($product->get_type(), $allowed_types)) {
                    continue;
                }

                // Simple product name
                $found_products[$product_id] = $product->get_name();
            }
        }

        wp_send_json($found_products);
    }

    public function ajax_get_product_details() {
        check_ajax_referer('arsol_proposal_quotation_nonce', 'nonce');
        
        $product_id = isset($_POST['product_id']) ? absint($_POST['product_id']) : 0;
        if (!$product_id) {
            wp_send_json_error('Missing product ID');
        }

        $product = wc_get_product($product_id);
        if (!$product) {
            wp_send_json_error('Invalid product');
        }
        
        $product_type = $product->get_type();
        $is_subscription = in_array($product_type, array('subscription', 'subscription_variation'));
        $sign_up_fee = 0;
        $regular_price_val = 0;
        $sale_price_val = '';
        $billing_interval = null;
        $billing_period = null;

        if ($is_subscription && class_exists('WC_Product_Subscription')) {
            // Logic for Subscription Products
            $regular_price_val = $product->get_regular_price();
            $active_price = $product->get_price();
            if (is_numeric($active_price) && is_numeric($regular_price_val) && $active_price < $regular_price_val) {
                $sale_price_val = $active_price;
            }
            
            $sign_up_fee = (float) $product->get_meta('_subscription_sign_up_fee');
            $billing_interval = $product->get_meta('_subscription_period_interval');
            $billing_period = $product->get_meta('_subscription_period');

        } else {
            // Logic for Simple/Other Products
            $regular_price_val = $product->get_regular_price();
            $sale_price_val = $product->get_sale_price();
        }
        
        // Ensure we have numeric values before formatting
        $regular_price_val = is_numeric($regular_price_val) ? (float) $regular_price_val : 0;
        $sale_price_val = is_numeric($sale_price_val) ? (float) $sale_price_val : '';

        $data = array(
            'product_name' => $product->get_name(),
            'regular_price' => wc_format_decimal($regular_price_val, wc_get_price_decimals()),
            'sale_price' => $sale_price_val !== '' ? wc_format_decimal($sale_price_val, wc_get_price_decimals()) : '',
            'product_type' => $product_type, // WooCommerce product types with prices: simple, subscription, subscription_variation, variation, external
            'sign_up_fee' => wc_format_decimal($sign_up_fee, wc_get_price_decimals()),
            'billing_interval' => $billing_interval,
            'billing_period'   => $billing_period
        );

        wp_send_json_success($data);
    }

    /**
     * Add conditional CSS class to quotation metabox
     */
    public function add_quotation_metabox_classes($classes) {
        $classes[] = 'arsol-pfw-show-if-arsol_pfw_proposal_costing_type-is-quotation';
        return $classes;
    }
}