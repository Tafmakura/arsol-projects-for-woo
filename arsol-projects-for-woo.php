<?php
/**
 * Plugin Name: Arsol Projects for WooCommerce
 * Plugin URI: https://arsol.com
 * Description: A comprehensive project management system for WooCommerce, allowing customers to create and manage projects, proposals, and requests.
 * Version: 2.0.0
 * Author: Arsol
 * Author URI: https://arsol.com
 * Text Domain: arsol-pfw
 * Domain Path: /languages
 * Requires at least: 5.0
 * Tested up to: 6.4
 * Requires PHP: 7.4
 * WC requires at least: 5.0
 * WC tested up to: 8.0
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 *
 * @package Arsol_Projects_For_Woo
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('ARSOL_PFW_PLUGIN_FILE', __FILE__);
define('ARSOL_PFW_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('ARSOL_PFW_PLUGIN_URL', plugin_dir_url(__FILE__));
define('ARSOL_PFW_PLUGIN_BASENAME', plugin_basename(__FILE__));

// Define project meta key constant for WooCommerce integration
define('ARSOL_PFW_PROJECT_META_KEY', 'arsol-pfw/parent-project-id');

// Declare HPOS compatibility
add_action('before_woocommerce_init', function() {
    if (class_exists('\Automattic\WooCommerce\Utilities\FeaturesUtil')) {
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility('custom_order_tables', __FILE__, true);
    }
});

// Initialize the plugin
add_action('plugins_loaded', function() {
    // Load the stage handler
    require_once ARSOL_PFW_PLUGIN_DIR . 'includes/core/stage-handler.php';
    
    // Load the stage functions
    require_once ARSOL_PFW_PLUGIN_DIR . 'includes/functions/stage-functions.php';
    
    // Load the setup class
    require_once ARSOL_PFW_PLUGIN_DIR . 'class-arsol-pfw-setup.php';
    
    // Initialize the plugin
    new \Arsol_Projects_For_Woo\Setup();
}); 