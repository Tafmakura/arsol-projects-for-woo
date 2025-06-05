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
    }

    private function require_files() {
        // Custom Post Types
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/custom-post-types/project/class-project-cpt-admin-setup.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/custom-post-types/project/class-project-cpt-admin-project.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/custom-post-types/project/class-project-cpt-admin-projects.php';
        
        // Project Request CPT
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/custom-post-types/project-request/class-project-request-cpt-admin-setup.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/custom-post-types/project-request/class-project-request-cpt-admin-request.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/custom-post-types/project-request/class-project-request-cpt-admin-requests.php';
        
        // Project Proposal CPT
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/custom-post-types/project-proposal/class-project-proposal-cpt-admin-setup.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/custom-post-types/project-proposal/class-project-proposal-cpt-admin-proposal.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/custom-post-types/project-proposal/class-project-proposal-cpt-admin-proposals.php';
        
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/classes/class-woocommerce-endpoints.php';
    }

    private function instantiate_classes() {
        // Project CPT
        new \Arsol_Projects_For_Woo\Custom_Post_Types\ProjectPost\Admin\Setup();
        new \Arsol_Projects_For_Woo\Custom_Post_Types\ProjectPost\Admin\Projects();
        new \Arsol_Projects_For_Woo\Custom_Post_Types\ProjectPost\Admin\Project();
        
        // Project Request CPT
        new \Arsol_Projects_For_Woo\Custom_Post_Types\ProjectRequest\Admin\Setup();
        new \Arsol_Projects_For_Woo\Custom_Post_Types\ProjectRequest\Admin\Request();
        new \Arsol_Projects_For_Woo\Custom_Post_Types\ProjectRequest\Admin\Requests();
        
        // Project Proposal CPT
        new \Arsol_Projects_For_Woo\Custom_Post_Types\ProjectProposal\Admin\Setup();
        new \Arsol_Projects_For_Woo\Custom_Post_Types\ProjectProposal\Admin\Proposal();
        new \Arsol_Projects_For_Woo\Custom_Post_Types\ProjectProposal\Admin\Proposals();
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
}
