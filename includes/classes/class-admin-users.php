<?php
/**
 * Admin Users Class
 *
 * Handles user-related functionality in the admin area for Arsol Projects For Woo.
 *
 * @package Arsol_Projects_For_Woo
 * @since 1.0.0
 */

namespace Arsol_Projects_For_Woo\Admin;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class Admin_Users
 *
 * Manages user-related functionality in the WordPress admin area.
 *
 * @package Arsol_Projects_For_Woo\Admin
 * @since 1.0.0
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
        
        // Set default permission for new users
        add_action('user_register', array($this, 'set_default_user_permission'));
        
        // Ajax handlers
        add_action('wp_ajax_arsol_pfw_user_action', array($this, 'handle_ajax_user_action'));
    }
    
    /**
     * Add admin menu for user management
     *
     * @return void
     */
    public function add_admin_menu() {
        add_submenu_page(
            'edit.php?post_type=arsol_project', // Parent slug (assuming this is your main CPT)
            __('Project Users', 'arsol-pfw'),
            __('Users', 'arsol-pfw'),
            'manage_options',
            'arsol-project-users',
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
            <h1><?php echo esc_html__('Project Users Management', 'arsol-pfw'); ?></h1>
            <div id="arsol-users-admin">
                <?php $this->render_users_table(); ?>
            </div>
        </div>
        <?php
    }
    
    /**
     * Render users table
     *
     * @return void
     */
    private function render_users_table() {
        $users = get_users(array(
            'meta_key' => 'arsol_pfw_project_user',
            'meta_value' => '1',
            'meta_compare' => '=',
            'fields' => 'all'
        ));
        
        ?>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th><?php esc_html_e('User', 'arsol-pfw'); ?></th>
                    <th><?php esc_html_e('Email', 'arsol-pfw'); ?></th>
                    <th><?php esc_html_e('Role', 'arsol-pfw'); ?></th>
                    <th><?php esc_html_e('Projects', 'arsol-pfw'); ?></th>
                    <th><?php esc_html_e('Actions', 'arsol-pfw'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($users)) : ?>
                    <tr>
                        <td colspan="5"><?php esc_html_e('No project users found.', 'arsol-pfw'); ?></td>
                    </tr>
                <?php else : ?>
                    <?php foreach ($users as $user) : ?>
                        <?php
                        $first_name = get_user_meta($user->ID, 'first_name', true);
                        $last_name = get_user_meta($user->ID, 'last_name', true);
                        
                        // Create display name from first + last name
                        $display_name = '';
                        if (!empty($first_name) || !empty($last_name)) {
                            $display_name = trim($first_name . ' ' . $last_name);
                        } else {
                            $display_name = $user->user_login;
                        }
                        ?>
                        <tr>
                            <td><?php echo esc_html($display_name); ?></td>
                            <td><?php echo esc_html($user->user_email); ?></td>
                            <td><?php echo esc_html(implode(', ', $user->roles)); ?></td>
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
        <?php
    }
    
    /**
     * Add custom fields to user profile
     *
     * @param WP_User $user The user object
     * @return void
     */
    public function add_user_profile_fields($user) {
        // Check if user has project management capability
        if (!$user->has_cap('arsol-manage-projects')) {
            return;
        }

        ?>
        <h3><?php esc_html_e('Arsol Project Settings', 'arsol-pfw'); ?></h3>
        <table class="form-table">
            <tr>
                <th><label for="arsol_pfw_project_user"><?php esc_html_e('Project Permissions', 'arsol-pfw'); ?></label></th>
                <td>
                    <?php 
                    $current_permission = get_user_meta($user->ID, 'arsol_pfw_project_permission', true);
                    // Handle legacy values - if it's '1' from old checkbox, default to 'request'
                    if ($current_permission === '1') {
                        $current_permission = 'request';
                    }
                    
                    // Get the effective permission to show what the user actually has
                    $effective_permission = $this->get_effective_user_permission($user->ID);
                    
                    // For display purposes, show the individual setting or the effective permission
                    $display_permission = !empty($current_permission) ? $current_permission : $effective_permission;
                    
                    // Check if global settings override individual settings
                    $global_settings = get_option('arsol_projects_settings', array());
                    $global_permission = isset($global_settings['user_project_permissions']) ? $global_settings['user_project_permissions'] : 'none';
                    $is_overridden = $global_permission !== 'user_specific';
                    ?>
                    <select id="arsol_pfw_project_user" 
                            name="arsol_pfw_project_user"
                            class="arsol-user-permission-select <?php echo $is_overridden ? 'arsol-permission-overridden' : ''; ?>"
                            <?php echo $is_overridden ? 'disabled' : ''; ?>>
                        <option value="none" <?php selected($display_permission, 'none'); ?>><?php esc_html_e('None', 'arsol-pfw'); ?></option>
                        <option value="request" <?php selected($display_permission, 'request'); ?>><?php esc_html_e('User can request projects', 'arsol-pfw'); ?></option>
                        <option value="create" <?php selected($display_permission, 'create'); ?>><?php esc_html_e('User can create projects', 'arsol-pfw'); ?></option>
                    </select>
                    <p class="description">
                        <?php 
                        if ($is_overridden) {
                            printf(
                                esc_html__('Using general settings override: "%s"', 'arsol-pfw'),
                                esc_html($this->get_permission_label($global_permission))
                            );
                        } else {
                            esc_html_e('Select the level of project permissions for this user.', 'arsol-pfw');
                        }
                        ?>
                    </p>
                </td>
            </tr>
        </table>
        <?php
    }
    
    /**
     * Save user profile fields
     *
     * @param int $user_id The user ID
     * @return void
     */
    public function save_user_profile_fields($user_id) {
        if (!current_user_can('edit_user', $user_id)) {
            return;
        }

        // Check if user has project management capability
        $user = get_user_by('id', $user_id);
        if (!$user || !$user->has_cap('arsol-manage-projects')) {
            return;
        }
        
        // Save project user setting
        if (isset($_POST['arsol_pfw_project_user'])) {
            $project_permission = sanitize_text_field($_POST['arsol_pfw_project_user']);
            // Validate the value
            $allowed_values = array('none', 'request', 'create');
            if (in_array($project_permission, $allowed_values)) {
                update_user_meta($user_id, 'arsol_pfw_project_permission', $project_permission);
            }
        }
    }
    
    /**
     * Add custom columns to users list
     *
     * @param array $columns Existing columns
     * @return array Modified columns
     */
    public function add_user_columns($columns) {
        $columns['arsol_projects'] = __('Projects', 'arsol-pfw');
        return $columns;
    }
    
    /**
     * Fill custom user columns
     *
     * @param string $value Column value
     * @param string $column_name Column name
     * @param int $user_id User ID
     * @return string Column content
     */
    public function fill_user_columns($value, $column_name, $user_id) {
        if ($column_name === 'arsol_projects') {
            $project_count = $this->get_user_project_count($user_id);
            $user_permission = $this->get_effective_user_permission($user_id);
            
            if ($user_permission !== 'none') {
                return sprintf('%d %s (%s)', $project_count, __('projects', 'arsol-pfw'), $this->get_permission_label($user_permission));
            } else {
                return '<span class="dashicons dashicons-minus" title="' . esc_attr__('No project permissions', 'arsol-pfw') . '"></span>';
            }
        }
        
        return $value;
    }
    
    /**
     * Get count of projects for a user
     *
     * @param int $user_id User ID
     * @return int Project count
     */
    private function get_user_project_count($user_id) {
        $projects = get_posts(array(
            'post_type' => 'arsol_project',
            'author' => $user_id,
            'post_status' => 'any',
            'numberposts' => -1,
            'fields' => 'ids'
        ));
        
        return count($projects);
    }
    
    /**
     * Handle AJAX user actions
     *
     * @return void
     */
    public function handle_ajax_user_action() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'arsol_pfw_user_action')) {
            wp_die(__('Security check failed', 'arsol-pfw'));
        }
        
        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_die(__('Insufficient permissions', 'arsol-pfw'));
        }
        
        $action = sanitize_text_field($_POST['action_type']);
        $user_id = absint($_POST['user_id']);
        
        switch ($action) {
            case 'toggle_project_user':
                $current_value = get_user_meta($user_id, 'arsol_pfw_project_user', true);
                $new_value = $current_value ? '0' : '1';
                update_user_meta($user_id, 'arsol_pfw_project_user', $new_value);
                wp_send_json_success(array('new_value' => $new_value));
                break;
                
            default:
                wp_send_json_error(__('Invalid action', 'arsol-pfw'));
        }
    }
    
    /**
     * Check if user can create projects
     *
     * @param int $user_id User ID
     * @return bool Whether user can create projects
     */
    public function can_user_create_projects($user_id) {
        return $this->get_effective_user_permission($user_id) === 'create';
    }

    /**
     * Check if user can request projects
     *
     * @param int $user_id User ID
     * @return bool Whether user can request projects
     */
    public function can_user_request_projects($user_id) {
        $permission = $this->get_effective_user_permission($user_id);
        return in_array($permission, array('request', 'create'));
    }

    /**
     * Get effective user permission based on global and individual settings
     *
     * @param int $user_id User ID
     * @return string User permission level ('none', 'request', 'create')
     */
    public function get_effective_user_permission($user_id) {
        $global_settings = get_option('arsol_projects_settings', array());
        $global_permission = isset($global_settings['user_project_permissions']) ? $global_settings['user_project_permissions'] : 'none';
        
        // If global setting is not 'user_specific', it overrides individual settings
        if ($global_permission !== 'user_specific') {
            return $global_permission;
        }
        
        // For user_specific mode, check individual user permission
        $user_permission = get_user_meta($user_id, 'arsol_pfw_project_permission', true);
        
        // If no individual setting, use default for new users
        if (empty($user_permission)) {
            $default_permission = isset($global_settings['default_user_permission']) ? $global_settings['default_user_permission'] : 'none';
            return $default_permission;
        }
        
        return $user_permission;
    }

    /**
     * Set default user permission when a new user is registered
     *
     * @param int $user_id The user ID
     * @return void
     */
    public function set_default_user_permission($user_id) {
        $global_settings = get_option('arsol_projects_settings', array());
        $global_permission = isset($global_settings['user_project_permissions']) ? $global_settings['user_project_permissions'] : 'none';
        
        // Only set individual permission if we're in user_specific mode
        if ($global_permission === 'user_specific') {
            $default_permission = isset($global_settings['default_user_permission']) ? $global_settings['default_user_permission'] : 'none';
            update_user_meta($user_id, 'arsol_pfw_project_permission', $default_permission);
        }
    }

    /**
     * Get permission label based on permission level
     *
     * @param string $permission Permission level ('none', 'request', 'create')
     * @return string Permission label
     */
    private function get_permission_label($permission) {
        $labels = array(
            'none' => esc_html__('None', 'arsol-pfw'),
            'request' => esc_html__('Can request projects', 'arsol-pfw'),
            'create' => esc_html__('Can create projects', 'arsol-pfw')
        );
        return isset($labels[$permission]) ? $labels[$permission] : esc_html__('Unknown', 'arsol-pfw');
    }
    
    // =============================
    // USER ROLE MANAGEMENT METHODS
    // =============================
    
    /**
     * Get all available user roles
     * 
     * @param bool $include_labels Whether to include role labels
     * @return array Array of roles or role names with labels
     */
    public function get_all_user_roles($include_labels = true) {
        global $wp_roles;
        
        if (!isset($wp_roles)) {
            $wp_roles = new \WP_Roles();
        }
        
        if ($include_labels) {
            return $wp_roles->get_names();
        } else {
            return array_keys($wp_roles->get_names());
        }
    }
    
    /**
     * Get users by role with formatted display names
     * 
     * @param string|array $roles Role name(s) to filter by
     * @param array $user_query_args Additional arguments for get_users()
     * @param bool $include_email Whether to include email in display names
     * @param string $format Display format: 'standard', 'customer'
     * @return array Array of user objects with formatted display names
     */
    public function get_users_by_role($roles, $user_query_args = array(), $include_email = false, $format = 'standard') {
        $default_args = array(
            'fields' => array('ID', 'user_login', 'user_email'),
            'role__in' => is_array($roles) ? $roles : array($roles),
            'meta_query' => array(
                'relation' => 'OR',
                array(
                    'key' => 'first_name',
                    'value' => '',
                    'compare' => '!='
                ),
                array(
                    'key' => 'last_name',
                    'value' => '',
                    'compare' => '!='
                )
            ),
            'orderby' => 'meta_value',
            'meta_key' => 'first_name'
        );
        
        $args = array_merge($default_args, $user_query_args);
        $users = get_users($args);
        
        $formatted_users = array();
        
        foreach ($users as $user) {
            $user_obj = get_userdata($user->ID);
            $formatted_users[] = (object) array(
                'ID' => $user->ID,
                'user_login' => $user->user_login,
                'user_email' => $user->user_email,
                'display_name' => $this->format_user_display_name($user->ID, $include_email, $format),
                'roles' => $user_obj ? $user_obj->roles : array()
            );
        }
        
        return $formatted_users;
    }
    
    /**
     * Get user roles for a specific user
     * 
     * @param int $user_id User ID
     * @param bool $include_labels Whether to return role labels instead of keys
     * @return array Array of user roles
     */
    public function get_user_roles($user_id, $include_labels = false) {
        $user = get_userdata($user_id);
        
        if (!$user) {
            return array();
        }
        
        if (!$include_labels) {
            return $user->roles;
        }
        
        $role_labels = array();
        $all_roles = $this->get_all_user_roles(true);
        
        foreach ($user->roles as $role) {
            if (isset($all_roles[$role])) {
                $role_labels[$role] = $all_roles[$role];
            }
        }
        
        return $role_labels;
    }
    
    /**
     * Check if user has specific role
     * 
     * @param int $user_id User ID
     * @param string $role Role to check
     * @return bool True if user has the role
     */
    public function user_has_role($user_id, $role) {
        $user = get_userdata($user_id);
        
        if (!$user) {
            return false;
        }
        
        return in_array($role, $user->roles);
    }
    
    /**
     * Check if user has any of the specified roles
     * 
     * @param int $user_id User ID
     * @param array $roles Array of roles to check
     * @return bool True if user has any of the roles
     */
    public function user_has_any_role($user_id, $roles) {
        $user = get_userdata($user_id);
        
        if (!$user) {
            return false;
        }
        
        return !empty(array_intersect($roles, $user->roles));
    }
    
    /**
     * Generate user role dropdown HTML
     * 
     * @param string $name Field name
     * @param string|array $selected Selected value(s)
     * @param string $placeholder Placeholder text
     * @param array $attributes Additional HTML attributes
     * @param bool $multiple Whether to allow multiple selections
     * @return string HTML dropdown
     */
    public function generate_role_dropdown($name, $selected = '', $placeholder = '', $attributes = array(), $multiple = false) {
        $default_attributes = array(
            'class' => 'arsol-pfw-admin-select2',
            'data-allow_clear' => 'true'
        );
        
        if (!empty($placeholder)) {
            $default_attributes['data-placeholder'] = $placeholder;
        }
        
        if ($multiple) {
            $default_attributes['multiple'] = 'multiple';
            $name .= '[]';
        }
        
        $attributes = array_merge($default_attributes, $attributes);
        $attr_string = '';
        
        foreach ($attributes as $key => $value) {
            $attr_string .= sprintf(' %s="%s"', esc_attr($key), esc_attr($value));
        }
        
        $roles = $this->get_all_user_roles(true);
        
        $html = sprintf('<select name="%s"%s>', esc_attr($name), $attr_string);
        
        if (!empty($placeholder) && !$multiple) {
            $html .= '<option value="">' . esc_html($placeholder) . '</option>';
        }
        
        foreach ($roles as $role_key => $role_label) {
            $is_selected = false;
            
            if (is_array($selected)) {
                $is_selected = in_array($role_key, $selected);
            } else {
                $is_selected = ($selected === $role_key);
            }
            
            $html .= sprintf(
                '<option value="%s"%s>%s</option>',
                esc_attr($role_key),
                $is_selected ? ' selected' : '',
                esc_html($role_label)
            );
        }
        
        $html .= '</select>';
        
        return $html;
    }
    
    /**
     * Get users with specific capabilities
     * 
     * @param string|array $capabilities Capability or array of capabilities
     * @param array $user_query_args Additional arguments for get_users()
     * @param bool $include_email Whether to include email in display names
     * @return array Array of user objects with formatted display names
     */
    public function get_users_with_capability($capabilities, $user_query_args = array(), $include_email = false) {
        $capabilities = is_array($capabilities) ? $capabilities : array($capabilities);
        
        $default_args = array(
            'fields' => array('ID', 'user_login', 'user_email'),
            'meta_query' => array(
                'relation' => 'OR',
                array(
                    'key' => 'first_name',
                    'value' => '',
                    'compare' => '!='
                ),
                array(
                    'key' => 'last_name',
                    'value' => '',
                    'compare' => '!='
                )
            ),
            'orderby' => 'meta_value',
            'meta_key' => 'first_name'
        );
        
        $args = array_merge($default_args, $user_query_args);
        $all_users = get_users($args);
        
        $filtered_users = array();
        
        foreach ($all_users as $user) {
            $user_obj = get_userdata($user->ID);
            $has_capability = false;
            
            foreach ($capabilities as $capability) {
                if ($user_obj && $user_obj->has_cap($capability)) {
                    $has_capability = true;
                    break;
                }
            }
            
            if ($has_capability) {
                $filtered_users[] = (object) array(
                    'ID' => $user->ID,
                    'user_login' => $user->user_login,
                    'user_email' => $user->user_email,
                    'display_name' => $this->format_user_display_name($user->ID, $include_email),
                    'roles' => $user_obj ? $user_obj->roles : array()
                );
            }
        }
        
        return $filtered_users;
    }
    
    /**
     * Get role capabilities
     * 
     * @param string $role Role name
     * @return array Array of capabilities for the role
     */
    public function get_role_capabilities($role) {
        $role_obj = get_role($role);
        
        if (!$role_obj) {
            return array();
        }
        
        return array_keys(array_filter($role_obj->capabilities));
    }
    
    /**
     * Format user display name from first + last name (used by role methods)
     * 
     * @param int $user_id User ID
     * @param bool $include_email Whether to include email in parentheses
     * @param string $format Display format: 'standard', 'customer'
     * @return string Formatted display name
     */
    public function format_user_display_name($user_id, $include_email = false, $format = 'standard') {
        $first_name = get_user_meta($user_id, 'first_name', true);
        $last_name = get_user_meta($user_id, 'last_name', true);
        $user = get_userdata($user_id);
        
        if (!$user) {
            return '';
        }
        
        // Create display name from first + last name
        $display_name = '';
        if (!empty($first_name) || !empty($last_name)) {
            $display_name = trim($first_name . ' ' . $last_name);
        } else {
            $display_name = $user->user_login;
        }
        
        // Apply formatting based on format type
        switch ($format) {
            case 'customer':
                // Customer format: "First Last (#ID - email@example.com)"
                if (!empty($user->user_email)) {
                    $display_name .= ' (#' . $user->ID . ' - ' . $user->user_email . ')';
                } else {
                    $display_name .= ' (#' . $user->ID . ')';
                }
                break;
                
            case 'standard':
            default:
                // Standard format: "First Last" or "First Last (email@example.com)"
                if ($include_email && !empty($user->user_email)) {
                    $display_name .= ' (' . $user->user_email . ')';
                }
                break;
        }
        
        return $display_name;
    }
    
    /**
     * Generate user dropdown with role filtering
     * 
     * @param string $name Field name
     * @param string|array $roles Role(s) to filter by
     * @param int|string $selected Selected value
     * @param string $placeholder Placeholder text
     * @param array $attributes Additional HTML attributes
     * @param bool $include_email Whether to include email in display names
     * @return string HTML dropdown
     */
    public function generate_user_dropdown_by_role($name, $roles, $selected = '', $placeholder = '', $attributes = array(), $include_email = false) {
        $default_attributes = array(
            'class' => 'arsol-pfw-admin-select2',
            'data-allow_clear' => 'true'
        );
        
        if (!empty($placeholder)) {
            $default_attributes['data-placeholder'] = $placeholder;
        }
        
        $attributes = array_merge($default_attributes, $attributes);
        $attr_string = '';
        
        foreach ($attributes as $key => $value) {
            $attr_string .= sprintf(' %s="%s"', esc_attr($key), esc_attr($value));
        }
        
        $users = $this->get_users_by_role($roles, array(), $include_email);
        
        $html = sprintf('<select name="%s"%s>', esc_attr($name), $attr_string);
        
        if (!empty($placeholder)) {
            $html .= '<option value="">' . esc_html($placeholder) . '</option>';
        }
        
        foreach ($users as $user) {
            $html .= sprintf(
                '<option value="%s"%s>%s</option>',
                esc_attr($user->ID),
                selected($selected, $user->ID, false),
                esc_html($user->display_name)
            );
        }
        
        $html .= '</select>';
        
        return $html;
    }
}
