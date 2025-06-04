<?php

namespace Arsol_Projects_For_Woo\Custom_Post_Types\ProjectPost\Admin;

if (!defined('ABSPATH')) {
    exit;
}

class Setup {
    public function __construct() {
        add_action('init', array($this, 'register_post_type'));
        add_action('init', array($this, 'register_project_status_taxonomy'));
        add_action('init', array($this, 'add_default_project_statuses'));
        add_filter('use_block_editor_for_post_type', array($this, 'disable_gutenberg_for_projects'), 10, 2);
        add_filter('wp_dropdown_users_args', array($this, 'modify_author_dropdown'), 10, 2);
        add_action('template_redirect', array($this, 'handle_project_template_redirect'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_wc_admin_styles'));
    }

    public function register_post_type() {
        // Debug logging
        if (function_exists('error_log')) {
            error_log('ARSOL DEBUG: Registering arsol-project post type (PARENT)');
        }

        $labels = array(
            'name'               => __('Arsol Projects', 'arsol-projects-for-woo'),
            'singular_name'      => __('Project', 'arsol-projects-for-woo'),
            'add_new'           => __('Add New', 'arsol-projects-for-woo'),
            'add_new_item'      => __('Add New Project', 'arsol-projects-for-woo'),
            'edit_item'         => __('Edit Project', 'arsol-projects-for-woo'),
            'new_item'          => __('New Project', 'arsol-projects-for-woo'),
            'view_item'         => __('View Project', 'arsol-projects-for-woo'),
            'search_items'      => __('Search Projects', 'arsol-projects-for-woo'),
            'not_found'         => __('No projects found', 'arsol-projects-for-woo'),
            'not_found_in_trash'=> __('No projects found in trash', 'arsol-projects-for-woo'),
            'menu_name'         => __('Arsol Projects for Woo', 'arsol-projects-for-woo'),
            'all_items'         => __('All Projects', 'arsol-projects-for-woo'),
        );

        $args = array(
            'labels'              => $labels,
            'public'              => true,
            'publicly_queryable'  => true, // Enable for comment handling
            'show_ui'            => true,
            'show_in_menu'       => true,
            'show_in_nav_menus'  => true,
            'show_in_admin_bar'  => true,
            'menu_position'      => 5,
            'menu_icon'          => 'dashicons-clipboard',
            'capability_type'    => 'post',
            'hierarchical'       => false,
            'supports'           => array('title', 'editor', 'excerpt', 'author', 'comments'),
            'has_archive'        => false,
            'rewrite'           => array('slug' => 'project', 'with_front' => false),
            'show_in_rest'      => false,
        );

        $result = register_post_type('arsol-project', $args);
        
        // Debug the result
        if (function_exists('error_log')) {
            if (is_wp_error($result)) {
                error_log('ARSOL DEBUG: Failed to register arsol-project: ' . $result->get_error_message());
            } else {
                error_log('ARSOL DEBUG: Successfully registered arsol-project post type (PARENT)');
            }
        }
    }

    /**
     * Disable Gutenberg for projects post type
     */
    public function disable_gutenberg_for_projects($use_block_editor, $post_type) {
        if ($post_type === 'arsol-project') {
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
        if ($screen && $screen->post_type === 'arsol-project') {
            // Get all users who can make purchases
            $query_args['role__in'] = array('customer', 'subscriber');
            $query_args['orderby'] = 'display_name';
            $query_args['order'] = 'ASC';
        }
        return $query_args;
    }

    /**
     * Handle template redirect for project pages
     */
    public function handle_project_template_redirect() {
        if (is_singular('arsol-project')) {
            $project_id = get_the_ID();
            $user_id = get_current_user_id();
            
            // Check if user can view the project
            if (!\Arsol_Projects_For_Woo\Woocommerce\Endpoints::user_can_view_project($user_id, $project_id)) {
                // Simple redirect to projects list
                wp_redirect(wc_get_account_endpoint_url('projects'));
                exit;
            }
            
            // Redirect to the project overview page in the account area
            wp_redirect(wc_get_account_endpoint_url('project-overview/' . $project_id));
            exit;
        }
    }

    /**
     * Register project status taxonomy
     */
    public function register_project_status_taxonomy() {
        $labels = array(
            'name'              => __('Project Statuses', 'arsol-projects-for-woo'),
            'singular_name'     => __('Project Status', 'arsol-projects-for-woo'),
            'search_items'      => __('Search Project Statuses', 'arsol-projects-for-woo'),
            'all_items'         => __('All Project Statuses', 'arsol-projects-for-woo'),
            'edit_item'         => __('Edit Project Status', 'arsol-projects-for-woo'),
            'update_item'       => __('Update Project Status', 'arsol-projects-for-woo'),
            'add_new_item'      => __('Add New Project Status', 'arsol-projects-for-woo'),
            'new_item_name'     => __('New Project Status Name', 'arsol-projects-for-woo'),
            'menu_name'         => __('Project Statuses', 'arsol-projects-for-woo'),
        );

        $args = array(
            'hierarchical'      => false,
            'labels'            => $labels,
            'show_ui'           => true,
            'show_admin_column' => true,
            'query_var'         => true,
            'rewrite'           => array('slug' => 'project-status'),
            'show_in_rest'      => true,
            'meta_box_cb'       => false,
        );

        register_taxonomy('arsol-project-status', 'arsol-project', $args);
    }

    /**
     * Add default project statuses
     */
    public function add_default_project_statuses() {
        $default_statuses = array(
            'not-started' => 'Not Started',
            'in-progress' => 'In Progress',
            'on-hold'     => 'On Hold',
            'completed'   => 'Completed',
            'cancelled'   => 'Cancelled'
        );

        foreach ($default_statuses as $slug => $name) {
            if (!term_exists($slug, 'arsol-project-status')) {
                wp_insert_term($name, 'arsol-project-status', array('slug' => $slug));
            }
        }
    }

    /**
     * Enqueue WooCommerce admin styles for arsol-project post type
     */
    public function enqueue_wc_admin_styles($hook) {
        global $typenow;
        if ($typenow === 'arsol-project' || (isset($_GET['post_type']) && $_GET['post_type'] === 'arsol-project')) {
            // WooCommerce admin styles
            wp_enqueue_style('woocommerce_admin_styles', WC()->plugin_url() . '/assets/css/admin.css', array(), WC_VERSION);
        }
    }
}
