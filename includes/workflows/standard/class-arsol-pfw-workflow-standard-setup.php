<?php
/**
 * Standard Workflow Setup Class
 *
 * Handles the setup and configuration of the standard workflow for the
 * Arsol Projects for WooCommerce plugin.
 *
 * @package Arsol_Projects_For_Woo
 * @subpackage Workflows\\Standard
 * @since 1.0.0
 */

declare(strict_types=1);

namespace Arsol_Projects_For_Woo\Workflows\Standard;

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Standard Workflow Setup Class
 *
 * Manages the standard workflow configuration and initialization.
 * This class handles the setup of the standard project workflow stages,
 * transitions, and related functionality.
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
     * Workflow configuration
     *
     * @var array
     */
    private $config = array();

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
     * Initialize setup
     */
    private function init(): void {
        // Load configuration
        $this->load_config();

        // Setup hooks
        $this->setup_hooks();

        // Initialize workflow components
        $this->init_workflow_components();
    }

    /**
     * Load workflow configuration
     */
    private function load_config(): void {
        $this->config = array(
            'workflow_name' => 'standard',
            'workflow_version' => '1.0.0',
            'workflow_description' => 'Standard workflow for Arsol Projects for WooCommerce',
            'enabled' => true,
            'auto_initialize' => true,
            'cleanup_enabled' => true,
            'cleanup_interval' => 'hourly',
            'max_workflow_age' => 30, // minutes
        );

        /**
         * Filter: arsol_pfw_standard_workflow_setup_config
         * Allows modification of standard workflow setup configuration
         */
        $this->config = apply_filters('arsol_pfw_standard_workflow_setup_config', $this->config);
    }

    /**
     * Setup WordPress hooks
     */
    private function setup_hooks(): void {
        // Workflow setup hooks
        add_action('arsol_pfw_standard_workflow_setup_init', array($this, 'on_setup_init'));
        add_action('arsol_pfw_standard_workflow_setup_complete', array($this, 'on_setup_complete'));
        
        // WordPress init hook
        add_action('init', array($this, 'on_wordpress_init'));
    }

    /**
     * Initialize workflow components
     */
    private function init_workflow_components(): void {
        if (!$this->config['enabled']) {
            return;
        }

        // Components will be initialized by their respective classes
        // This method serves as a central initialization point
        
        /**
         * Hook: arsol_pfw_standard_workflow_components_init
         * Fired when workflow components are being initialized
         */
        do_action('arsol_pfw_standard_workflow_components_init', $this);
    }

    /**
     * Handle setup initialization
     */
    public function on_setup_init(): void {
        /**
         * Hook: arsol_pfw_standard_workflow_setup_initialized
         * Fired when standard workflow setup is initialized
         */
        do_action('arsol_pfw_standard_workflow_setup_initialized', $this);
    }

    /**
     * Handle setup completion
     */
    public function on_setup_complete(): void {
        /**
         * Hook: arsol_pfw_standard_workflow_setup_completed
         * Fired when standard workflow setup is completed
         */
        do_action('arsol_pfw_standard_workflow_setup_completed', $this);
    }

    /**
     * Handle WordPress initialization
     */
    public function on_wordpress_init(): void {
        if ($this->config['auto_initialize']) {
            /**
             * Hook: arsol_pfw_standard_workflow_auto_init
             * Fired during WordPress init when auto-initialization is enabled
             */
            do_action('arsol_pfw_standard_workflow_auto_init', $this);
        }
    }

    /**
     * Get setup configuration
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
     * Update configuration
     *
     * @param string $key Configuration key
     * @param mixed $value Configuration value
     * @return bool
     */
    public function update_config(string $key, $value): bool {
        if (isset($this->config[$key])) {
            $this->config[$key] = $value;
            return true;
        }
        return false;
    }

    /**
     * Check if workflow is enabled
     *
     * @return bool
     */
    public function is_enabled(): bool {
        return (bool) $this->config['enabled'];
    }

    /**
     * Enable workflow
     *
     * @return bool
     */
    public function enable(): bool {
        $this->config['enabled'] = true;
        
        /**
         * Hook: arsol_pfw_standard_workflow_enabled
         * Fired when standard workflow is enabled
         */
        do_action('arsol_pfw_standard_workflow_enabled', $this);
        
        return true;
    }

    /**
     * Disable workflow
     *
     * @return bool
     */
    public function disable(): bool {
        $this->config['enabled'] = false;
        
        /**
         * Hook: arsol_pfw_standard_workflow_disabled
         * Fired when standard workflow is disabled
         */
        do_action('arsol_pfw_standard_workflow_disabled', $this);
        
        return true;
    }

    /**
     * Get setup status
     *
     * @return array
     */
    public function get_status(): array {
        return array(
            'setup_class' => get_class($this),
            'enabled' => $this->config['enabled'],
            'auto_initialize' => $this->config['auto_initialize'],
            'cleanup_enabled' => $this->config['cleanup_enabled'],
            'workflow_name' => $this->config['workflow_name'],
            'workflow_version' => $this->config['workflow_version'],
            'config' => $this->config
        );
    }

    /**
     * Reset configuration to defaults
     *
     * @return bool
     */
    public function reset_config(): bool {
        $this->load_config();
        
        /**
         * Hook: arsol_pfw_standard_workflow_config_reset
         * Fired when standard workflow configuration is reset
         */
        do_action('arsol_pfw_standard_workflow_config_reset', $this);
        
        return true;
    }
} 