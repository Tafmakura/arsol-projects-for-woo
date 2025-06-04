<?php
/**
 * Plugin Name: Arsol Projects for Woo
 * Plugin URI: https://your-site.com/arsol-projects-for-woo
 * Description: A WordPress plugin to manage projects with WooCommerce integration
 * Version: 0.0.8.8
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
new Setup();

// Define the settings page callback function
if (!function_exists('arsol_projects_settings_page_callback')) {
    function arsol_projects_settings_page_callback() {
        include ARSOL_PROJECTS_PLUGIN_DIR . 'includes/ui/templates/admin/page-admin-settings-general.php';
    }
}

add_action('admin_menu', function() {
    $parent_slug = 'edit.php?post_type=arsol-project';
    
    // 1. All Project Requests
    add_submenu_page(
        $parent_slug,
        __('Project Requests', 'arsol-pfw'),
        __('All Project Requests', 'arsol-pfw'),
        'edit_posts',
        'edit.php?post_type=arsol-pfw-request',
        '',
        1
    );
    
    // 2. All Project Proposals
    add_submenu_page(
        $parent_slug,
        __('Project Proposals', 'arsol-pfw'),
        __('All Project Proposals', 'arsol-pfw'),
        'edit_posts',
        'edit.php?post_type=arsol-pfw-proposal',
        '',
        2
    );
    
    // 3. Add Project Proposal
    add_submenu_page(
        $parent_slug,
        __('Add Project Proposal', 'arsol-pfw'),
        __('Add Project Proposal', 'arsol-pfw'),
        'edit_posts',
        'post-new.php?post_type=arsol-pfw-proposal',
        '',
        3
    );
    
    // 4. All Projects
    add_submenu_page(
        $parent_slug,
        __('All Projects', 'arsol-pfw'),
        __('All Projects', 'arsol-pfw'),
        'edit_posts',
        'edit.php?post_type=arsol-project',
        '',
        4
    );
    
    // 5. Add Project
    add_submenu_page(
        $parent_slug,
        __('Add Project', 'arsol-pfw'),
        __('Add Project', 'arsol-pfw'),
        'edit_posts',
        'post-new.php?post_type=arsol-project',
        '',
        5
    );
    
    // 6. Project Statuses
    add_submenu_page(
        $parent_slug,
        __('Project Statuses', 'arsol-pfw'),
        __('Project Statuses', 'arsol-pfw'),
        'manage_categories',
        'edit-tags.php?taxonomy=arsol-project-status&post_type=arsol-project',
        '',
        6
    );
    
    // 7. Settings (last)
    $settings_result = add_submenu_page(
        $parent_slug,
        __('Settings', 'arsol-projects-for-woo'),
        __('Settings', 'arsol-projects-for-woo'),
        'manage_options',
        'arsol-projects-settings',
        'arsol_projects_settings_page_callback',
        99
    );
    
    // Debug logging for settings menu
    if (function_exists('error_log')) {
        error_log('ARSOL DEBUG: Settings menu added: ' . ($settings_result ? 'SUCCESS' : 'FAILED'));
    }
}, 10);

// Clean up menu after WordPress adds default items
add_action('admin_menu', function() {
    global $submenu;
    $parent_slug = 'edit.php?post_type=arsol-project';
    
    if (isset($submenu[$parent_slug])) {
        // Remove all default WordPress submenus
        foreach ($submenu[$parent_slug] as $key => $menu_item) {
            // Keep only our custom menus (positions 1,2,3,4,5,6,99)
            if (!in_array($key, [1, 2, 3, 4, 5, 6, 99])) {
                unset($submenu[$parent_slug][$key]);
            }
        }
        
        // Sort the submenu by key to ensure proper order
        ksort($submenu[$parent_slug]);
    }
}, 999);

// Fix menu highlighting for custom post types
add_filter('parent_file', function($parent_file) {
    global $current_screen;
    
    if (!$current_screen) {
        return $parent_file;
    }
    
    // Highlight parent menu for our custom post types
    if (in_array($current_screen->post_type, ['arsol-pfw-request', 'arsol-pfw-proposal'])) {
        return 'edit.php?post_type=arsol-project';
    }
    
    // Highlight parent menu for project status taxonomy
    if ($current_screen->taxonomy === 'arsol-project-status') {
        return 'edit.php?post_type=arsol-project';
    }
    
    return $parent_file;
});

add_filter('submenu_file', function($submenu_file) {
    global $current_screen;
    
    if (!$current_screen) {
        return $submenu_file;
    }
    
    // Highlight correct submenu for project requests
    if ($current_screen->post_type === 'arsol-pfw-request') {
        if ($current_screen->base === 'post') {
            return 'post-new.php?post_type=arsol-pfw-request'; // Add new form
        }
        return 'edit.php?post_type=arsol-pfw-request'; // List view
    }
    
    // Highlight correct submenu for project proposals
    if ($current_screen->post_type === 'arsol-pfw-proposal') {
        if ($current_screen->base === 'post') {
            return 'post-new.php?post_type=arsol-pfw-proposal'; // Add new form
        }
        return 'edit.php?post_type=arsol-pfw-proposal'; // List view
    }
    
    // Highlight correct submenu for main projects
    if ($current_screen->post_type === 'arsol-project') {
        if ($current_screen->base === 'post') {
            return 'post-new.php?post_type=arsol-project'; // Add new form
        }
        return 'edit.php?post_type=arsol-project'; // List view
    }
    
    // Highlight Project Statuses submenu
    if ($current_screen->taxonomy === 'arsol-project-status') {
        return 'edit-tags.php?taxonomy=arsol-project-status&post_type=arsol-project';
    }
    
    return $submenu_file;
});
