<?php
/**
 * Project Stage Taxonomy Setup Class
 *
 * Handles registration and setup of the project stage taxonomy
 *
 * @package Arsol_Projects_For_Woo
 * @since 1.0.0
 */

namespace Arsol_Projects_For_Woo\Taxonomies\ProjectStage;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Project Stage Taxonomy Setup class
 */
class Taxonomies_Project_Stage_Setup {

    /**
     * Constructor
     */
    public function __construct() {
        add_action('init', array($this, 'register_project_stage_taxonomy'), 15);
        add_action('init', array($this, 'add_default_project_stages'), 20);
    }

    /**
     * Register project stage taxonomy
     */
    public function register_project_stage_taxonomy() {
        $labels = array(
            'name'              => __('Project Stages', 'arsol-pfw'),
            'singular_name'     => __('Project Stage', 'arsol-pfw'),
            'search_items'      => __('Search Project Stages', 'arsol-pfw'),
            'all_items'         => __('All Project Stages', 'arsol-pfw'),
            'edit_item'         => __('Edit Project Stage', 'arsol-pfw'),
            'update_item'       => __('Update Project Stage', 'arsol-pfw'),
            'add_new_item'      => __('Add New Project Stage', 'arsol-pfw'),
            'new_item_name'     => __('New Project Stage Name', 'arsol-pfw'),
            'menu_name'         => __('Project Stages', 'arsol-pfw'),
        );

        $args = array(
            'hierarchical'      => false,
            'labels'            => $labels,
            'show_ui'           => true,
            'show_admin_column' => true,
            'query_var'         => true,
            'rewrite'           => array('slug' => 'project-stage'),
            'show_in_rest'      => true,
            'meta_box_cb'       => false,
            'public'            => false,           // Hide from frontend
            'publicly_queryable' => false,         // Not queryable on frontend
            'show_in_menu'      => true,            // Show in admin menu
        );

        $result = register_taxonomy('arsol-pfw-project-stage', 'arsol-pfw-project', $args);

        // Debug logging
        if (function_exists('error_log')) {
            if (is_wp_error($result)) {
                error_log('ARSOL DEBUG: Failed to register arsol-pfw-project-stage taxonomy: ' . $result->get_error_message());
            } else {
                error_log('ARSOL DEBUG: Successfully registered arsol-pfw-project-stage taxonomy');
            }
        }
    }

    /**
     * Add default project stages (copied from existing project status setup)
     */
    public function add_default_project_stages() {
        $default_stages = array(
            'not-started' => 'Not Started',
            'in-progress' => 'In Progress',
            'on-hold'     => 'On Hold',
            'completed'   => 'Completed',
            'cancelled'   => 'Cancelled'
        );

        foreach ($default_stages as $slug => $name) {
            if (!term_exists($slug, 'arsol-pfw-project-stage')) {
                $result = wp_insert_term($name, 'arsol-pfw-project-stage', array('slug' => $slug));
                
                if (is_wp_error($result)) {
                    error_log('ARSOL DEBUG: Failed to create project stage "' . $slug . '": ' . $result->get_error_message());
                } else {
                    error_log('ARSOL DEBUG: Created project stage: ' . $slug);
                }
            }
        }
    }

    /**
     * Get all project stages
     * 
     * @return array
     */
    public static function get_project_stages() {
        $terms = get_terms(array(
            'taxonomy'   => 'arsol-pfw-project-stage',
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
     * Get project stage by slug
     * 
     * @param string $slug
     * @return WP_Term|false
     */
    public static function get_project_stage_by_slug($slug) {
        $term = get_term_by('slug', $slug, 'arsol-pfw-project-stage');
        return $term ? $term : false;
    }
}
