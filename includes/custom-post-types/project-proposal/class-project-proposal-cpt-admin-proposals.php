<?php

namespace Arsol_Projects_For_Woo\Custom_Post_Types\ProjectProposal\Admin;

if (!defined('ABSPATH')) exit;

class Proposals {
    public function __construct() {
        // Add custom columns to proposals table
        add_filter('manage_arsol-pfw-proposal_posts_columns', array($this, 'add_custom_columns'));
        add_action('manage_arsol-pfw-proposal_posts_custom_column', array($this, 'render_custom_column'), 10, 2);
        // Add filters to proposals table
        add_action('restrict_manage_posts', array($this, 'add_filters'));
        // Filtering logic for proposals table
        add_action('pre_get_posts', array($this, 'filter_proposals'));
        // Handle bulk actions
        add_filter('bulk_actions-edit-arsol-pfw-proposal', array($this, 'register_bulk_actions'));
        add_filter('handle_bulk_actions-edit-arsol-pfw-proposal', array($this, 'handle_bulk_actions'), 10, 3);
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
        $new_columns['customer'] = __('Customer', 'arsol-pfw');        $new_columns['project'] = __('Project', 'arsol-pfw');
        $new_columns['project'] = __('Project', 'arsol-pfw');
        $new_columns['proposal_status'] = __('Status', 'arsol-pfw');
        $new_columns['project_lead'] = __('Project Lead', 'arsol-pfw');
        $new_columns['date'] = $columns['date'];
        
        return $new_columns;
    }

    /**
     * Render custom column content
     */
    public function render_custom_column($column, $post_id) {
        switch ($column) {
            case 'proposal_status':
                $status = wp_get_object_terms($post_id, 'arsol-pfw-proposal-status', array('fields' => 'names'));
                if (!empty($status) && !is_wp_error($status)) {
                    echo esc_html($status[0]);
                }
                break;
                
            case 'customer':
                $post = get_post($post_id);
                if ($post && $post->post_author) {
                    echo \Arsol_Projects_For_Woo\Woocommerce::create_customer_filter_link($post->post_author, 'arsol-pfw-proposal');
                } else {
                    echo '<span class="na">&ndash;</span>';
                }
                break;
            
            case 'project':
                $parent_project_id = get_post_meta($post_id, '_arsol_pfw_parent_project_id', true);
                if ($parent_project_id) {
                    echo '#' . $parent_project_id;
                } else {
                    echo '<span class="na">&ndash;</span>';
                }
                break;
                
            case 'project':
                $parent_project_id = get_post_meta($post_id, '_arsol_pfw_parent_project_id', true);
                if ($parent_project_id) {
                    echo '#' . $parent_project_id;
                } else {
                    echo '<span class="na">&ndash;</span>';
                }
                break;
                
            case 'project_lead':
                $lead_id = get_post_meta($post_id, '_arsol_pfw_proposal_project_lead', true);
                echo \Arsol_Projects_For_Woo\Admin\Users::create_project_lead_filter_link($lead_id, 'arsol-pfw-proposal');
                break;
        }
    }

    /**
     * Add filters to proposals table
     */
    public function add_filters() {
        global $typenow;
        if ($typenow === 'arsol-pfw-proposal') {
            // Status filter
            $current_status = isset($_GET['proposal_status']) ? $_GET['proposal_status'] : '';
            $statuses = get_terms('arsol-pfw-proposal-status', array('hide_empty' => false));
            if (!empty($statuses) && !is_wp_error($statuses)) {
                echo '<select name="proposal_status" id="filter-by-proposal-status" class="postform status-filter-dropdown">';
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
            echo '<select name="customer" id="filter-by-customer" class="wc-customer-search" data-placeholder="' . esc_attr__('Filter by customer', 'arsol-pfw') . '" data-allow_clear="true" data-action="woocommerce_json_search_customers" data-security="' . esc_attr(wp_create_nonce('search-customers')) . '">';
            
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
     * Filtering logic for proposals table
     */
    public function filter_proposals($query) {
        global $pagenow, $typenow;

        if ($pagenow === 'edit.php' && $typenow === 'arsol-pfw-proposal' && $query->is_main_query()) {
            // Filter by customer (author)
            if (!empty($_GET['customer'])) {
                $query->set('author', sanitize_text_field($_GET['customer']));
            }

            // Filter by project lead (meta)
            if (!empty($_GET['project_lead'])) {
                $meta_query = $query->get('meta_query') ?: [];
                $meta_query[] = [
                    'key'     => '_arsol_pfw_proposal_project_lead',
                    'value'   => sanitize_text_field($_GET['project_lead']),
                    'compare' => '='
                ];
                $query->set('meta_query', $meta_query);
            }

            // Filter by proposal status (taxonomy)
            if (!empty($_GET['proposal_status'])) {
                $tax_query = $query->get('tax_query') ?: [];
                $tax_query[] = [
                    'taxonomy' => 'arsol-pfw-proposal-status',
                    'field'    => 'slug',
                    'terms'    => sanitize_text_field($_GET['proposal_status']),
                ];
                $query->set('tax_query', $tax_query);
            }
        }
    }

    /**
     * Register bulk actions
     */
    public function register_bulk_actions($bulk_actions) {
        $bulk_actions['mark_processing'] = __('Mark as Processing', 'arsol-pfw');
        $bulk_actions['mark_pending_approval'] = __('Mark as Pending Approval', 'arsol-pfw');
        $bulk_actions['mark_approved'] = __('Mark as Approved', 'arsol-pfw');
        $bulk_actions['mark_rejected'] = __('Mark as Rejected', 'arsol-pfw');
        return $bulk_actions;
    }

    /**
     * Handle bulk actions
     */
    public function handle_bulk_actions($redirect_to, $doaction, $post_ids) {
        $valid_actions = array(
            'mark_processing' => 'processing',
            'mark_pending_approval' => 'pending-approval',
            'mark_approved' => 'approved',
            'mark_rejected' => 'rejected'
        );
        
        if (!array_key_exists($doaction, $valid_actions)) {
            return $redirect_to;
        }

        $status = $valid_actions[$doaction];
        
        foreach ($post_ids as $post_id) {
            wp_set_object_terms($post_id, $status, 'arsol-pfw-proposal-status', false);
        }

        $redirect_to = add_query_arg('bulk_proposals_updated', count($post_ids), $redirect_to);
        return $redirect_to;
    }

    /**
     * Remove view links from admin
     */
    public function remove_view_link($actions, $post) {
        if ($post->post_type === 'arsol-pfw-proposal') {
            unset($actions['view']);
        }
        return $actions;
    }
}
