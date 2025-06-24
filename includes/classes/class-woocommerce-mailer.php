<?php
namespace Arsol_Projects_For_Woo\Classes;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * WooCommerce Mailer Class
 * 
 * Complete email system for Arsol Projects for Woo
 * Handles email registration, workflow triggers, templates, and WooCommerce integration
 * Manages all email functionality including logging, settings, and event handling
 */
class Woocommerce_Mailer {
    
    /**
     * Constructor
     */
    public function __construct() {
        add_action('init', array($this, 'init'));
    }
    
    /**
     * Initialize the mailer
     */
    public function init() {
        // Hook into WooCommerce email system
        add_filter('woocommerce_email_classes', array($this, 'add_email_classes'));
        add_action('woocommerce_init', array($this, 'load_email_classes'));
        
        // Setup all workflow email triggers
        $this->setup_workflow_hooks();
        
        // Add email settings to WooCommerce
        add_filter('woocommerce_email_settings', array($this, 'add_email_settings'));
    }
    
    /**
     * Setup workflow hooks for email triggers
     */
    private function setup_workflow_hooks() {
        // Request workflow hooks
        add_action('arsol_request_created', array($this, 'handle_request_created'), 10, 2);
        add_action('arsol_request_status_changed', array($this, 'handle_request_status_changed'), 10, 3);
        
        // Proposal workflow hooks
        add_action('arsol_proposal_processing_started', array($this, 'handle_proposal_processing'), 10, 3);
        add_action('arsol_proposal_ready_for_review', array($this, 'handle_proposal_ready'), 10, 2);
        add_action('arsol_proposal_approved', array($this, 'handle_proposal_approved'), 10, 2);
        add_action('arsol_proposal_rejected', array($this, 'handle_proposal_rejected'), 10, 2);
        
        // Project workflow hooks
        add_action('arsol_project_created', array($this, 'handle_project_created'), 10, 3);
        add_action('arsol_project_status_changed', array($this, 'handle_project_status_changed'), 10, 3);
        
        // WooCommerce integration hooks
        add_action('woocommerce_order_status_completed', array($this, 'handle_order_completed'), 10, 1);
        add_action('woocommerce_subscription_status_active', array($this, 'handle_subscription_active'), 10, 1);
        
        // Legacy support maintained for backward compatibility
        add_action('arsol_proposal_status_changed', array($this, 'trigger_proposal_status_email'), 10, 3);
        add_action('arsol_new_proposal_created', array($this, 'trigger_new_proposal_email'), 10, 2);
    }
    
    /**
     * Add custom email classes to WooCommerce
     * 
     * @param array $email_classes
     * @return array
     */
    public function add_email_classes($email_classes) {
        // Include base email class
        require_once ARSOL_PFW_PLUGIN_DIR . 'includes/emails/class-base-email.php';
        
        // Include all email class files
        require_once ARSOL_PFW_PLUGIN_DIR . 'includes/emails/class-new-request-email.php';
        require_once ARSOL_PFW_PLUGIN_DIR . 'includes/emails/class-request-status-email.php';
        require_once ARSOL_PFW_PLUGIN_DIR . 'includes/emails/class-proposal-processing-email.php';
        require_once ARSOL_PFW_PLUGIN_DIR . 'includes/emails/class-proposal-ready-email.php';
        require_once ARSOL_PFW_PLUGIN_DIR . 'includes/emails/class-proposal-decision-email.php';
        require_once ARSOL_PFW_PLUGIN_DIR . 'includes/emails/class-project-creation-email.php';
        require_once ARSOL_PFW_PLUGIN_DIR . 'includes/emails/class-project-status-email.php';
        
        // Add email classes
        $email_classes['Arsol_New_Request_Email'] = new \Arsol_Projects_For_Woo\Emails\New_Request_Email();
        $email_classes['Arsol_Request_Status_Email'] = new \Arsol_Projects_For_Woo\Emails\Request_Status_Email();
        $email_classes['Arsol_Proposal_Processing_Email'] = new \Arsol_Projects_For_Woo\Emails\Proposal_Processing_Email();
        $email_classes['Arsol_Proposal_Ready_Email'] = new \Arsol_Projects_For_Woo\Emails\Proposal_Ready_Email();
        $email_classes['Arsol_Proposal_Decision_Email'] = new \Arsol_Projects_For_Woo\Emails\Proposal_Decision_Email();
        $email_classes['Arsol_Project_Creation_Email'] = new \Arsol_Projects_For_Woo\Emails\Project_Creation_Email();
        $email_classes['Arsol_Project_Status_Email'] = new \Arsol_Projects_For_Woo\Emails\Project_Status_Email();
        
        return $email_classes;
    }
    
    /**
     * Load email classes when WooCommerce is ready
     */
    public function load_email_classes() {
        // Ensure WooCommerce emails are loaded
        WC()->mailer();
    }
    
    /**
     * Trigger proposal status change email
     * 
     * @param int $proposal_id
     * @param string $old_status
     * @param string $new_status
     */
    public function trigger_proposal_status_email($proposal_id, $old_status, $new_status) {
        $emails = WC()->mailer()->get_emails();
        
        if (isset($emails['Arsol_Proposal_Status_Email'])) {
            $emails['Arsol_Proposal_Status_Email']->trigger($proposal_id, $old_status, $new_status);
        }
    }
    
    /**
     * Trigger new proposal email
     * 
     * @param int $proposal_id
     * @param int $customer_id
     */
    public function trigger_new_proposal_email($proposal_id, $customer_id) {
        $emails = WC()->mailer()->get_emails();
        
        if (isset($emails['Arsol_New_Proposal_Email'])) {
            $emails['Arsol_New_Proposal_Email']->trigger($proposal_id, $customer_id);
        }
    }
    
    /**
     * Get email template path
     * 
     * @param string $template_name
     * @return string
     */
    public static function get_email_template_path($template_name) {
        return ARSOL_PFW_PLUGIN_DIR . 'includes/emails/templates/' . $template_name;
    }
    
    /**
     * Get email template with fallback
     * 
     * @param string $template_name
     * @param array $args
     * @return string
     */
    public static function get_email_template($template_name, $args = array()) {
        $template_path = self::get_email_template_path($template_name);
        
        if (file_exists($template_path)) {
            ob_start();
            extract($args);
            include $template_path;
            return ob_get_clean();
        }
        
        return '';
    }
    
    /**
     * Send custom email
     * 
     * @param string $to
     * @param string $subject
     * @param string $message
     * @param array $headers
     * @param array $attachments
     * @return bool
     */
    public static function send_email($to, $subject, $message, $headers = array(), $attachments = array()) {
        // Use WooCommerce's mailer if available
        if (class_exists('WC_Emails')) {
            $mailer = WC()->mailer();
            return $mailer->send($to, $subject, $message, $headers, $attachments);
        }
        
        // Fallback to WordPress wp_mail
        return wp_mail($to, $subject, $message, $headers, $attachments);
    }
    
    /**
     * Get default email headers
     * 
     * @return array
     */
    public static function get_default_headers() {
        return array(
            'Content-Type: text/html; charset=UTF-8',
            'From: ' . get_option('woocommerce_email_from_name') . ' <' . get_option('woocommerce_email_from_address') . '>'
        );
    }
    
    /**
     * Format email content with WooCommerce styling
     * 
     * @param string $content
     * @return string
     */
    public static function format_email_content($content) {
        // Wrap content in WooCommerce email template
        $email_heading = '';
        $email = null;
        
        // Get WooCommerce email template
        ob_start();
        wc_get_template('emails/email-header.php', array('email_heading' => $email_heading));
        echo $content;
        wc_get_template('emails/email-footer.php');
        
        return ob_get_clean();
    }
    
    /**
     * Handle request created event
     * 
     * @param int $request_id
     * @param int $customer_id
     */
    public function handle_request_created($request_id, $customer_id) {
        // This triggers the New_Request_Email class
        do_action('arsol_new_request_created', $request_id, $customer_id);
        
        // Log the event if logging is enabled
        $this->log_email_event('request_created', $request_id, array(
            'customer_id' => $customer_id
        ));
    }
    
    /**
     * Handle request status change
     * 
     * @param int $request_id
     * @param string $old_status
     * @param string $new_status
     */
    public function handle_request_status_changed($request_id, $old_status, $new_status) {
        // Skip email for initial status
        if ($old_status === 'pending-review' && $new_status === 'pending-review') {
            return;
        }
        
        $this->log_email_event('request_status_changed', $request_id, array(
            'old_status' => $old_status,
            'new_status' => $new_status
        ));
    }
    
    /**
     * Handle proposal processing started
     * 
     * @param int $proposal_id
     * @param int $customer_id
     * @param int $project_lead_id
     */
    public function handle_proposal_processing($proposal_id, $customer_id, $project_lead_id) {
        $this->log_email_event('proposal_processing_started', $proposal_id, array(
            'customer_id' => $customer_id,
            'project_lead_id' => $project_lead_id
        ));
    }
    
    /**
     * Handle proposal ready for review
     * 
     * @param int $proposal_id
     * @param int $customer_id
     */
    public function handle_proposal_ready($proposal_id, $customer_id) {
        $this->log_email_event('proposal_ready', $proposal_id, array(
            'customer_id' => $customer_id
        ));
    }
    
    /**
     * Handle proposal approved
     * 
     * @param int $proposal_id
     * @param int $customer_id
     */
    public function handle_proposal_approved($proposal_id, $customer_id) {
        $this->log_email_event('proposal_approved', $proposal_id, array(
            'customer_id' => $customer_id,
            'decision' => 'approved'
        ));
    }
    
    /**
     * Handle proposal rejected
     * 
     * @param int $proposal_id
     * @param int $customer_id
     */
    public function handle_proposal_rejected($proposal_id, $customer_id) {
        $this->log_email_event('proposal_rejected', $proposal_id, array(
            'customer_id' => $customer_id,
            'decision' => 'rejected'
        ));
    }
    
    /**
     * Handle project created
     * 
     * @param int $project_id
     * @param int $order_id
     * @param int $customer_id
     */
    public function handle_project_created($project_id, $order_id, $customer_id) {
        $this->log_email_event('project_created', $project_id, array(
            'order_id' => $order_id,
            'customer_id' => $customer_id
        ));
    }
    
    /**
     * Handle project status change
     * 
     * @param int $project_id
     * @param string $old_status
     * @param string $new_status
     */
    public function handle_project_status_changed($project_id, $old_status, $new_status) {
        $this->log_email_event('project_status_changed', $project_id, array(
            'old_status' => $old_status,
            'new_status' => $new_status
        ));
    }
    
    /**
     * Handle WooCommerce order completed
     * 
     * @param int $order_id
     */
    public function handle_order_completed($order_id) {
        $project_id = get_post_meta($order_id, '_arsol_pfw_project_id', true);
        
        if ($project_id) {
            do_action('arsol_project_payment_completed', $project_id, $order_id);
            
            $this->log_email_event('order_completed', $order_id, array(
                'project_id' => $project_id
            ));
        }
    }
    
    /**
     * Handle WooCommerce subscription active
     * 
     * @param \WC_Subscription $subscription
     */
    public function handle_subscription_active($subscription) {
        $project_id = $subscription->get_meta('_arsol_pfw_project_id');
        
        if ($project_id) {
            do_action('arsol_project_subscription_active', $project_id, $subscription->get_id());
            
            $this->log_email_event('subscription_active', $subscription->get_id(), array(
                'project_id' => $project_id
            ));
        }
    }
    
    /**
     * Add email settings to WooCommerce
     * 
     * @param array $settings
     * @return array
     */
    public function add_email_settings($settings) {
        $settings[] = array(
            'title' => 'Arsol Projects Email Settings',
            'type' => 'title',
            'id' => 'arsol_email_settings'
        );
        
        $settings[] = array(
            'title' => 'Enable Email Logging',
            'desc' => 'Log all Arsol project emails for debugging',
            'id' => 'arsol_email_logging_enabled',
            'type' => 'checkbox',
            'default' => 'no'
        );
        
        $settings[] = array(
            'title' => 'Test Email Recipient',
            'desc' => 'Email address for testing Arsol emails',
            'id' => 'arsol_test_email_recipient',
            'type' => 'email',
            'default' => get_option('admin_email')
        );
        
        $settings[] = array(
            'type' => 'sectionend',
            'id' => 'arsol_email_settings'
        );
        
        return $settings;
    }
    
    /**
     * Log email event
     * 
     * @param string $event
     * @param int $object_id
     * @param array $data
     */
    private function log_email_event($event, $object_id, $data = array()) {
        if (get_option('arsol_email_logging_enabled') === 'yes') {
            $log_data = array(
                'timestamp' => current_time('mysql'),
                'event' => $event,
                'object_id' => $object_id,
                'data' => $data,
                'user_id' => get_current_user_id()
            );
            
            error_log('Arsol Email Event: ' . json_encode($log_data));
            do_action('arsol_email_event_logged', $log_data);
        }
    }
    
    /**
     * Get proposal email data
     * 
     * @param int $proposal_id
     * @return array
     */
    public static function get_proposal_email_data($proposal_id) {
        $proposal = get_post($proposal_id);
        
        if (!$proposal || $proposal->post_type !== 'arsol-pfw-proposal') {
            return array();
        }
        
        $customer_id = get_post_meta($proposal_id, '_arsol_pfw_proposal_customer_id', true);
        $customer = get_user_by('id', $customer_id);
        
        return array(
            'proposal_id' => $proposal_id,
            'proposal_title' => $proposal->post_title,
            'proposal_content' => $proposal->post_content,
            'proposal_status' => $proposal->post_status,
            'customer_id' => $customer_id,
            'customer_email' => $customer ? $customer->user_email : '',
            'customer_name' => $customer ? \Arsol_Projects_For_Woo\Woocommerce::format_customer_name($customer) : '',
            'proposal_url' => get_permalink($proposal_id),
            'admin_url' => admin_url('post.php?post=' . $proposal_id . '&action=edit')
        );
    }
}
