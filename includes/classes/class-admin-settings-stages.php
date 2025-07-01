<?php
/**
 * Admin Stages Settings Class
 *
 * Handles the stages settings page functionality.
 *
 * @package Arsol_Projects_For_Woo
 * @since 1.0.0
 */

namespace Arsol_Projects_For_Woo\Admin;

if (!defined('ABSPATH')) {
    exit;
}

class Settings_Stages {
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
        register_setting('arsol_stages_settings', 'arsol_stages_settings');

        // Project Stages Settings Section
        add_settings_section(
            'arsol_stages_project_settings',
            __('Project Stages Configuration', 'arsol-pfw'),
            array($this, 'render_project_stages_section'),
            'arsol_stages_settings'
        );

        add_settings_field(
            'default_project_stage',
            __('Default Project Stage', 'arsol-pfw'),
            array($this, 'render_taxonomy_select_field'),
            'arsol_stages_settings',
            'arsol_stages_project_settings',
            array(
                'field' => 'default_project_stage',
                'taxonomy' => 'arsol-pfw-project-stage',
                'description' => __('The default stage assigned to new projects.', 'arsol-pfw'),
                'class' => 'arsol-pfw-default-project-stage'
            )
        );

        // Request Stages Settings Section
        add_settings_section(
            'arsol_stages_request_settings',
            __('Request Stages Configuration', 'arsol-pfw'),
            array($this, 'render_request_stages_section'),
            'arsol_stages_settings'
        );

        add_settings_field(
            'default_request_stage',
            __('Default Request Stage', 'arsol-pfw'),
            array($this, 'render_taxonomy_select_field'),
            'arsol_stages_settings',
            'arsol_stages_request_settings',
            array(
                'field' => 'default_request_stage',
                'taxonomy' => 'arsol-pfw-request-stage',
                'description' => __('The default stage assigned to new requests.', 'arsol-pfw'),
                'class' => 'arsol-pfw-default-request-stage'
            )
        );

        // Proposal Stages Settings Section
        add_settings_section(
            'arsol_stages_proposal_settings',
            __('Proposal Stages Configuration', 'arsol-pfw'),
            array($this, 'render_proposal_stages_section'),
            'arsol_stages_settings'
        );

        add_settings_field(
            'default_proposal_stage',
            __('Default Proposal Stage', 'arsol-pfw'),
            array($this, 'render_taxonomy_select_field'),
            'arsol_stages_settings',
            'arsol_stages_proposal_settings',
            array(
                'field' => 'default_proposal_stage',
                'taxonomy' => 'arsol-pfw-proposal-stage',
                'description' => __('The default stage assigned to new proposals.', 'arsol-pfw'),
                'class' => 'arsol-pfw-default-proposal-stage'
            )
        );

        // Stage Transition Settings Section
        add_settings_section(
            'arsol_stages_transition_settings',
            __('Stage Transition Settings', 'arsol-pfw'),
            array($this, 'render_transition_settings_section'),
            'arsol_stages_settings'
        );

        add_settings_field(
            'enable_stage_notifications',
            __('Stage Change Notifications', 'arsol-pfw'),
            array($this, 'render_checkbox_field'),
            'arsol_stages_settings',
            'arsol_stages_transition_settings',
            array(
                'field' => 'enable_stage_notifications',
                'label' => __('Send email notifications when stages change', 'arsol-pfw'),
                'description' => __('Automatically notify customers and administrators when project stages are updated.', 'arsol-pfw'),
                'class' => 'arsol-pfw-stage-notifications'
            )
        );

        add_settings_field(
            'stage_history_tracking',
            __('Stage History Tracking', 'arsol-pfw'),
            array($this, 'render_checkbox_field'),
            'arsol_stages_settings',
            'arsol_stages_transition_settings',
            array(
                'field' => 'stage_history_tracking',
                'label' => __('Track and log all stage changes', 'arsol-pfw'),
                'description' => __('Maintain a complete history of all stage transitions for audit purposes.', 'arsol-pfw'),
                'class' => 'arsol-pfw-stage-history'
            )
        );

        // Stage Display Settings Section
        add_settings_section(
            'arsol_stages_display_settings',
            __('Stage Display Settings', 'arsol-pfw'),
            array($this, 'render_display_settings_section'),
            'arsol_stages_settings'
        );

        add_settings_field(
            'show_stages_frontend',
            __('Show Stages on Frontend', 'arsol-pfw'),
            array($this, 'render_checkbox_field'),
            'arsol_stages_settings',
            'arsol_stages_display_settings',
            array(
                'field' => 'show_stages_frontend',
                'label' => __('Display current stage information to customers', 'arsol-pfw'),
                'description' => __('Shows stage status on customer-facing project pages.', 'arsol-pfw'),
                'class' => 'arsol-pfw-show-stages-frontend'
            )
        );

        add_settings_field(
            'stage_color_scheme',
            __('Stage Color Scheme', 'arsol-pfw'),
            array($this, 'render_select_field'),
            'arsol_stages_settings',
            'arsol_stages_display_settings',
            array(
                'field' => 'stage_color_scheme',
                'description' => __('Choose the color scheme for stage indicators.', 'arsol-pfw'),
                'options' => array(
                    'default' => __('Default WordPress colors', 'arsol-pfw'),
                    'traffic_light' => __('Traffic light (red, yellow, green)', 'arsol-pfw'),
                    'professional' => __('Professional blues and grays', 'arsol-pfw'),
                    'custom' => __('Custom colors', 'arsol-pfw')
                ),
                'class' => 'arsol-pfw-stage-colors'
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

        wp_enqueue_script('arsol-pfw-admin-stages', ARSOL_PROJECTS_PLUGIN_URL . 'assets/js/arsol-pfw-admin-stages.js', array('jquery'), ARSOL_PROJECTS_VERSION, true);
        wp_enqueue_style('arsol-pfw-admin-stages', ARSOL_PROJECTS_PLUGIN_URL . 'assets/css/arsol-pfw-admin-stages.css', array(), ARSOL_PROJECTS_VERSION);
    }

    /**
     * Render project stages section
     */
    public function render_project_stages_section() {
        echo '<p>' . __('Configure default settings for project stages and their behavior.', 'arsol-pfw') . '</p>';
        echo '<p><a href="edit-tags.php?taxonomy=arsol-pfw-project-stage&post_type=arsol-pfw-project" class="button button-secondary">' . __('Manage Project Stages', 'arsol-pfw') . '</a></p>';
    }

    /**
     * Render request stages section
     */
    public function render_request_stages_section() {
        echo '<p>' . __('Configure default settings for request stages and their workflow.', 'arsol-pfw') . '</p>';
        echo '<p><a href="edit-tags.php?taxonomy=arsol-pfw-request-stage&post_type=arsol-pfw-request" class="button button-secondary">' . __('Manage Request Stages', 'arsol-pfw') . '</a></p>';
    }

    /**
     * Render proposal stages section
     */
    public function render_proposal_stages_section() {
        echo '<p>' . __('Configure default settings for proposal stages and their transitions.', 'arsol-pfw') . '</p>';
        echo '<p><a href="edit-tags.php?taxonomy=arsol-pfw-proposal-stage&post_type=arsol-pfw-proposal" class="button button-secondary">' . __('Manage Proposal Stages', 'arsol-pfw') . '</a></p>';
    }

    /**
     * Render transition settings section
     */
    public function render_transition_settings_section() {
        echo '<p>' . __('Control how stage transitions are handled and tracked in your system.', 'arsol-pfw') . '</p>';
    }

    /**
     * Render display settings section
     */
    public function render_display_settings_section() {
        echo '<p>' . __('Customize how stage information is displayed throughout the system.', 'arsol-pfw') . '</p>';
    }

    /**
     * Render taxonomy select field
     */
    public function render_taxonomy_select_field($args) {
        $options = get_option('arsol_stages_settings', array());
        $field = $args['field'];
        $taxonomy = $args['taxonomy'];
        $value = isset($options[$field]) ? $options[$field] : '';
        $description = isset($args['description']) ? $args['description'] : '';
        $class = isset($args['class']) ? $args['class'] : '';

        $terms = get_terms(array(
            'taxonomy' => $taxonomy,
            'hide_empty' => false,
        ));

        echo '<select id="' . esc_attr($field) . '" name="arsol_stages_settings[' . esc_attr($field) . ']" class="' . esc_attr($class) . '">';
        echo '<option value="">' . __('Select a stage...', 'arsol-pfw') . '</option>';
        if (!is_wp_error($terms) && !empty($terms)) {
            foreach ($terms as $term) {
                echo '<option value="' . esc_attr($term->slug) . '" ' . selected($value, $term->slug, false) . '>' . esc_html($term->name) . '</option>';
            }
        }
        echo '</select>';
        if ($description) {
            echo '<p class="description">' . esc_html($description) . '</p>';
        }
    }

    /**
     * Render checkbox field
     */
    public function render_checkbox_field($args) {
        $options = get_option('arsol_stages_settings', array());
        $field = $args['field'];
        $value = isset($options[$field]) ? $options[$field] : '';
        $label = isset($args['label']) ? $args['label'] : '';
        $description = isset($args['description']) ? $args['description'] : '';
        $class = isset($args['class']) ? $args['class'] : '';

        echo '<fieldset class="' . esc_attr($class) . '">';
        echo '<label for="' . esc_attr($field) . '">';
        echo '<input type="checkbox" id="' . esc_attr($field) . '" name="arsol_stages_settings[' . esc_attr($field) . ']" value="1" ' . checked(1, $value, false) . ' />';
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
        $options = get_option('arsol_stages_settings', array());
        $field = $args['field'];
        $value = isset($options[$field]) ? $options[$field] : '';
        $field_options = isset($args['options']) ? $args['options'] : array();
        $description = isset($args['description']) ? $args['description'] : '';
        $class = isset($args['class']) ? $args['class'] : '';

        echo '<select id="' . esc_attr($field) . '" name="arsol_stages_settings[' . esc_attr($field) . ']" class="' . esc_attr($class) . '">';
        foreach ($field_options as $option_value => $option_label) {
            echo '<option value="' . esc_attr($option_value) . '" ' . selected($value, $option_value, false) . '>' . esc_html($option_label) . '</option>';
        }
        echo '</select>';
        if ($description) {
            echo '<p class="description">' . esc_html($description) . '</p>';
        }
    }
} 