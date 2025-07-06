<?php
/**
 * Default Workflow Transitions Handler
 *
 * Handles all workflow transitions including conversions and customer actions
 * for the default workflow system.
 *
 * @package Arsol_Projects_For_Woo
 * @subpackage Workflows\Default
 * @since 1.0.0
 */

declare(strict_types=1);

namespace Arsol_Projects_For_Woo\Workflows\Default;

use Exception;

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Default Workflow Transitions Handler
 *
 * Manages all workflow transitions including request to proposal conversion,
 * proposal to project conversion, and customer actions.
 *
 * @since 1.0.0
 */
class Transitions {

    /**
     * Class instance
     *
     * @var Transitions|null
     */
    private static $instance = null;

    /**
     * Get class instance
     *
     * @return Transitions
     */
    public static function get_instance(): Transitions {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor
     */
    private function __construct() {
        $this->init();
    }

    /**
     * Initialize transitions
     */
    private function init(): void {
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

        // Admin notice display
        add_action('admin_notices', array($this, 'display_conversion_notices'));
    }

    /**
     * Set proposal review status
     *
     * @param string $new_status New status
     * @param string $old_status Old status
     * @param \WP_Post $post Post object
     */
    public function set_proposal_review_status($new_status, $old_status, $post): void {
        // Removed automatic review status setting - using proposal status only
        // if ($post->post_type === 'arsol-pfw-proposal' && $new_status === 'publish' && $old_status !== 'publish') {
        // Set proposal to pending-approval status when published');
        // }
    }

    /**
     * Convert request to proposal
     */
    public function convert_request_to_proposal(): void {
        $request_id = intval($_GET['request_id']);
        $request_post = null;
        
        try {
            // Get source details
            $request_post = get_post($request_id);
            if (!$request_post || $request_post->post_type !== 'arsol-pfw-request') {
                throw new Exception(__('Invalid request.', 'arsol-pfw'));
            }

            // Security validation
            if (!isset($_GET['request_id']) || !wp_verify_nonce($_GET['_wpnonce'], 'arsol_convert_to_proposal_nonce')) {
                throw new Exception(__('Invalid request or nonce.', 'arsol-pfw'));
            }

            if (!current_user_can('edit_post', $request_id) || !current_user_can('publish_posts')) {
                throw new Exception(__('You do not have sufficient permissions to perform this action.', 'arsol-pfw'));
            }
            
            // Get stages handler
            $stages = Stages::get_instance();
            
            // Prevent concurrent conversions and handle stuck workflows
            if ($stages->is_workflow_in_progress($request_id)) {
                // Check if this is a stuck workflow (older than 5 minutes)
                $workflow_started = get_post_meta($request_id, '_arsol_workflow_started', true);
                $is_stuck = false;
                
                if ($workflow_started) {
                    $started_time = strtotime($workflow_started);
                    $current_time = current_time('timestamp');
                    $age_minutes = ($current_time - $started_time) / 60;
                    
                    if ($age_minutes > 0.5) { // 30 seconds
                        $is_stuck = true;
                        \Arsol_Projects_For_Woo\Woocommerce_Logs::log_workflow('warning', 
                            "Detected stuck workflow for request #{$request_id}, age: {$age_minutes} minutes. Auto-clearing...");
                    }
                }
                
                if ($is_stuck) {
                    // Force clear the stuck workflow
                    $stages->force_clear_stuck_workflow($request_id);
                    
                    \Arsol_Projects_For_Woo\Woocommerce_Logs::log_workflow('info', 
                        "Successfully cleared stuck workflow for request #{$request_id}. Proceeding with conversion...");
                } else {
                    throw new Exception(__('Conversion already in progress.', 'arsol-pfw'));
                }
            }
            
            // Start transaction with logging
            $stages->start_workflow_transaction($request_id, 'conversion', 'request_to_proposal');
            \Arsol_Projects_For_Woo\Woocommerce_Logs::log_request_to_proposal_conversion('info', 
                "Starting conversion: Request #{$request_id} to Proposal");
            
            // Validation step
            update_post_meta($request_id, '_arsol_conversion_step', 'validation');

            // Server-side validation of the request stage
            $current_stage = wp_get_object_terms($request_id, 'arsol-pfw-request-stage', array('fields' => 'slugs'));
            if (empty($current_stage) || $current_stage[0] !== 'approved') {
                throw new Exception(sprintf(
                    __('This request cannot be converted. The stage is "%s", must be "approved".', 'arsol-pfw'),
                    empty($current_stage) ? 'none' : $current_stage[0]
                ));
            }

            // Prepare conversion data for hooks
            $conversion_data = array(
                'request_id' => $request_id,
                'user_id' => get_current_user_id(),
                'conversion_method' => 'admin_conversion',
                'timestamp' => current_time('timestamp'),
                'request_post' => $request_post,
                'request_stage' => $current_stage[0]
            );

            /**
             * Hook: arsol_before_proposal_conversion_validation
             * Fired before any validation checks are performed
             */
            do_action('arsol_before_proposal_conversion_validation', $request_id, $conversion_data);

            /**
             * Hook: arsol_after_proposal_conversion_validated
             * Fired after all validation checks pass, before proposal creation
             */
            do_action('arsol_after_proposal_conversion_validated', $request_id, $request_post, $conversion_data);
            
            // Creation step
            update_post_meta($request_id, '_arsol_conversion_step', 'creation');

            // Create proposal args with filter for customization
            $proposal_args = array(
                'post_title'   => $request_post->post_title,
                'post_content' => '', // Clean content flow: Empty slate for proposal writing
                'post_status'  => 'publish',
                'post_type'    => 'arsol-pfw-proposal',
                'post_author'  => $request_post->post_author,
            );

            /**
             * Filter: arsol_proposal_conversion_args
             * Allows modification of proposal creation arguments
             */
            $proposal_args = apply_filters('arsol_proposal_conversion_args', $proposal_args, $request_id, $request_post, $conversion_data);

            /**
             * Hook: arsol_before_proposal_conversion_proposal_creation
             * Fired immediately before the proposal post is created
             */
            do_action('arsol_before_proposal_conversion_proposal_creation', $proposal_args, $request_id, $conversion_data);

            $new_proposal_id = wp_insert_post($proposal_args);
            if (is_wp_error($new_proposal_id)) {
                throw new Exception($new_proposal_id->get_error_message());
            }
            
            // Record created entity
            $stages->record_transaction_entity($request_id, $new_proposal_id);
            $conversion_data['new_proposal_id'] = $new_proposal_id;

            /**
             * Hook: arsol_after_proposal_conversion_proposal_created
             * Fired after the proposal is successfully created, before metadata copy
             */
            do_action('arsol_after_proposal_conversion_proposal_created', $new_proposal_id, $request_id, $request_post, $conversion_data);
            
            // Metadata copy step
            update_post_meta($request_id, '_arsol_conversion_step', 'metadata_copy');
            
            // Copy metadata from request to proposal
            $this->copy_request_metadata_to_proposal($request_id, $new_proposal_id);

            /**
             * Hook: arsol_after_proposal_conversion_complete
             * Fired after the conversion is complete, before cleanup
             */
            do_action('arsol_after_proposal_conversion_complete', $new_proposal_id, $request_id, $conversion_data);
            
            // Complete transaction
            $stages->complete_workflow_transaction($request_id);
            
            // Log success
            \Arsol_Projects_For_Woo\Woocommerce_Logs::log_request_to_proposal_conversion('success', 
                "Conversion completed: Request #{$request_id} → Proposal #{$new_proposal_id}");
            
            // Set success notice
            $this->set_conversion_success_notice('request', 'proposal', $request_id, $new_proposal_id, $request_post->post_title);
            
            /**
             * Hook: arsol_before_proposal_conversion_redirect
             * Fired just before redirecting to the new proposal
             */
            $redirect_url = admin_url('post.php?post=' . $new_proposal_id . '&action=edit');
            do_action('arsol_before_proposal_conversion_redirect', $new_proposal_id, $redirect_url, $conversion_data);

            // Redirect
            $this->safe_redirect($redirect_url);
            
        } catch (Exception $e) {
            // Log error
            \Arsol_Projects_For_Woo\Woocommerce_Logs::log_request_to_proposal_conversion('error', 
                "Conversion failed: Request #{$request_id} - " . $e->getMessage());
            
            // Rollback everything
            $stages->rollback_workflow_transaction($request_id, $e->getMessage());
            
            // Set failure notice
            if ($request_post) {
                $this->set_conversion_failure_notice('request', 'proposal', $request_id, $request_post->post_title, $e->getMessage());
            }
            
            // Redirect back
            $this->safe_redirect(admin_url('post.php?post=' . $request_id . '&action=edit'));
        }
    }

    /**
     * Convert proposal to project
     *
     * @param int $proposal_id Proposal ID
     * @param bool $is_internal_call Whether this is an internal call
     */
    public function convert_proposal_to_project($proposal_id = 0, $is_internal_call = false): void {
        // Implementation would go here - this is a large method
        // For now, just basic structure
        if (empty($proposal_id)) {
            $proposal_id = intval($_GET['proposal_id']);
        }
        
        // Get stages handler
        $stages = Stages::get_instance();
        
        // Validate proposal exists and is valid for conversion
        $proposal_post = get_post($proposal_id);
        if (!$proposal_post || $proposal_post->post_type !== 'arsol-pfw-proposal') {
            wp_die(__('Invalid proposal.', 'arsol-pfw'));
        }
        
        // Security checks and conversion logic would go here
        // This would be similar to convert_request_to_proposal but for proposals->projects
    }

    /**
     * Customer cancel request
     */
    public function customer_cancel_request(): void {
        if (!isset($_GET['request_id']) || !wp_verify_nonce($_GET['_wpnonce'], 'arsol_cancel_request_nonce')) {
            wp_die(__('Invalid request or nonce.', 'arsol-pfw'));
        }

        $request_id = intval($_GET['request_id']);
        $stages = Stages::get_instance();
        
        if ($stages->user_can_view_post(get_current_user_id(), $request_id)) {
            wp_delete_post($request_id, true); // Delete the request
            
            // Redirect to the requests list tab instead of general projects page
            $requests_url = add_query_arg('tab', 'requests', wc_get_account_endpoint_url('projects'));
            $this->safe_redirect($requests_url);
        } else {
            wp_die(__('You do not have permission to cancel this request.', 'arsol-pfw'));
        }
    }

    /**
     * Customer approve proposal
     */
    public function customer_approve_proposal(): void {
        if (!isset($_GET['proposal_id']) || !wp_verify_nonce($_GET['_wpnonce'], 'arsol_approve_proposal_nonce')) {
            wp_die(__('Invalid proposal or nonce.', 'arsol-pfw'));
        }

        $proposal_id = intval($_GET['proposal_id']);
        $stages = Stages::get_instance();
        
        if ($stages->user_can_view_post(get_current_user_id(), $proposal_id)) {
            // Set the proposal status to 'approved' before conversion
            wp_set_object_terms($proposal_id, 'approved', 'arsol-pfw-proposal-stage');
            
            // Re-use the conversion logic
            $this->convert_proposal_to_project($proposal_id, true);
        } else {
            wp_die(__('You do not have permission to approve this proposal.', 'arsol-pfw'));
        }
    }

    /**
     * Customer reject proposal
     */
    public function customer_reject_proposal(): void {
        if (!isset($_GET['proposal_id']) || !wp_verify_nonce($_GET['_wpnonce'], 'arsol_reject_proposal_nonce')) {
            wp_die(__('Invalid proposal or nonce.', 'arsol-pfw'));
        }

        $proposal_id = intval($_GET['proposal_id']);
        $stages = Stages::get_instance();
        
        if ($stages->user_can_view_post(get_current_user_id(), $proposal_id)) {
            wp_set_object_terms($proposal_id, 'rejected', 'arsol-pfw-proposal-stage');
            $this->safe_redirect(wp_get_referer());
        } else {
            wp_die(__('You do not have permission to reject this proposal.', 'arsol-pfw'));
        }
    }

    /**
     * Handle create request
     */
    public function handle_create_request(): void {
        // Prepare creation data for hooks
        $creation_data = array(
            'user_id' => get_current_user_id(),
            'creation_method' => 'frontend_form',
            'timestamp' => current_time('timestamp'),
            'form_data' => $_POST
        );

        /**
         * Hook: arsol_before_request_creation_validation
         * Fired before any validation checks are performed
         */
        do_action('arsol_before_request_creation_validation', $creation_data);

        if (!wp_verify_nonce($_POST['arsol_request_nonce'], 'arsol_create_request')) {
            wp_die(__('Invalid nonce.', 'arsol-pfw'));
        }

        /**
         * Hook: arsol_after_request_creation_validated
         * Fired after validation passes, before request creation
         */
        do_action('arsol_after_request_creation_validated', $creation_data);

        // Prepare request args with filter for customization
        $post_data = array(
            'post_title'   => sanitize_text_field($_POST['request_title']),
            'post_content' => wp_kses_post($_POST['request_description']),
            'post_status'  => 'publish',
            'post_type'    => 'arsol-pfw-request',
            'post_author'  => get_current_user_id(),
        );

        /**
         * Filter: arsol_request_creation_args
         * Allows modification of request creation arguments
         */
        $post_data = apply_filters('arsol_request_creation_args', $post_data, $creation_data);

        /**
         * Hook: arsol_before_request_creation_post_creation
         * Fired immediately before the request post is created
         */
        do_action('arsol_before_request_creation_post_creation', $post_data, $creation_data);

        $post_id = wp_insert_post($post_data);
        
        if (is_wp_error($post_id)) {
            /**
             * Hook: arsol_request_creation_post_creation_failed
             * Fired when request creation fails
             */
            do_action('arsol_request_creation_post_creation_failed', $post_id, $post_data, $creation_data);
            
            $this->safe_redirect(wc_get_account_endpoint_url('create-request'));
        }

        $creation_data['request_id'] = $post_id;

        /**
         * Hook: arsol_after_request_creation_post_created
         * Fired after the request is successfully created, before metadata and status
         */
        do_action('arsol_after_request_creation_post_created', $post_id, $post_data, $creation_data);

        /**
         * Hook: arsol_before_request_creation_status_assignment
         * Fired before setting the request status
         */
        do_action('arsol_before_request_creation_status_assignment', $post_id, 'pending-review', $creation_data);

        wp_set_object_terms($post_id, 'pending-review', 'arsol-pfw-request-stage');

        /**
         * Hook: arsol_after_request_creation_status_assigned
         * Fired after the request status is assigned
         */
        do_action('arsol_after_request_creation_status_assigned', $post_id, 'pending-review', $creation_data);

        /**
         * Hook: arsol_before_request_creation_metadata_save
         * Fired before saving request metadata
         */
        do_action('arsol_before_request_creation_metadata_save', $post_id, $_POST, $creation_data);

        $this->update_request_meta($post_id, $_POST);

        /**
         * Hook: arsol_after_request_creation_metadata_saved
         * Fired after all metadata has been saved
         */
        do_action('arsol_after_request_creation_metadata_saved', $post_id, $_POST, $creation_data);

        /**
         * Hook: arsol_after_request_creation_complete
         * Fired after the request creation is complete, before redirect
         */
        do_action('arsol_after_request_creation_complete', $post_id, $creation_data);

        /**
         * Hook: arsol_before_request_creation_redirect
         * Fired just before redirecting to the new request
         */
        $redirect_url = wc_get_account_endpoint_url('view-request/' . $post_id);
        do_action('arsol_before_request_creation_redirect', $post_id, $redirect_url, $creation_data);

        $this->safe_redirect($redirect_url);
    }

    /**
     * Handle edit request
     */
    public function handle_edit_request(): void {
        // Implementation would go here
        // This would handle request editing logic
    }

    /**
     * Copy request metadata to proposal
     *
     * @param int $request_id Request ID
     * @param int $proposal_id Proposal ID
     */
    private function copy_request_metadata_to_proposal($request_id, $proposal_id): void {
        // Phase 1: Comprehensive meta key restructuring
        
        // 1. Preserve original request content in proposal meta
        $request_post = get_post($request_id);
        update_post_meta($proposal_id, '_arsol_pfw_proposal_request_details', $request_post->post_content);
        
        // 2. Rename request meta keys with proposal context
        $meta_mapping = array(
            '_arsol_pfw_request_title' => '_arsol_pfw_proposal_request_title',
            '_arsol_pfw_request_date' => '_arsol_pfw_proposal_request_date',
            '_arsol_pfw_request_budget' => '_arsol_pfw_proposal_request_budget',
            '_arsol_pfw_request_start_date' => '_arsol_pfw_proposal_request_start_date',
            '_arsol_pfw_request_delivery_date' => '_arsol_pfw_proposal_request_delivery_date',
            '_arsol_pfw_request_attachments' => '_arsol_pfw_proposal_request_attachments',
        );
        
        foreach ($meta_mapping as $old_key => $new_key) {
            $value = get_post_meta($request_id, $old_key, true);
            if (!empty($value)) {
                update_post_meta($proposal_id, $new_key, $value);
            }
        }
        
        // 3. Set proposal-specific meta
        update_post_meta($proposal_id, '_arsol_pfw_proposal_converted_from_request', $request_id);
        update_post_meta($proposal_id, '_arsol_pfw_proposal_conversion_date', current_time('mysql'));
        
        // 4. Set default proposal stage
        wp_set_object_terms($proposal_id, 'pending-approval', 'arsol-pfw-proposal-stage');
    }

    /**
     * Update request metadata
     *
     * @param int $post_id Post ID
     * @param array $data Form data
     */
    private function update_request_meta($post_id, $data): void {
        // Implementation would go here
        // This would handle updating request metadata from form data
    }

    /**
     * Safe redirect
     *
     * @param string $url URL to redirect to
     */
    private function safe_redirect($url): void {
        if (wp_safe_redirect($url)) {
            exit;
        }
    }

    /**
     * Set conversion success notice
     *
     * @param string $from_type Source type
     * @param string $to_type Target type
     * @param int $from_id Source ID
     * @param int $to_id Target ID
     * @param string $title Title
     */
    private function set_conversion_success_notice($from_type, $to_type, $from_id, $to_id, $title): void {
        // Implementation would go here
        // This would set success notices for conversions
    }

    /**
     * Set conversion failure notice
     *
     * @param string $from_type Source type
     * @param string $to_type Target type
     * @param int $from_id Source ID
     * @param string $title Title
     * @param string $error Error message
     */
    private function set_conversion_failure_notice($from_type, $to_type, $from_id, $title, $error): void {
        // Implementation would go here
        // This would set failure notices for conversions
    }

    /**
     * Display conversion notices
     */
    public function display_conversion_notices(): void {
        // Implementation would go here
        // This would display admin notices for conversions
    }
} 