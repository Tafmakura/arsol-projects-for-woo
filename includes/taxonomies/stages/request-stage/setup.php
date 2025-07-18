<?php
/**
 * Request Stage Taxonomy Setup Class
 *
 * Handles registration and setup of the request stage taxonomy
 *
 * @package Arsol_Projects_For_Woo
 * @since 1.0.0
 */

namespace Arsol_Projects_For_Woo\Taxonomies\Stages\Request_Stage;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Request Stage Taxonomy Setup class
 */
class Setup {

    /**
     * Constructor
     */
    public function __construct() {
        add_action('init', array($this, 'register_request_stage_taxonomy'), 15);
        add_action('init', array($this, 'add_default_request_stages'), 20);
        $this->setup_menu_ordering();
    }

    /**
     * Register request stage taxonomy
     */
    public function register_request_stage_taxonomy() {
        $labels = array(
            'name'              => __('Request Stages', 'arsol-pfw'),
            'singular_name'     => __('Request Stage', 'arsol-pfw'),
            'search_items'      => __('Search Request Stages', 'arsol-pfw'),
            'all_items'         => __('All Request Stages', 'arsol-pfw'),
            'edit_item'         => __('Edit Request Stage', 'arsol-pfw'),
            'update_item'       => __('Update Request Stage', 'arsol-pfw'),
            'add_new_item'      => __('Add New Request Stage', 'arsol-pfw'),
            'new_item_name'     => __('New Request Stage Name', 'arsol-pfw'),
            'menu_name'         => __('Request Stages', 'arsol-pfw'),
        );

        $args = array(
            'hierarchical'      => false,
            'labels'            => $labels,
            'show_ui'           => true,         // Show taxonomy management UI
            'show_admin_column' => true,         // Keep admin columns
            'query_var'         => true,
            'rewrite'           => array('slug' => 'request-stage'),
            'show_in_rest'      => true,
            'meta_box_cb'       => false,        // Remove meta box
            'public'            => false,        // Hide from frontend
            'publicly_queryable' => false,      // Not queryable on frontend
        );

        $result = register_taxonomy('arsol-pfw-request-stage', 'arsol-pfw-request', $args);

        // Debug logging
        if (function_exists('error_log')) {
            if (is_wp_error($result)) {
                error_log('ARSOL DEBUG: Failed to register arsol-pfw-request-stage taxonomy: ' . $result->get_error_message());
            } else {
                error_log('ARSOL DEBUG: Successfully registered arsol-pfw-request-stage taxonomy');
            }
        }
    }

    /**
     * Add default request stages
     */
    public function add_default_request_stages() {
        $default_stages = array(
            'draft'             => 'Draft',
            'submitted'         => 'Submitted',
            'review'            => 'Under Review',
            'revision'          => 'Revision Required',
            'approved'          => 'Approved',
            'cancelled'         => 'Cancelled'
        );

        foreach ($default_stages as $slug => $name) {
            if (!term_exists($slug, 'arsol-pfw-request-stage')) {
                $result = wp_insert_term($name, 'arsol-pfw-request-stage', array('slug' => $slug));
                
                if (is_wp_error($result)) {
                    error_log('ARSOL DEBUG: Failed to create request stage "' . $slug . '": ' . $result->get_error_message());
                } else {
                    error_log('ARSOL DEBUG: Created request stage: ' . $slug);
                }
            }
        }
    }

    /**
     * Get all request stages
     * 
     * @return array
     */
    public static function get_request_stages() {
        $terms = get_terms(array(
            'taxonomy'   => 'arsol-pfw-request-stage',
            'hide_empty' => false,
            'orderby'    => 'name',
            'order'      => 'ASC',
        ));

        if (is_wp_error($terms)) {
            return array();
        }

        return $terms;
    }

    /**
     * Get request stage by slug
     * 
     * @param string $slug
     * @return WP_Term|false
     */
    public static function get_request_stage_by_slug($slug) {
        $term = get_term_by('slug', $slug, 'arsol-pfw-request-stage');
        return $term ? $term : false;
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
