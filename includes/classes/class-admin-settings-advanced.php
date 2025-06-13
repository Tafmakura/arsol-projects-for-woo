<?php
/**
 * Admin Settings Advanced Class
 *
 * @package Arsol_Projects_For_Woo\Admin
 * @version 1.0.0
 */

namespace Arsol_Projects_For_Woo\Admin;

use Arsol_Projects_For_Woo\Woocommerce_Logs;

if (!defined('ABSPATH')) {
    exit;
}

class Settings_Advanced {

    private $shortcode_fields = [];

    public function __construct() {
        add_action('init', array($this, 'init_translations'));
        add_action('admin_init', array($this, 'register_settings'));
        add_action('admin_notices', array($this, 'show_admin_notices'));
    }

    public function init_translations() {
        $this->shortcode_fields = [
            'project_overview_active_shortcode' => [
                'title' => __('Active Project Overview', 'arsol-pfw'),
                'description' => __('Overrides the overview section for active projects.', 'arsol-pfw')
            ],
            'project_overview_proposal_shortcode' => [
                'title' => __('Project Proposal Overview', 'arsol-pfw'),
                'description' => __('Overrides the overview section for project proposals.', 'arsol-pfw')
            ],
            'project_overview_request_shortcode' => [
                'title' => __('Project Request Overview', 'arsol-pfw'),
                'description' => __('Overrides the overview section for project requests.', 'arsol-pfw')
            ],
            'create_project_form_shortcode' => [
                'title' => __('Create Project Form', 'arsol-pfw'),
                'description' => __('Overrides the form for creating new projects.', 'arsol-pfw')
            ],
            'create_project_request_form_shortcode' => [
                'title' => __('Create Project Request Form', 'arsol-pfw'),
                'description' => __('Overrides the form for requesting new projects.', 'arsol-pfw')
            ],
            'project_request_edit_form_shortcode' => [
                'title' => __('Project Request Edit Form', 'arsol-pfw'),
                'description' => __('Overrides the form for editing a pending project request.', 'arsol-pfw')
            ],
            'projects_listing_shortcode' => [
                'title' => __('Active Projects Listing', 'arsol-pfw'),
                'description' => __('Overrides the display of all active projects for a user.', 'arsol-pfw')
            ],
            'project_proposal_listings_shortcode' => [
                'title' => __('Project Proposals Listing', 'arsol-pfw'),
                'description' => __('Overrides the display of all project proposals for a user.', 'arsol-pfw')
            ],
            'project_requests_listings_shortcode' => [
                'title' => __('Project Requests Listing', 'arsol-pfw'),
                'description' => __('Overrides the display of all project requests for a user.', 'arsol-pfw')
            ],
            'access_denied_shortcode' => [
                'title' => __('Access Denied Notice', 'arsol-pfw'),
                'description' => __('Overrides denied access notice.', 'arsol-pfw')
            ],
        ];
    }

    public function register_settings() {
        register_setting('arsol_projects_advanced_settings', 'arsol_projects_advanced_settings');
        register_setting('arsol_pfw_debug_options', 'arsol_pfw_debug_options');

        // Template Overrides Section
        add_settings_section(
            'arsol_projects_template_overrides_section',
            __('Template Overrides', 'arsol-pfw'),
            array($this, 'render_template_overrides_description'),
            'arsol_projects_advanced_settings'
        );

        foreach ($this->shortcode_fields as $id => $field_data) {
            add_settings_field(
                $id,
                $field_data['title'],
                array($this, 'render_text_field'),
                'arsol_projects_advanced_settings',
                'arsol_projects_template_overrides_section',
                [
                    'id' => $id,
                    'pattern' => '^\\[[a-zA-Z0-9\\s_-]+\\]$',
                    'description' => $field_data['description']
                ]
            );
        }

        // Debugging Section
        add_settings_section(
            'arsol_projects_debugging_section',
            __('Debugging', 'arsol-pfw'),
            array($this, 'render_debugging_description'),
            'arsol_projects_advanced_settings'
        );

        // Add debug option checkboxes
        if (class_exists('Arsol_Projects_For_Woo\Woocommerce_Logs')) {
            $debug_options = Woocommerce_Logs::get_available_debug_options();
            foreach ($debug_options as $option_key => $option_label) {
                add_settings_field(
                    $option_key,
                    $option_label,
                    array($this, 'render_debug_checkbox'),
                    'arsol_projects_advanced_settings',
                    'arsol_projects_debugging_section',
                    [
                        'option_key' => $option_key,
                        'label' => $option_label
                    ]
                );
            }
        }
    }

    public function render_template_overrides_description() {
        echo '<p>' . esc_html__('Use these settings to override the default plugin templates with your own shortcodes. This allows for custom layouts and designs for various components without needing to edit plugin files directly. Enter the shortcode you wish to use for each template override.', 'arsol-pfw') . '</p>';
        echo '<p><strong>' . esc_html__('Important:', 'arsol-pfw') . '</strong> ' . esc_html__('Template overrides are placed inside existing wrapper elements to preserve page structure and styling. Your shortcode content will appear within the appropriate container divs.', 'arsol-pfw') . '</p>';
    }

    public function render_debugging_description() {
        echo '<p>' . esc_html__('Enable debug logging for different components to help troubleshoot issues. Logs can be found', 'arsol-pfw') . ' <a href="' . esc_url(admin_url('admin.php?page=wc-status&tab=logs')) . '" target="_blank">' . esc_html__('here', 'arsol-pfw') . '</a>.</p>';
        echo '<p><strong>' . esc_html__('Note:', 'arsol-pfw') . '</strong> ' . esc_html__('Only enable logging when needed as it can generate large log files over time.', 'arsol-pfw') . '</p>';
    }

    public function render_debug_checkbox($args) {
        $debug_options = get_option('arsol_pfw_debug_options', array());
        $checked = !empty($debug_options[$args['option_key']]);
        ?>
        <label>
            <input type="checkbox" 
                   name="arsol_pfw_debug_options[<?php echo esc_attr($args['option_key']); ?>]" 
                   value="1" 
                   <?php checked($checked); ?> />
            <?php echo esc_html__('Enable logging for this component', 'arsol-pfw'); ?>
        </label>
        <?php
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
        <?php if (!empty($args['description'])) : ?>
            <p class="description"><?php echo esc_html($args['description']); ?></p>
        <?php endif; ?>
        <?php
    }

    public function show_admin_notices() {
        if (isset($_GET['logs_cleared']) && $_GET['logs_cleared'] == '1') {
            echo '<div class="notice notice-success is-dismissible">';
            echo '<p>' . esc_html__('Plugin logs have been cleared successfully.', 'arsol-pfw') . '</p>';
            echo '</div>';
        }
        
        if (isset($_GET['error']) && $_GET['error'] == '1') {
            echo '<div class="notice notice-error is-dismissible">';
            echo '<p>' . esc_html__('Error clearing logs. Logging class not available.', 'arsol-pfw') . '</p>';
            echo '</div>';
        }
    }
}
