<?php

namespace Arsol_Projects_For_Woo\Custom_Post_Types;

if (!defined('ABSPATH')) exit;

class Setup {
    public function __construct() {
        add_action('init', array($this, 'register_taxonomies'));
        add_action('init', array($this, 'load_cpt_and_taxonomy_classes'));
    }

    public function register_taxonomies() {
        // Register Project Status Taxonomy
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/taxonomies/class-project-status-taxonomy.php';
        \Arsol_Projects_For_Woo\Taxonomies\Project_Status_Taxonomy::register();
        
        // Register Review Status Taxonomy
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/taxonomies/class-review-status-taxonomy.php';
        \Arsol_Projects_For_Woo\Taxonomies\Review_Status_Taxonomy::register();
    }

    public function load_cpt_and_taxonomy_classes() {
        // Admin-specific hooks for Project Proposal CPT
        if (is_admin()) {
            require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/custom-post-types/project-proposal/class-project-proposal-cpt-admin-setup.php';
            require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/custom-post-types/project-proposal/class-project-proposal-cpt-admin-proposal.php';
            require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/custom-post-types/project-proposal/class-project-proposal-cpt-admin-proposal-budget.php';
            require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/custom-post-types/project-proposal/class-project-proposal-cpt-admin-proposal-invoice.php';
            
            new \Arsol_Projects_For_Woo\Custom_Post_Types\ProjectProposal\Admin\Setup();
            new \Arsol_Projects_For_Woo\Custom_Post_Types\ProjectProposal\Admin\Proposal();
            new \Arsol_Projects_For_Woo\Custom_Post_Types\ProjectProposal\Admin\Proposal_Budget();
            new \Arsol_Projects_For_Woo\Custom_Post_Types\ProjectProposal\Admin\Proposal_Invoice();
        }

        // Admin-specific hooks for Project CPT
        if (is_admin()) {
            require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/custom-post-types/project/class-project-cpt-admin-setup.php';
            require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/custom-post-types/project/class-project-cpt-admin-projects.php';
            require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/custom-post-types/project/class-project-cpt-admin-project.php';
            
            new \Arsol_Projects_For_Woo\Custom_Post_Types\Project\Admin\Setup();
            new \Arsol_Projects_For_Woo\Custom_Post_Types\Project\Admin\Projects();
            new \Arsol_Projects_For_Woo\Custom_Post_Types\Project\Admin\Project();
        }

        // Frontend-specific hooks for Project Proposal CPT
        if (!is_admin()) {
            // ...
        }

        // Frontend-specific hooks for Project CPT
        if (!is_admin()) {
            // ...
        }
    }
}