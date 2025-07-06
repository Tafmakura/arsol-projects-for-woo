<?php
/**
 * Standard Workflow Main Class
 *
 * Main coordination class for the standard workflow system.
 * Handles workflow initialization, transitions, stages, and all workflow operations.
 *
 * @package Arsol_Projects_For_Woo
 * @subpackage Workflows
 * @since 1.0.0
 */

declare(strict_types=1);

namespace Arsol_Projects_For_Woo\Workflows;

use Exception;

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Standard Workflow Main Class
 *
 * Consolidated workflow class that handles all aspects of the standard workflow
 * including transitions, stages, permissions, and transaction management.
 *
 * @since 1.0.0
 */
class StandardWorkflow {

    /**
     * Class instance
     *
     * @var StandardWorkflow|null
     */
    private static $instance = null;

    /**
     * Workflow configuration
     *
     * @var array
     */
    private $config = array();

    /**
     * Get class instance
     *
     * @return StandardWorkflow
     */
    public static function get_instance(): StandardWorkflow {
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
     * Initialize workflow
     */
    private function init(): void {
        // Load configuration
        $this->load_config();

        // Setup hooks
        $this->setup_hooks();
    }

    /**
     * Load workflow configuration
     */
    private function load_config(): void {
        $this->config = array(
            'name' => 'standard',
            'version' => '1.0.0',
            'description' => 'Standard workflow for Arsol Projects for WooCommerce',
            'stages' => array(
                'request' => array('pending', 'under-review', 'approved', 'rejected', 'cancelled'),
                'proposal' => array('pending-approval', 'approved', 'rejected', 'expired'),
                'project' => array('active', 'on-hold', 'completed', 'cancelled')
            ),
            'transitions' => array(
                'request_to_proposal' => array('from' => 'request', 'to' => 'proposal'),
                'proposal_to_project' => array('from' => 'proposal', 'to' => 'project')
            )
        );

        /**
         * Filter: arsol_pfw_standard_workflow_config
         * Allows modification of standard workflow configuration
         */
        $this->config = apply_filters('arsol_pfw_standard_workflow_config', $this->config);
    }

    /**
     * Setup WordPress hooks
     */
    private function setup_hooks(): void {
        // Workflow initialization hooks
        add_action('arsol_pfw_workflow_init', array($this, 'on_workflow_init'));
        add_action('arsol_pfw_workflow_cleanup', array($this, 'cleanup_stuck_workflows'));

        // Cleanup hooks
        add_action('arsol_pfw_cleanup_stuck_workflows', array($this, 'cleanup_stuck_workflows'));

        // Transition hooks
        add_action('admin_post_arsol_convert_to_proposal', array($this, 'convert_request_to_proposal'));
        add_action('admin_post_arsol_convert_to_project', array($this, 'convert_proposal_to_project'));
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
     * Get workflow configuration
     *
     * @param string $key Configuration key (optional)
     * @return mixed
     */
    public function get_config(string $key = ''): mixed {
        if (empty($key)) {
            return $this->config;
        }

        return isset($this->config[$key]) ? $this->config[$key] : null;
    }

    /**
     * Check if user can perform workflow action
     *
     * @param int $user_id User ID
     * @param int $post_id Post ID
     * @param string $action Action name
     * @return bool
     */
    public function user_can_perform_action(int $user_id, int $post_id, string $action): bool {
        return $this->user_can_view_post($user_id, $post_id);
    }

    /**
     * Get workflow status
     *
     * @return array
     */
    public function get_status(): array {
        return array(
            'name' => $this->config['name'],
            'version' => $this->config['version'],
            'active' => true,
            'config' => $this->config
        );
    }

    /**
     * Handle workflow initialization
     */
    public function on_workflow_init(): void {
        /**
         * Hook: arsol_pfw_standard_workflow_initialized
         * Fired when standard workflow is fully initialized
         */
        do_action('arsol_pfw_standard_workflow_initialized', $this);
    }

    /**
     * Get workflow statistics
     *
     * @return array
     */
    public function get_statistics(): array {
        $stats = array(
            'total_requests' => 0,
            'total_proposals' => 0,
            'total_projects' => 0,
            'active_workflows' => 0,
            'stuck_workflows' => 0
        );

        // Count posts by type
        $post_types = array('arsol-pfw-request', 'arsol-pfw-proposal', 'arsol-pfw-project');
        
        foreach ($post_types as $post_type) {
            $counts = wp_count_posts($post_type);
            $type_key = str_replace('arsol-pfw-', 'total_', $post_type) . 's';
            $stats[$type_key] = $counts->publish + $counts->draft + $counts->pending;
        }

        // Count active workflows
        global $wpdb;
        $active_workflows = $wpdb->get_var(
            "SELECT COUNT(*) FROM {$wpdb->postmeta} 
             WHERE meta_key = '_arsol_workflow_in_progress' 
             AND meta_value = '1'"
        );
        $stats['active_workflows'] = (int) $active_workflows;

        return $stats;
    }

    // ===========================================
    // STAGES METHODS (formerly from stages class)
    // ===========================================

    /**
     * Check if a user can view a specific post (project, proposal, or request)
     *
     * @param int $user_id The ID of the user
     * @param int $post_id The ID of the post
     * @return bool True if the user can view the post, false otherwise
     */
    public function user_can_view_post($user_id, $post_id): bool {
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

    /**
     * Start workflow transaction
     *
     * @param int $source_id Source post ID
     * @param string $workflow_type Workflow type
     * @param string $conversion_type Conversion type
     */
    public function start_workflow_transaction($source_id, $workflow_type, $conversion_type): void {
        // Mark workflow as in progress
        update_post_meta($source_id, '_arsol_workflow_in_progress', '1');
        update_post_meta($source_id, '_arsol_workflow_type', $workflow_type);
        update_post_meta($source_id, '_arsol_workflow_conversion_type', $conversion_type);
        update_post_meta($source_id, '_arsol_workflow_started', current_time('mysql'));
        update_post_meta($source_id, '_arsol_workflow_step', 'started');
        
        // Clear any previous transaction entities
        delete_post_meta($source_id, '_arsol_workflow_created_entities');
    }

    /**
     * Record transaction entity
     *
     * @param int $source_id Source post ID
     * @param int $entity_id Created entity ID
     */
    public function record_transaction_entity($source_id, $entity_id): void {
        $entities = get_post_meta($source_id, '_arsol_workflow_created_entities', true);
        if (!is_array($entities)) {
            $entities = array();
        }
        
        $entities[] = $entity_id;
        update_post_meta($source_id, '_arsol_workflow_created_entities', $entities);
    }

    /**
     * Complete workflow transaction
     *
     * @param int $source_id Source post ID
     */
    public function complete_workflow_transaction($source_id): void {
        // Mark workflow as completed
        update_post_meta($source_id, '_arsol_workflow_step', 'completed');
        update_post_meta($source_id, '_arsol_workflow_completed', current_time('mysql'));
        
        // Clear in-progress flag
        delete_post_meta($source_id, '_arsol_workflow_in_progress');
        
        // Keep transaction log for debugging (don't delete other meta)
        // These can be cleaned up later by maintenance tasks
    }

    /**
     * Rollback workflow transaction
     *
     * @param int $source_id Source post ID
     * @param string $reason Rollback reason
     */
    public function rollback_workflow_transaction($source_id, $reason): void {
        // Log the rollback
        update_post_meta($source_id, '_arsol_workflow_step', 'rollback');
        update_post_meta($source_id, '_arsol_workflow_rollback_reason', $reason);
        update_post_meta($source_id, '_arsol_workflow_rollback_time', current_time('mysql'));
        
        // Get conversion type for specific rollback
        $conversion_type = get_post_meta($source_id, '_arsol_workflow_conversion_type', true);
        
        switch ($conversion_type) {
            case 'request_to_proposal':
                $this->rollback_request_to_proposal($source_id);
                break;
            case 'proposal_to_project':
                $this->rollback_proposal_to_project($source_id);
                break;
        }
        
        // Clear workflow flags
        delete_post_meta($source_id, '_arsol_workflow_in_progress');
        delete_post_meta($source_id, '_arsol_workflow_step');
    }

    /**
     * Rollback request to proposal conversion
     *
     * @param int $source_id Source request ID
     */
    private function rollback_request_to_proposal($source_id): void {
        // Get created entities
        $entities = get_post_meta($source_id, '_arsol_workflow_created_entities', true);
        
        if (is_array($entities) && !empty($entities)) {
            foreach ($entities as $entity_id) {
                // Delete the created proposal
                wp_delete_post($entity_id, true);
                
                // Log the deletion
                \Arsol_Projects_For_Woo\Woocommerce_Logs::log_workflow('info', 
                    "Rollback: Deleted proposal #{$entity_id} created from request #{$source_id}");
            }
        }
        
        // Clear created entities
        delete_post_meta($source_id, '_arsol_workflow_created_entities');
    }

    /**
     * Rollback proposal to project conversion
     *
     * @param int $source_id Source proposal ID
     */
    private function rollback_proposal_to_project($source_id): void {
        // Get created entities
        $entities = get_post_meta($source_id, '_arsol_workflow_created_entities', true);
        
        if (is_array($entities) && !empty($entities)) {
            foreach ($entities as $entity_id) {
                $post = get_post($entity_id);
                
                if ($post && $post->post_type === 'arsol-pfw-project') {
                    // Delete the created project
                    wp_delete_post($entity_id, true);
                    
                    // Log the deletion
                    \Arsol_Projects_For_Woo\Woocommerce_Logs::log_workflow('info', 
                        "Rollback: Deleted project #{$entity_id} created from proposal #{$source_id}");
                }
            }
        }
        
        // Clear created entities
        delete_post_meta($source_id, '_arsol_workflow_created_entities');
    }

    /**
     * Check if workflow is in progress
     *
     * @param int $source_id Source post ID
     * @return bool
     */
    public function is_workflow_in_progress($source_id): bool {
        $in_progress = get_post_meta($source_id, '_arsol_workflow_in_progress', true);
        return $in_progress === '1';
    }

    /**
     * Cleanup stuck workflows
     *
     * @param int $max_age_minutes Maximum age in minutes
     */
    public function cleanup_stuck_workflows($max_age_minutes = 30): void {
        global $wpdb;
        
        // Find workflows that are stuck (older than max_age_minutes)
        $cutoff_time = date('Y-m-d H:i:s', current_time('timestamp') - ($max_age_minutes * 60));
        
        $stuck_workflows = $wpdb->get_results($wpdb->prepare(
            "SELECT post_id, meta_value as started_time 
             FROM {$wpdb->postmeta} 
             WHERE meta_key = '_arsol_workflow_started' 
             AND meta_value < %s
             AND post_id IN (
                 SELECT post_id 
                 FROM {$wpdb->postmeta} 
                 WHERE meta_key = '_arsol_workflow_in_progress' 
                 AND meta_value = '1'
             )",
            $cutoff_time
        ));
        
        foreach ($stuck_workflows as $workflow) {
            $this->force_clear_stuck_workflow($workflow->post_id);
            
            \Arsol_Projects_For_Woo\Woocommerce_Logs::log_workflow('info', 
                "Cleanup: Cleared stuck workflow for post #{$workflow->post_id}, started at {$workflow->started_time}");
        }
        
        if (!empty($stuck_workflows)) {
            \Arsol_Projects_For_Woo\Woocommerce_Logs::log_workflow('info', 
                "Cleanup completed: " . count($stuck_workflows) . " stuck workflows cleared");
        }
    }

    /**
     * Force clear stuck workflow
     *
     * @param int $post_id Post ID
     */
    public function force_clear_stuck_workflow($post_id): void {
        // Log the force clear
        update_post_meta($post_id, '_arsol_workflow_force_cleared', current_time('mysql'));
        update_post_meta($post_id, '_arsol_workflow_step', 'force_cleared');
        
        // Clear workflow flags
        delete_post_meta($post_id, '_arsol_workflow_in_progress');
        
        // Keep other metadata for debugging
        \Arsol_Projects_For_Woo\Woocommerce_Logs::log_workflow('warning', 
            "Force cleared stuck workflow for post #{$post_id}");
    }

    /**
     * Emergency cleanup all stuck workflows
     */
    public function emergency_cleanup_all_stuck_workflows(): void {
        global $wpdb;
        
        // Get all workflows that are marked as in progress
        $stuck_workflows = $wpdb->get_results(
            "SELECT post_id 
             FROM {$wpdb->postmeta} 
             WHERE meta_key = '_arsol_workflow_in_progress' 
             AND meta_value = '1'"
        );
        
        foreach ($stuck_workflows as $workflow) {
            $this->force_clear_stuck_workflow($workflow->post_id);
        }
        
        \Arsol_Projects_For_Woo\Woocommerce_Logs::log_workflow('warning', 
            "Emergency cleanup: Cleared " . count($stuck_workflows) . " stuck workflows");
    }

    /**
     * Get workflow statistics
     *
     * @return array
     */
    public function get_workflow_statistics(): array {
        global $wpdb;
        
        $stats = array(
            'active_workflows' => 0,
            'completed_workflows' => 0,
            'failed_workflows' => 0,
            'stuck_workflows' => 0
        );
        
        // Count active workflows
        $active_workflows = $wpdb->get_var(
            "SELECT COUNT(*) 
             FROM {$wpdb->postmeta} 
             WHERE meta_key = '_arsol_workflow_in_progress' 
             AND meta_value = '1'"
        );
        $stats['active_workflows'] = (int) $active_workflows;
        
        // Count completed workflows
        $completed_workflows = $wpdb->get_var(
            "SELECT COUNT(*) 
             FROM {$wpdb->postmeta} 
             WHERE meta_key = '_arsol_workflow_step' 
             AND meta_value = 'completed'"
        );
        $stats['completed_workflows'] = (int) $completed_workflows;
        
        // Count failed workflows
        $failed_workflows = $wpdb->get_var(
            "SELECT COUNT(*) 
             FROM {$wpdb->postmeta} 
             WHERE meta_key = '_arsol_workflow_step' 
             AND meta_value = 'rollback'"
        );
        $stats['failed_workflows'] = (int) $failed_workflows;
        
        // Count stuck workflows (older than 30 minutes)
        $cutoff_time = date('Y-m-d H:i:s', current_time('timestamp') - (30 * 60));
        $stuck_workflows = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) 
             FROM {$wpdb->postmeta} 
             WHERE meta_key = '_arsol_workflow_started' 
             AND meta_value < %s
             AND post_id IN (
                 SELECT post_id 
                 FROM {$wpdb->postmeta} 
                 WHERE meta_key = '_arsol_workflow_in_progress' 
                 AND meta_value = '1'
             )",
            $cutoff_time
        ));
        $stats['stuck_workflows'] = (int) $stuck_workflows;
        
        return $stats;
    }

    /**
     * Get workflow history for a post
     *
     * @param int $post_id Post ID
     * @return array
     */
    public function get_workflow_history($post_id): array {
        $history = array();
        
        // Get all workflow related meta
        $workflow_meta_keys = array(
            '_arsol_workflow_started',
            '_arsol_workflow_completed',
            '_arsol_workflow_rollback_time',
            '_arsol_workflow_force_cleared',
            '_arsol_workflow_type',
            '_arsol_workflow_conversion_type',
            '_arsol_workflow_step',
            '_arsol_workflow_rollback_reason'
        );
        
        foreach ($workflow_meta_keys as $meta_key) {
            $value = get_post_meta($post_id, $meta_key, true);
            if (!empty($value)) {
                $history[$meta_key] = $value;
            }
        }
        
        return $history;
    }

    // ===========================================
    // TRANSITIONS METHODS (formerly from transitions class)
    // ===========================================

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
            
            // Prevent concurrent conversions and handle stuck workflows
            if ($this->is_workflow_in_progress($request_id)) {
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
                    $this->force_clear_stuck_workflow($request_id);
                    
                    \Arsol_Projects_For_Woo\Woocommerce_Logs::log_workflow('info', 
                        "Successfully cleared stuck workflow for request #{$request_id}. Proceeding with conversion...");
                } else {
                    throw new Exception(__('Conversion already in progress.', 'arsol-pfw'));
                }
            }
            
            // Start transaction with logging
            $this->start_workflow_transaction($request_id, 'conversion', 'request_to_proposal');
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
                'post_type'    => 'arsol-pfw-proposal',
                'post_status'  => 'publish',
                'post_author'  => $request_post->post_author,
                'post_parent'  => $request_id,
                'meta_input'   => array(
                    '_arsol_parent_request_id' => $request_id,
                    '_arsol_proposal_status' => 'pending-approval',
                    '_arsol_created_method' => 'admin_conversion',
                    '_arsol_created_by' => get_current_user_id()
                )
            );

            /**
             * Filter: arsol_proposal_creation_args
             * Allows modification of proposal creation arguments
             */
            $proposal_args = apply_filters('arsol_proposal_creation_args', $proposal_args, $request_post);

            // Create proposal
            $proposal_id = wp_insert_post($proposal_args);

            if (is_wp_error($proposal_id)) {
                throw new Exception(sprintf(
                    __('Failed to create proposal: %s', 'arsol-pfw'),
                    $proposal_id->get_error_message()
                ));
            }

            // Record the created entity for transaction management
            $this->record_transaction_entity($request_id, $proposal_id);

            // Completion step
            update_post_meta($request_id, '_arsol_conversion_step', 'metadata');

            // Copy metadata from request to proposal
            $this->copy_request_metadata_to_proposal($request_id, $proposal_id);

            // Set proposal stage to pending-approval
            wp_set_object_terms($proposal_id, 'pending-approval', 'arsol-pfw-proposal-stage');

            // Complete transaction
            $this->complete_workflow_transaction($request_id);

            // Log successful conversion
            \Arsol_Projects_For_Woo\Woocommerce_Logs::log_request_to_proposal_conversion('info', 
                "Successfully converted Request #{$request_id} to Proposal #{$proposal_id}");

            /**
             * Hook: arsol_proposal_created_from_request
             * Fired after proposal is successfully created from request
             */
            do_action('arsol_proposal_created_from_request', $proposal_id, $request_id, $request_post);

            // Set success notice
            $this->set_conversion_success_notice('request', 'proposal', $request_id, $proposal_id, $request_post->post_title);

            // Redirect to proposal edit page
            $this->safe_redirect(admin_url('post.php?action=edit&post=' . $proposal_id));

        } catch (Exception $e) {
            // Log error
            \Arsol_Projects_For_Woo\Woocommerce_Logs::log_request_to_proposal_conversion('error', 
                "Failed to convert Request #{$request_id}: " . $e->getMessage());

            // Rollback transaction
            $this->rollback_workflow_transaction($request_id, $e->getMessage());

            // Set error notice
            if ($request_post) {
                $this->set_conversion_failure_notice('request', 'proposal', $request_id, $request_post->post_title, $e->getMessage());
            }

            // Redirect to request edit page
            $this->safe_redirect(admin_url('post.php?action=edit&post=' . $request_id));
        }
    }

    /**
     * Convert proposal to project
     *
     * @param int $proposal_id Proposal ID (0 for $_GET)
     * @param bool $is_internal_call Whether this is an internal call
     */
    public function convert_proposal_to_project($proposal_id = 0, $is_internal_call = false): void {
        // Implementation similar to convert_request_to_proposal
        // but for proposal to project conversion
        
        if (!$is_internal_call && !wp_verify_nonce($_GET['_wpnonce'], 'arsol_convert_to_project_nonce')) {
            wp_die(__('Invalid nonce.', 'arsol-pfw'));
        }

        $proposal_id = $proposal_id ?: intval($_GET['proposal_id']);
        $proposal_post = get_post($proposal_id);
        
        if (!$proposal_post || $proposal_post->post_type !== 'arsol-pfw-proposal') {
            wp_die(__('Invalid proposal.', 'arsol-pfw'));
        }

        if (!current_user_can('edit_post', $proposal_id)) {
            wp_die(__('You do not have sufficient permissions to perform this action.', 'arsol-pfw'));
        }

        // Implementation would continue similar to request conversion...
    }

    /**
     * Customer cancel request
     */
    public function customer_cancel_request(): void {
        if (!wp_verify_nonce($_GET['_wpnonce'], 'arsol_cancel_request_nonce')) {
            wp_die(__('Invalid nonce.', 'arsol-pfw'));
        }

        $request_id = intval($_GET['request_id']);
        $request_post = get_post($request_id);
        
        if (!$request_post || $request_post->post_type !== 'arsol-pfw-request') {
            wp_die(__('Invalid request.', 'arsol-pfw'));
        }

        if (!current_user_can('edit_post', $request_id)) {
            wp_die(__('You do not have sufficient permissions to perform this action.', 'arsol-pfw'));
        }

        // Set request stage to cancelled
        wp_set_object_terms($request_id, 'cancelled', 'arsol-pfw-request-stage');

        // Log action
        \Arsol_Projects_For_Woo\Woocommerce_Logs::log_customer_action('info', 
            "Customer cancelled request #{$request_id}");

        // Redirect
        $this->safe_redirect(wp_get_referer() ?: home_url());
    }

    /**
     * Customer approve proposal
     */
    public function customer_approve_proposal(): void {
        if (!wp_verify_nonce($_GET['_wpnonce'], 'arsol_approve_proposal_nonce')) {
            wp_die(__('Invalid nonce.', 'arsol-pfw'));
        }

        $proposal_id = intval($_GET['proposal_id']);
        $proposal_post = get_post($proposal_id);
        
        if (!$proposal_post || $proposal_post->post_type !== 'arsol-pfw-proposal') {
            wp_die(__('Invalid proposal.', 'arsol-pfw'));
        }

        if (!current_user_can('edit_post', $proposal_id)) {
            wp_die(__('You do not have sufficient permissions to perform this action.', 'arsol-pfw'));
        }

        // Set proposal stage to approved
        wp_set_object_terms($proposal_id, 'approved', 'arsol-pfw-proposal-stage');

        // Log action
        \Arsol_Projects_For_Woo\Woocommerce_Logs::log_customer_action('info', 
            "Customer approved proposal #{$proposal_id}");

        // Redirect
        $this->safe_redirect(wp_get_referer() ?: home_url());
    }

    /**
     * Customer reject proposal
     */
    public function customer_reject_proposal(): void {
        if (!wp_verify_nonce($_GET['_wpnonce'], 'arsol_reject_proposal_nonce')) {
            wp_die(__('Invalid nonce.', 'arsol-pfw'));
        }

        $proposal_id = intval($_GET['proposal_id']);
        $proposal_post = get_post($proposal_id);
        
        if (!$proposal_post || $proposal_post->post_type !== 'arsol-pfw-proposal') {
            wp_die(__('Invalid proposal.', 'arsol-pfw'));
        }

        if (!current_user_can('edit_post', $proposal_id)) {
            wp_die(__('You do not have sufficient permissions to perform this action.', 'arsol-pfw'));
        }

        // Set proposal stage to rejected
        wp_set_object_terms($proposal_id, 'rejected', 'arsol-pfw-proposal-stage');

        // Log action
        \Arsol_Projects_For_Woo\Woocommerce_Logs::log_customer_action('info', 
            "Customer rejected proposal #{$proposal_id}");

        // Redirect
        $this->safe_redirect(wp_get_referer() ?: home_url());
    }

    /**
     * Handle create request
     */
    public function handle_create_request(): void {
        if (!wp_verify_nonce($_POST['arsol_create_request_nonce'], 'arsol_create_request_action')) {
            wp_die(__('Invalid nonce.', 'arsol-pfw'));
        }

        if (!current_user_can('publish_posts')) {
            wp_die(__('You do not have sufficient permissions to perform this action.', 'arsol-pfw'));
        }

        $request_data = array(
            'post_title' => sanitize_text_field($_POST['arsol_request_title']),
            'post_content' => wp_kses_post($_POST['arsol_request_content']),
            'post_type' => 'arsol-pfw-request',
            'post_status' => 'publish',
            'post_author' => get_current_user_id(),
        );

        $request_id = wp_insert_post($request_data);

        if (is_wp_error($request_id)) {
            wp_die(__('Failed to create request.', 'arsol-pfw'));
        }

        // Update metadata
        $this->update_request_meta($request_id, $_POST);

        // Set initial stage
        wp_set_object_terms($request_id, 'pending', 'arsol-pfw-request-stage');

        // Log creation
        \Arsol_Projects_For_Woo\Woocommerce_Logs::log_request_creation('info', 
            "Created request #{$request_id}: " . $request_data['post_title']);

        // Redirect to request edit page
        $this->safe_redirect(admin_url('post.php?action=edit&post=' . $request_id));
    }

    /**
     * Handle edit request
     */
    public function handle_edit_request(): void {
        // Similar implementation to create but for editing existing request
        // Implementation would handle updating existing request data
    }

    /**
     * Copy request metadata to proposal
     *
     * @param int $request_id Request ID
     * @param int $proposal_id Proposal ID
     */
    private function copy_request_metadata_to_proposal($request_id, $proposal_id): void {
        $metadata_to_copy = array(
            '_arsol_request_budget' => '_arsol_proposal_budget',
            '_arsol_request_timeline' => '_arsol_proposal_timeline',
            '_arsol_request_description' => '_arsol_proposal_description',
            '_arsol_request_requirements' => '_arsol_proposal_requirements',
            '_arsol_request_priority' => '_arsol_proposal_priority',
            '_arsol_request_category' => '_arsol_proposal_category',
            '_arsol_request_files' => '_arsol_proposal_files',
            '_arsol_request_notes' => '_arsol_proposal_notes',
            '_arsol_request_contact_email' => '_arsol_proposal_contact_email',
            '_arsol_request_contact_phone' => '_arsol_proposal_contact_phone',
            '_arsol_request_preferred_start_date' => '_arsol_proposal_preferred_start_date',
            '_arsol_request_additional_info' => '_arsol_proposal_additional_info',
            '_arsol_request_custom_fields' => '_arsol_proposal_custom_fields',
            '_arsol_request_project_type' => '_arsol_proposal_project_type',
            '_arsol_request_estimated_hours' => '_arsol_proposal_estimated_hours',
            '_arsol_request_complexity' => '_arsol_proposal_complexity',
            '_arsol_request_technology_stack' => '_arsol_proposal_technology_stack',
            '_arsol_request_special_requirements' => '_arsol_proposal_special_requirements',
            '_arsol_request_delivery_format' => '_arsol_proposal_delivery_format',
            '_arsol_request_success_criteria' => '_arsol_proposal_success_criteria',
            '_arsol_request_constraints' => '_arsol_proposal_constraints',
            '_arsol_request_stakeholders' => '_arsol_proposal_stakeholders',
            '_arsol_request_approval_process' => '_arsol_proposal_approval_process',
            '_arsol_request_maintenance_requirements' => '_arsol_proposal_maintenance_requirements',
            '_arsol_request_integration_requirements' => '_arsol_proposal_integration_requirements',
            '_arsol_request_security_requirements' => '_arsol_proposal_security_requirements',
            '_arsol_request_performance_requirements' => '_arsol_proposal_performance_requirements',
        );

        foreach ($metadata_to_copy as $request_meta_key => $proposal_meta_key) {
            $value = get_post_meta($request_id, $request_meta_key, true);
            if (!empty($value)) {
                update_post_meta($proposal_id, $proposal_meta_key, $value);
            }
        }
    }

    /**
     * Update request metadata
     *
     * @param int $post_id Post ID
     * @param array $data Request data
     */
    private function update_request_meta($post_id, $data): void {
        // Update various request metadata fields
        // This would handle sanitization and updating of all request fields
    }

    /**
     * Safe redirect
     *
     * @param string $url URL to redirect to
     */
    private function safe_redirect($url): void {
        wp_redirect($url);
        exit;
    }

    /**
     * Set conversion success notice
     *
     * @param string $from_type Source type
     * @param string $to_type Target type
     * @param int $from_id Source ID
     * @param int $to_id Target ID
     * @param string $title Post title
     */
    private function set_conversion_success_notice($from_type, $to_type, $from_id, $to_id, $title): void {
        set_transient('arsol_conversion_success', array(
            'from_type' => $from_type,
            'to_type' => $to_type,
            'from_id' => $from_id,
            'to_id' => $to_id,
            'title' => $title
        ), 300);
    }

    /**
     * Set conversion failure notice
     *
     * @param string $from_type Source type
     * @param string $to_type Target type
     * @param int $from_id Source ID
     * @param string $title Post title
     * @param string $error Error message
     */
    private function set_conversion_failure_notice($from_type, $to_type, $from_id, $title, $error): void {
        set_transient('arsol_conversion_error', array(
            'from_type' => $from_type,
            'to_type' => $to_type,
            'from_id' => $from_id,
            'title' => $title,
            'error' => $error
        ), 300);
    }

    /**
     * Display conversion notices
     */
    public function display_conversion_notices(): void {
        // Check for success notice
        $success = get_transient('arsol_conversion_success');
        if ($success) {
            delete_transient('arsol_conversion_success');
            printf(
                '<div class="notice notice-success is-dismissible"><p>%s</p></div>',
                sprintf(
                    __('Successfully converted %s "%s" to %s.', 'arsol-pfw'),
                    $success['from_type'],
                    esc_html($success['title']),
                    $success['to_type']
                )
            );
        }

        // Check for error notice
        $error = get_transient('arsol_conversion_error');
        if ($error) {
            delete_transient('arsol_conversion_error');
            printf(
                '<div class="notice notice-error is-dismissible"><p>%s</p></div>',
                sprintf(
                    __('Failed to convert %s "%s" to %s: %s', 'arsol-pfw'),
                    $error['from_type'],
                    esc_html($error['title']),
                    $error['to_type'],
                    esc_html($error['error'])
                )
            );
        }
    }
} 