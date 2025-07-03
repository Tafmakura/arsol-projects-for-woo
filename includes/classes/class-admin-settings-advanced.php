<?php
/**
 * Admin Template Settings Class
 *
 * Handles template overrides for the plugin.
 *
 * @package Arsol_Projects_For_Woo\Admin
 * @version 1.0.0
 */

namespace Arsol_Projects_For_Woo\Admin;

if (!defined('ABSPATH')) {
    exit;
}

class Settings_Advanced {

    private $shortcode_fields = [];

    public function __construct() {
        add_action('init', array($this, 'init_translations'));
        add_action('admin_init', array($this, 'register_settings'));
    }

    public function init_translations() {
        $this->shortcode_fields = [
            'arsol_pfw_project_overview' => [
                'title' => __('Active Project Overview', 'arsol-pfw'),
                'description' => __('Overrides the overview section for active projects.', 'arsol-pfw'),
                'placeholder' => '[arsol_pfw_project_overview]'
            ],
            'arsol_pfw_proposal_overview' => [
                'title' => __('Project Proposal Overview', 'arsol-pfw'),
                'description' => __('Overrides the overview section for project proposals.', 'arsol-pfw'),
                'placeholder' => '[arsol_pfw_proposal_overview]'
            ],
            'arsol_pfw_request_overview' => [
                'title' => __('Project Request Overview', 'arsol-pfw'),
                'description' => __('Overrides the overview section for project requests.', 'arsol-pfw'),
                'placeholder' => '[arsol_pfw_request_overview]'
            ],
            'arsol_pfw_project_form' => [
                'title' => __('Create Project Form', 'arsol-pfw'),
                'description' => __('Overrides the Project Form for creating and editing projects.', 'arsol-pfw'),
                'placeholder' => '[arsol_pfw_project_form]'
            ],
            'arsol_pfw_edit_project_form' => [
                'title' => __('Edit Project Form', 'arsol-pfw'),
                'description' => __('Overrides the Project Form specifically for editing existing projects.', 'arsol-pfw'),
                'placeholder' => '[arsol_pfw_project_form is_edit="true"]'
            ],
            
            'arsol_pfw_request_form' => [
                'title' => __('Create Request Form', 'arsol-pfw'),
                'description' => __('Overrides the Request Form for creating and editing requests.', 'arsol-pfw'),
                'placeholder' => '[arsol_pfw_request_form]'
            ],
            'arsol_pfw_edit_request_form' => [
                'title' => __('Edit Request Form', 'arsol-pfw'),
                'description' => __('Overrides the Request Project Form specifically for editing existing requests.', 'arsol-pfw'),
                'placeholder' => '[arsol_pfw_request_form is_edit="true"]'
            ],
            'arsol_pfw_projects_list' => [
                'title' => __('Active Projects List', 'arsol-pfw'),
                'description' => __('Overrides the listing section for active projects.', 'arsol-pfw'),
                'placeholder' => '[arsol_pfw_projects_list]'
            ],
            'arsol_pfw_proposals_list' => [
                'title' => __('Project Proposals List', 'arsol-pfw'),
                'description' => __('Overrides the listing section for project proposals.', 'arsol-pfw'),
                'placeholder' => '[arsol_pfw_proposals_list]'
            ],
            'arsol_pfw_requests_list' => [
                'title' => __('Project Requests List', 'arsol-pfw'),
                'description' => __('Overrides the listing section for project requests.', 'arsol-pfw'),
                'placeholder' => '[arsol_pfw_requests_list]'
            ],
            'arsol_pfw_proposal_files' => [
                'title' => __('Proposal Files Display', 'arsol-pfw'),
                'description' => __('Overrides the files section for project proposals.', 'arsol-pfw'),
                'placeholder' => '[arsol_pfw_proposal_files]'
            ],
            'arsol_pfw_request_file_upload' => [
                'title' => __('Request File Upload', 'arsol-pfw'),
                'description' => __('Overrides the file upload section for project requests.', 'arsol-pfw'),
                'placeholder' => '[arsol_pfw_request_file_upload]'
            ],
            'arsol_pfw_project_files_list' => [
                'title' => __('Project Files List', 'arsol-pfw'),
                'description' => __('Overrides the files listing section for active projects.', 'arsol-pfw'),
                'placeholder' => '[arsol_pfw_project_files_list]'
            ],
            'arsol_pfw_no_access' => [
                'title' => __('Access Denied Notice', 'arsol-pfw'),
                'description' => __('Overrides denied access notice.', 'arsol-pfw'),
                'placeholder' => '[arsol_pfw_no_access]'
            ],
        ];
    }

    public function register_settings() {
        register_setting(
            'arsol_pfw_advanced_settings', 
            'arsol_pfw_advanced_settings',
            array(
                'sanitize_callback' => array($this, 'sanitize_settings'),
                'default' => array()
            )
        );

        // Template Overrides Section
        add_settings_section(
            'arsol_projects_template_overrides_section',
            __('Template Overrides', 'arsol-pfw'),
            array($this, 'render_template_overrides_description'),
            'arsol_pfw_advanced_settings'
        );

        foreach ($this->shortcode_fields as $id => $field_data) {
            add_settings_field(
                $id,
                $field_data['title'],
                array($this, 'render_text_field'),
                'arsol_pfw_advanced_settings',
                'arsol_projects_template_overrides_section',
                [
                    'id' => $id,
                    'pattern' => '^\\[[a-zA-Z0-9\\s_-]+\\]$',
                    'description' => $field_data['description'],
                    'placeholder' => $field_data['placeholder']
                ]
            );
        }
    }

    /**
     * Render template overrides description
     */
    public function render_template_overrides_description() {
        echo '<p>' . __('Override default templates with custom shortcodes. Enter valid shortcodes in the format [shortcode_name] to replace the default template rendering.', 'arsol-pfw') . '</p>';
        echo '<p><strong>' . __('Warning:', 'arsol-pfw') . '</strong> ' . __('Template overrides will completely replace the default content. Make sure your shortcodes are working properly before saving.', 'arsol-pfw') . '</p>';
    }

    /**
     * Render text field
     */
    public function render_text_field($args) {
        $settings = get_option('arsol_pfw_advanced_settings', array());
        $id = $args['id'];
        $value = isset($settings[$id]) ? $settings[$id] : '';
        $pattern = isset($args['pattern']) ? $args['pattern'] : '';
        $description = isset($args['description']) ? $args['description'] : '';
        $placeholder = isset($args['placeholder']) ? $args['placeholder'] : '';

        echo '<input type="text" id="' . esc_attr($id) . '" name="arsol_pfw_advanced_settings[' . esc_attr($id) . ']" value="' . esc_attr($value) . '" class="regular-text" placeholder="' . esc_attr($placeholder) . '"';
        if (!empty($pattern)) {
            echo ' pattern="' . esc_attr($pattern) . '"';
        }
        echo ' />';
        
        if (!empty($description)) {
            echo '<p class="description">' . esc_html($description) . '</p>';
        }
    }

    /**
     * Sanitize settings before saving
     *
     * @param array $input Raw input data
     * @return array Sanitized data
     */
    public function sanitize_settings($input) {
        $sanitized = array();
        
        if (!is_array($input)) {
            return $sanitized;
        }
        
        foreach ($input as $key => $value) {
            if (is_string($value)) {
                // Basic sanitization for shortcode fields
                $sanitized[$key] = sanitize_text_field($value);
            }
        }
        
        return $sanitized;
    }
}
