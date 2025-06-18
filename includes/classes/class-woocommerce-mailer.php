<?php
namespace Arsol_Projects_For_Woo\Classes;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * WooCommerce Mailer Class
 * 
 * Extends WooCommerce's email functionality for proposal-related emails
 * Handles email templates, notifications, and email customization
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
        
        // Email actions
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
        // Include email class files
        require_once ARSOL_PFW_PLUGIN_DIR . 'includes/emails/class-proposal-status-email.php';
        require_once ARSOL_PFW_PLUGIN_DIR . 'includes/emails/class-new-proposal-email.php';
        
        // Add email classes
        $email_classes['Arsol_Proposal_Status_Email'] = new \Arsol_Projects_For_Woo\Emails\Proposal_Status_Email();
        $email_classes['Arsol_New_Proposal_Email'] = new \Arsol_Projects_For_Woo\Emails\New_Proposal_Email();
        
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
        return ARSOL_PFW_PLUGIN_DIR . 'templates/emails/' . $template_name;
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
        
        $customer_id = get_post_meta($proposal_id, '_proposal_customer_id', true);
        $customer = get_user_by('id', $customer_id);
        
        return array(
            'proposal_id' => $proposal_id,
            'proposal_title' => $proposal->post_title,
            'proposal_content' => $proposal->post_content,
            'proposal_status' => $proposal->post_status,
            'customer_id' => $customer_id,
            'customer_email' => $customer ? $customer->user_email : '',
            'customer_name' => $customer ? $customer->display_name : '',
            'proposal_url' => get_permalink($proposal_id),
            'admin_url' => admin_url('post.php?post=' . $proposal_id . '&action=edit')
        );
    }
}
