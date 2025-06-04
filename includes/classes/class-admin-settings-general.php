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

        // Add settings fields
        add_settings_field(
            'enable_project_comments',
            __('Enable Project Comments', 'arsol-pfw'),
            array($this, 'render_checkbox_field'),
            'arsol_projects_settings',
            'arsol_projects_general_settings',
            array(
                'description' => __('Allow users to comment on projects', 'arsol-pfw')
            )
        );

        // User Permissions Section
        add_settings_section(
            'arsol_projects_user_permissions',
            __('User Project Permissions', 'arsol-pfw'),
            array($this, 'render_user_permissions_section'),
            'arsol_projects_settings'
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
                    'none' => __('None - No users can request/create projects', 'arsol-pfw'),
                    'request' => __('Users can request projects', 'arsol-pfw'),
                    'create' => __('Users can create projects', 'arsol-pfw'),
                    'user_specific' => __('User Specific - Individual user settings apply', 'arsol-pfw')
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

        // Add comments settings fields
        add_settings_field(
            'enable_project_comments',
            __('Allow comments on Projects', 'arsol-pfw'),
            array($this, 'render_checkbox_field'),
            'arsol_projects_settings',
            'arsol_projects_comments_settings',
            array(
                'description' => __('Allow users to comment on projects', 'arsol-pfw')
            )
        );
        add_settings_field(
            'enable_project_request_comments',
            __('Allow comments on Project Requests', 'arsol-pfw'),
            array($this, 'render_checkbox_field'),
            'arsol_projects_settings',
            'arsol_projects_comments_settings',
            array(
                'description' => __('Allow users to comment on project requests', 'arsol-pfw')
            )
        );
        add_settings_field(
            'enable_project_proposal_comments',
            __('Allow comments on Project Proposals', 'arsol-pfw'),
            array($this, 'render_checkbox_field'),
            'arsol_projects_settings',
            'arsol_projects_comments_settings',
            array(
                'description' => __('Allow users to comment on project proposals', 'arsol-pfw')
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
        $value = isset($settings['enable_project_comments']) ? $settings['enable_project_comments'] : 0;
        ?>
        <input type="checkbox" 
               id="enable_project_comments"
               name="arsol_projects_settings[enable_project_comments]"
               value="1"
               <?php checked(1, $value); ?>>
        <p class="description">
            <?php echo esc_html($args['description']); ?>
        </p>
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
}