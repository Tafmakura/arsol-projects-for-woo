<?php
/**
 * Email Manager
 *
 * @package Arsol_Projects_For_Woo
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Email Manager Class
 */
class Arsol_Email_Manager {

    /**
     * Initialize the email manager.
     */
    public static function init() {
        add_filter( 'woocommerce_email_classes', array( __CLASS__, 'add_emails' ) );
    }

    /**
     * Add custom emails to WooCommerce.
     *
     * @param array $email_classes Existing email classes.
     * @return array Modified email classes.
     */
    public static function add_emails( $email_classes ) {
        // Include working email classes
        require_once ARSOL_PFW_PLUGIN_DIR . 'includes/email/class-wc-email-new-request.php';
        require_once ARSOL_PFW_PLUGIN_DIR . 'includes/email/class-wc-email-proposal-decision.php';

        // Add to email classes
        $email_classes['WC_Email_New_Request'] = new WC_Email_New_Request();
        $email_classes['WC_Email_Proposal_Decision'] = new WC_Email_Proposal_Decision();

        return $email_classes;
    }
} 