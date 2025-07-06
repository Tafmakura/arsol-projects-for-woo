<?php
/**
 * Email Setup Class for Arsol Projects for WooCommerce
 *
 * Initializes all email functionality including email notifications
 * and email templates.
 *
 * @package Arsol_PFW\Setup
 * @since   1.0.0
 */

declare(strict_types=1);

namespace Arsol_PFW\Setup;

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Email Setup Class
 *
 * Manages initialization of all email components.
 */
class Email {
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->init_hooks();
        $this->init_classes();
    }
    
    /**
     * Initialize hooks
     */
    private function init_hooks() {
        add_action('arsol_pfw_init', array($this, 'init'), 10);
    }
    
    /**
     * Initialize email classes
     */
    private function init_classes() {
        // Include existing email manager class
        require_once ARSOL_PFW_PLUGIN_PATH . 'includes/email/class-arsol-pfw-email-manager.php';
        
        // Initialize email classes when they exist
        if (class_exists('Arsol_Email_Manager')) {
            Arsol_Email_Manager::init();
        }
    }
    
    /**
     * Initialize email functionality
     */
    public function init() {
        // Email initialization actions
        do_action('arsol_pfw_email_init');
    }
} 