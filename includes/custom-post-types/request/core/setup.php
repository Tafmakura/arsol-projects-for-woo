<?php

namespace Arsol_Projects_For_Woo\Custom_Post_Types\Request\Core;

if (!defined('ABSPATH')) {
    exit;
}

class Setup {
    public function __construct() {
        add_action('init', array($this, 'register_post_type'), 15);
        add_filter('use_block_editor_for_post_type', array($this, 'disable_gutenberg_for_project_requests'), 10, 2);
        add_filter('wp_dropdown_users_args', array($this, 'modify_author_dropdown'), 10, 2);

        add_action('add_meta_boxes', array($this, 'remove_publish_metabox'));
        
        // Add header container after title
        add_action('edit_form_after_title', array($this, 'render_request_header_container'));
        
        // Hook request details into header
        add_action('arsol_request_details_content', array($this, 'render_request_details_content'));
        
        // Protect core status terms from deletion
        add_action('pre_delete_term', array($this, 'protect_core_request_stages'), 10, 2);
    }

    public function register_post_type() {
        // Debug logging
        if (function_exists('error_log')) {
            error_log('ARSOL DEBUG: Registering arsol-pfw-request post type');
        }

        $labels = array(
            'name'               => __('Project Requests', 'arsol-pfw'),
            'singular_name'      => __('Project Request', 'arsol-pfw'),
            'add_new'           => __('Add New', 'arsol-pfw'),
            'add_new_item'      => __('Add New Project Request', 'arsol-pfw'),
            'edit_item'         => __('Edit Project Request', 'arsol-pfw'),
            'new_item'          => __('New Project Request', 'arsol-pfw'),
            'search_items'      => __('Search Project Requests', 'arsol-pfw'),
            'not_found'         => __('No project requests found', 'arsol-pfw'),
            'not_found_in_trash'=> __('No project requests found in trash', 'arsol-pfw'),
            'menu_name'         => __('Project Requests', 'arsol-pfw'),
            'all_items'         => __('All Project Requests', 'arsol-pfw'),
        );

        // Get base supports array
        $supports = array('title', 'author');
        
        // Add comments support if enabled
        if (\Arsol_Projects_For_Woo\Admin\Settings\General::is_comments_enabled_for_post_type('arsol-pfw-request')) {
            $supports[] = 'comments';
        }

        $args = array(
            'labels'              => $labels,
            'public'              => false,
            'publicly_queryable'  => false,
            'show_ui'            => true,
            'show_in_menu'       => 'edit.php?post_type=arsol-pfw-project',
            'show_in_nav_menus'  => false,
            'show_in_admin_bar'  => true,
            'menu_position'      => null,
            'menu_icon'          => 'dashicons-format-chat',
            'hierarchical'       => false,
            'supports'           => $supports,
            'has_archive'        => false,
            'rewrite'           => false,
            'show_in_rest'      => false,
            'taxonomies'         => array('arsol-pfw-request-stage'),
            // Use our custom capabilities
            'capability_type'    => 'arsol_pfw_request',
            'map_meta_cap'       => true,
        );

        $result = register_post_type('arsol-pfw-request', $args);
        
        // Debug the result
        if (function_exists('error_log')) {
            if (is_wp_error($result)) {
                error_log('ARSOL DEBUG: Failed to register arsol-pfw-request: ' . $result->get_error_message());
            } else {
                error_log('ARSOL DEBUG: Successfully registered arsol-pfw-request post type');
            }
        }
    }

    /**
     * Disable Gutenberg for project requests post type
     */
    public function disable_gutenberg_for_project_requests($use_block_editor, $post_type) {
        if ($post_type === 'arsol-pfw-request') {
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
        if ($screen && $screen->post_type === 'arsol-pfw-request') {
            // Get all users who can make purchases
            $query_args['role__in'] = array('customer', 'subscriber');
            $query_args['orderby'] = 'display_name';
            $query_args['order'] = 'ASC';
        }
        return $query_args;
    }

    /**
     * Remove the publish metabox
     */
    public function remove_publish_metabox() {
        remove_meta_box('submitdiv', 'arsol-pfw-request', 'side');
    }

    /**
     * Render request header container
     */
    public function render_request_header_container() {
        global $post;
        
        if (!$post || $post->post_type !== 'arsol-pfw-request') {
            return;
        }
        
        // Instantiate the request entity
        $request = new \Arsol_Projects_For_Woo\Custom_Post_Types\Arsol_PFW_Request($post->ID);
        
        $template_path = ARSOL_PFW_PLUGIN_DIR . 'ui/components/admin/section-edit-request-header.php';
        if (file_exists($template_path)) {
            include $template_path;
        }
    }

    /**
     * Render request details content
     */
    public function render_request_details_content($post) {
        if (!$post || $post->post_type !== 'arsol-pfw-request') {
            return;
        }
        $request = new \Arsol_Projects_For_Woo\Custom_Post_Types\Arsol_PFW_Request($post->ID);
        $requested_budget = $request->get_requested_project_budget();
        $requested_start_date = $request->get_requested_project_start_date();
        $requested_due_date = $request->get_requested_project_due_date();
        $request_date = $request->get_request_date();
        $request_attachments = $request->get_request_attachments();
        
        $template_path = ARSOL_PFW_PLUGIN_DIR . 'ui/components/admin/section-edit-request-header-column-2.php';
        if (file_exists($template_path)) {
            include $template_path;
        }
    }

    /**
     * Protect core request stage terms from deletion
     *
     * @param int $term_id Term ID
     * @param string $taxonomy Taxonomy slug
     */
    public function protect_core_request_stages($term_id, $taxonomy) {
        if ($taxonomy === 'arsol-pfw-request-stage') {
            $protected_stages = array('pending-review', 'under-review', 'on-hold', 'approved', 'rejected');
            $term = get_term($term_id, $taxonomy);
            
            if ($term && in_array($term->slug, $protected_stages)) {
                wp_die(
                    __('This request stage cannot be deleted as it\'s required for system functionality.', 'arsol-pfw'),
                    __('Cannot Delete Request Stage', 'arsol-pfw'),
                    array('back_link' => true)
                );
            }
        }
    }
} 