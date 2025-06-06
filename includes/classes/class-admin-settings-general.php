<?php
/**
 * Admin General Settings Class
 *
 * Handles the general settings page functionality.
 *
 * @package Arsol_Projects_For_Woo
 * @since 1.0.0
 */

namespace Arsol_Projects_For_Woo\Admin;

if (!defined('ABSPATH')) {
    exit;
}

class Settings_General {
    /**
     * Constructor
     */
    public function __construct() {
        // Register settings
        add_action('admin_init', array($this, 'register_settings'));
        
        // Add filter for post type supports
        add_filter('post_type_supports', array($this, 'filter_post_type_supports'), 10, 2);

        // Update capabilities when settings are saved
        add_action('update_option_arsol_projects_settings', array($this, 'update_capabilities'), 10, 2);
    }

    /**
     * Register settings
     */
    public function register_settings() {
        register_setting('arsol_projects_settings', 'arsol_projects_settings');

        // General Settings Section
        add_settings_section(
            'arsol_projects_general_settings',
            __('General Settings', 'arsol-pfw'),
            array($this, 'render_general_settings_section'),
            'arsol_projects_settings'
        );

        // User Permissions Section
        add_settings_section(
            'arsol_projects_user_permissions',
            __('User Project Permissions', 'arsol-pfw'),
            array($this, 'render_user_permissions_section'),
            'arsol_projects_settings'
        );

        add_settings_field(
            'manage_roles',
            __('Manage Roles', 'arsol-pfw'),
            array($this, 'render_roles_field'),
            'arsol_projects_settings',
            'arsol_projects_user_permissions',
            array(
                'field' => 'manage_roles',
                'description' => __('Users with these roles can manage all projects, proposals, and requests.', 'arsol-pfw')
            )
        );

        add_settings_field(
            'create_roles',
            __('Create/Request Roles', 'arsol-pfw'),
            array($this, 'render_roles_field'),
            'arsol_projects_settings',
            'arsol_projects_user_permissions',
            array(
                'field' => 'create_roles',
                'description' => __('Users with these roles can create new projects and requests.', 'arsol-pfw')
            )
        );

        add_settings_field(
            'user_project_permissions',
            __('Global Permission Setting', 'arsol-pfw'),
            array($this, 'render_select_field'),
            'arsol_projects_settings',
            'arsol_projects_user_permissions',
            array(
                'description' => __('Controls how user project permissions are handled globally', 'arsol-pfw'),
                'options' => array(
                    'none' => __('None', 'arsol-pfw'),
                    'request' => __('Users can request projects', 'arsol-pfw'),
                    'create' => __('Users can create projects', 'arsol-pfw'),
                    'user_specific' => __('Set per user', 'arsol-pfw')
                )
            )
        );

        add_settings_field(
            'default_user_permission',
            __('Default User', 'arsol-pfw'),
            array($this, 'render_conditional_select_field'),
            'arsol_projects_settings',
            'arsol_projects_user_permissions',
            array(
                'description' => __('Default permission level assigned to new users (only applies when "User Specific" is selected above)', 'arsol-pfw'),
                'condition_field' => 'user_project_permissions',
                'condition_value' => 'user_specific',
                'options' => array(
                    'none' => __('None', 'arsol-pfw'),
                    'request' => __('Can request projects', 'arsol-pfw'),
                    'create' => __('Can create projects', 'arsol-pfw')
                ),
                'class' => 'arsol-conditional-field',
                'data-condition-field' => 'user_project_permissions',
                'data-condition-value' => 'user_specific'
            )
        );

        // Comments Settings Section
        add_settings_section(
            'arsol_projects_comments_settings',
            __('Comments Settings', 'arsol-pfw'),
            array($this, 'render_comments_settings_section'),
            'arsol_projects_settings'
        );

        // Add comments settings fields with short labels and no descriptions
        add_settings_field(
            'enable_project_comments',
            __('', 'arsol-pfw'),
            array($this, 'render_checkbox_field'),
            'arsol_projects_settings',
            'arsol_projects_comments_settings',
            array(
                'label' => __('Allow project comments', 'arsol-pfw'),
                'field' => 'enable_project_comments',
                'description' => ''
            )
        );
        add_settings_field(
            'enable_project_request_comments',
            __('', 'arsol-pfw'),
            array($this, 'render_checkbox_field'),
            'arsol_projects_settings',
            'arsol_projects_comments_settings',
            array(
                'label' => __('Allow project request comments', 'arsol-pfw'),
                'field' => 'enable_project_request_comments',
                'description' => ''
            )
        );
        add_settings_field(
            'enable_project_proposal_comments',
            __('', 'arsol-pfw'),
            array($this, 'render_checkbox_field'),
            'arsol_projects_settings',
            'arsol_projects_comments_settings',
            array(
                'label' => __('Allow project proposal comments', 'arsol-pfw'),
                'field' => 'enable_project_proposal_comments',
                'description' => ''
            )
        );
    }

    /**
     * Render general settings section description
     */
    public function render_general_settings_section() {
        echo '<p>' . esc_html__('Configure general settings for Arsol Projects For Woo.', 'arsol-pfw') . '</p>';
    }

    /**
     * Render checkbox field
     */
    public function render_checkbox_field($args) {
        $settings = get_option('arsol_projects_settings', array());
        $field = isset($args['field']) ? $args['field'] : $args['label_for'] ?? '';
        $value = isset($settings[$field]) ? $settings[$field] : 0;
        $label = isset($args['label']) ? $args['label'] : '';
        ?>
        <label for="<?php echo esc_attr($field); ?>">
            <input type="checkbox"
                   id="<?php echo esc_attr($field); ?>"
                   name="arsol_projects_settings[<?php echo esc_attr($field); ?>]"
                   value="1"
                   <?php checked(1, $value); ?>>
            <?php echo esc_html($label); ?>
        </label>
        <?php if (!empty($args['description'])): ?>
            <p class="description"><?php echo esc_html($args['description']); ?></p>
        <?php endif; ?>
        <?php
    }

    /**
     * Render select field
     */
    public function render_select_field($args) {
        $settings = get_option('arsol_projects_settings', array());
        $value = isset($settings['user_project_permissions']) ? $settings['user_project_permissions'] : 'none';
        ?>
        <select id="user_project_permissions"
                name="arsol_projects_settings[user_project_permissions]">
            <?php foreach ($args['options'] as $option => $label): ?>
                <option value="<?php echo esc_attr($option); ?>" <?php selected($value, $option); ?>>
                    <?php echo esc_html($label); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <p class="description">
            <?php echo esc_html($args['description']); ?>
        </p>
        <?php
    }

    /**
     * Render conditional select field
     */
    public function render_conditional_select_field($args) {
        $settings = get_option('arsol_projects_settings', array());
        $value = isset($settings['default_user_permission']) ? $settings['default_user_permission'] : 'none';
        ?>
        <select id="default_user_permission"
                name="arsol_projects_settings[default_user_permission]">
            <?php foreach ($args['options'] as $option => $label): ?>
                <option value="<?php echo esc_attr($option); ?>" <?php selected($value, $option); ?>>
                    <?php echo esc_html($label); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <p class="description">
            <?php echo esc_html($args['description']); ?>
        </p>
        <?php
    }

    /**
     * Render user permissions section description
     */
    public function render_user_permissions_section() {
        echo '<p>' . esc_html__('Configure user project permissions for Arsol Projects For Woo.', 'arsol-pfw') . '</p>';
    }

    /**
     * Render comments settings section description
     */
    public function render_comments_settings_section() {
        echo '<p>' . esc_html__('Configure comment settings for Arsol Projects For Woo.', 'arsol-pfw') . '</p>';
    }

    /**
     * Render roles field
     */
    public function render_roles_field($args) {
        $field_name = $args['field'];
        $settings = get_option('arsol_projects_settings', array());
        $selected_roles = isset($settings[$field_name]) ? $settings[$field_name] : array('administrator');
        
        $editable_roles = get_editable_roles();
        $role_order = array('administrator', 'editor', 'author', 'contributor', 'subscriber');
        
        uksort($editable_roles, function ($a, $b) use ($role_order) {
            $a_pos = array_search($a, $role_order);
            $b_pos = array_search($b, $role_order);
            if ($a_pos === false && $b_pos === false) return 0;
            if ($a_pos === false) return 1;
            if ($b_pos === false) return -1;
            return $a_pos - $b_pos;
        });

        foreach ($editable_roles as $role => $details) {
            $is_admin = ($role === 'administrator');
            $checked = ($is_admin || in_array($role, $selected_roles)) ? 'checked' : '';
            $disabled = $is_admin ? 'disabled' : '';

            echo '<label>';
            echo '<input type="checkbox" name="arsol_projects_settings[' . esc_attr($field_name) . '][]" value="' . esc_attr($role) . '" ' . $checked . ' ' . $disabled . '> ';
            echo esc_html($details['name']);
            if ($is_admin) {
                echo ' <em>(' . esc_html__('always enabled', 'arsol-pfw') . ')</em>';
                // Add a hidden input to ensure the administrator role is always submitted
                echo '<input type="hidden" name="arsol_projects_settings[' . esc_attr($field_name) . '][]" value="administrator">';
            }
            echo '</label><br>';
        }

        if (!empty($args['description'])) {
            echo '<p class="description">' . esc_html($args['description']) . '</p>';
        }
    }

    /**
     * Check if comments are enabled for a specific post type
     *
     * @param string $post_type The post type to check
     * @return bool Whether comments are enabled for the post type
     */
    public static function is_comments_enabled_for_post_type($post_type) {
        $settings = get_option('arsol_projects_settings', array());
        
        switch ($post_type) {
            case 'arsol-project':
                return isset($settings['enable_project_comments']) && $settings['enable_project_comments'];
            case 'arsol-pfw-request':
                return isset($settings['enable_project_request_comments']) && $settings['enable_project_request_comments'];
            case 'arsol-pfw-proposal':
                return isset($settings['enable_project_proposal_comments']) && $settings['enable_project_proposal_comments'];
            default:
                return false;
        }
    }

    /**
     * Filter comment support for custom post types
     *
     * @param array $supports The post type supports
     * @param string $post_type The post type
     * @return array Modified supports array
     */
    public static function filter_post_type_supports($supports, $post_type) {
        if (in_array($post_type, array('arsol-project', 'arsol-pfw-request', 'arsol-pfw-proposal'))) {
            if (!self::is_comments_enabled_for_post_type($post_type)) {
                $supports = array_diff($supports, array('comments'));
            }
        }
        return $supports;
    }

    /**
     * Update capabilities when settings are saved
     *
     * @param mixed $old_value Old settings value
     * @param mixed $new_value New settings value
     */
    public function update_capabilities($old_value, $new_value) {
        $manage_roles = isset($new_value['manage_roles']) ? array_unique($new_value['manage_roles']) : array('administrator');
        $create_roles = isset($new_value['create_roles']) ? array_unique($new_value['create_roles']) : array('administrator');

        if (!in_array('administrator', $manage_roles)) {
            $manage_roles[] = 'administrator';
        }

        $all_roles = wp_roles()->get_names();

        foreach ($all_roles as $role_slug => $role_name) {
            $role = get_role($role_slug);
            if (!$role) {
                continue;
            }

            if (in_array($role_slug, $manage_roles)) {
                $role->add_cap('manage_projects');
                $role->add_cap('create_projects');
                $role->add_cap('request_projects');
            } else {
                $role->remove_cap('manage_projects');
                if (!in_array($role_slug, $create_roles)) {
                    $role->remove_cap('create_projects');
                    $role->remove_cap('request_projects');
                }
            }

            if (in_array($role_slug, $create_roles)) {
                $role->add_cap('create_projects');
                $role->add_cap('request_projects');
            } else {
                if (!in_array($role_slug, $manage_roles)) {
                    $role->remove_cap('create_projects');
                    $role->remove_cap('request_projects');
                }
            }
        }
    }
}