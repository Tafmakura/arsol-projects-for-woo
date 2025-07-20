<?php

namespace Arsol_Projects_For_Woo\Custom_Post_Types\Project\Admin;

if (!defined('ABSPATH')) exit;

class List_Controller {
    public function __construct() {
        // Add custom columns to projects table
        add_filter('manage_arsol-pfw-project_posts_columns', array($this, 'add_custom_columns'));
        add_action('manage_arsol-pfw-project_posts_custom_column', array($this, 'render_custom_column'), 10, 2);
        // Add filters to projects table
        add_action('restrict_manage_posts', array($this, 'add_filters'));
        // Filtering logic for projects table
        add_action('pre_get_posts', array($this, 'filter_projects_by_date_range'));
        // Handle bulk actions
        add_filter('bulk_actions-edit-arsol-pfw-project', array($this, 'register_bulk_actions'));
        add_filter('handle_bulk_actions-edit-arsol-pfw-project', array($this, 'handle_bulk_actions'), 10, 3);
    }

    /**
     * Add custom columns
     */
    public function add_custom_columns($columns) {
        $new_columns = array();
        
        // Add columns in desired order - check if keys exist first
        if (isset($columns['cb'])) {
            $new_columns['cb'] = $columns['cb'];
        }
        if (isset($columns['title'])) {
            $new_columns['title'] = $columns['title'];
        }
        $new_columns['customer'] = __('Customer', 'arsol-pfw');
        $new_columns['project_stage'] = __('Stage', 'arsol-pfw');
        $new_columns['project_lead'] = __('Project Lead', 'arsol-pfw');
        if (isset($columns['date'])) {
            $new_columns['date'] = $columns['date'];
        }
        
        return $new_columns;
    }

    /**
     * Render custom column content - WordPress Standard Pattern
     */
    public function render_custom_column($column, $post_id) {
        switch ($column) {
            case 'project_stage':
                $this->render_stage_column_direct($post_id);
                break;
            case 'customer':
                $this->render_customer_column_direct($post_id);
                break;
            case 'project_lead':
                $this->render_project_lead_column_direct($post_id);
                break;
        }
    }

    /**
     * Render stage column using direct WordPress functions
     */
    private function render_stage_column_direct($post_id) {
        $terms = wp_get_object_terms($post_id, 'arsol-pfw-project-stage', ['fields' => 'all']);
        if (!empty($terms) && !is_wp_error($terms)) {
            $term = $terms[0];
            echo '<span class="stage stage-' . esc_attr($term->slug) . '">' . esc_html($term->name) . '</span>';
        } else {
            echo '<span class="stage stage-not-started">Not Started</span>';
        }
    }

    /**
     * Render customer column using user functions
     */
    private function render_customer_column_direct($post_id) {
        $customer_id = get_post_meta($post_id, '_arsol_pfw_customer_id', true);
        if ($customer_id) {
            echo arsol_pfw_format_user($customer_id, 'display_name', false, true, true, ['post_type' => 'arsol-pfw-project']);
        } else {
            echo '<span class="na">&ndash;</span>';
        }
    }

    /**
     * Render project lead column using user functions
     */
    private function render_project_lead_column_direct($post_id) {
        $lead_id = get_post_meta($post_id, '_arsol_pfw_project_lead', true);
        if ($lead_id) {
            echo arsol_pfw_format_user($lead_id, 'display_name', false, true, true, ['post_type' => 'arsol-pfw-project']);
        } else {
            echo '<span class="na">&ndash;</span>';
        }
    }

    /**
     * Add filters to projects table
     */
    public function add_filters() {
        global $typenow;
        if ($typenow === 'arsol-pfw-project') {
            // Status filter (standard dropdown)
            $current_status = isset($_GET['project_stage']) ? $_GET['project_stage'] : '';
            $statuses = get_terms('arsol-pfw-project-stage', array('hide_empty' => false));
            
            echo '<select name="project_stage" id="filter-by-project-stage" class="postform stage-filter-dropdown">';
            echo '<option value="">' . __('All Stages', 'arsol-pfw') . '</option>';
            
            if (!empty($statuses) && !is_wp_error($statuses)) {
                foreach ($statuses as $status) {
                    echo '<option value="' . esc_attr($status->slug) . '"' . selected($current_status, $status->slug, false) . '>' . esc_html($status->name) . '</option>';
                }
            }
            
            echo '</select>';

            // Project Lead filter
            $current_lead = isset($_GET['project_lead']) ? $_GET['project_lead'] : '';
            echo '<div class="arsol-user-select2-wrapper">';
            \Arsol_Projects_For_Woo\Admin\Users::render_project_lead_search_field(array(
                'name' => 'project_lead',
                'id' => 'filter-by-project-lead',
                'selected' => $current_lead,
                'placeholder' => __('Filter by project lead', 'arsol-pfw')
            ));
            echo '</div>';

            // Customer filter (WooCommerce native customer search)
            $current_customer = isset($_GET['customer']) ? $_GET['customer'] : '';
            echo '<select name="customer" class="wc-customer-search" data-placeholder="' . esc_attr__('Filter by customer', 'arsol-pfw') . '" data-allow_clear="true" data-action="arsol_json_search_customers" data-security="' . esc_attr(wp_create_nonce('search-customers')) . '">';
            
            // If there's a current customer selected, add it as an option
            if (!empty($current_customer)) {
                $customer = get_userdata($current_customer);
                if ($customer) {
                    $customer_display = arsol_pfw_format_user($customer, 'display_name', false, true, false);
                    
                    printf(
                        '<option value="%s" selected="selected">%s</option>',
                        esc_attr($customer->ID),
                        esc_html($customer_display)
                    );
                }
            }
            echo '</select>';

            // Customer search functionality is now handled by global admin JS
        }
    }

    /**
     * Filtering logic for projects table
     */
    public function filter_projects_by_date_range($query) {
        global $pagenow, $typenow;

        if ($pagenow === 'edit.php' && $typenow === 'arsol-pfw-project' && $query->is_main_query()) {
            // Filter by project lead (meta)
            if (!empty($_GET['project_lead'])) {
                $meta_query = $query->get('meta_query') ?: [];
                $meta_query[] = [
                    'key' => '_arsol_pfw_project_lead',
                    'value' => sanitize_text_field($_GET['project_lead']),
                    'compare' => '='
                ];
                $query->set('meta_query', $meta_query);
            }

            // Filter by customer (author)
            if (!empty($_GET['customer'])) {
                $query->set('author', sanitize_text_field($_GET['customer']));
            }

            // Filter by project stage (taxonomy)
            if (!empty($_GET['project_stage'])) {
                $query->set('tax_query', array(
                    array(
                        'taxonomy' => 'arsol-pfw-project-stage',
                        'field'    => 'slug',
                        'terms'    => sanitize_text_field($_GET['project_stage']),
                    ),
                ));
            }
        }
    }

    /**
     * Register bulk actions
     */
    public function register_bulk_actions($bulk_actions) {
        // Example: $bulk_actions['mark_completed'] = 'Mark as Completed';
        return $bulk_actions;
    }

    /**
     * Handle bulk actions
     */
    public function handle_bulk_actions($redirect_to, $doaction, $post_ids) {
        // Example: handle bulk action logic
        return $redirect_to;
    }
}
