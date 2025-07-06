<?php
/**
 * Phases Setup Class
 *
 * Handles setup and initialization of the phases system for Arsol Projects for WooCommerce
 *
 * @package Arsol_Projects_For_Woo\Phases
 * @version 1.0.0
 */

namespace Arsol_Projects_For_Woo\Phases;

if (!defined('ABSPATH')) {
    exit;
}

class Setup {
    
    /**
     * Singleton instance
     */
    private static $instance = null;
    
    /**
     * Get singleton instance
     */
    public static function get_instance(): Setup {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor
     * Initializes the phases system
     */
    private function __construct() {
        // Load phases conversion class
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/phases/class-arsol-pfw-phases-conversion.php';
        
        // Initialize after all plugins are loaded
        add_action('plugins_loaded', array($this, 'init'), 20);
    }

    /**
     * Initialize the phases system
     */
    public function init(): void {
        $this->require_files();
        $this->instantiate_classes();
    }

    /**
     * Require phases-related files
     */
    private function require_files(): void {
        // Core phases classes
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/phases/class-arsol-pfw-phases-conversion.php';
    }

    /**
     * Instantiate phases classes
     */
    private function instantiate_classes(): void {
        // Initialize phases conversion system
        \Arsol_Projects_For_Woo\Phases\Conversion::get_instance();
    }

    /**
     * Get phases conversion instance
     *
     * @return \Arsol_Projects_For_Woo\Phases\Conversion
     */
    public function get_conversion_instance(): \Arsol_Projects_For_Woo\Phases\Conversion {
        return \Arsol_Projects_For_Woo\Phases\Conversion::get_instance();
    }
}
