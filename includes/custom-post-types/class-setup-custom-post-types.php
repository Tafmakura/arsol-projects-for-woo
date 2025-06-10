<?php

namespace Arsol_Projects_For_Woo\Custom_Post_Types;

if (!defined('ABSPATH')) exit;

class Setup {
    public function __construct() {
        add_action('init', array($this, 'load_cpt_and_taxonomy_classes'));
    }

    public function load_cpt_and_taxonomy_classes() {
        // Load and instantiate Project Request CPT classes
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/custom-post-types/project-request/class-project-request-cpt-admin-setup.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/custom-post-types/project-request/class-project-request-cpt-admin-requests.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/custom-post-types/project-request/class-project-request-cpt-admin-request.php';
        
        new \Arsol_Projects_For_Woo\Custom_Post_Types\ProjectRequest\Admin\Setup();
        new \Arsol_Projects_For_Woo\Custom_Post_Types\ProjectRequest\Admin\Requests();
        new \Arsol_Projects_For_Woo\Custom_Post_Types\ProjectRequest\Admin\Request();
        
        // Load and instantiate Project Proposal CPT classes
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/custom-post-types/project-proposal/class-project-proposal-cpt-admin-setup.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/custom-post-types/project-proposal/class-project-proposal-cpt-admin-proposal.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/custom-post-types/project-proposal/class-project-proposal-cpt-admin-proposal-budget.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/custom-post-types/project-proposal/class-project-proposal-cpt-admin-proposal-invoice.php';
        
        new \Arsol_Projects_For_Woo\Custom_Post_Types\ProjectProposal\Admin\Setup();
        new \Arsol_Projects_For_Woo\Custom_Post_Types\ProjectProposal\Admin\Proposal();
        new \Arsol_Projects_For_Woo\Custom_Post_Types\ProjectProposal\Admin\Proposal_Budget();
        new \Arsol_Projects_For_Woo\Custom_Post_Types\ProjectProposal\Admin\Proposal_Invoice();

        // Load and instantiate Project CPT classes
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/custom-post-types/project/class-project-cpt-admin-setup.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/custom-post-types/project/class-project-cpt-admin-projects.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/custom-post-types/project/class-project-cpt-admin-project.php';
        
        new \Arsol_Projects_For_Woo\Custom_Post_Types\Project\Admin\Setup();
        new \Arsol_Projects_For_Woo\Custom_Post_Types\Project\Admin\Projects();
        new \Arsol_Projects_For_Woo\Custom_Post_Types\Project\Admin\Project();
    }
}