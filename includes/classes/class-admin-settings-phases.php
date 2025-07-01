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
        // Add admin scripts for Select2
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        // Add AJAX handler for loading taxonomy terms
        add_action('wp_ajax_arsol_load_taxonomy_terms', array($this, 'ajax_load_taxonomy_terms'));
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
        register_setting('arsol_content_display_settings', 'arsol_content_display_settings');

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
            'arsol_phases_content_display',
            __('Content Display', 'arsol-pfw'),
            array($this, 'render_content_display_section'),
            'arsol_phases_settings'
        );

        // Request Content Display
        add_settings_field(
            'request_content_display',
            __('Request Content Display', 'arsol-pfw'),
            array($this, 'render_content_display_field'),
            'arsol_phases_settings',
            'arsol_phases_content_display',
            array(
                'type' => 'request',
                'taxonomy' => 'arsol-pfw-request-stage'
            )
        );

        // Proposal Content Display  
        add_settings_field(
            'proposal_content_display',
            __('Proposal Content Display', 'arsol-pfw'),
            array($this, 'render_content_display_field'),
            'arsol_phases_settings',
            'arsol_phases_content_display',
            array(
                'type' => 'proposal',
                'taxonomy' => 'arsol-pfw-proposal-stage'
            )
        );

        // Project Content Display
        add_settings_field(
            'project_content_display',
            __('Project Content Display', 'arsol-pfw'),
            array($this, 'render_content_display_field'),
            'arsol_phases_settings',
            'arsol_phases_content_display',
            array(
                'type' => 'project',
                'taxonomy' => 'arsol-pfw-project-stage'
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
     * Render content display section
     */
    public function render_content_display_section() {
        echo '<p>' . __('Control when post content is displayed on the frontend based on stage.', 'arsol-pfw') . '</p>';
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
     * Render content display field
     */
    public function render_content_display_field($args) {
        $settings = get_option('arsol_content_display_settings', array());
        $type = $args['type'];
        $taxonomy = $args['taxonomy'];
        
        $visibility = isset($settings[$type . '_visibility']) ? $settings[$type . '_visibility'] : 'hide';
        $stages = isset($settings[$type . '_stages']) ? $settings[$type . '_stages'] : array();
        
        // Visibility select with manage button (on top)
        echo '<div class="arsol-stage-field-container">';
        echo '<select name="arsol_content_display_settings[' . esc_attr($type) . '_visibility]" style="width: 250px;">';
        echo '<option value="hide"' . selected($visibility, 'hide', false) . '>' . __('Hide for selected stages', 'arsol-pfw') . '</option>';
        echo '<option value="show"' . selected($visibility, 'show', false) . '>' . __('Show for selected stages', 'arsol-pfw') . '</option>';
        echo '</select>';
        
        // Add manage button with WordPress secondary styling (no icon)
        $manage_url = admin_url('edit-tags.php?taxonomy=' . esc_attr($taxonomy));
        echo '<a href="' . esc_url($manage_url) . '" target="_blank" class="button button-secondary arsol-stage-manage-btn">';
        echo __('Manage Stages', 'arsol-pfw');
        echo '</a>';
        echo '</div>';
        
        // WooCommerce-style multi-select for stages
        echo '<div style="margin-bottom: 10px;">';
        echo '<select name="arsol_content_display_settings[' . esc_attr($type) . '_stages][]" multiple class="wc-enhanced-select arsol-stages-select2" data-taxonomy="' . esc_attr($taxonomy) . '" style="width: 100%; min-width: 300px;">';
        
        // Pre-populate with selected values for better UX
        if (!empty($stages)) {
            $selected_terms = get_terms(array(
                'taxonomy' => $taxonomy,
                'hide_empty' => false,
                'include' => $stages
            ));
            
            if (!is_wp_error($selected_terms)) {
                foreach ($selected_terms as $term) {
                    echo '<option value="' . esc_attr($term->term_id) . '" selected>' . esc_html($term->name) . '</option>';
                }
            }
        }
        echo '</select>';
        echo '</div>';
        
        echo '<p class="description">' . sprintf(__('Control when %s content appears on the frontend based on the current stage.', 'arsol-pfw'), esc_html($type)) . '</p>';
    }

    /**
     * AJAX handler for loading taxonomy terms
     */
    public function ajax_load_taxonomy_terms() {
        // Check user permissions
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'arsol-pfw'));
        }

        // Verify nonce for security
        check_ajax_referer('arsol_taxonomy_terms_nonce', 'security');

        $taxonomy = sanitize_text_field($_POST['taxonomy']);
        $search = isset($_POST['term']) ? sanitize_text_field($_POST['term']) : '';

        // Validate taxonomy exists and user can edit terms
        if (!taxonomy_exists($taxonomy) || !current_user_can('manage_categories')) {
            wp_send_json_error(__('Invalid taxonomy or insufficient permissions.', 'arsol-pfw'));
        }

        $args = array(
            'taxonomy' => $taxonomy,
            'hide_empty' => false,
            'number' => 50, // Limit for performance
            'orderby' => 'name',
            'order' => 'ASC'
        );

        if (!empty($search)) {
            $args['search'] = $search;
        }

        $terms = get_terms($args);
        $results = array();

        if (!is_wp_error($terms)) {
            foreach ($terms as $term) {
                $results[] = array(
                    'id' => $term->term_id,
                    'text' => $term->name,
                    'slug' => $term->slug
                );
            }
        }

        wp_send_json_success($results);
    }

    /**
     * Enqueue admin scripts using WordPress/WooCommerce standards
     */
    public function enqueue_admin_scripts($hook) {
        // Only load on our settings page
        if (strpos($hook, 'arsol-projects-settings') === false) {
            return;
        }

        // Check if we're on the phases tab
        $tab = isset($_GET['tab']) ? $_GET['tab'] : 'general';
        if ($tab !== 'phases') {
            return;
        }

        // Use WooCommerce's enhanced select functionality if available
        if (class_exists('WooCommerce')) {
            // WooCommerce provides selectWoo (enhanced Select2)
            wp_enqueue_script('selectWoo');
            wp_enqueue_style('select2');
            
            // Also enqueue WooCommerce admin styles for consistency
            wp_enqueue_style('woocommerce_admin_styles');
        } else {
            // Fallback to WordPress core Select2
            wp_enqueue_script('select2');
            wp_enqueue_style('select2');
        }

        // Localize script with proper WordPress standards
        $script_handle = class_exists('WooCommerce') ? 'selectWoo' : 'select2';
        wp_localize_script($script_handle, 'arsol_taxonomy_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('arsol_taxonomy_terms_nonce'),
            'strings' => array(
                'select_stages' => __('Select stages...', 'arsol-pfw'),
                'searching' => __('Searching...', 'arsol-pfw'),
                'no_results' => __('No results found', 'arsol-pfw')
            )
        ));
        
        // Initialize enhanced select with WordPress/WooCommerce standards
        $select_function = class_exists('WooCommerce') ? 'selectWoo' : 'select2';
        wp_add_inline_script($script_handle, '
            jQuery(document).ready(function($) {
                $(".arsol-stages-select2").each(function() {
                    var $select = $(this);
                    var taxonomy = $select.data("taxonomy");
                    
                    $select.' . $select_function . '({
                        placeholder: arsol_taxonomy_ajax.strings.select_stages,
                        allowClear: true,
                        width: "100%",
                        ajax: {
                            url: arsol_taxonomy_ajax.ajax_url,
                            dataType: "json",
                            delay: 250,
                            data: function (params) {
                                return {
                                    action: "arsol_load_taxonomy_terms",
                                    taxonomy: taxonomy,
                                    term: params.term || "",
                                    security: arsol_taxonomy_ajax.nonce
                                };
                            },
                            processResults: function (response) {
                                if (response.success && response.data) {
                                    return { results: response.data };
                                }
                                return { results: [] };
                            },
                            cache: true
                        },
                        minimumInputLength: 0,
                        language: {
                            searching: function() {
                                return arsol_taxonomy_ajax.strings.searching;
                            },
                            noResults: function() {
                                return arsol_taxonomy_ajax.strings.no_results;
                            }
                        },
                        escapeMarkup: function(markup) { 
                            return markup; 
                        }
                    });
                });
            });
        ');
    }
}
