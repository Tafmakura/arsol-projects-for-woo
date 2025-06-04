<?php

namespace Arsol_Projects_For_Woo\Custom_Post_Types\ProjectProposal\Admin;

if (!defined('ABSPATH')) {
    exit;
}

class Setup {
    public function __construct() {
        echo 'TEST';
        // Add project proposal post type
        add_action('init', array($this, 'register_post_type'), 15);
        add_action('init', array($this, 'register_proposal_status_taxonomy'), 15);
        add_action('init', array($this, 'add_default_proposal_statuses'), 20);
        add_filter('use_block_editor_for_post_type', array($this, 'disable_gutenberg_for_project_proposals'), 10, 2);
        add_filter('wp_dropdown_users_args', array($this, 'modify_author_dropdown'), 10, 2);
        add_action('admin_enqueue_scripts', array($this, 'enqueue_wc_admin_styles'));
    }

    public function register_post_type() {
        $labels = array(
            'name'               => __('Project Proposals', 'arsol-pfw'),
            'singular_name'      => __('Project Proposal', 'arsol-pfw'),
            'add_new'           => __('Add New', 'arsol-pfw'),
            'add_new_item'      => __('Add New Project Proposal', 'arsol-pfw'),
            'edit_item'         => __('Edit Project Proposal', 'arsol-pfw'),
            'new_item'          => __('New Project Proposal', 'arsol-pfw'),
            'view_item'         => __('View Project Proposal', 'arsol-pfw'),
            'search_items'      => __('Search Project Proposals', 'arsol-pfw'),
            'not_found'         => __('No project proposals found', 'arsol-pfw'),
            'not_found_in_trash'=> __('No project proposals found in trash', 'arsol-pfw'),
            'menu_name'         => __('Project Proposals', 'arsol-pfw'),
            'all_items'         => __('All Project Proposals', 'arsol-pfw'),
        );

        $args = array(
            'labels'              => $labels,
            'public'              => true,
            'publicly_queryable'  => true,
            'show_ui'            => true,
            'show_in_menu'       => 'edit.php?post_type=arsol-project',
            'show_in_nav_menus'  => true,
            'show_in_admin_bar'  => true,
            'menu_position'      => null,
            'menu_icon'          => 'dashicons-portfolio',
            'capability_type'    => 'post',
            'hierarchical'       => false,
            'supports'           => array('title', 'editor', 'excerpt', 'author', 'comments'),
            'has_archive'        => false,
            'rewrite'           => array('slug' => 'project-proposal', 'with_front' => false),
            'show_in_rest'      => false,
        );

        register_post_type('arsol-project-proposal', $args);
    }

    /**
     * Disable Gutenberg for project proposals post type
     */
    public function disable_gutenberg_for_project_proposals($use_block_editor, $post_type) {
        if ($post_type === 'arsol-project-proposal') {
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
        if ($screen && $screen->post_type === 'arsol-project-proposal') {
            // Get all users who can make purchases
            $query_args['role__in'] = array('customer', 'subscriber');
            $query_args['orderby'] = 'display_name';
            $query_args['order'] = 'ASC';
        }
        return $query_args;
    }

    /**
     * Register project proposal status taxonomy
     */
    public function register_proposal_status_taxonomy() {
        $labels = array(
            'name'              => __('Proposal Statuses', 'arsol-pfw'),
            'singular_name'     => __('Proposal Status', 'arsol-pfw'),
            'search_items'      => __('Search Proposal Statuses', 'arsol-pfw'),
            'all_items'         => __('All Proposal Statuses', 'arsol-pfw'),
            'edit_item'         => __('Edit Proposal Status', 'arsol-pfw'),
            'update_item'       => __('Update Proposal Status', 'arsol-pfw'),
            'add_new_item'      => __('Add New Proposal Status', 'arsol-pfw'),
            'new_item_name'     => __('New Proposal Status Name', 'arsol-pfw'),
            'menu_name'         => __('Proposal Statuses', 'arsol-pfw'),
        );

        $args = array(
            'hierarchical'      => false,
            'labels'            => $labels,
            'show_ui'           => true,
            'show_admin_column' => true,
            'query_var'         => true,
            'rewrite'           => array('slug' => 'proposal-status'),
            'show_in_rest'      => true,
            'meta_box_cb'       => false,
        );

        register_taxonomy('arsol-proposal-status', 'arsol-project-proposal', $args);
    }

    /**
     * Add default proposal statuses
     */
    public function add_default_proposal_statuses() {
        $default_statuses = array(
            'draft'       => 'Draft',
            'submitted'   => 'Submitted',
            'under-review'=> 'Under Review',
            'approved'    => 'Approved',
            'rejected'    => 'Rejected',
            'revised'     => 'Needs Revision'
        );

        foreach ($default_statuses as $slug => $name) {
            if (!term_exists($slug, 'arsol-proposal-status')) {
                wp_insert_term($name, 'arsol-proposal-status', array('slug' => $slug));
            }
        }
    }

    /**
     * Enqueue WooCommerce admin styles for project proposal post type
     */
    public function enqueue_wc_admin_styles($hook) {
        global $typenow;
        if ($typenow === 'arsol-project-proposal' || (isset($_GET['post_type']) && $_GET['post_type'] === 'arsol-project-proposal')) {
            // WooCommerce admin styles
            wp_enqueue_style('woocommerce_admin_styles', WC()->plugin_url() . '/assets/css/admin.css', array(), WC_VERSION);
        }
    }
} 