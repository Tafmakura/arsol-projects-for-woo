<?php

namespace Arsol_Projects_For_Woo\Custom_Post_Types\ProjectRequest\Admin;

if (!defined('ABSPATH')) exit;

class Requests {
    public function __construct() {
        // Add custom columns to requests table
        add_filter('manage_arsol-pfw-request_posts_columns', array($this, 'add_custom_columns'));
        add_action('manage_arsol-pfw-request_posts_custom_column', array($this, 'render_custom_column'), 10, 2);
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
        $new_columns['request_stage'] = __('Stage', 'arsol-pfw');
        $new_columns['request_budget'] = __('Budget', 'arsol-pfw');
        
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
                        echo esc_html($user->display_name);
                    } else {
                        echo '—';
                    }
                } else {
                    echo '—';
                }
                break;
                
            case 'request_stage':
                $terms = wp_get_object_terms($post_id, 'arsol-pfw-request-stage');
                if (!empty($terms) && !is_wp_error($terms)) {
                    echo esc_html($terms[0]->name);
                } else {
                    echo 'Pending Review';
                }
                break;
                
            case 'request_budget':
                $budget = get_post_meta($post_id, '_arsol_pfw_request_budget', true);
                if ($budget && is_numeric($budget)) {
                    if (function_exists('wc_price')) {
                        echo wc_price($budget);
                    } else {
                        echo '$' . number_format($budget, 2);
                    }
                } else {
                    echo '—';
                }
                break;
        }
    }
}
