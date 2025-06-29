<?php
/**
 * Taxonomies Setup Class
 *
 * Handles setup and registration of all custom taxonomies
 *
 * @package Arsol_Projects_For_Woo
 * @since 1.0.0
 */

namespace Arsol_Projects_For_Woo\Taxonomies;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Taxonomies Setup class
 */
class Taxonomies_Setup {

    /**
     * Constructor
     */
    public function __construct() {
        $this->require_files();
        $this->instantiate_classes();
    }

    /**
     * Include necessary taxonomy files
     */
    private function require_files() {
        // Project Phase Taxonomy Classes
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/classes/taxonomies/project-phase/class-taxonomies-project-phase-setup.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/classes/taxonomies/project-phase/class-taxonomies-project-phase-admin.php';
        
        // Project Stage Taxonomy Classes
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/classes/taxonomies/project-stage/class-taxonomies-project-stage-setup.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/classes/taxonomies/project-stage/class-taxonomies-project-stage-admin.php';
        
        // Request Stage Taxonomy Classes
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/classes/taxonomies/request-stage/class-taxonomies-request-stage-setup.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/classes/taxonomies/request-stage/class-taxonomies-request-stage-admin.php';
        
        // Proposal Stage Taxonomy Classes
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/classes/taxonomies/proposal-stage/class-taxonomies-proposal-stage-setup.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/classes/taxonomies/proposal-stage/class-taxonomies-proposal-stage-admin.php';
    }

    /**
     * Instantiate taxonomy classes
     */
    private function instantiate_classes() {
        // Initialize Project Phase Taxonomy
        new ProjectPhase\Taxonomies_Project_Phase_Setup();
        new ProjectPhase\Taxonomies_Project_Phase_Admin();
        
        // Initialize Project Stage Taxonomy
        new ProjectStage\Taxonomies_Project_Stage_Setup();
        new ProjectStage\Taxonomies_Project_Stage_Admin();
        
        // Initialize Request Stage Taxonomy
        new RequestStage\Taxonomies_Request_Stage_Setup();
        new RequestStage\Taxonomies_Request_Stage_Admin();
        
        // Initialize Proposal Stage Taxonomy
        new ProposalStage\Taxonomies_Proposal_Stage_Setup();
        new ProposalStage\Taxonomies_Proposal_Stage_Admin();
    }
}
