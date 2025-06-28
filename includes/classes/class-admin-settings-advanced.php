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

    private $default_message_fields = [];
    private $shortcode_fields = [];

    public function __construct() {
        add_action('init', array($this, 'init_translations'));
        add_action('admin_init', array($this, 'register_settings'));
    }

    public function init_translations() {
        // Initialize default message fields
        $this->default_message_fields = [
            'arsol_pfw_project_default_empty_content' => [
                'title' => __('Project (Empty Content)', 'arsol-pfw'),
                'description' => __('Default message displayed when a project has no content or orders.', 'arsol-pfw')
            ],
            'arsol_pfw_proposal_default_empty_content' => [
                'title' => __('Proposal (Empty Content)', 'arsol-pfw'),
                'description' => __('Default message displayed when a proposal has no content.', 'arsol-pfw')
            ],
            'arsol_pfw_request_default_on_hold_content' => [
                'title' => __('Request (On Hold)', 'arsol-pfw'),
                'description' => __('Default message displayed when a project request is on hold.', 'arsol-pfw')
            ],
            'arsol_pfw_request_default_under_review_content' => [
                'title' => __('Request (Under Review)', 'arsol-pfw'),
                'description' => __('Default message displayed when a project request is under review.', 'arsol-pfw')
            ]
        ];
        
        $this->shortcode_fields = [
            'arsol_pfw_project_overview' => [
                'title' => __('Active Project Overview', 'arsol-pfw'),
                'description' => __('Overrides the overview section for active projects.', 'arsol-pfw'),
                'placeholder' => '[arsol_pfw_project_content_active]'
            ],
            'arsol_pfw_proposal_overview' => [
                'title' => __('Project Proposal Overview', 'arsol-pfw'),
                'description' => __('Overrides the overview section for project proposals.', 'arsol-pfw'),
                'placeholder' => '[arsol_pfw_project_content_proposal]'
            ],
            'arsol_pfw_request_overview' => [
                'title' => __('Project Request Overview', 'arsol-pfw'),
                'description' => __('Overrides the overview section for project requests.', 'arsol-pfw'),
                'placeholder' => '[arsol_pfw_project_content_request]'
            ],
            'arsol_pfw_project_form' => [
                'title' => __('Create Project Form', 'arsol-pfw'),
                'description' => __('Overrides the Create Project Form for creating new projects.', 'arsol-pfw'),
                'placeholder' => '[arsol_pfw_project_create_form]'
            ],
            'arsol_pfw_request_form' => [
                'title' => __('Request Project Form', 'arsol-pfw'),
                'description' => __('Overrides the Request Project Form for requesting new projects.', 'arsol-pfw'),
                'placeholder' => '[arsol_pfw_project_request_form]'
            ],
            'arsol_pfw_proposal_form' => [
                'title' => __('Request Project Form (Edit)', 'arsol-pfw'),
                'description' => __('Overrides the Request Project Form for editing a pending project request.', 'arsol-pfw'),
                'placeholder' => '[arsol_pfw_project_request_form is_edit="true"]'
            ],
            'arsol_pfw_projects_list' => [
                'title' => __('Active Projects Listing', 'arsol-pfw'),
                'description' => __('Overrides the display of all active projects for a user.', 'arsol-pfw'),
                'placeholder' => '[arsol_pfw_projects_listing_active]'
            ],
            'arsol_pfw_proposal_list' => [
                'title' => __('Project Proposals Listing', 'arsol-pfw'),
                'description' => __('Overrides the display of all project proposals for a user.', 'arsol-pfw'),
                'placeholder' => '[arsol_pfw_projects_listing_proposals]'
            ],
            'arsol_pfw_requests_list' => [
                'title' => __('Project Requests Listing', 'arsol-pfw'),
                'description' => __('Overrides the display of all project requests for a user.', 'arsol-pfw'),
                'placeholder' => '[arsol_pfw_projects_listing_requests]'
            ],
            'arsol_pfw_no_access' => [
                'title' => __('Access Denied Notice', 'arsol-pfw'),
                'description' => __('Overrides denied access notice.', 'arsol-pfw'),
                'placeholder' => '[arsol_pfw_access_denied]'
            ],
        ];
    }

    public function register_settings() {
        register_setting(
            'arsol_projects_templates_settings', 
            'arsol_projects_templates_settings',
            array(
                'sanitize_callback' => array($this, 'sanitize_settings'),
                'default' => array()
            )
        );

        // Default Messages Section
        add_settings_section(
            'arsol_projects_default_messages_section',
            __('Default Messages', 'arsol-pfw'),
            array($this, 'render_default_messages_description'),
            'arsol_projects_templates_settings'
        );

        foreach ($this->default_message_fields as $id => $field_data) {
            add_settings_field(
                $id,
                $field_data['title'],
                array($this, 'render_textarea_field'),
                'arsol_projects_templates_settings',
                'arsol_projects_default_messages_section',
                [
                    'id' => $id,
                    'description' => $field_data['description'],
                    'rows' => 10,
                    'cols' => 80
                ]
            );
        }

        // Template Overrides Section
        add_settings_section(
            'arsol_projects_template_overrides_section',
            __('Template Overrides', 'arsol-pfw'),
            array($this, 'render_template_overrides_description'),
            'arsol_projects_templates_settings'
        );

        foreach ($this->shortcode_fields as $id => $field_data) {
            add_settings_field(
                $id,
                $field_data['title'],
                array($this, 'render_text_field'),
                'arsol_projects_templates_settings',
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
        
        // Sanitize default message fields (allow HTML and markdown)
        $message_fields = array_keys($this->default_message_fields);
        foreach ($message_fields as $field) {
            if (isset($input[$field])) {
                // Allow basic HTML and markdown - sanitize but preserve formatting
                $sanitized[$field] = wp_kses_post($input[$field]);
            }
        }
        
        // Sanitize shortcode fields (must be valid shortcodes)
        $shortcode_fields = array_keys($this->shortcode_fields);
        foreach ($shortcode_fields as $field) {
            if (isset($input[$field])) {
                $value = sanitize_text_field($input[$field]);
                // Validate shortcode format if not empty
                if (!empty($value) && !preg_match('/^\[[\w\s_-]+(\s+[^]]+)?\]$/', $value)) {
                    // Invalid shortcode format - skip saving
                    add_settings_error(
                        'arsol_projects_templates_settings',
                        $field,
                        sprintf(__('Invalid shortcode format for %s. Please use format: [shortcode_name]', 'arsol-pfw'), $field)
                    );
                            } else {
                    $sanitized[$field] = $value;
                }
            }
        }
        
        return $sanitized;
    }

    public function render_default_messages_description() {
        echo '<p>' . esc_html__('Configure default messages that appear in different sections of your project workflow. Each field shows the plugin\'s built-in default as a placeholder - leave fields empty to use these defaults, or add your own custom content to override them.', 'arsol-pfw') . '</p>';
        echo '<p><strong>' . esc_html__('Markdown Reference:', 'arsol-pfw') . '</strong> ' . esc_html__('Use **bold**, *italic*, [links](URL), `code`, - list item, > quote and other Markdown syntax for formatting.', 'arsol-pfw') . '</p>';
        echo '<p><em>' . esc_html__('Tip: Empty fields will automatically display the built-in defaults. Only customize the messages you want to change.', 'arsol-pfw') . '</em></p>';
    }

    public function render_template_overrides_description() {
        echo '<p>' . esc_html__('Use these settings to override the default plugin templates with your own shortcodes. The plugin now uses its own internal shortcodes for all content rendering, which you can see as placeholders in each input field below.', 'arsol-pfw') . '</p>';
        echo '<p>' . esc_html__('This allows for custom layouts and designs for various components without needing to edit plugin files directly. You can either use the default shortcodes as-is, or replace them with your own custom shortcodes (like Gravity Forms, Elementor widgets, etc.).', 'arsol-pfw') . '</p>';
        echo '<p><strong>' . esc_html__('Important:', 'arsol-pfw') . '</strong> ' . esc_html__('Template overrides are placed inside existing wrapper elements to preserve page structure and styling. Your shortcode content will appear within the appropriate container divs.', 'arsol-pfw') . '</p>';
        echo '<p><em>' . esc_html__('Tip: Leave fields empty to use the default shortcodes shown as placeholders, or enter your own shortcodes to customize specific areas.', 'arsol-pfw') . '</em></p>';
    }

    public function render_textarea_field($args) {
        $settings = get_option('arsol_projects_templates_settings');
        $value = isset($settings[$args['id']]) ? $settings[$args['id']] : '';
        $rows = isset($args['rows']) ? $args['rows'] : 8;
        $cols = isset($args['cols']) ? $args['cols'] : 80;
        
        // Get hardcoded default for placeholder
        $hardcoded_defaults = \Arsol_Projects_For_Woo\Admin\Setup_Defaults::get_hardcoded_defaults();
        $placeholder_text = isset($hardcoded_defaults[$args['id']]) ? $hardcoded_defaults[$args['id']] : __('Enter your markdown content here...', 'arsol-pfw');
        ?>
        <textarea id="<?php echo esc_attr($args['id']); ?>"
                  name="arsol_projects_templates_settings[<?php echo esc_attr($args['id']); ?>]"
                  rows="<?php echo esc_attr($rows); ?>"
                  cols="<?php echo esc_attr($cols); ?>"
                  class="large-text code"
                  style="font-family: Consolas, Monaco, 'Courier New', monospace; font-size: 13px; line-height: 1.4;"
                  placeholder="<?php echo esc_attr($placeholder_text); ?>"><?php echo esc_textarea($value); ?></textarea>
        
        <?php if (!empty($args['description'])) : ?>
            <p class="description"><?php echo esc_html($args['description']); ?></p>
        <?php endif; ?>
        <?php
    }

    public function render_text_field($args) {
        $settings = get_option('arsol_projects_templates_settings');
        $value = isset($settings[$args['id']]) ? $settings[$args['id']] : '';
        $pattern = isset($args['pattern']) ? $args['pattern'] : '.*';
        $placeholder = isset($args['placeholder']) ? $args['placeholder'] : '';
        ?>
        <input type="text"
               id="<?php echo esc_attr($args['id']); ?>"
               name="arsol_projects_templates_settings[<?php echo esc_attr($args['id']); ?>]"
               value="<?php echo esc_attr($value); ?>"
               class="regular-text"
               pattern="<?php echo esc_attr($pattern); ?>"
               placeholder="<?php echo esc_attr($placeholder); ?>"
               title="<?php esc_attr_e('Shortcode must be in the format [shortcode_name]', 'arsol-pfw'); ?>">
        <?php if (!empty($args['description'])) : ?>
            <p class="description"><?php echo esc_html($args['description']); ?></p>
        <?php endif; ?>
        <?php
    }

    /**
     * Get a default message by key - UPDATED for two-layer system
     * 
     * @param string $key The message key
     * @return string The effective message content (user setting or hardcoded default)
     */
    public static function get_default_message($key) {
        return \Arsol_Projects_For_Woo\Admin\Setup_Defaults::get_effective_default_message($key);
    }

    /**
     * Get all default messages - UPDATED for two-layer system
     * 
     * @return array All effective default messages
     */
    public static function get_all_default_messages() {
        $effective_messages = \Arsol_Projects_For_Woo\Admin\Setup_Defaults::get_all_effective_default_messages();
        
        return [
            'project_request_on_hold' => $effective_messages['arsol_pfw_request_default_on_hold_content'] ?? '',
            'project_request_under_review' => $effective_messages['arsol_pfw_request_default_under_review_content'] ?? '',
            'project_overview' => $effective_messages['arsol_pfw_project_default_empty_content'] ?? '',
            'project_proposals' => $effective_messages['arsol_pfw_proposal_default_empty_content'] ?? '',
        ];
    }
}
