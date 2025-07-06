<?php
/**
 * Default Workflow Stages Handler
 *
 * Handles workflow stages, permissions, and transaction management
 * for the default workflow system.
 *
 * @package Arsol_Projects_For_Woo
 * @subpackage Workflows\Default
 * @since 1.0.0
 */

declare(strict_types=1);

namespace Arsol_Projects_For_Woo\Workflows\Default;

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Default Workflow Stages Handler
 *
 * Manages workflow stages, user permissions, and transaction handling
 * for the default workflow system.
 *
 * @since 1.0.0
 */
class Stages {

    /**
     * Class instance
     *
     * @var Stages|null
     */
    private static $instance = null;

    /**
     * Get class instance
     *
     * @return Stages
     */
    public static function get_instance(): Stages {
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
     * Initialize stages handler
     */
    private function init(): void {
        // Setup cleanup hooks
        add_action('arsol_pfw_cleanup_stuck_workflows', array($this, 'cleanup_stuck_workflows'));
    }

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
} 