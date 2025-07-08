<?php
/**
 * Email Manager - Role-Based Email Distribution
 *
 * @package Arsol_Projects_For_Woo
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Email Manager Class
 * 
 * Manages role-based email distribution:
 * - Shop Manager emails (2)
 * - Project Lead emails (2)
 * - Customer emails (6)
 * 
 * No email is sent to multiple roles - each email targets one specific role.
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
        // Only proceed if WooCommerce is fully loaded
        if ( ! class_exists( 'WC_Email' ) ) {
            return $email_classes;
        }
        
        // Include role-based email classes (10 total)
        
        // Customer emails (6)
        require_once ARSOL_PFW_PLUGIN_DIR . 'includes/classes/integrations/woocommerce/email/class-arsol-pfw-wc-email-new-request.php';
        require_once ARSOL_PFW_PLUGIN_DIR . 'includes/classes/integrations/woocommerce/email/class-arsol-pfw-wc-email-request-stage.php';
        require_once ARSOL_PFW_PLUGIN_DIR . 'includes/classes/integrations/woocommerce/email/class-arsol-pfw-wc-email-proposal-ready.php';
        require_once ARSOL_PFW_PLUGIN_DIR . 'includes/classes/integrations/woocommerce/email/class-arsol-pfw-wc-email-project-creation.php';
        require_once ARSOL_PFW_PLUGIN_DIR . 'includes/classes/integrations/woocommerce/email/class-arsol-pfw-wc-email-project-stage.php';
        require_once ARSOL_PFW_PLUGIN_DIR . 'includes/classes/integrations/woocommerce/email/class-arsol-pfw-wc-email-project-completion.php';
        
        // Shop Manager emails (2)
        require_once ARSOL_PFW_PLUGIN_DIR . 'includes/classes/integrations/woocommerce/email/class-arsol-pfw-wc-email-admin-new-request.php';
        require_once ARSOL_PFW_PLUGIN_DIR . 'includes/classes/integrations/woocommerce/email/class-arsol-pfw-wc-email-admin-new-project.php';
        
        // Project Lead emails (2)
        require_once ARSOL_PFW_PLUGIN_DIR . 'includes/classes/integrations/woocommerce/email/class-arsol-pfw-wc-email-proposal-processing.php';
        require_once ARSOL_PFW_PLUGIN_DIR . 'includes/classes/integrations/woocommerce/email/class-arsol-pfw-wc-email-proposal-decision.php';

        // Register email classes by role (only if classes are available)
        
        // Customer emails
        if ( class_exists( 'WC_Email_New_Request' ) ) {
            $email_classes['WC_Email_New_Request'] = new WC_Email_New_Request();
        }
        if ( class_exists( 'WC_Email_Request_Stage' ) ) {
            $email_classes['WC_Email_Request_Stage'] = new WC_Email_Request_Stage();
        }
        if ( class_exists( 'WC_Email_Proposal_Ready' ) ) {
            $email_classes['WC_Email_Proposal_Ready'] = new WC_Email_Proposal_Ready();
        }
        if ( class_exists( 'WC_Email_Project_Creation' ) ) {
            $email_classes['WC_Email_Project_Creation'] = new WC_Email_Project_Creation();
        }
        if ( class_exists( 'WC_Email_Project_Stage' ) ) {
            $email_classes['WC_Email_Project_Stage'] = new WC_Email_Project_Stage();
        }
        if ( class_exists( 'WC_Email_Project_Completion' ) ) {
            $email_classes['WC_Email_Project_Completion'] = new WC_Email_Project_Completion();
        }
        
        // Shop Manager emails
        if ( class_exists( 'WC_Email_Admin_New_Request' ) ) {
            $email_classes['WC_Email_Admin_New_Request'] = new WC_Email_Admin_New_Request();
        }
        if ( class_exists( 'WC_Email_Admin_New_Project' ) ) {
            $email_classes['WC_Email_Admin_New_Project'] = new WC_Email_Admin_New_Project();
        }
        
        // Project Lead emails
        if ( class_exists( 'WC_Email_Proposal_Processing' ) ) {
            $email_classes['WC_Email_Proposal_Processing'] = new WC_Email_Proposal_Processing();
        }
        if ( class_exists( 'WC_Email_Proposal_Decision' ) ) {
            $email_classes['WC_Email_Proposal_Decision'] = new WC_Email_Proposal_Decision();
        }

        return $email_classes;
    }
} 
 