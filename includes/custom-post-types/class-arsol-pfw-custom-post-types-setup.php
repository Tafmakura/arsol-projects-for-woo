<?php

namespace Arsol_Projects_For_Woo\Custom_Post_Types;

if (!defined('ABSPATH')) {
    exit;
}

class Setup {
    public function __construct() {
        // Main includes setup handles all file includes and instantiations
        // This setup now only handles CPT-specific coordination
        
        // Add manual menu registration as backup
        \add_action('admin_menu', array($this, 'ensure_submenus'), 20);
    }

    /**
     * Ensure submenus are properly registered
     */
    public function ensure_submenus() {
        // Check if parent menu exists
        global $menu, $submenu;
        
        $parent_slug = 'edit.php?post_type=arsol-pfw-project';
        
        // Debug logging
        if (\function_exists('error_log')) {
            $parent_exists = isset($submenu[$parent_slug]);
            error_log('ARSOL DEBUG: Parent menu exists: ' . ($parent_exists ? 'YES' : 'NO'));
            
            if ($parent_exists) {
                error_log('ARSOL DEBUG: Submenus under parent: ' . count($submenu[$parent_slug]));
            }
        }
    }
}
