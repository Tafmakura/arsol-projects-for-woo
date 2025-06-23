<?php

namespace Arsol_Projects_For_Woo\Custom_Post_Types\Project\Admin;

if (!defined('ABSPATH')) exit;

class Projects {
    public function __construct() {
        // Add custom columns to projects table
        add_filter('manage_arsol-project_posts_columns', array($this, 'add_custom_columns'));
        add_action('manage_arsol-project_posts_custom_column', array($this, 'render_custom_column'), 10, 2);
        // Add filters to projects table
        add_action('restrict_manage_posts', array($this, 'add_filters'));
        // Filtering logic for projects table
        add_action('pre_get_posts', array($this, 'filter_projects_by_date_range'));
        // Handle bulk actions
        add_filter('bulk_actions-edit-arsol-project', array($this, 'register_bulk_actions'));
        add_filter('handle_bulk_actions-edit-arsol-project', array($this, 'handle_bulk_actions'), 10, 3);
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
        $new_columns['project_status'] = __('Status', 'arsol-pfw');
        $new_columns['project_lead'] = __('Project Lead', 'arsol-pfw');
        $new_columns['date'] = $columns['date'];
        
        return $new_columns;
    }

    /**
     * Render custom column content
     */
    public function render_custom_column($column, $post_id) {
        switch ($column) {
            case 'project_status':
                $status = wp_get_object_terms($post_id, 'arsol-project-status', array('fields' => 'names'));
                if (!empty($status) && !is_wp_error($status)) {
                    echo esc_html($status[0]);
                }
                break;
                
            case 'customer':
                $post = get_post($post_id);
                if ($post && $post->post_author) {
                    $customer = get_userdata($post->post_author);
                    if ($customer) {
                        // Priority: display_name -> first/last name -> email
                        $customer_display = '';
                        
                        if (!empty($customer->display_name)) {
                            $customer_display = $customer->display_name;
                        } elseif (!empty($customer->first_name) || !empty($customer->last_name)) {
                            $customer_display = trim($customer->first_name . ' ' . $customer->last_name);
                        } elseif (!empty($customer->user_email)) {
                            $customer_display = $customer->user_email;
                        } else {
                            $customer_display = __('Unknown Customer', 'arsol-pfw');
                        }
                        
                        // Make it a link to filter by this customer
                        $filter_url = add_query_arg(array(
                            'post_type' => 'arsol-project',
                            'customer' => $customer->ID
                        ), admin_url('edit.php'));
                        
                        printf(
                            '<a href="%s">%s</a>',
                            esc_url($filter_url),
                            esc_html($customer_display)
                        );
                    } else {
                        echo '<span class="na">&ndash;</span>';
                    }
                } else {
                    echo '<span class="na">&ndash;</span>';
                }
                break;
                
            case 'project_lead':
                $lead_id = get_post_meta($post_id, '_arsol_pfw_project_lead', true);
                if ($lead_id) {
                    $lead_user = get_userdata($lead_id);
                    if ($lead_user) {
                        // Priority: display_name -> first/last name -> email
                        $lead_display = '';
                        
                        if (!empty($lead_user->display_name)) {
                            $lead_display = $lead_user->display_name;
                        } elseif (!empty($lead_user->first_name) || !empty($lead_user->last_name)) {
                            $lead_display = trim($lead_user->first_name . ' ' . $lead_user->last_name);
                        } elseif (!empty($lead_user->user_email)) {
                            $lead_display = $lead_user->user_email;
                        } else {
                            $lead_display = __('Unknown Lead', 'arsol-pfw');
                        }
                        
                        // Make it a link to filter by this project lead
                        $filter_url = add_query_arg(array(
                            'post_type' => 'arsol-project',
                            'project_lead' => $lead_user->ID
                        ), admin_url('edit.php'));
                        
                        printf(
                            '<a href="%s">%s</a>',
                            esc_url($filter_url),
                            esc_html($lead_display)
                        );
                    } else {
                        echo '<span class="na">&ndash;</span>';
                    }
                } else {
                    echo '<span class="na">&ndash;</span>';
                }
                break;
        }
    }

    /**
     * Add filters to projects table
     */
    public function add_filters() {
        global $typenow;
        if ($typenow === 'arsol-project') {
            // Status filter (standard dropdown)
            $current_status = isset($_GET['project_status']) ? $_GET['project_status'] : '';
            $statuses = get_terms('arsol-project-status', array('hide_empty' => false));
            if (!empty($statuses) && !is_wp_error($statuses)) {
                echo '<select name="project_status" id="filter-by-project-status" class="postform status-filter-dropdown">';
                echo '<option value="">' . __('All Statuses', 'arsol-pfw') . '</option>';
                foreach ($statuses as $status) {
                    printf(
                        '<option value="%s" %s>%s</option>',
                        esc_attr($status->slug),
                        selected($current_status, $status->slug, false),
                        esc_html($status->name)
                    );
                }
                echo '</select>';
            }

            // Project Lead filter (WordPress native user dropdown)
            $current_lead = isset($_GET['project_lead']) ? $_GET['project_lead'] : '';
            $admin_users_helper = new \Arsol_Projects_For_Woo\Admin\Users();
            
            // Get users who can create projects based on Project Manager Roles setting
            $project_lead_users = get_users(array(
                'fields' => array('ID', 'display_name'),
                'meta_query' => array(
                    'relation' => 'OR',
                    array(
                        'key' => 'wp_capabilities',
                        'value' => 'manage_projects',
                        'compare' => 'LIKE'
                    ),
                    array(
                        'key' => 'wp_capabilities', 
                        'value' => 'create_projects',
                        'compare' => 'LIKE'
                    )
                )
            ));
            
            // Filter to only users who can actually create projects
            $valid_user_ids = array();
            foreach ($project_lead_users as $user) {
                if ($admin_users_helper->can_user_create_projects($user->ID)) {
                    $valid_user_ids[] = $user->ID;
                }
            }
            
            // Use WordPress native dropdown with Select2 class
            echo '<div class="arsol-user-select2-wrapper">';
            wp_dropdown_users(array(
                'name' => 'project_lead',
                'class' => 'arsol-user-select2',
                'selected' => $current_lead,
                'include' => $valid_user_ids,
                'show_option_none' => __('Filter by project lead', 'arsol-pfw'),
                'option_none_value' => ''
            ));
            echo '</div>';

            // Customer filter (WooCommerce native customer search)
            $current_customer = isset($_GET['customer']) ? $_GET['customer'] : '';
            echo '<select name="customer" class="wc-customer-search" data-placeholder="' . esc_attr__('Filter by customer', 'arsol-pfw') . '" data-allow_clear="true" data-action="woocommerce_json_search_customers" data-security="' . esc_attr(wp_create_nonce('search-customers')) . '">';
            
            // If there's a current customer selected, add it as an option
            if (!empty($current_customer)) {
                $customer = get_userdata($current_customer);
                if ($customer) {
                    // Format customer display like WooCommerce: "First Last (#ID – email)" or fallback to "Display Name (#ID – email)"
                    $customer_name = trim($customer->first_name . ' ' . $customer->last_name);
                    if (empty($customer_name)) {
                        $customer_name = $customer->display_name;
                    }
                    
                    printf(
                        '<option value="%s" selected="selected">%s (#%s &ndash; %s)</option>',
                        esc_attr($customer->ID),
                        esc_html($customer_name),
                        esc_html($customer->ID),
                        esc_html($customer->user_email)
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

        if ($pagenow === 'edit.php' && $typenow === 'arsol-project' && $query->is_main_query()) {
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

            // Filter by project status (taxonomy)
            if (!empty($_GET['project_status'])) {
                $tax_query = $query->get('tax_query') ?: [];
                $tax_query[] = [
                    'taxonomy' => 'arsol-project-status',
                    'field'    => 'slug',
                    'terms'    => sanitize_text_field($_GET['project_status']),
                ];
                $query->set('tax_query', $tax_query);
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
