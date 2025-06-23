<?php

namespace Arsol_Projects_For_Woo\Classes;

/**
 * Helper class for Arsol Projects for WooCommerce
 * Contains reusable methods used across different CPTs and admin areas
 */

if (!defined('ABSPATH')) {
    exit;
}

class Helper {
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->init_hooks();
    }
    
    /**
     * Initialize hooks
     */
    private function init_hooks() {
        // AJAX handler for project lead search
        add_action('wp_ajax_arsol_pfw_json_search_project_leads', array($this, 'json_search_project_leads'));
    }
    
    /**
     * AJAX handler for searching project lead users
     * Similar to WooCommerce's customer search functionality
     *
     * @return void
     */
    public function json_search_project_leads() {
        // Check security
        check_ajax_referer('search-project-leads', 'security');

        // Check permissions
        if (!current_user_can('edit_posts')) {
            wp_die(-1);
        }

        $term = isset($_GET['term']) ? sanitize_text_field($_GET['term']) : '';
        $exclude = isset($_GET['exclude']) ? array_map('intval', explode(',', $_GET['exclude'])) : array();

        if (empty($term)) {
            wp_die();
        }

        // Get users who can create projects
        $project_lead_users = get_users(array(
            'search' => '*' . $term . '*',
            'search_columns' => array('user_login', 'user_email', 'user_nicename', 'display_name'),
            'exclude' => $exclude,
            'number' => 50, // Limit results
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

        $found_users = array();
        $admin_users_helper = new \Arsol_Projects_For_Woo\Admin\Users();

        // Filter to only users who can actually create projects
        foreach ($project_lead_users as $user) {
            if ($admin_users_helper->can_user_create_projects($user->ID)) {
                $found_users[$user->ID] = sprintf(
                    '%s (#%s - %s)',
                    $user->display_name,
                    $user->ID,
                    $user->user_email
                );
            }
        }

        wp_send_json($found_users);
    }
    
    /**
     * Render a project lead search select field
     * Reusable method for all CPTs and admin areas
     *
     * @param array $args {
     *     Array of arguments for the select field
     *     @type string $name         Field name attribute
     *     @type string $id           Field id attribute
     *     @type string $placeholder  Placeholder text
     *     @type int    $selected     Currently selected user ID
     *     @type string $class        Additional CSS classes
     * }
     * @return void
     */
    public static function render_project_lead_search_field($args = array()) {
        $defaults = array(
            'name' => 'project_lead',
            'id' => 'project_lead',
            'placeholder' => __('Search for project lead...', 'arsol-pfw'),
            'selected' => '',
            'class' => ''
        );
        
        $args = wp_parse_args($args, $defaults);
        
        $classes = 'arsol-project-lead-search';
        if (!empty($args['class'])) {
            $classes .= ' ' . esc_attr($args['class']);
        }
        
        ?>
        <select class="<?php echo esc_attr($classes); ?>" 
                name="<?php echo esc_attr($args['name']); ?>" 
                id="<?php echo esc_attr($args['id']); ?>" 
                data-placeholder="<?php echo esc_attr($args['placeholder']); ?>" 
                data-allow_clear="true" 
                data-action="arsol_pfw_json_search_project_leads" 
                data-security="<?php echo esc_attr(wp_create_nonce('search-project-leads')); ?>">
            <?php if (!empty($args['selected'])): ?>
                <?php 
                $selected_user = get_userdata($args['selected']);
                if ($selected_user) {
                    printf(
                        '<option value="%s" selected="selected">%s (#%s - %s)</option>',
                        esc_attr($selected_user->ID),
                        esc_html($selected_user->display_name),
                        esc_html($selected_user->ID),
                        esc_html($selected_user->user_email)
                    );
                }
                ?>
            <?php endif; ?>
        </select>
        <?php
    }
    
    /**
     * Get users who can create projects
     * Reusable method for getting valid project lead users
     *
     * @return array Array of user IDs who can create projects
     */
    public static function get_project_lead_user_ids() {
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
        
        return $valid_user_ids;
    }
    
    /**
     * Format user display name for project lead fields
     * Consistent formatting across all areas
     *
     * @param int $user_id User ID
     * @return string Formatted display name or fallback
     */
    public static function format_project_lead_display($user_id) {
        if (empty($user_id)) {
            return '<span class="na">&ndash;</span>';
        }
        
        $user = get_userdata($user_id);
        if (!$user) {
            return '<span class="na">&ndash;</span>';
        }
        
        // Priority: display_name -> first/last name -> email
        $display_name = '';
        
        if (!empty($user->display_name)) {
            $display_name = $user->display_name;
        } elseif (!empty($user->first_name) || !empty($user->last_name)) {
            $display_name = trim($user->first_name . ' ' . $user->last_name);
        } elseif (!empty($user->user_email)) {
            $display_name = $user->user_email;
        } else {
            $display_name = __('Unknown Lead', 'arsol-pfw');
        }
        
        return esc_html($display_name);
    }
    
    /**
     * Create a filter link for project lead in admin columns
     * Reusable method for creating clickable filter links
     *
     * @param int    $user_id   User ID
     * @param string $post_type Post type for the filter URL
     * @return string HTML link or fallback display
     */
    public static function create_project_lead_filter_link($user_id, $post_type = 'arsol-project') {
        if (empty($user_id)) {
            return '<span class="na">&ndash;</span>';
        }
        
        $user = get_userdata($user_id);
        if (!$user) {
            return '<span class="na">&ndash;</span>';
        }
        
        $display_name = self::format_project_lead_display($user_id);
        
        // Create filter URL
        $filter_url = add_query_arg(array(
            'post_type' => $post_type,
            'project_lead' => $user->ID
        ), admin_url('edit.php'));
        
        return sprintf(
            '<a href="%s">%s</a>',
            esc_url($filter_url),
            $display_name
        );
    }
}
