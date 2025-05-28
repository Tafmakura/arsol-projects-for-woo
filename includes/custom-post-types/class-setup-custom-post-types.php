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
    }

    private function instantiate_classes() {
        new \Arsol_Projects_For_Woo\Custom_Post_Types\ProjectPost\Admin\Setup();
        new \Arsol_Projects_For_Woo\Custom_Post_Types\ProjectPost\Admin\Projects();
        new \Arsol_Projects_For_Woo\Custom_Post_Types\ProjectPost\Admin\Project();
    }
}
