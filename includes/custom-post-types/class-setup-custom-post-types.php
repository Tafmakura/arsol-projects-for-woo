<?php

namespace Arsol_Projects_For_Woo\Custom_Post_Types;

if (!defined('ABSPATH')) {
    exit;
}

class Setup {
    public function __construct() {
        $this->require_files();
        $this->instantiate_classes();
        
        // Add manual menu registration as backup
        add_action('admin_menu', array($this, 'ensure_submenus'), 20);

        // Ensure post types are registered with proper priority
        add_action('init', array($this, 'force_register_post_types'), 5);
    }

    private function require_files() {
        // Custom Post Types
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/custom-post-types/project/class-project-cpt-admin-setup.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/custom-post-types/project/class-project-cpt-admin-project.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/custom-post-types/project/class-project-cpt-admin-projects.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/custom-post-types/project-request/class-project-request-cpt-admin-setup.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/custom-post-types/project-proposal/class-project-proposal-cpt-admin-setup.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/classes/class-woocommerce-endpoints.php';
    }

    private function instantiate_classes() {
        // Project CPT
        new \Arsol_Projects_For_Woo\Custom_Post_Types\ProjectPost\Admin\Setup();
        new \Arsol_Projects_For_Woo\Custom_Post_Types\ProjectPost\Admin\Projects();
        new \Arsol_Projects_For_Woo\Custom_Post_Types\ProjectPost\Admin\Project();
        // Project Request CPT
        new \Arsol_Projects_For_Woo\Custom_Post_Types\ProjectRequest\Admin\Setup();
        // Project Proposal CPT
        new \Arsol_Projects_For_Woo\Custom_Post_Types\ProjectProposal\Admin\Setup();
    }

    /**
     * Ensure submenus are properly registered
     */
    public function ensure_submenus() {
        // Check if parent menu exists
        global $menu, $submenu;
        
        $parent_slug = 'edit.php?post_type=arsol-project';
        
        // Debug logging
        if (function_exists('error_log')) {
            $parent_exists = isset($submenu[$parent_slug]);
            error_log('ARSOL DEBUG: Parent menu exists: ' . ($parent_exists ? 'YES' : 'NO'));
            
            if ($parent_exists) {
                error_log('ARSOL DEBUG: Submenus under parent: ' . count($submenu[$parent_slug]));
            }
        }
    }

    /**
     * Force register post types
     */
    public function force_register_post_types() {
        // Force register project post type
        $project_setup = new \Arsol_Projects_For_Woo\Custom_Post_Types\ProjectPost\Admin\Setup();
        $project_setup->register_post_type();

        // Force register project request post type
        $request_setup = new \Arsol_Projects_For_Woo\Custom_Post_Types\ProjectRequest\Admin\Setup();
        $request_setup->register_post_type();

        // Force register project proposal post type
        $proposal_setup = new \Arsol_Projects_For_Woo\Custom_Post_Types\ProjectProposal\Admin\Setup();
        $proposal_setup->register_post_type();

        // Force flush rewrite rules
        $endpoints = new \Arsol_Projects_For_Woo\Woocommerce\Endpoints();
        $endpoints->force_flush_rewrite_rules();
    }
}
