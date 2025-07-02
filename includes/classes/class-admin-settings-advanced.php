<?php
/**
 * Admin Display Settings Class
 *
 * Handles display controls and template overrides for the plugin.
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
        // Add admin scripts for enhanced select
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
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
                'description' => __('Overrides the Create Project Form for creating new projects.', 'arsol-pfw'),
                'placeholder' => '[arsol_pfw_project_form]'
            ],
            'arsol_pfw_request_form' => [
                'title' => __('Request Project Form', 'arsol-pfw'),
                'description' => __('Overrides the Request Project Form for requesting new projects.', 'arsol-pfw'),
                'placeholder' => '[arsol_pfw_request_form]'
            ],
            'arsol_pfw_proposal_form' => [
                'title' => __('Request Project Form (Edit)', 'arsol-pfw'),
                'description' => __('Overrides the Request Project Form for editing a pending project request.', 'arsol-pfw'),
                'placeholder' => '[arsol_pfw_proposal_form]'
            ],
            'arsol_pfw_projects_list' => [
                'title' => __('Active Projects Listing', 'arsol-pfw'),
                'description' => __('Overrides the display of all active projects for a user.', 'arsol-pfw'),
                'placeholder' => '[arsol_pfw_projects_list]'
            ],
            'arsol_pfw_proposals_list' => [
                'title' => __('Project Proposals Listing', 'arsol-pfw'),
                'description' => __('Overrides the display of all project proposals for a user.', 'arsol-pfw'),
                'placeholder' => '[arsol_pfw_proposals_list]'
            ],
            'arsol_pfw_requests_list' => [
                'title' => __('Project Requests Listing', 'arsol-pfw'),
                'description' => __('Overrides the display of all project requests for a user.', 'arsol-pfw'),
                'placeholder' => '[arsol_pfw_requests_list]'
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
            'arsol_projects_templates_settings', 
            'arsol_projects_templates_settings',
            array(
                'sanitize_callback' => array($this, 'sanitize_settings'),
                'default' => array()
            )
        );

        // Register display settings
        register_setting('arsol_content_display_settings', 'arsol_content_display_settings');
        register_setting('arsol_sidebar_display_settings', 'arsol_sidebar_display_settings');
        register_setting('arsol_form_display_settings', 'arsol_form_display_settings');

        // Content Display Section
        add_settings_section(
            'arsol_content_display_section',
            __('Content Display', 'arsol-pfw'),
            array($this, 'render_content_display_section'),
            'arsol_projects_templates_settings'
        );

        // Content Display Fields
        $this->add_display_fields('content', 'arsol_content_display_section');

        // Sidebar Display Section
        add_settings_section(
            'arsol_sidebar_display_section',
            __('Sidebar Display', 'arsol-pfw'),
            array($this, 'render_sidebar_display_section'),
            'arsol_projects_templates_settings'
        );

        // Sidebar Display Fields
        $this->add_display_fields('sidebar', 'arsol_sidebar_display_section');

        // Form Display Section
        add_settings_section(
            'arsol_form_display_section',
            __('Form Display', 'arsol-pfw'),
            array($this, 'render_form_display_section'),
            'arsol_projects_templates_settings'
        );

        // Form Display Fields
        $this->add_form_display_fields();

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
     * Add display fields for a specific type (content/sidebar)
     */
    private function add_display_fields($type, $section) {
        $types = ['request', 'proposal', 'project'];
        foreach ($types as $phase_type) {
            add_settings_field(
                $type . '_' . $phase_type . '_display',
                ucfirst($phase_type) . ' ' . ucfirst($type),
                array($this, 'render_display_field'),
                'arsol_projects_templates_settings',
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
     * Add form display fields
     */
    private function add_form_display_fields() {
        $form_types = [
            'request_form' => 'Request Form',
            'edit_request_form' => 'Edit Request Form'
        ];

        foreach ($form_types as $form_type => $label) {
            add_settings_field(
                'form_' . $form_type . '_display',
                $label,
                array($this, 'render_display_field'),
                'arsol_projects_templates_settings',
                'arsol_form_display_section',
                [
                    'type' => 'form',
                    'phase_type' => $form_type,
                    'taxonomy' => 'arsol-pfw-request-stage'
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
        
        $visibility = isset($settings[$visibility_key]) ? $settings[$visibility_key] : 'hide';
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
        
        // Visibility dropdown and manage button
        echo '<div style="display: flex; align-items: center; gap: 10px; margin-bottom: 10px;">';
        echo '<select name="' . esc_attr($option_name) . '[' . esc_attr($visibility_key) . ']" style="min-width: 150px;">';
        echo '<option value="hide"' . selected($visibility, 'hide', false) . '>' . __('Hide for selected stages', 'arsol-pfw') . '</option>';
        echo '<option value="show"' . selected($visibility, 'show', false) . '>' . __('Show only for selected stages', 'arsol-pfw') . '</option>';
        echo '</select>';
        
        // Manage button
        $manage_url = admin_url('edit-tags.php?taxonomy=' . $taxonomy);
        echo '<a href="' . esc_url($manage_url) . '" target="_blank" class="button button-secondary">';
        echo __('Manage', 'arsol-pfw');
        echo '</a>';
        echo '</div>';
        
        // Stage multi-select
        echo '<select name="' . esc_attr($option_name) . '[' . esc_attr($stages_key) . '][]" multiple class="wc-enhanced-select" style="max-width: 600px; width: 100%;">';
        foreach ($terms as $term) {
            $selected = in_array($term->term_id, $selected_stages) ? 'selected' : '';
            echo '<option value="' . esc_attr($term->term_id) . '" ' . $selected . '>' . esc_html($term->name) . '</option>';
        }
        echo '</select>';
        
        echo '<p class="description">' . sprintf(__('Control when %s %s is displayed based on stage.', 'arsol-pfw'), $phase_type, $type) . '</p>';
        echo '</div>';
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
     * Render form display section
     */
    public function render_form_display_section() {
        echo '<p>' . __('Control when forms are displayed on the frontend based on stage.', 'arsol-pfw') . '</p>';
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

    public function render_template_overrides_description() {
        echo '<p>' . esc_html__('Use these settings to override the default plugin templates with your own shortcodes. The plugin now uses its own internal shortcodes for all content rendering, which you can see as placeholders in each input field below. This allows for custom layouts and designs for various components without needing to edit plugin files directly. You can either use the default shortcodes as-is, or replace them with your own custom shortcodes (like Gravity Forms, Elementor widgets, etc.).', 'arsol-pfw') . '</p>';
        echo '<p><strong>' . esc_html__('Important:', 'arsol-pfw') . '</strong> ' . esc_html__('Template overrides are placed inside existing wrapper elements to preserve page structure and styling. Your shortcode content will appear within the appropriate container divs.', 'arsol-pfw') . '</p>';
        echo '<p><em>' . esc_html__('Tip: Leave fields empty to use the default shortcodes shown as placeholders, or enter your own shortcodes to customize specific areas.', 'arsol-pfw') . '</em></p>';
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
     * Enqueue admin scripts using WordPress/WooCommerce standards
     */
    public function enqueue_admin_scripts($hook) {
        // Only load on our settings page
        if (strpos($hook, 'arsol-projects-settings') === false) {
            return;
        }

        // Check if we're on the templates tab
        $tab = isset($_GET['tab']) ? $_GET['tab'] : 'general';
        if ($tab !== 'templates') {
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
