<?php
/**
 * Standard Workflow Main Class
 *
 * Main coordination class for the standard workflow system.
 * Handles workflow initialization and coordination between transitions and stages.
 *
 * @package Arsol_Projects_For_Woo
 * @subpackage Workflows
 * @since 1.0.0
 */

declare(strict_types=1);

namespace Arsol_Projects_For_Woo\Workflows;

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Standard Workflow Main Class
 *
 * Coordinates all aspects of the standard workflow including transitions,
 * stages, and workflow management.
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
     * Transitions handler
     *
     * @var \Arsol_Projects_For_Woo\Workflows\Standard\Transitions|null
     */
    private $transitions = null;

    /**
     * Stages handler
     *
     * @var \Arsol_Projects_For_Woo\Workflows\Standard\Stages|null
     */
    private $stages = null;

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

        // Initialize handlers
        $this->init_handlers();

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
     * Initialize handlers
     */
    private function init_handlers(): void {
        // Initialize transitions handler
        $this->transitions = \Arsol_Projects_For_Woo\Workflows\Standard\Transitions::get_instance();

        // Initialize stages handler
        $this->stages = \Arsol_Projects_For_Woo\Workflows\Standard\Stages::get_instance();
    }

    /**
     * Setup WordPress hooks
     */
    private function setup_hooks(): void {
        // Workflow initialization hooks
        add_action('arsol_pfw_workflow_init', array($this, 'on_workflow_init'));
        add_action('arsol_pfw_workflow_cleanup', array($this, 'cleanup_stuck_workflows'));
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
     * Get transitions handler
     *
     * @return \Arsol_Projects_For_Woo\Workflows\Standard\Transitions|null
     */
    public function get_transitions(): ?\Arsol_Projects_For_Woo\Workflows\Standard\Transitions {
        return $this->transitions;
    }

    /**
     * Get stages handler
     *
     * @return \Arsol_Projects_For_Woo\Workflows\Standard\Stages|null
     */
    public function get_stages(): ?\Arsol_Projects_For_Woo\Workflows\Standard\Stages {
        return $this->stages;
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
        if ($this->stages) {
            return $this->stages->user_can_view_post($user_id, $post_id);
        }
        return false;
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
            'transitions_loaded' => $this->transitions !== null,
            'stages_loaded' => $this->stages !== null,
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
     * Cleanup stuck workflows
     *
     * @param int $max_age_minutes Maximum age in minutes
     */
    public function cleanup_stuck_workflows(int $max_age_minutes = 30): void {
        if ($this->stages) {
            $this->stages->cleanup_stuck_workflows($max_age_minutes);
        }
    }

    /**
     * Emergency cleanup all stuck workflows
     */
    public function emergency_cleanup_all_stuck_workflows(): void {
        if ($this->stages) {
            $this->stages->emergency_cleanup_all_stuck_workflows();
        }
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
} 