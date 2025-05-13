<?php

namespace Arsol_Projects_For_Woo\Woo;

if (!defined('ABSPATH')) {
    exit;
}

class AdminOrders {
    /**
     * Meta key used for storing project data
     */
    const PROJECT_META_KEY = '_arsol_project';

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
     * Check if an order is a parent order
     *
     * @param \WC_Order $order The order object
     * @return bool Whether this is a parent order
     */
    private function is_parent_order($order) {
        // Check for renewal, switch, or resubscribe meta
        $is_renewal = $order->get_meta('_subscription_renewal');
        $is_switch = $order->get_meta('_subscription_switch');
        $is_resubscribe = $order->get_meta('_subscription_resubscribe');
        
        if (!empty($is_renewal) || !empty($is_switch) || !empty($is_resubscribe)) {
            return false;
        }
        
        // If WooCommerce Subscriptions is active, use its functions for additional checks
        if (function_exists('wcs_is_subscription')) {
            $order_id = $order->get_id();
            if (wcs_is_subscription($order_id)) {
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Get parent order information for a child order
     *
     * @param \WC_Order $order The order object
     * @return array|false Parent order data or false if not a child order
     */
    private function get_parent_order_info($order) {
        $is_renewal = $order->get_meta('_subscription_renewal');
        $is_switch = $order->get_meta('_subscription_switch');
        $is_resubscribe = $order->get_meta('_subscription_resubscribe');
        
        // Handle renewal, switch, resubscribe orders
        if (!empty($is_renewal)) {
            return [
                'id' => $is_renewal,
                'type' => __('Renewal Order', 'arsol-projects-for-woo')
            ];
        }
        
        if (!empty($is_switch)) {
            return [
                'id' => $is_switch,
                'type' => __('Switch Order', 'arsol-projects-for-woo')
            ];
        }
        
        if (!empty($is_resubscribe)) {
            return [
                'id' => $is_resubscribe,
                'type' => __('Resubscribe Order', 'arsol-projects-for-woo')
            ];
        }
        
        // Handle subscription objects
        if (function_exists('wcs_is_subscription') && wcs_is_subscription($order)) {
            // First try the standard meta
            $original_order_id = $order->get_meta('_original_order');
            
            // If empty, try alternative methods to find parent order
            if (empty($original_order_id) && function_exists('wcs_get_subscription')) {
                // Try to get the related orders
                if (method_exists($order, 'get_related_orders')) {
                    $related_orders = $order->get_related_orders('ids', 'parent');
                    if (!empty($related_orders)) {
                        $original_order_id = reset($related_orders); // Get first parent order
                    }
                }
                
                // If still empty and we have the subscription ID, try another approach
                if (empty($original_order_id)) {
                    $subscription_id = $order->get_id();
                    $subscription = wcs_get_subscription($subscription_id);
                    if ($subscription && method_exists($subscription, 'get_parent_id')) {
                        $original_order_id = $subscription->get_parent_id();
                    }
                }
            }
            
            if (!empty($original_order_id)) {
                return [
                    'id' => $original_order_id,
                    'type' => __('Subscription', 'arsol-projects-for-woo')
                ];
            }
            
            // If we still don't have a parent order, display a special message
            return [
                'id' => '',
                'type' => __('Subscription (no parent found)', 'arsol-projects-for-woo'),
                'no_parent' => true
            ];
        }
        
        return false;
    }

    /**
     * Get all available projects for selection
     * 
     * @param int|null $user_id Optional. Filter projects by author ID
     * @return array Array of project post objects
     */
    private function get_projects($user_id = null) {
        $args = [
            'post_type' => 'project',
            'numberposts' => -1,
            'orderby' => 'title',
            'order' => 'ASC'
        ];
        
        // Add author filtering if user ID provided
        if (!empty($user_id)) {
            $args['author'] = absint($user_id);
        }
        
        return get_posts($args);
    }

    /**
     * Adds a project selector dropdown to the order details page
     *
     * @param \WC_Order $order The order object
     */
    public function add_project_selector_to_order($order) {
        // Check if this is a parent order
        if (!$this->is_parent_order($order)) {
            // This is a child order - get parent order info
            $parent_info = $this->get_parent_order_info($order);
            if ($parent_info) {
                $project_name = __('None', 'arsol-projects-for-woo');
                $project_id = '';
                
                // Only try to get parent project if we have a parent order
                if (empty($parent_info['no_parent'])) {
                    // Get parent order
                    $parent_order = wc_get_order($parent_info['id']);
                    $project_id = $parent_order ? $parent_order->get_meta(self::PROJECT_META_KEY) : '';
                    
                    if (!empty($project_id)) {
                        $project = get_post($project_id);
                        if ($project) {
                            $project_name = $project->post_title;
                        }
                    }
                }
                
                // Display read-only information
                ?>
               
                <p class="form-field form-field-wide">
                    <label><?php esc_html_e('Order Type:', 'arsol-projects-for-woo'); ?></label>
                    <span><?php echo esc_html($parent_info['type']); ?></span>
                </p>
                <?php if (!empty($parent_info['id'])) : ?>
                    <p class="form-field form-field-wide">
                        <label><?php esc_html_e('Parent Order:', 'arsol-projects-for-woo'); ?></label>
                        <a href="<?php echo esc_url(admin_url('post.php?post=' . $parent_info['id'] . '&action=edit')); ?>">
                            #<?php echo esc_html($parent_info['id']); ?>
                        </a>
                    </p>
                <?php endif; ?>
                <p class="form-field form-field-wide">
                    <label><?php esc_html_e('Project:', 'arsol-projects-for-woo'); ?></label>
                    <span><?php echo esc_html($project_name); ?></span>
                </p>
        
                <?php
                return;
            }
        }
        
        // Parent order - show selector
        // Get the currently assigned project (if any)
        $selected_project = $order->get_meta(self::PROJECT_META_KEY);
        
        // Get all projects
        $projects = $this->get_projects();
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
            $order = wc_get_order($order_id);
            if (!$order) {
                return;
            }
            
            $project_id = sanitize_text_field($_POST['arsol_project']);
            
            // If empty, delete the meta
            if (empty($project_id)) {
                $order->delete_meta_data(self::PROJECT_META_KEY);
            } else {
                // Verify this is a valid project before saving
                $project = get_post($project_id);
                if ($project && $project->post_type === 'project') {
                    $order->update_meta_data(self::PROJECT_META_KEY, $project_id);
                }
            }
            
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
            
            // Check if this is a child order
            if (!$this->is_parent_order($order)) {
                $parent_info = $this->get_parent_order_info($order);
                if ($parent_info) {
                    $parent_order = wc_get_order($parent_info['id']);
                    if ($parent_order) {
                        $project_id = $parent_order->get_meta(self::PROJECT_META_KEY);
                        if (!empty($project_id)) {
                            $project = get_post($project_id);
                            if ($project) {
                                echo '<a href="' . esc_url(get_edit_post_link($project_id)) . '">' . 
                                     esc_html($project->post_title) . '</a>' .
                                     '<br><small>(' . esc_html__('From parent order', 'arsol-projects-for-woo') . ')</small>';
                                return;
                            }
                        }
                    }
                }
            }
            
            // For parent orders or if parent relationship not found
            $project_id = $order->get_meta(self::PROJECT_META_KEY);
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