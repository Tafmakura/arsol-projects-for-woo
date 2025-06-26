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
     * Get hardcoded default messages (Layer 1)
     * These are always available as fallbacks
     */
    public static function get_hardcoded_defaults() {
        return array(
            'project_overview_message' => __('## Project In Progress

This project is currently **active** and in development. 

### What to Expect:
- Regular updates as work progresses
- Detailed documentation will be added
- Timeline and milestones will be shared

> *We\'ll keep you informed throughout the process.*', 'arsol-pfw'),

            'project_proposals_message' => __('## Proposal Pending

No proposal content has been added yet.

### Next Steps:
- Our team is preparing your custom proposal
- You\'ll receive detailed information soon
- **Estimated delivery:** Within 2-3 business days

> *Thank you for your patience while we craft the perfect solution for you.*', 'arsol-pfw'),

            'project_request_on_hold_message' => __('## Request On Hold

Your project request is currently **on hold**.

### What This Means:
- We\'ve received your request
- Currently reviewing requirements
- Will contact you when ready to proceed

### Contact Information:
- Email: [support@yoursite.com](mailto:support@yoursite.com)
- Phone: *Available during business hours*

> *We appreciate your understanding and will be in touch soon.*', 'arsol-pfw'),

            'project_request_under_review_message' => __('## Under Review

Your project request is being **carefully reviewed** by our team.

### Review Process:
1. **Technical feasibility** assessment
2. **Resource allocation** planning  
3. **Timeline estimation**
4. **Budget preparation**

### What\'s Next:
- You\'ll hear from us within **24-48 hours**
- We may contact you for additional details
- A detailed proposal will follow

> *Thank you for choosing us for your project needs.*', 'arsol-pfw')
        );
    }

    /**
     * Get effective default message (Layer 1 + Layer 2)
     * Returns user setting if exists, otherwise hardcoded default
     */
    public static function get_effective_default_message($key) {
        $user_settings = get_option('arsol_projects_advanced_settings', array());
        
        // If user has set a custom value (even empty string), use it
        if (isset($user_settings[$key])) {
            return $user_settings[$key];
        }
        
        // Otherwise fall back to hardcoded default
        $hardcoded_defaults = self::get_hardcoded_defaults();
        return isset($hardcoded_defaults[$key]) ? $hardcoded_defaults[$key] : '';
    }

    /**
     * Get all effective default messages
     */
    public static function get_all_effective_default_messages() {
        $hardcoded_defaults = self::get_hardcoded_defaults();
        $user_settings = get_option('arsol_projects_advanced_settings', array());
        
        $effective_defaults = array();
        foreach ($hardcoded_defaults as $key => $hardcoded_value) {
            // Use user setting if exists, otherwise hardcoded default
            $effective_defaults[$key] = isset($user_settings[$key]) ? $user_settings[$key] : $hardcoded_value;
        }
        
        return $effective_defaults;
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
        $current_settings = get_option('arsol_projects_advanced_settings', array());
        
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
            update_option('arsol_projects_advanced_settings', $current_settings);
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
        $this->initialize_project_statuses();
        $this->initialize_request_statuses();
        $this->initialize_proposal_statuses();
    }

    /**
     * Initialize default project statuses
     */
    private function initialize_project_statuses() {
        $taxonomy = 'arsol-project-status';
        
        $default_statuses = array(
            'active' => array(
                'name' => __('Active', 'arsol-pfw'),
                'slug' => 'active',
                'description' => __('Project is currently active and in progress', 'arsol-pfw')
            ),
            'completed' => array(
                'name' => __('Completed', 'arsol-pfw'),
                'slug' => 'completed',
                'description' => __('Project has been completed successfully', 'arsol-pfw')
            ),
            'on-hold' => array(
                'name' => __('On Hold', 'arsol-pfw'),
                'slug' => 'on-hold',
                'description' => __('Project is temporarily paused', 'arsol-pfw')
            ),
            'cancelled' => array(
                'name' => __('Cancelled', 'arsol-pfw'),
                'slug' => 'cancelled',
                'description' => __('Project has been cancelled', 'arsol-pfw')
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
     * Initialize default request statuses
     */
    private function initialize_request_statuses() {
        $taxonomy = 'arsol-request-status';
        
        $default_statuses = array(
            'pending' => array(
                'name' => __('Pending', 'arsol-pfw'),
                'slug' => 'pending',
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
    private function initialize_proposal_statuses() {
        $taxonomy = 'arsol-proposal-status';
        
        $default_statuses = array(
            'draft' => array(
                'name' => __('Draft', 'arsol-pfw'),
                'slug' => 'draft',
                'description' => __('Proposal is in draft status', 'arsol-pfw')
            ),
            'sent' => array(
                'name' => __('Sent', 'arsol-pfw'),
                'slug' => 'sent',
                'description' => __('Proposal has been sent to client', 'arsol-pfw')
            ),
            'accepted' => array(
                'name' => __('Accepted', 'arsol-pfw'),
                'slug' => 'accepted',
                'description' => __('Proposal has been accepted', 'arsol-pfw')
            ),
            'rejected' => array(
                'name' => __('Rejected', 'arsol-pfw'),
                'slug' => 'rejected',
                'description' => __('Proposal has been rejected', 'arsol-pfw')
            ),
            'expired' => array(
                'name' => __('Expired', 'arsol-pfw'),
                'slug' => 'expired',
                'description' => __('Proposal has expired', 'arsol-pfw')
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
} 