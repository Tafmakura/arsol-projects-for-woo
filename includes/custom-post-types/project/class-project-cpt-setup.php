<?php

namespace Arsol_Projects_For_Woo\Custom_Post_Types\Project\Admin;

if (!defined('ABSPATH')) {
    exit;
}

class Setup {
    public function __construct() {
        add_action('init', array($this, 'register_post_type'), 15);
        add_filter('use_block_editor_for_post_type', array($this, 'disable_gutenberg_for_projects'), 10, 2);
        add_filter('wp_dropdown_users_args', array($this, 'modify_author_dropdown'), 10, 2);
        add_action('template_redirect', array($this, 'handle_project_template_redirect'));

        add_action('add_meta_boxes', array($this, 'remove_publish_metabox'));
        
        // Add header container after title
        add_action('edit_form_after_title', array($this, 'render_project_header_container'));
        
        // Hook project details into header
        add_action('arsol_project_details_content', array($this, 'render_project_details_content'));
        add_action('arsol_project_proposal_content', array($this, 'render_project_proposal_content'));
    }

    /**
     * Register the Projects post type
     */
    public function register_post_type() {
        // Debug logging
        if (function_exists('error_log')) {
            error_log('ARSOL DEBUG: Registering arsol-pfw-project post type (PARENT)');
        }

        $labels = array(
            'name'               => __('Projects', 'arsol-pfw'),
            'singular_name'      => __('Project', 'arsol-pfw'),
            'add_new'           => __('Add New', 'arsol-pfw'),
            'add_new_item'      => __('Add New Project', 'arsol-pfw'),
            'edit_item'         => __('Edit Project', 'arsol-pfw'),
            'new_item'          => __('New Project', 'arsol-pfw'),
            'view_item'         => __('View Project', 'arsol-pfw'),
            'search_items'      => __('Search Projects', 'arsol-pfw'),
            'not_found'         => __('No projects found', 'arsol-pfw'),
            'not_found_in_trash'=> __('No projects found in trash', 'arsol-pfw'),
            'menu_name'         => __('Projects', 'arsol-pfw'),
            'all_items'         => __('All Projects', 'arsol-pfw'),
        );

        // Get base supports array
        $supports = array('title');
        
        // Add comments support if enabled
        if (\Arsol_Projects_For_Woo\Admin\Settings_General::is_comments_enabled_for_post_type('arsol-pfw-project')) {
            $supports[] = 'comments';
        }

        $args = array(
            'labels'              => $labels,
            'public'              => false,
            'publicly_queryable'  => false,
            'show_ui'            => true,
            'show_in_menu'       => true,
            'show_in_nav_menus'  => true,
            'show_in_admin_bar'  => true,
            'menu_position'      => 56.5,
            'menu_icon'          => 'dashicons-clipboard',
            'capability_type'    => array('arsol_project', 'arsol_projects'),
            'map_meta_cap'       => true,
            'hierarchical'       => false,
            'supports'           => $supports,
            'has_archive'        => false,
            'rewrite'           => array('slug' => 'project', 'with_front' => false),
            'show_in_rest'      => false,
        );

        $result = register_post_type('arsol-pfw-project', $args);
        
        // Debug the result
        if (function_exists('error_log')) {
            if (is_wp_error($result)) {
                error_log('ARSOL DEBUG: Failed to register arsol-pfw-project: ' . $result->get_error_message());
            } else {
                error_log('ARSOL DEBUG: Successfully registered arsol-pfw-project post type (PARENT)');
            }
        }
    }

    /**
     * Disable Gutenberg for projects post type
     */
    public function disable_gutenberg_for_projects($use_block_editor, $post_type) {
        if ($post_type === 'arsol-pfw-project') {
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
        if ($screen && $screen->post_type === 'arsol-pfw-project') {
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
        if (is_singular('arsol-pfw-project')) {
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
     * Remove the publish metabox
     */
    public function remove_publish_metabox() {
        remove_meta_box('submitdiv', 'arsol-pfw-project', 'side');
    }

    /**
     * Render project header container
     */
    public function render_project_header_container() {
        global $post;
        
        if (!$post || $post->post_type !== 'arsol-pfw-project') {
            return;
        }
        
        $template_path = ARSOL_PROJECTS_PLUGIN_DIR . 'includes/ui/components/admin/section-edit-active-header.php';
        if (file_exists($template_path)) {
            include $template_path;
        }
    }

    /**
     * Render project details content (placeholder for future use)
     */
    public function render_project_details_content($post) {
        if (!$post || $post->post_type !== 'arsol-pfw-project') {
            return;
        }
        
        // Placeholder for project-specific details
        echo '<p>' . __('Project details will be displayed here.', 'arsol-pfw') . '</p>';
    }

    /**
     * Render project proposal content (original proposal details)
     */
    public function render_project_proposal_content($post) {
        if (!$post || $post->post_type !== 'arsol-pfw-project') {
            return;
        }
        
        $template_path = ARSOL_PROJECTS_PLUGIN_DIR . 'includes/ui/components/admin/section-edit-active-header-column-2.php';
        if (file_exists($template_path)) {
            include $template_path;
        }
    }
} 