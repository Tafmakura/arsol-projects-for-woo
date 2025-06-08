<?php

namespace Arsol_Projects_For_Woo\Workflow;

if (!defined('ABSPATH')) {
    exit;
}

class Workflow_Handler {
    public function __construct() {
        // Actions for converting request to proposal
        add_action('admin_post_arsol_convert_to_proposal', array($this, 'convert_request_to_proposal'));

        // Actions for converting proposal to project
        add_action('admin_post_arsol_convert_to_project', array($this, 'convert_proposal_to_project'));

        // Action to set review status when a proposal is published
        add_action('transition_post_status', array($this, 'set_proposal_review_status'), 10, 3);
    }

    public function set_proposal_review_status($new_status, $old_status, $post) {
        if ($post->post_type === 'arsol-pfw-proposal' && $new_status === 'publish' && $old_status !== 'publish') {
            // Set the review status to 'under-review'
            wp_set_object_terms($post->ID, 'under-review', 'arsol-review-status');
        }
    }

    public function convert_request_to_proposal() {
        ob_start();

        if (!isset($_GET['request_id']) || !wp_verify_nonce($_GET['_wpnonce'], 'arsol_convert_to_proposal_nonce')) {
            wp_die(__('Invalid request or nonce.', 'arsol-pfw'));
        }

        $request_id = intval($_GET['request_id']);

        if (!current_user_can('edit_post', $request_id) || !current_user_can('publish_posts')) {
            wp_die(__('You do not have sufficient permissions to perform this action.', 'arsol-pfw'));
        }

        $request_post = get_post($request_id);

        if (!$request_post || $request_post->post_type !== 'arsol-pfw-request') {
            wp_die(__('Invalid request.', 'arsol-pfw'));
        }

        $proposal_args = array(
            'post_title'   => $request_post->post_title,
            'post_content' => $request_post->post_content,
            'post_status'  => 'draft',
            'post_type'    => 'arsol-pfw-proposal',
            'post_author'  => $request_post->post_author,
        );

        $new_proposal_id = wp_insert_post($proposal_args);

        if (is_wp_error($new_proposal_id)) {
            wp_die($new_proposal_id->get_error_message());
        }

        // Copy all meta data from request to proposal
        $request_meta = get_post_meta($request_id);
        foreach ($request_meta as $meta_key => $meta_values) {
            foreach ($meta_values as $meta_value) {
                update_post_meta($new_proposal_id, $meta_key, $meta_value);
            }
        }

        // Delete the original request
        wp_delete_post($request_id, true);

        // Clean the output buffer and redirect
        ob_end_clean();

        // Redirect to the new proposal's edit screen
        wp_redirect(admin_url('post.php?post=' . $new_proposal_id . '&action=edit'));
        exit;
    }

    public function convert_proposal_to_project() {
        ob_start();

        if (!isset($_GET['proposal_id']) || !wp_verify_nonce($_GET['_wpnonce'], 'arsol_convert_to_project_nonce')) {
            wp_die(__('Invalid proposal or nonce.', 'arsol-pfw'));
        }

        $proposal_id = intval($_GET['proposal_id']);

        if (!current_user_can('edit_post', $proposal_id) || !current_user_can('publish_posts')) {
            wp_die(__('You do not have sufficient permissions to perform this action.', 'arsol-pfw'));
        }

        $proposal_post = get_post($proposal_id);

        if (!$proposal_post || $proposal_post->post_type !== 'arsol-pfw-proposal') {
            wp_die(__('Invalid proposal.', 'arsol-pfw'));
        }

        if ($proposal_post->post_status !== 'publish') {
            wp_die(__('Only published proposals can be converted to projects.', 'arsol-pfw'));
        }

        $project_args = array(
            'post_title'   => $proposal_post->post_title,
            'post_content' => $proposal_post->post_content,
            'post_status'  => 'publish', // Projects are published immediately
            'post_type'    => 'arsol-project',
            'post_author'  => $proposal_post->post_author,
        );

        $new_project_id = wp_insert_post($project_args);

        if (is_wp_error($new_project_id)) {
            wp_die($new_project_id->get_error_message());
        }

        // Set the project status to 'not-started'
        wp_set_object_terms($new_project_id, 'not-started', 'arsol-project-status');

        // Copy and rename relevant meta data from proposal to project
        $meta_to_copy = array(
            '_proposal_budget'           => '_project_budget',
            '_proposal_recurring_budget' => '_project_recurring_budget',
            '_proposal_billing_interval' => '_project_billing_interval',
            '_proposal_billing_period'   => '_project_billing_period',
            '_proposal_start_date'       => '_project_start_date',
            '_proposal_delivery_date'    => '_project_delivery_date',
        );

        foreach ($meta_to_copy as $proposal_key => $project_key) {
            $value = get_post_meta($proposal_id, $proposal_key, true);
            if ($value) {
                update_post_meta($new_project_id, $project_key, $value);
            }
        }

        // Create invoices if checked on the original proposal
        if (get_post_meta($proposal_id, '_create_invoice_checked', true)) {
            $this->create_invoice_from_project($new_project_id, 'standard');
        }
        if (get_post_meta($proposal_id, '_create_recurring_invoice_checked', true)) {
            $this->create_invoice_from_project($new_project_id, 'recurring');
        }

        // Delete the original proposal
        wp_delete_post($proposal_id, true);

        // Clean the output buffer and redirect
        ob_end_clean();

        // Redirect to the new project's edit screen
        wp_redirect(admin_url('post.php?post=' . $new_project_id . '&action=edit'));
        exit;
    }

    private function create_invoice_from_project($project_id, $type = 'standard') {
        if (!class_exists('WooCommerce') || get_post_meta($project_id, '_' . $type . '_invoice_created', true) === 'yes') {
            return;
        }

        $project = get_post($project_id);
        $settings = get_option('arsol_projects_settings', array());
        $customer_id = $project->post_author;

        if ($type === 'standard') {
            $product_id = isset($settings['proposal_invoice_product']) ? $settings['proposal_invoice_product'] : '';
            $budget = get_post_meta($project_id, '_project_budget', true);
            if (empty($product_id) || empty($budget) || !is_numeric($budget)) {
                return;
            }
            $product = wc_get_product($product_id);
            if (!$product) {
                return;
            }
            $this->create_standard_order($project_id, $customer_id, $product, $budget);
        } else { // recurring
            if (!class_exists('WC_Subscriptions')) {
                return;
            }
            $product_id = isset($settings['proposal_recurring_invoice_product']) ? $settings['proposal_recurring_invoice_product'] : '';
            $budget = get_post_meta($project_id, '_project_recurring_budget', true);
            $billing_interval = get_post_meta($project_id, '_project_billing_interval', true);
            $billing_period = get_post_meta($project_id, '_project_billing_period', true);
            
            if (empty($product_id) || empty($budget) || !is_numeric($budget) || empty($billing_interval) || empty($billing_period)) {
                return;
            }
            
            $product = wc_get_product($product_id);
            // It's good practice to check if the product is a subscription type
            if (!$product || !$product->is_type(array('subscription', 'subscription_variation'))) {
                return;
            }

            $this->create_subscription_order($project_id, $customer_id, $product, $budget, $billing_interval, $billing_period);
        }
    }

    private function create_standard_order($project_id, $customer_id, $product, $budget) {
        try {
            $order = wc_create_order(array(
                'customer_id' => $customer_id,
                'status' => 'pending'
            ));

            if (is_wp_error($order)) {
                return;
            }

            $order->add_product($product, 1, array('total' => $budget));
            $order->calculate_totals();
            $order->save();
            
            update_post_meta($project_id, '_standard_invoice_created', 'yes');
            update_post_meta($project_id, '_standard_order_id', $order->get_id());

        } catch (\Exception $e) {
            // silent fail
        }
    }

    private function create_subscription_order($project_id, $customer_id, $product, $budget, $billing_interval, $billing_period) {
        if (!function_exists('wcs_create_subscription')) {
            return;
        }
        
        try {
            $start_date = get_post_meta($project_id, '_project_start_date', true);
            $subscription_start_date = !empty($start_date) ? gmdate('Y-m-d H:i:s', strtotime($start_date)) : gmdate('Y-m-d H:i:s');

            $subscription_args = array(
                'status' => 'pending',
                'customer_id' => $customer_id,
                'billing_period' => $billing_period,
                'billing_interval' => $billing_interval,
                'start_date' => $subscription_start_date,
            );

            $subscription = wcs_create_subscription($subscription_args);

            if (is_wp_error($subscription)) {
                return;
            }

            $subscription->add_product($product, 1, array('subtotal' => $budget, 'total' => $budget));
            
            // Link project to subscription
            update_post_meta($subscription->get_id(), '_arsol_project_id', $project_id);
            
            $subscription->calculate_totals();
            $subscription->save();

            update_post_meta($project_id, '_recurring_invoice_created', 'yes');
            update_post_meta($project_id, '_recurring_order_id', $subscription->get_id());

        } catch (\Exception $e) {
            // silent fail
        }
    }
}