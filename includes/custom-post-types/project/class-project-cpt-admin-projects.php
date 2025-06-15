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
        // Example: $columns['project_status'] = 'Status';
        return $columns;
    }

    /**
     * Render custom column content
     */
    public function render_custom_column($column, $post_id) {
        // Example: if ($column === 'project_status') { echo 'Status here'; }
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

            // Project Lead filter (manual select for Select2)
            $current_lead = isset($_GET['project_lead']) ? $_GET['project_lead'] : '';
            $admin_users_helper = new \Arsol_Projects_For_Woo\Admin\Users();
            $all_users = get_users(array('fields' => array('ID', 'display_name')));

            echo '<select name="project_lead" class="arsol-user-select2" data-placeholder="' . esc_attr__('Filter by project lead', 'arsol-pfw') . '" data-allow_clear="true">';
            echo '<option value="">' . __('Filter by project lead', 'arsol-pfw') . '</option>';
            foreach ($all_users as $user) {
                if ($admin_users_helper->can_user_create_projects($user->ID)) {
                    echo '<option value="' . esc_attr($user->ID) . '"' . selected($current_lead, $user->ID, false) . '>' . esc_html($user->display_name) . '</option>';
                }
            }
            echo '</select>';

            // Customer filter (WooCommerce native customer search)
            $current_customer = isset($_GET['customer_id']) ? $_GET['customer_id'] : '';
            echo '<select name="customer_id" class="wc-customer-search" data-placeholder="' . esc_attr__('Filter by registered customer', 'arsol-pfw') . '" data-allow_clear="true" data-action="woocommerce_json_search_customers" data-security="' . esc_attr(wp_create_nonce('search-customers')) . '">';
            
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
                    'key' => '_project_lead',
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
