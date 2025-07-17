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
        
        // Add columns in desired order
        $new_columns['cb'] = $columns['cb'];
        $new_columns['title'] = $columns['title'];
        $new_columns['customer'] = __('Customer', 'arsol-pfw');
        $new_columns['project_stage'] = __('Stage', 'arsol-pfw');
        $new_columns['project_lead'] = __('Project Lead', 'arsol-pfw');
        $new_columns['date'] = $columns['date'];
        
        return $new_columns;
    }

    /**
     * Render custom column content
     */
    public function render_custom_column($column, $post_id) {
        switch ($column) {
            case 'project_stage':
                $project = new \Arsol_Projects_For_Woo\Custom_Post_Types\Project($post_id);
                $stage = $project->get_stage();
                $stage_label = $project->get_stage_label();
                if ($stage) {
                    echo '<span class="stage stage-' . esc_attr($stage) . '">' . esc_html($stage_label) . '</span>';
                } else {
                    echo '<span class="stage stage-not-started">Not Started</span>';
                }
                break;
                
            case 'customer':
                // Use Project entity for customer access
                $project = new \Arsol_Projects_For_Woo\Custom_Post_Types\Project($post_id);
                $customer_id = $project->get_customer_id();
                if ($customer_id) {
                    echo \Arsol_Projects_For_Woo\Woocommerce::create_customer_filter_link($customer_id, 'arsol-pfw-project');
                } else {
                    echo '<span class="na">&ndash;</span>';
                }
                break;
                
            case 'project_lead':
                // Use Project entity for project lead access
                $project = new \Arsol_Projects_For_Woo\Custom_Post_Types\Project($post_id);
                $lead_id = $project->get_project_lead();
                echo \Arsol_Projects_For_Woo\Admin\Users::create_project_lead_filter_link($lead_id, 'arsol-pfw-project');
                break;
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
            echo '<select name="customer" class="wc-customer-search" data-placeholder="' . esc_attr__('Filter by customer', 'arsol-pfw') . '" data-allow_clear="true" data-action="woocommerce_json_search_customers" data-security="' . esc_attr(wp_create_nonce('search-customers')) . '">';
            
            // If there's a current customer selected, add it as an option
            if (!empty($current_customer)) {
                $customer = get_userdata($current_customer);
                if ($customer) {
                    $customer_display = \Arsol_Projects_For_Woo\Woocommerce::format_customer_admin_display($customer);
                    
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
