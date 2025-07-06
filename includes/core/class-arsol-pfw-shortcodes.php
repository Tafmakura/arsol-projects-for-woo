<?php
/**
 * Shortcodes Class for Arsol Projects for WooCommerce
 *
 * Handles all shortcodes functionality for the Arsol Projects plugin.
 * This class will be populated with the full shortcode functionality.
 *
 * @package Arsol_PFW\Core
 * @since   1.0.0
 */

declare(strict_types=1);

namespace Arsol_PFW\Core;

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Shortcodes Class
 *
 * Manages all plugin shortcodes.
 * NOTE: This is a placeholder - the full implementation will be moved here.
 */
class Shortcodes {
    
    /**
     * Constructor
     */
    public function __construct() {
        // Initialize shortcodes
        $this->init_shortcodes();
    }
    
    /**
     * Initialize shortcodes
     */
    private function init_shortcodes() {
        // Core shortcodes with arsol_pfw_ prefix
        add_shortcode('arsol_pfw_projects', array($this, 'render_projects'));
        add_shortcode('arsol_pfw_project', array($this, 'render_single_project'));
        add_shortcode('arsol_pfw_project_categories', array($this, 'render_project_categories'));
        add_shortcode('arsol_pfw_project_orders', array($this, 'project_orders_shortcode'));
        
        // Only register subscription shortcode if WooCommerce Subscriptions is active
        if (class_exists('WC_Subscriptions')) {
            add_shortcode('arsol_pfw_project_subscriptions', array($this, 'project_subscriptions_shortcode'));
        }
        
        add_shortcode('arsol_pfw_user_projects', array($this, 'user_projects_shortcode'));
        add_shortcode('arsol_pfw_user_projects_count', array($this, 'user_projects_count_shortcode'));
        add_shortcode('arsol_pfw_projects_count', array($this, 'projects_count_shortcode'));
        
        // Template override example/demo shortcode
        add_shortcode('arsol_pfw_template_override_demo', array($this, 'template_override_demo_shortcode'));

        // Template override shortcodes - match setting names exactly
        add_shortcode('arsol_pfw_project_overview', array($this, 'project_content_active_shortcode'));
        add_shortcode('arsol_pfw_proposal_overview', array($this, 'project_content_proposal_shortcode'));
        add_shortcode('arsol_pfw_request_overview', array($this, 'project_content_request_shortcode'));
        add_shortcode('arsol_pfw_project_form', array($this, 'project_form_shortcode'));
        add_shortcode('arsol_pfw_edit_project_form', array($this, 'project_edit_form_shortcode'));
        add_shortcode('arsol_pfw_request_form', array($this, 'project_request_form_shortcode'));
        add_shortcode('arsol_pfw_edit_request_form', array($this, 'project_request_edit_form_shortcode'));
        add_shortcode('arsol_pfw_projects_list', array($this, 'projects_listing_active_shortcode'));
        add_shortcode('arsol_pfw_proposals_list', array($this, 'projects_listing_proposals_shortcode'));
        add_shortcode('arsol_pfw_requests_list', array($this, 'projects_listing_requests_shortcode'));
        add_shortcode('arsol_pfw_no_access', array($this, 'access_denied_shortcode'));

        // File-related shortcodes
        add_shortcode('arsol_pfw_proposal_files', array($this, 'proposal_files_shortcode'));
        add_shortcode('arsol_pfw_request_file_upload', array($this, 'request_file_upload_shortcode'));
        add_shortcode('arsol_pfw_project_files_list', array($this, 'project_files_list_shortcode'));
    }
    
    /**
     * Placeholder shortcode methods
     * TODO: Move the full implementation from the old shortcodes class
     */
    
    public function render_projects($atts) {
        return '<p>Projects shortcode - implementation to be moved</p>';
    }
    
    public function render_single_project($atts) {
        return '<p>Single project shortcode - implementation to be moved</p>';
    }
    
    public function render_project_categories($atts) {
        return '<p>Project categories shortcode - implementation to be moved</p>';
    }
    
    public function project_orders_shortcode($atts) {
        return '<p>Project orders shortcode - implementation to be moved</p>';
    }
    
    public function project_subscriptions_shortcode($atts) {
        return '<p>Project subscriptions shortcode - implementation to be moved</p>';
    }
    
    public function user_projects_shortcode($atts) {
        return '<p>User projects shortcode - implementation to be moved</p>';
    }
    
    public function user_projects_count_shortcode($atts) {
        return '<p>User projects count shortcode - implementation to be moved</p>';
    }
    
    public function projects_count_shortcode($atts) {
        return '<p>Projects count shortcode - implementation to be moved</p>';
    }
    
    public function template_override_demo_shortcode($atts) {
        return '<p>Template override demo shortcode - implementation to be moved</p>';
    }
    
    public function project_content_active_shortcode($atts) {
        return '<p>Project content active shortcode - implementation to be moved</p>';
    }
    
    public function project_content_proposal_shortcode($atts) {
        return '<p>Project content proposal shortcode - implementation to be moved</p>';
    }
    
    public function project_content_request_shortcode($atts) {
        return '<p>Project content request shortcode - implementation to be moved</p>';
    }
    
    public function project_form_shortcode($atts) {
        return '<p>Project form shortcode - implementation to be moved</p>';
    }
    
    public function project_edit_form_shortcode($atts) {
        return '<p>Project edit form shortcode - implementation to be moved</p>';
    }
    
    public function project_request_form_shortcode($atts) {
        return '<p>Project request form shortcode - implementation to be moved</p>';
    }
    
    public function project_request_edit_form_shortcode($atts) {
        return '<p>Project request edit form shortcode - implementation to be moved</p>';
    }
    
    public function projects_listing_active_shortcode($atts) {
        return '<p>Projects listing active shortcode - implementation to be moved</p>';
    }
    
    public function projects_listing_proposals_shortcode($atts) {
        return '<p>Projects listing proposals shortcode - implementation to be moved</p>';
    }
    
    public function projects_listing_requests_shortcode($atts) {
        return '<p>Projects listing requests shortcode - implementation to be moved</p>';
    }
    
    public function access_denied_shortcode($atts) {
        return '<p>Access denied shortcode - implementation to be moved</p>';
    }
    
    public function proposal_files_shortcode($atts) {
        return '<p>Proposal files shortcode - implementation to be moved</p>';
    }
    
    public function request_file_upload_shortcode($atts) {
        return '<p>Request file upload shortcode - implementation to be moved</p>';
    }
    
    public function project_files_list_shortcode($atts) {
        return '<p>Project files list shortcode - implementation to be moved</p>';
    }
} 