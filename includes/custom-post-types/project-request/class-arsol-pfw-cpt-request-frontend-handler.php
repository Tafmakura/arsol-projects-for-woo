<?php
/**
 * Project Request Frontend Handler Class
 *
 * Handles frontend project request submissions.
 *
 * @package Arsol_Projects_For_Woo
 * @since 1.0.0
 */

namespace Arsol_Projects_For_Woo\Custom_Post_Types\ProjectRequest;

if (!defined('ABSPATH')) {
    exit;
}

class Frontend_Handler {
    /**
     * Constructor
     */
    public function __construct() {
        add_action('template_redirect', array($this, 'handle_create_request_submission'));
        add_filter('the_content', array($this, 'display_request_content'));
    }

    /**
     * Display request content with status messages
     */
    public function display_request_content($content) {
        if (!is_singular('arsol-pfw-request') || !in_the_loop() || !is_main_query()) {
            return $content;
        }

        global $post;
        $current_user_id = get_current_user_id();
        $post_author_id = $post->post_author;

        // Check if current user is the author
        if ($current_user_id !== $post_author_id) {
            return '<div class="arsol-pfw-error"><p>You do not have permission to view this request.</p></div>';
        }

        // Get request status
        $status_terms = get_the_terms($post->ID, 'arsol-pfw-request-stage');
        $status = $status_terms && !is_wp_error($status_terms) ? $status_terms[0]->slug : 'pending-review';

        // Display status-specific messages in content section
        $status_message = '';
        switch ($status) {
            case 'pending-review':
                $status_message = '<div class="arsol-pfw-notice arsol-pfw-notice-info">
                    <p><strong>Status:</strong> Your request is pending review. We will review it shortly and get back to you.</p>
                </div>';
                break;
                
            case 'under-review':
                $status_message = '<div class="arsol-pfw-notice arsol-pfw-notice-warning">
                    <p><strong>Status:</strong> Your request is currently under review. We are evaluating the details and will update you soon.</p>
                </div>';
                break;
                
            case 'on-hold':
                $status_message = '<div class="arsol-pfw-notice arsol-pfw-notice-warning">
                    <p><strong>Status:</strong> Your request is currently on hold. We may need additional information or are waiting for resources to become available.</p>
                </div>';
                break;
                
            case 'approved':
                $status_message = '<div class="arsol-pfw-notice arsol-pfw-notice-success">
                    <p><strong>Status:</strong> Great news! Your request has been approved and a proposal will be created for you shortly.</p>
                </div>';
                break;
        }

        // Check if user can proceed (only for approved status)
        $can_proceed = ($status === 'approved');
        
        if (!$can_proceed && $status !== 'approved') {
            $status_message .= '<div class="arsol-pfw-notice arsol-pfw-notice-info">
                <p><strong>Cannot Proceed:</strong> You cannot proceed to the next step until your request has been approved.</p>
            </div>';
        }

        // Build content with status messages
        $request_content = $status_message;
        $request_content .= '<div class="arsol-pfw-request-details">';
        $request_content .= $content;
        $request_content .= '</div>';

        return $request_content;
    }

    /**
     * Handle create request form submission
     */
    public function handle_create_request_submission() {
        if ('POST' !== $_SERVER['REQUEST_METHOD'] || !isset($_POST['create_request_nonce']) || !wp_verify_nonce($_POST['create_request_nonce'], 'create_request')) {
            return;
        }

        // Check if user can create project requests
        $user_id = get_current_user_id();
        $can_create = \Arsol_Projects_For_Woo\Admin\Admin_Capabilities::can_create_project_requests($user_id);

        if (!$can_create) {
            wc_add_notice(__('You do not have permission to create project requests. Please contact the administrator if you believe this is an error.', 'arsol-pfw'), 'error');
            wp_safe_redirect(wc_get_account_endpoint_url('projects'));
            exit;
        }
        
        $title = sanitize_text_field($_POST['request_title']);
        $description = wp_kses_post($_POST['request_description']);
        $budget = isset($_POST['request_budget']) ? sanitize_text_field($_POST['request_budget']) : '';
        $start_date = isset($_POST['request_start_date']) ? sanitize_text_field($_POST['request_start_date']) : '';
        $delivery_date = isset($_POST['request_delivery_date']) ? sanitize_text_field($_POST['request_delivery_date']) : '';
        
        // Create project request post
        $request_data = array(
            'post_title'    => $title,
            'post_content'  => $description,
            'post_status'   => 'publish',
            'post_type'     => 'arsol-pfw-request',
            'post_author'   => $user_id
        );
        
        $request_id = wp_insert_post($request_data);
        
        if (!is_wp_error($request_id)) {
            // Set default request status to 'pending-review'
            wp_set_object_terms($request_id, 'pending-review', 'arsol-pfw-request-stage');
            
            // Get request stage
            $stage_terms = get_the_terms($request_id, 'arsol-pfw-request-stage');

            // Set default request stage to 'pending-review'
            wp_set_object_terms($request_id, 'pending-review', 'arsol-pfw-request-stage');
            
            // Save additional request meta
            if (!empty($budget)) {
                // Remove formatting from budget amount (commas, etc.)
                $amount = \Arsol_Projects_For_Woo\Woocommerce::clean_amount_input($budget);
                $currency = get_woocommerce_currency();
                
                if (!empty($amount)) {
                    $budget_data = array(
                        'amount'   => $amount,
                        'currency' => $currency
                    );
                    update_post_meta($request_id, '_arsol_pfw_request_budget', $budget_data);
                }
            }
            if (!empty($start_date)) {
                update_post_meta($request_id, '_arsol_pfw_request_start_date', $start_date);
            }
            if (!empty($delivery_date)) {
                update_post_meta($request_id, '_arsol_pfw_request_delivery_date', $delivery_date);
            }
            
            // Redirect to the specific request view
            $redirect_url = wc_get_account_endpoint_url('view-request/' . $request_id);
            wp_safe_redirect($redirect_url);
            exit;
        } else {
            // Add error notice if request creation failed
            wc_add_notice(__('Failed to submit project request. Please try again.', 'arsol-pfw'), 'error');
        }
    }
} 