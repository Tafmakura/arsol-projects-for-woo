<?php

namespace Arsol_Projects_For_Woo\Woo;

if (!defined('ABSPATH')) {
    exit;
}

class AdminOrders {
    /**
     * Meta key used for storing project data
     */
    const PROJECT_META_KEY = 'arsol_project';

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
        // Get the order ID
        $order_id = $order->get_id();
        
        // Get the currently assigned project (if any)
        $selected_project = get_post_meta($order_id, self::PROJECT_META_KEY, true);
        
        // Get all projects
        $projects = get_posts([
            'post_type' => 'project',
            'numberposts' => -1,
            'orderby' => 'title',
            'order' => 'ASC'
        ]);
        ?>
        <div class="options_group">
            <p class="form-field form-field-wide">
                <label for="arsol_project_selector"><?php esc_html_e('Project:', 'arsol-projects-for-woo'); ?></label>
                <select name="arsol_project" id="arsol_project_selector" class="wc-enhanced-select" style="width: 100%;">
                    <option value=""><?php esc_html_e('None', 'arsol-projects-for-woo'); ?></option>
                    <?php foreach ($projects as $project) : ?>
                        <option value="<?php echo esc_attr($project->ID); ?>" 
                            <?php selected($selected_project, $project->ID); ?>>
                            <?php echo esc_html($project->post_title); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </p>
        </div>
        <?php
    }

    /**
     * Save the selected project ID to order meta
     *
     * @param int $order_id The order ID
     */
    public function save_project_field($order_id) {
        // Check if our custom field is set
        if (isset($_POST['arsol_project'])) {
            $project_id = sanitize_text_field($_POST['arsol_project']);
            
            // If empty, delete the meta
            if (empty($project_id)) {
                delete_post_meta($order_id, self::PROJECT_META_KEY);
            } else {
                // Verify this is a valid project before saving
                $project = get_post($project_id);
                if ($project && $project->post_type === 'project') {
                    update_post_meta($order_id, self::PROJECT_META_KEY, $project_id);
                }
            }
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
            $project_id = get_post_meta($order_id, self::PROJECT_META_KEY, true);
            
            if (!empty($project_id)) {
                $project = get_post($project_id);
                if ($project) {
                    echo '<a href="' . esc_url(get_edit_post_link($project_id)) . '">' . esc_html($project->post_title) . '</a>';
                }
            } else {
                echo '—';
            }
        }
    }
}