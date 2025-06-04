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
            'meta_compare' => '='
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
                        <tr>
                            <td><?php echo esc_html($user->display_name); ?></td>
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
                                esc_html__('Individual user permissions are overridden by global setting: "%s"', 'arsol-pfw'),
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
}
