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
        if (is_wp_error($current_status) || empty($current_status) || $current_status[0] !== 'under-review') {
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
        $this->safe_redirect(admin_url('post.php?post=' . $new_proposal_id . '&action=edit'));
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
            if ($is_internal_call) {
                $this->safe_redirect(wp_get_referer() ?: wc_get_account_endpoint_url('project-view-proposal/' . $proposal_id));
            } else {
                wp_die($new_project_id->get_error_message());
            }
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

        // Delete the original proposal
        wp_delete_post($proposal_id, true);

        // Clean the output buffer and redirect
        ob_end_clean();

        // Redirect based on how the function was called
        if ($is_internal_call) {
            $this->safe_redirect(wc_get_account_endpoint_url('project-overview/' . $new_project_id));
        } else {
            $this->safe_redirect(admin_url('post.php?post=' . $new_project_id . '&action=edit'));
        }
    }

    public function customer_cancel_request() {
        if (!isset($_GET['request_id']) || !wp_verify_nonce($_GET['_wpnonce'], 'arsol_cancel_request_nonce')) {
            wp_die(__('Invalid request or nonce.', 'arsol-pfw'));
        }

        $request_id = intval($_GET['request_id']);
        if (self::user_can_view_post(get_current_user_id(), $request_id)) {
            wp_delete_post($request_id, true); // Delete the request
            $this->safe_redirect(wc_get_account_endpoint_url('projects')); // Redirect to projects page
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
            $this->safe_redirect(wp_get_referer());
        } else {
            wp_die(__('You do not have permission to reject this proposal.', 'arsol-pfw'));
        }
    }

    public function handle_create_request() {
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
        $post_id = wp_insert_post($post_data);
        
        if (is_wp_error($post_id)) {
            $this->safe_redirect(wc_get_account_endpoint_url('project-create-request'));
        }

        wp_set_object_terms($post_id, 'pending', 'arsol-request-status');
        $this->update_request_meta($post_id, $_POST);

        
        $this->safe_redirect(wc_get_account_endpoint_url('project-view-request/' . $post_id));
    }

    public function handle_edit_request() {
        // Fixed namespace issues for WooCommerce functions
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
        $result = wp_update_post($post_data);



        $this->update_request_meta($post_id, $_POST);
        
        $this->safe_redirect(wc_get_account_endpoint_url('project-view-request/' . $post_id));
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
     * Safe redirect that handles "headers already sent" issues
     */
    private function safe_redirect($url) {
        if (headers_sent()) {
            // If headers are already sent, use JavaScript redirect
            echo '<script type="text/javascript">window.location.href="' . esc_url($url) . '";</script>';
            echo '<noscript><meta http-equiv="refresh" content="0;url=' . esc_url($url) . '" /></noscript>';
            exit;
        } else {
            // Use normal redirect
            wp_safe_redirect($url);
            exit;
        }
    }
}