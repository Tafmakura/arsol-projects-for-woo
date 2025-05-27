<?php

namespace Arsol_Projects_For_Woo\Custom_Post_Types\ProjectPost;

if (!defined('ABSPATH')) {
    exit;
}

class Setup {
    public function __construct() {
        add_action('init', array($this, 'register_post_type'));
        add_filter('use_block_editor_for_post_type', array($this, 'disable_gutenberg_for_projects'), 10, 2);
        add_filter('wp_dropdown_users_args', array($this, 'modify_author_dropdown'), 10, 2);
        add_action('do_meta_boxes', array($this, 'move_author_metabox_to_side'));
        add_action('do_meta_boxes', array($this, 'move_excerpt_metabox_to_top'));
        add_action('template_redirect', array($this, 'handle_project_template_redirect'));
    }

    public function register_post_type() {
        $labels = array(
            'name'               => __('Projects', 'arsol-projects-for-woo'),
            'singular_name'      => __('Project', 'arsol-projects-for-woo'),
            'add_new'           => __('Add New', 'arsol-projects-for-woo'),
            'add_new_item'      => __('Add New Project', 'arsol-projects-for-woo'),
            'edit_item'         => __('Edit Project', 'arsol-projects-for-woo'),
            'new_item'          => __('New Project', 'arsol-projects-for-woo'),
            'view_item'         => __('View Project', 'arsol-projects-for-woo'),
            'search_items'      => __('Search Projects', 'arsol-projects-for-woo'),
            'not_found'         => __('No projects found', 'arsol-projects-for-woo'),
            'not_found_in_trash'=> __('No projects found in trash', 'arsol-projects-for-woo'),
            'menu_name'         => __('Projects', 'arsol-projects-for-woo'),
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
            'menu_icon'          => 'dashicons-portfolio',
            'capability_type'    => 'post',
            'hierarchical'       => false,
            'supports'           => array('title', 'editor', 'excerpt', 'author', 'comments'),
            'has_archive'        => false,
            'rewrite'           => array('slug' => 'project', 'with_front' => false),
            'show_in_rest'      => false,
        );

        register_post_type('project', $args);
    }

    /**
     * Disable Gutenberg for projects post type
     */
    public function disable_gutenberg_for_projects($use_block_editor, $post_type) {
        if ($post_type === 'project') {
            return false;
        }
        return $use_block_editor;
    }

    /**
     * Modify the author dropdown to include WooCommerce customers
     */
    public function modify_author_dropdown($query_args, $r) {
        if (!is_admin()) {
            return $query_args;
        }

        $screen = get_current_screen();
        if ($screen && $screen->post_type === 'project') {
            // Get customers who have made orders
            $customer_ids = get_users(array(
                'role'    => 'customer',
                'fields'  => 'ID',
            ));

            $query_args['include'] = $customer_ids;
            $query_args['orderby'] = 'display_name';
            $query_args['order'] = 'ASC';
        }
        return $query_args;
    }

    /**
     * Move author metabox to side
     */
    public function move_author_metabox_to_side() {
        remove_meta_box('authordiv', 'project', 'normal');
        add_meta_box(
            'authordiv',
            __('Customer', 'arsol-projects-for-woo'),
            'post_author_meta_box',
            'project',
            'side',
            'high'
        );
    }

    /**
     * Move excerpt metabox to the top of the editor
     */
    public function move_excerpt_metabox_to_top() {
        // Remove the default excerpt metabox
        remove_meta_box('postexcerpt', 'project', 'normal');
        
        // Add it back with a new title but in the same position
        add_meta_box(
            'postexcerpt',
            __('Project Summary', 'arsol-projects-for-woo'), // Changed name
            'post_excerpt_meta_box',
            'project',
            'normal', // Keep in normal position
            'default' // Use default priority
        );
    }

    /**
     * Handle template redirect for project pages
     */
    public function handle_project_template_redirect() {
        if (is_singular('project')) {
            $project_id = get_the_ID();
            $user_id = get_current_user_id();
            
            // Check if user can view the project
            if (!\Arsol_Projects_For_Woo\Endpoints::user_can_view_project($user_id, $project_id)) {
                // Simple redirect to projects list
                wp_redirect(wc_get_account_endpoint_url('projects'));
                exit;
            }
            
            // Redirect to the project overview page in the account area
            wp_redirect(wc_get_account_endpoint_url('project-overview/' . $project_id));
            exit;
        }
    }
}
