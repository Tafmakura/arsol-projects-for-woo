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
                'label_for' => 'enable_project_comments',
                'description' => __('Allow users to comment on projects', 'arsol-pfw')
            )
        );

        add_settings_field(
            'projects_per_page',
            __('Projects Per Page', 'arsol-pfw'),
            array($this, 'render_number_field'),
            'arsol_projects_settings',
            'arsol_projects_general_settings',
            array(
                'label_for' => 'projects_per_page',
                'description' => __('Number of projects to display per page', 'arsol-pfw')
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
                'label_for' => 'user_project_permissions',
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
                'label_for' => 'default_user_permission',
                'description' => __('Default permission level assigned to new users (only applies when "User Specific" is selected above)', 'arsol-pfw'),
                'condition_field' => 'user_project_permissions',
                'condition_value' => 'user_specific',
                'options' => array(
                    'none' => __('None', 'arsol-pfw'),
                    'request' => __('Can request projects', 'arsol-pfw'),
                    'create' => __('Can create projects', 'arsol-pfw')
                )
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
        $value = isset($settings[$args['label_for']]) ? $settings[$args['label_for']] : 0;
        ?>
        <input type="checkbox" 
               id="<?php echo esc_attr($args['label_for']); ?>"
               name="arsol_projects_settings[<?php echo esc_attr($args['label_for']); ?>]"
               value="1"
               <?php checked(1, $value); ?>>
        <p class="description">
            <?php echo esc_html($args['description']); ?>
        </p>
        <?php
    }

    /**
     * Render number field
     */
    public function render_number_field($args) {
        $settings = get_option('arsol_projects_settings', array());
        $value = isset($settings[$args['label_for']]) ? $settings[$args['label_for']] : 10;
        ?>
        <input type="number" 
               id="<?php echo esc_attr($args['label_for']); ?>"
               name="arsol_projects_settings[<?php echo esc_attr($args['label_for']); ?>]"
               value="<?php echo esc_attr($value); ?>"
               min="1"
               max="100">
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
        $value = isset($settings[$args['label_for']]) ? $settings[$args['label_for']] : 'none';
        ?>
        <select id="<?php echo esc_attr($args['label_for']); ?>"
                name="arsol_projects_settings[<?php echo esc_attr($args['label_for']); ?>]">
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
        $value = isset($settings[$args['label_for']]) ? $settings[$args['label_for']] : 'none';
        ?>
        <tr class="arsol-conditional-field" data-condition-field="<?php echo esc_attr($args['condition_field']); ?>" data-condition-value="<?php echo esc_attr($args['condition_value']); ?>">
            <th scope="row">
                <label for="<?php echo esc_attr($args['label_for']); ?>"><?php echo esc_html($args['label_for']); ?></label>
            </th>
            <td>
                <select id="<?php echo esc_attr($args['label_for']); ?>"
                        name="arsol_projects_settings[<?php echo esc_attr($args['label_for']); ?>]">
                    <?php foreach ($args['options'] as $option => $label): ?>
                        <option value="<?php echo esc_attr($option); ?>" <?php selected($value, $option); ?>>
                            <?php echo esc_html($label); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <p class="description">
                    <?php echo esc_html($args['description']); ?>
                </p>
            </td>
        </tr>
        <?php
    }

    /**
     * Render user permissions section description
     */
    public function render_user_permissions_section() {
        echo '<p>' . esc_html__('Configure user project permissions for Arsol Projects For Woo.', 'arsol-pfw') . '</p>';
    }
}