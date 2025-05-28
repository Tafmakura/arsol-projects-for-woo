<?php
/**
 * Plugin Name: Arsol Projects for Woo
 * Plugin URI: https://your-site.com/arsol-projects-for-woo
 * Description: A WordPress plugin to manage projects with WooCommerce integration
 * Version: 0.0.8.1
 * Requires at least: 5.8
 * Requires PHP: 7.4
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
define('ARSOL_PROJECTS_VERSION', '1.2.7');
define('ARSOL_PROJECTS_ASSETS_VERSION', '1.3.7'); // Specific version for assets, helpful for cache busting

// Use correct namespace
use Arsol_Projects_For_Woo\Setup;

// Include the Setup class
require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/classes/class-setup.php';

// Instantiate the Setup class
new Setup();
