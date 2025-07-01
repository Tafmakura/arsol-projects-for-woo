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

    /**
     * Setup admin menu ordering for stage taxonomies
     */
    private function setup_menu_ordering() {
        // Use admin_menu with high priority to run after taxonomies are registered
        add_action('admin_menu', array($this, 'reorder_taxonomy_menus'), 999);
    }

    /**
     * Reorder taxonomy menus to show in desired order:
     * 1. Request Stages
     * 2. Proposal Stages  
     * 3. Project Stages
     */
    public function reorder_taxonomy_menus() {
        global $submenu, $menu;
        
        // The taxonomy menu items are typically added as top-level menu items
        // We need to find and reorder them
        
        $stage_menus = array();
        $other_menus = array();
        
        // Extract stage taxonomy menus
        foreach ($menu as $key => $menu_item) {
            if (isset($menu_item[2])) {
                $menu_slug = $menu_item[2];
                
                // Check if this is one of our stage taxonomy menus
                if ($menu_slug === 'edit-tags.php?taxonomy=arsol-pfw-request-stage') {
                    $stage_menus['request'] = array('key' => $key, 'item' => $menu_item);
                    unset($menu[$key]);
                } elseif ($menu_slug === 'edit-tags.php?taxonomy=arsol-pfw-proposal-stage') {
                    $stage_menus['proposal'] = array('key' => $key, 'item' => $menu_item);
                    unset($menu[$key]);
                } elseif ($menu_slug === 'edit-tags.php?taxonomy=arsol-pfw-project-stage') {
                    $stage_menus['project'] = array('key' => $key, 'item' => $menu_item);
                    unset($menu[$key]);
                }
            }
        }
        
        // Find a good position to insert our ordered menus
        // Let's put them after the custom post types (around position 20-30)
        $insert_position = 25;
        
        // Ensure position is available
        while (isset($menu[$insert_position])) {
            $insert_position++;
        }
        
        // Add stage menus in desired order
        if (isset($stage_menus['request'])) {
            $menu[$insert_position] = $stage_menus['request']['item'];
            $insert_position++;
        }
        
        if (isset($stage_menus['proposal'])) {
            $menu[$insert_position] = $stage_menus['proposal']['item'];
            $insert_position++;
        }
        
        if (isset($stage_menus['project'])) {
            $menu[$insert_position] = $stage_menus['project']['item'];
            $insert_position++;
        }
        
        // Sort menu by key to maintain order
        ksort($menu);
    }
}
