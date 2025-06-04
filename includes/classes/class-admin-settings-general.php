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

        // Add comments settings fields with short labels and no descriptions
        add_settings_field(
            'enable_project_comments',
            __('Project comments', 'arsol-pfw'),
            array($this, 'render_checkbox_field'),
            'arsol_projects_settings',
            'arsol_projects_comments_settings',
            array(
                'label' => __('Project comments', 'arsol-pfw'),
                'field' => 'enable_project_comments',
                'description' => ''
            )
        );
        add_settings_field(
            'enable_project_request_comments',
            __('Request comments', 'arsol-pfw'),
            array($this, 'render_checkbox_field'),
            'arsol_projects_settings',
            'arsol_projects_comments_settings',
            array(
                'label' => __('Request comments', 'arsol-pfw'),
                'field' => 'enable_project_request_comments',
                'description' => ''
            )
        );
        add_settings_field(
            'enable_project_proposal_comments',
            __('Proposal comments', 'arsol-pfw'),
            array($this, 'render_checkbox_field'),
            'arsol_projects_settings',
            'arsol_projects_comments_settings',
            array(
                'label' => __('Proposal comments', 'arsol-pfw'),
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
        <tr>
            <th scope="row"></th>
            <td>
                <label for="<?php echo esc_attr($field); ?>">
                    <input type="checkbox"
                           id="<?php echo esc_attr($field); ?>"
                           name="arsol_projects_settings[<?php echo esc_attr($field); ?>]"
                           value="1"
                           <?php checked(1, $value); ?>>
                    <?php echo esc_html($label); ?>
                </label>
            </td>
        </tr>
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
}