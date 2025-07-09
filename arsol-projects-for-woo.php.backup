<?php
/**
 * Plugin Name: Arsol Projects for Woo
 * Plugin URI: https://your-site.com/arsol-projects-for-woo
 * Description: A WordPress plugin to manage projects with WooCommerce integration
 * Version: 0.0.9.6
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

// Define additional constants for compatibility
define('ARSOL_PFW_PLUGIN_DIR', plugin_dir_path(__FILE__));

// Define project meta key constant for WooCommerce integration
define('ARSOL_PROJECT_META_KEY', 'arsol-pfw/parent-project-id');

// TEMPORARY DEBUG: Add debug functionality
add_action('admin_menu', function() {
    add_management_page(
        'ARSOL Debug', 
        'ARSOL Debug', 
        'manage_options', 
        'arsol-debug', 
        'arsol_debug_page'
    );
});

function arsol_debug_page() {
    $user = wp_get_current_user();
    $caps = $user->get_role_caps();
    $arsol_caps = array();
    
    // Get all ARSOL capabilities for current user
    foreach($caps as $cap => $has) {
        if(strpos($cap, 'arsol_pfw') !== false) {
            $arsol_caps[$cap] = $has;
        }
    }
    
    // Check if plugin capabilities are assigned to administrator role
    $admin_role = get_role('administrator');
    $admin_arsol_caps = array();
    if ($admin_role) {
        foreach($admin_role->capabilities as $cap => $has) {
            if(strpos($cap, 'arsol_pfw') !== false && $has) {
                $admin_arsol_caps[] = $cap;
            }
        }
    }
    
    // Check if settings exist
    $settings = get_option('arsol_pfw_general_settings', array());
    $manager_roles = isset($settings['project_manager_roles']) ? $settings['project_manager_roles'] : array();
    $user_roles = isset($settings['project_user_roles']) ? $settings['project_user_roles'] : array();
    
    ?>
    <div class="wrap">
        <h1>🐛 ARSOL PFW Capabilities Debug</h1>
        
        <div class="notice notice-info">
            <p><strong>This debug page will help identify why title links are not working in your admin lists.</strong></p>
            <p><em>This is temporary debug code - remove it when done!</em></p>
        </div>
        
        <h2>📋 Current User Information</h2>
        <table class="widefat striped">
            <tr><td><strong>Username:</strong></td><td><?php echo esc_html($user->user_login); ?></td></tr>
            <tr><td><strong>Display Name:</strong></td><td><?php echo esc_html($user->display_name); ?></td></tr>
            <tr><td><strong>Email:</strong></td><td><?php echo esc_html($user->user_email); ?></td></tr>
            <tr><td><strong>Roles:</strong></td><td><?php echo esc_html(implode(', ', $user->roles)); ?></td></tr>
        </table>
        
        <h2>🔑 Current User's ARSOL PFW Capabilities</h2>
        <?php if (!empty($arsol_caps)): ?>
            <table class="widefat striped">
                <thead>
                    <tr><th>Capability</th><th>Status</th></tr>
                </thead>
                <tbody>
                    <?php foreach($arsol_caps as $cap => $has): ?>
                        <tr>
                            <td><code><?php echo esc_html($cap); ?></code></td>
                            <td>
                                <?php if ($has): ?>
                                    <span style="color: green; font-weight: bold;">✅ HAS</span>
                                <?php else: ?>
                                    <span style="color: red; font-weight: bold;">❌ MISSING</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="notice notice-error">
                <p><strong>❌ NO ARSOL PFW CAPABILITIES FOUND FOR CURRENT USER!</strong></p>
                <p>This user has no ARSOL PFW capabilities assigned. This is likely why title links are not working.</p>
            </div>
        <?php endif; ?>
        
        <h2>👑 Administrator Role's ARSOL PFW Capabilities</h2>
        <?php if (!empty($admin_arsol_caps)): ?>
            <div class="notice notice-success">
                <p><strong>✅ Administrator role has <?php echo count($admin_arsol_caps); ?> ARSOL PFW capabilities:</strong></p>
            </div>
            <ul style="columns: 2; list-style-type: disc; margin-left: 20px;">
                <?php foreach($admin_arsol_caps as $cap): ?>
                    <li><code><?php echo esc_html($cap); ?></code></li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <div class="notice notice-error">
                <p><strong>❌ ADMINISTRATOR ROLE HAS NO ARSOL PFW CAPABILITIES!</strong></p>
                <p>This is the root cause of your title link issues. The administrator role needs ARSOL capabilities.</p>
            </div>
        <?php endif; ?>
        
        <h2>⚙️ Plugin Settings Analysis</h2>
        <table class="widefat striped">
            <tr>
                <td><strong>Project Manager Roles:</strong></td>
                <td>
                    <?php if (!empty($manager_roles)): ?>
                        <?php echo esc_html(implode(', ', $manager_roles)); ?>
                        <?php if (in_array('administrator', $manager_roles)): ?>
                            <span style="color: green;">✅</span>
                        <?php else: ?>
                            <span style="color: red;">❌ Administrator not included</span>
                        <?php endif; ?>
                    <?php else: ?>
                        <span style="color: red;">❌ Not set</span>
                    <?php endif; ?>
                </td>
            </tr>
            <tr>
                <td><strong>Customer Permissions Roles:</strong></td>
                <td>
                    <?php if (!empty($user_roles)): ?>
                        <?php echo esc_html(implode(', ', $user_roles)); ?>
                        <?php if (in_array('administrator', $user_roles)): ?>
                            <span style="color: green;">✅</span>
                        <?php else: ?>
                            <span style="color: red;">❌ Administrator not included</span>
                        <?php endif; ?>
                    <?php else: ?>
                        <span style="color: red;">❌ Not set</span>
                    <?php endif; ?>
                </td>
            </tr>
        </table>
        
        <h2>🔧 Quick Fix Instructions</h2>
        <?php if (empty($admin_arsol_caps)): ?>
            <div class="notice notice-warning">
                <h3>SOLUTION: Assign Capabilities Through Settings</h3>
                <ol style="font-size: 14px;">
                    <li>Go to: <strong>Projects → Settings → General</strong></li>
                    <li>Find: <strong>"Project Manager Permissions"</strong> section</li>
                    <li>Check the box for: <strong>Administrator</strong></li>
                    <li>Find: <strong>"Customer Permissions"</strong> section</li>
                    <li>Check the box for: <strong>Administrator</strong></li>
                    <li>Click: <strong>Save Settings</strong></li>
                    <li>Refresh this page to verify capabilities are assigned</li>
                    <li>Check your admin lists - title links should now work!</li>
                </ol>
            </div>
        <?php else: ?>
            <div class="notice notice-success">
                <p><strong>✅ Administrator role has capabilities! If title links still don't work, the issue might be in the column implementation.</strong></p>
            </div>
        <?php endif; ?>
        
        <h2>🧪 Manual Capability Assignment (Alternative)</h2>
        <p>If the settings method doesn't work, you can manually assign capabilities using this button:</p>
        
        <?php if (isset($_POST['assign_caps_manually'])): ?>
            <?php
            $admin_role = get_role('administrator');
            if ($admin_role) {
                $capabilities = [
                    'arsol_pfw_manage',
                    'edit_arsol_pfw_projects', 'edit_others_arsol_pfw_projects', 'publish_arsol_pfw_projects',
                    'read_private_arsol_pfw_projects', 'delete_arsol_pfw_projects', 'delete_others_arsol_pfw_projects',
                    'edit_arsol_pfw_proposals', 'edit_others_arsol_pfw_proposals', 'publish_arsol_pfw_proposals',
                    'read_private_arsol_pfw_proposals', 'delete_arsol_pfw_proposals', 'delete_others_arsol_pfw_proposals',
                    'edit_arsol_pfw_requests', 'edit_others_arsol_pfw_requests', 'publish_arsol_pfw_requests',
                    'read_private_arsol_pfw_requests', 'delete_arsol_pfw_requests', 'delete_others_arsol_pfw_requests',
                ];

                foreach ($capabilities as $cap) {
                    $admin_role->add_cap($cap);
                }
                
                echo '<div class="notice notice-success"><p><strong>✅ Capabilities manually assigned! Refresh this page to see the changes.</strong></p></div>';
            }
            ?>
        <?php endif; ?>
        
        <form method="post" style="margin: 20px 0;">
            <button type="submit" name="assign_caps_manually" class="button button-primary">
                🔧 Manually Assign All Capabilities to Administrator
            </button>
        </form>
        
        <hr>
        <p><em><strong>Remember:</strong> Remove the debug code from the main plugin file when you're done troubleshooting!</em></p>
    </div>
    <?php
}

// Use correct namespace
use Arsol_Projects_For_Woo\Setup;
use Arsol_Projects_For_Woo\Workflow\Workflow_Handler;
use Arsol_Projects_For_Woo\Admin\Setup_Defaults;
use Arsol_Projects_For_Woo\Frontend_Template_Sidebar_Meta;
use Arsol_Projects_For_Woo\Frontend_Template_Sidebar_Buttons;

// Include the Setup class
require_once ARSOL_PROJECTS_PLUGIN_DIR . 'class-arsol-pfw-setup.php';

// Note: Admin settings and setup defaults are now loaded by the main Setup class

// Include the workflow handler class
require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/workflow/class-arsol-pfw-workflow-handler.php';

// Include the frontend sidebar classes
require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/frontend/class-arsol-pfw-frontend-template-sidebar-meta.php';
require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/frontend/class-arsol-pfw-frontend-template-sidebar-buttons.php';

// Register activation hook
register_activation_hook(__FILE__, 'arsol_projects_activate');

/**
 * Plugin activation function
 */
function arsol_projects_activate() {
    // Set flag to flush rewrite rules on next init
    update_option('arsol_projects_flush_rewrite_rules', false);
    
    // Trigger defaults initialization
    do_action('arsol_pfw_plugin_activated');
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
    // Instantiate the Setup_Defaults class
    new Setup_Defaults();
    // Instantiate the Frontend Sidebar classes
    new Frontend_Template_Sidebar_Meta();
    new Frontend_Template_Sidebar_Buttons();
}
add_action('plugins_loaded', 'arsol_projects_init');

// Declare HPOS compatibility
add_action('before_woocommerce_init', function() {
    if (class_exists('\Automattic\WooCommerce\Utilities\FeaturesUtil')) {
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility('custom_order_tables', __FILE__, true);
    }
}); 