<?php
/**
 * Admin Setup Class
 *
 * Handles the admin menu setup for Arsol Projects For Woo.
 *
 * @package Arsol_Projects_For_Woo
 * @since 1.0.0
 */

namespace Arsol_Projects_For_Woo\Admin;

if (!defined('ABSPATH')) {
    exit;
}

// Removed the Setup class and its menu logic as settings are now a submenu under the CPT.

/**
 * Class Users
 *
 * Handles user-related functionality in the admin area.
 *
 * @package Arsol_Projects_For_Woo\Admin
 * @since 1.0.0
 */
class Users {
    
    /**
     * Constructor
     */
    public function __construct() {
        // Initialize user-related functionality
    }
    
    /**
     * Initialize hooks and actions
     */
    public function init() {
        // Add your user-related hooks here
    }
}
