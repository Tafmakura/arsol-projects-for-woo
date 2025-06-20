<?php
namespace Arsol_Projects_For_Woo\Custom_Post_Types\ProjectProposal\Admin;

use Arsol_Projects_For_Woo\Woocommerce_Subscriptions;

if (!defined('ABSPATH')) {
    exit;
}

class Proposal_Quotation {
    public function __construct() {
        add_action('add_meta_boxes', array($this, 'add_quotation_meta_box'));
        add_action('save_post', array($this, 'save_quotation_meta_box'));
        // Custom product search for products with prices only
        add_action('wp_ajax_arsol_search_products_with_price', array($this, 'ajax_search_products_with_price'));
        add_action('wp_ajax_arsol_proposal_quotation_ajax_get_product_details', array($this, 'ajax_get_product_details'));
        add_action('admin_footer', array($this, 'render_js_templates_in_footer'));
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
                        <th class="arsol-date-column"><?php _e('Start Date', 'arsol-pfw'); ?></th>
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
                        <th class="arsol-date-column"><?php _e('Start Date', 'arsol-pfw'); ?></th>
                        <th class="arsol-amount-column"><?php _e('Amount', 'arsol-pfw'); ?></th>
                        <th class="arsol-billing-cycle-column"><?php _e('Billing Cycle', 'arsol-pfw'); ?></th>
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
             <tr class="arsol-line-item arsol-product-item" data-id="{{ data.id }}">
                <td class="arsol-description-column">
                    <select class="arsol-description-input" name="line_items[products][{{ data.id }}][product_id]">
                        <option value=""><?php _e('Select Product...', 'arsol-pfw'); ?></option>
                        <?php foreach ($products as $product) : ?>
                            <option value="<?php echo esc_attr($product->get_id()); ?>" data-price="<?php echo esc_attr($product->get_regular_price()); ?>">
                                <?php echo esc_html($product->get_name()); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </td>
                <td class="arsol-date-column">
                    <input type="date" class="arsol-date-input" name="line_items[products][{{ data.id }}][start_date]" value="{{ data.start_date || '' }}">
                </td>
                <td class="arsol-amount-column">
                    <input type="text" class="arsol-amount-input wc_input_price" name="line_items[products][{{ data.id }}][regular_price]" value="{{ data.regular_price || '' }}"></td>
                <td class="arsol-amount-column">
                    <input type="number" class="arsol-amount-input" name="line_items[products][{{ data.id }}][quantity]" value="{{ data.quantity || '1' }}" min="1" step="1">
                </td>
                <td class="arsol-subtotal-column">
                    <span class="arsol-subtotal-display">{{ data.subtotal || '<?php echo wc_price(0); ?>' }}</span>
                </td>
                <td class="arsol-actions-column">
                    <a href="#" class="remove-line-item button button-link-delete"><?php _e('Remove', 'arsol-pfw'); ?></a>
                </td>
            </tr>
        </script>

        <script type="text/html" id="tmpl-arsol-one-time-fee-line-item">
             <tr class="arsol-line-item arsol-fee-item" data-id="{{ data.id }}">
                <td class="arsol-description-column">
                    <input type="text" class="arsol-description-input" name="line_items[one_time_fees][{{ data.id }}][description]" value="{{ data.description || '' }}" placeholder="<?php esc_attr_e('e.g. Setup Fee', 'arsol-pfw'); ?>">
                </td>
                <td class="arsol-date-column">
                    <span class="arsol-not-applicable">—</span>
                </td>
                <td class="arsol-amount-column">
                    <input type="text" class="arsol-amount-input wc_input_price" name="line_items[one_time_fees][{{ data.id }}][amount]" value="{{ data.amount || '' }}">
                </td>
                <td class="arsol-amount-column">
                    <span class="arsol-not-applicable">—</span>
                </td>
                <td class="arsol-subtotal-column">
                    <span class="arsol-subtotal-display">{{ data.subtotal || '<?php echo wc_price(0); ?>' }}</span>
                </td>
                <td class="arsol-actions-column">
                    <a href="#" class="remove-line-item button button-link-delete"><?php _e('Remove', 'arsol-pfw'); ?></a>
                </td>
            </tr>
        </script>

        <script type="text/html" id="tmpl-arsol-recurring-fee-line-item">
             <tr class="arsol-line-item arsol-recurring-fee-item" data-id="{{ data.id }}">
                <td class="arsol-description-column">
                    <input type="text" class="arsol-description-input" name="line_items[recurring_fees][{{ data.id }}][description]" value="{{ data.description || '' }}" placeholder="<?php esc_attr_e('e.g. Monthly Maintenance', 'arsol-pfw'); ?>">
                </td>
                <td class="arsol-date-column">
                    <input type="date" class="arsol-date-input" name="line_items[recurring_fees][{{ data.id }}][start_date]" value="{{ data.start_date || '' }}">
                </td>
                <td class="arsol-amount-column">
                    <input type="text" class="arsol-amount-input wc_input_price" name="line_items[recurring_fees][{{ data.id }}][amount]" value="{{ data.amount || '' }}">
                </td>
                <td class="arsol-amount-column">
                    <select class="arsol-billing-select" name="line_items[recurring_fees][{{ data.id }}][billing_interval]">
                        <option value="1" {{ data.billing_interval == '1' ? 'selected' : '' }}><?php _e('every', 'arsol-pfw'); ?></option>
                        <option value="2" {{ data.billing_interval == '2' ? 'selected' : '' }}><?php _e('every 2nd', 'arsol-pfw'); ?></option>
                        <option value="3" {{ data.billing_interval == '3' ? 'selected' : '' }}><?php _e('every 3rd', 'arsol-pfw'); ?></option>
                        <option value="4" {{ data.billing_interval == '4' ? 'selected' : '' }}><?php _e('every 4th', 'arsol-pfw'); ?></option>
                        <option value="5" {{ data.billing_interval == '5' ? 'selected' : '' }}><?php _e('every 5th', 'arsol-pfw'); ?></option>
                        <option value="6" {{ data.billing_interval == '6' ? 'selected' : '' }}><?php _e('every 6th', 'arsol-pfw'); ?></option>
                    </select>
                    <select class="arsol-billing-select" name="line_items[recurring_fees][{{ data.id }}][billing_period]">
                        <option value="day" {{ data.billing_period == 'day' ? 'selected' : '' }}><?php _e('day', 'arsol-pfw'); ?></option>
                        <option value="week" {{ data.billing_period == 'week' ? 'selected' : '' }}><?php _e('week', 'arsol-pfw'); ?></option>
                        <option value="month" {{ data.billing_period == 'month' ? 'selected' : '' }}><?php _e('month', 'arsol-pfw'); ?></option>
                        <option value="year" {{ data.billing_period == 'year' ? 'selected' : '' }}><?php _e('year', 'arsol-pfw'); ?></option>
                    </select>
                </td>
                <td class="arsol-subtotal-column">
                    <span class="arsol-subtotal-display">{{ data.subtotal || '<?php echo wc_price(0); ?>' }}</span> <span class="arsol-billing-period">{{ data.billing_period_display || '/mo' }}</span>
                </td>
                <td class="arsol-actions-column">
                    <a href="#" class="remove-line-item button button-link-delete"><?php _e('Remove', 'arsol-pfw'); ?></a>
                </td>
            </tr>
        </script>

        <script type="text/html" id="tmpl-arsol-shipping-fee-line-item">
             <tr class="arsol-line-item arsol-shipping-fee-item" data-id="{{ data.id }}">
                <td class="arsol-description-column">
                    <input type="text" class="arsol-description-input" name="line_items[shipping_fees][{{ data.id }}][description]" value="{{ data.description || '' }}" placeholder="<?php esc_attr_e('e.g. Express Shipping', 'arsol-pfw'); ?>">
                </td>
                <td class="arsol-date-column">
                    <span class="arsol-not-applicable">—</span>
                </td>
                <td class="arsol-amount-column">
                    <input type="text" class="arsol-amount-input wc_input_price" name="line_items[shipping_fees][{{ data.id }}][amount]" value="{{ data.amount || '' }}">
                </td>
                <td class="arsol-amount-column">
                    <span class="arsol-not-applicable">—</span>
                </td>
                <td class="arsol-subtotal-column">
                    <span class="arsol-subtotal-display">{{ data.subtotal || '<?php echo wc_price(0); ?>' }}</span>
                </td>
                <td class="arsol-actions-column">
                    <a href="#" class="remove-line-item button button-link-delete"><?php _e('Remove', 'arsol-pfw'); ?></a>
                </td>
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
        if (isset($_POST['arsol_proposal_notes'])) {
            update_post_meta($post_id, '_arsol_proposal_notes', wp_kses_post($_POST['arsol_proposal_notes']));
        }
        
        $cost_proposal_type = get_post_meta($post_id, '_cost_proposal_type', true);
        if ($cost_proposal_type !== 'quotation') {
            return;
        }

        // Clean up budget data when saving quotation
        delete_post_meta($post_id, '_proposal_budget');
        delete_post_meta($post_id, '_proposal_budget_details');
        delete_post_meta($post_id, '_proposal_recurring_budget');
        delete_post_meta($post_id, '_proposal_recurring_budget_details');
        delete_post_meta($post_id, '_proposal_billing_interval');
        delete_post_meta($post_id, '_proposal_billing_period');
        delete_post_meta($post_id, '_proposal_recurring_start_date');

        $line_items = isset($_POST['line_items']) ? (array) $_POST['line_items'] : array();
        
        $sanitized_line_items = array();
        if (!empty($line_items)) {
            foreach ( $line_items as $group_key => $group_value ) {
                if (!empty($group_value)) {
                    $sanitized_line_items[$group_key] = array_map( function( $item ) {
                        return array_map( 'sanitize_text_field', $item );
                    }, (array) $group_value );
                }
            }
        }
        
        update_post_meta($post_id, '_arsol_proposal_quotation_line_items', $sanitized_line_items);
        update_post_meta($post_id, '_arsol_proposal_one_time_total', sanitize_text_field($_POST['line_items_one_time_total']));
        
        $recurring_totals_json = isset($_POST['line_items_recurring_totals']) ? stripslashes($_POST['line_items_recurring_totals']) : '{}';
        $recurring_totals = json_decode($recurring_totals_json, true);
        update_post_meta($post_id, '_arsol_proposal_recurring_totals_grouped', $recurring_totals);
        
        // Save currency ISO code as the primary source of truth
        $currency_code = get_woocommerce_currency();
        update_post_meta($post_id, '_arsol_proposal_currency', $currency_code);
        update_post_meta($post_id, '_arsol_proposal_currency_symbol', get_woocommerce_currency_symbol($currency_code));
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
        check_ajax_referer('arsol-proposal-quotation-nonce', 'nonce');
        
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
            'regular_price' => wc_format_decimal($regular_price_val, wc_get_price_decimals()),
            'sale_price' => $sale_price_val !== '' ? wc_format_decimal($sale_price_val, wc_get_price_decimals()) : '',
            'product_type' => $product_type, // WooCommerce product types with prices: simple, subscription, subscription_variation, variation, external
            'sign_up_fee' => wc_format_decimal($sign_up_fee, wc_get_price_decimals()),
            'billing_interval' => $billing_interval,
            'billing_period'   => $billing_period
        );

        wp_send_json_success($data);
    }
}