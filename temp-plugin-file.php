<?php
/**
 * Plugin Name: Arsol Projects for Woo
 * Plugin URI: https://your-site.com/arsol-projects-for-woo
 * Description: A WordPress plugin to manage projects with WooCommerce integration
 * Version: 0.0.8.9
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

// Include the Setup class
require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/classes/class-setup.php';

// Include the admin settings class
require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/classes/class-admin-settings-general.php';

// Register activation hook
register_activation_hook(__FILE__, 'arsol_projects_activate');

/**
 * Plugin activation function
 */
function arsol_projects_activate() {
    // Flush rewrite rules
    flush_rewrite_rules();
}

// Instantiate the Setup class
