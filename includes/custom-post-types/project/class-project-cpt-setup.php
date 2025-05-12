<?php

namespace Arsol_Projects_For_Woo\Custom_Post_Types\Project;

if (!defined('ABSPATH')) {
    exit;
}

class Project_CPT_Setup {
    public function __construct() {
        add_action('init', array($this, 'register_post_type'));
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
            'show_ui'            => true,
            'show_in_menu'       => true,
            'show_in_nav_menus'  => true,
            'show_in_admin_bar'  => true,
            'menu_position'      => 5,
            'menu_icon'          => 'dashicons-portfolio',
            'capability_type'    => 'post',
            'hierarchical'       => false,
            'supports'           => array('title', 'editor', 'thumbnail', 'excerpt'),
            'has_archive'        => true,
            'rewrite'           => array('slug' => 'projects'),
            'show_in_rest'      => true,
        );

        register_post_type('project', $args);
    }
}
