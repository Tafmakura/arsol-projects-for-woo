<?php
/**
 * Email WooCommerce Integration
 *
 * Manages registration of custom email classes with WooCommerce.
 *
 * @package Arsol_Projects_For_Woo\Emails
 * @since 1.0.0
 */

defined('ABSPATH') || exit;

/**
 * Email WooCommerce Integration Class
 */
class Arsol_Email_WooCommerce {

    /**
     * Initialize email system
     */
    public static function init() {
        // Include email classes
        self::include_email_classes();
        
        // Register emails with WooCommerce
        add_filter('woocommerce_email_classes', array(__CLASS__, 'register_emails'));
        
        // Hook email triggers to actions
        self::setup_email_triggers();
    }

    /**
     * Include all email class files
     */
    private static function include_email_classes() {
        $email_classes = array(
            'class-email-admin-new-request.php',
            'class-email-admin-proposal-decision.php',
            'class-email-lead-new-proposal.php',
            'class-email-lead-proposal-decision.php',
            'class-email-lead-proposal-processing.php',
            'class-email-customer-proposal-ready.php',
            'class-email-customer-project-creation.php',
            'class-email-customer-project-status.php',
            'class-email-customer-project-completion.php',
            'class-email-request-status.php',
        );

        foreach ($email_classes as $class_file) {
            $file_path = ARSOL_PFW_PLUGIN_DIR . 'includes/email/classes/' . $class_file;
            if (file_exists($file_path)) {
                require_once $file_path;
            }
        }
    }

    /**
     * Register email classes with WooCommerce
     *
     * @param array $emails Existing email classes
     * @return array Modified email classes
     */
    public static function register_emails($emails) {
        // Admin emails
        if (class_exists('Arsol_Email_Admin_New_Request')) {
            $emails['Arsol_Email_Admin_New_Request'] = new Arsol_Email_Admin_New_Request();
        }
        
        if (class_exists('Arsol_Email_Admin_Proposal_Decision')) {
            $emails['Arsol_Email_Admin_Proposal_Decision'] = new Arsol_Email_Admin_Proposal_Decision();
        }

        // Project Lead emails
        if (class_exists('Arsol_Email_Lead_New_Proposal')) {
            $emails['Arsol_Email_Lead_New_Proposal'] = new Arsol_Email_Lead_New_Proposal();
        }
        
        if (class_exists('Arsol_Email_Lead_Proposal_Decision')) {
            $emails['Arsol_Email_Lead_Proposal_Decision'] = new Arsol_Email_Lead_Proposal_Decision();
        }
        
        if (class_exists('Arsol_Email_Lead_Proposal_Processing')) {
            $emails['Arsol_Email_Lead_Proposal_Processing'] = new Arsol_Email_Lead_Proposal_Processing();
        }

        // Customer emails
        if (class_exists('Arsol_Email_Customer_Proposal_Ready')) {
            $emails['Arsol_Email_Customer_Proposal_Ready'] = new Arsol_Email_Customer_Proposal_Ready();
        }
        
        if (class_exists('Arsol_Email_Customer_Project_Creation')) {
            $emails['Arsol_Email_Customer_Project_Creation'] = new Arsol_Email_Customer_Project_Creation();
        }
        
        if (class_exists('Arsol_Email_Customer_Project_Status')) {
            $emails['Arsol_Email_Customer_Project_Status'] = new Arsol_Email_Customer_Project_Status();
        }
        
        if (class_exists('Arsol_Email_Customer_Project_Completion')) {
            $emails['Arsol_Email_Customer_Project_Completion'] = new Arsol_Email_Customer_Project_Completion();
        }

        // Shared emails
        if (class_exists('Arsol_Email_Request_Status')) {
            $emails['Arsol_Email_Request_Status'] = new Arsol_Email_Request_Status();
        }

        return $emails;
    }

    /**
     * Setup email triggers
     */
    private static function setup_email_triggers() {
        // New request submitted
        add_action('arsol_pfw_new_request_submitted', array(__CLASS__, 'trigger_new_request_admin'), 10, 2);
        
        // New proposal created
        add_action('arsol_pfw_new_proposal_created', array(__CLASS__, 'trigger_new_proposal_lead'), 10, 2);
        
        // Proposal ready for customer review
        add_action('arsol_pfw_proposal_ready_for_review', array(__CLASS__, 'trigger_proposal_ready_customer'), 10, 2);
        
        // Proposal decision made
        add_action('arsol_pfw_proposal_decision_made', array(__CLASS__, 'trigger_proposal_decision'), 10, 3);
        
        // Proposal processing started
        add_action('arsol_pfw_proposal_processing_started', array(__CLASS__, 'trigger_proposal_processing'), 10, 2);
        
        // Project/order created
        add_action('arsol_pfw_project_order_created', array(__CLASS__, 'trigger_project_creation'), 10, 3);
        
        // Project status updated
        add_action('arsol_pfw_project_status_updated', array(__CLASS__, 'trigger_project_status'), 10, 2);
        
        // Project completed
        add_action('arsol_pfw_project_completed', array(__CLASS__, 'trigger_project_completion'), 10, 2);
        
        // Request status changed
        add_action('arsol_pfw_request_status_changed', array(__CLASS__, 'trigger_request_status'), 10, 3);
    }

    /**
     * Trigger new request admin email
     */
    public static function trigger_new_request_admin($request_id, $customer_id) {
        $emails = WC()->mailer()->get_emails();
        if (isset($emails['Arsol_Email_Admin_New_Request'])) {
            $emails['Arsol_Email_Admin_New_Request']->trigger($request_id, $customer_id);
        }
    }

    /**
     * Trigger new proposal lead email
     */
    public static function trigger_new_proposal_lead($proposal_id, $lead_id) {
        $emails = WC()->mailer()->get_emails();
        if (isset($emails['Arsol_Email_Lead_New_Proposal'])) {
            $emails['Arsol_Email_Lead_New_Proposal']->trigger($proposal_id, $lead_id);
        }
    }

    /**
     * Trigger proposal ready customer email
     */
    public static function trigger_proposal_ready_customer($proposal_id, $customer_id) {
        $emails = WC()->mailer()->get_emails();
        if (isset($emails['Arsol_Email_Customer_Proposal_Ready'])) {
            $emails['Arsol_Email_Customer_Proposal_Ready']->trigger($proposal_id, $customer_id);
        }
    }

    /**
     * Trigger proposal decision emails
     */
    public static function trigger_proposal_decision($proposal_id, $decision, $customer_id) {
        $emails = WC()->mailer()->get_emails();
        
        // Trigger lead email
        if (isset($emails['Arsol_Email_Lead_Proposal_Decision'])) {
            $emails['Arsol_Email_Lead_Proposal_Decision']->trigger($proposal_id, $decision);
        }
        
        // Trigger admin email if approved (needs conversion to project/order)
        if ($decision === 'approved' && isset($emails['Arsol_Email_Admin_Proposal_Decision'])) {
            $emails['Arsol_Email_Admin_Proposal_Decision']->trigger($proposal_id, $decision);
        }
    }

    /**
     * Trigger proposal processing email
     */
    public static function trigger_proposal_processing($proposal_id, $lead_id) {
        $emails = WC()->mailer()->get_emails();
        if (isset($emails['Arsol_Email_Lead_Proposal_Processing'])) {
            $emails['Arsol_Email_Lead_Proposal_Processing']->trigger($proposal_id, $lead_id);
        }
    }

    /**
     * Trigger project creation email
     */
    public static function trigger_project_creation($project_id, $order_id, $customer_id) {
        $emails = WC()->mailer()->get_emails();
        if (isset($emails['Arsol_Email_Customer_Project_Creation'])) {
            $emails['Arsol_Email_Customer_Project_Creation']->trigger($project_id, $order_id, $customer_id);
        }
    }

    /**
     * Trigger project status email
     */
    public static function trigger_project_status($project_id, $status) {
        $emails = WC()->mailer()->get_emails();
        if (isset($emails['Arsol_Email_Customer_Project_Status'])) {
            $emails['Arsol_Email_Customer_Project_Status']->trigger($project_id, $status);
        }
    }

    /**
     * Trigger project completion email
     */
    public static function trigger_project_completion($project_id, $customer_id) {
        $emails = WC()->mailer()->get_emails();
        if (isset($emails['Arsol_Email_Customer_Project_Completion'])) {
            $emails['Arsol_Email_Customer_Project_Completion']->trigger($project_id, $customer_id);
        }
    }

    /**
     * Trigger request status email
     */
    public static function trigger_request_status($request_id, $old_status, $new_status) {
        $emails = WC()->mailer()->get_emails();
        if (isset($emails['Arsol_Email_Request_Status'])) {
            $emails['Arsol_Email_Request_Status']->trigger($request_id, $old_status, $new_status);
        }
    }
} 