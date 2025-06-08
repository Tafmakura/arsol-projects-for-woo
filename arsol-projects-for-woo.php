<?php
/**
 * Plugin Name: Arsol Projects for Woo
 * Plugin URI: https://your-site.com/arsol-projects-for-woo
 * Description: A WordPress plugin to manage projects with WooCommerce integration
 * Version: 0.0.9.3
 * Requires at least: 5.8
 * Requires PHP: 7.4.1
 * Requires Plugins: woocommerce
 * Author: Taf Makura
 * Author URI: https://your-site.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: arsol-pfw
 * Domain Path: /languages
 * 
 * @package Arsol_Projects_For_Woo
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

// Prevent direct access to this file
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('ARSOL_PROJECTS_PLUGIN_FILE', __FILE__);
define('ARSOL_PROJECTS_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('ARSOL_PROJECTS_PLUGIN_URL', plugin_dir_url(__FILE__));
define('ARSOL_PROJECTS_PLUGIN_BASENAME', plugin_basename(__FILE__));

// Use correct namespace
use Arsol_Projects_For_Woo\Setup;
use Arsol_Projects_For_Woo\Workflow\Workflow_Handler;
use Arsol_Projects_For_Woo\Comments_Handler;

// Include the Setup class
require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/classes/class-setup.php';

// Include the admin settings class
require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/classes/class-admin-settings-general.php';

// Include the workflow handler class
require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/workflow/class-workflow-handler.php';

// Include the comments handler class
require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/classes/class-comments-handler.php';

// Register activation hook
register_activation_hook(__FILE__, 'arsol_projects_activate');

/**
 * Plugin activation function
 */
function arsol_projects_activate() {
    // Set flag to flush rewrite rules on next init
    update_option('arsol_projects_flush_rewrite_rules', false);
}

// Register deactivation hook
register_deactivation_hook(__FILE__, 'arsol_projects_deactivate');

/**
 * Plugin deactivation function
 */
function arsol_projects_deactivate() {
    // Delete the flush rewrite rules option
    delete_option('arsol_projects_flush_rewrite_rules');
    // Flush rewrite rules
    flush_rewrite_rules();
}

/**
 * Initializes the Arsol Projects for Woo plugin.
 *
 * This function is hooked to the 'plugins_loaded' action to ensure that all
 * dependent plugins are loaded before our plugin's main logic runs.
 *
 * @return void
 */
function arsol_projects_init() {
    // Instantiate the Setup class
    new Setup();
    // Instantiate the Workflow_Handler class
    new Workflow_Handler();
    // Instantiate the Comments_Handler class
    new Comments_Handler();
}
add_action('plugins_loaded', 'arsol_projects_init'); 