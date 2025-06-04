<?php

namespace Arsol_Projects_For_Woo\Custom_Post_Types;

if (!defined('ABSPATH')) {
    exit;
}

class Setup {
    public function __construct() {
        $this->require_files();
        $this->instantiate_classes();
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
}
