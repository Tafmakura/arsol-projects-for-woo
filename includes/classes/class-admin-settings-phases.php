<?php
/**
 * Admin Phases Settings Class
 *
 * Handles the phases settings page functionality.
 *
 * @package Arsol_Projects_For_Woo
 * @since 1.0.0
 */

namespace Arsol_Projects_For_Woo\Admin;

if (!defined('ABSPATH')) {
    exit;
}

class Settings_Phases {
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
        register_setting('arsol_phases_settings', 'arsol_phases_settings');

        // Phase Workflow Settings Section
        add_settings_section(
            'arsol_phases_workflow_settings',
            __('Phase Workflow Settings', 'arsol-pfw'),
            array($this, 'render_workflow_settings_section'),
            'arsol_phases_settings'
        );

        add_settings_field(
            'enable_phase_workflow',
            __('Enable Phase Workflow', 'arsol-pfw'),
            array($this, 'render_checkbox_field'),
            'arsol_phases_settings',
            'arsol_phases_workflow_settings',
            array(
                'field' => 'enable_phase_workflow',
                'label' => __('Enable the four-phase workflow: Request → Proposal → Active → Archive', 'arsol-pfw'),
                'description' => __('When enabled, projects follow a structured four-phase lifecycle.', 'arsol-pfw'),
                'class' => 'arsol-pfw-enable-workflow'
            )
        );

        add_settings_field(
            'auto_phase_transitions',
            __('Automatic Phase Transitions', 'arsol-pfw'),
            array($this, 'render_checkbox_field'),
            'arsol_phases_settings',
            'arsol_phases_workflow_settings',
            array(
                'field' => 'auto_phase_transitions',
                'label' => __('Automatically transition phases based on stage changes', 'arsol-pfw'),
                'description' => __('When enabled, phase changes will be triggered automatically by stage updates.', 'arsol-pfw'),
                'class' => 'arsol-pfw-auto-transitions'
            )
        );

        // Phase Display Settings Section
        add_settings_section(
            'arsol_phases_display_settings',
            __('Phase Display Settings', 'arsol-pfw'),
            array($this, 'render_display_settings_section'),
            'arsol_phases_settings'
        );

        add_settings_field(
            'show_phases_frontend',
            __('Show Phases on Frontend', 'arsol-pfw'),
            array($this, 'render_checkbox_field'),
            'arsol_phases_settings',
            'arsol_phases_display_settings',
            array(
                'field' => 'show_phases_frontend',
                'label' => __('Display current phase information to customers', 'arsol-pfw'),
                'description' => __('Shows phase progress indicators on customer-facing project pages.', 'arsol-pfw'),
                'class' => 'arsol-pfw-show-phases-frontend'
            )
        );

        add_settings_field(
            'phase_progress_style',
            __('Phase Progress Style', 'arsol-pfw'),
            array($this, 'render_select_field'),
            'arsol_phases_settings',
            'arsol_phases_display_settings',
            array(
                'field' => 'phase_progress_style',
                'description' => __('Choose how phase progress is displayed to customers.', 'arsol-pfw'),
                'options' => array(
                    'simple' => __('Simple text', 'arsol-pfw'),
                    'progress_bar' => __('Progress bar', 'arsol-pfw'),
                    'timeline' => __('Timeline view', 'arsol-pfw'),
                    'badges' => __('Phase badges', 'arsol-pfw')
                ),
                'class' => 'arsol-pfw-phase-progress-style'
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

        wp_enqueue_script('arsol-pfw-admin-phases', ARSOL_PROJECTS_PLUGIN_URL . 'assets/js/arsol-pfw-admin-phases.js', array('jquery'), ARSOL_PROJECTS_VERSION, true);
        wp_enqueue_style('arsol-pfw-admin-phases', ARSOL_PROJECTS_PLUGIN_URL . 'assets/css/arsol-pfw-admin-phases.css', array(), ARSOL_PROJECTS_VERSION);
    }

    /**
     * Render workflow settings section
     */
    public function render_workflow_settings_section() {
        echo '<p>' . __('Configure how the project phase workflow behaves in your system.', 'arsol-pfw') . '</p>';
    }

    /**
     * Render display settings section
     */
    public function render_display_settings_section() {
        echo '<p>' . __('Customize how phase information is displayed to customers and administrators.', 'arsol-pfw') . '</p>';
    }

    /**
     * Render checkbox field
     */
    public function render_checkbox_field($args) {
        $options = get_option('arsol_phases_settings', array());
        $field = $args['field'];
        $value = isset($options[$field]) ? $options[$field] : '';
        $label = isset($args['label']) ? $args['label'] : '';
        $description = isset($args['description']) ? $args['description'] : '';
        $class = isset($args['class']) ? $args['class'] : '';

        echo '<fieldset class="' . esc_attr($class) . '">';
        echo '<label for="' . esc_attr($field) . '">';
        echo '<input type="checkbox" id="' . esc_attr($field) . '" name="arsol_phases_settings[' . esc_attr($field) . ']" value="1" ' . checked(1, $value, false) . ' />';
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
        $options = get_option('arsol_phases_settings', array());
        $field = $args['field'];
        $value = isset($options[$field]) ? $options[$field] : '';
        $field_options = isset($args['options']) ? $args['options'] : array();
        $description = isset($args['description']) ? $args['description'] : '';
        $class = isset($args['class']) ? $args['class'] : '';

        echo '<select id="' . esc_attr($field) . '" name="arsol_phases_settings[' . esc_attr($field) . ']" class="' . esc_attr($class) . '">';
        foreach ($field_options as $option_value => $option_label) {
            echo '<option value="' . esc_attr($option_value) . '" ' . selected($value, $option_value, false) . '>' . esc_html($option_label) . '</option>';
        }
        echo '</select>';
        if ($description) {
            echo '<p class="description">' . esc_html($description) . '</p>';
        }
    }
} 