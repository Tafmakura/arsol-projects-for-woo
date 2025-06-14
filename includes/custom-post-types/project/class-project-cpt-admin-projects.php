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
            $users = get_users(array('role__in' => array('administrator', 'shop_manager')));
            echo '<select name="project_lead" class="arsol-pfw-admin-select2" data-placeholder="' . esc_attr__('Filter by project lead', 'arsol-pfw') . '" data-allow_clear="true">';
            echo '<option value="">' . __('Filter by project lead', 'arsol-pfw') . '</option>';
            foreach ($users as $user) {
                echo '<option value="' . esc_attr($user->ID) . '"' . selected($current_lead, $user->ID, false) . '>' . esc_html($user->display_name) . '</option>';
            }
            echo '</select>';

            // Customer filter (manual select for Select2)
            $current_customer = isset($_GET['customer_id']) ? $_GET['customer_id'] : '';
            $customers = get_users(array('role__in' => array('customer', 'subscriber')));
            echo '<select name="customer_id" class="arsol-pfw-admin-select2" data-placeholder="' . esc_attr__('Filter by registered customer', 'arsol-pfw') . '" data-allow_clear="true">';
            echo '<option value="">' . __('Filter by registered customer', 'arsol-pfw') . '</option>';
            foreach ($customers as $customer) {
                echo '<option value="' . esc_attr($customer->ID) . '"' . selected($current_customer, $customer->ID, false) . '>' . esc_html($customer->display_name) . '</option>';
            }
            echo '</select>';

            // Enqueue WooCommerce select2
            wp_enqueue_script('select2');
            wp_enqueue_style('select2');
            
            // Add inline script to initialize select2 with min-width and spacing
            add_action('admin_footer', function() {
                ?>
                <script type="text/javascript">
                jQuery(function($) {
                    $('.wc-customer-search, .select2-enhanced').select2({
                        allowClear: true,
                        minimumResultsForSearch: 0,
                        minimumInputLength: 1,
                        placeholder: function(){
                            return $(this).data('placeholder');
                        }
                    }).next('.select2-container').css({'min-width': '200px', 'margin-right': '8px'});
                });
                </script>
                <?php
            });
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
