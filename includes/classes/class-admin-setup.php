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
        
        // Add menu highlighting for taxonomy pages
        add_filter('parent_file', array($this, 'highlight_parent_menu'));
        add_filter('submenu_file', array($this, 'highlight_submenu'));
    }
    
    /**
     * Setup admin menus in the correct order
     */
    public function setup_admin_menus() {
        $parent_slug = 'edit.php?post_type=arsol-pfw-project';
        
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
        
        // 2. Request Stages
        add_submenu_page(
            $parent_slug,
            __('Request Stages', 'arsol-pfw'),
            __('Request Stages', 'arsol-pfw'),
            'manage_categories',
            'edit-tags.php?taxonomy=arsol-pfw-request-stage&post_type=arsol-pfw-request',
            '',
            2
        );
        
        // 3. Project Proposals
        add_submenu_page(
            $parent_slug,
            __('Project Proposals', 'arsol-pfw'),
            __('Project Proposals', 'arsol-pfw'),
            'edit_posts',
            'edit.php?post_type=arsol-pfw-proposal',
            '',
            3
        );
        
        // 4. Proposal Stages
        add_submenu_page(
            $parent_slug,
            __('Proposal Stages', 'arsol-pfw'),
            __('Proposal Stages', 'arsol-pfw'),
            'manage_categories',
            'edit-tags.php?taxonomy=arsol-pfw-proposal-stage&post_type=arsol-pfw-proposal',
            '',
            4
        );
        
        // 5. Projects
        add_submenu_page(
            $parent_slug,
            __('Projects', 'arsol-pfw'),
            __('Projects', 'arsol-pfw'),
            'edit_posts',
            'edit.php?post_type=arsol-pfw-project',
            '',
            5
        );
        
        // 6. Project Stages
        add_submenu_page(
            $parent_slug,
            __('Project Stages', 'arsol-pfw'),
            __('Project Stages', 'arsol-pfw'),
            'manage_categories',
            'edit-tags.php?taxonomy=arsol-pfw-project-stage&post_type=arsol-pfw-project',
            '',
            6
        );
        
        // 99. Settings (last)
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
        $parent_slug = 'edit.php?post_type=arsol-pfw-project';
        
        if (isset($submenu[$parent_slug])) {
            // Store our custom menus before cleanup
            $custom_menus = array();
            foreach ($submenu[$parent_slug] as $key => $menu_item) {
                if (in_array($key, [1, 2, 3, 4, 5, 6, 99])) {
                    $custom_menus[$key] = $menu_item;
                }
            }
            
            // Remove all default WordPress submenus
            foreach ($submenu[$parent_slug] as $key => $menu_item) {
                // Keep only our custom menus (positions 1,2,3,4,5,6,99)
                if (!in_array($key, [1, 2, 3, 4, 5, 6, 99])) {
                    unset($submenu[$parent_slug][$key]);
                }
            }
            
            // Restore our custom menus
            foreach ($custom_menus as $position => $menu_item) {
                $submenu[$parent_slug][$position] = $menu_item;
            }
            
            // Sort the submenu by key to ensure proper order
            ksort($submenu[$parent_slug]);
        }
    }
    
    /**
     * Highlight the Projects parent menu when viewing stage taxonomy pages
     *
     * @param string $parent_file The parent file
     * @return string Modified parent file
     */
    public function highlight_parent_menu($parent_file) {
        global $current_screen;
        
        if (!$current_screen) {
            return $parent_file;
        }
        
        // Check if we're on a stage taxonomy page
        $stage_taxonomies = array(
            'arsol-pfw-request-stage',
            'arsol-pfw-proposal-stage',
            'arsol-pfw-project-stage'
        );
        
        if (in_array($current_screen->taxonomy, $stage_taxonomies)) {
            return 'edit.php?post_type=arsol-pfw-project';
        }
        
        return $parent_file;
    }
    
    /**
     * Highlight the correct submenu when viewing stage taxonomy pages
     *
     * @param string $submenu_file The submenu file
     * @return string Modified submenu file
     */
    public function highlight_submenu($submenu_file) {
        global $current_screen;
        
        if (!$current_screen) {
            return $submenu_file;
        }
        
        // Map taxonomies to their submenu files
        $taxonomy_submenu_map = array(
            'arsol-pfw-request-stage' => 'edit-tags.php?taxonomy=arsol-pfw-request-stage&post_type=arsol-pfw-request',
            'arsol-pfw-proposal-stage' => 'edit-tags.php?taxonomy=arsol-pfw-proposal-stage&post_type=arsol-pfw-proposal',
            'arsol-pfw-project-stage' => 'edit-tags.php?taxonomy=arsol-pfw-project-stage&post_type=arsol-pfw-project'
        );
        
        if (isset($taxonomy_submenu_map[$current_screen->taxonomy])) {
            return $taxonomy_submenu_map[$current_screen->taxonomy];
        }
        
        return $submenu_file;
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
                <a href="?post_type=arsol-pfw-project&page=arsol-projects-settings&tab=general" class="nav-tab <?php echo $active_tab == 'general' ? 'nav-tab-active' : ''; ?>"><?php _e('General', 'arsol-pfw'); ?></a>
                <a href="?post_type=arsol-pfw-project&page=arsol-projects-settings&tab=phases" class="nav-tab <?php echo $active_tab == 'phases' ? 'nav-tab-active' : ''; ?>"><?php _e('Content', 'arsol-pfw'); ?></a>
                <a href="?post_type=arsol-pfw-project&page=arsol-projects-settings&tab=stages" class="nav-tab <?php echo $active_tab == 'stages' ? 'nav-tab-active' : ''; ?>"><?php _e('Stages', 'arsol-pfw'); ?></a>
                <a href="?post_type=arsol-pfw-project&page=arsol-projects-settings&tab=files" class="nav-tab <?php echo $active_tab == 'files' ? 'nav-tab-active' : ''; ?>"><?php _e('Files', 'arsol-pfw'); ?></a>
                <a href="?post_type=arsol-pfw-project&page=arsol-projects-settings&tab=templates" class="nav-tab <?php echo $active_tab == 'templates' ? 'nav-tab-active' : ''; ?>"><?php _e('Display', 'arsol-pfw'); ?></a>
                <a href="?post_type=arsol-pfw-project&page=arsol-projects-settings&tab=tools" class="nav-tab <?php echo $active_tab == 'tools' ? 'nav-tab-active' : ''; ?>"><?php _e('Tools', 'arsol-pfw'); ?></a>
                <a href="?post_type=arsol-pfw-project&page=arsol-projects-settings&tab=integrations" class="nav-tab <?php echo $active_tab == 'integrations' ? 'nav-tab-active' : ''; ?>"><?php _e('Integrations', 'arsol-pfw'); ?></a>
            </h2>
            <?php
            switch ($active_tab) {
                case 'phases':
                    include ARSOL_PROJECTS_PLUGIN_DIR . 'includes/ui/templates/admin/page-admin-settings-phases.php';
                    break;
                case 'stages':
                    include ARSOL_PROJECTS_PLUGIN_DIR . 'includes/ui/templates/admin/page-admin-settings-stages.php';
                    break;
                case 'files':
                    include ARSOL_PROJECTS_PLUGIN_DIR . 'includes/ui/templates/admin/page-admin-settings-files.php';
                    break;
                case 'templates':
                    include ARSOL_PROJECTS_PLUGIN_DIR . 'includes/ui/templates/admin/page-admin-settings-templates.php';
                    break;
                case 'tools':
                    include ARSOL_PROJECTS_PLUGIN_DIR . 'includes/ui/templates/admin/page-admin-settings-tools.php';
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

