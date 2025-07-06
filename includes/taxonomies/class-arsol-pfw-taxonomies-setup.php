<?php
/**
 * Taxonomies Setup Class
 *
 * Handles setup and registration of all custom taxonomies
 *
 * @package Arsol_Projects_For_Woo
 * @since 1.0.0
 */

namespace Arsol_Projects_For_Woo\Taxonomies;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Taxonomies Setup class
 */
class Setup {

    /**
     * Constructor
     */
    public function __construct() {
        // Main includes setup handles all file includes and instantiations
        // This setup now only handles taxonomy-specific coordination
        add_action('init', array($this, 'register_taxonomies'), 5);
    }

    /**
     * Register taxonomies
     */
    public function register_taxonomies() {
        // Project Stage taxonomy
        
        // Request Stage taxonomy
        
        // Proposal Stage taxonomy
    }
}
