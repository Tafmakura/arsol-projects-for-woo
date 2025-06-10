<?php

namespace Arsol_Projects_For_Woo\Custom_Post_Types\ProjectProposal\Admin;

if (!defined('ABSPATH')) {
    exit;
}

class Setup {
    public function __construct() {
        // Add project proposal post type
        add_action('init', array($this, 'register_post_type'), 15);
        add_action('init', array($this, 'register_review_status_taxonomy'), 15);
        add_action('init', array($this, 'add_default_review_statuses'), 20);
        add_filter('use_block_editor_for_post_type', array($this, 'disable_gutenberg_for_project_proposals'), 10, 2);
        add_filter('wp_dropdown_users_args', array($this, 'modify_author_dropdown'), 10, 2);
        add_action('admin_enqueue_scripts', array($this, 'enqueue_wc_admin_styles'));
    }

    public function register_post_type() {
        // Debug logging
        if (function_exists('error_log')) {
            error_log('ARSOL DEBUG: Registering arsol-pfw-proposal post type');
        }

        $labels = array(
            'name'               => __('Project Proposals', 'arsol-pfw'),
            'singular_name'      => __('Project Proposal', 'arsol-pfw'),
            'add_new'           => __('Add New', 'arsol-pfw'),
            'add_new_item'      => __('Add New Project Proposal', 'arsol-pfw'),
            'edit_item'         => __('Edit Project Proposal', 'arsol-pfw'),
            'new_item'          => __('New Project Proposal', 'arsol-pfw'),
            'search_items'      => __('Search Project Proposals', 'arsol-pfw'),
            'not_found'         => __('No project proposals found', 'arsol-pfw'),
            'not_found_in_trash'=> __('No project proposals found in trash', 'arsol-pfw'),
            'menu_name'         => __('Project Proposals', 'arsol-pfw'),
            'all_items'         => __('All Project Proposals', 'arsol-pfw'),
        );

        // Get base supports array
        $supports = array('title', 'editor', 'excerpt', 'author');
        
        // Add comments support if enabled
        if (\Arsol_Projects_For_Woo\Admin\Settings_General::is_comments_enabled_for_post_type('arsol-pfw-proposal')) {
            $supports[] = 'comments';
        }

        $args = array(
            'labels'              => $labels,
            'public'              => false,
            'publicly_queryable'  => false,
            'show_ui'            => true,
            'show_in_menu'       => false,
            'show_in_nav_menus'  => false,
            'show_in_admin_bar'  => true,
            'menu_position'      => null,
            'menu_icon'          => 'dashicons-portfolio',
            'capability_type'    => array('arsol_project_proposal', 'arsol_project_proposals'),
            'map_meta_cap'       => true,
            'hierarchical'       => false,
            'supports'           => $supports,
            'has_archive'        => false,
            'rewrite'           => false,
            'show_in_rest'      => false,
        );

        $result = register_post_type('arsol-pfw-proposal', $args);
        
        // Debug the result
        if (function_exists('error_log')) {
            if (is_wp_error($result)) {
                error_log('ARSOL DEBUG: Failed to register arsol-pfw-proposal: ' . $result->get_error_message());
            } else {
                error_log('ARSOL DEBUG: Successfully registered arsol-pfw-proposal post type');
            }
        }
    }

    /**
     * Disable Gutenberg for project proposals post type
     */
    public function disable_gutenberg_for_project_proposals($use_block_editor, $post_type) {
        if ($post_type === 'arsol-pfw-proposal') {
            return false;
        }
        return $use_block_editor;
    }

    /**
     * Modify the author dropdown to include only WooCommerce customers
     */
    public function modify_author_dropdown($query_args, $r) {
        if (!is_admin()) {
            return $query_args;
        }

        $screen = get_current_screen();
        if ($screen && $screen->post_type === 'arsol-pfw-proposal') {
            // Get all users who can make purchases
            $query_args['role__in'] = array('customer', 'subscriber');
            $query_args['orderby'] = 'display_name';
            $query_args['order'] = 'ASC';
        }
        return $query_args;
    }

    /**
     * Register project proposal review status taxonomy
     */
    public function register_review_status_taxonomy() {
        $labels = array(
            'name'              => __('Review Statuses', 'arsol-pfw'),
            'singular_name'     => __('Review Status', 'arsol-pfw'),
            'search_items'      => __('Search Review Statuses', 'arsol-pfw'),
            'all_items'         => __('All Review Statuses', 'arsol-pfw'),
            'edit_item'         => __('Edit Review Status', 'arsol-pfw'),
            'update_item'       => __('Update Review Status', 'arsol-pfw'),
            'add_new_item'      => __('Add New Review Status', 'arsol-pfw'),
            'new_item_name'     => __('New Review Status Name', 'arsol-pfw'),
            'menu_name'         => __('Review Statuses', 'arsol-pfw'),
        );

        $args = array(
            'hierarchical'      => false,
            'labels'            => $labels,
            'show_ui'           => true,
            'show_admin_column' => true,
            'query_var'         => true,
            'rewrite'           => array('slug' => 'review-status'),
            'show_in_rest'      => true,
            'meta_box_cb'       => false,
        );

        register_taxonomy('arsol-review-status', 'arsol-pfw-proposal', $args);
    }

    /**
     * Add default review statuses
     */
    public function add_default_review_statuses() {
        $default_statuses = array(
            'under-review'      => 'Under Review',
        );

        foreach ($default_statuses as $slug => $name) {
            if (!term_exists($slug, 'arsol-review-status')) {
                wp_insert_term($name, 'arsol-review-status', array('slug' => $slug));
            }
        }
    }

    /**
     * Enqueue WooCommerce admin styles for project proposal post type
     */
    public function enqueue_wc_admin_styles($hook) {
        global $typenow;
        if ($typenow === 'arsol-pfw-proposal' || (isset($_GET['post_type']) && $_GET['post_type'] === 'arsol-pfw-proposal')) {
            // WooCommerce admin styles
            wp_enqueue_style('woocommerce_admin_styles', WC()->plugin_url() . '/assets/css/admin.css', array(), WC_VERSION);
        }
    }
} 