<?php
/**
 * Project Phase Taxonomy Setup Class
 *
 * Handles registration and setup of the project phase taxonomy
 *
 * @package Arsol_Projects_For_Woo
 * @since 1.0.0
 */

namespace Arsol_Projects_For_Woo\Taxonomies\ProjectPhase;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Project Phase Taxonomy Setup class
 */
class Taxonomies_Project_Phase_Setup {

    /**
     * Constructor
     */
    public function __construct() {
        add_action('init', array($this, 'register_project_phase_taxonomy'), 15);
        add_action('init', array($this, 'add_default_project_phases'), 20);
    }

    /**
     * Register project phase taxonomy
     */
    public function register_project_phase_taxonomy() {
        $labels = array(
            'name'              => __('Project Phases', 'arsol-pfw'),
            'singular_name'     => __('Project Phase', 'arsol-pfw'),
            'search_items'      => __('Search Project Phases', 'arsol-pfw'),
            'all_items'         => __('All Project Phases', 'arsol-pfw'),
            'edit_item'         => __('Edit Project Phase', 'arsol-pfw'),
            'update_item'       => __('Update Project Phase', 'arsol-pfw'),
            'add_new_item'      => __('Add New Project Phase', 'arsol-pfw'),
            'new_item_name'     => __('New Project Phase Name', 'arsol-pfw'),
            'menu_name'         => __('Project Phases', 'arsol-pfw'),
        );

        $args = array(
            'hierarchical'      => false,
            'labels'            => $labels,
            'show_ui'           => true,
            'show_admin_column' => true,
            'query_var'         => true,
            'rewrite'           => array('slug' => 'project-phase'),
            'show_in_rest'      => true,
            'meta_box_cb'       => false,
            'public'            => false,           // Hide from frontend
            'publicly_queryable' => false,         // Not queryable on frontend
        );

        $result = register_taxonomy('arsol-pfw-project-phase', array('arsol-pfw-project', 'arsol-pfw-proposal', 'arsol-pfw-request'), $args);

        // Debug logging
        if (function_exists('error_log')) {
            if (is_wp_error($result)) {
                error_log('ARSOL DEBUG: Failed to register arsol-pfw-project-phase taxonomy: ' . $result->get_error_message());
            } else {
                error_log('ARSOL DEBUG: Successfully registered arsol-pfw-project-phase taxonomy');
            }
        }
    }

    /**
     * Add default project phases
     */
    public function add_default_project_phases() {
        $default_phases = array(
            'planning'      => 'Planning',
            'development'   => 'Development',
            'testing'       => 'Testing',
            'deployment'    => 'Deployment',
            'maintenance'   => 'Maintenance'
        );

        foreach ($default_phases as $slug => $name) {
            if (!term_exists($slug, 'arsol-pfw-project-phase')) {
                $result = wp_insert_term($name, 'arsol-pfw-project-phase', array('slug' => $slug));
                
                if (is_wp_error($result)) {
                    error_log('ARSOL DEBUG: Failed to create project phase "' . $slug . '": ' . $result->get_error_message());
                } else {
                    error_log('ARSOL DEBUG: Created project phase: ' . $slug);
                }
            }
        }
    }

    /**
     * Get all project phases
     * 
     * @return array
     */
    public static function get_project_phases() {
        $terms = get_terms(array(
            'taxonomy'   => 'arsol-pfw-project-phase',
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
     * Get project phase by slug
     * 
     * @param string $slug
     * @return WP_Term|false
     */
    public static function get_project_phase_by_slug($slug) {
        $term = get_term_by('slug', $slug, 'arsol-pfw-project-phase');
        return $term ? $term : false;
    }
}
