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

        // Phase Settings Section
        add_settings_section(
            'arsol_phases_general_settings',
            __('Phase Settings', 'arsol-pfw'),
            array($this, 'render_phases_section'),
            'arsol_phases_settings'
        );

        // Customer Notice Defaults Section
        add_settings_section(
            'arsol_phases_customer_notice_defaults',
            __('Customer Notice Defaults', 'arsol-pfw'),
            array($this, 'render_customer_notice_section'),
            'arsol_phases_settings'
        );

        // Customer Notice Fields
        add_settings_field(
            'arsol_pfw_project_default_customer_notice',
            __('Project Customer Notice Default', 'arsol-pfw'),
            array($this, 'render_customer_notice_field'),
            'arsol_phases_settings',
            'arsol_phases_customer_notice_defaults',
            array(
                'key' => 'arsol_pfw_project_default_customer_notice',
                'label' => __('Default notice content for projects', 'arsol-pfw'),
                'description' => __('This will be used when no custom notice is set for individual projects.', 'arsol-pfw')
            )
        );

        add_settings_field(
            'arsol_pfw_proposal_default_customer_notice',
            __('Proposal Customer Notice Default', 'arsol-pfw'),
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
            'arsol_pfw_request_default_customer_notice',
            __('Request Customer Notice Default', 'arsol-pfw'),
            array($this, 'render_customer_notice_field'),
            'arsol_phases_settings',
            'arsol_phases_customer_notice_defaults',
            array(
                'key' => 'arsol_pfw_request_default_customer_notice',
                'label' => __('Default notice content for requests', 'arsol-pfw'),
                'description' => __('This will be used when no custom notice is set for individual requests.', 'arsol-pfw')
            )
        );
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
     * Render customer notice field
     */
    public function render_customer_notice_field($args) {
        $settings = get_option('arsol_phases_settings', array());
        $key = $args['key'];
        $value = isset($settings[$key]) ? $settings[$key] : '';
        $description = $args['description'];

        // Get markdown default for placeholder
        $markdown_default = \Arsol_Projects_For_Woo\Admin\Setup_Defaults::get_effective_default_message($key);
        
        echo '<div class="arsol-pfw-customer-notice-field">';
        echo '<textarea name="arsol_phases_settings[' . esc_attr($key) . ']" rows="6" cols="80" class="large-text" placeholder="' . esc_attr($markdown_default) . '">' . esc_textarea($value) . '</textarea>';
        echo '<p class="description">' . esc_html($description) . '</p>';
        echo '<p class="description"><em>' . __('Leave empty to use the default content from markdown files.', 'arsol-pfw') . '</em></p>';
        echo '</div>';
    }
} 