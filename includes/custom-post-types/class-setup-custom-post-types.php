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
        // Main CPT Classes
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/custom-post-types/project/class-project-cpt.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/custom-post-types/project-request/class-project-request-cpt.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/custom-post-types/project-proposal/class-project-proposal-cpt.php';
        
        // Collection Management Classes
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/custom-post-types/project/class-projects-cpt.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/custom-post-types/project-request/class-project-requests-cpt.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/custom-post-types/project-proposal/class-project-proposals-cpt.php';
        
        // Custom Post Types
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/custom-post-types/project/class-project-cpt-setup.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/custom-post-types/project/class-project-cpt-admin-project.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/custom-post-types/project/class-project-cpt-admin-projects.php';
        
        // Project Request CPT
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/custom-post-types/project-request/class-project-request-cpt-setup.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/custom-post-types/project-request/class-project-request-cpt-admin-request.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/custom-post-types/project-request/class-project-request-cpt-admin-requests.php';
        
        // Project Proposal CPT
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/custom-post-types/project-proposal/class-project-proposal-cpt-setup.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/custom-post-types/project-proposal/class-project-proposal-cpt-admin-proposal.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/custom-post-types/project-proposal/class-project-proposal-cpt-admin-proposals.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/custom-post-types/project-proposal/class-project-proposal-cpt-admin-proposal-quotation.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/custom-post-types/project-proposal/class-project-proposal-cpt-admin-proposal-budget.php';
    }

    private function instantiate_classes() {
        // Main CPT Classes - These handle all component initialization
        new \Arsol_Projects_For_Woo\Custom_Post_Types\Project\Project_CPT();
        new \Arsol_Projects_For_Woo\Custom_Post_Types\ProjectRequest\Project_Request_CPT();
        new \Arsol_Projects_For_Woo\Custom_Post_Types\ProjectProposal\Project_Proposal_CPT();
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
