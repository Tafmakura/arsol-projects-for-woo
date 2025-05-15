<?php

namespace Arsol_Projects_For_Woo\Woo;

if (!defined('ABSPATH')) {
    exit;
}

class AdminOrders {
    /**
     * Meta key used for storing project data
     */
    const PROJECT_META_KEY = '_wc_other/arsol-projects-for-woo/project';

    public function __construct() {
        // Initialize hooks
        add_action('init', array($this, 'init'));
        
        // Move project data column to appear after order details (general section)
        add_action('woocommerce_admin_order_data_after_order_details', array($this, 'add_project_data_column'));
        
        add_action('woocommerce_process_shop_order_meta', array($this, 'save_project_field'));
        
        // Add new column management
        add_filter('manage_edit-shop_order_columns', array($this, 'add_project_column'));
        add_action('manage_shop_order_posts_custom_column', array($this, 'display_project_column_content'), 10, 2);
        
        // Register the project checkout field on woocommerce_init
        add_action('woocommerce_init', array($this, 'register_project_checkout_field'));
        
        // Remove duplicate project field
        add_action('admin_enqueue_scripts', array($this, 'remove_duplicate_project_field'));

        // Add project information to order details table
        add_action('woocommerce_order_details_after_order_table', array($this, 'display_project_details'));

        // Add project to subscription details table (WooCommerce Subscriptions)
        add_action('woocommerce_subscription_details_after_subscription_table', array($this, 'display_project_details'));
    }

    public function init() {
        // Add your initialization code here
    }

    /**
     * Remove duplicate project field that WooCommerce Core automatically generates
     * 
     * WooCommerce renders registered checkout fields on the admin order edit page.
     * Since we're already adding our custom UI for the project field, we remove
     * the duplicate field that WooCommerce generates to prevent confusion.
     */
    public function remove_duplicate_project_field() {
        // Add script to remove duplicate fields
        add_action('admin_footer', function() {
            ?>
            <script type="text/javascript">
            jQuery(function($) {
                // Target both project field locations:
                // 1. The standard form field with the class name
                // 2. Any project fields in the order_data_column > address section
                var projectFields = $(
                    'p.form-field._wc_other\\/arsol-projects-for-woo\\/project_field'
                    // + ', div.order_data_column div.address p:contains("Project:")'
                );
                
                if (projectFields.length > 0) {
                    console.log('Removed ' + projectFields.length + ' duplicate project field(s)');
                    projectFields.remove();
                }
            });
            </script>
            <?php
        });
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
     * Get project ID from order using WooCommerce Blocks API
     *
     * @param \WC_Order $order The order object
     * @return int|string Project ID or empty string if not set
     */
    private function get_project_from_order($order) {
        // Legacy approach fallback
        $project_id = $order->get_meta(self::PROJECT_META_KEY);
        
        // Modern WooCommerce Blocks approach if available
        if (class_exists('Automattic\WooCommerce\Blocks\Package')) {
            try {
                $field_id = 'arsol-projects-for-woo/project';
                $checkout_fields = \Automattic\WooCommerce\Blocks\Package::container()->get(
                    \Automattic\WooCommerce\Blocks\Domain\Services\CheckoutFields::class
                );
                $field_value = $checkout_fields->get_field_from_object($field_id, $order, 'order');
                
                if (!empty($field_value)) {
                    $project_id = $field_value;
                }
            } catch (\Exception $e) {
                // Fallback to legacy approach already handled
            }
        }
        
        return $project_id;
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
     * Adds a project data column to the order details page
     *
     * @param \WC_Order $order The order object
     */
    public function add_project_data_column($order) {
        // Start the data column
        ?>
    
        <?php
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
                    
                    // Display order type info
                    ?>

                    
                    <p class="form-field form-field-wide">
                        <label><strong><?php esc_html_e('Parent project:', 'arsol-projects-for-woo'); ?></strong></label>
                        <span><?php echo esc_html($project_name); ?></span>
                    </p>
                    <?php
                }
            } else {
                // Parent order - show selector
                //$selected_project = $order->get_meta(self::PROJECT_META_KEY);
                $selected_project = $this->get_project_from_order($order);
               // echo 'red>>>>>>'.$selected_project;
                $projects = $this->get_projects();
                ?>
                <p class="form-field form-field-wide">
                    <label for="arsol_project_selector"><?php esc_html_e('Project:', 'arsol-projects-for-woo'); ?></label>
                    <select name="arsol_project" id="arsol_project_selector" class="wc-enhanced-select" style="width: 100%;">
                        <option value="none" <?php selected(empty($selected_project), true); ?>><?php esc_html_e('None', 'arsol-projects-for-woo'); ?></option>
                        <?php foreach ($projects as $project) : ?>
                            <option value="<?php echo esc_attr($project->ID); ?>" 
                                <?php selected((int)$selected_project, (int)$project->ID); ?>>
                                <?php echo esc_html($project->post_title); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </p>
                <?php
            }
        ?>

        <?php
    }

    /**
     * Save the selected project ID to order meta
     *
     * @param int $order_id The order ID
     */
    public function save_project_field($order_id) {
        if (isset($_POST['arsol_project'])) {
            $order = wc_get_order($order_id);
            if (!$order) {
                return;
            }
            
            $project_id = sanitize_text_field($_POST['arsol_project']);
            
            // Handle "none" value consistently with checkout field
            if ($project_id === 'none' || empty($project_id)) {
                $order->delete_meta_data(self::PROJECT_META_KEY);
            } else {
                // Verify this is a valid project before saving
                $project = get_post($project_id);
                if ($project && $project->post_type === 'project') {
                    // Cast to integer to match checkout format
                    $order->update_meta_data(self::PROJECT_META_KEY, (int)$project_id);
                }
            }
            
            $order->save();
        }
    }

    /**
     * Display project information in a consistent table format
     *
     * @param \WC_Order|\WC_Subscription $order The order or subscription object
     */
    public function display_project_details($order) {
        // Determine if this is a subscription
        $is_subscription = function_exists('wcs_is_subscription') && wcs_is_subscription($order);
        $project_id = '';
        $is_from_parent = false;
        $parent_info = false;
        
        // Get project based on context (same logic, cleaner implementation)
        if (!$this->is_parent_order($order)) {
            // Child order or subscription - get from parent
            if ($is_subscription) {
                $parent_order_id = $order->get_parent_id();
                if ($parent_order_id) {
                    $parent_order = wc_get_order($parent_order_id);
                    if ($parent_order) {
                        $project_id = $parent_order->get_meta(self::PROJECT_META_KEY);
                        $is_from_parent = true;
                    }
                } else {
                    $project_id = $order->get_meta(self::PROJECT_META_KEY);
                }
            } else {
                // Regular child order
                $parent_info = $this->get_parent_order_info($order);
                if ($parent_info && empty($parent_info['no_parent'])) {
                    $parent_order = wc_get_order($parent_info['id']);
                    $project_id = $parent_order ? $parent_order->get_meta(self::PROJECT_META_KEY) : '';
                    $is_from_parent = true;
                }
            }
        } else {
            // Parent order - get directly
            $project_id = $this->get_project_from_order($order);
        }
        
        // Format project information
        $project_name = __('None', 'arsol-projects-for-woo');
        $has_link = false;
        
        if (!empty($project_id) && $project_id !== 'none') {
            $project = get_post($project_id);
            if ($project) {
                $project_name = $project->post_title;
                $has_link = true;
            }
        }
        
        // Get parent order info for display if applicable
        if ($is_from_parent) {
            $parent_order_id = $is_subscription ? $order->get_parent_id() : $parent_info['id'];
            $parent_order_obj = wc_get_order($parent_order_id);
            $parent_order_number = $parent_order_obj ? $parent_order_obj->get_order_number() : $parent_order_id;
            $order_url = $parent_order_obj ? 
                $parent_order_obj->get_view_order_url() : 
                wc_get_endpoint_url('view-order', $parent_order_id, wc_get_page_permalink('myaccount'));
        }
        
        // Always use the same formatting regardless of context
        ?>
        <header>
            <h2><?php esc_html_e('Project Details', 'arsol-projects-for-woo'); ?></h2>
        </header>
        <table class="shop_table shop_table_responsive my_account_orders woocommerce-orders-table woocommerce-MyAccount-subscriptions woocommerce-orders-table--subscriptions projects-row">
            <thead>
                <tr>
                    <th class="project-name woocommerce-orders-table__header woocommerce-orders-table__header-project-name"><span class="nobr"><?php esc_html_e('Project', 'arsol-projects-for-woo'); ?></span></th>
                    <th class="project-actions order-actions woocommerce-orders-table__header woocommerce-orders-table__header-order-actions woocommerce-orders-table__header-project-actions">&nbsp;</th>
                </tr>
            </thead>
            <tbody>
                <tr class="woocommerce-orders-table__row <?php echo $has_link ? 'woocommerce-orders-table__row--status-active' : 'woocommerce-orders-table__row--status-inactive'; ?>">
                    <td class="woocommerce-orders-table__cell" data-title="<?php esc_attr_e('Project', 'arsol-projects-for-woo'); ?>">
                        <?php if ($has_link) : ?>
                            <a href="<?php echo esc_url(get_permalink($project_id)); ?>">
                                <?php echo esc_html($project_name); ?>
                            </a>
                        <?php else : ?>
                            <?php echo esc_html($project_name); ?>
                        <?php endif; ?>
                        
                        <?php if ($is_from_parent): ?>
                            <?php esc_html_e('From parent order', 'arsol-projects-for-woo'); ?> 
                            <a href="<?php echo esc_url($order_url); ?>">#<?php echo esc_html($parent_order_number); ?></a>          
                        <?php endif; ?>
                        </td>
                    <td class="woocommerce-orders-table__cell woocommerce-orders-table__cell-order-actions">
                        <?php if ($has_link) : ?>
                            <a href="<?php echo esc_url(get_permalink($project_id)); ?>" class="woocommerce-button button view">
                                <?php esc_html_e('View', 'arsol-projects-for-woo'); ?>
                            </a>
                        <?php endif; ?>
                    </td>
                </tr>
            </tbody>
        </table>
        <?php
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
                                $parent_order_number = $parent_order->get_order_number();
                                $parent_order_url = $parent_order->get_edit_order_url(); // Admin URL for editing
                                
                                echo '<a href="' . esc_url(get_edit_post_link($project_id)) . '">' . 
                                     esc_html($project->post_title) . '</a>' .
                                     '<br>(' . esc_html__('From parent order', 'arsol-projects-for-woo') . ' ' .
                                     '<a href="' . esc_url($parent_order_url) . '">#' . esc_html($parent_order_number) . '</a>)';
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

    public function register_project_checkout_field() {
        if (!is_user_logged_in()) {
            return;
        }

        $user_id = get_current_user_id();
        $user_projects = get_posts([
            'post_type'      => 'project',
            'post_status'    => 'publish',
            'author'         => $user_id,
            'posts_per_page' => -1,
            'orderby'        => 'title',
            'order'          => 'ASC',
        ]);

        // Add "None" option first with explicit "none" value
        $options = [
            [
                'value' => 'none',
                'label' => __('None', 'arsol-projects-for-woo')
            ]
        ];
        
        // Then add the project options
        foreach ($user_projects as $project) {
            $options[] = [
                'value' => $project->ID,
                'label' => $project->post_title
            ];
        }

        woocommerce_register_additional_checkout_field(
            array(
                'id'         => 'arsol-projects-for-woo/project',
                'label'      => __('Project', 'arsol-projects-for-woo'),
                'location'   => 'order',
                'required'   => true,
                'type'       => 'select',
                'placeholder' => __('Select a project', 'arsol-projects-for-woo'),
                'options'    => $options,
                'validate'   => function($value) use ($user_id) {
                    // Accept "none" as valid
                    if ($value === 'none') {
                        return true;
                    }
                    
                    // For other values, perform normal validation
                    if (empty($value)) {
                        return new \WP_Error('required_field', __('Please select a project.', 'arsol-projects-for-woo'));
                    }
        
                    $project = get_post($value);
                    if (!$project || $project->post_type !== 'project' || $project->post_author != $user_id) {
                        return new \WP_Error('invalid_project', __('Invalid project selected.', 'arsol-projects-for-woo'));
                    }
        
                    return true;
                },
                'save' => function($order, $value) {
                    // Delete the meta if "none" is selected
                    if ($value === 'none') {
                        $order->delete_meta_data(self::PROJECT_META_KEY);
                    } else {
                        // Cast to integer to match admin format
                        $order->update_meta_data(self::PROJECT_META_KEY, (int)$value);
                    }
                }
            )
        );
    }

}