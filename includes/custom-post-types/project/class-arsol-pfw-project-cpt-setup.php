<?php
/**
 * Project CPT Setup Class for Arsol Projects for WooCommerce
 *
 * Handles registration and setup of the project custom post type.
 *
 * @package Arsol_PFW\Custom_Post_Types\Project
 * @since   1.0.0
 */

declare(strict_types=1);

namespace Arsol_PFW\Custom_Post_Types\Project;

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Project CPT Setup Class
 *
 * Registers and configures the project custom post type.
 */
class Setup {
    
    /**
     * Constructor
     */
    public function __construct() {
        add_action('init', array($this, 'register_post_type'), 5);
        add_action('init', array($this, 'register_meta_fields'), 10);
    }
    
    /**
     * Register the project custom post type
     */
    public function register_post_type() {
        $labels = array(
            'name' => _x('Projects', 'Post type general name', 'arsol-pfw'),
            'singular_name' => _x('Project', 'Post type singular name', 'arsol-pfw'),
            'menu_name' => _x('Projects', 'Admin Menu text', 'arsol-pfw'),
            'name_admin_bar' => _x('Project', 'Add New on Toolbar', 'arsol-pfw'),
            'add_new' => __('Add New', 'arsol-pfw'),
            'add_new_item' => __('Add New Project', 'arsol-pfw'),
            'new_item' => __('New Project', 'arsol-pfw'),
            'edit_item' => __('Edit Project', 'arsol-pfw'),
            'view_item' => __('View Project', 'arsol-pfw'),
            'all_items' => __('All Projects', 'arsol-pfw'),
            'search_items' => __('Search Projects', 'arsol-pfw'),
            'parent_item_colon' => __('Parent Projects:', 'arsol-pfw'),
            'not_found' => __('No projects found.', 'arsol-pfw'),
            'not_found_in_trash' => __('No projects found in Trash.', 'arsol-pfw'),
            'featured_image' => _x('Project Featured Image', 'Overrides the "Featured Image" phrase', 'arsol-pfw'),
            'set_featured_image' => _x('Set featured image', 'Overrides the "Set featured image" phrase', 'arsol-pfw'),
            'remove_featured_image' => _x('Remove featured image', 'Overrides the "Remove featured image" phrase', 'arsol-pfw'),
            'use_featured_image' => _x('Use as featured image', 'Overrides the "Use as featured image" phrase', 'arsol-pfw'),
            'archives' => _x('Project archives', 'The post type archive label', 'arsol-pfw'),
            'insert_into_item' => _x('Insert into project', 'Overrides the "Insert into post" phrase', 'arsol-pfw'),
            'uploaded_to_this_item' => _x('Uploaded to this project', 'Overrides the "Uploaded to this post" phrase', 'arsol-pfw'),
            'filter_items_list' => _x('Filter projects list', 'Screen reader text for the filter links', 'arsol-pfw'),
            'items_list_navigation' => _x('Projects list navigation', 'Screen reader text for the pagination', 'arsol-pfw'),
            'items_list' => _x('Projects list', 'Screen reader text for the items list', 'arsol-pfw'),
        );
        
        $args = array(
            'labels' => $labels,
            'public' => false,
            'publicly_queryable' => false,
            'show_ui' => true,
            'show_in_menu' => true,
            'show_in_nav_menus' => false,
            'show_in_admin_bar' => true,
            'query_var' => true,
            'rewrite' => false,
            'capability_type' => array('arsol_pfw_project', 'arsol_pfw_projects'),
            'map_meta_cap' => true,
            'has_archive' => false,
            'hierarchical' => false,
            'menu_position' => 25,
            'menu_icon' => 'dashicons-portfolio',
            'supports' => array('title', 'editor', 'author', 'thumbnail', 'comments'),
            'show_in_rest' => false,
        );
        
        register_post_type('arsol-pfw-project', apply_filters('arsol_pfw_project_post_type_args', $args));
    }
    
    /**
     * Register meta fields for projects
     */
    public function register_meta_fields() {
        // Customer ID
        register_post_meta('arsol-pfw-project', '_arsol_pfw_customer_id', array(
            'type' => 'integer',
            'single' => true,
            'sanitize_callback' => 'absint',
            'auth_callback' => array($this, 'meta_auth_callback'),
        ));
        
        // Project stage
        register_post_meta('arsol-pfw-project', '_arsol_pfw_project_stage', array(
            'type' => 'string',
            'single' => true,
            'sanitize_callback' => 'sanitize_text_field',
            'auth_callback' => array($this, 'meta_auth_callback'),
        ));
        
        // Project priority
        register_post_meta('arsol-pfw-project', '_arsol_pfw_project_priority', array(
            'type' => 'string',
            'single' => true,
            'sanitize_callback' => 'sanitize_text_field',
            'auth_callback' => array($this, 'meta_auth_callback'),
        ));
        
        // Project budget
        register_post_meta('arsol-pfw-project', '_arsol_pfw_project_budget', array(
            'type' => 'number',
            'single' => true,
            'sanitize_callback' => 'floatval',
            'auth_callback' => array($this, 'meta_auth_callback'),
        ));
        
        // Project deadline
        register_post_meta('arsol-pfw-project', '_arsol_pfw_project_deadline', array(
            'type' => 'string',
            'single' => true,
            'sanitize_callback' => 'sanitize_text_field',
            'auth_callback' => array($this, 'meta_auth_callback'),
        ));
    }
    
    /**
     * Meta field authorization callback
     *
     * @param bool $allowed Whether the user can edit the meta field
     * @param string $meta_key Meta key
     * @param int $post_id Post ID
     * @param int $user_id User ID
     * @param string $cap Capability
     * @param array $caps Capabilities
     * @return bool Whether the user can edit the meta field
     */
    public function meta_auth_callback($allowed, $meta_key, $post_id, $user_id, $cap, $caps) {
        // Only allow editing if user can edit the post
        return current_user_can('edit_post', $post_id);
    }
} 