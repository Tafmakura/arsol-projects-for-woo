<?php
/**
 * Admin Users Class - WordPress Native Capabilities
 *
 * Handles user-related functionality with WordPress-native capabilities for Arsol Projects For Woo.
 * Updated to work with the new WordPress-native capability system.
 *
 * @package Arsol_Projects_For_Woo
 * @since 2.0.0
 */

namespace Arsol_Projects_For_Woo\Admin;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class Users
 *
 * Manages user-related functionality with WordPress-native capabilities.
 *
 * @package Arsol_Projects_For_Woo\Admin\Users
 * @since 2.0.0
 */
class Users {
    
    /**
     * Constructor
     *
     * Initialize the admin users functionality.
     */
    public function __construct() {
        $this->init_hooks();
    }
    
    /**
     * Initialize WordPress hooks and filters
     *
     * @return void
     */
    private function init_hooks() {
        // Admin menu hooks
        add_action('admin_menu', array($this, 'add_admin_menu'));
        
        // User profile hooks
        add_action('show_user_profile', array($this, 'add_user_profile_fields'));
        add_action('edit_user_profile', array($this, 'add_user_profile_fields'));
        add_action('personal_options_update', array($this, 'save_user_profile_fields'));
        add_action('edit_user_profile_update', array($this, 'save_user_profile_fields'));
        
        // User list customization
        add_filter('manage_users_columns', array($this, 'add_user_columns'));
        add_filter('manage_users_custom_column', array($this, 'fill_user_columns'), 10, 3);
        
        // Ajax handlers
        add_action('wp_ajax_arsol_pfw_user_action', array($this, 'handle_ajax_user_action'));
        add_action('wp_ajax_arsol_json_search_project_leads', array($this, 'json_search_project_leads'));
        
        // New user registration hooks
        add_action('user_register', array($this, 'set_default_user_permission'));
    }
    
    /**
     * Add admin menu for user management
     *
     * @return void
     */
    public function add_admin_menu() {
        add_submenu_page(
            'edit.php?post_type=arsol-pfw-project',
            __('PFW Users', 'arsol-pfw'),
            __('Users', 'arsol-pfw'),
            'arsol_pfw_manage',
            'arsol-pfw-users',
            array($this, 'render_admin_page')
        );
    }
    
    /**
     * Render the admin page for user management
     *
     * @return void
     */
    public function render_admin_page() {
        ?>
        <div class="wrap">
            <h1><?php echo esc_html__('PFW Users Management', 'arsol-pfw'); ?></h1>
            <div id="arsol-users-admin">
                <?php $this->render_users_table(); ?>
            </div>
        </div>
        <?php
    }
    
    /**
     * Render users table showing users with PFW capabilities
     *
     * @return void
     */
    private function render_users_table() {
        // Get all users with any PFW capabilities
        $users = get_users(array(
            'meta_query' => array(
                'relation' => 'OR',
                array(
                    'key' => 'wp_capabilities',
                    'value' => 'arsol_pfw_manage',
                    'compare' => 'LIKE'
                ),
                array(
                    'key' => 'wp_capabilities',
                    'value' => 'edit_arsol_pfw_projects',
                    'compare' => 'LIKE'
                ),
                array(
                    'key' => 'wp_capabilities',
                    'value' => 'edit_arsol_pfw_proposals',
                    'compare' => 'LIKE'
                ),
                array(
                    'key' => 'wp_capabilities',
                    'value' => 'edit_arsol_pfw_requests',
                    'compare' => 'LIKE'
                ),
            )
        ));
        
        ?>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th><?php esc_html_e('User', 'arsol-pfw'); ?></th>
                    <th><?php esc_html_e('Email', 'arsol-pfw'); ?></th>
                    <th><?php esc_html_e('Role', 'arsol-pfw'); ?></th>
                    <th><?php esc_html_e('PFW Capabilities', 'arsol-pfw'); ?></th>
                    <th><?php esc_html_e('Projects', 'arsol-pfw'); ?></th>
                    <th><?php esc_html_e('Actions', 'arsol-pfw'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($users)) : ?>
                    <tr>
                        <td colspan="6"><?php esc_html_e('No PFW users found. Assign capabilities to roles in Settings → General.', 'arsol-pfw'); ?></td>
                    </tr>
                <?php else : ?>
                    <?php foreach ($users as $user) : ?>
                        <tr>
                            <td><?php echo esc_html($user->display_name); ?></td>
                            <td><?php echo esc_html($user->user_email); ?></td>
                            <td><?php echo esc_html(implode(', ', $user->roles)); ?></td>
                            <td><?php echo esc_html($this->get_user_pfw_capabilities($user)); ?></td>
                            <td><?php echo esc_html($this->get_user_project_count($user->ID)); ?></td>
                            <td>
                                <a href="<?php echo esc_url(get_edit_user_link($user->ID)); ?>" class="button button-small">
                                    <?php esc_html_e('Edit', 'arsol-pfw'); ?>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
        <div class="arsol-pfw-admin-notice">
            <p><strong><?php esc_html_e('Note:', 'arsol-pfw'); ?></strong> <?php esc_html_e('PFW capabilities are assigned to roles through Settings → General. This page shows users who currently have PFW capabilities based on their role assignments.', 'arsol-pfw'); ?></p>
        </div>
        <?php
    }
    
    /**
     * Get user's PFW capabilities as a readable string
     *
     * @param WP_User $user User object
     * @return string Formatted capabilities string
     */
    private function get_user_pfw_capabilities($user) {
        $capabilities = array();
        
        if ($user->has_cap('arsol_pfw_manage')) {
            $capabilities[] = __('Manager', 'arsol-pfw');
        }
        if ($user->has_cap('edit_arsol_pfw_projects')) {
            $capabilities[] = __('Projects', 'arsol-pfw');
        }
        if ($user->has_cap('edit_arsol_pfw_proposals')) {
            $capabilities[] = __('Proposals', 'arsol-pfw');
        }
        if ($user->has_cap('edit_arsol_pfw_requests')) {
            $capabilities[] = __('Requests', 'arsol-pfw');
        }
        
        return empty($capabilities) ? __('None', 'arsol-pfw') : implode(', ', $capabilities);
    }
    
    /**
     * Add custom fields to user profile for PFW capabilities
     *
     * @param WP_User $user The user object
     * @return void
     */
    public function add_user_profile_fields($user) {
        // Only show this to administrators or users with manage capability
        if (!current_user_can('manage_options') && !current_user_can('arsol_pfw_manage')) {
            return;
        }
        
        // Get current settings
        $settings = get_option('arsol_pfw_general_settings', array());
        $global_permission = isset($settings['user_project_permissions']) ? $settings['user_project_permissions'] : 'request';
        $user_permission = get_user_meta($user->ID, 'arsol_pfw_user_permission', true);
        if (empty($user_permission)) {
            $user_permission = isset($settings['default_user_permission']) ? $settings['default_user_permission'] : 'request';
        }
        
        // Determine effective permission for display
        $effective_level = $this->get_effective_user_permission($user->ID);
        
        ?>
        <h3><?php esc_html_e('Project Settings', 'arsol-pfw'); ?></h3>
        <table class="form-table">
            <?php if ($global_permission === 'user_specific') : ?>
            <tr>
                <th><label for="arsol_pfw_user_permission"><?php esc_html_e('Frontend Permissions', 'arsol-pfw'); ?></label></th>
                <td>
                    <select name="arsol_pfw_user_permission" id="arsol_pfw_user_permission">
                        <option value="none" <?php selected($user_permission, 'none'); ?>><?php esc_html_e('None', 'arsol-pfw'); ?></option>
                        <option value="request" <?php selected($user_permission, 'request'); ?>><?php esc_html_e('Can request projects', 'arsol-pfw'); ?></option>
                        <option value="create" <?php selected($user_permission, 'create'); ?>><?php esc_html_e('Can create projects', 'arsol-pfw'); ?></option>
                    </select>
                    <p class="description">
                        <?php esc_html_e('This individual permission controls frontend access for creating and requesting projects.', 'arsol-pfw'); ?>
                    </p>
                </td>
            </tr>
            <?php else : ?>
            <tr>
                <th><label for="arsol_pfw_user_permission_disabled"><?php esc_html_e('Frontend Permissions', 'arsol-pfw'); ?></label></th>
                <td>
                    <select name="arsol_pfw_user_permission_disabled" id="arsol_pfw_user_permission_disabled" disabled>
                    <?php 
                        // Show the current effective permission based on global setting
                        $current_option = '';
                        $current_label = '';
                        
                        if ($global_permission === 'none') {
                            $current_option = 'none';
                            $current_label = __('None (Global Setting)', 'arsol-pfw');
                        } elseif ($global_permission === 'request') {
                            $current_option = 'request';
                            $current_label = __('Can request projects (Global Setting)', 'arsol-pfw');
                        } elseif ($global_permission === 'create') {
                            $current_option = 'create';
                            $current_label = __('Can create projects (Global Setting)', 'arsol-pfw');
                        }
                        ?>
                        <option value="<?php echo esc_attr($current_option); ?>" selected><?php echo esc_html($current_label); ?></option>
                    </select>
                    <p class="description">
                        <?php 
                            printf(
                            esc_html__('Individual permissions are disabled. Global setting is "%s". Change to "Set per user" in %s to enable individual permissions.', 'arsol-pfw'),
                            esc_html($global_permission),
                            '<a href="' . esc_url(admin_url('edit.php?post_type=arsol-pfw-project&page=arsol-pfw-settings-general')) . '">' . esc_html__('Settings → General', 'arsol-pfw') . '</a>'
                            );
                        ?>
                    </p>
                </td>
            </tr>
            <?php endif; ?>
            <tr>
                <th><label><?php esc_html_e('Effective Permission Level', 'arsol-pfw'); ?></label></th>
                <td>
                    <?php
                    $level_descriptions = array(
                        'none' => __('This user cannot create or request projects on the frontend.', 'arsol-pfw'),
                        'creator' => __('This user can create and request projects they own, and view projects assigned to them.', 'arsol-pfw'),
                        'manager' => __('This user can manage the plugin, edit all projects, proposals, and requests regardless of ownership.', 'arsol-pfw')
                    );
                    
                    $level_labels = array(
                        'none' => __('None', 'arsol-pfw'),
                        'creator' => __('Creator', 'arsol-pfw'),
                        'manager' => __('Manager', 'arsol-pfw')
                    );
                    
                    echo '<strong>' . esc_html($level_labels[$effective_level]) . '</strong>';
                    ?>
                    <p class="description">
                        <?php echo esc_html($level_descriptions[$effective_level]); ?>
                    </p>
                </td>
            </tr>
        </table>
        <?php
    }
    
    /**
     * Save user profile fields for PFW capabilities
     *
     * @param int $user_id User ID
     * @return void
     */
    public function save_user_profile_fields($user_id) {
        // Only allow administrators or users with manage capability to save
        if (!current_user_can('manage_options') && !current_user_can('arsol_pfw_manage')) {
            return;
        }
        
        // Only save individual permission if it's posted
        if (isset($_POST['arsol_pfw_user_permission'])) {
            $permission = sanitize_text_field($_POST['arsol_pfw_user_permission']);
            
            // Validate the permission value
            $valid_permissions = array('none', 'request', 'create');
            if (in_array($permission, $valid_permissions)) {
                update_user_meta($user_id, 'arsol_pfw_user_permission', $permission);
            }
        }
    }
    
    /**
     * Add custom columns to user list
     *
     * @param array $columns Default columns
     * @return array Modified columns
     */
    public function add_user_columns($columns) {
        $columns['pfw_capabilities'] = __('PFW Capabilities', 'arsol-pfw');
        return $columns;
    }
    
    /**
     * Fill custom columns in user list
     *
     * @param string $value Column value
     * @param string $column_name Column name
     * @param int $user_id User ID
     * @return string Column content
     */
    public function fill_user_columns($value, $column_name, $user_id) {
        if ($column_name === 'pfw_capabilities') {
            $user = get_userdata($user_id);
            if ($user) {
                return $this->get_user_pfw_capabilities($user);
            }
        }
        return $value;
    }
    
    /**
     * Get project count for a user
     *
     * @param int $user_id User ID
     * @return int Project count
     */
    private function get_user_project_count($user_id) {
        $count = 0;
        
        // Count projects
        $projects = get_posts(array(
            'post_type' => 'arsol-pfw-project',
            'author' => $user_id,
            'post_status' => 'any',
            'numberposts' => -1,
            'fields' => 'ids'
        ));
        $count += count($projects);
        
        // Count proposals
        $proposals = get_posts(array(
            'post_type' => 'arsol-pfw-proposal',
            'author' => $user_id,
            'post_status' => 'any',
            'numberposts' => -1,
            'fields' => 'ids'
        ));
        $count += count($proposals);
        
        // Count requests
        $requests = get_posts(array(
            'post_type' => 'arsol-pfw-request',
            'author' => $user_id,
            'post_status' => 'any',
            'numberposts' => -1,
            'fields' => 'ids'
        ));
        $count += count($requests);
        
        return $count;
    }
    
    /**
     * Handle AJAX user actions
     *
     * @return void
     */
    public function handle_ajax_user_action() {
        check_ajax_referer('arsol_pfw_user_action', 'nonce');
        
        // No AJAX actions needed for capability management
        // Capabilities are managed through role assignments in settings
        wp_send_json_error(__('User capabilities are managed through role assignments in Settings → General.', 'arsol-pfw'));
    }

    // ========================================
    // UPDATED CAPABILITY HELPER METHODS
    // ========================================

    /**
     * Check if user can create projects (uses WordPress-native capabilities)
     *
     * @param int $user_id User ID
     * @return bool Whether user can create projects
     */
    public function can_user_create_projects($user_id) {
        // First check global permission setting
        $settings = get_option('arsol_pfw_general_settings', array());
        $global_permission = isset($settings['user_project_permissions']) ? $settings['user_project_permissions'] : 'request';
        
        // If global permission is "none", nobody can create projects
        if ($global_permission === 'none') {
            return false;
        }
        
        // If global permission is "create", allow anyone with WordPress capabilities to create
        if ($global_permission === 'create') {
            return \Arsol_Projects_For_Woo\Core\Capabilities_Handler::can_create_projects($user_id);
        }
        
        // If global permission is "request", don't allow creating projects (only requesting)
        if ($global_permission === 'request') {
            // Only allow if user has management capabilities
            return \Arsol_Projects_For_Woo\Core\Capabilities_Handler::can_manage_projects($user_id);
        }
        
        // If global permission is "user_specific", check individual user permission
        if ($global_permission === 'user_specific') {
            $user_permission = get_user_meta($user_id, 'arsol_pfw_user_permission', true);
            
            // If user permission is "create", check WordPress capabilities
            if ($user_permission === 'create') {
                return \Arsol_Projects_For_Woo\Core\Capabilities_Handler::can_create_projects($user_id);
            }
            
            // If user permission is "request" or "none", don't allow creating
            // Only allow if user has management capabilities
            return \Arsol_Projects_For_Woo\Core\Capabilities_Handler::can_manage_projects($user_id);
        }
        
        // Default fallback - use WordPress capabilities
        return \Arsol_Projects_For_Woo\Core\Capabilities_Handler::can_create_projects($user_id);
    }

    /**
     * Check if user can request projects (uses WordPress-native capabilities)
     *
     * @param int $user_id User ID
     * @return bool Whether user can request projects
     */
    public function can_user_request_projects($user_id) {
        // First check global permission setting
        $settings = get_option('arsol_pfw_general_settings', array());
        $global_permission = isset($settings['user_project_permissions']) ? $settings['user_project_permissions'] : 'request';
        
        // If global permission is "none", nobody can request projects
        if ($global_permission === 'none') {
            return false;
        }
        
        // If global permission is "request" or "create", allow anyone with WordPress capabilities to request
        if ($global_permission === 'request' || $global_permission === 'create') {
            return \Arsol_Projects_For_Woo\Core\Capabilities_Handler::can_create_project_requests($user_id);
        }
        
        // If global permission is "user_specific", check individual user permission
        if ($global_permission === 'user_specific') {
            $user_permission = get_user_meta($user_id, 'arsol_pfw_user_permission', true);
            
            // If user permission is "request" or "create", check WordPress capabilities
            if ($user_permission === 'request' || $user_permission === 'create') {
                return \Arsol_Projects_For_Woo\Core\Capabilities_Handler::can_create_project_requests($user_id);
            }
            
            // If user permission is "none", don't allow requesting
            // Only allow if user has management capabilities
            return \Arsol_Projects_For_Woo\Core\Capabilities_Handler::can_manage_projects($user_id);
        }
        
        // Default fallback - use WordPress capabilities
        return \Arsol_Projects_For_Woo\Core\Capabilities_Handler::can_create_project_requests($user_id);
    }

    /**
     * Get effective user permission level (uses WordPress-native capabilities)
     *
     * @param int $user_id User ID
     * @return string User permission level ('none', 'creator', 'manager')
     */
    public function get_effective_user_permission($user_id) {
        // First check if user has management capabilities
        if (\Arsol_Projects_For_Woo\Core\Capabilities_Handler::can_manage_projects($user_id)) {
            return 'manager';
        }
        
        // Check global permission setting
        $settings = get_option('arsol_pfw_general_settings', array());
        $global_permission = isset($settings['user_project_permissions']) ? $settings['user_project_permissions'] : 'request';
        
        // If global permission is "none", nobody can do anything
        if ($global_permission === 'none') {
            return 'none';
        }
        
        // If global permission is "request" or "create", check WordPress capabilities
        if ($global_permission === 'request' || $global_permission === 'create') {
            if (\Arsol_Projects_For_Woo\Core\Capabilities_Handler::can_create_projects($user_id) || 
                \Arsol_Projects_For_Woo\Core\Capabilities_Handler::can_create_project_requests($user_id)) {
                return 'creator';
            }
        }
        
        // If global permission is "user_specific", check individual user permission
        if ($global_permission === 'user_specific') {
            $user_permission = get_user_meta($user_id, 'arsol_pfw_user_permission', true);
            
            // If user permission is "request" or "create", check WordPress capabilities
            if ($user_permission === 'request' || $user_permission === 'create') {
                if (\Arsol_Projects_For_Woo\Core\Capabilities_Handler::can_create_projects($user_id) || 
                    \Arsol_Projects_For_Woo\Core\Capabilities_Handler::can_create_project_requests($user_id)) {
                    return 'creator';
                }
            }
        }
        
        return 'none';
    }

    // ========================================
    // PROJECT LEAD FUNCTIONALITY (UPDATED)
    // ========================================

    /**
     * AJAX handler for project lead search
     * Returns JSON formatted project leads for Select2 AJAX
     */
    public function json_search_project_leads() {
        check_ajax_referer('search-project-leads', 'security');
        
        $term = isset($_GET['term']) ? sanitize_text_field($_GET['term']) : '';
        $limit = isset($_GET['limit']) ? absint($_GET['limit']) : 20;
        
        $results = array();
        
        if (strlen($term) < 1) {
            wp_die();
        }
        
        // Search users who can manage projects or have PFW capabilities
        $user_args = array(
            'search' => '*' . $term . '*',
            'search_columns' => array('display_name', 'user_login', 'user_email', 'user_nicename'),
            'number' => $limit,
            'fields' => array('ID', 'display_name', 'user_email', 'first_name', 'last_name'),
            'meta_query' => array(
                'relation' => 'OR',
                array(
                    'key' => 'wp_capabilities',
                    'value' => 'arsol_pfw_manage',
                    'compare' => 'LIKE'
                ),
                array(
                    'key' => 'wp_capabilities',
                    'value' => 'edit_arsol_pfw_projects',
                    'compare' => 'LIKE'
                ),
                array(
                    'key' => 'wp_capabilities',
                    'value' => 'manage_options',
                    'compare' => 'LIKE'
                ),
            )
        );
        
        $users = get_users($user_args);
        
        foreach ($users as $user) {
            $formatted_name = arsol_pfw_format_user($user->ID, 'name_email');
            $results[$user->ID] = $formatted_name;
        }                $display_name = trim($user->first_name . ' ' . $user->last_name);
        
        wp_send_json($results);
    }
    
    /**
     * Render a project lead search select field
     * AJAX-enabled search field for project leads
     *
     * @param array $args Array of arguments for the select field
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
        
        $classes = 'arsol-pfw-project-lead-search';
        if (!empty($args['class'])) {
            $classes .= ' ' . esc_attr($args['class']);
        }
        
        echo '<select name="' . esc_attr($args['name']) . '" id="' . esc_attr($args['id']) . '" class="' . esc_attr($classes) . '" data-placeholder="' . esc_attr($args['placeholder']) . '" data-allow_clear="true" data-action="arsol_json_search_project_leads" data-security="' . wp_create_nonce('search-project-leads') . '">';
        echo '<option value="">' . esc_html($args['placeholder']) . '</option>';
        
        // If there's a selected value, add it as an option
        if (!empty($args['selected'])) {
            $selected_user = get_userdata($args['selected']);
            if ($selected_user) {
                $display_name = arsol_pfw_format_user($args['selected']);
                $display_name = wp_strip_all_tags($display_name);
                echo '<option value="' . esc_attr($args['selected']) . '" selected="selected">' . esc_html($display_name) . '</option>';
            }
        }
        
        echo '</select>';
    }
    
    /**
     * Get users who can manage projects (WordPress-native capabilities)
     *
     * @return array Array of user IDs who can manage projects
     */
    public static function get_project_lead_user_ids() {
        $users = get_users(array(
            'fields' => 'ID',
            'meta_query' => array(
                'relation' => 'OR',
                array(
                    'key' => 'wp_capabilities',
                    'value' => 'arsol_pfw_manage',
                    'compare' => 'LIKE'
                ),
                array(
                    'key' => 'wp_capabilities',
                    'value' => 'edit_arsol_pfw_projects',
                    'compare' => 'LIKE'
                ),
                array(
                    'key' => 'wp_capabilities',
                    'value' => 'manage_options',
                    'compare' => 'LIKE'
                ),
            )
        ));
        
        return $users;
    }
    
    /**
     * Format project lead display name
     *
     * @param int $user_id User ID
     * @return string Formatted display name
     */
    public static function format_project_lead_display($user_id) {
        $user = get_userdata($user_id);
        if (!$user) {
            return __('Unknown User', 'arsol-pfw');
        }
        
        $display_name = '';
        if (!empty($user->display_name)) {
            $display_name = $user->display_name;
        } elseif (!empty($user->first_name) || !empty($user->last_name)) {
            $display_name = trim($user->first_name . ' ' . $user->last_name);
        } else {
            $display_name = $user->user_email;
        }
        
        return $display_name . ' (' . $user->user_email . ')';
    }
    
    /**
     * Create project lead filter link
     *
     * @param int $user_id User ID
     * @param string $post_type Post type
     * @return string Formatted HTML link or fallback display
     */
    public static function create_project_lead_filter_link($user_id, $post_type = 'arsol-pfw-project') {
        if (!$user_id) {
            return '<span class="na">&ndash;</span>';
        }
        
        $user = get_userdata($user_id);
        if (!$user) {
            return '<span class="na">&ndash;</span>';
        }
        
        $display_name = '';
        if (!empty($user->display_name)) {
            $display_name = $user->display_name;
        } elseif (!empty($user->first_name) || !empty($user->last_name)) {
            $display_name = trim($user->first_name . ' ' . $user->last_name);
        } else {
            $display_name = $user->user_email;
        }
        
        // Create filter URL
        $filter_url = add_query_arg(array(
            'post_type' => $post_type,
            'project_lead' => $user_id,
        ), admin_url('edit.php'));
        
        return sprintf(
            '<a href="%s">%s</a>',
            esc_url($filter_url),
            esc_html($display_name)
        );
    }

    /**
     * Set default user permission for new users
     *
     * @param int $user_id User ID
     * @return void
     */
    public function set_default_user_permission($user_id) {
        $settings = get_option('arsol_pfw_general_settings', array());
        $global_permission = isset($settings['user_project_permissions']) ? $settings['user_project_permissions'] : 'request';
        
        // Only set individual permission if global setting is "user_specific"
        if ($global_permission === 'user_specific') {
            $default_permission = isset($settings['default_user_permission']) ? $settings['default_user_permission'] : 'request';
            update_user_meta($user_id, 'arsol_pfw_user_permission', $default_permission);
        }
    }
}
