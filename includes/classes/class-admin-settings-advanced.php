<?php
/**
 * Admin Settings Advanced Class
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
        $this->shortcode_fields = [
            'project_overview_shortcode' => __('Project Overview', 'arsol-pfw'),
            'create_project_form_shortcode' => __('Create Project Form', 'arsol-pfw'),
            'request_project_form_shortcode' => __('Request Project Form', 'arsol-pfw'),
            'projects_listing_shortcode' => __('Projects Listing', 'arsol-pfw'),
            'project_proposal_listings_shortcode' => __('Project Proposal Listings', 'arsol-pfw'),
            'project_requests_listings_shortcode' => __('Project Requests Listings', 'arsol-pfw'),
            'access_denied_shortcode' => __('Access Denied', 'arsol-pfw'),
        ];
        add_action('admin_init', array($this, 'register_settings'));
    }

    public function register_settings() {
        register_setting('arsol_projects_advanced_settings', 'arsol_projects_advanced_settings');

        add_settings_section(
            'arsol_projects_template_overrides_section',
            __('Template Overrides', 'arsol-pfw'),
            array($this, 'render_template_overrides_description'),
            'arsol_projects_advanced_settings'
        );

        foreach ($this->shortcode_fields as $id => $title) {
            add_settings_field(
                $id,
                $title,
                array($this, 'render_text_field'),
                'arsol_projects_advanced_settings',
                'arsol_projects_template_overrides_section',
                [
                    'id' => $id,
                    'pattern' => '^\\[[a-zA-Z0-9\\s_-]+\\]$'
                ]
            );
        }
    }

    public function render_template_overrides_description() {
        echo '<p>' . esc_html__('Use these settings to override the default plugin templates with your own shortcodes. This allows for custom layouts and designs for various components without needing to edit plugin files directly. Enter the shortcode you wish to use for each template override.', 'arsol-pfw') . '</p>';
    }

    public function render_text_field($args) {
        $settings = get_option('arsol_projects_advanced_settings');
        $value = isset($settings[$args['id']]) ? $settings[$args['id']] : '';
        $pattern = isset($args['pattern']) ? $args['pattern'] : '.*';
        ?>
        <input type="text"
               id="<?php echo esc_attr($args['id']); ?>"
               name="arsol_projects_advanced_settings[<?php echo esc_attr($args['id']); ?>]"
               value="<?php echo esc_attr($value); ?>"
               class="regular-text"
               pattern="<?php echo esc_attr($pattern); ?>"
               title="<?php esc_attr_e('Shortcode must be in the format [shortcode_name]', 'arsol-pfw'); ?>">
        <?php
    }
}
