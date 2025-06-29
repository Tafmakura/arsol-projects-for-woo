<?php
/**
 * Admin Setup Defaults Class
 *
 * Handles initialization of default plugin settings, taxonomies, and data.
 * Runs once on plugin activation or when defaults need initialization.
 *
 * @package Arsol_Projects_For_Woo\Admin
 * @version 1.0.0
 */

namespace Arsol_Projects_For_Woo\Admin;

if (!defined('ABSPATH')) {
    exit;
}

class Setup_Defaults {

    /**
     * Option key to track if defaults have been initialized
     */
    const DEFAULTS_INITIALIZED_KEY = 'arsol_pfw_defaults_initialized';

    /**
     * Current defaults version - increment when defaults change
     */
    const DEFAULTS_VERSION = '1.0.0';

    /**
     * File map for hardcoded default messages
     */
    private static $file_map = [
        'arsol_pfw_project_default_empty_content' => 'content-active-empty.md',
        'arsol_pfw_proposal_default_empty_content' => 'content-proposal-empty.md',
        'arsol_pfw_request_default_on_hold_content' => 'content-request-on-hold.md',
        'arsol_pfw_request_default_under_review_content' => 'content-request-under-review.md'
    ];

    /**
     * Constructor
     */
    public function __construct() {
        // Hook into plugin activation
        add_action('arsol_pfw_plugin_activated', array($this, 'maybe_initialize_defaults'));
        
        // Hook for manual initialization (admin action)
        add_action('wp_ajax_arsol_pfw_reset_defaults', array($this, 'reset_defaults_ajax'));
        
        // Check on admin init for version updates
        add_action('admin_init', array($this, 'maybe_update_defaults'));
    }

    /**
     * Check if defaults need initialization or updating
     */
    public function maybe_initialize_defaults() {
        $initialized = get_option(self::DEFAULTS_INITIALIZED_KEY);
        
        if (!$initialized) {
            $this->initialize_all_defaults();
            update_option(self::DEFAULTS_INITIALIZED_KEY, self::DEFAULTS_VERSION);
        }
    }

    /**
     * Check if defaults need updating for version changes
     */
    public function maybe_update_defaults() {
        $current_version = get_option(self::DEFAULTS_INITIALIZED_KEY);
        
        if ($current_version && version_compare($current_version, self::DEFAULTS_VERSION, '<')) {
            $this->update_defaults($current_version, self::DEFAULTS_VERSION);
            update_option(self::DEFAULTS_INITIALIZED_KEY, self::DEFAULTS_VERSION);
        }
    }

    /**
     * Initialize all plugin defaults
     */
    public function initialize_all_defaults() {
        $this->initialize_default_messages();
        $this->initialize_default_settings();
        $this->initialize_default_taxonomies();
        
        // Log the initialization
        if (function_exists('wc_get_logger')) {
            $logger = wc_get_logger();
            $logger->info('Plugin defaults initialized', array('source' => 'arsol-pfw-defaults'));
        }
    }

    /**
     * Get hardcoded default messages (Layer 1) - UPDATED to load from files
     * These are always available as fallbacks
     */
    public static function get_hardcoded_defaults() {
        $markdown_dir = ARSOL_PROJECTS_PLUGIN_DIR . 'includes/ui/markdown/frontend/';
        
        $file_mappings = array(
            'arsol_pfw_project_default_empty_content' => 'content-active-empty.md',
            'arsol_pfw_proposal_default_empty_content' => 'content-proposal-empty.md',
            'arsol_pfw_proposal_default_empty_content' => 'content-proposal-processing.md',
            'arsol_pfw_proposal_default_empty_content' => 'content-proposal-pending-approval.md',
            'arsol_pfw_request_default_on_hold_content' => 'content-request-on-hold.md',
            'arsol_pfw_request_default_under_review_content' => 'content-request-under-review.md'
        );
        
        $defaults = array();
        
        foreach ($file_mappings as $key => $filename) {
            $defaults[$key] = self::load_markdown_content($key, $filename);
        }
        
        return $defaults;
    }

    /**
     * Load markdown content with simple fallback to hardcoded text
     * Since markdown files are part of the plugin, they should always exist
     */
    public static function load_markdown_content($key, $filename) {
        $markdown_dir = ARSOL_PROJECTS_PLUGIN_DIR . 'includes/ui/markdown/frontend/';
        $file_path = $markdown_dir . $filename;
        
        // Try to load the markdown file
        if (file_exists($file_path)) {
            $content = file_get_contents($file_path);
            if ($content !== false) {
                return rtrim($content);
            }
        }
        
        // Fallback to hardcoded text only if file doesn't exist or can't be read
        // This should rarely happen since files are part of the plugin
        return self::get_hardcoded_fallback($key);
    }

    /**
     * Hardcoded fallbacks as absolute last resort
     * These are minimal and should rarely be used
     */
    private static function get_hardcoded_fallback($key) {
        $fallbacks = array(
            'arsol_pfw_project_default_empty_content' => __('This project is currently in progress. Content and details will be added as the project develops.', 'arsol-pfw'),
            'arsol_pfw_proposal_default_empty_content' => __('No proposal content has been added yet. Please check back later for updates.', 'arsol-pfw'),
            'arsol_pfw_proposal_default_empty_content' => __('Your proposal is currently being processed. We are preparing the details and will have it ready for your review soon.', 'arsol-pfw'),
            'arsol_pfw_proposal_default_empty_content' => __('Your proposal is ready and pending your approval. Please review the details below and let us know if you approve.', 'arsol-pfw'),
            'arsol_pfw_request_default_on_hold_content' => __('Your project request is currently on hold. We will contact you when we can proceed with your request.', 'arsol-pfw'),
            'arsol_pfw_request_default_under_review_content' => __('Your project request is under review. We will get back to you shortly with next steps.', 'arsol-pfw')
        );
        
        return isset($fallbacks[$key]) ? $fallbacks[$key] : '';
    }

    /**
     * Get fallback default (DEPRECATED - use load_markdown_content instead)
     */
    private static function get_fallback_default($key) {
        return self::get_hardcoded_fallback($key);
    }

    /**
     * Get effective default message (user setting or hardcoded fallback)
     * 
     * @param string $key The message key
     * @return string The effective message content
     */
    public static function get_effective_default_message($key) {
        // Check user setting first
        $user_settings = get_option('arsol_projects_templates_settings', array());
        if (!empty($user_settings[$key])) {
            return $user_settings[$key];
        }
        
        // Fall back to hardcoded default
        $hardcoded_defaults = self::get_hardcoded_defaults();
        return isset($hardcoded_defaults[$key]) ? $hardcoded_defaults[$key] : '';
    }

    /**
     * Get all effective default messages
     * 
     * @return array All effective default messages
     */
    public static function get_all_effective_default_messages() {
        $user_settings = get_option('arsol_projects_templates_settings', array());
        $hardcoded_defaults = self::get_hardcoded_defaults();
        
        $effective_messages = array();
        
        // For each hardcoded default, use user setting if available, otherwise use hardcoded
        foreach ($hardcoded_defaults as $key => $default_value) {
            $effective_messages[$key] = !empty($user_settings[$key]) ? $user_settings[$key] : $default_value;
        }
        
        return $effective_messages;
    }

    /**
     * Initialize default messages - UPDATED for two-layer system
     * Now we DON'T pre-populate the database, keeping it clean
     */
    private function initialize_default_messages() {
        // In the two-layer system, we don't pre-populate the database
        // The hardcoded defaults are always available as fallbacks
        // This keeps the database clean and allows proper empty state detection
        
        // Only initialize if there are legacy values that need migration
        $current_settings = get_option('arsol_projects_templates_settings', array());
        
        // Check if we have old-style pre-populated defaults that need to be cleared
        $hardcoded_defaults = self::get_hardcoded_defaults();
        $needs_cleanup = false;
        
        foreach ($hardcoded_defaults as $key => $hardcoded_value) {
            if (isset($current_settings[$key]) && $current_settings[$key] === $hardcoded_value) {
                // This is a pre-populated default, remove it to enable proper fallback
                unset($current_settings[$key]);
                $needs_cleanup = true;
            }
        }
        
        if ($needs_cleanup) {
            update_option('arsol_projects_templates_settings', $current_settings);
        }
    }

    /**
     * Initialize default general settings
     */
    private function initialize_default_settings() {
        $current_settings = get_option('arsol_projects_settings', array());
        
        $default_settings = array(
            'user_project_permissions' => 'request',
            'default_user_permission' => 'request',
            'require_project_selection' => 0,
            'mixed_cart_behavior' => 'add_all',
            'manage_roles' => array('administrator', 'editor'),
            'create_roles' => array('administrator', 'editor', 'author'),
            'enable_project_comments' => 1,
            'enable_project_request_comments' => 1,
            'enable_project_proposal_comments' => 1
        );

        // Only set defaults if they don't already exist
        $updated = false;
        foreach ($default_settings as $key => $default_value) {
            if (!isset($current_settings[$key])) {
                $current_settings[$key] = $default_value;
                $updated = true;
            }
        }

        if ($updated) {
            update_option('arsol_projects_settings', $current_settings);
        }
    }

    /**
     * Initialize default taxonomy terms
     */
    private function initialize_default_taxonomies() {
        $this->initialize_defaults();
    }

    /**
     * Initialize all defaults
     */
    private function initialize_defaults() {
        $this->initialize_request_stages();
        $this->initialize_proposal_stages();
    }

    /**
     * Initialize default request stages
     */
    private function initialize_request_stages() {
        $taxonomy = 'arsol-pfw-request-stage';
        
        $default_statuses = array(
            'pending-review' => array(
                'name' => __('Pending Review', 'arsol-pfw'),
                'slug' => 'pending-review',
                'description' => __('Request is pending review', 'arsol-pfw')
            ),
            'under-review' => array(
                'name' => __('Under Review', 'arsol-pfw'),
                'slug' => 'under-review',
                'description' => __('Request is currently being reviewed', 'arsol-pfw')
            ),
            'approved' => array(
                'name' => __('Approved', 'arsol-pfw'),
                'slug' => 'approved',
                'description' => __('Request has been approved', 'arsol-pfw')
            ),
            'rejected' => array(
                'name' => __('Rejected', 'arsol-pfw'),
                'slug' => 'rejected',
                'description' => __('Request has been rejected', 'arsol-pfw')
            ),
            'on-hold' => array(
                'name' => __('On Hold', 'arsol-pfw'),
                'slug' => 'on-hold',
                'description' => __('Request is on hold', 'arsol-pfw')
            )
        );

        foreach ($default_statuses as $status_data) {
            if (!term_exists($status_data['slug'], $taxonomy)) {
                wp_insert_term(
                    $status_data['name'],
                    $taxonomy,
                    array(
                        'slug' => $status_data['slug'],
                        'description' => $status_data['description']
                    )
                );
            }
        }
    }

    /**
     * Initialize default proposal statuses
     */
    private function initialize_proposal_stages() {
        $taxonomy = 'arsol-pfw-proposal-stage';
        
        $default_statuses = array(
            'processing' => array(
                'name' => __('Processing', 'arsol-pfw'),
                'slug' => 'processing',
                'description' => __('Proposal is being processed', 'arsol-pfw')
            ),
            'pending-approval' => array(
                'name' => __('Pending Approval', 'arsol-pfw'),
                'slug' => 'pending-approval',
                'description' => __('Proposal is pending customer approval', 'arsol-pfw')
            ),
            'approved' => array(
                'name' => __('Approved', 'arsol-pfw'),
                'slug' => 'approved',
                'description' => __('Proposal has been approved', 'arsol-pfw')
            ),
            'rejected' => array(
                'name' => __('Rejected', 'arsol-pfw'),
                'slug' => 'rejected',
                'description' => __('Proposal has been rejected', 'arsol-pfw')
            )
        );

        foreach ($default_statuses as $status_data) {
            if (!term_exists($status_data['slug'], $taxonomy)) {
                wp_insert_term(
                    $status_data['name'],
                    $taxonomy,
                    array(
                        'slug' => $status_data['slug'],
                        'description' => $status_data['description']
                    )
                );
            }
        }
    }

    /**
     * Update defaults for version changes
     */
    private function update_defaults($old_version, $new_version) {
        // Handle version-specific updates here
        // Example: if (version_compare($old_version, '1.1.0', '<')) { ... }
        
        if (function_exists('wc_get_logger')) {
            $logger = wc_get_logger();
            $logger->info("Plugin defaults updated from {$old_version} to {$new_version}", array('source' => 'arsol-pfw-defaults'));
        }
    }

    /**
     * Force reset all defaults (admin action)
     */
    public function reset_defaults_ajax() {
        if (!current_user_can('manage_options') || !wp_verify_nonce($_POST['nonce'], 'arsol_pfw_reset_defaults')) {
            wp_send_json_error('Permission denied');
        }

        // Force re-initialization
        delete_option(self::DEFAULTS_INITIALIZED_KEY);
        $this->maybe_initialize_defaults();

        wp_send_json_success(array(
            'message' => __('Defaults have been reset successfully', 'arsol-pfw')
        ));
    }

    /**
     * Check if defaults have been initialized
     */
    public static function are_defaults_initialized() {
        return get_option(self::DEFAULTS_INITIALIZED_KEY) !== false;
    }

    /**
     * Get current defaults version
     */
    public static function get_defaults_version() {
        return get_option(self::DEFAULTS_INITIALIZED_KEY, '0.0.0');
    }

    /**
     * Debug method to check if markdown files exist and are readable
     * Only available for administrators
     */
    public static function debug_markdown_files() {
        if (!current_user_can('manage_options')) {
            return array('error' => 'Permission denied');
        }

        $markdown_dir = ARSOL_PROJECTS_PLUGIN_DIR . 'includes/ui/markdown/frontend/';
        
        $file_mappings = array(
            'arsol_pfw_project_default_empty_content' => 'content-active-empty.md',
            'arsol_pfw_proposal_default_empty_content' => 'content-proposal-empty.md',
            'arsol_pfw_proposal_default_empty_content' => 'content-proposal-processing.md',
            'arsol_pfw_proposal_default_empty_content' => 'content-proposal-pending-approval.md',
            'arsol_pfw_request_default_on_hold_content' => 'content-request-on-hold.md',
            'arsol_pfw_request_default_under_review_content' => 'content-request-under-review.md'
        );
        
        $debug_info = array(
            'markdown_dir' => $markdown_dir,
            'dir_exists' => is_dir($markdown_dir),
            'dir_readable' => is_readable($markdown_dir),
            'files' => array()
        );
        
        foreach ($file_mappings as $key => $filename) {
            $file_path = $markdown_dir . $filename;
            
            $debug_info['files'][$key] = array(
                'filename' => $filename,
                'path' => $file_path,
                'exists' => file_exists($file_path),
                'readable' => is_readable($file_path),
                'size' => file_exists($file_path) ? filesize($file_path) : 0,
                'preview' => file_exists($file_path) ? substr(file_get_contents($file_path), 0, 100) . '...' : 'File not found',
                'effective_content' => self::load_markdown_content($key, $filename)
            );
        }
        
        return $debug_info;
    }

    /**
     * Initialize default advanced settings if they don't exist
     */
    private function initialize_default_advanced_settings() {
        $current_settings = get_option('arsol_projects_templates_settings', array());
        
        // We don't set defaults for advanced settings anymore - 
        // they're handled by the two-layer system (user setting + hardcoded fallback)
        // This just ensures the option exists
        if (false === get_option('arsol_projects_templates_settings')) {
            update_option('arsol_projects_templates_settings', $current_settings);
        }
    }
} 