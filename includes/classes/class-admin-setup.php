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
        
        // 2. Add Project Request
        add_submenu_page(
            $parent_slug,
            __('Add Project Request', 'arsol-pfw'),
            __('Add Project Request', 'arsol-pfw'),
            'edit_posts',
            'post-new.php?post_type=arsol-pfw-request',
            '',
            2
        );
        
        // 3. All Project Proposals
        add_submenu_page(
            $parent_slug,
            __('Project Proposals', 'arsol-pfw'),
            __('All Project Proposals', 'arsol-pfw'),
            'edit_posts',
            'edit.php?post_type=arsol-pfw-proposal',
            '',
            3
        );
        
        // 4. Add Project Proposal
        add_submenu_page(
            $parent_slug,
            __('Add Project Proposal', 'arsol-pfw'),
            __('Add Project Proposal', 'arsol-pfw'),
            'edit_posts',
            'post-new.php?post_type=arsol-pfw-proposal',
            '',
            4
        );
        
        // 5. All Projects
        add_submenu_page(
            $parent_slug,
            __('All Projects', 'arsol-pfw'),
            __('All Projects', 'arsol-pfw'),
            'edit_posts',
            'edit.php?post_type=arsol-project',
            '',
            5
        );
        
        // 6. Add Project
        add_submenu_page(
            $parent_slug,
            __('Add Project', 'arsol-pfw'),
            __('Add Project', 'arsol-pfw'),
            'edit_posts',
            'post-new.php?post_type=arsol-project',
            '',
            6
        );
        
        // 7. Project Statuses
        add_submenu_page(
            $parent_slug,
            __('Project Statuses', 'arsol-pfw'),
            __('Project Statuses', 'arsol-pfw'),
            'manage_categories',
            'edit-tags.php?taxonomy=arsol-project-status&post_type=arsol-project',
            '',
            7
        );
        
        // 8. Settings (last)
        $settings_result = add_submenu_page(
            $parent_slug,
            __('Settings', 'arsol-projects-for-woo'),
            __('Settings', 'arsol-projects-for-woo'),
            'manage_options',
            'arsol-projects-settings',
            array($this, 'settings_page_callback'),
            99
        );
    }
    
    /**
     * Clean up admin menus after WordPress adds default items
     */
    public function cleanup_admin_menus() {
        global $submenu;
        $parent_slug = 'edit.php?post_type=arsol-project';
        
        if (isset($submenu[$parent_slug])) {
            // Store our settings menu before cleanup
            $settings_menu = null;
            foreach ($submenu[$parent_slug] as $key => $menu_item) {
                if (isset($menu_item[2]) && $menu_item[2] === 'arsol-projects-settings') {
                    $settings_menu = $menu_item;
                    break;
                }
            }
            
            // Remove all default WordPress submenus
            foreach ($submenu[$parent_slug] as $key => $menu_item) {
                // Keep only our custom menus (positions 1,2,3,4,5,6,7,99)
                if (!in_array($key, [1, 2, 3, 4, 5, 6, 7, 99])) {
                    unset($submenu[$parent_slug][$key]);
                }
            }
            
            // Ensure settings menu is preserved at position 99
            if ($settings_menu && !isset($submenu[$parent_slug][99])) {
                $submenu[$parent_slug][99] = $settings_menu;
            }
            
            // Sort the submenu by key to ensure proper order
            ksort($submenu[$parent_slug]);
        }
    }
    
    /**
     * Settings page callback
     */
    public function settings_page_callback() {
        include ARSOL_PROJECTS_PLUGIN_DIR . 'includes/ui/templates/admin/page-admin-settings-general.php';
    }
}

