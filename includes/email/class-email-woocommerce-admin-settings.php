<?php

namespace Arsol_Projects_For_Woo\Emails;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Email WooCommerce Admin Settings Class
 * Handles all email configuration and WooCommerce settings integration
 */
class Email_Woo_Admin_Settings {
    
    /**
     * Constructor
     */
    public function __construct() {
        // Hook into WooCommerce settings
        add_filter('woocommerce_email_settings', array($this, 'add_email_settings'));
        add_action('woocommerce_init', array($this, 'init_email_hooks'));
        
        // Admin functionality
        add_action('wp_ajax_arsol_send_test_email', array($this, 'handle_test_email'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
    }
    
    /**
     * Initialize email workflow hooks and logging
     */
    public function init_email_hooks() {
        // Ensure WooCommerce emails are loaded
        WC()->mailer();
        
        // Email event logging
        if (get_option('arsol_email_logging_enabled') === 'yes') {
            add_action('arsol_email_sent', array($this, 'log_email_event'), 10, 3);
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
            'title' => 'Arsol Projects Email System',
            'type' => 'title',
            'id' => 'arsol_email_settings',
            'desc' => 'Configure email notifications for the project workflow system.'
        );
        
        $settings[] = array(
            'title' => 'Enable Email Logging',
            'desc' => 'Log all Arsol project emails for debugging and monitoring',
            'id' => 'arsol_email_logging_enabled',
            'type' => 'checkbox',
            'default' => 'no'
        );
        
        $settings[] = array(
            'title' => 'Test Email Recipient',
            'desc' => 'Email address for testing Arsol project emails',
            'id' => 'arsol_test_email_recipient',
            'type' => 'email',
            'default' => get_option('admin_email'),
            'css' => 'min-width:300px;'
        );
        
        $settings[] = array(
            'title' => 'Customer Portal Base URL',
            'desc' => 'Base URL for customer portal links in emails (e.g., https://yoursite.com/portal/)',
            'id' => 'arsol_portal_base_url',
            'type' => 'url',
            'default' => home_url('/customer-portal/'),
            'css' => 'min-width:400px;'
        );
        
        $settings[] = array(
            'title' => 'Email System Status',
            'desc' => $this->get_email_system_status(),
            'id' => 'arsol_email_system_status',
            'type' => 'info'
        );
        
        $settings[] = array(
            'title' => 'Test Email Functionality',
            'desc' => $this->get_test_email_controls(),
            'id' => 'arsol_test_email_controls',
            'type' => 'info'
        );
        
        $settings[] = array(
            'type' => 'sectionend',
            'id' => 'arsol_email_settings'
        );
        
        return $settings;
    }
    
    /**
     * Get email system status for admin display
     * 
     * @return string
     */
    private function get_email_system_status() {
        $email_setup = new Email_Setup();
        $email_classes = $email_setup->get_email_classes();
        $total_emails = count($email_classes);
        $loaded_emails = 0;
        
        foreach ($email_classes as $class_name => $file_name) {
            $file_path = ARSOL_PROJECTS_PLUGIN_DIR . 'includes/email/' . $file_name;
            if (file_exists($file_path)) {
                $loaded_emails++;
            }
        }
        
        $status = sprintf(
            '<strong>%d of %d</strong> email classes loaded successfully.',
            $loaded_emails,
            $total_emails
        );
        
        if ($loaded_emails === $total_emails) {
            $status .= ' <span style="color: green; font-weight: bold;">✓ All systems operational</span>';
        } else {
            $status .= ' <span style="color: red; font-weight: bold;">⚠ Some email classes missing</span>';
        }
        
        // Add email list
        $status .= '<br/><br/><strong>Available Email Types:</strong><br/>';
        $status .= '<ul style="margin-left: 20px;">';
        
        $email_info = array(
            'New_Request_Email' => 'New Project Request (Customer, Admin)',
            'Request_Status_Email' => 'Project Request Status Change (Customer, Admin)', 
            'New_Proposal_Email' => 'New Project Proposal Created (Project Lead, Admin)',
            'Proposal_Processing_Email' => 'Proposal Processing Started (Customer, Project Lead, Admin)',
            'Proposal_Ready_Email' => 'Project Proposal Ready for Review (Customer, Admin)',
            'Proposal_Decision_Email' => 'Project Proposal Decision (Project Lead, Admin)',
            'Project_Creation_Email' => 'Your Project Order Is Ready (Customer, Project Lead, Admin)',
            'Project_Status_Email' => 'Project Status Update (Customer, Project Lead, Admin)',
            'Project_Completion_Email' => 'Project Completion Notification (Customer, Project Lead, Admin)',
            'Billing_Notification_Email' => 'Project Billing Notification (Customer, Admin)'
        );
        
        foreach ($email_info as $class => $description) {
            $file_exists = file_exists(ARSOL_PROJECTS_PLUGIN_DIR . 'includes/email/class-email-' . strtolower(str_replace('_', '-', str_replace('_Email', '', $class))) . '.php');
            $icon = $file_exists ? '✓' : '✗';
            $color = $file_exists ? 'green' : 'red';
            $status .= sprintf('<li><span style="color: %s;">%s</span> %s</li>', $color, $icon, $description);
        }
        
        $status .= '</ul>';
        
        return $status;
    }
    
    /**
     * Get test email controls
     * 
     * @return string
     */
    private function get_test_email_controls() {
        $html = '<div id="arsol-test-email-controls">';
        $html .= '<button type="button" class="button button-secondary" onclick="arsolSendTestEmail(\'new_request\')">Test New Request Email</button> ';
        $html .= '<button type="button" class="button button-secondary" onclick="arsolSendTestEmail(\'project_completion\')">Test Project Completion Email</button> ';
        $html .= '<button type="button" class="button button-secondary" onclick="arsolSendTestEmail(\'proposal_ready\')">Test Proposal Ready Email</button>';
        $html .= '<div id="arsol-test-email-result" style="margin-top: 10px;"></div>';
        $html .= '</div>';
        
        return $html;
    }
    
    /**
     * Handle AJAX test email request
     */
    public function handle_test_email() {
        check_ajax_referer('arsol_test_email', 'nonce');
        
        if (!current_user_can('manage_woocommerce')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }
        
        $email_type = sanitize_text_field($_POST['email_type']);
        $test_recipient = get_option('arsol_test_email_recipient');
        
        if (!$test_recipient) {
            wp_send_json_error('No test email recipient configured');
        }
        
        $result = $this->send_test_email($email_type, $test_recipient);
        
        if ($result) {
            wp_send_json_success('Test email sent successfully to ' . $test_recipient);
        } else {
            wp_send_json_error('Failed to send test email');
        }
    }
    
    /**
     * Send test email
     * 
     * @param string $email_type
     * @param string $test_recipient
     * @return bool
     */
    public function send_test_email($email_type, $test_recipient) {
        $emails = WC()->mailer()->get_emails();
        
        // Map email types to WooCommerce email keys
        $email_key_map = array(
            'new_request' => 'Arsol_New_Request_Email',
            'request_status' => 'Arsol_Request_Status_Email',
            'proposal_ready' => 'Arsol_Proposal_Ready_Email',
            'project_completion' => 'Arsol_Project_Completion_Email',
            'project_creation' => 'Arsol_Project_Creation_Email'
        );
        
        $email_key = isset($email_key_map[$email_type]) ? $email_key_map[$email_type] : null;
        
        if (!$email_key || !isset($emails[$email_key])) {
            return false;
        }
        
        // Generate test data
        $test_data = $this->generate_test_data($email_type);
        
        // Override recipient for testing
        $email = $emails[$email_key];
        $original_recipient = $email->recipient ?? '';
        $email->recipient = $test_recipient;
        
        // Trigger test email
        $result = call_user_func_array(array($email, 'trigger'), $test_data);
        
        // Restore original recipient
        $email->recipient = $original_recipient;
        
        return $result !== false;
    }
    
    /**
     * Generate test data for email types
     * 
     * @param string $email_type
     * @return array
     */
    private function generate_test_data($email_type) {
        // Generate appropriate test data based on email type
        switch ($email_type) {
            case 'new_request':
                return array(1, 1); // request_id, customer_id
            case 'request_status':
                return array(1, 'pending-review', 'under-review'); 
            case 'proposal_ready':
                return array(1, 1); // proposal_id, customer_id
            case 'project_completion':
                return array(1, 1); // project_id, customer_id
            case 'project_creation':
                return array(1, 100, 1); // project_id, order_id, customer_id
            default:
                return array(1, 1);
        }
    }
    
    /**
     * Enqueue admin scripts for test functionality
     */
    public function enqueue_admin_scripts($hook) {
        if ($hook === 'woocommerce_page_wc-settings') {
            wp_enqueue_script('arsol-email-admin', ARSOL_PROJECTS_PLUGIN_URL . 'assets/js/email-admin.js', array('jquery'), '1.0.0', true);
            wp_localize_script('arsol-email-admin', 'arsol_email_ajax', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('arsol_test_email')
            ));
        }
    }
    
    /**
     * Log email events for debugging
     * 
     * @param string $email_type
     * @param string $recipient
     * @param array $data
     */
    public function log_email_event($email_type, $recipient, $data = array()) {
        $log_data = array(
            'timestamp' => current_time('mysql'),
            'email_type' => $email_type,
            'recipient' => $recipient,
            'data' => $data,
            'user_id' => get_current_user_id()
        );
        
        error_log('Arsol Email Event: ' . json_encode($log_data));
        do_action('arsol_email_event_logged', $log_data);
    }
}
