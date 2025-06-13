<?php

namespace Arsol_Projects_For_Woo;

if (!defined('ABSPATH')) {
    exit;
}

class Frontend_Woocommerce_Checkout {

    public function __construct() {
        // Register the project checkout field when WordPress and WooCommerce are fully loaded
        add_action('wp_loaded', array($this, 'register_project_checkout_field'));
        
        // This hook saves the data from both classic and block checkouts.
        add_action('woocommerce_checkout_update_order_meta', array($this, 'save_project_from_checkout'), 10, 2);
    }

    /**
     * Check if the project field should be displayed based on cart contents
     * This only applies to regular orders, not pre-assigned orders from proposals
     *
     * @return bool
     */
    private function should_display_project_field() {
        $settings = get_option('arsol_projects_settings', array());
        $project_products = !empty($settings['project_products']) ? (array) $settings['project_products'] : array();
        $project_categories = !empty($settings['project_categories']) ? (array) $settings['project_categories'] : array();

        // If no specific products or categories are configured, don't show the field
        if (empty($project_products) && empty($project_categories)) {
            return false;
        }

        if (WC()->cart === null) {
            return false;
        }

        // Check if cart has project items and handle mixed cart behavior
        return $this->handle_mixed_cart_behavior($project_products, $project_categories);
    }

    /**
     * Handle mixed cart behavior based on settings
     *
     * @param array $project_products
     * @param array $project_categories
     * @return bool Whether to show project field
     */
    private function handle_mixed_cart_behavior($project_products, $project_categories) {
        $settings = get_option('arsol_projects_settings', array());
        $mixed_cart_behavior = isset($settings['mixed_cart_behavior']) ? $settings['mixed_cart_behavior'] : 'add_all';
        
        $project_items = array();
        $non_project_items = array();
        
        // Categorize cart items
        foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
            $product_id = $cart_item['product_id'];
            $is_project_item = false;

            // Check if the product is in the allowed list
            if (!empty($project_products) && in_array($product_id, $project_products)) {
                $is_project_item = true;
            }

            // Check if the product's categories are in the allowed list
            if (!$is_project_item && !empty($project_categories)) {
                $product_category_ids = wc_get_product_term_ids($product_id, 'product_cat');
                if (!empty(array_intersect($product_category_ids, $project_categories))) {
                    $is_project_item = true;
                }
            }

            if ($is_project_item) {
                $project_items[$cart_item_key] = $cart_item;
            } else {
                $non_project_items[$cart_item_key] = $cart_item;
            }
        }

        // If no project items, don't show field
        if (empty($project_items)) {
            return false;
        }

        // If only project items, show field
        if (empty($non_project_items)) {
            return true;
        }

        // Mixed cart - handle based on setting
        switch ($mixed_cart_behavior) {
            case 'add_all':
                // Add all items to project (current behavior)
                return true;

            case 'purge_non_project':
                // Remove non-project items from cart
                $this->remove_non_project_items($non_project_items);
                return true;

            default:
                return true;
        }
    }

    /**
     * Remove non-project items from cart
     *
     * @param array $non_project_items
     */
    private function remove_non_project_items($non_project_items) {
        foreach ($non_project_items as $cart_item_key => $cart_item) {
            WC()->cart->remove_cart_item($cart_item_key);
        }
        
        // Add notice to inform user
        if (!empty($non_project_items)) {
            $removed_count = count($non_project_items);
            $message = sprintf(
                _n(
                    '%d non-project item was removed from your cart.',
                    '%d non-project items were removed from your cart.',
                    $removed_count,
                    'arsol-pfw'
                ),
                $removed_count
            );
            wc_add_notice($message, 'notice');
        }
    }

    /**
     * Get all available projects for selection
     * 
     * @param int|null $user_id Optional. Filter projects by author ID
     * @return array Array of project post objects
     */
    private function get_projects($user_id = null) {
        $args = [
            'post_type' => 'arsol-project',
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
     * Check if there's a pre-assigned project from proposal orders
     * 
     * @return int|false Project ID if pre-assigned, false otherwise
     */
    private function get_pre_assigned_project() {
        // Check if there's a project ID stored in session (from proposal conversion)
        if (WC()->session) {
            $session_project = WC()->session->get('arsol_pre_assigned_project');
            if (!empty($session_project)) {
                return intval($session_project);
            }
        }
        
        // Check if any cart items have project metadata (for future use)
        if (WC()->cart) {
            foreach (WC()->cart->get_cart() as $cart_item) {
                if (!empty($cart_item['arsol_project_id'])) {
                    return intval($cart_item['arsol_project_id']);
                }
            }
        }
        
        return false;
    }

    public function register_project_checkout_field() {
        // Use new Blocks-compatible registration if available
        if (class_exists('Automattic\\WooCommerce\\Blocks\\Package')) {
            // Check if there's a pre-assigned project from cart/session
            $pre_assigned_project = $this->get_pre_assigned_project();
            
            // Show field if: pre-assigned project exists OR regular field should be displayed
            if ($pre_assigned_project || $this->should_display_project_field()) {
                try {
                    $settings = get_option('arsol_projects_settings', array());
                    $is_required = !empty($settings['require_project_selection']);

                    $field_id = 'arsol-projects-for-woo/arsol-project';
                    
                    $checkout_fields_controller = \Automattic\WooCommerce\Blocks\Package::container()->get(
                        \Automattic\WooCommerce\Blocks\Domain\Services\CheckoutFields::class
                    );
                    
                    if ($pre_assigned_project) {
                        // Show read-only project information instead of selector
                        $project = get_post($pre_assigned_project);
                        if ($project) {
                            $checkout_fields_controller->register_checkout_field(
                                array(
                                    'id' => $field_id,
                                    'type' => 'text',
                                    'label' => __('Project', 'arsol-pfw'),
                                    'location' => 'order',
                                    'required' => false,
                                    'attributes' => array(
                                        'readonly' => true,
                                        'disabled' => true,
                                    ),
                                    'default' => esc_html($project->post_title),
                                    'experimental_attributes' => array(),
                                )
                            );
                            
                            // Store the project ID in session for saving later
                            WC()->session->set('arsol_pre_assigned_project', $pre_assigned_project);
                        }
                    } else {
                        // Show normal project selector
                        $current_user_id = get_current_user_id();
                        $projects = $this->get_projects($current_user_id);

                        $options = array(
                            array(
                                'value' => '',
                                'label' => __('Select a project…', 'arsol-pfw'),
                            ),
                        );

                        foreach ($projects as $project) {
                            // Only show projects that the user can view
                            if (Woocommerce::user_can_view_project($current_user_id, $project->ID)) {
                                $options[] = array(
                                    'value' => (string) $project->ID,
                                    'label' => esc_html($project->post_title),
                                );
                            }
                        }
                        
                        $checkout_fields_controller->register_checkout_field(
                            array(
                                'id' => $field_id,
                                'type' => 'select',
                                'label' => __('Project', 'arsol-pfw'),
                                'location' => 'order',
                                'options' => $options,
                                'required' => $is_required,
                                'attributes' => array(),
                                'experimental_attributes' => array(),
                                'default' => '',
                            )
                        );
                    }
                } catch (\Exception $e) {
                    // Log error or handle gracefully
                }
            }
        } else {
            // Fallback for classic checkout
            add_action('woocommerce_after_order_notes', array($this, 'display_classic_project_field'));
        }
    }

    /**
     * Display the project field for classic checkout
     */
    public function display_classic_project_field($checkout) {
        // Check for pre-assigned project first
        $pre_assigned_project = $this->get_pre_assigned_project();
        
        // Show field if: pre-assigned project exists OR regular field should be displayed
        if (!$pre_assigned_project && !$this->should_display_project_field()) {
            return;
        }
        
        if ($pre_assigned_project) {
            // Show read-only project information
            $project = get_post($pre_assigned_project);
            if ($project) {
                echo '<div id="arsol-project-checkout-field">';
                echo '<p class="form-row form-row-wide">';
                echo '<label for="arsol_project_readonly"><strong>' . esc_html__('Project', 'arsol-pfw') . '</strong></label>';
                echo '<input type="text" id="arsol_project_readonly" value="' . esc_attr($project->post_title) . '" readonly disabled style="background-color: #f9f9f9; color: #666;" />';
                echo '<input type="hidden" name="arsol_project_id" value="' . esc_attr($pre_assigned_project) . '" />';
                echo '</p>';
                echo '</div>';
                
                // Store in session for saving
                WC()->session->set('arsol_pre_assigned_project', $pre_assigned_project);
            }
            return;
        }

        // Show normal project selector
        $settings = get_option('arsol_projects_settings', array());
        $is_required = !empty($settings['require_project_selection']);

        $current_user_id = get_current_user_id();
        $projects = $this->get_projects($current_user_id);

        $options = array();
        foreach ($projects as $project) {
            if (Woocommerce::user_can_view_project($current_user_id, $project->ID)) {
                $options[$project->ID] = esc_html($project->post_title);
            }
        }

        $field_args = array(
            'type'          => 'select',
            'class'         => array('form-row-wide'),
            'label'         => __('Project', 'arsol-pfw'),
            'required'      => $is_required,
            'placeholder'   => __('Select a project…', 'arsol-pfw'),
            'options'       => $options,
        );

        echo '<div id="arsol-project-checkout-field">';
        woocommerce_form_field(
            'arsol_project_id',
            $field_args,
            $checkout->get_value('arsol_project_id')
        );
        echo '</div>';
    }

    /**
     * Save project from checkout
     */
    public function save_project_from_checkout($order_id, $data) {
        $project_id = '';
        
        // First check for pre-assigned project from session (proposal orders)
        if (WC()->session) {
            $pre_assigned = WC()->session->get('arsol_pre_assigned_project');
            if (!empty($pre_assigned)) {
                $project_id = sanitize_text_field($pre_assigned);
                // Clear the session after use
                WC()->session->__unset('arsol_pre_assigned_project');
            }
        }
        
        // If no pre-assigned project, check for user selection
        if (empty($project_id)) {
            // For classic checkout
            if (isset($_POST['arsol_project_id'])) {
                $project_id = sanitize_text_field($_POST['arsol_project_id']);
            }
            // For block checkout
            elseif (isset($data['arsol-projects-for-woo/arsol-project'])) {
                $project_id = sanitize_text_field($data['arsol-projects-for-woo/arsol-project']);
            }
        }
        
        if (!empty($project_id)) {
            $order = wc_get_order($order_id);
            $order->update_meta_data(Woocommerce::PROJECT_META_KEY, $project_id);
            // The order->save() is called by WooCommerce after this hook.
        }
    }
} 