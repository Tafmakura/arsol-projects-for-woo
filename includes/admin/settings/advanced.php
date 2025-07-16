<?php
/**
 * Admin Template Settings Class
 *
 * Handles template overrides for the plugin.
 *
 * @package Arsol_Projects_For_Woo\Admin
 * @version 1.0.0
 */

namespace Arsol_Projects_For_Woo\Admin\Settings;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Advanced class
 */
class Advanced {

    /**
     * Shortcode fields configuration
     *
     * @var array
     */
    private $shortcode_fields = array();

    /**
     * Constructor
     */
    public function __construct() {
        // Register settings after init to ensure text domain is loaded
        add_action( 'init', array( $this, 'setup_settings' ), 20 );
    }

    /**
     * Setup settings after init
     */
    public function setup_settings() {
        // Initialize translations first
        $this->init_translations();
        // Then register settings on admin_init
        add_action( 'admin_init', array( $this, 'register_settings' ) );
    }

    /**
     * Initialize translations and shortcode fields configuration
     */
    public function init_translations() {
        $this->shortcode_fields = array(
            'arsol_pfw_project_overview' => array(
                'title'       => __( 'Active Project Overview', 'arsol-pfw' ),
                'description' => __( 'Overrides the overview section for active projects.', 'arsol-pfw' ),
                'placeholder' => '[arsol_pfw_project_overview]',
            ),
            'arsol_pfw_proposal_overview' => array(
                'title'       => __( 'Project Proposal Overview', 'arsol-pfw' ),
                'description' => __( 'Overrides the overview section for project proposals.', 'arsol-pfw' ),
                'placeholder' => '[arsol_pfw_proposal_overview]',
            ),
            'arsol_pfw_request_overview' => array(
                'title'       => __( 'Project Request Overview', 'arsol-pfw' ),
                'description' => __( 'Overrides the overview section for project requests.', 'arsol-pfw' ),
                'placeholder' => '[arsol_pfw_request_overview]',
            ),
            'arsol_pfw_project_form' => array(
                'title'       => __( 'Project Form', 'arsol-pfw' ),
                'description' => __( 'Overrides the Project Form for creating and editing projects.', 'arsol-pfw' ),
                'placeholder' => '[arsol_pfw_project_form]',
            ),
            'arsol_pfw_edit_project_form' => array(
                'title'       => __( 'Edit Project Form', 'arsol-pfw' ),
                'description' => __( 'Overrides the Project Form specifically for editing existing projects.', 'arsol-pfw' ),
                'placeholder' => '[arsol_pfw_project_form is_edit="true"]',
            ),
            'arsol_pfw_request_form' => array(
                'title'       => __( 'Create Request Form', 'arsol-pfw' ),
                'description' => __( 'Overrides the Request Form for creating and editing requests.', 'arsol-pfw' ),
                'placeholder' => '[arsol_pfw_request_form]',
            ),
            'arsol_pfw_edit_request_form' => array(
                'title'       => __( 'Edit Request Form', 'arsol-pfw' ),
                'description' => __( 'Overrides the Request Project Form specifically for editing existing requests.', 'arsol-pfw' ),
                'placeholder' => '[arsol_pfw_request_form is_edit="true"]',
            ),
            'arsol_pfw_projects_list' => array(
                'title'       => __( 'Active Projects List', 'arsol-pfw' ),
                'description' => __( 'Overrides the listing section for active projects.', 'arsol-pfw' ),
                'placeholder' => '[arsol_pfw_projects_list]',
            ),
            'arsol_pfw_proposals_list' => array(
                'title'       => __( 'Project Proposals List', 'arsol-pfw' ),
                'description' => __( 'Overrides the listing section for project proposals.', 'arsol-pfw' ),
                'placeholder' => '[arsol_pfw_proposals_list]',
            ),
            'arsol_pfw_requests_list' => array(
                'title'       => __( 'Project Requests List', 'arsol-pfw' ),
                'description' => __( 'Overrides the listing section for project requests.', 'arsol-pfw' ),
                'placeholder' => '[arsol_pfw_requests_list]',
            ),
            'arsol_pfw_proposal_files' => array(
                'title'       => __( 'Proposal Files Display', 'arsol-pfw' ),
                'description' => __( 'Overrides the files section for project proposals.', 'arsol-pfw' ),
                'placeholder' => '[arsol_pfw_proposal_files]',
            ),
            'arsol_pfw_request_file_upload' => array(
                'title'       => __( 'Request File Upload', 'arsol-pfw' ),
                'description' => __( 'Overrides the file upload section for project requests.', 'arsol-pfw' ),
                'placeholder' => '[arsol_pfw_request_file_upload]',
            ),
            'arsol_pfw_project_files_list' => array(
                'title'       => __( 'Project Files List', 'arsol-pfw' ),
                'description' => __( 'Overrides the files listing section for active projects.', 'arsol-pfw' ),
                'placeholder' => '[arsol_pfw_project_files_list]',
            ),
            'arsol_pfw_no_access' => array(
                'title'       => __( 'Access Denied Notice', 'arsol-pfw' ),
                'description' => __( 'Overrides denied access notice.', 'arsol-pfw' ),
                'placeholder' => '[arsol_pfw_no_access]',
            ),
        );
    }

    /**
     * Register settings
     */
    public function register_settings() {
        // Register main settings group
        register_setting(
            'arsol_pfw_advanced_settings', 
            'arsol_pfw_advanced_settings',
            array(
                'sanitize_callback' => array( $this, 'validate_settings' ),
                'default'           => array(),
            )
        );

        // Template Overrides Section
        add_settings_section(
            'arsol_projects_template_overrides_section',
            __( 'Template Overrides', 'arsol-pfw' ),
            array( $this, 'render_template_overrides_description' ),
            'arsol_pfw_advanced_settings'
        );

        // Add settings fields for each shortcode
        foreach ( $this->shortcode_fields as $id => $field_data ) {
            add_settings_field(
                $id,
                $field_data['title'],
                array( $this, 'render_shortcode_field' ),
                'arsol_pfw_advanced_settings',
                'arsol_projects_template_overrides_section',
                array(
                    'id'          => $id,
                    'description' => $field_data['description'],
                    'placeholder' => $field_data['placeholder'],
                )
            );
        }
    }

    /**
     * Render template overrides section description
     */
    public function render_template_overrides_description() {
        echo '<p>' . esc_html__( 'Override default templates with custom shortcodes. Enter valid shortcodes in the format [shortcode_name] to replace the default template rendering.', 'arsol-pfw' ) . '</p>';
        echo '<p><strong>' . esc_html__( 'Warning:', 'arsol-pfw' ) . '</strong> ' . esc_html__( 'Template overrides will completely replace the default content. Make sure your shortcodes are working properly before saving.', 'arsol-pfw' ) . '</p>';
    }

    /**
     * Render shortcode field
     *
     * @param array $args Field arguments.
     */
    public function render_shortcode_field( $args ) {
        $settings    = get_option( 'arsol_pfw_advanced_settings', array() );
        $id          = $args['id'];
        $value       = isset( $settings[ $id ] ) ? $settings[ $id ] : '';
        $description = isset( $args['description'] ) ? $args['description'] : '';
        $placeholder = isset( $args['placeholder'] ) ? $args['placeholder'] : '';

        printf(
            '<input type="text" id="%1$s" name="arsol_pfw_advanced_settings[%1$s]" value="%2$s" class="regular-text" placeholder="%3$s" pattern="%4$s" />',
            esc_attr( $id ),
            esc_attr( $value ),
            esc_attr( $placeholder ),
            esc_attr( '^\\[[a-zA-Z0-9\\s_=-]+\\]$' )
        );

        if ( ! empty( $description ) ) {
            echo '<p class="description">' . esc_html( $description ) . '</p>';
        }
    }

    /**
     * Validate settings before saving
     *
     * @param array $input Raw input data.
     * @return array Validated data.
     */
    public function validate_settings( $input ) {
        $validated = array();

        if ( ! is_array( $input ) ) {
            return $validated;
        }

        foreach ( $input as $key => $value ) {
            // Skip if not a valid shortcode field
            if ( ! array_key_exists( $key, $this->shortcode_fields ) ) {
                continue;
            }

            // Skip empty values
            if ( empty( $value ) ) {
                continue;
            }

            // Sanitize the value
            $sanitized_value = sanitize_text_field( $value );

            // Validate shortcode format
            if ( $this->is_valid_shortcode( $sanitized_value ) ) {
                $validated[ $key ] = $sanitized_value;
            } else {
                // Add error notice for invalid shortcode
                add_settings_error(
                    'arsol_pfw_advanced_settings',
                    $key,
                    sprintf(
                        /* translators: %1$s: field name, %2$s: invalid shortcode */
                        __( 'Invalid shortcode format for %1$s: "%2$s". Please use the format [shortcode_name].', 'arsol-pfw' ),
                        $this->shortcode_fields[ $key ]['title'],
                        $sanitized_value
                    ),
                    'error'
                );
            }
        }

        return $validated;
    }

    /**
     * Validate shortcode format
     *
     * @param string $shortcode The shortcode to validate.
     * @return bool True if valid, false otherwise.
     */
    private function is_valid_shortcode( $shortcode ) {
        // Check if it matches the basic shortcode pattern
        if ( ! preg_match( '/^\[[\w\s_=-]+\]$/', $shortcode ) ) {
            return false;
        }

        // Extract shortcode name
        $shortcode_name = trim( $shortcode, '[]' );
        $shortcode_name = explode( ' ', $shortcode_name )[0];

        // Check if shortcode exists or is one of our known shortcodes
        return shortcode_exists( $shortcode_name ) || $this->is_known_shortcode( $shortcode_name );
    }

    /**
     * Check if shortcode is one of our known plugin shortcodes
     *
     * @param string $shortcode_name The shortcode name to check.
     * @return bool True if known, false otherwise.
     */
    private function is_known_shortcode( $shortcode_name ) {
        $known_shortcodes = array(
            'arsol_pfw_project_overview',
            'arsol_pfw_proposal_overview',
            'arsol_pfw_request_overview',
            'arsol_pfw_project_form',
            'arsol_pfw_request_form',
            'arsol_pfw_projects_list',
            'arsol_pfw_proposals_list',
            'arsol_pfw_requests_list',
            'arsol_pfw_proposal_files',
            'arsol_pfw_request_file_upload',
            'arsol_pfw_project_files_list',
            'arsol_pfw_no_access',
        );

        return in_array( $shortcode_name, $known_shortcodes, true );
    }
}
