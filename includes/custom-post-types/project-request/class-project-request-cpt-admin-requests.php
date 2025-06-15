<?php

namespace Arsol_Projects_For_Woo\Custom_Post_Types\ProjectRequest\Admin;

if (!defined('ABSPATH')) exit;

class Requests {
    public function __construct() {
        // Add custom columns to requests table
        add_filter('manage_arsol-pfw-request_posts_columns', array($this, 'add_custom_columns'));
        add_action('manage_arsol-pfw-request_posts_custom_column', array($this, 'render_custom_column'), 10, 2);
        // Add filters to requests table
        add_action('restrict_manage_posts', array($this, 'add_filters'));
        // Filtering logic for requests table
        add_action('pre_get_posts', array($this, 'filter_requests'));
        // Handle bulk actions
        add_filter('bulk_actions-edit-arsol-pfw-request', array($this, 'register_bulk_actions'));
        add_filter('handle_bulk_actions-edit-arsol-pfw-request', array($this, 'handle_bulk_actions'), 10, 3);
        // Remove view links from admin
        add_filter('post_row_actions', array($this, 'remove_view_link'), 10, 2);
    }

    /**
     * Add custom columns
     */
    public function add_custom_columns($columns) {
        $new_columns = array();
        
        // Add columns in desired order
        $new_columns['cb'] = $columns['cb'];
        $new_columns['title'] = $columns['title'];
        $new_columns['request_status'] = __('Status', 'arsol-pfw');
        $new_columns['request_budget'] = __('Budget', 'arsol-pfw');
        $new_columns['request_timeline'] = __('Timeline', 'arsol-pfw');
        $new_columns['author'] = $columns['author'];
        $new_columns['date'] = $columns['date'];
        
        return $new_columns;
    }

    /**
     * Render custom column content
     */
    public function render_custom_column($column, $post_id) {
        switch ($column) {
            case 'request_status':
                $status = wp_get_object_terms($post_id, 'arsol-request-status', array('fields' => 'names'));
                if (!empty($status) && !is_wp_error($status)) {
                    echo esc_html($status[0]);
                }
                break;
                
            case 'request_budget':
                $budget = get_post_meta($post_id, '_request_budget', true);
                if ($budget) {
                    echo wc_price($budget);
                }
                break;
                
            case 'request_timeline':
                $timeline = get_post_meta($post_id, '_request_timeline', true);
                if ($timeline) {
                    echo sprintf(_n('%d day', '%d days', $timeline, 'arsol-pfw'), $timeline);
                }
                break;
        }
    }

    /**
     * Add filters to requests table
     */
    public function add_filters() {
        global $typenow;
        if ($typenow === 'arsol-pfw-request') {
            // Status filter
            $current_status = isset($_GET['request_status']) ? $_GET['request_status'] : '';
            $statuses = get_terms('arsol-request-status', array('hide_empty' => false));
            if (!empty($statuses) && !is_wp_error($statuses)) {
                echo '<select name="request_status" id="filter-by-request-status" class="postform status-filter-dropdown">';
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

            // Customer filter (WooCommerce native customer search)
            $current_customer = isset($_GET['customer']) ? $_GET['customer'] : '';
            echo '<select name="customer" id="filter-by-customer" class="wc-customer-search" data-placeholder="' . esc_attr__('Filter by customer', 'arsol-pfw') . '" data-allow_clear="true" data-action="woocommerce_json_search_customers" data-security="' . esc_attr(wp_create_nonce('search-customers')) . '">';
            
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
     * Filtering logic for requests table
     */
    public function filter_requests($query) {
        global $pagenow, $typenow;

        if ($pagenow === 'edit.php' && $typenow === 'arsol-pfw-request' && $query->is_main_query()) {
            // Filter by customer (author)
            if (!empty($_GET['customer'])) {
                $query->set('author', sanitize_text_field($_GET['customer']));
            }

            // Filter by request status (taxonomy)
            if (!empty($_GET['request_status'])) {
                $tax_query = $query->get('tax_query') ?: [];
                $tax_query[] = [
                    'taxonomy' => 'arsol-request-status',
                    'field'    => 'slug',
                    'terms'    => sanitize_text_field($_GET['request_status']),
                ];
                $query->set('tax_query', $tax_query);
            }
        }
    }

    /**
     * Register bulk actions
     */
    public function register_bulk_actions($bulk_actions) {
        $bulk_actions['mark_approved'] = __('Mark as Approved', 'arsol-pfw');
        $bulk_actions['mark_rejected'] = __('Mark as Rejected', 'arsol-pfw');
        return $bulk_actions;
    }

    /**
     * Handle bulk actions
     */
    public function handle_bulk_actions($redirect_to, $doaction, $post_ids) {
        if ($doaction !== 'mark_approved' && $doaction !== 'mark_rejected') {
            return $redirect_to;
        }

        $status = $doaction === 'mark_approved' ? 'approved' : 'rejected';
        
        foreach ($post_ids as $post_id) {
            wp_set_object_terms($post_id, $status, 'arsol-request-status', false);
        }

        $redirect_to = add_query_arg('bulk_requests_updated', count($post_ids), $redirect_to);
        return $redirect_to;
    }

    /**
     * Remove view links from admin
     */
    public function remove_view_link($actions, $post) {
        if ($post->post_type === 'arsol-pfw-request') {
            unset($actions['view']);
        }
        return $actions;
    }
}
