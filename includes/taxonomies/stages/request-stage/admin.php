<?php
/**
 * Request Stage Taxonomy Admin Class
 *
 * Handles admin functionality for the request stage taxonomy
 *
 * @package Arsol_Projects_For_Woo
 * @since 1.0.0
 */

namespace Arsol_Projects_For_Woo\Taxonomies\Stages\Request_Stage;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Request Stage Taxonomy Admin class
 */
class Admin {

    /**
     * Constructor
     */
    public function __construct() {
        // Add capability checks for taxonomy screens
        add_action('admin_init', array($this, 'check_taxonomy_access'));
    }

    /**
     * Check if user can access taxonomy screens
     */
    public function check_taxonomy_access() {
        \Arsol_Projects_For_Woo\Core\Access_Handler::check_taxonomy_access();
    }
}
