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
        // Delay menu setup until after init to ensure text domain is loaded
        add_action('init', array($this, 'setup_admin_hooks'), 20);
    }
    
    /**
     * Setup admin hooks after init
     */
    public function setup_admin_hooks() {
        add_action('admin_menu', array($this, 'setup_admin_menus'), 10);
        add_action('admin_menu', array($this, 'cleanup_admin_menus'), 999);
    }
    
    /**
     * Setup admin menus in the correct order
     */
    public function setup_admin_menus() {
        $parent_slug = 'edit.php?post_type=arsol-project';
        
        // 1. Project Requests
        add_submenu_page(
            $parent_slug,
            __('Project Requests', 'arsol-pfw'),
            __('Project Requests', 'arsol-pfw'),
            'edit_posts',
            'edit.php?post_type=arsol-pfw-request',
            '',
            1
        );
        
        // 2. Project Proposals
        add_submenu_page(
            $parent_slug,
            __('Project Proposals', 'arsol-pfw'),
            __('Project Proposals', 'arsol-pfw'),
            'edit_posts',
            'edit.php?post_type=arsol-pfw-proposal',
            '',
            2
        );
        
        // 3. Projects
        add_submenu_page(
            $parent_slug,
            __('Projects', 'arsol-pfw'),
            __('Projects', 'arsol-pfw'),
            'edit_posts',
            'edit.php?post_type=arsol-project',
            '',
            3
        );
        
        // 4. Project Statuses
        add_submenu_page(
            $parent_slug,
            __('Project Statuses', 'arsol-pfw'),
            __('Project Statuses', 'arsol-pfw'),
            'manage_categories',
            'edit-tags.php?taxonomy=arsol-project-status&post_type=arsol-project',
            '',
            4
        );
        
        // 5. Settings (last)
        $settings_result = add_submenu_page(
            $parent_slug,
            __('Settings', 'arsol-pfw'),
            __('Settings', 'arsol-pfw'),
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
                // Keep only our custom menus (positions 1,2,3,4,99)
                if (!in_array($key, [1, 2, 3, 4, 99])) {
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
        $active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'general';
        ?>
        <div class="wrap">
            <h1><?php _e('Arsol Projects for Woo', 'arsol-pfw'); ?></h1>
            <h2 class="nav-tab-wrapper">
                <a href="?post_type=arsol-project&page=arsol-projects-settings&tab=general" class="nav-tab <?php echo $active_tab == 'general' ? 'nav-tab-active' : ''; ?>"><?php _e('General', 'arsol-pfw'); ?></a>
                <a href="?post_type=arsol-project&page=arsol-projects-settings&tab=advanced" class="nav-tab <?php echo $active_tab == 'advanced' ? 'nav-tab-active' : ''; ?>"><?php _e('Advanced', 'arsol-pfw'); ?></a>
                <a href="?post_type=arsol-project&page=arsol-projects-settings&tab=integrations" class="nav-tab <?php echo $active_tab == 'integrations' ? 'nav-tab-active' : ''; ?>"><?php _e('Integrations', 'arsol-pfw'); ?></a>
            </h2>
            <?php
            switch ($active_tab) {
                case 'advanced':
                    include ARSOL_PROJECTS_PLUGIN_DIR . 'includes/ui/templates/admin/page-admin-settings-advanced.php';
                    break;
                case 'integrations':
                    include ARSOL_PROJECTS_PLUGIN_DIR . 'includes/ui/templates/admin/page-admin-settings-integrations.php';
                    break;
                default:
        include ARSOL_PROJECTS_PLUGIN_DIR . 'includes/ui/templates/admin/page-admin-settings-general.php';
                    break;
            }
            ?>
        </div>
        <?php
    }
}

