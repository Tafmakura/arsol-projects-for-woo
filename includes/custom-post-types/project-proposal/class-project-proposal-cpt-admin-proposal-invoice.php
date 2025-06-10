<?php
namespace Arsol_Projects_For_Woo\Custom_Post_Types\ProjectProposal\Admin;

use Arsol_Projects_For_Woo\Woocommerce_Subscriptions;

if (!defined('ABSPATH')) {
    exit;
}

class Proposal_Invoice {
    public function __construct() {
        add_action('add_meta_boxes', array($this, 'add_invoice_meta_box'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('save_post_arsol-pfw-proposal', array($this, 'save_invoice_meta_box'));
        
        // AJAX Handlers
        add_action('wp_ajax_arsol_proposal_invoice_ajax_search_products', array($this, 'ajax_search_products'));
        add_action('wp_ajax_arsol_proposal_invoice_ajax_get_product_details', array($this, 'ajax_get_product_details'));
    }

    public function add_invoice_meta_box() {
        add_meta_box(
            'arsol_proposal_invoice_metabox',
            __('Proposal Line Items', 'arsol-pfw'),
            array($this, 'render_invoice_meta_box'),
            'arsol-pfw-proposal',
            'normal',
            'high'
        );
    }

    public function enqueue_scripts($hook) {
        global $post;

        if (('post.php' === $hook || 'post-new.php' === $hook) && isset($post->post_type) && 'arsol-pfw-proposal' === $post->post_type) {
            $plugin_dir = \ARSOL_PROJECTS_PLUGIN_DIR;
            $plugin_url = \ARSOL_PROJECTS_PLUGIN_URL;

            wp_enqueue_style(
                'arsol-proposal-invoice',
                $plugin_url . 'includes/custom-post-types/project-proposal/assets/admin-proposal-invoice.css',
                array(),
                filemtime($plugin_dir . 'includes/custom-post-types/project-proposal/assets/admin-proposal-invoice.css')
            );

            wp_enqueue_script(
                'arsol-proposal-invoice',
                $plugin_url . 'includes/custom-post-types/project-proposal/assets/admin-proposal-invoice.js',
                array('jquery', 'select2', 'wp-util'),
                filemtime($plugin_dir . 'includes/custom-post-types/project-proposal/assets/admin-proposal-invoice.js'),
                true
            );
            
            // Get currency symbol based on ISO code for historical accuracy
            $saved_code = get_post_meta($post->ID, '_arsol_proposal_currency', true);
            $currency_symbol = $saved_code ? get_woocommerce_currency_symbol($saved_code) : get_woocommerce_currency_symbol();

            // Get line items and populate product names from IDs
            $line_items = get_post_meta($post->ID, '_arsol_proposal_line_items', true) ?: array();
            
            // Populate product names for existing products
            if (!empty($line_items['products'])) {
                foreach ($line_items['products'] as $key => $product_item) {
                    if (!empty($product_item['product_id']) && empty($product_item['product_name'])) {
                        $product = wc_get_product($product_item['product_id']);
                        if ($product) {
                            $line_items['products'][$key]['product_name'] = $product->get_formatted_name();
                        }
                    }
                }
            }

             wp_localize_script(
                'arsol-proposal-invoice',
                'arsol_proposal_invoice_vars',
                array(
                    'ajax_url' => admin_url('admin-ajax.php'),
                    'nonce'   => wp_create_nonce('arsol-proposal-invoice-nonce'),
                    'currency_symbol' => $currency_symbol,
                    'line_items' => $line_items,
                    'calculation_constants' => Woocommerce_Subscriptions::get_calculation_constants()
                )
            );
        }
    }

    public function render_invoice_meta_box($post) {
        wp_nonce_field('arsol_proposal_invoice_save', 'arsol_proposal_invoice_nonce');
        ?>
        <div id="proposal_invoice_builder">
            <!-- Products Section -->
            <div class="line-items-container">
                <h3><?php _e('Products & Services', 'arsol-pfw'); ?></h3>
                <table class="widefat" id="product-line-items">
                    <thead>
                        <tr>
                            <th class="product-column"><?php _e('Product', 'arsol-pfw'); ?></th>
                            <th class="start-date-column"><?php _e('Start Date', 'arsol-pfw'); ?></th>
                            <th><?php _e('Qty', 'arsol-pfw'); ?></th>
                            <th><?php _e('Price', 'arsol-pfw'); ?></th>
                            <th><?php _e('Sale Price', 'arsol-pfw'); ?></th>
                            <th class="subtotal-column"><?php _e('Subtotal', 'arsol-pfw'); ?></th>
                            <th class="actions-column"></th>
                        </tr>
                    </thead>
                    <tbody id="product-lines-body"></tbody>
                </table>
                <div class="section-footer">
                    <div class="section-footer-left">
                        <button type="button" class="button add-line-item" data-type="product"><?php _e('+ Add Product', 'arsol-pfw'); ?></button>
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
                            <th class="fee-name-column"><?php _e('Fee Name', 'arsol-pfw'); ?></th>
                            <th class="start-date-column"><?php _e('Start Date', 'arsol-pfw'); ?></th>
                            <th><?php _e('Amount', 'arsol-pfw'); ?></th>
                            <th class="billing-cycle-column"><?php _e('Billing Cycle', 'arsol-pfw'); ?></th>
                            <th class="taxable-column"><?php _e('Tax', 'arsol-pfw'); ?></th>
                            <th class="subtotal-column"><?php _e('Subtotal', 'arsol-pfw'); ?></th>
                            <th class="actions-column"></th>
                        </tr>
                    </thead>
                    <tbody id="recurring-fee-lines-body"></tbody>
                </table>
                <div class="section-footer">
                    <div class="section-footer-left">
                        <button type="button" class="button add-line-item" data-type="recurring-fee"><?php _e('+ Add Recurring Fee', 'arsol-pfw'); ?></button>
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
                            <th class="fee-name-column"><?php _e('Fee Name', 'arsol-pfw'); ?></th>
                            <th><?php _e('Amount', 'arsol-pfw'); ?></th>
                            <th class="taxable-column"><?php _e('Tax', 'arsol-pfw'); ?></th>
                            <th class="subtotal-column"><?php _e('Subtotal', 'arsol-pfw'); ?></th>
                            <th class="actions-column"></th>
                        </tr>
                    </thead>
                    <tbody id="onetime-fee-lines-body"></tbody>
                </table>
                <div class="section-footer">
                    <div class="section-footer-left">
                        <button type="button" class="button add-line-item" data-type="onetime-fee"><?php _e('+ Add Fee', 'arsol-pfw'); ?></button>
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
            <!-- Recurring Fees Section -->
            <div class="line-items-container" style="display:none;">
            </div>
            <hr>
            <!-- Shipping Section -->
            <div class="line-items-container">
                <h3><?php _e('Shipping', 'arsol-pfw'); ?></h3>
                <table class="widefat" id="shipping-lines-table">
                     <thead>
                        <tr>
                            <th class="shipping-description-column"><?php _e('Description', 'arsol-pfw'); ?></th>
                            <th class="shipping-class-column"><?php _e('Shipping Class', 'arsol-pfw'); ?></th>
                            <th class="shipping-amount-column"><?php _e('Amount', 'arsol-pfw'); ?></th>
                            <th class="taxable-column"><?php _e('Tax', 'arsol-pfw'); ?></th>
                            <th class="subtotal-column"><?php _e('Subtotal', 'arsol-pfw'); ?></th>
                            <th class="actions-column"></th>
                        </tr>
                    </thead>
                    <tbody id="shipping-lines-body"></tbody>
                </table>
                <div class="section-footer">
                    <div class="section-footer-left">
                        <button type="button" class="button add-line-item" data-type="shipping-fee"><?php _e('+ Add Shipping Fee', 'arsol-pfw'); ?></button>
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
            <div class="line-items-totals">
                <table align="right" class="totals-table">
                    <tbody>
                        <tr>
                            <td class="total-amount"><strong><?php _e('One-Time Total:', 'arsol-pfw'); ?></strong> <span id="one-time-total-display"><?php echo wc_price(0); ?></span></td>
                        </tr>
                        <tr>
                            <td class="total-amount"><strong><?php _e('Average Recurring Total:', 'arsol-pfw'); ?></strong> <span id="average-monthly-total-display"><?php echo wc_price(0); ?></span></td>
                        </tr>
                    </tbody>
                </table>
                 <input type="hidden" name="line_items_one_time_total" id="line_items_one_time_total">
                 <input type="hidden" name="line_items_recurring_totals" id="line_items_recurring_totals">
            </div>
            <hr>
            <!-- Notes Section -->
            <div class="line-items-container">
                <h3><?php _e('Notes', 'arsol-pfw'); ?></h3>
                <p class="description"><?php _e('These notes will be displayed on the frontend proposal view.', 'arsol-pfw'); ?></p>
                <?php
                $notes_content = get_post_meta($post->ID, '_arsol_proposal_notes', true);
                wp_editor(
                    $notes_content,
                    'arsol_proposal_notes',
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
        $this->render_js_templates();
    }
    
    private function render_js_templates() {
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
            <tr class="line-item product-item" data-id="{{ data.id }}">
                <td class="product-column">
                    <select class="product-select" name="line_items[products][{{ data.id }}][product_id]" style="width:100%;">
                        <# if (data.product_id && data.product_name) { #>
                            <option value="{{ data.product_id }}" selected="selected">{{ data.product_name }}</option>
                        <# } else { #>
                             <option value=""><?php _e('Select a product', 'arsol-pfw'); ?></option>
                        <# } #>
                    </select>
                    <div class="product-sub-text">{{{ data.sub_text }}}</div>
                </td>
                <td class="start-date-column">
                    <input type="date" class="start-date-input" name="line_items[products][{{ data.id }}][start_date]" value="{{ data.start_date || '' }}" style="display:none;">
                </td>
                <td><input type="number" class="quantity-input" name="line_items[products][{{ data.id }}][quantity]" value="{{ data.quantity || 1 }}" min="1"></td>
                <td><input type="text" class="price-input wc_input_price" name="line_items[products][{{ data.id }}][price]" value="{{ data.regular_price || '' }}"></td>
                <td><input type="text" class="sale-price-input wc_input_price" name="line_items[products][{{ data.id }}][sale_price]" value="{{ data.sale_price || '' }}"></td>
                <td class="subtotal-display subtotal-column">{{{ data.subtotal_formatted || '<?php echo wc_price(0); ?>' }}}</td>
                <td class="actions-column"><a href="#" class="remove-line-item button button-secondary">&times;</a></td>
            </tr>
        </script>

        <script type="text/html" id="tmpl-arsol-onetime-fee-line-item">
            <tr class="line-item fee-item" data-id="{{ data.id }}">
                <td class="fee-name-column">
                    <input type="text" class="fee-name-input" name="line_items[one_time_fees][{{ data.id }}][name]" value="{{ data.name || '' }}" placeholder="<?php esc_attr_e('e.g. Setup Fee', 'arsol-pfw'); ?>">
                </td>
                <td>
                    <input type="text" class="fee-amount-input wc_input_price" name="line_items[one_time_fees][{{ data.id }}][amount]" value="{{ data.amount || '' }}">
                </td>
                <td class="taxable-column">
                    <select name="line_items[one_time_fees][{{ data.id }}][tax_class]">
                        <# _.each(<?php echo json_encode($tax_class_options); ?>, function(label, value) { #>
                            <option value="{{ value }}" <# if (data.tax_class == value) { #>selected="selected"<# } #>>{{ label }}</option>
                        <# }); #>
                    </select>
                </td>
                <td class="subtotal-display subtotal-column">{{{ data.subtotal_formatted || '<?php echo wc_price(0); ?>' }}}</td>
                <td class="actions-column"><a href="#" class="remove-line-item button button-secondary">&times;</a></td>
            </tr>
        </script>

        <script type="text/html" id="tmpl-arsol-recurring-fee-line-item">
             <tr class="line-item recurring-fee-item" data-id="{{ data.id }}">
                <td class="fee-name-column">
                    <input type="text" class="fee-name-input" name="line_items[recurring_fees][{{ data.id }}][name]" value="{{ data.name || '' }}" placeholder="<?php esc_attr_e('e.g. Monthly Maintenance', 'arsol-pfw'); ?>">
                </td>
                <td class="start-date-column">
                    <input type="date" class="start-date-input" name="line_items[recurring_fees][{{ data.id }}][start_date]" value="{{ data.start_date || '' }}">
                </td>
                <td>
                    <input type="text" class="fee-amount-input wc_input_price" name="line_items[recurring_fees][{{ data.id }}][amount]" value="{{ data.amount || '' }}">
                </td>
                <td class="billing-cycle-column">
                    <?php
                        $intervals = function_exists('wcs_get_subscription_period_interval_strings') ? wcs_get_subscription_period_interval_strings() : array(1=>1);
                        $periods = function_exists('wcs_get_subscription_period_strings') ? wcs_get_subscription_period_strings() : array('month' => 'month');
                    ?>
                    <select name="line_items[recurring_fees][{{ data.id }}][interval]" class="billing-interval">
                        <# _.each(<?php echo json_encode($intervals); ?>, function(label, value) { #>
                            <option value="{{ value }}" <# if (data.interval == value) { #>selected="selected"<# } #>>{{ label }}</option>
                        <# }); #>
                    </select>
                    <select name="line_items[recurring_fees][{{ data.id }}][period]" class="billing-period">
                         <# _.each(<?php echo json_encode($periods); ?>, function(label, value) { #>
                            <option value="{{ value }}" <# if (data.period == value) { #>selected="selected"<# } #>>{{ label }}</option>
                        <# }); #>
                    </select>
                </td>
                <td class="taxable-column">
                    <select name="line_items[recurring_fees][{{ data.id }}][tax_class]">
                        <# _.each(<?php echo json_encode($tax_class_options); ?>, function(label, value) { #>
                            <option value="{{ value }}" <# if (data.tax_class == value) { #>selected="selected"<# } #>>{{ label }}</option>
                        <# }); #>
                    </select>
                </td>
                <td class="subtotal-display subtotal-column">{{{ data.subtotal_formatted || '<?php echo wc_price(0); ?>' }}}</td>
                <td class="actions-column"><a href="#" class="remove-line-item button button-secondary">&times;</a></td>
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
            <tr class="line-item shipping-fee-item" data-id="{{ data.id }}">
                <td class="shipping-description-column">
                    <input type="text" class="shipping-description-input" name="line_items[shipping_fees][{{ data.id }}][description]" value="{{ data.description || '' }}" placeholder="<?php esc_attr_e('e.g. Express Shipping', 'arsol-pfw'); ?>">
                </td>
                <td class="shipping-class-column">
                    <select class="shipping-class-select" name="line_items[shipping_fees][{{ data.id }}][shipping_class_id]">
                        <option value=""><?php _e('None', 'arsol-pfw'); ?></option>
                        <# _.each(<?php echo json_encode($shipping_classes); ?>, function(name, id) { #>
                            <option value="{{ id }}" <# if (data.shipping_class_id == id) { #>selected="selected"<# } #>>{{ name }}</option>
                        <# }); #>
                    </select>
                </td>
                <td class="shipping-amount-column">
                    <input type="text" class="fee-amount-input wc_input_price" name="line_items[shipping_fees][{{ data.id }}][amount]" value="{{ data.amount || '' }}">
                </td>
                <td class="taxable-column">
                    <select name="line_items[shipping_fees][{{ data.id }}][tax_class]">
                        <# _.each(<?php echo json_encode($tax_class_options); ?>, function(label, value) { #>
                            <option value="{{ value }}" <# if (data.tax_class == value) { #>selected="selected"<# } #>>{{ label }}</option>
                        <# }); #>
                    </select>
                </td>
                <td class="subtotal-display subtotal-column">{{{ data.subtotal_formatted || '<?php echo wc_price(0); ?>' }}}</td>
                <td class="actions-column"><a href="#" class="remove-line-item button button-secondary">&times;</a></td>
            </tr>
        </script>
        <?php
    }

    public function save_invoice_meta_box($post_id) {
        if (!isset($_POST['arsol_proposal_invoice_nonce']) || !wp_verify_nonce($_POST['arsol_proposal_invoice_nonce'], 'arsol_proposal_invoice_save')) {
            return;
        }
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }
        
        // Save Notes
        if (isset($_POST['arsol_proposal_notes'])) {
            update_post_meta($post_id, '_arsol_proposal_notes', wp_kses_post($_POST['arsol_proposal_notes']));
        }
        
        $cost_proposal_type = get_post_meta($post_id, '_cost_proposal_type', true);
        if ($cost_proposal_type !== 'invoice_line_items') {
            return;
        }

        $line_items = isset($_POST['line_items']) ? (array) $_POST['line_items'] : array();
        
        $sanitized_line_items = array();
        if (!empty($line_items)) {
            foreach ( $line_items as $group_key => $group_value ) {
                if (!empty($group_value)) {
                    $sanitized_line_items[$group_key] = array_map( function( $item ) {
                        if (isset($item['sub_text'])) {
                            $item['sub_text'] = wp_kses_post($item['sub_text']);
                        }
                        return array_map( 'sanitize_text_field', $item );
                    }, (array) $group_value );
                }
            }
        }
        
        update_post_meta($post_id, '_arsol_proposal_line_items', $sanitized_line_items);
        update_post_meta($post_id, '_arsol_proposal_one_time_total', sanitize_text_field($_POST['line_items_one_time_total']));
        
        $recurring_totals_json = isset($_POST['line_items_recurring_totals']) ? stripslashes($_POST['line_items_recurring_totals']) : '{}';
        $recurring_totals = json_decode($recurring_totals_json, true);
        update_post_meta($post_id, '_arsol_proposal_recurring_totals_grouped', $recurring_totals);
        
        // Save currency ISO code as the primary source of truth
        $currency_code = get_woocommerce_currency();
        update_post_meta($post_id, '_arsol_proposal_currency', $currency_code);
        update_post_meta($post_id, '_arsol_proposal_currency_symbol', get_woocommerce_currency_symbol($currency_code));
    }

    public function ajax_search_products() {
        check_ajax_referer('arsol-proposal-invoice-nonce', 'nonce');

        $search_term = isset($_GET['search']) ? sanitize_text_field($_GET['search']) : '';
        if (empty($search_term)) {
            wp_send_json_error('Missing search term');
        }

        $product_types = apply_filters('arsol_proposal_product_types', array('simple', 'variable', 'subscription', 'variation'));

        $query = new \WC_Product_Query( array(
            'limit' => 20,
            'status' => 'publish',
            's' => $search_term,
            'stock_status' => 'instock',
            'type' => $product_types,
        ) );

        $products = array();
        foreach ( $query->get_products() as $product ) {
            $products[] = array(
                'id'   => $product->get_id(),
                'text' => $product->get_formatted_name(),
            );
        }
        
        wp_send_json_success($products);
    }

    public function ajax_get_product_details() {
        check_ajax_referer('arsol-proposal-invoice-nonce', 'nonce');
        
        $product_id = isset($_POST['product_id']) ? absint($_POST['product_id']) : 0;
        if (!$product_id) {
            wp_send_json_error('Missing product ID');
        }

        $product = wc_get_product($product_id);
        if (!$product) {
            wp_send_json_error('Invalid product');
        }
        
        $is_subscription = $product->is_type(array('subscription', 'subscription_variation'));
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
            'regular_price' => wc_format_decimal($regular_price_val, wc_get_price_decimals()),
            'sale_price' => $sale_price_val !== '' ? wc_format_decimal($sale_price_val, wc_get_price_decimals()) : '',
            'is_subscription' => $is_subscription,
            'sign_up_fee' => wc_format_decimal($sign_up_fee, wc_get_price_decimals()),
            'billing_interval' => $billing_interval,
            'billing_period'   => $billing_period
        );

        wp_send_json_success($data);
    }
}