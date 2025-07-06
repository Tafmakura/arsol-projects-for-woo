<?php
/**
 * Workflows Setup Manager
 *
 * Manages the setup and initialization of all workflow systems for the
 * Arsol Projects for WooCommerce plugin.
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
 * Workflows Setup Manager Class
 *
 * Initializes and manages all workflow systems including default and custom workflows.
 * This class acts as the main entry point for workflow functionality.
 *
 * @since 1.0.0
 */
class Setup {

    /**
     * Class instance
     *
     * @var Setup|null
     */
    private static $instance = null;

    /**
     * Workflow instances
     *
     * @var array
     */
    private $workflows = array();

    /**
     * Get class instance
     *
     * @return Setup
     */
    public static function get_instance(): Setup {
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
     * Initialize workflows
     */
    private function init(): void {
        // Load workflow dependencies
        $this->load_dependencies();

        // Initialize default workflow
        $this->init_default_workflow();

        // Hook into WordPress
        add_action('init', array($this, 'setup_hooks'));
    }

    /**
     * Load workflow dependencies
     */
    private function load_dependencies(): void {
        // Load standard workflow setup
        require_once ARSOL_PFW_PLUGIN_DIR . 'includes/workflows/standard/class-arsol-pfw-workflow-standard-setup.php';
        require_once ARSOL_PFW_PLUGIN_DIR . 'includes/workflows/standard/class-arsol-pfw-workflow-standard.php';
    }

    /**
     * Initialize default workflow
     */
    private function init_default_workflow(): void {
        // Initialize standard workflow setup
        $standard_setup = \Arsol_Projects_For_Woo\Workflows\Standard\Setup::get_instance();
        
        // Initialize standard workflow
        $standard_workflow = \Arsol_Projects_For_Woo\Workflows\StandardWorkflow::get_instance();
        
        // Store workflow instance
        $this->workflows['default'] = $standard_workflow;
    }

    /**
     * Setup WordPress hooks
     */
    public function setup_hooks(): void {
        // Global workflow hooks
        add_action('arsol_pfw_workflow_cleanup', array($this, 'cleanup_stuck_workflows'));
        
        // Schedule cleanup if not already scheduled
        if (!wp_next_scheduled('arsol_pfw_workflow_cleanup')) {
            wp_schedule_event(time(), 'hourly', 'arsol_pfw_workflow_cleanup');
        }
    }

    /**
     * Get workflow instance
     *
     * @param string $workflow_name Workflow name (default: 'default')
     * @return mixed|null
     */
    public function get_workflow(string $workflow_name = 'default') {
        return isset($this->workflows[$workflow_name]) ? $this->workflows[$workflow_name] : null;
    }

    /**
     * Register custom workflow
     *
     * @param string $name Workflow name
     * @param object $workflow_instance Workflow instance
     * @return bool
     */
    public function register_workflow(string $name, $workflow_instance): bool {
        if (isset($this->workflows[$name])) {
            return false; // Workflow already registered
        }

        $this->workflows[$name] = $workflow_instance;
        return true;
    }

    /**
     * Get all registered workflows
     *
     * @return array
     */
    public function get_workflows(): array {
        return $this->workflows;
    }

    /**
     * Cleanup stuck workflows across all workflow types
     */
    public function cleanup_stuck_workflows(): void {
        foreach ($this->workflows as $workflow) {
            if (method_exists($workflow, 'cleanup_stuck_workflows')) {
                $workflow->cleanup_stuck_workflows();
            }
        }
    }

    /**
     * Emergency cleanup all stuck workflows
     */
    public function emergency_cleanup_all(): void {
        foreach ($this->workflows as $workflow) {
            if (method_exists($workflow, 'emergency_cleanup_all_stuck_workflows')) {
                $workflow->emergency_cleanup_all_stuck_workflows();
            }
        }
    }

    /**
     * Check if workflow is active
     *
     * @param string $workflow_name Workflow name
     * @return bool
     */
    public function is_workflow_active(string $workflow_name): bool {
        return isset($this->workflows[$workflow_name]);
    }

    /**
     * Get workflow status
     *
     * @return array
     */
    public function get_workflow_status(): array {
        $status = array();
        
        foreach ($this->workflows as $name => $workflow) {
            $status[$name] = array(
                'name' => $name,
                'active' => true,
                'class' => get_class($workflow),
                'methods' => get_class_methods($workflow)
            );
        }
        
        return $status;
    }
} 