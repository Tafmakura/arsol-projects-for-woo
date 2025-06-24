<?php
/**
 * Email Setup
 *
 * Handles initialization of the email system.
 *
 * @package Arsol_Projects_For_Woo\Emails
 * @since 1.0.0
 */

defined('ABSPATH') || exit;

/**
 * Email Setup Class
 */
class Arsol_Email_Setup {

    /**
     * Constructor
     */
    public function __construct() {
        $this->init();
    }

    /**
     * Initialize email system
     */
    private function init() {
        // Include the WooCommerce email integration class
        require_once ARSOL_PFW_PLUGIN_DIR . 'includes/email/class-email-woocommerce.php';
        
        // Initialize the email system
        Arsol_Email_WooCommerce::init();
    }
} 