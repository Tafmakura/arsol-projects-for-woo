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
        // Include all email classes
        require_once ARSOL_PFW_PLUGIN_DIR . 'includes/email/class-wc-email-new-request.php';
        require_once ARSOL_PFW_PLUGIN_DIR . 'includes/email/class-wc-email-proposal-decision.php';
        require_once ARSOL_PFW_PLUGIN_DIR . 'includes/email/class-wc-email-request-status.php';
        require_once ARSOL_PFW_PLUGIN_DIR . 'includes/email/class-wc-email-proposal-ready.php';
        require_once ARSOL_PFW_PLUGIN_DIR . 'includes/email/class-wc-email-project-creation.php';
        require_once ARSOL_PFW_PLUGIN_DIR . 'includes/email/class-wc-email-admin-new-request.php';
        require_once ARSOL_PFW_PLUGIN_DIR . 'includes/email/class-wc-email-admin-proposal-decision.php';
        require_once ARSOL_PFW_PLUGIN_DIR . 'includes/email/class-wc-email-new-proposal.php';
        require_once ARSOL_PFW_PLUGIN_DIR . 'includes/email/class-wc-email-proposal-processing.php';
        require_once ARSOL_PFW_PLUGIN_DIR . 'includes/email/class-wc-email-lead-proposal-processing.php';
        require_once ARSOL_PFW_PLUGIN_DIR . 'includes/email/class-wc-email-project-status.php';
        require_once ARSOL_PFW_PLUGIN_DIR . 'includes/email/class-wc-email-project-completion.php';

        // Add all email classes to WooCommerce
        $email_classes['WC_Email_New_Request'] = new WC_Email_New_Request();
        $email_classes['WC_Email_Proposal_Decision'] = new WC_Email_Proposal_Decision();
        $email_classes['WC_Email_Request_Status'] = new WC_Email_Request_Status();
        $email_classes['WC_Email_Proposal_Ready'] = new WC_Email_Proposal_Ready();
        $email_classes['WC_Email_Project_Creation'] = new WC_Email_Project_Creation();
        $email_classes['WC_Email_Admin_New_Request'] = new WC_Email_Admin_New_Request();
        $email_classes['WC_Email_Admin_Proposal_Decision'] = new WC_Email_Admin_Proposal_Decision();
        $email_classes['WC_Email_New_Proposal'] = new WC_Email_New_Proposal();
        $email_classes['WC_Email_Proposal_Processing'] = new WC_Email_Proposal_Processing();
        $email_classes['WC_Email_Lead_Proposal_Processing'] = new WC_Email_Lead_Proposal_Processing();
        $email_classes['WC_Email_Project_Status'] = new WC_Email_Project_Status();
        $email_classes['WC_Email_Project_Completion'] = new WC_Email_Project_Completion();

        return $email_classes;
    }
} 