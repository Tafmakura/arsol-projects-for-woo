<?php

namespace Arsol_Projects_For_Woo\Woo;

if (!defined('ABSPATH')) {
    exit;
}

class AdminOrders {
    public function __construct() {
        // Initialize hooks
        add_action('init', array($this, 'init'));
        add_action('woocommerce_admin_order_data_after_order_details', array($this, 'add_project_selector_to_order'));
        add_action('woocommerce_process_shop_order_meta', array($this, 'save_project_field'));
        
        // Add new column management
        add_filter('manage_edit-shop_order_columns', array($this, 'add_project_column'));
        add_action('manage_shop_order_posts_custom_column', array($this, 'display_project_column_content'), 10, 2);
    }

    public function init() {
        // Add your initialization code here
    }

    /**
     * Adds a project selector dropdown to the order details page
     *
     * @param \WC_Order $order The order object
     */
    public function add_project_selector_to_order($order) {
        $selected_project = $order->get_meta('arsol_project', true);
        $current_project_title = '—';
        
        if ($selected_project) {
            $project = get_post($selected_project);
            if ($project) {
                $current_project_title = $project->post_title;
            }
        }

        wp_nonce_field('arsol_save_project_data', 'arsol_project_nonce');

        ?>
        <div class="order_data_column">
            <h3>
                <?php esc_html_e('Project', 'arsol-projects-for-woo'); ?>
                <a href="#" class="edit_address"><?php esc_html_e('Edit', 'woocommerce'); ?></a>
            </h3>
            <div class="address">
                <p><?php echo esc_html($current_project_title); ?></p>
            </div>
            <div class="edit_address">
                <?php
                $projects = get_posts([
                    'post_type' => 'project',
                    'numberposts' => -1
                ]);
                ?>
                <p class="form-field">
                    <select name="assigned_project" id="project_selector" class="wc-enhanced-select">
                        <option value=""><?php esc_html_e('None', 'arsol-projects-for-woo'); ?></option>
                        <?php foreach ($projects as $project) : ?>
                            <option value="<?php echo esc_attr($project->ID); ?>" <?php selected($selected_project, $project->ID); ?>>
                                <?php echo esc_html($project->post_title); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </p>
            </div>
        </div>
        <?php
    }

    /**
     * Save the selected project ID to order meta
     *
     * @param int $order_id The order ID
     */
    public function save_project_field($order_id) {
        // Verify nonce
        if (!isset($_POST['arsol_project_nonce']) || !wp_verify_nonce($_POST['arsol_project_nonce'], 'arsol_save_project_data')) {
            return;
        }

        $order = wc_get_order($order_id);
        if (!$order) {
            return;
        }

        if (isset($_POST['assigned_project'])) {
            $project_id = sanitize_text_field($_POST['assigned_project']);
            $order->update_meta_data('arsol_project', $project_id);
            $order->save();
        }
    }

    /**
     * Add a custom column to the orders list
     *
     * @param array $columns Existing columns
     * @return array Modified columns
     */
    public function add_project_column($columns) {
        $new_columns = array();
        foreach ($columns as $key => $column) {
            $new_columns[$key] = $column;
            if ($key === 'order_status') {
                $new_columns['project'] = __('Project', 'arsol-projects-for-woo');
            }
        }
        return $new_columns;
    }

    /**
     * Display content for the custom project column
     *
     * @param string $column Column name
     * @param int $order_id Order ID
     */
    public function display_project_column_content($column, $order_id) {
        if ($column === 'project') {
            $order = wc_get_order($order_id);
            if (!$order) {
                return;
            }
            
            $project_id = $order->get_meta('arsol_project', true);
            if ($project_id) {
                $project = get_post($project_id);
                if ($project) {
                    echo esc_html($project->post_title);
                }
            } else {
                echo '—';
            }
        }
    }
}
