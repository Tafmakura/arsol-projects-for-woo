<?php

namespace Arsol_Projects_For_Woo\Custom_Post_Types\ProjectProposal\Admin;

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
            $plugin_dir = ARSOL_PROJECTS_PLUGIN_DIR;
            $plugin_url = ARSOL_PROJECTS_PLUGIN_URL;

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
             wp_localize_script(
                'arsol-proposal-invoice',
                'arsol_proposal_invoice_vars',
                array(
                    'ajax_url' => admin_url('admin-ajax.php'),
                    'nonce'   => wp_create_nonce('arsol-proposal-invoice-nonce'),
                    'currency_symbol' => get_woocommerce_currency_symbol(),
                    'line_items' => get_post_meta($post->ID, '_arsol_proposal_line_items', true) ?: array()
                )
            );
        }
    }

    public function render_invoice_meta_box($post) {
        wp_nonce_field('arsol_proposal_invoice_save', 'arsol_proposal_invoice_nonce');
        ?>
        <div id="proposal_invoice_builder">
            <div class="line-items-container">
                <h3><?php _e('Products', 'arsol-pfw'); ?></h3>
                <table class="widefat" id="product-line-items">
                    <thead>
                        <tr>
                            <th class="product-column"><?php _e('Product', 'arsol-pfw'); ?></th>
                            <th><?php _e('Qty', 'arsol-pfw'); ?></th>
                            <th><?php _e('Price', 'arsol-pfw'); ?></th>
                            <th><?php _e('Sale Price', 'arsol-pfw'); ?></th>
                            <th><?php _e('Subtotal', 'arsol-pfw'); ?></th>
                            <th class="actions-column"></th>
                        </tr>
                    </thead>
                    <tbody id="product-lines-body">
                        <?php // Product rows will be added here by JS ?>
                    </tbody>
                </table>
                <button type="button" class="button add-line-item" data-type="product"><?php _e('+ Add Product', 'arsol-pfw'); ?></button>
            </div>

            <hr>

            <div class="line-items-container">
                <h3><?php _e('One-Time Fees', 'arsol-pfw'); ?></h3>
                <table class="widefat" id="onetime-fee-line-items">
                    <thead>
                        <tr>
                            <th class="fee-name-column"><?php _e('Fee Name', 'arsol-pfw'); ?></th>
                            <th><?php _e('Amount', 'arsol-pfw'); ?></th>
                            <th><?php _e('Subtotal', 'arsol-pfw'); ?></th>
                            <th class="taxable-column"><?php _e('Taxable', 'arsol-pfw'); ?></th>
                            <th class="actions-column"></th>
                        </tr>
                    </thead>
                    <tbody id="onetime-fee-lines-body">
                        <?php // Fee rows will be added here by JS ?>
                    </tbody>
                </table>
                <button type="button" class="button add-line-item" data-type="onetime-fee"><?php _e('+ Add Fee', 'arsol-pfw'); ?></button>
            </div>

            <hr>
             <div class="line-items-totals">
                <table align="right">
                    <tr>
                        <td><strong><?php _e('Grand Total:', 'arsol-pfw'); ?></strong></td>
                        <td width="1%"></td>
                        <td class="grand-total-amount">
                            <span id="grand-total-display"><?php echo wc_price(0); ?></span>
                            <input type="hidden" name="line_items_grand_total" id="line_items_grand_total">
                        </td>
                    </tr>
                </table>
            </div>
        </div>
        <?php
        $this->render_js_templates();
    }
    
    private function render_js_templates() {
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
                </td>
                <td><input type="number" class="quantity-input" name="line_items[products][{{ data.id }}][quantity]" value="{{ data.quantity || 1 }}" min="1"></td>
                <td><input type="text" class="price-input wc_input_price" name="line_items[products][{{ data.id }}][price]" value="{{ data.price || '' }}" readonly></td>
                <td><input type="text" class="sale-price-input wc_input_price" name="line_items[products][{{ data.id }}][sale_price]" value="{{ data.sale_price || '' }}"></td>
                <td class="subtotal-display">{{{ data.subtotal_formatted || '<?php echo wc_price(0); ?>' }}}</td>
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
                <td class="subtotal-display">{{{ data.subtotal_formatted || '<?php echo wc_price(0); ?>' }}}</td>
                <td class="taxable-column">
                    <input type="checkbox" name="line_items[one_time_fees][{{ data.id }}][taxable]" <# if (data.taxable) { #>checked="checked"<# } #>>
                </td>
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

        $line_items = isset($_POST['line_items']) ? $_POST['line_items'] : array();
        
        // Basic sanitation, more would be needed for a production plugin
        $sanitized_line_items = array_map(function($group) {
            return array_map(function($item) {
                return array_map('sanitize_text_field', $item);
            }, $group);
        }, $line_items);

        update_post_meta($post_id, '_arsol_proposal_line_items', $sanitized_line_items);
        update_post_meta($post_id, '_arsol_proposal_grand_total', sanitize_text_field($_POST['line_items_grand_total']));
    }

    public function ajax_search_products() {
        check_ajax_referer('arsol-proposal-invoice-nonce', 'nonce');

        $search_term = isset($_GET['search']) ? sanitize_text_field($_GET['search']) : '';
        if (empty($search_term)) {
            wp_send_json_error('Missing search term');
        }

        $product_types = apply_filters('arsol_proposal_product_types', array('simple', 'variable', 'subscription', 'variation'));

        $args = array(
            'post_type' => 'product',
            'post_status' => 'publish',
            's' => $search_term,
            'posts_per_page' => 20,
            'tax_query' => array(
                array(
                    'taxonomy' => 'product_type',
                    'field'    => 'slug',
                    'terms'    => $product_types,
                ),
            ),
            'meta_query' => array(
                 array(
                    'key' => '_stock_status',
                    'value' => 'instock'
                )
            )
        );

        $query = new \WP_Query($args);
        $products = array();

        foreach ($query->posts as $post) {
            $product = wc_get_product($post->ID);
            if ($product) {
                 $products[] = array(
                    'id'   => $product->get_id(),
                    'text' => $product->get_formatted_name(),
                );
            }
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

        $data = array(
            'price' => wc_format_decimal($product->get_regular_price(), 2),
            'sale_price' => $product->get_sale_price() ? wc_format_decimal($product->get_sale_price(), 2) : '',
        );

        wp_send_json_success($data);
    }
}
