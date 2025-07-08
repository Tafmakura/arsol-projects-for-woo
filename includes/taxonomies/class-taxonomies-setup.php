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
        $this->setup_menu_ordering();
    }

    /**
     * Include necessary taxonomy files
     */
    private function require_files() {
        // Project Stage Taxonomy Classes
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/taxonomies/project-stage/class-taxonomies-project-stage-setup.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/taxonomies/project-stage/class-taxonomies-project-stage-admin.php';
        
        // Request Stage Taxonomy Classes
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/taxonomies/request-stage/class-taxonomies-request-stage-setup.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/taxonomies/request-stage/class-taxonomies-request-stage-admin.php';
        
        // Proposal Stage Taxonomy Classes
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/taxonomies/proposal-stage/class-taxonomies-proposal-stage-setup.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/taxonomies/proposal-stage/class-taxonomies-proposal-stage-admin.php';
    }

    /**
     * Instantiate taxonomy classes
     */
    private function instantiate_classes() {
        // Initialize Request Stage Taxonomy (first)
        new RequestStage\Taxonomies_Request_Stage_Setup();
        new RequestStage\Taxonomies_Request_Stage_Admin();
        
        // Initialize Proposal Stage Taxonomy (second)
        new ProposalStage\Taxonomies_Proposal_Stage_Setup();
        new ProposalStage\Taxonomies_Proposal_Stage_Admin();
        
        // Initialize Project Stage Taxonomy (third)
        new ProjectStage\Taxonomies_Project_Stage_Setup();
        new ProjectStage\Taxonomies_Project_Stage_Admin();
    }

    /**
     * Setup admin menu ordering for stage taxonomies
     */
    private function setup_menu_ordering() {
        // Use admin_init with lower priority to run after taxonomies are registered
        add_action('admin_init', array($this, 'reorder_taxonomy_menus'), 999);
    }

    /**
     * Reorder taxonomy menus to show in desired order:
     * 1. Request Stages
     * 2. Proposal Stages  
     * 3. Project Stages
     */
    public function reorder_taxonomy_menus() {
        global $menu;
        
        if (!is_admin() || !current_user_can('manage_options')) {
            return;
        }
        
        // Find the positions of our stage taxonomy menus
        $stage_positions = array();
        
        foreach ($menu as $position => $menu_item) {
            if (isset($menu_item[2])) {
                $menu_slug = $menu_item[2];
                
                if ($menu_slug === 'edit-tags.php?taxonomy=arsol-pfw-request-stage') {
                    $stage_positions['request'] = $position;
                } elseif ($menu_slug === 'edit-tags.php?taxonomy=arsol-pfw-proposal-stage') {
                    $stage_positions['proposal'] = $position;
                } elseif ($menu_slug === 'edit-tags.php?taxonomy=arsol-pfw-project-stage') {
                    $stage_positions['project'] = $position;
                }
            }
        }
        
        // If we found the menus, they should automatically appear in WordPress admin
        // The ordering will be based on registration order, which is already correct
        // in our taxonomies setup class (request -> proposal -> project)
    }
}
