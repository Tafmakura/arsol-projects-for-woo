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
        add_action('admin_init', array($this, 'register_settings'));
    }

    /**
     * Register settings
     */
    public function register_settings() {
        register_setting('arsol_stages_settings', 'arsol_stages_settings');

        add_settings_section(
            'arsol_stages_general_settings',
            __('Stage Settings', 'arsol-pfw'),
            array($this, 'render_stages_section'),
            'arsol_stages_settings'
        );
    }

    /**
     * Render stages section
     */
    public function render_stages_section() {
        echo '<p>' . __('Configure stage settings here.', 'arsol-pfw') . '</p>';
    }
} 