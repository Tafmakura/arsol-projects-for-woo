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
        // Project Core Classes
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/custom-post-types/project/class-arsol-pfw-cpt-project.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/custom-post-types/project-request/class-arsol-pfw-cpt-request.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/custom-post-types/project-proposal/class-arsol-pfw-cpt-proposal.php';
        
        // Project Collection Classes
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/custom-post-types/project/class-arsol-pfw-cpt-project-list.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/custom-post-types/project-request/class-arsol-pfw-cpt-request-list.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/custom-post-types/project-proposal/class-arsol-pfw-cpt-proposal-list.php';
        
        // Project Setup Classes
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/custom-post-types/project/class-arsol-pfw-cpt-project-setup.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/custom-post-types/project/class-arsol-pfw-cpt-project-admin.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/custom-post-types/project/class-arsol-pfw-cpt-project-admin-list.php';
        
        // Project Request Setup Classes
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/custom-post-types/project-request/class-arsol-pfw-cpt-request-setup.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/custom-post-types/project-request/class-arsol-pfw-cpt-request-admin.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/custom-post-types/project-request/class-arsol-pfw-cpt-request-admin-list.php';
        
        // Project Proposal Setup Classes
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/custom-post-types/project-proposal/class-arsol-pfw-cpt-proposal-setup.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/custom-post-types/project-proposal/class-arsol-pfw-cpt-proposal-admin.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/custom-post-types/project-proposal/class-arsol-pfw-cpt-proposal-admin-list.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/custom-post-types/project-proposal/class-arsol-pfw-cpt-proposal-admin-quotation.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/custom-post-types/project-proposal/class-arsol-pfw-cpt-proposal-admin-budget.php';
    }

    private function instantiate_classes() {
        // Project CPT
        new \Arsol_Projects_For_Woo\Custom_Post_Types\Project\Admin\Setup();
        new \Arsol_Projects_For_Woo\Custom_Post_Types\Project\Admin\Projects();
        new \Arsol_Projects_For_Woo\Custom_Post_Types\Project\Admin\Project();
        
        // Project Request CPT
        new \Arsol_Projects_For_Woo\Custom_Post_Types\ProjectRequest\Admin\Setup();
        new \Arsol_Projects_For_Woo\Custom_Post_Types\ProjectRequest\Admin\Request();
        new \Arsol_Projects_For_Woo\Custom_Post_Types\ProjectRequest\Admin\Requests();
        
        // Project Proposal CPT
        new \Arsol_Projects_For_Woo\Custom_Post_Types\ProjectProposal\Admin\Setup();
        new \Arsol_Projects_For_Woo\Custom_Post_Types\ProjectProposal\Admin\Proposal();
        new \Arsol_Projects_For_Woo\Custom_Post_Types\ProjectProposal\Admin\Proposals();
        new \Arsol_Projects_For_Woo\Custom_Post_Types\ProjectProposal\Admin\Proposal_Quotation();
        new \Arsol_Projects_For_Woo\Custom_Post_Types\ProjectProposal\Admin\Proposal_Budget();
    }

    /**
     * Ensure submenus are properly registered
     */
    public function ensure_submenus() {
        // Check if parent menu exists
        global $menu, $submenu;
        
        $parent_slug = 'edit.php?post_type=arsol-pfw-project';
        
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
