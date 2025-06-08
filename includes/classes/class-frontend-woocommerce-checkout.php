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
     *
     * @return bool
     */
    private function should_display_project_field() {
        $settings = get_option('arsol_projects_settings', array());
        $project_products = !empty($settings['project_products']) ? (array) $settings['project_products'] : array();
        $project_categories = !empty($settings['project_categories']) ? (array) $settings['project_categories'] : array();

        // If no specific products or categories are set, show the field by default
        if (empty($project_products) && empty($project_categories)) {
            return true;
        }

        if (WC()->cart === null) {
            return false;
        }

        foreach (WC()->cart->get_cart() as $cart_item) {
            $product_id = $cart_item['product_id'];

            // Check if the product is in the allowed list
            if (!empty($project_products) && in_array($product_id, $project_products)) {
                return true;
            }

            // Check if the product's categories are in the allowed list
            if (!empty($project_categories)) {
                $product_category_ids = wc_get_product_term_ids($product_id, 'product_cat');
                if (!empty(array_intersect($product_category_ids, $project_categories))) {
                    return true;
                }
            }
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

    public function register_project_checkout_field() {
        // Use new Blocks-compatible registration if available
        if (class_exists('Automattic\\WooCommerce\\Blocks\\Package')) {
            if ($this->should_display_project_field()) {
                try {
                    $settings = get_option('arsol_projects_settings', array());
                    $is_required = !empty($settings['require_project_selection']);

                    $field_id = 'arsol-projects-for-woo/arsol-project';
                    
                    $checkout_fields_controller = \Automattic\WooCommerce\Blocks\Package::container()->get(
                        \Automattic\WooCommerce\Blocks\Domain\Services\CheckoutFields::class
                    );

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
        if (!$this->should_display_project_field()) {
            return;
        }

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
        
        // For classic checkout
        if (isset($_POST['arsol_project_id'])) {
            $project_id = sanitize_text_field($_POST['arsol_project_id']);
        }
        // For block checkout
        elseif (isset($data['arsol-projects-for-woo/arsol-project'])) {
            $project_id = sanitize_text_field($data['arsol-projects-for-woo/arsol-project']);
        }
        
        if (!empty($project_id)) {
            $order = wc_get_order($order_id);
            $order->update_meta_data(Woocommerce::PROJECT_META_KEY, $project_id);
            // The order->save() is called by WooCommerce after this hook.
        }
    }
} 