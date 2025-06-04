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

class Setup {
    
    /**
     * Constructor
     */
    public function __construct() {
        add_action('admin_menu', array($this, 'setup_admin_menus'), 10);
        add_action('admin_menu', array($this, 'cleanup_admin_menus'), 999);
        add_filter('parent_file', array($this, 'fix_parent_menu_highlighting'));
        add_filter('submenu_file', array($this, 'fix_submenu_highlighting'));
    }
    
    /**
     * Setup admin menus in the correct order
     */
    public function setup_admin_menus() {
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
            array($this, 'settings_page_callback'),
            99
        );
        
        // Debug logging for settings menu
        if (function_exists('error_log')) {
            error_log('ARSOL DEBUG: Settings menu added: ' . ($settings_result ? 'SUCCESS' : 'FAILED'));
            error_log('ARSOL DEBUG: Settings callback method exists: ' . (method_exists($this, 'settings_page_callback') ? 'YES' : 'NO'));
            error_log('ARSOL DEBUG: Current user can manage_options: ' . (current_user_can('manage_options') ? 'YES' : 'NO'));
        }
    }
    
    /**
     * Clean up admin menus after WordPress adds default items
     */
    public function cleanup_admin_menus() {
        global $submenu;
        $parent_slug = 'edit.php?post_type=arsol-project';
        
        if (isset($submenu[$parent_slug])) {
            // Debug: Log what menus exist before cleanup
            if (function_exists('error_log')) {
                error_log('ARSOL DEBUG: Menus before cleanup: ' . count($submenu[$parent_slug]));
                foreach ($submenu[$parent_slug] as $key => $menu_item) {
                    error_log('ARSOL DEBUG: Menu position ' . $key . ': ' . $menu_item[0] . ' (' . $menu_item[2] . ')');
                }
            }
            
            // Store our settings menu before cleanup
            $settings_menu = null;
            foreach ($submenu[$parent_slug] as $key => $menu_item) {
                if (isset($menu_item[2]) && $menu_item[2] === 'arsol-projects-settings') {
                    $settings_menu = $menu_item;
                    error_log('ARSOL DEBUG: Found settings menu at position ' . $key);
                    break;
                }
            }
            
            // Remove all default WordPress submenus
            $removed_count = 0;
            foreach ($submenu[$parent_slug] as $key => $menu_item) {
                // Keep only our custom menus (positions 1,2,3,4,5,6,99)
                if (!in_array($key, [1, 2, 3, 4, 5, 6, 99])) {
                    unset($submenu[$parent_slug][$key]);
                    $removed_count++;
                }
            }
            
            // Ensure settings menu is preserved at position 99
            if ($settings_menu && !isset($submenu[$parent_slug][99])) {
                $submenu[$parent_slug][99] = $settings_menu;
                error_log('ARSOL DEBUG: Restored settings menu at position 99');
            }
            
            // Debug: Log what happened during cleanup
            if (function_exists('error_log')) {
                error_log('ARSOL DEBUG: Removed ' . $removed_count . ' menu items');
                error_log('ARSOL DEBUG: Menus after cleanup: ' . count($submenu[$parent_slug]));
                
                // Log final menu structure
                foreach ($submenu[$parent_slug] as $key => $menu_item) {
                    error_log('ARSOL DEBUG: Final menu position ' . $key . ': ' . $menu_item[0] . ' (' . $menu_item[2] . ')');
                }
            }
            
            // Sort the submenu by key to ensure proper order
            ksort($submenu[$parent_slug]);
        }
    }
    
    /**
     * Fix parent menu highlighting for custom post types
     */
    public function fix_parent_menu_highlighting($parent_file) {
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
    }
    
    /**
     * Fix submenu highlighting for custom post types
     */
    public function fix_submenu_highlighting($submenu_file) {
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
    }
    
    /**
     * Settings page callback
     */
    public function settings_page_callback() {
        include ARSOL_PROJECTS_PLUGIN_DIR . 'includes/ui/templates/admin/page-admin-settings-general.php';
    }
}

