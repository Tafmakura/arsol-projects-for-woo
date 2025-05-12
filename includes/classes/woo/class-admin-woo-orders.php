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
        $order_id = $order->get_id();
        $selected_project = get_post_meta($order_id, '_assigned_project', true);

        $projects = get_posts([
            'post_type' => 'project',
            'numberposts' => -1
        ]);

        echo '<div class="form-field form-field-wide">';
        echo '<label for="project_selector">Project:</label>';
        echo '<select name="assigned_project" id="project_selector" class="wc-customer-search">';
        echo '<option value="">None</option>';
        foreach ($projects as $project) {
            $selected = ($selected_project == $project->ID) ? 'selected' : '';
            echo "<option value='{$project->ID}' $selected>{$project->post_title}</option>";
        }
        echo '</select>';
        echo '</div>';
    }
}
