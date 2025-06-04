<?php

namespace Arsol_Projects_For_Woo\Custom_Post_Types\ProjectRequest\Admin;

if (!defined('ABSPATH')) {
    exit;
}

class Setup {
    public function __construct() {
        add_action('init', array($this, 'register_post_type'), 15);
        add_action('init', array($this, 'register_request_status_taxonomy'), 15);
        add_action('init', array($this, 'add_default_request_statuses'), 20);
        add_filter('use_block_editor_for_post_type', array($this, 'disable_gutenberg_for_project_requests'), 10, 2);
        add_filter('wp_dropdown_users_args', array($this, 'modify_author_dropdown'), 10, 2);
        add_action('admin_enqueue_scripts', array($this, 'enqueue_wc_admin_styles'));
    }

    public function register_post_type() {
        $labels = array(
            'name'               => __('Project Requests', 'arsol-pfw'),
            'singular_name'      => __('Project Request', 'arsol-pfw'),
            'add_new'           => __('Add New', 'arsol-pfw'),
            'add_new_item'      => __('Add New Project Request', 'arsol-pfw'),
            'edit_item'         => __('Edit Project Request', 'arsol-pfw'),
            'new_item'          => __('New Project Request', 'arsol-pfw'),
            'view_item'         => __('View Project Request', 'arsol-pfw'),
            'search_items'      => __('Search Project Requests', 'arsol-pfw'),
            'not_found'         => __('No project requests found', 'arsol-pfw'),
            'not_found_in_trash'=> __('No project requests found in trash', 'arsol-pfw'),
            'menu_name'         => __('Project Requests', 'arsol-pfw'),
            'all_items'         => __('All Project Requests', 'arsol-pfw'),
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
            'menu_icon'          => 'dashicons-format-chat',
            'capability_type'    => 'post',
            'hierarchical'       => false,
            'supports'           => array('title', 'editor', 'excerpt', 'author', 'comments'),
            'has_archive'        => false,
            'rewrite'           => array('slug' => 'project-request', 'with_front' => false),
            'show_in_rest'      => false,
        );

        register_post_type('arsol-project-request', $args);
    }

    /**
     * Disable Gutenberg for project requests post type
     */
    public function disable_gutenberg_for_project_requests($use_block_editor, $post_type) {
        if ($post_type === 'arsol-project-request') {
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
        if ($screen && $screen->post_type === 'arsol-project-request') {
            // Get all users who can make purchases
            $query_args['role__in'] = array('customer', 'subscriber');
            $query_args['orderby'] = 'display_name';
            $query_args['order'] = 'ASC';
        }
        return $query_args;
    }

    /**
     * Register project request status taxonomy
     */
    public function register_request_status_taxonomy() {
        $labels = array(
            'name'              => __('Request Statuses', 'arsol-pfw'),
            'singular_name'     => __('Request Status', 'arsol-pfw'),
            'search_items'      => __('Search Request Statuses', 'arsol-pfw'),
            'all_items'         => __('All Request Statuses', 'arsol-pfw'),
            'edit_item'         => __('Edit Request Status', 'arsol-pfw'),
            'update_item'       => __('Update Request Status', 'arsol-pfw'),
            'add_new_item'      => __('Add New Request Status', 'arsol-pfw'),
            'new_item_name'     => __('New Request Status Name', 'arsol-pfw'),
            'menu_name'         => __('Request Statuses', 'arsol-pfw'),
        );

        $args = array(
            'hierarchical'      => false,
            'labels'            => $labels,
            'show_ui'           => true,
            'show_admin_column' => true,
            'query_var'         => true,
            'rewrite'           => array('slug' => 'request-status'),
            'show_in_rest'      => true,
            'meta_box_cb'       => false,
        );

        register_taxonomy('arsol-request-status', 'arsol-project-request', $args);
    }

    /**
     * Add default request statuses
     */
    public function add_default_request_statuses() {
        $default_statuses = array(
            'pending'     => 'Pending Review',
            'under-review'=> 'Under Review',
            'approved'    => 'Approved',
            'rejected'    => 'Rejected',
            'cancelled'   => 'Cancelled'
        );

        foreach ($default_statuses as $slug => $name) {
            if (!term_exists($slug, 'arsol-request-status')) {
                wp_insert_term($name, 'arsol-request-status', array('slug' => $slug));
            }
        }
    }

    /**
     * Enqueue WooCommerce admin styles for project request post type
     */
    public function enqueue_wc_admin_styles($hook) {
        global $typenow;
        if ($typenow === 'arsol-project-request' || (isset($_GET['post_type']) && $_GET['post_type'] === 'arsol-project-request')) {
            // WooCommerce admin styles
            wp_enqueue_style('woocommerce_admin_styles', WC()->plugin_url() . '/assets/css/admin.css', array(), WC_VERSION);
        }
    }
} 