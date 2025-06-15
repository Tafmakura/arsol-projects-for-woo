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
        $new_columns['proposal_status'] = __('Status', 'arsol-pfw');
        $new_columns['proposal_budget'] = __('Budget', 'arsol-pfw');
        $new_columns['proposal_timeline'] = __('Timeline', 'arsol-pfw');
        $new_columns['related_request'] = __('Related Request', 'arsol-pfw');
        $new_columns['author'] = $columns['author'];
        $new_columns['date'] = $columns['date'];
        
        return $new_columns;
    }

    /**
     * Render custom column content
     */
    public function render_custom_column($column, $post_id) {
        switch ($column) {
            case 'proposal_status':
                $status = wp_get_object_terms($post_id, 'arsol-proposal-status', array('fields' => 'names'));
                if (!empty($status) && !is_wp_error($status)) {
                    echo esc_html($status[0]);
                }
                break;
                
            case 'proposal_budget':
                $budget = get_post_meta($post_id, '_proposal_budget', true);
                if ($budget) {
                    echo wc_price($budget);
                }
                break;
                
            case 'proposal_timeline':
                $timeline = get_post_meta($post_id, '_proposal_timeline', true);
                if ($timeline) {
                    echo sprintf(_n('%d day', '%d days', $timeline, 'arsol-pfw'), $timeline);
                }
                break;
                
            case 'related_request':
                $request_id = get_post_meta($post_id, '_related_request', true);
                if ($request_id) {
                    $request = get_post($request_id);
                    if ($request) {
                        echo '<a href="' . esc_url(get_edit_post_link($request_id)) . '">' . esc_html($request->post_title) . '</a>';
                    }
                }
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
            $statuses = get_terms('arsol-proposal-status', array('hide_empty' => false));
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

            // Related Request filter
            $current_request = isset($_GET['related_request']) ? $_GET['related_request'] : '';
            $requests = get_posts(array(
                'post_type' => 'arsol-pfw-request',
                'posts_per_page' => -1,
                'orderby' => 'title',
                'order' => 'ASC',
                'post_status' => 'publish'
            ));
            echo '<select name="related_request" id="filter-by-related-request" class="select2-hidden-accessible enhanced" data-placeholder="' . esc_attr__('Filter by related request', 'arsol-pfw') . '" data-allow_clear="true">';
            echo '<option value="">' . __('Filter by related request', 'arsol-pfw') . '</option>';
            foreach ($requests as $request) {
                printf(
                    '<option value="%s" %s>%s</option>',
                    esc_attr($request->ID),
                    selected($current_request, $request->ID, false),
                    esc_html($request->post_title)
                );
            }
            echo '</select>';

            // Enqueue WooCommerce select2 and customer search
            wp_enqueue_script('selectWoo');
            wp_enqueue_style('select2');
            wp_enqueue_script('wc-enhanced-select');
            
            // Add inline script to initialize WooCommerce customer search
            add_action('admin_footer', function() {
                ?>
                <script type="text/javascript">
                jQuery(function($) {
                    // Initialize WooCommerce customer search
                    $('.wc-customer-search').selectWoo({
                        ajax: {
                            url: wc_enhanced_select_params.ajax_url,
                            dataType: 'json',
                            delay: 250,
                            data: function (params) {
                                return {
                                    term: params.term,
                                    action: 'woocommerce_json_search_customers',
                                    security: $(this).attr('data-security'),
                                    exclude: []
                                };
                            },
                            processResults: function (data) {
                                var terms = [];
                                if (data) {
                                    $.each(data, function (id, text) {
                                        terms.push({
                                            id: id,
                                            text: text
                                        });
                                    });
                                }
                                return {
                                    results: terms
                                };
                            },
                            cache: true
                        },
                        placeholder: $(this).attr('data-placeholder'),
                        allowClear: $(this).attr('data-allow_clear') === 'true',
                        minimumInputLength: 1
                    }).addClass('enhanced');
                    
                    // Initialize other select2 dropdowns
                    $('.select2-enhanced').selectWoo({
                        allowClear: true,
                        minimumResultsForSearch: 0,
                        placeholder: function(){
                            return $(this).data('placeholder');
                        }
                    });
                    
                    // Add min-width and spacing to all select2 containers
                    $('.select2-container').css({'min-width': '200px', 'margin-right': '8px'});
                });
                </script>
                <?php
            });
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

            // Filter by proposal status (taxonomy)
            if (!empty($_GET['proposal_status'])) {
                $tax_query = $query->get('tax_query') ?: [];
                $tax_query[] = [
                    'taxonomy' => 'arsol-proposal-status',
                    'field'    => 'slug',
                    'terms'    => sanitize_text_field($_GET['proposal_status']),
                ];
                $query->set('tax_query', $tax_query);
            }

            // Filter by related request (meta)
            if (!empty($_GET['related_request'])) {
                $meta_query = $query->get('meta_query') ?: [];
                $meta_query[] = [
                    'key' => '_related_request',
                    'value' => sanitize_text_field($_GET['related_request']),
                    'compare' => '='
                ];
                $query->set('meta_query', $meta_query);
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
            wp_set_object_terms($post_id, $status, 'arsol-proposal-status', false);
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
