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
class Setup {

    /**
     * Constructor
     */
    public function __construct() {
        $this->setup_menu_ordering();
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
        
        if (is_array($menu)) {
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
    }
        
        // The ordering will be based on registration order, which is already correct
        // in our taxonomies setup class (request -> proposal -> project)
    }
}
