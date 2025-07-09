<?php

namespace Arsol_Projects_For_Woo\Custom_Post_Types\Project\Admin;

if (!defined('ABSPATH')) exit;

class Projects {
    public function __construct() {
        // Add custom columns to projects table
        add_filter('manage_arsol-pfw-project_posts_columns', array($this, 'add_custom_columns'));
        add_action('manage_arsol-pfw-project_posts_custom_column', array($this, 'render_custom_column'), 10, 2);
        // Add filtering logic for clickable columns
        add_action('pre_get_posts', array($this, 'filter_by_custom_columns'));
    }

    /**
     * Add custom columns
     */
    public function add_custom_columns($columns) {
        $new_columns = array();
        
        // Keep checkbox and title as-is
        $new_columns['cb'] = $columns['cb'];
        $new_columns['title'] = $columns['title'];
        
        // Add our custom columns
        $new_columns['customer'] = __('Customer', 'arsol-pfw');
        $new_columns['project_stage'] = __('Stage', 'arsol-pfw');
        $new_columns['project_lead'] = __('Project Lead', 'arsol-pfw');
        
        // Keep date column
        $new_columns['date'] = $columns['date'];
        
        return $new_columns;
    }

    /**
     * Render custom column content - ONLY for our custom columns
     */
    public function render_custom_column($column, $post_id) {
        switch ($column) {
            case 'customer':
                $author_id = get_post_field('post_author', $post_id);
                if ($author_id) {
                    $user = get_userdata($author_id);
                    if ($user) {
                        $filter_url = add_query_arg(array(
                            'post_type' => 'arsol-pfw-project',
                            'author' => $author_id
                        ), admin_url('edit.php'));
                        echo '<a href="' . esc_url($filter_url) . '">' . esc_html($user->display_name) . '</a>';
                    } else {
                        echo '—';
                    }
                } else {
                    echo '—';
                }
                break;
                
            case 'project_stage':
                $terms = wp_get_object_terms($post_id, 'arsol-pfw-project-stage');
                if (!empty($terms) && !is_wp_error($terms)) {
                    echo esc_html($terms[0]->name);
                } else {
                    echo 'Not Started';
                }
                break;
                
            case 'project_lead':
                $lead_id = get_post_meta($post_id, '_arsol_pfw_project_lead', true);
                if ($lead_id) {
                    $user = get_userdata($lead_id);
                    if ($user) {
                        $filter_url = add_query_arg(array(
                            'post_type' => 'arsol-pfw-project',
                            'meta_key' => '_arsol_pfw_project_lead',
                            'meta_value' => $lead_id
                        ), admin_url('edit.php'));
                        echo '<a href="' . esc_url($filter_url) . '">' . esc_html($user->display_name) . '</a>';
                    } else {
                        echo '—';
                    }
                } else {
                    echo '—';
                }
                break;
        }
    }

    /**
     * Filter posts based on custom column clicks
     */
    public function filter_by_custom_columns($query) {
        global $pagenow, $typenow;

        if ($pagenow === 'edit.php' && $typenow === 'arsol-pfw-project' && $query->is_main_query()) {
            // Filter by project lead meta
            if (!empty($_GET['meta_key']) && $_GET['meta_key'] === '_arsol_pfw_project_lead' && !empty($_GET['meta_value'])) {
                $query->set('meta_key', '_arsol_pfw_project_lead');
                $query->set('meta_value', sanitize_text_field($_GET['meta_value']));
            }
        }
    }
}
