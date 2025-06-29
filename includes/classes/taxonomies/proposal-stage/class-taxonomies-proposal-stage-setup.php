<?php
/**
 * Proposal Stage Taxonomy Setup Class
 *
 * Handles registration and setup of the proposal stage taxonomy
 *
 * @package Arsol_Projects_For_Woo
 * @since 1.0.0
 */

namespace Arsol_Projects_For_Woo\Taxonomies\ProposalStage;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Proposal Stage Taxonomy Setup class
 */
class Taxonomies_Proposal_Stage_Setup {

    /**
     * Constructor
     */
    public function __construct() {
        add_action('init', array($this, 'register_proposal_stage_taxonomy'), 15);
        add_action('init', array($this, 'add_default_proposal_stages'), 20);
    }

    /**
     * Register proposal stage taxonomy
     */
    public function register_proposal_stage_taxonomy() {
        $labels = array(
            'name'              => __('Proposal Stages', 'arsol-pfw'),
            'singular_name'     => __('Proposal Stage', 'arsol-pfw'),
            'search_items'      => __('Search Proposal Stages', 'arsol-pfw'),
            'all_items'         => __('All Proposal Stages', 'arsol-pfw'),
            'edit_item'         => __('Edit Proposal Stage', 'arsol-pfw'),
            'update_item'       => __('Update Proposal Stage', 'arsol-pfw'),
            'add_new_item'      => __('Add New Proposal Stage', 'arsol-pfw'),
            'new_item_name'     => __('New Proposal Stage Name', 'arsol-pfw'),
            'menu_name'         => __('Proposal Stages', 'arsol-pfw'),
        );

        $args = array(
            'hierarchical'      => false,
            'labels'            => $labels,
            'show_ui'           => false,        // Hide taxonomy management UI
            'show_admin_column' => true,         // Keep admin columns
            'query_var'         => true,
            'rewrite'           => array('slug' => 'proposal-stage'),
            'show_in_rest'      => true,
            'meta_box_cb'       => false,        // Remove meta box
            'show_in_menu'      => false,        // Hide from menus
        );

        $result = register_taxonomy('arsol-pfw-proposal-stage', 'arsol-pfw-proposal', $args);

        // Debug logging
        if (function_exists('error_log')) {
            if (is_wp_error($result)) {
                error_log('ARSOL DEBUG: Failed to register arsol-pfw-proposal-stage taxonomy: ' . $result->get_error_message());
            } else {
                error_log('ARSOL DEBUG: Successfully registered arsol-pfw-proposal-stage taxonomy');
            }
        }
    }

    /**
     * Add default proposal stages (copied from existing proposal status setup)
     */
    public function add_default_proposal_stages() {
        $default_stages = array(
            'processing'        => 'Processing',
            'pending-approval'  => 'Pending Approval',
            'approved'          => 'Approved',
            'rejected'          => 'Rejected'
        );

        foreach ($default_stages as $slug => $name) {
            if (!term_exists($slug, 'arsol-pfw-proposal-stage')) {
                $result = wp_insert_term($name, 'arsol-pfw-proposal-stage', array('slug' => $slug));
                
                if (is_wp_error($result)) {
                    error_log('ARSOL DEBUG: Failed to create proposal stage "' . $slug . '": ' . $result->get_error_message());
                } else {
                    error_log('ARSOL DEBUG: Created proposal stage: ' . $slug);
                }
            }
        }
    }

    /**
     * Get all proposal stages
     * 
     * @return array
     */
    public static function get_proposal_stages() {
        $terms = get_terms(array(
            'taxonomy'   => 'arsol-pfw-proposal-stage',
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
     * Get proposal stage by slug
     * 
     * @param string $slug
     * @return WP_Term|false
     */
    public static function get_proposal_stage_by_slug($slug) {
        $term = get_term_by('slug', $slug, 'arsol-pfw-proposal-stage');
        return $term ? $term : false;
    }
}
