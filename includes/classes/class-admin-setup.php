<?php
/**
 * Admin Setup Class
 *
 * Handles the admin settings page for Arsol Projects For Woo.
 *
 * @package Arsol_Projects_For_Woo
 * @since 1.0.0
 */

namespace Arsol_Projects_For_Woo\Admin;

if (!defined('ABSPATH')) {
    exit;
}

class Setup {
    /**
     * Constructor
     */
    public function __construct() {
        // Add menu item
        add_action('admin_menu', array($this, 'add_admin_menu'));
        
        // Register settings
        add_action('admin_init', array($this, 'register_settings'));
    }

    /**
     * Add menu item to WordPress admin menu
     */
    public function add_admin_menu() {
        add_menu_page(
            __('Arsol Projects For Woo', 'arsol-pfw'),
            __('Arsol Projects', 'arsol-pfw'),
            'manage_options',
            'arsol-projects',
            array($this, 'render_settings_page'),
            'dashicons-portfolio',
            56
        );
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
    }

    /**
     * Render the settings page
     */
    public function render_settings_page() {
        if (!current_user_can('manage_options')) {
            return;
        }

        // Include the template
        include ARSOL_PROJECTS_PLUGIN_DIR . 'includes/ui/templates/admin/page-admin-settings-general.php';
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
}
