<?php
/**
 * Admin Files Settings Class
 *
 * Handles the files settings page functionality.
 *
 * @package Arsol_Projects_For_Woo
 * @since 1.0.0
 */

namespace Arsol_Projects_For_Woo\Admin;

if (!defined('ABSPATH')) {
    exit;
}

class Settings_Files {
    /**
     * Constructor
     */
    public function __construct() {
        // Register settings after init to ensure text domain is loaded
        add_action('init', array($this, 'setup_settings'), 20);
        
        // Add scripts for admin page
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
    }

    /**
     * Setup settings after init
     */
    public function setup_settings() {
        add_action('admin_init', array($this, 'register_settings'));
    }

    /**
     * Register settings
     */
    public function register_settings() {
        register_setting('arsol_files_settings', 'arsol_files_settings');

        // File Upload Settings Section
        add_settings_section(
            'arsol_files_upload_settings',
            __('File Upload Settings', 'arsol-pfw'),
            array($this, 'render_upload_settings_section'),
            'arsol_files_settings'
        );

        add_settings_field(
            'enable_file_uploads',
            __('Enable File Uploads', 'arsol-pfw'),
            array($this, 'render_checkbox_field'),
            'arsol_files_settings',
            'arsol_files_upload_settings',
            array(
                'field' => 'enable_file_uploads',
                'label' => __('Allow customers to upload files to projects', 'arsol-pfw'),
                'description' => __('Enables file upload functionality for projects, proposals, and requests.', 'arsol-pfw'),
                'class' => 'arsol-pfw-enable-uploads'
            )
        );

        add_settings_field(
            'max_file_size',
            __('Maximum File Size', 'arsol-pfw'),
            array($this, 'render_number_field'),
            'arsol_files_settings',
            'arsol_files_upload_settings',
            array(
                'field' => 'max_file_size',
                'description' => __('Maximum file size in MB (leave empty for server default).', 'arsol-pfw'),
                'class' => 'arsol-pfw-max-file-size',
                'min' => 1,
                'max' => 100,
                'step' => 1
            )
        );

        add_settings_field(
            'allowed_file_types',
            __('Allowed File Types', 'arsol-pfw'),
            array($this, 'render_multiselect_field'),
            'arsol_files_settings',
            'arsol_files_upload_settings',
            array(
                'field' => 'allowed_file_types',
                'description' => __('Select which file types customers can upload.', 'arsol-pfw'),
                'options' => array(
                    'pdf' => __('PDF Documents (.pdf)', 'arsol-pfw'),
                    'doc' => __('Word Documents (.doc, .docx)', 'arsol-pfw'),
                    'xls' => __('Excel Files (.xls, .xlsx)', 'arsol-pfw'),
                    'ppt' => __('PowerPoint (.ppt, .pptx)', 'arsol-pfw'),
                    'txt' => __('Text Files (.txt)', 'arsol-pfw'),
                    'jpg' => __('Images (.jpg, .jpeg, .png, .gif)', 'arsol-pfw'),
                    'zip' => __('Archives (.zip, .rar)', 'arsol-pfw'),
                    'csv' => __('CSV Files (.csv)', 'arsol-pfw')
                ),
                'class' => 'arsol-pfw-allowed-types'
            )
        );

        // File Organization Settings Section
        add_settings_section(
            'arsol_files_organization_settings',
            __('File Organization Settings', 'arsol-pfw'),
            array($this, 'render_organization_settings_section'),
            'arsol_files_settings'
        );

        add_settings_field(
            'file_organization_method',
            __('File Organization Method', 'arsol-pfw'),
            array($this, 'render_select_field'),
            'arsol_files_settings',
            'arsol_files_organization_settings',
            array(
                'field' => 'file_organization_method',
                'description' => __('How files should be organized in the uploads directory.', 'arsol-pfw'),
                'options' => array(
                    'by_project' => __('By Project (recommended)', 'arsol-pfw'),
                    'by_date' => __('By Upload Date', 'arsol-pfw'),
                    'by_type' => __('By File Type', 'arsol-pfw'),
                    'flat' => __('All in one folder', 'arsol-pfw')
                ),
                'class' => 'arsol-pfw-organization-method'
            )
        );

        add_settings_field(
            'enable_file_versioning',
            __('File Versioning', 'arsol-pfw'),
            array($this, 'render_checkbox_field'),
            'arsol_files_settings',
            'arsol_files_organization_settings',
            array(
                'field' => 'enable_file_versioning',
                'label' => __('Keep versions of replaced files', 'arsol-pfw'),
                'description' => __('When enabled, old versions of files are kept when new versions are uploaded.', 'arsol-pfw'),
                'class' => 'arsol-pfw-file-versioning'
            )
        );

        // File Access Settings Section
        add_settings_section(
            'arsol_files_access_settings',
            __('File Access & Security', 'arsol-pfw'),
            array($this, 'render_access_settings_section'),
            'arsol_files_settings'
        );

        add_settings_field(
            'file_access_method',
            __('File Access Method', 'arsol-pfw'),
            array($this, 'render_select_field'),
            'arsol_files_settings',
            'arsol_files_access_settings',
            array(
                'field' => 'file_access_method',
                'description' => __('How files are accessed and downloaded.', 'arsol-pfw'),
                'options' => array(
                    'direct' => __('Direct access (faster, less secure)', 'arsol-pfw'),
                    'protected' => __('Protected access (slower, more secure)', 'arsol-pfw'),
                    'private' => __('Private access (requires login)', 'arsol-pfw')
                ),
                'class' => 'arsol-pfw-access-method'
            )
        );

        add_settings_field(
            'enable_download_logging',
            __('Download Logging', 'arsol-pfw'),
            array($this, 'render_checkbox_field'),
            'arsol_files_settings',
            'arsol_files_access_settings',
            array(
                'field' => 'enable_download_logging',
                'label' => __('Log all file downloads', 'arsol-pfw'),
                'description' => __('Track who downloads files and when for audit purposes.', 'arsol-pfw'),
                'class' => 'arsol-pfw-download-logging'
            )
        );

        // File Display Settings Section
        add_settings_section(
            'arsol_files_display_settings',
            __('File Display Settings', 'arsol-pfw'),
            array($this, 'render_display_settings_section'),
            'arsol_files_settings'
        );

        add_settings_field(
            'show_file_previews',
            __('File Previews', 'arsol-pfw'),
            array($this, 'render_checkbox_field'),
            'arsol_files_settings',
            'arsol_files_display_settings',
            array(
                'field' => 'show_file_previews',
                'label' => __('Show file previews when possible', 'arsol-pfw'),
                'description' => __('Display thumbnails and previews for supported file types.', 'arsol-pfw'),
                'class' => 'arsol-pfw-file-previews'
            )
        );

        add_settings_field(
            'files_per_page',
            __('Files Per Page', 'arsol-pfw'),
            array($this, 'render_number_field'),
            'arsol_files_settings',
            'arsol_files_display_settings',
            array(
                'field' => 'files_per_page',
                'description' => __('Number of files to display per page in file listings.', 'arsol-pfw'),
                'class' => 'arsol-pfw-files-per-page',
                'min' => 5,
                'max' => 100,
                'step' => 5,
                'default' => 20
            )
        );
    }

    /**
     * Enqueue admin scripts
     */
    public function enqueue_admin_scripts($hook) {
        // Only load on our settings page
        if (strpos($hook, 'arsol-projects-settings') === false) {
            return;
        }

        wp_enqueue_script('arsol-pfw-admin-files', ARSOL_PROJECTS_PLUGIN_URL . 'assets/js/arsol-pfw-admin-files.js', array('jquery'), ARSOL_PROJECTS_VERSION, true);
        wp_enqueue_style('arsol-pfw-admin-files', ARSOL_PROJECTS_PLUGIN_URL . 'assets/css/arsol-pfw-admin-files.css', array(), ARSOL_PROJECTS_VERSION);
    }

    /**
     * Render upload settings section
     */
    public function render_upload_settings_section() {
        echo '<p>' . __('Configure file upload capabilities and restrictions for your projects.', 'arsol-pfw') . '</p>';
        $max_upload = wp_max_upload_size();
        echo '<p class="description">' . sprintf(__('Server maximum upload size: %s', 'arsol-pfw'), size_format($max_upload)) . '</p>';
    }

    /**
     * Render organization settings section
     */
    public function render_organization_settings_section() {
        echo '<p>' . __('Configure how uploaded files are organized and managed in your system.', 'arsol-pfw') . '</p>';
    }

    /**
     * Render access settings section
     */
    public function render_access_settings_section() {
        echo '<p>' . __('Control file access permissions and security settings.', 'arsol-pfw') . '</p>';
    }

    /**
     * Render display settings section
     */
    public function render_display_settings_section() {
        echo '<p>' . __('Customize how files are displayed to users throughout the system.', 'arsol-pfw') . '</p>';
    }

    /**
     * Render checkbox field
     */
    public function render_checkbox_field($args) {
        $options = get_option('arsol_files_settings', array());
        $field = $args['field'];
        $value = isset($options[$field]) ? $options[$field] : '';
        $label = isset($args['label']) ? $args['label'] : '';
        $description = isset($args['description']) ? $args['description'] : '';
        $class = isset($args['class']) ? $args['class'] : '';

        echo '<fieldset class="' . esc_attr($class) . '">';
        echo '<label for="' . esc_attr($field) . '">';
        echo '<input type="checkbox" id="' . esc_attr($field) . '" name="arsol_files_settings[' . esc_attr($field) . ']" value="1" ' . checked(1, $value, false) . ' />';
        echo ' ' . esc_html($label);
        echo '</label>';
        if ($description) {
            echo '<p class="description">' . esc_html($description) . '</p>';
        }
        echo '</fieldset>';
    }

    /**
     * Render select field
     */
    public function render_select_field($args) {
        $options = get_option('arsol_files_settings', array());
        $field = $args['field'];
        $value = isset($options[$field]) ? $options[$field] : '';
        $field_options = isset($args['options']) ? $args['options'] : array();
        $description = isset($args['description']) ? $args['description'] : '';
        $class = isset($args['class']) ? $args['class'] : '';

        echo '<select id="' . esc_attr($field) . '" name="arsol_files_settings[' . esc_attr($field) . ']" class="' . esc_attr($class) . '">';
        foreach ($field_options as $option_value => $option_label) {
            echo '<option value="' . esc_attr($option_value) . '" ' . selected($value, $option_value, false) . '>' . esc_html($option_label) . '</option>';
        }
        echo '</select>';
        if ($description) {
            echo '<p class="description">' . esc_html($description) . '</p>';
        }
    }

    /**
     * Render number field
     */
    public function render_number_field($args) {
        $options = get_option('arsol_files_settings', array());
        $field = $args['field'];
        $value = isset($options[$field]) ? $options[$field] : (isset($args['default']) ? $args['default'] : '');
        $description = isset($args['description']) ? $args['description'] : '';
        $class = isset($args['class']) ? $args['class'] : '';
        $min = isset($args['min']) ? $args['min'] : '';
        $max = isset($args['max']) ? $args['max'] : '';
        $step = isset($args['step']) ? $args['step'] : '';

        echo '<input type="number" id="' . esc_attr($field) . '" name="arsol_files_settings[' . esc_attr($field) . ']" value="' . esc_attr($value) . '" class="' . esc_attr($class) . '"';
        if ($min !== '') echo ' min="' . esc_attr($min) . '"';
        if ($max !== '') echo ' max="' . esc_attr($max) . '"';
        if ($step !== '') echo ' step="' . esc_attr($step) . '"';
        echo ' />';
        if ($description) {
            echo '<p class="description">' . esc_html($description) . '</p>';
        }
    }

    /**
     * Render multiselect field
     */
    public function render_multiselect_field($args) {
        $options = get_option('arsol_files_settings', array());
        $field = $args['field'];
        $selected_values = isset($options[$field]) ? (array) $options[$field] : array();
        $field_options = isset($args['options']) ? $args['options'] : array();
        $description = isset($args['description']) ? $args['description'] : '';
        $class = isset($args['class']) ? $args['class'] : '';

        echo '<fieldset class="' . esc_attr($class) . '">';
        foreach ($field_options as $option_value => $option_label) {
            $checked = in_array($option_value, $selected_values) ? 'checked' : '';
            echo '<label style="display: block; margin-bottom: 5px;">';
            echo '<input type="checkbox" name="arsol_files_settings[' . esc_attr($field) . '][]" value="' . esc_attr($option_value) . '" ' . $checked . ' />';
            echo ' ' . esc_html($option_label);
            echo '</label>';
        }
        if ($description) {
            echo '<p class="description">' . esc_html($description) . '</p>';
        }
        echo '</fieldset>';
    }
} 