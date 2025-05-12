<?php

namespace Arsol_Projects_For_Woo\Custom_Post_Types;

if (!defined('ABSPATH')) {
    exit;
}

class Setup_Custom_Post_Types {
    public function __construct() {
        $this->require_files();
        $this->instantiate_classes();
    }

    private function require_files() {
        // Custom Post Types
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/custom-post-types/project/class-project-cpt-setup.php';
    }

    private function instantiate_classes() {
        new Project\Project_CPT_Setup();
    }
}
