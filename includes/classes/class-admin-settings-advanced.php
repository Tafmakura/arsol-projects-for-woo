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
        
        // Add AJAX handler for cleanup
        add_action('wp_ajax_arsol_cleanup_conversions', array($this, 'handle_cleanup_ajax'));
        
        // Add AJAX handler for reset defaults
        add_action('wp_ajax_arsol_reset_defaults', array($this, 'handle_reset_defaults_ajax'));
    }

    public function init_translations() {
        // Initialize default message fields
        $this->default_message_fields = [
            'project_overview_message' => [
                'title' => __('Active Project (Empty)', 'arsol-pfw'),
                'description' => __('Placeholder message for projects when they don\'t have project content.', 'arsol-pfw')
            ],
            'project_proposals_message' => [
                'title' => __('Project Proposal (Empty)', 'arsol-pfw'),
                'description' => __('Placeholder message for project proposals when they don\'t have content.', 'arsol-pfw')
            ],
            'project_request_on_hold_message' => [
                'title' => __('Project Request (On-Hold)', 'arsol-pfw'),
                'description' => __('Default message displayed when a project request is put on hold.', 'arsol-pfw')
            ],
            'project_request_under_review_message' => [
                'title' => __('Project Request (Under Review)', 'arsol-pfw'),
                'description' => __('Default message displayed when a project request is under review.', 'arsol-pfw')
            ]
        ];
        
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

        // Default Messages Section
        add_settings_section(
            'arsol_projects_default_messages_section',
            __('Default Messages', 'arsol-pfw'),
            array($this, 'render_default_messages_description'),
            'arsol_projects_advanced_settings'
        );

        foreach ($this->default_message_fields as $id => $field_data) {
            add_settings_field(
                $id,
                $field_data['title'],
                array($this, 'render_textarea_field'),
                'arsol_projects_advanced_settings',
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

        // Conversion Management Section
        add_settings_section(
            'arsol_projects_conversion_management_section',
            __('Conversion Management', 'arsol-pfw'),
            array($this, 'render_conversion_management_description'),
            'arsol_projects_advanced_settings'
        );

        add_settings_field(
            'conversion_cleanup',
            __('Maintenance', 'arsol-pfw'),
            array($this, 'render_conversion_cleanup_field'),
            'arsol_projects_advanced_settings',
            'arsol_projects_conversion_management_section'
        );

        // Reset Defaults Section
        add_settings_section(
            'arsol_projects_reset_defaults_section',
            __('Reset Defaults', 'arsol-pfw'),
            array($this, 'render_reset_defaults_description'),
            'arsol_projects_advanced_settings'
        );

        add_settings_field(
            'reset_defaults',
            __('Reset Plugin Defaults', 'arsol-pfw'),
            array($this, 'render_reset_defaults_field'),
            'arsol_projects_advanced_settings',
            'arsol_projects_reset_defaults_section'
        );
    }

    public function render_default_messages_description() {
        echo '<p>' . esc_html__('Configure default messages that appear in different sections of your project workflow. Each field shows the plugin\'s built-in default as a placeholder - leave fields empty to use these defaults, or add your own custom content to override them.', 'arsol-pfw') . '</p>';
        echo '<p><strong>' . esc_html__('Markdown Reference:', 'arsol-pfw') . '</strong> ' . esc_html__('Use **bold**, *italic*, [links](URL), `code`, - list item, > quote and other Markdown syntax for formatting.', 'arsol-pfw') . '</p>';
        echo '<p><em>' . esc_html__('Tip: Empty fields will automatically display the built-in defaults. Only customize the messages you want to change.', 'arsol-pfw') . '</em></p>';
    }

    public function render_conversion_management_description() {
        echo '<p>' . esc_html__('Manage the conversion system that handles transforming requests to proposals and proposals to projects. The system includes automatic rollback capabilities and cleanup functionality.', 'arsol-pfw') . '</p>';
    }

    public function render_conversion_cleanup_field() {
        ?>
        <button type="button" id="cleanup-conversions" class="button">
            <?php esc_html_e('Clean Up Stuck Conversions', 'arsol-pfw'); ?>
        </button>
        <p class="description">
            <?php esc_html_e('Remove conversion data for processes stuck for more than 30 minutes.', 'arsol-pfw'); ?>
        </p>
        
        <script type="text/javascript">
        jQuery(document).ready(function($) {
            $('#cleanup-conversions').on('click', function() {
                if (confirm('<?php esc_js_e('Clean up stuck conversions?', 'arsol-pfw'); ?>')) {
                    const button = $(this);
                    button.prop('disabled', true).text('<?php esc_js_e('Cleaning...', 'arsol-pfw'); ?>');
                    
                    $.ajax({
                        url: ajaxurl,
                        type: 'POST',
                        data: {
                            action: 'arsol_cleanup_conversions',
                            nonce: '<?php echo wp_create_nonce('arsol_admin'); ?>'
                        },
                        success: function(response) {
                            if (response.success) {
                                alert('<?php esc_js_e('Cleanup completed:', 'arsol-pfw'); ?> ' + response.data.cleaned + ' <?php esc_js_e('conversions cleaned', 'arsol-pfw'); ?>');
                            } else {
                                alert('<?php esc_js_e('Cleanup failed:', 'arsol-pfw'); ?> ' + response.data);
                            }
                            button.prop('disabled', false).text('<?php esc_js_e('Clean Up Stuck Conversions', 'arsol-pfw'); ?>');
                            location.reload();
                        },
                        error: function() {
                            alert('<?php esc_js_e('Ajax request failed.', 'arsol-pfw'); ?>');
                            button.prop('disabled', false).text('<?php esc_js_e('Clean Up Stuck Conversions', 'arsol-pfw'); ?>');
                        }
                    });
                }
            });
        });
        </script>
        <?php
    }

    /**
     * Handle AJAX cleanup request
     */
    public function handle_cleanup_ajax() {
        if (!wp_verify_nonce($_POST['nonce'], 'arsol_admin') || !current_user_can('manage_options')) {
            wp_send_json_error('Security check failed');
        }
        
        $cleaned = \Arsol_Projects_For_Woo\Workflow\Workflow_Handler::cleanup_stuck_workflows(30);
        
        wp_send_json_success(array(
            'cleaned' => $cleaned,
            'message' => sprintf(__('%d stuck conversions cleaned up.', 'arsol-pfw'), $cleaned)
        ));
    }

    /**
     * Handle AJAX reset defaults request
     */
    public function handle_reset_defaults_ajax() {
        if (!wp_verify_nonce($_POST['nonce'], 'arsol_admin') || !current_user_can('manage_options')) {
            wp_send_json_error('Security check failed');
        }
        
        // Force reset defaults by deleting the initialization flag
        delete_option(\Arsol_Projects_For_Woo\Admin\Setup_Defaults::DEFAULTS_INITIALIZED_KEY);
        
        // Trigger re-initialization
        do_action('arsol_pfw_plugin_activated');
        
        wp_send_json_success(array(
            'reset' => 1,
            'message' => __('Plugin defaults have been reset successfully.', 'arsol-pfw')
        ));
    }

    public function render_template_overrides_description() {
        echo '<p>' . esc_html__('Use these settings to override the default plugin templates with your own shortcodes. This allows for custom layouts and designs for various components without needing to edit plugin files directly. Enter the shortcode you wish to use for each template override.', 'arsol-pfw') . '</p>';
        echo '<p><strong>' . esc_html__('Important:', 'arsol-pfw') . '</strong> ' . esc_html__('Template overrides are placed inside existing wrapper elements to preserve page structure and styling. Your shortcode content will appear within the appropriate container divs.', 'arsol-pfw') . '</p>';
    }

    public function render_textarea_field($args) {
        $settings = get_option('arsol_projects_advanced_settings');
        $value = isset($settings[$args['id']]) ? $settings[$args['id']] : '';
        $rows = isset($args['rows']) ? $args['rows'] : 8;
        $cols = isset($args['cols']) ? $args['cols'] : 80;
        
        // Get hardcoded default for placeholder
        $hardcoded_defaults = \Arsol_Projects_For_Woo\Admin\Setup_Defaults::get_hardcoded_defaults();
        $placeholder_text = isset($hardcoded_defaults[$args['id']]) ? $hardcoded_defaults[$args['id']] : __('Enter your markdown content here...', 'arsol-pfw');
        ?>
        <div style="position: relative;">
            <textarea id="<?php echo esc_attr($args['id']); ?>"
                      name="arsol_projects_advanced_settings[<?php echo esc_attr($args['id']); ?>]"
                      rows="<?php echo esc_attr($rows); ?>"
                      cols="<?php echo esc_attr($cols); ?>"
                      class="large-text code"
                      style="font-family: Consolas, Monaco, 'Courier New', monospace; font-size: 13px; line-height: 1.4;"
                      placeholder="<?php echo esc_attr($placeholder_text); ?>"><?php echo esc_textarea($value); ?></textarea>
            
            <?php if (isset($hardcoded_defaults[$args['id']])) : ?>
                <button type="button" 
                        class="button button-secondary" 
                        style="margin-top: 5px;"
                        onclick="loadDefaultContent('<?php echo esc_js($args['id']); ?>')">
                    <?php _e('Load Default Content', 'arsol-pfw'); ?>
                </button>
            <?php endif; ?>
        </div>
        
        <?php if (!empty($args['description'])) : ?>
            <p class="description"><?php echo esc_html($args['description']); ?></p>
        <?php endif; ?>
        
        <?php if (empty($value) && !empty($placeholder_text)) : ?>
            <p class="description" style="margin-top: 8px; padding: 8px; background: #f9f9f9; border-left: 4px solid #00a0d2;">
                <strong><?php _e('Default Preview:', 'arsol-pfw'); ?></strong><br>
                <small style="color: #666;"><?php _e('This is what will be displayed when the field is empty. You can customize it above.', 'arsol-pfw'); ?></small>
            </p>
        <?php endif; ?>
        
        <?php
        // Add JavaScript for loading defaults (only once)
        static $script_added = false;
        if (!$script_added) :
            $script_added = true;
            ?>
            <script type="text/javascript">
            var arsolDefaultContent = <?php echo json_encode($hardcoded_defaults); ?>;
            
            function loadDefaultContent(fieldId) {
                if (arsolDefaultContent[fieldId]) {
                    if (confirm('<?php esc_js_e('Load the default content? This will replace any existing content in this field.', 'arsol-pfw'); ?>')) {
                        document.getElementById(fieldId).value = arsolDefaultContent[fieldId];
                    }
                }
            }
            </script>
        <?php endif; ?>
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
            'project_request_on_hold' => $effective_messages['project_request_on_hold_message'] ?? '',
            'project_request_under_review' => $effective_messages['project_request_under_review_message'] ?? '',
            'project_overview' => $effective_messages['project_overview_message'] ?? '',
            'project_proposals' => $effective_messages['project_proposals_message'] ?? ''
        ];
    }

    public function render_reset_defaults_description() {
        echo '<p>' . esc_html__('Use this section to reset all plugin defaults to their initial values.', 'arsol-pfw') . '</p>';
    }

    public function render_reset_defaults_field() {
        ?>
        <button type="button" id="reset-defaults" class="button">
            <?php esc_html_e('Reset Plugin Defaults', 'arsol-pfw'); ?>
        </button>
        <p class="description">
            <?php esc_html_e('This action cannot be undone.', 'arsol-pfw'); ?>
        </p>
        
        <script type="text/javascript">
        jQuery(document).ready(function($) {
            $('#reset-defaults').on('click', function() {
                if (confirm('<?php esc_js_e('Reset plugin defaults?', 'arsol-pfw'); ?>')) {
                    const button = $(this);
                    button.prop('disabled', true).text('<?php esc_js_e('Resetting...', 'arsol-pfw'); ?>');
                    
                    $.ajax({
                        url: ajaxurl,
                        type: 'POST',
                        data: {
                            action: 'arsol_reset_defaults',
                            nonce: '<?php echo wp_create_nonce('arsol_admin'); ?>'
                        },
                        success: function(response) {
                            if (response.success) {
                                alert('<?php esc_js_e('Reset completed:', 'arsol-pfw'); ?> ' + response.data.reset + ' <?php esc_js_e('defaults reset', 'arsol-pfw'); ?>');
                            } else {
                                alert('<?php esc_js_e('Reset failed:', 'arsol-pfw'); ?> ' + response.data);
                            }
                            button.prop('disabled', false).text('<?php esc_js_e('Reset Plugin Defaults', 'arsol-pfw'); ?>');
                            location.reload();
                        },
                        error: function() {
                            alert('<?php esc_js_e('Ajax request failed.', 'arsol-pfw'); ?>');
                            button.prop('disabled', false).text('<?php esc_js_e('Reset Plugin Defaults', 'arsol-pfw'); ?>');
                        }
                    });
                }
            });
        });
        </script>
        <?php
    }
}
