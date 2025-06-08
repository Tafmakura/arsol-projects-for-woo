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

        // Customer actions
        add_action('admin_post_arsol_cancel_request', array($this, 'customer_cancel_request'));
        add_action('admin_post_arsol_approve_proposal', array($this, 'customer_approve_proposal'));
        add_action('admin_post_arsol_reject_proposal', array($this, 'customer_reject_proposal'));

        // Form submissions
        add_action('admin_post_arsol_create_request', array($this, 'handle_create_request'));
        add_action('admin_post_arsol_edit_request', array($this, 'handle_edit_request'));

        // AJAX handlers
        add_action('wp_ajax_arsol_handle_request_submission', array($this, 'handle_request_submission_ajax'));
    }

    /**
     * Check if a user can view a specific post (project, proposal, or request).
     *
     * @param int $user_id The ID of the user.
     * @param int $post_id The ID of the post.
     * @return bool True if the user can view the post, false otherwise.
     */
    public static function user_can_view_post($user_id, $post_id) {
        if (empty($user_id) || empty($post_id)) {
            return false;
        }
    
        $post = get_post($post_id);
    
        if (!$post) {
            return false;
        }
    
        // Check if the user is the author of the post
        if ((int) $post->post_author === (int) $user_id) {
            return true;
        }
    
        // Fallback to the general project management capability check
        return \Arsol_Projects_For_Woo\Admin\Admin_Capabilities::can_manage_projects($user_id);
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

        // Server-side validation of the request status
        $current_status = wp_get_object_terms($request_id, 'arsol-request-status', array('fields' => 'slugs'));
        if (empty($current_status) || $current_status[0] !== 'under-review') {
            wp_die(__('This request cannot be converted. The status must be "Under Review".', 'arsol-pfw'));
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

        // Copy relevant meta data from request to proposal, renaming keys as needed
        $meta_to_copy = array(
            '_request_budget'         => '_proposal_budget',
            '_request_start_date'     => '_proposal_start_date',
            '_request_delivery_date'  => '_proposal_delivery_date',
        );

        foreach ($meta_to_copy as $request_key => $proposal_key) {
            $value = get_post_meta($request_id, $request_key, true);
            if ($value) {
                // Copy to the main editable proposal field
                update_post_meta($new_proposal_id, $proposal_key, $value);
                // Also copy to a new field to preserve the original request data for display
                update_post_meta($new_proposal_id, '_original' . $request_key, $value);
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

    public function convert_proposal_to_project($proposal_id = 0) {
        ob_start();

        if (empty($proposal_id)) {
            if (!isset($_GET['proposal_id']) || !wp_verify_nonce($_GET['_wpnonce'], 'arsol_convert_to_project_nonce')) {
                wp_die(__('Invalid proposal or nonce.', 'arsol-pfw'));
            }
            $proposal_id = intval($_GET['proposal_id']);
        }

        $is_internal_call = did_action('admin_post_arsol_approve_proposal') > 0;

        $can_convert = self::user_can_view_post(get_current_user_id(), $proposal_id);
        if (!$is_internal_call) {
            $can_convert = current_user_can('publish_posts');
        }

        if (!$can_convert) {
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
            '_proposal_recurring_start_date' => '_project_recurring_start_date',
            '_proposal_start_date'       => '_proposal_start_date', // Keep for display
            '_proposal_delivery_date'    => '_project_due_date', // Correctly map to due date
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

        // Redirect based on how the function was called
        if ($is_internal_call) {
            wp_safe_redirect(wc_get_account_endpoint_url('projects'));
        } else {
            wp_redirect(admin_url('post.php?post=' . $new_project_id . '&action=edit'));
        }
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
            $budget_data = get_post_meta($project_id, '_project_budget', true);
            if (empty($product_id) || empty($budget_data) || !is_array($budget_data) || empty($budget_data['amount'])) {
                return;
            }
            $product = wc_get_product($product_id);
            if (!$product) {
                return;
            }
            $this->create_standard_order($project_id, $customer_id, $product, $budget_data);
        } else { // recurring
            if (!class_exists('WC_Subscriptions')) {
                return;
            }
            $product_id = isset($settings['proposal_recurring_invoice_product']) ? $settings['proposal_recurring_invoice_product'] : '';
            $budget_data = get_post_meta($project_id, '_project_recurring_budget', true);
            $billing_interval = get_post_meta($project_id, '_project_billing_interval', true);
            $billing_period = get_post_meta($project_id, '_project_billing_period', true);
            
            if (empty($product_id) || empty($budget_data) || !is_array($budget_data) || empty($budget_data['amount']) || empty($billing_interval) || empty($billing_period)) {
                return;
            }
            
            $product = wc_get_product($product_id);
            // It's good practice to check if the product is a subscription type
            if (!$product || !$product->is_type(array('subscription', 'subscription_variation'))) {
                return;
            }

            $this->create_subscription_order($project_id, $customer_id, $product, $budget_data, $billing_interval, $billing_period);
        }
    }

    private function create_standard_order($project_id, $customer_id, $product, $budget_data) {
        try {
            $order = wc_create_order(array(
                'customer_id' => $customer_id,
                'status' => 'pending',
                'currency' => $budget_data['currency']
            ));

            if (is_wp_error($order)) {
                return;
            }

            $order->add_product($product, 1, array('total' => $budget_data['amount']));
            $order->calculate_totals();
            $order->save();
            
            update_post_meta($project_id, '_standard_invoice_created', 'yes');
            update_post_meta($project_id, '_standard_order_id', $order->get_id());

        } catch (\Exception $e) {
            // silent fail
        }
    }

    private function create_subscription_order($project_id, $customer_id, $product, $budget_data, $billing_interval, $billing_period) {
        if (!function_exists('wcs_create_subscription')) {
            return;
        }
        
        try {
            // Prioritize the specific recurring start date
            $recurring_start_date = get_post_meta($project_id, '_project_recurring_start_date', true);

            if (!empty($recurring_start_date)) {
                $subscription_start_date = gmdate('Y-m-d H:i:s', strtotime($recurring_start_date));
            } else {
                // Fallback to overall project start date or current time
                $start_date = get_post_meta($project_id, '_project_start_date', true);
                $subscription_start_date = !empty($start_date) ? gmdate('Y-m-d H:i:s', strtotime($start_date)) : gmdate('Y-m-d H:i:s');
            }

            $subscription_args = array(
                'status' => 'pending',
                'customer_id' => $customer_id,
                'billing_period' => $billing_period,
                'billing_interval' => $billing_interval,
                'start_date' => $subscription_start_date,
                'currency' => $budget_data['currency']
            );

            $subscription = wcs_create_subscription($subscription_args);

            if (is_wp_error($subscription)) {
                return;
            }

            $subscription->add_product($product, 1, array('subtotal' => $budget_data['amount'], 'total' => $budget_data['amount']));
            
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

    public function customer_cancel_request() {
        if (!isset($_GET['request_id']) || !wp_verify_nonce($_GET['_wpnonce'], 'arsol_cancel_request_nonce')) {
            wp_die(__('Invalid request or nonce.', 'arsol-pfw'));
        }

        $request_id = intval($_GET['request_id']);
        if (self::user_can_view_post(get_current_user_id(), $request_id)) {
            wp_delete_post($request_id, true); // Delete the request
            wp_safe_redirect(wc_get_account_endpoint_url('projects')); // Redirect to projects page
            exit;
        } else {
            wp_die(__('You do not have permission to cancel this request.', 'arsol-pfw'));
        }
    }

    public function customer_approve_proposal() {
        if (!isset($_GET['proposal_id']) || !wp_verify_nonce($_GET['_wpnonce'], 'arsol_approve_proposal_nonce')) {
            wp_die(__('Invalid proposal or nonce.', 'arsol-pfw'));
        }

        $proposal_id = intval($_GET['proposal_id']);
        if (self::user_can_view_post(get_current_user_id(), $proposal_id)) {
            // Set the proposal status to 'approved' before conversion
            wp_set_object_terms($proposal_id, 'approved', 'arsol-review-status');
            
            // Re-use the conversion logic
            $this->convert_proposal_to_project($proposal_id);
        } else {
            wp_die(__('You do not have permission to approve this proposal.', 'arsol-pfw'));
        }
    }

    public function customer_reject_proposal() {
        if (!isset($_GET['proposal_id']) || !wp_verify_nonce($_GET['_wpnonce'], 'arsol_reject_proposal_nonce')) {
            wp_die(__('Invalid proposal or nonce.', 'arsol-pfw'));
        }

        $proposal_id = intval($_GET['proposal_id']);
        if (self::user_can_view_post(get_current_user_id(), $proposal_id)) {
            wp_set_object_terms($proposal_id, 'rejected', 'arsol-review-status');
            wp_safe_redirect(wp_get_referer());
            exit;
        } else {
            wp_die(__('You do not have permission to reject this proposal.', 'arsol-pfw'));
        }
    }

    public function handle_create_request() {
        ob_start();
        if (!wp_verify_nonce($_POST['arsol_request_nonce'], 'arsol_create_request')) {
            wp_die(__('Invalid nonce.', 'arsol-pfw'));
        }

        $post_data = array(
            'post_title'   => sanitize_text_field($_POST['request_title']),
            'post_content' => wp_kses_post($_POST['request_description']),
            'post_status'  => 'publish',
            'post_type'    => 'arsol-pfw-request',
            'post_author'  => get_current_user_id(),
        );
        $post_id = wp_insert_post($post_data, true);

        if (is_wp_error($post_id)) {
            $redirect_url = wc_get_account_endpoint_url('project-request');
        } else {
            wp_set_object_terms($post_id, 'pending', 'arsol-request-status');
            $this->update_request_meta($post_id, $_POST);
            set_transient('arsol_pfw_request_submitted_' . get_current_user_id(), $post_id, 60);
            // Manually construct the URL for robustness
            $redirect_url = trailingslashit(wc_get_account_endpoint_url('project-view-request')) . $post_id . '/';
        }

        ob_end_clean();
        // Use a JS redirect for robustness
        echo '<script>window.location.href = "' . esc_url_raw($redirect_url) . '";</script>';
        exit;
    }

    public function handle_edit_request() {
        ob_start();
        if (!wp_verify_nonce($_POST['arsol_request_nonce'], 'arsol_edit_request')) {
            wp_die(__('Invalid nonce.', 'arsol-pfw'));
        }

        $post_id = intval($_POST['request_id']);

        // Verify user has permission to edit
        if (!self::user_can_view_post(get_current_user_id(), $post_id)) {
            wp_die(__('You do not have permission to edit this request.', 'arsol-pfw'));
        }

        $post_data = array(
            'ID'           => $post_id,
            'post_title'   => sanitize_text_field($_POST['request_title']),
            'post_content' => wp_kses_post($_POST['request_description']),
        );
        $result = wp_update_post($post_data, true);

        if (!is_wp_error($result)) {
            $this->update_request_meta($post_id, $_POST);
        }
        
        // Manually construct the URL for robustness
        $redirect_url = trailingslashit(wc_get_account_endpoint_url('project-view-request')) . $post_id . '/';
        ob_end_clean();
        // Use a JS redirect for robustness
        echo '<script>window.location.href = "' . esc_url_raw($redirect_url) . '";</script>';
        exit;
    }

    private function update_request_meta($post_id, $data) {
        if (isset($data['request_budget'])) {
            $amount = wc_clean(wp_unslash($data['request_budget']));
            $currency = get_woocommerce_currency();
            update_post_meta($post_id, '_request_budget', ['amount' => $amount, 'currency' => $currency]);
        }
        if (isset($data['request_start_date'])) {
            update_post_meta($post_id, '_request_start_date', sanitize_text_field($data['request_start_date']));
        }
        if (isset($data['request_delivery_date'])) {
            update_post_meta($post_id, '_request_delivery_date', sanitize_text_field($data['request_delivery_date']));
        }
    }

    /**
     * Handle request submission via AJAX
     */
    public function handle_request_submission_ajax() {
        // Verify nonce
        if (!check_ajax_referer('arsol_create_request', 'arsol_request_nonce', false)) {
            wp_send_json_error(array('notice' => __('Security check failed. Please refresh the page and try again.', 'arsol-pfw')));
        }

        $is_edit = isset($_POST['request_id']);
        $form_action = $is_edit ? 'arsol_edit_request' : 'arsol_create_request';

        if ($is_edit) {
            $post_id = intval($_POST['request_id']);
            if (!self::user_can_view_post(get_current_user_id(), $post_id)) {
                wp_send_json_error(array('notice' => __('You do not have permission to edit this request.', 'arsol-pfw')));
            }

            $post_data = array(
                'ID'           => $post_id,
                'post_title'   => sanitize_text_field($_POST['request_title']),
                'post_content' => wp_kses_post($_POST['request_description']),
            );
            $result = wp_update_post($post_data, true);

            if (is_wp_error($result)) {
                wp_send_json_error(array('notice' => __('There was an error updating your request. Please try again.', 'arsol-pfw')));
            }

            $this->update_request_meta($post_id, $_POST);
            wp_send_json_success(array('notice' => __('Request updated successfully.', 'arsol-pfw')));
        } else {
            $post_data = array(
                'post_title'   => sanitize_text_field($_POST['request_title']),
                'post_content' => wp_kses_post($_POST['request_description']),
                'post_status'  => 'publish',
                'post_type'    => 'arsol-pfw-request',
                'post_author'  => get_current_user_id(),
            );
            $post_id = wp_insert_post($post_data, true);

            if (is_wp_error($post_id)) {
                wp_send_json_error(array('notice' => __('There was an error creating your request. Please try again.', 'arsol-pfw')));
            }

            wp_set_object_terms($post_id, 'pending', 'arsol-request-status');
            $this->update_request_meta($post_id, $_POST);

            // Set a transient to indicate a new submission
            set_transient('arsol_pfw_request_submitted_' . get_current_user_id(), $post_id, 60);

            wp_send_json_success(array(
                'notice' => __('Request submitted successfully.', 'arsol-pfw'),
                'redirect_url' => wc_get_account_endpoint_url('project-view-request', $post_id)
            ));
        }
    }
}