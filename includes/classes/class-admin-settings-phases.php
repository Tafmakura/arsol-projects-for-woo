<?php
/**
 * Admin Display Settings Class
 *
 * Handles the display settings page functionality including customer notices and display controls.
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
        // Add admin scripts for enhanced select
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

        // Register display settings under the main phases settings group
        register_setting('arsol_phases_settings', 'arsol_content_display_settings');
        register_setting('arsol_phases_settings', 'arsol_sidebar_display_settings');
        register_setting('arsol_phases_settings', 'arsol_comment_display_settings');
        register_setting('arsol_phases_settings', 'arsol_files_display_settings');
        register_setting('arsol_phases_settings', 'arsol_form_display_settings');

        // Customer Notice Defaults Section
        add_settings_section(
            'arsol_phases_customer_notice_defaults',
            __('Customer Notice Defaults', 'arsol-pfw'),
            array($this, 'render_customer_notice_section'),
            'arsol_phases_settings'
        );

        // Customer Notice Fields
        add_settings_field(
            'arsol_pfw_request_default_customer_notice',
            __('Request Customer Notice', 'arsol-pfw'),
            array($this, 'render_customer_notice_field'),
            'arsol_phases_settings',
            'arsol_phases_customer_notice_defaults',
            array(
                'key' => 'arsol_pfw_request_default_customer_notice',
                'label' => __('Default notice content for requests', 'arsol-pfw'),
                'description' => __('This will be used when no custom notice is set for individual requests.', 'arsol-pfw')
            )
        );

        add_settings_field(
            'arsol_pfw_proposal_default_customer_notice',
            __('Proposal Customer Notice', 'arsol-pfw'),
            array($this, 'render_customer_notice_field'),
            'arsol_phases_settings',
            'arsol_phases_customer_notice_defaults',
            array(
                'key' => 'arsol_pfw_proposal_default_customer_notice',
                'label' => __('Default notice content for proposals', 'arsol-pfw'),
                'description' => __('This will be used when no custom notice is set for individual proposals.', 'arsol-pfw')
            )
        );

        add_settings_field(
            'arsol_pfw_project_default_customer_notice',
            __('Project Customer Notice', 'arsol-pfw'),
            array($this, 'render_customer_notice_field'),
            'arsol_phases_settings',
            'arsol_phases_customer_notice_defaults',
            array(
                'key' => 'arsol_pfw_project_default_customer_notice',
                'label' => __('Default notice content for projects', 'arsol-pfw'),
                'description' => __('This will be used when no custom notice is set for individual projects.', 'arsol-pfw')
            )
        );

        // Content Display Section
        add_settings_section(
            'arsol_content_display_section',
            __('Content Display', 'arsol-pfw'),
            array($this, 'render_content_display_section'),
            'arsol_phases_settings'
        );

        // Content Display Fields
        $this->add_display_fields('content', 'arsol_content_display_section');

        // Sidebar Display Section
        add_settings_section(
            'arsol_sidebar_display_section',
            __('Sidebar Display', 'arsol-pfw'),
            array($this, 'render_sidebar_display_section'),
            'arsol_phases_settings'
        );

        // Sidebar Display Fields
        $this->add_display_fields('sidebar', 'arsol_sidebar_display_section');

        // Comment Display Section
        add_settings_section(
            'arsol_comment_display_section',
            __('Comments Display', 'arsol-pfw'),
            array($this, 'render_comment_display_section'),
            'arsol_phases_settings'
        );

        // Comment Display Fields
        $this->add_comment_display_fields();

        // Files Display Section
        add_settings_section(
            'arsol_files_display_section',
            __('Files Display', 'arsol-pfw'),
            array($this, 'render_files_display_section'),
            'arsol_phases_settings'
        );

        // Files Display Fields
        $this->add_files_display_fields();

        // Form Display Section
        add_settings_section(
            'arsol_form_display_section',
            __('Form Display', 'arsol-pfw'),
            array($this, 'render_form_display_section'),
            'arsol_phases_settings'
        );

        // Form Display Fields
        $this->add_form_display_fields();
    }

    /**
     * Add display fields for a specific type (content/sidebar)
     */
    private function add_display_fields($type, $section) {
        $types = ['request', 'proposal', 'project'];
        foreach ($types as $phase_type) {
            add_settings_field(
                $type . '_' . $phase_type . '_display',
                ucfirst($phase_type) . ' ' . ucfirst($type),
                array($this, 'render_display_field'),
                'arsol_phases_settings',
                $section,
                [
                    'type' => $type,
                    'phase_type' => $phase_type,
                    'taxonomy' => 'arsol-pfw-' . $phase_type . '-stage'
                ]
            );
        }
    }

    /**
     * Add comment display fields with plural labels
     */
    private function add_comment_display_fields() {
        $types = ['request', 'proposal', 'project'];
        foreach ($types as $phase_type) {
            add_settings_field(
                'comment_' . $phase_type . '_display',
                ucfirst($phase_type) . ' Comments',
                array($this, 'render_display_field'),
                'arsol_phases_settings',
                'arsol_comment_display_section',
                [
                    'type' => 'comment',
                    'phase_type' => $phase_type,
                    'taxonomy' => 'arsol-pfw-' . $phase_type . '-stage'
                ]
            );
        }
    }

    /**
     * Add files display fields
     */
    private function add_files_display_fields() {
        $files_types = [
            'request_file_upload' => [
                'label' => 'Request File Upload',
                'taxonomy' => 'arsol-pfw-request-stage'
            ],
            'proposal_file_display' => [
                'label' => 'Proposal File Display',
                'taxonomy' => 'arsol-pfw-proposal-stage'
            ]
        ];

        foreach ($files_types as $files_type => $config) {
            add_settings_field(
                'files_' . $files_type . '_display',
                $config['label'],
                array($this, 'render_display_field'),
                'arsol_phases_settings',
                'arsol_files_display_section',
                [
                    'type' => 'files',
                    'phase_type' => $files_type,
                    'taxonomy' => $config['taxonomy']
                ]
            );
        }
    }

    /**
     * Add form display fields
     */
    private function add_form_display_fields() {
        $form_types = [
            'request_form' => [
                'label' => 'Request Form',
                'taxonomy' => 'arsol-pfw-request-stage'
            ],
            'edit_request_form' => [
                'label' => 'Edit Request Form',
                'taxonomy' => 'arsol-pfw-request-stage'
            ],
            'project_form' => [
                'label' => 'Project Form',
                'taxonomy' => 'arsol-pfw-project-stage'
            ],
            'edit_project_form' => [
                'label' => 'Edit Project Form',
                'taxonomy' => 'arsol-pfw-project-stage'
            ]
        ];

        foreach ($form_types as $form_type => $config) {
            add_settings_field(
                'form_' . $form_type . '_display',
                $config['label'],
                array($this, 'render_display_field'),
                'arsol_phases_settings',
                'arsol_form_display_section',
                [
                    'type' => 'form',
                    'phase_type' => $form_type,
                    'taxonomy' => $config['taxonomy']
                ]
            );
        }
    }

    /**
     * Render display field
     */
    public function render_display_field($args) {
        $type = $args['type'];
        $phase_type = $args['phase_type'];
        $taxonomy = $args['taxonomy'];
        
        $option_name = 'arsol_' . $type . '_display_settings';
        $settings = get_option($option_name, array());
        
        $visibility_key = $phase_type . '_visibility';
        $stages_key = $phase_type . '_stages';
        
        // Set default visibility based on type - forms and files default to 'show' to prevent overriding
        $default_visibility = (in_array($type, ['form', 'files'])) ? 'show' : 'hide';
        $visibility = isset($settings[$visibility_key]) ? $settings[$visibility_key] : $default_visibility;
        $selected_stages = isset($settings[$stages_key]) ? $settings[$stages_key] : array();
        
        // Get taxonomy terms
        $terms = get_terms(array(
            'taxonomy' => $taxonomy,
            'hide_empty' => false,
        ));
        
        if (is_wp_error($terms)) {
            $terms = array();
        }

        echo '<div class="arsol-pfw-display-field">';
        
        // Visibility dropdown
        echo '<div style="margin-bottom: 10px;">';
        echo '<select name="' . esc_attr($option_name) . '[' . esc_attr($visibility_key) . ']" style="min-width: 150px;">';
        echo '<option value="hide"' . selected($visibility, 'hide', false) . '>' . __('Hide for selected stages', 'arsol-pfw') . '</option>';
        echo '<option value="show"' . selected($visibility, 'show', false) . '>' . __('Show for selected stages', 'arsol-pfw') . '</option>';
        echo '</select>';
        echo '</div>';
        
        // Stage multi-select
        echo '<select name="' . esc_attr($option_name) . '[' . esc_attr($stages_key) . '][]" multiple class="wc-enhanced-select arsol-pfw-multiselect2">';
        foreach ($terms as $term) {
            $selected = in_array($term->term_id, $selected_stages) ? 'selected' : '';
            echo '<option value="' . esc_attr($term->term_id) . '" ' . $selected . '>' . esc_html($term->name) . '</option>';
        }
        echo '</select>';
        
        echo '<p class="description">' . sprintf(__('Control when %s %s is displayed based on stage.', 'arsol-pfw'), $phase_type, $type) . '</p>';
        echo '</div>';
    }

    /**
     * Render phases section
     */
    public function render_phases_section() {
        echo '<p>' . __('Configure phase settings here.', 'arsol-pfw') . '</p>';
    }

    /**
     * Render customer notice section
     */
    public function render_customer_notice_section() {
        echo '<p>' . __('Configure default customer notice content that will be used when no custom notice is set for individual projects, proposals, or requests.', 'arsol-pfw') . '</p>';
    }

    /**
     * Render content display section
     */
    public function render_content_display_section() {
        echo '<p>' . __('Control when post content is displayed on the frontend based on stage.', 'arsol-pfw') . '</p>';
    }

    /**
     * Render sidebar display section
     */
    public function render_sidebar_display_section() {
        echo '<p>' . __('Control when sidebar elements are displayed on the frontend based on stage.', 'arsol-pfw') . '</p>';
    }

    /**
     * Render comment display section
     */
    public function render_comment_display_section() {
        echo '<p>' . __('Control when comment content is displayed on the frontend based on stage.', 'arsol-pfw') . '</p>';
    }

    /**
     * Render files display section
     */
    public function render_files_display_section() {
        echo '<p>' . __('Control when file upload and display elements are shown on the frontend based on stage.', 'arsol-pfw') . '</p>';
    }

    /**
     * Render form display section
     */
    public function render_form_display_section() {
        echo '<p>' . __('Control when forms are displayed on the frontend based on stage.', 'arsol-pfw') . '</p>';
    }

    /**
     * Render customer notice field
     */
    public function render_customer_notice_field($args) {
        $settings = get_option('arsol_phases_settings', array());
        $key = $args['key'];
        $value = isset($settings[$key]) ? $settings[$key] : '';
        $description = $args['description'];

        // Get markdown default for placeholder
        $customer_notice_defaults = \Arsol_Projects_For_Woo\Admin\Setup_Defaults::get_customer_notice_defaults();
        $markdown_default = isset($customer_notice_defaults[$key]) ? $customer_notice_defaults[$key] : '';
        
        echo '<div class="arsol-pfw-customer-notice-field">';
        echo '<textarea name="arsol_phases_settings[' . esc_attr($key) . ']" rows="6" cols="80" class="large-text" placeholder="' . esc_attr($markdown_default) . '">' . esc_textarea($value) . '</textarea>';
        echo '<p class="description">' . esc_html($description) . '</p>';
        echo '<p class="description"><em>' . __('Leave empty to use the default content from markdown files.', 'arsol-pfw') . '</em></p>';
        echo '</div>';
    }

    /**
     * Enqueue admin scripts
     */
    public function enqueue_admin_scripts($hook) {
        // Only load on our settings page
        if (strpos($hook, 'arsol-projects-settings') === false) {
            return;
        }

        // Check if we're on the display tab
        $tab = isset($_GET['tab']) ? $_GET['tab'] : 'general';
        if ($tab !== 'display') {
            return;
        }

        // Use WooCommerce's enhanced select if available
        if (class_exists('WooCommerce')) {
            wp_enqueue_script('selectWoo');
            wp_enqueue_style('select2');
            wp_enqueue_style('woocommerce_admin_styles');
            
            // Simple SelectWoo initialization without AJAX
            wp_add_inline_script('selectWoo', '
                jQuery(document).ready(function($) {
                    $(".wc-enhanced-select").selectWoo({
                        placeholder: "' . esc_js(__('Select stages...', 'arsol-pfw')) . '",
                        allowClear: true,
                        width: "100%"
                    });
                });
            ');
        } else {
            // Fallback to basic WordPress styling
            wp_enqueue_script('jquery');
            wp_add_inline_script('jquery', '
                jQuery(document).ready(function($) {
                    $(".wc-enhanced-select").css({
                        "width": "100%",
                        "min-height": "30px"
                    });
                });
            ');
        }
    }
}
